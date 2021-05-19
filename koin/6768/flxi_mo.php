<?php
if (function_exists("date_default_timezone_set")) { date_default_timezone_set("Asia/Jakarta"); }
$logid = md5(uniqid(rand(), true));
/*
	created by hengky irawan
	last modified by hengky irawan 2012-05-04 2012-05-04 23:32
*/
include("_flxi_setting.php");
include("_flxi_keyword2url.php");
include("_foul_words.php");

// constant declaration - code folding
DEFINE("DO_CHECK_PARAM", true);

// constant declaration - mysql database parameter
DEFINE("DB_TBL1", "queue_flxi_mo");
DEFINE("DB_TBL2", "sms_out_flxi");
DEFINE("DR_STATUS_OK", "success");

// constant declaration - forward address
DEFINE("FWD_MO", "koinfrm/indosis-mo.php");

// constant declaration - log path and naming - LOG_DIR.LOG_NME.date(LOG_PTN).log
DEFINE("LOG_PFX", "");
DEFINE("LOG_PTN", "Y-m-d");
DEFINE("DIR_MO", "/opt/apps/".SHORTCODE."/queue/".OPERATOR."/mo/");
DEFINE("DIR_LOG", "/opt/apps/".SHORTCODE."/log/".OPERATOR."/");
$LOG_SFX[1] = "_mo_rcv";
$LOG_SFX[2] = "_mo_dsp";
$LOG_SFX[3] = "_mo_tmt";
$LOG_SFX[4] = "_mt_rcv";
$LOG_SFX[5] = "_mt_dsp";
$LOG_SFX[6] = "_mt_tmt";
$LOG_SFX[7] = "_dr_rcv";
$LOG_SFX[8] = "_dr_dsp";
$LOG_SFX[9] = "_dr_tmt";

$arr_type["text"] = "0";
$arr_type["wap"] = "1";
$arr_type["binary"] = "2";

// get xml posted string
$rawXML = file_get_contents("php://input");
$rawXML = trim(str_replace("\n", "", $rawXML));

// load xml into dom document object where all errors in this section will be logged using log id instead of transaction id
set_error_handler("common_error_load_xml");
	$err_msg = "failed to load string to xml object";
	if ($rawXML == "") { common_error_load_xml("", "", "", ""); }

	$err_msg = "failed to load string to xml object";
	$XML = new SimpleXMLElement($rawXML);

	$err_msg = "failed to extract transaction id from xml object";
	$xml_tranid = trim($XML->attributes());

	$err_msg = "failed to extract xml type from xml object";
	$xml_momtdr = trim(strtolower($XML->sms->attributes()));

	$err_msg = "missing transaction id";
	if ($xml_tranid == "") { common_error_load_xml("", "", "", ""); }

	$err_msg = "missing xml type";
	if ($xml_momtdr == "") { common_error_load_xml("", "", "", ""); }
restore_error_handler();

