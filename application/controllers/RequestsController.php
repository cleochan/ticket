<?php

class RequestsController extends Zend_Controller_Action
{
  
	
    function init()
    {
        $this->db = Zend_Registry::get("db");
        						

    }
	
    function preDispatch()
    {  
            $auth = Zend_Auth::getInstance();
            $users = new Users();
            if(!$auth->hasIdentity() || !$users->IsValid())
            { 
                $this->_redirect('/login/logout?url='.$_SERVER["REQUEST_URI"]);
            }

            //get system title
            $get_title = new Params();
            $this->view->system_title = $get_title -> GetVal("system_title");
            $this->view->system_version = $get_title -> GetVal("system_version");

            //make top menu
            $menu = new Menu();
            $this->view->top_menu = $menu -> GetTopMenu($this->getRequest()->getControllerName());

            //echo "<pre>";
            //print_r($_SESSION);
            //echo "<pre>";
            //die;
    }
	
    function indexAction()
    {
        $this->view->title = "Active Requests";
        $params = $this->_request->getParams();
        
        $this->view->type = $params['type'];
        $menu = new Menu();
        $this->view->menu = $menu ->GetRequestsMenu($params['type']); 
        
        //build category tree
        $category_model = new Category();
        $this->view->category_tree = $category_model->BuildTree();
        
        $requests = new Requests();
        
        //read cookie
        if($_COOKIE['TICKET_INITIAL_CATEGORY_ID'])
        {
            $this->view->category = $_COOKIE['TICKET_INITIAL_CATEGORY_ID'];
            $requests->category = $_COOKIE['TICKET_INITIAL_CATEGORY_ID'];

            //additional_data_title
            $requests_additional_type = new RequestsAdditionalType();
            $requests_additional_type_array = $requests_additional_type->GetFormElements($_COOKIE['TICKET_INITIAL_CATEGORY_ID']);
            
            if(!empty($requests_additional_type_array))
            {
                $addtional_title = array();
                
                foreach($requests_additional_type_array as $requests_additional_type_array_key => $requests_additional_type_array_val)
                {
                    $addtional_title["additional".$requests_additional_type_array_key] = $requests_additional_type_array_val[1];
                }
                
                $this->view->addtional_title = $addtional_title;
            }
        }
        
        if($params['page'])
        {
                $current_page = $params['page'];
        }else
        {
                $current_page = 1;
        }

        $requests -> page = $current_page;

        $list = $requests -> PushListData();
        $this->view->list = $list;

        $this->view->older = $current_page + 1;
        $this->view->newer = $current_page - 1;

        $requests -> page = $this->view->older;
        $list = $requests -> PushListData();
        
        if(count($list))
        {
                $this->view->can_older = 1;
        }

        if($this->view->newer)
        {
                $requests -> page = $this->view->newer;
                $list = $requests -> PushListData();
                if(count($list))
                {
                        $this->view->can_newer = 1;
                }
        }

        $this->view->p_type = $params['type'];
        $this->view->p_page = $params['page'];
    }
	
