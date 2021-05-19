<?php
if (function_exists("date_default_timezone_set")) { date_default_timezone_set("Asia/Jakarta"); }
$logid = md5(uniqid(rand(), true));
/*
	created by hengky irawan
	last modified by hengky irawan 2012-05-04 2012-05-04 23:32
*/

include("_flxi_setting.php");
include("_flxi_keyword2url.php");

// constant declaration
DEFINE("DO_SET_OTHERS", true);
DEFINE("DO_CHECK_PARAM", true);
DEFINE("DO_GET_PARAMETERS", true);
DEFINE("DO_CHECK_DB_CONNECTION", true);

DEFINE("LOG_PFX", "");
DEFINE("LOG_PTN", "Y-m-d");
DEFINE("DIR_LOG", "/opt/apps/".SHORTCODE."/log/".OPERATOR."/");

DEFINE("OPERATOR_URL_FREE", "http://spm.telkomflexi.com:8080/api/push0.php");
DEFINE("OPERATOR_URL_CHARGE", "http://spm.telkomflexi.com:8080/spmapi/push.php");

// constant declaration - mysql database parameter
DEFINE("DB_TBL1", "queue_flxi_mt");
DEFINE("DB_TBL2", "sms_out_flxi");
DEFINE("MT_STATUS_OK", "OK");

if (DO_SET_OTHERS)
{
	$LOG_SFX[1] = "_mo_rcv";
	$LOG_SFX[2] = "_mo_dsp";
	$LOG_SFX[3] = "_mo_tmt";
	$LOG_SFX[4] = "_mt_rcv";
	$LOG_SFX[5] = "_mt_dsp";
	$LOG_SFX[6] = "_mt_tmt";
	$LOG_SFX[7] = "_dr_rcv";
	$LOG_SFX[8] = "_dr_dsp";
	$LOG_SFX[9] = "_dr_tmt";

	$arr_type["0"] = "text";
	$arr_type["1"] = "wap";
	$arr_type["2"] = "binary";
}

// sub-procedure: mt receiver ----------------------------------------------------------------------------------------------------
$flowid = "4|mt|rcv";
if (DO_GET_PARAMETERS)
{
	$in_userid = isset($_GET["uid"]) ? $_GET["uid"] : "";
	$in_passwd = isset($_GET["pwd"]) ? $_GET["pwd"] : "";
	$in_trxids = isset($_GET["trxid"]) ? $_GET["trxid"] : "";
	$in_msisdn = isset($_GET["msisdn"]) ? $_GET["msisdn"] : "";
	$in_servid = isset($_GET["sid"]) ? $_GET["sid"] : "";
	$in_mttype = isset($_GET["mttype"]) ? $_GET["mttype"] : "push";	// pull, push
	$in_keywod = isset($_GET["keyword"]) ? $_GET["keyword"] : "";
	$in_contyp = isset($_GET["contype"]) ? $_GET["contype"] : "0";	// 0, 1, 2 (text, wap, binary)
	$in_conten = isset($_GET["content"]) ? $_GET["content"] : "";

	$in_userid = trim($in_userid);
	$in_passwd = trim($in_passwd);
	$in_trxids = trim($in_trxids);
	$in_msisdn = trim($in_msisdn);
	$in_servid = trim($in_servid);
	$in_mttype = strtolower(trim($in_mttype));
	$in_keywod = trim($in_keywod);
	$in_contyp = trim($in_contyp);
	$in_conten = trim($in_conten);
}

if (DO_CHECK_PARAM)
{
	if ($in_trxids == "")
	{
		$errno = 401;
		log_write($flowid, $logid, "begin", "[".$errno."] invalid parameters (trxid)");
		log_write($flowid, $logid, "  received", $_SERVER["REQUEST_URI"]);
		log_write($flowid, $logid, "  error", "[".$errno."] invalid parameters (trxid)");
		log_write($flowid, $logid, "  solution", "message not processed, response error to sender");
		log_write($flowid, $logid, "finish", "counted as error\m");
		echo '<?xml version="1.0"?><response><status>'.$errno.'</status><trxid>'.$in_trxids.'<trxid><msg>invalid parameters (trxid)</msg></response>';
		return;
	}

	log_write($flowid, $in_trxids, "begin", "");
	log_write($flowid, $in_trxids, "  received", $_SERVER["REQUEST_URI"]);
	if ($in_keywod == "")
	{
		$errno = 402;
		log_write($flowid, $in_trxids, "  error", "[".$errno."] invalid parameters (keyword)");
		log_write($flowid, $in_trxids, "  solution", "message not processed, response error to sender");
		log_write($flowid, $in_trxids, "finish", "counted as error\m");
		echo '<?xml version="1.0"?><response><status>402</status><trxid>'.$in_trxids.'<trxid><msg>invalid parameters (keyword)</msg></response>';
		return;
	}
	$tmp1 = strtoupper(SHORTCODE."_".$in_keywod."_");
	$tmp2 = strtoupper(SHORTCODE."#".$in_keywod."#");
	$n = strlen($tmp1);
	$chk = strtoupper(substr($in_trxids, 0, $n));
	if (($tmp1 != $chk) && ($tmp2 != $chk))
	{
		$errno = 403;
		log_write($flowid, $in_trxids, "  error", "[".$errno."] invalid format (trxid)");
		log_write($flowid, $in_trxids, "  solution", "message not processed, response error to sender");
		log_write($flowid, $in_trxids, "finish", "counted as error\m");
		echo '<?xml version="1.0"?><response><status>'.$errno.'</status><trxid>'.$in_trxids.'<trxid><msg>invalid format (trxid)</msg></response>';
		return;
	}
}

