
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
<td width="60" align="center"><a href="main.php">RECRUITMENT</a></td>
<td width="100" align="center"><a href="careperhours.php">User Attempt / Hours</a></td>
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
			<option value="COIM">COIM</option>
			<option value="RAHASIA">RAHASIA</option>
			<option value="SUKA">SUKA</option>
			<option value="TALU">TALU</option>
			<option value="TOLAK">TOLAK</option>
			<option value="JPOP">JPOP</option>
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
				<td width="80px"><b>UNREG</b></td>
				<td width="80px"><b>RECRUITMENT</b></td>
			</tr>
		<?
			$totalreg = 0;
			$totalunreg = 0;
			$n = 0;
			$nn= 0;
			$totalrec = 0;
			$q0 = mysql_query("select count(*) as totalreg from gr_member_in inner join groups on gr_member_in.group_id=groups.group_id where groups.group_name = '$keyword'");
			$q1 = mysql_query("SELECT date_format(gr_member_in.last_upd,'%d-%m-%Y') as date, count(*) as reg from gr_member_in inner join groups on gr_member_in.group_id=groups.group_id where groups.group_name='$keyword' and last_upd between '$from 00:00:00' and '$to 23:59:59' group by date");
			$q2 = mysql_query("SELECT date_format(gr_member_out.last_upd,'%d-%m-%Y') as date, count(*) as unreg from gr_member_out inner join groups on gr_member_out.group_id=groups.group_id where groups.group_name='$keyword' and last_upd between '$from 00:00:00' and '$to 23:59:59' group by date");
			$num = mysql_num_rows($q1);
			$final1 = mysql_fetch_object($q0);
			if($num>0)
			{
				while($data = mysql_fetch_object($q1))
				{
				?>
					<tr align="center">
						<td width="80px"><?=$data->date?></td>
						<td width="80px"><?=$data->reg?></td>
				<?
					while($data2 = mysql_fetch_object($q2))
					{
					$unreg[$n] = $data2->unreg;
					$totalunreg = $totalunreg+$data2->unreg;
					$n++;
					}
					$rec = $data->reg - $unreg[$nn];
					$totalrec = $totalrec + $rec;
				?>
						<td width="80px"><?=$unreg[$nn]?></td>
						<td width="80px"><?=$rec?></td>
					</tr>
				<?
					$nn++;
					$totalreg = $totalreg+$data->reg;
				}
			}
			else
			{
				echo "<tr align='center'><td colspan=2>Data still empty</td></tr>";
			}
		?>
			<tr>
				<td width="80px"><center>Jumlah</center></td>
				<td width="80px"><center><?=$totalreg?></center></td>
				<td width="80px"><center><?=$totalunreg?></center></td>
				<td width="80px"><center><?=$totalrec?></center></td>
			</tr>
<? $db->close($con); ?>
	<table>
Total member sekarang : <?=$final1->totalreg?>
	
</td>
</tr></tbody></table>
</body></html>