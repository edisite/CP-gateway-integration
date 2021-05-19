<?php
	include "common_6768_isat.php";
	$in_msisdn 	= isset($_GET["msisdn"]) ? $_GET["msisdn"] : "";
	$in_smstxt 	= isset($_GET["sms"]) ? $_GET["sms"] : "";
	$in_trx_time 	= isset($_GET["trx_time"]) ? $_GET["trx_time"] : "";
	$in_trxids 	= isset($_GET["transid"]) ? $_GET["transid"] : "";
	$in_subtyp 	= isset($_GET["substype"]) ? $_GET["substype"] : "";
	$in_keywod 	= isset($_GET["keyword"]) ? $_GET["keyword"] : "";
	$in_operat 	= isset($_GET["provider"]) ? $_GET["provider"] : "indosat";
	$in_sc 		= isset($_GET["sc"]) ? $_GET["sc"] : "6768";

	$in_msisdn 	= trim($in_msisdn);
	$in_smstxt 	= trim($in_smstxt);
	$in_subtyp 	= trim($in_subtyp);
	$in_trxids 	= trim($in_trxids);
	$in_sc 	   	= trim($in_sc);
	$in_trx_time 	= trim($in_trx_time);
	$in_keywod 	= strtoupper(trim($in_keywod));
	$in_operat 	= strtoupper(trim($in_operat));
	
	DEFINE("SHORTCODE", "6768");
	DEFINE("LOG_PFX", "");
	DEFINE("LOG_PTN", "Y-m-d");
	DEFINE("LOG_SFX", "MC_PULL_");
	DEFINE("DIR_MT", "/opt/apps/".SHORTCODE."/queue/isat/mt/");
	DEFINE("DIR_LOG", "/opt/apps/6768/log/isat/mc/");
	DEFINE("DIR_IP", "");
	DEFINE("DB_HOST", "10.1.1.9");
	DEFINE("DB_USER", "edi");
	DEFINE("DB_PSWD", "3disit3SQL");
	
	DEFINE("DB_NAME", "mc_6768_isat");
	DEFINE("DB_TBL1", "queue_isat_mt");
	DEFINE("DB_TBL2", "sms_out_isat");
	DEFINE("DB_TBL3", "freetalk_queue");
	$trx_time	= date("YmdHis");
	
	log_write($in_trxids, "begin", "");
	log_write($in_trxids, "  received", $_SERVER["REQUEST_URI"]);
	
	if($in_trxids == "")
	{
		echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>1</status><msg>missing transaction id</msg></push>';
		log_write("error", "error", "missing transaction id");
		return;
	}
	if($in_msisdn == "" || !is_numeric($in_msisdn))
	{
		echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>2</status><msg>invalid msisdn number ['.$in_msisdn.']</msg></push>';
		log_write($in_trxids, "  error", "invalid msisdn number [".$in_msisdn."]");
		return;
	}
	if($in_sc == "")
	{
		echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>3</status><msg>missing SHORTCODE</msg></push>';
		log_write("error", "error", "missing SHORTCODE");
		return;
	}
	
	
	$key = explode(" ", $in_smstxt);
	
	$key1 = $key[0];
	$key2 = $key[1];
	$key3 = $key[2];
	$key4 = $key[3];
	$key5 = $key[4];
	
	if(strtolower($key1) == "artis")
	{
		$key1 = "artispull";
	}
	if(strtolower($key1) == "jpop")
	{
		$key1 = "jpoppull";
	}

	// check if database connection is available
	$dbCN = @mysql_connect(DB_HOST, DB_USER, DB_PSWD);
	
	if (!$dbCN)
	{
		echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>5</status><msg>database connection failed, rejecting any incoming transaction</msg></push>';
		log_write($in_trxids, "  error", "database connection failed, rejecting any incoming transaction");
		log_write($in_trxids, "  solution", "contact developer to analyze this problem");
		log_write($in_trxids, "finish", "error\m");
		return;
	}

	// check if database name is a valid database
	$tmp = @mysql_select_db(DB_NAME, $dbCN);
	if (!$tmp)
	{
		echo '<?xml version="1.0"?><push><transid>'.$in_trxids.'</transid><status>6</status><msg>open database failed, rejecting any incoming transaction</msg></push>';
		log_write($in_trxids, "  error", "open database failed, rejecting any incoming transaction");
		log_write($in_trxids, "  solution", "contact developer to analyze this problem");
		log_write($in_trxids, "finish", "error\m");
		@mysql_close($dbCN);
		return;
	}
	
	// cek key2 + key3
	$sqlkey3 = "SELECT a.order_id,b.keywords, b.group_id, b.type_keyword FROM key2 a, keywords b WHERE b.keywords = '".$key1."' AND a.key_nm = '".$key2."'  LIMIT 1"; 

	// cek keyword 1
	$sqlkey4 = "SELECT * FROM keywords WHERE keywords = '".$key1."' limit 1";
	
	$qrykey3 = mysql_query($sqlkey3);
	$numkey3 = mysql_num_rows($qrykey3);
	//log_write($in_trxids, "	Query Key 1", $sqlkey3);
	if($numkey3 >= 1)
	{
		while($datakey3 = mysql_fetch_array($qrykey3))
		{
			log_write($in_trxids, "  Query Key 3", $sqlkey3);
			$keywords 		= $datakey3['keywords'];
			$group_id 		= $datakey3['group_id'];
			$type_keyword 	= $datakey3['type_keyword'];
			$status 		= $datakey3['status'];
			$order_id 		= $datakey3['order_id'];					
			$type_key 		= $datakey3['type_key'];
				
			log_write($in_trxids, "  keyword", $keywords);
			log_write($in_trxids, "  group id", $group_id);
			//log_write($in_trxids, "  keyword", $type_keyword);
			log_write($in_trxids, "  status", $status);
			
			$startx = "3";
			$MOKEY = $keywords;	
		}
	}
	else
	{
		
		$qrykey4 = mysql_query($sqlkey4);
		$numkey4 = mysql_num_rows($qrykey4);
		//log_write($in_trxids, " Query Key 2", $sqlkey4);
		if($numkey4 >= 1)
		{
			log_write($in_trxids, "  Query Key 2", $sqlkey4);
			while($datakey4 = mysql_fetch_array($qrykey4))
			{
				$keywords	= $datakey4['keywords'];
				$group_id 	= $datakey4['group_id'];	
				$type_keyword 	= $datakey4['type_keyword'];
				log_write($in_trxids, "  keyword", $keywords);	
				log_write($in_trxids, "  group id", $group_id);
				log_write($in_trxids, "  type keyword", $type_keyword);	
				
				$startx = "4";
				$MOKEY = $keywords;
			}
		}
		else
		{
			log_write($in_trxids, "  Keyword ", " Keyword not exist ");
		}
	}		

	
	//check trx limit 5 hour
	
	$SQL_MO = "SELECT * FROM recv_mo_limit l, recv_mo_acv a WHERE l.group_id = '".$group_id."' and a.dtm <= now() + INTERVAL 5 HOUR and a.msisdn = '".$in_msisdn."' and isms = '".urlencode($in_smstxt)."'";
	//log_write($in_trxids, "  Proses ", $SQL_MO);
	$num_MO = mysql_num_rows(mysql_query($SQL_MO));
	if($num_MO >= 1)
	{
		log_write($in_trxids, "  MO ", "MO OVERLIMIT ".$in_smstxt.$in_msisdn);
		echo "<?xml version='1.0'?><PUSH><STATUS>0</STATUS><TRANSID>".$in_trxids."</TRANSID><MSG>Message processed successfully</MSG></PUSH>";
		//exit;
		return;
	}
	else
	{
		$SQL_ACV = "INSERT INTO recv_mo_acv(dtm,msisdn,keyword,isms,mo_type)VALUES(now(),'".$in_msisdn."','".$keywords."','".urlencode($in_smstxt)."','".$mo_type."')";
		log_write($in_trxids, "  MO ", "ADD ".$SQL_ACV);
		mysql_query($SQL_ACV);
		
	}
	$partnerID = getCPID($keywords);
	log_write($in_trxids, "  Receiver MO ", strtolower($MOKEY));
	log_write($in_trxids, "  Param MO ", $partnerID." : ".strtolower($MOKEY));
	//check member
	
	if($startx == "3") // DPPULL - REG MESSAGE
	{
			$msg = "dpull";
	}
	elseif($startx == "4") // DPPULL - with cms
	{
			
		$sqlcms = "SELECT * FROM arenagames4263.content where orderingCode = '".$key2."' limit 1";
		log_write($in_trxids, "  CMS ",$sqlcms);
		$qrycms = mysql_query($sqlcms);
		$numcms = mysql_num_rows($qrycms);
		if($numcms >= 1)
		{
			while($datacms = mysql_fetch_array($qrycms))
			{
				$title = $datacms['title'];
				$objectid = $datacms['objectId'];
				$msg = "dpull_o";
				log_write($in_trxids, "  CMS", $title);
				log_write($in_trxids, "  CMS", $objectId);
				
			}
		}
		else
		{
			
			$substr_key = substr($key2,0,-3);
			$sqlcms1 = "SELECT * FROM arenagames4263.content where orderingCode = '".$substr_key."' limit 1";
			log_write($in_trxids, "  CMS ",$sqlcms1);
			$qrycms1 = mysql_query($sqlcms1);
			$numcms1 = mysql_num_rows($qrycms1);
			if($numcms1 >= 1)
			{
				while($datacms1 = mysql_fetch_array($qrycms1))
				{
					$title = $datacms1['title'];
					$objectid = $datacms1['objectId'];
					$msg = "dpull_o";
					log_write($in_trxids, "  CMS", $title);
					log_write($in_trxids, "  CMS", $objectId);
					
				}
			}
			else
			{
				log_write($in_trxids, "  CMS ", "Content Not Exist CMS Tools");
				$msg = "dpull_o";
			}
		}
		
	}
	
	
	if($type_keyword  == "reg_msg")
	{	
		if($msg == "dpull") // msg unregister for member is not exist
		{
			$set_msg_reg = "SELECT * FROM msg_reg WHERE group_id  = '".$group_id."' AND order_id = '".$order_id."' limit 1";
		}
		elseif($msg == "dpull_o") // msg unregister for member is not exist
		{
			$set_msg_reg = "SELECT * FROM msg_reg WHERE group_id  = '".$group_id."' limit 1";
		}
		log_write($in_trxids, "  Content ", "PULL Content");
		log_write($in_trxids, "  Content ", $set_msg_reg);
	
		$qry_msg_reg = mysql_query($set_msg_reg);
		$num_msg_reg = mysql_num_rows($qry_msg_reg);
		if($num_msg_reg >= 1)
		{
			while($data_msg_reg = mysql_fetch_array($qry_msg_reg))
			{
				$data_msg_sms 	= $data_msg_reg['msg_reg'];
				$data_sid 		= $data_msg_reg['sid'];
				$msgkind 	= $data_msg_reg['msgkind'];
				$msgtype 	= $data_msg_reg['msgtype'];
				$data_urls 		= $data_msg_reg['url'];
				$data_urls		= trim($data_urls);
			}
		}
	
	}
	elseif($type_keyword  == "reg_story")
	{
		log_write($in_trxids, "  Content ", " PULL Story");
		$check_his_story = "SELECT * FROM his_story where msisdn = '".$in_msisdn."'  and group_id = '".$group_id."' and order_id = '".$order_id."' ORDER BY sequence_story DESC LIMIT 1";
		$qry_check_his_story = mysql_query($check_his_story);
		$num_check_his_story = mysql_num_rows($qry_check_his_story);
		if($num_check_his_story >= 1)
		{
			while($data_seq_his_story = mysql_fetch_array($qry_check_his_story))
			{
				$sequence_story = $data_seq_his_story['sequence_story'];
			}
			$sequence_story = $sequence_story + 1;
		}
		else
		{
			$sequence_story = 1;

		}
		log_write($in_trxids, "  Sequence ", $sequence_story);
	
		if($msg == "dpull") // msg unregister for member is not exist
		{
			$set_msg_reg = "SELECT * FROM msg_story WHERE sequence_story = '".$sequence_story."' and group_id = '".$group_id."' AND order_id = '".$order_id."' limit 1";
		}
		elseif($msg == "dpull_o") // msg unregister for member is not exist
		{
			$set_msg_reg = "SELECT * FROM msg_story WHERE sequence_story = '".$sequence_story."' and group_id = '".$group_id."' limit 1";
		}
		
		log_write($in_trxids, "  Content ", $set_msg_reg);
		$qry_catalog_story = mysql_query($set_msg_reg);
		$num_catalog_story = mysql_num_rows($qry_catalog_story);
		if($num_catalog_story >= 1)
		{
			log_write($in_trxids, "  Content ", "CATALOG MSG STORY");
			while($data_catalog_story = mysql_fetch_array($qry_catalog_story))
			{
				$data_msg_sms 	= $data_catalog_story['msg_story'];
				$data_sid 		= $data_catalog_story['sid'];
				$data_urls 		= $data_catalog_story['url'];
				$data_urls		= trim($data_urls);
				$objectid 		= $data_catalog_story['objectid'];
				$msgtype 		= $data_catalog_story['msgtype'];
				$msgstatus 		= $data_catalog_story['msgstatus'];
				$msgkind 		= $data_catalog_story['msgkind'];
				
				$sequence_story = $data_catalog_story['sequence_story'];	
				
			}
			if($sequence_story == 1)
			{
				$add_his_story_msg = "INSERT INTO his_story(group_id,msisdn,sequence_story,updatetime,order_id)VALUES('".$group_id."','".$in_msisdn."','".$sequence_story."',now(),'".$order_id."')";
			}
			elseif($sequence_story > 1)
			{
				$add_his_story_msg = "UPDATE his_story SET sequence_story = '".$sequence_story."',updatetime = now() WHERE group_id = '".$group_id."' AND msisdn = '".$in_msisdn."' AND order_id = '".$order_id."'";
			}		
			mysql_query($add_his_story_msg);
			log_write($in_trxids, "  History ", $add_his_story_msg);
		}
		else
		{
			log_write($in_trxids, "  Content ", "Content is Empty for sequence : ".$sequence_story);
		}	
	}
	
	if($msgkind == 1)
	{
		$msg_story_url = $data_msg_sms;
		log_write($in_trxids, "  Content ", $msg_story_url);
	}
	elseif($msgkind == 2)
	{
		if($data_urls !="")
		{
			$url_ori = $data_urls.$in_msisdn;
			log_write($in_trxids, "  URL ORI", $url_ori);
			//$get_url_tiny = "http://sl9.co/create.php?u=$url_ori";
			$get_url_tiny = "http://10.1.1.83/portal/tiny/create.php?g=$url_ori";
			log_write($in_trxids, "  URL ORI", $get_url_tiny);
			$url_tiny = file_get_contents($get_url_tiny);
			if($url_tiny !="")
			{
				$msg_story_url = $data_msg_sms." ".trim($url_tiny);
			}
			else
			{
				$msg_story_url = $data_msg_sms." ".trim($url_ori);
			}
		}
		
		
		log_write($in_trxids, "  URL Tiny", $msg_story_url);
		
	}
	elseif($msgkind == 3)
	{
		$code_gen = file_get_contents($url_gen_code);
		log_write($in_trxids, "  Code Gen", $code_gen);
		if($data_urls !="")
		{
			$get_url_tiny = "http://sl9.co/create.php?u=$data_urls";
			$url_tiny = file_get_contents($get_url_tiny);
			//$add_cek = 'cek ';
		}
		log_write($in_trxids, "  msg", "Generate API");
		log_write($in_trxids, "  kode produk", $key3);
		log_write($in_trxids, "  URL WAP", $data_urls);

		$msg_story_url = $msg_story." ".$code_gen.$add_cek." ".trim($url_tiny);
	}
	elseif($msgkind == 4)
	{
		if($data_urls !="")
		{
			$url_ori = $data_urls."o=".$objectid."%26m=".base64_encode($in_msisdn);
			log_write($in_trxids, "  URL ORI", $url_ori);
			//$get_url_tiny = "http://sl9.co/create.php?u=$url_ori";
			$get_url_tiny = "http://10.1.1.83/portal/tiny/create.php?g=$url_ori";
			$get_url_tiny = str_replace(" ","", $get_url_tiny); 
			log_write($in_trxids, "  URL ORI", $get_url_tiny);
			$url_tiny = file_get_contents($get_url_tiny);
			if($url_tiny !="")
			{
				$msg_story_url = $data_msg_sms." ".trim($url_tiny);
			}
			else
			{
				$url_ori = $data_urls."o=".$objectid."%&m=".base64_encode($in_msisdn);
				$msg_story_url = $msg_story." ".trim($url_ori);
			}
		}
		
		
		log_write($in_trxids, "  URL Tiny", $msg_story_url);

	}
	elseif($msgkind == 5)
	{
		if($data_urls !="")
		{
			$url_ori = $data_urls."o=".$objectid."%26m=".base64_encode($in_msisdn);
			log_write($in_trxids, "  URL ORI ", $url_ori);
			$get_url_tiny = "http://10.1.1.83/portal/tiny/create.php?g=$url_ori";
			$url_tiny = file_get_contents($get_url_tiny);
			//$add_cek = 'cek ';
			$msg = " di ";
			$msg_story_url = $data_msg_sms.$title.$msg.trim($url_tiny);
		}
		else
		{
			$msg_story_url = $data_msg_sms;
		}	
	}
	elseif($msgkind == 6)
	{
		if($data_urls !="")
		{
			$url_ori = $data_urls."o=".$objectid."%26m=".base64_encode($in_msisdn);
			log_write($in_trxids, "  URL ORI", $url_ori);
			//$get_url_tiny = "http://sl9.co/create.php?u=$url_ori";
			$get_url_tiny = "http://10.1.1.83/portal/mimpiman/create.php?a=$url_ori";
			log_write($in_trxids, "  URL ORI", $get_url_tiny);
			$url_tiny = file_get_contents($get_url_tiny);
			if($url_tiny !="")
			{
				$msg_story_url = $data_msg_sms." ".trim($url_tiny);
			}
			else
			{
				$msg_story_url = $data_msg_sms." ".trim($url_ori);
			}
		}
		else
		{
			$msg_story_url = $data_msg_sms;
		}
		log_write($in_trxids, "  URL Tiny", $msg_story_url);
	}
	
	if(($msg_story_url != "") && ($data_sid !="") && ($in_msisdn !="") && ($keywords != "") && ($in_trxids != "")) // for wellcome message
	{
		log_write($in_trxids, "  SMS ", $msg_story_url);
		
		$insert_t_mt_dc = "INSERT INTO t_mt_dc(sid,msisdn,insert_time,trx_time,updtime,trx_id,sts,sms,method,shortcode,keyword,isms,age,provider,objectId)VALUES ('".$data_sid."','".$in_msisdn."',now(),now(),now(),'".$in_trxids."','0','".$msg_story_url."','pull','6768','".$keywords."','".$in_smstxt."',0,'".$in_operat."','".$objectid."')";
		mysql_query($insert_t_mt_dc);
		log_write($in_trxids, "  INSERT MT DC ", $insert_t_mt_dc);
	
	}

	log_write($in_trxids, "finish", "counted as success\m");
	mysql_close($dbCN);
	
	echo "<?xml version='1.0'?><PUSH><STATUS>0</STATUS><TRANSID>".$in_trxids."</TRANSID><MSG>Message processed successfully</MSG></PUSH>";
	
function log_write($ptrxid, $psubject, $pmsg)
{
	$path = DIR_LOG.LOG_PFX.LOG_SFX.date(LOG_PTN).".log";
	$objfile = fopen($path, "a");
		chmod($path, 0777);
		$pmsg = str_replace("\n", "", $pmsg);
		$pmsg = str_replace("\m", "\n", $pmsg);
		fprintf($objfile, "%s|4|cm-tools|%-8s|%-15s|%s\n", date("Y-m-d H:i:s"), $ptrxid, $psubject, $pmsg);
	fclose($objfile);
}
?>