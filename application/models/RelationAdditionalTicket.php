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
    
    function GetRelatedTicketId($keyword, $category_id=0)
    {
        $requests_additional_type_model = new RequestsAdditionalType();
        $requests_additional_type_id_array = $requests_additional_type_model->GetFormElements($category_id);
        
        $requests_additional_type_id_array_result = array();
        
        if(!empty($requests_additional_type_id_array))
        {
            foreach($requests_additional_type_id_array as $requests_additional_type_id_array_key => $requests_additional_type_id_array_val)
            {
                $requests_additional_type_id_array_result[] = $requests_additional_type_id_array_key;
            }
        }
        
        $select = $this->select();
        $select->where("requests_additional_type_id in (?)", $requests_additional_type_id_array_result);
        $select->where("type_value like ?", "%".$keyword."%");
        
        $rows = $this->fetchAll($select);
        
        $result = array();
        
        if(!empty($rows))
        {
            foreach($rows as $row)
            {
                $result[] = $row['ticket_id'];
            }
        }
        
        return $result;
    }
}