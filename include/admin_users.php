<?
switch($admin_action)
{
	case "edit_user":
	case "add_user":
		$conn=mysqli_connect($myhost, $myuser, $mypass)
			or die("Connessione non riuscita".mysqli_error($conn));
		((bool)mysqli_query($conn, "USE " . $dbname));
		$query="SELECT ditte.* FROM ditte";
		$ditte=array();
		$ditte[-1]="nessuna";
		$result=@mysqli_query($conn, $query)
			or die($query."<br>".mysqli_error($conn));
		while($row=mysqli_fetch_assoc($result))
			$ditte[$row["id"]]=$row["ragione_sociale"];
		((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
		$query="SELECT commesse.* FROM commesse WHERE commesse.visibile=1 order by commesse.commessa";
		$commesse=array();
		$commesse[-1]="----";
		$result=@mysqli_query($conn, $query)
			or die($query."<br>".mysqli_error($conn));
		while($row=mysqli_fetch_assoc($result))
			$commesse[$row["id"]]=$row["descrizione"];
		((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);

		$utenti="";
		$query="SELECT login FROM utenti
				WHERE utenti.id<>'".$_GET["user_to_edit"]."'";
		$result=@mysqli_query($conn, $query)
			or die($query."<br>".mysqli_error($conn));
		while($row=mysqli_fetch_assoc($result))
			$utenti.='"'.$row["login"].'":"1",';
		$utenti=rtrim($utenti,",");
		((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);

		if(($admin_action=="edit_user")&&(!isset($valori)))
		{
			$query="SELECT utenti.*,utenti_trasferte.tr_destinazione,
					utenti_trasferte.tr_motivo,utenti_trasferte.tr_ind_forf,
					utenti_trasferte.tr_km
				FROM utenti LEFT JOIN utenti_trasferte
				ON utenti.id=utenti_trasferte.id
				WHERE utenti.id='".$_GET["user_to_edit"]."'";
			$result=@mysqli_query($conn, $query)
				or die($query."<br>".mysqli_error($conn));
			$valori=mysqli_fetch_assoc($result);
			$valori["data_inizio_coll"]=my_date_format($valori["data_inizio_coll"],"d/m/Y");
			$valori["data_fine_coll"]=my_date_format($valori["data_fine_coll"],"d/m/Y");
			$valori["blocca_a_data"]=my_date_format($valori["blocca_a_data"],"d/m/Y");
			$subtitle="modifica utente '".$valori["cognome"]." ".$valori["nome"]."'";
		}
		elseif($admin_action=="add_user")
		{
			if(!isset($valori["livello"]))
				$valori["livello"]=0;
			if(!isset($valori["data_fine_coll"]))
				$valori["data_fine_coll"]="----";
			if(!isset($valori["data_inizio_coll"]))
				$valori["data_inizio_coll"]="----";
			$subtitle="aggiungi utente";
		}
		((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);

		logged_header($op,$_SESSION["nome"]." ".
			$_SESSION["cognome"]." - ".$_SESSION["ditta_rs"],
			$subtitle);
		close_logged_header($_SESSION["livello"]);

		?>

		<form action="<?=$self?>" id="edit_form" method="post"
			onsubmit="return check_post_user(this)">
			<div id="centra">
		<?
		if($admin_action=="edit_user")
		{?>
				<input type="hidden" value="<?=$valori["id"]?>" name="id_user">
		<?}?>
				<input type="hidden" value="1" name="performAction">
				<table class="edit">
					<tr>
						<td>login</td>
						<td>
							<input type="text" name="utente" size="15" value="<?=safeString($valori["login"])?>">
						</td>
					</tr>
					<tr>
						<td>nome</td>
						<td>
							<input type="text" name="nome" size="15" value="<?=safeString($valori["nome"])?>">
						</td>
					</tr>
					<tr>
						<td>cognome</td>
						<td>
							<input type="text" name="cognome" size="15" value="<?=safeString($valori["cognome"])?>">
						</td>
					</tr>
					<tr>
						<td>livello</td>
						<td>
							<select name="livello">
					<?
					foreach($livelli as $liv_id=>$liv_text)
					{?>
								<option value="<?=$liv_id?>"<?=($liv_id==$valori["livello"]?" selected":"")?>>
									<?=$liv_text?>
								</option>
					<?}?>
							</select>
						</td>
					</tr>
					<tr>
						<td>data inizio</td>
						<td>
							<input type="text" name="data_inizio_coll"
								size="10" maxlength="10" value="<?=$valori["data_inizio_coll"]?>"
								onchange="this.value=formattadata(this.value)">
						</td>
					</tr>
					<tr>
						<td>data termine</td>
						<td>
							<input type="text" name="data_fine_coll"
								size="10" maxlength="10" value="<?=$valori["data_fine_coll"]?>"
								onchange="this.value=formattadata(this.value)">
						</td>
					</tr>
					<?
					if($admin_action=="edit_user")
					{?>
					<tr>
						<td>congelato al</td>
						<td>
							<input type="text" name="blocca_a_data" size="10"
								maxlength="10" value="<?=$valori["blocca_a_data"]?>"
								onchange="this.value=formattadata(this.value)">
						</td>
					</tr>
					<?}?>
					<tr>
						<td>patrono</td>
						<td>
							<input type="text" name="patrono" size="4"
								maxlength="4" value="<?=$valori["patrono"]?>"
								onchange="this.value=trim(this.value)">
						</td>
					</tr>
					<tr>
						<td>ingresso default</td>
						<td>
							<input type="text" name="ingresso_def" size="5"
								maxlength="5" value="<?=int_to_hour($valori["ingresso_def"])?>"
								onchange="this.value=formattaora(this)">
						</td>
					</tr>
					<tr>
						<td>uscita default</td>
						<td>
							<input type="text" name="uscita_def" size="5"
								maxlength="5" value="<?=int_to_hour($valori["uscita_def"])?>"
								onchange="this.value=formattaora(this)">
						</td>
					</tr>
					<tr>
						<td>pausa default</td>
						<td>
							<input type="text" name="pausa_def" size="5"
								maxlength="5" value="<?=int_to_hour($valori["pausa_def"])?>"
								onchange="this.value=formattaora(this)">
						</td>
					</tr>
					<tr>
						<td>rimborso al km default</td>
						<td>
							<input type="text" name="tr_euri_km" size="10"
								maxlength="10" value="<?=$valori["tr_euri_km"]?>"
								onchange="this.value=formattavaluta(this)">
						</td>
					</tr>
					<tr>
						<td>ditta</td>
						<td>
							<select name="ditta">
					<?
					foreach($ditte as $ditta_id=>$ditta_text)
					{?>
								<option value="<?=$ditta_id?>"<?=($ditta_id==$valori["ditta"]?" selected":"")?>>
									<?=$ditta_text?>
								</option>
					<?}?>
							</select>
						</td>
					</tr>
					<tr style="display:none">
						<td>commessa</td>
						<td>
							<select class="input" name="commessa">
					<?
					foreach($commesse as $commessa_id=>$commessa_text)
					{?>
								<option value="<?=$commessa_id?>"<?=($commessa_id==$valori["commessa_default"]?" selected":"")?>>
									<?=$commessa_text?>
								</option>
					<?}?>
							</select>
						</td>
					</tr>
					<tr>
						<td>destinazione trasferta</td>
						<td>
							<input type="text" class="input" size="30"
								maxlength="30" name="tr_destinazione"
								value="<?=safeString($valori["tr_destinazione"])?>">
						</td>
					</tr>
					<tr>
						<td>motivo trasferta</td>
						<td>
							<input type="text" class="input" size="30"
								maxlength="30" name="tr_motivo"
								value="<?=safeString($valori["tr_motivo"])?>">
						</td>
					</tr>
					<tr>
						<td>indennit&agrave; forfettaria</td>
						<td>
							<input type="text" class="input" size="8"
								name="tr_ind_forf" value="<?=($valori["tr_ind_forf"])?>">
						</td>
					</tr>
					<tr>
						<td>km trasferta</td>
						<td>
							<input type="text" class="input" size="8"
								name="tr_km" value="<?=($valori["tr_km"])?>">
						</td>
					</tr>
					<tr>
						<td>solo trasferte</td>
						<td>
							<input type="checkbox" class="check"
								name="solo_trasf"<?=(($valori["solo_trasf"]==1)?" checked":"")?>>
						</td>
					</tr>
					<?if($admin_action=="edit_user")
					{?>
					<tr>
						<td>expired</td>
						<td>
							<input type="checkbox" class="check" name="expired"<?=(($valori["expired"]==1)?" checked":"")?>>
						</td>
					</tr>
					<tr>
						<td>attivo</td>
						<td>
							<input type="checkbox" class="check" name="attivo"<?=(($valori["attivo"]==1)?" checked":"")?>>
						</td>
					</tr>
					<tr>
						<td>buoni pasto</td>
						<td>
							<input type="checkbox" class="check" name="buoni_pasto"<?=(($valori["buoni_pasto"]==1)?" checked":"")?>>
						</td>
					</tr>
					<tr>
						<td>stampe</td>
						<td>
							<input type="checkbox" class="check" name="stampe"<?=(($valori["stampe"]==1)?" checked":"")?>>
						</td>
					</tr>
					<?}?>
					<tr>
						<td colspan="2" class="footer">
							<input type="submit" class="button"
								name="<?=$admin_action?>" value="accetta">
							&nbsp;
							<input type="button" class="button" onclick="
								javascript:redirect('<?=$self?>&amp;op=admin_users');"
								value="annulla">
						</td>
					</tr>
				</table>
			</div>
		</form>
		<?
		break;
	case "list_users":
		logged_header($op,$_SESSION["nome"]." ".$_SESSION["cognome"].
				" - ".$_SESSION["ditta_rs"],
				"gestione utenti");
		close_logged_header($_SESSION["livello"]);

		$conn=mysqli_connect($myhost, $myuser, $mypass)
			or die("Connessione non riuscita".mysqli_error($conn));
		((bool)mysqli_query($conn, "USE " . $dbname));
		$query="SELECT ditte.* FROM ditte";
		$ditte=array();
		$ditte[-1]="nessuna";
		$result=@mysqli_query($conn, $query)
			or die($query."<br>".mysqli_error($conn));
		while($row=mysqli_fetch_assoc($result))
			$ditte[$row["id"]]=$row["ragione_sociale"];
		((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
		$query="SELECT commesse.* FROM commesse WHERE commesse.visibile=1 order by commesse.commessa";
		$commesse=array();
		$commesse[-1]="----";
		$result=@mysqli_query($conn, $query)
			or die($query."<br>".mysqli_error($conn));
		while($row=mysqli_fetch_assoc($result))
			$commesse[$row["id"]]=$row["descrizione"];
		((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);

		if($_SESSION["livello"]!=2)
			$query="SELECT * FROM utenti WHERE eliminato=0 ORDER BY ditta,cognome";
		else
			$query="SELECT * FROM utenti WHERE eliminato=0 AND attivo=1 ORDER BY ditta,cognome";
		$result=@mysqli_query($conn, $query)
			or die($query."<br>".mysqli_error($conn));
		?>
		<table class="edit" style="font-size:80%;">
			<tr class="table-header">
		<?
		if($_SESSION["livello"]!=2)
		{?>
				<td colspan="3">&nbsp;</td>
		<?}?>
				<td>login</td>
				<td>nome</td>
				<td>cognome</td>
		<?
		if($_SESSION["livello"]!=2)
		{?>
				<td>livello</td>
				<td>expired</td>
				<td>attivo</td>
				<td>data inizio</td>
				<td>data termine</td>
				<td>congelato al</td>
				<td>patrono</td>
		<?}?>
				<td>ditta</td>
		<?
		if($_SESSION["livello"]!=2)
		{?>
				<td style="display:none">commessa</td>
				<td>stampe</td>
				<td>buoni</td>
		<?}?>
			</tr>
		<?
		while($row=mysqli_fetch_assoc($result))
		{
			$row_class=(($row["attivo"]==1)?"row_attivo":"row_inattivo");
			?>
			<tr class="<?=$row_class?>">
			<?
			if($_SESSION["livello"]!=2)
			{?>
				<td>
					<a href="<?=$self?>&amp;op=admin_users&amp;admin_action=edit_user&amp;user_to_edit=<?=$row["id"]?>">
						<img src="img/b_edit.png" alt="Edit" title="Edit">
					</a>
				</td>
				<td>
					<a href="#" onclick="MsgOkCancel('Elimino utente <?=$row["login"]?>?',
								'<?=$self?>&amp;op=pa_del_user&amp;user_to_del=<?=$row["id"]?>');">
						<img src="img/b_drop.png" alt="Elimina" title="Elimina">
					</a>
				</td>
				<td>
					<a href="#" onclick="MsgOkCancel('Resetto la password di <?=$row["nome"]?> <?=$row["cognome"]?>?',
								'<?=$self?>&amp;op=pa_reset_user&amp;user_to_reset=<?=$row["id"]?>');">
						<img src="img/b_reset.png" alt="Resetta password" title="Resetta password">
					</a>
				</td>
			<?
			}?>
				<td>
					<a href="<?=$self?>&amp;op=display&amp;id_edit=<?=$row["id"]?>">
						<?=$row["login"]?>
					</a>
				</td>
				<td><?=$row["nome"]?></td>
				<td><?=$row["cognome"]?></td>
			<?
			if($_SESSION["livello"]!=2)
			{?>
				<td><?=$livelli[$row["livello"]]?></td>
				<td><?=($row["expired"]==1?"si":"no")?></td>
				<td><?=($row["attivo"]==1?"si":"no")?></td>
				<td><?=($row["data_inizio_coll"]!="0000-00-00"?my_date_format($row["data_inizio_coll"],"d/m/Y"):"----")?></td>
				<td><?=($row["data_fine_coll"]!="0000-00-00"?my_date_format($row["data_fine_coll"],"d/m/Y"):"----")?></td>
				<td><?=($row["blocca_a_data"]!="0000-00-00"?my_date_format($row["blocca_a_data"],"d/m/Y"):"----")?></td>
				<td><?=$row["patrono"]?></td>
			<?}?>
			<td><?=$ditte[$row["ditta"]]?></td>
			<?
			if($_SESSION["livello"]!=2)
			{?>
				<td style="display:none"><?=$commesse[$row["commessa_default"]]?></td>
				<td><?=($row["stampe"]==1?"si":"no")?></td>
				<td><?=($row["buoni_pasto"]==1?"si":"no")?></td>
			<?}?>
			</tr>
		<?}
		if($_SESSION["livello"]!=2)
		{?>
			<tr class="row_attivo">
				<td colspan="16">
					<a href="<?=$self?>&amp;op=admin_users&amp;admin_action=add_user">
						<img src="img/b_edit.png" alt="Nuovo" title="Nuovo">
						&nbsp;Nuovo utente
					</a>
				</td>
			</tr>
		<?}?>
		</table>
		<?
		break;
}
?>
</div>
<?
if(($admin_action=="add_user")
	||($admin_action=="edit_user"))
{?>
	<script type="text/javascript">
		var utenti={<?=$utenti?>};
		function check_post_user(form)
		{
			var out=true;

			if((form.patrono.value.length>0)&&
				((form.patrono.value.length!=4)||(!is_number(form.patrono.value))))
			{
				showMessage("patrono deve essere 'mmyy'");
				return false;
			}
			if(!is_number(form.tr_euri_km.value))
			{
				showMessage("rimborso al km non valido");
				return false;
			}
			if(trim(form.utente.value).length==0)
			{
				showMessage("utente non valido");
				return false;
			}
			if(utenti[trim(form.utente.value)]!=null)
			{
				showMessage("Utente gia' presente");
				return false;
			}
			if((form.data_inizio_coll.value!="----")
				&&(!is_date(form.data_inizio_coll.value)))
			{
				showMessage("Data inizio non valida");
				return false;
			}
			if((form.data_fine_coll.value!="----")
				&&(!is_date(form.data_fine_coll.value)))
			{
				showMessage("Data termine non valida");
				return false;
			}
			if((form.blocca_a_data.value!="----")
				&&(!is_date(form.blocca_a_data.value)))
			{
				showMessage("Data blocco non valida");
				return false;
			}
			return out;
		}

		document.getElementById("edit_form").utente.focus();
	</script>
<?}
?>
