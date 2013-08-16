<?php

class Wiki_TopicController extends Zend_Controller_Action {

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

    /**
     *
     * @var Wiki_Model_Detail 
     */
    private $_detailModel;
    /**
     *
     * @var Wiki_Model_DbTable_Category 
     */
    private $_categories;
    /**
     *
     * @var Wiki_Model_DbTable_Contributor
     */
    private $_contributorModel;
    /**
     *
     * @var Zend_Db_Adapter_Abstract 
     */
    private $_db;

    public function init() {
        $this->_menu = new Menu();
        $this->_topicsModel = new Wiki_Model_DbTable_Topics();
        $this->_contentsModel = new Wiki_Model_DbTable_Contents();
        $this->_detailModel = new Wiki_Model_Detail();
        $this->_db = Zend_Registry::get('db');
		$this->_categories = new Wiki_Model_DbTable_Category();
    }

    public function indexAction() {
        $this->view->title = "Wiki";
        $suc = $this->_request->get('msg');
        if ($suc == 1) {
            $this->view->message = 'The topic is deleted successfully';
        }
        $this->view->data = $this->_detailModel->getTopics();
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

    public function detailAction() {
        $form = new Wiki_Form_Comment();
        $commentModel = new Wiki_Model_DbTable_Comments();
        if($this->_request->isPost()){
            if($form->isValidPartial($_POST)){
                $uid = Zend_Auth::getInstance()->getStorage()->read()->id;
                $tid = $this->_request->getParam('id');
                $content = $this->_request->getPost('content');
                $commentModel->AddComment($tid, $uid, $content);
                $contentEle = $form->getElement('content');
                $contentEle->setValue('');
                $this->_redirect('/wiki/topic/detail/id/'.$tid.'/#comments');
            }
        }
        $this->view->form = $form;
        $tid = $this->_request->get('id');
        $suc = $this->_request->get('msg');
//        if ($suc == 1) {
//            $this->view->message = 'The topic is created as you see :)';
//        }
        $data = $this->_detailModel->getDetail($tid);
        $data['tid'] = $tid;
        $this->view->categorys = $this->_categories->getParentsHtml($data['parent_id']);
        $this->view->data = $data;
        
        $rowCount = 10;
        $page = $this->_request->get('page');
        $count = $commentModel->GetTotal($tid);
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Null($count));
        $paginator->getView()->assign(array('position'=>'#comments'));
        $paginator->setItemCountPerPage($rowCount);
        $paginator->setCurrentPageNumber($page);
        $this->view->paginator = $paginator ;
        $this->view->comments = $commentModel->GetComments($tid,$page,$rowCount);
    }
    public function historyAction() {
        $tid = $this->_request->get('id');
        $order = $this->_request->get('orederBy');
        $sort= $this->_request->get('sortOrder');
        if($sort==NULL||$sort==='DESC'){
            $this->view->sort = 'ASC';
        }else{
            $this->view->sort = 'DESC';
        }
        $this->view->data = $this->_detailModel->getDetails($tid,$order,$sort);
        $this->view->tid = $tid;
    }

    public function revertAction() {
        $tid = $this->_request->get('id');
        $vid = $this->_request->get('version');
        $data = $this->_detailModel->getDetailWithVersion($tid, $vid);
        $prevId = $this->_detailModel->getPreviousVersionId($tid, $vid);
        $nextId = $this->_detailModel->getNextVersionId($tid, $vid);
        $data['tid'] = $tid;
        $data['prevId'] = $prevId;
        $data['nextId'] = $nextId;
        $this->view->categorys = $this->_categories->getParentsHtml($data['parent_id']);
        $this->view->data = $data;
    }

    public function setDefaultAction() {
        $tid = $this->_request->get('id');
        $vid = $this->_request->get('version');
        $uid = Zend_Auth::getInstance()->getStorage()->read()->id;
        $newcid = $this->_contentsModel->Revert($vid, $uid);
//        $this->_forward('revert', NULL, NULL, array('id' => $tid, 'version' => $newcid));
        $this->_redirect('/wiki/topic/revert/id/'.$tid.'/version/'.$newcid);
    }

