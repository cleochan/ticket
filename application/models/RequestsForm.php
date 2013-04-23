<?php

class RequestsForm extends Zend_Form
{
	public function __construct($options = NULL)
	{
		parent::__construct($options);
		$this->setName('Requests');
		
		$id = new Zend_Form_Element_Hidden('id');
		$id -> setDecorators(array(array('ViewHelper'),));
		
		$dead_line = new Zend_Form_Element_Text('dead_line');
		$dead_line -> setDecorators(array(array('ViewHelper'),));
		
                $category = new Zend_Form_Element_Hidden('category');
		$category -> setDecorators(array(array('ViewHelper'),));
		
        // //create 
        // status options
		$st_array = array(1 => "Pending",
                          2 => "Closed",
                          3 => "Canceled");
        
		$status = new Zend_Form_Element_Select('status');
		$status -> setDecorators(array(array('ViewHelper'),))
				-> addMultiOptions($st_array);
		
		//create priority radio
		$pr_array = array(1=>"normal", 2=>"urgent");
		$priority = new Zend_Form_Element_Radio('priority');
		$priority -> setDecorators(array(array('ViewHelper'),))
				  -> addMultiOptions($pr_array)
				  -> setSeparator(' ')
				  -> setRequired(True)
				  -> setValue(1)
			      -> addValidator('NotEmpty');
		
		$title = new Zend_Form_Element_Text('title');
		$title -> setDecorators(array(array('ViewHelper'),))
			   -> setRequired(True)
			   -> setAttrib('size',100)
			   -> addFilter('StripTags')
		       -> addFilter('StringTrim');
                
		$contents = new Zend_Form_Element_Textarea('contents');
		$contents -> setDecorators(array(array('ViewHelper'),))
		          -> addFilter('StringTrim')
                  -> setAttribs(array('style' => 'width:800px;height:300px;visibility:hidden;'));

		$comments = new Zend_Form_Element_Textarea('comments');
		$comments -> setDecorators(array(array('ViewHelper'),))
		          -> addFilter('StringTrim')
                  -> setAttribs(array('style' => 'width:800px;height:300px;visibility:hidden;'));
		
		//attached files <=20
		//check folder existed
		$folder = date("Ym");
		if(!is_dir("../public/attachment/".$folder))
		{
			mkdir("../public/attachment/".$folder, 0777);
		}
		
		for($n=1;$n<21;$n++)
		{
                    ${"attachment".$n} = new Zend_Form_Element_File("attachment".$n);
		    ${"attachment".$n} -> setDecorators(array(array('File'),))
		    		     	   -> setDestination("../public/attachment/".$folder);
		}
		
		$participants = new Zend_Form_Element_Text('participants');
		$participants -> setDecorators(array(array('ViewHelper'),))
			    -> setAttrib('size',70)
			    -> addFilter('StripTags')
		        -> addFilter('StringTrim');

		$submit = new Zend_Form_Element_Submit('submitx');
		$submit -> setDecorators(array(array('ViewHelper'),));
        
                //initial additional type start
                $requests_additional_type_mode = new RequestsAdditionalType();
                $type_array = $requests_additional_type_mode->DumpAllActive();
                
                if(!empty($type_array))
                {
                    foreach($type_array as $type_array_val)
                    {
                        $param_name = "additional".$type_array_val['requests_additional_type_id'];
                        
                        switch($type_array_val['type_id'])
                        {
                            case 1: //input box
                                ${$param_name} = new Zend_Form_Element_Text($param_name);
                                ${$param_name} -> setDecorators(array(array('ViewHelper'),))
                                                             -> addFilter('StripTags')
                                                             -> addFilter('StringTrim');
                                if($type_array_val['type_required'])
                                {
                                    ${$param_name} -> setRequired(True);
                                }
                                if($type_array_val['type_values'])
                                {
                                    ${$param_name} ->setValue($type_array_val['type_values']);
                                }
                                break;
                            case 2: //drop down
                                ${$param_name} = new Zend_Form_Element_Select($param_name);
                                ${$param_name}  -> setDecorators(array(array('ViewHelper'),));
				
                                if($type_array_val['type_required'])
                                {
                                    ${$param_name} -> setRequired(True);
                                }
                                
                                if($type_array_val['type_values'])
                                {
                                    $values_array_final = array();
                                    
                                    $values_array = explode("|", $type_array_val['type_values']);
                                    foreach($values_array as $values_array_val)
                                    {
                                        $values_array_final[$values_array_val] = $values_array_val;
                                    }
                                    
                                    arsort($values_array_final);
                                    $values_array_final[] = "";
                                    asort($values_array_final);
                                    
                                    ${$param_name}  -> addMultiOptions($values_array_final);
                                }
                                break;
                            case 3: //radio
                                ${$param_name} = new Zend_Form_Element_Radio($param_name);
                                ${$param_name}  -> setDecorators(array(array('ViewHelper'),))
                                                                -> setSeparator(' ');
                                
                                if($type_array_val['type_required'])
                                {
                                    ${$param_name} -> setRequired(True)
                                                                 -> addValidator('NotEmpty');
                                }
                                
                                if($type_array_val['type_values'])
                                {
                                    $values_array_final = array();
                                    
                                    $values_array = explode("|", $type_array_val['type_values']);
                                    foreach($values_array as $values_array_val)
                                    {
                                        $values_array_final[$values_array_val] = $values_array_val;
                                    }
                                    
                                    arsort($values_array_final);
                                    asort($values_array_final);
                                    
                                    ${$param_name}  -> addMultiOptions($values_array_final);
                                }
                                break;
                            default:
                                break;
                        }
                    }
                }
                //initial additional type finished
                
                $add_elements_result = array(
                                                                $id,
                                                                $dead_line,
                                                                $category,
                                                                $priority,
                                                                $title,
                                                                $contents,
                                                                $comments,
                                                                $status,
                                                                $participants,
                                                                $submit
                                                                );
                
                for($n=1;$n<21;$n++)
                {
                    $add_elements_result[] = ${"attachment".$n};
                }
                
                if(!empty($type_array))
                {
                    foreach($type_array as $type_array_val)
                    {
                        $add_elements_result[] = ${"additional".$type_array_val['requests_additional_type_id']};
                    }
                }
        

		$this -> addElements($add_elements_result);
	}
}