if (DO_CHECK_DB_CONNECTION)
{
	$dbCN = @mysql_connect(DB_HOST, DB_USER, DB_PSWD);
	if (!$dbCN)
	{
		$errno = 1001;
		log_write($flowid, $in_trxids, "  error", FATAL_ERROR);
		log_write($flowid, $in_trxids, "  error", "[".$errno."] cannot open database connection");
		log_write($flowid, $in_trxids, "  solution", "message not processed, response error to sender");
		log_write($flowid, $in_trxids, "finish", "counted as error\m");
		echo '<?xml version="1.0"?><response><status>'.$errno.'</status><trxid>'.$in_trxids.'<trxid><msg>internal error [database connection failed]</msg></response>';
		return;
	}
	else
	{
		$tmp = @mysql_select_db(DB_NAME, $dbCN);
		if (!$tmp)
		{
			$errno = 1002;
			log_write($flowid, $in_trxids, "  error", FATAL_ERROR);
			log_write($flowid, $in_trxids, "  error", "[".$errno."] cannot open selected database");
			log_write($flowid, $in_trxids, "  solution", "message not processed, response error to sender");
			log_write($flowid, $in_trxids, "finish", "counted as error\m");
			echo '<?xml version="1.0"?><response><status>'.$errno.'</status><trxid>'.$in_trxids.'<trxid><msg>internal error (open database failed)</msg></response>';
			@mysql_close($dbCN);
			return;
		}
	}
}
log_write($flowid, $in_trxids, "finish", "counted as error\m");

// sub-procedure: mt dispatcher --------------------------------------------------------------------------------------------------
$flowid = "5|mt|dsp";
log_write($flowid, $in_trxids, "begin", "");
log_write($flowid, $in_trxids, "  info", "this system does not having mt dispatcher procedure, data will be saved into db upon transmitter");
log_write($flowid, $in_trxids, "finish", "counted as success\m");

// sub-procedure: mt transmitter -------------------------------------------------------------------------------------------------
$flowid = "6|mt|tmt";
log_write($flowid, $in_trxids, "begin", "");
log_write($flowid, $in_trxids, "  received", $_SERVER["REQUEST_URI"]);
log_write($flowid, $in_trxids, "  keyword", $in_keywod);

$tmp = strtolower($in_keywod);
$prt = $keywordowner[$tmp];
$in_partner = isset($prt) ? $prt : "";
log_write($flowid, $in_trxids, "  partner", $in_partner);

// save data into database
$arrf[0] = "dtm";            $arrv[0] = "NOW()";
$arrf[1] = "trxid";          $arrv[1] = "'".$in_trxids."'";
$arrf[2] = "trxdtm";         $arrv[2] = "NOW()";
$arrf[3] = "operator";       $arrv[3] = "'".OPERATOR."'";
$arrf[4] = "substype";       $arrv[4] = "''";
$arrf[5] = "msisdn";         $arrv[5] = "'".$in_msisdn."'";
$arrf[6] = "keyword";        $arrv[6] = "'".rawurlencode($in_keywod)."'";
$arrf[7] = "smstype";        $arrv[7] = "'".$in_contyp."'";
$arrf[8] = "partner";        $arrv[8] = "'".$in_partner."'";
$arrf[9] = "source";         $arrv[9] = "''";
$arrf[10] = "sid";           $arrv[10] = "'".$in_servid."'";
$arrf[11] = "dmethod";       $arrv[11] = "'".$in_mttype."'";
$arrf[12] = "sms";           $arrv[12] = "'".rawurlencode($in_conten)."'";
$arrf[13] = "isms";          $arrv[13] = "''";
$arrf[14] = "drdtm";         $arrv[14] = "''";
$arrf[15] = "drstatus";      $arrv[15] = "''";
$SQL = "INSERT INTO ".DB_TBL2." (".join(",", $arrf).") VALUES (".join(",", $arrv).")";
log_write($flowid, $in_trxids, "  save2db", $SQL);
if (mysql_query($SQL, $dbCN) == false)
{
	if (mysql_errno() == 1062)
	{
		$errno = 601;
		log_write($flowid, $in_trxids, "  error", "[".$errno."] query failed, transaction id already exist");
		log_write($flowid, $in_trxids, "  error", "[".mysql_errno($dbCN)."] ".mysql_error($dbCN));
		log_write($flowid, $in_trxids, "  solution", "message not processed, response error to sender");
		log_write($flowid, $in_trxids, "finish", "counted as error\m");
		echo '<?xml version="1.0"?><response><status>'.$errno.'</status><trxid>'.$in_trxids.'<trxid><msg>transaction id already exist</msg></response>';
		@mysql_close($dbCN);
		return;
	}
	$errno = 602;
	log_write($flowid, $in_trxids, "  error", "[".$errno."] query failed");
	log_write($flowid, $in_trxids, "  error", "[".mysql_errno($dbCN)."] ".mysql_error($dbCN));
	log_write($flowid, $in_trxids, "  solution", "message not processed, response error to sender");
	log_write($flowid, $in_trxids, "finish", "counted as error\m");
	echo '<?xml version="1.0"?><response><status>'.$errno.'</status><trxid>'.$in_trxids.'<trxid><msg>internal error (query failed)</msg></response>';
	@mysql_close($dbCN);
	return;
}
@mysql_close($dbCN);

