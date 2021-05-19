<?php
if (function_exists("date_default_timezone_set")) { date_default_timezone_set("Asia/Jakarta"); }
/*
	created by hengky irawan
	last modified by hengky irawan on 2012-05-04 23:321
*/

// constant declaration - code folding
DEFINE("DO_CHECK_PARAM", true);

// constant declaration - operator
DEFINE("SHORTCODE",	"6768");

// constant declaration - forward address
//DEFINE("FWD_MO", "");

DEFINE("FWDMOPROMO", true);

// constant declaration - log path and naming - LOG_DIR.LOG_PFX.date(LOG_PTN).LOG_SFX.log
DEFINE("LOG_PFX", "");
DEFINE("LOG_PTN", "Y-m-d");
DEFINE("LOG_SFX", "_mo_rcv");
DEFINE("DIR_LOG", "/opt/apps/".SHORTCODE."/log/isat/");
DEFINE("DIR_MO", "/opt/apps/".SHORTCODE."/queue/isat/mo/");
DEFINE("DIR_IP", "/opt/apps/".SHORTCODE."/queue/isat/ipaddr/");
DEFINE("DB_TBL1", "queue_isat_mo");
DEFINE("DB_TBL2", "sms_out_isat");
DEFINE("SEND_MT_PFX", "");
DEFINE("SEND_MT_SFX", "");
DEFINE("URLFWD", "http://localhost/handle/6768/koin_isat_mo.php");
DEFINE("DROKCODE", "<status>0</status>");

// get parameters value
$in_msisdn = isset($_GET["msisdn"]) ? $_GET["msisdn"] : "";
$in_trxids = isset($_GET["trxid"]) ? $_GET["trxid"] : (isset($_GET["trx_id"]) ? $_GET["trx_id"] : (isset($_GET["transid"]) ? $_GET["transid"] : (isset($_GET["tid"]) ? $_GET["tid"] : "")));
$in_trxdtm = isset($_GET["trxdate"]) ? $_GET["trxdate"] : (isset($_GET["trx_time"]) ? $_GET["trx_time"] : "");
$in_smstxt = isset($_GET["sms"]) ? $_GET["sms"] : "";
$in_substp = isset($_GET["substype"]) ? $_GET["substype"] : "";
$in_srcadd = isset($_GET["sourceaddress"]) ? $_GET["sourceaddress"] : $_SERVER["REMOTE_ADDR"];
$in_operat = isset($_GET["operator"]) ? $_GET["operator"] : "indosat";

$in_msisdn = trim($in_msisdn);
$in_trxids = trim($in_trxids);
$in_trxdtm = trim($in_trxdtm);
$in_smstxt = trim($in_smstxt);
$in_substp = trim($in_substp);
$in_srcadd = trim(strtolower($in_srcadd));
$in_operat = trim(strtolower($in_operat));

$msisdn = $in_msisdn;
$sc 	= $_GET["sc"];

$URL = $_SERVER["REQUEST_URI"];
$in_srcadd = str_replace("http://", "", $in_srcadd);