    function indexInactiveAction()
    {
        $this->view->title = "Inactive Requests";
        $params = $this->_request->getParams();
        
        $this->view->type = $params['type'];
        $menu = new Menu();
        $this->view->menu = $menu ->GetRequestsMenu($params['type']); 
        
        //build category tree
        $category_model = new Category();
        $this->view->category_tree = $category_model->BuildTree();
        
        $requests = new Requests();
        
        //read cookie
        if($_COOKIE['TICKET_INITIAL_CATEGORY_ID'])
        {
            $this->view->category = $_COOKIE['TICKET_INITIAL_CATEGORY_ID'];
            $requests->category = $_COOKIE['TICKET_INITIAL_CATEGORY_ID'];

            //additional_data_title
            $requests_additional_type = new RequestsAdditionalType();
            $requests_additional_type_array = $requests_additional_type->GetFormElements($_COOKIE['TICKET_INITIAL_CATEGORY_ID']);
            
            if(!empty($requests_additional_type_array))
            {
                $addtional_title = array();
                
                foreach($requests_additional_type_array as $requests_additional_type_array_key => $requests_additional_type_array_val)
                {
                    $addtional_title["additional".$requests_additional_type_array_key] = $requests_additional_type_array_val[1];
                }
                
                $this->view->addtional_title = $addtional_title;
            }
        }
        
        if($params['page'])
        {
                $current_page = $params['page'];
        }else
        {
                $current_page = 1;
        }

        $requests -> page = $current_page;

        $list = $requests -> PushListDataInactive();
        $this->view->list = $list;

        $this->view->older = $current_page + 1;
        $this->view->newer = $current_page - 1;

        $requests -> page = $this->view->older;
        $list = $requests -> PushListDataInactive();
        
        if(count($list))
        {
                $this->view->can_older = 1;
        }

        if($this->view->newer)
        {
                $requests -> page = $this->view->newer;
                $list = $requests -> PushListDataInactive();
                if(count($list))
                {
                        $this->view->can_newer = 1;
                }
        }

        $this->view->p_type = $params['type'];
        $this->view->p_page = $params['page'];

    }
    