    public function deleteAction() {
        $tid = $this->_request->get('id');
        $uid = Zend_Auth::getInstance()->getStorage()->read()->id;
        $this->_detailModel->deleteTopic($uid, $tid);
//        $this->_forward('index', NULL, array('msg' => 1));
        $this->_redirect('/wiki/index/index/msg/1');
    }

    public function editAction() {
        $form = new Wiki_Form_Create();
        $this->view->title = "Wiki";
        $tid = $this->_request->get('id');
        $this->view->tid = $tid;
        $detail = $this->_detailModel->getDetail($tid);
        /* saved the topic id to post */
        $hidden = new Zend_Form_Element_Hidden('tid');
        $hidden->setValue($tid)->setName('tid');
        $form->addElement($hidden);

        /* saved the version id to post */
        $hiddenv = new Zend_Form_Element_Hidden('vid');
        $hiddenv->setValue($detail['vid'])->setName('vid');
        $form->addElement($hiddenv);

        /* set value to form */
        $content = $form->getElement('content');
        $title = $form->getElement('title');
        $category = $form->getElement('category');
        $content->setValue($detail['content']);
        $title->setValue($detail['title'])->setAttrib('disabled', 'ture');
        /* @var $categoryEle Zend_Form_Element_Select*/
        $options = $this->_categories->getOptions(0);
        $categoryEle = $form->getElement('category');
        $categoryEle->addMultiOptions($options);
        $category->setValue($detail['cid']);

        $this->view->form = $form;
        if ($this->_request->isPost()) {
            if ($form->isValidPartial($_POST)) {
                $userinfo = Zend_Auth::getInstance()->getStorage()->read();
                $tid = $this->_request->getPost('tid');
                if ($tid !== NULL) {
                    /* if the category change,save it */
                    $where = $this->_db->quoteInto('id=?', $tid);
                    $this->_topicsModel->__cid = $this->_request->getPost('category');
                    $this->_topicsModel->change($where);

                    /* create a new version */
                    $content = $this->_request->getPost('content');
                    $uid = $userinfo->id;
                    $preversion_id = $this->_request->getPost('vid');
                    $this->_contentsModel->CreateContent($tid, $uid, $content, $preversion_id,TRUE);
                    $this->_contributorModel->UpdateRecord($tid,$uid);
                    $this->view->message = 'The data was saved';
                } else {
                    die('insert error');
                }
            }
        } else {
            
        }

    }

    public function createAction() {
        $form = new Wiki_Form_Create();
        $this->view->form = $form;
        /*@var $categoryEle Zend_Form_Element_Select*/
        $options = $this->_categories->getOptions(0);
        $title = $form->getElement('title');
        $title->setAttrib('placeholder', 'Please Enter Your Title');
        $categoryEle = $form->getElement('category');
        $categoryEle->addMultiOptions(array(''=>'Please Choose A Type'));
        $categoryEle->addMultiOptions($options);
        if ($this->_request->isPost()) {
            if ($form->isValidPartial($_POST)) {
                $userinfo = Zend_Auth::getInstance()->getStorage()->read();
                $title = $this->_request->getPost('title');
                $uid = $userinfo->id;
                $cid = $this->_request->getPost('category');
                $insertId = $this->_topicsModel->CreateTopic($title,$uid,$cid);
                if ($insertId !== NULL) {
                    $content = $this->_request->getPost('content');
                    $uid = $userinfo->id;
                    $this->_contentsModel->CreateContent($insertId, $uid, $content, NULL, TRUE);
                    $this->_contributorModel->UpdateRecord($insertId,$uid);
//                    $this->_forward('detail', NULL, NULL, array('id' => $insertId, 'msg' => 1));
                    $this->_redirect('/wiki/topic/detail/id/'.$insertId.'/msg/1');
                } else {
                    die('insert error');
                }
            }
        }
    }
    public function searchedAction(){
    }

    public function autoClearAction(){
        if($_SERVER['REMOTE_ADDR']=='127.0.0.1'){
            $this->_contentsModel->Clear();
        }
        exit();
    }


    function recentUpdatesAction() {
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

