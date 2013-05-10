<?php

class PluginController extends Zend_Controller_Action
{
	
	function init(){
		$this->db = Zend_Registry::get("db");
		
	}
    
    function preDispatch()
	{
		//disable layout for Login page
		$this->_helper->layout->disableLayout();
	}
	
    function indexAction()
    {
		die;
    }
	
	function updateTimeAction()
	{
        $select = $this->db->select();
        $select->from("kpi_tickets", array("id", "suggestion_hour"));
        $data = $this->db->fetchAll($select);
        
        $kpi_tickets = new KpiTickets();
        
        foreach($data as $d)
        {
            if(3 != count(explode(":", $d['suggestion_hour'])))
            {
                $result = $kpi_tickets->fetchRow("id='".$d['id']."'");
                
                if($d['suggestion_hour'] < 10)
                {
                    $result->suggestion_hour = "0".$d['suggestion_hour'].":00:00";
                }else{
                    $result->suggestion_hour = $d['suggestion_hour'].":00:00";
                }
                $result->save();
            }
        }
        die;
    }
    
    function kpiAction()
    {
        $a = "2012-12-25 10:25:30";
        
        echo date("w", mktime(substr($a, 11, 2), substr($a, 14, 2), substr($a, 17, 2), substr($a, 5, 2), substr($a, 8, 2), substr($a, 0, 4)));
        
        die;
    }
    
    function testXmlAction()
    {
        $res = array(  
                'hello' => '11212',  
                'world' => '232323',  
                'array' => array(  
                                    'test' => 'test',  
                                    'b'    => array('c'=>'c', 'd'=>'d')  
                                ),  
                'a' => 'haha'  
        );  
        $xml = new ArrayXml();  
        echo $xml->toXml($res);  
        die;
    }
    
    function rsoAction()
    {
        $a = array("apple", "banana", "pear", "orange");
        rsort($a);
        $a[] = "";
        sort($a);
        print_r($a);die;
    }
    
    function cookieTestAction()
    {
        setcookie("TICKET_INITIAL_CATEGORY_ID", "CCC", time()+(3600*24*365), "/", "demo.local.ticket");
        
        
        //echo $_COOKIE['TICKET_INITIAL_CATEGORY_ID'];
        
        die;
    }
    
    function uniAction()
    {
        $a = new Category();
        print_r($a->GetChildren(7));
        die;
    }
    
    function mergeTestAction()
    {
        $a = array(1,2,3);
        $b = array(2,3);
        
        $c = array_merge($a, $b);
        
        print_r($c);die;
    }
    
    function t3Action()
    {
    	echo "t3";die;
    }
    
    function testWsdlClientAction()
    {
    	ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache
    	
    	$client = new SoapClient("http://t1.ciaomark.com/wsdl/ticket.wsdl");
    
    	print_r($client->S1(2));
    
    	die;
    }
    
    function testWsdlServerAction()
    {
    	function S1($num)
    	{
    		if(1==$num)
    		{
    			$result = array("a","b","c");
    		}elseif(2==$num)
    		{
    			$result = array("d","e","f");
    		}
    
    		return $result;
    	}
    
    	ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache
    
    	$server = new SoapServer("http://t1.ciaomark.com/wsdl/ticket.wsdl");
    
    	$server->addFunction("S1");
    
    	$server->handle();
    
    	die;
    
    }
