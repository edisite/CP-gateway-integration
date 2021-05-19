
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
<td width="100" align="center"><a href="main.php">RECRUITMENT</a></td>
<td width="100" align="center"><a href="careperhours.php">User Attempt / Hours</a></td>
<td width="43" align="center"><a href="dr2pull.php">DR2PULL</a></td>
<!--<td width="43" align="center"><a href="dr2pull.php">DR2PULL</a></td>-->
<td width="70" align="center"><a href="dr2push.php">DR2PUSH</a></td>

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

<form name="form1" method="post" action="careperhours.php">
<td height="117" align="left" valign="middle" class="shade">
<b>Search for user attempt: </b> &nbsp;&nbsp;<br>
<table border="0">
  <tbody><tr>
    <td>DATE :</td>
    <td>
	<input type="text" id="from" name="from" />
      </td>
	<td></td>
    <td></td>
	<td>
		<select name="keyword">
		<option value="CINTA">CINTA</option>
			<option value="MOCI">MOCI</option>
			<option value="ARTIS">ARTIS</option>
			<option value="COIM">COIM</option>
			<option value="RAHASIA">RAHASIA</option>
			<option value="TOLAK">TOLAK</option>
			<option value="TALU">TALU</option>
			<option value="SUKA">SUKA</option>
		</select>
	</td>
	<td><input type="submit" name="Submit" value="Search"></td>
  </tr>
</tbody></table>
</form>
<tr class="shade" onmouseover="this.className = &#39;rowselected&#39;;" onmouseout="this.className = &#39;shade&#39;;" valign="top">
<td>user attempt :</td>
</tr>
<tr class="shade" onmouseover="this.className = &#39;rowselected&#39;;" onmouseout="this.className = &#39;shade&#39;;" valign="top">
<td>Date : <?=$from?> | keyword : <?=$keyword?></td>
</tr>
<tr class="shade" onmouseover="this.className = &#39;rowselected&#39;;" onmouseout="this.className = &#39;shade&#39;;" valign="top">
<td>
	<table border="1" style="border-collapse:collapse;align:center;">
			<tr align="center" bgcolor ='#CD5C5C'>
				<td width="80px"><b>Hours</b></td>
				<td width="80px"><b>Type</b></td>
				<td width="60px"><b>Keyword</b></td>
				<td width="60px"><b>Total</b></td>
			</tr>
		<?
			$countreg = 0;
			$countunreg = 0;
			$rec=0;
			if($from != "")
			{
				$q = mysql_query("SELECT HOUR( dtm ) AS h, count( * ) AS ttl, keyword, mo_type FROM recv_mo_acv WHERE date( dtm ) = '".$from."' AND keyword = '".$keyword."' AND mo_type = 'REG' GROUP BY h");
				$x = mysql_query("SELECT HOUR( dtm ) AS h, count( * ) AS ttl, keyword, mo_type FROM recv_mo_acv WHERE date( dtm ) = '".$from."' AND keyword = '".$keyword."' AND mo_type = 'UNREG' GROUP BY h");
				while($result=mysql_fetch_array($q))
				{	
					$hours = $result['h'];
					$ttl = $result['ttl'];
					$keyword = $result['keyword'];
					$mo_type = $result['mo_type'];
					echo "	<tr bgcolor = '#F0F0F0'>				
							<td align='center'>$hours</td>
							<td align='right'>$mo_type</td>
							<td align='center'>$keyword</td>
							<td align='center'>$ttl</td>
							</tr>
					";		
				}
				while($result=mysql_fetch_array($x))
				{	
					$hours = $result['h'];
					$ttl = $result['ttl'];
					$keyword = $result['keyword'];
					$mo_type = $result['mo_type'];
					echo "	<tr bgcolor='#E0E0E0'>				
							<td align='center'>$hours</td>
							<td align='right'>$mo_type</td>
							<td align='center'>$keyword</td>
							<td align='center'>$ttl</td>
							</tr>
					";		
				}
			}
		
			$db->free_memory($q);
			
		?>
	<table>

	
</td>
</tr></tbody></table>
</body></html>