// send data to operator
$XML = "";
$XML = $XML.'<?xml version="1.0"?>';
$XML = $XML.'<message>';
	$XML = $XML.'<sms type="mt">';
		$XML = $XML.'<destination messageid="'.$in_trxids.'">';
			$XML = $XML.'<address>';
				$XML = $XML.'<number type="national">'.$in_msisdn.'</number>';
			$XML = $XML.'</address>';
		$XML = $XML.'</destination>';
		$XML = $XML.'<source>';
			$XML = $XML.'<address>';
				$XML = $XML.'<number type="abbreviated">'.SHORTCODE.'</number>';
			$XML = $XML.'</address>';
		$XML = $XML.'</source>';
		$XML = $XML.'<ud type="'.$arr_type[$in_contyp].'">'.$in_conten.'</ud>';
		$XML = $XML.'<rsr type="all"/>';
	$XML = $XML.'</sms>';
$XML = $XML.'</message>';
log_write($flowid, $in_trxids, "  send post", $XML);
$response = trim(xml_post($in_servid, $XML));
log_write($flowid, $in_trxids, "  response", $response);
if (strtoupper($response) == MT_STATUS_OK)
{
	log_write($flowid, $in_trxids, "  response", "response is success");
}
else
{
	log_write($flowid, $in_trxids, "  response", "invalid response");
}
log_write($flowid, $in_trxids, "finish", "counted as success\m");
echo '<?xml version="1.0"?><response><status>0</status><trxid>'.$in_trxids.'<trxid><msg>message processed successfull</msg></response>';

// SUBROUTINE, WRITE LOG TO FILE #################################################################################################
function log_write($pflowid, $ptrxid, $psubject, $pmsg)
{
	global $LOG_SFX;
	$n = substr($pflowid, 0, 1);
	$path = DIR_LOG.LOG_PFX.date(LOG_PTN).$LOG_SFX[$n].".log";
	$objfile = fopen($path, "a");
		chmod($path, 0777);
		$pmsg = str_replace("\n", "", $pmsg);
		$pmsg = str_replace("\m", "\n", $pmsg);
		fprintf($objfile, "%s|".$pflowid."|nothread|%-8s|%-15s|%s\n", date("Y-m-d H:i:s"), $ptrxid, $psubject, $pmsg);
	fclose($objfile);
}

// FUNCTION, XML POST ############################################################################################################
function xml_post($sid, $data)
{
	$n = strlen($sid);
	$n1 = ($n > 0) ? substr($sid, -1) : "";
	$n2 = ($n > 1) ? substr($sid, -2, 1) : "";
	if ($n1 == "0" && $n2 != "0") { $URL = OPERATOR_URL_FREE; }
	else { $URL = OPERATOR_URL_CHARGE; }

	$n = strlen($data);
	$CURL = curl_init();
	curl_setopt($CURL, CURLOPT_URL, $URL);
	curl_setopt($CURL, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($CURL, CURLOPT_USERPWD, "676818:Puls4");
	curl_setopt($CURL, CURLOPT_POST, 1);
	curl_setopt($CURL, CURLOPT_POSTFIELDS, $data);
	curl_setopt($CURL, CURLOPT_HTTPHEADER, array("Content-Type: text/xml", "Content-length: ".$n));
	curl_setopt($CURL, CURLOPT_RETURNTRANSFER, 1);
	$res = curl_exec($CURL);
	curl_close($CURL);
	return $res;
}