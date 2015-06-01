<?
	require_once("include/mysql.php");
	require_once("include/datetime.php");

	if(isset($_POST["submit"])&&isset($_POST["da"])&&isset($_POST["a"]))
	{
		printf("Periodo dal %s al %s<br>",my_date_format($_POST["da"],"d.m.Y"),my_date_format($_POST["a"],"d.m.Y"));
		if(isset($_GET["id"]))
		{
			$exploded=explode(",",$_GET["id"]);
			$where="";
			foreach($exploded as $exp)
				$where.=" OR presenze.id='".$exp."'";
			$where=" AND (".ltrim($where," OR").")";
		}
		elseif(isset($_GET["nid"]))
			$where=" AND presenze.id!='".$_GET["nid"]."'";
		else
			$where=" AND utenti.istat=1";
	
		$conn=mysqli_connect($myhost, $myuser, $mypass)
				or die("Connessione non riuscita ".mysqli_error($conn));
		((bool)mysqli_query($conn, "USE " . $dbname))
				or die("Connessione non riuscita ".mysqli_error($conn));
	
	
		$query="SELECT operaio,cognome,nome,giorno,presenze.id,ingresso,uscita,pausa,ingresso2,uscita2,
						ore_str,festivo,ferie,malattia 
					FROM presenze LEFT JOIN utenti ON presenze.id=utenti.id
				WHERE ditta=3 AND giorno BETWEEN '".$_POST["da"]."' AND '".$_POST["a"]."'
				AND presenze.id!=11 AND presenze.id!=47 AND presenze.id!=48 $where";
	
		$result=mysqli_query($conn, $query)
			or die("$query<br>".mysqli_error($conn));
	
		$ore_normali=array(0,0);
		$ore_straord=array(0,0);
		$ore_ferie=array(0,0);
		$ore_permessi=array(0,0);
		$ore_malattia=array(0,0);
		$operai=array();
		$impiegati=array();
		while($row=mysqli_fetch_assoc($result))
		{
			$o=$row["operaio"];
			$ingresso=($row["ingresso"]!=-1?$row["ingresso"]:0);
			$uscita=($row["uscita"]!=-1?$row["uscita"]:0);
			$pausa=($row["pausa"]!=-1?$row["pausa"]:0);
			$ingresso2=($row["ingresso2"]!=-1?$row["ingresso2"]:0);
			$uscita2=($row["uscita2"]!=-1?$row["uscita2"]:0);
			$ore_str=($row["ore_str"]!=-1?$row["ore_str"]:0);
			$festivo=$row["festivo"];
			$ferie=$row["ferie"];
			$malattia=$row["malattia"];
	
			if($malattia)
				$ore_malattia[$o]+=480;
			elseif($ferie)
				$ore_ferie[$o]+=480;
			else
			{
				$ore=(1440+$uscita-$ingresso-$pausa+$uscita2-$ingresso2)%1440;
				if($festivo)
					$ore_straord[$o]+=$ore;
				else
				{
					if($ore_str>0)
					{
						$ore_normali[$o]+=480;
						$ore_straord[$o]+=$ore_str;
						if($ore<480)
							echo $row["giorno"]." ".$row["id"]." $ore_str $ore<br>";
					}
					else
					{
						if($ore>480)
							$ore=480;
						$ore_normali[$o]+=$ore;
						$ore_permessi[$o]+=(480-$ore);
					}
				}
			}
			if($o)
				$operai[$row["cognome"]." ".$row["nome"]]=1;
			else
				$impiegati[$row["cognome"]." ".$row["nome"]]=1;
		}
		ksort($operai);
		ksort($impiegati);
		for($o==0;$o<2;$o++)
		{
			if($o==0)
			{
				echo "impiegati<br>";
				foreach($impiegati as $k=>$v)
					echo "$k<br>";
			}
			else
			{
				echo "operai<br>";
				foreach($operai as $k=>$v)
					echo "$k<br>";
			}
			echo "ore normali: ".int_to_hour($ore_normali[$o])."<br>";
			echo "ore straordinari: ".int_to_hour($ore_straord[$o])."<br>";
			echo "ore ferie: ".int_to_hour($ore_ferie[$o])."<br>";
			echo "ore permessi: ".int_to_hour($ore_permessi[$o])."<br>";
			echo "ore malattia: ".int_to_hour($ore_malattia[$o])."<br>";
			echo "f+p+m: ".int_to_hour($ore_permessi[$o]+$ore_ferie[$o]+$ore_malattia[$o])."<br>";
			echo "<br>";
		}
		((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
		((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
	}
	else
	{?>
		<form action="istat.php" method="post">
			dal giorno (yyyy-mm-dd) <input type="text" name="da"><br>
			al giorno  (yyyy-mm-dd) <input type="text" name="a"><br>
			<input type="submit" name="submit" value="vai">
		</form>
	<?}
?>
