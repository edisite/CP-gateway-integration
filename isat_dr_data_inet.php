<?php
/*
	functions and capabilities:
*/

// constant declaration - code folding
DEFINE("DO_CHECK_PARAM", true);

// constant declaration - operator
DEFINE("SHORTCODE",	"6768");

// constant declaration - log path and naming - LOG_DIR.LOG_PFX.date(LOG_PTN).LOG_SFX.log
DEFINE("LOG_PFX", "");
DEFINE("LOG_PTN", "Y-m-d");
DEFINE("LOG_SFX", "_dr_freetalk");
DEFINE("DIR_DR", "/opt/apps/".SHORTCODE."/queue/isat/dr/");
DEFINE("DIR_LOG", "/opt/apps/".SHORTCODE."/log/isat/");
DEFINE("DB_TBL1", "queue_isat_dr");
DEFINE("DB_TBL2", "sms_out_isat");
DEFINE("URL_BASE", "");
DEFINE("SIDDR2", "46500112034010");
DEFINE("SIDFREETALK", "46500112034009|26687020200711");
DEFINE("KEYFREETALK", "crmfreetalk");

DEFINE("DR_STATUS_OK", "2");

DEFINE("URLFREETALK", "http://127.0.0.1/465/isat/isat_mt_freetalk.php");

// get parameters value
$in_trxdtm = trim(isset($_GET["trxdate"]) ? $_GET["trxdate"] : date("YmdHis"));
$in_svceid = trim(isset($_GET["serviceid"]) ? $_GET["serviceid"] : (isset($_GET["sid"]) ? $_GET["sid"] : ""));
$in_trxids = trim(isset($_GET["trxid"]) ? $_GET["trxid"] : (isset($_GET["trx_id"]) ? $_GET["trx_id"] : (isset($_GET["tid"]) ? $_GET["tid"] : "")));
$in_msisdn = trim(isset($_GET["msisdn"]) ? $_GET["msisdn"] : (isset($_GET["dest"]) ? $_GET["dest"] : ""));
$in_drstat = trim(isset($_GET["status"]) ? $_GET["status"] : "");
$in_operat = trim(isset($_GET["operator"]) ? $_GET["operator"] : "indosat");
$in_operat = strtolower(trim($in_operat));

// check parameters value
if ($in_trxids == "")
{
	echo '<? xml version="1.0" ?><push><transid>'.$in_trxids.'</transid><status>1</status><msg>missing transaction id</msg></push>';
	log_write($in_operat, "error", "error", "missing transaction id");
	return;
}
log_write($in_operat, $in_trxids, "begin", "");
log_write($in_operat, $in_trxids, "  received", $_SERVER["REQUEST_URI"]);
if (DO_CHECK_PARAM)
{
	if (strlen($in_msisdn) < 6)
	{
		echo '<? xml version="1.0" ?><push><transid>'.$in_trxids.'</transid><status>2</status><msg>invalid msisdn length ['.strlen($in_msisdn).']['.$in_msisdn.']</msg></push>';
		log_write($in_operat, $in_trxids, "  error", "invalid msisdn length [".strlen($in_msisdn)."][".$in_msisdn."]");
		log_write($in_operat, $in_trxids, "finish", "error\m");
		return;
	}
	if (!is_numeric($in_msisdn))
	{
		echo '<? xml version="1.0" ?><push><transid>'.$in_trxids.'</transid><status>3</status><msg>invalid msisdn number ['.$in_msisdn.']</msg></push>';
		log_write($in_operat, $in_trxids, "  error", "invalid msisdn number [".$in_msisdn."]");
		log_write($in_operat, $in_trxids, "finish", "error\m");
		return;
	}
	if ($in_operat == "")
	{
		echo '<? xml version="1.0" ?><push><transid>'.$in_trxids.'</transid><status>4</status><msg>missing operator</msg></push>';
		log_write($in_operat, $in_trxids, "  error", "missing operator");
		log_write($in_operat, $in_trxids, "finish", "error\m");
		return;
	}
}

// hit mt url to create free talk service
if ($in_svceid == SIDDR2)
{
	$buffer = "";
	$tmp = $in_trxids.$in_msisdn.date("YmdHis").(substr("00".(rand(0, 99)), -2));
	$URL = URLFREETALK."?uid=freetalk&pwd=freetalkpwd&serviceid=".SIDFREETALK."&msisdn=".$in_msisdn;
	$URL = $URL."&sms=".rawurlencode("Kamu mendapat gratis bicara 60 menit berlaku selama 1 minggu dari Program 6768 atau *323# Indosat. Untuk cek gratis bicaramu ketik *555*2# kemudian OK/Yes/Dial")."&smstype=0&transid=".$tmp."&shortcode=".SHORTCODE;
	$URL = $URL."&keyword=".KEYFREETALK."&price=0&delivery_method=pull&operator=".$in_operat;

	log_write($in_operat, $in_trxids, "  freetalk", "begin     |free talk sid detected, creating free talk mt");
	log_write($in_operat, $in_trxids, "  freetalk", "  trxid   |".$tmp);
	log_write($in_operat, $in_trxids, "  freetalk", "  url mt  |".$URL);
	$handle = fopen($URL, "r");
	if ($handle)
	{
		while (!feof($handle)) { $buffer .= fgets($handle, 4096); }
		fclose($handle);
		log_write($in_operat, $in_trxids, "  freetalk", "  response|".$buffer);
		log_write($in_operat, $in_trxids, "  freetalk", "finish    |success\m");
	}
	else
	{
		log_write($in_operat, $in_trxids, "  freetalk", "  error   |cannot open mt url address");
		log_write($in_operat, $in_trxids, "  freetalk", "  solution|free talk for this msisdn ".$in_msisdn." is not processed, continue to next dr process");
		log_write($in_operat, $in_trxids, "  freetalk", "finish    |error\m");
	}
}

