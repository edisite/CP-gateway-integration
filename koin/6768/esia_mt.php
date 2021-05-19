<?php
if (function_exists("date_default_timezone_set")) { date_default_timezone_set("Asia/Jakarta"); }

// constant declaration - these declarations just to help code folding while coding
DEFINE("DO_CHECK_PARAM", true);
DEFINE("DO_SET_SVCKEYS_PUSH", true);
DEFINE("DO_SET_OTHERS", true);
DEFINE("DO_GET_PARAMETERS", true);
DEFINE("DO_SET_WAP_PARAM", true);
DEFINE("DO_SET_CONTENTID", true);

// constant declaration - operator
DEFINE("SHORTCODE",	"6768");

// constant declaration - log path and naming - LOG_DIR.LOG_PFX.date(LOG_PTN).LOG_SFX.log
DEFINE("LOG_PFX", "");
DEFINE("LOG_PTN", "Y-m-d");
DEFINE("LOG_SFX", "_mt_rcv");
DEFINE("DIR_MT", "/opt/apps/".SHORTCODE."/queue/esia/mt/");
DEFINE("DIR_MT2", "/opt/apps/".SHORTCODE."/queue/esia/mt2/");
DEFINE("DIR_LOG", "/opt/apps/".SHORTCODE."/log/esia/");
DEFINE("DIR_IP", "/opt/apps/".SHORTCODE."/queue/esia/ipaddr/");
DEFINE("DB_TBL1", "queue_esia_mt");
DEFINE("DB_TBL2", "sms_out_esia");
DEFINE("DR_STATUS_OK", "2");
DEFINE("BIN_UDH", "06050415821582");	// UDH for operator logo (currently specific for telkomsel)
DEFINE("BIN_OPR", "15F001");			// this value identify the operator in use (currently specific for telkomsel)
										// if BIN_OPR value is set and it's value is exist as prefix in the incoming message then
										// remove it from incoming message
