<?php
/**
 * Description of Abstract
 *
 * @author Ron
 */
abstract class Wiki_Models_DbTable_Abstract extends Zend_Db_Table_Abstract {
    protected $columnMarking = '__';
    /**
     * 
     * @return array A array include the properties which have set value
     */
    protected function _getData(){
        $properties = get_class_vars(get_class($this));
        $data = array();
        foreach ($properties as $propertieName => $value) {
            $prefix = substr($propertieName,0,  strlen($this->columnMarking));
            if ($prefix == $this->columnMarking) {
                if(isset($this->$propertieName)){
                    $columnName = substr($propertieName,strlen($this->columnMarking));
                    $data[$columnName] = $this->$propertieName;
                    unset($this->$propertieName);
                }
            }
        }
        return $data;
    }
    
    public function create() {
        $data  = $this->_getData();
        if(count($data)<=0) throw new ErrorException('argument $data is empty');
        return $this->insert($data);
    }
    
    public function change($where) {
        $data  = $this->_getData();
        if(count($data)<=0) throw new ErrorException('argument $data is empty');
        return $this->update($data,$where);
    }
}
?>
