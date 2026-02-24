<?php
namespace lib;

class Channel {

    static	public function http_post_data($action, $data_string)
    {
        //这里，
        /*$sql= "insert into wolive_tests (content) values ('".json_encode($data)."')";
        $this->pdo->exec($sql);*/
        $token = "5187726681:AAEospvo6fdP8BWpWYTYXamxtDPx6EAVKfM";
        $url = 'https://api.telegram.org/bot' . $token . '' . "/" . $action . "?";
      
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(

                'Content-Type: application/json; charset=utf-8',

                'Content-Length: ' . strlen($data_string))

        );

        ob_start();

        curl_exec($ch);

        $return_content = ob_get_contents();

        //echo $return_content."


        ob_end_clean();

        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // return array($return_code, $return_content);

        return $return_content;

    }

	static public function get($id){
		global $DB;
		$value=$DB->getRow("SELECT * FROM pre_channel WHERE id='$id' LIMIT 1");
		return $value;
	}


    static public function submit5($type, $gid=0,$money=0,$terminals="PC",$stype =0,$userstype=0){
		global $DB;
		if(checkmobile()==true){
			$sqls = " AND (device=0 OR device=2)";
		}else{
			$sqls = " AND (device=0 OR device=1)";
		}
	
		
		$paytype=$DB->getRow("SELECT id,name,status FROM pre_type WHERE name='$type'{$sqls} LIMIT 1");

		if(!$paytype || $paytype['status']==0){
		
		   // sysmsg('支付方式(type)不存在');
		      $return_arr = array("code"=>"201","msg"=>"Payment method (type) does not exist");
    	   	echo json_encode(json_encode($return_arr));
    	   	exit;
		    
		}

		$typeid = $paytype['id'];
		$typename = $paytype['name'];
       
		return self::getSubmitInfo2($typeid, $typename, $gid,$money,$terminals,$stype,$userstype); 
	}
    //获取通道、插件、费率信息
	static public function getSubmitInfo2($typeid, $typename, $gid,$money=0,$terminals,$stype,$userstype){
		global $DB;
		if($gid>0)$groupinfo=$DB->getColumn("SELECT info FROM pre_group WHERE gid='$gid' LIMIT 1");
		if(!$groupinfo)$groupinfo=$DB->getColumn("SELECT info FROM pre_group WHERE gid=0 LIMIT 1");
       
       
       /*指定通道：*/
		if($userstype>0){
		    if($stype>0){

                $rows=$DB->getRow("SELECT * FROM pre_channel WHERE id='$stype'  LIMIT 1");
                if($rows){
                        
                   
                      $channel = $rows['id'];
                      $plugin = $rows['plugin'];
 
                      
                      if(empty($money_rate))$money_rate = $rows['rate'];{
                            return ['typeid'=>$typeid, 'typename'=>$typename, 'plugin'=>$plugin, 'channel'=>$channel, 'rate'=>$money_rate,];
                      }
                   
                } 
            }
		}

		if($groupinfo){
			$info = json_decode($groupinfo,true);
			$groupinfo = $info[$typeid];
		
			if(is_array($groupinfo)){
				$channel = $groupinfo['daifu_channel'];
				$money_rate = $groupinfo['rate'];
				
	    
        		
					
			     
			}	else{
				$channel = -1;
				$money_rate = null;
			}
			
			
		
			
			if($channel==0){ //当前商户关闭该通道
				return false;
			}elseif($channel==-1){ //随机可用通道
				$row=$DB->getRow("SELECT * FROM pre_channel WHERE type='$typeid' AND status=1 AND ccy_no='$ccy_no' ORDER BY rand() LIMIT 1");
				if($row){
					$channel = $row['id'];
					$plugin = $row['plugin'];
					$apptype = $row['apptype'];
					if(empty($money_rate))$money_rate = $row['rate'];
			
        	
					
				}
			}else{
			    
				if($groupinfo['type']=='roll'){ //解析轮询组

					$channel = self::getChannelFromRoll($channel,$money,$terminals,1);
		
				  
			 
					if($channel==0){ //当前轮询组未开启
						return false;
					}
						$row=$DB->getRow("SELECT * FROM pre_channel WHERE id='$channel' LIMIT 1");
						;
    				if($row['status']==1){
    					$plugin = $row['plugin'];
    					$apptype = $row['apptype'];
    					if(empty($money_rate)){
    					    $money_rate = $row['rate'];
    					}
    	
        				$ccy_no = $row['ccy_no'];
        				$bank_code = $row['bank_code'];
    			       
    				
    				}
					
				}
				    $row=$DB->getRow("SELECT * FROM pre_channel WHERE id='$channel' LIMIT 1");
    				if($row['status']==1){
    					$plugin = $row['plugin'];
    					$apptype = $row['apptype'];
    					if(empty($money_rate)){
    					    $money_rate = $row['rate'];
    					}
    				}
		
				
			}
		}else{
			$row=$DB->getRow("SELECT * FROM pre_channel WHERE type='$typeid' AND status=1 AND ccy_no='$ccy_no' ORDER BY rand() LIMIT 1");
			if($row){
				$channel = $row['id'];
				$plugin = $row['plugin'];
				$apptype = $row['apptype'];
				$money_rate = $row['rate'];

			
			}
			
		}
		
		if(!$plugin || !$channel){ //通道已关闭
			return false;
		}
        
       
        
        
		return ['typeid'=>$typeid, 'typename'=>$typename, 'plugin'=>$plugin, 'channel'=>$channel, 'rate'=>$money_rate, 'apptype'=>$apptype];
	}

    // 支付提交处理（输入支付方式名称）
	static public function submit_chang($type, $gid=0,$money=0,$terminals="PC",$stype =0,$userstype=0){
		global $DB;
		if(checkmobile()==true){
			$sqls = " AND (device=0 OR device=2)";
		}else{
			$sqls = " AND (device=0 OR device=1)";
		}
		  
		$paytype=$DB->getRow("SELECT id,name,status FROM pre_type WHERE name='$type'{$sqls} LIMIT 1");
		if(!$paytype || $paytype['status']==0){
		   
		} 
		$typeid = $paytype['id'];
		$typename = $paytype['name'];
   
		return self::getSubmitInfo_change($typeid, $typename, $gid,$money,$terminals,$stype,$userstype); 
	}

    	//获取通道、插件、费率信息
	static public function getSubmitInfo_change($typeid, $typename, $gid,$money=0,$terminals,$stype,$userstype){
		global $DB;
		if($gid>0)$groupinfo=$DB->getColumn("SELECT info FROM pre_group WHERE gid='$gid' LIMIT 1");
		if(!$groupinfo)$groupinfo=$DB->getColumn("SELECT info FROM pre_group WHERE gid=0 LIMIT 1");
		
		
		if($userstype>0){
		    if($stype>0){

                $rows=$DB->getRow("SELECT id,plugin,status,rate,apptype FROM pre_channel WHERE id='$stype'  LIMIT 1");
                if($rows){
                  $channel = $rows['id'];
                  $plugin = $rows['plugin'];
                  $apptype = $rows['apptype'];
                  if(empty($money_rate))$money_rate = $rows['rate'];{
                        return ['typeid'=>$typeid, 'typename'=>$typename, 'plugin'=>$plugin, 'channel'=>$channel, 'rate'=>$money_rate, 'apptype'=>$apptype];
                    }
                } 
            }
		}
		
		
		
		if($groupinfo){
			$info = json_decode($groupinfo,true);
			$groupinfo = $info[$typeid];
			if(is_array($groupinfo)){
				$channel = $groupinfo['channel'];
				$money_rate = $groupinfo['rate'];
			}
			else{
				$channel = -1;
				$money_rate = null;
			}
			if($channel==0){ //当前商户关闭该通道
				return false;
			}
			elseif($channel==-1){ //随机可用通道
				$row=$DB->getRow("SELECT id,plugin,status,rate,apptype FROM pre_channel WHERE type='$typeid' AND status=1 ORDER BY rand() LIMIT 1");
				if($row){
					$channel = $row['id'];
					$plugin = $row['plugin'];
					$apptype = $row['apptype'];
					if(empty($money_rate))$money_rate = $row['rate'];
				}
			}
			else{
				if($groupinfo['type']=='roll'){ //解析轮询组
					$channel = self::getChannelFromRoll($channel,$money,$terminals);
					
				
					if($channel==0){ //当前轮询组未开启
						return false;
					}
				}
				$row=$DB->getRow("SELECT plugin,status,rate,apptype FROM pre_channel WHERE id='$channel' LIMIT 1");
				if($row['status']==1){
					$plugin = $row['plugin'];
					$apptype = $row['apptype'];
					if(empty($money_rate))$money_rate = $row['rate'];
				}
			}
		}else{
			$row=$DB->getRow("SELECT id,plugin,status,rate,apptype FROM pre_channel WHERE type='$typeid' AND status=1 ORDER BY rand() LIMIT 1");
			if($row){
				$channel = $row['id'];
				$plugin = $row['plugin'];
				$apptype = $row['apptype'];
				$money_rate = $row['rate'];
			}
		}
		if(!$plugin || !$channel){ //通道已关闭
			return false;
		}
		return ['typeid'=>$typeid, 'typename'=>$typename, 'plugin'=>$plugin, 'channel'=>$channel, 'rate'=>$money_rate, 'apptype'=>$apptype];
	}



	// 支付提交处理（输入支付方式名称）
	static public function submit($type, $gid=0,$money=0,$terminals="PC",$stype =0,$userstype=0){
		global $DB;
		if(checkmobile()==true){
			$sqls = " AND (device=0 OR device=2)";
		}else{
			$sqls = " AND (device=0 OR device=1)";
		}
		$paytype=$DB->getRow("SELECT id,name,status FROM pre_type WHERE name='$type'{$sqls} LIMIT 1");
		if(!$paytype || $paytype['status']==0)sysmsg('支付方式(type)不存在');
		$typeid = $paytype['id'];
		$typename = $paytype['name'];

		return self::getSubmitInfo($typeid, $typename, $gid,$money,$terminals,$stype,$userstype); 
	}

    // 支付提交处理2（输入支付方式ID）
	static public function submit2_chang($typeid, $gid=0,$money=0,$terminals="PC",$stype=0,$userstype=0){
		global $DB;
		$paytype=$DB->getRow("SELECT id,name,status FROM pre_type WHERE id='$typeid' LIMIT 1");
		if(!$paytype || $paytype['status']==0){echo '支付方式(type)不存在';exit();};
		$typename = $paytype['name']; 
        
		return self::getSubmitInfo($typeid, $typename, $gid,$money,$terminals,$stype,$userstype);
	}


	// 支付提交处理2（输入支付方式ID）
	static public function submit2($typeid, $gid=0,$money=0,$terminals="PC",$stype=0,$userstype=0){
		global $DB;
		$paytype=$DB->getRow("SELECT id,name,status FROM pre_type WHERE id='$typeid' LIMIT 1");
		if(!$paytype || $paytype['status']==0)sysmsg('支付方式(type)不存在');
		$typename = $paytype['name'];
        
		return self::getSubmitInfo($typeid, $typename, $gid,$money,$terminals,$stype,$userstype);
	}
	//获取通道、插件、费率信息
	static public function getSubmitInfo($typeid, $typename, $gid,$money=0,$terminals,$stype,$userstype){
		global $DB;
		if($gid>0)$groupinfo=$DB->getColumn("SELECT info FROM pre_group WHERE gid='$gid' LIMIT 1");
		if(!$groupinfo)$groupinfo=$DB->getColumn("SELECT info FROM pre_group WHERE gid=0 LIMIT 1");
		
		
		if($userstype>0){
		    if($stype>0){

                $rows=$DB->getRow("SELECT id,plugin,status,rate,apptype FROM pre_channel WHERE id='$stype'  LIMIT 1");
                if($rows){
                  $channel = $rows['id'];
                  $plugin = $rows['plugin'];
                  $apptype = $rows['apptype'];
                  if(empty($money_rate))$money_rate = $rows['rate'];{
                        return ['typeid'=>$typeid, 'typename'=>$typename, 'plugin'=>$plugin, 'channel'=>$channel, 'rate'=>$money_rate, 'apptype'=>$apptype];
                    }
                } 
            }
		}
		
		
		
		if($groupinfo){
			$info = json_decode($groupinfo,true);
			$groupinfo = $info[$typeid];
			if(is_array($groupinfo)){
				$channel = $groupinfo['channel'];
				$money_rate = $groupinfo['rate'];
			}
			else{
				$channel = -1;
				$money_rate = null;
			}
			if($channel==0){ //当前商户关闭该通道
				return false;
			}
			elseif($channel==-1){ //随机可用通道
				$row=$DB->getRow("SELECT id,plugin,status,rate,apptype FROM pre_channel WHERE type='$typeid' AND status=1 ORDER BY rand() LIMIT 1");
				if($row){
					$channel = $row['id'];
					$plugin = $row['plugin'];
					$apptype = $row['apptype'];
					if(empty($money_rate))$money_rate = $row['rate'];
				}
			}
			else{
				if($groupinfo['type']=='roll'){ //解析轮询组
					$channel = self::getChannelFromRoll($channel,$money,$terminals);
				
				
					if($channel==0){ //当前轮询组未开启
						return false;
					}
				}
				$row=$DB->getRow("SELECT plugin,status,rate,apptype FROM pre_channel WHERE id='$channel' LIMIT 1");
				if($row['status']==1){
					$plugin = $row['plugin'];
					$apptype = $row['apptype'];
					if(empty($money_rate))$money_rate = $row['rate'];
				}
			}
		}else{
			$row=$DB->getRow("SELECT id,plugin,status,rate,apptype FROM pre_channel WHERE type='$typeid' AND status=1 ORDER BY rand() LIMIT 1");
			if($row){
				$channel = $row['id'];
				$plugin = $row['plugin'];
				$apptype = $row['apptype'];
				$money_rate = $row['rate'];
			}
		}
		if(!$plugin || !$channel){ //通道已关闭
			return false;
		}
		return ['typeid'=>$typeid, 'typename'=>$typename, 'plugin'=>$plugin, 'channel'=>$channel, 'rate'=>$money_rate, 'apptype'=>$apptype];
	}

	// 获取当前商户可用支付方式
	static public function getTypes($gid=0){
		global $DB;
		if(checkmobile()==true){
			$sqls = " AND (device=0 OR device=2)";
		}else{
			$sqls = " AND (device=0 OR device=1)";
		}
		$rows = $DB->getAll("SELECT * FROM pre_type WHERE status=1{$sqls}");
		$paytype = [];
		foreach($rows as $row){
			$paytype[$row['id']] = $row;
		}
		if($gid>0)$groupinfo=$DB->getColumn("SELECT info FROM pre_group WHERE gid='$gid' LIMIT 1");
		if(!$groupinfo)$groupinfo=$DB->getColumn("SELECT info FROM pre_group WHERE gid=0 LIMIT 1");
		if($groupinfo){
			$info = json_decode($groupinfo,true);
			foreach($info as $id=>$row){
				if(!isset($paytype[$id]))continue;
				if($row['channel']==0){
					unset($paytype[$id]);
				}elseif($row['channel']==-1){
					$status=$DB->getColumn("SELECT status FROM pre_channel WHERE type='$id' AND status=1 LIMIT 1");
					if(!$status || $status==0){
						unset($paytype[$id]);
					}elseif(empty($row['rate'])){
						$paytype[$id]['rate']=$DB->getColumn("SELECT rate FROM pre_channel WHERE type='$id' AND status=1 LIMIT 1");
					}else{
						$paytype[$id]['rate']=$row['rate'];
					}
				}else{
					if($row['type']=='roll'){
						$status=$DB->getColumn("SELECT status FROM pre_roll WHERE id='{$row['channel']}' LIMIT 1");
					}else{
						$status=$DB->getColumn("SELECT status FROM pre_channel WHERE id='{$row['channel']}' LIMIT 1");
					}
					if(!$status || $status==0)unset($paytype[$id]);
					else $paytype[$id]['rate']=$row['rate'];
				}
			}
		}else{
			foreach($paytype as $id=>$row){
				$status=$DB->getColumn("SELECT status FROM pre_channel WHERE type='$id' AND status=1 limit 1");
				if(!$status || $status==0)unset($paytype[$id]);
				else{
					$paytype[$id]['rate']=$DB->getColumn("SELECT rate FROM pre_channel WHERE type='$id' AND status=1 limit 1");
				}
			}
		}
		return $paytype;
	}

	//根据轮询组ID获取支付通道ID
	static private function getChannelFromRoll($channel,$money=0,$terminals){
	   // var_dump($money);
	   //  echo "<br/>";
		global $DB;
		$row=$DB->getRow("SELECT * FROM pre_roll WHERE id='$channel' LIMIT 1");

		
		if($row['status']==1){
		    
		    $info_result = array();
            if (!empty($row['prices'])) {
                $arr_price = explode('|', $row['prices']);
               
                //查询金额应该匹配到那个数据：
                foreach ($arr_price as $rows) {
                    $a = explode(':', $rows);
                    $as = explode(',', $a[1]);
                    for ($i = 0; $i < count($as); $i++) {
                        if(strpos($as[$i],'#') !== false){ 
                              $ass = explode('#', $as[$i]);
                              if($money>=$ass[0] && $money<=$ass[1]){
                                  $info_result[] = $a[0]; 
                              }
                        }else{
                            if ($as[$i] == $money) {
                                $info_result[] = $a[0];
                            }
                        }
                    }
                }
               
            }
            if(count($info_result)<=0){
                 sysmsg('没有该金额的通道');
            }
            
            $info_terminals_result = array();
            if (!empty($row['terminals'])) {
                $arr_price_terminals = explode('|', $row['terminals']);

                //查询金额应该匹配到那个数据：
                foreach ($arr_price_terminals as $rows) {
                    $a = explode(':', $rows);
                    $as = explode(',', $a[1]);
                
                  
                    $info_terminals_result[$a[0]] = $as;
                   
                }
            }
            
            
            //终端类型：PC，IOS ，安卓
            $all_channel_count = 0;
            $info_terminals_result_end =array();
            foreach ($info_terminals_result as $key=>$value){
               
                if($terminals == "PC"){
                    $info_terminals_result_end[$key] = array("name"=>$key,"weight"=>$value[0]);
                }elseif($terminals == "Android"){
                    $info_terminals_result_end[$key] = array("name"=>$key,"weight"=>$value[1]);
                }else{
                    //IOS
                    $info_terminals_result_end[$key] = array("name"=>$key,"weight"=>$value[2]);
                }       
            }
            
             
         	
            $all_channel = count($info_result);
            //if($all_channel>1){
                foreach ($info_terminals_result_end as $key=>$value){
                    if (!in_array($key, $info_result)) {
                         unset($info_terminals_result_end[$key]);
                    }
                }
            //}
               
	        $channels = self::random_weight($info_terminals_result_end);
           
            if(!$channels){
                if($terminals == "PC"){
                  sysmsg('请用安卓或者IOS提交订单');
                }elseif($terminals == "Android"){
                     sysmsg('请用PC或IOS提交订单');
                }else{
                    //IOS
                   sysmsg('请用PC或安卓提交订单');
                }
                
            }
             
            $info = self::rollinfo_decode($row['info'], true);
            //var_dump($info);
		  
            foreach ($info as $k => $v) {
                if($v['name'] != $channels){
                     unset($info[$k]);
                }
            }
            
            if (count($info_result) > 0) {
                foreach ($info as $k => $v) {
                    if (!in_array($v['name'], $info_result)) {
                        unset($info[$k]);
                    }
                }
            }else{
                sysmsg('暂时没有通道可用，请联系客服充值!');
            }
	        
       
		    
         
			if($row['kind']==1){
				$channel = self::random_weight($info);
					   
			}else{
				$channel = $info[$row['index']]['name'];
			
				$index = ($row['index'] + 1) % count($info);
				$DB->exec("UPDATE pre_roll SET `index`='$index' WHERE id='{$row['id']}'");
			}
		    if(!$channel){
		       /* pc  安卓  ios 
                1    0     0      暂不支持手机端提交，请用PC提交订单
                1    0     1      暂不支持android提交，请用PC或iphone提交订单
                1    1     0      暂不支持iphone提交，请用PC或android提交订单   
                0    0     1      暂不支持PC和android提交，请用iphone提交订单
                0    1     0      暂不支持PC和iphone提交，请用android提交订单
                0    1     1      暂不支持PC提交，请用手机端提交*/
		    }
			return $channel;
		}
		return false;
	}

	//解析轮询组info
	static private function rollinfo_decode($content){
		$result = [];
		$arr = explode(',',$content);
		foreach($arr as $row){
			$a = explode(':',$row);
			$result[] = ['name'=>$a[0], 'weight'=>$a[1]];
		}
		return $result;
	}

	//加权随机
	static private function random_weight($arr){
		$weightSum = 0;
		foreach ($arr as $value) {
			$weightSum += $value['weight'];
		}
		if($weightSum<=0)return false;
		$randNum = rand(1, $weightSum);
		foreach ($arr as $k => $v) {
			if ($randNum <= $v['weight']) {
				return $v['name'];
			}
			$randNum -=$v['weight'];
		}
	}
}
