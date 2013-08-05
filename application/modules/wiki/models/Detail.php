<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Detail
 *
 * @author Ron
 */
class Wiki_Model_Detail {

    /**
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $db;

    function __construct() {
        $this->db = Zend_Registry::get('db');
    }

    /**
     * 
     * @param string $id
     * @return array
     */
    public function getDetail($id) {

        $select = $this->db->select();
        $select->from('wiki_topics AS t', array('title', 'cid', 'id', 'status', 'create_time','uid AS creator_uid'))
                ->joinLeft('users AS u', 'u.id=t.uid', array('realname as creator_name'))
                ->joinLeft('wiki_category AS c', 'c.id=t.cid', array('cname'))
                ->joinLeft('wiki_contents AS ct', 't.id=ct.tid AND u.id=ct.uid', array('content','create_time AS update_time', 'u.realname as update_name'))
                ->where('t.id=:id AND ct.is_default=1');
        return $this->db->fetchRow($select, array('id' => $id));
    }
    /**
     * 
     * @param string $id
     * @return array
     */
    public function getDetails($id) {

        $select = $this->db->select();
        $select->from('wiki_topics AS t', array('title', 'cid', 'id', 'status', 'create_time'))
                ->joinLeft('users AS u', 'u.id=t.uid', array('realname as creator_name'))
                ->joinLeft('wiki_category AS c', 'c.id=t.cid', array('cname'))
                ->joinLeft('wiki_contents AS ct', 't.id=ct.tid AND u.id=ct.uid', array('uid', 'create_time AS update_time', 'u.realname as update_name'))
                ->where('t.id=:id')
                ->order('update_time DESC');
        return $this->db->fetchAll($select, array('id' => $id));
    }
    
    public function deleteTopic($uid,$tid){
        
        $where = $this->db->quoteInto('id=? AND ',$tid).$this->db->quoteInto('uid=?',$uid);
        $result = $this->db->delete('wiki_topics', $where);
        if($result>0){
            $where = $this->db->quoteInto('tid=?', $tid);
            $resultc = $this->db->delete('wiki_contents', $where);
            if($resultc>0) {
                return TRUE;
            }
        }
        return FALSE;
    }

}

?>
