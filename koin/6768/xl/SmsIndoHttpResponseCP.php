<?


	$Uid = $_GET['uid'];
	$Pwd = $_GET['pwd'];
	$serviceid = $_GET['serviceid'];
	$msisdn = $_GET['msisdn'];
	$sms = $_GET['sms'];	
	$transid = $_GET['transid'];
	$smstype = $_GET['smstype'];
	$delivery_method = $_GET['delivery_method'];
	$shortcode = $_GET['shortcode'];
	$keyword = $_GET['keyword'];
	$isms = $_GET['iSMS'];
	$operator = "isat";
	
	
	
	$sc = "6768";
	$trxdate = date("YmdHis");
	
	
	//include("crypt.php");
	
	
	$error = 0;
	//include("seriviceid.php");
	createLogOut("",$shortcode,$transid,"--- Start -----"); 
	createLogOut($sms,$shortcode,$transid," sms : "); 
	createLogOut($transid,$shortcode,$transid," transid : ");  	
	//http://<ip>:<port>/?uid=<..>&pwd=<..>&serviceid=<..>&msisdn=<..>&sms=<..>&transid=<..>&smstype=<..>&delivery_method=<..>
	// text or wap push
	// check parameter
	$lensid = strlen($serviceid);
	$lenmsisdn = strlen($msisdn);
	$lensms = strlen($sms);
	$lentransid = strlen($transid);
	$lenshortcode = strlen($shortcode);
	//echo $shortcode."<br>";
	if (($lensid <5) || ($lenmsisdn <5) || ($lensms <5) || ($lentransid <2)|| ($shortcode == "error")) {
			$error = 1;
			createLogOut($error,$shortcode,$transid," Parameter Not Complite:");			
		} 	
	
	$log_ins = "('".$msisdn."','".$transid."','".$serviceid."','".$sms."','".$smstype."','".$shortcode."','".$delivery_method."')";			
	
	createLogOut($log_ins,$shortcode,$transid," Incomming form CP "); 
	
	if ($error == 0) 
	{
		
		
			mysql_pconnect("10.1.1.9","edi","3disit3SQL");
			mysql_select_db('smppxl'); 

			$sqlcheck = mysql_query("SELECT dna, age, sid FROM queue_exel_mt WHERE msisdn='".$msisdn."' and trx_id='".$transid."'");
			$num = mysql_num_rows($sqlcheck);
		
			if ($num > 0) {
				
					createLogOut($idreply,$shortcode,$transid,"Doble : "); 
					$error = 2; 
				
			} else {	
				
				include "keyword.php";
				
				
					$myquery = "INSERT INTO queue_exel_mt (dtm,trxdtm,type,msisdn,trxid,operator,partner,shortcode,keyword,dmethod,sid, shortname,sms) VALUES  ";
					$myquery = $myquery."(now(),'";
					$myquery = $myquery. $trxdate;
					$myquery = $myquery. "','".$type."','";
					$myquery = $myquery. $msisdn;
					$myquery = $myquery. "','";
					$myquery = $myquery. $transid;
					$myquery = $myquery. "','";
					$myquery = $myquery. "xl";				
					$myquery = $myquery. "','";
					$myquery = $myquery. $cpid;
					$myquery = $myquery. "','";
					$myquery = $myquery. $shortcode;
					$myquery = $myquery. "','";
					$myquery = $myquery. $keyword;
					$myquery = $myquery. "','".$delivery_method."','".$serviceid."','".$shortname."','".rawurlencode($sms)."')";
					
					$myquery_o = "INSERT INTO sms_out_exel (dtm,trxdtm,smstype,msisdn,trxid,operator,partner,shortcode,keyword,dmethod,sid,sms) VALUES  ";
                                        $myquery_o  = $myquery_o."(now(),'";
                                        $myquery_o  = $myquery_o. $trxdate;
                                        $myquery_o  = $myquery_o. "','".$type."','";
                                        $myquery_o  = $myquery_o. $msisdn;
                                        $myquery_o  = $myquery_o. "','";
                                        $myquery_o  = $myquery_o. $transid;
                                        $myquery_o  = $myquery_o. "','";
                                        $myquery_o  = $myquery_o. "xl";
                                        $myquery_o  = $myquery_o. "','";
                                        $myquery_o  = $myquery_o. $cpid;
                                        $myquery_o  = $myquery_o. "','";
                                        $myquery_o  = $myquery_o. $shortcode;
                                        $myquery_o  = $myquery_o. "','";
                                        $myquery_o  = $myquery_o. $keyword;
                                        $myquery_o  = $myquery_o. "','".$delivery_method."','".$serviceid."','".rawurlencode($sms)."')";	
					


					createLogOut($myquery,$shortcode,$transid," DB Query :  ");					
					$mysqlquery = mysql_query($myquery);
					$myerror = mysql_error();
						createLogOut($myerror,$shortcode,$transid," DB Result :  ");
						mysql_query("COMMIT");


					createLogOut($myquery_o,$shortcode,$transid," DB Query :  ");
                                        $mysqlquery = mysql_query($myquery_o);
                                        $myerror_o = mysql_error();
                                                createLogOut($myerror_o,$shortcode,$transid," DB Result :  ");
                                                mysql_query("COMMIT");
				
				mysql_close();	
			}
		
	
		if ($error == 0) 
		{
			createLogOut("Done" ,$shortcode,$transid,"Result : ");			
		} 
		else 
		{
			createLogOut("Do Nothing" ,$shortcode,$transid,"Doble : ");
		}	
	} 
