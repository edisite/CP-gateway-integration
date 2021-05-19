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
DEFINE("LOG_SFX", "_mt_rcv_3p");
DEFINE("DIR_MT", "/opt/apps/".SHORTCODE."/queue/isat/mt/");
DEFINE("DIR_LOG", "/opt/apps/".SHORTCODE."/log/timwee/");
DEFINE("DIR_IP", "/opt/apps/".SHORTCODE."/queue/isat/ipaddr/");
DEFINE("DIR_MT_hutc", "/opt/apps/".SHORTCODE."/queue/hutch/mt/");
DEFINE("DIR_IP_hutc", "/opt/apps/".SHORTCODE."/queue/hutch/ipaddr/");
DEFINE("DB_TBL1", "queue_isat_mt");
DEFINE("DB_TBL2", "sms_out_isat");

DEFINE("DB_TBL1huct", "queue_hutc_mt");
DEFINE("DB_TBL2huct", "sms_out_hutc");

DEFINE("DB_TBL1axis", "queue_axis_mt");
DEFINE("DB_TBL2axis", "sms_out_axis");

DEFINE("DB_TBL1tsel", "queue_tsel_mt");
DEFINE("DB_TBL2tsel", "sms_out_tsel");

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
	$in_shcode = isset($_GET["shortcode"]) ? $_GET["shortcode"] : "6768";
	$in_keywod = isset($_GET["keyword"]) ? $_GET["keyword"] : "";
	$in_prices = isset($_GET["price"]) ? $_GET["price"] : "";
	$in_dlvmtd = isset($_GET["delivery_method"]) ? $_GET["delivery_method"] : "pull";
	$in_operat = isset($_GET["operator"]) ? $_GET["operator"] : (isset($_GET["provider"]) ? $_GET["provider"] : "");

	$in_userid = trim($in_userid);
	$in_passwd = trim($in_passwd);
	$in_srvcid = trim($in_srvcid);
	$in_msisdn = trim($in_msisdn);
	$in_smstxt = trim($in_smstxt);
	$in_smstyp = trim($in_smstyp);
	$in_trxids = trim($in_trxids);
	$in_shcode = trim($in_shcode);
	$in_keywod = strtolower(trim($in_keywod));
	$in_prices = trim($in_prices);
	$in_dlvmtd = strtolower(trim($in_dlvmtd));
	$in_operat = strtolower(trim($in_operat));
}

// modify value of sms type for indosat so it's always point to 0 or sms type of plain text 
// this is specifically requested by Saptadi by email in "Re: Pendaftaran Keyword dan PB Ayu Dewi" - Wed 6/13/2012 1:58 PM
$in_smstyp = "0";

// check parameters value
if($in_userid != "twid" && $in_passwd != "twpass")
{
	echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>10</status><msg>missing user id</msg></push>';
	log_write_isat("error", "error", "missing transaction id");
	exit;

}
if ($in_trxids == "")
{
	echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>1</status><msg>missing transaction id</msg></push>';
	log_write_isat("error", "error", "missing transaction id");
	return;
}

