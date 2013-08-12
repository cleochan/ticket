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

	function indexAction(){
		//$this->_categories->create(0, "ZendTest3", 1);
		$params = $this->_request->getParams();
	    $this->view->title = "Category";
		
		$this->view->categories = $categories =  $this->_categories->getCategories();

	//var_dump($this->getRequest());
	}
	
	function addCategoryAction(){
		$params = $this->_request->getParams();
	    $this->view->title = "Add Category";
		$this->view->categories = $categories =  $this->_categories->getCategories();
		
		$form = new Wiki_Form_AddCategory();
		$this->view->form = $form;
		    if ($this->_request->isPost()) {
            	if ($form->isValidPartial($_POST)) {
						$this->_categories->create($this->_request->getPost('parent_id'), $this->_request->getPost('cname'), $this->_request->getPost('status'));
                   		$this->_redirect('/wiki/category');
					}
			}
	}
	
	function editCategoryAction(){
		$params = $this->_request->getParams();
	    $this->view->title = "Edit Category";
		$form = new Wiki_Form_AddCategory();
		$this->view->categories = $categories =  $this->_categories->getCategories();
		
		$this->view->form = $form;
		    if ($this->_request->isPost()) {
            	if ($form->isValidPartial($_POST)) {
						$this->_categories->edit($this->_request->getPost('category_id'), $this->_request->getPost('parent_id'), $this->_request->getPost('cname'), $this->_request->getPost('status'));
                   		$this->_redirect('/wiki/category');
					}
			}
	}

	function deleteCategoryAction(){
		$params = $this->_request->getParams();
	    $this->view->title = "Delete Category";
		$this->view->categories = $categories =  $this->_categories->getCategories();
		$form = new Wiki_Form_AddCategory();
		$this->view->form = $form;
		    if ($this->_request->isPost()) {
            	if ($form->isValidPartial($_POST)) {
						$this->_categories->delete($this->_request->getPost('category_id'));
                   		$this->_redirect('/wiki/category');
					}
			}
		
	}

}

