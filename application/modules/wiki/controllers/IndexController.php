<?php

class Wiki_IndexController extends Zend_Controller_Action
{
    private $_menu; 
    function init() {
        $this->db = Zend_Registry::get("db");
        $this->_menu = new Menu(); 
    }

    function preDispatch()
    {  
            if('call-file' != $this->getRequest()->getActionName())
            {
                $auth = Zend_Auth::getInstance();
                $users = new Users();
                if(!$auth->hasIdentity() || !$users->IsValid())
                { 
                    $this->_redirect('/login/logout?url='.$_SERVER["REQUEST_URI"]);
                }
            }
            //get system title
            $get_title = new Params();
            $this->view->system_title = $get_title -> GetVal("system_title");
            $this->view->system_version = $get_title -> GetVal("system_version");

            //make top menu
            $this->view->top_menu = $this->_menu -> GetTopMenu($this->getRequest()->getModuleName());
    }

    public function indexAction()
    {
        $this->view->title = "Wiki";
        $this->view->menu = $this->_menu->GetWikiMenu(NULL);

    }

	function contributorAction()
	{
	    $this->view->title = "Contributor";
		$this->view->menu = $this->_menu->GetWikiMenu(NULL);
		$params = $this->_request->getParams();
		
		$contributors = new Wiki_Model_Contributor();
       	$contributor_array = $contributors -> getContributors();
		$this->view->contributor_array = $contributor_array ;
		
		$this->view->top_menu = $this->_menu -> GetTopMenu($this->getRequest()->getModuleName());
		


	}

}

