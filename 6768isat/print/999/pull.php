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
	if($_GET['telco']=='xl') { $logo = "http://localhost/portal/report/6768isat/img/xl.jpg"; }
	else { $logo = "http://localhost/portal/report/6768isat/img/indosat.jpg"; }
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
$db->selectdb('mc_999_isat');
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
	$q = mysql_query("SELECT date_format(insertime,'%d-%m-%Y') as date,keyword, sid, count(msisdn) as dr2 FROM `report_dr_2` where devmethod='pull' and keyword='$keyword' and insertime like '$bb[cc] %' group by date,keyword,sid order by date,sid");
					
	while($result=mysql_fetch_object($q))
	{				
		if($result->sid=='99900001006015') {$price = 3300;}
		else if($result->sid=='99900001006028') {$price = 5500;}
		else if($result->sid=='99900001006054') {$price = 8800;}
		else if($result->sid=='99900001006041') {$price = 5500;}
		else if($result->sid=='99900001006016') {$price = 3300;}
		else if($result->sid=='99900001006029') {$price = 5500;}
		else if($result->sid=='99900001006055') {$price = 8800;}
		else if($result->sid=='99900001006042') {$price = 5500;}
		else if($result->sid=='99900001006017') {$price = 3300;}
		else if($result->sid=='99900001006030') {$price = 5500;}
		else if($result->sid=='99900001006056') {$price = 8800;}
		else if($result->sid=='99900001006043') {$price = 5500;}
		else if($result->sid=='99900001006018') {$price = 3300;}
		else if($result->sid=='99900001006031') {$price = 5500;}
		else if($result->sid=='99900001006057') {$price = 8800;}
		else if($result->sid=='99900001006044') {$price = 5500;}
		else if($result->sid=='99900001006019') {$price = 3300;}
		else if($result->sid=='99900001006032') {$price = 5500;}
		else if($result->sid=='99900001006058') {$price = 8800;}
		else if($result->sid=='99900001006045') {$price = 5500;}
		else if($result->sid=='99900001006020') {$price = 3300;}
		else if($result->sid=='99900001006033') {$price = 5500;}
		else if($result->sid=='99900001006059') {$price = 8800;}
		else if($result->sid=='99900001006046') {$price = 5500;}
		else if($result->sid=='99900001006021') {$price = 3300;}
		else if($result->sid=='99900001006034') {$price = 5500;}
		else if($result->sid=='99900001006060') {$price = 8800;}
		else if($result->sid=='99900001006047') {$price = 5500;}
		else if($result->sid=='99900001006022') {$price = 3300;}
		else if($result->sid=='99900001006035') {$price = 5500;}
		else if($result->sid=='99900001006061') {$price = 8800;}
		else if($result->sid=='99900001006048') {$price = 5500;}
		else if($result->sid=='99900001006023') {$price = 3300;}
		else if($result->sid=='99900001006036') {$price = 5500;}
		else if($result->sid=='99900001006062') {$price = 8800;}
		else if($result->sid=='99900001006049') {$price = 5500;}
		else if($result->sid=='99900001006024') {$price = 3300;}
		else if($result->sid=='99900001006037') {$price = 5500;}
		else if($result->sid=='99900001006063') {$price = 8800;}
		else if($result->sid=='99900001006050') {$price = 5500;}
		else if($result->sid=='99900001006025') {$price = 3300;}
		else if($result->sid=='99900001006038') {$price = 5500;}
		else if($result->sid=='99900001006064') {$price = 8800;}
		else if($result->sid=='99900001008083') {$price = 0;}
		else if($result->sid=='99900001006051') {$price = 5500;}
		else if($result->sid=='99900001006026') {$price = 3300;}
		else if($result->sid=='99900001006039') {$price = 5500;}
		else if($result->sid=='99900001006065') {$price = 8800;}
		else if($result->sid=='99900001008084') {$price = 0;}
		else if($result->sid=='99900001006052') {$price = 5500;}
		else if($result->sid=='99900001006027') {$price = 3300;}
		else if($result->sid=='99900001006040') {$price = 5500;}
		else if($result->sid=='99900001006066') {$price = 8800;}
		else if($result->sid=='99900001006053') {$price = 5500;}
		else if($result->sid=='99900001006195') {$price = 3300;}
		else if($result->sid=='99900001006196') {$price = 5500;}
		else if($result->sid=='99900001006198') {$price = 5500;}
		else if($result->sid=='99900001006199') {$price = 8800;}
		else if($result->sid=='99900001006197') {$price = 5500;}
		else if($result->sid=='99900001006189') {$price = 3300;}
		else if($result->sid=='99900001006190') {$price = 5500;}
		else if($result->sid=='99900001006192') {$price = 5500;}
		else if($result->sid=='99900001006193') {$price = 8800;}
		else if($result->sid=='99900001006191') {$price = 5500;}
		else if($result->sid=='99900001006201') {$price = 3300;}
		else if($result->sid=='99900001006202') {$price = 5500;}
		else if($result->sid=='99900001006204') {$price = 5500;}
		else if($result->sid=='99900001006205') {$price = 8800;}
		else if($result->sid=='99900001006203') {$price = 5500;}
		else if($result->sid=='99900001006221') {$price = 5500;}
		else if($result->sid=='99900001006220') {$price = 5500;}
		else if($result->sid=='99900001001002') {$price = 1100;}
		else if($result->sid=='99900001004003') {$price = 1100;}
		else if($result->sid=='99900001004004') {$price = 2200;}
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