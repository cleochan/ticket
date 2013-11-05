<?php

class Wiki_ContributorController extends Zend_Controller_Action {

    private $_menu;

    /**
     *
     * @var Zend_Db_Adapter_Abstract 
     */
    private $_db;
	
	public function init() {
        $this->_menu = new Menu();
        $this->_db = Zend_Registry::get('db');
   		$this->_contributors = new Wiki_Model_Contributor();
        $this->_contributorModel = new Wiki_Model_DbTable_Contributor();
		$this->_detailModel = new Wiki_Model_Detail();
		$this->view->addScriptPath(APPLICATION_PATH.'/modules/wiki/views/scripts/shared');
    }

    public function preDispatch() {
/*        if ('call-file' != $this->getRequest()->getActionName()) {
            $auth = Zend_Auth::getInstance();
            $users = new Users();
            if (!$auth->hasIdentity() || !$users->IsValid()) {
                $this->_redirect('/login/logout?url=' . $_SERVER["REQUEST_URI"]);
                if (!isset($_SESSION['ckfinder'])) {
                    $_SESSION['ckfinder'] = TRUE;
                }
            }
        }*/
        //get system title
        $get_title = new Params();
        $this->view->system_title = $get_title->GetVal("system_title");
        $this->view->system_version = $get_title->GetVal("system_version");
        //make top menu
        $this->view->top_menu = $this->_menu->GetTopMenu($this->getRequest()->getModuleName());
        $this->view->menu = $this->_menu->GetWikiMenu("contributor");
		$this->view->layout()->setLayout('wiki_layout'); 
    }

    function indexAction() {
        $params = $this->_request->getParams();
        $this->view->title = "Contributions";

        if (!$this->getRequest()->getParam('page')) {
            $this->getRequest()->setParam('page', '1');
        }
        if (!$this->getRequest()->getParam('sortorder')) {
			$this->getRequest()->setParam('sortorder', "DESC");
        } elseif ($this->getRequest()->getParam('sortorder') === 'ASC') {
			$this->getRequest()->setParam('sortorder', "DESC");
        } else {
			$this->getRequest()->setParam('sortorder', "ASC");
        }
		
		$params = $this->_request->getParams();
		
		$flag = "";
		if(isset($params['contributions'])){
			$flag = "topics";
            $contributor_array = $this->_contributors->getAllContributedTopicsByID($params['user'], 
            																	   $this->getRequest()->getParam('page'), 
            																	   $this->getRequest()->getParam('sortby'),
            																	   $this->getRequest()->getParam('sortorder'));
			$table_headers = $this->_contributors->getTableHeaders($flag);
			$count = $this->_contributors->GetTotalTopicsById($params['user']);
		}elseif (isset($params['user'])){
			$flag = "user";
            $contributor_array = $this->_contributors->getAllContributedTopicsByID($params['user'], 
            																	   $this->getRequest()->getParam('page'), 
            																	   $this->getRequest()->getParam('sortby'),
            																	   $this->getRequest()->getParam('sortorder'));
			$table_headers = $this->_contributors->getTableHeaders($flag);
			$count = $this->_contributors->GetTotalTopicsById($params['user']);
	    }else{
	    	$flag = "contributors";
			$contributor_array = $this->_contributors->getContributors($this->getRequest()->getParam('page'),
																	   $this->getRequest()->getParam('sortby'),
																	   $this->getRequest()->getParam('sortorder'));
			$table_headers = $this->_contributors->getTableHeaders($flag);
			$count = $this->_contributors->GetTotalContributors();
	    }

		foreach($table_headers as $key=>$val){
			$headerLink = '<a href="/wiki/contributor/index/';
			
			if (isset($params['user'])){
				$headerLink .= 'user/' . $params['user'] .'/';
			}
			
			if (isset($params['contributions'])){
				$headerLink .= 'contributions/' . $params['contributions'] .'/';
			}
			
			$headerLink .= 'sortby/' . $key . '/sortorder/' . $params['sortorder'] .  '">' . $val . '</a>';
			$table_headers[$key] = $headerLink;
		}

		$this->view->table_headers = $table_headers;
			
		switch($flag){
			case "user":
			case "topics":
					foreach($contributor_array as $key=>$val){
						$val['topic_title'] = '<a href="/wiki/topic/detail/id/' . $val['topic_id'] . '">' . $val['topic_title'] . '</a>';
						$contributor_array[$key]['topic_title'] = $val['topic_title'];
						unset($contributor_array[$key]['topic_id']);
					}
				break;
			default:
		}		
		
		if(!$this->getRequest()->getParam('type')){
			$this->getRequest()->setParam('type', $flag);
		}

        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Null($count));
        $paginator->setItemCountPerPage($this->_contributors->getNumberRecordsPerPage());
        $paginator->setCurrentPageNumber($params['page']);
        $this->view->paginator = $paginator;

