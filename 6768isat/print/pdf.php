<?php
require('/var/www/service/libpdf/fpdf.php');

class PDF extends FPDF
{
function Header()
{
	date_default_timezone_set('Asia/Jakarta');
	$from = $_GET['from'];
	$to = $_GET['to'];
	$keyword = strtoupper($_GET['keyword']);
	$method = $_GET['method'];
	$tmp = explode('_',$_GET['cp']);
	$cp = $tmp[0].' '.$tmp[1];
	if($_GET['telco']=='xl') { $logo = "../6768xl/img/xl.jpg"; }
	else { $logo = "../6768xl/img/indosat.jpg"; }
	$D = date("d-m-Y");
	$this->Image($logo,10,6,30);
	$this->SetFont('Arial','B',14);
	$this->Cell(80);
	$this->Cell(30,10,$cp,0,0,'C');
	$this->SetFont('Arial','B',12);
	$this->Ln(5);
	$this->Cell(80);
	$this->Cell(30,10,$method.' REPORT DR2 '.$keyword,0,0,'C');
	$this->SetFont('Arial','B',10);
	$this->Ln(5);
	$this->Cell(80);
	$this->Cell(30,10,$from.' s/d '.$to,0,0,'C');
	$this->SetFont('Arial','',10);
	$this->Ln(9);
	$this->SetFillColor(255,170,42);
	$this->Cell(0,10,'Printed on '.$D,0,0,'R',1);
	$this->Ln(15);
	$this->SetFillColor(170,255,42);
	$this->SetFont('Times','',12);
	$this->Cell(10,9,'No',1,0,'C',1);$this->Cell(30,9,'Date',1,0,'C',1);$this->Cell(40,9,'SID',1,0,'C',1);$this->Cell(30,9,'Price',1,0,'C',1);$this->Cell(30,9,'DR2',1,0,'C',1);$this->Cell(50,9,'Revenue',1,1,'C',1);
}

function Footer()
{
	$this->SetY(-15);
    	$this->SetFont('Arial','I',8);
	$count_page = count($this->PageNo());
    	$this->Cell(0,10,'Page '.$this->PageNo().' of {nb}',0,0,'C');
}
}

$pdf = new PDF('P','mm','A4');
$pdf->AliasNbPages();
$pdf->AddPage();

require('/var/www/class/db.php');
$from = $_GET['from'];
$to = $_GET['to'];
$keyword = $_GET['keyword'];

$db = new Database;
$con = $db->connect('10.1.1.9','ahmad','4hm4dd3vs0l3gr4');
$tmp = explode('_',$_GET['cp']);
$cp = $tmp[0].' '.$tmp[1];
if($tmp[1]=='6768' && $_GET['telco']=='xl')
{
	$db->selectdb('mc_6768_xl');
}
else if($tmp[1]=='6768' && $_GET['telco']=='isat')
{
	$db->selectdb('mc_6768_isat');
}
else if($tmp[1]=='9155' && $_GET['telco']=='xl')
{
	$db->selectdb('mc_9155_xl');
}
else if($tmp[1]=='9155' && $_GET['telco']=='isat')
{
	$db->selectdb('mc_9155_isat');
}
else if($tmp[1]=='9818' && $_GET['telco']=='xl')
{
	$db->selectdb('mc_9818_xl');
}
else if($tmp[1]=='9818' && $_GET['telco']=='isat')
{
	$db->selectdb('mc_9818_isat');
}
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
	$q = mysql_query("SELECT date_format(insertime,'%d-%m-%Y') as date,keyword, sid, count(msisdn) as dr2 FROM `report_dr_2` where devmethod='pull' and isms like '$keyword%' and insertime like '$bb[cc] %' group by date,keyword,sid order by date,sid");
					
	while($result=mysql_fetch_object($q))
	{				
		if($result->sid=='67680184001027') {$price = 0;}
		else if($result->sid=='67680184001023') {$price = 1;}
		else if($result->sid=='67680184021001') {$price = 1100;}
		else if($result->sid=='67680184112004') {$price = 1100;}
		else if($result->sid=='67680184049008') {$price = 2200;}
		else if($result->sid=='67680184008010') {$price = 2200;}
		else if($result->sid=='67680184137001') {$price = 5500;}
		else if($result->sid=='67680184015002') {$price = 5500;}
		else if($result->sid=='67680184137002') {$price = 11000;}
		else if($result->sid=='67680184015003') {$price = 11000;}
		else if($result->sid=='67680184137003') {$price = 16500;}
		else if($result->sid=='676806') {$price = 2000;}
		else if($result->sid=='676803') {$price = 1000;}
		else if($result->sid=='676805') {$price = 2000;}
		else if($result->sid=='676808') {$price = 5000;}
		else if($result->sid=='676810') {$price = 10000;}
		else if($result->sid=='676811') {$price = 15000;}
		$revenue = $result->dr2*$price;
		if($result->dr2 != 0) {
			$pdf->Cell(10,8,$no,1,0,'L');$pdf->Cell(30,8,$result->date,1,0,'C');$pdf->Cell(40,8,$result->sid,1,0,'R');$pdf->Cell(30,8,rupiah($price),1,0,'R');$pdf->Cell(30,8,$result->dr2,1,0,'R');$pdf->Cell(50,8,rupiah($revenue),1,1,'R');
		}
		$countdr2 = $countdr2+$result->dr2;
		$countrev2 = $countrev2+$revenue;
		$no++;
	}
}
$pdf->SetFont('Arial','B');
$pdf->Cell(110,8,'TOTAL',1,0,'C');$pdf->Cell(30,8,$countdr2,1,0,'R');$pdf->Cell(50,8,rupiah($countrev2),1,1,'R');

$db->free_memory($q);
$db->close($con);
$D = date("d-m-Y");
$telco_name = strtoupper($_GET[telco]);
$keyword_name = strtoupper($_GET['keyword']);
$pdf->Output("PULL REPORT DR2 ".$keyword_name." ".$D." - ".$tmp[0]." ".$telco_name." ".$tmp[1].".pdf","I");
?>