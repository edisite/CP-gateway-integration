<?php
if (function_exists("date_default_timezone_set")) { date_default_timezone_set("Asia/Jakarta"); }
/*
	created by hengky irawan
	last modified by hengky irawan 2012-05-04 23:32
*/

include "../../koin/mc/common_6768_isat.php";

// constant declaration - these declarations just to help code folding while coding
DEFINE("DO_CHECK_PARAM", true);
DEFINE("DO_SET_SVCKEYS_PUSH", true);
DEFINE("DO_SET_URL_PREFIX", true);
DEFINE("DO_SET_OTHERS", true);
DEFINE("DO_GET_PARAMETERS", true);
DEFINE("DO_SET_WAP_PARAM", true);
DEFINE("DO_SET_CONTENTID", true);
DEFINE("DO_SET_SDMCODE", true);

// constant declaration
DEFINE("SHORTCODE",	"6768");
DEFINE("LOG_PFX", "");
DEFINE("LOG_PTN", "Y-m-d");
DEFINE("LOG_SFX", "_mt_rcv");
DEFINE("DIR_MT", "/opt/apps/".SHORTCODE."/queue/isat/mt/");
DEFINE("DIR_LOG", "/opt/apps/".SHORTCODE."/log/isat/");
DEFINE("DIR_IP", "/opt/apps/".SHORTCODE."/queue/isat/ipaddr/");
DEFINE("DB_TBL1", "queue_isat_mt");
DEFINE("DB_TBL2", "sms_out_isat");
DEFINE("DB_TBL3", "freetalk_queue");
DEFINE ("DB_TBL4", "queue_isat_dr");

DEFINE("DB_HOST", "10.1.1.94");
DEFINE("DB_USER", "edimdw6768isat");
DEFINE("DB_PSWD", "3disilit3SQL");
DEFINE("DB_NAME", "mdw6768_isat");

DEFINE ("URL_BASE", "");

//DEFINE("DB_HOST", "10.1.1.9");
//DEFINE("DB_USER", "edi");
//DEFINE("DB_PSWD", "3disit3SQL");
//DEFINE("DB_NAME", "mdw6768_isat");


DEFINE("OUTGOING_UID", "000923");
DEFINE("OUTGOING_PWD", "eO4MiMvm");
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
	$in_shcode = isset($_GET["shortcode"]) ? $_GET["shortcode"] : "";
	//$in_shcode = "6768";
	$in_keywod = isset($_GET["keyword"]) ? $_GET["keyword"] : "";
	$in_prices = isset($_GET["price"]) ? $_GET["price"] : "";
	$in_dlvmtd = isset($_GET["delivery_method"]) ? $_GET["delivery_method"] : "pull";
	$in_age = isset($_GET["age"]) ? $_GET["age"] : "0";
	$in_operat = isset($_GET["operator"]) ? $_GET["operator"] : (isset($_GET["provider"]) ? $_GET["provider"] : "indosat");
	$in_subtype = isset($_GET["subtype"]) ? $_GET["subtype"] : "0";
	$in_mediacode = isset($_GET["mediacode"]) ? $_GET["mediacode"] : "000";
	$in_dc 			= isset($_GET["dc"]);
	$in_waptype 			= isset($_GET["waptype"]) ? $_GET["waptype"] : "0";
	
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
	$in_mediacode = strtolower(trim($in_mediacode));
	
}

