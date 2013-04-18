<?php

class RelationAdditionalTicket extends Zend_Db_Table
{
    protected $_name = 'relation_additional_ticket';

    function DumpData($ticket_id)
    {
        $result = array();

        $rows = $this->fetchAll("ticket_id='".$ticket_id."'");

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