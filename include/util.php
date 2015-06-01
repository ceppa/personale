<?
	function fillPresenze($id,$data_inizio_coll,$data_fine_coll,$id_commessa,$patrono,$tr_euri_km,&$mese,&$anno)
	{
		global $conn;

		if(mktime(0,0,0,$mese+1,0,$anno)>=strtotime($data_inizio_coll))
		{
			if(($data_fine_coll!="0000-00-00")&&(mktime(0,0,0,$mese,1,$anno)>strtotime($data_fine_coll)))
			{
				$mese=my_date_format($data_fine_coll,"n");
				$anno=my_date_format($data_fine_coll,"Y");
			}
		}
		else
		{
			$mese=my_date_format($data_inizio_coll,"n");
			$anno=my_date_format($data_inizio_coll,"Y");
		}

		$monthdays=date("d",mktime(0,0,0,$mese+1,0,$anno));
		if(((int)substr($data_fine_coll,5,2))==$mese)
			$monthdays=((int)substr($data_fine_coll,8,2));

		if((((int)substr($data_inizio_coll,5,2))==$mese)
				&&((int)substr($data_inizio_coll,0,4))==$anno)
			$monthdays-=(((int)substr($data_inizio_coll,8,2))-1);


		$query="SELECT * FROM presenze WHERE id=".$id." AND year(giorno)=$anno AND month(giorno)=$mese";
		$result=mysqli_query($conn, $query)
			or die($query."<br>".mysqli_error($conn));

		if(mysqli_num_rows($result)<$monthdays)
		{
			if(mktime(0,0,0,$mese,1,$anno)>=strtotime($data_inizio_coll))
				$ts_inizio=mktime(0,0,0,$mese,1,$anno);
			else
				$ts_inizio=strtotime($data_inizio_coll);
			if(($data_fine_coll=="0000-00-00")||(mktime(0,0,0,$mese+1,0,$anno)<strtotime($data_fine_coll)))
				$ts_fine=mktime(0,0,0,$mese+1,0,$anno);
			else
				$ts_fine=strtotime($data_fine_coll);

			for($ts=$ts_inizio;$ts<=$ts_fine;$ts=mktime(0,0,0,date("n",$ts),date("d",$ts)+1,date("Y",$ts)))
			{
				$giorno=date("Y-m-d",$ts);
				$result2=mysqli_query($conn, "SELECT * FROM presenze WHERE id=".$id." AND giorno='$giorno'");
				if(!mysqli_num_rows($result2))
				{
					$weekend=(date("w",$ts)%6?0:1);

					$min_inizio=$_SESSION["ingresso_def"];
					$min_fine=$_SESSION["uscita_def"];
					$min_pausa=$_SESSION["pausa_def"];
					if((((int)$min_inizio)==0)||(((int)$min_fine)==0))
					{
						switch($id)
						{
							case 1:
								$min_inizio=465;
								$min_fine=990;
								$min_pausa=45;
								break;
							case 65:
								$min_inizio=480;
								$min_fine=1020;
								$min_pausa=60;
								break;
							default :
								$min_inizio=495;
								$min_fine=1020;
								$min_pausa=45;
								break;
						}
					}

					$query="INSERT INTO presenze(id,giorno,ingresso,pausa,uscita,festivo,id_commessa,tr_euri_km)
								VALUES('".$id."',
								'$giorno',
								'$min_inizio',
								'$min_pausa',
								'$min_fine',
								'$weekend',
								'".$id_commessa."',
								'".$tr_euri_km."')";
					mysqli_query($conn, $query)
						or die($query."<br>".mysqli_error($conn));
				}
				((mysqli_free_result($result2) || (is_object($result2) && (get_class($result2) == "mysqli_result"))) ? true : false);
			}
			//feste fisse
			$query="SELECT * FROM feste";
			$result1=mysqli_query($conn, $query)
				or die($query."<br>".mysqli_error($conn));

			while($row=mysqli_fetch_assoc($result1))
			{
				$query="UPDATE presenze SET festivo=1
					where id='".$id."'
						AND month(giorno)='$mese'
						AND year(giorno)='$anno'
						AND giorno='$anno-".$row["festa"]."'";
				mysqli_query($conn, $query)
					or die($query."<br>".mysqli_error($conn));
			}
			((mysqli_free_result($result1) || (is_object($result1) && (get_class($result1) == "mysqli_result"))) ? true : false);

			//unita d'Italia
				$query="UPDATE presenze SET festivo=1
					where id='".$id."' AND giorno='2011-03-17'";
				mysqli_query($conn, $query)
					or die($query."<br>".mysqli_error($conn));

			//pasqua
			$query="UPDATE presenze SET festivo=1
				WHERE id='".$id."'
					AND giorno IN (SELECT pasqua FROM pasqua WHERE year(pasqua)='$anno'
									AND month(pasqua)='$mese')";
			mysqli_query($conn, $query)
				or die($query."<br>".mysqli_error($conn));
			//patrono
			if(strlen($patrono))
			{
				$query="UPDATE presenze SET festivo=1
						WHERE id='".$id."'
							AND month(giorno)='$mese'
							AND year(giorno)='$anno'
							AND giorno='$anno-".substr($patrono,2,2)."-".substr($patrono,0,2)."'";
				mysqli_query($conn, $query)
					or die($query."<br>".mysqli_error($conn));
			}

			//ferie schedulate
			$result2=mysqli_query($conn, "SELECT * FROM permessi_ferie
									WHERE id_utente=".$id."
									AND ((month(da)='$mese' AND year(da)='$anno')
										OR  (month(a)='$mese' AND year(a)='$anno'))
									AND stato=1");
			while($row2=mysqli_fetch_assoc($result2))
			{
				if((substr($row2["da"],11,8)=="00:00:00")&&(substr($row2["a"],11,8)=="00:00:00"))
				{
					if(strtotime($row2["da"])<mktime(0,0,0,$mese,1,$anno))
						$da=date("Y-m-d",mktime(0,0,0,$mese,1,$anno));
					else
						$da=substr($row2["da"],0,10);
					if(strtotime($row2["a"])>mktime(0,0,0,$mese+1,0,$anno))
						$a=date("Y-m-d",mktime(0,0,0,$mese+1,0,$anno));
					else
						$a=substr($row2["a"],0,10);

					$query="UPDATE presenze SET ferie=1
							WHERE id=".$id."
							AND giorno BETWEEN '$da' AND '$a'
							AND festivo=0";
					mysqli_query($conn, $query)
						or die($query."<br>".mysqli_error($conn));
				}
				else
				{
					$stringa="Permesso dalle ".substr($row2["da"],11,5)." alle ".substr($row2["a"],11,5);
					$query="UPDATE presenze SET note='$stringa'
							WHERE id='".$id."'
							AND giorno='".substr($row2["da"],0,10)."'";
					mysqli_query($conn, $query)
						or die($query."<br>".mysqli_error($conn));
				}
			}
			((mysqli_free_result($result2) || (is_object($result2) && (get_class($result2) == "mysqli_result"))) ? true : false);

			$query="UPDATE presenze SET ingresso=-1,uscita=-1,ingresso2=-1,uscita2=-1,pausa=-1,id_commessa=-1
				WHERE id='".$id."'
					AND year(giorno)='$anno'
					AND month(giorno)='$mese'
					AND (festivo=1 OR ferie=1)";
			mysqli_query($conn, $query)
				or die($query."<br>".mysqli_error($conn));

			$query="SELECT * FROM utenti_trasferte WHERE id='".$id."'";
			$result3=mysqli_query($conn, $query)
				or die($query."<br>".mysqli_error($conn));
			if(mysqli_num_rows($result3))
			{
				$row3=mysqli_fetch_assoc($result3);
				$query="UPDATE presenze SET
							tr_destinazione='".$row3["tr_destinazione"]."',
							tr_motivo='".$row3["tr_motivo"]."',
							tr_ind_forf='".$row3["tr_ind_forf"]."',
							tr_km='".$row3["tr_km"]."'
						WHERE id='".$id."'
							AND year(giorno)='$anno'
							AND month(giorno)='$mese' AND festivo!=1 AND ferie!=1";
				mysqli_query($conn, $query)
					or die($query."<br>".mysqli_error($conn));
			}
			((mysqli_free_result($result3) || (is_object($result3) && (get_class($result3) == "mysqli_result"))) ? true : false);
		}
		((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
	}

function queryWithRollback($conn,$query)
{
	$result=mysqli_query($conn, $query);
	if(!$result)
	{
		$error=mysqli_error($conn);
		mysqli_query($conn, "ROLLBACK");
		die("$query<br>$error");
	}
	return (is_null($___mysqli_res = mysqli_insert_id($conn)) ? false : $___mysqli_res);
}

?>