// MESSAGE ORIGIN @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
if ($xml_momtdr == "mo")
{
	$flowid = "1|mo|rcv";
	log_write($flowid, $xml_tranid, "begin", "");
	log_write($flowid, $xml_tranid, "  received", xml_trim($rawXML));

	$xml_source = trim($XML->sms->source->address->number);
	log_write($flowid, $xml_tranid, "  source", $xml_source);

	$xml_target = trim($XML->sms->destination->address->number);
	log_write($flowid, $xml_tranid, "  destination", $xml_target);

	$xml_conten = trim($XML->sms->ud);
	log_write($flowid, $xml_tranid, "  content", $xml_conten);

	$xml_smstyp = trim(strtolower($XML->sms->ud->attributes()));
	log_write($flowid, $xml_tranid, "  sms type", $xml_smstyp);

	$xml_datetm = date("Y-m-d H:i:s");

	// this is the hard-way to read xml parameter of 'param' - but the advantage is that
	// the code don't have to worry about changes in param line order, or attributes order
	$xml_servid = "";
	$xml_operat = "";
	if (isset($XML->sms->param))
	{
		for ($i = 0; $i < 2; $i++)
		{
			$param_name = "";
			$param_value = "";
			foreach($XML->sms->param[$i]->attributes() as $name => $value)
			{
				$name = trim(strtolower($name));
				if ($name == "name") { $param_name = trim(strtolower($value)); }
				if ($name == "value") { $param_value = $value; }
			}
			if ($param_name == "mm_service") { $xml_servid = $param_value; }
			if ($param_name == "mm_serviceprovider") { $xml_operat = $param_value; }
		}
	}
	log_write($flowid, $xml_tranid, "  service", $xml_servid);
	log_write($flowid, $xml_tranid, "  sprovider", $xml_operat);

	// after iteration completed, check retrieved xml values
	if ($xml_source == "") { common_error_then_die($flowid, $xml_tranid, "", "mo source msisdn number is not specified"); }
	if ($xml_target == "") { common_error_then_die($flowid, $xml_tranid, $xml_source, "mo destination msisdn number is not specified"); }
	if ($xml_conten == "") { common_error_then_die($flowid, $xml_tranid, $xml_source, "mo unicode data for text message is not specified"); }
	if ($xml_smstyp == "") { common_error_then_die($flowid, $xml_tranid, $xml_source, "mo message type is not specified"); }

	// set other variables based on received parameters
	$xml_smstyp = "";

	// check if text message contain foul words
	log_write($flowid, $xml_tranid, "  xml", "checking for foul words");
	$tmp = trim(strtolower($xml_conten));
	$foulword = isHavingFouldWords($tmp);
	if ($foulword != "") { common_error_then_die($flowid, $xml_tranid, $xml_source, "message contain foul word '".$foulword."'"); }

	// determine who will process this message (based on keyword)
	log_write($flowid, $xml_tranid, "  xml", "checking message owner");
	while (strpos($tmp, "  ") !== false) { $tmp = str_replace("  ", " ", $tmp); }
	$arr = explode(" ", $tmp);
	if ($arr[0] == "reg") { $keyword = $arr[1]; }
	elseif ($arr[0] == "unreg" || $arr[0] == "stop" || $arr[0] == "setop") { $keyword = $arr[1]; }
	else { $keyword = $arr[0]; }
	log_write($flowid, $xml_tranid, "  keyword", $keyword);

	// get owner from given keyword
	$owner = isset($keywordowner[$keyword]) ? $keywordowner[$keyword] : "";
	if ($owner == "")
	{
		log_write($flowid, $xml_tranid, "  owner", "owner not found, set to default owner");
		$URL = $ownerurl["default"];
	}
	else
	{
		$URL = $ownerurl[$owner];
	}
	if ($URL == "")
	{
		log_write($flowid, $xml_tranid, "  url", "url not found, set to default url");
		$URL = $ownerurl["default"];
	}
	if ($URL == "") // at this point, if url still not found that mean owner or url not found, and default url also not found
	{
		common_error_then_die($flowid, $xml_tranid, $xml_source, "keyword owner not found");
	}
	log_write($flowid, $xml_tranid, "  url", $URL);
	log_write($flowid, $xml_tranid, "finish", "counted as success\m");

	// sub-procedure: mo dispatcher ----------------------------------------------------------------------------------------------
	$flowid = "2|mo|dsp";
	log_write($flowid, $xml_tranid, "begin", "");
	log_write($flowid, $xml_tranid, "  info", "this system does not having mo dispatcher procedure");
	log_write($flowid, $xml_tranid, "finish", "counted as success\m");

	// sub-procedure: mo transmitter ---------------------------------------------------------------------------------------------
	$flowid = "3|mo|tmt";
	log_write($flowid, $xml_tranid, "begin", "");
	$tmp = $URL."?transid=".$xml_tranid."&trxdate=".(date("YmdHis"))."&msisdn=".$xml_source."&shortcode=".$xml_target."&substype=20&provider=".OPERATOR."&sms=".rawurlencode($xml_conten)."&sourceaddress=";
	log_write($flowid, $xml_tranid, "  send get", $tmp);
	$handle = fopen($tmp, "r");
	if ($handle)
	{
		$buffer = "";
		while (!feof($handle)) { $buffer = $buffer.fgets($handle, 4096); }
		fclose($handle);
		log_write($flowid, $xml_tranid, "  response", $buffer);
		log_write($flowid, $xml_tranid, "finish", "counted as success\m");
	}
	else
	{
		common_error_then_die($flowid, $xml_tranid, $xml_source, "cannot open url from the owner of keyword");
	}

	// sub-procedure: mt receiver ------------------------------------------------------------------------------------------------
	$flowid = "4|mt|rcv";
	log_write($flowid, $xml_tranid, "begin", "");
	log_write($flowid, $xml_tranid, "  received", $buffer);
	$XML = new SimpleXMLElement($buffer);
	$res_code = $XML->responsecode;
	$res_info = $XML->responseinfo;

	// partner response declared that message cannot be processed, write to log
	if ($res_code != "0")
	{
		log_write($flowid, $xml_tranid, "  error", "partner declared that message cannot be processed");
		log_write($flowid, $xml_tranid, "  error", "[".$res_code."]".$res_info);
		log_write($flowid, $xml_tranid, "  solution", "this message is not processed and replying error response");
		log_write($flowid, $xml_tranid, "finish", "counted as an error\m");

		$tmp = "";
		$tmp = $tmp.'<?xml version="1.0"?>';
		$tmp = $tmp.'<message>';
		$tmp = $tmp.'<sms type="mt">';
		$tmp = $tmp.'<destination messageid="">';
		$tmp = $tmp.'<address>';
		$tmp = $tmp.'<number type="national"></number>';
		$tmp = $tmp.'</address>';
		$tmp = $tmp.'</destination>';
		$tmp = $tmp.'<ud type="text">Maaf, permintaan Anda tidak dapat kami proses.</ud>';
		$tmp = $tmp.'<rsr type="success_failure"/>';
		$tmp = $tmp.'</sms>';
		$tmp = $tmp.'</message>';
		echo $tmp;
		return;
	}

	$res_tranid = $XML->transid;
	$res_servid = $XML->serviceid;
	$res_msisdn = $XML->msisdn;
	$res_conten = $XML->sms;
	$res_userid = $XML->uid;
	$res_passwd = $XML->pwd;
	$res_msgtyp = $XML->smstype;
	$res_msgori = $XML->isms;
	$res_shcode = $XML->shortcode;
	$res_keywod = $XML->keyword;
	$res_dlvmtd = $XML->delivery_method;
	$res_operat = $XML->provider;

	// check mt validity, must exist, transaction id, msisdn, service id and message
	if ($res_msisdn == "") { common_error_then_die($flowid, $xml_tranid, $res_msisdn, "mt target msisdn number is not specified"); }
	if ($res_servid == "") { common_error_then_die($flowid, $xml_tranid, $res_msisdn, "mt service id parameter is not specified"); }
	if ($res_conten == "") { common_error_then_die($flowid, $xml_tranid, $res_msisdn, "mt unicode data for text message is not specified"); }
	log_write($flowid, $xml_tranid, "finish", "counted as success\m");

	// sub-procedure: mt dispatcher ----------------------------------------------------------------------------------------------
	$flowid = "5|mt|dsp";
	log_write($flowid, $xml_tranid, "begin", "");
	log_write($flowid, $xml_tranid, "  info", "this system does not having mt dispatcher procedure");
	log_write($flowid, $xml_tranid, "finish", "counted as success\m");

	// send or write our processed message response as a mt
	$flowid = "6|mt|tmt";
	log_write($flowid, $xml_tranid, "begin", "");
	$resXML = "";
	$resXML = $resXML.'<?xml version="1.0"?>';
	$resXML = $resXML.'<message>';
	$resXML = $resXML.'<sms type="mt">';
	$resXML = $resXML.'<destination messageid="'.$res_tranid.'">';
	$resXML = $resXML.'<address>';
	$resXML = $resXML.'<number type="national">'.$res_msisdn.'</number>';
	$resXML = $resXML.'</address>';
	$resXML = $resXML.'</destination>';
	$resXML = $resXML.'<ud type="text">'.urldecode($res_conten).'</ud>';
	$resXML = $resXML.'<rsr type="success_failure"/>';
	$resXML = $resXML.'</sms>';
	$resXML = $resXML.'</message>';
	header("Content-Type: text/xml");
	echo $resXML;
	log_write($flowid, $xml_tranid, "  send xml", $resXML);
	log_write($flowid, $xml_tranid, "  send xml", "succeed");

	// DATABASE: write transaction into database
	$dbCN = @mysql_connect(DB_HOST, DB_USER, DB_PSWD);
	if (!$dbCN)
	{
		common_error_then_die($flowid, $xml_tranid, $xml_source, "cannot connect to database to save transaction");
	}
	else
	{
		$tmp = @mysql_select_db(DB_NAME, $dbCN);
		if (!$tmp)
		{
			@mysql_close($dbCN);
			common_error_then_die($flowid, $xml_tranid, $xml_source, "cannot open database to save transaction");
		}
	}
	$tmp = isset($arr_type[$xml_smstyp]) ? $arr_type[$xml_smstyp] : "0";

	unset($arrf, $arrv);
	$arrf[0] = "dtm";			$arrv[0] = "NOW()";
	$arrf[1] = "trxid";			$arrv[1] = "'".$res_tranid."'";
	$arrf[2] = "trxdtm";		$arrv[2] = "'".date("Y-m-d H:i:s")."'";
	$arrf[3] = "operator";		$arrv[3] = "'".OPERATOR."'";
	$arrf[4] = "msisdn";		$arrv[4] = "'".$res_msisdn."'";
	$arrf[5] = "keyword";		$arrv[5] = "'".rawurlencode($res_keywod)."'";
	$arrf[6] = "sms";			$arrv[6] = "'".rawurlencode($res_conten)."'";
	$arrf[7] = "isms";			$arrv[7] = "'".rawurlencode($xml_conten)."'";
	$arrf[8] = "smstype";		$arrv[8] = "'".$tmp."'";
	$arrf[9] = "source";		$arrv[9] = "''";
	$arrf[10] = "sid";			$arrv[10] = "'".$res_servid."'";
	$arrf[11] = "dmethod";		$arrv[11] = "'pull'";
	$arrf[12] = "partner";      $arrv[12] = "'".$owner."'";
	$SQL = "INSERT INTO ".DB_TBL2." (".join(",", $arrf).") VALUES (".join(",", $arrv).")";
	log_write($flowid, $xml_tranid, "  save2db", $SQL);

	if (mysql_query($SQL, $dbCN) == false)
	{
		@mysql_close($dbCN);
		common_error_then_die($flowid, $xml_tranid, $xml_source, "query failed");
	}
	@mysql_close($dbCN);
	log_write($flowid, $xml_tranid, "finish", "counted as success\m");
}
else // DELIVERY REPORT @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
{
	$flowid = "7|dr|rcv";
	$tmp = trim($XML->sms->destination->attributes()); // since dr having new trx id then get mo-mt trx id
	if ($tmp != "") { $xml_tranid = $tmp; }	// just in case mo-mt trx id is not specified then use given dr trx id

	log_write($flowid, $xml_tranid, "begin", "");
	log_write($flowid, $xml_tranid, "  received", $rawXML);

	$xml_source = trim($XML->sms->source->address->number);
	$xml_target = trim($XML->sms->destination->address->number);
	$xml_status = trim(strtolower($XML->sms->rsr->attributes()));
	log_write($flowid, $xml_tranid, "  msisdn", $xml_target);
	log_write($flowid, $xml_tranid, "  status", $xml_status);
	log_write($flowid, $xml_tranid, "finish", "counted as success\m");

	// sub-procedure: dr dispatcher ----------------------------------------------------------------------------------------------
	$flowid = "8|dr|dsp";
	log_write($flowid, $xml_tranid, "begin", "");
	log_write($flowid, $xml_tranid, "  info", "this system does not having dr dispatcher procedure");
	log_write($flowid, $xml_tranid, "finish", "counted as success\m");

	// sub-procedur: dr transmitter ----------------------------------------------------------------------------------------------
	$flowid = "9|dr|tmt";
	log_write($flowid, $xml_tranid, "begin", "");

	// try to open database connection to update delivery report
	$dbCN = @mysql_connect(DB_HOST, DB_USER, DB_PSWD);
	if (!$dbCN)
	{
		log_write($flowid, $xml_tranid, "  error", "cannot connect to database to update transaction");
		log_write($flowid, $xml_tranid, "  solution", "delivery report status must be manually update to database and send to partner");
		log_write($flowid, $xml_tranid, "finish", "counted as error\m");
		return;
	}
	else
	{
		$tmp = @mysql_select_db(DB_NAME, $dbCN);
		if (!$tmp)
		{
			log_write($flowid, $xml_tranid, "  error", "cannot open database to update transaction");
			log_write($flowid, $xml_tranid, "  solution", "delivery report status must be manually update to database and send to partner");
			log_write($flowid, $xml_tranid, "finish", "counted as error\m");
			@mysql_close($dbCN);
			return;
		}
	}

	// if open database connection succeed then update transaction delivery report
	$SQL = "UPDATE ".DB_TBL2." SET drdtm = IF(drstatus = '".DR_STATUS_OK."', drdtm, NOW()), drstatus = IF(drstatus = '".DR_STATUS_OK."', drstatus, '".$xml_status."') WHERE trxid = '".$xml_tranid."' AND msisdn = '".$xml_target."' LIMIT 1";
	log_write($flowid, $xml_tranid, "  update", $SQL);
	if (mysql_query($SQL, $dbCN) == false)
	{
		log_write($flowid, $xml_tranid, "  error", "query failed");
		log_write($flowid, $xml_tranid, "  solution", "delivery report status must be manually update to database and send to partner");
		log_write($flowid, $xml_tranid, "finish", "counted as error\m");
		@mysql_close($dbCN);
		return;
	}

	// if open database connection succeed then get partner name for this transaction id
	$SQL = "SELECT partner, keyword FROM ".DB_TBL2." WHERE trxid = '".$xml_tranid."' AND msisdn = '".$xml_target."' LIMIT 1";
	if ($que = mysql_query($SQL, $dbCN))
	{
		$rs = mysql_fetch_array($que);
		$owner = isset($rs["partner"]) ? $rs["partner"] : "";
		$owner = trim(strtolower($owner));

		// if partner name not found then try to get partner name from keyword
		if ($owner == "")
		{
			$keyword = isset($rs["keyword"]) ? $rs["keyword"] : "";
			$keyword = trim(strtolower($keyword));
			$owner = isset($keywordowner[$keyword]) ? $keywordowner[$keyword] : "";
			if ($owner == "")
			{
				$owner = "default";
				log_write($flowid, $xml_tranid, "  owner", "owner not found, set to default owner");
			}
			else
			{
				log_write($flowid, $xml_tranid, "  owner", $owner);
			}
		}
		else
		{
			log_write($flowid, $xml_tranid, "  owner", $owner);
		}
	}
	else
	{
		log_write($flowid, $xml_tranid, "  error", "query failed");
		log_write($flowid, $xml_tranid, "  solution", "delivery report status must be manually send to partner");
		log_write($flowid, $xml_tranid, "finish", "counted as error\m");
		@mysql_close($dbCN);
		return;
	}


	// close database connection
	@mysql_close($dbCN);

	// get partner url address to forward this delivery report
	$URL = trim($ownerurldr[$owner]);
	if ($URL == "")
	{
		log_write($flowid, $xml_tranid, "  error", "partner's url not found");
		log_write($flowid, $xml_tranid, "  solution", "delivery report status must be manually send to partner");
		log_write($flowid, $xml_tranid, "finish", "counted as error\m");
		return;
	}

	// foward delivery report to partner
	$tmp = $URL."?transid=".$xml_tranid."&trxdate=".(date("YmdHis"))."&msisdn=".$xml_target."&shortcode=".$xml_source."&substype=20&provider=".OPERATOR."&status=".$xml_status;
	log_write($flowid, $xml_tranid, "  send get", $tmp);
	$handle = fopen($tmp, "r");
	if ($handle)
	{
		$buffer = "";
		while (!feof($handle)) { $buffer = $buffer.fgets($handle, 4096); }
		fclose($handle);
		log_write($flowid, $xml_tranid, "  response", $buffer);
		log_write($flowid, $xml_tranid, "finish", "counted as success\m");
	}
	else
	{
		log_write($flowid, $xml_tranid, "  error", "cannot open partner's url");
		log_write($flowid, $xml_tranid, "  solution", "delivery report status must be manually send to partner");
		log_write($flowid, $xml_tranid, "finish", "counted as error\m");
	}
}