log_write_isat($in_trxids, "begin", "");
log_write_isat($in_trxids, "  received", $_SERVER["REQUEST_URI"]);
if (DO_CHECK_PARAM)
{
	if (strlen($in_msisdn) < 6)
	{
		echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>2</status><msg>invalid msisdn length ['.strlen($in_msisdn).']['.$in_msisdn.']</msg></push>';
		log_write_isat($in_trxids, "  error", "invalid msisdn length [".strlen($in_msisdn)."][".$in_msisdn."]");
		log_write_isat($in_trxids, "finish", "counted as error\m");
		return;
	}
	if (!is_numeric($in_msisdn))
	{
		echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>3</status><msg>invalid msisdn number ['.$in_msisdn.']</msg></push>';
		log_write_isat($in_trxids, "  error", "invalid msisdn number [".$in_msisdn."]");
		log_write_isat($in_trxids, "finish", "counted as error\m");
		return;
	}
	if ($in_operat == "")
	{
		echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>4</status><msg>missing operator</msg></push>';
		log_write_isat($in_trxids, "  error", "missing operator");
		log_write_isat($in_trxids, "finish", "counted as error\m");
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

if($in_operat == "isat")
{

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
		$URL = $URL."&sdmcode=".($in_keywod.SHORTCODE);
		log_write_isat($in_trxids, "  URL", $URL);

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

			log_write_isat($in_trxids, "  output", $path);
			log_write_isat($in_trxids, "finish", "counted as success\m");
			echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>0</status><msg>Message processed successfully</msg></push>';
		}
		else
		{
			log_write_isat($in_trxids, "  error", "cannot save data to file");
			log_write_isat($in_trxids, "finish", "counted as error\m");
			echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>6</status><msg>cannot save data to file</msg></push>';
		}
}
elseif($in_operat == "hutc")
{
		// get service keys
		$tmp = strtolower($in_keywod);
		$svckey = ""; // this value only set for XL operator

		// build hit url address
		$HITMTD = "get";
		$URL = "";	// compatible for esia, flexi, hutch, m8
		$prefix = "";
		$URL = $prefix."?msisdn=".$in_msisdn;
		$URL = $URL."&trxid=".$in_trxids;
		$URL = $URL."&trxdate=".date("YmdHis");
		$URL = $URL."&operator=".$in_operat;
		$URL = $URL."&mttype=".$mttype[$in_smstyp];
		$URL = $URL."&mttext=".rawurlencode($in_smstxt);
		$URL = $URL."&sid=".$in_srvcid;
		$URL = $URL."&username=".$in_userid;
		$URL = $URL."&password=".$in_passwd;
		$URL = $URL."&contentid="."";
		$URL = $URL."&trtype=".$in_dlvmtd;
		$URL = $URL."&price=".$in_prices;
		log_write_hutc($in_trxids, "  URL", $URL);

		// create query - query storage table for record existence
		$SQL1 = "SELECT IF(drstatus = '".DR_STATUS_OK."', true, false) AS eval FROM ".DB_TBL2huct." WHERE trxid = '".$in_trxids."' AND msisdn = '".$in_msisdn."' LIMIT 1";

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
		$SQL2 = "INSERT INTO ".DB_TBL1huct." (".join(",", $arrf).") VALUES (".join(",", $arrv).")";

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
		$SQL3 = "INSERT INTO ".DB_TBL2huct." (".join(",", $arrf).") VALUES (".join(",", $arrv).")";

		// create query - query storage table to update existing record
		$SQL4 = "UPDATE ".DB_TBL2huct." SET sid = '".$in_srvcid."' WHERE trxid = '".$in_trxids."' AND msisdn = '".$in_msisdn."' LIMIT 1";

		// create text file
		$path = DIR_MT_hutc.date("YmdHis")."_".$in_trxids."_".md5(uniqid(rand(), true)).".dat";
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

			log_write_hutc($in_trxids, "  output", $path);
			log_write_hutc($in_trxids, "finish", "counted as success\m");
			echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>0</status><msg>Message processed successfully</msg></push>';
		}
		else
		{
			log_write_hutc($in_trxids, "  error", "cannot save data to file");
			log_write_hutc($in_trxids, "finish", "counted as error\m");
			echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>6</status><msg>cannot save data to file</msg></push>';
		}

}
elseif($in_operat == "axis")
{
		// build hit url address
		log_write_axis($in_trxids, "  msg type", "text message");

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
		log_write_axis($in_trxids, "  URL", $URL);

		// create query - query storage table for record existence
		$SQL1 = "SELECT IF(drstatus = '".DR_STATUS_OK."', true, false) AS eval FROM ".DB_TBL2axis." WHERE trxid = '".$in_trxids."' AND msisdn = '".$in_msisdn."' LIMIT 1";

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
		$SQL2 = "INSERT INTO ".DB_TBL1axis." (".join(",", $arrf).") VALUES (".join(",", $arrv).")";

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
		$SQL3 = "INSERT INTO ".DB_TBL2axis." (".join(",", $arrf).") VALUES (".join(",", $arrv).")";

		// create query - query storage table to update existing record
		$SQL4 = "UPDATE ".DB_TBL2axis." SET sid = '".$in_servid."' WHERE trxid = '".$in_trxids."' AND msisdn = '".$in_msisdn."' LIMIT 1";

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

			log_write_axis($in_trxids, "  output", $path);
			log_write_axis($in_trxids, "finish", "counted as success\m");
			echo '<?xml version="1.0" ?><push><transid>'.$in_trxids.'</transid><status>0</status><msg>Message processed successfully</msg></push>';
		}
		else
		{
			log_write_axis($in_trxids, "  error", "cannot create file [".$path."]");
			log_write_axis($in_trxids, "  solution", "skip and do not process this message, error response sent back to sender");
			log_write_axis($in_trxids, "finish", "counted as error\m");
			echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>0</status><msg>Message processed successfully</msg></push>';
			return;
		}
}
elseif($in_operat == "tsel")
{
	// get service keys
	$tmp = strtolower($in_keywod);
	$svckey = ""; // this value only set for XL operator

	// get source address
	$prefix = $url_prefix[$in_operat."_".$in_dlvmtd."_".$in_smstyp];
	log_write_tsel($in_trxids, "  index", $in_operat."_".$in_dlvmtd."_".$in_smstyp);
	log_write_tsel($in_trxids, "  prefix", $prefix);

	// build hit url address
	$URL = "";
	if ($in_smstyp == "0") // sms text
	{
		$HITMTD = "get";
		log_write_tsel($in_trxids, "  sms type", "text message");
		if ($in_dlvmtd == "pull") // default as text message
		{
			$URL = $prefix."?appsid=".$in_userid."&pwd=".$in_passwd."&sid=".$in_srvcid."&msisdn=".$in_msisdn."&sms=".rawurlencode($in_smstxt)."&trx_id=".$in_trxids;
		}
		else
		{
			$URL = $prefix."?msisdn=".$in_msisdn."&cp_name=".$in_userid."&pwd=".$in_passwd."&sid=".$in_srvcid."&sms=".rawurlencode($in_smstxt);
		}
	}
	elseif ($in_smstyp == "1") // wap
	{
		$HITMTD = "post";
		log_write_tsel($in_trxids, "  sms type", "wap message");
		$pos_http = stripos($in_smstxt, "~http");
		if ($pos_http > 5)
		{
			$response = substr($in_smstxt, 0, $pos_http);
			if (strlen($response) > 70) { $response = substr($response, 0, 67)."..."; }
		}
		else
		{
			$response = "Click Goto to download";
		}
		log_write_tsel($in_trxids, "  response", $response);

		$linkurl = substr($in_smstxt, $pos_http + 1); 		// extract url address from message
		$n = stripos($linkurl, " ");						// remove trailing content which is not part of url address
		if ($n > 1) { $linkurl = substr($linkurl, 0, $n); }
		log_write_tsel($in_trxids, "  link url", $linkurl);

		$contentidprice = $cid[$in_operat][strtolower($in_srvcid)];
		$XML = '';
		$XML = $XML.'<wap-push>';
			$XML = $XML.'<url>'.$linkurl.'</url>';
			$XML = $XML.'<msisdn_sender>'.$in_msisdn.'</msisdn_sender>';
			$XML = $XML.'<msisdn_receipient>'.$in_msisdn.'</msisdn_receipient>';
			$XML = $XML.'<sid>'.$in_srvcid.'</sid>';
			$XML = $XML.'<text>'.$response.'</text>';
			$XML = $XML.'<trx_id>'.$in_trxids.'</trx_id>';
			$XML = $XML.'<trx_date>'.date("YmdHis").'</trx_date>';
			$XML = $XML.'<contentid>'.$contentidprice.'</contentid>';
		$XML = $XML.'</wap-push>';
		$URL = $prefix.'?cpid='.$in_userid.'&pwd='.$in_passwd.'&msg='.rawurlencode($XML);
	}
	else // binary
	{
		$HITMTD = "post";

		log_write_tsel($in_trxids, "  sms type", "binary message");
		$XML = '';
		$XML = $XML.'<smartmessaging>';
			$XML = $XML.'<status>00</status>';
			$XML = $XML.'<trx_date>'.date("YmdHis").'</trx_date>';
			$XML = $XML.'<trx_id>'.$in_trxids.'</trx_id>';
			$XML = $XML.'<contentid>'.$cid[$in_operat][strtolower($in_srvcid)].'</contentid>';
			$XML = $XML.'<sid>'.$in_srvcid.'</sid>';
			$XML = $XML.'<msisdn_sender>'.$in_msisdn.'</msisdn_sender>';
			$XML = $XML.'<msisdn_recipient>'.$in_msisdn.'</msisdn_recipient>';
			$XML = $XML.'<packet totalpacket="1">';
				$tmp = "";
				$tmp = strtoupper($in_smstxt);
				$tmp = substr($tmp, 0, strlen(BIN_OPR));
				$tmp = ($tmp == BIN_OPR) ? substr($in_smstxt, strlen(BIN_OPR)) : $in_smstxt;
				$XML = $XML.'<data pkt="1" udh="'.BIN_UDH.BIN_OPR.'" msgsrc="'.$tmp.'" />';
			$XML = $XML.'</packet>';
		$XML = $XML.'</smartmessaging>';
		$URL = $prefix.'?cpid='.$in_userid.'&pwd='.$in_passwd.'&type=OL&msg='.rawurlencode($XML);
		log_write_tsel($in_trxids, "  URL", $URL);
	}

	// create query to save to text file 1 of 4 - query storage table for record existence
	$SQL1 = "SELECT IF(drstatus = '".DR_STATUS_OK."', true, false) AS eval FROM ".DB_TBL2tsel." WHERE trxid = '".$in_trxids."' AND msisdn = '".$in_msisdn."' LIMIT 1";

	// create query to save to text file 2 of 4 - query queue table to insert new record
	unset($arrf, $arrv);
	$arrf[0] = "dtm";				$arrv[0] = "NOW()";
	$arrf[1] = "trxid";				$arrv[1] = "'".$in_trxids."'";
	$arrf[2] = "sid";				$arrv[2] = "'".$in_srvcid."'";
	$arrf[3] = "msisdn";			$arrv[3] = "'".$in_msisdn."'";
	$arrf[4] = "sms";				$arrv[4] = "'".rawurlencode($in_smstxt)."'";
	$arrf[5] = "type";				$arrv[5] = "'".$in_smstyp."'";
	$arrf[6] = "method";			$arrv[6] = "'".$in_dlvmtd."'";
	$arrf[7] = "keyword";			$arrv[7] = "'".rawurlencode($keyword)."'";
	$arrf[8] = "partneruserid";		$arrv[8] = "'".$in_userid."'";
	$arrf[9] = "partnerpasswd";		$arrv[9] = "'".$in_passwd."'";
	$arrf[10] = "hit_mtd";			$arrv[10] = "'".$HITMTD."'";
	if ($in_dlvmtd == "pull")
	{
		$arrf[11] = "hit_url";			$arrv[11] = "CONCAT('http://', tied_value, '".$URL."')";
		$SQL2 = "INSERT INTO ".DB_TBL1tsel." (".join(",", $arrf).") SELECT ".join(",", $arrv)." FROM ".DB_TBL1tsel." WHERE trxid = '".$in_trxids."' LIMIT 1";
	}
	else
	{
		$arrf[11] = "hit_url";			$arrv[11] = "'".$URL."'";
		$SQL2 = "INSERT INTO ".DB_TBL1tsel." (".join(",", $arrf).") VALUES (".join(",", $arrv).")";
	}

	// create query to save to text file 3 of 4 - query storage table to insert new record
	unset($arrf, $arrv);
	$arrf[0] = "dtm";		$arrv[0] = "NOW()";
	$arrf[1] = "trxid";		$arrv[1] = "'".$in_trxids."'";
	$arrf[2] = "msisdn";	$arrv[2] = "'".$in_msisdn."'";
	$arrf[3] = "keyword";	$arrv[3] = "'".$in_keywod."'";
	$arrf[4] = "smstype";	$arrv[4] = "'".$in_smstyp."'";
	$arrf[5] = "partner";	$arrv[5] = "'".$in_userid."'";
	$arrf[6] = "sid";		$arrv[6] = "'".$in_srvcid."'";
	$arrf[7] = "dmethod";	$arrv[7] = "'".$in_dlvmtd."'";
	$arrf[8] = "sms";		$arrv[8] = "'".$in_smstxt."'";
	$SQL3 = "INSERT INTO ".DB_TBL2tsel." (".join(",", $arrf).") VALUES (".join(",", $arrv).")";

	// create query to save to text file 4 of 4 - query storage table to update existing record
	$SQL4 = "UPDATE ".DB_TBL2tsel." SET sid = '".$in_srvcid."' WHERE trxid = '".$in_trxids."' AND msisdn = '".$in_msisdn."' LIMIT 1";

	// create text file
	$XML = '<?xml version="1.0"?>';
	$XML = $XML.'<data>';
	$XML = $XML.'    <userid>'.$in_userid.'</userid>';
	$XML = $XML.'    <password>'.$in_passwd.'</password>';
	$XML = $XML.'    <trxid>'.$in_trxids.'</trxid>';
	$XML = $XML.'    <msisdn>'.$in_msisdn.'</msisdn>';
	$XML = $XML.'    <sid>'.$in_srvcid.'</sid>';
	$XML = $XML.'    <smstype>'.$in_smstyp.'</smstype>';
	$XML = $XML.'    <keyword>'.rawurlencode($in_keywod).'</keyword>';
	$XML = $XML.'    <sms>'.rawurlencode($in_smstxt).'</sms>';
	$XML = $XML.'    <dlvmtd>'.$in_dlvmtd.'</dlvmtd>';
	$XML = $XML.'    <operator>'.$in_operat.'</operator>';
	$XML = $XML.'</data>';
	$XML = $XML.'<queries>';
	$XML = $XML.'    <sql_if><dbcn>2</dbcn><sql>'.$SQL1.'</sql></sql_if>';
	$XML = $XML.'    <sql_if_empty1><dbcn>1</dbcn><sql>'.$SQL2.'</sql></sql_if_empty1>';
	$XML = $XML.'    <sql_if_empty2><dbcn>2</dbcn><sql>'.$SQL3.'</sql></sql_if_empty2>';
	$XML = $XML.'    <sql_if_exist_true1></sql_if_exist_true1>';
	$XML = $XML.'    <sql_if_exist_false1><dbcn>1</dbcn><sql>'.$SQL2.'</sql></sql_if_exist_false1>';
	$XML = $XML.'    <sql_if_exist_false2><dbcn>2</dbcn><sql>'.$SQL4.'</sql></sql_if_exist_false2>';
	$XML = $XML.'</queries>';

	$path = DIR_MT.date("YmdHis")."_".$in_trxids."_".md5(uniqid(rand(), true)).".dat";
	if ($afile = fopen($path, "w"))
	{
		fprintf($afile, "%s\n", $XML);
		log_write_tsel($in_trxids, "  output", $path);
		log_write_tsel($in_trxids, "finish", "counted as success\m");
		echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>0</status><msg>Message processed successfully</msg></push>';
		fclose($afile);
	}
	else
	{
		log_write_tsel($in_trxids, "  error", "cannot save data to file");
		log_write_tsel($in_trxids, "finish", "counted as error\m");
		echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>6</status><msg>cannot save data to file</msg></push>';
	}
}
elseif($in_operat == "xl")
		{
				$sc = "6768";
				$trxdate = date("YmdHis");
				
				
				//include("crypt.php");
				
				
				$error = 0;
				//include("seriviceid.php");
				log_write_xl("",$sc,$in_trxids,"--- Start -----"); 
				log_write_xl($in_smstxt,$sc,$in_trxids," in_smstxt : "); 
				log_write_xl($in_trxids,$sc,$in_trxids," in_trxids : ");  	
				//http://<ip>:<port>/?uid=<..>&pwd=<..>&in_srvcid =<..>&in_msisdn=<..>&in_smstxt=<..>&in_trxids=<..>&in_smstyp=<..>&in_dlvmtd=<..>
				// text or wap push
				// check parameter
				$lensid = strlen($in_srvcid );
				$lenmsisdn = strlen($in_msisdn);
				$lensms = strlen($in_smstxt);
				$lentransid = strlen($in_trxids);
				$lenshortcode = strlen($sc);
				//echo $sc."<br>";
				if (($lensid <5) || ($lenmsisdn <5) || ($lensms <5) || ($lentransid <2)|| ($sc == "error")) {
						$error = 1;
						log_write_xl($error,$sc,$in_trxids," Parameter Not Complite:");			
					} 	
				
				$log_ins = "('".$in_msisdn."','".$in_trxids."','".$in_srvcid ."','".$in_smstxt."','".$in_smstyp."','".$sc."','".$in_dlvmtd."')";			
				
				log_write_xl($log_ins,$sc,$in_trxids," Incomming form CP "); 
				
				if ($error == 0) {
					
					
						mysql_pconnect("10.1.1.75","hengky","h3ngkyp4ss");
						mysql_select_db('mdw6768_xl'); 

						$sqlcheck = mysql_query("SELECT dna, age, sid FROM queue_exel_mt WHERE in_msisdn='".$in_msisdn."' and trx_id='".$in_trxids."'");
						$num = mysql_num_rows($sqlcheck);
					
						if ($num > 0) {
							
								log_write_xl($idreply,$sc,$in_trxids,"Doble : "); 
								$error = 2; 
							
						} else {	
							
							include "xl/keyword.php";
							
							
								$myquery = "INSERT INTO queue_exel_mt (dtm,trxdtm,type,msisdn,trxid,operator,partner,sc,keyword,dmethod,sid, shortname,sms) VALUES  ";
								$myquery = $myquery."(now(),'";
								$myquery = $myquery. $trxdate;
								$myquery = $myquery. "','".$type."','";
								$myquery = $myquery. $in_msisdn;
								$myquery = $myquery. "','";
								$myquery = $myquery. $in_trxids;
								$myquery = $myquery. "','";
								$myquery = $myquery. "xl";				
								$myquery = $myquery. "','";
								$myquery = $myquery. $cpid;
								$myquery = $myquery. "','";
								$myquery = $myquery. $sc;
								$myquery = $myquery. "','";
								$myquery = $myquery. $in_keywod;
								$myquery = $myquery. "','".$in_dlvmtd."','".$in_srvcid ."','".$shortname."','".rawurlencode($in_smstxt)."')";
								
								$myquery_o = "INSERT INTO sms_out_exel (dtm,trxdtm,smstype,msisdn,trxid,operator,partner,shortcode,keyword,dmethod,sid,sms) VALUES  ";
								$myquery_o  = $myquery_o."(now(),'";
								$myquery_o  = $myquery_o. $trxdate;
								$myquery_o  = $myquery_o. "','".$type."','";
								$myquery_o  = $myquery_o. $in_msisdn;
								$myquery_o  = $myquery_o. "','";
								$myquery_o  = $myquery_o. $in_trxids;
								$myquery_o  = $myquery_o. "','";
								$myquery_o  = $myquery_o. "xl";
								$myquery_o  = $myquery_o. "','";
								$myquery_o  = $myquery_o. $cpid;
								$myquery_o  = $myquery_o. "','";
								$myquery_o = $myquery_o. $sc;
								$myquery_o = $myquery_o. "','";
								$myquery_o = $myquery_o. $in_keywod;
								$myquery_o = $myquery_o. "','".$in_dlvmtd."','".$in_srvcid ."','".rawurlencode($in_smstxt)."')";	
								
								log_write_xl($myquery,$sc,$in_trxids," DB Query :  ");					
								$mysqlquery = mysql_query($myquery);
								$myerror = mysql_error();
								log_write_xl($myerror,$sc,$in_trxids," DB Result :  ");
								mysql_query("COMMIT");

								log_write_xl($myquery_o,$sc,$in_trxids," DB Query :  ");
								$mysqlquery = mysql_query($myquery_o);
								$myerror_o = mysql_error();
								log_write_xl($myerror_o,$sc,$in_trxids," DB Result :  ");
								mysql_query("COMMIT");
					
								
						}
					mysql_close();						
				
					if ($error == 0) 
					{
						log_write_xl("Done" ,$sc,$in_trxids,"Result : ");			
					} 
					else 
					{
						log_write_xl("Do Nothing" ,$sc,$in_trxids,"Doble : ");
					}	
				} 
			//	mysql_close();	
				
				if ($error == 0) {	
					echo "<?xml version=\"1.0\" ?><push><status>0</status><in_trxids>".$in_trxids."</in_trxids><msg>Message processed successfully</msg></push>";
				} else {
					if ($error == 2) {
					 "<?xml version=\"1.0\" ?><push><status>14</status><in_trxids>".$in_trxids."</in_trxids><msg>Doble</msg></push>";
					} else {
					echo "<?xml version=\"1.0\" ?><push><status>-1</status><in_trxids>".$in_trxids."</in_trxids><msg>Parameter Incomplete</msg></push>";
					}
				}

}
else
{
		echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>11</status><msg> operator</msg></push>';
		log_write_isat($in_trxids, "  error", "missing operator");
		log_write_isat($in_trxids, "finish", "counted as error\m");
		return;
}

