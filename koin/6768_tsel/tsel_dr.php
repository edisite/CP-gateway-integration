<?php
if (function_exists("date_default_timezone_set")) { date_default_timezone_set("Asia/Jakarta"); }
$logid = md5(uniqid(rand(), true));

// constant declaration - code folding
DEFINE("DO_CHECK_PARAM", true);

// constant declaration - operator
DEFINE("SHORTCODE",	"6768");
DEFINE("LOG_PFX", "");
DEFINE("LOG_PTN", "Y-m-d");
DEFINE("LOG_SFX", "_dr_rcv");
DEFINE("DIR_LOG", "/opt/apps/".SHORTCODE."/log/tsel/");
DEFINE("DIR_DR", "/opt/apps/".SHORTCODE."/queue/tsel/dr/");
DEFINE("DB_TBL1", "queue_tsel_dr");
DEFINE("DB_TBL2", "sms_out_tsel");
DEFINE("BASE", "http://202.149.71.75/koinfrm/indosis-dr.php");
DEFINE("DR_STATUS_OK", "1");

// get parameters value
$type = isset($_GET["type"]) ? $_GET["type"] : "sms";
$type = trim(strtolower($type));
if ($type == "ol")
{
	$rawXML = isset($_GET["msg"]) ? $_GET["msg"] : "";
	$rawXML = rawurldecode($rawXML);
	//$rawXML = stripslashes($rawXML);
	$rawXML = trim($rawXML);
	logxml($rawXML);
	if ($rawXML == "")
	{
		log_write($logid, "begin", "");
		log_write($logid, "  received", $rawXML);
		log_write($logid, "  error", "xml reecived is an empty string");
		log_write($logid, "  solution", "skip and do not process this message, error response sent back to sender");
		log_write($logid, "finish", "counted as error\m");
		echo '<?xml version="1.0"?><push><transid></transid><status>6</status><msg>type of binary message received, but xml is empty</msg></push>';
		return;
	}

	set_error_handler("error_handler");
		$rawXML = '<?xml version="1.0"?>'.$rawXML;
		$XML = new SimpleXMLElement($rawXML);
	restore_error_handler();

	$in_trxids = $XML->trx_id;
	$in_servid = $XML->contentid;
	$in_trxdtm = isset($XML->trx_date) ? $XML->trx_date : date("YmdHis");
	$in_msisdn = isset($XML->msisdn_recipient) ? $XML->msisdn_recipient : $XML->msisdn_sender;
	$in_drstat = $XML->status;
	$in_mtrxid = $XML->sid;
	$in_operat = "tsel";
}
else
{
	$in_trxids = isset($_GET["trxid"]) ? $_GET["trxid"] : (isset($_GET["trx_id"]) ? $_GET["trx_id"] : "");
	$in_servid = isset($_GET["serviceid"]) ? $_GET["serviceid"] : (isset($_GET["sid"]) ? $_GET["sid"] : "");
	$in_trxdtm = isset($_GET["trxdate"]) ? $_GET["trxdate"]	: (isset($_GET["date"]) ? $_GET["date"] : (isset($_GET["time"]) ? $_GET["time"] : date("YmdHis")));
	$in_msisdn = isset($_GET["msisdn"]) ? $_GET["msisdn"] : "";
	$in_drstat = isset($_GET["status"]) ? $_GET["status"] : "";
	$in_mtrxid = isset($_GET["mtid"]) ? $_GET["mtid"] : "";
	$in_operat = isset($_GET["operator"]) ? $_GET["operator"] : "tsel";
}

// trim leading and trailing spaces and standardize capitalization
$in_trxdtm = trim($in_trxdtm);
$in_servid = trim($in_servid);
$in_trxids = trim($in_trxids);
$in_msisdn = trim($in_msisdn);
$in_drstat = trim($in_drstat);
$in_mtrxid = trim($in_mtrxid);
$in_operat = strtolower(trim($in_operat));

// check main parameters value
if ($in_trxids == "")
{
	log_write($logid, "begin", "");
	log_write($logid, "  received", $_SERVER["REQUEST_URI"]);
	log_write($logid, "  error", "missing transaction id");
	log_write($logid, "finish", "counted as error\m");
	echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>1</status><msg>missing transaction id</msg></push>';
	return;
}

