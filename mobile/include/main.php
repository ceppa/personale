<?
	require_once("const.php");
	
	if(!isset($_SESSION["id"]))
	{
		$header="Please login";
		$footer="";
		$content="";
		$module="login";
	}
	else
	{
		$header=$_SESSION["name"];
		$footer="";
		require_once("home.php");
		$mese=date("m");
		$anno=date("Y");
		$content=doHome($mese,$anno);
		$module="home";
	}
	$out=array
	(
		"header"=>$header,
		"footer"=>$footer,
		"content"=>$content,
		"module"=>$module
	);
	echo json_encode($out);
?>
