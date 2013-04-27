<?php

class LoginController extends Zend_Controller_Action
{
	public $params_val;
	
	function init()
	{
		$this->db = Zend_Registry::get("db");
		//$this->auth = Zend_Registry::get("auth");	
	}
	
	function preDispatch()
	{
		//disable layout for Login page
		$this->_helper->layout->disableLayout();
		
		//get system title
		$get_title = new Params();
		$this->view->system_title = $get_title -> GetVal("system_title");
	}
	
    function indexAction()
    {
		$this->view->title = "Login"; // title of this page
		$params = $this->_request->getParams();
		$this->view->url = $params['url'];
		if ($this->_request->isPost()) {
            Zend_Loader::loadClass('Zend_Filter_StripTags');
			$f = new Zend_Filter_StripTags();
			$username = $f->filter($this->_request->getPost('username'));
			$password = $f->filter($this->_request->getPost('passwd'));
			if (empty($username)) {
				$this->view->msg = "Username is required.";
			} else {
                Zend_Loader::loadClass('Zend_Auth_Adapter_DbTable');
				$db = Zend_Registry::get('db');
				$authAdapter = new Zend_Auth_Adapter_DbTable($db);
				
				$authAdapter->setTableName('users');
				$authAdapter->setIdentityColumn('username');
				$authAdapter->setCredentialColumn('passwd');

				$authAdapter->setIdentity($username);
				$authAdapter->setCredential(md5($password));

				$auth_back = Zend_Auth::getInstance();
				$result = $auth_back->authenticate($authAdapter);

				if ($result->isValid()) {				
					$data = $authAdapter->getResultRowObject(null, 'passwd');
					if($data->status) //status=0
					{
                        $auth_back->getStorage()->write($data);
						require_once('Zend/Session/Namespace.php');
						//Zend_Loader::loadClass('Zend_Session_Namespace');
						
						$session = new Zend_Session_Namespace('Zend_Auth');
						$session->setExpirationSeconds(3600*24*365); //a year
						Zend_Session::rememberMe(3600*24*365);
						
						if($this->_request->getPost('url'))
						{
							$this->_redirect($this->_request->getPost('url'));
						}else
						{
							$this->_redirect('/index/index/type/2');
						}
					}else
					{
						Zend_Auth::getInstance()->clearIdentity();
						$this->view->msg = "Login failed";
					}
				} else {
					$this->view->msg = "Login failed";
				}	
			}
		}
    }

    function logoutAction()
    {
        $params = $this->_request->getParams();

        unset($_SESSION['search_ticket_users_current']);
        Zend_Auth::getInstance()->clearIdentity();
        $this->_redirect('/login?url='.$params['url']);
    }
    
    //set cookie and redirect to the target page to make the cookie established instantly
    function setCookieRedirectAction()
    {
        $params = $this->_request->getParams();
        
        //get cookie domain
        $params_model = new Params();
        $cookie_domain = $params_model->GetVal("cookie_domain");
        
        if(NULL !== $params['target'])
        {
            switch($params['target'])
            {
                case 1: //request index
                    if($params['cookie_value'])
                    {
                        setcookie("TICKET_INITIAL_CATEGORY_ID", $params['cookie_value'], time()+(3600*24*365), "/", $cookie_domain);
                        $this->_redirect('/requests/index/category/'.$params['cookie_value']);
                    }else{
                        setcookie("TICKET_INITIAL_CATEGORY_ID", $params['cookie_value'], time()-1, "/", $cookie_domain); //unset
                        $this->_redirect('/requests/index');
                    }
                    break;
                case 2: //request inactive index
                    if($params['cookie_value'])
                    {
                        setcookie("TICKET_INITIAL_CATEGORY_ID", $params['cookie_value'], time()+(3600*24*365), "/", $cookie_domain);
                        $this->_redirect('/requests/index-inactive/category/'.$params['cookie_value']);
                    }else{
                        setcookie("TICKET_INITIAL_CATEGORY_ID", $params['cookie_value'], time()-1, "/", $cookie_domain); //unset
                        $this->_redirect('/requests/index-inactive');
                    }
                    break;
                case 3: //ticket index
                    if($params['cookie_value'])
                    {
                        setcookie("TICKET_INITIAL_CATEGORY_ID", $params['cookie_value'], time()+(3600*24*365), "/", $cookie_domain);
                        $this->_redirect('/index/index/type/'.$params['type'].'/category/'.$params['cookie_value']);
                    }else{
                        setcookie("TICKET_INITIAL_CATEGORY_ID", $params['cookie_value'], time()-1, "/", $cookie_domain); //unset
                        $this->_redirect('/index/index/type/'.$params['type']);
                    }
                    break;
                case 4: //task index
                    if($params['cookie_value'])
                    {
                        setcookie("TICKET_INITIAL_CATEGORY_ID", $params['cookie_value'], time()+(3600*24*365), "/", $cookie_domain);
                        $this->_redirect('/tasks/index/type/'.$params['type'].'/category/'.$params['cookie_value']);
                    }else{
                        setcookie("TICKET_INITIAL_CATEGORY_ID", $params['cookie_value'], time()-1, "/", $cookie_domain); //unset
                        $this->_redirect('/tasks/index/type/'.$params['type']);
                    }
                    break;
                default :
                    echo "Invalid Action";
                    die;
                    break;
            }
        }else{
            echo "Invalid Action";
            die;
        }
    }

}

