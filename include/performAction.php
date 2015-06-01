<?
	if(isset($_POST["edited"]))
	{
		$giorno=stripcslashes($_POST["giorno"]);
		$multi=(strchr($giorno,","));
		if(!isset($_POST["trasf"]))
		{
			$fields=array("festivo","malattia","ferie","note","trasferta",
						"ingresso","uscita","pausa","ingresso2","uscita2",
						"ore_str","forza_ore_str","ore_via","forza_ore_via",
						"forza_ore_giorn","forza_ore_perm","id_commessa");
			$values=array();

			if(hour_to_int($_POST["ore_str"])!=-1)
				$int_ore_str=((int)(hour_to_int($_POST["ore_str"])/15))*15;
			else
				$int_ore_str=-1;
			if($_SESSION["livello"]==1)
			{
				if(hour_to_int($_POST["forza_ore_via"])!=-1)
					$_POST["ore_via"]=$_POST["forza_ore_via"];
				if(hour_to_int($_POST["forza_ore_str"])!=-1)
					$_POST["ore_str"]=$_POST["forza_ore_str"];
			}
			else
			{
				$_POST["forza_ore_via"]="----";
				$_POST["forza_ore_giorn"]="----";
				$_POST["forza_ore_perm"]="----";
				$_POST["forza_ore_str"]="----";
			}
			$values["ferie"]=(isset($_POST["ferie"])?1:0);
			$values["malattia"]=(isset($_POST["malattia"])?1:0);
			$values["festivo"]=((isset($_POST["festivo"])&&($_POST["festivo"]==1))?1:0);
			$values["note"]=trim($_POST["note"]);
			$values["trasferta"]=$_POST["trasferta"];
			$values["ingresso"]=hour_to_int($_POST["ingresso"]);
			$values["uscita"]=hour_to_int($_POST["uscita"]);
			$values["pausa"]=hour_to_int($_POST["pausa"]);
			$values["ingresso2"]=hour_to_int($_POST["ingresso2"]);
			$values["uscita2"]=hour_to_int($_POST["uscita2"]);
			$values["ore_str"]=$int_ore_str;
			$values["forza_ore_str"]=hour_to_int($_POST["forza_ore_str"]);
			$values["ore_via"]=hour_to_int($_POST["ore_via"]);
			$values["forza_ore_via"]=hour_to_int($_POST["forza_ore_via"]);
			$values["forza_ore_giorn"]=hour_to_int($_POST["forza_ore_giorn"]);
			$values["forza_ore_perm"]=hour_to_int($_POST["forza_ore_perm"]);
			$values["id_commessa"]=$_POST["id_commessa"];
		}
		else
		{
			if(!strlen($_POST["tr_destinazione"]))
			{
				$_POST["tr_ind_forf"]=0.00;
				$_POST["tr_spese_viaggio"]=0.00;
				$_POST["tr_spese_alloggio"]=0.00;
				$_POST["tr_spese_vitto"]=0.00;
				$_POST["tr_km"]=0.00;
				$_POST["tr_euri_km"]=0.00;
//				$_POST["tr_euri_km"]=$_SESSION["tr_euri_km"];
			}
			$fields=array("tr_destinazione","tr_motivo","tr_ind_forf","tr_spese_viaggio"
						,"tr_spese_alloggio","tr_spese_vitto","tr_km","tr_euri_km");
			$values=array();
			$values["tr_destinazione"]=trim($_POST["tr_destinazione"]);
			$values["tr_motivo"]=trim($_POST["tr_motivo"]);
			$values["tr_ind_forf"]=$_POST["tr_ind_forf"];
			$values["tr_spese_viaggio"]=$_POST["tr_spese_viaggio"];
			$values["tr_spese_alloggio"]=$_POST["tr_spese_alloggio"];
			$values["tr_spese_vitto"]=$_POST["tr_spese_vitto"];
			$values["tr_km"]=$_POST["tr_km"];
			$values["tr_euri_km"]=$_POST["tr_euri_km"];
			$op="displaytrasf";
		}
		if($multi)
			foreach($values as $k=>$v)
				if(!strlen($_POST[$k."_c"]))
					unset($values[$k]);

		if(count($values))
		{
			$query="UPDATE presenze SET ";
			foreach($values as $k=>$v)
				$query.=" $k='$v',";
			$query=rtrim($query,",");
			$query.=" WHERE giorno IN ($giorno) AND id='".$_SESSION["id_edit"]."'";

			$conn=mysqli_connect($myhost, $myuser, $mypass);
			((bool)mysqli_query($conn, "USE " . $dbname)) or die(mysqli_error($conn));
			@mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));
			$message=(mysqli_affected_rows($conn)>0?"Modifica Effettuata":"");
			((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
		}
	}
	elseif(isset($_POST["edit_user"])&&($_SESSION["livello"]==1))
	{
		$buoni_pasto=(isset($_POST["buoni_pasto"])?1:0);
		$solo_trasf=(isset($_POST["solo_trasf"])?1:0);
		$expired=(isset($_POST["expired"])?1:0);
		$stampe=(isset($_POST["stampe"])?1:0);
		$attivo=(isset($_POST["attivo"])?1:0);
		$ingresso_def=(hour_to_int($_POST["ingresso_def"]));
		$pausa_def=(hour_to_int($_POST["pausa_def"]));
		$uscita_def=(hour_to_int($_POST["uscita_def"]));
		$conn=mysqli_connect($myhost, $myuser, $mypass);
		((bool)mysqli_query($conn, "USE " . $dbname)) or die(mysqli_error($conn));
		if($_POST["data_inizio_coll"]!="----")
		{
			$query="DELETE FROM presenze
				WHERE ID='".$_POST["id_user"]."'
				AND giorno<'".date_to_sql($_POST["data_inizio_coll"])."'";
			@mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));
		}
		if($_POST["data_fine_coll"]!="----")
		{
			$query="DELETE FROM presenze WHERE ID='".$_POST["id_user"]."'
				AND giorno>'".date_to_sql($_POST["data_fine_coll"])."'";
			@mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));
		}
		$query="UPDATE utenti SET
					login='".trim($_POST["utente"])."',
					nome='".trim($_POST["nome"])."',
					cognome='".trim($_POST["cognome"])."',
					livello='".$_POST["livello"]."',
					expired='$expired',
					buoni_pasto='$buoni_pasto',
					stampe='$stampe',
					attivo='$attivo',
					solo_trasf='$solo_trasf',
					data_inizio_coll='".date_to_sql($_POST["data_inizio_coll"])."',
					data_fine_coll='".date_to_sql($_POST["data_fine_coll"])."',
					blocca_a_data='".date_to_sql($_POST["blocca_a_data"])."',
					ditta='".$_POST["ditta"]."',
					commessa_default='".$_POST["commessa"]."',
					patrono='".$_POST["patrono"]."',
					ingresso_def='$ingresso_def',
					pausa_def='$pausa_def',
					uscita_def='$uscita_def',
					tr_euri_km='".$_POST["tr_euri_km"]."'
				WHERE id='".$_POST["id_user"]."'";
		@mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));

		$query="DELETE FROM utenti_trasferte
			WHERE id='".$_POST["id_user"]."'";
		@mysqli_query($conn, $query)
			or die($query."<br>".mysqli_error($conn));
		$tr_destinazione=trim($_POST["tr_destinazione"]);
		$tr_motivo=trim($_POST["tr_motivo"]);
		$tr_ind_forf=str_replace(",",".",$_POST["tr_ind_forf"]);
		$tr_km=str_replace(",",".",$_POST["tr_km"]);
		if(strlen($tr_destinazione)&&strlen($tr_motivo))
		{

			$query="INSERT INTO utenti_trasferte
				(id,tr_destinazione,tr_motivo,tr_ind_forf,tr_km)
				VALUES ('".$_POST["id_user"]."','$tr_destinazione',
					'$tr_motivo','$tr_ind_forf','$tr_km')";
			@mysqli_query($conn, $query)
				or die($query."<br>".mysqli_error($conn));
		}

		((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
		$op="admin_users";
		$message="Modifica effettuata";
		$admin_action="list_users";
		if($_POST["id_user"]==$_SESSION["id_edit"])
		{
			$_SESSION["blocca_a_data"]=date_to_sql($_POST["blocca_a_data"]);
			$_SESSION["data_inizio_coll"]=date_to_sql($_POST["data_inizio_coll"]);
			$_SESSION["data_fine_coll"]=date_to_sql($_POST["data_fine_coll"]);
			$_SESSION["id_commessa"]=$_POST["commessa"];
			$_SESSION["tr_euri_km"]=$_POST["tr_euri_km"];
			$_SESSION["solo_trasf"]=$solo_trasf;
		}
	}
	elseif(isset($_POST["add_user"])&&($_SESSION["livello"]==1))
	{
		$solo_trasf=(isset($_POST["solo_trasf"])?1:0);
		$conn=mysqli_connect($myhost, $myuser, $mypass);
		((bool)mysqli_query($conn, "USE " . $dbname)) or die(mysqli_error($conn));
		$query="INSERT INTO utenti(login,pass,nome,cognome,livello,
					expired,data_inizio_coll,data_fine_coll,patrono,
					ditta,commessa_default,tr_euri_km,solo_trasf)
				VALUES('".trim($_POST["utente"])."',
					md5('service'),
					'".trim($_POST["nome"])."',
					'".trim($_POST["cognome"])."',
					'".$_POST["livello"]."',
					1,
					'".date_to_sql($_POST["data_inizio_coll"])."',
					'".date_to_sql($_POST["data_fine_coll"])."',
					'".$_POST["patrono"]."',
					'".$_POST["ditta"]."',
					'".$_POST["commessa"]."',
					'".$_POST["tr_euri_km"]."',
					'$solo_trasf' )";

		@mysqli_query($conn, $query)
			or die($query."<br>".mysqli_error($conn));
		$tr_destinazione=str_replace("'","`",trim($_POST["tr_destinazione"]));
		$tr_motivo=str_replace("'","`",trim($_POST["tr_motivo"]));
		$tr_ind_forf=str_replace(",",".",$_POST["tr_ind_forf"]);
		$tr_km=str_replace(",",".",$_POST["tr_km"]);
		if(strlen($tr_destinazione)&&strlen($tr_motivo))
		{
			$user_id=(is_null($___mysqli_res = mysqli_insert_id($conn)) ? false : $___mysqli_res);
			$query="INSERT INTO utenti_trasferte
				(id,tr_destinazione,tr_motivo,tr_ind_forf,tr_km)
				VALUES ('$user_id','$tr_destinazione',
					'$tr_motivo','$tr_ind_forf','$tr_km')";
			@mysqli_query($conn, $query)
				or die($query."<br>".mysqli_error($conn));
		}
		((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
		$op="admin_users";
		$message="Utente inserito";
		$admin_action="list_users";
	}
	elseif(isset($_POST["add_ditta"])&&($_SESSION["livello"]==1))
	{
		$conn=mysqli_connect($myhost, $myuser, $mypass);
		((bool)mysqli_query($conn, "USE " . $dbname)) or die(mysqli_error($conn));
		$query="INSERT INTO ditte(ragione_sociale)
			VALUES('".trim($_POST["ragione_sociale"])."')";
		@mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));
		((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
		$op="admin_ditte";
		$message="Ditta inserita";
		$admin_action="list_ditte";
	}
	elseif(isset($_POST["edit_ditta"])&&($_SESSION["livello"]==1))
	{
		$conn=mysqli_connect($myhost, $myuser, $mypass);
		((bool)mysqli_query($conn, "USE " . $dbname)) or die(mysqli_error($conn));
		$query="UPDATE ditte
				SET ragione_sociale='".trim($_POST["ragione_sociale"])."'
				WHERE id='".$_POST["id_ditta"]."'";
		@mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));
		((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
		$op="admin_ditte";
		$message="Ditta modificata";
		$admin_action="list_ditte";
	}
	elseif(isset($_POST["add_commessa"])&&($_SESSION["livello"]==1))
	{
		$conn=mysqli_connect($myhost, $myuser, $mypass);
		((bool)mysqli_query($conn, "USE " . $dbname)) or die(mysqli_error($conn));
		$query="INSERT INTO commesse(commessa,descrizione)
			VALUES('".trim($_POST["commessa"])."'
				,'".trim($_POST["descrizione"])."')";
		@mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));
		((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
		$op="admin_commesse";
		$message="Commessa inserita";
		$admin_action="list_commesse";
	}
	elseif(isset($_POST["edit_commessa"])&&($_SESSION["livello"]==1))
	{
	$conn=mysqli_connect($myhost, $myuser, $mypass);
		((bool)mysqli_query($conn, "USE " . $dbname)) or die(mysqli_error($conn));
		$query="UPDATE commesse SET
				commessa='".trim($_POST["commessa"])."',
				descrizione='".trim($_POST["descrizione"])."',
				visibile='".(isset($_POST["visibile"])?1:0)."'
			WHERE id='".$_POST["id_commessa"]."'";
		@mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));
		((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
		$op="admin_commesse";
		$message="Commessa modificata";
		$admin_action="list_commesse";
	}
	elseif(isset($_POST["add_trasf"])&&($_SESSION["livello"]==1))
	{
		$_POST["importo"]=str_replace(",",".",$_POST["importo"]);
		$descrizione=str_replace("'","\'",trim($_POST["descrizione"]));
		$codice=str_replace("'","\'",trim($_POST["codice"]));
		$codice2=str_replace("'","\'",trim($_POST["codice2"]));
		$paese_id=($_POST["paese_id"]>0?$_POST["paese_id"]:"NULL");
		$importo=$_POST["importo"];
		$enabled=(isset($_POST["enabled"])?1:0);

		$conn=mysqli_connect($myhost, $myuser, $mypass);
		((bool)mysqli_query($conn, "USE " . $dbname)) or die(mysqli_error($conn));
		$query="INSERT INTO trasferte(codice,codice2,paese_id,descrizione,importo,enabled)
			VALUES('$codice',
				'$codice2',
				$paese_id,
				'$descrizione',
				'$importo',
				'$descrizione')";
		@mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));
		((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
		$op="admin_trasf";
		$message="Trasferta inserita";
		$admin_action="list_trasf";
	}
	elseif(isset($_POST["edit_trasf"])&&($_SESSION["livello"]==1))
	{

		$_POST["importo"]=str_replace(",",".",$_POST["importo"]);
		$descrizione=str_replace("'","\'",trim($_POST["descrizione"]));
		$codice=str_replace("'","\'",trim($_POST["codice"]));
		$codice2=str_replace("'","\'",trim($_POST["codice2"]));
		$paese_id=($_POST["paese_id"]>0?$_POST["paese_id"]:"NULL");
		$importo=$_POST["importo"];
		$enabled=(isset($_POST["enabled"])?1:0);
		$conn=mysqli_connect($myhost, $myuser, $mypass);
		((bool)mysqli_query($conn, "USE " . $dbname)) or die(mysqli_error($conn));
		$query="UPDATE trasferte SET codice='$codice',
									codice2='$codice2',
									paese_id=$paese_id,
									descrizione='$descrizione',
									importo='$importo',
									enabled='$enabled'
									WHERE id='".$_POST["id_trasf"]."'";
		@mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));
		((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
		$op="admin_trasf";
		$message="Trasferta modificata";
		$admin_action="list_trasf";
	}
	elseif(isset($_POST["add_paese"])&&($_SESSION["livello"]==1))
	{
		$paese=str_replace("'","\'",trim($_POST["paese"]));
		$variante=str_replace("'","\'",trim($_POST["variante"]));
		$divisa=str_replace("'","\'",trim($_POST["divisa"]));
		$ord=str_replace(",",".",trim($_POST["ord"]));
		$base=str_replace(",",".",trim($_POST["base"]));
		$fest=str_replace(",",".",trim($_POST["fest"]));

		if(strlen($ord))
			$ordInsert="'$ord'";
		else
			$ordInsert="null";

		if(strlen($base))
			$baseInsert="'$base'";
		else
			$baseInsert="null";

		if(strlen($fest))
			$festInsert="'$fest'";
		else
			$festInsert="null";

		$conn=mysqli_connect($myhost, $myuser, $mypass);
		((bool)mysqli_query($conn, "USE " . $dbname)) or die(mysqli_error($conn));
		$query="INSERT INTO trasferte_paesi(paese,variante,divisa,ord,base,fest)
			VALUES('$paese','$variante','$divisa',$ordInsert,$baseInsert,$festInsert)";
		@mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));
		((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
		$op="admin_paesi";
		$message="Paese inserito";
		$admin_action="list_paesi";
	}
	elseif(isset($_POST["edit_paese"])&&($_SESSION["livello"]==1))
	{
		$id_paese=$_POST["id_paese"];
		$paese=str_replace("'","\'",trim($_POST["paese"]));
		$variante=str_replace("'","\'",trim($_POST["variante"]));
		$divisa=str_replace("'","\'",trim($_POST["divisa"]));
		$ord=str_replace(",",".",trim($_POST["ord"]));
		$base=str_replace(",",".",trim($_POST["base"]));
		$fest=str_replace(",",".",trim($_POST["fest"]));
		if(strlen($ord))
			$ordEdit="'$ord'";
		else
			$ordEdit="null";

		if(strlen($base))
			$baseEdit="'$base'";
		else
			$baseEdit="null";

		if(strlen($fest))
			$festEdit="'$fest'";
		else
			$festEdit="null";


		$conn=mysqli_connect($myhost, $myuser, $mypass);
		((bool)mysqli_query($conn, "USE " . $dbname)) or die(mysqli_error($conn));
		$query="UPDATE trasferte_paesi SET paese='$paese',
									variante='$variante',
									divisa='$divisa',
									ord=$ordEdit,
									base=$baseEdit,
									fest=$festEdit
									WHERE id='$id_paese'";

		@mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));
		((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
		$op="admin_paesi";
		$message="Paese modificato";
		$admin_action="list_paesi";
	}
	elseif(isset($_POST["add_foglio"]))
	{
		$conn=mysqli_connect($myhost, $myuser, $mypass);
		((bool)mysqli_query($conn, "USE " . $dbname)) or die(mysqli_error($conn));

		$ok=1;
/*		if($_POST["trattamento"]!="PDL")
		{
			$query="SELECT DISTINCT importo,descrizione FROM trasferte
					WHERE descrizione LIKE '%".substr($_POST["trattamento"],0,4)."%'
						AND descrizione LIKE '%".$_POST["paese"]."%'
						ORDER BY descrizione";
			$result=@mysql_query($query) or die($query."<br>".mysql_error());
			$ok=mysql_num_rows($result);
		}
*/
		if($ok)
		{
/*			if($_POST["trattamento"]!="PDL")
			{
				$row=mysql_fetch_assoc($result);
				$diaria=$row["importo"];
				mysql_free_result($result);
			}
			else
				$diaria=0;
*/
			$paese=str_replace("'","\'",$_POST["paese"]);
			$query="SELECT ord,base,fest FROM trasferte_paesi
				WHERE paese='$paese'";
			$result=mysqli_query($conn, $query)
				or die($query."<br>".mysqli_error($conn));
			if($row=mysqli_fetch_assoc($result))
			{
				if(!strlen($row["ord"]))
				{
					$giorni_ord=0;
					$cong_ord="NULL";
				}
				else
				{
					$giorni_ord=str_replace(",",".",$_POST["giorni_ord"]);
					$giorni_ord=((int)($giorni_ord*2))/2;
					$cong_ord=$row["ord"];
				}
				if(!strlen($row["base"]))
				{
					$giorni_base=0;
					$cong_base="NULL";
				}
				else
				{
					$giorni_base=str_replace(",",".",$_POST["giorni_base"]);
					$giorni_base=((int)($giorni_base*2))/2;
					$cong_base=$row["base"];
				}
				if(!strlen($row["fest"]))
				{
					$giorni_fest=0;
					$cong_fest="NULL";
				}
				else
				{
					$giorni_fest=str_replace(",",".",$_POST["giorni_fest"]);
					$giorni_fest=((int)($giorni_fest*2))/2;
					$cong_fest=$row["fest"];
				}
			}
			else
			{
				$giorni_fest=0;
				$giorni_base=0;
				$giorni_ord=0;
			}
			((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);

			$anno=date("Y",strtotime(date_to_sql($_POST["datainizio"])));
			$query="SELECT max(numero) as numero FROM fogliviaggio WHERE anno='$anno'";
			$result=mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));
			if($row=mysqli_fetch_assoc($result))
				$numero=1+$row["numero"];
			else
				$numero=1;
			((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
			if(isset($_POST["cambio"]))
				$cambio=str_replace(",",".",$_POST["cambio"]);
			else
				$cambio=1;

			$id_commessa=(isset($_POST["commessa"])?$_POST["commessa"]:0);
			$piedilista=(isset($_POST["piedilista"])?1:0);

			$query="INSERT INTO fogliviaggio(id_utente,loc,datainizio,orainizio,
						datafine,orafine,motivo,id_commessa,giorni_ord,giorni_base,
						giorni_fest,numero,anno,paese,cambio,cong_ord,cong_base,cong_fest,piedilista,locked)
					VALUES ('".$_SESSION["id_edit"]."',
						'".substr(trim($_POST["loc"]),0,20)."',
						'".date_to_sql($_POST["datainizio"])."',
						'".hour_to_int($_POST["orainizio"])."',
						'".date_to_sql($_POST["datafine"])."',
						'".hour_to_int($_POST["orafine"])."',
						'".substr(trim($_POST["motivo"]),0,20)."',
						'$id_commessa',
						'$giorni_ord',
						'$giorni_base',
						'$giorni_fest',
						'$numero',
						'$anno',
						'$paese',
						'$cambio',
						$cong_ord,
						$cong_base,
						$cong_fest,
						$piedilista,
						0)";
			@mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));
			$id_foglio=(is_null($___mysqli_res = mysqli_insert_id($conn)) ? false : $___mysqli_res);

			for($conta=0;$conta<DETTAGLIO_SPESE_LINES;$conta++)
			{
				$postvar_desc="desc_$conta";
				$postvar_imp="imp_$conta";
				$postvar_impdivisa="impdivisa_$conta";
				$imp=$_POST[$postvar_imp];
				$impdivisa=$_POST[$postvar_impdivisa];
				if(((float)$imp)>0)
					$impdivisa=0;
				$imp=to_number($imp);
				$impdivisa=to_number($impdivisa);
				$postvar_fatt_ric="fatt_ric_$conta";


				if((isset($_POST[$postvar_desc]))
					&&strlen($_POST[$postvar_desc])
					&&(
						(isset($_POST[$postvar_imp])&&strlen($_POST[$postvar_imp]))
						||(isset($_POST[$postvar_impdivisa])&&strlen($_POST[$postvar_impdivisa]))
					))
				{
					$query="INSERT INTO fogliviaggio_dettagliospese(
						id_foglio,riga,descrizione,importo,importo_divisa,fatt_ric)
							VALUES('$id_foglio',
								'$conta',
								'".substr(trim($_POST[$postvar_desc]),0,40)."',
								'".to_number($imp)."',
								'".to_number($impdivisa)."',
								'".$_POST[$postvar_fatt_ric]."')";
					@mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));
				}
			}
			((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
			$op="foglio_viaggio";
			$message="Foglio viaggio inserito";
			$foglio_action="list_fogli";
		}
	}
	elseif(isset($_POST["edit_foglio"]))
	{
		$conn=mysqli_connect($myhost, $myuser, $mypass);
		((bool)mysqli_query($conn, "USE " . $dbname)) or die(mysqli_error($conn));

		$ok=1;
/*		if($_POST["trattamento"]!="PDL")
		{
			$query="SELECT DISTINCT importo,descrizione FROM trasferte
					WHERE descrizione LIKE '%".substr($_POST["trattamento"],0,4)."%'
					AND descrizione LIKE '%".$_POST["paese"]."%' ORDER BY descrizione";
			$result=@mysql_query($query) or die($query."<br>".mysql_error());
			$ok=mysql_num_rows($result);
		}*/
		if($ok)
		{
/*			if($_POST["trattamento"]!="PDL")
			{
				$row=mysql_fetch_assoc($result);
				$diaria=$row["importo"];
				mysql_free_result($result);
			}
			else
				$diaria=0;
*/
			if(isset($_POST["cambio"]))
				$cambio=str_replace(",",".",$_POST["cambio"]);
			else
				$cambio=1;

			$paese=str_replace("'","\'",$_POST["paese"]);
			$query="SELECT ord,base,fest FROM trasferte_paesi
				WHERE paese='$paese'";
			$result=mysqli_query($conn, $query)
				or die($query."<br>".mysqli_error($conn));
			if($row=mysqli_fetch_assoc($result))
			{
				if(!strlen($row["ord"]))
				{
					$giorni_ord=0;
					$cong_ord="NULL";
				}
				else
				{
					$giorni_ord=str_replace(",",".",$_POST["giorni_ord"]);
					$giorni_ord=((int)($giorni_ord*2))/2;
					$cong_ord=$row["ord"];
				}
				if(!strlen($row["base"]))
				{
					$giorni_base=0;
					$cong_base="NULL";
				}
				else
				{
					$giorni_base=str_replace(",",".",$_POST["giorni_base"]);
					$giorni_base=((int)($giorni_base*2))/2;
					$cong_base=$row["base"];
				}
				if(!strlen($row["fest"]))
				{
					$giorni_fest=0;
					$cong_fest="NULL";
				}
				else
				{
					$giorni_fest=str_replace(",",".",$_POST["giorni_fest"]);
					$giorni_fest=((int)($giorni_fest*2))/2;
					$cong_fest=$row["fest"];
				}
			}
			else
			{
				$giorni_fest=0;
				$giorni_base=0;
				$giorni_ord=0;
			}
			((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);


			$id_commessa=(isset($_POST["commessa"])?$_POST["commessa"]:0);
			$piedilista=(isset($_POST["piedilista"])?1:0);

			mysqli_query($conn, "START TRANSACTION")
				or die("Start Transaction<br>".mysqli_error($conn));

			$query="UPDATE fogliviaggio SET
					datainizio='".date_to_sql($_POST["datainizio"])."',
					orainizio='".hour_to_int($_POST["orainizio"])."',
					datafine='".date_to_sql($_POST["datafine"])."',
					orafine='".hour_to_int($_POST["orafine"])."',
					motivo='".str_replace("'","\'",substr($_POST["motivo"],0,20))."',
					id_commessa='".$id_commessa."',
					loc='".str_replace("'","\'",substr(trim($_POST["loc"]),0,20))."',
					giorni_ord='".$giorni_ord."',
					giorni_base='".$giorni_base."',
					giorni_fest='".$giorni_fest."',
					cong_ord=".$cong_ord.",
					cong_base=".$cong_base.",
					cong_fest=".$cong_fest.",
					paese='$paese',
					cambio='$cambio',
					piedilista='$piedilista'";

			$anno=date("Y",strtotime(date_to_sql($_POST["datainizio"])));
			if($anno!=$_POST["anno_prec"])
			{
				$nquery="SELECT max(numero) AS numero FROM fogliviaggio WHERE anno='$anno'";
				$result=mysqli_query($conn, $nquery) or die($nquery."<br>".mysqli_error($conn));
				if($row=mysqli_fetch_assoc($result))
					$numero=1+$row["numero"];
				else
					$numero=1;
				((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
				$query.=",numero='$numero',anno='$anno'";
			}
			$id_foglio=$_POST["id_foglio"];
			$query.=" WHERE id_foglio='$id_foglio'";

			queryWithRollback($conn,$query);

			$query="DELETE FROM fogliviaggio_dettagliospese WHERE id_foglio=$id_foglio";
			queryWithRollback($conn,$query);

			for($conta=0;$conta<DETTAGLIO_SPESE_LINES;$conta++)
			{
				$postvar_desc="desc_$conta";
				$postvar_imp="imp_$conta";
				$postvar_impdivisa="impdivisa_$conta";
				$imp=$_POST[$postvar_imp];
				$impdivisa=$_POST[$postvar_impdivisa];
				if(((float)$imp)>0)
					$impdivisa=0;
				$postvar_fatt_ric="fatt_ric_$conta";

				if((isset($_POST[$postvar_desc]))
					&&strlen($_POST[$postvar_desc])
					&&(
						(isset($_POST[$postvar_imp])&&strlen($_POST[$postvar_imp]))
						||(isset($_POST[$postvar_impdivisa])&&strlen($_POST[$postvar_impdivisa]))
					))
				{
					$query="INSERT INTO fogliviaggio_dettagliospese
						(id_foglio,riga,descrizione,importo,importo_divisa,fatt_ric)
						VALUES('$id_foglio',
							'$conta',
							'".str_replace("'","\'",trim($_POST[$postvar_desc]))."',
							'".to_number($imp)."',
							'".to_number($impdivisa)."',
							'".$_POST[$postvar_fatt_ric]."')";
					queryWithRollback($conn,$query);
				}
			}
			mysqli_query($conn, "COMMIT")
				or die("Commit<br>".mysqli_error($conn));
			((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
			$op="foglio_viaggio";
			$message="foglio viaggio aggiornato";
			$foglio_action="list_fogli";
		}
	}
 	elseif(isset($_POST["add_pf"]))
	{
		if($_POST["pf"]=="ferie")
		{
			$da=date_to_sql($_POST["da"]);
			$a=date_to_sql($_POST["a"]);
		}
		else
		{
			$giorno=date_to_sql($_POST["giorno"]);
			$da=$giorno." ".$_POST["dalle"];
			$a=$giorno." ".$_POST["alle"];
		}
		$conn=mysqli_connect($myhost, $myuser, $mypass);
		((bool)mysqli_query($conn, "USE " . $dbname)) or die(mysqli_error($conn));

		$query="INSERT INTO permessi_ferie(id_utente,data,da,a,stato)
				VALUES('".$_SESSION["id_edit"]."',
					'".date("Y-m-d H:i.s")."',
					'$da',
					'$a',
					0)";
		@mysqli_query($conn, $query)
			or die($query."<br>".mysqli_error($conn));
		((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);

		require_once "Mail.php";

		$from = "HighTecService <zampieri@hightecservice.biz>";
//		$to = "zampieri@hightecservice.biz,3478624224@sms.vodafone.it";
		$to = "zampieri@hightecservice.biz";
		$subject = "Richiesta permesso/ferie ".$_SESSION["cognome"]." ".$_SESSION["nome"];
		$body = "Dal $da\nAl $a";

		$host = "localhost";
		$headers = array ('From' => $from,  'To' => $to, 'Subject' => $subject);
		$smtp = Mail::factory('smtp',  array ('host' => $host, 'auth' => false));
		$mail = $smtp->send($to, $headers, $body);
		header("Location: $self&op=permessi_ferie&message=inserimento%20effettuato");
	}
	elseif(isset($_POST["edit_pf"]))
	{
		if($_POST["pf"]=="ferie")
		{
			$da=date_to_sql($_POST["da"]);
			$a=date_to_sql($_POST["a"]);
		}
		else
		{
			$giorno=date_to_sql($_POST["giorno"]);
			$da=$giorno." ".$_POST["dalle"];
			$a=$giorno." ".$_POST["alle"];
		}
		$conn=mysqli_connect($myhost, $myuser, $mypass);
		((bool)mysqli_query($conn, "USE " . $dbname)) or die(mysqli_error($conn));

		$query="UPDATE permessi_ferie SET
					data='".date("Y-m-d H:i.s")."',
					da='$da',
					a='$a'
				WHERE id='".$_POST["id_pf"]."'";
		@mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));

		$query="SELECT id_utente FROM permessi_ferie WHERE id=".$_POST["id_pf"];
		$result=@mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));
		$row=mysqli_fetch_assoc($result);
		$id_utente=$row["id_utente"];
		((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
		((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);

		if($id_utente==$_SESSION["id_edit"])
		{
			require_once "Mail.php";
			$from = "HighTecService <zampieri@hightecservice.biz>";
//			$to = "zampieri@hightecservice.biz,3478624224@sms.vodafone.it";
			$to = "zampieri@hightecservice.biz";
			$subject = "Modifica richiesta permesso/ferie ".$_SESSION["cognome"]." ".$_SESSION["nome"];
			$body = "Dal $da\nAl $a";

			$host = "localhost";
			$headers = array ('From' => $from,  'To' => $to, 'Subject' => $subject);
			$smtp = Mail::factory('smtp',  array ('host' => $host, 'auth' => false));
			$mail = $smtp->send($to, $headers, $body);
		}
		header("Location: $self&op=permessi_ferie&message=modifica%20effettuata");
	}
	elseif($op=="pa_pf_del")
	{
		if(($_SESSION["livello"]==1) && isset($_GET["id"]))
		{
			$conn=mysqli_connect($myhost, $myuser, $mypass)
				or die("Connessione non riuscita".mysqli_error($conn));
			((bool)mysqli_query($conn, "USE " . $dbname));
			$query="DELETE FROM permessi_ferie WHERE id=".$_GET["id"];
			@mysqli_query($conn, $query)
				or die("$query<br>".mysqli_error($conn));
			if(mysqli_affected_rows($conn)==1)
				$message="permesso eliminato";
			((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
			$op="permessi_ferie";
		}
	}
	elseif($op=="pa_pf_sw")
	{
		if(($_SESSION["livello"]==1)&&isset($_GET["stato"])&&isset($_GET["id"])&&isset($_GET["id_utente"])&&isset($_GET["commessa"]))
		{
			$conn=mysqli_connect($myhost, $myuser, $mypass)
				or die("Connessione non riuscita".mysqli_error($conn));
			((bool)mysqli_query($conn, "USE " . $dbname));
			permessi_ferie_sw($_GET["id"],$_GET["id_utente"],$_GET["stato"],$_GET["da"],$_GET["a"],$_GET["commessa"]);
			$message="stato modificato";
			((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
		}
		elseif(($_SESSION["livello"]==1)&&isset($_GET["accettatutti"]))
		{
			$conn=mysqli_connect($myhost, $myuser, $mypass)
				or die("Connessione non riuscita".mysqli_error($conn));
			((bool)mysqli_query($conn, "USE " . $dbname));

			$where="";
			if($_SESSION["pf_mostra_tutti"]==3)
				$where=" AND id_utente='".$_SESSION["id_edit"]."'";
			$query="SELECT * FROM permessi_ferie WHERE stato=0 $where";
			$result=@mysqli_query($conn, $query)
				or die("$query: <br>".mysqli_error($conn));

			while($row=mysqli_fetch_assoc($result))
			{
				if(substr($row["da"],11)=="00:00:00")
					$da=substr($row["da"],0,10);
				else
					$da=$row["da"];

				if(substr($row["a"],11)=="00:00:00")
					$a=substr($row["a"],0,10);
				else
					$a=$row["a"];

				permessi_ferie_sw($row["id"],$row["id_utente"],1,$da,$a,"");
			}
			((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
		}
		$op="permessi_ferie";
	}
//malattie
 	elseif(isset($_POST["add_malattie"]))
	{
		$da=date_to_sql($_POST["da"]);
		$a=date_to_sql($_POST["a"]);
		$codice=trim($_POST["codice"]);
		$conn=mysqli_connect($myhost, $myuser, $mypass);
		((bool)mysqli_query($conn, "USE " . $dbname)) or die(mysqli_error($conn));

		$query="INSERT INTO malattie(id_utente,data,da,a,codice,stato)
				VALUES('".$_SESSION["id_edit"]."',
					'".date("Y-m-d H:i.s")."',
					'$da',
					'$a',
					'$codice',
					0)";
		@mysqli_query($conn, $query)
			or die($query."<br>".mysqli_error($conn));
		((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);

		require_once "Mail.php";

		$from = "HighTecService <zampieri@hightecservice.biz>";
//		$to = "carlo.ceppa@gmail.com";
		$to = "zampieri@hightecservice.biz";
		$subject = "Comunicazione malattia ".$_SESSION["cognome"]." "
			.$_SESSION["nome"]." - ".$_SESSION["ditta_rs"];
		$body = "Dal $da\nAl $a\nCodice $codice";

		$host = "localhost";
		$headers = array ('From' => $from,  'To' => $to, 'Subject' => $subject);
		$smtp = Mail::factory('smtp',  array ('host' => $host, 'auth' => false));
		$mail = $smtp->send($to, $headers, $body);
		header("Location: $self&op=malattie&message=inserimento%20effettuato");
	}
	elseif(isset($_POST["edit_malattie"]))
	{
		$da=date_to_sql($_POST["da"]);
		$a=date_to_sql($_POST["a"]);
		$codice=trim($_POST["codice"]);
		$conn=mysqli_connect($myhost, $myuser, $mypass);
		((bool)mysqli_query($conn, "USE " . $dbname)) or die(mysqli_error($conn));

		$query="UPDATE malattie SET
					data='".date("Y-m-d H:i.s")."',
					da='$da',
					a='$a',
					codice='$codice'
				WHERE id='".$_POST["id_malattie"]."'";
		@mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));

		$query="SELECT id_utente FROM malattie WHERE id=".$_POST["id_malattie"];
		$result=@mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));
		$row=mysqli_fetch_assoc($result);
		$id_utente=$row["id_utente"];
		((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
		((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);

		if($id_utente==$_SESSION["id_edit"])
		{
			require_once "Mail.php";
			$from = "HighTecService <zampieri@hightecservice.biz>";
//			$to = "zampieri@hightecservice.biz,3478624224@sms.vodafone.it";
			$to = "zampieri@hightecservice.biz";
			$subject = "Modifica comunicazione malattia ".$_SESSION["cognome"]." "
				.$_SESSION["nome"]." - ".$_SESSION["ditta_rs"];
			$body = "Dal $da\nAl $a\nCodice $codice";

			$host = "localhost";
			$headers = array ('From' => $from,  'To' => $to, 'Subject' => $subject);
			$smtp = Mail::factory('smtp',  array ('host' => $host, 'auth' => false));
			$mail = $smtp->send($to, $headers, $body);
		}
		header("Location: $self&op=malattie&message=modifica%20effettuata");
	}
	elseif($op=="pa_malattie_del")
	{
		if(($_SESSION["livello"]==1) && isset($_GET["id"]))
		{
			$conn=mysqli_connect($myhost, $myuser, $mypass)
				or die("Connessione non riuscita".mysqli_error($conn));
			((bool)mysqli_query($conn, "USE " . $dbname));
			$query="DELETE FROM malattie WHERE id=".$_GET["id"];
			@mysqli_query($conn, $query)
				or die("$query<br>".mysqli_error($conn));
			if(mysqli_affected_rows($conn)==1)
				$message="richiesta eliminata";
			((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
			$op="malattie";
		}
	}
	elseif($op=="pa_malattie_sw")
	{
		if(($_SESSION["livello"]==1)
				&&isset($_GET["stato"])
				&&isset($_GET["id"])
				&&isset($_GET["id_utente"])
				&&isset($_GET["commessa"])
				&&isset($_GET["codice"]))
		{
			$conn=mysqli_connect($myhost, $myuser, $mypass)
				or die("Connessione non riuscita".mysqli_error($conn));
			((bool)mysqli_query($conn, "USE " . $dbname));
			malattie_sw($_GET["id"],$_GET["id_utente"],$_GET["stato"],
				$_GET["da"],$_GET["a"],$_GET["commessa"],$_GET["codice"]);
			$message="stato modificato";
			((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
		}
		elseif(($_SESSION["livello"]==1)&&isset($_GET["accettatutti"]))
		{
			$conn=mysqli_connect($myhost, $myuser, $mypass)
				or die("Connessione non riuscita".mysqli_error($conn));
			((bool)mysqli_query($conn, "USE " . $dbname));

			$where="";
			if($_SESSION["malattie_mostra_tutti"]==3)
				$where=" AND malattie.id_utente='".$_SESSION["id_edit"]."'";
			$query="SELECT malattie.*,utenti.commessa_default FROM malattie
						LEFT JOIN utenti ON malattie.id_utente=utenti.id
						WHERE stato=0 $where";
			$result=@mysqli_query($conn, $query)
				or die("$query: <br>".mysqli_error($conn));


			while($row=mysqli_fetch_assoc($result))
			{
				$da=$row["da"];
				$a=$row["a"];
				$codice=$row["codice"];
				$commessa=$row["commessa_default"];

				malattie_sw($row["id"],$row["id_utente"],1,$da,$a,
					$commessa,$codice);
			}
			((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
		}
		$op="malattie";
	}
	elseif(isset($_POST["submit_congela"])&&($_SESSION["livello"]==1))
	{
		$ids="";
		foreach($_POST as $k=>$v)
		{
			if(substr($k,0,3)=="id_")
				$ids.="$v,";
		}

		if(strlen($ids))
		{
			$ids = rtrim($ids,',');
			$conn=mysqli_connect($myhost, $myuser, $mypass)
				or die("Connessione non riuscita".mysqli_error($conn));
			((bool)mysqli_query($conn, "USE " . $dbname));
			$query="UPDATE utenti SET
						blocca_a_data='".date_to_sql($_POST["data"])."'
						WHERE id IN ($ids)";
			@mysqli_query($conn, $query)
				or die("$query<br>".mysqli_error($conn));
			$message=mysqli_affected_rows($conn)." utenti modificati";
			((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
		}
		$op="congela_dati";
		header("Location: $self&op=$op&message=$message");
	}
	elseif(isset($_GET["user_to_del"])&&($_SESSION["livello"]==1))
	{
		$conn=mysqli_connect($myhost, $myuser, $mypass)
			or die("Connessione non riuscita".mysqli_error($conn));
		((bool)mysqli_query($conn, "USE " . $dbname));
		$query="UPDATE utenti SET eliminato=1,attivo=0
			WHERE id='".$_GET["user_to_del"]."'";
		$result=@mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));
		((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
		$message="Utente eliminato";
		$op="admin_users";
		$admin_action="list_users";
	}
	elseif(isset($_GET["user_to_reset"])&&($_SESSION["livello"]==1))
	{
		$conn=mysqli_connect($myhost, $myuser, $mypass)
			or die("Connessione non riuscita".mysqli_error($conn));
		((bool)mysqli_query($conn, "USE " . $dbname));
		$query="UPDATE utenti SET
					pass=md5('service'),
					expired=1
				WHERE id='".$_GET["user_to_reset"]."'";
		$result=@mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));
		((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
		$message="Password resettata";
		$op="admin_users";
		$admin_action="list_users";
	}
	elseif(($op="pa_foglio_viaggio")
		&&(isset($_REQUEST["foglio_action"]))
		&&(isset($_REQUEST["foglio_to_edit"])))
	{
		$fa=$_REQUEST["foglio_action"];
		$set="";
		if(strstr($fa,"lock")==$fa)
			$set="locked='".($fa=="lock_on"?1:0)."'";
		elseif(strstr($fa,"cong")==$fa)
			$set="conguaglio='".($fa=="cong_on"?1:0)."'";

		if($set)
		{
			$ftr=$_REQUEST["foglio_to_edit"];

			$conn=mysqli_connect($myhost, $myuser, $mypass)
				or die("Connessione non riuscita".mysqli_error($conn));
			((bool)mysqli_query($conn, "USE " . $dbname));
			$query="UPDATE fogliviaggio SET
						$set
					WHERE id_foglio='$ftr'";
			$result=@mysqli_query($conn, $query)
				or die($query."<br>".mysqli_error($conn));
			((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);

			if(isset($_REQUEST["redirect"]))
			{
				$redirect="report";
				$exploded=explode(",",$_REQUEST["redirect"]);
				foreach($exploded as $v)
				{
					$xx=explode(".",$v);
					$redirect.="&".$xx[0]."=".$xx[1];
				}
				header("Location: $self&$redirect");
			}
			else
			{
				$message="Foglio modificato";
				$op="foglio_viaggio";
			}
		}
	}
?>
