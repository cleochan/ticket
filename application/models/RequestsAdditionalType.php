<?php

class RequestsAdditionalType extends Zend_Db_Table
{
	protected $_name = 'requests_additional_type';
    
        function DumpAllActive()
        {
            $rows = $this->fetchAll("type_status=1");
            
            return $rows->toArray();
        }
	
        function GetFormElements($requests_category_id)
        {
            $rows = $this->fetchAll("type_status=1 and requests_category_id='".$requests_category_id."'");
            
            $result = array();
            
            foreach($rows as $row)
            {
                $result[$row['requests_additional_type_id']] = array($row['type_required'], $row['type_title']);
            }
            
            return $result;
        }
}