if (DO_SET_SDMCODE)
{
	// sdm code based on keyword
	$sdmcode["coim"] = "coim_6768";
	$sdmcode["rahasia"] = "rahasia_6768";
	$sdmcode["suka"] = "suka_6768";
	$sdmcode["talu"] = "talu_6768";
	$sdmcode["tolak"] = "tolak_6768";
	$sdmcode["jpop"] = "jpop_6768";
	$sdmcode["horo"] = "horo_6768";
	$sdmcode["razo"] = "razo_6768";//20140205
	$sdmcode["hoki"] = "hoki_6768";//20140205
	$sdmcode["cinta"] = "cinta_6768";//20140211
	$sdmcode["moci"] = "moci_6768";//20140211
	$sdmcode["infb"] = "infb_6768";//20140703
	$sdmcode["itafb"] = "itafb_6768";//20140703
	$sdmcode["spafb"] = "spafb_6768";//20140703
	$sdmcode["bunfb"] = "bunfb_6768";//20140703
	$sdmcode["bplfb"] = "bplfb_6768";//20140703
	$sdmcode["erofb"] = "erofb_6768";//20140703
	$sdmcode["intfb"] = "intfb_6768";//20140703
	$sdmcode["ingfb"] = "ingfb_6768";//20140703
	$sdmcode["fengshui"] = "fengshui_6768";//20140710
	$sdmcode["caper"] = "caper_96768";//20140710
	$sdmcode["kata"] = "kata_6768";//20140902
	$sdmcode["vea"] = "vea_96768";//20141031
	$sdmcode["stardut"] = "stardut_96768";//20141031debol_96768
	$sdmcode["debol"] = "debol_96768";//20141031debol_96768
	$sdmcode["fun"] = "fun_96768";//20141031debol_96768
	$sdmcode["gemval"] = "gameval_96768";//20151208gameval_96768
	$sdmcode["romance"] = "romance_96768";//20151208gameval_96768
	$sdmcode["bugila"] = "bugila_96768";//20151208gameval_96768
}	


$partnerID = getCPID($in_keywod);

if(strtolower($in_keywod) == "artispull")	{$in_keywod = "artis";}
if(strtolower($in_keywod) == "jpoppull")	{$in_keywod = "jpop";}
if(strtolower($in_keywod) == "veapull")		{$in_keywod = "vea";}
if(strtolower($in_keywod) == "stardutpull")	{$in_keywod = "stardut";}
if(strtolower($in_keywod) == "debolpull")	{$in_keywod = "debol";}

if(strlen($in_smstxt) >= 160)
{
	$sms = trim($in_smstxt);
	$pos_http = strpos($sms,"http");
	
	$pjgori = strlen($sms);
	
	$panjang = strlen($pos_http);
	$pgjurl = $pjgori - $panjang;
	$pjgcut = 145 - $pgjurl;
	// log_write($in_trxids, " str length ", $pjgcut);
	// log_write($in_trxids, " str length ", $panjang);
	if($panjang != 0)
	{
		if ($pos_http > 5) 
		{
			$response=substr($sms,0,$pos_http);
			$pesan = strlen($response);
			if ($pesan > 60)
			{
				$response=substr($response,0,$pjgcut)." klik ";
			}

		} 
		else 
		{
			$response = "Click Goto to donwload";
		}
		log_write($in_trxids, " str length ", $response);
		
		$sms = substr($sms,$pos_http , (strlen($sms)- $pos_http) );
		$sms = substr($sms,7);
		$spacepos = strpos($sms, " ");
		if ($spacepos != "" )
		{
			$sms = substr($sms, 0, $spacepos);
			
		}
		$sms = $response." http://".$sms;
		log_write($in_trxids, " str length ", $sms);
		
		$in_smstxt = $sms;
	}
	
}

$in_age1 = $in_age;

if(($in_dc == "0") && ($in_dlvmtd == "pull"))
{
	
	$in_retry_age = $in_age;
	$in_age1 = "0";
	log_write($in_trxids, " age ", $in_age);
}

// modify value of sms type for indosat so it's always point to 0 or sms type of plain text 
// this is specifically requested by Saptadi by email in "Re: Pendaftaran Keyword dan PB Ayu Dewi" - Wed 6/13/2012 1:58 PM

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
		log_write($in_trxids, "finish", "counted as error\m");
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

	if(strpos($in_trxids,'@') !== false) {
		
		$in_trxids = mt_rand(100000012, 999999999);
		log_write($in_trxids, "  Parsing trxid ", $in_trxids);
	}
	if(strpos($in_trxids,'%') !== false) {
		
		$in_trxids = mt_rand(100000012, 999999999);
		log_write($in_trxids, "  Parsing trxid ", $in_trxids);
	}
	
	$LogParam = ": ".$partnerID." : ".$in_keywod." : ".$in_srvcid." : ".$in_msisdn." : ".$in_dlvmtd." : ".$in_trxids." : ".$in_shcode." : ".$in_age1;
	log_write($in_trxids, "  Param Log ", $LogParam);
