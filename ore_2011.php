
<?
ini_set("error_reporting",E_ALL );

$ore_lav=480;
$version = '0.5.1';
$dbname="hightecs_presenze";
$myhost="localhost";
$myuser="hightecs_envy";
$mypass="minair";
	
	
$conn=mysqli_connect($myhost, $myuser, $mypass)
	or die("Connessione non riuscita ".mysqli_error($conn));
((bool)mysqli_query($conn, "USE " . $dbname));

		
$query="SELECT ditte.ragione_sociale,utenti.cognome,utenti.nome,presenze.festivo,presenze.ore_str,presenze.giorno,presenze.uscita,presenze.ingresso,presenze.pausa,presenze.uscita2,presenze.ingresso2,presenze.ore_via,
				utenti.ditta
			FROM presenze JOIN utenti ON presenze.id=utenti.id
			JOIN ditte ON utenti.ditta=ditte.id
			WHERE presenze.giorno between '2011-01-01' and '2011-12-31' 
			AND utenti.ditta>0
			ORDER BY utenti.ditta,utenti.cognome,presenze.giorno";
$result=mysqli_query($conn, $query)
		or die(mysqli_error($conn));
$totore=array();
$ore_norm=0;
$ore_str=0;
$ore_via=0;

while($row=mysqli_fetch_assoc($result))
{
	$lavorato=0;
	if(($row["ingresso"]==-1)&&(($row["ore_str"]>0||($row["ore_via"]>0))))
	{
		$ore_str=$row["ore_str"]+1-(($row["ore_str"]+1)%5);
		$ore_norm=0;
		$ore_via=$row["ore_via"]+1-(($row["ore_via"]+1) % 5);
	}
	else
	{
		$uscita=$row["uscita"]+1-(($row["uscita"]+1) % 5);
		$ingresso=$row["ingresso"]+1-(($row["ingresso"]+1) % 5);
		if($ingresso>0)
			$lavorato=1;
		$uscita2=$row["uscita2"]+1-(($row["uscita2"]+1) % 5);
		$ingresso2=$row["ingresso2"]+1-(($row["ingresso2"]+1) % 5);
		$pausa=$row["pausa"]+1-(($row["pausa"]+1) % 5);
		$ore_via=$row["ore_via"]+1-(($row["ore_via"]+1) % 5);

		$oremattina=($uscita-$ingresso-$pausa);
		if(($oremattina<0)&&($row["uscita"]!=-1))
			$oremattina=($oremattina +1440)%1440;

		$orepome=$uscita2-$ingresso2;
		if(($orepome<0))
			$orepome=($orepome+1440)%1440;

	
		$ore=$oremattina+$orepome;
		

		if($ore>=$ore_via)
			$ore-=$ore_via;
		else
			if($ore>0)
				printf("error: %s - %s, ore %d via %d<br>",$row["giorno"],$row["cognome"],$ore,$ore_via);
		

		$ore=($ore-($ore % 15));

		if($ore>$ore_lav)
		{
			if($row["festivo"]==1)
			{
				$ore_str=$ore;
				$ore_norm=0;
			}
			else
			{
				$ore_norm=$ore_lav;
				if(($ore-$ore_lav)<30)				
					$ore_str=0;
				else
					$ore_str=$ore-$ore_lav;
			}
		}
		else
		{
			$ore_norm=$ore;
			$ore_str=0;
		}

	}
	$anno=substr($row["giorno"],0,4);
	$cognome=$row["cognome"]." ".$row["nome"];
	if(!isset($totore[$cognome]))
		$totore[$cognome]=array("ore_norm"=>0,"ore_str"=>0,"ore_via"=>0,"giorni"=>0);

	if(!isset($totore[$cognome]["ore_norm"]))
		$totore[$cognome]["ore_norm"]=$ore_norm;
	else
		$totore[$cognome]["ore_norm"]+=$ore_norm;

	if(!isset($totore[$cognome]["ore_str"]))
		$totore[$cognome]["ore_str"]=$ore_str;
	else
		$totore[$cognome]["ore_str"]+=$ore_str;

	if(!isset($totore[$cognome]["ore_via"]))
		$totore[$cognome]["ore_via"]=$ore_via;
	else
		$totore[$cognome]["ore_via"]+=$ore_via;

	if(!isset($totore[$cognome]["giorni"]))
		$totore[$cognome]["giorni"]=$lavorato;
	else
		$totore[$cognome]["giorni"]+=$lavorato;
}
?>
<table>
	<tr>
		<td>nome</td>
		<td>ore_norm</td>
		<td>ore_str</td>
		<td>ore_via</td>
		<td>giorni_lav</td>
	</tr>
<?
foreach($totore as $cognome=>$array)
{
?>
	<tr>
		<td><?=$cognome?></td>
		<td><?=$array["ore_norm"]?></td>
		<td><?=$array["ore_str"]?></td>
		<td><?=$array["ore_via"]?></td>
		<td><?=$array["giorni"]?></td>
	</tr>
<?
}
?>
</table>
