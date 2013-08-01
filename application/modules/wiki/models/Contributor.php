<?php

class Wiki_Model_Contributor{
	
	var $page;
	
	function __construct(){
        $this->db = Zend_Registry::get("db");
		define("RECORDS_PER_PAGE", 20);
		define("LATEST_TOPICS", 10);
    }
    
	function getContributors($current_page, $sortBy="dptname", $order="ASC"){
		
		$row_position = ($current_page-1) * RECORDS_PER_PAGE;
		$select = $this->db->select();
		$select->from("wiki_contributors as w", array("uid as userid", "tid as ticketid", "SUM(count) as contribution"));
		$select->joinLeft("users as u", "u.id=w.uid", array("u.realname as name"));
		$select->joinLeft("departments as d", "d.id=u.department", array("d.name as dptname"));
		$select->group("w.uid");
		if($sortBy!=""){
			echo $order;	
			$select->order($sortBy . " " . $order);
		}
		else {
			echo "no sortby";
			$select->order("dptname ASC");
		}
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

	/**
	 * Get All Contributed Topics By ID Function
	 * 
	 * Queries the database to get topics the user has contributed to. Specifically gets date created and topic title.
	 * 
	 * @param integer $id - User ID
	 * 
	 * @return array $result
	 * @author Jonathan Coupe
	 */
	function getAllContributedTopicsByID($id, $current_page){
		
		$row_position = ($current_page-1) * RECORDS_PER_PAGE;
		
		$select = $this->db->select();
		$select->from("wiki_topics as t", array("title", "id as topicid", "uid as userid"));
		$select->joinLeft("wiki_comments as c", "t.id=c.tid", array("create_time as datecreated"));
		$select->where("t.uid = ?", $id);
		$select->order("datecreated DESC");
		$select->limit(RECORDS_PER_PAGE, $row_position);
		$data = $this->db->fetchAll($select);

		$result = array();
		
        foreach($data as $key => $val)
        {
            $temp = array();
            $temp['topic_title'] = $val['title'];
            $temp['topic_id'] = $val['topicid'];
			$temp['user_id'] = $val['userid'];
            $temp['date_created'] = $val['datecreated'];
			$result[] = $temp;
        }

		return $result;
	}
	
	/**
	 * Get Limited Contributed Topics By ID Function
	 * 
	 * Queries the database to get topics the user has contributed to. Specifically gets date created and topic title.
	 * Limited to 10 topics, can be changed in the LATEST_TOPICS definition.
	 * 
	 * @param integer $id - User ID
	 * 
	 * @return array $result
	 * @author Jonathan Coupe
	 */	
		function getLimitedContributedTopicsByID($id){
		
		$select = $this->db->select();
		$select->from("wiki_topics as t", array("title", "id as topicid", "uid as userid"));
		$select->joinLeft("wiki_comments as c", "t.id=c.tid", array("create_time as datecreated"));
		$select->where("t.uid = ?", $id);
		$select->order("datecreated DESC");
		$select->limit(LATEST_TOPICS, 0);
		$data = $this->db->fetchAll($select);
		
		$result = array();
		
        foreach($data as $key => $val)
        {
            $temp = array();
            $temp['topic_title'] = $val['title'];
            $temp['topic_id'] = $val['topicid'];
			$temp['user_id'] = $val['userid'];
            $temp['date_created'] = $val['datecreated'];
			$result[] = $temp;
        }

		return $result;
	}
	
	function getPageCount($dbName, $id=0, $idName="", $searchCol=""){
		
		$getPages = $this->db->select();
		$getPages->from( $dbName." as t", array("COUNT(".$idName.") as count"));
		if((!$id==0)&&(!$idName==="")){
			$getPages->where("t.".$searchCol." = ?", $id);
		}
		$data = $this->db->fetchAll($getPages);
		foreach($data as $key => $val)
        {
		$result = $val['count'];
		}
		$pagesFound = 0;
		for($i = ($result / RECORDS_PER_PAGE); $i>0; $i--)
		{
			$pagesFound++;
		}

		return $pagesFound;
	}
	
	function sortByColumn($columnName){
		
	}
	
}
