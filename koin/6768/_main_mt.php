<?php
if (function_exists("date_default_timezone_set")) { date_default_timezone_set("Asia/Jakarta"); }
/*
	created by hengky irawan
	last modified by hengky irawan 2012-05-04 23:32
*/

// constant declaration - these declarations just to help code folding while coding
DEFINE("DO_CHECK_PARAM", true);
DEFINE("DO_SET_SVCKEYS_PUSH", true);
DEFINE("DO_SET_URL_PREFIX", true);
DEFINE("DO_SET_OTHERS", true);
DEFINE("DO_GET_PARAMETERS", true);
DEFINE("DO_SET_WAP_PARAM", true);
DEFINE("DO_SET_CONTENTID", true);

// constant declaration
DEFINE("SHORTCODE",	"6768");
DEFINE("LOG_PFX", "");
DEFINE("LOG_PTN", "Y-m-d");
DEFINE("LOG_SFX", "_mt_rcv");
DEFINE("DIR_MT", "/opt/apps/".SHORTCODE."/queue/isat/mt/");
DEFINE("DIR_LOG", "/opt/apps/".SHORTCODE."/log/isat/");
DEFINE("DIR_IP", "/opt/apps/".SHORTCODE."/queue/isat/ipaddr/");
DEFINE("DB_TBL1", "queue_isat_mt");
DEFINE("DB_TBL2", "sms_out_isat");
DEFINE("OUTGOING_UID", "pulsa2");
DEFINE("OUTGOING_PWD", "pulsapwd");
DEFINE("DR_STATUS_OK", "2");

if (DO_SET_OTHERS)
{
	// index is related to $in_smstyp
	$mttype["0"] = "sms";
	$mttype["1"] = "wappush";
	$mttype["2"] = "bin";
}

// get parameters value
if (DO_GET_PARAMETERS)
{
	$in_userid = isset($_GET["uid"]) ? $_GET["uid"] : "";
	$in_passwd = isset($_GET["pwd"]) ? $_GET["pwd"] : "";
	$in_srvcid = isset($_GET["serviceid"]) ? $_GET["serviceid"] : "";
	$in_msisdn = isset($_GET["msisdn"]) ? $_GET["msisdn"] : "";
	$in_smstxt = isset($_GET["sms"]) ? $_GET["sms"] : "";
	$in_smstyp = isset($_GET["smstype"]) ? $_GET["smstype"] : "0";
	$in_trxids = isset($_GET["transid"]) ? $_GET["transid"] : "";
	$in_shcode = isset($_GET["shortcode"]) ? $_GET["shortcode"] : "";
	$in_keywod = isset($_GET["keyword"]) ? $_GET["keyword"] : "";
	$in_prices = isset($_GET["price"]) ? $_GET["price"] : "";
	$in_dlvmtd = isset($_GET["delivery_method"]) ? $_GET["delivery_method"] : "pull";
	$in_operat = isset($_GET["operator"]) ? $_GET["operator"] : (isset($_GET["provider"]) ? $_GET["provider"] : "indosat");

	$in_userid = trim($in_userid);
	$in_passwd = trim($in_passwd);
	$in_srvcid = trim($in_srvcid);
	$in_msisdn = trim($in_msisdn);
	$in_smstxt = trim($in_smstxt);
	$in_smstyp = trim($in_smstyp);
	$in_trxids = trim($in_trxids);
	$in_shcode = trim($in_shcode);
	$in_keywod = trim($in_keywod);
	$in_prices = trim($in_prices);
	$in_dlvmtd = strtolower(trim($in_dlvmtd));
	$in_operat = strtolower(trim($in_operat));
}

// modify value of sms type for indosat so it's always point to 0 or sms type of plain text 
// this is specifically requested by Saptadi by email in "Re: Pendaftaran Keyword dan PB Ayu Dewi" - Wed 6/13/2012 1:58 PM
$in_smstyp = "0";

