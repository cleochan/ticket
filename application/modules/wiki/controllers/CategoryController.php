<?php

class Wiki_CategoryController extends Zend_Controller_Action {

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
        $this->_categories = new Wiki_Model_DbTable_Category();
    }

    public function indexAction() {

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
    }

    public function categoryAction() {
        //$this->_categories->create(0, "ZendTest3", 1);
        $params = $this->_request->getParams();
        $this->view->title = "Category";

        $parentCategories = $this->_categories->getParentCategories();
        $subCategories = $this->_categories->getSubCategories();

        $this->view->parentCategories = $parentCategories;
        $this->view->subCategories = $subCategories;
        //var_dump($this->getRequest());
    }

    public function addCategoryAction() {
        $params = $this->_request->getParams();
        $this->view->title = "Add Category";
        $form = new Wiki_Form_AddCategory();
        $this->view->form = $form;
        if ($this->_request->isPost()) {
            if ($form->isValidPartial($_POST)) {
                $this->_categories->create($this->_request->getPost('parent_id'), $this->_request->getPost('cname'), $this->_request->getPost('status'));
                $this->view->message = 'Category Added. Returning to Categories..';
                $this->_redirect('/wiki/index/category');
            }
        }
    }

}

