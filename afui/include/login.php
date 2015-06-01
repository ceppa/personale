<?

function doLogout()
{
	ob_start();
	require_once("const.php");
	unset($_SESSION["id"]);
	$content=trim(ob_get_clean());
	return $content;
}

function doLogin()
{
	ob_start();
	require_once("const.php");
	require_once("mysql.php");
	$user=$_POST["username"];
	$query="SELECT utenti.*
			FROM utenti 
			WHERE login='$user' AND attivo=1";
	$conn=new mysqlConnection;
	$result=$conn->do_query($query);
	$login=$conn->result_to_array($result,false);
	$logged=0;
	$expired=0;
	$message="";
	$page="";
	$header="";
	unset($_SESSION["id"]);
	if(count($login))
	{
		$combined_hash=md5($login[0]["pass"].$_SESSION["rnd"]);
		if($combined_hash==$_POST["password"])
		{
			$_SESSION["name"]=trim($login[0]["nome"]." ".$login[0]["cognome"]);
			$header=$_SESSION["name"];
			$_SESSION["id"]=$login[0]["id"];
			$_SESSION["livello"]=$login[0]["livello"];
			$logged=1;
			$expired=(int)$login[0]["expired"];
			if(!$expired)
			{
				$module="home";
			}
		}
		else
		{
			$message="wrong password";
		}
	}
	else
		$message="not a valid user";
	$conn=null;
	$content=trim(ob_get_clean());
	echo json_encode(
		array(
			"logged"=>"$logged",
			"expired"=>"$expired",
			"header"=>$header,
			"content"=>$content,
			"message"=>"$message",
			"page"=>$page
			)
		);
}

	$op=(isset($_REQUEST["op"])?$_REQUEST["op"]:"");
	switch($op)
	{
		case "doLogin":
			doLogin();
			break;
		case "doLogout":
			doLogout();
		default;
			break;
	}
?>
