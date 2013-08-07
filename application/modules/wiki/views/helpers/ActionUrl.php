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
     * @param string $controllerName
     * @param array  $params
     * @return string the url of action
     */
    public function ActionUrl($actionName,array $params=NULL,$controllerName=NULL,$moduleName='wiki') {
        $urlOptions = array('action'=>$actionName);
        if($controllerName!=NULL) $urlOptions['controller']=$controllerName;
        if($moduleName!=NULL) $urlOptions['module']=$moduleName;
        if($params!=NULL) $urlOptions = array_merge($urlOptions,$params);
        return $this->url($urlOptions, NULL, TRUE);
    }
}

?>
