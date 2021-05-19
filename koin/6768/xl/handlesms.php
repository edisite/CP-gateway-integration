<?php

// Tapping program
// msisdn=<msisdn>&transid=<trxid>&trx_time=<trxdate>&sms=<sms>&substype=<10;20;30>
$shortcode	= "6768";
$msisdn		= $_GET["msisdn"];
$transid	= $_GET["transid"];
$sms		= $_GET["sms"];
$shortname 	= $_GET["shortname"];
$ip			= $_SERVER["REMOTE_ADDR"];
$trx_time	= date("YmdHis");
$substype	= "20";
$operator	= "xl"; 

	$headmsisdn = substr($msisdn, 0, 3);
	
		$modir = "/opt/apps/6768/queue/xl/in/mo2/";
		$moerr = "/opt/apps/6768/queue/xl/in/error/";
		$logmodir = "/opt/apps/6768/log/xl/receiver/mo/";
		$log_ins = "'".$msisdn."';'".$trx_time."';'".$shortcode."';'".$transid."';'".$substype."';'".$sms."'";

		createLogOut($ip, $transid, "MO Incoming IP ");
		createLogOut($log_ins, $transid, "Creating MO ");

		$ckey = strpos($sms, " ");
		$panjang = strlen($ckey);
		if ($panjang == 0)
		{
			$keyword = strtolower($sms);
		}
		else
		{
			$keyword = strtolower(substr($sms, 0, $ckey));
			$message = strtolower(trim(substr($sms, $ckey)));
		}

		
			$queryString = "msisdn=".$msisdn."&transid=".$transid."&sms=".rawurlencode($sms)."&trxdate=".$trx_time."&substype=".$substype."&sc=6768&operator=".$operator;
			$url = "http://10.1.1.75:7007/mo?".$queryString;
			createLogOut($url, $transid, " URL : ");
			$buffer = "";
			$handle = fopen($url, "r");
			if ($handle)
			{
				while (!feof($handle)) { $buffer .= fgets($handle, 4096);	}
				fclose($handle);
				$is_sending = trim($buffer);
				createLogOut($handle,$transid, " Success : ");
			}
			else
			{
				
				createLogOut($handle,$transid, " Error : ");
			}


			$xml = simplexml_load_string($is_sending);
			$hasil_sending = $xml->STATUS;	
						
			if ($hasil_sending == "0") 
			{
				createLogOut($hasil_sending,$transid, " Success : ");
			}
			else 
			{
				createLogOut($hasil_sending,$transid, " Fail : ");
				createInCRM($log_ins,$transid,$shortcode);
			}
		
		echo "<?xml version='1.0' ?><MO><STATUS>1</STATUS><TRANSID>".$transid."</TRANSID><MSG>Message processed successfully</MSG></MO>";
	

function createLogOut($param, $transid_s, $action)
{
	$tanggal_access = date("Ymd");
	$dir_acc = "/opt/apps/6768/log/xl/receiver/mo/";
	$file_log = $dir_acc."Recv".$tanggal_access.".log";
	chmod($file_log, 0777);
	$handle = fopen($file_log, "a+");
		fprintf($handle, "%s", date("Y-m-d H:i:s")."|".$transid_s."|".$action.": ".$param."\n");
	fclose($handle);
}

function createInPull($param, $transid_s, $sc)
{
	$tanggal_access = date("Ymd");
	$dir_accmo = "/opt/apps/6768/queue/xl/in/mo/";
	$file_log = $dir_accmo.$transid_s."_".date("YmdHis")."_".rand().".dat";
	$handle = fopen($file_log, "a");
	fprintf($handle, "%s", $param."\n");
	fclose($handle);
	createLogOut($file_log,$transid_s, "Successfully created on ");
}



function createLogOut_dr($param, $transid_s, $action)
{
	$tanggal_access = date("Ymd");
	$dir_acc = "/opt/apps/6768/log/xl/receiver/dr/";
	$file_log = $dir_acc."DR_".$tanggal_access.".log";
	$handle = fopen($file_log, "a");
	fprintf($handle, "%s", date("Y-m-d H:i:s ;")."|".$transid_s."|".$action.":".$param."\n");
	fclose($handle);
}

function createInCRM($param, $transid_s, $sc) 
{
	$tanggal_access = date('Ymd');	
	$dir_acc = "/opt/apps/6768/queue/xl/in/error/";
	$file_log = $dir_acc.$transid_s."_".date("YmdHis")."_".rand().".dat";	
	$handle = fopen($file_log, 'a');
	fprintf($handle, "%s", $param."\n");
	fclose($handle);
	createLogOut($file_log,$transid_s, "Successfully created on ");
}
?>