// check if incoming MT will generate Free Talk service, if it does, then store it into free talk table
	
	//kon db
	
	$dbCN = @mysql_pconnect(DB_HOST, DB_USER, DB_PSWD);
	if (!$dbCN)
	{
		log_write($in_trxids, "  error", "FATAL ERROR ##############################");
		log_write($in_trxids, "  error", "database connection failed");
		log_write($in_trxids, "  solution", "mt will be processed, but freetalk will not");
		log_write($in_trxids, "  solution", "contact developer to analyze this problem");
	}
	else
	{
		$tmp = @mysql_select_db(DB_NAME, $dbCN);
		if (!$tmp)
		{
			log_write($in_trxids, "  error", "FATAL ERROR ##############################");
			log_write($in_trxids, "  error", "open database failed");
			log_write($in_trxids, "  solution", "mt will be processed, but freetalk will not");
			log_write($in_trxids, "  solution", "contact developer to analyze this problem");
		}
	}	
	
	$source_file = "http://localhost/koin/api_mc/api_blackmsisdn.php?m=".$in_msisdn;
	$api_file = file_get_contents($source_file);
	if($api_file == 1)
	{	
		log_write($in_trxids, "  error ", $source_file);		
		log_write($in_trxids, "  error ", "MSISDN BLACKLIST");
		log_write($in_trxids, "  error ", "Transaction Not sent for Operator");
		
		$URL = URL_BASE;
		$URL = $URL."?msisdn=".$in_msisdn;
		$URL = $URL."&trxid=".$in_trxids;
		$URL = $URL."&trxdate=";
		$URL = $URL."&operator=INDOSAT";
		$URL = $URL."&status=66";
		$URL = $URL."&serviceid=".$in_srvcid;
		$URL = $URL."&mtid=".$in_trxids;
		$URL = $URL."&dest=".$in_msisdn;
		$URL = $URL."&time=";
		$URL = $URL."&tid=".$in_trxids;
		$URL = $URL."&sc=".$in_shcode;
		$URL = $URL."&errcode=".urlencode("Blacklist msisdn in gateway pulsa");
		
		unset($arrf, $arrv);
		$arrf[0] = "dtm";				$arrv[0] = "NOW()";
		$arrf[1] = "trxid";				$arrv[1] = "'".$in_trxids."'";
		$arrf[2] = "trxdtm";			$arrv[2] = "'".$in_trxdtm."'";
		$arrf[3] = "msisdn";			$arrv[3] = "'".$in_msisdn."'";
		$arrf[4] = "status";			$arrv[4] = "'66'";
		$arrf[5] = "sid";				$arrv[5] = "'".$in_srvcid."'";
		$arrf[6] = "mtid";				$arrv[6] = "'".$in_trxids."'";
		$arrf[7] = "hit_url";			$arrv[7] = "'".$URL."'";
		$arrf[8] = "keyword";			$arrv[8] = "'".$in_keywod."'";
		$arrf[9] = "method";			$arrv[9] = "'PUSH'";
		$SQL1 = "INSERT INTO ".DB_TBL4." (".join(",", $arrf).") VALUES (".join(",", $arrv).")";
		
		MYSQL_QUERY($SQL1);
		log_write($in_trxids, "  error", " create dr 66 for delete member");
		log_write($in_trxids, "  error", $SQL1);
		log_write($in_trxids, "finish", "counted as success\m");
		mysql_close($dbCN);
		
		echo '<? xml version="1.0" ?><push><transid>'.$in_trxids.'</transid><status>0</status><msg>Message processed successfully</msg></push>';
		return;
	}
	/*
	if($in_dlvmtd == "pull" && $in_srvcid != '67680184001027')
	{
		$SQLCEK = "SELECT * FROM ".DB_TBL2." WHERE dtm >= now() - INTERVAL 1 WEEK and `dmethod` = 'pull' and msisdn = '".$in_msisdn."' and sid != '67680184001027' and drstatus =".DR_STATUS_OK." limit 3";
		$QRYCEK = mysql_query($SQLCEK);
		$NUMCEK = mysql_num_rows($QRYCEK);
		if($NUMCEK >= 3)
		{
			log_write($in_trxids, "  error ", $SQLCEK);
			log_write($in_trxids, "  error ", "In 1 week maximum success 2, so this MT over limit");
			log_write($in_trxids, "  error ", "Transaction Not sent for Operator");
			echo '<? xml version="1.0" ?><push><transid>'.$in_trxids.'</transid><status>0</status><msg>Message processed successfully</msg></push>';
			return;
		}
	}*/
	
	if($in_dlvmtd == "pull")
	{
		$SQLCEK = "SELECT * FROM dc_sid WHERE service = '".$in_keywod."'";
		$QRYCEK = mysql_query($SQLCEK);
		$NUMCEK = mysql_num_rows($QRYCEK);
		if($NUMCEK >= 1)
		{
			$SQLQueue = "INSERT INTO dc_mt_queue(waktu,msisdn,trx_id,keyword,smstype,sid,sms,reply,age,objectId,mediacode,retry_age,dmethod,trx_date,waptype)VALUES(now(),'".$in_msisdn."','".$in_trxids."','".$in_keywod."','".$in_smstyp."','".$in_srvcid."','".$in_smsori."','".$in_smstxt."','".$in_age."','".$in_objectid."','".$in_mediacode."','".$in_retry_age."','".$in_dlvmtd."',NOW(),'".$in_waptype."')";
			mysql_query($SQLQueue);

			//log_write($in_trxids, "  Set Queue Retry ", $SQLQueue);
		}
	}
		
	if ($in_smstyp == "11" || $in_smstyp == "21")
	{
		if ($in_subtype == "10" || $in_subtype == "30")
		{
			$trxdate = date('YmdHis');
			$trx_id = $in_msisdn.$trxdate;
			
			$buffer = "";
			$Uid="mcpmusic";
			$Pwd="mcpmusic";
			$op_mtdest = "http://10.1.1.89/handle/6768/isat_mt.php?";
			$smsmatrik = rawurlencode("Mohon maaf, layanan ini dengan bonus gratis bicara 60 menit hanya berlaku untuk Indosat Mentari dan IM3. Terima kasih.");
			$send_par = $op_mtdest."uid=".$Uid."&pwd=".$Pwd."&serviceid=67680184001027&msisdn=".$in_msisdn."&sms=".$smsmatrik."&transid=".$trx_id."&smstype=0&delivery_method=push&shortcode=6768&keyword=freetalk&mtType=1&subtype=".$in_subtype."&age=0";
			log_write($in_trxids, "  notifikasi freetalk ", fretalk);
			log_write($in_trxids, "  URL ", $send_par);
			$handle = fopen($send_par, "r");
			if ($handle)
			{
				while (!feof($handle)) { $buffer .= fgets($handle, 4096); }
				fclose($handle);
				log_write($in_trxids, "  response", trim($buffer));
			}
			else
			{
				log_write($in_trxids, "  error", trim($buffer));
			}

		}
		else
		{
			$SQL = "INSERT INTO ".DB_TBL3." (trxid, dtm) VALUES ('".$in_trxids."', NOW())";
			if (@mysql_query($SQL, $dbCN))
			{
				log_write($in_trxids, "  freetalk", "queued and waiting for dr");
			}
			else
			{
				log_write($in_trxids, "  error", " freetalk queued and waiting for dr");
			}
		}
	}
