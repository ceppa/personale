<?php
$ore_lav=480;
$version = '0.5.1';
$myhost="localhost";
$myuser="root";
$mypass="minair";
$mesi=array("gennaio","febbraio","marzo","aprile","maggio","giugno","luglio","agosto","settembre","ottobre","novembre","dicembre");
$giorni_settimana=array("domenica","luned&iacute;","marted&iacute;","mercoled&iacute;","gioved&iacute;","venerd&iacute;","sabato");
if(isset($_SESSION["pass"]))
	$login_password = $_SESSION["pass"];
$check_ip = true;
$do_time_out = false;
$session_time = 0.5;
$luser_tries = 1;
$big_luser = 10;
$livelli=array(0=>"User",1=>"Admin",2=>"Read only");
$indbase_val=21;
$indsparo_val=30;
$indnav_val=26;
/*    end prefs   */



// init..
$is_logged = false;
$random_string = '';
$address_is_good = false;
$agent_is_good = false;
if($check_ip == false) $address_is_good = true;
if(!$message = @$_GET['message']) $message = '';
$self = $_SERVER['PHP_SELF'];

ini_set ('session.name', 'pj');
session_start();


// watching the clock?

$time = '';
$time_out = false;
if($do_time_out == true)
{
    // I like to work with 1/100th of a sec
    $real_session_time = $session_time * 6000;
    //    timestamp..
    $now = explode(' ',microtime());
    $time = $now[1].substr($now[0],2,2);
    settype($time, "double");

    // time-out (do this before login events)
    if(isset($_SESSION['login_at']))
	{
        if($_SESSION['login_at'] < ($time - $real_session_time))
		{
            $message = 'sessione scaduta!';
            $time_out = true;
        }
    }
}


// let's go..

if ((isset($_POST['logout'])) or ($time_out == true)) {
    session_unset(); // kill it! (session 'pj' only, as it's been named, above)
    echo '<meta http-equiv="refresh" content="0;URL=',$self,'?message=',$message,'">';
    exit;
}


// already created a random key for this user?..

if (isset($_SESSION['key']))
    $random_string = $_SESSION['key'];
else
    // a new visitor.
    $random_string = $_SESSION['key'] = make_key();



// check their IP address..
if ((isset($_SESSION['remote_addr']))
and ($_SERVER['REMOTE_ADDR'] == $_SESSION['remote_addr'])) {
    $address_is_good = true;
} else { $_SESSION['remote_addr'] = $_SERVER['REMOTE_ADDR']; }

// check their user agent..
if ((isset($_SESSION['agent']))
and ($_SERVER['HTTP_USER_AGENT'] == $_SESSION['agent'])) {
    $agent_is_good = true;
} else { $_SESSION['agent'] = $_SERVER['HTTP_USER_AGENT']; }


// we simply concatenate the password and random key to create a unique session md5 hash
// hmac functions are not available on most web servers, but this is near as dammit.

