<?
	require_once("include/mysql.php");
	require_once("include/datetime.php");

	if(isset($_POST["submit"])&&isset($_POST["mese"])&&isset($_POST["anno"]))
	{
		$da=sprintf("%d-%02d-01",$_POST["anno"],$_POST["mese"]);
		$a=date("Y-m-d",mktime (0,0,0,$_POST["mese"]+1,0,$_POST["anno"]));

		$conn=mysqli_connect($myhost, $myuser, $mypass)
				or die("Connessione non riuscita ".mysqli_error($conn));
		((bool)mysqli_query($conn, "USE " . presenze))
				or die("Connessione non riuscita ".mysqli_error($conn));


		$query="SELECT ragione_sociale,utenti.id,cognome,nome,giorno,presenze.id,ingresso,uscita,pausa,ingresso2,uscita2,
					ore_str,festivo,ferie,malattia,trasferta 
				FROM presenze LEFT JOIN utenti ON presenze.id=utenti.id
				LEFT JOIN ditte ON utenti.ditta=ditte.id
			WHERE queryore=1 AND giorno BETWEEN '$da' AND '$a'
			ORDER BY ditta,cognome,utenti.id,giorno";
			//AND presenze.id!=11 AND presenze.id!=47 AND presenze.id!=48 $where";

		$result=mysqli_query($conn, $query)
			or die("$query<br>".mysqli_error($conn));

		$output=array();
		while($row=mysqli_fetch_assoc($result))
		{
			$id=$row["id"];
			$ingresso=($row["ingresso"]!=-1?$row["ingresso"]:0);
			$uscita=($row["uscita"]!=-1?$row["uscita"]:0);
			$pausa=($row["pausa"]!=-1?$row["pausa"]:0);
			$ingresso2=($row["ingresso2"]!=-1?$row["ingresso2"]:0);
			$uscita2=($row["uscita2"]!=-1?$row["uscita2"]:0);
			$ore_str=($row["ore_str"]!=-1?$row["ore_str"]:0);
			$festivo=$row["festivo"];
			$ferie=$row["ferie"];
			$malattia=$row["malattia"];
			$trasferta=($row["trasferta"]>0?1:0);
			if(!isset($output[$id]))
				$output[$id]=array(
					"cognome"=>$row["cognome"],
					"nome"=>$row["nome"],
					"pgdm"=>$row["giorno"],
					"ugdm"=>$row["giorno"],
					"giorni_lavorati"=>0,
					"giorni_malattia"=>0,
					"giorni_ferie"=>0,
					"giorni_trasferta"=>0,
					"ore_straord"=>0,
					"ore_normali"=>0,
					"ore_permessi"=>0,
					"ditta"=>$row["ragione_sociale"]);

			$output[$id]["ugdm"]=$row["giorno"];
			$output[$id]["giorni_trasferta"]+=$trasferta;
			if($malattia)
				$output[$id]["giorni_malattia"]++;
			elseif($ferie)
				$output[$id]["giorni_ferie"]++;
			else
			{
				$ore=(1440+$uscita-$ingresso-$pausa+$uscita2-$ingresso2)%1440;
				if($ore>0)
					$output[$id]["giorni_lavorati"]++;
				
				if($festivo)
					$output[$id]["ore_straord"]+=$ore;
				else
				{
					if($ore_str>0)
					{
						$output[$id]["ore_normali"]+=480;
						$output[$id]["ore_straord"]+=$ore_str;
						if($ore<480)
							echo $row["giorno"]." ".$row["id"]." $ore_str $ore<br>";
					}
					else
					{
						if($ore>480)
							$ore=480;
						$output[$id]["ore_normali"]+=$ore;
						$output[$id]["ore_permessi"]+=(480-$ore);
					}
				}
			}
		}
?>
<table>
	<tr>
		<td></td>
		<td align="center">ditta</td>
		<td align="center">cognome</td>
		<td align="center">nome</td>
		<td align="center">pgdm</td>
		<td align="center">ugdm</td>
		<td align="center">giorni lavorati</td>
		<td align="center">ore lavorate normali</td>
		<td align="center">ore straordinario</td>
		<td align="center">ore permesso</td>
		<td align="center">giorni ferie</td>
		<td align="center">giorni trasferta</td>
	</tr>
<?

		$ditta="";
		foreach($output as $array)
		{
			if($ditta!=$array["ditta"])
				$i=0;
			$ditta=$array["ditta"];
			$i++;
?>
			<tr>
				<td align="center"><?=$i?></td>
				<td align="center"><?=$array["ditta"]?></td>
				<td align="center"><?=$array["cognome"]?></td>
				<td align="center"><?=$array["nome"]?></td>
				<td align="center"><?=$array["pgdm"]?></td>
				<td align="center"><?=$array["ugdm"]?></td>
				<td align="center"><?=$array["giorni_lavorati"]?></td>
				<td align="center"><?=int_to_hour($array["ore_normali"])?></td>
				<td align="center"><?=int_to_hour($array["ore_straord"])?></td>
				<td align="center"><?=int_to_hour($array["ore_permessi"])?></td>
				<td align="center"><?=$array["giorni_ferie"]?></td>
				<td align="center"><?=$array["giorni_trasferta"]?></td>
			</tr>
<?
		}
?>
</table>
<?
		((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
		((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
	}
	else
	{?>
		<form action="<?=$_SERVER["PHP_SELF"]?>" method="post">
			mese
			<select name="mese">
				<option value="1">gennaio</option>
				<option value="2">febbraio</option>
				<option value="3">marzo</option>
				<option value="4">aprile</option>
				<option value="5">maggio</option>
				<option value="6">giugno</option>
				<option value="7">luglio</option>
				<option value="8">agosto</option>
				<option value="9">settembre</option>
				<option value="10">ottobre</option>
				<option value="11">novembre</option>
				<option value="12">dicembre</option>
			</select>
			<br>
			anno
			<select name="anno">
			<?
				for($i=2000;$i<2040;$i++)
				{?>
				<option value="<?=$i?>"<?=($i==date("Y")?" selected":"")?>><?=$i?></option>
				<?}?>
			</select>
			<br>
			<input type="submit" name="submit" value="vai">
		</form>
	<?}
?>
