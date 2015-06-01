<?
	require_once("const.php");

	function nonullString($str)
	{
		return (strlen($str)?safeString($str):"----");
	}

	function doHome(&$mese,&$anno)
	{
		ob_start();
		echo tabella_presenze($_SESSION["id"],$mese,$anno,0);
?>
		
<?
		return ob_get_clean();
	}
	$op=(isset($_REQUEST["op"])?$_REQUEST["op"]:"");
	switch($op)
	{
		case "goHome":
			$mese=(isset($_REQUEST["mese"])?$_REQUEST["mese"]:date("m"));
			$anno=(isset($_REQUEST["anno"])?$_REQUEST["anno"]:date("Y"));
			$content=doHome($mese,$anno);

			$timestamp = mktime(0, 0, 0, sprintf("%02s", $mese), 10);
			$monthName = date("F", $timestamp);
			$header=sprintf("%s - %s %d",$_SESSION["name"],$monthName,$anno);
			$footer="";
			$out=array
			(
				"header"=>$header,
				"footer"=>$footer,
				"content"=>$content,
				"mese"=>$mese,
				"anno"=>$anno,
			);
			echo json_encode($out);
			break;
		default:
			break;
	}

function tabella_presenze($id_utente,&$mese,&$anno,$trasf)
{
	require_once("datetime.php");
	require_once("mysql.php");
	$ore_lav=480;

	$conn=new mysqlConnection;
	$query="SELECT data_inizio_coll,data_fine_coll
		FROM utenti WHERE id='$id_utente'";
	$result=$conn->do_query($query);
	$date=$conn->result_to_array($result,false);
	if(count($date)!=1)
		return;
	$data_inizio_coll=$date[0]["data_inizio_coll"];
	$data_fine_coll=$date[0]["data_fine_coll"];

	$da_data=date("Y-m-d",mktime(0,0,0,$mese,1,$anno));
	$a_data=date("Y-m-d",mktime(0,0,0,$mese+1,0,$anno));

	if($da_data<$data_inizio_coll)
	{
		$da_data=$data_inizio_coll;
		list($anno,$mese,$foo)=explode("-",$da_data);
		$a_data=date("Y-m-d",mktime(0,0,0,$mese+1,0,$anno));
	}
	if(($data_fine_coll!="0000-00-00")&&($a_data>$data_fine_coll))
	{
		$a_data=$data_fine_coll;
		list($na,$nm,$foo)=explode("-",$a_data);
		if(($na!=$anno)||($nm!=$mese))
			$da_data=date("Y-m-d",mktime(0,0,0,$nm,1,$na));
	}
	list($anno,$mese,$foo)=explode("-",$a_data);
	$mese=(int)$mese;
	fillPresenze($id_utente,$da_data,$a_data);

	$query="SELECT codice,codice2,descrizione,importo
		FROM trasferte ORDER BY descrizione";
	$result=$conn->do_query($query);
	$trasferte=$conn->result_to_array($result,true);

	$query="SELECT commesse.commessa FROM commesse";
	$result=$conn->do_query($query);
	$commesse=$conn->result_to_array($result,true);
	$commesse[-1]="----";

	$query="SELECT presenze.*,
				trasferte.codice,trasferte.codice2,trasferte.descrizione,trasferte.importo,
				commesse.commessa
			FROM presenze
			LEFT JOIN trasferte ON presenze.trasferta=trasferte.id
			LEFT JOIN commesse ON presenze.id_commessa=commesse.id
		WHERE presenze.id='$id_utente' 
			AND giorno BETWEEN '$da_data' AND '$a_data' 
		ORDER BY giorno";
	$result=$conn->do_query($query);
	$rows=$conn->result_to_array($result,false);
	$conn=null;
	$tabella=array();
	if(count($rows))
	{
		$str_on=0;
		$per_on=0;
		$sst_on=0;
		$via_on=0;
		$tra_on=0;
		$fer_on=0;
		$mal_on=0;
		$com_on=0;
		$trasf_on=0;
		$note_on=0;

		foreach($rows as $row)
		{
			$str_on+=($row["ingresso2"]!=-1);
			$ore_lav_eff=((format_minute($row["uscita2"])-format_minute($row["ingresso2"])+1440)%1440)+((format_minute($row["uscita"])-format_minute($row["ingresso"])+1440)%1440)-format_minute($row["pausa"]);
			$per_on+=(((!$row["festivo"])&&($ore_lav_eff>0)&&($ore_lav_eff<$ore_lav))||($row["forza_ore_perm"]!=-1));
			$sst_on+=(($row["ore_str"]!=-1)||($row["forza_ore_str"]!=-1));
			$via_on+=(($row["ore_via"]!=-1)||($row["forza_ore_via"]!=-1));
			$tra_on+=(($_SESSION["livello"]!=2)&&($row["trasferta"]!=-1));
			$fer_on+=($row["ferie"]!=0);
			$mal_on+=($row["malattia"]!=0);
			$com_on+=(($_SESSION["livello"]!=2)&&($row["id_commessa"]!=-1));
			$note_on+=(($_SESSION["livello"]!=2)&&(strlen($row["note"])!=0));

			if($trasf&&($_SESSION["livello"]==1))
				$trasf_on+=(strlen($row["tr_destinazione"])&&strlen($row["tr_motivo"]));
		}

		foreach($rows as $row)
		{
			$line=array();
			$line["giorno"]=date("d D",strtotime($row["giorno"]));
			$line["__hidden__festivo"]=$row["festivo"];
			if(!$trasf)
			{
				$line["Ingresso<br>Mattino"]=int_to_hour($row["ingresso"]);
				$line["Pausa<br>Pranzo"]=int_to_hour($row["pausa"]);
				$line["Uscita<br>Sera"]=int_to_hour($row["uscita"]);
				if($str_on)
				{
					$line["Extra<br>Ingresso"]=int_to_hour($row["ingresso2"]);
					$line["Extra<br>Uscita"]=int_to_hour($row["uscita2"]);
				}
				$ore_lav_eff=((format_minute($row["uscita2"])-format_minute($row["ingresso2"])+1440)%1440)+((format_minute($row["uscita"])-format_minute($row["ingresso"])+1440)%1440)-format_minute($row["pausa"]);
				if($ore_lav_eff==0)
					$ore_lav_eff=-1;

				if($row["forza_ore_giorn"]!=-1)
					$line["Ore<br>Giorn"]=int_to_hour($row["forza_ore_giorn"]);
				else
					$line["Ore<br>Giorn"]=($row["festivo"]?"----":int_to_hour($ore_lav_eff>$ore_lav?$ore_lav:$ore_lav_eff));

				if($per_on)
				{
					if($row["forza_ore_perm"]!=-1)
						$line["Ore<br>Perm"]=int_to_hour($row["forza_ore_perm"]);
					else
						$line["Ore<br>Perm"]=($row["festivo"]?"----":int_to_hour((($ore_lav_eff<>-1)&&($ore_lav_eff<$ore_lav))?$ore_lav-$ore_lav_eff:-1));
				}
				if($sst_on)
				{
					if($row["forza_ore_str"]!=-1)
						$line["Ore<br>Str"]=int_to_hour($row["forza_ore_str"]);
					else
						$line["Ore<br>Str"]=int_to_hour($row["ore_str"]);
				}
				if($via_on)
				{
					if($row["forza_ore_via"]!=-1)
						$line["Ore<br>Viaggio"]=int_to_hour($row["forza_ore_via"]);

					$line["Ore<br>Viaggio"]=int_to_hour($row["ore_via"]);
				}
				if($tra_on)
				{
					$line["Trasferta"]=nonullString($row["codice"]);
					$line["__hidden__trasferta"]=nonullString($row["descrizione"]);
					$line["__hidden__tr_euri"]=nonullString($row["importo"]);
					$line["__hidden__tr_cod2"]=nonullString($row["codice2"]);
				}
				if($fer_on)
					$line["Ferie"]=($row["ferie"]?"<img src='../img/check.png' alt='check'>":"");
				if($mal_on)
					$line["Malattia"]=($row["malattia"]?"<img src='../img/check.png' alt='check'>":"");
				if($com_on)
					$line["__hidden__Commessa"]=$row["commessa"];
				if($note_on)
					$line["Note"]=$row["note"];

			}
			else
			{
				if(strlen($row["tr_destinazione"])&&strlen($row["tr_motivo"]))
				{
					$line["Destinazione"]=$row["tr_destinazione"];
					$line["Motivo"]=$row["tr_motivo"];
					$line["Ind.<br>Forf"]=$row["tr_ind_forf"];
					$line["Spese<br>viaggio"]=$row["tr_spese_viaggio"];
					$line["Spese<br>alloggio"]=$row["tr_spese_alloggio"];
					$line["Spese<br>di vitto"]=$row["tr_spese_vitto"];
					$line["KM<br>percorsi"]=$row["tr_km"];
					$line["Rimborso<br>al KM"]=$row["tr_euri_km"];
				}
				else
				{
					$line["Destinazione"]="----";
					$line["Motivo"]="----";
					$line["Ind.<br>Forf"]="----";
					$line["Spese<br>viaggio"]="----";
					$line["Spese<br>alloggio"]="----";
					$line["Spese<br>di vitto"]="----";
					$line["KM<br>percorsi"]="----";
					$line["Rimborso<br>al KM"]="----";
				}
			}
			$tabella[]=$line;
		}
	}
	return json_encode($tabella);
}


