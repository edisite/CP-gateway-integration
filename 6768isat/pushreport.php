
<!-- saved from url=(0053)http://202.43.169.58:82/csarka/report/carereport1.php -->
<html><head><meta http-equiv="Content-Type" content="text/html; charset=windows-1252">

<link rel="icon" href="http://www.indosat.com/indosat.ico" type="image/x-icon">
<link rel="shortcut icon" href="http://www.indosat.com/indosat.ico" type="image/x-icon">
<title>Report 6768 ISAT</title>
<link rel="stylesheet" href="./css/style.css" type="text/css">
</head><body topmargin="5" onload="if(document.getElementById(&#39;SearchFor&#39;)) document.getElementById(&#39;ctlSearchFor&#39;).focus();" bgcolor="white">
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
	$db->selectdb('mc_6768_isat');
?>

<form name="form1" method="post" action="pushreport.php">
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
		<select name="keyword">
		<?
			$query = mysql_query("SELECT distinct keyword FROM `report_dr_2` where `devmethod`='push'");
			while($qrow=mysql_fetch_object($query))
			{
				echo "<option value='$qrow->keyword'>$qrow->keyword</option>";
			}
		?>
		</select>
	</td>
	<td><input type="submit" name="Submit" value="Search"></td>
  </tr>
</tbody></table>
</form>
<tr class="shade" onmouseover="this.className = &#39;rowselected&#39;;" onmouseout="this.className = &#39;shade&#39;;" valign="top">
<td>Date : <?=$from?> s/d <?=$to?> | keyword : <?=$keyword?> | <a href="../print/6768/push.php?from=<?=$from?>&to=<?=$to?>&keyword=<?=$keyword?>&cp=KOIN_6768&telco=isat&method=PUSH"><img src="http://122.129.112.169/portal/report/img/pdf.png" height="20px" /></a></td>
</tr>
<tr class="shade" onmouseover="this.className = &#39;rowselected&#39;;" onmouseout="this.className = &#39;shade&#39;;" valign="top">
<td>
	<table border="1" style="border-collapse:collapse;align:center;">
			<tr align="center">
				<td width="25px"><b>No</b></td>
				<td width="80px"><b>Date</b></td>
				<td width="80px"><b>SID</b></td>
				<td width="60px"><b>Price</b></td>
				<td width="40px"><b>DR2</b></td>
				<td width="90px"><b>Revenue</b></td>
			</tr>
		<?
			function rupiah($angka)
			{
				return strrev(implode('.',str_split(strrev(strval($angka)),3)));
			}
			$countdr2 = 0;
			$countrev2 = 0;
			$no=1;
			$beda = mysql_fetch_array(mysql_query("SELECT DATEDIFF('$to','$from') AS beda"));
			for($x=0;$x<=$beda[beda];$x++)
			{
				$bb = mysql_fetch_array(mysql_query("SELECT DATE_ADD('$from',INTERVAL $x DAY) AS cc"));
				$q = mysql_query("SELECT date_format(updatetime,'%d-%m-%Y') as date,keyword, sid, count(msisdn) as dr2 FROM `report_dr_2` where devmethod='push' and keyword='$keyword' and updatetime like '$bb[cc] %' group by date,sid order by date,sid");
					
				while($result=mysql_fetch_object($q))
				{		
					if($result->sid=='676800') {$price = 0;}
					else if($result->sid=='676811') {$price = 15000;}
					else if($result->sid=='676810') {$price = 10000;}
					else if($result->sid=='676808') {$price = 5000;}
					else if($result->sid=='676806') {$price = 2000;}
					else if($result->sid=='676805') {$price = 2000;}
					else if($result->sid=='676803') {$price = 1000;}

					else if($result->sid=='67680184137003') {$price = 15000;}
					else if($result->sid=='67680184137002') {$price = 10000;}
					else if($result->sid=='67680184015003') {$price = 10000;}
					else if($result->sid=='67680184015002') {$price = 5000;}
					else if($result->sid=='67680184137001') {$price = 5000;}
					else if($result->sid=='67680184008010') {$price = 2000;}
					else if($result->sid=='67680184049008') {$price = 2000;}
					else if($result->sid=='67680184112004') {$price = 1000;}
					else if($result->sid=='67680184021001') {$price = 1000;}
					else if($result->sid=='67680184047004') {$price = 1000;}
					else if($result->sid=='67680184001023') {$price = 1;}
					else if($result->sid=='67680184001027') {$price = 0;}
					$revenue = $result->dr2*$price;
					if($result->dr2 != 0) {	
						echo "
							<tr>
								<td align='left'>$no</td>
								<td align='center'>$result->date</td>
								<td align='right'>$result->sid</td>
								<td align='right'>".rupiah($price)."</td>
								<td align='right'>$result->dr2</td>
								<td align='right'>".rupiah($revenue)."</td>
							</tr>
						";
					}
						$countdr2 = $countdr2+$result->dr2;
						$countrev2 = $countrev2+$revenue;
						$no++;					
				}				
			}
			echo "<tr>	
					<td colspan='4' align='center'>Total</td>
					<td align='right'>$countdr2</td>
					<td align='right'>".rupiah($countrev2)."</td>
				</tr>";
		 $qq = mysql_query("select sid,max(insertime) as maxdr from report_dr_2 where keyword='$keyword' and devmethod='push'");
$ss = mysql_fetch_object($qq);
echo "<tr><td colspan=5>$ss->maxdr</td><td>$ss->sid</td></tr>";
			$db->free_memory($q);
			$db->close($con);
		?>
	<table>
</td>
</tr></tbody></table>
</body></html>	