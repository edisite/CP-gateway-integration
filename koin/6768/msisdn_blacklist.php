<?php
mysql_connect('10.1.1.10','edisite','1c0nn3ct8SQL');
mysql_select_db("edisite");

$SQLlist = "SELECT * FROM msisdn_blacklist WHERE msisdn = '".$msisdn."' and sc = '".$sc."'";

$QRYlist = mysql_query($SQLlist);
$NUMlist = mysql_num_rows($QRYlist);
if($NUMlist >= 1)
{
	$exitproc = "1";
}
else
{
	$exitproc = "0";
}
mysql_close();
?>