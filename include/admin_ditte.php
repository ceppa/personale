<?
switch($admin_action)
{
	case "add_ditta":
		$subtitle="nuova ditta";
	case "edit_ditta":
		$conn=mysqli_connect($myhost, $myuser, $mypass)
			or die("Connessione non riuscita".mysqli_error($conn));
		((bool)mysqli_query($conn, "USE " . $dbname));
		$query="SELECT ragione_sociale
					FROM ditte
					WHERE id<>'".@$_GET["ditta_to_edit"]."'";
		$result=@mysqli_query($conn, $query)
			or die("$query<br>".mysqli_error($conn));
		$ditte="";
		while($row=mysqli_fetch_assoc($result))
			$ditte.='"'.$row["ragione_sociale"].'":"1",';
		$ditte=rtrim($ditte,",");
		((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);

		if(($admin_action=="edit_ditta")&&(!isset($valori)))
		{
			$query="SELECT ditte.* FROM ditte WHERE ditte.id=".$_GET["ditta_to_edit"];
			$result=@mysqli_query($conn, $query)
				or die($query."<br>".mysqli_error($conn));
			$valori=mysqli_fetch_assoc($result);
			((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
		}
		((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
		if(!isset($subtitle))
			$subtitle="modifica ditta '".@$valori["ragione_sociale"]."'";
		logged_header($op,$_SESSION["nome"]." ".$_SESSION["cognome"].
				" - ".$_SESSION["ditta_rs"]
				,$subtitle);
		close_logged_header($_SESSION["livello"]);

		?>
		<div id="centra">
			<form action="<?=$self?>" id="edit_form" method="post"
				onsubmit="return check_post_ditta(this)">
				<input type="hidden" value="1" name="performAction">
		<?
		if($admin_action=="edit_ditta")
		{?>
				<input type="hidden" value="<?=$valori["id"]?>"
					name="id_ditta">
		<?}?>
				<table class="edit">
					<tr>
						<td>ragione sociale</td>
						<td>
							<input type="text" name="ragione_sociale" size="30"
								maxlength="30"
								value="<?=safeString(@$valori["ragione_sociale"])?>">
						</td>
					</tr>
					<tr>
						<td colspan="2" class="footer">
							<input type="submit" class="button"
								name="<?=$admin_action?>" value="accetta">
							&nbsp;
							<input type="button" class="button"
								onclick='javascript:redirect("<?=$self?>
									&amp;op=admin_ditte");'
								value="annulla">
						</td>
					</tr>
				</table>
			</form>
		</div>
		<?
		break;
	case "list_ditte":
		logged_header($op,$_SESSION["nome"]." ".
			$_SESSION["cognome"]." - ".$_SESSION["ditta_rs"]
			,"gestione ditte");
		close_logged_header($_SESSION["livello"]);

		$conn=mysqli_connect($myhost, $myuser, $mypass)
			or die("Connessione non riuscita".mysqli_error($conn));
		((bool)mysqli_query($conn, "USE " . $dbname));
		$query="SELECT * FROM ditte";
		$result=@mysqli_query($conn, $query)
			or die($query."<br>".mysqli_error($conn));
		?>
		<table class="edit">
			<tr class="table-header">
				<td>
					&nbsp;
				</td>
				<td>ragione sociale</td>
			</tr>
		<?
		while($row=mysqli_fetch_assoc($result))
		{
			?>
			<tr class="row_attivo">
				<td>
					<a href="<?=$self?>&amp;op=admin_ditte&amp;admin_action=edit_ditta&amp;ditta_to_edit=<?=$row["id"]?>">
						<img src="img/b_edit.png" alt="Edit" title="Edit">
					</a>
				</td>
				<td>
					<?=$row["ragione_sociale"]?>
				</td>
			</tr>
		<?}?>
			<tr class="row_attivo">
				<td colspan="2">
					<a href="<?=$self?>&amp;op=admin_ditte&amp;admin_action=add_ditta">
						<img src="img/b_edit.png" alt="Nuova" title="Nuova">
						&nbsp;Nuova ditta
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
if(($admin_action=="add_ditta")
	||($admin_action=="edit_ditta"))
{?>
	<script type="text/javascript">
		var ditte={<?=$ditte?>};
		function check_post_ditta(form)
		{
			var out=true;

			if(trim(form.ragione_sociale.value).length==0)
			{
				showMessage("nome non valido");
				return false;
			}
			if(ditte[form.ragione_sociale.value]!=null)
			{
				showMessage("ragione sociale gia' presente");
				return false;
			}
			return out;
		}

		document.getElementById("edit_form").ragione_sociale.focus();
	</script>
<?}
?>
