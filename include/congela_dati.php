<?
logged_header($op,$_SESSION["nome"]." ".$_SESSION["cognome"]." - ".$_SESSION["ditta_rs"]
	,"congela dati");
close_logged_header($_SESSION["livello"]);

$conn=mysqli_connect($myhost, $myuser, $mypass);
((bool)mysqli_query($conn, "USE " . $dbname));
$query="SELECT utenti.id,utenti.cognome,utenti.nome,utenti.blocca_a_data,ditte.ragione_sociale
		FROM utenti LEFT JOIN ditte ON utenti.ditta=ditte.id
		WHERE utenti.livello=0 AND utenti.attivo=1
		ORDER BY ditta,cognome,nome";
$result=@mysqli_query($conn, $query)
	or die("$query<br>".mysqli_error($conn));
$giorno=date("d/m/Y",mktime(0,0,0,date('m'),0,date('Y')));
?>

<div class="centra" style="font-size:90%">
	<form action="<?=$self?>" method="post" id="congelaForm"
			onsubmit="return is_date(document.getElementById('data').value)">
		<input type="hidden" value="1" name="performAction">
		<input type="text" size="10" maxlength="10" name="data" id="data" class="input" value="<?=$giorno?>">
		<input type="submit" name="submit_congela" class="button" value="ok"><br>
		<a title="seleziona tutti" href="javascript:checkAll(document.getElementById('congelaForm'),true);">tutti</a> |
		<a title="deseleziona tutti" href="javascript:checkAll(document.getElementById('congelaForm'),false);">nessuno</a>
		<table class="edit">
			<tr class="table-header">
				<td>&nbsp;</td>
				<td>cognome</td>
				<td>nome</td>
				<td>ditta</td>
				<td>congelato al</td>
			</tr>
			<?
			while($row=mysqli_fetch_assoc($result))
			{?>
				<tr>
					<td><input type="checkbox" name="id_<?=$row["id"]?>" value="<?=$row["id"]?>"></td>
					<td><?=$row["cognome"]?></td>
					<td><?=$row["nome"]?></td>
					<td><?=$row["ragione_sociale"]?></td>
					<td><?=$mese=my_date_format($row["blocca_a_data"],"d/m/Y");?></td>
				</tr>
			<?}

((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);

?>
		</table>
	</form>
</div>
</div>
<script type="text/javascript">
	function checkAll(fmobj,value)
	{
		for (var i=0;i<fmobj.elements.length;i++)
		{
			var e = fmobj.elements[i];
			if ( (e.type=='checkbox') && (!e.disabled) )
				e.checked = value;
		}
	}
	document.getElementById("data").focus();
</script>
