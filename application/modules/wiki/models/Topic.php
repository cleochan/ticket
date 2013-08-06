<?php
/**
 * Description of Detail
 *
 * @author Ron
 */
class Wiki_Model_Topic {

    /**
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $db;

    function __construct() {
        $this->db = Zend_Registry::get('db');
    }
    public function getTopics($id) {
        $select = $this->db->select();
        $select->from('wiki_topics AS t', array('title', 'cid', 'id', 'status', 'create_time'))
                ->joinLeft('users AS u', 'u.id=t.uid', array('realname as creator_name'))
                ->joinLeft('wiki_category AS c', 'c.id=t.cid', array('cname'))
                ->joinLeft('wiki_contents AS ct', 't.id=ct.tid AND u.id=ct.uid', array('uid','is_default','create_time AS update_time', 'u.realname as update_name','id AS version_id'))
                ->where('t.id=:id')
                ->order('ct.id DESC');
        return $this->db->fetchAll($select, array('id' => $id));
    }
}

?>
