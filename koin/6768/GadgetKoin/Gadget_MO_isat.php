<?php

$msisdn = $_GET['msisdn'];
$sms = $_GET['sms'];
$trxdate = $_GET['trxdate'];
$substype = $_GET['substype'];
$transid = $_GET['transid'];
$sc = $_GET['sc'];

if(($msisdn == "") || ($sms == "") || ($transid == "") || ($sc == ""))
{

}

echo '<?xml version="1.0" ?><MO><STATUS>0</STATUS><TRANSID>".$transid."</TRANSID><MSG>Successfully</MSG></MO>';
$pieces = explode(" ", $sms);

$key = $pieces[0];
$key1 = $pieces[1];
$key2 = $pieces[2];
$key3 = $pieces[3];
$key3 = $pieces[4];


mysql_query('122.129.112.162','edi','3disit3SQL');
mysql_select_db('cms_gadget_6768');

if(strtolower($key) == "harga")
{
	$keyreply = "HARGA";
	$sql1 = "select * from _content where merk like '%$key1%' and type like '%$key2%' limit 0, 1";
	$sql2 = "select * from _content where merk like '%$key1%' and type like '%$key2%$key3' limit 0, 1";
	$sql3 = "select * from _content where merk like '%$key1%$key2%' and type like '%$key3%$key4%' limit 0, 1";
	
	$qry = mysql_query($sql1);
	$numqry = mysql_num_rows($qry);
	if($numqry >= 1)
	{
		createLogOut($sql1,$transid,"Query Firts");
		while($dataqry = mysql_fetch_array($qry))
		{
			$idreply = "1";
			$merkh = $dataqry['merk'];
			$typeh = $dataqry['type'];
			$price_newh = $dataqry['price_new'];
			$price_sech = $dataqry['price_second'];
		}		
	}
	else
	{
		$qry2 = mysq_query($sql2);
		$numqry2 = mysql_num_rows($qry2);
		if($numqry2 >= 1)
		{
			createLogOut($sql2,$transid,"Query Secn");
			while($dataqry2 = mysql_fetch_array($qry2))
			{
				$idreply = "1";
				$merkh = $dataqry2['merk'];
				$typeh = $dataqry2['type'];
				$price_newh = $dataqry2['price_new'];
				$price_sech = $dataqry2['price_second'];
			}
		}
		else
		{
			$qry3 = mysq_query($sql3);
			$numqry3 = mysql_num_rows($qry3);
			if($numqry3 >= 1)
			{
				createLogOut($sql3,$transid,"Query Third");
				while($dataqry3 = mysql_fetch_array($qry3))
				{
					$idreply = "1";
					$merkh = $dataqry3['merk'];
					$typeh = $dataqry3['type'];
					$price_newh = $dataqry3['price_new'];
					$price_sech = $dataqry3['price_second'];
				}
			}
			else
			{
				createLogOut("HP and Gadget Not Exist in Catalog",$transid,"Note");	
			}
		}
	}
	$msgvalue = "(HARGA) ".$merkh." ".$typeh." Harga Baru Rp.".$price_newh. "Harga Second Rp.".$price_sech;
	
	
	
}
else
{
	$sqlkeyword = "SELECT * FROM WHERE keyword = '$key' and orderingcode = '$key' limit 0, 1";
	$sqlkeyword2 = "SELECT * FROM WHERE keyword = '$key1' and orderingcode = '$key2' limit 0, 1";
	
	$qrykeyword = mysql_query($sqlkeyword);
	$numkey = mysql_num_rows($qrykeyword);
	if($numkey >= 1)
	{
		$keyAct = "1";
		while($datakey = mysql_fetch_array($qrykeyword))
		{
			$objectidkey = $datakey['replyID'];
			$keyreply = strtoupper($datakey['keyword']);
		}
	}
	else
	{
		$qrykeyword2 = mysql_query($sqlkeyword2);
		$numkey2 = mysql_num_rows($qrykeyword2);
		if($numkey2 >= 1)
		{
			$keyAct = "1";
			while($datakey2 = mysql_fetch_array($qrykeyword2))
			{
				$objectidkey = $datakey2['replyID'];
				$keyreply = strtoupper($datakey['keyword']);
			}
		}
	}
	if($keyAct == "1")
	{
		$keydata = "SELECT * FROM _content WHERE objectid = '$objectidkey' limit 0, 4";
		$qrydataqry = mysql_query($keydata);
		$numkeydataqry = mysql_num_rows($qrydataqry);
		if($numkeydataqry >= 1)
		{
			while(dataqry3 = mysql_fetch_array($qrydataqry))
			{
				$idreply = "1";
				$merkh = $dataqry3['merk'];
				$typeh = $dataqry3['type'];
				$price_newh = $dataqry3['price_new'];
				$price_sech = $dataqry3['price_second'];
				
				$msgvalue = $keyreply." Rekomendasi HP-Gadget ".$merkh." ".$typeh." Harga Baru Rp.".$price_newh." Harga Second Rp ".$price_sech;
			}
		}
		else
		{
			createLogOut("HP and Gadget Not Exist in Catalog",$transid,"Note");
		}
		
		
	}
}


	
if($idreply == "1")
{
	$https="http://127.0.0.1/koin/6768/_main_mt.php?";
	$urlopen = "uid=indosat&pwd=indosat&serviceid=67680184047004&msisdn=".$msisdn."&sms=".urlencode($msgvalue)."&smstype=0&transid=".$transid."&keyword=HARGA&delivery_method=pull&operator=indosat";
	
	$url = $https.$urlopen;
	createLogOut($url, $transid, " URL : ");
	$buffer = "";
	$handle = fopen($url, "r");
	if ($handle)
	{
		while (!feof($handle)) { $buffer .= fgets($handle, 4096);	}
		fclose($handle);
		$is_sending = trim($buffer);
		createLogOut($is_sending, $transid, " Result : ");

	}
	else
	{
		createLogOut($handle,$transid, " Error : ");
	}
}

function createLogOut($param, $transid_s, $action)
{
	$tanggal_access = date("Ymd");
	//$dir_acc = "/opt/apps/9155/log/xl/receiver/dr/";
	$dir_acc = "";
	$file_log = $dir_acc."HP".$tanggal_access.".log";

	$handle = fopen($file_log, "a");
		fprintf($handle, "%s", date("Y-m-d H:i:s")."|".$transid_s."|".$action.": ".$param."\n");
	fclose($handle);
}

?>