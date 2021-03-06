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
            'columns' => 'id',
            'refTableClass' => 'Wiki_Model_DbTable_Category',
            'refColumns' => 'parent_id'
        )
    );

    /**
     *
     * @var Zend_Cache_Core 
     */
    private $_cache;

    public function __construct()
    {
        $this->_db = Zend_Registry::get("db");
        $frontendOptions = new Zend_Cache_Core(
                array(
            'caching' => true,
            'cache_id_prefix' => 'wikiSearch',
            'logging' => false,
            'write_control' => true,
            'automatic_serialization' => true,
            'ignore_user_abort' => true
        ));

        $backendOptions = new Zend_Cache_Backend_File(array(
            'cache_dir' => sys_get_temp_dir()) // Directory where to put the cache files
        );

        $this->_cache = Zend_Cache::factory($frontendOptions, $backendOptions);
    }

    public function getSelectOptions($parentId, $defaultOption, $limiter = '- ', &$count = 0, &$return = null, &$data = null)
    {
        if (!isset($data)) {
            $data = $this->fetchAll()->toArray();
        }
        if (!isset($count)) {
            $count = 0;
        }
        if (is_string($defaultOption) && strlen($defaultOption) > 0 && !isset($return[''])) {
            $return[''] = $defaultOption;
        }
        $result = $this->getChildenById($data, $parentId);
        if ($result != NULL && count($result) > 0) {
            foreach ($result as $row) {
                $key = $row['id'];
                $return[$key] = str_repeat($limiter, $count) . ' ' . $row['cname'];
                $count++;
                $this->getSelectOptions($row['id'], $defaultOption, $limiter, $count, $return, $data);
                $count--;
            }
        }
        return $return;
    }

    public function getAllInArray()
    {
        return $this->select()->from($this->_name, array('id', 'parent_id', 'cname AS name'))->query()->fetchAll();
    }

    public function getChildrenIds($parentId, &$return = null, &$data = null)
    {
        if (!isset($return)) {
            $return = array();
        }
        if (!isset($data)) {
            $data = $this->fetchAll()->toArray();
        }
		if($parentID == '*'){
			return $this->fetchAll()->toArray();
		}
        $result = $this->getChildenById($data, $parentId);
        if ($result != NULL && count($result) > 0) {
            foreach ($result as $row) {
                $return[] = $row['id'];
                $this->getChildrenIds($row['id'], $return, $data);
            }
        }
        return $return;
    }

    private function getChildenById(array $array, $parentId)
    {
        $result = array();
        foreach ($array as $key => $value) {
            if ($value['parent_id'] == $parentId) {
                $result[] = $value;
            }
        }
        return $result;
    }

    private function getParentById(array $array, $parentId)
    {
        foreach ($array as $key => $value) {
            if ($value['id'] == $parentId) {
                return $value;
            }
        }
        return NULL;
    }

    public function getParentsRows($parentId, &$return = null, &$data = null)
    {
        if (!isset($return)) {
            $return = array();
        }
        if (!isset($data)) {
            $data = $this->fetchAll()->toArray();
        }
        $result = $this->getParentById($data, $parentId);
        if ($result != NULL && count($result) > 0) {
            $return[] = $result;
            $this->getParentsRows($result['parent_id'], $return, $data);
        }
        return $return;
    }

    public function getCategoryPath($cid, $cname, $parentId, $uri = "/wiki/topic/index")
    {
        $parents = $this->getParentsRows($parentId);
        $returnStr = '';
        $limiter = ' &gt; ';
        if ($parents != NULL && is_array($parents) && count($parents) > 0) {
            foreach ($parents as $key => $value) {
                $returnStr = "<a href='{$uri}/cid/{$value['id']}'>{$value['cname']}</a>{$limiter}{$returnStr}";
            }
            $returnStr .= "<a href='{$uri}/cid/{$cid}'>{$cname}</a>";
        } else {
            $returnStr = "<a href='{$uri}/cid/{$cid}'>{$cname}</a>";
        }
        //$this->_cache->save($returnStr,$cacheId,array('topic_list_cache'));
        return $returnStr;
    }

    public function getParents($parentId, &$return)
    {
        if ($parentId != NULL) {
            $select = $this->select();
            $select->from($this->_name, array('id AS cid', 'cname', 'parent_id'))
                    ->where('id=?', $parentId);
            $row = $this->fetchRow($select);
            if ($row != NULL) {
                $row = $row->toArray();
                $return[] = $row;
                $this->getParents($row['parent_id'], $return);
            }
            return $return;
        } else {
            return NULL;
        }
    }

    function getCategories()
    {

        $select = $this->_db->select();
        $select->from("wiki_category as ct", array("ct.id as id", "ct.parent_id as parent_id", "ct.cname as cname", "ct.status as status"));
        $select->order("cname ASC");
        $select->where("ct.status = 1");

        $data = $this->_db->fetchAll($select);

        $tree = array();
        $result = array();
        foreach ($data as $key => $val) {
            $temp = array();
            $temp['category_parent'] = $val['parent_id'];
            $temp['category_id'] = $val['id'];
            $temp['category_name'] = $val['cname'];
            $temp['category_status'] = $val['status'];
            $tree[] = $temp;
        }
        $hiddenValues = $this->getHiddenFields($tree);
        $result = $this->buildCatTree($tree);
        $outputString = $this->buildHtmlFromCategoryTree($result) . $hiddenValues;
        return $outputString;
    }

    function buildCatTree(&$tree, $root = 0)
    {
        $result = array();
        foreach ($tree as $parent) {
            if ($parent['category_parent'] == $root) {
                $children = $this->buildCatTree($tree, $parent['category_id']);
                if ($children) {
                    $parent['children'] = $children;
                }
                $result[$parent['category_id']] = $parent;
            }
        }
        return $result;
    }

    function buildHtmlFromCategoryTree($array)
    {
        $output = array();
        $out = "";
        foreach ($array as $item) {
            if ($item['category_parent'] == 0) {
                $out .= '<h3><a class="categoryLink" value="' . $item['category_id'] . '">' . $item['category_name'] . "</a></h3>"
                        . '<input type="hidden" class="' . $item['category_id'] . '" value="' . $item['category_name'] . '" />'
                        . '<input type="hidden" class="' . $item['category_id'] . '" value="' . $item['category_status'] . '" />'
                        . '<input type="hidden" class="' . $item['category_id'] . '" value="' . $item['category_parent'] . '" />'
                        . '<input type="hidden" class="' . $item['category_id'] . '" value="' . $item['category_id'] . '" />';
                $out .= "<ul>";
                foreach ($item as $key => $val) {
                    if (is_array($val)) {
                        $out .= $this->getChildren($val);
                    }
                }
                $out .= "</ul>";
            }
        }
        return $out;
    }

    function getHiddenFields($tree)
    {
        $output = "";
        foreach ($tree as $key => $val) {
            $output .= '<input type="hidden" class="' . $val['category_id'] . '" value="' . $val['category_name'] . '" />';
            $output .= '<input type="hidden" class="' . $val['category_id'] . '" value="' . $val['category_status'] . '" />';
            $output .= '<input type="hidden" class="' . $val['category_id'] . '" value="' . $val['category_parent'] . '" />';
            $output .= '<input type="hidden" class="' . $val['category_id'] . '" value="' . $val['category_id'] . '" />';
        }
        return $output;
    }

    function getChildren($val, $level = "")
    {
        $output = "";
        $hiddenValues = "";
        foreach ($val as $child => $value) {
            //	var_dump($value);
            if (is_array($value)) {
                $output .= $this->getChildren($value, $level);
            } else {
                if ($child == "category_id") {
                    $output .= '<li><a class="categoryLink" value="' . $value . '">';
                }
                if ($child == "category_name") {
                    $level = $level . "&nbsp;&nbsp;&nbsp;&nbsp;";
                    $output .= $level . $value . "</a></li>";
                }
            }
        }
        return $output . $hiddenValues;
    }

    public function hasNoChildren($id)
    {
        $data = $this->fetchAll()->toArray();
        $iterator = new RecursiveArrayIterator($data);
        while ($iterator->valid()) {
            if ($iterator->hasChildren()) {
                foreach ($iterator->getChildren() as $key => $val) {
                    if ($key === 'parent_id') {
                        if ($val == $id) {
                            return false;
                        }
                    }
                }
            }
            $iterator->next();
        }
        return true;
    }

    public function create($parent_id, $cname, $status)
    {
        if($parent_id == NULL){
            $this->__parent_id = 0;
        }  else {
            $this->__parent_id = $parent_id;
        }
        
        $this->__cname = $cname;
        $this->__status = $status;
        parent::create();
        if (isset($this->_cache)) {
            $this->_cache->clean('all', array('topic_list_cache'));
        }
    }

    public function edit($id, $parent_id, $cname, $status)
    {

        $this->__cname = $cname;
        $this->__parent_id = $parent_id;
        $this->__status = $status;
        $where = array();
        $where = $this->_db->quoteInto("id =  ?", $id);

        parent::change($where);
        if (isset($this->_cache)) {
            $this->_cache->clean('all', array('topic_list_cache'));
        }
    }

    public function delete($id)
    {
        $where = array();
        $where = $this->_db->quoteInto("id =  ?", $id);
        $this->_db->delete($this->_name, $where);
        if (isset($this->_cache)) {
            $this->_cache->clean('all', array('topic_list_cache'));
        }
    }

    public function rename($id, $newName)
    {
        $this->__cname = $newName;
        $where = $this->_db->quoteInto("id =  ?", $id);
        parent::change($where);
        if (isset($this->_cache)) {
            $this->_cache->clean('all', array('topic_list_cache'));
        }
    }

    public function changeParentId($id, $newParentId)
    {
        $this->__parent_id = $newParentId;
        $where = $this->_db->quoteInto("id =  ?", $id);
        parent::change($where);
        if (isset($this->_cache)) {
            $this->_cache->clean('all', array('topic_list_cache'));
        }
    }

}

