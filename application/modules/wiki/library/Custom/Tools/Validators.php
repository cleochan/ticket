<?php
/**
 * A tool to get validator easily
 *
 * @author Ron.Choi
 * 
 * 2013-5-9 13:52:54
 */
class Custom_Tools_Validators{
    /**
     * To check the record wether exists
     * @param string $tableName
     * @param string $columnField
     * @param string $errorMessage
     * @param bool $isContinue
     * @return array
     */
    public static function Db_NoRecordExists ($tableName,$columnField,$errorMessage='Record already exists',$isContinue=TRUE){
        
        return  array('Db_NoRecordExists', $isContinue, array($tableName, $columnField,'messages'=>array(Zend_Validate_Db_RecordExists::ERROR_RECORD_FOUND=>$errorMessage)));
        
    }
    
    /**
     * 
     * @param string $compareField
     * @param string $errorMessage
     * @param bool $isContinue
     * @return array
     */
    public static function Identical ($compareField,$errorMessage='Not the same',$isContinue=TRUE){
        
        return array('identical',$isContinue,array($compareField,'messages'=>array(Zend_Validate_Identical::NOT_SAME=>$errorMessage)));
        
    }
    
    /**
     * 
     * @param string $errorMessage
     * @param bool $isContinue
     * @return array
     */
    public static function NotEmpty ($errorMessage='Empty fields not allowed',$isContinue=TRUE){
        
        return array('NotEmpty',$isContinue,array('messages'=>array(Zend_Validate_NotEmpty::IS_EMPTY=>$errorMessage)));
        
    }
}

?>
