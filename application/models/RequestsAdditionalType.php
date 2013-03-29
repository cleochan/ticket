<?php

class RequestsAdditionalType extends Zend_Db_Table
{
	protected $_name = 'requests_additional_type';
    
        function DumpAllActive()
        {
            $rows = $this->fetchAll("type_status=1");
            
            return $rows->toArray();
        }
	
}



