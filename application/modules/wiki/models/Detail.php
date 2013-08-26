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
    private $_cache;
    public function __construct() {
        $this->db = Zend_Registry::get('db');
                $frontendOptions = new Zend_Cache_Core(
                array(
                    'caching' => true,
                    'cache_id_prefix' => 'wikiSearch',
                    'logging' => false,
                    'write_control' => true,
                    'automatic_serialization' => true,
                    'ignore_user_abort' => true
                ));

        $backendOptions = new Zend_Cache_Backend_File(array(
            'cache_dir' => sys_get_temp_dir()) // Directory where to put the cache files
        );

        $this->_cache = Zend_Cache::factory($frontendOptions, $backendOptions);
    }
    public function getDetailSelect(array $fields){
        $select = $this->db->select();
        $select->from('wiki_topics AS t',$fields)
                ->joinLeft('users AS u', 'u.id=t.uid')
                ->joinLeft('wiki_category AS c', 'c.id=t.cid')
                ->joinLeft('wiki_contents AS ct', 't.id=ct.tid')
                ->joinLeft('users as u2','u2.id=ct.uid');
        return $select;
    }
    
    /**
     * Get the detail of Topic 
     * @param string $id The Topic Id
     * @return array
     */
    public function getDetail($id) {
        $fields = array(
                           't.title', 
                           't.cid', 
                           't.id', 
                           't.status AS tstatus', 
                           't.create_time AS created_time',
                           't.uid AS creator_uid',
                           'u.realname as creator_name',
                           'c.id',
                           'c.cname',
                           'c.parent_id',
                           'ct.content',
                           'ct.version_id',
                           'ct.create_time AS update_time',
                           'ct.id AS vid',
                           'u2.realname AS update_name'
                           );
        $select = $this->getDetailSelect($fields);
        $select->where('t.id=:id AND ct.is_default=1');
        return $this->db->fetchRow($select, array('id' => $id));
    }
    /**
     * Get the all version detail of Topic
     * @param string $id  The Topic Id
     * @param type $orderBy
     * @param type $sortOrder
     * @return array
     */
    public function getHistoryList($id,$orderBy='ID',$sortOrder='DESC') {
        $fields = array(
                           't.title', 
                           't.cid', 
                           't.id AS tid', 
                           't.status AS tstatus', 
                           't.create_time AS created_time',
                           't.uid AS creator_uid',
                           'u.realname as creator_name',
                           'c.cname',
                           'c.parent_id',
                           'ct.uid AS cuid',
                           'ct.is_default',
                           'ct.create_time AS update_time',
                           'ct.id AS vid',
                           'ct.version_id',
                           'ct.preversion_id',
                           'u2.realname AS update_name'
                           );
        $select = $this->getDetailSelect($fields);
        $select->where('t.id=:id');
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

    public function getDetailWithVersion($id, $vid,$version_id) {
        $fields = array(
                           't.title', 
                           't.cid', 
                           't.id AS tid',
                           't.status AS tstatus', 
                           't.create_time AS created_time',
                           't.uid AS creator_uid',
                           'u.realname as creator_name',
                           'c.cname',
                           'c.parent_id',
                           'ct.content',
                           'ct.uid AS cuid',
                           'ct.is_default',
                           'ct.create_time AS update_time',
                           'ct.id AS vid',
                           'ct.version_id',
                           'ct.preversion_id',
                           'u2.realname AS update_name'
                           );
        $select = $this->getDetailSelect($fields);
        $select->where('t.id=:id');
        if($vid !=NULL){
            $select->where('ct.id = ?', $vid);
        }
        if($version_id != NULL){
            $select->where('ct.version_id',$version_id);
        }
        return $this->db->fetchRow($select, array('id' => $id));
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
    /**
     * 
     * @param type $page
     * @param type $rowCount
     * @param type $orderBy
     * @param type $sortOrder
     * @param array $categoryIds
     * @return type
     */
    public function getTopicsPaging($page,$rowCount,$orderBy='id',$sortOrder='DESC',array $categoryIds=NULL,$keyword=NULL,$contributorId = NULL) {
    	if(isset($categoryIds)){
    		$cid = end($categoryIds);
    	}else{
    		return NULL;
    	}
        $cacheId = md5("wiki_topic_list|{$page}|{$orderBy}|{$sortOrder}|{$cid}|{$keyword}|{$contributorId}|");
        if($keyword!=NULL){
            $session = new Zend_Session_Namespace('wiki');
            $session->last_search_cache_id = $cacheId;
            $session->last_search_keyword = $keyword;
            $session->last_search_cids = $categoryIds;
        }
        if(($data = $this->_cache->load($cacheId)) === FALSE){
            $fields = array(
                           't.title', 
                           't.cid', 
                           't.id AS tid', 
                           't.status AS tstatus', 
                           't.create_time AS created_time',
                           't.uid AS creator_uid',
                           'u.realname as creator_name',
                           'c.id AS cid',
                           'c.cname',
                           'c.parent_id',
                           'ct.content',
                           'ct.create_time AS update_time',
                           'ct.id AS vid',
                           'ct.uid AS updator_uid',
                           'u2.realname AS update_name'
                           );
            $select = $this->getDetailSelect($fields);
            $select->where('ct.is_default=1');
            if(is_array($categoryIds) && count($categoryIds)>0){
                $select->where('cid IN(?)',$categoryIds);
            }
            if($contributorId != NULL){
                $select->where('ct.uid = ?',$contributorId);
            }
            if($keyword!=NULL){
                //$select->where('MATCH(t.title) AGAINST(? IN BOOLEAN MODE) OR MATCH(ct.content) AGAINST(? IN BOOLEAN MODE)', $keyword);
                $select->where('t.title LIKE ? OR ct.content LIKE ?',"%$keyword%");
            }
            $this->setOrder($select, $orderBy, $sortOrder);
            $select->limitPage($page, $rowCount);
            $data = $this->db->fetchAll($select);
            $this->_cache->save($data,$cacheId,array('topic_list_cache'));
            return $data;
        }else{
            return $data;
        }
    }
    public function getCount(array $categoryIds=NULL,$keyword=NULL) {
            $fields = array(new Zend_Db_Expr('COUNT(*) AS total'));
            $select = $this->getDetailSelect($fields);
            $select->where('ct.is_default=1');
            if(is_array($categoryIds) && count($categoryIds)>0){
                $select->where('cid IN(?)',$categoryIds);
            }
            if($keyword!=NULL){
                $select->where('MATCH(t.title) AGAINST(? IN BOOLEAN MODE) OR MATCH(ct.content) AGAINST(? IN BOOLEAN MODE)', $keyword);
            }
        return $this->db->fetchOne($select);
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
            $select->order('update_time '.$sortOrder);
        }
        
    }
}

?>
