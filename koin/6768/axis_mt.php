<?php
if (function_exists("date_default_timezone_set")) { date_default_timezone_set("Asia/Jakarta"); }
/*
	created by hengky irawan
	last modified by hengky irawan 2012-05-04 2012-05-07 17:50
*/

// constant declaration - these declarations just to help code folding while coding
DEFINE("DO_CHECK_PARAM", true);
DEFINE("DO_SET_OTHERS", true);
DEFINE("DO_GET_PARAMETERS", true);

// constant declaration - operator
DEFINE("SHORTCODE",	"6768");

// constant declaration - log path and naming - LOG_DIR.LOG_PFX.date(LOG_PTN).LOG_SFX.log
DEFINE("LOG_PFX", "");
DEFINE("LOG_PTN", "Y-m-d");
DEFINE("LOG_SFX", "_mt_rcv");
DEFINE("DIR_MT", "/opt/apps/".SHORTCODE."/queue/axis/mt/");
DEFINE("DIR_LOG", "/opt/apps/".SHORTCODE."/log/axis/");
DEFINE("DIR_IP", "");
DEFINE("DB_TBL1", "queue_axis_mt");
DEFINE("DB_TBL2", "sms_out_axis");
DEFINE("INCOMING_UID", "axis");
DEFINE("INCOMING_PWD", "axis");
DEFINE("OUTGOING_UID", "koinsm");
DEFINE("OUTGOING_PWD", "SU9kRj");
DEFINE("DR_STATUS_OK", "1");

$logid = md5(uniqid(rand(), true));
if (DO_SET_OTHERS)
{
	// index is related to $in_smstyp
	$mttype["0"] = "sms";
	$mttype["1"] = "wappush";
	$mttype["2"] = "bin";

	$dlvmtd["pull"] = "mtpull";
	$dlvmtd["push"] = "mtpush";
}

// get parameters value
if (DO_GET_PARAMETERS)
{
	$in_userid = isset($_GET["uid"]) ? $_GET["uid"] : "";
	$in_passwd = isset($_GET["pwd"]) ? $_GET["pwd"] : "";
	$in_servid = isset($_GET["serviceid"]) ? $_GET["serviceid"] : "";
	$in_msisdn = isset($_GET["msisdn"]) ? $_GET["msisdn"] : "";
	$in_smstxt = isset($_GET["sms"]) ? $_GET["sms"] : "";
	$in_smstyp = isset($_GET["smstype"]) ? $_GET["smstype"] : "0";
	$in_trxids = isset($_GET["transid"]) ? $_GET["transid"] : "";
	$in_shcode = isset($_GET["shortcode"]) ? $_GET["shortcode"] : "";
	$in_keywod = isset($_GET["keyword"]) ? $_GET["keyword"] : "";
	$in_prices = isset($_GET["price"]) ? $_GET["price"] : "";
	$in_dlvmtd = isset($_GET["delivery_method"]) ? $_GET["delivery_method"] : "pull";
	$in_operat = isset($_GET["operator"]) ? $_GET["operator"] : (isset($_GET["provider"]) ? $_GET["provider"] : "axis");

	$in_userid = trim($in_userid);
	$in_passwd = trim($in_passwd);
	$in_servid = trim($in_servid);
	$in_msisdn = trim($in_msisdn);
	$in_smstxt = trim($in_smstxt);
	$in_smstyp = trim($in_smstyp);
	$in_trxids = trim($in_trxids);
	$in_shcode = trim($in_shcode);
	$in_keywod = trim($in_keywod);
	$in_prices = trim($in_prices);
	$in_dlvmtd = strtolower(trim($in_dlvmtd));
	$in_operat = strtolower(trim($in_operat));

	$tmp = strtoupper($in_servid);
	$n = strpos($tmp, SHORTCODE);
	if ($n === false)
	{
		$out_scode = "";
		$out_ccode = $tmp;
	}
	else
	{
		$out_scode = substr($tmp, 0, $n);
		$out_ccode = substr($tmp, $n);
	}
}

// check parameters value
if ($in_trxids == "")
{
	log_write($logid, "begin", "");
	log_write($logid, "  received", $_SERVER["REQUEST_URI"]);
	log_write($logid, "  error", "missing transaction id");
	log_write($logid, "  solution", "skip and do not process this message, error response sent back to sender");
	log_write($logid, "finish", "counted as error\m");
	echo '<?xml version="1.0" ?><push><transid>'.$in_trxids.'</transid><status>1</status><msg>missing transaction id</msg></push>';
	return;
}
log_write($in_trxids, "begin", "");
log_write($in_trxids, "  received", $_SERVER["REQUEST_URI"]);
if (DO_CHECK_PARAM)
{
	if (strlen($in_msisdn) < 6)
	{
		log_write($in_trxids, "  error", "invalid msisdn length [".strlen($in_msisdn)."][".$in_msisdn."]");
		log_write($in_trxids, "  solution", "skip and do not process this message, error response sent back to sender");
		log_write($in_trxids, "finish", "counted as error\m");
		echo '<?xml version="1.0" ?><push><transid>'.$in_trxids.'</transid><status>2</status><msg>invalid msisdn length ['.strlen($in_msisdn).']['.$in_msisdn.']</msg></push>';
		return;
	}
	if (!is_numeric($in_msisdn))
	{
		log_write($in_trxids, "  error", "invalid msisdn number [".$in_msisdn."]");
		log_write($in_trxids, "  solution", "skip and do not process this message, error response sent back to sender");
		log_write($in_trxids, "finish", "counted as error\m");
		echo '<?xml version="1.0" ?><push><transid>'.$in_trxids.'</transid><status>3</status><msg>invalid msisdn number ['.$in_msisdn.']</msg></push>';
		return;
	}
	if ($in_operat == "")
	{
		log_write($in_trxids, "  error", "missing operator");
		log_write($in_trxids, "  solution", "skip and do not process this message, error response sent back to sender");
		log_write($in_trxids, "finish", "counted as error\m");
		echo '<?xml version="1.0" ?><push><transid>'.$in_trxids.'</transid><status>4</status><msg>missing operator</msg></push>';
		return;
	}
}

