<?
if(is_array($_REQUEST["giorno"]))
{
	$multi=1;
	$hidden="";
	$conta=0;
	foreach($_REQUEST["giorno"] as $g)
	{
		$conta++;
		$hidden.="'$g',";
	}
	$hidden=trim($hidden,",");
	$giorno=$_REQUEST["giorno"][0];
	$titolo="modifica $conta giorni";
}
else
{
	$multi=0;
	$giorno=$_REQUEST["giorno"];
	$hidden="'$giorno'";
	$titolo=($giorni_settimana[my_date_format($giorno,"w")].
		my_date_format($giorno," d.m.Y"));
}

logged_header($op,$_SESSION["nome"]." ".$_SESSION["cognome"]." - ".$_SESSION["ditta_rs"]
	,$titolo);
close_logged_header($_SESSION["livello"]);


$mese=(int)substr($giorno,5,2);
$utente=$_SESSION["id_edit"];
$conn=mysqli_connect($myhost, $myuser, $mypass)
	or die("Connessione non riuscita".mysqli_error($conn));
((bool)mysqli_query($conn, "USE " . $dbname));
$trasferte=array();
$trasferte[-1]="----";
$query="SELECT id,codice,CONCAT(descrizione,' (€ ',importo,')') AS descrizione
		FROM trasferte WHERE enabled=1 ORDER BY descrizione";
$result=@mysqli_query($conn, $query)
	or die($query."<br>".mysqli_error($conn));
while($row=mysqli_fetch_assoc($result))
	$trasferte[$row["id"]]=$row["descrizione"];