// SUBROUTINE, HANDLE ERROR TRIGGERED LOADING XML ################################################################################
function common_error_load_xml($errno, $errstr, $errfile, $errline)
{
	global $logid, $rawXML, $xml_target, $err_msg;

	log_write($logid, "begin", "");
		log_write($logid, "  received", $rawXML);
		log_write($logid, "  error", $err_msg);
		log_write($logid, "  solution", "message is not processed and return error message to sender");
	log_write($logid, "finish", "counted as error\m");

	if ($errno == E_WARNING && (substr_count($errstr, "DOMDocument::loadXML()") > 0))
	{
		$tmp = "";
		$tmp = $tmp.'<?xml version="1.0"?>';
		$tmp = $tmp.'<message>';
		$tmp = $tmp.'<sms type="mt">';
		$tmp = $tmp.'<destination messageid="">';
		$tmp = $tmp.'<address>';
		$tmp = $tmp.'<number type="national">'.$xml_target.'</number>';
		$tmp = $tmp.'</address>';
		$tmp = $tmp.'</destination>';
		$tmp = $tmp.'<ud type="text">Maaf, permintaan Anda tidak dapat kami proses karena data yang kami terima tidak lengkap.</ud>';
		$tmp = $tmp.'<rsr type="success_failure"/>';
		$tmp = $tmp.'</sms>';
		$tmp = $tmp.'</message>';
		echo $tmp;
		return;
	}
	else { return false; }
}

