<?php

/**
 * Description of Message
 *
 * @author Ron
 */
class Custom_Message
{
    public static function setMessage($message,$actionName)
    {
        $session = new Zend_Session_Namespace('Wiki_Message');
        $session->message = array(md5($actionName)=>$message);
    }
    
    public static function getMessage()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $key = md5("/{$request->getModuleName()}/{$request->getControllerName()}/{$request->getActionName()}");
        $session = new Zend_Session_Namespace('Wiki_Message');
        $result = $session->message[$key];
        unset($session->message[$key]);
        return $result;
    }
}

?>
