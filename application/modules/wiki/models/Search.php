<?php

class Wiki_Model_Search {

    var $page;

    function __construct() {
        $this->db = Zend_Registry::get("db");
    }

    function _init() {
        
    }

    public function getTableHeaders() {
        return array("contributor_name" => "Contributor",
            "last_updated" => "Last Updated",
            "creation_time" => "Date Created",
            "topic_title" => "Topic",
            "category_name" => "Category");
    }

    private function _getSearchData($keyword) {

        $select = $this->db->select();
        $select->from("users as u", array("u.realname as name", "u.id as userid"));
        $select->joinLeft("wiki_topics as t", "u.id=t.uid", array("title", "create_time as createtime"));
        $select->joinLeft("wiki_contents as c", "t.id=c.tid", array("c.id as contentid", "create_time as updatetime", "c.content as contents"));
        $select->joinLeft("wiki_category as ct", "ct.id=t.cid", array("ct.cname as catname"));
        $select->joinLeft("wiki_category as ct2", "ct.parent_id=ct2.id", array("ct2.cname as parent"));
        $select->where("u.id = t.uid");
        $select->where("title like '%" . $keyword . "%' or c.content like '%" . $keyword . "%' ");
        $data = $this->db->fetchAll($select);

        $result = array();
        foreach ($data as $key => $val) {
            $temp = array();
            $temp['contributor_name'] = $val['name'];
            $temp['last_updated'] = $val['updatetime'];
            $temp['creation_time'] = $val['createtime'];
            $temp['topic_title'] = $val['title'];
            $temp['category_name'] = $val['parent'] . " > " . $val['catname'];
            //$temp['content_id'] = $val['contentid'];
            //	$temp['user_id'] = $val['userid'];
            $result[] = $temp;
        }

        return $result;
    }

    public function search($keywords) {

        $data = array();
        $data = $this->_getSearchData($keywords);

        /*
          $result = array();
          foreach($data as $row){
          $temp = array();
          foreach ($row as $key => $val) {

          if(is_string($val)){
          $temp[$key] = strip_tags($val);
          }
          }
          $result[] = $temp;
          }
         */
        return $data;
    }

}