    function addAction()
    {   
		$params = $this->_request->getParams();
		$this->view->title = "Add Request";
        
		$menu = new Menu();
		$this->view->menu = $menu -> GetRequestsMenu($params['type']);
        
        //create user list
		$users = new Users();
		$this->view->users_array = $users -> GetRealNameString();
		
		$form = new RequestsForm();
		$now = date("Y-m-d H:i:s");
		$form->submitx->setLabel('Create Request');
        
                if($params['category'])
                {
                    $requests_additional_type_model = new RequestsAdditionalType();
                    $this->view->get_form_elements = $requests_additional_type_model->GetFormElements($params['category']);
                    $category_model = new Category();
                    $this->view->category = array($params['category'], $category_model->GetVal($params['category']));
                    $form->category->setValue($params['category']);
                
                }else{
                    echo "Invalid Action";
                    die;
                }
		$this->view->form = $form;

        
		if($this->_request->isPost()){
			//rename attachment
			$random = mt_rand(1000,9999);
			$insert_file = new Filemap();
			
			for($n=1;$n<21;$n++)
			{
				if($form->{"attachment".$n}->getFileName())
				{
					${"attachment".$n} = $form->{"attachment".$n}->getFileName();
					$path = substr(${"attachment".$n},0,28);
					$short_path = substr(${"attachment".$n},21,7);
					${"attachment".$n."_origine"} = substr(${"attachment".$n},28); //remove folder path
					${"attachment".$n."_db"} = time().$random;
					$form->{"attachment".$n}->addFilter('Rename',$path.${"attachment".$n."_db"},1);
					$random += 1;
					
					//get file info
					$get_file_info = $form->{"attachment".$n}->getFileInfo();
					
					//insert to files table
					$insert_file->AddToDb(${"attachment".$n."_origine"}, $short_path.${"attachment".$n."_db"}, $get_file_info["attachment".$n]['type']);
					$att_pool[] = $short_path.${"attachment".$n."_db"};
				}
			}

			$formData = $this->_request->getPost();
			if($form->isValid($formData)){

				$form->getValues();				

				$check_user_string = new Users;
				
                //5, participants if exist
				if(trim($form->getValue('participants')))
				{
					$check_user_result3 = $check_user_string -> MakeString($form->getValue('participants'), 1);
					
					if(is_array($check_user_result3)) //error
					{
						if('error1' == $check_user_result3[0])
						{
							$this->view->notice="System can't find the participant: ".$check_user_result3[1];
							$form->populate($formData);
						}elseif('error2' == $check_user_result3[0])
						{
							$this->view->notice="You didn't choose a valid participant.";
							$form->populate($formData);
						}	
						$error = 1;
					}
				}
                
				if(!$error)
				{
                                        //insert to db
					$tickets = new Requests();
				
					$row = $tickets->createRow();

					if($form->getValue('dead_line'))
					{
						$row->dead_line = $form->getValue('dead_line')." 23:59:59";
					}
					$row->category = $form->getValue('category');
                                        $row->priority = $form->getValue('priority');
					$row->title = $form->getValue('title');
					if($form->getValue('contents'))
					{
						$row->contents = $form->getValue('contents');
					}
					$row->status = 1; //pending
                    
					if($check_user_result3)
					{
						$row->participants = $check_user_result3;
					}
				
					//auto
					$row->created_date = $now;
					$row->composer = $_SESSION["Zend_Auth"]["storage"]->id."@".$_SESSION["Zend_Auth"]["storage"]->username;
					
					if(!empty($att_pool))
					{
						$row->attachment = implode("|", $att_pool);
                                        }
			       
   				    $pt = $row->save();
                                    
                                    //insert into relation_additional_request
                                    if(!empty($this->view->get_form_elements))
                                    {
                                        $relation_table = new RelationAdditionalRequest();
                                        
                                        foreach($this->view->get_form_elements as $get_form_elements_key => $get_form_elements_val)
                                        {
                                            $relation_table_row = $relation_table->createRow();
                                            $relation_table_row->request_id = $pt;
                                            $relation_table_row->requests_additional_type_id = $get_form_elements_key;
                                            $relation_name = "additional".$get_form_elements_key;
                                            $relation_table_row->type_value = $form->getValue($relation_name);
                                            $relation_table_row->save();
                                        }
                                    }
                    
                    //send email
                    $mail = new Mail();
                    $file_map = new Filemap();
                    $mail->url = "requests/view/id/".$pt;
                    $mail->ticket_title = $form->getValue('title');
                    $mail->tx = $mail->FormatUrl($form->getValue('contents'));
                    if($row->attachment)
                    {
                        $mail->attachment = $file_map->MakeDownloadLink($row->attachment);
                    }
                    $mail->mail_contents = $mail->ContentsTemplate(4);
                    $mail->mail_subject = "Request Created: ".$form->getValue('title');
                    
                    if($check_user_result3)
                    {
                        $user_string = $check_user_result3;
                        $user_list_for_send = $check_user_string -> GetUserIdArray($user_string);
                        $mail->to = $check_user_string -> MakeMailUserList($user_list_for_send);
                    }
                    
                    $mail->cc = $check_user_string -> MakeMailUserList(array($_SESSION["Zend_Auth"]["storage"]->id));
                    
                    //send
                    $mail->Send();
                                    
					//redirect, depends on the initial status
					$menu = new Menu();
					$this->_redirect('requests/view/id/'.$pt);
				}
			}else{
				///////////////////////////////////////////////////////////
				//check valid start
				
				//1, project
				if(!$formData['category'])
				{
					$this->view->notice="Category is required.";
					$form->populate($formData);
					$error = 1;
				}
				
				//2, title
				if(!trim($formData['title']))
				{
					$this->view->notice="Title is required.";
					$form->populate($formData);
					$error = 1;
				}
				
				//check valid end
				///////////////////////////////////////////////////////////
				
				if(!$error)
				{
					$this->view->notice="Some information are inValid.";
					$form->populate($formData);
				}
                                $form->setAction($form->getView()->url(array('controller' => 'requests', 'action' => 'add', 'type' => 'add', 'category'=>1)));
			}
		}
    }
    
