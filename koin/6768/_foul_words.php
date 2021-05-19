<?php
function isHavingFouldWords($text)
{
	$temp1 = strtolower($text);
	$temp2 = "";
	$r = "";
	$n = strlen($temp1);
	if ($n < 1) { return ""; } // if text is empty then return empty string or not having foul words

	// list of foul words - do not add "shiit" if there is "shit" in the list
	$foul[0] = "anjing";
	$foul[1] = "asu";
	$foul[2] = "tai";
	$foul[3] = "tahi";
	$foul[4] = "taek";
	$foul[5] = "babi";
	$foul[6] = "shit";
	$foul[7] = "fuck";
	$foul[8] = "ngentot";
	$foul[9] = "kontol";
	$foul[10] = "memek";
	$foul[11] = "neraka";
	$foul[12] = "jahanam";
	$foul[13] = "setan";
	$foul[14] = "iblis";
	$foul[15] = "dajal";
	$foul[16] = "penipu";
	$foul[17] = "maling";
	$foul[18] = "nipu";
	$foul[19] = "nepu";
	$foul[20] = "tepu";
	$foul[21] = "bohong";
	$foul[22] = "bong";
	$foul[23] = "mati";
	$foul[24] = "matek";
	$foul[25] = "modar";
	$foul[26] = "modiar";
	$foul[27] = "matih";
	$foul[28] = "mampus";
	$foul[29] = "silit";
	$foul[30] = "ashole";
	$foul[31] = "ashol";
	$foul[32] = "jancuk";
	$foul[33] = "jiancuk";
	$foul[34] = "dancuk";
	$foul[35] = "diancuk";
	$foul[36] = "bajingan";
	$foul[37] = "perek";
	$foul[38] = "damput";
	$foul[39] = "diamput";
	$foul[40] = "jembut";
	$foul[41] = "bangsat";

	// begin processing
	for ($i = 0; $i < $n; $i++)
	{
		$s = substr($temp1, $i, 1);
		$a = ord($s);
		if ($a < 97 || $a > 122) { $s = " "; } // convert non alphabet into space
		if ($s != $r) // remove repeating characters, i.e. "shiiiit" will become "shit"
		{
			$r = $s;
			$temp2 = $temp2.$s;
		}
	}

	$arr = explode(" ", $temp2);
	$result = array_intersect($arr, $foul);
	$m = count($result);
	if ($m > 0)
	{
		return $result[0];	// if fould word is found then return first foul word
	}
	else
	{
		return "";	// if foul word not found then return empty string
	}
}
?>