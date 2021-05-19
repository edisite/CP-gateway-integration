<?php

// Tapping program
// msisdn=<msisdn>&transid=<trxid>&trx_time=<trxdate>&sms=<sms>&substype=<10;20;30>
$shortcode	= "6768";
$msisdn		= $_GET["msisdn"];
$transid	= $_GET["transid"];
$status		= $_GET["status"];
$serviceid	= $_GET["serviceid"];
$ip			= $_SERVER["REMOTE_ADDR"];
$trx_time	= date("YmdHis");
$operator	= "xl";

	
		$logmodir = "/opt/apps/6768/log/xl/receiver/dr/";
		$log_ins = "'".$msisdn."';'".$trx_time."';'".$shortcode."';'".$transid."';'".$serviceid."';'".$status."'";

		createLogOut($ip, $transid, "DR Incoming IP ");
		createLogOut($log_ins, $transid, "Creating DR ");

				
			$queryString = "msisdn=".$msisdn."&dest=".$msisdn."&trxid=".$transid."&status=".$status."&trxdate=".$trx_time."&serviceid=".$serviceid."&sc=7677&operator=".$operator;
			$url = "http://10.1.1.75:7007/dr?".$queryString;
			createLogOut($url, $transid, "URL ");
			$buffer = "";
			$handle = fopen($url, "r");
			if ($handle)
			{
				while (!feof($handle)) { $buffer .= fgets($handle, 4096);	}
				fclose($handle);
				$is_sending = trim($buffer);
				createLogOut($is_sending, $transid, "Result ");

				$dbCN = mysql_connect("10.1.1.75","danial","d4n14lk1t4");
                                mysql_select_db('mdw6768_xl');

				$SQL = "UPDATE sms_out_exel SET drstatus = '".$status."', drdtm = NOW() WHERE trxid = '".$transid."' AND msisdn = '".$msisdn."'";
                                createLogOut($SQL, $transid, "update DB ");
                                mysql_query($SQL, $dbCN);
                                mysql_close($dbCN);
                        	
				$myerror_o = mysql_error();
                                createLogOut($myerror_o, $transid,"DB Result ");
			}
			else
			{
				createLogOut($handle,$transid, " Error : ");
			}
		
		echo "<?xml version='1.0' ?><MO><STATUS>1</STATUS><TRANSID>".$transid."</TRANSID><MSG>Message processed successfully</MSG></MO>";
	

function createLogOut($param, $transid_s, $action)
{
	$tanggal_access = date("Ymd");
	$dir_acc = "/opt/apps/6768/log/xl/receiver/dr/";
	$file_log = $dir_acc."DR_".$tanggal_access.".log";

	$handle = fopen($file_log, "a");
		fprintf($handle, "%s", date("Y-m-d H:i:s")."|".$transid_s."|".$action.": ".$param."\n");
	fclose($handle);
}

function createInPull($param, $transid_s, $sc)
{
	$tanggal_access = date("Ymd");
	$dir_accmo = "/opt/apps/6768/queue/xl/in/dr/";
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
