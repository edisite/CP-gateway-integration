<?
	switch ($keyword){		
		case "jodoh" : $shortname="jodoh";$cpid="crm"; break;
		case "game" : $shortname="game";$cpid="crm"; break;
		case "bola" : $shortname="bola";$cpid="crm"; break;
		case "sehat" : $shortname="sehat";$cpid="crm"; break;
		case "doa" : $shortname="doa";$cpid="crm"; break;
		case "gokil" : $shortname="gokil";$cpid="crm"; break;
		case "hoki" : $shortname="hoki";$cpid="crm"; break;
		case "kaya" : $shortname="kaya";$cpid="crm"; break;
		case "seleb" : $shortname="seleb";$cpid="crm"; break;
		case "hadis" : $shortname="Hadis";$cpid="crm"; break;
		case "testpull" : $shortname="testpull";$cpid="crm"; break;
		case "testpush" : $shortname="testpush";$cpid="crm"; break;
		case "cinta" : $shortname="CINTA";$cpid="crm"; break;
		case "zodiak" : $shortname="Zodiak";$cpid="crm"; break;
		default: $shortname=$keyword;$cpid="crm"; break;
		
				
	}
	
	if ($dmethod == "pull") {
		$type = 1;
	} else {
		$type = 0;
	}	
?>
