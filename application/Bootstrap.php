<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initAutoload()
	{
		$sessionConfig = new Zend_Config_Ini('../application/configs/session.ini', 'development');
		Zend_Session::setOptions($sessionConfig->toArray());
		Zend_Session::start();
	}
	
	protected function _initModulesAutoload()
	{
	    $autoloader = new Zend_Application_Module_Autoloader(array(
	        'namespace' => 'Wiki',
	        'basePath' => APPLICATION_PATH.'/modules/wiki'
	    ));
	    return $autoloader;
	}
	
}