// SUBROUTINE, WRITE LOG TO FILE #################################################################################################
function log_write_isat($ptrxid, $psubject, $pmsg)
{
	$path = DIR_LOG.LOG_PFX.date(LOG_PTN).LOG_SFX.".log";
	$objfile = fopen($path, "a");
		chmod($path, 0777);
		$pmsg = str_replace("\n", "", $pmsg);
		$pmsg = str_replace("\m", "\n", $pmsg);
		fprintf($objfile, "%s|4|mt|rcv|isat|%-8s|%-15s|%s\n", date("Y-m-d H:i:s"), $ptrxid, $psubject, $pmsg);
	fclose($objfile);
}
function log_write_hutc($ptrxid, $psubject, $pmsg)
{
	$path = DIR_LOG.LOG_PFX.date(LOG_PTN).LOG_SFX.".log";
	$objfile = fopen($path, "a");
		chmod($path, 0777);
		$pmsg = str_replace("\n", "", $pmsg);
		$pmsg = str_replace("\m", "\n", $pmsg);
		fprintf($objfile, "%s|4|mt|rcv|hutch|%-8s|%-15s|%s\n", date("Y-m-d H:i:s"), $ptrxid, $psubject, $pmsg);
	fclose($objfile);
}
function log_write_axis($ptrxid, $psubject, $pmsg)
{
	$path = DIR_LOG.LOG_PFX.date(LOG_PTN).LOG_SFX.".log";
	$objfile = fopen($path, "a");
		chmod($path, 0777);
		$pmsg = str_replace("\n", "", $pmsg);
		$pmsg = str_replace("\m", "\n", $pmsg);
		fprintf($objfile, "%s|4|mt|rcv|axis|%-8s|%-15s|%s\n", date("Y-m-d H:i:s"), $ptrxid, $psubject, $pmsg);
	fclose($objfile);
}
function log_write_tsel($ptrxid, $psubject, $pmsg)
{
	$path = DIR_LOG.LOG_PFX.date(LOG_PTN).LOG_SFX.".log";
	$objfile = fopen($path, "a");
		chmod($path, 0777);
		$pmsg = str_replace("\n", "", $pmsg);
		$pmsg = str_replace("\m", "\n", $pmsg);
		fprintf($objfile, "%s|4|mt|rcv|tsel|%-8s|%-15s|%s\n", date("Y-m-d H:i:s"), $ptrxid, $psubject, $pmsg);
	fclose($objfile);
}
function log_write_xl($param,$sc,$transid_s,$action) 
{
	$tanggal_access = date('Y-m-d');	
	if ($sc == "error") 
	{
		$dir_acc = "/opt/apps/6768/log/";
	} 
	else 
	{
		$dir_acc = "/opt/apps/6768/log/timwee/";
	}
	$file_log = $dir_acc.$tanggal_access."_mt_rcv_3p.log";	
	chmod($file_log, 0777);	
	$handle = fopen($file_log, 'a+');
	//echo "masuk".date("Y-m-d H:i:s").";".$transid_s.";".$action.";".$param."\n"."<br>";
	fprintf($handle, date("Y-m-d H:i:s"));
	fprintf($handle," %s | ",$transid_s);
	fprintf($handle," %s | ",$action);
	fprintf($handle," %s ",$param."\n");
	fclose($handle);
	
}s
?>
