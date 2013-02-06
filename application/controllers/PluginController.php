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
    
    function phpinfoAction()
    {
        phpinfo();
        die;
    }
    
    function testCurlAction()
    {
        // 初始化一个 cURL 对象
        $curl = curl_init();

        // 设置你需要抓取的URL
        curl_setopt($curl, CURLOPT_URL, 'http://www.smartbuyglasses.com');

        // 设置header
        curl_setopt($curl, CURLOPT_HEADER, 1);

        // 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        // 运行cURL，请求网页
        $data = curl_exec($curl);

        // 关闭URL请求
        curl_close($curl);

        // 显示获得的数据
        var_dump($data);
    }
}