((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);

$query="SELECT commesse.* FROM commesse
		WHERE commesse.visibile=1
		ORDER BY commesse.commessa";
$commesse=array();
$commesse[-1]="----";
$result=@mysqli_query($conn, $query)
	or die($query."<br>".mysqli_error($conn));
while($row=mysqli_fetch_assoc($result))
	$commesse[$row["id"]]=$row["commessa"]." - ".$row["descrizione"];
((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);

if(!isset($valori))
{
	$query="SELECT * FROM presenze WHERE giorno='$giorno' AND id='$utente'";
	$result=@mysqli_query($conn, $query)
		or die($query."<br>".mysqli_error($conn));
	$valori=mysqli_fetch_assoc($result);
	((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
}
if($valori["tr_euri_km"]==0)
	$valori["tr_euri_km"]=$_SESSION["tr_euri_km"];
((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
?>

<div class="centra">
	<form action="<?=$self?>&amp;mese=<?=my_date_format($giorno,"n")?>
						&amp;anno=<?=my_date_format($giorno,"Y")?>"
					id="edit_form" method="post"
					onsubmit="return <?=$_REQUEST["trasf"]==1?"check_post_trasf"
												:"check_post"?>(this)">
<!--					<p class="titolo-tab">
			<?=($giorni_settimana[my_date_format($giorno,"w")].
										my_date_format($giorno," d.m.Y"))?>
		</p>-->
		<?=($valori["festivo"]&&($_SESSION["livello"]!=1)?'<p class="titolo-tab">festivo</p>':'')?>
		<input type="hidden" value="<?=$hidden?>" id="giorno" name="giorno">
		<input type="hidden" value="1" name="performAction">
		<?
		if($_SESSION["livello"]!=1)
		{?>
			<input type="hidden" value="<?=$valori["festivo"]?>" name="festivo">
		<?}
if(!($_REQUEST["trasf"]==1))
{
?>	<table class="edit">
	<?
		if($_SESSION["livello"]==1)
		{?>
	<tr>
		<td>
			<input type="checkbox" class="check" name="festivo_c">
			festivo
		</td>
		<td>
			<input type="checkbox" class="check"
				name="festivo"<?=(($valori["festivo"]==1)?" checked":"")?>
				value="<?=$valori["festivo"]?>"
				onchange="this.value=(this.checked?1:0);
						if(!Number(this.value))
							document.getElementById('edit_form').ore_via.value='----';
						document.getElementById('edit_form').ingresso.onchange();">
		</td>
	</tr>
		<?}?>
	<tr>
		<td>
			<input type="checkbox" class="check" name="ferie_c">
			ferie
		</td>
		<td>
			<input type="checkbox" class="check" name="ferie"<?=(($valori["ferie"]==1)?" checked":"")?>
				onClick="if(this.checked){resetForm(document.getElementById('edit_form'),'f');}">
		</td>
	</tr>
	<tr>
		<td>
			<input type="checkbox" class="check" name="malattia_c">
			malattia
		</td>
		<td>
			<input type="checkbox" class="check" name="malattia"<?=(($valori["malattia"]==1)?" checked":"")?>
				onClick="if(this.checked){resetForm(document.getElementById('edit_form'),'m');}">
		</td>
	</tr>
	<tr>
		<td>
			<input type="checkbox" class="check" name="trasferta_c">
			trasferta
		</td>
		<td>
			<select class="input" name="trasferta">
		<?
			foreach($trasferte as $trasf_id=>$trasf_text)
			{?>
				<option value="<?=$trasf_id?>"<?=($trasf_id==$valori["trasferta"]?" selected":"")?>>
					<?=$trasf_text?>
				</option>
			<?}?>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			<input type="checkbox" class="check" name="ingresso_c">
			ingresso mattino
		</td>
		<td>
			<input type="text" name="ingresso" size="5" maxlength="5"
				value="<?=int_to_hour($valori["ingresso"])?>"
				onChange="this.value=int2ora(ora2int(this.value));
					document.getElementById('edit_form').ore_str.value=
						calcola_str_da_ingressi(document.getElementById('edit_form'));">
		</td>
	</tr>
	<tr>
		<td>
			<input type="checkbox" class="check" name="pausa_c">
			pausa pranzo
		</td>
		<td>
			<input type="text" name="pausa" size="5" maxlength="5"
				value="<?=int_to_hour($valori["pausa"])?>"
				onChange="this.value=int2ora(ora2int(this.value));
					document.getElementById('edit_form').ore_str.value=
						calcola_str_da_ingressi(document.getElementById('edit_form'))">
		</td>
	</tr>
	<tr>
		<td>
			<input type="checkbox" class="check" name="uscita_c">
			uscita sera
		</td>
		<td>
			<input type="text" name="uscita" size="5" maxlength="5"
				value="<?=int_to_hour($valori["uscita"])?>"
				onChange="this.value=int2ora(ora2int(this.value));
					document.getElementById('edit_form').ore_str.value=
						calcola_str_da_ingressi(document.getElementById('edit_form'))">
		</td>
	</tr>
	<tr>
		<td>
			<input type="checkbox" class="check" name="ingresso2_c">
			extra ingresso
		</td>
		<td>
			<input type="text" name="ingresso2" size="5" maxlength="5"
				value="<?=int_to_hour($valori["ingresso2"])?>"
				onChange="this.value=int2ora(ora2int(this.value));
					document.getElementById('edit_form').ore_str.value=
						calcola_str_da_ingressi(document.getElementById('edit_form'))">
		</td>
	</tr>
	<tr>
		<td>
			<input type="checkbox" class="check" name="uscita2_c">
			extra uscita
		</td>
		<td>
			<input type="text" name="uscita2" size="5" maxlength="5"
				value="<?=int_to_hour($valori["uscita2"])?>"
				onChange="this.value=int2ora(ora2int(this.value));
					document.getElementById('edit_form').ore_str.value=
						calcola_str_da_ingressi(document.getElementById('edit_form'))">
		</td>
	</tr>
	<tr>
		<td>
			<input type="checkbox" class="check" name="ore_str_c">
			ore straordinario
		</td>
		<td>
			<input type="text" name="ore_str" size="5" maxlength="5"
				value="<?=int_to_hour($valori["ore_str"])?>"
				onChange="this.value=int2ora(ora2int(this.value));
					document.getElementById('edit_form').ore_via.value=
						calcola_via_da_str(document.getElementById('edit_form'))">
		</td>
	</tr>
	<tr>
		<td>
			<input type="checkbox" class="check" name="ore_via_c">
			ore viaggio
		</td>
		<td>
			<input type="text" name="ore_via" size="5" maxlength="5"
				value="<?=int_to_hour($valori["ore_via"])?>"
				onChange="this.value=int2ora(ora2int(this.value));
					document.getElementById('edit_form').ore_str.value=
						calcola_str_da_ingressi(document.getElementById('edit_form'))">
		</td>
	</tr>
	<tr>
		<td>
			<input type="checkbox" class="check" name="note_c">
			note
		</td>
		<td>
		<input type="text" name="note" size="50" maxlength="50"
			value="<?=safeString($valori["note"])?>">
		</td>
	</tr>
	<tr style="display:none">
		<td>
			<input type="checkbox" class="check" name="id_commessa_c">
			commessa
		</td>
		<td>
			<select class="input" name="id_commessa">
			<?
	foreach($commesse as $commessa_id=>$commessa_text)
	{?>
				<option value="<?=$commessa_id?>"<?=($commessa_id==$valori["id_commessa"]?" selected":"")?>>
					<?=$commessa_text?>
				</option>
	<?}?>
			</select>
		</td>
	</tr>
	</table>
	<?
	if($_SESSION["livello"]==1)
	{?>
		<table class="edit">
			<tr>
				<td>
					<input type="checkbox" class="check" name="forza_ore_giorn_c">
					forza ore giornaliere
				</td>
				<td>
					<input type="text" name="forza_ore_giorn" size="5"
						maxlength="5" value="<?=int_to_hour($valori["forza_ore_giorn"])?>"
						onChange="this.value=int2ora(ora2int(this.value));
							document.getElementById('edit_form').forza_ore_giorn.value=formattaora(document.getElementById('edit_form').forza_ore_giorn);">
				</td>
			</tr>
			<tr>
				<td>
					<input type="checkbox" class="check" name="forza_ore_perm_c">
					forza ore permesso
				</td>
				<td>
					<input type="text" name="forza_ore_perm" size="5"
						maxlength="5" value="<?=int_to_hour($valori["forza_ore_perm"])?>"
						onChange="this.value=int2ora(ora2int(this.value));
							document.getElementById('edit_form').forza_ore_perm.value=formattaora(document.getElementById('edit_form').forza_ore_perm)">
				</td>
			</tr>
			<tr>
				<td>
					<input type="checkbox" class="check" name="forza_ore_str_c">
					forza ore straordinario
				</td>
				<td>
					<input type="text" name="forza_ore_str" size="5" maxlength="5"
						value="<?=int_to_hour($valori["forza_ore_str"])?>"
						onChange="this.value=int2ora(ora2int(this.value));
							document.getElementById('edit_form').forza_ore_str.value=formattaora(document.getElementById('edit_form').forza_ore_str)">
				</td>
			</tr>
			<tr>
				<td>
					<input type="checkbox" class="check" name="forza_ore_via_c">
					forza ore viaggio
				</td>
				<td>
					<input type="text" name="forza_ore_via" size="5" maxlength="5"
						value="<?=int_to_hour($valori["forza_ore_via"])?>"
						onChange="this.value=int2ora(ora2int(this.value));
							document.getElementById('edit_form').forza_ore_via.value=formattaora(document.getElementById('edit_form').forza_ore_via)">
				</td>
			</tr>
		</table>
	<?}
}
else
{?>
	<input type="hidden" name="trasf" value="1">
	<p class="titolo-tab">trasferta</p>
	<table class="edit">
		<tr>
			<td>
				<input type="checkbox" class="check" name="tr_destinazione_c">
				destinazione
			</td>
			<td>
				<input type="text" name="tr_destinazione" size="30"
					maxlength="30" value="<?=safeString($valori["tr_destinazione"])?>">
			</td>
		</tr>
		<tr>
			<td>
				<input type="checkbox" class="check" name="tr_motivo_c">
				motivo
			</td>
			<td>
				<input type="text" name="tr_motivo" size="30"
					maxlength="30" value="<?=safeString($valori["tr_motivo"])?>">
			</td>
		</tr>
		<tr>
			<td>
				<input type="checkbox" class="check" name="tr_ind_forf_c">
				indennit&agrave; forfettaria
			</td>
			<td>
				<input type="text" name="tr_ind_forf" size="10"
					maxlength="10" value="<?=$valori["tr_ind_forf"]?>"
					onchange="this.value=this.value.replace(',','.')">
			</td>
		</tr>
		<tr>
			<td>
				<input type="checkbox" class="check" name="tr_spese_viaggio_c">
				spese viaggio
			</td>
			<td>
				<input type="text" name="tr_spese_viaggio" size="10"
				maxlength="10" value="<?=$valori["tr_spese_viaggio"]?>"
				onchange="this.value=this.value.replace(',','.')">
		</td>
	</tr>
	<tr>
		<td>
			<input type="checkbox" class="check" name="tr_spese_alloggio_c">
			spese alloggio
		</td>
		<td>
			<input type="text" name="tr_spese_alloggio" size="10"
				maxlength="10" value="<?=$valori["tr_spese_alloggio"]?>"
				onchange="this.value=this.value.replace(',','.')">
		</td>
	</tr>
	<tr>
		<td>
			<input type="checkbox" class="check" name="tr_spese_vitto_c">
			spese di vitto
		</td>
		<td>
			<input type="text" name="tr_spese_vitto" size="10"
				maxlength="10" value="<?=$valori["tr_spese_vitto"]?>"
				onchange="this.value=this.value.replace(',','.')">

		</td>
	</tr>
	<tr>
		<td>
			<input type="checkbox" class="check" name="tr_km_c">
			km percorsi
		</td>
		<td>
			<input type="text" name="tr_km" size="10" maxlength="10"
				value="<?=$valori["tr_km"]?>"
				onchange="this.value=this.value.replace(',','.')">
		</td>
	</tr>
	<tr>
		<td>
			<input type="checkbox" class="check" name="tr_euri_km_c">
			rimborso chilometrico
		</td>
		<td>
			<input type="text" name="tr_euri_km" size="10" maxlength="10"
				value="<?=$valori["tr_euri_km"]?>"
				onchange="this.value=this.value.replace(',','.')">
		</td>
	</tr>
	</table><?
}?>

<input type="submit" class="button" name="edited" value="accetta">&nbsp;
<input type="button" class="button" onclick='
	javascript:redirect("<?=$self?>&amp;op=<?=($_REQUEST["trasf"]==1?"displaytrasf":"display")?>&amp;mese=<?=my_date_format($giorno,"n")?>&amp;anno=<?=my_date_format($giorno,"Y")?>");' value="annulla">
</form>
</div>

<p class=title>Istruzioni per la compilazione</p><div style="text-align:left;">
<?
if($_REQUEST["trasf"]==1)
{?>
<ul>
	<li> Destinazione e motivo sono mandatori.</li>
	<li> Se non vengono compilati n&egrave; in campo destinazione, n&egrave; il campo motivo la trasferta viene considerata cancellata.</li>
	<li> Negli importi il punto fa da separatore fra unit&agrave; e centesimi</li>
</ul>
<?}
else
{?>
<ul>
	<li> Le ore devono inserite nel formato H:MM oppure HH:MM, arrotondate a 5'.<br>
		Per eliminare un orario &egrave; sufficiente cancellare il contenuto del campo.</li>
	<li> Se viene selezionato il check FERIE o MALATTIA, le ore vengono cancellate
		automaticamente.</li>
	<li> Le ore di straordinario vengono automaticamente calcolate modificando i
		campi di ingresso e/o uscita. L'entit&agrave; minima per il riconoscimento delle
		ore straordinarie &egrave di 30 minuti</li>
	<li> Le ore straordinarie possono essere suddivise parzialmente o completamente
		in ore di viaggio. Inserire nel campo ORE VIAGGIO l'ammontare delle ore, il
		campo ORE STRAORDINARIO verr&agrave; automaticamente modificato. ATTENZIONE: prima
		di modificare o inserire le ORE VIAGGIO, completare l'inserimento
		dell'ingresso e dell'uscita.</li>
	<li> Se l'uscita avviene prima dell'effettuazione delle 8 ore, le restanti
		verranno automaticamente calcolate in ore di permesso. I permessi devono
		essere multipli di 15 minuti.</li>
	<li> Utilizzare i campi EXTRA INGRESSO e EXTRA USCITA, per rientri lavorativi
		serali.</li>
	<li> In caso di trasferta selezionare la descrizione applicabile in funzione
		di: localit&agrave;, trattamento, indennit&agrave speciali.</li>
	<li> Per segnalare casi particolari utilizzare il campo NOTE.</li></ul>
<?}?>
</div>
</div>
<script type="text/javascript">
	var inputs=document.getElementsByTagName("input");
	if(document.getElementById("giorno").value.indexOf(",")<0)
	{
		for(var i=0;i<inputs.length;i++)
		{
			var nodeName=inputs.item(i).name;
			if(nodeName.substr(nodeName.length-2,2)=="_c")
				inputs.item(i).style.display="none";
		}
	}

	function resetForm(form,fm)
	{
		form.id_commessa.value=-1;
		form.ore_str.value='----';
		form.ore_via.value='----';
		form.ingresso.value='----';
		form.uscita.value='----';
		form.ingresso2.value='----';
		form.uscita2.value='----';
		form.pausa.value='----';
		form.trasferta.value=-1;
		if(fm=='m')
			form.ferie.checked=false;
		else
			form.malattia.checked=false;
		form.note.value='';
	}

	function calcola_via_da_str(form)
	{
		var ingresso=form.ingresso;
		var uscita=form.uscita;
		var pausa=form.pausa;
		var ingresso2=form.ingresso2;
		var uscita2=form.uscita2;
		var ore_str=form.ore_str;
		var festivo=form.festivo.value;
		var ore_lav=<?=$ore_lav?>;
		var out=0;

		if(Number(festivo))
			ore_lav=0;
		ore_str.value=int2ora(ora2int(ore_str.value));

		if((ora2int(uscita.value)!=-1)&&(ora2int(ingresso.value)!=-1))
		{
			out=(ora2int(uscita.value)-ora2int(ingresso.value)+1440)%1440;
			if(ora2int(pausa.value)!=-1)
				out-=ora2int(pausa.value);
			if((ora2int(uscita2.value)!=-1)&&(ora2int(ingresso2.value)!=-1))
				out+=(((ora2int(uscita2.value)-ora2int(ingresso2.value))+1440)%1440);
			if(ora2int(ore_str.value)!=-1)
				out-=ora2int(ore_str.value);
		}
		if(out>ore_lav)
			return(int2ora(out-ore_lav));
		else
		{
			if(ora2int(ore_str.value)+Number(out-ore_lav)>0)
				ore_str.value=int2ora(ora2int(ore_str.value)+Number(out-ore_lav));
			else
				ore_str.value=int2ora(-1);
			return(int2ora(-1));
		}
	}

	function calcola_str_da_ingressi(form)
	{
		var ore_lav=<?=$ore_lav?>;
		var out=0;
		var in1,out1,pp,in2,out2,via;
		var ingresso=form.ingresso;
		var uscita=form.uscita;
		var pausa=form.pausa;
		var ingresso2=form.ingresso2;
		var uscita2=form.uscita2;
		var ore_via=form.ore_via;
		var festivo=form.festivo.value;

		if(Number(festivo))
			ore_lav=0;

		if((ora2int(uscita.value)!=-1)&&(ora2int(ingresso.value)!=-1))
		{
			out=(ora2int(uscita.value)-ora2int(ingresso.value)+1440)%1440;
			if(ora2int(pausa.value)!=-1)
				out-=ora2int(pausa.value);
			if((ora2int(uscita2.value)!=-1)&&(ora2int(ingresso2.value)!=-1))
				out+=(((ora2int(uscita2.value)-ora2int(ingresso2.value))+1440)%1440);
			if(ora2int(ore_via.value)!=-1)
				out-=ora2int(ore_via.value);
		}
		if(out>ore_lav)
		{
			if(out-30>=ore_lav)
				return(int2ora(out-ore_lav));
			else
				return(int2ora(-1));
		}
		else
		{
			if(ora2int(ore_via.value)+Number(out-ore_lav)>0)
				ore_via.value=int2ora(ora2int(ore_via.value)+Number(out-ore_lav));
			else
				ore_via.value=int2ora(-1);
			return(int2ora(-1));
		}
	}
	<?
	if($_REQUEST["trasf"]==1)
	{?>
		document.getElementById("edit_form").tr_destinazione.focus();

		function check_post_trasf(form)
		{
			var out=true;
			if((form.tr_destinazione.value.length)&&(form.tr_motivo.value.length))
			{
				if(!is_number(form.tr_ind_forf.value))
				{
					showMessage("indennit&agrave; forf. non valida");
					return false;
				}
				if(!is_number(form.tr_spese_viaggio.value))
				{
					showMessage("spese viaggio non valide");
					return false;
				}
				if(!is_number(form.tr_spese_alloggio.value))
				{
					showMessage("spese alloggio non valide");
					return false;
				}
				if(!is_number(form.tr_spese_vitto.value))
				{
					showMessage("spese vitto non valide");
					return false;
				}
				if(!is_number(form.tr_km.value))
				{
					showMessage("km percorsi non validi");
					return false;
				}
				if(!is_number(form.tr_euri_km.value))
				{
					showMessage("costo chilometrico non valido");
					return false;
				}
			}
			else
			{
				if(form.tr_destinazione.value.length
					||form.tr_motivo.value.length)
				{
					showMessage("destinazione e motivo vanno entrambi compilati");
					return false;
				}
			}
			return out;
		}



	<?}
	else
	{?>

		function check_post(form)
		{
			out=true;
			if((form.ingresso.value!="----")||
				form.uscita.value!="----")
			{
				if(!is_hour(form.ingresso.value))
				{
					showMessage("ora ingresso non valida");
					return false;
				}
				if(!is_hour(form.uscita.value))
				{
					showMessage("ora uscita non valida");
					return false;
				}
			}
			if((form.pausa.value!="----")&&(!is_hour(form.pausa.value)))
			{
				showMessage("pausa non valida");
				return false;
			}
			if((form.ingresso2.value!="----")||
				(form.uscita2.value!="----"))
			{
				if(!is_hour(form.ingresso2.value))
				{
					showMessage("ora ingresso pom non valida");
					return false;
				}
				if(!is_hour(form.uscita2.value))
				{
					showMessage("ora uscita pom non valida");
					return false;
				}
			}
			if((form.ore_str.value!="----")&&(!is_hour(form.ore_str.value)))
			{
				showMessage("straordinario non valido");
				return false;
			}
			if((form.ore_via.value!="----")&&(!is_hour(form.ore_via.value)))
			{
				showMessage("ore viaggio non valide");
				return false;
			}
			return out;
		}


		document.getElementById("edit_form").ingresso.focus();
	<?}?>
</script>
