<?php
/**
 * 订单列表
**/
include("../includes/common.php");
if($islogin==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");


$paytype = [];
$type_select = '';
$type_select2 = '';
$rs = $DB->getAll("SELECT * FROM pre_channel where topzidingyi !='' group by topzidingyi"); 
foreach($rs as $row){
    if(!empty($row['topzidingyi'])){
        $chang = $row['topzidingyi'];
    }else{
        $chang = $row['name'];
    }
	$paytype[$row['id']] = $chang;
	$type_select .= '<option value="'.$row['id'].'">'.$chang.'</option>';
		$type_select2 .= '<option value="'.$chang.'">'.$chang.'</option>';
}
unset($rs);

$sqls="";
$links='';
$sql = " 1=1";

if(!empty($_GET['addtime3']) && $_GET['addtime3']!="undefined"){
    
    $time_arr = explode(" - ",$_GET['addtime3']);
    
    $time1= strtotime($time_arr[0]);
    $time2= strtotime($time_arr[1]);
    
    $sql .= " and addtime >='".$time1."' and addtime<='".$time2."'";
    $links .= "&addtime=".$_GET['addtime3'];
}
if(!empty($_GET['typelist']) || $_GET['typelist']=="0"){
    $typelist= $_GET['typelist']; 
  
    $sql .= " and typelist=".$typelist;
    $links .= "&typelist=".$typelist;
}
// if($_GET['tongjifangshis']!=""){
//     $tongjifangshis= $_GET['tongjifangshis']; 
  
//     $sql .= " and tongjilist='".$tongjifangshis."'";
    
// }
if(!empty($_GET['channel_id2'])){
    $channel_id2= $_GET['channel_id2'];   
    $arr_change_str = "";
    if($channel_id2=="10001"){
        $arr_change = array();
        //先查所有不符合条件的：
        $r2s_allchannel = $DB->getAll("SELECT id,channel_id FROM pre_jizhang"); 
        foreach ($r2s_allchannel as $ksvv=>$sqvv){
            $channel_idsa = $sqvv['channel_id'];
            $r2s = $DB->getAll("SELECT id FROM pre_channel where id ='$channel_idsa'"); 
         
            if(!$r2s){
                
                $arr_change[] = $channel_idsa; 
            }
        }
        if(count($arr_change)>0){
            $arr_change_str = implode(",",$arr_change);
        }else{
            $arr_change_str ="asqsas";
        }
        
        $sql .= " and channel_id in (".$arr_change_str.")";
        $links .= "&channel_id2=".$channel_id2;
    }else{
        $r2s = $DB->getAll("SELECT id FROM pre_channel where topzidingyi ='$channel_id2'"); 
        $arr_change = array();
        foreach ($r2s as $ks=>$sq){
            $arr_change[] = $sq['id'];
        }
       
        $arr_change_str = implode(",",$arr_change);
        $sql .= " and channel_id in (".$arr_change_str.")";
        $links .= "&channel_id2=".$channel_id2;
    }
   
    
    
}
$sql .= " order by addtime desc";
$numrows=$DB->getColumn("SELECT count(*) from pre_jizhang  WHERE{$sql}");

$link=$links;
$sql_cha = "SELECT t1.id, t1.channel_id, t1.residuemoney
FROM pay_jizhang t1
JOIN (
    SELECT channel_id, MAX(addtime) AS max_addtime
    FROM pay_jizhang
    GROUP BY channel_id
) t2 ON t1.channel_id = t2.channel_id AND t1.addtime = t2.max_addtime;
";
$zuizhong = $DB->getAll($sql_cha); 
$jizhangya_arr = array();
$all_jieyu = 0;
foreach ($zuizhong as $kvs=>$ves){
    $all_jieyu +=$ves['residuemoney'];
    $jizhangya_arr[$ves['channel_id']] = $ves['residuemoney'];
}
?>

<div>
    <h4>剩余预付合计：<span style="color:red"><?php echo $all_jieyu?></span></h4><br>
    <?php
   foreach ($jizhangya_arr as $ksp=>$vsp){
        echo "<h4>".$paytype[$ksp].":".$vsp."</h4><br>";
    }
    ?>
    
</div>
	  <form name="form1" id="form1">
	  <div class="table-responsive">
<?php echo $con?>
        <table class="table table-striped table-bordered table-vcenter">
            <!--<th>商户号<br/>网站域名</th>-->
           <thead><tr><th>ID</th><th>操作时间</th><th>操作行为</th><th>操作金额</th><th>计算公式</th><th>上游编号</th><th>预付结余</th><th>统计方式</th><th>统计时间</th><th>创建时间</th><th>详情</th><th>汇率</th><th>备注</th><th>操作员</th><th>操作</th></tr></thead>
          <tbody>
              <input id="sql_l" type="hidden" value="<?php echo $sql?>">      
<?php
$pagesize=30;
$pages=ceil($numrows/$pagesize);

$page=isset($_GET['page'])?intval($_GET['page']):1;
$offset=$pagesize*($page - 1);
$rs=$DB->query("SELECT * FROM pre_jizhang  WHERE{$sql} limit $offset,$pagesize");
$typelist = array("预付Usdt","投诉扣除(元)","余额扣除(元)","预退付","上游补钱",'实时下发Usdt');
     $typebian = array("+","-");
     $bianrates = array("元","Usdt");
     $tongjis = array("完成时间","创建时间");
if($rs){
    while($res = $rs->fetch())
{    
    $shangyouyuer =0;
    
     $addtime = date("Y-m-d H:i:s",$res['addtime']);

     $createtime = date("Y-m-d H:i:s",$res['createtime']);
     $data ="";
     if($res['typelist']=="2"){
            $start_time = date('Y-m-d H:i:s',$res['tongjistarttime']);
	        $end_time = date('Y-m-d H:i:s',$res['tongjiendtime']);
	        $apptype = $res['channel_id'];
            $tongjilist = $res['tongjilist'];
            $caozuolist = $res['typelist'];
            $moneys = $res['money'];
            $plugin=$DB->getRow("select `topzidingyi` from `pre_channel` where `id`='{$apptype}' limit 1");

	    $topzidingyi = $plugin['topzidingyi'];
	
	    $channel_info=$DB->getAll("select `id`,`name` from `pre_channel` where `topzidingyi`='{$topzidingyi}'");
	
	    $channel_arr = array();
	    $channel_arr_id = array();
    	foreach($channel_info as $key=>$veal){
	        $channel_arr_id[]=$veal['id'];
		    $channel_arr[$veal['id']]['name']=$veal['name'];
        }
	        $all_channel = implode(",",$channel_arr_id);
	
	    if(!empty($all_channel)){
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
    		$select = "";
    $jizhang_id = $res['id'];
        	if(count($order_info)>0){
        	    foreach($channel_arr as $key=>$veal){
        	        //查询当前的费率 显示出来：
        	       
        	        $jizhanginfo=$DB->getRow("select `rate`,`id` from `pre_jizhangrate` where `channal_id`='{$key}' and `jizhang_id`='{$jizhang_id}' limit 1");
        	        if($jizhanginfo){
        	            $dangqan_rate = $jizhanginfo['rate'];
        	            $dangqan_rate_id = $jizhanginfo['id'];
        	        }else{
        	            $dangqan_rate = 100;
        	            $dangqan_rate_id = 0;
        	        }
        	        
        	        if($dangqan_rate>0){
        	              $shangyouyuer += $veal['money']*(1-($dangqan_rate/100));

        	        }else{
        	            $shangyouyuer +=$veal['money'];
        	        }

                    if($veal['money']>0){
                        $veal['money'] = $veal['money'];
                        
                    }else{
                        $veal['money']=0;
                    }
        		     $select .= '<label><span>'.$veal['name'].':</span><span>'.$veal['money'].'('.$dangqan_rate.')</span></label> <a class="btn btn-xs btn-info" data-feilv='.$dangqan_rate.' onclick="xiugairate('.$dangqan_rate.','.$key.','.$dangqan_rate_id.','.$jizhang_id.')">录入费率</a>'."<br>";
        		}
        	}else{
        	    $select="";
        	}
      
    
        
    
    	    $data .=$select;
    	    $data .="<br>".'<label><span>合计:</span><span>'.$all_money.'</span></label>&nbsp;'."\r\n"; 
    
    	    $chaer = sprintf("%.2f",($shangyouyuer-$moneys));
    	    $data .= "<br>".'<label style="color:red"><span>差额:</span><span>'.$chaer.'</span></label>&nbsp;';
    	
    	    $data .= "<br>".'<label><span>上游余额:</span><span>'.$shangyouyuer.'</span></label>&nbsp;'; 
            $detial = $data;
	    }else{
	         $detial = "";
	    }
	
       
    	
        
        
        $addtime2 = date("Y-m-d H:i:s",$res['tongjistarttime']);
        $endtime2 = date("Y-m-d H:i:s",$res['tongjiendtime']);
        $tongjifangshi = $tongjis[$res['tongjilist']];
        $tongjitime = $addtime2."至".$endtime2;
     }else{
          $detial = "";
           $tongjitime = "";
            $tongjifangshi = "";
     }
    
    if($res['istrue']=="0"){
        $istrue = '<span style="color:blue">正确</span>';
    }else{
        $istrue ='<span style="color:red">有误</span>';
    }
if($res['feilv']>0){
   $bianhou =  $res['money']*$res['feilv'];
   $pp ='<span style="color:red">'.$typebian[$res['typebian']]."</span>".$res['money']."(".$bianrates[$res['bianrates']].')<br>'.$bianhou.'元';
}else{
     $bianhou =  $res['money'];
     $pp ='<span style="color:red">'.$typebian[$res['typebian']]."</span>".$res['money']."(".$bianrates[$res['bianrates']].')';
}    
if(empty($res['adminname'])){
    $adminname ="未知";
}else{
    $adminname =$res['adminname'];
}

    
echo '<tr>
    <td><input type="checkbox" name="checkbox[]" id="list1" value="'.$res['id'].'" onClick="unselectall1()"><b>'.$res['id'].'</b></td>
    <td>'.$addtime.'</td>
    <td>'.$typelist[$res['typelist']].'</td>
    
    <td>'.$pp.'</td>

<td>'.$res['gongshi'].'</td>
    <td>'.$paytype[$res['channel_id']].'</td>
       
    <td>'.$res['residuemoney'].'(元)'.$istrue.'</td>
    
    
       <td>'.$tongjifangshi.'</td>
      <td>'.$tongjitime.'</td>
      <td>'.$createtime.'</td>
      <td>'.$detial.'</td>
        <td>'.$res['feilv'].'</td>
       <td>'.$res['remakes'].'</td>
         <td>'.$adminname.'</td>
     <td>
    <a class="btn btn-xs btn-info" onclick="editframe('.$res['id'].')">编辑</a>&nbsp;
    <a class="btn btn-xs btn-danger" onclick="delItem('.$res['id'].')">删除</a>&nbsp;
   
    
    </td></tr>';
}
}   



?>
          </tbody>
        </table>
<input name="chkAll1" type="checkbox" id="chkAll1" onClick="this.value=check1(this.form.list1)" value="checkbox">&nbsp;全选&nbsp;
<select name="shanchuall"><option selected>操作账单</option><option value="1">全部删除</option></select><!--<option value="4">删除订单</option>-->
<button type="button" onclick="operation()">确定</button>
      </div>
	 </form>
	
<?php
echo'<div class="text-center"><ul class="pagination">';
$first=1;
$prev=$page-1;
$next=$page+1;
$last=$pages;
if ($page>1)
{
echo '<li><a href="javascript:void(0)" onclick="listTable(\'page='.$first.$link.'\')">首页</a></li>';
echo '<li><a href="javascript:void(0)" onclick="listTable(\'page='.$prev.$link.'\')">&laquo;</a></li>';
} else {
echo '<li class="disabled"><a>首页</a></li>';
echo '<li class="disabled"><a>&laquo;</a></li>';
}
$start=$page-10>1?$page-10:1;
$end=$page+10<$pages?$page+10:$pages;
for ($i=$start;$i<$page;$i++)
echo '<li><a href="javascript:void(0)" onclick="listTable(\'page='.$i.$link.'\')">'.$i .'</a></li>';
echo '<li class="disabled"><a>'.$page.'</a></li>';
for ($i=$page+1;$i<=$end;$i++)
echo '<li><a href="javascript:void(0)" onclick="listTable(\'page='.$i.$link.'\')">'.$i .'</a></li>';
if ($page<$pages)
{
echo '<li><a href="javascript:void(0)" onclick="listTable(\'page='.$next.$link.'\')">&raquo;</a></li>';
echo '<li><a href="javascript:void(0)" onclick="listTable(\'page='.$last.$link.'\')">尾页</a></li>';
} else {
echo '<li class="disabled"><a>&raquo;</a></li>';
echo '<li class="disabled"><a>尾页</a></li>';
}
echo'</ul></div>';
