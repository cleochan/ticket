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
    protected $_sequence = false;  
    protected $_referenceMap = array(  
        'TopicsRef' => array(  
            'columns'       => 'tid',  
            'refTableClass' => 'Wiki_Model_DbTable_Topics',  
            'refColumns'    => 'id'  
        )  
    );
    /**
     *
     * @var Zend_Db_Adapter_Abstract 
     */
    protected $_db;
    protected $__tid;
    protected $__uid;
    protected $__create_time;
    protected $__content;
    protected $__attachment;
    protected $__is_default;
    protected $__status;
    
    public function init(){
        $this->_db = Zend_Registry::get("db");
    }
    public function SetAsDefault($contentId,$topicId) {
        $this->__is_default = 0;
        $where = $this->_db->quoteInto('is_default = 1 And tid = ?', $topicId);
        $this->change($where);
        $this->__is_default = 1;
        $where = $this->_db->quoteInto('id = ?', $contentId);
        $this->change($where);
    }
    public function Set($contentId) {
        $this->__content = '7777777777777777777777777777';
        $where = $this->_db->quoteInto('id = ?', $contentId);
        $this->change($where);
    }
}
