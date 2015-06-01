<?
switch(@$_POST["ajax"])
{
	case "update":
		$annoSelect=$_POST["anno"];
		$userSelect=$_POST["user"];
		displayAnticipi($userSelect,$annoSelect);
		die();
		break;
	case "editAnticipoForm":
		$anticipi_to_edit=$_POST["anticipi_to_edit"];
		$askForm=$_POST["askForm"];
		if($askForm)
		{
			ob_start();
			loadAnticipiForm();
			$form=ob_get_clean();
		}
		else $form="";
		require_once("mysql.php");
		require_once("datetime.php");
		$conn=mysqli_connect($myhost, $myuser, $mypass)
			or die("Connessione non riuscita ".mysqli_error($conn));
		((bool)mysqli_query($conn, "USE " . $dbname));
		$query="SELECT anticipi.* FROM anticipi
			WHERE anticipi.id='$anticipi_to_edit'";
		$result=@mysqli_query($conn, $query)
			or die($query."<br>".(mysqli_error($conn)));
		$row=mysqli_fetch_assoc($result);
		if(!$row)
			$valori["data_valuta"]=date("d/m/Y");
		else
			$valori["data_valuta"]=my_date_format(substr($row["data_valuta"],0,10),"d/m/Y");
		$valori["euri"]=$row["euri"];
		$valori["note"]=$row["note"];
		$valori["id_anticipi"]=$row["id"];
		$valori["id_utente"]=$row["id_utente"];
		((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
		((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
		$out=array("valori"=>$valori,"form"=>$form);
		echo json_encode($out);
		die();
		break;
 	case "submitForm":
		require_once("mysql.php");
		require_once("datetime.php");
		require_once("session.php");
		require_once("const.php");
		$id_anticipi=$_POST["id_anticipi"];
		$id_utente=$_POST["id_utente"];
		$euri=str_replace(",",".",$_POST["euri"]);
		$note=str_replace("'","\'",$_POST["note"]);
		$data_valuta=date_to_sql($_POST["data_valuta"]);
		$conn=mysqli_connect($myhost, $myuser, $mypass);
		((bool)mysqli_query($conn, "USE " . $dbname)) or die(mysqli_error($conn));

		if($id_anticipi==0)
		{
			$id_utente_to_edit=$_SESSION["id_edit"];
			$data_richiesta=date("Y-m-d H:i:s");
			$query="INSERT INTO anticipi(id_utente,data_richiesta,data_valuta,euri,note,stato)
				VALUES('$id_utente_to_edit',
						'$data_richiesta',
						'$data_valuta',
						'$euri',
						'$note',
						0)";
			$subject = "Richiesta anticipo ".$_SESSION["cognome"]." ".$_SESSION["nome"];
		}
		else
		{
			$query="UPDATE anticipi SET ";
			if($id_utente==$_SESSION["id_edit"])
				$query.="data_richiesta='".date("Y-m-d H:i:s")."',";
			$query.="
					data_valuta='$data_valuta',
					euri='$euri',
					note='$note'
				WHERE id='$id_anticipi'";
			$subject = "Modifica richiesta anticipo ".$_SESSION["cognome"]." ".$_SESSION["nome"];
		}
		@mysqli_query($conn, $query)
			or die($query."<br>".mysqli_error($conn));
		((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
		if($id_utente==$_SESSION["id_edit"])
		{
			require_once "Mail.php";
			$from = "HighTecService <zampieri@hightecservice.biz>";
	//		$to = "zampieri@hightecservice.biz,3478624224@sms.vodafone.it";
			$to = "zampieri@hightecservice.biz";
			$body = "Data valuta $data_valuta\nEuro $euri";
			$host = "localhost";
			$headers = array ('From' => $from,  'To' => $to, 'Subject' => $subject);
			$smtp = Mail::factory('smtp',  array ('host' => $host, 'auth' => false));
			$mail = $smtp->send($to, $headers, $body);
		}
		die();
		break;
	case "anticipi_sw":
		require_once("mysql.php");
		require_once("session.php");

		if(($_SESSION["livello"]==1)&&isset($_POST["id"]))
		{
			$id=$_POST["id"];
			$conn=mysqli_connect($myhost, $myuser, $mypass)
				or die("Connessione non riuscita ".mysqli_error($conn));
			((bool)mysqli_query($conn, "USE " . $dbname));
			$query="UPDATE anticipi SET stato=((stato+1)%3)
					WHERE id='$id'";
			$result=@mysqli_query($conn, $query)
				or die($query."<br>".mysqli_error($conn));
			((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
		}
		die();
		break;
	case "anticipi_del":
		require_once("mysql.php");
		require_once("session.php");
		if(isset($_POST["id"])&&($_SESSION["livello"]==1))
		{
			$id=$_POST["id"];
			$conn=mysqli_connect($myhost, $myuser, $mypass);
			((bool)mysqli_query($conn, "USE " . $dbname))
				or die(mysqli_error($conn));

			$query="DELETE FROM anticipi
					WHERE id='$id'";
			@mysqli_query($conn, $query)
				or die($query."<br>".mysqli_error($conn));
			((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
		}
		die();
		break;
	default:
		logged_header("anticipi",$_SESSION["nome"]." ".
				$_SESSION["cognome"]." - ".$_SESSION["ditta_rs"]
				,"");
		close_logged_header($_SESSION["livello"]);
	?>
		<input type="hidden" name="hiddenUser" id="hiddenUser" value="0">
		<input type="hidden" name="hiddenAnno" id="hiddenAnno" value="<?=date("Y")?>">
		<input type="hidden" name="hiddenLivello" id="hiddenLivello" value="<?=$_SESSION["livello"]?>">
		<div id="listDiv"></div>
		<div id="editDiv"></div>
		<div id="waitDiv"><img class="wait" src="img/wait.gif" alt="...please wait..."></div>
	<?
		require_once("js/anticipi.js");
		die();
		break;
}



function displayAnticipi($user_id,$anno)
{
	global $dbname,$myuser,$mypass,$myhost;

	require_once("mysql.php");
	require_once("session.php");
	require_once("datetime.php");

	$user_id=(int)$user_id;
	$anno=(int)$anno;
	$utenti=array();
	$anni=array();


	$conn=mysqli_connect($myhost, $myuser, $mypass)
		or die("Connessione non riuscita ".mysqli_error($conn));
	((bool)mysqli_query($conn, "USE " . $dbname));

	if($_SESSION["livello"]==1)
	{
		$query="SELECT utenti.id AS user_id,utenti.cognome,utenti.nome,
				YEAR(anticipi.data_valuta) AS anno
			FROM anticipi JOIN utenti
			ON anticipi.id_utente=utenti.id
		WHERE utenti.attivo=1 AND utenti.expired=0
			ORDER BY utenti.cognome, anticipi.data_richiesta DESC";
		$result=@mysqli_query($conn, $query)
			or die($query."<br>".mysqli_error($conn));

		while($row=mysqli_fetch_assoc($result))
		{
			$utenti[$row["user_id"]]=$row["cognome"]." ".$row["nome"];
			if(($user_id==0)||($row["user_id"]==$user_id))
				$anni[$row["anno"]]=1;
		}
	}
	else
	{
		$query="SELECT YEAR(anticipi.data_valuta) AS anno
			FROM anticipi
		WHERE id_utente=".$_SESSION['id_edit']."
			ORDER BY data_richiesta DESC";
		$result=@mysqli_query($conn, $query)
			or die($query."<br>".mysqli_error($conn));

		while($row=mysqli_fetch_assoc($result))
			$anni[$row["anno"]]=1;
	}
	((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
	if(($anno!=0)&&(!isset($anni[$anno])))
		$anno=(count($anni)?key($anni):0);

	if($user_id>0)
		$userCondition="id_utente=$user_id";
	else
		$userCondition="id_utente>$user_id";
	if($anno>0)
		$yearCondition="YEAR(anticipi.data_valuta)=$anno";
	else
		$yearCondition="YEAR(anticipi.data_valuta)>$anno";


	$color=array(0=>"yellow",1=>"green",2=>"red");
	if($_SESSION["livello"]!=1)
		$query="SELECT * FROM anticipi
				WHERE id_utente=".$_SESSION['id_edit']." AND $yearCondition
				ORDER BY data_richiesta DESC";
	else
		$query="SELECT anticipi.*,utenti.cognome,utenti.nome
				FROM anticipi JOIN utenti
				ON anticipi.id_utente=utenti.id
			WHERE utenti.attivo=1 AND utenti.expired=0 AND $userCondition AND $yearCondition
				ORDER BY utenti.cognome, anticipi.data_valuta DESC";

	$result=@mysqli_query($conn, $query)
		or die($query."<br>".mysqli_error($conn));
	$rows=array();
	while($row=mysqli_fetch_assoc($result))
		$rows[]=$row;

	((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
	((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
	?>
	<div style="width:100%;font-size:18px;font-weight:bold;text-align:center">
		N.B. richiesta valida SOLO per rimborso spese e trasferte
<?
	if(count($utenti))
	{?>
		<select name="user_id" id="userSelect" onchange="userSelectChange()">
			<option value="0">tutti gli utenti</option>
		<?
			foreach($utenti as $id=>$u)
			{
				$selected=($id==$user_id?" selected='selected'":"");
			?>
				<option value="<?=$id?>"<?=$selected?>><?=$u?></option>
			<?}?>
		</select>
	<?}
	else
	{?>
		<input type="hidden" name="user_id" id="userSelect" value="<?=$_SESSION['id_edit']?>">
	<?}?>
		<select name="anno" id="annoSelect" onchange="annoSelectChange()">
			<option value="0">tutti gli anni</option>
		<?
			foreach($anni as $id=>$foo)
			{
				$selected=($id==$anno?" selected='selected'":"");
			?>
				<option value="<?=$id?>"<?=$selected?>><?=$id?></option>
			<?}?>
		</select>

	</div>
	<table class="edit">
		<tr class="table-header">
			<td>&nbsp;</td>
	<?
	if($_SESSION["livello"]==1)
	{?>
			<td>cognome</td>
			<td>nome</td>
	<?}?>
			<td>data richiesta</td>
			<td>data valuta</td>
			<td>importo</td>
			<td>note</td>
		</tr>
	<?
	foreach($rows as $row)
	{
		$data_richiesta=my_date_format(substr($row["data_richiesta"],0,10),"d/m/Y");
		$data_valuta=my_date_format(substr($row["data_valuta"],0,10),"d/m/Y");
		$euri=$row["euri"];
		?>
		<tr class="row_attivo">
			<td class="right">
		<?
		if(($_SESSION["livello"]==1)||(($row["stato"]==0)&&(datetime_diff(date("Y-m-d"),$row["data_valuta"])>=-864000)
			&&((int)date("Ym")<=(int)date("Ym",strtotime($row["data_valuta"])))))
		{?>
			<img src="img/b_edit.png" alt="Edit" class="link"
				title="Edit" onclick="editAnticipoForm('<?=$row["id"]?>')">
		<?}

		$onclick="";
		if($_SESSION["livello"]==1)
		{
			$cognome=str_replace("'","\'",$row["cognome"]);
		?>
				<img class="link" src="img/b_drop.png"
						alt="Elimina" title="Elimina"
						onclick="anticipi_del('<?=$row["id"]?>','<?=$cognome?>')">
		<?
			$onclick="switchStato('".$row["id"]."')";
		}?>
					<img class="link" src="img/<?=$color[$row["stato"]]?>.png"
						alt="<?=$color[$row["stato"]]?>"
						title="<?=$color[$row["stato"]]?>"
						onclick="<?=$onclick?>" >
			</td>
		<?
		if($_SESSION["livello"]==1)
		{?>
			<td><?=$row["cognome"]?></td>
			<td><?=$row["nome"]?></td>
		<?}?>
			<td><?=$data_richiesta?></td>
			<td><?=$data_valuta?></td>
			<td><?=$euri?></td>
			<td><?=$row["note"]?></td>
		</tr>
	<?}?>
		<tr class="row_attivo">
			<td colspan="<?=($_SESSION["livello"]==1?7:5)?>" class="link" onclick="editAnticipoForm(0)" >
				<img src="img/b_edit.png" alt="Nuovo"
					title="Nuovo">
					&nbsp;Nuova richiesta
			</td>
		</tr>
	</table>
<?
}


function loadAnticipiForm()
{
	require_once("session.php");

?>
<div id="centra">
	<form method="post" id="edit_form">
		<input type="hidden" name="id_anticipi"
			value="0">
		<input type="hidden" name="id_utente"
			value="0">
		<table class="edit">
			<tr>
				<td class="right">data valuta (gg/mm/aaaa)</td>
				<td>
					<input type="text" name="data_valuta" size="10"
						value=""
						onchange="this.value=formattadata(this.value)">
				</td>
			</tr>
			<tr>
				<td class="right">importo euro</td>
				<td>
					<input type="text" name="euri" size="10"
						value=""
						onchange="this.value=formattavaluta(this)">
				</td>
			</tr>
			<tr>
				<td class="right">note</td>
				<td>
					<textarea name="note" cols="40" rows="10" <?=($_SESSION["livello"]!=1?"readonly":"")?>></textarea>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="footer">
					<input type="button" class="button"
						name="submit" onclick="submitClick()"
						value="accetta">
					&nbsp;
					<input type="button" class="button"
						onclick="annullaClick()"
						value="annulla">
				</td>
			</tr>
		</table>
	</form>
</div>
<?
}
?>
