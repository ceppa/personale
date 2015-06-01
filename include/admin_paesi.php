<?
switch($admin_action)
{
	case "add_paese":
		$subtitle="nuovo paese";
	case "edit_paese":
		$conn=mysqli_connect($myhost, $myuser, $mypass)
			or die("Connessione non riuscita".mysqli_error($conn));
		((bool)mysqli_query($conn, "USE " . $dbname));
		$paesi="";
		$query="SELECT paese FROM
			trasferte_paesi WHERE id<>'".@$_GET["paese_to_edit"]."'";
		$result=@mysqli_query($conn, $query)
			or die($query."<br>".mysqli_error($conn));
		while($row=mysqli_fetch_assoc($result))
			$paesi.='"'.$row["paese"].'":"1",';
		$paesi=rtrim($paesi,",");
		((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
		if(($admin_action=="edit_paese")&&(!isset($valori)))
		{
			$query="SELECT trasferte_paesi.* FROM trasferte_paesi
				WHERE trasferte_paesi.id='".$_GET["paese_to_edit"]."'";
			$result=@mysqli_query($conn, $query)
				or die($query."<br>".mysqli_error($conn));
			$valori=mysqli_fetch_assoc($result);
			((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
		}
		((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
		if(!isset($subtitle))
			$subtitle="modifica paese '".@$valori["paese"]."'";
		logged_header($op,$_SESSION["nome"]." ".$_SESSION["cognome"]
				." - ".$_SESSION["ditta_rs"]
				,$subtitle);
		close_logged_header($_SESSION["livello"]);

		?>
		<div id="centra">
			<form action="<?=$self?>" id="edit_form" method="post"
				onsubmit="return check_post_paese(this)">
				<input type="hidden" value="1" name="performAction">
		<?
		if($admin_action=="edit_paese")
		{?>
				<input type="hidden" value="<?=$valori["id"]?>" name="id_paese">
		<?}?>
				<table class="edit">
					<tr>
						<td>paese</td>
						<td>
							<input type="text" name="paese"
								size="20" maxlength="20"
								value="<?=safeString(@$valori["paese"])?>">
						</td>
					</tr>
					<tr>
						<td>variante</td>
						<td>
							<input type="text" name="variante"
								size="20" maxlength="20"
								value="<?=safeString(@$valori["variante"])?>">
						</td>
					</tr>
					<tr>
						<td>divisa</td>
						<td>
							<input type="text" name="divisa"
								size="10" maxlength="10"
								value="<?=safeString(@$valori["divisa"])?>">
						</td>
					</tr>
					<tr>
						<td>conguaglio ord</td>
						<td>
							<input type="text" name="ord"
								size="10" maxlength="10"
								value="<?=safeString(@$valori["ord"])?>">
						</td>
					</tr>
					<tr>
						<td>conguaglio base</td>
						<td>
							<input type="text" name="base"
								size="10" maxlength="10"
								value="<?=safeString(@$valori["base"])?>">
						</td>
					</tr>
					<tr>
						<td>conguaglio fest</td>
						<td>
							<input type="text" name="fest"
								size="10" maxlength="10"
								value="<?=safeString(@$valori["fest"])?>">
						</td>
					</tr>
					<tr>
						<td colspan="2" class="footer">
							<input type="submit" class="button"
								name="<?=$admin_action?>" value="accetta">
							&nbsp;
							<input type="button" class="button"
								onclick='javascript:redirect("<?=$self?>&amp;op=admin_paesi");'
								value="annulla">
						</td>
					</tr>
				</table>
			</form>
		</div>
		<?
		break;
	case "list_paesi":
		logged_header($op,$_SESSION["nome"]." ".$_SESSION["cognome"]
				." - ".$_SESSION["ditta_rs"]
				,"gestione paesi");
		close_logged_header($_SESSION["livello"]);

		$conn=mysqli_connect($myhost, $myuser, $mypass)
			or die("Connessione non riuscita".mysqli_error($conn));
		((bool)mysqli_query($conn, "USE " . $dbname));
		$query="SELECT id,paese,variante,divisa,ord,base,fest
			FROM trasferte_paesi
			ORDER BY paese,variante";
		$result=@mysqli_query($conn, $query)
			or die($query."<br>".mysqli_error($conn));
		?>
		<table class="edit">
			<tr class="table-header">
				<td>&nbsp;</td>
				<td>paese</td>
				<td>variante</td>
				<td>divisa</td>
				<td>ord</td>
				<td>base</td>
				<td>fest</td>
			</tr>
		<?
		while($row=mysqli_fetch_assoc($result))
		{?>
			<tr class="row_attivo">
				<td>
					<a href="<?=$self?>&amp;op=admin_paesi
							&amp;admin_action=edit_paese
							&amp;paese_to_edit=<?=$row["id"]?>">
						<img src="img/b_edit.png" alt="Edit" title="Edit">
					</a>
				</td>
				<td><?=$row["paese"]?></td>
				<td><?=$row["variante"]?></td>
				<td><?=$row["divisa"]?></td>
				<td><?=$row["ord"]?></td>
				<td><?=$row["base"]?></td>
				<td><?=$row["fest"]?></td>
			</tr>
		<?}?>
			<tr class="row_attivo">
				<td colspan="7">
					<a href="<?=$self?>&amp;op=admin_paesi&amp;admin_action=add_paese">
						<img src="img/b_edit.png" alt="Nuovo" title="Nuovo">
						&nbsp;Nuovo paese
					</a>
				</td>
			</tr>
		</table>
		<?
		break;
}?>
</div>
<?
if(($admin_action=="add_paese")
	||($admin_action=="edit_paese"))
{?>
	<script type="text/javascript">
		var paesi={<?=$paesi?>};
		function check_post_paese(form)
		{
			out=true;
			if(trim(form.paese.value).length==0)
			{
				showMessage("paese non valido");
				return false;
			}
			if(paesi[trim(form.paese.value)]!=null)
			{
				showMessage("paese gia' presente");
				return false;
			}
			return out;
		}

		document.getElementById("edit_form").paese.focus();
	</script>
<?}
?>