function fillPresenze($id,$da_data,$a_data)
{
	require_once("datetime.php");
	require_once("mysql.php");

	list($anno,$mese,$foo)=explode("-",$da_data);
	$mese=(int)$mese;

	$conn=new mysqlConnection;
	$query="SELECT commessa_default,patrono,tr_euri_km,
			ingresso_def,uscita_def,pausa_def
		FROM utenti WHERE id='$id'";
	$result=$conn->do_query($query);


	$date=$conn->result_to_array($result,false);
	if(count($date)!=1)
		return;
	$id_commessa_def=$date[0]["commessa_default"];
	$tr_euri_km_def=$date[0]["tr_euri_km"];
	$ingresso_def=$date[0]["ingresso_def"];
	$uscita_def=$date[0]["uscita_def"];
	$pausa_def=$date[0]["pausa_def"];
	$patrono=$date[0]["patrono"];
	$note_def="";

	$query="SELECT CONCAT('$anno','-',festa) AS festa FROM feste";

	$result=$conn->do_query($query);
	$feste=$conn->result_to_array($result,false);

	$query="SELECT pasqua FROM pasqua
		WHERE year(pasqua)='$anno' 
		AND month(pasqua)='$mese'";
	$result=$conn->do_query($query);
	$pasqua=$conn->result_to_array($result,false);
	if(count($pasqua))
		$pasqua=$pasqua[0]["pasqua"];
	else
		$pasqua="";

	$patrono=sprintf("%d-%02d-%02d",$anno,substr($patrono,2,2),substr($patrono,0,2));

	$query="SELECT * FROM permessi_ferie
				WHERE id_utente=".$id."
				AND ((month(da)='$mese' AND year(da)='$anno') 
					OR  (month(a)='$mese' AND year(a)='$anno'))
				AND stato=1";
	$result=$conn->do_query($query);
	$pf=$conn->result_to_array($result,false);

	$query="SELECT * FROM utenti_trasferte WHERE id='".$id."'";
	$result=$conn->do_query($query);
	$trasferte=$conn->result_to_array($result,false);
	if(count($trasferte))
	{
		$tr_destinazione_def=$trasferte[0]["tr_destinazione"];
		$tr_motivo_def=$trasferte[0]["tr_motivo"];
		$tr_ind_forf_def=$trasferte[0]["tr_ind_forf"];
		$tr_km_def=$trasferte[0]["tr_km"];
	}
	else
	{
		$tr_destinazione_def="";
		$tr_motivo_def="";
		$tr_ind_forf_def=0;
		$tr_km_def=0;
	}

	$giorno=$da_data;
	while($giorno<=$a_data)
	{
		$date=new DateTime($giorno, new DateTimeZone('Europe/Rome'));
		$festivo=(($date->format('w'))%6?0:1);

		//feste fisse
		foreach($feste as $festa)
			if($festa["festa"]==$giorno)
				$festivo=1;
		//pasqua
		if($giorno==$pasqua)
			$festivo=1;
		//patrono
		if($giorno==$patrono)
			$festivo=1;
		//ferie
		$ferie=0;
		if(!$festivo)
		{
			foreach($pf AS $f)
			{
				$data_da=substr($f["da"],0,10);
				$ora_da=substr($f["da"],11);
				$data_a=substr($f["a"],0,10);
				$ora_a=substr($f["a"],11);
	
				if(($ora_da=="00:00:00")&&($ora_a=="00:00:00"))
				{
					if(($giorno>=$data_da)&&
							($giorno<=$data_da))
						$ferie=1;
				}
				else
				{
					if(($giorno>=$data_da)&&
							($giorno<=$data_a))
						$note="Permesso dalle $ora_da alle $ora_a";
				}
				
			}
		}
		//trasferte
		if($ferie||$festivo)
		{
			$tr_destinazione="";
			$tr_motivo="";
			$tr_ind_forf=0; 
			$tr_km=0;
			$id_commessa=-1;
			$ingresso=-1;
			$uscita=-1;
			$pausa=-1;
			$tr_euri_km=0;
		}
		else
		{
			$tr_destinazione=$tr_destinazione_def;
			$tr_motivo=$tr_motivo_def;
			$tr_ind_forf=$tr_ind_forf_def; 
			$tr_km=$tr_km_def;
			$id_commessa=$id_commessa_def;
			$ingresso=$ingresso_def;
			$pausa=$pausa_def;
			$uscita=$uscita_def;
			$tr_euri_km=$tr_euri_km_def;
		}
		

		$query="INSERT INTO presenze(id,giorno,ingresso,pausa,uscita,
			festivo,ferie,id_commessa,tr_euri_km,tr_ind_forf,tr_km,tr_destinazione,tr_motivo) 
					VALUES('$id',
					'$giorno',
					'$ingresso',
					'$pausa',
					'$uscita',
					'$festivo',
					'$ferie',
					'$id_commessa',
					'$tr_euri_km',
					'$tr_ind_forf',
					'$tr_km',
					'$tr_destinazione',
					'$tr_motivo')
			ON DUPLICATE KEY UPDATE ingresso=ingresso,pausa=pausa,uscita=uscita,
				festivo=festivo,ferie=ferie,id_commessa=id_commessa,tr_euri_km=tr_euri_km,
				tr_ind_forf=tr_ind_forf,tr_km=tr_km,tr_destinazione=tr_destinazione,
				tr_motivo=tr_motivo";

		$conn->do_query($query);

		$date->add(new DateInterval('P1D'));
		$giorno=$date->format('Y-m-d');
	}
	$conn=null;
}


?>
