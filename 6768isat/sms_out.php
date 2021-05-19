
<!-- saved from url=(0053)http://202.43.169.58:82/csarka/report/carereport1.php -->
<html><head><meta http-equiv="Content-Type" content="text/html; charset=windows-1252">

<link rel="icon" href="http://www.indosat.com/indosat.ico" type="image/x-icon">
<link rel="shortcut icon" href="http://www.indosat.com/indosat.ico" type="image/x-icon">
<title>Report 6768 ISAT</title>
<link rel="stylesheet" href="./css/style.css" type="text/css">
</head><body topmargin="5" onLoad="if(document.getElementById(&#39;SearchFor&#39;)) document.getElementById(&#39;ctlSearchFor&#39;).focus();" bgcolor="white">
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.1/themes/base/jquery-ui.css" />
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="js/jquery-ui.js"></script>
<script src="js/jquery-ui.js"></script>
<script>
  $(function() {
    $( "#from" ).datepicker({ dateFormat: 'yy-mm-dd', showAnim: 'drop' });
	$( "#to" ).datepicker({ dateFormat: 'yy-mm-dd', showAnim: 'drop' });
  });
  </script><div id="search_suggest" style="visibility: hidden;"></div><div id="master_details"></div>
<table><tbody><tr>
<td width="200"><img src="./img/indosat copy.gif" width="200" height="64"></td>
<td width="231" align="center"><font size="+0"><b>REPORT 6768 ISAT </b></font></td>
<td width="10" align="center"></td><td width="42" align="center"><a href="logout.php">Logout</a></td>
<td width="43" align="center"><a href="main.php">RECRUITMENT</a></td>
<td width="43" align="center"><a href="dr2pull.php">DR2PULL</a></td>
<td width="43" align="center"><a href="dr2push.php">DR2PUSH</a></td>
</tr></tbody></table>

<!-- delete form -->

<table style="border: 1px solid rgb(192, 192, 192);" width="95%" align="center" border="0" cellpadding="4" cellspacing="1">
	<tbody><tr>
		
<?
	date_default_timezone_set('Asia/Jakarta');
	require('/var/www/class/db.php');
	$from = $_POST['from'];
	$to = $_POST['to'];
	$keyword = $_POST['keyword'];

	$datetime = date('Y-m-d H:i:s');
	$dates = date('Y-m-d');
	
	$db = new Database;
	$con = $db->connect('10.1.1.9','ahmad','4hm4dd3vs0l3gr4');
	$db->selectdb('mdw6768_isat');
?>

<form name="form1" method="post" action="sms_out.php">
<td height="117" align="left" valign="middle" class="shade">
<b>Search for: </b> &nbsp;&nbsp;<br>
<table border="0">
  <tbody><tr>
    <td>FROM :</td>
    <td>
	<input type="text" id="from" name="from" />
      </td>
	<td>TO :</td>
    <td>
	<input type="text" id="to" name="to" />
      </td>
	<td>
		<!--<select name="keyword">
			<option value="3DGame">3DGame</option>
			<option value="Bukbar">Bukbar</option>
			<option value="Bukber">Bukber</option>
			<option value="GK15">GK15</option>
			<option value="GK10">GK10</option>
			<option value="GK5">GK5</option>
			<option value="GKF">GKF</option>
		</select>-->
		<input type="text" name="keyword" placeholder="keyword" />
	</td>
	<td><input type="submit" name="Submit" value="Search"></td>
  </tr>
</tbody></table>
</form>
<tr class="shade" onMouseOver="this.className = &#39;rowselected&#39;;" onMouseOut="this.className = &#39;shade&#39;;" valign="top">
<td>Date : <?=$from?> s/d <?=$to?> | keyword : <?=$keyword?> | &cp=KOIN_6768&telco=isat&method=PULL"><img src="http://122.129.112.169/portal/report/img/pdf.png" height="20px" /></a></td>
</tr>
<tr class="shade" onmouseover="this.className = &#39;rowselected&#39;;" onmouseout="this.className = &#39;shade&#39;;" valign="top">
<td>
	<table border="1" style="border-collapse:collapse;align:center;">
			<tr align="center">
				<td width="25px"><b>Date</b></td>
				<td width="80px"><b>Partner</b></td>
				<td width="60px"><b>keyword</b></td>
				<td width="60px"><b>SMS</b></td>
				<td width="60px"><b>Method</b></td>
				<td width="40px"><b>Smstype</b></td>
				<td width="60px"><b>SID</b></td>
				<td width="40px"><b>DR STATUS</b></td>
				<td width="30px"><b>TOTAL</b></td>
			</tr>
		<?

				$q = mysql_query(" SELECT date( dtm ) AS dtm, partner, keyword, sms, dmethod, smstype, sid, age, drstatus, count( * ) AS ttl FROM `sms_out_isat`WHERE date( `dtm` ) = '$from' GROUP BY partner, keyword, sid, dmethod, age, drstatus ORDER BY `ttl` DESC ");
					
				while($result=mysql_fetch_array($q))
				{
					$dtm = $result['dtm'];
					$partner= $result['partner'];
					$sms= $result['sms'];
					$keyword= $result['keyword'];					
					$dmethod= $result['dmethod'];
					$smstype= $result['smstype'];										
					$sid= $result['sid'];					
					$drstatus= $result['drstatus'];
					$ttl= $result['ttl'];	
					$sms = decode($sms);
					$keyword = decode($keyword);
			echo "<tr>	
					<td>$dtm</td>
					<td>$partner</td>
					<td>$keyword</td>
					<td>$sms</td>
					
					<td>$dmethod</td>
					<td>$smstype</td>
					<td>$sid</td>
					<td>$drstatus</td>
					<td><b>$ttl</b></td>
				</tr>";
				}				

			$db->close($con);
		?>
	<table>
<td width="74"></td>
</tr></tbody></table>
</body></html>	