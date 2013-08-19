<?php

class Wiki_SearchController extends Zend_Controller_Action {

    private $_menu;

    /**
     *
     * @var Zend_Db_Adapter_Abstract 
     */
    private $_db;
	
	public function init() {
        $this->_menu = new Menu();
        $this->_db = Zend_Registry::get('db');
    }

    public function preDispatch() {
        if ('call-file' != $this->getRequest()->getActionName()) {
            $auth = Zend_Auth::getInstance();
            $users = new Users();
            if (!$auth->hasIdentity() || !$users->IsValid()) {
                $this->_redirect('/login/logout?url=' . $_SERVER["REQUEST_URI"]);
                if (!isset($_SESSION['ckfinder'])) {
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
		$this->view->layout()->setLayout('wiki_layout'); 
    }

	function indexAction(){
		$params = $this->_request->getParams();
	    $this->view->title = "Search Results";
		$output = null;
		if (isset($params['keyword'])) {
			$output = Zend_Search_Lucene_Search_QueryParser::parse($params['keyword']);
        }
		var_dump($output);
		
		/*$pathTerm  = new Zend_Search_Lucene_Index_Term(
                     '/data/doc_dir/' . $filename, 'path'
                 );
		$pathQuery = new Zend_Search_Lucene_Search_Query_Term($pathTerm);
		 
		$query = new Zend_Search_Lucene_Search_Query_Boolean();
		$query->addSubquery($userQuery, true);
		$query->addSubquery($pathQuery, true);
		*/

	}
	
}
	