// check parameters value
if ($in_trxids == "")
{
	log_write("error", "error", "missing transaction id");
	echo '<?xml version="1.0" ?><MO><STATUS>-1</STATUS><TRANSID>'.$in_trxids.'</TRANSID><MSG>Parameter incomplete</MSG></MO>';	
	return;
}
log_write($in_trxids, "begin", "");
log_write($in_trxids, "  received", $in_srcadd);
log_write($in_trxids, "  received", $_SERVER["REQUEST_URI"]);
if (DO_CHECK_PARAM)
{
	if (strlen($in_msisdn) < 6)
	{
		log_write($in_trxids, "  error", "invalid msisdn length [".strlen($in_msisdn)."][".$in_msisdn."]");
		log_write($in_trxids, "finish", "counted as error\m");
		echo '<?xml version="1.0" ?><MO><STATUS>-1</STATUS><TRANSID>'.$in_trxids.'</TRANSID><MSG>Parameter incomplete</MSG></MO>';
		return;
	}
	if (!is_numeric($in_msisdn))
	{
		log_write($in_trxids, "  error", "invalid msisdn number [".$in_msisdn."]");
		log_write($in_trxids, "finish", "counted as error\m");
		echo '<?xml version="1.0" ?><MO><STATUS>-1</STATUS><TRANSID>'.$in_trxids.'</TRANSID><MSG>Parameter incomplete</MSG></MO>';
		return;
	}
	if ($in_operat == "")
	{
		log_write($in_trxids, "  error", "missing operator");
		log_write($in_trxids, "finish", "counted as error\m");
		echo '<?xml version="1.0" ?><MO><STATUS>-1</STATUS><TRANSID>'.$in_trxids.'</TRANSID><MSG>Parameter incomplete</MSG></MO>';
		return;
	}
	$arr = split(" ", str_replace("  ", " ", strtolower($in_smstxt)));
	if ($arr[0] == "reg" || $arr[0] == "set" || $arr[0] == "unreg" || $arr[0] == "unrek" || $arr[0] == "stop" || $arr[0] == "setop" || $arr[0] == "unset")
	{
		$keyword = isset($arr[1]) ? $arr[1] : $arr[0];
		$subkwd  = isset($arr[2]) ? $arr[2] : $arr[3];
	}
	else
	{
		$keyword = $arr[0];
		if(strtolower($keyword) == "vea"){$keyword ="veapull";}
		if(strtolower($keyword) == "stardut"){$keyword ="stardutpull";}
		if(strtolower($keyword) == "debol"){$keyword ="debolpull";}
		if(strtolower($keyword) == "artis"){$keyword ="artispull";}

	}
	log_write($in_trxids, "  keyword", $keyword);
	$fwdmo		= "0";
	if(FWDMOPROMO){
		if(strtolower($keyword) == "romance" && strtolower($arr[0]) == "reg"){
			if($subkwd != ""){
				$fwdmo = "1";
			}
		}
	}
}


/*
if($sc != "6768")
{
    $n = strpos($URL, "?");
    if ($n === false)
    {
	return;
    }
	$PAR = substr($URL, $n);
	
$URL = URLFWD.$PAR;
log_write($in_trxids, "  send get", $URL);

$handle = fopen($URL, "r");
if ($handle)
{
	$buffer = "";
	while (!feof($handle)) { $buffer = $buffer.fgets($handle, 4096); }
	fclose($handle);
	log_write($in_trxids, "  response", $buffer);

	$tmp = strtolower($buffer);
	if (strpos($tmp, DROKCODE) === false)
	{
		log_write($in_trxids, "finish", "counted as error\m");
	}
	else
	{
		log_write($in_trxids, "finish", "counted as success\m");
	}
	echo $buffer;
	return;
}
else
{
	log_write($in_trxids, "  error", "send failed, cannot access operator");
	log_write($in_trxids, "  solution", "skip this transaction and return error code to sender");
	log_write($in_trxids, "finish", "counted as error\m");
	echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>-32</status><msg>operator not reachable, please try again<</msg></push>';
	return;
}
}
*/


$geturl = "http://10.1.1.83/portal/isat/direct3/gw/cekkwd/pulsa/indosat/".strtoupper($keyword);
$resp = file_get_contents($geturl);
$jresp = json_decode($resp,true);

$r_kwd 			= $jresp['keyword'];
$r_cp			= $jresp['cp'];
$r_partner		= $jresp['partner'];
$r_operator		= $jresp['operator'];
$r_urlmo		= $jresp['url_mo'];
$r_urldr		= $jresp['url_dr'];

log_write($in_trxids, "  api keyword ", $r_kwd);
log_write($in_trxids, "  api cp ", $r_cp);
log_write($in_trxids, "  api partner ", $r_partner);
log_write($in_trxids, "  api operator ", $r_operator);
log_write($in_trxids, "  api url mo ", $r_urlmo);


if($r_urlmo != ""){
	DEFINE("FWD_MO", $r_urlmo);
}else{
	DEFINE("FWD_MO", "");
}

//check msisdn blocked
		
// forward MO

