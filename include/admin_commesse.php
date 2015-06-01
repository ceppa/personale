<?
switch($admin_action)
{
	case "add_commessa":
		$subtitle="nuova commessa";
	case "edit_commessa":
		$conn=mysqli_connect($myhost, $myuser, $mypass)
			or die("Connessione non riuscita ".mysqli_error($conn));
		((bool)mysqli_query($conn, "USE " . $dbname));
		$query="SELECT commessa,descrizione
					FROM commesse
					WHERE id<>'".@$_GET["commessa_to_edit"]."'
						AND visibile=1";
		$result=@mysqli_query($conn, $query)
			or die("$query<br>".mysqli_error($conn));
		$commesse="";
		$descrizioni="";
		while($row=mysqli_fetch_assoc($result))
		{
			$commesse.='"'.$row["commessa"].'":"1",';
			$descrizioni.='"'.$row["descrizione"].'":"1",';
		}
		$commesse=rtrim($commesse,",");
		$descrizioni=rtrim($descrizioni,",");
		((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
		if(($admin_action=="edit_commessa")&&(!isset($valori)))
		{
			$query="SELECT commesse.* FROM commesse WHERE commesse.id=".$_GET["commessa_to_edit"];
			$result=@mysqli_query($conn, $query)
				or die($query."<br>".mysqli_error($conn));
			$valori=mysqli_fetch_assoc($result);
			((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
		}
		((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
		if(!isset($subtitle))
			$subtitle="modifica commessa '".@$valori["commessa"]."'";
		logged_header($op,$_SESSION["nome"]." ".$_SESSION["cognome"]
				." - ".$_SESSION["ditta_rs"]
				,$subtitle);
		close_logged_header($_SESSION["livello"]);

		?>
		<div id="centra">
			<form action="<?=$self?>" id="edit_form" method="post"
				onsubmit="return check_post_commessa(this)">
			 	<input type="hidden" value="1" name="performAction">
		<?
		if($admin_action=="edit_commessa")
		{?>
				<input type="hidden" value="<?=$valori["id"]?>" name="id_commessa">
		<?}?>
				<table class="edit">
					<tr>
						<td>commessa</td>
						<td>
							<input type="text" name="commessa" size="25"
								maxlength="25"
								value="<?=safeString(@$valori["commessa"])?>">
						</td>
					</tr>
					<tr>
						<td>descrizione</td>
						<td>
							<input type="text" name="descrizione" size="30"
								maxlength="30"
								value="<?=safeString(@$valori["descrizione"])?>">
						</td>
					</tr>
		<?
		if($admin_action=="edit_commessa")
		{?>
					<tr>
						<td>visibile</td>
						<td>
							<input type="checkbox" name="visibile"<?=(@$valori["visibile"]==1?" checked":"")?>>
						</td>
					</tr>
		<?}?>
					<tr>
						<td colspan="2" class="footer">
							<input type="submit" class="button"
								name="<?=$admin_action?>" value="accetta">&nbsp;
							<input type="button" class="button" onclick='
								javascript:redirect("<?=$self?>&amp;op=admin_commesse");'
								value="annulla">
						</td>
					</tr>
				</table>
			</form>
		</div>
		<?
		break;
	case "list_commesse":
		logged_header($op,$_SESSION["nome"]." ".
			$_SESSION["cognome"]." - ".$_SESSION["ditta_rs"]
			,"gestione commesse");
		close_logged_header($_SESSION["livello"]);

		$conn=mysqli_connect($myhost, $myuser, $mypass)
			or die("Connessione non riuscita".mysqli_error($conn));
		((bool)mysqli_query($conn, "USE " . $dbname));
		$query="SELECT * FROM commesse";
		$result=@mysqli_query($conn, $query)
			or die($query."<br>".mysqli_error($conn));
		?>
		<table class="edit">
			<tr class="table-header">
				<td>&nbsp;</td>
				<td>commessa</td>
				<td>descrizione</td>
				<td>visibile</td>
			</tr>
		<?
		while($row=mysqli_fetch_assoc($result))
		{
			$row_class=(($row["visibile"]==1)?"row_attivo":"row_inattivo");
		?>
			<tr class="<?=$row_class?>">
				<td>
					<a href="<?=$self?>&amp;op=admin_commesse&amp;admin_action=edit_commessa&amp;commessa_to_edit=<?=$row["id"]?>">
						<img src="img/b_edit.png" alt="Edit" title="Edit">
					</a>
				</td>
				<td><?=$row["commessa"]?></td>
				<td><?=$row["descrizione"]?></td>
				<td><?=($row["visibile"]==1?"si":"no")?></td>
			</tr>
		<?}?>
			<tr class="row_attivo">
				<td colspan="4">
					<a href="<?=$self?>&amp;op=admin_commesse
							&amp;admin_action=add_commessa">
						<img src="img/b_edit.png" alt="Nuova" title="Nuova">
						&nbsp;Nuova commessa
					</a>
				</td>
			</tr>
		</table>
		<?
		break;
}
?>
</div>
<?
if(($admin_action=="add_commessa")
	||($admin_action=="edit_commessa"))
{?>
	<script type="text/javascript">
		var commesse={<?=$commesse?>};
		var descrizioni={<?=$descrizioni?>};
		function check_post_commessa(form)
		{
			var out=true;

			if(trim(form.commessa.value).length==0)
			{
				showMessage("codice non valido");
				return false;
			}

			if(trim(form.descrizione.value).length==0)
			{
				showMessage("descrizione non valida");
				return false;
			}
			if(commesse[form.commessa.value]!=null)
			{
				showMessage("commessa gia' presente");
				return false;
			}
			if(descrizioni[form.descrizione.value]!=null)
			{
				showMessage("descrizione gia' presente");
				return false;
			}
			return out;
		}

		document.getElementById("edit_form").commessa.focus();
	</script>
<?}
?>