// check parameters value
if ($in_trxids == "")
{
	echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>1</status><msg>missing transaction id</msg></push>';
	log_write("error", "error", "missing transaction id");
	return;
}
log_write($in_trxids, "begin", "");
log_write($in_trxids, "  received", $_SERVER["REQUEST_URI"]);
if (DO_CHECK_PARAM)
{
	if (strlen($in_msisdn) < 6)
	{
		echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>2</status><msg>invalid msisdn length ['.strlen($in_msisdn).']['.$in_msisdn.']</msg></push>';
		log_write($in_trxids, "  error", "invalid msisdn length [".strlen($in_msisdn)."][".$in_msisdn."]");
		log_write($in_trxids, "finish", "counted as error\m");
		return;
	}
	if (!is_numeric($in_msisdn))
	{
		echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>3</status><msg>invalid msisdn number ['.$in_msisdn.']</msg></push>';
		log_write($in_trxids, "  error", "invalid msisdn number [".$in_msisdn."]");
		log_write($in_trxids, "finish", "counted as error\m");
		return;
	}
	if ($in_operat == "")
	{
		echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>4</status><msg>missing operator</msg></push>';
		log_write($in_trxids, "  error", "missing operator");
		log_write($in_trxids, "finish", "counted as error\m");
		return;
	}
	$arr = split(" ", str_replace("  ", " ", strtolower($in_smstxt)));
	if ($arr[0] == "reg" || $arr[0] == "set" || $arr[0] == "unreg" || $arr[0] == "unrek" || $arr[0] == "stop" || $arr[0] == "setop" || $arr[0] == "unset")
	{
		$keyword = isset($arr[1]) ? $arr[1] : $arr[0];
	}
	else
	{
		$keyword = $arr[0];
	}
}

// get service keys
$tmp = strtolower($in_keywod);
$svckey = ""; // this value only set for XL operator

// build hit url address
$HITMTD = "get";
$URL = "";
$prefix = "";
$URL = $prefix."?uid=".OUTGOING_UID;
$URL = $URL."&pwd=".OUTGOING_PWD;
$URL = $URL."&serviceid=".$in_srvcid;
$URL = $URL."&msisdn=".$in_msisdn;
$URL = $URL."&sms=".rawurlencode($in_smstxt);
$URL = $URL."&transid=".$in_trxids;
$URL = $URL."&smstype=".$in_smstyp;

if ($in_dlvmtd == "push")
{
	if (!$in_srvcid == "67680184047001")
	{
		$URL = $URL."&sdmcode=".($in_keywod.SHORTCODE);
	}
}
log_write($in_trxids, "  URL", $URL);

// create query - query storage table for record existence
$SQL1 = "SELECT IF(drstatus = '".DR_STATUS_OK."', true, false) AS eval FROM ".DB_TBL2." WHERE trxid = '".$in_trxids."' AND msisdn = '".$in_msisdn."' LIMIT 1";

// create query - query queue table to insert new record
unset($arrf, $arrv);
$arrf[0] = "dtm";				$arrv[0] = "NOW()";
$arrf[1] = "trxid";				$arrv[1] = "'".$in_trxids."'";
$arrf[2] = "sid";				$arrv[2] = "'".$in_srvcid."'";
$arrf[3] = "msisdn";			$arrv[3] = "'".$in_msisdn."'";
$arrf[4] = "sms";				$arrv[4] = "'".rawurlencode($in_smstxt)."'";
$arrf[5] = "type";				$arrv[5] = "'".$in_smstyp."'";
$arrf[6] = "method";			$arrv[6] = "'".$in_dlvmtd."'";
$arrf[7] = "keyword";			$arrv[7] = "'".rawurlencode($keyword)."'";
$arrf[8] = "partneruserid";		$arrv[8] = "'".OUTGOING_UID."'";
$arrf[9] = "partnerpasswd";		$arrv[9] = "'".OUTGOING_PWD."'";
$arrf[10] = "hit_url";			$arrv[10] = "'".$URL."'";
$arrf[11] = "hit_mtd";			$arrv[11] = "'".$HITMTD."'";
$SQL2 = "INSERT INTO ".DB_TBL1." (".join(",", $arrf).") VALUES (".join(",", $arrv).")";