if (DO_SET_OTHERS)
{
	// index is related to $in_smstyp
	$mttype["0"] = "sms";
	$mttype["1"] = "wappush";
	$mttype["2"] = "bin";
}
if (DO_SET_WAP_PARAM)
{
	$wap_pushdc_cpid["telkomsel"] = "MCPGLOBAL";
	$wap_pushdc_pswd["telkomsel"] = "mcp661global";
	$wap_pushdc_ctnm["telkomsel"] = "GAME";
}
if (DO_SET_CONTENTID)
{
	$cid["telkomsel"]["pulsareg1000"] = "";
	$cid["telkomsel"]["pulsareg2000"] = "";
	$cid["telkomsel"]["mds_y_c04101_0000_pull"] = "";
	$cid["telkomsel"]["pulsasms_pull500"] = "";
	$cid["telkomsel"]["pulsa67681000"] = "";
	$cid["telkomsel"]["pulsa67682000"] = "";
	$cid["telkomsel"]["tabloidpulsasms_pull_5k"] = "";
	$cid["telkomsel"]["tabloidpulsasms_pull_10k"] = "";
	$cid["telkomsel"]["tabloidpulsasms_pull_15k"] = "";
	$cid["telkomsel"]["pulsasm3300"] = "pulsa_sm_3000_smspull";
	$cid["telkomsel"]["tabloidpulsa_wp_dc_5k"] = "TabloidPulsa_WP_DC_5k";
	$cid["telkomsel"]["pulsarich5500"] = "pulsa_rich5000_wappush";
	$cid["telkomsel"]["tabloidpulsa_wpdc8k"] = "TabloidPulsa_WPDC8k";
	$cid["telkomsel"]["pulsarich8800"] = "PulsaRich8000";
	$cid["telkomsel"]["pulsarich11000"] = "pulsa_rich10000_wappush";
	$cid["telkomsel"]["tabloidpulsa_wpdc10k"] = "TabloidPulsa_WPDC10k";
	$cid["telkomsel"]["pulsawp15000"] = "pulsawp15000";
	$cid["telkomsel"]["mds_y_c02111_3300_wc"] = "MDS_Y_C02111_3300_WC";
	$cid["telkomsel"]["mds_y_c02111_5500_wc"] = "MDS_Y_C02111_5500_WC";
	$cid["telkomsel"]["pulsa_wc5k"] = "Pulsa_WC5k";
	$cid["telkomsel"]["pulsa_wc8k"] = "Pulsa_WC8k";
	$cid["telkomsel"]["mds_y_c02111_8800_wc"] = "MDS_Y_C02111_8800_WC";
	$cid["telkomsel"]["pulsa_wc10k"] = "Pulsa_WC10k";
	$cid["telkomsel"]["mds_y_c02111_010k_wc"] = "MDS_Y_C02111_010K_WC";
	$cid["telkomsel"]["pulsa_wc15k"] = "Pulsa_WC15k";
	$cid["telkomsel"]["mds_y_c04101_0000_push"] = "smspush0";
	$cid["telkomsel"]["pulsasms_push_500"] = "";
	$cid["telkomsel"]["pulsareg1000"] = "";
	$cid["telkomsel"]["pulsareg2000"] = "";
	$cid["telkomsel"]["mds_y_c04101_5500_push"] = "smspush5500";
	$cid["telkomsel"]["mds_y_c04003_010k_push"] = "smspush11000";
	$cid["telkomsel"]["mds_y_c04003_015k_push"] = "smspush16500";
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
	$in_shcode = isset($_GET["shortcode"]) ? $_GET["shortcode"] : "";
	$in_keywod = isset($_GET["keyword"]) ? $_GET["keyword"] : "";
	$in_prices = isset($_GET["price"]) ? $_GET["price"] : "";
	$in_dlvmtd = isset($_GET["delivery_method"]) ? $_GET["delivery_method"] : "pull";
	$in_operat = isset($_GET["operator"]) ? $_GET["operator"] : (isset($_GET["provider"]) ? $_GET["provider"]: "telkomsel");

	$in_userid = trim($in_userid);
	$in_passwd = trim($in_passwd);
	$in_srvcid = trim($in_srvcid);
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
		log_write($in_trxids, "finish", "counted as error\m");
		return;
	}
	if (!is_numeric($in_msisdn))
	{
		echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>3</status><msg>invalid msisdn number ['.$in_msisdn.']</msg></push>';
		log_write($in_trxids, "  error", "invalid msisdn number [".$in_msisdn."]");
		log_write($in_trxids, "finish", "counted as error\m");
		return;
	}
	if ($in_operat == "")
	{
		echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>4</status><msg>missing operator</msg></push>';
		log_write($in_trxids, "  error", "missing operator");
		log_write($in_trxids, "finish", "counted error\m");
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

// get service keys
$tmp = strtolower($in_keywod);
$svckey = ""; // this value only set for XL operator

// build hit url address
$prefix = "";
$URL = "";
if ($in_smstyp == "0") // sms text
{
	$HITMTD = "get";
	log_write($in_trxids, "  sms type", "text message");
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
	log_write($in_trxids, "  sms type", "wap message");
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
	log_write($in_trxids, "  response", $response);

	$linkurl = substr($in_smstxt, $pos_http + 1); 		// extract url address from message
	$n = stripos($linkurl, " ");						// remove trailing content which is not part of url address
	if ($n > 1) { $linkurl = substr($linkurl, 0, $n); }
	log_write($in_trxids, "  link url", $linkurl);

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

	log_write($in_trxids, "  sms type", "binary message");
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
	log_write($in_trxids, "  URL", $URL);
}

// create query to save to text file 1 of 4 - query storage table for record existence
$SQL1 = "SELECT IF(drstatus = '".DR_STATUS_OK."', true, false) AS eval FROM ".DB_TBL2." WHERE trxid = '".$in_trxids."' AND msisdn = '".$in_msisdn."' LIMIT 1";

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
$arrf[10] = "hit_url";			$arrv[10] = "'".$URL."'";
$arrf[11] = "hit_mtd";			$arrv[11] = "'".$HITMTD."'";
$SQL2 = "INSERT INTO ".DB_TBL1." (".join(",", $arrf).") VALUES (".join(",", $arrv).")";

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
$SQL3 = "INSERT INTO ".DB_TBL2." (".join(",", $arrf).") VALUES (".join(",", $arrv).")";

// create query to save to text file 4 of 4 - query storage table to update existing record
$SQL4 = "UPDATE ".DB_TBL2." SET sid = '".$in_srvcid."' WHERE trxid = '".$in_trxids."' AND msisdn = '".$in_msisdn."' LIMIT 1";

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
	log_write($in_trxids, "  output", $path);
	log_write($in_trxids, "finish", "counted as success\m");
	echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>0</status><msg>Message processed successfully</msg></push>';
	fclose($afile);
}
else
{
	log_write($in_trxids, "  error", "cannot save data to file");
	log_write($in_trxids, "finish", "counted as error\m");
	echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>6</status><msg>cannot save data to file</msg></push>';
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