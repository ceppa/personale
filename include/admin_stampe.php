<?
logged_header($op,$_SESSION["nome"]." ".$_SESSION["cognome"]
		." - ".$_SESSION["ditta_rs"],
		"gestione stampe");
close_logged_header($_SESSION["livello"]);

$conn=mysqli_connect($myhost, $myuser, $mypass)
	or die("Connessione non riuscita".mysqli_error($conn));
((bool)mysqli_query($conn, "USE " . $dbname));
$query="SELECT id,cognome,nome,ditta,stampa_ore FROM utenti
	WHERE attivo=1
	ORDER BY ditta,cognome";
$utenti=array();
$result=@mysqli_query($conn, $query)
	or die($query."<br>".mysqli_error($conn));
while($row=mysqli_fetch_assoc($result))
	$utenti[$row["id"]]=$row;
((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);

$query="SELECT * FROM ditte";
$ditte=array();
$result=mysqli_query($conn, $query)
	or die($query."<br>".mysqli_error($conn));
while($row=mysqli_fetch_assoc($result))
	$ditte[$row["id"]]=$row["ragione_sociale"];
((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);

if($_SESSION["livello"]==2)
{
	$query="SELECT localita.* FROM localita ORDER BY localita.localita";
	$localita=array();
	$result=@mysqli_query($conn, $query)
		or die($query."<br>".mysqli_error($conn));
	while($row=mysqli_fetch_assoc($result))
		$localita[$row["id"]]=$row["localita"];
	((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);

}
((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
if(!isset($_POST["da_data"]))
	$da_data=date("d/m/Y",mktime(0,0,0,1,1,date("Y")));
else
	$da_data=$_POST["da_data"];
if(!isset($_POST["a_data"]))
	$a_data=date("d/m/Y",mktime(0,0,0,1,0,1+date("Y")));
else
	$a_data=$_POST["a_data"];

if(!isset($_POST["mese"]))
	$mese=date("n");
else
	$mese=$_POST["mese"];

if(!isset($_POST["anno"]))
	$anno=date("Y");
else
	$anno=$_POST["anno"];

?>
<div class="inline">
	<form action="<?=$self?>" target="_blank"
			method="post" name="stampa_periodo"
			onsubmit="return ((is_date(document.stampa_periodo.da_data.value))
							&&(is_date(document.stampa_periodo.a_data.value)))"">
		<input type="hidden" name="report" value="1">
		<table class="edit">
			<tr>
				<td colspan="2">
					<div class="centra">
						<p class="title">Per periodo</p>
					</div>
				</td>
			</tr>
			<tr>
				<td>Dal giorno</td>
				<td>
					<input type="text" name="da_data" value="<?=$da_data?>"
						size="10" maxlength="10"
						onchange="this.value=formattadata(this.value)">
				</td>
			</tr>
			<tr>
				<td>Al giorno</td>
				<td>
					<input type="text" name="a_data" value="<?=$a_data?>"
						size="10" maxlength="10"
						onchange="this.value=formattadata(this.value)">
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<div class="centra">
						<input type="submit" name="submit_periodo"
							value="stampa">
					</div>
				</td>
			</tr>
		</table>
	</form>
</div>

<div class="inline">
	<form action="<?=$self?>" target="_blank" method="post" name="stampa_commesse">
		<input type="hidden" name="report" value="1">
		<table class="edit">
			<tr>
				<td colspan="2">
					<div class="centra">
						<p class="title">Per commessa</p>
					</div>
				</td>
			</tr>
			<tr>
				<td>Mese</td>
				<td>
					<select class="input" name="mese">
					<?
foreach($mesi as $mese_id=>$mese_text)
{?>
						<option value="<?=($mese_id+1)?>"<?=($mese_id+1==$mese?" selected":"")?>>
							<?=$mese_text?>
						</option>
<?}?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Anno</td>
				<td>
					<select class="input" name="anno">
<?
for($anno_id=2005;$anno_id<=2020;$anno_id++)
{?>
						<option value="<?=$anno_id?>"<?=($anno_id==$anno?" selected":"")?>>
							<?=$anno_id?>
						</option>
<?}?>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<div class="centra">
						<input type="submit" name="submit_commessa"
							value="stampa">
					</div>
				</td>
			</tr>
		</table>
	</form>
</div>

<div class="inline">
	<form action="<?=$self?>" target="_blank" method="post" name="stampa_pf">
		<input type="hidden" name="report" value="1">
		<table class="edit">
			<tr>
				<td colspan="2">
					<div class="centra">
						<p class="title">Permessi/ferie</p>
					</div>
				</td>
			</tr>
			<tr>
				<td>Mese</td>
				<td>
					<select class="input" name="mese">
<?
foreach($mesi as $mese_id=>$mese_text)
{?>
						<option value="<?=($mese_id+1)?>"<?=($mese_id+1==$mese?" selected":"")?>>
							<?=$mese_text?>
						</option>
<?}?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Anno</td>
				<td>
					<select class="input" name="anno">
<?
for($anno_id=2005;$anno_id<=2020;$anno_id++)
{?>
						<option value="<?=$anno_id?>"<?=($anno_id==$anno?" selected":"")?>>
							<?=$anno_id?>
						</option>
<?}?>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<div class="centra">
						<input type="submit" name="submit_pf"
							value="stampa">
					</div>
				</td>
			</tr>
		</table>
	</form>
</div>
<div class="inline">
	<form action="<?=$self?>" target="_blank" method="post" name="stampa_riepilogo">
		<input type="hidden" name="report" value="1">
		<table class="edit">
			<tr>
				<td colspan="2">
					<div class="centra">
						<p class="title">Riepilogo mese / anno</p>
					</div>
				</td>
			</tr>
			<tr>
				<td>Mese</td>
				<td>
					<select class="input" name="mese">
						<option value="0">annuale</option>

<?
foreach($mesi as $mese_id=>$mese_text)
{?>
						<option value="<?=($mese_id+1)?>"<?=($mese_id+1==$mese?" selected":"")?>>
							<?=$mese_text?>
						</option>
<?}?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Anno</td>
				<td>
					<select class="input" name="anno">
<?
for($anno_id=2005;$anno_id<=2020;$anno_id++)
{?>
						<option value="<?=$anno_id?>"<?=($anno_id==$anno?" selected":"")?>>
							<?=$anno_id?>
						</option>
<?}?>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<div class="centra">
						<input type="submit" name="print_riepilogo"
							value="stampa">
						&nbsp;
						<input type="submit" name="csv_riepilogo"
							value="csv">
						&nbsp;
						<input type="submit" name="xls_riepilogo"
							value="xls">
					</div>
				</td>
			</tr>
		</table>
	</form>
</div>

<div class="inline"<?=($_SESSION["livello"]!=2?'':' style="clear:left"')?>>
	<form action="<?=$self?>" target="_blank" method="post" name="stampa_mese">
		<input type="hidden" name="report" value="1">
		<table class="edit">
			<tr>
				<td colspan="2">
					<div class="centra">
						<p class="title">Per mese</p>
					</div>
				</td>
			</tr>
			<tr>
				<td>Mese</td>
				<td>
					<select class="input" name="mese">
<?
foreach($mesi as $mese_id=>$mese_text)
{?>
						<option value="<?=($mese_id+1)?>"<?=($mese_id+1==$mese?" selected":"")?>>
							<?=$mese_text?>
						</option>
<?}?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Anno</td>
				<td>
					<select class="input" name="anno">
<?
for($anno_id=2005;$anno_id<=2020;$anno_id++)
{?>
						<option value="<?=$anno_id?>"<?=($anno_id==$anno?" selected":"")?>>
							<?=$anno_id?>
						</option>
<?}?>
					</select>
				</td>
			</tr>
<?
if($_SESSION["livello"]==2)
{?>
			<tr>
				<td>Localita</td>
				<td>
					<select class="input" name="localita">
<?
	foreach($localita as $localita_id=>$localita_text)
	{?>
						<option value="<?=$localita_id?>">
							<?=$localita_text?>
						</option>
	<?}?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Ditta</td>
				<td>
					<select class="input" name="ditta">
<?
	foreach($ditte as $ditta_id=>$ditta_text)
	{?>
						<option value="<?=$ditta_id?>">
							<?=$ditta_text?>
						</option>
	<?}?>
					</select>
				</td>
			</tr>
<?}?>

			<tr>
				<td colspan="2">
					<div class="centra">
						<input type="submit" name="submit_mese"
							value="stampa">
					</div>
				</td>
			</tr>
		</table>
	</form>
</div>

<div class="inline">
	<form action="<?=$self?>" target="_blank"
			method="post" name="stampa_fogli_viaggio"
			onsubmit="return ((is_date(document.stampa_fogli_viaggio.da_data.value))
							&&(is_date(document.stampa_fogli_viaggio.a_data.value)))"">
		<input type="hidden" name="report" value="1">
		<table class="edit">
			<tr>
				<td colspan="2">
					<div class="centra">
						<p class="title">Fogli Viaggio</p>
					</div>
				</td>
			</tr>
			<tr>
				<td>Dal giorno</td>
				<td>
					<input type="text" name="da_data" value="<?=$da_data?>"
						size="10" maxlength="10"
						onchange="this.value=formattadata(this.value)">
				</td>
			</tr>
			<tr>
				<td>Al giorno</td>
				<td>
					<input type="text" name="a_data" value="<?=$a_data?>"
						size="10" maxlength="10"
						onchange="this.value=formattadata(this.value)">
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<div class="centra">
						<input type="submit" name="submit_fogli_viaggio"
							value="stampa">
					</div>
				</td>
			</tr>
		</table>
	</form>
</div>
<div class="inline">
	<form action="<?=$self?>" target="_blank"
			method="post" name="stampa_conguagli"
			onsubmit="return ((is_date(document.stampa_conguagli.da_data.value))
							&&(is_date(document.stampa_conguagli.a_data.value)))"">
		<input type="hidden" name="report" value="1">
		<table class="edit">
			<tr>
				<td colspan="2">
					<div class="centra">
						<p class="title">Conguagli</p>
					</div>
				</td>
			</tr>
			<tr>
				<td>Dal giorno</td>
				<td>
					<input type="text" name="da_data" value="<?=$da_data?>"
						size="10" maxlength="10"
						onchange="this.value=formattadata(this.value)">
				</td>
			</tr>
			<tr>
				<td>Al giorno</td>
				<td>
					<input type="text" name="a_data" value="<?=$a_data?>"
						size="10" maxlength="10"
						onchange="this.value=formattadata(this.value)">
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<div class="centra">
						<input type="submit" name="submit_conguagli"
							value="stampa">
					</div>
				</td>
			</tr>
		</table>
	</form>
</div>
<div class="inline">
	<form action="<?=$self?>" target="_blank" method="post" name="stampa_buoni_pasto">
		<input type="hidden" name="report" value="1">
		<table class="edit">
			<tr>
				<td colspan="2">
					<div class="centra">
						<p class="title">Buoni Pasto</p>
					</div>
				</td>
			</tr>
			<tr>
				<td>Mese</td>
				<td>
					<select class="input" name="mese">
					<?
foreach($mesi as $mese_id=>$mese_text)
{?>
						<option value="<?=($mese_id+1)?>"<?=($mese_id+1==$mese?" selected":"")?>>
							<?=$mese_text?>
						</option>
<?}?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Anno</td>
				<td>
					<select class="input" name="anno">
<?
for($anno_id=2005;$anno_id<=2020;$anno_id++)
{?>
						<option value="<?=$anno_id?>"<?=($anno_id==$anno?" selected":"")?>>
							<?=$anno_id?>
						</option>
<?}?>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<div class="centra">
						<input type="submit" name="submit_buoni_pasto"
							value="stampa">
					</div>
				</td>
			</tr>
		</table>
	</form>
</div>
<div class="inline">
	<form action="<?=$self?>" target="_blank" method="post" name="stampa_ore">
		<input type="hidden" name="report" value="1">
		<table class="edit">
			<tr>
				<td colspan="2">
					<div class="centra">
						<p class="title">Stampa ore</p>
					</div>
				</td>
			</tr>
			<tr>
				<td>Mese</td>
				<td>
					<select class="input" name="mese">
					<?
foreach($mesi as $mese_id=>$mese_text)
{?>
						<option value="<?=($mese_id+1)?>"<?=($mese_id+1==$mese?" selected":"")?>>
							<?=$mese_text?>
						</option>
<?}?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Anno</td>
				<td>
					<select class="input" name="anno">
<?
for($anno_id=2005;$anno_id<=2020;$anno_id++)
{?>
						<option value="<?=$anno_id?>"<?=($anno_id==$anno?" selected":"")?>>
							<?=$anno_id?>
						</option>
<?}?>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<div class="centra">
						<input type="button" name="scelta_utenti_ore" id="scelta_utenti_ore"
							value="utenti">
						<input type="submit" name="submit_stampa_ore"
							value="stampa">
					</div>
				</td>
			</tr>
			<tr id="table_utenti">
				<td colspan="2">
					<table>
						<?
							foreach($utenti as $id=>$utente)
							{
								$checked=($utente["stampa_ore"]?" checked":"");
								?>
						<tr>
							<td>
								<input type="checkbox"
									name="ore_utente[]"
									value="<?=$id?>"<?=$checked?>>
									<?=($utente["cognome"]." ".$utente["nome"])?>
							</td>
						</tr>

							<?}?>
					</table>
				</td>
			</tr>
		</table>
	</form>
</div>
</div>
<script type="text/javascript">
	document.getElementById("table_utenti").style.display="none";
	document.getElementById("scelta_utenti_ore").onclick=function()
	{
		var el=document.getElementById("table_utenti");
		if(el.style.display=="none")
			el.style.display="";
		else
			el.style.display="none";
	}
</script>