// build hit url address
log_write($in_trxids, "  msg type", "text message");

$URL = '<?xml version="1.0" encoding="UTF-8"?>';
$URL = $URL.'<message type="'.$dlvmtd[$in_dlvmtd].'">';
	$URL = $URL.'<msisdn>'.$in_msisdn.'</msisdn>';
	$URL = $URL.'<sms><![CDATA['.$in_smstxt.']]></sms>';
	$URL = $URL.'<ccode>'.$out_ccode.'</ccode>';
	$URL = $URL.'<scode>'.$out_scode.'</scode>';
	if ($in_dlvmtd == "pull")
	{
		$URL = $URL.'<tid>'.$in_trxids.'</tid>';
	}
	$URL = $URL.'<cpid>'.OUTGOING_UID.'</cpid>';
	$URL = $URL.'<cppwd>'.OUTGOING_PWD.'</cppwd>';
$URL = $URL.'</message>';
log_write($in_trxids, "  URL", $URL);

// create query - query storage table for record existence
$SQL1 = "SELECT IF(drstatus = '".DR_STATUS_OK."', true, false) AS eval FROM ".DB_TBL2." WHERE trxid = '".$in_trxids."' AND msisdn = '".$in_msisdn."' LIMIT 1";

// create query - query queue table to insert new record
unset($arrf, $arrv);
$arrf[0] = "dtm";				$arrv[0] = "NOW()";
$arrf[1] = "trxid";				$arrv[1] = "'".$in_trxids."'";
$arrf[2] = "sid";				$arrv[2] = "'".$in_servid."'";
$arrf[3] = "msisdn";			$arrv[3] = "'".$in_msisdn."'";
$arrf[4] = "sms";				$arrv[4] = "'".rawurlencode($in_smstxt)."'";
$arrf[5] = "type";				$arrv[5] = "'".$in_smstyp."'";
$arrf[6] = "method";			$arrv[6] = "'".$in_dlvmtd."'";
$arrf[7] = "keyword";			$arrv[7] = "'".rawurlencode($in_keywod)."'";
$arrf[8] = "partneruserid";		$arrv[8] = "'".OUTGOING_UID."'";
$arrf[9] = "partnerpasswd";		$arrv[9] = "'".OUTGOING_PWD."'";
$arrf[10] = "hit_url";			$arrv[10] = "'".$URL."'";
$SQL2 = "INSERT INTO ".DB_TBL1." (".join(",", $arrf).") VALUES (".join(",", $arrv).")";

// create query - query storage table to insert new record
unset($arrf, $arrv);
$arrf[0] = "dtm";		$arrv[0] = "NOW()";
$arrf[1] = "trxid";		$arrv[1] = "'".$in_trxids."'";
$arrf[2] = "msisdn";	$arrv[2] = "'".$in_msisdn."'";
$arrf[3] = "keyword";	$arrv[3] = "'".$in_keywod."'";
$arrf[4] = "smstype";	$arrv[4] = "'".$in_smstyp."'";
$arrf[5] = "partner";	$arrv[5] = "'".OUTGOING_UID."'";
$arrf[6] = "sid";		$arrv[6] = "'".$in_servid."'";
$arrf[7] = "dmethod";	$arrv[7] = "'".$in_dlvmtd."'";
$arrf[8] = "sms";		$arrv[8] = "'".$in_smstxt."'";
$SQL3 = "INSERT INTO ".DB_TBL2." (".join(",", $arrf).") VALUES (".join(",", $arrv).")";

// create query - query storage table to update existing record
$SQL4 = "UPDATE ".DB_TBL2." SET sid = '".$in_servid."' WHERE trxid = '".$in_trxids."' AND msisdn = '".$in_msisdn."' LIMIT 1";

// create text file
$path = DIR_MT.date("YmdHis")."_".$in_trxids."_".md5(uniqid(rand(), true)).".dat";
$afile = fopen($path, "w");
if ($afile)
{
	fprintf($afile, "%s\n", '<?xml version="1.0" ?>');
	fprintf($afile, "%s\n", '<data>');
	fprintf($afile, "%s\n", '    <userid>'.OUTGOING_UID.'</userid>');
	fprintf($afile, "%s\n", '    <password>'.OUTGOING_PWD.'</password>');
	fprintf($afile, "%s\n", '    <trxid>'.$in_trxids.'</trxid>');
	fprintf($afile, "%s\n", '    <msisdn>'.$in_msisdn.'</msisdn>');
	fprintf($afile, "%s\n", '    <sid>'.$in_servid.'</sid>');
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
	echo '<?xml version="1.0" ?><push><transid>'.$in_trxids.'</transid><status>0</status><msg>Message processed successfully</msg></push>';
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
		fprintf($objfile, "%s|4|mt|rcv|nothread|%-8s|%-15s|%s\n", date("Y-m-d H:i:s"), $ptrxid, $psubject, $pmsg);
	fclose($objfile);
}
?>