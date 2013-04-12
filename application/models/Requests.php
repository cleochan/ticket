<?php

class Requests extends Zend_Db_Table
{
	protected $_name = 'requests';

	var $request;
	var $lm; //limit
	var $page;
        var $category;
	
	function PushListData()
	{
            $find = $this->select();

            //Step1: just active
            $find->where("status = ?", 1);
            
            //Step2: category
            if($this->category)
            {
                $category_model = new Category();
                $find->where("category IN (?)", $category_model->GetChildren($this->category));
            }

            //Step3: order
            $find->order("status ASC");
            $find->order("id ASC");

            //Step4: limit and offset
            $this->lm = 100;
            $offset = ($this->page - 1) * $this->lm;

            $find->limit($this->lm, $offset);

            //Fetch
            $result = $this->fetchAll($find);

            $users = new Users();
            $category = new Category();

            if(!empty($result))
            {
                foreach($result as $val)
                {
                    $data = array();

                    $data['id'] = $val->id;
                    $data['composer'] = $users->GetRealName($val->composer);
                    $data['title'] = $val->title;
                    $data['priority'] = $this->Priority($val->priority);
                    $data['category'] = $category->GetVal($val->category);
                    $data['status'] = $this->GetStatusStr($val->status);
                    $data['deadline'] = substr($val->dead_line, 0, 10);
                    $data['created_date'] = $val->created_date;
                    $data['closed_date'] = $val->closed_date;

                    $data_group[] = $data;
                }
            }

            return $data_group;
	}
	
	function PushListDataInactive()
	{
            $find = $this->select();

            //Step1: just active
            $find->where("status in (?)", array(2,3));
            
            //Step2: category
            if($this->category)
            {
                $find->where("category = ?", $this->category);
            }

            //Step3: order
            $find->order("status ASC");
            $find->order("id DESC");

            //Step4: limit and offset
            $this->lm = 100;
            $offset = ($this->page - 1) * $this->lm;

            $find->limit($this->lm, $offset);


            //Fetch
            $result = $this->fetchAll($find);

            $users = new Users();
            $category = new Category();

            if(!empty($result))
            {
                foreach($result as $val)
                {
                    $data = array();

                    $data['id'] = $val->id;
                    $data['composer'] = $users->GetRealName($val->composer);
                    $data['title'] = $val->title;
                    $data['priority'] = $this->Priority($val->priority);
                    $data['category'] = $category->GetVal($val->category);
                    $data['status'] = $this->GetStatusStr($val->status);
                    $data['deadline'] = substr($val->dead_line, 0, 10);
                    $data['created_date'] = $val->created_date;
                    $data['closed_date'] = $val->closed_date;

                    $data_group[] = $data;
                }
            }

            return $data_group;
	}
	
	function Priority($num)
	{
		switch($num)
		{
			case 2:
				$str = "<font color='#ff0000'>urgent</font>";
				break;
			default:
				$str = "<font color='#666'>normal</font>";
				break;
		}
		
		return $str;
	}
	
	function GetStatusStr($status_key)
	{
		switch($status_key)
		{
			case 1:
				$str = "Pending";
				break;
			case 2:
				$str = "Closed";
				break;
			case 3:
				$str = "Canceled";
				break;
			default:
				$str = "";
				break;
		}
		
		return $str;
	}
	
	function StatusArray()
	{
		$status[1] = "Pending";
		$status[2] = "Closed";
		$status[3] = "Canceled";
		
		return $status;
	}
	
}