// SUBROUTINE, HANDLE ERROR ######################################################################################################
function common_error_then_die($pflowid, $ptrxid, $ptarget, $pmsg)
{
	log_write($pflowid, $ptrxid, "  received", $pmsg);
	log_write($pflowid, $ptrxid, "  error", $pmsg);
	log_write($pflowid, $ptrxid, "  solution", "message is not processed and return error message to sender");
	log_write($pflowid, $ptrxid, "finish", "counted as error\m");

	$tmp = "";
	$tmp = $tmp.'<?xml version="1.0"?>';
	$tmp = $tmp.'<message>';
	$tmp = $tmp.'<sms type="mt">';
	$tmp = $tmp.'<destination messageid="'.$ptrxid.'">';
	$tmp = $tmp.'<address>';
	$tmp = $tmp.'<number type="national">'.$ptarget.'</number>';
	$tmp = $tmp.'</address>';
	$tmp = $tmp.'</destination>';
	$tmp = $tmp.'<ud type="text">'.$pmsg.'</ud>';
	$tmp = $tmp.'<rsr type="success_failure"/>';
	$tmp = $tmp.'</sms>';
	$tmp = $tmp.'</message>';
	echo $tmp;
	die();
}

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

// FUNCTION, TRIM SPACES IN XML ##################################################################################################
function xml_trim($strxml)
{
	$res = "";
	$cnk = "";
	$n = strlen($strxml);
	for ($i = 0; $i < $n; $i++)
	{
		$s = substr($strxml, $i, 1);
		$res = $res.$s;
		if ($s == ">")
		{
			$cnk = "";
			$a = $i + 1;
			for ($i = $a; $i < $n; $i++)
			{
				$s = substr($strxml, $i, 1);
				if ($s == " " || $s == "\t") { $cnk = $cnk.$s; }
				elseif ($s == "<") { $res = $res.$s; break; }
				else { $res = $res.$cnk.$s; break; }
			}
		}
	}
	return $res;
}