// create query - query storage table to insert new record
unset($arrf, $arrv);
$arrf[0] = "dtm";		$arrv[0] = "NOW()";
$arrf[1] = "trxid";		$arrv[1] = "'".$in_trxids."'";
$arrf[2] = "msisdn";	$arrv[2] = "'".$in_msisdn."'";
$arrf[3] = "keyword";	$arrv[3] = "'".$in_keywod."'";
$arrf[4] = "smstype";	$arrv[4] = "'".$in_smstyp."'";
$arrf[5] = "partner";	$arrv[5] = "'".OUTGOING_UID."'";
$arrf[6] = "sid";		$arrv[6] = "'".$in_srvcid."'";
$arrf[7] = "dmethod";	$arrv[7] = "'".$in_dlvmtd."'";
$arrf[8] = "sms";		$arrv[8] = "'".$in_smstxt."'";
$SQL3 = "INSERT INTO ".DB_TBL2." (".join(",", $arrf).") VALUES (".join(",", $arrv).")";

// create query - query storage table to update existing record
$SQL4 = "UPDATE ".DB_TBL2." SET sid = '".$in_srvcid."' WHERE trxid = '".$in_trxids."' AND msisdn = '".$in_msisdn."' LIMIT 1";

// create text file
$path = DIR_MT.date("YmdHis")."_".$in_trxids."_".md5(uniqid(rand(), true)).".dat";
if ($afile = fopen($path, "w"))
{
	fprintf($afile, "%s\n", '<?xml version="1.0"?>');
	fprintf($afile, "%s\n", '<data>');
	fprintf($afile, "%s\n", '    <userid>'.OUTGOING_UID.'</userid>');
	fprintf($afile, "%s\n", '    <password>'.OUTGOING_PWD.'</password>');
	fprintf($afile, "%s\n", '    <trxid>'.$in_trxids.'</trxid>');
	fprintf($afile, "%s\n", '    <msisdn>'.$in_msisdn.'</msisdn>');
	fprintf($afile, "%s\n", '    <sid>'.$in_srvcid.'</sid>');
	fprintf($afile, "%s\n", '    <smstype>'.$in_smstyp.'</smstype>');
	fprintf($afile, "%s\n", '    <keyword>'.rawurlencode($in_keywod).'</keyword>');
	fprintf($afile, "%s\n", '    <sms>'.rawurlencode($in_smstxt).'</sms>');
	fprintf($afile, "%s\n", '    <dlvmtd>'.$in_dlvmtd.'</dlvmtd>');
	fprintf($afile, "%s\n", '    <operator>'.$in_operat.'</operator>');
	fprintf($afile, "%s\n", '</data>');
	fprintf($afile, "%s\n", '<queries>');
	fprintf($afile, "%s\n", '    <sql_if><dbcn>2</dbcn><sql>'.$SQL1.'</sql></sql_if>');
	fprintf($afile, "%s\n", '    <sql_if_empty1><dbcn>1</dbcn><sql>'.$SQL2.'</sql></sql_if_empty1>');
	fprintf($afile, "%s\n", '    <sql_if_empty2><dbcn>2</dbcn><sql>'.$SQL3.'</sql></sql_if_empty2>');
	fprintf($afile, "%s\n", '    <sql_if_exist_true1></sql_if_exist_true1>');
	fprintf($afile, "%s\n", '    <sql_if_exist_false1><dbcn>1</dbcn><sql>'.$SQL2.'</sql></sql_if_exist_false1>');
	fprintf($afile, "%s\n", '    <sql_if_exist_false2><dbcn>2</dbcn><sql>'.$SQL4.'</sql></sql_if_exist_false2>');
	fprintf($afile, "%s\n", '</queries>');
	fclose($afile);

	log_write($in_trxids, "  output", $path);
	log_write($in_trxids, "finish", "counted as success\m");
	echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>0</status><msg>Message processed successfully</msg></push>';
}
else
{
	log_write($in_trxids, "  error", "cannot save data to file");
	log_write($in_trxids, "finish", "counted as error\m");
	echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>6</status><msg>cannot save data to file</msg></push>';
}

// SUBROUTINE, WRITE LOG TO FILE #################################################################################################
function log_write($ptrxid, $psubject, $pmsg)
{
	$path = DIR_LOG.LOG_PFX.date(LOG_PTN).LOG_SFX.".log";
	$objfile = fopen($path, "a");
		chmod($path, 0777);
		$pmsg = str_replace("\n", "", $pmsg);
		$pmsg = str_replace("\m", "\n", $pmsg);
		fprintf($objfile, "%s|4|mt|rcv|nothread|%-8s|%-15s|%s\n", date("Y-m-d H:i:s"), $ptrxid, $psubject, $pmsg);
	fclose($objfile);
}
?>
