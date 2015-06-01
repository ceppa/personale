<?
if(isset($_GET["foglio_action"]))
{
	$foglio_action=$_GET["foglio_action"];
	$subtitle=($foglio_action=="add_foglio"?"nuovo foglio viaggio":
		"modifica foglio viaggio");
}
else
{
	$foglio_action="list_fogli";
	$subtitle="gestione fogli viaggio";
}

logged_header($op,$_SESSION["nome"]." ".$_SESSION["cognome"].
				" - ".$_SESSION["ditta_rs"],
				$subtitle);
close_logged_header($_SESSION["livello"]);

switch($foglio_action)
{
	case "edit_foglio":
	case "add_foglio":
		$show_commessa=true;
		$conn=mysqli_connect($myhost, $myuser, $mypass)
			or die("Connessione non riuscita".mysqli_error($conn));
		((bool)mysqli_query($conn, "USE " . $dbname));
		$giorni_ord=0;
		$giorni_base=0;
		$giorni_fest=0;

		if((!isset($valori))&&($foglio_action=="edit_foglio"))
		{
			$query="SELECT fogliviaggio.* FROM fogliviaggio
				WHERE fogliviaggio.id_foglio=".$_GET["foglio_to_edit"];
			$result=@mysqli_query($conn, $query)
				or die($query."<br>".mysqli_error($conn));
			$valori=mysqli_fetch_assoc($result);
			if(count($valori)==0)
				die();
			((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
			if($valori["id_commessa"]==0)
				$show_commessa=false;

			$giorni_ord=(isset($valori["giorni_ord"])?$valori["giorni_ord"]:0);
			$giorni_base=(isset($valori["giorni_base"])?$valori["giorni_base"]:0);
			$giorni_fest=(isset($valori["giorni_fest"])?$valori["giorni_fest"]:0);
			if($valori["datafine"]>"2013-09-01")
			{
				$giorni_ord=(int)$giorni_ord;
				$giorni_base=(int)$giorni_base;
				$giorni_fest=(int)$giorni_fest;
			}
			$valori["datainizio"]=($valori["datainizio"]!="0000-00-00"?
									my_date_format($valori["datainizio"],"d/m/Y"):"----");
			$valori["datafine"]=($valori["datafine"]!="0000-00-00"?
									my_date_format($valori["datafine"],"d/m/Y"):"----");
			$valori["orainizio"]=int_to_hour($valori["orainizio"]);
			$valori["oreviaggioand"]=int_to_hour($valori["oreviaggioand"]);
			$valori["orafine"]=int_to_hour($valori["orafine"]);
			$valori["oreviaggiorit"]=int_to_hour($valori["oreviaggiorit"]);


			$query="SELECT fatt_ric,descrizione,importo,importo_divisa FROM fogliviaggio_dettagliospese
					WHERE id_foglio=".$_GET["foglio_to_edit"]." ORDER BY riga";
			$result=@mysqli_query($conn, $query)
				or die($query."<br>".mysqli_error($conn));
			$conta=0;
			while($row=mysqli_fetch_assoc($result))
			{
				$valori["desc_$conta"]=$row["descrizione"];
				$valori["imp_$conta"]=$row["importo"];
				$valori["impdivisa_$conta"]=$row["importo_divisa"];
				if($valori["imp_$conta"]>0)
					$valori["impdivisa_$conta"]="";
				elseif($valori["impdivisa_$conta"]>0)
					$valori["imp_$conta"]="";
				$valori["fatt_ric_$conta"]=$row["fatt_ric"];
				$conta++;
			}
			((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
		}
		$query="SELECT trasferte.descrizione,
				trasferte_paesi.paese,
				trasferte_paesi.variante,
				trasferte_paesi.divisa,
				trasferte_paesi.ord,
				trasferte_paesi.base,
				trasferte_paesi.fest
				FROM trasferte LEFT JOIN trasferte_paesi
					ON trasferte.paese_id=trasferte_paesi.id
					ORDER BY trasferte_paesi.paese";
		$result=@mysqli_query($conn, $query)
			or die($query."<br>".mysqli_error($conn));
		$misto=array();
		$diaria=array();
		$array_escl_ord=array();
		$array_escl_base=array();
		$array_escl_fest=array();
		$array_non_euro=array();
		while($row=mysqli_fetch_assoc($result))
		{
			if(strlen($paese=$row["paese"]))
			{
				if(strlen($row["variante"]))
					$paese=sprintf("%s %s",$paese,trim($row["variante"]));
				$paesi[]=$paese;

				if(strlen($row["base"])==0)
					$array_escl_base[$paese]=1;
				if(strlen($row["ord"])==0)
					$array_escl_ord[$paese]=1;
				if(strlen($row["fest"])==0)
					$array_escl_fest[$paese]=1;

				if($row["divisa"]!="€")
					$array_non_euro[$paese]=$row["divisa"];

				if(strstr($row["descrizione"],"MISTA"))
					$misto[]=$paese;
				elseif(strstr($row["descrizione"],"DIARIA"))
					$diaria[]=$paese;
			}
		}
		$lista_escl_fest="";
		foreach($array_escl_fest as $paese=>$foo)
			$lista_escl_fest.="\"$paese\",";
		$lista_escl_fest=rtrim($lista_escl_fest,",");
		$lista_escl_ord="";
		foreach($array_escl_ord as $paese=>$foo)
			$lista_escl_ord.="\"$paese\",";
		$lista_escl_ord=rtrim($lista_escl_ord,",");
		$lista_escl_base="";
		foreach($array_escl_base as $paese=>$foo)
			$lista_escl_base.="\"$paese\",";
		$lista_escl_base=rtrim($lista_escl_base,",");
		$lista_non_euro="";
		foreach($array_non_euro as $paese=>$divisa)
			$lista_non_euro.="\"$paese\":\"$divisa\",";
		$lista_non_euro=rtrim($lista_non_euro,",");

		$misto=array_flip($misto);
		$diaria=array_flip($diaria);
		$paesi=array_flip(array_flip($paesi));
		((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);

		$commesse=array(0=>"--seleziona--");
		$query="SELECT * FROM commesse WHERE visibile=1 ORDER BY commessa";
		$result=@mysqli_query($conn, $query)
			or die($query."<br>".mysqli_error($conn));
		while($row=mysqli_fetch_assoc($result))
			$commesse[$row["id"]]=$row["commessa"]." - ".$row["descrizione"];

		((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
		((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);


		?>
		<div id="centra">
			<form action="<?=$self?>" method="post" id="edit_form"
				onsubmit="return check_post_foglio(this)">
		<?
		if($foglio_action=="edit_foglio")
		{?>
				<input type="hidden" name="anno_prec" value="<?=$valori["anno"]?>">
				<input type="hidden" name="id_foglio" value="<?=$_GET["foglio_to_edit"]?>">
		<?}?>
				<input type="hidden" name="performAction" value="1">
				<table class="edit">
					<tr>
						<td>localit&agrave; (max 20 car.)</td>
						<td>
							<input type="text" name="loc" id="loc" size="20"
								maxlength="20"
								value="<?=safeString(@$valori["loc"])?>">
						</td>
					</tr>
					<tr>
						<td>paese</td>
						<td>
							<select name="paese" id="paese"
								onchange="showHideOrdBase()" >
		<?
		foreach($paesi as $paese)
		{?>
								<option value="<?=safeString($paese)?>"<?=(@$valori["paese"]==$paese?" selected":"")?>>
									<?=safeString($paese)?>
								</option>
		<?}?>
							</select>
						</td>
					</tr>
					<tr id="row_cambio">
						<td>cambio valuta attuale: 1 <span id="label_cambio"></span> =</td>
						<td>
							<input type="text" id="input_cambio" name="cambio" size="10"
								value="<?=@$valori["cambio"]?>" > Euro
						</td>
					</tr>
					<tr>
						<td>data inizio (gg/mm/aaaa)</td>
						<td>
							<input type="text" id="datainizio" name="datainizio" size="10"
								value="<?=@$valori["datainizio"]?>">
						</td>
					</tr>
					<tr>
						<td>ora inizio (h:mm)</td>
						<td>
							<input type="text" id="orainizio" name="orainizio" size="5"
								value="<?=@$valori["orainizio"]?>" >
<!--								onChange="
										if(is_hour(this.value))
											this.value=formattaora(this);
										document.getElementById('edit_form').oreviaggioand.value=
												viaggioand(document.getElementById('edit_form').orainizio,495);
										">-->

						</td>
					</tr>
					<tr>
						<td>data fine (gg/mm/aaaa)</td>
						<td>
							<input type="text" id="datafine" name="datafine" size="10"
								value="<?=@$valori["datafine"]?>">
						</td>
					</tr>
					<tr>
						<td>ora fine (h:mm)</td>
						<td>
							<input type="text" id="orafine" name="orafine" size="5"
								value="<?=@$valori["orafine"]?>" >
<!--								onChange="
										if(is_hour(this.value))
											this.value=formattaora(this);
										document.getElementById('edit_form').oreviaggiorit.value=
											viaggiorit(document.getElementById('edit_form').orafine,1020);
										">-->
						</td>
					</tr>
					<tr>
						<td>giorni</td>
						<td>
							<input type="text" name="giorni_cell" id="giorni_cell"
								readonly="readonly" size="3">
						</td>
					</tr>
					<tr>
						<td>motivo (max 20 car.)</td>
						<td>
							<input type="text" name="motivo" size="20"
								maxlength="20"
								value="<?=safeString(@$valori["motivo"])?>">
						</td>
					</tr>
					<tr>
						<td>pie di lista</td>
						<td>
							<input type="checkbox" id="piedilista"
								name="piedilista" <?=(@$valori["piedilista"]==1?"checked='checked'":"")?>>
						</td>
					</tr>
<?
if($show_commessa)
{?>
					<tr>
						<td>commessa</td>
						<td>
							<select class="input" name="commessa">
					<?
					foreach($commesse as $commessa_id=>$commessa_text)
					{?>
								<option value="<?=$commessa_id?>"<?=($commessa_id==@$valori["id_commessa"]?" selected":"")?>>
									<?=$commessa_text?>
								</option>
					<?}?>
							</select>
						</td>
					</tr>
<?
}?>
					<tr id="ord">
						<td>giorni trasferta ordinaria</td>
						<td>
							<input type="text" id="input_ord" name="giorni_ord" size="2"
								value="<?=$giorni_ord?>">
						</td>
					</tr>
					<tr id="base">
						<td>giorni trasferta in base</td>
						<td>
							<input type="text" id="input_base" name="giorni_base" size="2"
								value="<?=$giorni_base?>">
						</td>
					</tr>
					<tr id="fest">
						<td id="festiva">giorni trasferta festiva</td>
						<td>
							<input type="text" id="input_fest" name="giorni_fest" size="2"
								value="<?=$giorni_fest?>">
						</td>
					</tr>
					<tr>
						<td colspan="2" class="header">
							Spese sostenute
						</td>
					</tr>
					<tr>
						<td colspan="2" class="nopadding">
							<table id="table_detail">
								<tr>
									<td class="header">Descrizione</td>
									<td class="header">Importo <span id="label_euro">Euro</span></td>
									<td class="header">Importo <span id="label_divisa"></span></td>
									<td class="header">Fatt/Ric</td>
								</tr>
					<?
						for($i=0;$i<DETTAGLIO_SPESE_LINES;$i++)
						{?>
								<tr>
									<td>
										<input type="text" name="desc_<?=$i?>" id="desc_<?=$i?>"
											maxlength="40" size="40" value="<?=safeString(@$valori["desc_$i"])?>"
											onchange="if(trim(this.value).length==0)
													{
														document.getElementById('imp_<?=$i?>').value=''
														document.getElementById('impdivisa_<?=$i?>').value=''
													}
													">
									</td>
									<td>
										<input type="text" name="imp_<?=$i?>" id="imp_<?=$i?>"
											onchange="imp_change(this)"
											size="10" value="<?=@$valori["imp_$i"]?>">
									</td>
									<td>
										<input type="text" name="impdivisa_<?=$i?>" id="impdivisa_<?=$i?>"
											onchange="impdivisa_change(this)"
											size="10" value="<?=@$valori["impdivisa_$i"]?>">
									</td>
									<td>
										<select class="input" name="fatt_ric_<?=$i?>">
											<option value="R"<?=(@$valori["fatt_ric_$i"]=="R"?" selected":"")?>>
												Ricevuta
											</option>
											<option value="F"<?=(@$valori["fatt_ric_$i"]=="F"?" selected":"")?>>
												Fattura
											</option>
										</select>
									</td>
								</tr>
						<?}?>



							</table>
						</td>
					</tr>
					<tr>
						<td colspan="2" class="footer">
							<input type="submit" class="button"
								name="<?=$foglio_action?>" value="accetta">
							&nbsp;
							<input type="button" class="button"
								onclick="javascript:redirect('<?=$self?>&amp;op=foglio_viaggio');"
								value="annulla">
						</td>
					</tr>
				</table>
			</form>
		</div>

	<script type="text/javascript">
	</script>

		<?
		break;
	default:
		$conn=mysqli_connect($myhost, $myuser, $mypass)
			or die("Connessione non riuscita".mysqli_error($conn));
		((bool)mysqli_query($conn, "USE " . $dbname));
		$query="SELECT * FROM fogliviaggio
			WHERE id_utente='".$_SESSION['id_edit']."'
			ORDER BY datainizio DESC";
		$result=@mysqli_query($conn, $query)
			or die($query."<br>".mysqli_error($conn));
		?>
		<table class="edit">
			<tr class="table-header">
				<td colspan="2">&nbsp;</td>
				<td>paese</td>
				<td>località</td>
				<td>data inizio</td>
				<td>data fine</td>
			</tr>
		<?
		while($row=mysqli_fetch_assoc($result))
		{
			$locked=$row["locked"];
			$is_admin=0;
			if($_SESSION["livello"]==1)
				$is_admin=1;
			$row_class=($locked?"row_inattivo":"row_attivo");

		?>
			<tr class="<?=$row_class?>">
				<td>
		<?
			if(($is_admin)||(!$locked))
			{?>
					<a href="<?=$self?>&amp;op=foglio_viaggio
							&amp;foglio_action=edit_foglio
							&amp;foglio_to_edit=<?=$row["id_foglio"]?>">
						<img src="img/b_edit.png" alt="Edit" title="Edit">
					</a>
			<?}
			if($is_admin)
			{
				$foglio_action=($locked?"lock_off":"lock_on");
				?>
					<a href="<?=$self?>&amp;op=pa_foglio_viaggio
							&amp;foglio_action=<?=$foglio_action?>
							&amp;foglio_to_edit=<?=$row["id_foglio"]?>">
						<img src="img/<?=($locked?"lock-icon.png"
								:"lock-off-icon.png")?>"
								alt="<?=($locked?"unlock":"lock")?>"
								title="<?=($locked?"unlock":"lock")?>">
					</a>
				<?
			}?>
				</td>
				<td>
					<a href="<?=$self?>&amp;op=stampa_foglio&amp;foglio_to_print=<?=$row["id_foglio"]?>"
							target="_blank">
						<img src="img/b_print.png" alt="Stampa" title="Stampa">
					</a>
				</td>
				<td><?=$row["paese"]?></td>
				<td><?=$row["loc"]?></td>
				<td><?=($row["datainizio"]!="0000-00-00"?my_date_format($row["datainizio"],"d/m/Y"):"----")?></td>
				<td><?=($row["datafine"]!="0000-00-00"?my_date_format($row["datafine"],"d/m/Y"):"----")?></td>
			</tr>
		<?}?>
			<tr class="row_attivo">
				<td colspan="6">
					<a href="<?=$self?>&amp;op=foglio_viaggio&amp;foglio_action=add_foglio">
						<img src="img/b_edit.png" alt="Nuovo" title="Nuovo">
						&nbsp;Nuovo foglio viaggio
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
if(($foglio_action=="add_foglio")
	||($foglio_action=="edit_foglio"))

	require_once("js/foglio_viaggio.js");
?>
