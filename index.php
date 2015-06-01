<?php
date_default_timezone_set("Europe/Rome");
//ini_set("error_reporting",E_ALL & ~E_NOTICE & ~E_STRICT);
ini_set("error_reporting",E_ALL);

include ("include/session.php");
include ("include/const.php");
include ("include/mysql.php");
include ("include/datetime.php");
include ("include/auth.php");
include ("include/util.php");

if($is_logged)
{
	$report=(isset($_REQUEST["report"]) || (substr(@$_GET["op"],0,6)=="stampa"));
	if($report)
	{
		require_once("include/report.php");
		die();
	}

	if(isset($_REQUEST["op"]))
		$op=$_REQUEST["op"];
	else
		$op=($_SESSION["livello"]==2?"admin_users":"display");

	if($op=="edit")
	{
		if(!isset($_REQUEST["giorno"]))
			$op="display";
		else
		{
			if(is_array($_REQUEST["giorno"]))
				$test=$_REQUEST["giorno"][0];
			else
				$test=$_REQUEST["giorno"];
			if((($_SESSION["livello"]==0)||($_SESSION["livello"]==2))
					&&($_SESSION["blocca_a_data"]!="0000-00-00")
					&&(strtotime($_SESSION["blocca_a_data"])>=strtotime($test)))
				$op="display";
		}

	}

	if(($_SESSION["livello"]==0)&&(substr($op,0,5)=="admin"))
		$op="display";
	if(($op=="display")
			&&($_SESSION["solo_trasf"]))
		$op="displaytrasf";

	if((substr($op,0,7)=="display")&&($_SESSION["livello"]==2)&&($_SESSION["id_edit"]==$_SESSION["id"]))
		$op="admin_users";

	if(substr($op,0,5)=="admin")
	{
		if(isset($_GET["admin_action"]))
			$admin_action=$_GET["admin_action"];
		else
		{
			if(substr($op,6,5)=="users")
				$admin_action="list_users";
			elseif(substr($op,6,5)=="ditte")
				$admin_action="list_ditte";
			elseif(substr($op,6,5)=="comme")
				$admin_action="list_commesse";
			elseif(substr($op,6,5)=="trasf")
				$admin_action="list_trasf";
			elseif(substr($op,6,5)=="paesi")
				$admin_action="list_paesi";
		}
	}

	if(isset($_POST["performAction"])||
			(substr($op,0,3)=="pa_"))
		require_once("include/performAction.php");

	do_header($is_logged,$expired,$_SESSION["livello"],$op,$ore_lav);
	if(isset($_SESSION["pda"]))
	{?>
		<p class="message" id="message">
			<?=(isset($message)?$message:"")?>
		</p><?
	}

	switch($op)
	{
		case "congela_dati":
			require_once("include/congela_dati.php");
			break;
		case "displaytrasf":
			$trasf=1;
		case "display":
			require_once("include/presenze_display.php");
			break;
			//end if op==display
		case "edit":
			require_once("include/presenze_edit.php");
			break;
			//end if op==edit
		case "admin_users":
			require_once("include/admin_users.php");
			break;
			//end if op==admin_user
		case "admin_ditte":
			require_once("include/admin_ditte.php");
			break;
			//end if op==admin_ditte
		case "admin_commesse":
			require_once("include/admin_commesse.php");
			break;
			//end if op==admin_commesse
		case "admin_trasf":
			require_once("include/admin_trasf.php");
			break;
			//end if op==admin_trasf
		case "admin_paesi":
			require_once("include/admin_paesi.php");
			break;
			//end if op==admin_paesi
		case "admin_stampe":
			require_once("include/admin_stampe.php");
			break;
			//end if op==admin_stampe
		case "foglio_viaggio":
			require_once("include/foglio_viaggio.php");
			break;
		case "permessi_ferie":
			require_once("include/permessi_ferie.php");
			break;
			//fine op=permessi_ferie
		case "malattie":
			require_once("include/malattie.php");
			break;
			//fine op=malattie
		case "anticipi":
			require_once("include/anticipi.php");
			break;
		case "void":
			break;
		default:
			logged_header($op,"MAH");
			close_logged_header($_SESSION["livello"]);
			?>
			Se finisci qui c'&egrave; qualche problema

			</div>
			<?
			break;
	}
}
else
{
	do_header($is_logged,$expired,@$_SESSION["livello"],@$_GET["op"],$ore_lav);
	if(!isset($_SESSION["pda"]))
	{
		?>
		<div id="header">
			<p class="message" id="message">
			<?=(isset($message)?$message:"")?>
			</p>
		</div>
		<?
	}
	else
	{?>
		<p class="message" id="message">
			<?=(isset($message)?$message:"")?>
		</p><?
	}?>

	<div id="content">
		<table border="0" cellspacing="0" cellpadding="0" style="margin-left:auto;margin-right:auto;">
    		<tr>
        		<td style="text-align:center;height:200px;vertical-align:middle">

    <?
	if(!$expired)
	{?>
        <form id="passform" method="post" action="<?=$self?>">
			<table class="login_form">
				<tr>
					<td class="right">Utente:</td>
					<td class="left">
						<input type="text" class="input" size="21" name="loginuser">
					</td>
				</tr>
        		<tr>
        			<td class="right">Password:</td>
        			<td class="left">
        				<input type="password" class="input" size="21" name="loginpass" title="protetta tramite hash casuale quando clicchi 'Entra'">
        			</td>
        		</tr>
	        	<tr>
	        		<td colspan="2" align="center">
	        			<input type="submit" class="button" name="login" value="Entra" title="su alcuni sistemi DEVI cliccare qui, premere invio non funziona" onclick="loginpass.value = hex_md5('<?=$random_string?>' + hex_md5(loginpass.value))">
	        		</td>
	        	</tr>
			</table>
        </form>
		<script type="text/javascript">
        	document.getElementById("passform").loginuser.focus();
        </script>

	<?
	}
	else
	{?>
        <b>modifica la password</b>
        <br>
        <br>
        <form id="passform" method="post" action="<?=$self?>">
			<input type="hidden" name="id" value="<?=$_SESSION["id"]?>">
			<table class="login_form">
				<tr>
					<td class="right">password:</td>
					<td>
						<input type="password" class="input" size="21" name="newpass">
					</td>
				</tr>
        		<tr>
        			<td class="right">ripeti:</td>
        			<td>
        				<input type="password" class="input" size="21" name="newpass2">
        			</td>
        		</tr>
				<tr>
					<td colspan="2" align="center"><input type="button" class="button" name="chpwd" value="accetta" onclick="
						if(newpass.value==newpass2.value)
						{
							newpass.value = hex_md5(newpass.value);
							newpass2.value=hex_md5(newpass2.value);
							submit();
						}
						else
							newpass.focus();
					">
					</td>
				</tr>
		</table>
        </form>
        <script type="text/javascript">
        	document.getElementById("passform").newpass.focus();
        </script>
	<?
	}
	?>
		</td>
    </tr>
</table>
</div>
<?
}
?>
	</body>
