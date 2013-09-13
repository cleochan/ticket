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
    
    public function indexAction()
    {
        $form = new Wiki_Form_Category;
        $this->view->form = $form;
        $form->category->addMultiOptions($this->_categories->getSelectOptions(0,'Root'));
        $form->cname->setAttrib('placeholder','Name Of New Category');
        if($this->_request->isPost()){
            if($form->isValidPartial($this->_getAllParams())){
                $this->_categories->create($form->category->getValue(), $form->cname->getValue(), 1);
                $this->_redirect('/wiki/category',array('method'=>'get'));
            }
        }
    }

    public function categoriesJsonAction()
    {
        $data = $this->_categories->getAllInArray();
        $this->_helper->json($data);
    }

    public function renameAction($param)
    {
        $id = $this->_request->getPost('id');
        $newName = $this->_request->getPost('newName');
        $this->_categories->rename($id, $newName);
        $this->_helper->json(array('success' => TRUE));
    }

    public function moveAction()
    {
        $id = $this->_request->getPost('id');
        $newParentId = $this->_request->getPost('newParentId');
        $this->_categories->changeParentId($id, $newParentId);
        $this->_helper->json(array('success' => TRUE));
    }

    public function deleteAction()
    {
        $id = $this->_request->getPost('id');
        if ($this->_categories->hasNoChildren($id) && $this->_topicModel->hasNoTopics($id)) {
            $this->_categories->delete($id);
            $this->_helper->json(array('success' => TRUE));
        }
        $this->_helper->json(array('success' => FALSE));
    }
    
    public function checkDeleteAction()
    {
        $id = $this->_request->getPost('id');
        $this->_helper->json(array('hasNoTopics' => $this->_topicModel->hasNoTopics($id)));
    }

}

