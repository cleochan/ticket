<?php

class Wiki_Model_Contributor{
	
	var $page;
	
	function __construct(){
        $this->db = Zend_Registry::get("db");
		defined('RECORDS_PER_PAGE') || define("RECORDS_PER_PAGE", 20);
		defined('LATEST_TOPICS') || define("LATEST_TOPICS", 10);
		$this->navHelper = $this->getNavHelper();
    }
	
	function _init(){

	}
	
	public function getTableHeaders(){
		return array("department_name"=>"Department Name",
					 "contributor_name"=>"Contributor",
					 "contributions"=>"Contributions");
	}
    
	function getContributors($current_page, $sortBy="dptname", $order="ASC"){
		
		$row_position = ($current_page-1) * RECORDS_PER_PAGE;
		$select = $this->db->select();
		$select->from("wiki_contributors as w", array("uid as userid", "tid as ticketid", "SUM(count) as contribution"));
		$select->joinLeft("users as u", "u.id=w.uid", array("u.realname as name"));
		$select->joinLeft("departments as d", "d.id=u.department", array("d.name as dptname"));
		$select->group("w.uid");
		if($sortBy!=""){
			$select->order($sortBy . " " . $order);
		}
		$select->limit(RECORDS_PER_PAGE, $row_position);
		$data = $this->db->fetchAll($select);

		$result = array();
		
        foreach($data as $key => $val)
        {
            $temp = array();
           // $temp['contributor_id'] = $val['userid'];
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
           // $temp['contributor_id'] = $val['userid'];
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
	function getAllContributedTopicsByID($id, $current_page, $sortBy="datecreated", $order="ASC"){
		
		$row_position = ($current_page-1) * RECORDS_PER_PAGE;
		
		$select = $this->db->select();
		$select->from("wiki_topics as t", array("title", "id as topicid", "uid as userid"));
		$select->joinLeft("wiki_contents as c", "t.id=c.tid", array("create_time as datecreated"));
		$select->where("t.uid = ?", $id);
		if($sortBy!=""){
			$select->order($sortBy . " " . $order);
		}
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
		$select->joinLeft("wiki_contents as c", "t.id=c.tid", array("create_time as datecreated"));
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
		
		function getRecentUpdates($current_page, $sortBy="datecreated", $order="ASC"){
		
		$row_position = ($current_page-1) * RECORDS_PER_PAGE;
		$select = $this->db->select();
		$select->from("users as u", array("u.realname as name", "u.id as userid"));
		$select->joinLeft("wiki_topics as t", "u.id=t.uid", array("title"));
		$select->joinLeft("wiki_contents as c", "t.id=c.tid", array("c.id as contentid", "create_time as datecreated"));
		$select->joinLeft("wiki_category as ct", "ct.id=t.cid", array("ct.cname as catname"));
		$select->joinLeft("wiki_category as ct2", "ct.parent_id=ct2.id", array("ct2.cname as parent"));
		$select->where("u.id = t.uid");
		if($sortBy!=""){
			$select->order($sortBy . " " . $order);
		}
		$select->limit(RECORDS_PER_PAGE, $row_position);
		$data = $this->db->fetchAll($select);

		$result = array();
		
        foreach($data as $key => $val)
        {
            $temp = array();
            $temp['contributor_name'] = $val['name'];
            $temp['date_created'] = $val['datecreated'];
            $temp['topic_title'] = $val['title'];
 			$temp['category_name'] = $val['catname'];
			$temp['parent_name'] = $val['parent'];
			$temp['content_id'] = $val['contentid'];
			$temp['user_id'] = $val['userid'];
			$result[] = $temp;
        }

		return $result;
	}
	
	/**
	 * Get Page CountFunction
	 * 
	 * Finds the number of navigation pages needed. Uses row per page limit specified by constant RECORDS_PER_PAGE.
	 * 
	 * @param string $tableName - Database Table name, must match exactly
	 * @param integer $id - Numeric value to be found if using a Where clause. No need to specify this if getting all rows.
	 * @param string $idName - Table column to count rows of.
	 * @param string $searchCol - Table column for Where clause. Only specify if limiting page count to specific criteria, such as a particular user ID.
	 * 
	 * @return integer $pagesFound
	 * @author Jonathan Coupe
	 */	
	function getPageCount($tableName, $idName="", $id=0, $searchCol=""){
		
		$getPages = $this->db->select();
		$getPages->from( $tableName." as t", array("COUNT(".$idName.") as count"));
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
	
	function getNavHelper(){
		return new NavHelper();
	}
	
}
