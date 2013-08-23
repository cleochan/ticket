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
        $this->search = new Wiki_Model_Search();

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

        $this->cache = Zend_Cache::factory($frontendOptions, $backendOptions);
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
        $this->view->menu = $this->_menu->GetWikiMenu('searched');
        $this->view->layout()->setLayout('wiki_layout');
    }

    function indexAction() {
        $params = $this->_request->getParams();
        $this->view->title = "Search Results";
        $this->cache->clean(Zend_Cache::CLEANING_MODE_OLD);
        if (isset($params['keyword'])) {
            $searchCacheID = $params['keyword'];
        } else if (isset($_SESSION["Zend_Auth"]["storage"]->last_search_term)) {
            $searchCacheID = $_SESSION["Zend_Auth"]["storage"]->last_search_term;
        } else {
            $searchCacheID = "";
        }
        $this->view->table_headers = $this->search->getTableHeaders();
        if ($searchCacheID != "") {
            if (!$this->cache->test($searchCacheID)) {
                $this->view->table_data = $this->search->search($searchCacheID);
                $this->cache->save($this->view->table_data, $searchCacheID);
            } else {
                $this->view->table_data = $this->cache->load($searchCacheID);
            }
        }
        $_SESSION["Zend_Auth"]["storage"]->last_search_term = $searchCacheID;

        $this->view->addScriptPath(APPLICATION_PATH . '/modules/wiki/views/scripts/shared');
        echo $this->view->render('wiki_template.phtml');
//        $isSearched = TRUE;
//        $this->_forward('index', 'topic','wiki',array('from'=>'searched'));
    }

}

