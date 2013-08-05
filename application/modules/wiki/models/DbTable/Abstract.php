<?php
/**
 * Description of Abstract
 *
 * @author Ron
 */
abstract class Wiki_Model_DbTable_Abstract extends Zend_Db_Table_Abstract {
    protected $columnMarking = '__';
    protected $data;

    /**
     * 
     * @return array A array include the properties which have set value
     */
    protected function _getData(){
        $properties = get_class_vars(get_class($this));
        foreach ($properties as $propertieName => $value) {
            $prefix = substr($propertieName,0,  strlen($this->columnMarking));
            if ($prefix == $this->columnMarking) {
                if(isset($this->$propertieName)){
                    $columnName = substr($propertieName,strlen($this->columnMarking));
                    $this->data[$columnName] = $this->$propertieName;
                    unset($this->$propertieName);
                }
            }
        }
    }
    
    protected function __set($propertieName,$value){
        $prefix = substr($propertieName, 0, strlen($this->columnMarking));
        if ($prefix == $this->columnMarking) {
                $columnName = substr($propertieName, strlen($this->columnMarking));
                $this->data[$columnName] = $value;
        }
    }
    
    public function create() {
        if(count($this->data)<=0) $this->_getData();
        if(count($this->data)<=0) throw new ErrorException('argument $data is empty');
        $result = $this->insert($this->data);
        unset($this->data);
        return $this->getAdapter()->lastInsertId();
    }
    
    public function change($where) {
        if(count($this->data)<=0) $this->_getData();
        if(count($this->data)<=0) throw new ErrorException('argument $data is empty');
        $result = $this->update($this->data,$where);
        unset($this->data);
        return $result;
    }
}
?>
