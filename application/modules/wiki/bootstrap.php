<?php

class Wiki_Bootstrap extends Zend_Application_Module_Bootstrap
{
    public function _initScript(){
        $view = Zend_Layout::getMvcInstance()->getView();
        $view->Scripts = $view->Scripts.<<<EOF
    <!--[if (gte IE 6)&(lte IE 8)]>
    <script type="text/javascript" src="/scripts/selectivizr-min.js"></script>
    <![endif]-->      
EOF;
    }

}
