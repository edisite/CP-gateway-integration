<?

$user = $_POST['usrname'];
$pass = $_POST['passwd'];	
	
if (($user == "indosat1") && ($pass == "indosat1"))
{
session_register("myusername");
session_register("mypassword");
$ownid="1";
header('Location: main.php?ownid='.$ownid);
//header('Location: dr2pull.php');	

} else {
		echo "User name or Password is fail.";
}

?>
