<?php
/**
 * 结算列表
**/
include("../includes/common.php");
if($islogin==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");

function display_type($type){
	if($type==1)
		return '支付宝';
	elseif($type==2)
		return '微信';
	elseif($type==3)
		return 'QQ钱包';
	elseif($type==4)
		return '银行卡';
	else
		return 1;
}

function display_status($status, $id){
	if($status==0)
		return '<font color=green>待处理</font>';
	elseif($status==1)
		return '<font color=orange>已回调处理</font>';
	elseif($status==2)
			return '<font color=orange>并未支付</font>';
	else
		return '<font color=blue>未知状态</font>';
}


?>
	<form name="form1" id="form1">
	  <div class="table-responsive">
<?php echo $con?>
        <table class="table table-striped table-bordered table-vcenter">
            <!--<th>商户号</th>-->
          <thead><tr><th>商户ID</th><th>最近三小时的成率</th><th>最近30分钟的成率</th><th>今日订单总量</th><th>今日总成率</th><th>今日跑量</th><th>昨日订单总量</th><th>昨日总成率</th><th>昨日跑量</th></tr></thead>
          <tbody>
<?php
$array = array();
$pagesize=30;
$pages=ceil($numrows/$pagesize);
$page=isset($_GET['page'])?intval($_GET['page']):1;
$offset=$pagesize*($page - 1);

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
            $zuoriding = $DB->getAll("select status,money from pay_order where uid='".$vs['uid']."' and date='" . $todayzuori . "'"); 
            
            $zuori_zong =0;
            $zuori_zong_cheng =0;
            $zuori_zong_sum=0;
            if($zuoriding){
                $zuori_zong = count($zuoriding);
                 foreach ($zuoriding as $skss=>$svss){
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
        
        
            
            $array[$ks]['jindingdan'] = $zong_count;
            $array[$ks]['zong'] = (round($zong_count_chenggong/$zong_count,2)*100)."%";
            $array[$ks]['paoliang'] = $zong_paoliang;
            
             $array[$ks]['zuodingdan'] = $zuori_zong;
            if($zuori_zong>0){
                 $array[$ks]['zuori'] = (round($zuori_zong_cheng/$zuori_zong,2)*100)."%";
            }else{
                 $array[$ks]['zuori'] = "0%";
            }
            $array[$ks]['zuoripaoliang'] = $zuori_zong_sum;
        }


// 自定义排序函数
function sort_by_paoliang($a, $b) {
    if ($a['paoliang'] == $b['paoliang']) {
        return 0;
    }
    // 降序排列，如果需要升序排列则交换返回-1和1的位置
    return ($a['paoliang'] < $b['paoliang']) ? 1 : -1;
}
function sort_by_paoliang_zuori($a, $b) {
    if ($a['zuoripaoliang'] == $b['zuoripaoliang']) {
        return 0;
    }
    // 降序排列，如果需要升序排列则交换返回-1和1的位置
    return ($a['zuoripaoliang'] < $b['zuoripaoliang']) ? 1 : -1;
}
// 使用usort进行排序


if($_GET['column']=="2"){

     usort($array, 'sort_by_paoliang_zuori');  
}else{
       //今日：
       if(count($array)>0){
               usort($array, 'sort_by_paoliang');

       }
}

// $rs=$DB->query("SELECT * FROM pre_tianshibudan WHERE{$sql} order by id desc limit $offset,$pagesize");
// while($res = $rs->fetch())
foreach ($array as $ks=>$res)
{
    //根据单号需要
    // $ordersn = explode("\n",$res['ordersn']);
    $res['createtime'] = date("Y-m-d H:i:s",$res['createtime']);
    if($res['updatetime']>0){
        $res['updatetime'] = date("Y-m-d H:i:s",$res['updatetime']);
    }else{
        $res['updatetime'] ="待处理";
    }
    $new_msg = "";
    if(!empty($res['order_sn'])){
        //处理：
        //$ordersn_arr = explode(",",$res['order_sn']);
        //foreach ($ordersn_arr as $ks=>$sv){
            $rse=$DB->getAll("SELECT * FROM pre_budandetail WHERE tianshibudan_id={$res['id']}");
            foreach ($rse as $ks=>$vs){
                //查询支付渠道：
                $channem_info = $DB->getRow("SELECT * FROM pre_channel WHERE id={$vs['channel']} limit 1");
                
                $new_msg .=$vs['order_sn']."=>".$channem_info['name']."=>".$vs['money']."<br>";
            }
        //}
    }
    
echo '<tr><td><b>'.$res['uid'].'</b></td><td><b>'.$res['sanshi'].'</b></td><td>'.$res['sanfen'].'</td><td>'.$res['jindingdan'].'</td><td>'.$res['zong'].'</td><td>'.$res['paoliang'].'</td><td>'.$res['zuodingdan'].'</td><td>'.$res['zuori'].'</td><td>'.$res['zuoripaoliang'].'</td></tr>';
}
?>
          </tbody>
        </table>

      </div>
	 </form>
<?php
// echo'<div class="text-center"><ul class="pagination">';
// $first=1;
// $prev=$page-1;
// $next=$page+1;
// $last=$pages;
// if ($page>1)
// {
// echo '<li><a href="javascript:void(0)" onclick="listTable(\'page='.$first.$link.'\')">首页</a></li>';
// echo '<li><a href="javascript:void(0)" onclick="listTable(\'page='.$prev.$link.'\')">&laquo;</a></li>';
// } else {
// echo '<li class="disabled"><a>首页</a></li>';
// echo '<li class="disabled"><a>&laquo;</a></li>';
// }
// $start=$page-10>1?$page-10:1;
// $end=$page+10<$pages?$page+10:$pages;
// for ($i=$start;$i<$page;$i++)
// echo '<li><a href="javascript:void(0)" onclick="listTable(\'page='.$i.$link.'\')">'.$i .'</a></li>';
// echo '<li class="disabled"><a>'.$page.'</a></li>';
// for ($i=$page+1;$i<=$end;$i++)
// echo '<li><a href="javascript:void(0)" onclick="listTable(\'page='.$i.$link.'\')">'.$i .'</a></li>';
// if ($page<$pages)
// {
// echo '<li><a href="javascript:void(0)" onclick="listTable(\'page='.$next.$link.'\')">&raquo;</a></li>';
// echo '<li><a href="javascript:void(0)" onclick="listTable(\'page='.$last.$link.'\')">尾页</a></li>';
// } else {
// echo '<li class="disabled"><a>&raquo;</a></li>';
// echo '<li class="disabled"><a>尾页</a></li>';
// }
// echo'</ul></div>';
