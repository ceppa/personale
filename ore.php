<?php
$ore_lav=480;
$version = '0.5.1';
$myhost="localhost";
$myuser="root";
$mypass="minair";
if(isset($_GET["until"]))
{
    $until=$_GET["until"];
    $yy=substr($until,0,4);
    $mm=substr($until,5,2);
    $dd=substr($until,8,2);
    if(($yy>2000)&&($mm>0)&&($mm<=12)&&($dd>0)&&($dd<=cal_days_in_month(CAL_GREGORIAN,$mm,$yy)))
	$until=sprintf("%04d-%02d-%02d",$yy,$mm,$dd);
    else
	die("data incorretta");
}
else
    $until=date("Y-m-d");

$conn=mysqli_connect($myhost, $myuser, $mypass)
	or die("Connessione non riuscita ".mysqli_error($conn));
((bool)mysqli_query($conn, "USE " . presenze));

		
$query="SELECT ditte.ragione_sociale,utenti.cognome,presenze.festivo,presenze.ore_str,presenze.giorno,presenze.uscita,presenze.ingresso,presenze.pausa,presenze.uscita2,presenze.ingresso2,presenze.ore_via,
				utenti.ditta
			FROM presenze JOIN utenti ON presenze.id=utenti.id
			JOIN ditte ON utenti.ditta=ditte.id
			WHERE presenze.giorno<\"$until\"
			AND utenti.ditta>0
			ORDER BY utenti.ditta,utenti.cognome,presenze.giorno";
$result=mysqli_query($conn, $query)
		or die(mysqli_error($conn));
$totore=array();
while($row=mysqli_fetch_assoc($result))
{
	if(($row["ingresso"]==-1)&&($row["ore_str"]>0))
		$ore=$row["ore_str"];
	else
	{
		$uscita=$row["uscita"]+1-(($row["uscita"]+1) % 5);
		$ingresso=$row["ingresso"]+1-(($row["ingresso"]+1) % 5);
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
	
		$oreprima=$ore;
		if($ore>$ore_lav)
		{
			if($row["festivo"]==1)
				$ore=($ore-($ore % 15));
			else
			{
				if(($ore-$ore_lav)<30)
					$ore=$ore_lav;
				else
					$ore=($ore-($ore % 15));
			}
		}
	}
	$anno=substr($row["giorno"],0,4);
	if(!isset($totore[$row["ragione_sociale"]][$anno]))
		$totore[$row["ragione_sociale"]][$anno]=$ore;
	else
		$totore[$row["ragione_sociale"]][$anno]+=$ore;
}

echo "Minuti lavorati alla data $until esclusa<br><br>";
foreach($totore as $rs=>$array)
{
	echo "$rs<br>";
	foreach($array as $anno=>$minuti)
		echo "$anno $minuti<br>";
}
?>
