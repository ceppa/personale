<?
if(isset($_GET["malattie_action"]))
{
	$malattie_action=$_GET["malattie_action"];
	$subtitle=($malattie_action=="add_malattie"?"nuova comunicazione":
		"modifica richiesta");
	if(($malattie_action=="edit_malattie")
			&&(((int)$_GET["malattie_to_edit"])==0))
	{
		$malattie_action="list_malattie";
		$subtitle="gestione comunicazioni malattia";
	}
}
else
{
	$malattie_action="list_malattie";
	$subtitle="gestione comunicazioni malattia";
}
logged_header($op,$_SESSION["nome"]." ".
		$_SESSION["cognome"]." - ".$_SESSION["ditta_rs"]
		,$subtitle);
close_logged_header($_SESSION["livello"]);

switch($malattie_action)
{
	case "add_malattie":
	case "edit_malattie":
		if((!isset($valori))&&($malattie_action=="edit_malattie"))
		{
			$valori["id_malattie"]=$_GET["malattie_to_edit"];
			$conn=mysqli_connect($myhost, $myuser, $mypass)
				or die("Connessione non riuscita".mysqli_error($conn));
			((bool)mysqli_query($conn, "USE " . $dbname));

			$query="SELECT malattie.* FROM malattie WHERE malattie.id=".$_GET["malattie_to_edit"];
			$result=@mysqli_query($conn, $query)
				or die($query."<br>".mysqli_error($conn));

			$row=mysqli_fetch_assoc($result);

			$valori["da"]=my_date_format($row["da"],"d/m/Y");
			$valori["a"]=my_date_format($row["a"],"d/m/Y");
			$valori["codice"]=$row["codice"];
			((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
			((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
		}
		?>

		<div id="centra">
			<form action="<?=$self?>" method="post" id="edit_form"
				onsubmit="return check_post_malattie(this)">
				<input type="hidden" name="performAction" value="1">
		<?
		if($malattie_action=="edit_malattie")
		{?>
				<input type="hidden" name="id_malattie" value="<?=@$valori["id_malattie"]?>">
		<?}?>
				<table class="edit">
					<tr>
						<td colspan="2">
							<div id="div_malattie">
								<table class="edit">
									<tr>
										<td class="right" style="border-style:none">dal (gg/mm/aaaa)</td>
										<td style="border-style:none">
											<input type="text" id="da" name="da" size="10"
												value="<?=(strlen(@$valori["da"])?$valori["da"]:"----")?>"
												onchange="this.value=formattadata(this.value)">
										</td>
									</tr>
									<tr>
										<td class="right" style="border-style:none">al (gg/mm/aaaa)</td>
										<td style="border-style:none">
											<input type="text" id="a" name="a" size="10"
												value="<?=(strlen(@$valori["a"])?$valori["a"]:"----")?>"
												onchange="this.value=formattadata(this.value)">
										</td>
									</tr>
									<tr>
										<td class="right" style="border-style:none">codice</td>
										<td style="border-style:none">
											<input type="text" name="codice" size="10"
												value="<?=@$valori["codice"]?>" >
										</td>
									</tr>
								</table>
							</div>
						</td>
					</tr>
					<tr>
						<td colspan="2" class="footer">
							<input type="submit" class="button"
								name="<?=$malattie_action?>" value="accetta">&nbsp;
							<input type="button" class="button"
								onclick="javascript:redirect('<?=$self?>&amp;op=malattie');"
								value="annulla">
						</td>
					</tr>
				</table>
			</form>
		</div>
		<?
		break;
	default:
		$conn=mysqli_connect($myhost, $myuser, $mypass)
			or die("Connessione non riuscita".mysqli_error($conn));
		((bool)mysqli_query($conn, "USE " . $dbname));
		$ncols=($_SESSION["livello"]==1?8:6);
		if(isset($_SESSION["pda"]))
		    $ncols-=2;

		if(isset($_SESSION["pda"])||($_SESSION["livello"]==1))
		{
			if(isset($_GET["mostra_tutti"]))
				$_SESSION["malattie_mostra_tutti"]=1;
			elseif(isset($_GET["attivi"]))
				$_SESSION["malattie_mostra_tutti"]=2;
			elseif(isset($_GET["history"]))
				$_SESSION["malattie_mostra_tutti"]=3;
			elseif(isset($_GET["filtra"])||(!isset($_SESSION["malattie_mostra_tutti"])))
				$_SESSION["malattie_mostra_tutti"]=0;
			if($_SESSION["malattie_mostra_tutti"]==0)
				$query="SELECT malattie.*,utenti.cognome,utenti.nome,utenti.commessa_default
						FROM malattie JOIN utenti
						ON malattie.id_utente=utenti.id
						WHERE malattie.stato<>1
						ORDER BY utenti.cognome, malattie.data DESC";
			elseif($_SESSION["malattie_mostra_tutti"]==3)
				$query="SELECT malattie.*,utenti.cognome,utenti.nome,utenti.commessa_default
						FROM malattie JOIN utenti
						ON malattie.id_utente=utenti.id
						WHERE malattie.id_utente='".$_SESSION["id_edit"]."'
						ORDER BY utenti.cognome, malattie.data DESC";
			elseif($_SESSION["malattie_mostra_tutti"]==2)
				$query="SELECT malattie.*,utenti.cognome,utenti.nome,utenti.commessa_default
						FROM malattie JOIN utenti
						ON malattie.id_utente=utenti.id
						WHERE DATE(NOW())<=DATE(malattie.a)
						ORDER BY utenti.cognome, malattie.data DESC";
			elseif($_SESSION["malattie_mostra_tutti"]==1)
				$query="SELECT malattie.*,utenti.cognome,utenti.nome,utenti.commessa_default
						FROM malattie JOIN utenti
						ON malattie.id_utente=utenti.id
						ORDER BY utenti.cognome, malattie.data DESC";

			if($_SESSION["malattie_mostra_tutti"]==0)
			{
				?>
				<div class="centra">
				<a href='<?=$self?>&amp;op=malattie&mostra_tutti'>
					mostra tutti
				</a>
				<?
			}
			else
			{
				?>
				<div class="centra">
				<a href='<?=$self?>&amp;op=malattie&filtra '>
					filtra
				</a>
				<?
			}
			?>
				 |
				<a href='<?=$self?>&amp;op=pa_malattie_sw&accettatutti'>
					accetta tutti
				</a>
				 |
				<a href='<?=$self?>&amp;op=malattie&attivi'>
					attivi
				</a>
				 |
				<a href='<?=$self?>&amp;op=malattie&history'>
					storico <?=$_SESSION["nome"]." ".$_SESSION["cognome"]?>
				</a>
				</div>
			<?
		}
		else
		{
		    if($_SESSION["livello"]!=1)
			$query="SELECT * FROM malattie
		    	    WHERE id_utente=".$_SESSION['id_edit']."
			    ORDER BY a DESC";
		    else
			    $query="SELECT malattie.*,utenti.cognome,utenti.nome,utenti.commessa_default
					    FROM malattie JOIN utenti
					    ON malattie.id_utente=utenti.id
					    ORDER BY utenti.cognome, malattie.data DESC";
		}
		$result=@mysqli_query($conn, $query)
			or die($query."<br>".mysqli_error($conn));
		?>

		<table class="edit">
			<tr class="table-header">
				<td>&nbsp;</td>
		<?
		if($_SESSION["livello"]==1)
		{?>
				<td>cognome</td>
				<td>nome</td>
		<?}
		if(!isset($_SESSION["pda"]))
		{?>
				<td>data richiesta</td>
		<?}?>
				<td>dal</td>
				<td>al</td>
		<?
		if(!isset($_SESSION["pda"]))
		{?>
				<td>durata</td>
		<?}?>
			<td>codice</td>
		</tr>
		<?
		while($row=mysqli_fetch_assoc($result))
		{
			$da=my_date_format($row["da"],"d/m/Y");
			$a=my_date_format($row["a"],"d/m/Y");
			$durata=conta_giorni_lav(substr($row["da"],0,10),substr($row["a"],0,10));
			if(((int)$durata)!=$durata)
				$durata=((int)$durata)+1;
			$durata.=" gg";
			$color=array(0=>"yellow",1=>"green",2=>"red");
			?>

			<tr>
				<td class="right">
			<?
			if(($row["stato"]==0)&&(datetime_diff(date("Y-m-d"),$row["da"])>=-864000)
				&&((int)date("Ym")<=(int)date("Ym",strtotime($row["da"]))))
			{?>
					<a href="<?=$self?>&amp;op=malattie&amp;malattie_action=edit_malattie&amp;malattie_to_edit=<?=$row["id"]?>">
						<img src="img/b_edit.png" alt="Edit" title="Edit">
					</a>
			<?}
			if($_SESSION["livello"]==1)
			{
				$link="$self&amp;op=pa_malattie_sw&amp;";
				$link.="id=".$row["id"]."&amp;stato=".(($row["stato"]+1)%2);
				$link.="&amp;commessa=".$row["commessa_default"];
				$link.="&amp;id_utente=".$row["id_utente"];
				$link.="&amp;da=".$row["da"]."&amp;a=".$row["a"];
				$link.="&amp;codice=".$row["codice"];
				?>
					<a href="#" onclick="
						MsgOkCancel('Elimino richiesta di <?=$row["cognome"]?>?',
							'<?=$self?>&amp;op=pa_malattie_del&amp;id=<?=$row["id"]?>');">
						<img src="img/b_drop.png" alt="Elimina" title="Elimina">
					</a>
					<a href="<?=$link?>" title="switch" alt="switch">
				<?
			}?>
						<img src="img/<?=$color[$row["stato"]]?>.png"
							alt="<?=$color[$row["stato"]]?>"
							title="<?=$color[$row["status"]]?>">
			<?
			if($_SESSION["livello"]==1)
			{?>
					</a>
			<?}?>
				</td>
			<?
			if($_SESSION["livello"]==1)
			{?>
				<td>
					<?=$row["cognome"]?>
				</td>
				<td>
					<?=$row["nome"]?>
				</td>
			<?}
			if(!isset($_SESSION["pda"]))
			{?>
			    <td>
			    	<?=my_date_format($row["data"],"d/m/Y")?>
			    </td>
			<?}?>
				<td>
					<?=$da?>
				</td>
				<td>
					<?=$a?>
				</td>
			<?
			if(!isset($_SESSION["pda"]))
			{?>
			    <td>
			    	<?=$durata?>
			    </td>
			<?}?>
			    <td>
			    	<?=$row["codice"]?>
			    </td>
			</tr>
		<?}?>
			<tr>
				<td colspan="<?=$ncols?>" class="row_attivo">
					<a href="<?=$self?>&amp;op=malattie&amp;malattie_action=add_malattie">
						<img src="img/b_edit.png" alt="Nuovo" title="Nuovo">
						&nbsp;Nuova comunicazione
					</a>
				</td>
			</tr>
		</table>
		<?
		((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
		((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);

		break;
}?>
</div>
<?
if(($malattie_action=="add_malattie")
	||($malattie_action=="edit_malattie"))
{?>
	<script type="text/javascript">
	function check_post_malattie(form)
	{
		var da,a,codice;
		var out=true;

		da=form.da.value;
		a=form.a.value;
		codice=form.codice.value.trim();

		if(codice.length==0)
		{
			showMessage("inserisci il codice");
			return false;
		}
		if(!is_date(da))
		{
			showMessage("data inizio non valida");
			return(false);
		}
		if(!is_date(a))
		{
			showMessage("data fine non valida");
			return false;
		}
		if(datetime_diff(String(da)+" 0:00",String(a)+" 0:00")<0)
		{
			showMessage("data fine precedente data inizio");
			return false;
		}
<?
if($_SESSION["livello"]!=1)
{?>
		if(datetime_diff('<?=date("d/m/Y")?>',da)<-864000)
		{
			showMessage("data inizio troppo antecendente");
			return false;
		}
		var splitted=da.split("/");
		while(splitted[1].length<2)
			splitted[1]="0"+splitted[1];

		while(splitted[2].length<3)
			splitted[2]="0"+splitted[2];
		if(splitted[2].length==3)
			splitted[2]="2"+splitted[2];

		if(Number('<?=date("Ym")?>'>Number(splitted[2]+splitted[1])))
		{
			showMessage("data inizio nel mese passato");
			return false;
		}
<?}?>
		return out;
	}
	document.getElementById('edit_form').da.focus();
	</script>
<?}
?>
