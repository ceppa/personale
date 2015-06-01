<?
switch($admin_action)
{
	case "add_trasf":
		$subtitle="nuova tipologia trasferta";
	case "edit_trasf":
		$conn=mysqli_connect($myhost, $myuser, $mypass)
			or die("Connessione non riuscita".mysqli_error($conn));
		((bool)mysqli_query($conn, "USE " . $dbname));
		$codici="";
		$query="SELECT trasferte.codice FROM trasferte WHERE trasferte.id<>'".$_GET["trasf_to_edit"]."'";
		$result=@mysqli_query($conn, $query)
			or die($query."<br>".mysqli_error($conn));
		while($row=mysqli_fetch_assoc($result))
			$codici.='"'.$row["codice"].'":"1",';
		$codici=rtrim($codici,",");
		((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
		$paesi=array(0=>"seleziona");
		$query="SELECT id,paese FROM trasferte_paesi
			ORDER BY paese";
		$result=@mysqli_query($conn, $query)
			or die($query."<br>".mysqli_error($conn));
		while($row=mysqli_fetch_assoc($result))
			$paesi[$row["id"]]=$row["paese"];
		((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);

		if(($admin_action=="edit_trasf")&&(!isset($valori)))
		{
			$query="SELECT trasferte.id,
					trasferte.codice,
					trasferte.codice2,
					trasferte.descrizione,
					trasferte.importo,
					trasferte.enabled,
					trasferte.divisa,
					trasferte.indennita,
					trasferte.paese_id,
					trasferte_paesi.paese
				FROM trasferte LEFT JOIN trasferte_paesi ON trasferte.paese_id=trasferte_paesi.id
					WHERE trasferte.id='".$_GET["trasf_to_edit"]."'";
			$result=@mysqli_query($conn, $query)
				or die($query."<br>".mysqli_error($conn));
			$valori=mysqli_fetch_assoc($result);
			((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
		}
		((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
		if(!isset($subtitle))
			$subtitle="modifica trasferta '".$valori["codice"]."'";
		logged_header($op,$_SESSION["nome"]." ".$_SESSION["cognome"]
				." - ".$_SESSION["ditta_rs"]
				,$subtitle);
		close_logged_header($_SESSION["livello"]);

		?>
		<div id="centra">
			<form action="<?=$self?>" id="edit_form" method="post"
				onsubmit="return check_post_trasf(this)">
				<input type="hidden" value="1" name="performAction">
		<?
		if($admin_action=="edit_trasf")
		{?>
				<input type="hidden" value="<?=$valori["id"]?>" name="id_trasf">
		<?}?>
				<table class="edit">
					<tr>
						<td>codice</td>
						<td>
							<input type="text" name="codice"
								size="20" maxlength="20"
								value="<?=safeString($valori["codice"])?>">
						</td>
					</tr>
					<tr>
						<td>paese</td>
						<td>
							<select class="input" name="paese_id">
		<?
			foreach($paesi as $id=>$paese)
			{?>
				<option value="<?=$id?>"<?=($id==$valori["paese_id"]?" selected":"")?>>
					<?=safeString($paese)?>
				</option>
			<?}?>
							</select>
						</td>
					</tr>
					<tr>
						<td>codice2</td>
						<td>
							<input type="text" name="codice2"
								size="20" maxlength="20"
								value="<?=safeString($valori["codice2"])?>">
						</td>
					</tr>
					<tr>
						<td>descrizione</td>
						<td>
							<input type="text" name="descrizione"
								size="30" maxlength="30"
								value="<?=safeString($valori["descrizione"])?>">
						</td>
					</tr>
					<tr>
						<td>importo</td>
						<td>
							<input type="text" name="importo"
								size="10" value="<?=$valori["importo"]?>"
								onchange="this.value=formattavaluta(this)">
						</td>
					</tr>
					<tr>
						<td>attiva</td>
						<td>
							<input type="checkbox" name="enabled"
								value="1" <?=$valori["enabled"]?"checked='checked'":""?>>
						</td>
					</tr>
					<tr>
						<td colspan="2" class="footer">
							<input type="submit" class="button"
								name="<?=$admin_action?>" value="accetta">
							&nbsp;
							<input type="button" class="button"
								onclick='javascript:redirect("<?=$self?>
									&amp;op=admin_trasf");'
								value="annulla">
						</td>
					</tr>
				</table>
			</form>
		</div>
		<?
		break;
	case "list_trasf":
		logged_header($op,$_SESSION["nome"]." ".$_SESSION["cognome"]
				." - ".$_SESSION["ditta_rs"]
				,"gestione trasferte");
		close_logged_header($_SESSION["livello"]);

		$conn=mysqli_connect($myhost, $myuser, $mypass)
			or die("Connessione non riuscita".mysqli_error($conn));
		((bool)mysqli_query($conn, "USE " . $dbname));
		$query="SELECT trasferte.*,trasferte_paesi.paese
			FROM trasferte LEFT JOIN trasferte_paesi
			ON trasferte.paese_id=trasferte_paesi.id
			ORDER BY trasferte.codice";
		$result=@mysqli_query($conn, $query)
			or die($query."<br>".mysqli_error($conn));
		?>
		<table class="edit">
			<tr class="table-header">
				<td>&nbsp;</td>
				<td>codice</td>
				<td>codice2</td>
				<td>descrizione</td>
				<td>paese</td>
				<td>importo</td>
				<td>attiva</td>
			</tr>
		<?
		while($row=mysqli_fetch_assoc($result))
		{?>
			<tr class="row_attivo">
				<td>
					<a href="<?=$self?>&amp;op=admin_trasf
							&amp;admin_action=edit_trasf
							&amp;trasf_to_edit=<?=$row["id"]?>">
						<img src="img/b_edit.png" alt="Edit" title="Edit">
					</a>
				</td>
				<td><?=$row["codice"]?></td>
				<td><?=$row["codice2"]?></td>
				<td><?=$row["descrizione"]?></td>
				<td><?=$row["paese"]?></td>
				<td><?=$row["importo"]?></td>
				<td><?=$row["enabled"]?"Si":"-"?></td>
			</tr>
		<?}?>
			<tr class="row_attivo">
				<td colspan="6">
					<a href="<?=$self?>&amp;op=admin_trasf&amp;admin_action=add_trasf">
						<img src="img/b_edit.png" alt="Nuova" title="Nuova">
						&nbsp;Nuova trasferta
					</a>
				</td>
			</tr>
		</table>
		<?
		break;
}?>
</div>
<?
if(($admin_action=="add_trasf")
	||($admin_action=="edit_trasf"))
{?>
	<script type="text/javascript">
		var codici={<?=$codici?>};
		function check_post_trasf(form)
		{
			out=true;
			if(trim(form.codice.value).length==0)
			{
				showMessage("codice non valido");
				return false;
			}
			if(codici[trim(form.codice.value)]!=null)
			{
				showMessage("codice gia' presente");
				return false;
			}
			return out;
		}

		document.getElementById("edit_form").codice.focus();
	</script>
<?}
?>
