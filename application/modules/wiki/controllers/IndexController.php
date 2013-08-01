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
     * @var Wiki_Models_DbTable_Contents 
     */
    private $_contentsModel;

    public function init() {
        $this->_menu = new Menu();
        $this->_topicsModel = new Wiki_Model_DbTable_Topics();
        $this->_contentsModel = new Wiki_Model_DbTable_Contents();
    }

    public function preDispatch() {
        if ('call-file' != $this->getRequest()->getActionName()) {
            $auth = Zend_Auth::getInstance();
            $users = new Users();
            if (!$auth->hasIdentity() || !$users->IsValid()) {
                $this->_redirect('/login/logout?url=' . $_SERVER["REQUEST_URI"]);
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

    public function indexAction() {
        $this->view->title = "Wiki";
    }

    public function createAction() {
        $form = new Wiki_Form_Create();
        $this->view->form = $form;
        if ($this->_request->isPost()) {
            if ($form->isValidPartial($_POST)) {
                //date_default_timezone_set('PRC');
                $userinfo = Zend_Auth::getInstance()->getStorage()->read();
                $this->_topicsModel->__title = $this->_request->getPost('title');
                $this->_topicsModel->__uid = $userinfo->id;
                $this->_topicsModel->__create_time = date('Y-m-d H:i:s');
                $this->_topicsModel->__status = 1;
                $this->_topicsModel->__cid = $this->_request->getPost('category');
                $insertId = $this->_topicsModel->create();
                if ($insertId !== NULL) {
                    $this->_contentsModel->__tid = $insertId;
                    $this->_contentsModel->__content = $this->_request->getPost('content');
                    $this->_contentsModel->__uid = $userinfo->id;
                    $this->_contentsModel->__attachment = './';
                    $this->_contentsModel->__is_default = 1;
                    $this->_contentsModel->__status = 1;
                    $this->_contentsModel->__create_time = date('Y-m-d H:i:s');
                    $insertIdC = $this->_contentsModel->create();
                    //$insertId!==NULL?$this->_contentsModel->SetAsDefault($insertIdC,$insertId):  die('insert error');
                    echo 'then redirect to wiki detail page';
                } else {
                    die('insert error');
                }
            }
        }
    }

	function showContributorAction(){
		$params = $this->_request->getParams();
	    $this->view->title = "Contributor";
		$this->view->menu = $this->_menu->GetWikiMenu($params['action']);
		
		$contributors = new Wiki_Model_Contributor();
		
		if(isset($params['userid'])){
			$uid = $params['userid'];
			$contributor = $contributors->getContributorByID($uid);
		}
		else{
			echo "Invalid User";
		}
		
		$this->view->contributor = $contributor;
		$this->view->latest_topics = $contributors->getLimitedContributedTopicsByID($uid);
		
		$this->view->top_menu = $this->_menu->GetTopMenu($this->getRequest()->getModuleName());
	}

    function contributorAction() {
        $params = $this->_request->getParams();
	    $this->view->title = "Contributions";
		$this->view->menu = $this->_menu->GetWikiMenu($params['action']);
        $contributors = new Wiki_Model_Contributor();
		
		$tableKeys = array(	0=>"dptname", 1=>"name", 2=>"contribution" );

		if(isset($params['page'])){
			$current_page = $params['page'];
		}
		else{
			$current_page = 1;
		}
		
		if(isset($params['sortBy'])){
			$sort_column_by = $params['sortBy'];
		}
		else {
			$sort_column_by = "dptname";
		}
		
		if(isset($params['sortBy'])){
			$sort_column_order = $params['sortOrder'];
		}
		else {
			$sort_column_order = "ASC";
		}
		
		$page_urls = array();
		$i = 0;
		foreach($tableKeys as $key=>$val){
			$page_urls["column_".$i] = "";
			$reverseorder = "";
			
			if(!isset($params['page'])){
				$page_urls["column_".$i] .= "?page=".$current_page;
				}
			else {
				$page_urls["column_".$i] .= "?page=".$params['page'];
			}
			$page_urls["column_".$i] .= "&sortBy=".$val;
			(($sort_column_order === "ASC") &&($tableKeys[$i]==$sort_column_by) ? $reverseorder = "DESC" : $reverseorder = "ASC"); 
			$page_urls["column_".$i] .= "&sortOrder=".$reverseorder;
			$i++;
		}

        $contributor_array = $contributors->getContributors($current_page, $sort_column_by, $sort_column_order);
        
        $this->view->current_page = $current_page;
		$this->view->pages = $contributors -> getPageCount("wiki_contributors", 0, "uid"); 

		for($p = 1; $p<$this->view->pages+1; $p++){
			$page_urls["page_".$p] = "";

			$page_urls["page_".$p] .= "?page=".$p;
			$page_urls["page_".$p] .= "&sortBy=".$sort_column_by;
			$page_urls["page_".$p] .= "&sortOrder=".$sort_column_order;
			
		}
		var_dump($page_urls);
		$this->view->page_urls = $page_urls;
        $this->view->contributor_array = $contributor_array;
        $this->view->top_menu = $this->_menu->GetTopMenu($this->getRequest()->getModuleName());
    }

	function contributionsAction(){
		$params = $this->_request->getParams();
	    $this->view->title = "Contributions";
		$this->view->menu = $this->_menu->GetWikiMenu($params['action']);
		
		$contributors = new Wiki_Model_Contributor();
		
		if(isset($params['page'])){
			$current_page = $params['page'];
			
		}
		else{
			$current_page = 1;
		}
		
		if(isset($params['userid'])){
			$uid = $params['userid'];
			$contributor = $contributors->getContributorByID($uid);
		}
		else{
			echo "Invalid User";
		}
		
		$this->view->current_page = $current_page;
		$this->view->contributor = $contributor;
		$contributed_topics = $contributors->getAllContributedTopicsByID($uid, $current_page);
		$this->view->pages = $contributors -> getPageCount("wiki_comments", $uid, "id", "uid"); 
		
		$pageUrls = array();
		for($i = 1; $i<$this->view->pages+1; $i++){
			$pageUrls[$i] = "?userid=".$uid."&page=".$i; 
		}

		$this->view->p_urls = $pageUrls;
		$this->view->all_topics = $contributed_topics;
		$this->view->top_menu = $this->_menu->GetTopMenu($this->getRequest()->getModuleName());

	}

}