$URL = FWD_MO;
$URL = $URL."?msisdn=".$in_msisdn;
$URL = $URL."&sms=".rawurlencode($in_smstxt);
$URL = $URL."&trxdate=".date("YmdHis");
$URL = $URL."&substype=".$in_substp;
$URL = $URL."&transid=".$in_trxids;
$URL = $URL."&shortcode=".$sc;
$URL = $URL."&sc=".$sc;
$URL = $URL."&operator=".$in_operat;
$URL = $URL."&sourceaddress=".$in_srcadd;
$URL = $URL."&auth=1234-1234";
$URL = $URL."&tid=".$in_trxids;
$URL = $URL."&body=".rawurlencode($in_smstxt);
$URL = $URL."&connect_id=indosat_sms";
$URL = $URL."&shortno=";
$URL = $URL."&access=";


log_write($in_trxids, "  api url mo ", $URL);

// create string for insert into database - queue table
unset($arrf, $arrv);
$arrf[0] = "dtm";				$arrv[0] = "NOW()";
$arrf[1] = "trxid";				$arrv[1] = "'".$in_trxids."'";
$arrf[2] = "trxdtm";			$arrv[2] = "'".$in_trxdtm."'";
$arrf[3] = "msisdn";			$arrv[3] = "'".$in_msisdn."'";
$arrf[4] = "sms";				$arrv[4] = "'".rawurlencode($in_smstxt)."'";
$arrf[5] = "type";				$arrv[5] = "'0'";
$arrf[6] = "method";			$arrv[6] = "'pull'";
$arrf[7] = "operator";			$arrv[7] = "'".$in_operat."'";
$arrf[8] = "substype";			$arrv[8] = "'".$in_substp."'";
$arrf[9] = "source";			$arrv[9] = "'".$in_srcadd."'";
$arrf[10] = "keyword";			$arrv[10] = "'".rawurlencode($keyword)."'";
$arrf[11] = "hit_url";			$arrv[11] = "'".$URL."'";
$SQL1 = "INSERT INTO ".DB_TBL1." (".join(",", $arrf).") VALUES (".join(",", $arrv).")";

if($fwdmo == "1"){
	$URL4 = "";
	$URL4 = $URL4."?msisdn=".$in_msisdn;
	$URL4 = $URL4."&sms=".rawurlencode($in_smstxt);
	$URL4 = $URL4."&trxdate=".date("YmdHis");
	$URL4 = $URL4."&substype=".$in_substp;
	$URL4 = $URL4."&transid=".$in_trxids;
	$URL4 = $URL4."&shortcode=".$sc;
	$URL4 = $URL4."&sc=".$sc;
	// create string for insert into database - queue table
	unset($arrf, $arrv);
	$arrf[0] = "dtm";				$arrv[0] = "NOW()";
	$arrf[1] = "trxid";				$arrv[1] = "'".$in_trxids."'";
	$arrf[2] = "trxdtm";			$arrv[2] = "'".$in_trxdtm."'";
	$arrf[3] = "msisdn";			$arrv[3] = "'".$in_msisdn."'";
	$arrf[4] = "sms";				$arrv[4] = "'".rawurlencode($in_smstxt)."'";
	$arrf[5] = "type";				$arrv[5] = "'0'";
	$arrf[6] = "method";			$arrv[6] = "'pull'";
	$arrf[7] = "operator";			$arrv[7] = "'".$in_operat."'";
	$arrf[8] = "substype";			$arrv[8] = "'".$in_substp."'";
	$arrf[9] = "source";			$arrv[9] = "'".$in_srcadd."'";
	$arrf[10] = "keyword";			$arrv[10] = "'".rawurlencode($keyword)."'";
	$arrf[11] = "hit_url";			$arrv[11] = "'http://10.1.1.83/portal/koin/isat/download/mo.php".$URL4."'";
	$SQL4 = "INSERT INTO ".DB_TBL1." (".join(",", $arrf).") VALUES (".join(",", $arrv).")";
}

// create string for insert into database - storage table - this is needed to store original message
unset($arrf, $arrv);
$arrf[0] = "dtm";			$arrv[0] = "NOW()";
$arrf[1] = "trxid";			$arrv[1] = "'".$in_trxids."'";
$arrf[2] = "trxdtm";		$arrv[2] = "'".$in_trxdtm."'";
$arrf[3] = "msisdn";		$arrv[3] = "'".$in_msisdn."'";
$arrf[4] = "keyword";		$arrv[4] = "'".rawurlencode($keyword)."'";
$arrf[5] = "sms";			$arrv[5] = "'".rawurlencode($in_smstxt)."'";
$arrf[6] = "smstype";		$arrv[6] = "''";
$arrf[7] = "source";		$arrv[7] = "'".$in_srcadd."'";
$arrf[8] = "dmethod";		$arrv[8] = "'pull'";
$SQL2 = "INSERT INTO ".DB_TBL2." (".join(",", $arrf).") VALUES (".join(",", $arrv).")";

