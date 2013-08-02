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
   public function getDetail($id){
       
       $select = $this->db->select();
       $select->from('wiki_topics AS t',array('uid AS tuid','title','create_time'))
               ->joinLeft('wiki_contents AS c', 't.id=c.tid',array('c.create_time AS update_time','c.uid AS cuid','content'))
               ->where('t.id=:id AND c.is_default=1');
       return $this->db->fetchAll($select,array('id'=>$id));
   }
}

?>
