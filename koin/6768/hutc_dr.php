<?php
if (function_exists("date_default_timezone_set")) { date_default_timezone_set("Asia/Jakarta"); }
$logid = md5(uniqid(rand(), true));
/*
	created by hengky irawan
	last modified by hengky irawan 2012-05-04 2012-05-04 23:32
*/

// constant declaration - code folding
DEFINE("DO_CHECK_PARAM", true);

// constant declaration
DEFINE("SHORTCODE",	"6768");
DEFINE("LOG_PFX", "");
DEFINE("LOG_PTN", "Y-m-d");
DEFINE("LOG_SFX", "_dr_rcv");
DEFINE("DIR_DR", "/opt/apps/".SHORTCODE."/queue/hutch/dr/");
DEFINE("DIR_LOG", "/opt/apps/".SHORTCODE."/log/hutch/");
DEFINE("DB_TBL1", "queue_hutc_dr");
DEFINE("DB_TBL2", "sms_out_hutc");
DEFINE("URL_BASE", "");
DEFINE("DR_STATUS_OK", "2");

// get parameters value
$in_trxids = isset($_GET["trxid"]) ? $_GET["trxid"] : (isset($_GET["trx_id"]) ? $_GET["trx_id"] : (isset($_GET["tid"]) ? $_GET["tid"] : ""));
$in_servid = isset($_GET["serviceid"]) ? $_GET["serviceid"] : (isset($_GET["sid"]) ? $_GET["sid"] : "");
$in_trxdtm = isset($_GET["trxdate"]) ? $_GET["trxdate"]	: (isset($_GET["date"]) ? $_GET["date"] : (isset($_GET["time"]) ? $_GET["time"] : date("YmdHis")));
$in_msisdn = isset($_GET["msisdn"]) ? $_GET["msisdn"] : (isset($_GET["dest"]) ? $_GET["dest"] : "");
$in_drstat = isset($_GET["status"]) ? $_GET["status"] : "";
$in_drtrid = isset($_GET["mtid"]) ? $_GET["mtid"] : (isset($_GET["tid"]) ? $_GET["tid"] : "");
$in_operat = isset($_GET["operator"]) ? $_GET["operator"] : (isset($_GET["provider"]) ? $_GET["provider"] : "hutch");

// trim leading and trailing spaces and standardize capitalization
$in_trxdtm = trim($in_trxdtm);
$in_servid = trim($in_servid);
$in_trxids = trim($in_trxids);
$in_msisdn = trim($in_msisdn);
$in_drstat = trim($in_drstat);
$in_drtrid = trim($in_drtrid);
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

$URL = URL_BASE;
$URL = $URL."?msisdn=".$in_msisdn;
$URL = $URL."&trxid=".$in_trxids;
$URL = $URL."&trxdate=".$in_trxdtm;
$URL = $URL."&operator=".$in_operat;
$URL = $URL."&status=".$in_drstat;
$URL = $URL."&serviceid=".$in_servid;
$URL = $URL."&mtid=".$in_drtrid;
$URL = $URL."&dest=".$in_msisdn;
$URL = $URL."&time=".$in_trxdtm;
$URL = $URL."&tid=".$in_trxids;

// create query to determine if record exist in database's storage table
$SQLIF = "SELECT IF(drstatus = '".DR_STATUS_OK."', true, false) AS res, IFNULL(keyword, '') AS var1 FROM ".DB_TBL2." WHERE trxid = '".$in_trxids."' AND msisdn = '".$in_msisdn."' LIMIT 1";

// create query - insert new record into queue
unset($arrf, $arrv);
$arrf[0] = "dtm";				$arrv[0] = "NOW()";
$arrf[1] = "trxid";				$arrv[1] = "'".$in_trxids."'";
$arrf[2] = "trxdtm";			$arrv[2] = "'".$in_trxdtm."'";
$arrf[3] = "msisdn";			$arrv[3] = "'".$in_msisdn."'";
$arrf[4] = "status";			$arrv[4] = "'".$in_drstat."'";
$arrf[5] = "sid";				$arrv[5] = "'".$in_servid."'";
$arrf[6] = "mtid";				$arrv[6] = "'".$in_drtrid."'";
$arrf[7] = "hit_url";			$arrv[7] = "'".$URL."'";
$arrf[8] = "keyword";			$arrv[8] = "'[var1]'";
$SQL1 = "INSERT INTO ".DB_TBL1." (".join(",", $arrf).") VALUES (".join(",", $arrv).")";

// create query - insert new record into storage if not exist
unset($arrf, $arrv);
$arrf[0] = "dtm";				$arrv[0] = "NOW()";
$arrf[1] = "trxid";				$arrv[1] = "'".$in_trxids."'";
$arrf[2] = "trxdtm";			$arrv[2] = "'".$in_trxdtm."'";
$arrf[3] = "msisdn";			$arrv[3] = "'".$in_msisdn."'";
$arrf[4] = "drstatus";			$arrv[4] = "'".$in_drstat."'";
$arrf[5] = "sid";				$arrv[5] = "'".$in_servid."'";
$arrf[6] = "drdtm";				$arrv[6] = "NOW()";
$SQL2 = "INSERT INTO ".DB_TBL2." (".join(",", $arrf).") VALUES (".join(",", $arrv).")";

// create query - update new record into storage if it is exist
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
	fprintf($afile, "%s\n", '    <mtrxid>'.$in_drtrid.'</mtrxid>');
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
	log_write($in_trxids, "finish", "counted as success\m");
	echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>0</status><msg>Message processed successfully</msg></push>';
}
else
{
	log_write($in_trxids, "  output", $path);
	log_write($in_trxids, "  error", "cannot write to output file");
	log_write($in_trxids, "finish", "counted as error\m");
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
?>