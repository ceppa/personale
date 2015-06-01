<?
	error_reporting (E_ALL);
	$sitename="Personale";
	ini_set ('session.name', '$siteName');
	session_start();
	if(!isset($_SESSION["rnd"]))
		$_SESSION["rnd"]=md5(rand());
	date_default_timezone_set ('Europe/Rome');
?>
