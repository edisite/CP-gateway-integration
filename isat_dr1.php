<?php
if (function_exists("date_default_timezone_set")) { date_default_timezone_set("Asia/Jakarta"); }
$logid = md5(uniqid(rand(), true));
/*
	created by hengky irawan
	last modified by hengky irawan 2012-05-04 23:32
*/

// constant declaration - code folding
DEFINE("DO_CHECK_PARAM", true);

// constant declaration
DEFINE("SHORTCODE",	"6768");
DEFINE("LOG_PFX", "");
DEFINE("LOG_PTN", "Y-m-d");
DEFINE("LOG_SFX", "_dr_rcv_1");
DEFINE("DIR_DR", "/opt/apps/".SHORTCODE."/queue/isat/dr/");
DEFINE("DIR_LOG", "/opt/apps/".SHORTCODE."/log/isat/");
DEFINE("DB_TBL1", "queue_isat_dr");
DEFINE("DB_TBL2", "sms_out_isat");
DEFINE("DB_TBL3", "freetalk_queue");
DEFINE("DB_TBL4", "freetalk_sent");

DEFINE("URL_BASE", "");
DEFINE("DR_STATUS_OK", "2");

DEFINE("DB_HOST", "10.1.1.9");
DEFINE("DB_USER", "edi");
DEFINE("DB_PSWD", "3disit3SQL");
DEFINE("DB_NAME", "mdw6768_isat");

DEFINE("DR_STATUS_OK", "2");

DEFINE("HAVING_FREETALK", true); // this is just for editor code folding
DEFINE("DAYFREETALK", 7);
DEFINE("KEYFREETALK", "freetalk");
//DEFINE("SIDDR2", "91550134034016"); // this is SID with charging (currently 2,000) that will be receiving freetalk
DEFINE("SIDDR2", "67680184034030"); // this is SID with charging (currently 2,000) that will be receiving freetalk

DEFINE("SIDNOCHARGE", "67680184001027"); // this is SID without charging for notifying subscriber
DEFINE("SIDFREETALK", "67680184034029|26687054500711"); // while sending freetalk use this SID
DEFINE("URLFREETALK", "http://10.1.1.89/koin/isat_mt_freetalk.php"); // this is url address where to send freetalk
DEFINE("URLMT", "http://10.1.1.89/handle/6768/isat_mt.php"); // this url address to notify subscriber

