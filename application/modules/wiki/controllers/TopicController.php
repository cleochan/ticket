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

    /**
     *
     * @var Zend_Cache_Core
     */
    private $_cache;
    
    public function init() {
        $this->_menu = new Menu();
        $this->_topicsModel = new Wiki_Model_DbTable_Topics();
        $this->_contentsModel = new Wiki_Model_DbTable_Contents();
        $this->_detailModel = new Wiki_Model_Detail();
        $this->_db = Zend_Registry::get('db');
		$this->_categories = new Wiki_Model_DbTable_Category();
        $this->_contributorModel = new Wiki_Model_DbTable_Contributor();
		$this->_search = new Wiki_Model_Search();
		
        $frontendOptions = new Zend_Cache_Core(
                array(
                    'caching' => true,
                    'cache_id_prefix' => 'wikiSearch',
                    'logging' => false,
                    'write_control' => true,
                    'automatic_serialization' => true,
                    'ignore_user_abort' => true
                ));

        $backendOptions = new Zend_Cache_Backend_File(array(
            'cache_dir' => sys_get_temp_dir()) // Directory where to put the cache files
        );

        $this->_cache = Zend_Cache::factory($frontendOptions, $backendOptions);
    }
    public function indexAction() {
        $this->view->title = "Wiki";
        $suc = $this->_request->get('msg');
        $page = $this->_request->get('page');
        $order = $this->_request->get('orederBy');
        $sort = $this->_request->get('sortOrder');
        $cid = $this->_request->get('cid');
        $keyword = $this->_request->get('keyword');

        /* For paging */
        $rowCount = 3;
        $cids = $this->_categories->getChildrenIds($cid);
        if ($cid != NULL)
            $cids[] = $cid;
        $count = $this->_detailModel->getCount($cids, $keyword);
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Null($count));
        $paginator->setItemCountPerPage($rowCount);
        $paginator->setCurrentPageNumber($page);
        $this->view->paginator = $paginator;

        $this->view->data = $this->_detailModel->getTopicsPaging($page, $rowCount, $order, $sort, $cids, $keyword);

        /* Category paths */
        foreach ($this->view->data as $key => $value) {
            $this->view->data[$key]['category_path'] = $this->_categories->getCategoryPath($value['cid'], $value['cname'], $value['parent_id']);
        }
        /* For message */
        if ($suc == 1) {
            $this->view->message = 'The topic is deleted successfully';
        }
        /* For sort,toggle ASC and DESC when click */
        if ($sort == NULL || $sort === 'DESC') {
            $this->view->sort = 'ASC';
        } else {
            $this->view->sort = 'DESC';
        }
        /* Category id which is selected */
        $this->view->cid = $cid;
        $this->view->keyword = $keyword;
        $this->view->orderBy = $order;
        $this->view->options = $this->_categories->getSelectOptions(0, 'All');
        
        $this->view->addScriptPath(APPLICATION_PATH . '/modules/wiki/views/scripts/shared');
        echo $this->view->render('wiki_template.phtml');
		
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
        if ($suc == 1) {
            $this->view->message = 'The topic is created as you see :)';
        }elseif($suc == 2){
            $this->view->message = 'The data was saved';
        }
        $data = $this->_detailModel->getDetail($tid);
        $data['tid'] = $tid;
        $this->view->categoryPath = $this->_categories->getCategoryPath($data['cid'],$data['cname'],$data['parent_id']);
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
        $this->view->data = $this->_detailModel->getHistoryList($tid,$order,$sort);
        $this->view->tid = $tid;
    }

    public function revertAction() {
        $tid = $this->_request->get('id');
        $vid = $this->_request->get('version');
        $version_id = $this->_request->get('version_id');
        $data = $this->_detailModel->getDetailWithVersion($tid, $vid,$version_id);
        $prevId = $this->_detailModel->getPreviousVersionId($tid, $data['vid']);
        $nextId = $this->_detailModel->getNextVersionId($tid, $data['vid']);
        $data['prevId'] = $prevId;
        $data['nextId'] = $nextId;
        $this->view->categoryPath = $this->_categories->getCategoryPath($data['cid'],$data['cname'],$data['parent_id']);
        $this->_cache->clean('all', array('topic_list_cache'));
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
        $this->_cache->clean('all', array('topic_list_cache'));
        $this->_redirect('/wiki/topic/index/msg/1');
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
        $hiddenv->setValue($detail['version_id'])->setName('vid');
        $form->addElement($hiddenv);

        /* set value to form */
        $content = $form->getElement('content');
        $title = $form->getElement('title');
        $category = $form->getElement('category');
        $content->setValue($detail['content']);
        $title->setValue($detail['title'])->setAttrib('disabled', 'ture');
        /* @var $categoryEle Zend_Form_Element_Select*/
        $options = $this->_categories->getSelectOptions(0);
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
                    $this->_cache->clean('all', array('topic_list_cache'));
                    $this->_redirect('/wiki/topic/detail/id/'.$tid.'/msg/2');
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
        $options = $this->_categories->getSelectOptions(0);
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
                    $this->_cache->clean('all', array('topic_list_cache'));
                    $this->_redirect('/wiki/topic/detail/id/'.$insertId.'/msg/1');
                } else {
                    die('insert error');
                }
            }
        }
    }

    function searchedAction() {
        $this->view->title = "Wiki";
        $suc = $this->_request->get('msg');
        $page = $this->_request->get('page');
        $order = $this->_request->get('orederBy');
        $sort = $this->_request->get('sortOrder');
        $cid = $this->_request->get('cid');
        $keyword = $this->_request->get('keyword');

        /* For paging */
        $rowCount = 3;
        $cids = $this->_categories->getChildrenIds($cid);
        if ($cid != NULL)
            $cids[] = $cid;
        $count = $this->_detailModel->getCount($cids, $keyword);
        
        $session = new Zend_Session_Namespace('wiki');
        if($session->last_search_cache_id != NULL && $keyword == NULL){
            $this->view->data = $this->_cache->load($session->last_search_cache_id);
            $count = count($this->view->data);
        }else{
            $this->view->data = $this->_detailModel->getTopicsPaging($page, $rowCount, $order, $sort, $cids, $keyword);
        }
        
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Null($count));
        $paginator->setItemCountPerPage($rowCount);
        $paginator->setCurrentPageNumber($page);
        $this->view->paginator = $paginator;

