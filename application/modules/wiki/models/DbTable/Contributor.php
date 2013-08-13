<?php

/**
 * Description of Contributor
 *
 * @author Ron
 */
class Wiki_Model_DbTable_Contributor extends Wiki_Model_DbTable_Abstract {
    
    protected $_name = 'wiki_contributors';
    
    /**
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db;
    protected $__uid;
    protected $__tid;
    protected $__count;
    
    public function init() {
        parent::init();
        $this->_db = Zend_Registry::get('db');
    }

    public function AddRecord($tid,$uid,$count=1){
        $this->__tid = $tid;
        $this->__uid = $uid;
        $this->__count = $count;
        return $this->create();
    }
    
    /**
     * 
     * @param string $tid
     * @param string $uid
     * @return mix
     */
    public function UpdateRecord($tid,$uid){
       $this->__count = new Zend_Db_Expr('count + 1');
       $where = $this->_db->quoteInto('uid = ?', $uid).$this->_db->quoteInto(' AND tid = ?', $tid);
       if(!$this->change($where)){
            return $this->AddRecord($tid, $uid);
       }
       return NULL;
    }
}

?>
