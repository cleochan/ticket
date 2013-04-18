<?php

class RelationAdditionalRequest extends Zend_Db_Table
{
    protected $_name = 'relation_additional_request';

    function DumpData($request_id)
    {
        $result = array();

        $rows = $this->fetchAll("request_id='".$request_id."'");

        if(!empty($rows))
        {
            foreach($rows as $row)
            {
                $result[$row['requests_additional_type_id']] = $row['type_value'];
            }
        }
        
        return $result;
    }      
}