<<<<<<< HEAD
        $rowCount = 10;
        $page = $this->_request->get('page');
        $count = $commentModel->GetTotal($tid);
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Null($count));
        $paginator->getView()->assign(array('position'=>'#comments'));
        $paginator->setItemCountPerPage($rowCount);
        $paginator->setCurrentPageNumber($page);

=======
        /* Category paths */
        foreach ($this->view->data as $key => $value) {
            $this->view->data[$key]['category_path'] = $this->_categories->getCategoryPath($value['cid'], $value['cname'], $value['parent_id']);
        }
        /* For message */
        if ($suc == 1) {
            $this->view->message = 'The topic is deleted successfully';
        }
        /* For sort,toggle ASC and DESC when click */
        if ($sort == NULL || $sort === 'DESC') {
            $this->view->sort = 'ASC';
        } else {
            $this->view->sort = 'DESC';
        }
        /* Category id which is selected */
        $this->view->cid = $cid;
        $this->view->keyword = $keyword;
        $this->view->orderBy = $order;
        $this->view->options = $this->_categories->getSelectOptions(0, 'All');
        
>>>>>>> 8c7196e5b9057bfca3f1c9408a8c4adcc01137aa
        $this->view->addScriptPath(APPLICATION_PATH . '/modules/wiki/views/scripts/shared');
        echo $this->view->render('wiki_template.phtml');

    }
    
    public function clearCacheAction() {
        if($_SERVER['REMOTE_ADDR']=='127.0.0.1'){
            $this->_cache->clean('all');
        }
        exit();
    }

    
    public function autoClearAction(){
        if($_SERVER['REMOTE_ADDR']=='127.0.0.1'){
            //$this->_contentsModel->Clear();
        }
        exit();
    }



}

