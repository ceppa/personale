<?
if(!isset($trasf))
	$trasf=0;

if(!isset($_GET["mese"]))
	$mese=date("n");
else
	$mese=(int)$_GET["mese"];

if(!isset($_GET["anno"]))
	$anno=date("Y");
else
	$anno=(int)$_GET["anno"];

$conn=mysqli_connect($myhost, $myuser, $mypass);
((bool)mysqli_query($conn, "USE " . $dbname));

fillPresenze($_SESSION["id_edit"],
	$_SESSION["data_inizio_coll"],
	$_SESSION["data_fine_coll"],
	$_SESSION["id_commessa"],
	$_SESSION["patrono"],
	$_SESSION["tr_euri_km"],
	$mese,
	$anno);

$tabella=tabella_presenze($conn,$_SESSION["id_edit"],
		date("Y-m-d",mktime(0,0,0,(int)$mese,1,(int)$anno)),
		date("Y-m-d",mktime(0,0,0,(int)$mese+1,0,(int)$anno)),
		$_SESSION["data_inizio_coll"],
		$_SESSION["data_fine_coll"],$trasf);

logged_header($op."|".$mese."|".$anno,$_SESSION["nome"]." ".$_SESSION["cognome"]." - ".$_SESSION["ditta_rs"]);
close_logged_header($_SESSION["livello"]);
?>
<form name="multiedit" method="post" action="<?=$self?>">
<input type="hidden" name="trasf" value="<?=$trasf?>">
<input type="hidden" name="op" value="edit">
<div class="centra">
	<table class="mese">
		<tr class="table-header">
			<td>
				<img id="editAll"
					align="right"
					src="img/b_edit.png"
					alt="Edit"
					title="Edit"
					style="display:none"
					onmouseover="style.cursor='pointer'"
					onclick="multiedit.submit();">
			</td>
			<td>Giorno</td>
			<?
foreach(current($tabella) as $field=>$void)
	if(substr($field,0,10)!="__hidden__")
	{?>
			<td><?=$field?></td><?
	}
foreach($tabella as $giorno=>$row)
{
	$classe=($row["__hidden__festivo"]?"festivo":"feriale");
	?>
	<tr>
		<td class=<?=($classe."-center")?>><?
	if(($_SESSION["livello"]!=2)&&
			(($_SESSION["livello"]==1)
				||($_SESSION["blocca_a_data"]=="0000-00-00")
				||(strtotime($giorno)>strtotime($_SESSION["blocca_a_data"]))))
	{?>
			<a href="<?=$self?>&amp;op=edit&amp;giorno=<?=$giorno?>&amp;trasf=<?=$trasf?>">
				<img src="img/b_edit.png" alt="Edit" title="Edit">
			</a>
			<input type="checkbox" name="giorno[]" value="<?=$giorno?>" onclick="checkGiorni(multiedit)">
	<?
	}?>
		</td>
		<td class="<?=$classe?>-left">
			<?=(my_date_format($giorno,"d ").substr($giorni_settimana[my_date_format($giorno,"w")],0,3))?>
		</td>
	<?
	foreach($row as $fieldname=>$field)
		if(substr($fieldname,0,10)!="__hidden__")
		{?>
		<td class="<?=$classe?>-center">
			<?=$field?>
		</td>
		<?}?>
	</tr>
<?
}
?>
</table>
</div>
</form>
</div>

<script type="text/javascript">
	function checkGiorni(form)
	{
		var conta=0;
		for(var i=0;i<form.elements["giorno[]"].length;i++)
			if(form.elements["giorno[]"][i].checked)
				conta++;
		if(conta)
			document.getElementById("editAll").style.display="block";
		else
			document.getElementById("editAll").style.display="none";

	}
</script>

<?
@((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
?>
