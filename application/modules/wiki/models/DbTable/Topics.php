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

    public function findAllD(){
        $row = $this->find(32)->current();
        $select = $this->select();
        $select->from('users',array('realname'));
        return $row->findDependentRowset('Wiki_Model_DbTable_Contents')->current()->findDependentRowset('Wiki_Model_DbTable_Users','ContentRef',$select)->getRow(0);
    }
    public function getDetail($id){
        $row = $this->find($id)->current();
        $select = $this->select();
        $select->from('users',array('realname'));
        $content = $row->findDependentRowset('Wiki_Model_DbTable_Contents');
    }
}