/*
// MO ----------------------------------------------------------------------------------------------------------------------------
$tmp = "";
$tmp = $tmp."<?xml version='1.0' ?>";
$tmp = $tmp."<message id='routerSMSRouter1@cmsapp1:3465977'>";
$tmp = $tmp."	<sms type='mo'>";
$tmp = $tmp."		<destination>";
$tmp = $tmp."			<address>";
$tmp = $tmp."				<number type='abbreviated'>3338</number>";
$tmp = $tmp."			</address>";
$tmp = $tmp."		</destination>";
$tmp = $tmp."		<source>";
$tmp = $tmp."			<address>";
$tmp = $tmp."				<number type='national'>02170701800</number>";
$tmp = $tmp."			</address>";
$tmp = $tmp."		</source>";
$tmp = $tmp."		<ud type='text'>Cek ya ya</ud>";
$tmp = $tmp."		<param name='mm_service' value='333800'/>";
$tmp = $tmp."		<param name='mm_serviceprovider' value='J001_Telkom'/>";
$tmp = $tmp."	</sms>";
$tmp = $tmp."</message>";

// DR ----------------------------------------------------------------------------------------------------------------------------
$tmp = "";
$tmp = $tmp."<?xml version='1.0' ?>";
$tmp = $tmp."<message id='routerSMSRouter1@cmsapp1:4685979'>";
$tmp = $tmp."	<sms type='dr'>";
$tmp = $tmp."		<destination messageid=‘1234567890'>";
$tmp = $tmp.			"<address>";
$tmp = $tmp."				<number type='national'>02170701800</number>";
$tmp = $tmp."			</address>";
$tmp = $tmp."		</destination>";
$tmp = $tmp."		<source>
$tmp = $tmp."			<address>";
$tmp = $tmp."				<number type='abbreviated'>3838</number>";
$tmp = $tmp."			</address>";
$tmp = $tmp."		</source>";
$tmp = $tmp."		<rsr type='success'/>";
$tmp = $tmp."	</sms>";
$tmp = $tmp."</message>";


<?xml version='1.0'?>
<message messageid='smsapi@021712219761341516765'>
	<sms type='mo'>
		<destination >
			<address>
				<number type='abbreviated'>6768</number>
			</address>
		</destination>
		<source>
			<address>
				<number type='national'>02171221976</number>
			</address>
		</source>
		<ud type='text'>reg hikmah</ud>
	</sms>
</message>
*/
?>