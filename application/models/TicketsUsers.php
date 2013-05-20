<?php

class TicketsUsers extends Zend_Db_Table
{
	protected $_name = 'tickets_users';
	
    function UpdateStatus($uid, $action)
    {
        $a = $this->fetchRow("id = '".$uid."'");
        $a->status = $action;
        $b = $a->save();
        
        $kpi_tickets = new KpiTickets();
        $kpi_tickets ->StatusTrigger($uid, $action);
        
        return $b;
    }
    
    function GetUserArray($ticket_id)
    {
        $a = $this->fetchAll("ticket_id = '".$ticket_id."' and (del='0' or del='' or del is NULL)");
        
        $c = array();
        
        if(count($a))
        {
            foreach($a as $b)
            {
                $c[] = $b['user_id'];
            }
        }
        
        $c = array_unique($c);
        
        return $c;
    }
    
    function MoveUp($id)
    {
        $original = $this->fetchRow("id = '".$id."'");
        
        if($original['user_id'] && $original['sequence'])
        {
            $next = $this->select();
            $next -> where("user_id = ?", $original['user_id']);
            $next -> where("sequence < ?", $original['sequence']);
            $next -> order("sequence DESC");
            $next -> limit(1);
            
            $next_value = $this->fetchRow($next);
            
            if($next_value['sequence'])
            {
                $new_sequence = $next_value['sequence'] - 1;
            }else{
                $new_sequence = $original['sequence'] - 1;
            }
            
            $original -> sequence = $new_sequence;
                
            if($original -> save())
            {
                $result = 1;
            }else
            {
                $result = 0;
            }
        }else
        {
            $result = 0;
        }
        
        return $result;
    }
    
    function MoveDown($id)
    {
        $original = $this->fetchRow("id = '".$id."'");
        
        if($original['user_id'] && $original['sequence'])
        {
            $next = $this->select();
            $next -> where("user_id = ?", $original['user_id']);
            $next -> where("sequence > ?", $original['sequence']);
            $next -> order("sequence DESC");
            $next -> limit(1);
            
            $next_value = $this->fetchRow($next);
            
            if($next_value['sequence'])
            {
                $new_sequence = $next_value['sequence'] + 1;
            }else{
                $new_sequence = $original['sequence'] + 1;
            }
            
            $original -> sequence = $new_sequence;
                
            if($original -> save())
            {
                $result = 1;
            }else
            {
                $result = 0;
            }
        }else
        {
            $result = 0;
        }
        
        return $result;
    }
	
	function MakeFocus($task_id)
	{
		$current_id = $_SESSION["Zend_Auth"]["storage"]->id."@".$_SESSION["Zend_Auth"]["storage"]->username;
		
		$ticket = $this -> fetchRow('id = "'.$task_id.'"');
		
		if(strpos($ticket->make_focus, $current_id) || "0" == strval(strpos($ticket->make_focus, $current_id))) //remove
		{
			$result = 0;
			$focus = explode("|", $ticket->make_focus);
			foreach($focus as $key => $val)
			{
				if($val == $current_id)
				{
					unset($focus[$key]);
				}
			}
			$focus_str = implode("|", $focus);
		}else //insert
		{
			$result = 1;
			if($ticket->make_focus)
			{
				$str = explode("|", $ticket->make_focus);
				{
					$str[] = $current_id;
					$focus_str = implode("|", $str);
				}
			}else
			{
				$focus_str = $current_id;
			}
		}
		
		$ticket -> make_focus = $focus_str;
		$ticket -> save();
				
		return $result;
	}
}



