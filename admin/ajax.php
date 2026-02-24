<?php
include("../includes/common.php");
include("../cron_jiqi.php");
if($islogin==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");
$act=isset($_GET['act'])?daddslashes($_GET['act']):null;

if(strpos($_SERVER['HTTP_REFERER'],$_SERVER['HTTP_HOST'])===false)exit('{"code":403}');

 @header('Content-Type: application/json; charset=UTF-8');
 function exportExcel($title=array(), $data=array(), $fileName='', $savePath='./', $isDown=false){
     ob_end_clean();

ob_start();
        //include('/Public/Classes/PHPExcel.php');
       include("../assets/excel/PHPExcel.php");
        $obj = new PHPExcel();

        //横向单元格标识
        $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');
       
        $obj->getActiveSheet(0)->setTitle('sheet名称');   //设置sheet名称
        $_row = 1;   //设置纵向单元格标识
        if($title){
            $_cnt = count($title);
            $obj->getActiveSheet(0)->mergeCells('A'.$_row.':'.$cellName[$_cnt-1].$_row);   //合并单元格
            $obj->setActiveSheetIndex(0)->setCellValue('A'.$_row, '数据导出时间：'.date('Y-m-d H:i:s'));  //设置合并后的单元格内容
            $_row++;
            $i = 0;
            foreach($title as $v){   //设置列标题
                $obj->setActiveSheetIndex(0)->setCellValue($cellName[$i].$_row, $v);
                $i++;
            }
            $_row++;
        }

        //填写数据
        if($data){
            $i = 0;
            foreach($data as $_v){
                $j = 0;
                foreach($_v as $_cell){
                    $obj->getActiveSheet(0)->setCellValue($cellName[$j] . ($i+$_row), $_cell);
                    $j++;
                }
                $i++;
            }
        }
     
        //文件名处理
        if(!$fileName){
            $fileName = uniqid(time(),true);
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        header('Content-Disposition: attachment;filename="'.$fileName.'.xlsx"');
        
        header('Cache-Control: max-age=0');
        
        $objWriter = PHPExcel_IOFactory::createWriter($obj, 'Excel2007');
        
        $objWriter->save('php://output');
        exit;
        
        
        $objWrite = PHPExcel_IOFactory::createWriter($obj, 'Excel2007');

        if($isDown){   //网页下载
            header('pragma:public');
            header("Content-Disposition:attachment;filename=$fileName.xlsx");
            $objWrite->save('php://output');exit;
        }

        $_fileName = iconv("utf-8", "gb2312", $fileName);   //转码
        $_savePath = $savePath.$_fileName.'.xlsx';
        $objWrite->save($_savePath);

        return $savePath.$fileName.'.xlsx';
    }
switch($act){
case 'daochuj':
        ini_set('memory_limit','2048M'); //设置程序运行的内存
		ini_set('max_execution_time',0); //设置程序的执行时间,0为无上限
        $filename = '今日用户跑路成率数据导出--' . date('Y-m-d H:i:s');
        $array = array();
       

        $excel_array = array("商户号",'最近三小时的成率','最近30分钟的成率','今日总成率','今日跑量','昨日总成率','昨日跑量');
        
        $today = date("Y-m-d");
        
        $todayzuori = date("Y-m-d", strtotime("-1 day"));
        
        $sanfenzhong_start_time = strtotime(date("Y-m-d H:i:s",(time()-30*60)));
        $sanfenzhong_end_time = strtotime(date("Y-m-d H:i:s",time()));
        
        $sanxiaoshi_start_time = strtotime(date("Y-m-d H:i:s",(time()-3*60*60)));
        $sanxiaoshi_end_time = strtotime(date("Y-m-d H:i:s",time()));
        
        $jinri_start_time = date("Y-m-d");
        $jinri_end_time = date("Y-m-d", strtotime("+1 day"));
        
        
        $pre_userlist = $DB->getAll("select uid from pay_order where status = '1' and date='" . $today . "' group by uid");
        foreach($pre_userlist as $ks=>$vs){
            $array[$ks]['uid'] = $vs['uid'];
            //查询最近三小时的订单数据：
            $sabxuaisghi = $DB->getAll("select status,money,addtime from pay_order where uid='".$vs['uid']."' and addtime>='" . $jinri_start_time . "' and addtime<='".$jinri_end_time."'");
            
            //查询一下昨日的：
            $zuoriding = $DB->getAll("select status from pay_order where uid='".$vs['uid']."' and date='" . $todayzuori . "'"); 
            
            $zuori_zong =0;
            $zuori_zong_cheng =0;
            $zuori_zong_sum=0;
            if($zuoriding){
                $zuori_zong = count($zuoriding);
                 foreach ($sabxuaisghi as $skss=>$svss){
                    if($svss['status']=='1'){
                        $zuori_zong_cheng +=1;
                        $zuori_zong_sum +=$svss['money'];
                    }
                     
                 }
            }
            
            
         
            if($sabxuaisghi){
                $zong_count = count($sabxuaisghi);
                $zong_count_chenggong = 0;
                $zong_paoliang =0;
                
                $sanxiaoshiding_num =0;
                $sanxiaoshiding_num_cheng =0;
                
                $sanfenzhongding_num =0;
                $sanfenzhongding_num_cheng =0;
                
                foreach ($sabxuaisghi as $sk=>$sv){
                    if($sv['status']=='1'){
                        $zong_count_chenggong +=1;
                        $zong_paoliang +=$sv['money'];
                    }
                    $sijin = strtotime($sv['addtime']);
                     //三小时内
                    if($sanxiaoshi_start_time<=$sijin && $sijin<=$sanxiaoshi_end_time){
                        $sanxiaoshiding_num +=1;
                        if($sv['status']=='1'){
                            $sanxiaoshiding_num_cheng +=1;
                        }
                    }
                    //三十分钟内：
                    if($sanfenzhong_start_time<=$sijin && $sijin<=$sanfenzhong_end_time){
                        $sanfenzhongding_num +=1;
                        if($sv['status']=='1'){
                            $sanfenzhongding_num_cheng +=1;
                        }
                    }
                }
            }
          
            
            if($sanxiaoshiding_num>0){
                $array[$ks]['sanshi'] = (round($sanxiaoshiding_num_cheng/$sanxiaoshiding_num,2)*100)."%";
            }else{
                 $array[$ks]['sanshi'] = "0%";
            }
            if($sanfenzhongding_num>0){
                 $array[$ks]['sanfen'] = (round($sanfenzhongding_num_cheng/$sanfenzhongding_num,2)*100)."%";
            }else{
                 $array[$ks]['sanfen'] = "0%";
            }
        
        
            
           
            $array[$ks]['zong'] = (round($zong_count_chenggong/$zong_count,2)*100)."%";
            $array[$ks]['paoliang'] = $zong_paoliang;
            if($zuori_zong>0){
                 $array[$ks]['zuori'] = (round($zuori_zong_cheng/$zuori_zong,2)*100)."%";
            }else{
                 $array[$ks]['zuori'] = "0%";
            }
            $array[$ks]['zuoripaoliang'] = $zuori_zong_sum;
        }
    

        exportExcel($excel_array, $array, $filename, './', true);
//上传图片：
case  'uploadtupian':
     $uploadDir = '/budanuploads/tousu/'; // 确保服务器上这个路径是存在的并且可写
    $response = ['success' => false, 'url' => '', 'error' => ''];
    $tempName = $_FILES['image']['tmp_name'];
        $fileName = basename($_FILES['image']['name']);
        $targetFilePath = $uploadDir . $fileName; 
        // 检查文件类型,大小等
        if(move_uploaded_file($tempName, $targetFilePath)){
            // 文件上传成功
            $response['success'] = true;
            $response['url'] = $targetFilePath;
        } else {
            $response['error'] = '上传失败';
        }
       var_dump($response) ;
       exit();
        
    $uploadDir = '/budanuploads/tousu/'; // 确保服务器上这个路径是存在的并且可写
    $response = ['success' => false, 'url' => '', 'error' => ''];
    if(isset($_FILES['image'])){ 
        $tempName = $_FILES['image']['tmp_name'];
        $fileName = basename($_FILES['image']['name']);
        $targetFilePath = $uploadDir . $fileName; 
        // 检查文件类型,大小等
        if(move_uploaded_file($tempName, $targetFilePath)){
            // 文件上传成功
            $response['success'] = true;
            $response['url'] = $targetFilePath;
        } else {
            $response['error'] = '上传失败';
        }
    } else {
        $response['error'] = '没有文件被上传';
    }
    // 设置响应的 Content-Type 为 JSON 
    // header('Content-Type: application/json');
    // 发送 JSON 响应
    echo json_encode($response);

case 'daochu':
        ini_set('memory_limit','2048M'); //设置程序运行的内存
		ini_set('max_execution_time',0); //设置程序的执行时间,0为无上限
        $filename = '订单数据导出--' . date('Y-m-d H:i:s');
        $array = array();
        
        //$user_list = M()->query($sql_list);
        /*if(isset($_GET['uid']) && !empty($_GET['uid'])) {
        	$uid = intval($_GET['uid']);
        	$sqls.=" AND A.`uid`='$uid'";
        	$links.='&uid='.$uid;
        }
        if(isset($_GET['type']) && $_GET['type']>0) {
        	$type = intval($_GET['type']);
        	$sqls.=" AND A.`type`='$type'";
        	$links.='&type='.$type;
        }elseif(isset($_GET['channel']) && $_GET['channel']>0) {
        	$channel = intval($_GET['channel']);
        	$sqls.=" AND A.`channel`='$channel'";
        	$links.='&channel='.$channel;
        }
        if(isset($_GET['dstatus']) && $_GET['dstatus']>0) {
        	$dstatus = intval($_GET['dstatus']);
        	$sqls.=" AND A.status={$dstatus}";
        	$links.='&dstatus='.$dstatus;
        }
        if(isset($_GET['value']) && !empty($_GET['value'])) {
        	if($_GET['column']=='name'){
        		$sql=" A.`{$_GET['column']}` like '%{$_GET['value']}%'";
        	}elseif($_GET['column']=='addtime'){
        	 
        		$kws = explode('>',$_GET['value']);
        		$sql=" A.`addtime`>='{$kws[0]}' AND A.`addtime`<='{$kws[1]}'";
        	}else{
        		$sql=" A.`{$_GET['column']}`='{$_GET['value']}'";
        	}
        	$sql.=$sqls;
        	$numrows=$DB->getColumn("SELECT count(*) from pre_order A WHERE{$sql}");
        	$con='包含 '.$_GET['value'].' 的共有 <b>'.$numrows.'</b> 条订单';
        	$link='&column='.$_GET['column'].'&value='.$_GET['value'].$links;
        }else{
        	$sql=" 1";
        	$sql.=$sqls;
        	$numrows=$DB->getColumn("SELECT count(*) from pre_order A WHERE{$sql}");
        	$con='共有 <b>'.$numrows.'</b> 条订单';
        	$link=$links;
        }*/

        $excel_array = array("系统订单号","商户订单号",'订单金额','支付状态','通知状态','创建时间','完成时间','商户分成','支付方式','通道ID','支付插件','终端类型');
       // $user_list = $DB->getAll("SELECT * FROM pre_order");
        
        $sql = $_GET['sql'];
        $user_list=$DB->query("SELECT A.*,B.plugin FROM pre_order A LEFT JOIN pre_channel B ON A.channel=B.id WHERE{$sql} order by trade_no desc");
       
       
        
        $status_arr = array("未支付",'已支付');
        $tongzhi_arr = array("通知失败",'通知完成');
        
        $pay_arr = array();
        $pay_list = $DB->getAll("SELECT * FROM pre_type");
         foreach ($pay_list as $keys => $values) {
             $pay_arr[$values['id']] = $values['showname'];
         }

        foreach ($user_list as $key => $value) {
            $array[$key]['trade_no'] = "'".$value['trade_no'];
            $array[$key]['out_trade_no'] = "'".$value['out_trade_no'];
            $array[$key]['money'] = $value['money'];
            if($value['status'] =='0'){
                $array[$key]['status'] = $status_arr[0];
            }else{
                $array[$key]['status'] =$status_arr[1];
            }
            if($value['notify'] =='1'){
                $array[$key]['tongzhi'] = $tongzhi_arr[0];
            }else{ 
                $array[$key]['tongzhi'] =$tongzhi_arr[1];
            }
            $array[$key]['addtime'] = $value['addtime'];
            $array[$key]['endtime'] = $value['endtime'];
            $array[$key]['getmoney'] = $value['getmoney'];

            $array[$key]['pay'] = $pay_arr[$value['type']];
            $array[$key]['channel'] = $value['channel'];
            $channel =  $value['channel'];
            $srow=$DB->getRow("select * from pre_channel where id='$channel' limit 1");
            
            $array[$key]['channel_name'] = $srow['plugin'];
            $array[$key]['zhongduan'] = $value['terminals'];
        }
       
     
        
        exportExcel($excel_array, $array, $filename, './', true);
case 'getcount3':
	$thtime=date("Y-m-d").' 00:00:00';
	$count1=$DB->getColumn("SELECT count(*) from pre_order");
	$count2=$DB->getColumn("SELECT count(*) from pre_user");

	$paytype = [];
	$rs = $DB->getAll("SELECT id,name,showname FROM pre_type WHERE status=1");
	foreach($rs as $row){
		$paytype[$row['id']] = $row['showname'];
	}
	unset($rs);

	$channel = [];
	$rs = $DB->getAll("SELECT id,name FROM pre_channel WHERE status=1");
	foreach($rs as $row){
		$channel[$row['id']] = $row['name'];
	}
	unset($rs);

	/*$tongji_cachetime=getSetting('tongji_cachetime', true);
	$tongji_cache = $CACHE->read('tongji');
	if($tongji_cachetime+3600>=time() && $tongji_cache){
		$array = unserialize($tongji_cache);
		$result=["code"=>0,"type"=>"cache","paytype"=>$paytype,"channel"=>$channel,"count1"=>$count1,"count2"=>$count2,"usermoney"=>round($array['usermoney'],2),"settlemoney"=>round($array['settlemoney'],2),"order_today"=>$array['order_today'],"order"=>[]];
	}else{*/
		$usermoney=$DB->getColumn("SELECT SUM(money) FROM pre_user WHERE money!='0.00'");
		$settlemoney=$DB->getColumn("SELECT SUM(money) FROM pre_settle");

		$today=date("Y-m-d");
		if($_POST['ts']=="创建时间"){
		    $rs=$DB->query("SELECT type,channel,money from pre_order where status=1 and addtime>='$today'");
		}else{
		    $rs=$DB->query("SELECT type,channel,money from pre_order where status=1 and date>='$today'");
		}
		
		foreach($paytype as $id=>$type){
			$order_paytype[$id]=0;
		}
		foreach($channel as $id=>$type){
			$order_channel[$id]=0;
		}
		while($row = $rs->fetch())
		{
			$order_paytype[$row['type']]+=$row['money'];
			$order_channel[$row['channel']]+=$row['money'];
		}
		foreach($order_paytype as $k=>$v){
			$order_paytype[$k] = round($v,2);
		}
		foreach($order_channel as $k=>$v){
			$order_channel[$k] = round($v,2);
		}
		$allmoney=0;
		foreach($order_paytype as $order){
			$allmoney+=$order;
		}
		$order_today['all']=round($allmoney,2);
		$order_today['paytype']=$order_paytype;
		$order_today['channel']=$order_channel;

/*		saveSetting('tongji_cachetime',time());
		$CACHE->save('tongji',serialize(["usermoney"=>$usermoney,"settlemoney"=>$settlemoney,"order_today"=>$order_today]));
*/
		$result=["code"=>0,"type"=>"online","paytype"=>$paytype,"channel"=>$channel,"count1"=>$count1,"count2"=>$count2,"usermoney"=>round($usermoney,2),"settlemoney"=>round($settlemoney,2),"order_today"=>$order_today,"order"=>[]];
/*	}*/
	for($i=1;$i<30;$i++){
	    $order_today2 = array();
	    $order_paytype = array();
	    $order_channel = array();
	    	$allmoney=0;
	    	
	   	$day = date("Y-m-d", strtotime("-{$i} day"));
	   	$day2 = date("Ymd", strtotime("-{$i} day"));
	   	$days = $day." 23:59:59";
	    if($_GET['ts']=="创建时间"){
		    $sql="SELECT type,channel,money from pre_order where status='1' and addtime>='$day' and addtime<='$days'";
		}else{
		    $sql="SELECT type,channel,money from pre_order where status='1' and date='$day'";
		}
		$rs=$DB->query($sql);
        $row = $rs->fetchAll();
      
        if($row){
            foreach ($row as $ks=>$vs){
                	$order_paytype[$vs['type']]+=$vs['money'];
    			    $order_channel[$vs['channel']]+=$vs['money'];
            }
    		
    		
    		foreach($order_paytype as $k=>$v){
    			$order_paytype[$k] = round($v,2);
    		}
    		foreach($order_channel as $k=>$v){
    			$order_channel[$k] = round($v,2);
    		}
    	
    		foreach($order_paytype as $order){
    			$allmoney+=$order;
    		}
    	    $order_today2['all']=round($allmoney,2);
    		$order_today2['paytype']=$order_paytype;
    		$order_today2['channel']=$order_channel;
    		
    	    $result["order"][$day2] = $order_today2;
        }else{
            break;
        }
  
            
		   
	
       
		
	    
			
// 		if($order_tongji = $CACHE->read('order_'.$day)){
// 			$result["order"][$day] = unserialize($order_tongji);
// 		}else{
// 			break;
// 		}
	}
	exit(json_encode($result));
break;     
case 'getcount2':
	$thtime=date("Y-m-d").' 00:00:00';
	$count1=$DB->getColumn("SELECT count(*) from pre_order");
	$count2=$DB->getColumn("SELECT count(*) from pre_user");

	$paytype = [];
	$rs = $DB->getAll("SELECT id,name,showname FROM pre_type WHERE status=1");
	foreach($rs as $row){
		$paytype[$row['id']] = $row['showname'];
	}
	unset($rs);

	$channel = [];
	$rs = $DB->getAll("SELECT id,name FROM pre_channel WHERE status=1");
	foreach($rs as $row){
		$channel[$row['id']] = $row['name'];
	}
	unset($rs);

	/*$tongji_cachetime=getSetting('tongji_cachetime', true);
	$tongji_cache = $CACHE->read('tongji');
	if($tongji_cachetime+3600>=time() && $tongji_cache){
		$array = unserialize($tongji_cache);
		$result=["code"=>0,"type"=>"cache","paytype"=>$paytype,"channel"=>$channel,"count1"=>$count1,"count2"=>$count2,"usermoney"=>round($array['usermoney'],2),"settlemoney"=>round($array['settlemoney'],2),"order_today"=>$array['order_today'],"order"=>[]];
	}else{*/
		$usermoney=$DB->getColumn("SELECT SUM(money) FROM pre_user WHERE money!='0.00'");
		$settlemoney=$DB->getColumn("SELECT SUM(money) FROM pre_settle");

		$today=date("Y-m-d");
		$rs=$DB->query("SELECT type,channel,money from pre_order where status=1 and addtime>='$today'");
		foreach($paytype as $id=>$type){
			$order_paytype[$id]=0;
		}
		foreach($channel as $id=>$type){
			$order_channel[$id]=0;
		}
		while($row = $rs->fetch())
		{
			$order_paytype[$row['type']]+=$row['money'];
			$order_channel[$row['channel']]+=$row['money'];
		}
		foreach($order_paytype as $k=>$v){
			$order_paytype[$k] = round($v,2);
		}
		foreach($order_channel as $k=>$v){
			$order_channel[$k] = round($v,2);
		}
		$allmoney=0;
		foreach($order_paytype as $order){
			$allmoney+=$order;
		}
		$order_today['all']=round($allmoney,2);
		$order_today['paytype']=$order_paytype;
		$order_today['channel']=$order_channel;

/*		saveSetting('tongji_cachetime',time());
		$CACHE->save('tongji',serialize(["usermoney"=>$usermoney,"settlemoney"=>$settlemoney,"order_today"=>$order_today]));
*/
		$result=["code"=>0,"type"=>"online","paytype"=>$paytype,"channel"=>$channel,"count1"=>$count1,"count2"=>$count2,"usermoney"=>round($usermoney,2),"settlemoney"=>round($settlemoney,2),"order_today"=>$order_today,"order"=>[]];
/*	}*/
	for($i=1;$i<30;$i++){
		$day = date("Ymd", strtotime("-{$i} day"));
		if($order_tongji = $CACHE->read('order_'.$day)){
			$result["order"][$day] = unserialize($order_tongji);
		}else{
			break;
		}
	}
	exit(json_encode($result));
break;    
case 'getcount':
	$thtime=date("Y-m-d").' 00:00:00';
	$count1=$DB->getColumn("SELECT count(*) from pre_order");
	$count2=$DB->getColumn("SELECT count(*) from pre_user");

	$paytype = [];
	$rs = $DB->getAll("SELECT id,name,showname FROM pre_type WHERE status=1");
	foreach($rs as $row){
		$paytype[$row['id']] = $row['showname'];
	}
	unset($rs);

	$channel = [];
	$rs = $DB->getAll("SELECT id,name FROM pre_channel WHERE status=1");
	foreach($rs as $row){
		$channel[$row['id']] = $row['name'];
	}
	unset($rs);

	/*$tongji_cachetime=getSetting('tongji_cachetime', true);
	$tongji_cache = $CACHE->read('tongji');
	if($tongji_cachetime+3600>=time() && $tongji_cache){
		$array = unserialize($tongji_cache);
		$result=["code"=>0,"type"=>"cache","paytype"=>$paytype,"channel"=>$channel,"count1"=>$count1,"count2"=>$count2,"usermoney"=>round($array['usermoney'],2),"settlemoney"=>round($array['settlemoney'],2),"order_today"=>$array['order_today'],"order"=>[]];
	}else{*/
		$usermoney=$DB->getColumn("SELECT SUM(money) FROM pre_user WHERE money!='0.00'");
		$settlemoney=$DB->getColumn("SELECT SUM(money) FROM pre_settle");

		$today=date("Y-m-d");
		$rs=$DB->query("SELECT type,channel,money from pre_order where status=1 and date>='$today'");
		foreach($paytype as $id=>$type){
			$order_paytype[$id]=0;
		}
		foreach($channel as $id=>$type){
			$order_channel[$id]=0;
		}
		while($row = $rs->fetch())
		{
			$order_paytype[$row['type']]+=$row['money'];
			$order_channel[$row['channel']]+=$row['money'];
		}
		foreach($order_paytype as $k=>$v){
			$order_paytype[$k] = round($v,2);
		}
		foreach($order_channel as $k=>$v){
			$order_channel[$k] = round($v,2);
		}
		$allmoney=0;
		foreach($order_paytype as $order){
			$allmoney+=$order;
		}
		$order_today['all']=round($allmoney,2);
		$order_today['paytype']=$order_paytype;
		$order_today['channel']=$order_channel;

/*		saveSetting('tongji_cachetime',time());
		$CACHE->save('tongji',serialize(["usermoney"=>$usermoney,"settlemoney"=>$settlemoney,"order_today"=>$order_today]));
*/
		$result=["code"=>0,"type"=>"online","paytype"=>$paytype,"channel"=>$channel,"count1"=>$count1,"count2"=>$count2,"usermoney"=>round($usermoney,2),"settlemoney"=>round($settlemoney,2),"order_today"=>$order_today,"order"=>[]];
/*	}*/
	for($i=1;$i<30;$i++){
		$day = date("Ymd", strtotime("-{$i} day"));
		if($order_tongji = $CACHE->read('order_'.$day)){
			$result["order"][$day] = unserialize($order_tongji);
		}else{
			break;
		}
	}
	exit(json_encode($result));
break;
case "delerweima":
$trade_no=trim($_GET['trade_no']);
		if($DB->exec("DELETE FROM pre_erweima WHERE id='$trade_no'"))
		{
			exit('{"code":200}');
	}
	else
	{
		exit("DELETE FROM pre_erweima WHERE id='$trade_no'".'{"code":100}');
	}
		
break;
case "huoqudingdan":
$trade_no=trim($_POST['order_info']);
	$order_info = $DB->getRow("select money,out_trade_no from pre_order where trade_no='$trade_no' limit 1");
	if(!$order_info){
	    	exit('{"code":-1,"msg":"未找到"}');
	}else{
	    $result = array('code'=>"0",'info'=>$order_info);
	   exit(json_encode($result));
	}
		
break;
case 'uploadimg':
	if($_POST['do']=='upload'){
		$type = $_POST['type'];
		$filename = $type.'_'.md5_file($_FILES['file']['tmp_name']).'.png';
		$fileurl = 'assets/img/Product/'.$filename;
		if(copy($_FILES['file']['tmp_name'], ROOT.'assets/img/Product/'.$filename)){
			exit('{"code":0,"msg":"succ","url":"'.$fileurl.'"}');
		}else{
			exit('{"code":-1,"msg":"上传失败，请确保有本地写入权限"}');
		}
	}
	exit('{"code":-1,"msg":"null"}');
break;
case 'setStatus':
	$trade_no=trim($_GET['trade_no']);
	$status=is_numeric($_GET['status'])?intval($_GET['status']):exit('{"code":200}');
	if($status==5){
		if($DB->exec("DELETE FROM pre_order WHERE trade_no='$trade_no'"))
			exit('{"code":200}');
		else
			exit('{"code":400,"msg":"删除订单失败！['.$DB->error().']"}');
	}else{
		if($DB->exec("update pre_order set status='$status',notify='1',is_shoudongstatus='1' where trade_no='$trade_no'")!==false){
		    $status_arr = array("0"=>"未支付","1"=>"已支付","2"=>"已退款","3"=>"已冻结");
		    $status_str = $status_arr[$status];
		    $admin_user = $_COOKIE['admin_user'];
		    $parameter = array(
              'chat_id' => $denglucaozuo_chat,  
              'parse_mode' => 'HTML',
              'text' => date("Y-m-d H:i:s")."\r\n用户：".$admin_user."==>修改订单：".$trade_no."状态为：".$status_str,
            );
            $data_string = json_encode($parameter);
            $action = "sendMessage";
            //http_post_data('sendMessage', json_encode($parameter));
            $link = 'https://api.telegram.org/bot'.$token ;
            $url = $link. "/" . $action . "?";
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
		    
		    
		    if($status==1){
		    $srow=$DB->getRow("select * from pre_order where trade_no='$trade_no' limit 1");
		    $money=$srow["getmoney"];
		    $data=date("Y-m-d H:i:s");
		    $DB->exec("update `pre_order` set `api_trade_no` ='-1',`endtime` ='$date',`date` =NOW() where `trade_no`='$trade_no'");
		    changeUserMoney($srow['uid'], $money, true, '订单收入', $srow['trade_no']);
		    }
			exit('{"code":200}'); 
		}else{
			exit('{"code":400,"msg":"修改订单失败！['.$DB->error().']"}');
		}
	}
break;
case 'order':
	$trade_no=trim($_GET['trade_no']);
	$row=$DB->getRow("select A.*,B.showname typename,C.name channelname from pre_order A,pre_type B,pre_channel C where trade_no='$trade_no' and A.type=B.id and A.channel=C.id limit 1");
	if(!$row)
		exit('{"code":-1,"msg":"当前订单不存在或未成功选择支付通道！"}');
	$result=array("code"=>0,"msg"=>"succ","data"=>$row);
	exit(json_encode($result));
break;
case 'operation':
	$status=is_numeric($_POST['status'])?intval($_POST['status']):exit('{"code":-1,"msg":"请选择操作"}');
	$checkbox=$_POST['checkbox'];
	$i=0;
	foreach($checkbox as $trade_no){
		if($status==4)$DB->exec("DELETE FROM pre_order WHERE trade_no='$trade_no'");
		elseif($status==3){
			$row=$DB->getRow("select uid,getmoney,status from pre_order where trade_no='$trade_no' limit 1");
			if($row && $row['status']==3 && $row['getmoney']>0){
				if(changeUserMoney($row['uid'], $row['getmoney'], true, '解冻订单', $trade_no))
					$DB->exec("update pre_order set status='1' where trade_no='$trade_no'");
			}
		}
		elseif($status==2){
			$row=$DB->getRow("select uid,getmoney,status from pre_order where trade_no='$trade_no' limit 1");
			if($row && $row['status']==1 && $row['getmoney']>0){
				if(changeUserMoney($row['uid'], $row['getmoney'], false, '冻结订单', $trade_no))
					$DB->exec("update pre_order set status='3' where trade_no='$trade_no'");
			}
		}
		else $DB->exec("update pre_order set status='$status' where trade_no='$trade_no' limit 1");
		$i++;
	}
	exit('{"code":0,"msg":"成功改变'.$i.'条订单状态"}');
break;


case 'operationshanchu':

	$checkbox=$_POST['checkbox'];

	$i=0;
	foreach($checkbox as $trade_no){

			$sql = "DELETE FROM pre_jizhang WHERE id='$trade_no'";
	        $DB->exec($sql);
		$i++;
	}
	exit('{"code":0,"msg":"成功删除账单'.$i.'条"}');
break;

case 'getmoney': //退款查询
	if(!$conf['admin_paypwd'])exit('{"code":-1,"msg":"你还未设置支付密码"}');
	$trade_no=trim($_POST['trade_no']);
	$api=isset($_POST['api'])?intval($_POST['api']):0;
	$row=$DB->getRow("select * from pre_order where trade_no='$trade_no' limit 1");
	if(!$row)
		exit('{"code":-1,"msg":"当前订单不存在！"}');
	if($row['status']!=1)
		exit('{"code":-1,"msg":"只支持退款已支付状态的订单"}');
	if($api==1){
		if(!$row['api_trade_no'])
			exit('{"code":-1,"msg":"接口订单号不存在"}');
		$channel = \lib\Channel::get($row['channel']);
		if(!$channel){
			exit('{"code":-1,"msg":"当前支付通道信息不存在"}');
		}
		if(\lib\Plugin::isrefund($channel['plugin'])==false){
			exit('{"code":-1,"msg":"当前支付通道不支持API退款"}');
		}
		$money = $row['money'];
	}else{
		$money = $row['getmoney'];
	}
	exit('{"code":0,"money":"'.$money.'"}');
break;
case 'refund': //退款操作
	$trade_no=trim($_POST['trade_no']);
	$row=$DB->getRow("select uid,getmoney,status from pre_order where trade_no='$trade_no' limit 1");
	if(!$row)
		exit('{"code":-1,"msg":"当前订单不存在！"}');
	if($row['status']!=1)
		exit('{"code":-1,"msg":"只支持退款已支付状态的订单"}');
	if($row['getmoney']>0){
		changeUserMoney($row['uid'], $row['getmoney'], false, '订单退款', $trade_no);
		$DB->exec("update pre_order set status='2' where trade_no='$trade_no'");
	}
	exit('{"code":0,"msg":"已成功从UID:'.$row['uid'].'扣除'.$row['getmoney'].'元余额"}');
break;
case 'apirefund': //API退款操作
	$trade_no=trim($_POST['trade_no']);
	$paypwd=trim($_POST['paypwd']);
	if($paypwd!=$conf['admin_paypwd'])
		exit('{"code":-1,"msg":"支付密码输入错误！"}');
	$row=$DB->getRow("select uid,money,getmoney,status from pre_order where trade_no='$trade_no' limit 1");
	if(!$row)
		exit('{"code":-1,"msg":"当前订单不存在！"}');
	if($row['status']!=1)
		exit('{"code":-1,"msg":"只支持退款已支付状态的订单"}');
	$message = null;
	if(api_refund($trade_no, $message)){
		if($row['getmoney']>0){
			if(changeUserMoney($row['uid'], $row['getmoney'], false, '订单退款', $trade_no)){
				$addstr = '，并成功从UID:'.$row['uid'].'扣除'.$row['getmoney'].'元余额';
			}
			$DB->exec("update pre_order set status='2' where trade_no='$trade_no'");
		}
		exit('{"code":0,"msg":"API退款成功！退款金额￥'.$row['money'].$addstr.'"}');
	}else{
		exit('{"code":-1,"msg":"API退款失败：'.$message.'"}');
	}
break;
case 'freeze': //冻结订单
	$trade_no=trim($_POST['trade_no']);
	$row=$DB->getRow("select uid,getmoney,status from pre_order where trade_no='$trade_no' limit 1");
	if(!$row)
		exit('{"code":-1,"msg":"当前订单不存在！"}');
	if($row['status']!=1)
		exit('{"code":-1,"msg":"只支持冻结已支付状态的订单"}');
	if($row['getmoney']>0){
		changeUserMoney($row['uid'], $row['getmoney'], false, '订单冻结', $trade_no);
		$DB->exec("update pre_order set status='3' where trade_no='$trade_no'");
	}
	exit('{"code":0,"msg":"已成功从UID:'.$row['uid'].'冻结'.$row['getmoney'].'元余额"}');
break;
case 'unfreeze': //解冻订单
	$trade_no=trim($_POST['trade_no']);
	$row=$DB->getRow("select uid,getmoney,status from pre_order where trade_no='$trade_no' limit 1");
	if(!$row)
		exit('{"code":-1,"msg":"当前订单不存在！"}');
	if($row['status']!=3)
		exit('{"code":-1,"msg":"只支持解冻已冻结状态的订单"}');
	if($row['getmoney']>0){
		changeUserMoney($row['uid'], $row['getmoney'], true, '订单解冻', $trade_no);
		$DB->exec("update pre_order set status='1' where trade_no='$trade_no'");
	}
	exit('{"code":0,"msg":"已成功为UID:'.$row['uid'].'恢复'.$row['getmoney'].'元余额"}');
break;
case 'notify':
	$trade_no=trim($_POST['trade_no']);
	$row=$DB->getRow("select * from pre_order where trade_no='$trade_no' limit 1");
	if(!$row)
		exit('{"code":-1,"msg":"当前订单不存在！"}');
	$url=creat_callback($row);
	$DB->exec("update pre_order set is_shoudongnotify=1 where trade_no='$trade_no'");
	if($row['notify']>0)
		$DB->exec("update pre_order set notify=0 where trade_no='$trade_no'");
	exit('{"code":0,"url":"'.($_POST['isreturn']==1?$url['return']:$url['notify']).'"}');
break; 
case 'getPayType':
   
	$id=intval($_GET['id']);
	$row=$DB->getRow("select * from pre_type where id='$id' limit 1");
	
	if(!$row)
		exit('{"code":-1,"msg":"当前支付方式不存在！"}');
	$result = ['code'=>0,'msg'=>'succ','data'=>$row];
	exit(json_encode($result));
break;
case 'setPayType':
	$id=intval($_GET['id']);
	$status=intval($_GET['status']);
	$row=$DB->getRow("select * from pre_type where id='$id' limit 1");
	if(!$row)
		exit('{"code":-1,"msg":"当前支付方式不存在！"}');
	$sql = "UPDATE pre_type SET status='$status' WHERE id='$id'";
	if($DB->exec($sql))exit('{"code":0,"msg":"修改支付方式成功！"}');
	else exit('{"code":-1,"msg":"修改支付方式失败['.$DB->error().']"}');
break;
case 'delPayType':
	$id=intval($_GET['id']);
	$row=$DB->getRow("select * from pre_type where id='$id' limit 1");
	if(!$row)
		exit('{"code":-1,"msg":"当前支付方式不存在！"}');
	$row=$DB->getRow("select * from pre_channel where type='$id' limit 1");
	if($row)
		exit('{"code":-1,"msg":"删除失败，存在使用该支付方式的支付通道"}');
	$sql = "DELETE FROM pre_type WHERE id='$id'";
	if($DB->exec($sql))exit('{"code":0,"msg":"删除支付方式成功！"}');
	else exit('{"code":-1,"msg":"删除支付方式失败['.$DB->error().']"}');
break;
case 'savePayType':
	if($_POST['action'] == 'add'){
		$name=trim($_POST['name']);
		$showname=trim($_POST['showname']);
		$device=intval($_POST['device']);
		if(!preg_match('/^[a-zA-Z0-9]+$/',$name)){
			exit('{"code":-1,"msg":"调用值不符合规则"}');
		}
		$row=$DB->getRow("select * from pre_type where name='$name' and device='$device' limit 1");
		if($row)
			exit('{"code":-1,"msg":"同一个调用值+支持设备不能重复"}');
		$sql = "INSERT INTO pre_type (name, showname, device, status) VALUES ('{$name}','{$showname}',{$device},1)";
		if($DB->exec($sql))exit('{"code":0,"msg":"新增支付方式成功！"}');
		else exit('{"code":-1,"msg":"新增支付方式失败['.$DB->error().']"}');
	}else{
		$id=intval($_POST['id']);
		$name=trim($_POST['name']);
		$showname=trim($_POST['showname']);
		$device=intval($_POST['device']);
		if(!preg_match('/^[a-zA-Z0-9]+$/',$name)){
			exit('{"code":-1,"msg":"调用值不符合规则"}');
		}
		$row=$DB->getRow("select * from pre_type where name='$name' and device='$device' and id<>$id limit 1");
		if($row)
			exit('{"code":-1,"msg":"同一个调用值+支持设备不能重复"}');
		$sql = "UPDATE pre_type SET name='{$name}',showname='{$showname}',device='{$device}' WHERE id='$id'";
		if($DB->exec($sql)!==false)exit('{"code":0,"msg":"修改支付方式成功！"}');
		else exit('{"code":-1,"msg":"修改支付方式失败['.$DB->error().']"}');
	}
break;
case 'getPlugin':
	$name = trim($_GET['name']);
	$row=$DB->getRow("select * from pre_plugin where name='$name'");
	if($row){
		$result = ['code'=>0,'msg'=>'succ','data'=>$row];
		exit(json_encode($result));
	}
	else exit('{"code":-1,"msg":"当前支付插件不存在！"}');
break;
case 'getPlugins':
	$typeid = intval($_GET['typeid']);
	$type=$DB->getColumn("select name from pre_type where id='$typeid' limit 1");
	if(!$type)
		exit('{"code":-1,"msg":"当前支付方式不存在！"}');
	$list=$DB->getAll("select name,showname from pre_plugin where types like '%$type%'");
	if($list){
		$result = ['code'=>0,'msg'=>'succ','data'=>$list];
		exit(json_encode($result));
	}
	else exit('{"code":-1,"msg":"没有找到支持该支付方式的插件"}');
break;
case 'getChannel':
	$id=intval($_GET['id']);
	$row=$DB->getRow("select * from pre_channel where id='$id' limit 1");
	if(!$row){
	 
		exit('{"code":-1,"msg":"当前支付通道不存在！"}');
	    
	}
	$have_order=0;
	//如果存在订单或者账单直接提示：禁止修改：
    $channel_id = $row['id'];
		$row_jizhang=$DB->getRow("select * from pre_jizhang where channel_id='$channel_id' limit 1");
			//先看看是不是有订单：
		$row_order=$DB->getRow("select * from pre_order where channel='$channel_id' limit 1");
    if($row_jizhang || $row_order){
        $have_order = 1;
        // exit('{"code":-1,"msg":"当前支付通道存在账单记录，禁止修改此通道信息！"}');

    }

    // if($row_order){
    //     exit('{"code":-1,"msg":"当前支付通道存在订单记录，禁止修改此通道信息！"}');

    // }
	$result = ['code'=>0,'msg'=>'succ','data'=>$row,'have_order'=>$have_order];
	exit(json_encode($result));
break;
case 'getjizhang':
	$id=intval($_GET['id']);
	$row=$DB->getRow("select * from pre_jizhang where id='$id' limit 1");
	if(!$row){
	    	exit('{"code":-1,"msg":"当前账单信息不存在！"}');
	}
	
    $addtime = $row['addtime'];
    $now_time = time();
    $chaguo = 172800;
    if(($now_time-$addtime)>$chaguo){
        exit('{"code":-1,"msg":"超过48小时！禁止编辑！"}');
    }
	
	
	$row['addtime']  = date("Y-m-d H:i:s",$row['addtime']);
	$row['tongjistarttime']  = date("Y-m-d H:i:s",$row['tongjistarttime'])." - ".date("Y-m-d H:i:s",$row['tongjiendtime']);
	$result = ['code'=>0,'msg'=>'succ','data'=>$row];
	exit(json_encode($result));
break;

case 'getChannels':
	$typeid = intval($_GET['typeid']);
	$type=$DB->getColumn("select name from pre_type where id='$typeid' limit 1");
	if(!$type)
		exit('{"code":-1,"msg":"当前支付方式不存在！"}');
	$list=$DB->getAll("select id,name from pre_channel where type='$typeid' and status=1");
	if($list){
		$result = ['code'=>0,'msg'=>'succ','data'=>$list];
		exit(json_encode($result));
	}
	else exit('{"code":-1,"msg":"没有找到支持该支付方式的通道"}');
break;
case 'setChannel_p':
	$id=intval($_GET['id']);
	$status=intval($_GET['status']);
	$row=$DB->getRow("select * from pre_channel where id='$id' limit 1");
	if(!$row)
		exit('{"code":-1,"msg":"当前支付通道不存在！"}');
	if($status==1 && (empty($row['appid']) || empty($row['appkey']))){
		exit('{"code":-1,"msg":"请先配置好密钥后再开启"}');
	}
	$row=$DB->getAll("select * from pre_channel order by dijige asc");
	$all_diji = array();
	foreach ($row as $k2=>$v2){
	   $all_diji[] =  $v2['id'];  
	}
	if($status=="1"){
	    //向上调整：1
	    	$row=$DB->getAll("select * from pre_channel order by dijige asc");
	    	
	    	foreach ($all_diji as $k=>$v){
	    	    if($v==$id){
	    	        if($k==0){
	    	            	exit('{"code":0,"msg":"已经是第一了！"}');
	    	        }
	    	    }
	    	    // array(1,2,3,4,5,6,7);
	    	    //4   向上移动一位
	    	    // array(1,2,4,3,5,6,7);
	    	    if($v==$id){
	    	       $old_id = $all_diji[$k-1];
	    	       $all_diji[$k-1]=$v;
	    	       $all_diji[$k]=$old_id;
	    	    }
	    	}
	    	$new_dijige =0;
	    	foreach ($all_diji as $k=>$v){
	    	     $for_id = $v;
	    	     $new_dijige +=1;
	    	     $sql = "UPDATE pre_channel SET dijige='$new_dijige' WHERE id='$for_id'";
	    	     $DB->exec($sql);
	    	}
	    
	    
	    	//$sql = "UPDATE pre_channel SET dijige=dijige+1 WHERE id='$id'";

	}else{
	    //向下调整1： 
	    	$row=$DB->getAll("select * from pre_channel order by dijige asc");
	    	
	    	foreach ($all_diji as $k=>$v){
	    	    if($v==$id){
	    	        
	    	        if($k+1==count($all_diji)){
	    	            exit('{"code":0,"msg":"已经是最后一个了！"}');
	    	        }
	    	    }
	    	    // array(1,2,3,4,5,6,7);
	    	    //4   向上移动一位
	    	    // array(1,2,4,3,5,6,7);
	    	    if($v==$id){
	    	       $old_id = $all_diji[$k+1];
	    	       $all_diji[$k+1]=$v;
	    	       $all_diji[$k]=$old_id;
	    	    }
	    	}
	    	$new_dijige =0;
	    	foreach ($all_diji as $k=>$v){
	    	     $for_id = $v;
	    	     $new_dijige +=1;
	    	     $sql = "UPDATE pre_channel SET dijige='$new_dijige' WHERE id='$for_id'";
	    	     $DB->exec($sql);
	    	}
	    
	    //	$sql = "UPDATE pre_channel SET dijige=dijige-1 WHERE id='$id'";
	}
	exit('{"code":0,"msg":"修改支付通道成功！"}');
	
	if($DB->exec($sql))exit('{"code":0,"msg":"修改支付通道成功！"}');
	else exit('{"code":-1,"msg":"修改支付通道失败['.$DB->error().']"}');
break;
case 'setChannel_S2':
	$id=intval($_GET['id']);
	$dijige=intval($_GET['dijige']);
	$row=$DB->getRow("select * from pre_roll where id='$id' limit 1");
	if(!$row){
		exit('{"code":-1,"msg":"当前通道轮询不存在！"}');
    }
	$row=$DB->getAll("select * from pre_roll order by dijige asc");
	$all_diji = array();
	foreach ($row as $k2=>$v2){
	   $all_diji[] =  $v2['id'];  
	   
	}

	    //向上调整：1
	    	$row=$DB->getAll("select * from pre_roll order by dijige asc");
	    	
	    	foreach ($all_diji as $k=>$v){
	    	    
	    	    // array(1,2,3,4,5,6,7);
	    	    //  
	    	    // array(1,2,3,4,5,6,7);
	    	    if($v == $id){
	    	      //  $a1=array("0"=>"red","1"=>"green");
	    	       unset($all_diji[$k]);
                    $a2=array($v);
                    array_splice($all_diji,$dijige-1,0,$a2);
                   
	    	    }
	    	    
	    	   
	    	}
	    
	    	
	    	
	    	$new_dijige =0;
	    	foreach ($all_diji as $k=>$v){
	    	     $for_id = $v;
	    	     $new_dijige +=1;
	    	     $sql = "UPDATE pre_roll SET dijige='$new_dijige' WHERE id='$for_id'";
	    	     $DB->exec($sql);
	    	}
	    
	    
	    	//$sql = "UPDATE pre_channel SET dijige=dijige+1 WHERE id='$id'";


	exit('{"code":0,"msg":"修改通道轮询顺序成功！"}');
	
	if($DB->exec($sql))exit('{"code":0,"msg":"修改通道轮询顺序成功！"}');
	else exit('{"code":-1,"msg":"修改通道轮询顺序失败['.$DB->error().']"}');
break;
case 'setChannel_S':
	$id=intval($_GET['id']);
	$dijige=intval($_GET['dijige']);
	$row=$DB->getRow("select * from pre_channel where id='$id' limit 1");
	if(!$row)
		exit('{"code":-1,"msg":"当前支付通道不存在！"}');
	if($status==1 && (empty($row['appid']) || empty($row['appkey']))){
		exit('{"code":-1,"msg":"请先配置好密钥后再开启"}');
	}
	$row=$DB->getAll("select * from pre_channel order by dijige asc");
	$all_diji = array();
	foreach ($row as $k2=>$v2){
	   $all_diji[] =  $v2['id'];  
	   
	}

	    //向上调整：1
	    	$row=$DB->getAll("select * from pre_channel order by dijige asc");
	    	
	    	foreach ($all_diji as $k=>$v){
	    	    
	    	    // array(1,2,3,4,5,6,7);
	    	    //  
	    	    // array(1,2,3,4,5,6,7);
	    	    if($v == $id){
	    	      //  $a1=array("0"=>"red","1"=>"green");
	    	       unset($all_diji[$k]);
                    $a2=array($v);
                    array_splice($all_diji,$dijige-1,0,$a2);
                   
	    	    }
	    	    
	    	   
	    	}
	    
	    	
	    	
	    	$new_dijige =0;
	    	foreach ($all_diji as $k=>$v){
	    	     $for_id = $v;
	    	     $new_dijige +=1;
	    	     $sql = "UPDATE pre_channel SET dijige='$new_dijige' WHERE id='$for_id'";
	    	     $DB->exec($sql);
	    	}
	    
	    
	    	//$sql = "UPDATE pre_channel SET dijige=dijige+1 WHERE id='$id'";


	exit('{"code":0,"msg":"修改支付通道成功！"}');
	
	if($DB->exec($sql))exit('{"code":0,"msg":"修改支付通道成功！"}');
	else exit('{"code":-1,"msg":"修改支付通道失败['.$DB->error().']"}');
break;


case 'setChannel':
	$id=intval($_GET['id']);
	$status=intval($_GET['status']);
	$row=$DB->getRow("select * from pre_channel where id='$id' limit 1");
	if(!$row)
		exit('{"code":-1,"msg":"当前支付通道不存在！"}');
	if($status==1 && (empty($row['appid']) || empty($row['appkey']))){
		exit('{"code":-1,"msg":"请先配置好密钥后再开启"}');
	}
	$sql = "UPDATE pre_channel SET status='$status' WHERE id='$id'";
	if($DB->exec($sql)){
	    $zifingyi = $row['zidingyi'];
	        if($status=="0"){
	            $status2 = "1";
	        }else{
	            $status2 = "0";
	        }
	    	$row2=$DB->getRow("select * from pre_tongdao where zidingyi='$zifingyi' limit 1");
	    	
        	if(!$row2){
        	     exit('{"code":0,"msg":"修改支付通道成功！但是同步机器人失败！机器人无次支付通道信息"}');
        	}else {
        	    $sql2 = "UPDATE pre_tongdao SET status='$status2' WHERE zidingyi='$zifingyi'";
        	    if($DB->exec($sql2)){
        	          exit('{"code":0,"msg":"修改支付通道成功！且同步至机器人成功！"}');
        	    }else{
        	          exit('{"code":0,"msg":"修改支付通道成功！但是同步机器人失败！"}');
        	    }
        	}
        	
	}else {
	    exit('{"code":-1,"msg":"修改支付通道失败['.$DB->error().']"}');
	    
	}
break;
case 'delChannel':
	$id=intval($_GET['id']);
	$row=$DB->getRow("select * from pre_channel where id='$id' limit 1");
			$channel_id = $row['id'];

	if(!$row){
        exit('{"code":-1,"msg":"当前支付通道不存在！"}');
	    
	}
	$row_jizhang=$DB->getRow("select * from pre_jizhang where channel_id='$channel_id' limit 1");
    if($row_jizhang){
        exit('{"code":-1,"msg":"当前支付通道存在账单记录，禁止删除此通道！"}');

    }
	//先看看是不是有订单：
		$row_order=$DB->getRow("select * from pre_order where channel='$channel_id' limit 1");
    if($row_order){
        exit('{"code":-1,"msg":"当前支付通道存在订单记录，禁止删除此通道！"}');

    }
    
	//再看看是不是有账单：
		
	$sql = "DELETE FROM pre_channel WHERE id='$id'";
	if($DB->exec($sql))exit('{"code":0,"msg":"删除支付通道成功！"}');
	else exit('{"code":-1,"msg":"删除支付通道失败['.$DB->error().']"}');
break;
case 'deljizhang':
	$id=intval($_GET['id']);
	$row=$DB->getRow("select * from pre_jizhang where id='$id' limit 1");
	if(!$row){
		exit('{"code":-1,"msg":"当前账单记录不存在！"}');
	}
    $addtime = $row['addtime']; 
    $now_time = time();
    $chaguo = 172800;
    if(($now_time-$addtime)>$chaguo){
        exit('{"code":-1,"msg":"超过48小时！禁止删除！"}'); 
    }
	$sql = "DELETE FROM pre_jizhang WHERE id='$id'";
	if($DB->exec($sql))exit('{"code":0,"msg":"删除账单记录成功！"}');
	else exit('{"code":-1,"msg":"删除账单记录失败['.$DB->error().']"}');
break;

case 'saveChannel':
	if($_POST['action'] == 'add'){
		$name=trim($_POST['name']);
		$rate=trim($_POST['rate']);
		$type=intval($_POST['type']);
		$plugin=trim($_POST['plugin']);
		$beizhu=trim($_POST['beizhu']);
		$zidingyi=trim($_POST['zidingyi']);
			$chatid=trim($_POST['chatid']);
		$topzidingyi=trim($_POST['topzidingyi']);
		/*
		bianhao
        feilv
        chenglv
        shifoukangtou
        nengfoubingfa
        jinefanwei
		
		*/
		$bianhao=trim($_POST['bianhao']);
		$feilv=trim($_POST['feilv']);
		$chenglv=trim($_POST['chenglv']);
	    $shifoukangtou=trim($_POST['shifoukangtou']);
		$nengfoubingfa=trim($_POST['nengfoubingfa']);
		$jinefanwei=trim($_POST['jinefanwei']);
        $yunxingtime = trim($_POST['yunxingtime']);
		
		if(empty($zidingyi)){
		    	exit('{"code":-1,"msg":"自定义编号不能为空"}');
		}
		if(empty($chatid)){
		    	exit('{"code":-1,"msg":"会话群chatid不能为空"}');
		}
		if(empty($topzidingyi)){
		    	exit('{"code":-1,"msg":"上游通道自定义编号不能为空"}');
		}
		
		if(!preg_match('/^[0-9.]+$/',$rate)){
			exit('{"code":-1,"msg":"分成比例不符合规则"}');
		}
		$row=$DB->getRow("select * from pre_channel where name='$name' limit 1");
		if($row)
			exit('{"code":-1,"msg":"支付通道名称重复"}');
			
		$row2=$DB->getRow("select * from pre_channel where zidingyi='$zidingyi' limit 1");
		if($row2){
			exit('{"code":-1,"msg":"自定义编号重复"}');	
		}
	
			
			
		$sql = "INSERT INTO pre_channel (name,chatid, rate, type, plugin,beizhu,zidingyi,bianhao,feilv,chenglv,shifoukangtou,nengfoubingfa,jinefanwei,yunxingtime,topzidingyi) VALUES ('{$name}', '{$chatid}', '{$rate}', {$type}, '{$plugin}', '{$beizhu}', '{$zidingyi}', '{$bianhao}', '{$feilv}', '{$chenglv}', '{$shifoukangtou}', '{$nengfoubingfa}', '{$jinefanwei}', '{$yunxingtime}', '{$topzidingyi}')";
		$DB->exec($sql);
		$rows=$DB->getRow("select * from pre_channel order by id desc limit 1");
		$ids = $rows['id'];
		$sql = "UPDATE pre_channel SET dijige='{$ids}' WHERE id='$ids'";
		if($DB->exec($sql)!==false)exit('{"code":0,"msg":"新增支付通道成功！"}');
	
		else exit('{"code":-1,"msg":"新增支付通道失败['.$DB->error().']"}');
	}else{
		$id=intval($_POST['id']);
		$channel_id = $id;
        $row_jizhang=$DB->getRow("select * from pre_jizhang where channel_id='$channel_id' limit 1");
        $row_order=$DB->getRow("select * from pre_order where channel='$channel_id' limit 1");
        if($row_jizhang || $row_order){
           $have_order = 1;
           exit('{"code":-1,"msg":"必须清空订单和账单才能编辑该通道！"}');
        }
		$name=trim($_POST['name']);
		$rate=trim($_POST['rate']); 
		$type=intval($_POST['type']);
		$plugin=trim($_POST['plugin']); 
	    $beizhu=trim($_POST['beizhu']);
	    $zidingyi=trim($_POST['zidingyi']);
	    $chatid=trim($_POST['chatid']);
	    $topzidingyi=trim($_POST['topzidingyi']); 
	    $bianhao=trim($_POST['bianhao']);
		$feilv=trim($_POST['feilv']);
		$chenglv=trim($_POST['chenglv']);
	    $shifoukangtou=trim($_POST['shifoukangtou']);
		$nengfoubingfa=trim($_POST['nengfoubingfa']);
		$jinefanwei=trim($_POST['jinefanwei']); 
	    $yunxingtime = trim($_POST['yunxingtime']); 	 
	     
	    if(empty($zidingyi)){
		    	exit('{"code":-1,"msg":"自定义编号不能为空"}');
		}
			if(empty($chatid)){
		    	exit('{"code":-1,"msg":"会话群chatid不能为空"}');
		}
		if(empty($topzidingyi)){
		    	exit('{"code":-1,"msg":"上游通道自定义编号不能为空"}');
		}
	     
		if(!preg_match('/^[0-9.]+$/',$rate)){
			exit('{"code":-1,"msg":"分成比例不符合规则"}');
		}
		$row=$DB->getRow("select * from pre_channel where name='$name' and id<>$id limit 1");
		if($row)
			exit('{"code":-1,"msg":"支付通道名称重复"}');
		
		$row2=$DB->getRow("select * from pre_channel where zidingyi='$zidingyi' and id<>$id limit 1");
		if($row2){
			exit('{"code":-1,"msg":"自定义编号重复"}');	
		}
		
			
		$sql = "UPDATE pre_channel SET name='{$name}',chatid='{$chatid}',rate='{$rate}',topzidingyi='{$topzidingyi}',type='{$type}',plugin='{$plugin}',beizhu='{$beizhu}',zidingyi='{$zidingyi}',bianhao='{$bianhao}',feilv='{$feilv}',chenglv='{$chenglv}',shifoukangtou='{$shifoukangtou}',nengfoubingfa='{$nengfoubingfa}',jinefanwei='{$jinefanwei}',yunxingtime='{$yunxingtime}' WHERE id='$id'";
		if($DB->exec($sql)!==false)exit('{"code":0,"msg":"修改支付通道成功！"}');
		else exit('{"code":-1,"msg":"修改支付通道失败['.$DB->error().']"}');
	}
break;

case 'chongxinhesuan':
    $topzidingyi = $_POST['channel_id'];
    $channel_info=$DB->getAll("select `id` from `pre_channel` where `topzidingyi`='{$topzidingyi}'");
    	    
    $channel_arr_id = array();
    foreach($channel_info as $key=>$veal){
    	$channel_arr_id[]=$veal['id'];
    		      
     }
    $all_channel = implode(",",$channel_arr_id);
    
    
    $row2=$DB->getAll("select * from pre_jizhang where channel_id  in ('$all_channel') order by addtime asc");
    //var_dump($row2);
    foreach ($row2 as $kv=>$vv){
        $jizhangid_id = $vv['id'];
        $last_money = $row2[$kv-1]['residuemoney'];
        if(abs($last_money)<=0){
            $last_money =0;
        }
         $feilv =  $vv['feilv'];
        if($feilv<=0){
            $feilv_jisuan = 1;
        }else{
            $feilv_jisuan = $feilv;
        }
        
        $jisuan = $feilv_jisuan*$vv['money'];
        $typebian = $vv['typebian'];
         
          $residuemoney = $vv['residuemoney'];
          
        if($typebian==0){
		    $genggai_money = $last_money+$jisuan;
		     $pp = $last_money."+".$jisuan."=".$genggai_money."对比".$residuemoney;
		 }else{
		    $genggai_money = $last_money-$jisuan;
		     $pp = $last_money."-".$jisuan."=".$genggai_money."对比".$residuemoney;
		}
	
      
        $xiangcha = abs($genggai_money-$residuemoney);
        
        //相差超过一元就表示有错误：
        if($xiangcha>=1){
             $istrue = 1;
           
        }else{
             $istrue = 0;
        }
        $ola_istrue = $vv['istrue'];
        //echo $ola_istrue."==>".$istrue."\r\n";
        //echo $pp;
        //if($ola_istrue !=$istrue){
            
            $sql = "UPDATE pre_jizhang SET istrue='{$istrue}', gongshi='{$pp}'  WHERE id='{$jizhangid_id}'";
            $sqas=	$DB->exec($sql);
        //}
          
        
    }
    exit('{"code":0,"msg":"处理完毕！"}');
    
break;
  

case 'savejizhang2':
        $id = $_POST['rate_id'];
        $jizhangid_id = $_POST['jizhangid_id'];
	    $now_rate=$_POST['now_rate'];
      
        $zhangdan_id = $_POST['zhangdan_id'];


		$residuemoney = trim($_POST['residuemoney']);
		

        $createtime = time();
		if($jizhangid_id==0){ 
		    	$sql = "INSERT INTO pre_jizhangrate (jizhang_id,channal_id, rate,cratetime) VALUES ('{$zhangdan_id}','{$id}', '{$now_rate}','{$createtime}')";
		}else{
		        $sql = "UPDATE pre_jizhangrate SET rate='{$now_rate}'  WHERE id='{$jizhangid_id}'";

		}	 
 
	    $sqas=	$DB->exec($sql);
		if($sqas!==false){
	
		    exit('{"code":0,"msg":"修改费率成功！"}');
		    
		}
	
		else exit('{"code":-1,"msg":"修改费率失败['.$DB->error().']"}');
	
break;

case 'savejizhang':
  
	if($_POST['action'] == 'add'){
	$typelist=intval($_POST['typelist']);
        if($typelist=="0"){
            //预付款
            
            $_POST['typebian']=0;
            $_POST['bianrates']=1;
        }else if($typelist=="1"){
            //投诉
             $_POST['feilv']=0;
            $_POST['typebian']=0;
            $_POST['bianrates']=0;
        }else if($typelist=="2"){
            //余额扣除
            $_POST['feilv']=0;
             $_POST['typebian']=1;
            $_POST['bianrates']=0;
        }elseif($typelist=="3"){
            //预退付：
            //$_POST['feilv']=0;
            $_POST['typebian']=1;
            $_POST['bianrates']=1;
        }elseif($typelist=="4"){
            //上游补钱：
            // $_POST['feilv']=0;
             $_POST['feilv']=0;
            $_POST['typebian']=1;
            $_POST['bianrates']=0;
        }elseif($typelist=="5"){
            //时下发usdt：
            $_POST['typebian']=0;
            $_POST['bianrates']=1;
        }
        
        $feilv =  $_POST['feilv'];
        if($feilv<=0){
            $feilv_jisuan = 1;
        }else{
            $feilv_jisuan = $feilv;
        }
		$addtime=strtotime($_POST['addtime']);
	
		if(!empty($_POST['tongjitime'])){
		    $tongjitime = explode(" - ",$_POST['tongjitime']);
    		$tongji_starttime = strtotime($tongjitime['0']);
    		$tongji_endtime = strtotime($tongjitime['1']);
		}else{
		    	$tongji_starttime = "";
    		$tongji_endtime = "";
		}
	
	    $adminname = $_COOKIE['admin_user'];
	
	
		$typebian=intval($_POST['typebian']);
		$money=trim($_POST['money']);
		$bianrates=trim($_POST['bianrates']);
		$channel_id=intval($_POST['channel_id']);
		if(!empty($_POST['tongjilist'])){
		     $tongjilist=trim($_POST['tongjilist']); 
		}else{
		     $tongjilist=0;
		}
      
		$residuemoney = trim($_POST['residuemoney']);
		$shengrates=trim($_POST['shengrates']);
		$remakes = trim($_POST['remakes']);

        $createtime = time();
		$istrue="0";
		//如果是预付款：	
		//if($typelist==0){
		    //查询这个通道上次
		    if($channel_id==0){
		        exit('{"code":-1,"msg":"必须选择一个上游通道"}');
		    }
		     $zhang_info = $DB->getRow("select `residuemoney` from `pre_jizhang` where `channel_id`='{$channel_id}' and id !='{$id}' order by addtime desc limit 1");
		    $old_residuemoney = $zhang_info['residuemoney'];
		    $zuixin_residuemoney = floatval($feilv_jisuan*floatval($money));
		    if($typebian==0){
		         $genggai_money = $old_residuemoney+$zuixin_residuemoney;
		         $pp = $old_residuemoney."+".$zuixin_residuemoney."=".$genggai_money;
		    }else{
		         $genggai_money = $old_residuemoney-$zuixin_residuemoney;
		          $pp = $old_residuemoney."-".$zuixin_residuemoney."=".$genggai_money;
		    }
		    $genggai_money = $genggai_money;
		     $residuemoney =$residuemoney;
        
               $xiangcha = abs($genggai_money-$residuemoney);
            // var_dump($xiangcha); 
		    if($xiangcha>1){
		        $istrue="1";
		    }
            $gongshi = $residuemoney."是否等于".$pp;
			
		$sql = "INSERT INTO pre_jizhang (adminname,gongshi,istrue,feilv,addtime, typelist,tongjilist, typebian,money,bianrates,remakes,channel_id,residuemoney,createtime,tongjistarttime,tongjiendtime) VALUES ('{$adminname}','{$gongshi}','{$istrue}','{$feilv}','{$addtime}', '{$typelist}', '{$tongjilist}',{$typebian}, '{$money}', '{$bianrates}', '{$remakes}', '{$channel_id}', '{$residuemoney}',  '{$createtime}', '{$tongji_starttime}',  '{$tongji_endtime}')";

	    $sqas=	$DB->exec($sql);
		$old_chs = $DB->lastinsertid();
		if($sqas!==false){
		    //这里去增加查询各个上游的费率：
		    if($channel_id>0){
		        $plugin=$DB->getRow("select `topzidingyi` from `pre_channel` where `id`='{$channel_id}' limit 1");
    	        $topzidingyi = $plugin['topzidingyi'];
    	        $channel_info=$DB->getAll("select `id`,`name` from `pre_channel` where `topzidingyi`='{$topzidingyi}'");
    	        $channel_arr = array();
    	        $channel_arr_id = array();
        	    foreach($channel_info as $key=>$veal){
    	            $channel_arr_id[]=$veal['id'];
    		        $channel_arr[$veal['id']]['name']=$veal['name'];
                }
    	        $all_channel = implode(",",$channel_arr_id);
		    
		        for($us=0;$us<count($channel_arr_id);$us++){
		              $now_channel_id = $channel_arr_id[$us];
		              $channel_info = $DB->getRow("select `feilv` from `pre_channel` where `id`='{$now_channel_id}' limit 1");
		              $rate = $channel_info['feilv']>0?$channel_info['feilv']:0;
		           
		              $sql_one = "INSERT INTO pre_jizhangrate (jizhang_id, channal_id,rate, cratetime) VALUES ('{$old_chs}', '{$now_channel_id}', '{$rate}','{$createtime}')";
		              $DB->exec($sql_one);
		        }
		    }
		    
		     
		    
		    exit('{"code":0,"msg":"新增账单成功！"}');
		    
		}
	
		else exit('{"code":-1,"msg":"新增支付通道失败['.$DB->error().']"}');
	}else{
		$id=intval($_POST['id']);
	  
        $createtime = time();
		$addtime=strtotime($_POST['addtime']);
	    $adminname = $_COOKIE['admin_user'];
		$typelist=intval($_POST['typelist']);
		//先查询是不是修改了：channel_id：
		 $channel_info = $DB->getRow("select `channel_id`,`addtime` from `pre_jizhang` where `id`='{$id}' limit 1");
		 $old_channel_id = $channel_info['channel_id'];
		 $channel_time =  $channel_info['addtime'];
		 if($typelist=="0"){
            //预付款
        
            $_POST['typebian']=0;
            $_POST['bianrates']=1;
        }else if($typelist=="1"){
            //投诉
       $_POST['feilv']=0;
            $_POST['typebian']=0;
            $_POST['bianrates']=0;
        }else if($typelist=="2"){
            //余额扣除
         $_POST['feilv']=0;
             $_POST['typebian']=1;
            $_POST['bianrates']=0;
        }elseif($typelist=="3"){
            //预退付：
        // $_POST['feilv']=0;
             $_POST['typebian']=1;
            $_POST['bianrates']=1;
        }elseif($typelist=="4"){
            //预退付：
        // $_POST['feilv']=0;
             $_POST['feilv']=0;
            $_POST['typebian']=1;
            $_POST['bianrates']=0;
        }elseif($typelist=="5"){
        
            $_POST['typebian']=0;
            $_POST['bianrates']=1;
        }
		$feilv =  $_POST['feilv'];
        if($feilv<=0){
            $feilv_jisuan = 1;
        }else{
            $feilv_jisuan = $feilv;
        }
		if(!empty($_POST['tongjilist'])){
		     $tongjilist=trim($_POST['tongjilist']); 
		}else{
		     $tongjilist=0;
		}
		$typebian=intval($_POST['typebian']);
		$money=trim($_POST['money']);
		$bianrates=trim($_POST['bianrates']);
		$channel_id=intval($_POST['channel_id']);
	
		$residuemoney = trim($_POST['residuemoney']);
        if(!empty($_POST['tongjitime'])){
		    	$tongjitime = explode(" - ",$_POST['tongjitime']);
    		$tongji_starttime = strtotime($tongjitime['0']);
    		$tongji_endtime = strtotime($tongjitime['1']);
		}else{
		    	$tongji_starttime = "";
    		$tongji_endtime = "";
		}
		
		$remakes = trim($_POST['remakes']);
			
		$istrue=0;
		//查询这个通道上次
		    if($channel_id==0){
		        exit('{"code":-1,"msg":"必须选择一个上游通道"}');
		    }
		     $zhang_info = $DB->getRow("select `residuemoney` from `pre_jizhang` where `channel_id`='{$channel_id}' and id !='{$id}' and addtime<='{$channel_time}'  order by addtime desc limit 1");
		    $old_residuemoney = $zhang_info['residuemoney'];
		    $zuixin_residuemoney = floatval($feilv_jisuan*floatval($money));
		    if($typebian==0){
		         $genggai_money = $old_residuemoney+$zuixin_residuemoney;
		         $pp = $old_residuemoney."+".$zuixin_residuemoney."=".$genggai_money;
		    }else{
		         $genggai_money = $old_residuemoney-$zuixin_residuemoney;
		          $pp = $old_residuemoney."-".$zuixin_residuemoney."=".$genggai_money;
		    }

		    $xiangcha = abs($genggai_money-$residuemoney);
		    if($xiangcha>1){
		        $istrue="1";
		    }
		      $gongshi = $residuemoney."是否等于".$pp;
			
		$sql = "UPDATE pre_jizhang SET adminname='{$adminname}',gongshi='{$gongshi}',addtime='{$addtime}',istrue='{$istrue}',feilv='{$feilv}',tongjistarttime='{$tongji_starttime}',tongjiendtime='{$tongji_endtime}',typelist='{$typelist}',tongjilist='{$tongjilist}',typebian='{$typebian}',money='{$money}',bianrates='{$bianrates}',channel_id='{$channel_id}',residuemoney='{$residuemoney}',remakes='{$remakes}' WHERE id='$id'";
		if($DB->exec($sql)!==false){
		    if($channel_id>0 && $old_channel_id != $channel_id){
		        	$sql_del = "DELETE FROM pre_jizhangrate WHERE jizhang_id='$id'";
		        	 $DB->exec($sql_del);
		        	 
		        $plugin=$DB->getRow("select `topzidingyi` from `pre_channel` where `id`='{$channel_id}' limit 1");
    	        $topzidingyi = $plugin['topzidingyi'];
    	        $channel_info=$DB->getAll("select `id`,`name` from `pre_channel` where `topzidingyi`='{$topzidingyi}'");
    	        $channel_arr = array();
    	        $channel_arr_id = array();
        	    foreach($channel_info as $key=>$veal){
    	            $channel_arr_id[]=$veal['id'];
    		        $channel_arr[$veal['id']]['name']=$veal['name'];
                }
    	        $all_channel = implode(",",$channel_arr_id);
		    
		        for($us=0;$us<count($channel_arr_id);$us++){
		              $now_channel_id = $channel_arr_id[$us];
		              $channel_info = $DB->getRow("select `feilv` from `pre_channel` where `id`='{$now_channel_id}' limit 1");
		              $rate = $channel_info['feilv']>0?$channel_info['feilv']:0;
		              
		              $sql_two = "INSERT INTO pre_jizhangrate (jizhang_id, channal_id,rate, cratetime) VALUES ('{$id}', '{$now_channel_id}', '{$rate}','{$createtime}')";
		              $DB->exec($sql_two);
		        }
		    }
		    exit('{"code":0,"msg":"修改账单成功！"}');
		    
		}
		else exit('{"code":-1,"msg":"修改账单失败['.$DB->error().']"}');
	}
break;
case 'tongbus':

	    $zidingyi=trim($_POST['zidingyi']);
        
	    if(empty($zidingyi)){
		    	exit('{"code":-1,"msg":"请输入你要同步到自定义编号！"}');
		}
		
		$row2=$DB->getRow("select * from pre_tongdao where zidingyi='$zidingyi' limit 1");
         if(!$row2){
		          exit('{"code":-1,"msg":"未查询到机器人有此自定义通道的信息"}');
         }else{
         
              
                 	$result = ['code'=>0,'msg'=>'succ','data'=>$row2];
	                exit(json_encode($result));
         }

break;

case 'chajizhangshijian':

	    $id=trim($_POST['zhangdan_id']);
	    $row3=$DB->getRow("select * from pre_jizhang where id='$id' limit 1");
        $addtime = $row3['addtime'];
        $now_time = time();
        $chaguo = 172800;
        if(($now_time-$addtime)>$chaguo){
            	exit('{"code":-1,"msg":"超过48小时！禁止点击！"}');
        }else{
             $result = ['code'=>0,'msg'=>'正常操作','data'=>''];
	        exit(json_encode($result));
        }
	 

break;


case 'tongbus2':

	    $id=trim($_POST['id']);
	    $row3=$DB->getRow("select * from pre_channel where id='$id' limit 1");
        $zidingyi = $row3['zidingyi'];
	    if(empty($zidingyi)){
		    	exit('{"code":-1,"msg":"请输入你要同步到自定义编号！"}');
		}
		
		$row2=$DB->getRow("select * from pre_tongdao where zidingyi='$zidingyi' limit 1");
         if(!$row2){
		          exit('{"code":-1,"msg":"未查询到机器人有此自定义通道的信息"}');
         }else{
            $result = ['code'=>0,'msg'=>'succ','data'=>$row2];
	        exit(json_encode($result));
         }

break;
case 'jizhangInfo':
	$id=intval($_GET['id']);
	$row=$DB->getRow("select * from pre_jizhang where id='$id' limit 1");
	if(!$row)
		exit('{"code":-1,"msg":"当前账单不存在！"}');
	$apptype = $row['channel_id'];
    $tongjilist = $row['tongjilist'];
     $caozuolist = $row['typelist'];
     $moneys = $row['money'];
    $start_time = date('Y-m-d H:i:s',$row['addtime']);
	$end_time = date('Y-m-d H:i:s',$row['endtime']);
	
	$plugin=$DB->getRow("select `topzidingyi` from `pre_channel` where `id`='{$apptype}' limit 1");
	if(!$plugin)
		exit('{"code":-1,"msg":"当前上游编号不存在！"}');
	$topzidingyi = $plugin['topzidingyi'];
	
	$channel_info=$DB->getAll("select `id`,`name` from `pre_channel` where `topzidingyi`='{$topzidingyi}'");
	
	$channel_arr = array();
	$channel_arr_id = array();
	foreach($channel_info as $key=>$veal){
	    $channel_arr_id[]=$veal['id'];
		$channel_arr[$veal['id']]['name']=$veal['name'];
    }
	$all_channel = implode(",",$channel_arr_id);
	
	//查询所有的订单：
	if($tongjilist=="0"){
	    //完成时间
	    $sqal ="select money,channel from pre_order where endtime >='".$start_time."' and endtime<='".$end_time."' and channel in (".$all_channel.") and status='1'";
	}else{
	    //开始时间
	   	$sqal ="select money,channel from pre_order where addtime >='".$start_time."' and addtime<='".$end_time."' and channel in (".$all_channel.") and status='1'";
	}
 
	$order_info=$DB->getAll($sqal);
  
	$all_money = 0;
	foreach ($order_info as $keyo=>$ordero){
	    $channel_arr[$ordero['channel']]['money'] +=$ordero['money'];
	    $all_money+=$ordero['money'];
	}

	//差额=合计跑量-余额扣除数
		
	if(count($order_info)>0){
	    	foreach($channel_arr as $key=>$veal){
		
			    $select .= '<label><span>'.$veal['name'].':</span><span>'.$veal['money'].'</span></label>&nbsp;'."<br/>";
		    }
	}else{
	    $select="";
	}
  
	$data = '<div class="modal-body">
	<div style="text-aligin:center"><button class="btn btn-danger btn-block" onclick="tongbus('.$id.')">查看上游编号:'.$topzidingyi.'详情信息</button></div>';
  

    $data .="<div style='margin-top:50px'><div>";
    

	$data .= $select;
	  $data .='<label><span>合计:</span><span>'.$all_money.'</span></label>&nbsp;'."<br/>"; 
	if($caozuolist=="2"){
	   $chaer = $all_money-$moneys;
	    	$data .= '<label><span>差额:</span><span>'.$chaer.'</span></label>&nbsp;';
	}
	
	$result=array("code"=>0,"msg"=>"succ","data"=>$data);
	exit(json_encode($result));
break;
case 'channelInfo':
	$id=intval($_GET['id']);
	$row=$DB->getRow("select * from pre_channel where id='$id' limit 1");
	if(!$row)
		exit('{"code":-1,"msg":"当前支付通道不存在！"}');
	$apptype = explode(',',$row['apptype']);
	$plugin=$DB->getRow("select `inputs`,`select` from `pre_plugin` where `name`='{$row['plugin']}' limit 1");
	if(!$plugin)
		exit('{"code":-1,"msg":"当前支付插件不存在！"}');
	$arr = explode(',',$plugin['inputs']);
	$inputs = [];
	foreach($arr as $item){
		$a = explode(':',$item);
		$inputs[$a[0]] = $a[1];
	}
	$data = '<div class="modal-body">
	<div style="text-aligin:center"><button class="btn btn-danger btn-block" onclick="tongbus('.$id.')">同步机器人配置</button></div>
	<form class="form" id="form-info">';
	if(!empty($plugin['select'])){
		$arr = explode(',',$plugin['select']);
		$select = '';
		foreach($arr as $item){
			$a = explode(':',$item);
			$select .= '<label><input type="checkbox" '.(in_array($a[0],$apptype)?'checked':null).' name="apptype[]" value="'.$a[0].'">'.$a[1].'</label>&nbsp;';
		}
		$data .= '<div class="form-group"><input type="hidden" id="isapptype" name="isapptype" value="1"/><label>请选择可用的接口：</label><br/><div class="checkbox">'.$select.'</div></div>';
	}
	if($inputs['appid'])$data .= '<div class="form-group"><label>'.$inputs['appid'].'：</label><br/><input type="text" id="appid" name="appid" value="'.$row['appid'].'" class="form-control" required/></div>';
	if($inputs['appkey'])$data .= '<div class="form-group"><label>'.$inputs['appkey'].'：</label><br/><textarea id="appkey" name="appkey" rows="2" class="form-control" required>'.$row['appkey'].'</textarea></div>';
	if($inputs['appsecret'])$data .= '<div class="form-group"><label>'.$inputs['appsecret'].'：</label><br/><textarea id="appsecret" name="appsecret" rows="2" class="form-control" required>'.$row['appsecret'].'</textarea></div>';
	if($inputs['appurl'])$data .= '<div class="form-group"><label>'.$inputs['appurl'].'：</label><br/><input type="text" id="appurl" name="appurl" value="'.$row['appurl'].'" class="form-control" required/></div>';
	if($inputs['appmchid'])$data .= '<div class="form-group"><label>'.$inputs['appmchid'].'：</label><br/><input type="text" id="appmchid" name="appmchid" value="'.$row['appmchid'].'" class="form-control" required/></div>';

    if($inputs['apiurl'])$data .= '<div class="form-group"><label>'.$inputs['apiurl'].'：</label><br/><input type="text" id="apiurl" name="apiurl" value="'.$row['apiurl'].'" class="form-control" required/></div>';
    if($inputs['huidiaourl'])$data .= '<div class="form-group"><label>'.$inputs['huidiaourl'].'：</label><br/><input type="text" id="huidiaourl" name="huidiaourl" value="'.$row['huidiaourl'].'" class="form-control" required/></div>';
   $selectq1 = "";
   $selectq2 = "";
    if($row['pccode'] != "0"){
       $selectq2 = "selected"; 
    }else{
        $selectq1 = "selected";
    }
    
  
   $data .= '<div class="form-group">
                <label>是否开启PC码：</label><br/>
                
                
                
                <select   name="pccode">
                    
                    <option value="0" ' .$selectq1 .'>关闭</option>
                    <option value="1" '.$selectq2.'>开启</option>
                </select>
            </div>';
    $selectq1t = "";
   $selectq2t = "";
    if($row['tianshiyidong'] == "1"){
       $selectq2t = "selected"; 
    }else{
        $selectq1t = "selected";
    }        
    $data .= '<div class="form-group">
                <label>天使支付流程引导页：</label><br/>
                <select   name="tianshiyidong">
                    <option value="0" ' .$selectq1t .'>关闭</option>
                    <option value="1" '.$selectq2t.'>开启</option>
                </select>
            </div>';        
            
            
     $selectq3 = "";
   $selectq4 = "";
    if($row['shortlink']=="0"){
       $selectq3 = "selected"; 
    }else{
        $selectq4 = "selected";
    }
    $selectq5 = "";
    $selectq6 = "";
    
    $selectq7 = "";
    $selectq8 = "";
    
   
    
    if($row['waiwangip']=="0"){
       $selectq5 = "selected"; 
    }else{
        $selectq6 = "selected";
    }
    
    if($row['weixinxianzhi']!="0"){
       $selectq8 = "selected"; 
    }else{
        $selectq7 = "selected";
    }
  
    
    // $data .= '<div class="form-group">
    //             <label>是否开启PC短码：</label><br/>
    //             <select   name="shortlink">
    //                 <option value="0" '.$selectq3.'>关闭</option>
    //                 <option value="1" '.$selectq4.'>开启</option>
    //             </select>
    //         </div>'; 
     $data .= '<div class="form-group">
                <label>是否支持外网IP：</label><br/>
                <select   name="waiwangip">
                    <option value="0" '.$selectq5.'>支持</option>
                    <option value="1" '.$selectq6.'>不支持</option>
                </select>
            </div>';
    // $data .= '<div class="form-group">
    //             <label>是否开启微信限制：</label><br/>
    //             <select   name="weixinxianzhi">
    //                 <option value="0" '.$selectq7.'>开启</option>
    //                 <option value="1" '.$selectq8.'>不开启</option>
    //             </select>
    //         </div>';

           


	$data .= '<button type="button" id="save" onclick="saveInfo('.$id.')" class="btn btn-primary btn-block">保存</button></form></div>';
	$result=array("code"=>0,"msg"=>"succ","data"=>$data);
	exit(json_encode($result));
break;
case 'saveChannelInfo':
 
    
	$id=intval($_GET['id']);
	$appid=isset($_POST['appid'])?trim($_POST['appid']):null;
	$appkey=isset($_POST['appkey'])?trim($_POST['appkey']):null;
	$appsecret=isset($_POST['appsecret'])?trim($_POST['appsecret']):null;
	$appurl=isset($_POST['appurl'])?trim($_POST['appurl']):null;
	$appmchid=isset($_POST['appmchid'])?trim($_POST['appmchid']):null;
	$apiurl=isset($_POST['apiurl'])?trim($_POST['apiurl']):null;
	$huidiaourl=isset($_POST['huidiaourl'])?trim($_POST['huidiaourl']):null;
	
	$pccode=isset($_POST['pccode'])?trim($_POST['pccode']):1;
	$shortlink=isset($_POST['shortlink'])?trim($_POST['shortlink']):null;
    $waiwangip=isset($_POST['waiwangip'])?trim($_POST['waiwangip']):null;
    $tianshiyidong=isset($_POST['tianshiyidong'])?trim($_POST['tianshiyidong']):null;
    
    $weixinxianzhi=isset($_POST['weixinxianzhi'])?trim($_POST['weixinxianzhi']):null;
	if(isset($_POST['isapptype'])){
		if(!isset($_POST['apptype']) || count($_POST['apptype'])<=0)exit('{"code":-1,"msg":"请至少选择一个可用的支付接口"}');
		$apptype=implode(',',$_POST['apptype']);
	}else{
		$apptype=null;
	}
	$sql = "UPDATE pre_channel SET appid='{$appid}',appkey='{$appkey}',appsecret='{$appsecret}',appurl='{$appurl}',appmchid='{$appmchid}',apptype='{$apptype}',apiurl='{$apiurl}',huidiaourl='{$huidiaourl}',pccode='{$pccode}',shortlink='{$shortlink}',waiwangip='{$waiwangip}',tianshiyidong='{$tianshiyidong}',weixinxianzhi='{$weixinxianzhi}' WHERE id='$id'";
	if($DB->exec($sql)!==false)exit('{"code":0,"msg":"修改支付密钥成功！"}');
	else exit('{"code":-1,"msg":"修改支付密钥失败['.$DB->error().']"}');
break;
case 'getRoll':
	$id=intval($_GET['id']);
	$row=$DB->getRow("select * from pre_roll where id='$id' limit 1");
	if(!$row)
		exit('{"code":-1,"msg":"当前轮询组不存在！"}');
	$result = ['code'=>0,'msg'=>'succ','data'=>$row];
	exit(json_encode($result));
break;
case 'setRoll':
	$id=intval($_GET['id']);
	$status=intval($_GET['status']);
	$row=$DB->getRow("select * from pre_roll where id='$id' limit 1");
	if(!$row)
		exit('{"code":-1,"msg":"当前轮询组不存在！"}');
	if($status==1 && empty($row['info'])){
		exit('{"code":-1,"msg":"请先配置好支付通道后再开启"}');
	}
	$sql = "UPDATE pre_roll SET status='$status' WHERE id='$id'";
	if($DB->exec($sql))exit('{"code":0,"msg":"修改轮询组成功！"}');
	else exit('{"code":-1,"msg":"修改轮询组失败['.$DB->error().']"}');
break;
case 'delRoll':
	$id=intval($_GET['id']);
	$row=$DB->getRow("select * from pre_roll where id='$id' limit 1");
	if(!$row)
		exit('{"code":-1,"msg":"当前轮询组不存在！"}');
	$sql = "DELETE FROM pre_roll WHERE id='$id'";
	if($DB->exec($sql))exit('{"code":0,"msg":"删除轮询组成功！"}');
	else exit('{"code":-1,"msg":"删除轮询组失败['.$DB->error().']"}');
break;
case 'saveRoll':
	if($_POST['action'] == 'add'){
		$name=trim($_POST['name']);
		$type=intval($_POST['type']);
		$kind=intval($_POST['kind']);
		$zuming = trim($_POST['zuming']);
			$yanse = trim($_POST['yanse']);
			
	
			
		$row=$DB->getRow("select * from pre_roll where name='$name' limit 1");
		if($row)
			exit('{"code":-1,"msg":"轮询组名称重复"}');
		$sql = "INSERT INTO pre_roll (name, type, kind, zuming, yanse) VALUES ('{$name}', '{$type}', '{$kind}', '{$zuming}', '{$yanse}')";
	
		
		if($DB->exec($sql))exit('{"code":0,"msg":"新增轮询组成功！"}');
		else exit('{"code":-1,"msg":"新增轮询组失败['.$DB->error().']"}');
	}else{
		$id=intval($_POST['id']);
		$name=trim($_POST['name']);
		$type=intval($_POST['type']);
		$kind=intval($_POST['kind']);
		$zuming = trim($_POST['zuming']);
		$yanse =trim ($_POST['yanse']);
		
				
		$row=$DB->getRow("select * from pre_roll where name='$name' and id<>$id limit 1");
		if($row)
			exit('{"code":-1,"msg":"轮询组名称重复"}');
		$sql = "UPDATE pre_roll SET name='{$name}',type='{$type}',kind='{$kind}',zuming='{$zuming}',yanse='{$yanse}' WHERE id='$id'";
		if($DB->exec($sql)!==false)exit('{"code":0,"msg":"修改轮询组成功！"}');
		else exit('{"code":-1,"msg":"修改轮询组失败['.$DB->error().']"}');
	}
break;
case 'rollInfo':
	$id=intval($_GET['id']);
	$row=$DB->getRow("select * from pre_roll where id='$id' limit 1");
	if(!$row)
		exit('{"code":-1,"msg":"当前轮询组不存在！"}');
	$list=$DB->getAll("select id,name from pre_channel where type='{$row['type']}' and status=1");
	if(!$list)exit('{"code":-1,"msg":"没有找到支持该支付方式的通道"}');
	if(!empty($row['info'])){
		$arr = explode(',',$row['info']);
		$arr_price = explode('|',$row['prices']);
		$arr_terminals = explode('|',$row['terminals']);
		$info = [];
		$info_prices = [];
		$info_terminals = [];
		
		foreach($arr_price as $items){
			$as = explode(':',$items);
			
			$info_prices[$as[0]] = $as[1];
		
		}
		
		foreach($arr_terminals as $items){
			$as = explode(':',$items);
			
			$info_terminals[$as[0]] = $as[1];
		
		}
		
	//	var_dump($info_prices);
		foreach($arr as $item){
			$a = explode(':',$item);
			
			$info[] = ['channel'=>$a[0], 'prices'=>$info_prices[$a[0]],'weight'=>$a[1]?$a[1]:1,'terminals'=>$info_terminals[$a[0]]];
		
		}
		
		
	}else{
		$info = null;
	}
	$result=array("code"=>0,"msg"=>"succ","channels"=>$list,"info"=>$info,"info_prices"=>$info_prices,'info_terminals'=>$info_terminals);
	exit(json_encode($result));
break;
case 'saveRollInfo':
	$id=intval($_GET['id']);
	$row=$DB->getRow("select * from pre_roll where id='$id' limit 1");
	if(!$row)
		exit('{"code":-1,"msg":"当前轮询组不存在！"}');
	$list=$_POST['list'];
	if(empty($list))
		exit('{"code":-1,"msg":"通道配置不能为空！"}');
	$info = '';
	$info_price = '';
	$info_terminals = '';
// 	var_dump($list);
// 	exit();
	foreach($list as $a){
		$info .= $row['kind']==1 ? $a['channel'].':'.$a['weight'].',' : $a['channel'].',';
		if(!empty($a['prices'])){
		    $info_price .=  $a['channel'].':'.$a['prices'].'|' ;
		}
		if(!empty($a['terminals'])){
		    $info_terminals .=  $a['channel'].':'.$a['terminals'].'|' ;
		}
		
	}
	$info = trim($info,',');
	$info_price = trim($info_price,'|');
	$info_terminals = trim($info_terminals,'|');
	if(empty($info))
		exit('{"code":-1,"msg":"通道配置不能为空！"}');
	$sql = "UPDATE pre_roll SET info='{$info}',prices = '{$info_price}',terminals='{$info_terminals}' WHERE id='$id'";
	
	if($DB->exec($sql)!==false)exit('{"code":0,"msg":"修改轮询组成功！"}');
	else exit('{"code":-1,"msg":"修改轮询组失败['.$DB->error().']"}');
break;
case 'getGroup':
	$gid=intval($_GET['gid']);
	$row=$DB->getRow("select * from pre_group where gid='$gid' limit 1");
	if(!$row)
		exit('{"code":-1,"msg":"当前用户组不存在！"}');
	$result = ['code'=>0,'msg'=>'succ','gid'=>$gid,'name'=>$row['name'],'info'=>json_decode($row['info'],true)];
	exit(json_encode($result));
break;
case 'gettousu':
	$gid=intval($_GET['id']);
	$row=$DB->getRow("select * from pre_tousu where id='$gid' limit 1");

	if(!$row)
		exit('{"code":-1,"msg":"当前投诉订单不存在！"}');
	$result = ['code'=>0,'msg'=>'succ','id'=>$gid,'info'=>$row];
	exit(json_encode($result));
break;
case 'getshangyou':
	$gid=intval($_GET['id']);
	$row=$DB->getRow("select * from pre_shangyouhuifu where id='$gid' limit 1");

	if(!$row)
		exit('{"code":-1,"msg":"当前条件监控信息不存在！"}');
	$result = ['code'=>0,'msg'=>'succ','id'=>$gid,'info'=>$row];
	exit(json_encode($result));
break;

case 'delGroup':
	$gid=intval($_GET['gid']);
	$row=$DB->getRow("select * from pre_group where gid='$gid' limit 1");
	if(!$row)
		exit('{"code":-1,"msg":"当前用户组不存在！"}');
	$sql = "DELETE FROM pre_group WHERE gid='$gid'";
	if($DB->exec($sql))exit('{"code":0,"msg":"删除用户组成功！"}');
	else exit('{"code":-1,"msg":"删除用户组失败['.$DB->error().']"}');
break;
case 'deltousu':
	$id=intval($_GET['id']);
	$row=$DB->getRow("select * from pre_shangyouhuifu where id='$id' limit 1");
	if(!$row)
		exit('{"code":-1,"msg":"当前条件监控信息不存在！"}');
	$sql = "DELETE FROM pre_shangyouhuifu WHERE id='$id'";
	if($DB->exec($sql))exit('{"code":0,"msg":"删除条件监控成功！"}');
	else exit('{"code":-1,"msg":"删除投诉订单失败['.$DB->error().']"}');
break;
case 'delshangyou':
	$id=intval($_GET['id']);
	$row=$DB->getRow("select * from pre_shangyouhuifu where id='$id' limit 1");
	if(!$row)
		exit('{"code":-1,"msg":"当前上游回复记录不存在！"}');
	$sql = "DELETE FROM pre_shangyouhuifu WHERE id='$id'";
	if($DB->exec($sql))exit('{"code":0,"msg":"删除成功！"}');
	else exit('{"code":-1,"msg":"删除失败['.$DB->error().']"}');
break;

case 'saveGroup':
	if($_POST['action'] == 'add'){
		$name=trim($_POST['name']);
		$row=$DB->getRow("select * from pre_group where name='$name' limit 1");
		if($row)
			exit('{"code":-1,"msg":"用户组名称重复"}');
		$info=$_POST['info'];
		$info=json_encode($info);
		$sql = "INSERT INTO pre_group (name, info) VALUES ('{$name}', '{$info}')";
		if($DB->exec($sql))exit('{"code":0,"msg":"新增用户组成功！"}');
		else exit('{"code":-1,"msg":"新增用户组失败['.$DB->error().']"}');
	}elseif($_POST['action'] == 'changebuy'){
		$gid=intval($_POST['gid']);
		$status=intval($_POST['status']);
		$sql = "UPDATE pre_group SET isbuy='{$status}' WHERE gid='$gid'";
		if($DB->exec($sql))exit('{"code":0,"msg":"修改上架状态成功！"}');
		else exit('{"code":-1,"msg":"修改上架状态失败['.$DB->error().']"}');
	}else{
		$gid=intval($_POST['gid']);
		$name=trim($_POST['name']);
		$row=$DB->getRow("select * from pre_group where name='$name' and gid<>$gid limit 1");
		if($row)
			exit('{"code":-1,"msg":"用户组名称重复"}');
		$info=$_POST['info'];
		$info=json_encode($info);
		$sql = "UPDATE pre_group SET name='{$name}',info='{$info}' WHERE gid='$gid'";
		if($DB->exec($sql)!==false)exit('{"code":0,"msg":"修改用户组成功！"}');
		else exit('{"code":-1,"msg":"修改用户组失败['.$DB->error().']"}');
	}
break;
case 'saveGroupPrice':
	$prices = $_POST['price'];
	$sorts = $_POST['sort'];
	foreach($prices as $gid=>$item){
		$price = trim($item);
		$sort = trim($sorts[$gid]);
		if(empty($price)||!is_numeric($price))exit('{"code":-1,"msg":"GID:'.$gid.'的售价填写错误"}');
		$DB->exec("UPDATE pre_group SET price='{$price}',sort='{$sort}' WHERE gid='$gid'");
	}
	exit('{"code":0,"msg":"保存成功！"}');
break;
case 'setUser':
	$uid=intval($_GET['uid']);
	$type=trim($_GET['type']);
	$status=intval($_GET['status']);
	if($type=='pay')$sql = "UPDATE pre_user SET pay='$status' WHERE uid='$uid'";
	elseif($type=='settle')$sql = "UPDATE pre_user SET settle='$status' WHERE uid='$uid'";
	elseif($type=='group')$sql = "UPDATE pre_user SET gid='$status' WHERE uid='$uid'";
	else $sql = "UPDATE pre_user SET status='$status' WHERE uid='$uid'";
	if($DB->exec($sql)!==false)exit('{"code":0,"msg":"修改用户成功！"}');
	else exit('{"code":-1,"msg":"修改用户失败['.$DB->error().']"}');
break;
case 'resetUser':
	$uid=intval($_GET['uid']);
	$key = random(32);
	$sql = "UPDATE pre_user SET `key`='$key' WHERE uid='$uid'";
	if($DB->exec($sql)!==false)exit('{"code":0,"msg":"重置密钥成功","key":"'.$key.'"}');
	else exit('{"code":-1,"msg":"重置密钥失败['.$DB->error().']"}');
break;
case 'user_settle_info':
	$uid=intval($_GET['uid']);
	$rows=$DB->getRow("select * from pre_user where uid='$uid' limit 1");
	if(!$rows)
		exit('{"code":-1,"msg":"当前用户不存在！"}');
	$data = '<div class="form-group"><div class="input-group"><div class="input-group-addon">结算方式</div><select class="form-control" id="pay_type" default="'.$rows['settle_id'].'">'.($conf['settle_alipay']?'<option value="1">支付宝</option>':null).''.($conf['settle_wxpay']?'<option value="2">微信</option>':null).''.($conf['settle_qqpay']?'<option value="3">QQ钱包</option>':null).''.($conf['settle_bank']?'<option value="4">银行卡</option>':null).'</select></div></div>';
	$data .= '<div class="form-group"><div class="input-group"><div class="input-group-addon">结算账号</div><input type="text" id="pay_account" value="'.$rows['account'].'" class="form-control" required/></div></div>';
	$data .= '<div class="form-group"><div class="input-group"><div class="input-group-addon">真实姓名</div><input type="text" id="pay_name" value="'.$rows['username'].'" class="form-control" required/></div></div>';
	$data .= '<input type="submit" id="save" onclick="saveInfo('.$uid.')" class="btn btn-primary btn-block" value="保存">';
	$result=array("code"=>0,"msg"=>"succ","data"=>$data,"pay_type"=>$rows['settle_id']);
	exit(json_encode($result));
break;
case 'user_settle_save':
	$uid=intval($_POST['uid']);
	$pay_type=trim(daddslashes($_POST['pay_type']));
	$pay_account=trim(daddslashes($_POST['pay_account']));
	$pay_name=trim(daddslashes($_POST['pay_name']));
	$sds=$DB->exec("update `pre_user` set `settle_id`='$pay_type',`account`='$pay_account',`username`='$pay_name' where `uid`='$uid'");
	if($sds!==false)
		exit('{"code":0,"msg":"修改记录成功！"}');
	else
		exit('{"code":-1,"msg":"修改记录失败！'.$DB->error().'"}');
break;
case 'user_cert':
	$uid=intval($_GET['uid']);
	$rows=$DB->getRow("select cert,certno,certname,certtime from pre_user where uid='$uid' limit 1");
	if(!$rows)
		exit('{"code":-1,"msg":"当前用户不存在！"}');
	$result = ['code'=>0,'msg'=>'succ','uid'=>$uid,'cert'=>$rows['cert'],'certno'=>$rows['certno'],'certname'=>$rows['certname'],'certtime'=>$rows['certtime']];
	exit(json_encode($result));
break;
case 'recharge':
	$uid=intval($_POST['uid']);
	$do=$_POST['actdo'];
	$rmb=floatval($_POST['rmb']);
	$row=$DB->getRow("select uid,money from pre_user where uid='$uid' limit 1");
	if(!$row)
		exit('{"code":-1,"msg":"当前用户不存在！"}');
	if($do==1 && $rmb>$row['money'])$rmb=$row['money'];
	if($do==0){
		changeUserMoney($uid, $rmb, true, '后台加款');
	}else{
		changeUserMoney($uid, $rmb, false, '后台扣款');
	}
	exit('{"code":0,"msg":"succ"}');
break;
case 'create_batch':
	$count=$DB->getColumn("SELECT count(*) from pre_settle where status=0");
	if($count==0)exit('{"code":-1,"msg":"当前不存在待结算的记录"}');
	$batch=date("Ymd").rand(111,999);
	$allmoney = 0;
	$rs=$DB->query("SELECT * from pre_settle where status=0");
	while($row = $rs->fetch())
	{
		$DB->exec("UPDATE pre_settle SET batch='$batch',status=2 WHERE id='{$row['id']}'");
		$allmoney+=$row['realmoney'];
	}
	$DB->exec("INSERT INTO `pre_batch` (`batch`, `allmoney`, `count`, `time`, `status`) VALUES ('{$batch}', '{$allmoney}', '{$count}', '{$date}', '0')");

	exit('{"code":0,"msg":"succ","batch":"'.$batch.'","count":"'.$count.'","allmoney":"'.$allmoney.'"}');
break;
case 'complete_batch':
	$batch=trim($_POST['batch']);
	$DB->exec("UPDATE pre_settle SET status=1 WHERE batch='$batch'");
	exit('{"code":0,"msg":"succ"}');
break;
case 'del_batch':
	$batch=trim($_POST['batch']);

	$DB->exec("DELETE  FROM pre_settle WHERE batch='$batch'");
	$DB->exec("DELETE  FROM pre_batch WHERE batch='$batch'");
	
	exit('{"code":0,"msg":"succ"}');
break;
case 'setBudanStatus':
	$id=intval($_GET['id']);
	$status=intval($_GET['status']);
	$changdate = time();
	$admin_id  = $_COOKIE['admin_user'];
	if($status==4){
	    $sql2 = "update pre_tianshibudan set status='$status',deletetime='$changdate',admin_id='$admin_id'  where id='$id'";
	    if($DB->exec($sql2)!==false)
			exit('{"code":200}');
		else
			exit('{"code":400,"msg":"删除记录失败！['.$DB->error().']"}');
	}else{
		if($status==1){
			$sql = "update pre_tianshibudan set status='$status',updatetime='$changdate',admin_id='$admin_id'  where id='$id'";
		}else{
			$sql = "update pre_tianshibudan set status='$status',updatetime='$changdate',admin_id='$admin_id' where id='$id'";
		}
		if($DB->exec($sql)!==false)
		    //这里还要去推送给对应的商户群：
		    
		
			exit('{"code":200}');
		else
			exit('{"code":400,"msg":"修改记录失败！['.$DB->error().']"}');
	}
break;
case 'setSettleStatus':
	$id=intval($_GET['id']);
	$status=intval($_GET['status']);
	if($status==4){
		if($DB->exec("DELETE FROM pre_settle WHERE id='$id'"))
			exit('{"code":200}');
		else
			exit('{"code":400,"msg":"删除记录失败！['.$DB->error().']"}');
	}else{
		if($status==1){
			$sql = "update pre_settle set status='$status',endtime='$date',result=NULL where id='$id'";
		}else{
			$sql = "update pre_settle set status='$status',endtime=NULL where id='$id'";
		}
		if($DB->exec($sql)!==false)
			exit('{"code":200}');
		else
			exit('{"code":400,"msg":"修改记录失败！['.$DB->error().']"}');
	}
break;
case 'opslist':
	$status=$_POST['status'];
	$checkbox=$_POST['checkbox'];
	$i=0;
	foreach($checkbox as $id){
		if($status==4){
			$sql = "DELETE FROM pre_settle WHERE id='$id'";
		}elseif($status==1){
			$sql = "update pre_settle set status='$status',endtime='$date',result=NULL where id='$id'";
		}else{
			$sql = "update pre_settle set status='$status',endtime=NULL where id='$id'";
		}
		$DB->exec($sql);
		$i++;
	}
	exit('{"code":0,"msg":"成功改变'.$i.'条记录状态"}');
break;
case 'settle_result':
	$id=intval($_POST['id']);
	$row=$DB->getRow("select * from pre_settle where id='$id' limit 1");
	if(!$row)
		exit('{"code":-1,"msg":"当前结算记录不存在！"}');
	$result = ['code'=>0,'msg'=>'succ','result'=>$row['result']];
	exit(json_encode($result));
break;
case 'settle_setresult':
	$id=intval($_POST['id']);
	$result=trim($_POST['result']);
	$row=$DB->getRow("select * from pre_settle where id='$id' limit 1");
	if(!$row)
		exit('{"code":-1,"msg":"当前结算记录不存在！"}');
	$sds = $DB->exec("UPDATE pre_settle SET result='$result' WHERE id='$id'");
	if($sds!==false)
		exit('{"code":0,"msg":"修改成功！"}');
	else
		exit('{"code":-1,"msg":"修改失败！'.$DB->error().'"}');
break;
case 'settle_info':
	$id=intval($_GET['id']);
	$rows=$DB->getRow("select * from pre_settle where id='$id' limit 1");
	if(!$rows)
		exit('{"code":-1,"msg":"当前结算记录不存在！"}');
	$data = '<div class="form-group"><div class="input-group"><div class="input-group-addon">结算方式</div><select class="form-control" id="pay_type" default="'.$rows['type'].'">'.($conf['settle_alipay']?'<option value="1">支付宝</option>':null).''.($conf['settle_wxpay']?'<option value="2">微信</option>':null).''.($conf['settle_qqpay']?'<option value="3">QQ钱包</option>':null).''.($conf['settle_bank']?'<option value="4">银行卡</option>':null).'</select></div></div>';
	$data .= '<div class="form-group"><div class="input-group"><div class="input-group-addon">结算账号</div><input type="text" id="pay_account" value="'.$rows['account'].'" class="form-control" required/></div></div>';
	$data .= '<div class="form-group"><div class="input-group"><div class="input-group-addon">真实姓名</div><input type="text" id="pay_name" value="'.$rows['username'].'" class="form-control" required/></div></div>';
	$data .= '<input type="submit" id="save" onclick="saveInfo('.$id.')" class="btn btn-primary btn-block" value="保存">';
	$result=array("code"=>0,"msg"=>"succ","data"=>$data,"pay_type"=>$rows['type']);
	exit(json_encode($result));
break;
case 'tuisong_save':
    $token = "5313902856:AAEIQRhZIH6DOc2itLEig_D9ojdtOCkiAgY";
	$id=intval($_POST['id']);
	$rows=$DB->getRow("select * from pre_tousu where id='$id' limit 1");
	if(!$rows){
	     exit('{"code":-1,"msg":"没有找到记录！"}');
	}else{
	    if($rows['status']=="1"){
	        exit('{"code":-1,"msg":"这个投诉已经推送给商户了！"}');
	    }
	    $uid = $rows['uid'];
	    $uid_info = $DB->getRow("select chatid from pre_botsettle where merchant ='".$uid."' limit 1");
        if(!$uid_info){
             exit('{"code":-1,"msg":"异常，没有找到用户信息！"}');
        }
	    $shangyou_chatid = $uid_info['chatid'];
	    
	    
	    //执行记录：
	    $sds=$DB->exec("update `pre_tousu` set `status`='1' where `id`='$id'");
	    $ss = "update `pre_tousu` set `status`='1' where `id`='$id'";
	    if(!$sds){
	        exit('{"code":-1,"msg":"推送异常--修改状态错误！"'.$ss.'}');
	    }
	    //投诉扣除：
	    $today = date("Y-m-d");
	    $money = $rows['money'];
	    $result = $DB->exec("INSERT INTO `pre_usertousu` (`pid`, `money`, `date`, `chatid`) VALUES ('{$uid}', '{$money}', '{$today}', '{$shangyou_chatid}')");
        if(!$result){
          $sds=$DB->exec("update `pre_tousu` set `status`='0' where `id`='$id'");

	        exit('{"code":-1,"msg":"推送异常---记录投诉金额错误！"}');
	    }
	    
	    
	    //先执行扣除，记录。如果成功再推送给商户信息：

	    $shangyou_msg ="有1个订单被投诉并要求退款，目前已退款给客户\r\n\r\n";
	    $tousuorder = $rows['tousuorder'];
	    $order = $rows['order'];
	    $out_trade_no = $rows['out_trade_no'];
	    $money = $rows['money'];
	    $shangyou_msg .="投诉编号：<b>".$tousuorder."\r\n</b>系统订单号:<b>".$order."\r\n</b>商户订单号：<b>".$out_trade_no."\r\n</b>投诉金额:<b>".$money."</b>元\r\n已从下发金额中扣除了<b>".$money."</b>元投诉退款
\r\n可在商户后台-投诉管理中查看\r\n可在本群菜单-投诉管理中查看";
	    $targetFile = $rows['tousuimage'];
	            $parameter = array(
                    'chat_id' => $shangyou_chatid,
                    'photo'=>"https://test.freewing1688.xyz/".$targetFile,
                    'caption'=>$shangyou_msg,
                     'parse_mode' => 'HTML',
                );
         
	    //执行推送消息：
	    $link = 'https://api.telegram.org/bot' . $token . '';
        $url =$link. "/sendPhoto?";
        $data_string =json_encode($parameter);
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
        ob_end_clean();
        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // return array($return_code, $return_content);
        exit('{"code":0,"msg":"推送成功！"}');
	    
	}

break;
case 'shangyouqunkan_save':
	$id=intval($_POST['id']);
	$conditionsText=trim(daddslashes($_POST['conditionsText']));
	$reply_content=trim(daddslashes($_POST['reply_content']));

	$createtime = time();
 

	 
	
	if($id){
		$sds=$DB->exec("update `pre_shangyouhuifu` set `conditions`='$conditionsText',`reply_content`='$reply_content' where `id`='$id'");
		if($sds!==false){
		    exit('{"code":0,"msg":"修改监听条件信息成功！"}');
		}else{
		    exit('{"code":-1,"msg":"修改监听条件信息失败！'.$DB->error().'"}');    
		}
	}else{
	    	 $shangyou_info =$DB->getRow("select * from pre_shangyouhuifu where conditions='$conditionsText' limit 1");
        	if($shangyou_info){
        	     exit('{"code":-1,"msg":"这个条件已经记录了！"}');
        	}
        	
	    $result = $DB->exec("INSERT INTO `pre_shangyouhuifu` (`conditions`,`reply_content`,`createtime`) VALUES ('{$conditionsText}', '{$reply_content}', '{$createtime}')");
        if($result!==false){
		    exit('{"code":0,"msg":"添加监听条件信息成功！"}');
		}else{
		    exit('{"code":-1,"msg":"添加监听条件信息`失败！'."INSERT INTO `pre_shangyouhuifu` (`conditions`,`reply_content`,'createtime') VALUES ('{$conditionsText}', '{$reply_content}', '{$createtime}')".'"}');    
		}
	}

	
break;
case 'tousu_save':
	$id=intval($_POST['id']);
	$order=trim(daddslashes($_POST['order']));
	$out_trade_no=trim(daddslashes($_POST['out_trade_no']));
	$money=trim(daddslashes($_POST['money']));
	$tousuimage = trim(daddslashes($_POST['tousuimage']));
	$tousuorder= date("YmdHis").rand(1000,9999);
	$createtime = time();
	$tousu_info =$DB->getRow("select * from pre_tousu where order='$order' limit 1");
	if($tousu_info){
	     exit('{"code":-1,"msg":"这个订单已经记录投诉了！"}');
	}
	
	$order_info =$DB->getRow("select * from pre_order where trade_no='$order' limit 1");
	if($order_info['out_trade_no'] !=$out_trade_no){
	     exit('{"code":-1,"msg":"商户订单号填写不正确！"}');
	}
	if(floor($money) != floor($order_info['money'])){
	    exit('{"code":-1,"msg":"金额与订单不匹配，请先核对！"}');
	}
	if($order_info['status'] !='1'){
	   exit('{"code":-1,"msg":"这个订单后台状态是未支付的，请先核对！"}');

	}
	//查一下上游：
	$admin = $_COOKIE['admin_user'];
	$paytype = $order_info['type'];
	$channel_id = $order_info['channel'];
	$channel_info = $DB->getRow("select * from pre_channel where id='$channel_id' limit 1");
	$channel = $channel_info['name'];
	
	$uid= $order_info['uid'];
	
	if($id){
		$sds=$DB->exec("update `pre_tousu` set `channel`='$channel',`paytype`='$paytype',`admin`='$admin',`order`='$order',`uid`='$uid',`out_trade_no`='$out_trade_no',`tousuimage`='$tousuimage',`money`='$money' where `id`='$id'");
		if($sds!==false){
		    exit('{"code":0,"msg":"修改投诉信息成功！"}');
		}else{
		    exit('{"code":-1,"msg":"修改投诉信息失败！'.$DB->error().'"}');    
		}
	}else{
	    $result = $DB->exec("INSERT INTO `pre_tousu` (`channel`,`paytype`,`admin`,`tousuorder`, `order`, `out_trade_no`, `money`, `createtime`, `tousuimage`, `uid`) VALUES ('{$channel}', '{$paytype}', '{$admin}', '{$tousuorder}', '{$order}', '{$out_trade_no}', '{$money}',  '{$createtime}', '{$tousuimage}', '{$uid}')");
        if($result!==false){
		    exit('{"code":0,"msg":"添加投诉信息成功！"}');
		}else{
		    exit('{"code":-1,"msg":"添加投诉信息失败！'.$DB->error().'"}');    
		}
	}

	
break;
case 'settle_save':
	$id=intval($_POST['id']);
	$pay_type=trim(daddslashes($_POST['pay_type']));
	$pay_account=trim(daddslashes($_POST['pay_account']));
	$pay_name=trim(daddslashes($_POST['pay_name']));
	$sds=$DB->exec("update `pre_settle` set `type`='$pay_type',`account`='$pay_account',`username`='$pay_name' where `id`='$id'");
	if($sds!==false)
		exit('{"code":0,"msg":"修改记录成功！"}');
	else
		exit('{"code":-1,"msg":"修改记录失败！'.$DB->error().'"}');
break;
case 'paypwd_check':
	if(isset($_SESSION['paypwd']) && $_SESSION['paypwd']==$conf['admin_paypwd'])
		exit('{"code":0,"msg":"ok"}');
	else
		exit('{"code":-1,"msg":"error"}');
break;
case 'paypwd_input':
	$paypwd=trim($_POST['paypwd']);
	if(!$conf['admin_paypwd'])exit('{"code":-1,"msg":"你还未设置支付密码"}');
	if($paypwd == $conf['admin_paypwd']){
		$_SESSION['paypwd'] = $paypwd;
		exit('{"code":0,"msg":"ok"}');
	}else{
		exit('{"code":-1,"msg":"支付密码错误！"}');
	}
break;
case 'paypwd_reset':
	unset($_SESSION['paypwd']);
	exit('{"code":0,"msg":"ok"}');
break;
case 'set':
	foreach($_POST as $k=>$v){
		saveSetting($k, $v);
	}
	$ad=$CACHE->clear();
	if($ad)exit('{"code":0,"msg":"succ"}');
	else exit('{"code":-1,"msg":"修改设置失败['.$DB->error().']"}');
break;
case 'setGonggao':
	$id=intval($_GET['id']);
	$status=intval($_GET['status']);
	$sql = "UPDATE pre_anounce SET status='$status' WHERE id='$id'";
	if($DB->exec($sql))exit('{"code":0,"msg":"修改状态成功！"}');
	else exit('{"code":-1,"msg":"修改状态失败['.$DB->error().']"}');
break;
case 'delGonggao':
	$id=intval($_GET['id']);
	$sql = "DELETE FROM pre_anounce WHERE id='$id'";
	if($DB->exec($sql))exit('{"code":0,"msg":"删除公告成功！"}');
	else exit('{"code":-1,"msg":"删除公告失败['.$DB->error().']"}');
break;
case 'getServerIp':
	$ip = getServerIp();
	exit('{"code":0,"ip":"'.$ip.'"}');
break;
case 'epayurl':
	$id = intval($_GET['id']);
	$conf['payapi']=$id;
	if($url = pay_api()){
		exit('{"code":0,"url":"'.$url.'"}');
	}else{
		exit('{"code":-1}');
	}
break;
case 'iptype':
	$result = [
	['name'=>'0_X_FORWARDED_FOR', 'ip'=>real_ip(0), 'city'=>get_ip_city(real_ip(0))],
	['name'=>'1_X_REAL_IP', 'ip'=>real_ip(1), 'city'=>get_ip_city(real_ip(1))],
	['name'=>'2_REMOTE_ADDR', 'ip'=>real_ip(2), 'city'=>get_ip_city(real_ip(2))]
	];
	exit(json_encode($result));
break;
default:
	exit('{"code":-4,"msg":"No Act"}');
break;
}