// check other parameters value
log_write($in_trxids, "begin", "");
log_write($in_trxids, "  received", $_SERVER["REQUEST_URI"]);
if (DO_CHECK_PARAM)
{
	if (strlen($in_msisdn) < 6)
	{
		log_write($in_trxids, "  error", "invalid msisdn length [".strlen($in_msisdn)."][".$in_msisdn."]");
		log_write($in_trxids, "finish", "counted as error\m");
		echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>2</status><msg>invalid msisdn length ['.strlen($in_msisdn).']['.$in_msisdn.']</msg></push>';
		return;
	}
	if (!is_numeric($in_msisdn))
	{
		log_write($in_trxids, "  error", "invalid msisdn number [".$in_msisdn."]");
		log_write($in_trxids, "finish", "counted as error\m");
		echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>3</status><msg>invalid msisdn number ['.$in_msisdn.']</msg></push>';
		return;
	}
	if ($in_operat == "")
	{
		log_write($in_trxids, "  error", "missing operator");
		log_write($in_trxids, "finish", "counted as error\m");
		echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>4</status><msg>missing operator</msg></push>';
		return;
	}
}

// determine url to send delivery report
$URL = BASE;
$URL = $URL."?msisdn=".$in_msisdn;
$URL = $URL."&trxid=".$in_trxids;
$URL = $URL."&trxdate=".$in_trxdtm;
$URL = $URL."&operator=".$in_operat;
$URL = $URL."&status=".$in_drstat;
$URL = $URL."&serviceid=".$in_servid;
$URL = $URL."&mtid=".$in_mtrxid;

// create query to determine if record exist in database's storage table
$SQLIF = "SELECT IF(drstatus = '".DR_STATUS_OK."', true, false) AS res FROM ".DB_TBL2." WHERE trxid = '".$in_trxids."' AND msisdn = '".$in_msisdn."' LIMIT 1";

// create query to save to text file 1 of 2 - query queue table to insert new record
unset($arrf, $arrv);
$arrf[0] = "dtm";				$arrv[0] = "NOW()";
$arrf[1] = "trxid";				$arrv[1] = "'".$in_trxids."'";
$arrf[2] = "trxdtm";			$arrv[2] = "'".$in_trxdtm."'";
$arrf[3] = "msisdn";			$arrv[3] = "'".$in_msisdn."'";
$arrf[4] = "status";			$arrv[4] = "'".$in_drstat."'";
$arrf[5] = "sid";				$arrv[5] = "'".$in_servid."'";
$arrf[6] = "mtid";				$arrv[6] = "'".$in_mtrxid."'";
$arrf[7] = "hit_url";			$arrv[7] = "'".$URL."'";
$SQL1 = "INSERT INTO ".DB_TBL1." (".join(",", $arrf).") VALUES (".join(",", $arrv).")";

// create query to save to text file 2 of 4 - query storage table to update existing record
unset($arrf, $arrv);
$arrf[0] = "dtm";				$arrv[0] = "NOW()";
$arrf[1] = "trxid";				$arrv[1] = "'".$in_trxids."'";
$arrf[2] = "trxdtm";			$arrv[2] = "'".$in_trxdtm."'";
$arrf[3] = "msisdn";			$arrv[3] = "'".$in_msisdn."'";
$arrf[4] = "status";			$arrv[4] = "'".$in_drstat."'";
$arrf[5] = "sid";				$arrv[5] = "'".$in_servid."'";
$arrf[6] = "drdtm";				$arrv[6] = "NOW()";
$SQL2 = "INSERT INTO ".DB_TBL2." (".join(",", $arrf).") VALUES (".join(",", $arrv).")";

// create query to insert
$SQL3 = "UPDATE ".DB_TBL2." SET drdtm = '".$in_trxdtm."', drstatus = '".$in_drstat."' WHERE trxid = '".$in_trxids."' AND msisdn = '".$in_msisdn."' AND sid = '".$in_servid."' LIMIT 1";

