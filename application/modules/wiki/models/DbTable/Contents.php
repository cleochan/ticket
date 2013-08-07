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
    protected $__preversion_id;
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
    public function Revert($contentId,$uid) {
        $select = $this->select();
        $select->from($this->_name, array('tid', 'uid', 'create_time','content','is_default',`status`,'preversion_id'))
                ->where('id=?',$contentId);
        $row = $this->fetchRow();
        $this->__uid = $uid;
        $this->__tid = $row->tid;
        $this->__status = $row->status;
        $this->__preversion_id = $contentId;
        $this->__create_time = date('Y-m-d H:i:s');
        $this->__content = $row->content;
        $this->__is_default = 0;
        $this->create();
        $insertId = $this->_db->lastInsertId();
        $this->SetAsDefault($insertId, $row->tid);
        return $insertId;
    }
    public function Clear($contentId) {
        $this->__content = '7777777777777777777777777777';
        $where = $this->_db->quoteInto('id = ?', $contentId);
        $this->change($where);
    }
}
