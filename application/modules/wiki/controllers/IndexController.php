<?php

class Wiki_IndexController extends Zend_Controller_Action {

    private $_menu;
    /**
     *
     * @var Wiki_Models_DbTable_Topics 
     */
    private $_topicsModel;
    /**
     *
     * @var Wiki_Model_Detail 
     */
    private $_detailModel;
    /**
     *
     * @var Zend_Db_Adapter_Abstract 
     */
    private $_db;

    public function init() {
        $this->_menu = new Menu();
        $this->_detailModel = new Wiki_Model_Detail();
        $this->_topicsModel = new Wiki_Model_DbTable_Topics();
        $this->_db = Zend_Registry::get('db');
        $this->_contributors = new Wiki_Model_Contributor();
        $this->_categories = new Wiki_Model_DbTable_Category();

    }
    public function indexAction() {
        $this->view->title = "Wiki";
        $suc = $this->_request->get('msg');
        $page = $this->_request->get('page');
        $order = $this->_request->get('orederBy');
        $sort= $this->_request->get('sortOrder');
        if($sort==NULL||$sort==='DESC'){
            $this->view->sort = 'ASC';
        }else{
            $this->view->sort = 'DESC';
        }
        if($suc==1){
            $this->view->message = 'The topic is deleted successfully';
        }
        $rowCount = 10;
        $count = $this->_topicsModel->GetTotal();
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Null($count));
        $paginator->setItemCountPerPage($rowCount);
        $paginator->setCurrentPageNumber($page);
        $this->view->paginator = $paginator ; 
        $this->view->data = $this->_detailModel->getTopicsPaging($page,$rowCount,$order,$sort);
    }
    public function preDispatch() {
        if ('call-file' != $this->getRequest()->getActionName()) {
            $auth = Zend_Auth::getInstance();
            $users = new Users();
            if (!$auth->hasIdentity() || !$users->IsValid()) {
                $this->_redirect('/login/logout?url=' . $_SERVER["REQUEST_URI"]);
                if(!isset($_SESSION['ckfinder'])){
                    $_SESSION['ckfinder'] = TRUE;
                }
            }
        }
        //get system title
        $get_title = new Params();
        $this->view->system_title = $get_title->GetVal("system_title");
        $this->view->system_version = $get_title->GetVal("system_version");
        //make top menu
        $this->view->top_menu = $this->_menu->GetTopMenu($this->getRequest()->getModuleName());
        $this->view->menu = $this->_menu->GetWikiMenu($this->getRequest()->getActionName());
    }
	function showContributorAction(){
		$params = $this->_request->getParams();
	    $this->view->title = "Contributor";
		$this->view->menu = $this->_menu->GetWikiMenu($params['action']);
		
		if(isset($params['userid'])){
			$uid = $params['userid'];
			$contributor = $this->_contributors->getContributorByID($uid);
			$this->view->contributor = $contributor;
			
			$this->view->latest_topics = $this->_contributors->getLimitedContributedTopicsByID($uid);
		}
		
		$this->view->top_menu = $this->_menu->GetTopMenu($this->getRequest()->getModuleName());
	}

    function contributorAction() {
        $params = $this->_request->getParams();
	    $this->view->title = "Contributions";
		
		$tableKeys = array(	1=>"dptname", 2=>"name", 3=>"contribution" );

		if(!isset($params['page'])){
			$this->getRequest()->setParam('page', 1);
		}
		
		if(!isset($params['sortBy'])){
			$this->getRequest()->setParam('sortBy', "dptname");
		}
		
		if(!isset($params['sortOrder'])){
			$this->getRequest()->setParam('sortOrder', "ASC");
		}
		
		$params = $this->_request->getParams();
		$page_urls = array();
		for($i=1; $i<count($tableKeys)+1; $i++){
				$page_urls["column_".$i] = $this->_contributors->navHelper->writeURL($params, $tableKeys[$i], $params['page']);
			}

        $contributor_array = $this->_contributors->getContributors($this->getRequest()->getParam('page'), 
       															   $this->getRequest()->getParam('sortBy'), 
       															   $this->getRequest()->getParam('sortOrder'));
        
        $this->view->current_page = $this->getRequest()->getParam('page');
		$this->view->pages = $this->_contributors->getPageCount("wiki_contributors", "uid"); 

		for($p = 1; $p<$this->view->pages+1; $p++){
			$page_urls["page_".$p] = $this->_contributors->navHelper->writeURL($params, $params['sortBy'], $p);
		}

		$this->view->page_urls = $page_urls;
        $this->view->contributor_array = $contributor_array;
		
    }

	function contributionsAction(){
		$params = $this->_request->getParams();
	    $this->view->title = "Contributions";
		
		$tableKeys = array(	1=>"title", 2=>"datecreated");

		if(!isset($params['page'])){
			$this->getRequest()->setParam('page', 1);
		}
		
		if(!isset($params['sortBy'])){
			$this->getRequest()->setParam('sortBy', "datecreated");
		}
		
		if(!isset($params['sortOrder'])){
			$this->getRequest()->setParam('sortOrder', "ASC");
		}
		
		if(isset($params['userid'])){
			$uid = $params['userid'];
			$contributor = $this->_contributors->getContributorByID($uid);
			$this->view->contributor = $contributor;
		}
		
		$params = $this->_request->getParams(); //Get params again once missing values are set
		$page_urls = array();
		for($i=1; $i<count($tableKeys)+1; $i++){
				$page_urls["column_".$i] = $this->_contributors->navHelper->writeURL($params, $tableKeys[$i], $params['page']);
			}
		
		$this->view->current_page = $this->getRequest()->getParam('page');

		$contributed_topics = $this->_contributors->getAllContributedTopicsByID($uid, 
																				$this->getRequest()->getParam('page'), 
																				$this->getRequest()->getParam('sortBy'), 
																				$this->getRequest()->getParam('sortOrder'));
		$this->view->pages = $this->_contributors->getPageCount("wiki_comments", "id", $uid, "uid"); 
		
		for($p = 1; $p<$this->view->pages+1; $p++){
			$page_urls["page_".$p] = $this->_contributors->navHelper->writeURL($params, $params['sortBy'], $p);
		}

		$this->view->page_urls = $page_urls;
		$this->view->all_topics = $contributed_topics;
	}

	function recentUpdatesAction(){
		$params = $this->_request->getParams();
	    $this->view->title = "Recent Updates";

		$tableKeys = array(	1=>"datecreated", 2=>"cname", 3=>"title", 4=>"name");
		
		if(!isset($params['page'])){
			$this->getRequest()->setParam('page', 1);
		}
		
		if(!isset($params['sortBy'])){
			$this->getRequest()->setParam('sortBy', "datecreated");
		}
		
		if(!isset($params['sortOrder'])){
			$this->getRequest()->setParam('sortOrder', "ASC");
		}
		
		$params = $this->_request->getParams(); //Get params again once missing values are set
		$page_urls = array();
		for($i=1; $i<count($tableKeys)+1; $i++){
				$page_urls["column_".$i] = $this->_contributors->navHelper->writeURL($params, $tableKeys[$i], $params['page']);
			}
		$recent_updates = $this->_contributors->getRecentUpdates($this->getRequest()->getParam('page'), 
																 $this->getRequest()->getParam('sortBy'), 
																 $this->getRequest()->getParam('sortOrder'));
																 
		$this->view->pages = $this->_contributors->getPageCount("wiki_comments", "id"); 
		
		for($p = 1; $p<$this->view->pages+1; $p++){
			$page_urls["page_".$p] = $this->_contributors->navHelper->writeURL($params, $params['sortBy'], $p);
		}

		$this->view->page_urls = $page_urls;
		$this->view->current_page = $this->getRequest()->getParam('page');
		$this->view->recent_updates = $recent_updates;
	}


	
}

