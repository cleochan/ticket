<?php 

class NavHelper {
	
	public function writeURL($params, $column_name, $pageNum){
		$url = "?";
		foreach($params as $key=>$val){
			$url .= $this->paramSwitch($key, $val, $column_name, $pageNum);	
		}
		$url = rtrim($url,'&');
		return $url;
	}
	
	public function paramSwitch($paramname, $param, $column_name, $pageNum){
		switch($paramname){
			case "page":
				return $url = "page=".$pageNum."&";;
			case "sortBy":
				return $url = "sortBy=".$column_name."&";;
			case "sortOrder":
				if($param=="ASC"){
					return $url = "sortOrder=DESC&";
				}else{
					return $url = "sortOrder=ASC&";
				} 
			case "userid":
				return $url = "userid=".$param."&";;
			default:
				break;
		}
	}
	
}

?>