mysql_close($dbCN);

	$in_smstyp = "0";	
	// get service keys
$tmp = strtolower($in_keywod);
$svckey = ""; // this value only set for XL operator


if($in_waptype == "1") // wap message, since 2011-12-14 WAP will be treated as SMS, change # to 1 to treat wap as wap
{
	$HITMTD = "post";
	log_write($in_trxids, "  sms type", "wap message");
	$pos_http = stripos($in_smstxt, "http");
	if ($pos_http > 5)
	{
		$response = substr($in_smstxt, 0, $pos_http);
		if (strlen($response) > 50) { $response = substr($response, 0, 50)."..."; }
	}
	else
	{
		$response = $in_smstxt;
		if (strlen($response) > 50) { $response = substr($response, 0,50)."..."; }	}
	log_write($in_trxids, "  response", $response);

	$linkurl = substr($in_smstxt, $pos_http + 7); 		// extract url address from message +1 to extract ~, +7 to extract url address
	$n = stripos($linkurl, " ");						// remove trailing content which is not part of url address
	if ($n > 1) { $linkurl = substr($linkurl, 0, $n); }
	log_write($in_trxids, "  link url", $linkurl);
	if(strlen($linkurl) > 20){
		$linkurl = "";
	}

	$XML = '';
	$XML = $XML.'<?xml version="1.0" encoding="UTF-8"?>';
	$XML = $XML.'<PUSH>';
		$XML = $XML.'<UID>'.OUTGOING_UID.'</UID>';
		$XML = $XML.'<PWD>'.OUTGOING_PWD.'</PWD>';
		$XML = $XML.'<SID>'.$in_srvcid.'</SID>';
		$XML = $XML.'<TID>'.$in_trxids.'</TID>';
		$XML = $XML.'<MSISDN>'.$in_msisdn.'</MSISDN>';
		$XML = $XML.'<CONTENT TYPE="binary">';
		//$XML = $XML.'<LINE NUMBER="1">0605040B8423F0FF0601AE02056A0045C60C03'.bin2hex($linkurl).'000103'.bin2hex($response).'000101</LINE>';
		//perubahan dari pak aldrian tgl 20150316 13:41 email pak windraa
		$XML = $XML.'<LINE NUMBER="1">0605040B8423F068656C6C6F'.bin2hex($linkurl).'000103'.bin2hex($response).'000101</LINE>';
		$XML = $XML.'<LINE NUMBER="2">0605040B8423F068656C6C6F'.bin2hex($linkurl).'000103'.bin2hex($response).'000101</LINE>';
		$XML = $XML.'</CONTENT>';
		//$XML = $XML.'<DCS>245</DCS>';
			if($in_dlvmtd == "push" && $in_srvcid != "67680184137005"){				
				
				$tmp = $sdmcode[$in_keywod]; // try to get sdm code based on keyword
				if ($tmp == "") 
				{ 
					$tmp = $sdmcode[$in_srvcid]; 
				};  // if sdm code not found, then try to get sdm code from service id

				
				if ($tmp != "") 
				{ 
					$XML = $XML.'<SDMCODE>'.$tmp.'</SDMCODE>';
				}
				else
				{
					$XML = $XML.'<SDMCODE>'.($in_keywod.SHORTCODE).'</SDMCODE>';
				}
			}	
			$XML = $XML.'<DCS>192</DCS>';

			/*if($in_dlvmtd == "push")
			{
				$XML = $XML.'<SDMCODE>'.$sdmcode[$in_keywod].'</SDMCODE>';
			}*/

		$XML = $XML.'<UDHL>0</UDHL>';
		$XML = $XML.'</PUSH>';
	$URLLOG = 'xmldata='.$XML;
	
	
	log_write($in_trxids, "  URL ORI", $URLLOG);
	$URL = 'xmldata='.rawurlencode($XML);
	log_write($in_trxids, "  URL ", $linkurl);
}
elseif($in_waptype == "2") // binary message
{
	$HITMTD = "post";
	log_write($in_trxids, "  sms type", "binary message");

	$XML = '';
	$XML = $XML.'<?xml version="1.0" encoding="UTF-8"?>';
	$XML = $XML.'<PUSH>';
		$XML = $XML.'<UID>'.OUTGOING_UID.'</UID>';
		$XML = $XML.'<PWD>'.OUTGOING_PWD.'</PWD>';
		$XML = $XML.'<SID>'.$in_srvcid.'</SID>';
		$XML = $XML.'<TID>'.$in_trxids.'</TID>';
		$XML = $XML.'<MSISDN>'.$in_msisdn.'</MSISDN>';
		$XML = $XML.'<CONTENT TYPE="binary">';

		if (strlen($in_smstxt) < 513)
		{
			$XML = $XML.'<LINE NUMBER="1">'.$in_smstxt.'</LINE>';
		}
		else
		{
			$i = 0;
			$tmp = $in_smstxt;
			while (1)
			{
				$i = $i + 1;
				$j = substr("0".$i, -2);
				$part = substr($tmp, 0, 256);
				$XML = $XML.'<LINE NUMBER="'.$i.'">0B0504C34FC00200036302'.$j.$part.'</LINE>';
				if (strlen($part) < 257) { break; } else { $tmp = substr($tmp, 257, 256); }
			}
		}

		$XML = $XML.'</CONTENT>';
		$XML = $XML.'<DCS>0</DCS>';
		$XML = $XML.'<UDHL>0</UDHL>';
	$XML = $XML.'</PUSH>';

	$URL = 'xmldata='.rawurlencode($XML);
	log_write($in_trxids, "  XML", $XML);
	log_write($in_trxids, "  URL", $URL);
	
}
else
{
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
	if(strtolower($in_keywod) == "gs")
	{
		$URL = $URL."&sc=96869";
	}
	if($in_srvcid == "67680184034030" || $in_srvcid == "67680184137005" || $in_srvcid == "96768184008002")
	{
		log_write($in_trxids, "  note ", "mt without sdmcode");
	}else{
		if (strtolower($in_dlvmtd) == "push")
		{
			$tmp = $sdmcode[$in_keywod]; // try to get sdm code based on keyword
			if ($tmp == "") 
			{ 
				$tmp = $sdmcode[$in_servid]; 
			};  // if sdm code not found, then try to get sdm code from service id

			if ($tmp != "") 
			{ 
				$URL = $URL."&sdmcode=".$tmp;	
			}
			else
			{
				$URL = $URL."&sdmcode=".($in_keywod.SHORTCODE);
			}
		}
	}
}



	
//log_write($in_trxids, "  URL", $URL);

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
$arrf[7] = "keyword";			$arrv[7] = "'".rawurlencode($in_keywod)."'";
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
$arrf[5] = "partner";	$arrv[5] = "'".$partnerID."'";
$arrf[6] = "sid";		$arrv[6] = "'".$in_srvcid."'";
$arrf[7] = "dmethod";	$arrv[7] = "'".$in_dlvmtd."'";
$arrf[8] = "reply";		$arrv[8] = "'".$in_smstxt."'";
$arrf[9] = "age";		$arrv[9] = "'".$in_age."'";
$SQL3 = "INSERT INTO ".DB_TBL2." (".join(",", $arrf).") VALUES (".join(",", $arrv).")";

// create query - query storage table to update existing record
$SQL4 = "UPDATE ".DB_TBL2." SET sid = '".$in_srvcid."',reply = '".$in_smstxt."',smstype= '".$in_smstyp."', partner ='".$partnerID."', age = '".$in_age."' WHERE trxid = '".$in_trxids."' AND msisdn = '".$in_msisdn."' LIMIT 1";

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


	log_write($in_trxids, "  output", $path);
	log_write($in_trxids, "finish", "counted as success\m");
	echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>0</status><msg>Message processed successfully</msg></push>';
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
