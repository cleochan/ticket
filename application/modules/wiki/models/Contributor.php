<?php

class Wiki_Model_Contributor{
	
	var $page;
	
	function __construct(){
        $this->db = Zend_Registry::get("db");
		define("RECORDS_PER_PAGE", 20);
		define("LATEST_TOPICS", 10);
    }
    
	function getContributors($current_page){
		$row_position = ($current_page-1) * RECORDS_PER_PAGE;
		
		$select = $this->db->select();
		$select->from("wiki_contributors as w", array("uid as userid", "tid as ticketid", "SUM(count) as contribution"));
		$select->joinLeft("users as u", "u.id=w.uid", array("u.realname as name"));
		$select->joinLeft("departments as d", "d.id=u.department", array("d.name as dptname"));
		$select->group("w.uid");
		$select->order("dptname ASC");
		$select->limit(RECORDS_PER_PAGE, $row_position);
		$data = $this->db->fetchAll($select);

		$result = array();
		
        foreach($data as $key => $val)
        {
            $temp = array();
            $temp['contributor_id'] = $val['userid'];
            $temp['department_name'] = $val['dptname'];
            $temp['contributor_name'] = $val['name'];
 			$temp['contributions'] = $val['contribution'];
			$result[] = $temp;
        }

		return $result;
	}
	
	function getContributorByID($id){
		
		$select = $this->db->select();
		$select->from("wiki_contributors as w", array("uid as userid", "tid as ticketid", "SUM(count) as contribution"));
		$select->joinLeft("users as u", "u.id=w.uid", array("u.realname as name"));
		$select->joinLeft("departments as d", "d.id=u.department", array("d.name as dptname"));
		$select->where("w.uid = ?", $id);
		$data = $this->db->fetchAll($select);

		$result = array();
        foreach($data as $key => $val)
        {
            $temp = array();
            $temp['contributor_id'] = $val['userid'];
            $temp['department_name'] = $val['dptname'];
            $temp['contributor_name'] = $val['name'];
 			$temp['contributions'] = $val['contribution'];
			$result[] = $temp;
        }
		return $result;
	}

	function getLatestContributedTopics($id){
		
		$select = $this->db->select();
		$select->from("wiki_contributors as w", array("uid as userid", "tid as ticketid", "SUM(count) as contribution"));
		$select->joinLeft("users as u", "u.id=w.uid", array("u.realname as name"));
		$select->joinLeft("departments as d", "d.id=u.department", array("d.name as dptname"));
		$select->where("w.uid = ?", $id);
		$select->order("dptname ASC");
		$select->limit(LATEST_TOPICS, 0);
		$data = $this->db->fetchAll($select);

		$result = array();
		
        foreach($data as $key => $val)
        {
            $temp = array();
            $temp['contributor_id'] = $val['userid'];
            $temp['department_name'] = $val['dptname'];
            $temp['contributor_name'] = $val['name'];
 			$temp['contributions'] = $val['contribution'];
			$result[] = $temp;
        }

		return $result;
		
		
	}
	
	function getPageCount($contributorcount){
		$pages = 1;
		for($i = ($contributorcount/RECORDS_PER_PAGE); $i>0; $i--)
		{
			$pages++;
		}
		return $pages;
	}
	
}