// get parameters value
$in_trxids = isset($_GET["trxid"]) ? $_GET["trxid"] : (isset($_GET["trx_id"]) ? $_GET["trx_id"] : (isset($_GET["tid"]) ? $_GET["tid"] : ""));
$in_servid = isset($_GET["serviceid"]) ? $_GET["serviceid"] : (isset($_GET["sid"]) ? $_GET["sid"] : "");
$in_trxdtm = isset($_GET["trxdate"]) ? $_GET["trxdate"]	: (isset($_GET["date"]) ? $_GET["date"] : (isset($_GET["time"]) ? $_GET["time"] : date("YmdHis")));
$in_msisdn = isset($_GET["msisdn"]) ? $_GET["msisdn"] : (isset($_GET["dest"]) ? $_GET["dest"] : "");
$in_drstat = isset($_GET["status"]) ? $_GET["status"] : "";
$in_drtrid = isset($_GET["mtid"]) ? $_GET["mtid"] : (isset($_GET["tid"]) ? $_GET["tid"] : "");
$in_operat = isset($_GET["operator"]) ? $_GET["operator"] : (isset($_GET["provider"]) ? $_GET["provider"] : "indosat");

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
$SQLIF = "SELECT IF(dreport = '".DR_STATUS_OK."', true, false) AS res, IFNULL(keyword, '') AS var1 FROM ".DB_TBL2." WHERE trxid = '".$in_trxids."' AND msisdn = '".$in_msisdn."' LIMIT 1";

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
	
	echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>0</status><msg>Message processed successfully</msg></push>';

	log_write($in_trxids, "finish", "counted as success\m");
}
else
{
	log_write($in_trxids, "  output", $path);
	log_write($in_trxids, "  error", "cannot write to output file");
	log_write($in_trxids, "finish", "counted as error\m");
	echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>5</status><msg>Cannot save message to file</msg></push>';
}

	$dbCN = mysql_connect(DB_HOST, DB_USER, DB_PSWD);
	mysql_select_db(DB_NAME);


	if (HAVING_FREETALK)
	{
		// delete this delivery report transaction id from freetalk queue table regardless if it is exist or not
		$cnt = 0;
		$SQL = "DELETE FROM ".DB_TBL3." WHERE trxid = '".$in_trxids."'";
		if (mysql_query($SQL, $dbCN))
		{
			$cnt = mysql_affected_rows($dbCN);
		}
		else
		{
			log_write($in_trxids, "error", $SQL);
			log_write($in_trxids, "error", "[".mysql_errno($dbCN)."] ".mysql_error($dbCN));
			log_write($in_trxids, "solution", "this is a fatal error, support must report this error immediately");
			log_write($in_trxids, "solution", "proceed process anyway, freetalk considered as not exist");
		}

		// freetalk is found in queue and have been deleted, then check if current msisdn allowed to receive free talk
		$dosend = 0;
		$deltaday = 0;
		if ($cnt > 0)
		{
			$SQL = "SELECT DATEDIFF(NOW(), last_dtm) AS dd FROM ".DB_TBL4." WHERE msisdn = '".$in_msisdn."'";
			log_write($in_trxid, "  freetalk ", $SQL);
			if ($que = mysql_query($SQL, $dbCN))
			{
				if ($rs = mysql_fetch_array($que))
				{
					$dosend = ($rs["dd"] >= DAYFREETALK) ? 2 : 1;
					$deltaday = DAYFREETALK - $rs["dd"];
				}
				else
				{
					$dosend = 2;
				}
			}
			else
			{
				log_write($in_trxid, "  error", $SQL);
				log_write($in_trxid, "  error", "[".mysql_errno($dbCN)."] ".mysql_error($dbCN));
				log_write($in_trxid, "  solution", "");
			}
		}

		// this is where we send freetalk
		if ($dosend == 0) { log_write($in_trxid, "  freetalk", "no freetalk for current delivery report"); }
		if ($dosend == 1) { log_write($in_trxid, "  freetalk", "freetalk already received in last ".DAYFREETALK." days"); }
		if ($dosend == 2) { log_write($in_trxid, "  freetalk", "send freetalk"); }
		if ($in_drstat == DR_STATUS_OK && ($in_servid == SIDDR2 or $in_servid == SIDDR204) && $dosend > 0)
		{
			$buffer = "";
			$tmp = $in_trxids.$in_msisdn.date("YmdHis").(substr("00".(rand(0, 99)), -2));
			if ($dosend == 2) // send freetalk
			{
				$URL = URLFREETALK."?uid=freetalk&pwd=freetalkpwd&serviceid=".SIDFREETALK."&msisdn=".$in_msisdn;
				$URL = $URL."&sms=".rawurlencode("Selamat, Kamu mendapat gratis bicara 60menit dari Program 9155 Indosat. Untuk cek gratis bicaramu ketik *555*2# kemudian OK/Yes/Dial");
				$URL = $URL."&smstype=0&transid=".$tmp."&shortcode=".SHORTCODE."&keyword=".KEYFREETALK."&price=0&delivery_method=pull&operator=".$in_operat;
			}
			else // send common mt to notify customer
			{
				$URL = URLMT."?uid=freetalk&pwd=freetalkpwd&serviceid=".SIDNOCHARGE."&msisdn=".$in_msisdn;
				$URL = $URL."&sms=".rawurlencode("Mohon maaf, gratis bicara 60 menit hanya dapat diperoleh sekali dalam ".DAYFREETALK." hari (atau ".$deltaday." hari lagi)");
				$URL = $URL."&smstype=0&transid=".$tmp."&shortcode=".SHORTCODE."&keyword=".KEYFREETALK."&price=0&delivery_method=pull&operator=".$in_operat;
			}

			log_write($in_trxids, "  freetalk", "begin     |freetalk sid detected, creating freetalk mt");
			log_write($in_trxids, "  freetalk", "  trxid   |".$tmp);
			log_write($in_trxids, "  freetalk", "  url mt  |".$URL);
			$handle = fopen($URL, "r");
			if ($handle)
			{
				while (!feof($handle)) { $buffer .= fgets($handle, 4096); }
				fclose($handle);
				log_write($in_trxids, "  freetalk", "  response|".$buffer);
				log_write($in_trxids, "  freetalk", "finish    |counted as success\m");
			}
			else
			{
				log_write($in_trxids, "  freetalk", "  error   |cannot open mt url address");
				log_write($in_trxids, "  freetalk", "  solution|free talk for this msisdn ".$in_msisdn." is not processed, continue to next dr process");
				log_write($in_trxids, "  freetalk", "finish    |counted as error\m");
			}
		}
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