</html>
<?



function display_admin_nav($livello)
{
	global $self,$op;
	$ops=array("s_display"=>"presenze",
			"s_congela_dati"=>"congela dati",
			"r_admin_users"=>"utenti",
			"s_admin_ditte"=>"ditte",
			"s_admin_trasf"=>"trasferte",
			"s_admin_paesi"=>"paesi",
			"s_admin_commesse"=>"commesse",
			"r_admin_stampe"=>"stampe");

	?>
	<div id="admin_nav">
		<table class="admin_nav">
		<tr style="height:20px">
	<?
	foreach($ops as $kp=>$v)
	{
		if(($livello==1)
			||(substr($kp,0,2)=="r_"))
		{
			$k=substr($kp,2);
			$bg=($op==$k?"#ccc":"#eee")
	?>
			<td style="white-space:nowrap;background-color:<?=$bg?>;width:70px;text-align:center;
					padding:0px 5px;vertical-align:middle;border-right:1px solid #222;"
					onmouseover="style.cursor='pointer';style.backgroundColor='#ddd';"
					onmouseout="style.backgroundColor='<?=$bg?>'"
					onclick="redirect('<?=$self?>&amp;op=<?=$k?>');">
				<?=$v?>
			</td>
		<?}
	}?>

		</tr>
		</table>
		</div>
	<?
}

