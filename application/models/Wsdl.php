<?php
class Wsdl
{
	function WsdlServer()
	{
		/*
		 * $data_array(
		 * 				"request_type" =>
		 * 				"params" => array()
		 * 				)
		*/
		function S1($data_array)
		{
			$data_result = $this->ServiceGate($data_array);
	
			return $data_result;
		}
		
		$params_model = new Params();
		
		if(!$params_model->GetVal("wsdl_cache_enabled"))
		{
			ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache
		}
		
		$server = new SoapServer($params_model->GetVal("wsdl_file_url"));
	
		$server->addFunction("S1");
	
		$server->handle();
	
		die;
	
	}
	
	function ServiceGate($data_array)
	{
		switch ($data_array['request_type'])
		{
			case "DetectIdentity":
				$users_model = new Users();
				$result = $users_model->DetectIdentity($data_array['params']['username'], $data_array['params']['password']);
				break;
		}
		
		return $result;
	}
}

