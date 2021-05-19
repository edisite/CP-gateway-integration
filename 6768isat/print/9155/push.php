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
if($tmp[1]=='9155' && $_GET['telco']=='xl')
{
	$db->selectdb('mc_9155_xl');
}
else if($tmp[1]=='9155' && $_GET['telco']=='isat')
{
	$db->selectdb('mc_9155_isat');
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
	$q = mysql_query("SELECT date_format(insertime,'%d-%m-%Y') as date,keyword, sid, count(msisdn) as dr2 FROM `report_dr_2` where devmethod='push' and keyword='$keyword' and insertime like '$bb[cc] %' group by date,sid order by date,sid");
					
	while($result=mysql_fetch_object($q))
	{
						
		if($result->sid=='9155') {$price = 0;}
		else if($result->sid=='915500') {$price = 0;}
		else if($result->sid=='915501') {$price = 500;}
		else if($result->sid=='915502') {$price = 500;}
		else if($result->sid=='915503') {$price = 1000;}
		else if($result->sid=='915504') {$price = 1000;}
		else if($result->sid=='915505') {$price = 2000;}
		else if($result->sid=='915506') {$price = 2000;}
		else if($result->sid=='915507') {$price = 3000;}
		else if($result->sid=='915508') {$price = 5000;}
		else if($result->sid=='915509') {$price = 8000;}
		else if($result->sid=='915510') {$price = 10000;}
		else if($result->sid=='915511') {$price = 15000;}
		else if($result->sid=='915512') {$price = 3000;}
		else if($result->sid=='915513') {$price = 5000;}
		else if($result->sid=='915514') {$price = 8000;}
		else if($result->sid=='915515') {$price = 10000;}
		else if($result->sid=='915516') {$price = 15000;}

		else if($result->sid=='91550134001003') {$price = 950;}
		else if($result->sid=='91550134008004') {$price = 1300;}
		else if($result->sid=='91550134004005') {$price = 1950;}
		else if($result->sid=='91550134008006') {$price = 2000;}
		else if($result->sid=='91550134020007') {$price = 2800;}
		else if($result->sid=='91550134006008') {$price = 2950;}
		else if($result->sid=='91550134006009') {$price = 4950;}
		else if($result->sid=='91550134006010') {$price = 7950;}

		else if($result->sid=='91550134107001') {$price = 2800;}
		else if($result->sid=='91550134107002') {$price = 4650;}
		else if($result->sid=='91550134107003') {$price = 7650;}
		else if($result->sid=='91550134110001') {$price = 2800;}
		else if($result->sid=='91550134110002') {$price = 4650;}
		else if($result->sid=='91550134110003') {$price = 7650;}

		else if($result->sid=='91550134061001') {$price = 950;}
		else if($result->sid=='91550134063002') {$price = 1300;}
		else if($result->sid=='91550134062003') {$price = 1950;}
		else if($result->sid=='91550134063004') {$price = 2000;}
		else if($result->sid=='91550134065005') {$price = 2800;}
		else if($result->sid=='91550134064006') {$price = 2950;}
		else if($result->sid=='91550134064007') {$price = 4950;}
		else if($result->sid=='91550134064008') {$price = 7950;}
		else if($result->sid=='91550134065009') {$price = 2800;}
		else if($result->sid=='91550134065010') {$price = 4650;}
		else if($result->sid=='91550134065011') {$price = 7650;}

		else if($result->sid=='91550134040001') {$price = 950;}
		else if($result->sid=='91550134042002') {$price = 1300;}
		else if($result->sid=='91550134041003') {$price = 1950;}
		else if($result->sid=='91550134042004') {$price = 2000;}
		else if($result->sid=='91550134044005') {$price = 2800;}
		else if($result->sid=='91550134043006') {$price = 2950;}
		else if($result->sid=='91550134043007') {$price = 4950;}
		else if($result->sid=='91550134043008') {$price = 7950;}
		else if($result->sid=='91550134044009') {$price = 2800;}
		else if($result->sid=='91550134044010') {$price = 4650;}
		else if($result->sid=='91550134044011') {$price = 7650;}

		else if($result->sid=='91550134054001') {$price = 950;}
		else if($result->sid=='91550134056002') {$price = 1300;}
		else if($result->sid=='91550134055003') {$price = 1950;}
		else if($result->sid=='91550134056004') {$price = 2000;}
		else if($result->sid=='91550134058005') {$price = 2800;}
		else if($result->sid=='91550134057006') {$price = 2950;}
		else if($result->sid=='91550134057007') {$price = 4950;}
		else if($result->sid=='91550134057008') {$price = 7950;}
		else if($result->sid=='91550134058009') {$price = 2800;}
		else if($result->sid=='91550134058010') {$price = 4650;}
		else if($result->sid=='91550134058011') {$price = 7650;}

		else if($result->sid=='91550134047001') {$price = 950;}
		else if($result->sid=='91550134049002') {$price = 1300;}
		else if($result->sid=='91550134048003') {$price = 1950;}
		else if($result->sid=='91550134049004') {$price = 2000;}
		else if($result->sid=='91550134051005') {$price = 2800;}
		else if($result->sid=='91550134050006') {$price = 2950;}
		else if($result->sid=='91550134050007') {$price = 4950;}
		else if($result->sid=='91550134050008') {$price = 7950;}
		else if($result->sid=='91550134051009') {$price = 2800;}
		else if($result->sid=='91550134051010') {$price = 4650;}
		else if($result->sid=='91550134051011') {$price = 7650;}
		else if($result->sid=='91550134111004') {$price = 500;}

		else if($result->sid=='91550134109005') {$price = 950;}
		else if($result->sid=='91550134111006') {$price = 1300;}
		else if($result->sid=='91550134111007') {$price = 2000;}
		else if($result->sid=='91550134139008') {$price = 1950;}
		else if($result->sid=='91550134139009') {$price = 4950;}
		else if($result->sid=='91550134139011') {$price = 9950;}
		else if($result->sid=='91550134108004') {$price = 500;}

		else if($result->sid=='91550134112005') {$price = 950;}
		else if($result->sid=='91550134108006') {$price = 1300;}
		else if($result->sid=='91550134108007') {$price = 2000;}
		else if($result->sid=='91550134137008') {$price = 1950;}
		else if($result->sid=='91550134137009') {$price = 4950;}
		else if($result->sid=='91550134137011') {$price = 9950;}
		
		else if($result->sid=='91550134021001') {$price = 1;}
		else if($result->sid=='91550134023002') {$price = 1;}

		else if($result->sid=='91550134021003') {$price = 950;}
		else if($result->sid=='91550134022004') {$price = 950;}

		else if($result->sid=='91550134023005') {$price = 1300;}
		else if($result->sid=='91550134022006') {$price = 1950;}
		else if($result->sid=='91550134023007') {$price = 2000;}
		else if($result->sid=='91550134015008') {$price = 1950;}
		else if($result->sid=='91550134015009') {$price = 4950;}
		else if($result->sid=='91550134015010') {$price = 9950;}
		else if($result->sid=='91550134134012') {$price = 2950;}
		else if($result->sid=='91550134134013') {$price = 4950;}
		else if($result->sid=='91550134134014') {$price = 7950;}
		else if($result->sid=='91550134034015') {$price = 0;}
		else if($result->sid=='91550134034016') {$price = 2000;}
		else if($result->sid=='91550134001011') {$price = 0;}
		else if($result->sid=='91550134008012') {$price = 0;}
		else if($result->sid=='91550134015011') {$price = 14950;}
		else if($result->sid=='91550134137015') {$price = 14950;}
		$revenue = $result->dr2*$price;
		if($result->dr2 != 0) {
			$pdf->Cell(10,8,$no,1,0,'L');$pdf->Cell(30,8,$result->date,1,0,'C');$pdf->Cell(40,8,$result->sid,1,0,'R');$pdf->Cell(30,8,rupiah($price),1,0,'R');$pdf->Cell(30,8,$result->dr2,1,0,'R');$pdf->Cell(50,8,rupiah($revenue),1,1,'R');
		}
		$countdr2 = $countdr2+$result->dr2;
		$countrev2 = $countrev2+$revenue;
		$no++;
	}
}
$pdf->Cell(110,8,'Total',1,0,'C');$pdf->Cell(30,8,$countdr2,1,0,'R');$pdf->Cell(50,8,rupiah($countrev2),1,1,'R');

$db->free_memory($q);
$db->close($con);
$D = date("d-m-Y");
$telco_name = strtoupper($_GET[telco]);
$keyword_name = strtoupper($_GET['keyword']);
$pdf->Output("PUSH REPORT DR2 ".$keyword_name." ".$D." - ".$tmp[0]." ".$telco_name." ".$tmp[1].".pdf","I");
?>