<?php

class Wiki_CategoryController extends Zend_Controller_Action
{

    private $_menu;

    /**
     *
     * @var Zend_Db_Adapter_Abstract 
     */
    private $_db;

    /**
     *
     * @var Wiki_Model_DbTable_Category 
     */
    private $_categories;
    
    /**
     *
     * @var Wiki_Model_DbTable_Topic
     */
    private $_topicModel;

    public function init()
    {
        $this->_menu = new Menu();
        $this->_db = Zend_Registry::get('db');
        $this->_contributors = new Wiki_Model_Contributor();
        $this->_categories = new Wiki_Model_DbTable_Category();
        $this->_topicModel = new Wiki_Model_DbTable_Topics();
        $this->_form = new Wiki_Form_Category();
    }

    public function preDispatch()
    {
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
        $this->view->menu = $this->_menu->GetWikiMenu("category");
        $this->view->layout()->setLayout('wiki_layout');
    }

    function indexAction() {
        $params = $this->_request->getParams();
        $this->view->title = "Category";

        $this->view->categories = $categories = $this->_categories->getCategories();
        $this->view->form = $this->_form;
		$this->view->error_messages = "";
	//	$this->view->form->addError('Error: Cannot delete Category with children');
        if ($this->_request->isPost()) {
            if ($this->_form->isValidPartial($_POST)) {
                if (isset($_POST['select_action_menu'])) {
                    switch ($_POST['select_action_menu']) {
                        case "add":
                            $this->_categories->create($this->_request->getPost('parent_id'), $this->_request->getPost('cname'), $this->_request->getPost('status'));
                            $this->_redirect('/wiki/category');
                            break;
                        case "edit":
                            $this->_categories->edit($this->_request->getPost('category_id'), $this->_request->getPost('parent_id'), $this->_request->getPost('cname'), $this->_request->getPost('status'));
                            $this->_redirect('/wiki/category');
                            break;
                        case "delete":
							if($this->_categories->hasNoChildren($this->_request->getPost('category_id'))){
								$this->_categories->delete($this->_request->getPost('category_id'));
		                        $this->_redirect('/wiki/category');
							}else{
								$this->view->error_messages .= "Cannot delete category with children.";
							}
                            break;
                        default:
                            break;
                    }
                }
            }
			if(isset($this->_cache)){
				$this->_cache->clean('all', array('topic_list_cache'));
			}
        }
    }

}

