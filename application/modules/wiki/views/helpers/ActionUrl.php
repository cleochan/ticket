<?php

/**
 * Order to get Url more simplely
 *
 * @author Ron
 */
class Wiki_View_Helper_ActionUrl extends Zend_View_Helper_Url {

    /**
     * Order to get Url more simplely
     * @param string $actionName
     * @param array  $params
     * @param string $controllerName
     * @return string The url of action
     */
    public function ActionUrl($actionName=NuLL, array $params = NULL, $controllerName = NULL, $moduleName = NULL) {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $urlOptions = array('action' => $actionName);
        $moduleName != NULL ? $urlOptions['module'] = $moduleName : $urlOptions['module'] = $request->getModuleName();
        $controllerName != NULL ? $urlOptions['controller'] = $controllerName : $urlOptions['controller'] = $request->getControllerName();
        $actionName != NULL ? $urlOptions['action'] =$actionName: $urlOptions['action'] = $request->getActionName(); 
        if ($params != NULL) $urlOptions = array_merge($urlOptions, $params);
        return $this->url($urlOptions, 'default', TRUE);
    }

}

?>