// build url address for forwarding delivery report
$URL = URL_BASE;
$URL = $URL."?msisdn=".$in_msisdn;
$URL = $URL."&trxid=".$in_trxids;
$URL = $URL."&trxdate=".$in_trxdtm;
$URL = $URL."&operator=".$in_operat;
$URL = $URL."&status=".$in_drstat;
$URL = $URL."&serviceid=".$in_svceid;
$URL = $URL."&mtid=".$in_drtrid;
$URL = $URL."&dest=".$in_msisdn;
$URL = $URL."&time=".$in_trxdtm;
$URL = $URL."&tid=".$in_trxids;

// create query to determine if record exist in database's storage table
$SQLIF = "SELECT IF(drstatus = '".DR_STATUS_OK."', true, false) AS res FROM ".DB_TBL2." WHERE msisdn = '".$in_msisdn."' and keyword = 'freetalk' and drstatus is null ORDER BY `sms_out_isat`.`dtm`  DESC";

// create query - insert new record into queue
unset($arrf, $arrv);
$arrf[0] = "dtm";				$arrv[0] = "NOW()";
$arrf[1] = "trxid";				$arrv[1] = "'".$in_trxids."'";
$arrf[2] = "trxdtm";			$arrv[2] = "'".$in_trxdtm."'";
$arrf[3] = "msisdn";			$arrv[3] = "'".$in_msisdn."'";
$arrf[4] = "status";			$arrv[4] = "'".$in_drstat."'";
$arrf[5] = "sid";				$arrv[5] = "'".$in_svceid."'";
$arrf[6] = "mtid";				$arrv[6] = "'".$in_drtrid."'";
$arrf[7] = "hit_url";			$arrv[7] = "'".$URL."'";
//$SQL1 = "INSERT INTO ".DB_TBL1." (".join(",", $arrf).") VALUES (".join(",", $arrv).")";

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
$SQL3 = "UPDATE ".DB_TBL2." SET drdtm = '".$in_trxdtm."', drstatus = '".$in_drstat."' WHERE msisdn = '".$in_msisdn."' AND keyword = 'freetalk' and drstatus IS NULL LIMIT 1";
log_write($in_operat, $in_trxids, "  freetalk", "  url mt  |".$URL);
log_write($in_operat, $in_trxids, "  freetalk", "  url mt  |".$SQLIF);
log_write($in_operat, $in_trxids, "  freetalk", "  url mt  |".$SQL1);
log_write($in_operat, $in_trxids, "  freetalk", "  url mt  |".$SQL2);
log_write($in_operat, $in_trxids, "  freetalk", "  url mt  |".$SQL3);
// create text file
$path = DIR_DR.date("YmdHis")."_".$in_trxids."_".md5(uniqid(rand(), true)).".dat";
if ($afile = fopen($path, "w"))
{
	fprintf($afile, "%s\n", '<? xml version="1.0" ?>');
	fprintf($afile, "%s\n", '<data>');
	fprintf($afile, "%s\n", '    <trxdate>'.$in_trxdtm.'</trxdate>');
	fprintf($afile, "%s\n", '    <trxid>'.$in_trxids.'</trxid>');
	fprintf($afile, "%s\n", '    <msisdn>'.$in_msisdn.'</msisdn>');
	fprintf($afile, "%s\n", '    <sid>'.$in_svceid.'</sid>');
	fprintf($afile, "%s\n", '    <status>'.$in_drstat.'</status>');
	fprintf($afile, "%s\n", '    <mtrxid>'.$in_drtrid.'</mtrxid>');
	fprintf($afile, "%s\n", '    <operator>'.$in_operat.'</operator>');
	fprintf($afile, "%s\n", '</data>');
	fprintf($afile, "%s\n", '<queries>');
	fprintf($afile, "%s\n", '    <sql_if><dbcn>2</dbcn><sql>'.$SQLIF.'</sql></sql_if>');
	fprintf($afile, "%s\n", '    <sql_if_empty1><dbcn>1</dbcn><sql></sql></sql_if_empty1>');
	fprintf($afile, "%s\n", '    <sql_if_empty2><dbcn>2</dbcn><sql>'.$SQL2.'</sql></sql_if_empty2>');
	fprintf($afile, "%s\n", '    <sql_if_exist_true1><dbcn></dbcn><sql></sql></sql_if_exist_true1>');
	fprintf($afile, "%s\n", '    <sql_if_exist_true2><dbcn></dbcn><sql></sql></sql_if_exist_true2>');
	fprintf($afile, "%s\n", '    <sql_if_exist_false1><dbcn>1</dbcn><sql>'.$SQL1.'</sql></sql_if_exist_false1>');
	fprintf($afile, "%s\n", '    <sql_if_exist_false2><dbcn>2</dbcn><sql>'.$SQL3.'</sql></sql_if_exist_false2>');
	fprintf($afile, "%s\n", '</queries>');
	fclose($afile);

	log_write($in_operat, $in_trxids, "  output", $path);
	log_write($in_operat, $in_trxids, "finish", "success\m");
	echo '<? xml version="1.0" ?><push><transid>'.$in_trxids.'</transid><status>0</status><msg>Message processed successfully</msg></push>';
}
else
{
	log_write($in_trxids, "  output", $path);
	log_write($in_trxids, "  error", "cannot write to output file");
	log_write($in_trxids, "finish", "counted as error\m");
	echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>5</status><msg>Cannot save message to file</msg></push>';
}

// SUBROUTINE, WRITE LOG TO FILE ################################################################################################
function log_write($poperator, $ptrxid, $psubject, $pmsg)
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
