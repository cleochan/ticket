<?php

class Wiki_Model_Search {

    var $page;

    function __construct() {
        $this->db = Zend_Registry::get("db");
    }

    function _init() {
        
    }

    public function getTableHeaders() {
        return array("topic_title" => "Topic",
            "category_name" => "Category",
            "creator_name" => "Creator",
            "creation_time" => "Create Time",
            "last_updated_by" => "Last Updated By",
            "last_update_time" => "Last Update Time");
    }

    private function _getSearchData($keyword) {

        $select = $this->db->select();
        $select->from("users as u", array("u.realname as name", "u.id as userid"));
        $select->joinLeft("wiki_topics as t", "u.id=t.uid", array("title", "create_time as createtime"));
        $select->joinLeft("wiki_contents as c", "t.id=c.tid", array("c.id as contentid", "create_time as updatetime", "c.content as contents", "c.uid as userid"));
        $select->joinLeft("users as u2", "u2.id=c.uid", array("u2.realname as contributor"));
        $select->joinLeft("wiki_category as ct", "ct.id=t.cid", array("ct.cname as catname"));
        $select->joinLeft("wiki_category as ct2", "ct.parent_id=ct2.id", array("ct2.cname as parent"));
        $select->where("u.id = t.uid");
        $select->where("title like '%" . $keyword . "%' or c.content like '%" . $keyword . "%' ");
        $data = $this->db->fetchAll($select);

        $result = array();
        foreach ($data as $key => $val) {
            $temp = array();
            $temp['topic_title'] = $val['title'];
            $temp['category_name'] = $val['parent'] . " > " . $val['catname'];
            $temp['creator_name'] = $val['name'];
            $temp['creation_time'] = $val['createtime'];
            $temp['last_updated_by'] = $val['contributor'];
            $temp['last_update_time'] = $val['updatetime'];

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