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
class Wiki_Model_DbTable_Comments extends Wiki_Model_DbTable_Abstract{
    protected $_name = 'wiki_comments';
    protected $_sequence = false;  
    protected $_referenceMap = array(  
        'UserRef' => array(  
            'columns'       => 'uid',  
            'refTableClass' => 'Wiki_Model_DbTable_Users',  
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
    protected $__status;
    
    public function init(){
        $this->_db = Zend_Registry::get("db");
    }
    
    public function AddComment($topic_id,$user_id,$content){
        $this->__tid = $topic_id;
        $this->__uid = $user_id;
        $this->__content = $content;
        $this->__create_time = date('Y-m-d H:i:s');
        $this->__status = 1;
        $this->create();
    }
    public function GetComments($topic_id){
        $select = $this->_db->select();
        $select->from($this->_name.' AS c', array('uid','tid','content','create_time'))
                ->joinLeft('users AS u', 'c.uid = u.id',array('realname'))
                ->where('c.tid=:tid')
                ->order('c.id ASC');
        return $this->_db->fetchAll($select,array('tid'=>$topic_id));
    }
}
