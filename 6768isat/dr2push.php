
<!-- saved from url=(0054)http://202.43.169.58:82/csarka/report/main.php?ownid=1 -->
<html><head><meta http-equiv="Content-Type" content="text/html; charset=windows-1252">

<link rel="icon" href="http://www.indosat.com/indosat.ico" type="image/x-icon">
<link rel="shortcut icon" href="http://www.indosat.com/indosat.ico" type="image/x-icon">
<title>Report 6768 ISAT</title>
<link rel="stylesheet" href="./css/style.css" type="text/css">
</head><body topmargin="5" onload="if(document.getElementById(&#39;SearchFor&#39;)) document.getElementById(&#39;ctlSearchFor&#39;).focus();" bgcolor="white">
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.1/themes/base/jquery-ui.css" />
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="js/jquery-ui.js"></script>
<div style="visibility: hidden;" id="search_suggest">
<script>
  $(function() {
    $( "#from" ).datepicker({ dateFormat: 'yy-mm-dd', showAnim: 'drop' });
	$( "#to" ).datepicker({ dateFormat: 'yy-mm-dd', showAnim: 'drop' });
  });
  </script></div><div id="master_details"></div>

<table><tbody><tr>
<td width="200"><img src="./img/indosat copy.gif" width="200" height="64"></td>
<td width="161" align="center">
<font size="+0"><b>&nbsp;REPORT 6768 ISAT </b></font></td>
<td width="43" align="center"><a href="logout.php">Logout</a></td>
<td width="43" align="center"><a href="main.php">RECRUITMENT</a></td>
<td width="43" align="center"><a href="dr2pull.php">DR2PULL</a></td>
<td width="43" align="center"><a href="dr2push.php">DR2PUSH</a></td>
<td width="108" align="center">&nbsp;</td>
</tr></tbody></table>


<table style="border: 1px solid rgb(192, 192, 192);" width="95%" align="center" border="0" cellpadding="4" cellspacing="1">
	<tbody><tr>

<form name="form1" method="post" action="pushreport.php">
<td height="157" align="left" valign="middle" class="shade">
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
			require('/var/www/class/db.php');
			$db = new Database;
			$con = $db->connect('10.1.1.9','ahmad','4hm4dd3vs0l3gr4');
			$db->selectdb('mc_6768_isat');
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
</td>
</form>
</table>
</body></html>