// admin login
$expired=false;
if(isset($_POST['login']))
{

	$conn=mysqli_connect($myhost, $myuser, $mypass)
		or die("Connessione non riuscita ".mysqli_error($conn));
	((bool)mysqli_query($conn, "USE " . presenze));
	$result=mysqli_query($conn, "SELECT utenti.*,ditte.ragione_sociale,commesse.commessa FROM (utenti LEFT JOIN ditte ON utenti.ditta=ditte.id) LEFT JOIN commesse ON utenti.commessa_default=commesse.id WHERE login=\"".$_POST["loginuser"]."\" AND attivo=1");
	if(mysqli_num_rows($result))
	{
		$row=mysqli_fetch_assoc($result);
		$combined_hash = md5($random_string.$row['pass']);
	    // u da man!
	    if (($_POST['loginpass'] == $combined_hash))
		{
			if($row['expired']==1)
			{
				$message = 'password scaduta';
				$expired=true;
				$is_logged = false;
				$_SESSION['id']=$row['id'];
			}
			else
			{
				$_SESSION['login_at'] = $time;
				$_SESSION['session_pass'] = md5($combined_hash);
				$_SESSION['pass']=$row['pass'];
				$_SESSION['id']=$row['id'];
				$_SESSION['livello']=$row['livello'];
				$_SESSION['id_edit']=$_SESSION["id"];
				$_SESSION['nome']=$row['nome'];
				$_SESSION['cognome']=$row['cognome'];
				$_SESSION['data_inizio_coll']=$row['data_inizio_coll'];
				$_SESSION['data_fine_coll']=$row['data_fine_coll'];
				$_SESSION['blocca_a_data']=$row['blocca_a_data'];
				$_SESSION['ditta']=$row['ditta'];
				$_SESSION['commessa']=$row['commessa'];
				$_SESSION['id_commessa']=$row['commessa_default'];
				$_SESSION['patrono']=$row['patrono'];
				$_SESSION['ditta_rs']=$row['ragione_sociale'];
		        $is_logged = true;
			}
	    }
		 else
		{
			// oh oh..
			$message = 'password incorretta!';
			@$_SESSION['count']++;

			// they blew it.
			if($_SESSION['count'] >= $luser_tries)
			{
				$random_string = $_SESSION['key'] = make_key();
	            $_SESSION['count'] = 0;
				@$_SESSION['big_count']++;

				// they *really* blew it..
				if($_SESSION['big_count'] >= $big_luser)
				{
					die("<html><body text=\"#A80200\"><center><br><br><h1>no!</h1>
						after $big_luser failed attempts, clearly something's not right<br>
						<b>restart your browser if you want to try again</b>
						</center></html>");
				}
			}
		}
		((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
	}
	else
	{
		$message = 'utente sconosciuto';
		@$_SESSION['count']++;

		// they blew it.
		if($_SESSION['count'] >= $luser_tries)
		{
			$random_string = $_SESSION['key'] = make_key();
			$_SESSION['count'] = 0;
			@$_SESSION['big_count']++;

			// they *really* blew it..
			if($_SESSION['big_count'] >= $big_luser)
			{
				die("<html><body text=\"#A80200\"><center><br><br><h1>no!</h1>
					after $big_luser failed attempts, clearly something's not right<br>
					<b>restart your browser if you want to try again</b>
					</center></html>");
			}
		}
	}
	((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
}
elseif(isset($_POST['id']))
{
	$conn=mysqli_connect($myhost, $myuser, $mypass)
		or die("Connessione non riuscita ".mysqli_error($conn));

	((bool)mysqli_query($conn, "USE " . presenze));
	$query="UPDATE utenti SET expired=0,pass=\"".$_POST["newpass"]."\" WHERE id=".$_POST["id"];
	mysqli_query($conn, $query)
		or die("$query<br>".mysqli_error($conn));

	$result=mysqli_query($conn, "SELECT utenti.*,ditte.ragione_sociale,commesse.commessa FROM (utenti LEFT JOIN ditte ON utenti.ditta=ditte.id) LEFT JOIN commesse ON utenti.commessa_default=commesse.id WHERE utenti.id=\"".$_POST["id"]."\"");
	$row=mysqli_fetch_assoc($result);
	$combined_hash = md5($random_string.$row['pass']);

	$_SESSION['login_at'] = $time;
	$_SESSION['session_pass'] = md5($combined_hash);
	$_SESSION['pass']=$row['pass'];
	$_SESSION['id']=$row['id'];
	$_SESSION['id_edit']=$row['id'];
	$_SESSION['livello']=$row['livello'];
	$_SESSION['nome']=$row['nome'];
	$_SESSION['cognome']=$row['cognome'];
	$_SESSION['data_inizio_coll']=$row['data_inizio_coll'];
	$_SESSION['data_fine_coll']=$row['data_fine_coll'];
	$_SESSION['blocca_a_data']=$row['blocca_a_data'];
	$_SESSION['ditta']=$row['ditta'];
	$_SESSION['commessa']=$row['commessa'];
	$_SESSION['id_commessa']=$row['commessa_default'];
	$_SESSION['patrono']=$row['patrono'];
	$_SESSION['ditta_rs']=$row['ragione_sociale'];
	$is_logged = true;

	((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
	((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
}
elseif(isset($_GET["id_edit"])&&($_SESSION["livello"]>0))
{
	$_SESSION['id_edit']=$_GET["id_edit"];
	$conn=mysqli_connect($myhost, $myuser, $mypass)
		or die("Connessione non riuscita ".mysqli_error($conn));
	((bool)mysqli_query($conn, "USE " . presenze));
	$query="SELECT utenti.*,ditte.ragione_sociale,commesse.commessa FROM (utenti LEFT JOIN ditte ON utenti.ditta=ditte.id) LEFT JOIN commesse ON utenti.commessa_default=commesse.id WHERE utenti.id=".$_GET["id_edit"];

	$result=mysqli_query($conn, $query)
		or die("$query<br>".mysqli_error($conn));
	$row=mysqli_fetch_assoc($result);
	$combined_hash = md5($random_string.$row['pass']);
	$_SESSION['login_at'] = $time;
	$_SESSION['session_pass'] = md5($combined_hash);
	$_SESSION['pass']=$row['pass'];
	$_SESSION['id_edit']=$row['id'];
	$_SESSION['nome']=$row['nome'];
	$_SESSION['cognome']=$row['cognome'];
	$_SESSION['data_inizio_coll']=$row['data_inizio_coll'];
	$_SESSION['data_fine_coll']=$row['data_fine_coll'];
	$_SESSION['blocca_a_data']=$row['blocca_a_data'];
	$_SESSION['ditta']=$row['ditta'];
	$_SESSION['commessa']=$row['commessa'];
	$_SESSION['id_commessa']=$row['commessa_default'];
	$_SESSION['patrono']=$row['patrono'];
	$_SESSION['ditta_rs']=$row['ragione_sociale'];
	$is_logged = true;

	((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
	((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
}

// already logged in..
$combined_hash = md5($random_string.$_SESSION["pass"]);
if (@$_SESSION['session_pass'] == md5($combined_hash))
{
    if(($address_is_good == true) and ($agent_is_good == true))
        $is_logged = true;
	else
		$message = 'chi sei!?!';
}

// the page..

do_header($is_logged,$expired,$_SESSION["livello"],$_GET["op"],$ore_lav);
echo '<div id="header"><font class="message"><blink>',$message,'</blink></font>';
	if($is_logged)
		echo "<form name=\"logout_form\" action=\"$self\" method=\"post\">";
	echo "<table class=\"tab_header\"><tr><td width=20%>&nbsp";



//-----mymain------------------------------------------------------------------------------------------------------------------------------

if($is_logged)
{
	logged_header($op,$_SESSION["nome"]." ".$_SESSION["cognome"]." - ".$_SESSION["ditta_rs"]);
	close_logged_header($_SESSION["livello"]);

	if(!isset($_GET["id"]))
	{
		$query="SELECT id,cognome,nome FROM utenti WHERE attivo=1";
	
		$conn=mysqli_connect($myhost, $myuser, $mypass)
			or die("Connessione non riuscita ".mysqli_error($conn));
		((bool)mysqli_query($conn, "USE " . presenze));
		$result=mysqli_query($conn, $query);
		echo "<table class=\"edit\">";
		echo "<tr><td class=\"header\">id</td><td class=\"header\">nome</td><td class=\"header\">cognome</td></tr>";
		while($row=mysqli_fetch_assoc($result))
			echo "<tr><td>".$row["id"]."</td><td>".$row["nome"]."</td><td><a href=\"".$_SERVER['PHP_SELF']."?id=".$row["id"]."\">".$row["cognome"]."</a></td></tr>";
		echo "</table>";

		((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
	}
	else
	{
		$query="SELECT id,data_inizio_coll, data_fine_coll, cognome FROM utenti WHERE id=".$_GET["id"];
	
		$conn=mysqli_connect($myhost, $myuser, $mypass)
			or die("Connessione non riuscita ".mysqli_error($conn));
		((bool)mysqli_query($conn, "USE " . presenze));
		$result=mysqli_query($conn, $query);

		echo "<table class=\"edit\">";
		echo "<tr><td class=\"header\">data_inizio_coll</td><td class=\"header\">data_fine_coll</td><td class=\"header\">cognome</td></tr>";
		while($row=mysqli_fetch_assoc($result))
			echo "<tr><td>".$row["data_inizio_coll"]."</td><td>".$row["data_fine_coll"]."</td><td>".$row["cognome"]."</td></tr>";
		echo "</table>";

		((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);

	}
	

	echo "</div>";
}
else
{
	echo "</td></tr></table></div><div id=\"content\">";

    /*
        otherwise, display the login form..
        */
    echo '
<table border=0 cellspacing=0 cellpadding=0 align=center>
    <tr><td colspan=3 height=100></td></tr>
    <tr>
        <td></td>
        <td>';
	if(!$expired)
	{
		echo '
        <form name="passform" method=post action="',$self,'">
		<table class=login_form>
			<tr><td class=right>Utente:</td><td class=left><input type="text" class=input size="15" name="loginuser"></td></tr>
        	<tr><td class=right>Password:</td><td class=left><input type="password" class=input size="15" name="loginpass" title="protetta tramite hash casuale quando clicchi \'Entra\'"></td></tr>
        	<tr><td colspan=2 align=center><input type=submit class=button name=login value="Entra" title="su alcuni sistemi DEVI cliccare qui, premere invio non funziona" onclick="loginpass.value = hex_md5(\''.$random_string.'\' + hex_md5(loginpass.value))"></td></tr>
		</table>
        </form>';
	}
	else
	{
		echo '
        <b>modifica la password</b><br>
        <br>
        <form name="passform" method=post action="',$self,'">
		<input type="hidden" name="id" value="'.$_SESSION["id"].'">
		<table border=0 cellspacing=0 cellpadding=0 align=center>
			<tr><td>password:</td><td><input type="password" class=input size="15" name="newpass"></td></tr>
        	<tr><td>ripeti:</td><td><input type="password" class=input size="15" name="newpass2"></td></tr>
			<tr><td colspan=2 align=center><input type=button class=button name="chpwd" value="accetta" onclick="if(newpass.value==newpass2.value){newpass.value = hex_md5(newpass.value);newpass2.value=hex_md5(newpass2.value);submit();}else newpass.focus();"></td></tr>
		</table>
        </form>';
	}
        echo'
		</td>
        <td>
        </td>
    </tr>
</table>';

// Internet Explorer, etc..

	if(!$expired)
	{
		echo '
		<table width="50%" border=0 cellspacing=0 cellpadding=6 align=center>
		    <tr><td colspan=3 height=42></td></tr>';
		echo '</table>';
	}
	echo '</div>';
}



//-------------------------------------------------------------------------------------------------------------------------------------

function display_admin_nav($livello)
{
	echo "<div id=\"admin_nav\">";
	echo "<table class=admin_nav><tr>";
	if($livello==1)
		echo "<tr><td><a href=$self?op=display>presenze</a></td><td>|</td>";
	echo "<td><a href=$self?op=admin_users>utenti</a></td>";
	echo "<td>|</td>";
	if($livello==1)
	{
		echo "<td><a href=$self?op=admin_ditte>ditte</a></td><td>|</td>";
		echo "<td><a href=$self?op=admin_trasf>trasferte</a></td><td>|</td>";
		echo "<td><a href=$self?op=admin_commesse>commesse</a></td><td>|</td>";
	}
	echo "<td><a href=$self?op=admin_stampe>stampe</a></td>";
	echo "</tr></table>";
	echo "</div>";
}

/*
    function do_header()
    */

function logged_header($op,$page_title)
{
	if(substr($op,0,7)=="display")
	{
		$explode=explode("|",substr($op,7));
		echo "<a href=".$_SERVER["PHP_SELF"]."?op=stampa_mese&mese=".$explode[0]."&anno=".$explode[1]." target=_blank>stampa</a>";
		echo " | ";
		echo "<a href=".$_SERVER["PHP_SELF"]."?op=foglio_viaggio>foglio viaggio</a>";
	}
	elseif($op=="foglio_viaggio")
		echo "<a href=".$_SERVER["PHP_SELF"].">torna a presenze</a>";

	echo "</td><td width=60% align=center><font class=\"titolo\">$page_title</font></td>";
	echo "<td width=20% align=right>";
	echo "<input type=\"submit\" class=button value=\"Esci\" name=\"logout\">";
}

function close_logged_header($livello)
{
	echo "</td></tr></table></form>";
	echo "</div>";
	echo "<div id=\"content\">";
}
function do_header($is_logged,$expired,$level,$op,$ore_lav)
{
	global $version;
	$bodystyle="user";
	    echo '
<!-- <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd"> -->
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<title>hightecservice.biz</title>
<meta name="description" content="envysoft secure authentication">
<meta name="keywords"content="php,javascript,authentication,md5,hashing,php&javascript,authenticating,auth,AUTH,secure,secure login,security,php and javascript secure authentication,combat session fixation!">
<script language="JavaScript" src="md5.js"></script>
<script language="JavaScript">
	function MsgOkCancel(messaggio,pagina)
	{
		var fRet;
		fRet=confirm(messaggio);
		if(fRet)
			window.location=pagina;
	}
	function redirect(pagina)
	{
		window.location=pagina;
	}

</script>
<link rel="stylesheet" href="style.css" title="envysheet" type="text/css">
</head>
<body';

	echo " class=$bodystyle";
	if(!$is_logged)
	{
		if(!$expired)
			echo " onLoad=\"javascript:document.passform.loginuser.focus()\">";
		else
			echo ' onLoad="javascript:document.passform.newpass.focus()">';
	}
	else echo '>';
}


function make_key() {
    $random_string = '';
    for($i=0;$i<32;$i++) { $random_string .= chr(rand(97,122)); }
    return $random_string;
}

?>
