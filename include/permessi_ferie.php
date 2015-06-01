<?
if(isset($_GET["pf_action"]))
{
	$pf_action=$_GET["pf_action"];
	$subtitle=($pf_action=="add_pf"?"nuovo permesso/ferie":
		"modifica permesso/ferie");
}
else
{
	$pf_action="list_pf";
	$subtitle="gestione permessi/ferie";
}
logged_header($op,$_SESSION["nome"]." ".
		$_SESSION["cognome"]." - ".$_SESSION["ditta_rs"]
		,$subtitle);
close_logged_header($_SESSION["livello"]);

switch($pf_action)
{
	case "add_pf":
	case "edit_pf":
		if((!isset($valori))&&($pf_action=="edit_pf"))
		{
			$valori["id_pf"]=$_GET["pf_to_edit"];
			$conn=mysqli_connect($myhost, $myuser, $mypass)
				or die("Connessione non riuscita".mysqli_error($conn));
			((bool)mysqli_query($conn, "USE " . $dbname));

			$query="SELECT permessi_ferie.* FROM permessi_ferie WHERE permessi_ferie.id=".$_GET["pf_to_edit"];
			$result=@mysqli_query($conn, $query)
				or die($query."<br>".mysqli_error($conn));

			$row=mysqli_fetch_assoc($result);
			if((substr($row["da"],0,10)!=substr($row["a"],0,10))
				||substr($row["da"],11)==substr($row["a"],11))
			{
				$valori["pf"]="ferie";
				$valori["da"]=my_date_format(substr($row["da"],0,10),"d/m/Y");
				$valori["a"]=my_date_format(substr($row["a"],0,10),"d/m/Y");
			}
			else
			{
				$valori["pf"]="permesso";
				$valori["giorno"]=my_date_format(substr($row["da"],0,10),"d/m/Y");
				$valori["dalle"]=substr($row["da"],11,5);
				$valori["alle"]=substr($row["a"],11,5);
			}
			((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
			((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
		}
		if(!isset($valori["pf"]))
			$valori["pf"]="ferie";
		?>

		<div id="centra">
			<form action="<?=$self?>" method="post" id="edit_form"
				onsubmit="return check_post_pf(this)">
				<input type="hidden" name="performAction" value="1">
		<?
		if($pf_action=="edit_pf")
		{?>
				<input type="hidden" name="id_pf" value="<?=$valori["id_pf"]?>">
		<?}?>
				<table class="edit">
					<tr>
						<td class="right">tipo</td>
						<td>
							<select name="pf"  onChange="javascript:hide_permessi(this);">
								<option value="ferie"<?=($valori["pf"]=="ferie"?" selected":"")?>>
									ferie
								</option>
								<option value="permesso"<?=($valori["pf"]=="permesso"?" selected":"")?>>
									permesso
								</option>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<div id="div_ferie"<?=($valori["pf"]=="ferie"?' style="display: block;"':
																		' style="display: none;"')?>>
								<table class="edit">
									<tr>
										<td class="right" style="border-style:none">dal (gg/mm/aaaa)</td>
										<td style="border-style:none">
											<input type="text" name="da" size="10"
												value="<?=(strlen(@$valori["da"])?$valori["da"]:"----")?>"
												onchange="this.value=formattadata(this.value)">
										</td>
									</tr>
									<tr>
										<td class="right" style="border-style:none">al (gg/mm/aaaa)</td>
										<td style="border-style:none">
											<input type="text" name="a" size="10"
												value="<?=(strlen(@$valori["a"])?$valori["a"]:"----")?>"
												onchange="this.value=formattadata(this.value)">
										</td>
									</tr>
								</table>
							</div>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<div id="div_permessi"<?=($valori["pf"]=="permesso"?' style="display: block;"':
														' style="display: none;"')?>>
								<table class="edit">
									<tr>
										<td class="right" style="border-style:none">giorno (gg/mm/aaaa)</td>
										<td style="border-style:none">
											<input type="text" name="giorno" size="10"
												value="<?=(strlen($valori["giorno"])?$valori["giorno"]:"----")?>"
												onchange="this.value=formattadata(this.value)">
										</td>
									</tr>
									<tr>
										<td class="right" style="border-style:none">dalle (hh:mm)</td>
										<td style="border-style:none">
											<input type="text" name="dalle" size="5"
												value="<?=(strlen($valori["dalle"])?$valori["dalle"]:"----")?>"
												onChange="this.value=formattaora(this);">
										</td>
									</tr>
									<tr>
										<td class="right" style="border-style:none">alle (hh:mm)</td>
										<td style="border-style:none">
											<input type="text" name="alle" size="5"
												value="<?=(strlen($valori["alle"])?$valori["alle"]:"----")?>"
												onChange="this.value=formattaora(this);">
										</td>
									</tr>
								</table>
							</div>
						</td>
					</tr>
					<tr>
						<td colspan="2" class="footer">
							<input type="submit" class="button"
								name="<?=$pf_action?>" value="accetta">&nbsp;
							<input type="button" class="button"
								onclick="javascript:redirect('<?=$self?>&amp;op=permessi_ferie');"
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
		$ncols=($_SESSION["livello"]==1?7:5);
		if(isset($_SESSION["pda"]))
		    $ncols-=2;

		if(isset($_SESSION["pda"])||($_SESSION["livello"]==1))
		{
			if(isset($_GET["mostra_tutti"]))
				$_SESSION["pf_mostra_tutti"]=1;
			elseif(isset($_GET["attivi"]))
				$_SESSION["pf_mostra_tutti"]=2;
			elseif(isset($_GET["history"]))
				$_SESSION["pf_mostra_tutti"]=3;
			elseif(isset($_GET["filtra"])||(!isset($_SESSION["pf_mostra_tutti"])))
				$_SESSION["pf_mostra_tutti"]=0;
			if($_SESSION["pf_mostra_tutti"]==0)
				$query="SELECT permessi_ferie.*,utenti.cognome,utenti.nome,utenti.commessa_default
						FROM permessi_ferie JOIN utenti
						ON permessi_ferie.id_utente=utenti.id
						WHERE permessi_ferie.stato<>1
						ORDER BY utenti.cognome, permessi_ferie.data DESC";
			elseif($_SESSION["pf_mostra_tutti"]==3)
				$query="SELECT permessi_ferie.*,utenti.cognome,utenti.nome,utenti.commessa_default
						FROM permessi_ferie JOIN utenti
						ON permessi_ferie.id_utente=utenti.id
						WHERE permessi_ferie.id_utente='".$_SESSION["id_edit"]."'
						ORDER BY utenti.cognome, permessi_ferie.data DESC";
			elseif($_SESSION["pf_mostra_tutti"]==2)
				$query="SELECT permessi_ferie.*,utenti.cognome,utenti.nome,utenti.commessa_default
						FROM permessi_ferie JOIN utenti
						ON permessi_ferie.id_utente=utenti.id
						WHERE DATE(NOW())<=DATE(permessi_ferie.a)
						ORDER BY utenti.cognome, permessi_ferie.data DESC";
			elseif($_SESSION["pf_mostra_tutti"]==1)
				$query="SELECT permessi_ferie.*,utenti.cognome,utenti.nome,utenti.commessa_default
						FROM permessi_ferie JOIN utenti
						ON permessi_ferie.id_utente=utenti.id
						ORDER BY utenti.cognome, permessi_ferie.data DESC";

			if($_SESSION["pf_mostra_tutti"]==0)
			{
				?>
				<div class="centra">
				<a href='<?=$self?>&amp;op=permessi_ferie&mostra_tutti'>
					mostra tutti
				</a>
				<?
			}
			else
			{
				?>
				<div class="centra">
				<a href='<?=$self?>&amp;op=permessi_ferie&filtra '>
					filtra
				</a>
				<?
			}
			?>
				 |
				<a href='<?=$self?>&amp;op=pa_pf_sw&accettatutti'>
					accetta tutti
				</a>
				 |
				<a href='<?=$self?>&amp;op=permessi_ferie&attivi'>
					attivi
				</a>
				 |
				<a href='<?=$self?>&amp;op=permessi_ferie&history'>
					storico <?=$_SESSION["nome"]." ".$_SESSION["cognome"]?>
				</a>
				</div>
			<?
		}
		else
		{
		    if($_SESSION["livello"]!=1)
			$query="SELECT * FROM permessi_ferie
		    	    WHERE id_utente=".$_SESSION['id_edit']."
			    ORDER BY a DESC";
		    else
			    $query="SELECT permessi_ferie.*,utenti.cognome,utenti.nome,utenti.commessa_default
					    FROM permessi_ferie JOIN utenti
					    ON permessi_ferie.id_utente=utenti.id
					    ORDER BY utenti.cognome, permessi_ferie.data DESC";
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
		</tr>
		<?
		while($row=mysqli_fetch_assoc($result))
		{
			$da=my_date_format(substr($row["da"],0,10),"d/m/Y");
			$a=my_date_format(substr($row["a"],0,10),"d/m/Y");
			if((substr($row["da"],0,10)==substr($row["a"],0,10))&&
				$row["da"]!=$row["a"])
			{
				$da.=substr($row["da"],10,6);
				$a.=substr($row["a"],10,6);
				$durata=int_to_hour(hour_to_int(substr($row["a"],11,5))-hour_to_int(substr($row["da"],11,5)));
			}
			else
			{
				$durata=conta_giorni_lav(substr($row["da"],0,10),substr($row["a"],0,10));
				if(((int)$durata)!=$durata)
					$durata=((int)$durata)+1;
				$durata.=" gg";
			}
			$color=array(0=>"yellow",1=>"green",2=>"red");
			?>

			<tr>
				<td class="right">
			<?
			if(($row["stato"]==0)&&(datetime_diff(date("Y-m-d"),$row["da"])>=-864000)
				&&((int)date("Ym")<=(int)date("Ym",strtotime($row["da"]))))
			{?>
					<a href="<?=$self?>&amp;op=permessi_ferie&amp;pf_action=edit_pf&amp;pf_to_edit=<?=$row["id"]?>">
						<img src="img/b_edit.png" alt="Edit" title="Edit">
					</a>
			<?}
			if($_SESSION["livello"]==1)
			{
				$link="$self&amp;op=pa_pf_sw&amp;id=".$row["id"]."&amp;stato=".(($row["stato"]+1)%3)."&amp;commessa=".$row["commessa_default"]."&amp;id_utente=".$row["id_utente"];
				if((substr($row["da"],11,8)=="00:00:00")&&(substr($row["da"],11,8)=="00:00:00"))
					$link.="&amp;da=".substr($row["da"],0,10)."&amp;a=".substr($row["a"],0,10);
				else
					$link.="&amp;da=".$row["da"]."&amp;a=".$row["a"];
				?>
					<a href="#" onclick="
						MsgOkCancel('Elimino permesso di <?=$row["cognome"]?>?',
							'<?=$self?>&amp;op=pa_pf_del&amp;id=<?=$row["id"]?>');">
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
			    	<?=my_date_format(substr($row["data"],0,10),"d/m/Y")?>
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
			</tr>
		<?}?>
			<tr>
				<td colspan="<?=$ncols?>" class="row_attivo">
					<a href="<?=$self?>&amp;op=permessi_ferie&amp;pf_action=add_pf">
						<img src="img/b_edit.png" alt="Nuovo" title="Nuovo">
						&nbsp;Nuova richiesta
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
if(($pf_action=="add_pf")
	||($pf_action=="edit_pf"))
{?>
	<script type="text/javascript">
	function hide_permessi(selObj)
	{
		if(selObj.selectedIndex==0)
		{
			document.getElementById("div_permessi").style.display = 'none';
			document.getElementById("div_ferie").style.display = 'block';
			document.getElementById('edit_form').da.focus();
		}
		else
		{
			document.getElementById("div_permessi").style.display = 'block';
			document.getElementById("div_ferie").style.display = 'none';
			document.getElementById('edit_form').giorno.focus();
		}
	}

	function check_post_pf(form)
	{
		var da,a,orada,oraa;
		var out=true;

		if(form.pf.value=="ferie")
		{
			da=form.da.value;
			a=form.a.value;
			orada="0:00";
			oraa="0:00";
		}
		else
		{
			da=form.giorno.value;
			a=da;
			orada=form.dalle.value;
			oraa=form.alle.value;
		}
		if(!is_date(da))
		{
			showMessage("data inizio non valida");
			return(false);
		}
		if(!is_hour(orada))
		{
			showMessage("ora inizio non valida");
			return(false);
		}
		if(!is_date(a))
		{
			showMessage("data fine non valida");
			return false;
		}
		if(!is_hour(oraa))
		{
			showMessage("ora fine non valida");
			return false;
		}
		if(datetime_diff(String(da)+" "+String(orada),String(a)+" "+String(oraa))<0)
		{
			showMessage("data/ora fine precedente data/ora inizio");
			return false;
		}
<?
if($_SESSION["livello"]!=1)
{?>
		if(datetime_diff('<?=date("d/m/Y")?>',da)<-864000)
		{
			showMessage("data/ora inizio troppo antecendente");
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
			showMessage("data/ora inizio nel mese passato");
			return false;
		}
<?}?>
		var splorada=orada.split(":");
		var sploraa=oraa.split(":");
		if((Number(sploraa[1])-Number(splorada[1])+60)%15)
		{
			showMessage("i permessi devono essere multipli di 15'");
			return false;
		}
		return out;
	}

	document.getElementById('edit_form').pf.focus();
	</script>
<?}
?>
