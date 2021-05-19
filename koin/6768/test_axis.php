<?php

$XML = "";
$XML = $XML.'<?xml version="1.0" encoding="UTF-8"?>';
$XML = $XML.'<message type="mtpush">';                 
$XML = $XML.'<msisdn>6283871023346</msisdn>';
$XML = $XML.'<sms>';
$XML = $XML.'<![CDATA[Di antara sifat Tuhan yang wajib lagi mulia, Kalam, namanya. Artinya Tuhan itu Berkata-kata.]]>';
$XML = $XML.'</sms>';
$XML = $XML.'<ccode>6768SMSPUSH1000</ccode>';
$XML = $XML.'<scode>KOINRELI</scode>';
$XML = $XML.'<cpid>koinsm</cpid>';
$XML = $XML.'<cppwd>SU9kRj</cppwd>';
$XML = $XML.'</message>';

$length = strlen($XML);
$URL = "http://103.3.221.154:10000/mt/mvas/send/";
$CURL = curl_init();
curl_setopt($CURL, CURLOPT_URL, $URL);
curl_setopt($CURL, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($CURL, CURLOPT_POST, 1);
curl_setopt($CURL, CURLOPT_POSTFIELDS, $XML);
curl_setopt($CURL, CURLOPT_HTTPHEADER, array("Content-Type: text/xml", "Content-length: ".$length));
curl_setopt($CURL, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($CURL);
curl_close($CURL);
echo $response; //Ack from CMS
?>
