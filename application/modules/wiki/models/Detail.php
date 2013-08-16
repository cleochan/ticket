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
     * Get the detail of Topic 
     * @param string $id The Topic Id
     * @return array
     */
    public function getDetail($id) {

        $select = $this->db->select();
        $select->from('wiki_topics AS t', array('title', 'cid', 'id', 'status', 'create_time','uid AS creator_uid'))
                ->joinLeft('users AS u', 'u.id=t.uid', array('realname as creator_name'))
                ->joinLeft('wiki_category AS c', 'c.id=t.cid', array('cname','parent_id'))
                ->joinLeft('wiki_contents AS ct', 't.id=ct.tid AND u.id=ct.uid', array('content','create_time AS update_time', 'u.realname AS update_name','ct.id AS vid'))
                ->where('t.id=:id AND ct.is_default=1');
        return $this->db->fetchRow($select, array('id' => $id));
    }
    /**
     * Get the all version detail of Topic
     * @param string $id  The Topic Id
     * @param type $orderBy
     * @param type $sortOrder
     * @return array
     */
    public function getDetails($id,$orderBy='ID',$sortOrder='DESC') {

        $select = $this->db->select();
        $select->from('wiki_topics AS t', array('title', 'cid', 'id', 'status', 'create_time'))
                ->joinLeft('users AS u', 'u.id=t.uid', array('realname as creator_name'))
                ->joinLeft('wiki_category AS c', 'c.id=t.cid', array('cname'))
                ->joinLeft('wiki_contents AS ct', 't.id=ct.tid AND u.id=ct.uid', array('uid','is_default','create_time AS update_time', 'u.realname as update_name','id AS version_id','preversion_id'))
                ->where('t.id=:id');
        $this->setOrder($select, $orderBy, $sortOrder);
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

    public function getDetailWithVersion($id, $vid) {
        $select = $this->db->select();
        $select->from('wiki_topics AS t', array('title', 'cid', 'id', 'status', 'create_time','uid AS creator_uid'))
                ->joinLeft('users AS u', 'u.id=t.uid', array('realname as creator_name'))
                ->joinLeft('wiki_category AS c', 'c.id=t.cid', array('cname','parent_id'))
                ->joinLeft('wiki_contents AS ct', 't.id=ct.tid AND u.id=ct.uid', array('content','is_default','create_time AS update_time', 'u.realname as update_name','id AS version_id'))
                ->where('t.id=:id AND ct.id=:vid');
        return $this->db->fetchRow($select, array('id' => $id,'vid'=>$vid));
    }
    
    public function getParentVersionId($tid,$id){
        $select = $this->db->select();
        $select->from('wiki_contents AS ct', array('preversion_id'))
               ->where('ct.id=:id AND ct.tid=:tid');
        return $this->db->fetchOne($select, array('id' => $id,'tid'=>$tid));
    }
    
    public function getChildrenVersionIds($tid,$id){
        $select = $this->db->select();
        $select->from('wiki_contents AS ct', array('id'))
               ->where('ct.preversion_id=:id AND ct.tid=:tid');
        return $this->db->fetchAll($select, array('id' => $id,'tid'=>$tid));
    }
    public function getPreviousVersionId($tid,$id){
        $select = $this->db->select();
        $select->from('wiki_contents AS ct', array('id'))
               ->where('ct.id<:id AND ct.tid=:tid')
               ->order('id DESC')
               ->limit('1');
        return $this->db->fetchOne($select, array('id' => $id,'tid'=>$tid));
    }
    
    public function getNextVersionId($tid,$id){
        $select = $this->db->select();
        $select->from('wiki_contents AS ct', array('id'))
               ->where('ct.id>:id AND ct.tid=:tid')
               ->limit('1');
        return $this->db->fetchOne($select, array('id' => $id,'tid'=>$tid));
    }
    public function getTopics() {
        $select = $this->db->select();
        $select->from('wiki_topics AS t', array('title', 'cid', 'id', 'status', 'create_time'))
                ->joinLeft('users AS u', 'u.id=t.uid', array('t.uid AS creator_uid','realname as creator_name'))
                ->joinLeft('wiki_category AS c', 'c.id=t.cid', array('cname'))
                ->joinLeft('wiki_contents AS ct', 't.id=ct.tid AND u.id=ct.uid', array('ct.uid AS updator_uid','create_time AS update_time', 'u.realname as update_name'))
                ->where('ct.is_default=1')
                ->order('t.id DESC');
        return $this->db->fetchAll($select);
    }
    /**
     * 
     * @param int $page
     * @param int $rowCount
     * @param string $orderBy
     * @param string $sortOrder
     * @return array
     */
    public function getTopicsPaging($page,$rowCount,$orderBy='ID',$sortOrder='DESC') {
        $select = $this->db->select();
        $select->from('wiki_topics AS t', array('title', 'cid', 'id', 'status', 'create_time'))
                ->joinLeft('users AS u', 'u.id=t.uid', array('t.uid AS creator_uid','realname as creator_name'))
                ->joinLeft('wiki_category AS c', 'c.id=t.cid', array('cname'))
                ->joinLeft('wiki_contents AS ct', 't.id=ct.tid AND u.id=ct.uid', array('ct.uid AS updator_uid','create_time AS update_time', 'u.realname as update_name'))
                ->where('ct.is_default=1');
        $this->setOrder($select, $orderBy, $sortOrder);
        $select->limitPage($page, $rowCount);
        return $this->db->fetchAll($select);
    }
    
    /**
     * 
     * @param Zend_Db_Select $select
     * @param string $orderBy
     * @param string $sortOrder
     */
    private function setOrder(&$select,$orderBy,$sortOrder){
        if($sortOrder != 'ASC' && $sortOrder != 'DESC'){
            $sortOrder = 'DESC';
        }
        if($sortOrder==='ASC' || $sortOrder==='DESC'){
            switch ($orderBy) {
                case 'id':
                    $select->order('t.id '.$sortOrder);
                    break;
                case 'topic':
                    $select->order('t.title '.$sortOrder);
                    break;
                case 'category':
                    $select->order('cname '.$sortOrder);
                    break;
                case 'creator':
                    $select->order('creator_name '.$sortOrder);
                    break;
                case 'create_time':
                    $select->order('create_time '.$sortOrder);
                    break;
                case 'update_time':
                    $select->order('update_time '.$sortOrder);
                    break;
                case 'update_name':
                    $select->order('update_name '.$sortOrder);
                    break;
                case 'version_id':
                    $select->order('version_id '.$sortOrder);
                    break;
                case 'preversion_id':
                    $select->order('preversion_id '.$sortOrder);
                    break;
                default:
                    $select->order('update_time '.$sortOrder);
                    break;
            }
        }else{
            $select->order('t.id '.$sortOrder);
        }
        
    }
}

?>
