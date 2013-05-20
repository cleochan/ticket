<?php

class WsdlController extends Zend_Controller_Action
{
	function indexAction()
	{
		$wsdl_model = new Wsdl();
		$wsdl_model->WsdlServer();
		die;
	}
}

