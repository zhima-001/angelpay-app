<?php

class five
{


    public function __construct()
    {

        include "cron_jiqi.php";
        
        $this->pdo = new PDO("mysql:host=" . $dbHost . ";dbname=" . $dbName, $dbUser, $dbPassword, array(PDO::ATTR_PERSISTENT => true));
        
    }
    public function index(){
        $sql_info = "select * from pay_order  where status='1' and notify='1' and chao_huidiao='0'";
        $order_query2 = $this->pdo->query($sql_info);
        $userbotsettle_info2 = $order_query2->fetchAll();
   
        foreach ($userbotsettle_info2 as $key=>$data){
            $key_sql = "select * from pay_user  WHERE uid='{$data['uid']}' LIMIT 1";
            $user_query = $this->pdo->query($key_sql);
            $user_info = $user_query->fetchAll();
            $key = $user_info['0']['key'];
         	$array=array('pid'=>$data['uid'],'trade_no'=>$data['trade_no'],'out_trade_no'=>$data['out_trade_no'],'type'=>$type,'name'=>$data['name'],'money'=>(float)$data['money'],'trade_status'=>'TRADE_SUCCESS');
            $arg=$this->argSort($this->paraFilter($array));
        	$prestr=$this->createLinkstring($arg);
        	$urlstr=$this->createLinkstringUrlencode($arg);
        	$sign=$this->md5Sign($prestr, $key);
        
        	$url=$data['notify_url'].'&'.$urlstr.'&sign='.$sign.'&sign_type=MD5';
           
          
            $content = $this->curl_get($url);

		
        	if(strpos($content,'success')!==false || strpos($content,'SUCCESS')!==false || strpos($content,'Success')!==false){
        	   
        	    	$this->pdo->exec("UPDATE pay_order SET notify=0 WHERE trade_no='{$data['trade_no']}'");
        	    	echo $data['trade_no']."回调成功！<br>";
        		    //
        	}else{
        	    //修改操作记录：
        	   
        	    $this->pdo->exec("UPDATE pay_order SET huidiao_num=huidiao_num+1 WHERE trade_no='{$data['trade_no']}'");
        	    echo $data['trade_no']."回调不成功！回调次数+1 <br>";
        	    if($data['huidiao_num']>=14){
        	       echo $data['trade_no']."回调不成功！回调不在进行后续回调 <br>";
        	       $this->pdo->exec("UPDATE pay_order SET chao_huidiao=1 WHERE trade_no='{$data['trade_no']}'");
        	    }
        		
        	}
        }
        echo "执行完毕！";
    }
    public function paraFilter($para) {
		$para_filter = array();
		foreach ($para as $key=>$val) {
			if($key == "sign" || $key == "sign_type" || $val == "" || $key == "stype" || $key == "request_method" )continue;
			else $para_filter[$key] = $para[$key];
		}
		return $para_filter;
	}
	public function argSort($para) {
		ksort($para);
		reset($para);
		return $para;
	}
	public function createLinkstring($para) {
		$arg  = "";
		foreach ($para as $key=>$val) {
			$arg.=$key."=".$val."&";
		}
		//去掉最后一个&字符
		$arg = substr($arg,0,-1);

		return $arg;
	}
	public function createLinkstringUrlencode($para) {
		$arg  = "";
		foreach ($para as $key=>$val) {
			$arg.=$key."=".urlencode($val)."&";
		}
		//去掉最后一个&字符
		$arg = substr($arg,0,-1);

		return $arg;
	}
	public function md5Sign($prestr, $key) {
		$prestr = $prestr . $key;
		return md5($prestr);
	}
	public function curl_get($url)
    {
        $ch=curl_init($url);
        $httpheader[] = "Accept: */*";
        $httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
        $httpheader[] = "Connection: close";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Linux; U; Android 4.4.1; zh-cn; R815T Build/JOP40D) AppleWebKit/533.1 (KHTML, like Gecko)Version/4.0 MQQBrowser/4.5 Mobile Safari/533.1');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $content=curl_exec($ch);
        curl_close($ch);
        return($content);
    }
}
$oen = new five();
$oen->index();


?>


