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
class Wiki_Model_DbTable_Contents extends Wiki_Model_DbTable_Abstract{
    protected $_name = 'wiki_contents';
    /**
     *
     * @var Zend_Db_Adapter_Abstract 
     */
    protected $_db;
    private $__tid;
    private $__uid;
    private $__create_time;
    private $__content;
    private $__attachment;
    private $__is_default;
    private $__status;
    
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