        $this->view->table_data = $contributor_array;
		
		echo $this->view->render('wiki_template.phtml');
		
    }

    function contributionsAction() {
        $params = $this->_request->getParams();
        $this->view->title = "Contributions";

        $tableKeys = array(1 => "title", 2 => "datecreated");

        if (!isset($params['page'])) {
            $this->getRequest()->setParam('page', 1);
        }

        if (!isset($params['sortBy'])) {
            $this->getRequest()->setParam('sortBy', "datecreated");
        }

        if (!isset($params['sortOrder'])) {
            $this->getRequest()->setParam('sortOrder', "ASC");
        }

        if (isset($params['userid'])) {
            $uid = $params['userid'];
            $contributor = $this->_contributors->getContributorByID($uid);
            $this->view->contributor = $contributor;
        }

        $params = $this->_request->getParams(); //Get params again once missing values are set
        $page_urls = array();
        for ($i = 1; $i < count($tableKeys) + 1; $i++) {
            $page_urls["column_" . $i] = $this->_contributors->navHelper->writeURL($params, $tableKeys[$i], $params['page']);
        }

        $this->view->current_page = $this->getRequest()->getParam('page');

        $contributed_topics = $this->_contributors->getAllContributedTopicsByID($uid, $this->getRequest()->getParam('page'), $this->getRequest()->getParam('sortBy'), $this->getRequest()->getParam('sortOrder'));
        $this->view->pages = $this->_contributors->getPageCount("wiki_comments", "id", $uid, "uid");

        for ($p = 1; $p < $this->view->pages + 1; $p++) {
            $page_urls["page_" . $p] = $this->_contributors->navHelper->writeURL($params, $params['sortBy'], $p);
        }

        $this->view->page_urls = $page_urls;
        $this->view->all_topics = $contributed_topics;
    }

    function recentUpdatesAction() {
    	$this->view->menu = $this->_menu->GetWikiMenu($this->getRequest()->getActionName());
        $params = $this->_request->getParams();
        $this->view->title = "Recent Updates";

        $tableKeys = array(1 => "datecreated", 2 => "catname", 3 => "title", 4 => "name");

        if (!isset($params['page'])) {
            $this->getRequest()->setParam('page', 1);
        }

        if (!isset($params['sortBy'])) {
            $this->getRequest()->setParam('sortBy', "datecreated");
        }

        if (!isset($params['sortOrder'])) {
            $this->getRequest()->setParam('sortOrder', "ASC");
        }

        $params = $this->_request->getParams(); //Get params again once missing values are set
        $page_urls = array();
        for ($i = 1; $i < count($tableKeys) + 1; $i++) {
            $page_urls["column_" . $i] = $this->_contributors->navHelper->writeURL($params, $tableKeys[$i], $params['page']);
        }
        $recent_updates = $this->_contributors->getRecentUpdates($this->getRequest()->getParam('page'), $this->getRequest()->getParam('sortBy'), $this->getRequest()->getParam('sortOrder'));

        $this->view->pages = $this->_contributors->getPageCount("wiki_comments", "id");

        for ($p = 1; $p < $this->view->pages + 1; $p++) {
            $page_urls["page_" . $p] = $this->_contributors->navHelper->writeURL($params, $params['sortBy'], $p);
        }

        $this->view->page_urls = $page_urls;
        $this->view->current_page = $this->getRequest()->getParam('page');
        $this->view->recent_updates = $recent_updates;
    }
	
}
	