// store source address (if exist) into text file
/*if ($in_srcadd != "")
{
	$path = DIR_IP.$in_trxids."_".$in_msisdn.".dat";
	if ($afile = fopen($path, "w"))
	{
		fprintf($afile, "%s\n", SEND_MT_PFX.$in_srcadd.SEND_MT_SFX);
		log_write($in_trxids, "  srcaddr", $path);
		fclose($afile);
	}
	else
	{
		log_write($in_trxids, "  error", "cannot create file [".$path."]");
		log_write($in_trxids, "  error", $path);
		log_write($in_trxids, "finish", "counted as error\m");
		echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>5</status><msg>cannot create file ['.$path.']</msg></push>';
		return;
	}
}
*/

// create text file
$path = DIR_MO.date("YmdHis")."_".$in_trxids."_".md5(uniqid(rand(), true)).".dat";
if ($afile = fopen($path, "w"))
{
	fprintf($afile, "%s\n", '<?xml version="1.0"?>');
	fprintf($afile, "%s\n", '<data>');
	fprintf($afile, "%s\n", '    <trxid>'.$in_trxids.'</trxid>');
	fprintf($afile, "%s\n", '    <trxdtm>'.$in_trxdtm.'</trxdtm>');
	fprintf($afile, "%s\n", '    <msisdn>'.$in_msisdn.'</msisdn>');
	fprintf($afile, "%s\n", '    <operator>'.$in_operat.'</operator>');
	fprintf($afile, "%s\n", '    <substype>'.$in_substp.'</substype>');
	fprintf($afile, "%s\n", '    <source>'.$in_srcadd.'</source>');
	fprintf($afile, "%s\n", '    <sms>'.rawurlencode($in_smstxt).'</sms>');
	fprintf($afile, "%s\n", '</data>');
	fprintf($afile, "%s\n", '<queries>');
	fprintf($afile, "%s\n", '    <sql1><dbcn>1</dbcn><sql>'.$SQL1.'</sql></sql1>');
	fprintf($afile, "%s\n", '    <sql2><dbcn>2</dbcn><sql>'.$SQL2.'</sql></sql2>');
	if($fwdmo == "1"){
		fprintf($afile, "%s\n", '    <sql3><dbcn>3</dbcn><sql>'.$SQL4.'</sql></sql2>');
	}
	fprintf($afile, "%s\n", '</queries>');
	fclose($afile);

	log_write($in_trxids, "  output", $path);
	//log_write($in_trxids, "  line1", $SQL1);
	//log_write($in_trxids, "  line2", $SQL2);
	log_write($in_trxids, "finish", "counted as success\m");
	
	echo '<?xml version="1.0" ?><MO><STATUS>0</STATUS><TRANSID>'.$in_trxids.'</TRANSID><MSG>Message processed successfully</MSG></MO>';
}
else
{
	log_write($in_trxids, "  error", "cannot create file [".$path."]");
	log_write($in_trxids, "  error", $path);
	log_write($in_trxids, "finish", "counted as error\m");
	echo '<?xml version="1.0" ?><MO><STATUS>3</STATUS><TRANSID>'.$in_trxids.'</TRANSID><MSG>System Error</MSG></MO>';
	return;
}

// SUBROUTINE, WRITE LOG TO FILE #################################################################################################
function log_write($ptrxid, $psubject, $pmsg)
{
	$path = DIR_LOG.LOG_PFX.date(LOG_PTN).LOG_SFX.".log";
	$objfile = fopen($path, "a");
		chmod($path, 0777);
		$pmsg = str_replace("\n", "", $pmsg);
		$pmsg = str_replace("\m", "\n", $pmsg);
		fprintf($objfile, "%s|1|mo|rcv|nothread|%-8s|%-15s|%s\n", date("Y-m-d H:i:s"), $ptrxid, $psubject, $pmsg);
	fclose($objfile);
}
?>