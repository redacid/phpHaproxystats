#!/usr/bin/php -q
<?php
ini_set('error_reporting', E_ERROR);
$login="user";
$pass="pass";
$url="https://domain.com/haproxy/;csv;norefresh";
//# pxname,svname,qcur,qmax,scur,smax,slim,stot,bin,bout,dreq,dresp,ereq,econ,eresp,wretr,wredis,status,weight,act,bck,chkfail,chkdown,lastchg,downtime,qlimit,pid,iid,sid,throttle,lbtot,tracked,type,rate,rate_lim,rate_max,check_status,check_code,check_duration,hrsp_1xx,hrsp_2xx,hrsp_3xx,hrsp_4xx,hrsp_5xx,hrsp_other,hanafail,req_rate,req_rate_max,req_tot,cli_abrt,srv_abrt,comp_in,comp_out,comp_byp,comp_rsp,lastsess,last_chk,last_agt,qtime,ctime,rtime,ttime,agent_status,agent_code,agent_duration,check_desc,agent_desc,check_rise,check_fall,check_health,agent_rise,agent_fall,agent_health,addr,cookie,mode,algo,conn_rate,conn_rate_max,conn_tot,intercepted,dcon,dses

//echo $argv[1];

#./get-haproxy-stats.php srv-status redis_6380 app33 status

if (php_sapi_name() == "cli") {
	$cmd=$argv[1];
        $name=$argv[2];
        $srv=$argv[3];
	$column=$argv[4];
	$rn="\r\n";
} else {
	//$cmd=$_GET['command'];
        //$name=$_GET['name'];
        //$srv=$_GET['srv'];
        //$column=$_GET['column'];
        //$rn="<br />";
}


//echo $cmd.$rn;
//echo $name.$rn;
//echo $srv.$rn;
//echo $column.$rn;

function get_service_state($cmd,$name,$srv,$column) {
//GLOBAL $cmd;
GLOBAL $login;
GLOBAL $pass;
GLOBAL $url;
GLOBAL $rn;
$curlInit = curl_init($url);
$proxy="127.0.0.1:8118";
//curl_setopt($curlInit, CURLOPT_PROXY, $proxy);
curl_setopt($curlInit,CURLOPT_CONNECTTIMEOUT,10);
curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);
curl_setopt($curlInit,CURLOPT_USERPWD,$login.':'.$pass);
$response = curl_exec($curlInit);
curl_close($curlInit);
//echo $response;
$lines = preg_split('/\\r\\n?|\\n/', $response);
$headers = explode(",",$lines[0]);
#echo $lines[0];
//var_dump($headers);
unset($lines[0]);
$csv = array_map('str_getcsv', $lines);
$data = array();
// prepare data
foreach($lines as $line){
	$t = explode(",",$line);
	$items= array();
	foreach($t as $item_id=>$item_value){
		$items[$headers[$item_id]] = $item_value;
	}
$data[$t[0]][$t[1]] = $items;
//var_dump($data[$t[0]][$t[1]]); 
}

switch ($cmd) {
    case "srv-status":
        return $data[$name][$srv][$column].$rn;
	break;
    case "srv-front":
	return $data[$name]['FRONTEND']['scur'].$rn;
        break;
    case "srv-back":
        return $data[$name]['BACKEND']['scur'].$rn;
        break;
    default:
       return "unknown CMD param";
}


//return $data[$name][$srv][$column].$rn;

}
echo get_service_state($cmd,$name,$srv,$column);
?>
