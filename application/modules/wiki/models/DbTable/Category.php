<?php

class Wiki_Model_DbTable_Category extends Wiki_Model_DbTable_Abstract
{
	protected $_db;
    protected $_name = 'wiki_category';
	protected $_primary = 'id';
	protected $_sequence = true;
    protected $__parent_id;
    protected $__cname;
    protected $__status;
    protected $_referenceMap = array(  
        'CategoryRef' => array(  
            'columns'       => 'id',  
            'refTableClass' => 'Wiki_Model_DbTable_Category',  
            'refColumns'    => 'parent_id'  
        )  
    );
	
	function __construct(){
	   $this->_db = Zend_Registry::get("db");
    }
	
	function _init(){

	}

	function getParentCategories(){
		
		$select = $this->_db->select();
		$select->from("wiki_category as ct", array("ct.id as id", "ct.parent_id as parent_id", "ct.cname as cname"));
		$select->order("parent_id ASC");
		$select->where("ct.status = 1");
		$select->where("ct.parent_id = 0");
		$data = $this->_db->fetchAll($select);

		$result = array();
		
        foreach($data as $key => $val)
        {
            $temp = array();
			$temp['category_parent'] = $val['parent_id'];
			$temp['category_id'] = $val['id'];
			$temp['category_name'] = $val['cname'];
			$result[] = $temp;
        }

		return $result;
	}

	function getSubCategories(){
		
		$select = $this->_db->select();
		$select->from("wiki_category as ct", array("ct.id as id", "ct.parent_id as parent_id", "ct.cname as cname"));
		$select->order("parent_id ASC");
		$select->where("ct.status = 1");
		$select->where("ct.parent_id != 0");
		$data = $this->_db->fetchAll($select);

		$result = array();
		
        foreach($data as $key => $val)
        {
            $temp = array();
			$temp['category_parent'] = $val['parent_id'];
			$temp['category_id'] = $val['id'];
			$temp['category_name'] = $val['cname'];
			$result[] = $temp;
        }

		return $result;
	}
	
	public function create($parent_id, $cname, $status){
		$this->__parent_id = $parent_id;
		$this->__cname = $cname;
		$this->__status = $status;
		parent::create();
	}
    
}

