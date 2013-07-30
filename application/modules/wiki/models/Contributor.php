<?php

class Wiki_Model_Contributor{
	
	//var $department;
	//var $name;
	//var $contribution;
	
	function __construct(){
        $this->db = Zend_Registry::get("db");
    }
    
	function getContributors(){
		
		$select = $this->db->select();
		$select->from("wiki_contributors as w", array("uid as userid", "tid as ticketid", "count as contribution"));
		$select->joinLeft("users as u", "u.id=w.uid", array("u.realname as name"));
		$select->joinLeft("departments as d", "d.id=u.department", array("d.name as dptname"));
		$select->order("dptname DESC");
		$data = $this->db->fetchAll($select);

		$result = array();
        
        foreach($data as $key => $val)
        {
            $temp = array();
            
          //  $temp['contributor_id'] = $val['userid'];
            $temp['department_name'] = $val['dptname'];
            $temp['contributor_name'] = $val['name'];
 			$temp['contributions'] = $val['contribution'];
            
            $result[] = $temp;
        }
		return $result;
	}
	
}