    function viewAction()
    {
		$params = $this->_request->getParams();

		$this->view->title = "Request #".$params["id"];
		$theid = $params["id"];
        
		$menu = new Menu();
		$this->view->menu = $menu ->GetRequestsMenu($params['type']);
		$this->view->type = $params['type'];
		
		$now = date("Y-m-d H:i:s");
        		
                $form = new RequestsForm();
		$form->submitx->setLabel('Submit');
		$this->view->form = $form;
        
                $request_model = new Requests();
                $category_id = $request_model->GetCategory($theid);
        
                if($category_id)
                {
                    $requests_additional_type_model = new RequestsAdditionalType();
                    $this->view->get_form_elements = $requests_additional_type_model->GetFormElements($category_id);
                    $category_model = new Category();
                    $this->view->category = array($category_id, $category_model->GetVal($category_id));
                    $form->category->setValue($category_id);
                }else{
                    echo "Invalid Action";
                    die;
                }
		
		if($this->_request->isPost()){

            if(!$_SESSION['request_contents'][$theid]['ticket_auth'])
			{
				//change the attribute of the form model
				$form->priority->setRequired(False);
				$form->title->setRequired(False);
				$form->status->setRequired(False);
				$form->category->setRequired(False);
			}
            
			//rename attachment
			$random = mt_rand(1000,9999);
			$insert_file = new Filemap();
			
			for($n=1;$n<21;$n++)
			{
				if($form->{"attachment".$n}->getFileName())
				{
					${"attachment".$n} = $form->{"attachment".$n}->getFileName();
					$path = substr(${"attachment".$n},0,28);
					$short_path = substr(${"attachment".$n},21,7);
					${"attachment".$n."_origine"} = substr(${"attachment".$n},28); //remove folder path
					${"attachment".$n."_db"} = time().$random;
					$form->{"attachment".$n}->addFilter('Rename',$path.${"attachment".$n."_db"},1);
					$random += 1;
					
					//get file info
					$get_file_info = $form->{"attachment".$n}->getFileInfo();
					
					//insert to files table
					$insert_file->AddToDb(${"attachment".$n."_origine"}, $short_path.${"attachment".$n."_db"}, $get_file_info["attachment".$n]['type']);
					$att_pool[] = $short_path.${"attachment".$n."_db"};
				}
			}
			$formData = $this->_request->getPost();
			if($form->isValid($formData)){
				//file uploads
			$post_info = $form->getValues();
   
			//new users
			$check_user_string = new Users;
						
            //5, participant if exist
			if(trim($form->getValue('participants')))
			{
				$check_user_result3 = $check_user_string -> MakeString($form->getValue('participants'), 1);
					
				if(is_array($check_user_result3)) //error
				{
					if('error1' == $check_user_result3[0])
					{
						$this->view->notice="System can't find the participant: ".$check_user_result3[1];

						$form->populate($formData);
					}elseif('error2' == $check_user_result3[0])
					{
						$this->view->notice="You didn't choose a valid participant.";
						$form->populate($formData);
					}	
					$error = 1;
				}
			}
			
			//check valid end
			///////////////////////////////////////////////////////////		
								
				if(!$error)
				{
					//Step1: insert comments if exists
                    
					if($form->getValue('comments') || !empty($att_pool))
					{
						$comments = new RequestsComments();
						$add_comments = $comments->createRow();
					
						$add_comments->request_id = $form->getValue('id');
						$add_comments->contents = $form->getValue('comments');
						$add_comments->composer = $_SESSION["Zend_Auth"]["storage"]->id."@".$_SESSION["Zend_Auth"]["storage"]->username;
						$add_comments->created_date = $now;

						if(!empty($att_pool))
						{
							$add_comments->attachment = implode("|", $att_pool);
						}
					
						$add_comments->save();
					}
					
                    if($_SESSION['request_contents'][$theid]['ticket_auth'])
                    {
                        //Step2: update request
                        $tickets = new Requests();

                        $row = $tickets->fetchRow('id = "'.$form->getValue('id').'"');
                        $composer = $row['composer'];
                        if($form->getValue('dead_line'))
                        {
                            $row->dead_line = $form->getValue('dead_line')." 23:59:59";
                        }
                        $row->category = $form->getValue('category');
                        $row->priority = $form->getValue('priority');
                        $row->title = $form->getValue('title');
                        $row->status = $form->getValue('status');
                        $row->participants = $check_user_result3;

                        if(in_array($row->status, array(2,3)))
                        {
                            $row->closed_by = $_SESSION["Zend_Auth"]["storage"]->id."@".$_SESSION["Zend_Auth"]["storage"]->username;
                            $row->closed_date = $now;
                        }

                        $row->save();
                        
                        //Step3: update additional data
                        if(!empty($this->view->get_form_elements))
                        {
                            $relation_table = new RelationAdditionalRequest();

                            foreach($this->view->get_form_elements as $get_form_elements_key => $get_form_elements_val)
                            {
                                $relation_table_row = $relation_table->fetchRow("request_id='".$form->getValue('id')."' and requests_additional_type_id='".$get_form_elements_key."'");
                                $relation_name = "additional".$get_form_elements_key;
                                $relation_table_row->type_value = $form->getValue($relation_name);
                                $relation_table_row->save();
                            }
                        }
                    }
                    //sending email
                    if($check_user_result3) //participants
                    {
                        $user_string = $composer."|".$check_user_result3;
                    }
                    
                    $user_list_for_send = $check_user_string -> GetUserIdArray($user_string);
                    
                    if(in_array($form->getValue('status'), array(2,3))) //closed/canceled
                    {
                        //send email close request
                        $mail = new Mail();
                        $file_map = new Filemap();
                        $mail->url = "requests/view/id/".$form->getValue('id');
                        $mail->ticket_title = $form->getValue('title');
                        $mail->tx = $mail->FormatUrl($add_comments->contents);
                        $mail->attachment = $file_map->MakeDownloadLink($add_comments->attachment);
                        $mail->mail_contents = $mail->ContentsTemplate(6);
                        $mail->mail_subject = "Request Closed: ".$form->getValue('title');

                        if($user_list_for_send)
                        {
                            $mail->to = $check_user_string -> MakeMailUserList($user_list_for_send);
                        }

                        $mail->cc = $check_user_string -> MakeMailUserList(array($_SESSION["Zend_Auth"]["storage"]->id));


                        //send
                        $mail->Send();
                    }else
                    {
                        if($form->getValue('comments') || !empty($att_pool))
                        {
                            //send email update request
                            $mail = new Mail();
                            $file_map = new Filemap();
                            $mail->url = "requests/view/id/".$form->getValue('id');
                            $mail->ticket_title = $form->getValue('title');
                            $mail->tx = $mail->FormatUrl($add_comments->contents);
                            $mail->attachment = $file_map->MakeDownloadLink($add_comments->attachment);
                            $mail->mail_contents = $mail->ContentsTemplate(5);
                            $mail->mail_subject = "Request Updated: ".$form->getValue('title');

                            if($user_list_for_send)
                            {
                                $mail->to = $check_user_string -> MakeMailUserList($user_list_for_send);
                            }

                            $mail->cc = $check_user_string -> MakeMailUserList(array($_SESSION["Zend_Auth"]["storage"]->id));


                            //send
                            $mail->Send();
                        }
                    }
                    
					//unset session
					$theid = $form->getValue('id');
					unset($_SESSION['request_contents'][$theid]);
					
					//redirect
					$this->_redirect('requests/view/id/'.$form->getValue('id'));
                    
				}
			}else{
                   
					///////////////////////////////////////////////////////////
					//check valid start
				
					//1, project
					if(!$formData['category'])
					{
						$this->view->notice="Category is required.";
						$form->populate($formData);
						$error = 1;
					}
				
					//2, title
					if(!trim($formData['title']))
					{
						$this->view->notice="Title is required.";
						$form->populate($formData);
						$error = 1;
					}
				
					//check valid end
					///////////////////////////////////////////////////////////			
				if(!$error)
				{
					$this->view->notice="Some information are inValid";
					$form->populate($formData);
				}
			}
			
			//push static data
			if($_SESSION['request_contents'][$theid])
			{
				$this->view->ticket = $_SESSION['request_contents'][$theid]['contents'];
				$this->view->attachments = $_SESSION['request_contents'][$theid]['attachments'];
				$this->view->comments_data = $_SESSION['request_contents'][$theid]['comments_data'];
				$this->view->users_array = $_SESSION['request_contents'][$theid]['users_array'];
				$this->view->ticket_auth = $_SESSION['request_contents'][$theid]['ticket_auth'];
			}
		}else
		{
                    $tickets = new Requests();

                    if($params['id'])
                    {
                        $requests_tickets = new RequestsTickets();
                        $this->view->related_tickets = $requests_tickets ->RelatedTickets($params['id']);

                        if($this->view->related_tickets)
                        {
                            $this->view->create_ticket_button_value = "Create another ticket";
                        }else
                        {
                            $this->view->create_ticket_button_value = "Close this request and create ticket";
                        }
                
                        $ticket = $tickets->fetchRow('id="'.$params['id'].'"');
                        //update contents
                        $ticket->dead_line = substr($ticket->dead_line,0,10);
                        $users = new Users();
                        $ticket->participants = $users -> GetNameString($ticket->participants);				
                        $this->view->id = $params['id'];
                        
                        $ticket_array_standby = $ticket->toArray();
                        
                        //get additional type
                        $relation_additional_request_model = new RelationAdditionalRequest();
                        $additional_data = $relation_additional_request_model->DumpData($params['id']);
                        if(!empty($additional_data))
                        {
                            foreach($additional_data as $additional_data_key => $additional_data_val)
                            {
                                $key_name = "additional".$additional_data_key;
                                $ticket_array_standby[$key_name] = $additional_data_val;
                            }
                        }
                        
                        $form->populate($ticket_array_standby);

                        //push static data for read only
                        $ticket_array_standby['status'] = $tickets -> GetStatusStr($ticket_array_standby['status']);
                        $ticket_array_standby['priority'] = $tickets -> Priority($ticket_array_standby['priority']);
                        $ticket_array_standby['skype'] = $users ->GetSkype($ticket_array_standby['composer']);
                        $ticket_array_standby['composer'] = $users -> GetRealName($ticket_array_standby['composer']);
                        $ticket_array_standby['closed_by'] = $users -> GetRealName($ticket_array_standby['closed_by']);

                        $this->view->ticket = $ticket_array_standby;
                        $theid = $params['id'];
                        $_SESSION['request_contents'][$theid]['contents'] = $ticket_array_standby;

                        //attachments for subject
                        $att_string = new Filemap();
                        $this->view->attachments = $att_string -> MakeDownloadLink($ticket_array_standby['attachment']);
                        $_SESSION['request_contents'][$theid]['attachments'] = $this->view->attachments;

                        //push comments
                        $comments = new RequestsComments();
                        $comments_data = $comments -> fetchAll('request_id = "'.$params['id'].'"');
                                        $comments_array=array ();
                        foreach($comments_data as $data_val)
                        {
                            //make object to array
                            $data['request_id'] = $data_val->request_id;
                            $data['contents'] = $data_val->contents;
                            $data['attachment'] = $att_string -> MakeDownloadLink($data_val->attachment);
                            $data['skype'] = $users ->GetSkype($data_val->composer);
                            $data['composer'] = $users -> GetRealName($data_val->composer);
                            $data['created_date'] = $data_val->created_date;					
                            $comments_array[] = $data;
                        }
                        $this->view->comments_data = $comments_array;
                        $_SESSION['request_contents'][$theid]['comments_data'] = $comments_array;

                        //create user list
                        $this->view->users_array = $users -> GetRealNameString();
                        $_SESSION['request_contents'][$theid]['users_array'] = $this->view->users_array;

                        //get ticket auth
                        $this->view->ticket_auth = $users ->GetRequestAuth($params['id']);

                        $_SESSION['request_contents'][$theid]['ticket_auth'] = $this->view->ticket_auth;
                    }
        }
    }
    
    
    function callFileAction()
    {
    	$params = $this->_request->getParams();
    	
		$filemap = new Filemap();
		$data = $filemap ->fetchRow("indb like '%".$params['val']."%'");
		
		if($data['ftype'])
		{
			//print_r('/attachment/'.$data['indb']);die;
			header('Content-type: '.$data['ftype']);
			header('Content-Disposition: attachment; filename="'.$data['origine'].'"');
			readfile('../public/attachment/'.$data['indb']);
		}

		die;
    }
    
}

