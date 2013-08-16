<?php
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
    /**
     * 
     * @param string $topic_id
     * @param string $user_id
     * @param string $content
     */
    public function AddComment($topic_id,$user_id,$content){
        $this->__tid = $topic_id;
        $this->__uid = $user_id;
        $this->__content = $content;
        $this->__create_time = date('Y-m-d H:i:s');
        $this->__status = 1;
        $this->create();
    }
    /**
     * 
     * @param string $topic_id
     * @return array
     */
    public function GetComments($topic_id,$page,$rowCount){
        $select = $this->_db->select();
        $select->from($this->_name.' AS c', array('uid','tid','content','create_time'))
                ->joinLeft('users AS u', 'c.uid = u.id',array('realname'))
                ->where('c.tid=:tid')
                ->order('c.id ASC')
                ->limit($rowCount, $page);
        return $this->_db->fetchAll($select,array('tid'=>$topic_id));
    }

    public function GetTotal($tid) {
        $select = $this->select();
        $select->from($this->_name, array(new Zend_Db_Expr('COUNT(*) AS total')))
                ->where('tid = ? AND status = 1',array('tid'=>$tid));
        $result = $this->fetchRow($select)->toArray();
        return $result['total'];
    }
}
