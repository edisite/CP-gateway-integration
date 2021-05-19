<?php

$XML = "";
$XML = $XML.'<?xml version="1.0"?>';
$XML = $XML.'<message>';
	$XML = $XML.'<sms type="mt">';
		$XML = $XML.'<destination id="1234567890">';
			$XML = $XML.'<address>';
				$XML = $XML.'<number type="national">02171221976</number>';
			$XML = $XML.'</address>';
		$XML = $XML.'</destination>';
		$XML = $XML.'<rsr type="all"/>';
		$XML = $XML.'<ud type="text">PUSH http for TEST services</ud>';
	$XML = $XML.'</sms>';
$XML = $XML.'</message>';

$XML = "";
$XML = $XML.'<?xml version="1.0"?>';
$XML = $XML.'<message>';
	$XML = $XML.'<sms type="mt">';
		$XML = $XML.'<destination messageid="6768_tomboati_12345678abcde">';
			$XML = $XML.'<address>';
				$XML = $XML.'<number type="national">02171221976</number>';
			$XML = $XML.'</address>';
		$XML = $XML.'</destination>';
		$XML = $XML.'<source>';
			$XML = $XML.'<address>';
				$XML = $XML.'<number type="abbreviated">6768</number>';
			$XML = $XML.'</address>';
		$XML = $XML.'</source>';
		$XML = $XML.'<ud type="text">test manual push message</ud>';
		$XML = $XML.'<rsr type="all"/>';
	$XML = $XML.'</sms>';
$XML = $XML.'</message>';

$length = strlen($XML);
$URL = "http://spm.telkomflexi.com:8080/spmapi/push.php";
$CURL = curl_init();
curl_setopt($CURL, CURLOPT_URL, $URL);
curl_setopt($CURL, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($CURL, CURLOPT_USERPWD, "676818:Puls4");
curl_setopt($CURL, CURLOPT_POST, 1);
curl_setopt($CURL, CURLOPT_POSTFIELDS, $XML);
curl_setopt($CURL, CURLOPT_HTTPHEADER, array("Content-Type: text/xml", "Content-length: ".$length));
curl_setopt($CURL, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($CURL);
curl_close($CURL);
echo $response; //Ack from CMS
?>
