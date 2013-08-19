<?php
/**
 * Description of Content
 *
 * @package Wiki_Model_DbTable
 * @author Ron
 */

/**
 * 创建此类的目的在于简化插入和更新数据的操作
 * 
 * <p>通常插入与更新操作都要写的大量的语句给字段赋值,而且有时字段多时难以记忆,
 * 
 * 继承此类的Model可创建与表字段名相对应的带有$columnMarking前缀protected属性,例如:protected $__name;然后只需为相
 * 
 * 应的属性赋值,如:$this->$__name='ron';,一般IDE在此时都会有代码提示,由此减轻了记忆的负担,最后再调用change()或create()
 * 
 * 方法即可</p>
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
                    $this->$propertieName=NULL;
                }
            }
        }
    }
    
    /**
     * 当为私有属性赋值时,该值将会被保存在$data属性中
     * @param string $propertieName
     * @param mix $value
     */
    public function __set($propertieName,$value){
        $prefix = substr($propertieName, 0, strlen($this->columnMarking));
        if ($prefix == $this->columnMarking) {
                $columnName = substr($propertieName, strlen($this->columnMarking));
                $this->data[$columnName] = $value;
        }
    }
    /**
     * 将保存在$data中的数据插入到数据库
     * @return mix The primary key of the row inserted.
     * @throws ErrorException 当$data为空抛出异常
     */
    public function create() {
        if(count($this->data)<=0) $this->_getData();
        if(count($this->data)<=0) throw new ErrorException('argument $data is empty');
        $result = $this->insert($this->data);
        unset($this->data);
        return $result;
    }
    /**
     * 将保存在$data中的数据更新到数据库中
     * @param string $where 条件
     * @return int The number of rows updated.
     * @throws ErrorException 当$data为空抛出异常
     */
    public function change($where) {
        if(count($this->data)<=0) $this->_getData();
        if(count($this->data)<=0) throw new ErrorException('argument $data is empty');
        $result = $this->update($this->data,$where);
        unset($this->data);
        return $result;
    }
}
?>