//	mysql_close();	
	
if ($error == 0) {	
	echo "<?xml version=\"1.0\" ?><push><status>0</status><transid>".$transid."</transid><msg>Message processed successfully</msg></push>";
} else {
	if ($error == 2) {
	 "<?xml version=\"1.0\" ?><push><status>2</status><transid>".$transid."</transid><msg>Doble</msg></push>";
	} else {
	echo "<?xml version=\"1.0\" ?><push><status>-1</status><transid>".$transid."</transid><msg>Parameter Incomplete</msg></push>";
	}
}
function createLogOut($param,$sc,$transid_s,$action) {
	$tanggal_access = date('Ymd');	
	if ($sc == "error") {
		$dir_acc = "/opt/apps/".$sc."/log/";
	} else {
		$dir_acc = "/opt/apps/".$sc."/log/xl/dispatcher/";
	}
	$file_log = $dir_acc."logSmsCP".$tanggal_access.".log";	
	chmod($file_log, 0777);	
	$handle = fopen($file_log, 'a+');
	//echo "masuk".date("Y-m-d H:i:s").";".$transid_s.";".$action.";".$param."\n"."<br>";
	fprintf($handle, date("Y-m-d H:i:s"));
	fprintf($handle," %s | ",$transid_s);
	fprintf($handle," %s | ",$action);
	fprintf($handle," %s ",$param."\n");
	fclose($handle);
	
}


function createDBFile($param, $sc,$transid_si, $dbtbl) {
	$tanggal_access = date('Ymd');	
	if ($sc == "error") {
		$dir_acc = "/opt/apps/".$sc."/log/";
	} else {
		$dir_acc = "/opt/apps/".$sc."/log/isat/dispatcher/";
	}
	
	$dir_acc = "/opt/apps/".$sc."/queue/isat/out/DB/";
	$file_log = $dir_acc.$dbtbl."_".$transid_si."_".$sc.".dat";
	
	$handle = fopen($file_log, 'w');
	fprintf($handle,"%s", $param);
	fclose($handle);
	createLogOut($file_log,$sc,$transid," DB File Create :");	
}

function hex_encode( $text, $joiner='' ) {
			for ($l=0; $l<strlen($text); $l++) {
					$letter = substr($text, $l, 1);
					$ret .= sprintf("%s%02X", $joiner, ord($letter));
			}
			return $ret;
		}

?>