// create text file
$path = DIR_DR.date("YmdHis")."_".$in_trxids."_".md5(uniqid(rand(), true)).".dat";
if ($afile = fopen($path, "w"))
{
	fprintf($afile, "%s\n", '<?xml version="1.0"?>');
	fprintf($afile, "%s\n", '<data>');
	fprintf($afile, "%s\n", '    <trxdate>'.$in_trxdtm.'</trxdate>');
	fprintf($afile, "%s\n", '    <trxid>'.$in_trxids.'</trxid>');
	fprintf($afile, "%s\n", '    <msisdn>'.$in_msisdn.'</msisdn>');
	fprintf($afile, "%s\n", '    <sid>'.$in_servid.'</sid>');
	fprintf($afile, "%s\n", '    <status>'.$in_drstat.'</status>');
	fprintf($afile, "%s\n", '    <mtrxid>'.$in_mtrxid.'</mtrxid>');
	fprintf($afile, "%s\n", '    <operator>'.$in_operat.'</operator>');
	fprintf($afile, "%s\n", '</data>');
	fprintf($afile, "%s\n", '<queries>');
	fprintf($afile, "%s\n", '    <sql_if><dbcn>2</dbcn><sql>'.$SQLIF.'</sql></sql_if>');
	fprintf($afile, "%s\n", '    <sql_if_empty1><dbcn>1</dbcn><sql>'.$SQL1.'</sql></sql_if_empty1>');
	fprintf($afile, "%s\n", '    <sql_if_empty2><dbcn>2</dbcn><sql>'.$SQL2.'</sql></sql_if_empty2>');
	fprintf($afile, "%s\n", '    <sql_if_exist_true1><dbcn></dbcn><sql></sql></sql_if_exist_true1>');
	fprintf($afile, "%s\n", '    <sql_if_exist_true2><dbcn></dbcn><sql></sql></sql_if_exist_true2>');
	fprintf($afile, "%s\n", '    <sql_if_exist_false1><dbcn>1</dbcn><sql>'.$SQL1.'</sql></sql_if_exist_false1>');
	fprintf($afile, "%s\n", '    <sql_if_exist_false2><dbcn>2</dbcn><sql>'.$SQL3.'</sql></sql_if_exist_false2>');
	fprintf($afile, "%s\n", '</queries>');
	fclose($afile);

	log_write($in_trxids, "  output", $path);
	log_write($in_trxids, "finish", "counted as success\n");
	echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>0</status><msg>Message processed successfully</msg></push>';
}
else
{
	log_write($in_trxids, "  output", $path);
	log_write($in_trxids, "  error", "cannot write to output file");
	log_write($in_trxids, "finish", "counted as error\n");
	echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>5</status><msg>Cannot save message to file</msg></push>';
}

// SUBROUTINE, WRITE LOG TO FILE #################################################################################################
function log_write($ptrxid, $psubject, $pmsg)
{
	$path = DIR_LOG.LOG_PFX.date(LOG_PTN).LOG_SFX.".log";
	$objfile = fopen($path, "a");
		chmod($path, 0777);
		$pmsg = str_replace("\n", "", $pmsg);
		$pmsg = str_replace("\m", "\n", $pmsg);
		fprintf($objfile, "%s|7|dr|rcv|nothread|%-8s|%-15s|%s\n", date("Y-m-d H:i:s"), $ptrxid, $psubject, $pmsg);
	fclose($objfile);
}

function error_handler($errno, $errstr, $errfile, $errline)
{
	global $rawXML;
	$path = DIR_LOG.LOG_PFX.date(LOG_PTN).LOG_SFX.".err";
	$objfile = fopen($path, "a");
		chmod($path, 0777);
		fprintf($objfile, "%s|%s\n", date("Y-m-d H:i:s"), $rawXML);
	fclose($objfile);
}

function logxml($msg)
{
	$path = DIR_LOG.LOG_PFX.date(LOG_PTN).LOG_SFX.".txt";
	$objfile = fopen($path, "a");
		chmod($path, 0777);
		fprintf($objfile, "%s|%s\n", date("Y-m-d H:i:s"), $msg);
	fclose($objfile);
}
?>