<?php

class Wiki_Model_DbTable_Topics extends Wiki_Model_DbTable_Abstract
{

    protected $_name = 'wiki_topics';

    protected $__uid;
    protected $__cid;
    protected $__title;
    protected $__create_time;
    protected $__status;
    public function init(){
        $this->_db = Zend_Registry::get('db');
        
    }

    public function CreateTopic($title, $uid, $cid) {
        $this->__uid = $uid;
        $this->__cid = $cid;
        $this->__title = $title;
        $this->__create_time = date('Y-m-d H:i:s');
        $this->__status = 1;
        $this->create();
        return $this->_db->lastInsertId();
    }
}

