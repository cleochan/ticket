<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Content
 *
 * @author Ron
 */
class Wiki_Models_DbTable_Contents extends Wiki_Models_DbTable_Abstract{
    protected $_name = 'wiki_contents';
    /**
     *
     * @var Zend_Db_Adapter_Abstract 
     */
    protected $_db;
    public $__tid;
    public $__uid;
    public $__create_time;
    public $__content;
    public $__attachment;
    public $__is_default;
    public $__status;
    
    public function init(){
        $this->_db = Zend_Registry::get("db");
    }
    public function SetAsDefault($contentId,$topicId) {
        $this->__is_default = 0;
        $where = $this->_db->quoteInto('is_default = 0 And tid = ?', $topicId);
        $this->change($where);
        $this->__is_default = 1;
        $where = $this->_db->quoteInto('is_default = 1 And id = ?', $contentId);
        $this->change($where);
    }
    
}
