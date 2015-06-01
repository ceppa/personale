<?
function calcolagiorni($datainizio,$orainizio,$datafine,$orafine)
{
	require_once("datetime.php");
	if($datafine<"2013-09-01")
	{
		$giorni=my_date_diff($datainizio,$datafine);
		if($orainizio>720)
			$giorni-=0.5;
		if($orafine<720)
			$giorni-=0.5;
		if($giorni<0)
			$giorni=0;
	}
	else
	{
/*
24 ore 1 giorno
2° giorno inizia passate 10 ore
non ci sono mezzi
*/
		$data1=sprintf("%s %02d:%02d:00",
			$datainizio,
			(int)($orainizio / 60),
			$orainizio%60);
		$data2=sprintf("%s %02d:%02d:00",
			$datafine,
			(int)($orafine / 60),
			$orafine%60);
		$secondi=datetime_diff($data1,$data2);
		if($secondi<0)
			return 0;
		$ore=(int)$secondi/3600;

		$reminder=$ore%24;
		$giorni=1+(int)($ore/24);
		if(($giorni>1)&&($reminder<10))
			$giorni--;
	}
	return $giorni;
}

if(count($_POST))
{
	$func=(isset($_POST["func"])?$_POST["func"]:"");
	switch($func)
	{
		case "calcolagiorni":
			require_once("datetime.php");

			if(isset($_POST["datainizio"])&&strlen($_POST["datainizio"])
				&&isset($_POST["orainizio"])&&strlen($_POST["orainizio"])
				&&isset($_POST["datafine"])&&strlen($_POST["datafine"])
				&&isset($_POST["orafine"])&&strlen($_POST["orafine"]))
			{
				$datainizio=date_to_sql($_POST["datainizio"]);
				$orainizio=hour_to_int($_POST["orainizio"]);
				$datafine=date_to_sql($_POST["datafine"]);
				$orafine=hour_to_int($_POST["orafine"]);
				$giorni=calcolagiorni($datainizio,$orainizio,$datafine,$orafine);
				echo $giorni;
			}
			else
				echo "-";
			break;
		default:
			break;
	}
}

?>
