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
     * @var Wiki_Model_DbTable_Category
     */
    private $_categoryModel;
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
        $this->_categoryModel = new Wiki_Model_DbTable_Category();
    }

    public function indexAction() {
        $this->view->title = "Wiki";
        $suc = $this->_request->get('msg');
        $page = $this->_request->get('page');
        $order = $this->_request->get('orederBy');
        $sort = $this->_request->get('sortOrder');
        $cid = $this->_request->get('cid');
        if ($sort == NULL || $sort === 'DESC') {
            $this->view->sort = 'ASC';
        } else {
            $this->view->sort = 'DESC';
        }
        if ($suc == 1) {
            $this->view->message = 'The topic is deleted successfully';
        }
        $rowCount = 10;
        $count = $this->_topicsModel->GetTotal();
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Null($count));
        $paginator->setItemCountPerPage($rowCount);
        $paginator->setCurrentPageNumber($page);
        $this->view->paginator = $paginator;
        $this->view->data = $this->_detailModel->getTopicsPaging($page, $rowCount, $order, $sort,$cid);

        /*Category id which is selected*/
        $this->view->cid = $cid;
        $this->view->options = $this->_categoryModel->getOptions(0,'All');
		
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



	
}