/*
    function do_header()
    */

function logged_header($op,$page_title,$subtitle="")
{
	global $self,$mesi;

	if(!isset($_SESSION["pda"]))
	{
		global $myhost,$dbname,$myuser,$mypass,$indbase_val,$indsparo_val,$indnav_val;
?>
		<div id="header">
			<form method="post" action="<?=$self?>" style="margin: 0px;">
				<table class="tab_header">
					<tr style="height:28px">
						<td style="vertical-align:middle;text-align:left; margin:0px; font-size: 100%; font-weight:normal; padding:0px; width:33%; white-space:nowrap;">
<?
		if(substr($op,0,7)=="display")
		{
			$explode=explode("|",$op);
			$mese=$explode[1];
			$anno=$explode[2];
			$trasf=strstr($op,"trasf");

			?>
			<input type="hidden" name="mese" value="<?=$mese?>">
			<input type="hidden" name="anno" value="<?=$anno?>">
			<?
			if(substr($op,0,12)=="displaytrasf")
			{
				if(($_SESSION['livello']==1)||($_SESSION['show_tr']))
				{
					$conn=mysqli_connect($myhost, $myuser, $mypass)
						or die("Connessione non riuscita ".mysqli_error($conn));
					((bool)mysqli_query($conn, "USE " . $dbname));
					$query="SELECT count(*) as conta
								FROM presenze
							WHERE id='".$_SESSION["id_edit"]."'
								AND giorno BETWEEN '".date("Y-m-d",mktime(0,0,0,$mese,1,$anno))."'
										AND '".date("Y-m-d",mktime(0,0,0,$mese+1,0,$anno))."'
								AND	CHAR_LENGTH(tr_destinazione)>0
								AND CHAR_LENGTH(tr_motivo)>0";
					$result=@mysqli_query($conn, $query)
						or die("$query<br>".mysqli_error($conn));
					$conta=mysqli_fetch_assoc($result);
					((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
					((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
					if($conta["conta"]>0)
						$print=" <a href='$self
										&amp;op=stampa_indennita
										&amp;ind_mese=$mese
										&amp;ind_anno=$anno' target='_blank'>
										stampa</a>";
				}
			}
			else
				$print=" <a href='$self
									&amp;op=stampa_mese
									&amp;mese=$mese
									&amp;anno=$anno'
									target='_blank'>stampa</a>";

		}
		?>
		<select name="op" class="input" onChange="submit();">
		<?
		if(($_SESSION['livello']==1)||($_SESSION['solo_trasf']!=1))
		{?>

			<option value="display"<?=(((substr($op,0,7)=="display")
							&&(substr($op,0,12)!="displaytrasf"))?" selected":"")?>>presenze</option>
		<?}
		if(($_SESSION['livello']==1)||($_SESSION['show_tr']))
		{?>
			<option value="displaytrasf"<?=((substr($op,0,12)=="displaytrasf")?" selected":"")?>>
				trasferte
			</option><?
		}
		if($_SESSION["livello"]!=2)
		{?>
			<option value="foglio_viaggio"<?=(($op=="foglio_viaggio")?" selected":"")?>>
				foglio viaggio
			</option>
			<option value="permessi_ferie"<?=(($op=="permessi_ferie")?" selected":"")?>>
				permessi/ferie
			</option>
			<option value="malattie"<?=(($op=="malattie")?" selected":"")?>>
				comunicazione malattia
			</option>
			<option value="anticipi"<?=(($op=="anticipi")?" selected":"")?>>
				richiesta anticipo
			</option>
		<?}?>
		</select>
		<?=(isset($print)?$print:"")?>
		</td>
		<td style="text-align:center; width:33%; vertical-align:middle;margin:0px; padding:0px;white-space:nowrap;">
			<p class="titolo">
				<?=$page_title?>
			</p>
		</td>
		<td style="width:33%;height:28px;white-space:nowrap;text-align:right; margin:0px; padding:0px; vertical-align: top;">
			<input type="submit" class="button" value="Esci" name="logout">
		</td>
		</tr>
		<tr style="height:28px">
			<td style="font-weight:normal;width:33%;vertical-align:middle;text-align:left;white-space:nowrap;">
<?
	if(substr($op,0,7)=="display")
	{
		if(mktime(0,0,0,(int)$mese,1,(int)$anno)>strtotime($_SESSION["data_inizio_coll"]))
		{
			$link="$self&amp;op=".($trasf?"displaytrasf":"display");
			$link.="&amp;mese=".date("n",mktime(0,0,0,$mese-1,1,$anno));
			$link.="&amp;anno=".date("Y",mktime(0,0,0,$mese-1,1,$anno));
			?>
				<a href="<?=$link?>"><--<?=$mesi[($mese+10)%12]?></a>
			<?
		}
	}?>
			</td>
			<td style="font-size:130%;width:33%;vertical-align:middle;text-align:center;white-space:nowrap;">
<?
	if(substr($op,0,7)=="display")
		$subtitle=(($trasf?"trasferte ":"presenze ").$mesi[$mese-1]." ".$anno);
?>
				<?=$subtitle?>
			</td>
			<td style="font-weight:normal;width:33%;vertical-align:middle;text-align:right;white-space:nowrap;">
<?
	if(substr($op,0,7)=="display")
	{
		if(($anno*12+$mese+1<=date("Y")*12+date("n"))
			&&(($_SESSION["data_fine_coll"]=="0000-00-00")
				||(mktime(0,0,0,$mese+1,1,$anno)<=strtotime($_SESSION["data_fine_coll"]))))
		{
			$link="$self&amp;op=".($trasf?"displaytrasf":"display");
			$link.="&amp;mese=".date("n",mktime(0,0,0,$mese+1,1,$anno));
			$link.="&amp;anno=".date("Y",mktime(0,0,0,$mese+1,1,$anno));
			?>
					<a href="<?=$link?>">
						<?=($mesi[$mese%12]."-->")?>
					</a>
			<?
		}
	}
?>
			</td>
					</tr>
				</table>
			</form>
		</div>
<?	}
	else
	{?>
		<input type="button" class="button" value="Esci" name="logout" onclick="submit();">
	<?
	}
}

function close_logged_header($livello)
{
	if(!isset($_SESSION["pda"]))
	{?>
		<?
		if($livello>0)
			display_admin_nav($livello);
		?>
		<p class="message" id="message">
			<?=(isset($message)?$message:"")?>
		</p>

		<div id="content">
		<?
	}
	else
	{?>
		<div>
	<?}

}

function do_header($is_logged,$expired,$level,$op,$ore_lav)
{
	global $version,$message;
	if(strlen($message))
		$onload=" onload='showMessage(\"$message\")'";
	else
		$onload="";
	$bodystyle=($level>0?"admin":"user");
	if(isset($_SESSION["pda"]))
		$bodystyle="none";
	$ie=strstr($_SERVER["HTTP_USER_AGENT"],"MSIE");
	if($ie)
		echo '﻿<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">';
	else
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';

?>
<html>
<head>
<link rel="icon" href="favicon.png">
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<title>hightecservice.biz</title>
<meta name="description" content="envysoft secure authentication">
<meta name="keywords" content="php,javascript,authentication,md5,hashing,php,javascript,authenticating,auth,AUTH,secure,secure login,security,php and javascript secure authentication,combat session fixation!">
<script type="text/javascript" src="md5.js"></script>
<SCRIPT TYPE="text/javascript" SRC="include/cal.js"></SCRIPT>
<script type="text/javascript" src="include/datetime.js"></script>
<script type="text/javascript" src="include/util.js"></script>
<script type="text/javascript" src="js/minified.js"></script>
<script type="text/javascript">
	var maintimer = setTimeout('document.getElementById("message").style.visibility = "hidden";', 5000);

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

	function showMessage(message)
	{
		document.getElementById("message").innerHTML=message;
		document.getElementById("message").style.visibility='visible';
		clearTimeout(maintimer);
		var timer = setTimeout('document.getElementById("message").style.visibility = "hidden";', 5000);
	}

</script>
<link rel="stylesheet" href="index.css" title="envysheet" type="text/css">
</head>
<body class="<?=$bodystyle?>"<?=$onload?>>
<?
}


function tabella_presenze($conn,$id_utente,$da_data,$a_data,$data_inizio_coll,$data_fine_coll,$trasf)
{
	global $ore_lav;

	aggiusta_date($da_data,$a_data,$data_inizio_coll,$data_fine_coll);
	$trasferte=array();
	$trasferte[-1]=array("codice"=>"----","codice2"=>"----","descrizione"=>"----","importo"=>0);
	$query="SELECT * FROM trasferte ORDER BY descrizione";
	$result=@mysqli_query($conn, $query) or die("$query<br>".mysqli_error($conn));
	while($row=mysqli_fetch_assoc($result))
	{
		$trasferte[$row["id"]]["codice"]=$row["codice"];
		$trasferte[$row["id"]]["codice2"]=$row["codice2"];
		$trasferte[$row["id"]]["descrizione"]=$row["descrizione"];
		$trasferte[$row["id"]]["importo"]=$row["importo"];
	}
	((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);

	$query="SELECT commesse.* FROM commesse";
	$commesse=array();
	$commesse[-1]="----";
	$result=@mysqli_query($conn, $query)
		or die("$query<br>".mysqli_error($conn));
	while($row=mysqli_fetch_assoc($result))
		$commesse[$row["id"]]=$row["commessa"];
	((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);


	$query="SELECT * FROM presenze
		WHERE id='$id_utente'
			AND giorno BETWEEN '$da_data' AND '$a_data'
		ORDER BY giorno";
	$result=@mysqli_query($conn, $query) or die("$query<br>".mysqli_error($conn));

	$tabella=array();

	if(mysqli_num_rows($result))
	{
		$str_on=0;
		$per_on=0;
		$sst_on=0;
		$via_on=0;
		$tra_on=0;
		$fer_on=0;
		$mal_on=0;
		$com_on=0;
		$trasf_on=0;
		$note_on=0;

		while($row=mysqli_fetch_assoc($result))
		{
			$str_on+=($row["ingresso2"]!=-1);
			$ore_lav_eff=((format_minute($row["uscita2"])-format_minute($row["ingresso2"])+1440)%1440)+((format_minute($row["uscita"])-format_minute($row["ingresso"])+1440)%1440)-format_minute($row["pausa"]);
			$per_on+=(((!$row["festivo"])&&($ore_lav_eff>0)&&($ore_lav_eff<$ore_lav))||($row["forza_ore_perm"]!=-1));
			$sst_on+=(($row["ore_str"]!=-1)||($row["forza_ore_str"]!=-1));
			$via_on+=(($row["ore_via"]!=-1)||($row["forza_ore_via"]!=-1));
			$tra_on+=(($_SESSION["livello"]!=2)&&($row["trasferta"]!=-1));
			$fer_on+=($row["ferie"]!=0);
			$mal_on+=($row["malattia"]!=0);
			$com_on+=(($_SESSION["livello"]!=2)&&($row["id_commessa"]>0));
			$note_on+=(($_SESSION["livello"]!=2)&&(strlen($row["note"])!=0));

			if($trasf&&($_SESSION["livello"]==1))
				$trasf_on+=(strlen($row["tr_destinazione"])&&strlen($row["tr_motivo"]));
		}

		mysqli_data_seek($result,  0);
		while($row=mysqli_fetch_assoc($result))
		{
			if(!isset($tabella[$row["giorno"]]))
				$tabella[$row["giorno"]]=array();
			$tabella[$row["giorno"]]["__hidden__festivo"]=$row["festivo"];
			if(!$trasf)
			{
				$tabella[$row["giorno"]]["Ingresso<br>Mattino"]=int_to_hour($row["ingresso"]);
				$tabella[$row["giorno"]]["Pausa<br>Pranzo"]=int_to_hour($row["pausa"]);
				$tabella[$row["giorno"]]["Uscita<br>Sera"]=int_to_hour($row["uscita"]);
				if($str_on)
				{
					$tabella[$row["giorno"]]["Extra<br>Ingresso"]=int_to_hour($row["ingresso2"]);
					$tabella[$row["giorno"]]["Extra<br>Uscita"]=int_to_hour($row["uscita2"]);
				}
				$ore_lav_eff=((format_minute($row["uscita2"])-format_minute($row["ingresso2"])+1440)%1440)+((format_minute($row["uscita"])-format_minute($row["ingresso"])+1440)%1440)-format_minute($row["pausa"]);
				if($ore_lav_eff==0)
					$ore_lav_eff=-1;

				if($row["forza_ore_giorn"]!=-1)
					$tabella[$row["giorno"]]["Ore<br>Giorn"]=int_to_hour($row["forza_ore_giorn"]);
				else
					$tabella[$row["giorno"]]["Ore<br>Giorn"]=($row["festivo"]?"----":int_to_hour($ore_lav_eff>$ore_lav?$ore_lav:$ore_lav_eff));

				if($per_on)
				{
					if($row["forza_ore_perm"]!=-1)
						$tabella[$row["giorno"]]["Ore<br>Perm"]=int_to_hour($row["forza_ore_perm"]);
					else
						$tabella[$row["giorno"]]["Ore<br>Perm"]=($row["festivo"]?"----":int_to_hour((($ore_lav_eff<>-1)&&($ore_lav_eff<$ore_lav))?$ore_lav-$ore_lav_eff:-1));
				}
				if($sst_on)
				{
					if($row["forza_ore_str"]!=-1)
						$tabella[$row["giorno"]]["Ore<br>Str"]=int_to_hour($row["forza_ore_str"]);
					else
						$tabella[$row["giorno"]]["Ore<br>Str"]=int_to_hour($row["ore_str"]);
				}
				if($via_on)
				{
					if($row["forza_ore_via"]!=-1)
						$tabella[$row["giorno"]]["Ore<br>Viaggio"]=int_to_hour($row["forza_ore_via"]);

					$tabella[$row["giorno"]]["Ore<br>Viaggio"]=int_to_hour($row["ore_via"]);
				}
				if($tra_on)
				{
					$tabella[$row["giorno"]]["Trasferta"]=$trasferte[$row["trasferta"]]["codice"];
					$tabella[$row["giorno"]]["__hidden__trasferta"]=$trasferte[$row["trasferta"]]["descrizione"];
					$tabella[$row["giorno"]]["__hidden__tr_euri"]=$trasferte[$row["trasferta"]]["importo"];
					$tabella[$row["giorno"]]["__hidden__tr_cod2"]=$trasferte[$row["trasferta"]]["codice2"];
				}
				if($fer_on)
					$tabella[$row["giorno"]]["Ferie"]=($row["ferie"]?"<img src='img/check.png' alt='check'>":"");
				if($mal_on)
					$tabella[$row["giorno"]]["Malattia"]=($row["malattia"]?"<img src='img/check.png' alt='check'>":"");
				if($com_on)
					$tabella[$row["giorno"]]["__hidden__Commessa"]=@$commesse[$row["id_commessa"]];
				if($note_on)
					$tabella[$row["giorno"]]["Note"]=$row["note"];

			}
			else
			{
				if(strlen($row["tr_destinazione"])&&strlen($row["tr_motivo"]))
				{
					$tabella[$row["giorno"]]["Destinazione"]=$row["tr_destinazione"];
					$tabella[$row["giorno"]]["Motivo"]=$row["tr_motivo"];
					$tabella[$row["giorno"]]["Ind.<br>Forf"]=$row["tr_ind_forf"];
					$tabella[$row["giorno"]]["Spese<br>viaggio"]=$row["tr_spese_viaggio"];
					$tabella[$row["giorno"]]["Spese<br>alloggio"]=$row["tr_spese_alloggio"];
					$tabella[$row["giorno"]]["Spese<br>di vitto"]=$row["tr_spese_vitto"];
					$tabella[$row["giorno"]]["KM<br>percorsi"]=$row["tr_km"];
					$tabella[$row["giorno"]]["Rimborso<br>al KM"]=$row["tr_euri_km"];
				}
				else
				{
					$tabella[$row["giorno"]]["Destinazione"]="----";
					$tabella[$row["giorno"]]["Motivo"]="----";
					$tabella[$row["giorno"]]["Ind.<br>Forf"]="----";
					$tabella[$row["giorno"]]["Spese<br>viaggio"]="----";
					$tabella[$row["giorno"]]["Spese<br>alloggio"]="----";
					$tabella[$row["giorno"]]["Spese<br>di vitto"]="----";
					$tabella[$row["giorno"]]["KM<br>percorsi"]="----";
					$tabella[$row["giorno"]]["Rimborso<br>al KM"]="----";
				}
			}

		}
		((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
	}
	return $tabella;
}

function aggiusta_date(&$inizio,&$fine,$inizio_coll,$fine_coll)
{
	if(($inizio_coll!="0000-00-00")&&(strtotime($inizio)<strtotime($inizio_coll)))
		$inizio=$inizio_coll;
	if(($fine_coll!="0000-00-00")&&(strtotime($fine))>strtotime($fine_coll))
		$fine=$fine_coll;
}




function formattaimporto($importo,$punti)
{
	if(!strlen($importo))
		$importo=0;
	$formatted=sprintf("%.2f",($importo/100));
	$formatted=str_replace(".",",",$formatted);
	if($punti)
	{
		$i=strlen($formatted)-6;
		for($i;$i>($importo>0?0:1);$i-=3)
			$formatted=substr($formatted,0,$i).".".substr($formatted,$i);
	}
	return $formatted;
}




function conta_giorni_lav($da,$a)
{
	global $conn,$dbname;

	$ferie=array();
	$iter=strtotime($da);

	$contaa=0;
	while($iter<=strtotime($a))
	{
		if((date("w",$iter)+1)%7 >1)
			$ferie[date("Y-m-d",$iter)]=1;
		$iter=mktime(0,0,0,date("n",$iter),date("d",$iter)+1,date("Y",$iter));
	}
	((bool)mysqli_query($conn, "USE " . $dbname));
	$query1="SELECT * FROM pasqua";
	$result1=@mysqli_query($conn, $query1) or die($query1."<br>".mysqli_error($conn));
	while($row1=mysqli_fetch_assoc($result1))
		if(isset($ferie[$row1["pasqua"]]))
			unset($ferie[$row1["pasqua"]]);
	((mysqli_free_result($result1) || (is_object($result1) && (get_class($result1) == "mysqli_result"))) ? true : false);

	$query1="SELECT festa FROM feste";
	$result1=@mysqli_query($conn, $query1) or die($query1."<br>".mysqli_error($conn));
	while($row1=mysqli_fetch_assoc($result1))
	{
		foreach($ferie as $key=>$value)
			if(substr($key,5)==$row1["festa"])
				unset($ferie[$key]);
	}
	((mysqli_free_result($result1) || (is_object($result1) && (get_class($result1) == "mysqli_result"))) ? true : false);

	foreach($ferie as $key=>$value)
		if((substr($key,5,2)==substr($_SESSION["patrono"],2)) &&
			(substr($key,8,2)==substr($_SESSION["patrono"],0,2)))
				unset($ferie[$key]);
	return count($ferie);
}


function permessi_ferie_sw($id_pf,$id_utente,$stato,$da,$a,$comm)
{
	global $conn;

	$query="UPDATE permessi_ferie SET stato='".$stato."' WHERE id='".$id_pf."'";
	$result=@mysqli_query($conn, $query)
		or die("$query<br>".mysqli_error($conn));

//	if(strtotime($da)>time())
	{
		if((strlen($da)==10)&&(strlen($a)==10))
		{
			if($stato==1)
				$query="UPDATE presenze SET
						ferie=1,
						ingresso=-1,
						uscita=-1,
						ingresso2=-1,
						uscita2=-1,
						forza_ore_giorn=-1,
						forza_ore_perm=-1,
						forza_ore_str=-1,
						forza_ore_via=-1,
						pausa=-1,
						id_commessa=-1
					WHERE id='".$id_utente."'
						AND (giorno BETWEEN '".$da."'
								AND '".$a."')
						AND festivo=0";
			else
				$query="UPDATE presenze SET
						ferie=0,
						ingresso=495,
						uscita=1020,
						ingresso2=-1,
						uscita2=-1,
						forza_ore_giorn=-1,
						forza_ore_perm=-1,
						forza_ore_str=-1,
						forza_ore_via=-1,
						pausa=45,
						id_commessa='".$comm."'
					WHERE id='".$id_utente."'
						AND (giorno BETWEEN '".$da."' AND '".$a."')
						AND festivo=0";
			@mysqli_query($conn, $query)
				or die("$query<br>".mysqli_error($conn));
		}
		else
		{
			$query="SELECT note FROM presenze
					WHERE giorno='".substr($da,0,10)."'
						AND id='".$id_utente."'";
			$result=@mysqli_query($conn, $query)
				or die("$query<br>".mysqli_error($conn));
			if($row=mysqli_fetch_assoc($result))
			{
				$stringa=$row["note"];
				if(!(($i=strpos($row["note"],"Permesso dalle "))===false))
				{
					if(!(($l=strstr(substr($stringa,$i+26)," "))===false))
						$stringa=substr($stringa,0,($i>0?$i-1:$i)).$l;
					else
						$stringa=substr($stringa,0,($i>0?$i-1:$i));
				}
				if($stato==1)
				{
					if(strlen($stringa))
						$stringa.=" ";
					$stringa.="Permesso dalle ".substr($da,11,5)." alle ".substr($a,11,5);
				}
				$query="UPDATE presenze
							SET note='$stringa'
						WHERE id='".$id_utente."'
							AND giorno='".substr($da,0,10)."'";
				@mysqli_query($conn, $query)
					or die("$query<br>".mysqli_error($conn));
			}
			((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
		}
	}
}



function malattie_sw($id_malattie,$id_utente,$stato,$da,$a,$comm,$codice)
{
	global $conn;

	$query="UPDATE malattie SET stato='".$stato."' WHERE id='".$id_malattie."'";
	$result=@mysqli_query($conn, $query)
		or die("$query<br>".mysqli_error($conn));

	if($stato==1)
		$query="UPDATE presenze SET
						ferie=0,
						malattia=1,
						ingresso=-1,
						uscita=-1,
						ingresso2=-1,
						uscita2=-1,
						forza_ore_giorn=-1,
						forza_ore_perm=-1,
						forza_ore_str=-1,
						forza_ore_via=-1,
						pausa=-1,
						id_commessa=-1,
						note='codice: ".$codice."'
					WHERE id='".$id_utente."'
						AND (giorno BETWEEN '".$da."'
								AND '".$a."')
						AND festivo=0";
	else
		$query="UPDATE presenze SET
						ferie=0,
						malattia=0,
						ingresso=495,
						uscita=1020,
						ingresso2=-1,
						uscita2=-1,
						forza_ore_giorn=-1,
						forza_ore_perm=-1,
						forza_ore_str=-1,
						forza_ore_via=-1,
						pausa=45,
						id_commessa='".$comm."',
						note=''
					WHERE id='".$id_utente."'
						AND (giorno BETWEEN '".$da."' AND '".$a."')
						AND festivo=0";
	@mysqli_query($conn, $query)
		or die("$query<br>".mysqli_error($conn));
}

?>
