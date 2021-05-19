<?php
include "../../koin/mc/common_6768_isat.php";
/*
	functions and capabilities:
		- receive mt from multiple operators
		- save incoming mt into text files having xml contents
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
DEFINE("LOG_SFX", "_mt_freetalk");
DEFINE("DIR_MT", "/opt/apps/".SHORTCODE."/queue/isat/mt/");
DEFINE("DIR_LOG", "/opt/apps/".SHORTCODE."/log/isat/");
DEFINE("DIR_IP", "");

DEFINE("DB_HOST", "10.1.1.9");
DEFINE("DB_USER", "edi");
DEFINE("DB_PSWD", "3disit3SQL");
DEFINE("DB_NAME", "mdw6768_isat");
DEFINE("DB_TBL1", "queue_isat_mt");
DEFINE("DB_TBL2", "sms_out_isat");
DEFINE("DB_TBL3", "freetalk_queue");
DEFINE("DB_TBL4", "freetalk_sent");

DEFINE("INCOMING_UID", "indosat");
DEFINE("INCOMING_PWD", "indosat");
DEFINE("OUTGOING_UID", "PIndomedia");
DEFINE("OUTGOING_PWD", "PInd0");
DEFINE("FREETALK_UID", "freetalk");

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
	$in_servid = isset($_GET["serviceid"]) ? $_GET["serviceid"] : "";
	$in_msisdn = isset($_GET["msisdn"]) ? $_GET["msisdn"] : "";
	$in_smstxt = isset($_GET["sms"]) ? $_GET["sms"] : "";
	$in_smstyp = isset($_GET["smstype"]) ? $_GET["smstype"] : "";
	$in_trxids = isset($_GET["transid"]) ? $_GET["transid"] : "";
	$in_shcode = isset($_GET["shortcode"]) ? $_GET["shortcode"] : "";
	$in_keywod = isset($_GET["keyword"]) ? $_GET["keyword"] : "";
	$in_prices = isset($_GET["price"]) ? $_GET["price"] : "";
	$in_dlvmtd = isset($_GET["delivery_method"]) ? $_GET["delivery_method"] : "";
	$in_operat = isset($_GET["operator"]) ? $_GET["operator"] : (isset($_GET["provider"]) ? $_GET["provider"] : "indosat");

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
}

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
		log_write($in_trxids, "finish", "error\m");
		return;
	}
	if (!is_numeric($in_msisdn))
	{
		echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>3</status><msg>invalid msisdn number ['.$in_msisdn.']</msg></push>';
		log_write($in_trxids, "  error", "invalid msisdn number [".$in_msisdn."]");
		log_write($in_trxids, "finish", "error\m");
		return;
	}
	if ($in_operat == "")
	{
		echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>4</status><msg>missing operator</msg></push>';
		log_write($in_trxids, "  error", "missing operator");
		log_write($in_trxids, "finish", "error\m");
		return;
	}
	$arr = split(" ", str_replace("  ", " ", strtolower($in_smstxt)));
	if ($arr[0] == "reg" || $arr[0] == "set" || $arr[0] == "unreg" || $arr[0] == "unrek" || $arr[0] == "stop" || $arr[0] == "setop" || $arr[0] == "unset")
	{
		$keyword = $arr[1];
	}
	else
	{
		$keyword = $arr[0];
	}
}

// check if database connection is available
$dbCN = @mysql_connect(DB_HOST, DB_USER, DB_PSWD);
if (!$dbCN)
{
	log_write($in_trxids, "  error", "database connection failed");
	log_write($in_trxids, "  solution", "proceed process anyway, sending freetalk for this dr must be determined manually");
}

$tmp = @mysql_select_db(DB_NAME, $dbCN);
if (!$tmp)
{
	log_write($in_trxids, "  error", "open database failed");
	log_write($in_trxids, "  solution", "proceed process anyway, sending freetalk for this dr must be determined manually");
}

// save or update freetalk sent to database
$SQL = "REPLACE INTO ".DB_TBL4." (msisdn, last_dtm, last_trxid) VALUES ('".$in_msisdn."', NOW(), '".$in_trxids."')";
if (mysql_query($SQL, $dbCN))
{
	log_write($in_trxids, "  freetalk", "successfully saved into database");
}
else
{
	log_write($in_trxids, "  error", $SQL);
	log_write($in_trxids, "  error", "[".mysql_errno($dbCN)."] ".mysql_error($dbCN));
	log_write($in_trxids, "  solution", "cannot save freetalk to database, to avoid sending accumulative freetalk this process must be aborted");
	log_write($in_trxids, "  solution", "this is a fatal error, please contact developer as soon as possible");
	log_write($in_trxids, "finish", "counted as error\m");
	@mysql_close($dbCN);
	return;
}

//------------------------------
$partnerID = getCPID($in_keywod);
// get service keys
$tmp = strtolower($in_keywod);
$svckey = "";

// build hit url address
$prefix = "";
$URL = "";
$HITMTD = "get";


	$in_smstxt = str_ireplace("~http", "http", $in_smstxt);
	if ($in_userid == FREETALK_UID)
	{
		$URL = $prefix."?uid=".OUTGOING_UID."&pwd=".OUTGOING_PWD."&serviceid=".$in_servid."&msisdn=".$in_msisdn."&sms=".rawurlencode($in_smstxt)."&transid=&smstype=0";
	}
	else
	{
		$URL = $prefix."?uid=".OUTGOING_UID."&pwd=".OUTGOING_PWD."&serviceid=".$in_servid."&msisdn=".$in_msisdn."&sms=".rawurlencode($in_smstxt)."&transid=".$in_trxids."&smstype=0";
	}

// create query to save to text file 3 of 4 - query storage table to insert new record
unset($arrf, $arrv);
$arrf[0] = "dtm";		$arrv[0] = "NOW()";
$arrf[1] = "trxid";		$arrv[1] = "'".$in_trxids."'";
$arrf[2] = "msisdn";	$arrv[2] = "'".$in_msisdn."'";
$arrf[3] = "keyword";	$arrv[3] = "'".$in_keywod."'";
$arrf[4] = "smstype";	$arrv[4] = "'".$in_smstyp."'";
$arrf[5] = "partner";	$arrv[5] = "'".OUTGOING_UID."'";
$arrf[6] = "sid";		$arrv[6] = "'".$in_servid."'";
$arrf[7] = "dmethod";	$arrv[7] = "'".$in_dlvmtd."'";
$arrf[8] = "reply";		$arrv[8] = "'".$in_smstxt."'";

$SQL3 = "INSERT INTO ".DB_TBL2." (".join(",", $arrf).") VALUES (".join(",", $arrv).")";

log_write($in_trxids, "  smsout", $SQL3);

log_write($in_trxids, "  send get ", $URL);
$handle = fopen($URL, "r");
if($handle){
	while (!feof($handle)) {
				$buffer .= fgets($handle, 4096);
	   }
	fclose($handle);
	$is_sending = trim($buffer);

	log_write($in_trxids, "  Responses", $is_sending);
} 
else {
	log_write($in_trxids, "  Response", "Error");
}
mysql_query($SQL3);

@mysql_close($dbCN);


echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>0</status><msg>Message processed successfully</msg></push>';

// SUBROUTINE, WRITE LOG TO FILE ################################################################################################
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