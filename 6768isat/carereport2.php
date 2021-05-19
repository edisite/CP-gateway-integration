
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
<td width="43" align="center"><a href="main.php">RECRUITMENT</a></td>
<!--<td width="43" align="center"><a href="dr2pull.php">DR2PULL</a></td>-->
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

<form name="form1" method="post" action="carereport.php">
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
			<option value="ARTIS">ARTIS</option>
			<option value="AYO">AYO</option>
			<option value="CINTA">CINTA</option>
			<option value="HAPE">HAPE</option>
			<option value="MOCI">MOCI</option>
		</select>
	</td>
	<td><input type="submit" name="Submit" value="Search"></td>
  </tr>
</tbody></table>
</form>
<tr class="shade" onmouseover="this.className = &#39;rowselected&#39;;" onmouseout="this.className = &#39;shade&#39;;" valign="top">
<td>Date : <?=$from?> s/d <?=$to?> | keyword : <?=$keyword?></td>
</tr>
<tr class="shade" onmouseover="this.className = &#39;rowselected&#39;;" onmouseout="this.className = &#39;shade&#39;;" valign="top">
<td>
	<table border="1" style="border-collapse:collapse;align:center;">
			<tr align="center">
				<td width="80px"><b>Date</b></td>
				<td width="80px"><b>REG</b></td>
				<td width="60px"><b>UNREG</b></td>
				<td width="60px"><b>RECRUITMENT</b></td>
			</tr>
		<?
			$countreg = 0;
			$countunreg = 0;
			$rec=0;
			$beda = mysql_fetch_array(mysql_query("SELECT DATEDIFF('$to','$from') AS beda"));
			for($x=0;$x<=$beda[beda];$x++)
			{
				$bb = mysql_fetch_array(mysql_query("SELECT DATE_ADD('$from',INTERVAL $x DAY) AS cc"));
				$q = mysql_query("SELECT date_format(dtm,'%d-%m-%Y') as date, (select count(*) from recv_mo_acv where keyword='$keyword' and mo_type='reg' and dtm like '$bb[cc]%') as reg, (select count(*) from recv_mo_acv where keyword='$keyword' and mo_type='unreg' and dtm like '$bb[cc]%') as unreg FROM `recv_mo_acv` where keyword ='$keyword' and dtm like '$bb[cc]%' group by date");
					
				while($result=mysql_fetch_object($q))
				{
					if($result->reg != 0) {
						$rec = $rec+$result->reg-$result->unreg;
						echo "
							<tr>
								<td align='center'>$result->date</td>
								<td align='right'>$result->reg</td>
								<td align='right'>$result->unreg</td>
								<td align='right'>$rec</td>
							</tr>
						";
						$countreg = $countreg+$result->reg;
						$countunreg = $countunreg+$result->unreg;
					}
					
				}				
			}
			echo "<tr>	
					<td align='center'>Total</td>
					<td align='right'>$countreg</td>
					<td align='right'>$countunreg</td>
					<td align='right'>$rec</td>
				</tr>";
		
			$db->free_memory($q);
			
		?>
		<table>
			<tr><?
				$query1 = mysql_query("select count(*) as totalreg from gr_member_in inner join groups on gr_member_in.group_id=groups.group_id where groups.group_name = '$keyword'");
	$final1 = mysql_fetch_object($query1);
?>
				<td>Total Member Sekarang : <?=$final1->totalreg?></td>
<? $db->close($con); ?>
			</tr>
		</table>
	<table>

	
</td>
</tr></tbody></table>
</body></html>