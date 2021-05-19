<?php
if (function_exists("date_default_timezone_set")) { date_default_timezone_set("Asia/Jakarta"); }
/*
	created by hengky irawan
	last modified by hengky irawan 2012-05-04 2012-05-07 17:50
*/

// constant declaration - code folding
DEFINE("DO_CHECK_PARAM", true);

// constant declaration - operator
DEFINE("SHORTCODE",	"6768");

// constant declaration - forward address
DEFINE("FWD_MO", "");

// constant declaration - log path and naming - LOG_DIR.LOG_PFX.date(LOG_PTN).LOG_SFX.log
DEFINE("LOG_PFX", "");
DEFINE("LOG_PTN", "Y-m-d");
DEFINE("LOG_SFX", "_mo_rcv");
DEFINE("DIR_MO", "/opt/apps/".SHORTCODE."/queue/axis/mo/");
DEFINE("DIR_LOG", "/opt/apps/".SHORTCODE."/log/axis/");
DEFINE("DIR_IP", "/opt/apps/".SHORTCODE."/queue/axis/ipaddr/");
DEFINE("DB_TBL1", "queue_axis_mo");
DEFINE("DB_TBL2", "sms_out_axis");
DEFINE("SEND_MT_PFX", "");
DEFINE("SEND_MT_SFX", "");

// get parameters value
$logid = md5(uniqid(rand(), true));
$rawXML = trim(file_get_contents("php://input"));
if ($rawXML == "")
{
	log_write($logid, "begin", "");
	log_write($logid, "  received", $rawXML);
	log_write($logid, "  error", "xml reecived is an empty string");
	log_write($logid, "  solution", "skip and do not process this message, error response sent back to sender");
	log_write($logid, "finish", "counted as error\m");
	echo "NOK";
	return;
}
$XML = new SimpleXMLElement($rawXML);

$in_msisdn = $XML->msisdn;
$in_trxids = $XML->tid;
$in_trxdtm = $XML->tdate;
$in_smstxt = $XML->sms;
$in_substp = "0";
$in_srcadd = $_SERVER["REMOTE_ADDR"];
$in_operat = "axis";

$in_msisdn = trim($in_msisdn);
$in_trxids = trim($in_trxids);
$in_trxdtm = trim($in_trxdtm);
$in_smstxt = trim($in_smstxt);
$in_substp = trim($in_substp);
$in_srcadd = trim(strtolower($in_srcadd));
$in_operat = trim(strtolower($in_operat));

// check parameters value
if ($in_trxids == "")
{
	log_write($logid, "begin", "");
	log_write($logid, "  received", $rawXML);
	log_write($logid, "  error", "missing transaction id");
	log_write($logid, "  solution", "skip and do not process this message, error response sent back to sender");
	log_write($logid, "finish", "counted as error\m");
	echo "NOK";
	return;
}
log_write($in_trxids, "begin", "");
log_write($in_trxids, "  received", $rawXML);
if (DO_CHECK_PARAM)
{
	if (strlen($in_msisdn) < 6)
	{
		log_write($in_trxids, "  error", "invalid msisdn length [".strlen($in_msisdn)."][".$in_msisdn."]");
		log_write($in_trxids, "  solution", "skip and do not process this message, error response sent back to sender");
		log_write($in_trxids, "finish", "counted as error\m");
		echo "NOK";
		return;
	}
	if (!is_numeric($in_msisdn))
	{
		log_write($in_trxids, "  error", "invalid msisdn number [".$in_msisdn."]");
		log_write($in_trxids, "  solution", "skip and do not process this message, error response sent back to sender");
		log_write($in_trxids, "finish", "counted as error\m");
		echo "NOK";
		return;
	}
	if ($in_operat == "")
	{
		log_write($in_trxids, "  error", "missing operator");
		log_write($in_trxids, "  solution", "skip and do not process this message, error response sent back to sender");
		log_write($in_trxids, "finish", "counted as error\m");
		echo "NOK";
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

// forward MO
$URL = FWD_MO;
$URL = $URL."?msisdn=".$in_msisdn;
$URL = $URL."&sms=".rawurlencode($in_smstxt);
$URL = $URL."&trxdate=".date("YmdHis");
$URL = $URL."&substype=".$in_substp;
$URL = $URL."&transid=".$in_trxids;
$URL = $URL."&shortcode=".SHORTCODE;
$URL = $URL."&sc=".SHORTCODE;
$URL = $URL."&operator=".$in_operat;
$URL = $URL."&sourceaddress=".$in_srcadd;
log_write($in_trxids, "  URL", $URL);

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
if ($in_srcadd != "")
{
	$path = DIR_IP.$in_trxids."_".$in_msisdn.".dat";
	if ($afile = fopen($path, "w"))
	{
		fprintf($afile, "%s\n", $in_srcadd);
		log_write($in_trxids, "  srcaddr", $path);
		fclose($afile);
	}
	else
	{
		echo "NOK";
		log_write($in_trxids, "  error", "cannot create file [".$path."]");
		log_write($in_trxids, "  error", $path);
		log_write($in_trxids, "finish", "counted as error\m");
		return;
	}
}

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
	fprintf($afile, "%s\n", '</queries>');
	fclose($afile);

	log_write($in_trxids, "  output", $path);
	log_write($in_trxids, "  line1", $SQL1);
	log_write($in_trxids, "  line2", $SQL2);
	log_write($in_trxids, "finish", "counted as success\m");
	echo "OK";
}
else
{
	log_write($in_trxids, "  error", "cannot create file [".$path."]");
	log_write($in_trxids, "  solution", "skip and do not process this message, error response sent back to sender");
	log_write($in_trxids, "finish", "counted as error\m");
	echo "NOK";
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