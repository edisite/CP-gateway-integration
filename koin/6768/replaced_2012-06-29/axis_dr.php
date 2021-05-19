<?php
if (function_exists("date_default_timezone_set")) { date_default_timezone_set("Asia/Jakarta"); }
$logid = md5(uniqid(rand(), true));
/*
	created by hengky irawan
	last modified by hengky irawan 2012-05-04 2012-05-04 23:32
*/

// constant declaration - code folding
DEFINE("DO_CHECK_PARAM", true);

// constant declaration - operator
DEFINE("SHORTCODE",	"6768");

// constant declaration - log path and naming - LOG_DIR.LOG_PFX.date(LOG_PTN).LOG_SFX.log
DEFINE("LOG_PFX", "");
DEFINE("LOG_PTN", "Y-m-d");
DEFINE("LOG_SFX", "_dr_rcv");
DEFINE("DIR_DR", "/opt/apps/".SHORTCODE."/queue/axis/dr/");
DEFINE("DIR_LOG", "/opt/apps/".SHORTCODE."/log/axis/");
DEFINE("DB_TBL1", "queue_axis_dr");
DEFINE("DB_TBL2", "sms_out_axis");
DEFINE("URL_BASE", "");
DEFINE("DR_STATUS_OK", "1");

// get parameters value
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

$in_shcode = $XML->adn;
$in_msisdn = $XML->msisdn;
$in_trxids = $XML->tid;
$in_servid = $XML->ccode;
$in_status = $XML->status;
$in_trxdtm = $XML->tdate;
$in_drstat = $XML->status;
$in_operat = "axis";
$in_drtrid = "";

$in_shcode = trim($in_shcode);
$in_msisdn = trim($in_msisdn);
$in_trxids = trim($in_trxids);
$in_servid = trim($in_servid);
$in_status = trim($in_status);
$in_trxdtm = trim($in_trxdtm);
$in_drstat = trim($in_drstat);
$in_operat = trim($in_operat);
$in_drtrid = trim($in_drtrid);

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
}

// forward DR
$URL = URL_BASE;
$URL = $URL."?msisdn=".$in_msisdn;
$URL = $URL."&trxid=".$in_trxids;
$URL = $URL."&trxdate=".rawurlencode($in_trxdtm);
$URL = $URL."&operator=".$in_operat;
$URL = $URL."&status=".$in_drstat;
$URL = $URL."&serviceid=".$in_servid;
$URL = $URL."&mtid=".$in_drtrid;
$URL = $URL."&dest=".$in_msisdn;
$URL = $URL."&time=".rawurlencode($in_trxdtm);
$URL = $URL."&tid=".$in_trxids;
log_write($in_trxids, "  URL", $URL);

// create query to determine if record exist in database's storage table
$SQLIF = "SELECT IF(drstatus = '".DR_STATUS_OK."', true, false) AS res FROM ".DB_TBL2." WHERE trxid = '".$in_trxids."' AND msisdn = '".$in_msisdn."' LIMIT 1";

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
$SQL1 = "INSERT INTO ".DB_TBL1." (".join(",", $arrf).") VALUES (".join(",", $arrv).")";

// create query - insert new record into storage if not exist
unset($arrf, $arrv);
$arrf[0] = "dtm";				$arrv[0] = "NOW()";
$arrf[1] = "trxid";				$arrv[1] = "'".$in_trxids."'";
$arrf[2] = "trxdtm";			$arrv[2] = "'".$in_trxdtm."'";
$arrf[3] = "msisdn";			$arrv[3] = "'".$in_msisdn."'";
$arrf[4] = "status";			$arrv[4] = "'".$in_drstat."'";
$arrf[5] = "sid";				$arrv[5] = "'".$in_servid."'";
$arrf[6] = "drdtm";				$arrv[6] = "NOW()";
$SQL2 = "INSERT INTO ".DB_TBL2." (".join(",", $arrf).") VALUES (".join(",", $arrv).")";

// create query - update new record into storage if it is exist
$SQL3 = "UPDATE ".DB_TBL2." SET drdtm = '".$in_trxdtm."', drstatus = '".$in_drstat."' WHERE trxid = '".$in_trxids."' AND msisdn = '".$in_msisdn."' AND sid = '".$in_servid."' LIMIT 1";

// create text file
$path = DIR_DR.date("YmdHis")."_".$in_trxids."_".md5(uniqid(rand(), true)).".dat";
if ($afile = fopen($path, "w"))
{
	fprintf($afile, "%s\n", '<?xml version="1.0" ?>');
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
		fprintf($objfile, "%s|7|dr|rcv|nothread|%-8s|%-15s|%s\n", date("Y-m-d H:i:s"), $ptrxid, $psubject, $pmsg);
	fclose($objfile);
}
?>