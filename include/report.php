<?


$conn=mysqli_connect($myhost, $myuser, $mypass)
		or die("Connessione non riuscita".mysqli_error($conn));
((bool)mysqli_query($conn, "USE " . $dbname));

if(isset($_POST["submit_mese"]))
	stampa_per_mese($_POST["mese"],$_POST["anno"]);
if(isset($_POST["submit_stampa_ore"]))
	stampa_per_ore_mese();
if(isset($_POST["submit_buoni_pasto"]))
	stampa_buoni_pasto($_POST["mese"],$_POST["anno"]);
elseif(isset($_POST["print_riepilogo"]))
	stampa_riepilogo($_POST["mese"],$_POST["anno"],"pdf");
elseif(isset($_POST["csv_riepilogo"]))
	stampa_riepilogo($_POST["mese"],$_POST["anno"],"csv");
elseif(isset($_POST["xls_riepilogo"]))
	stampa_riepilogo($_POST["mese"],$_POST["anno"],"xls");
elseif(isset($_POST["submit_pf"]))
	stampa_pf($_POST["mese"],$_POST["anno"]);
elseif(isset($_POST["submit_commessa"]))
	stampa_per_commessa($_POST["mese"],$_POST["anno"]);
elseif(isset($_POST["submit_periodo"]))
	stampa_per_periodo($_POST["da_data"],$_POST["a_data"]);
elseif(isset($_REQUEST["submit_fogli_viaggio"]))
	stampa_fogli_viaggio(date_to_sql($_REQUEST["da_data"]),date_to_sql($_REQUEST["a_data"]));
elseif(isset($_REQUEST["submit_conguagli"]))
	stampa_conguagli(date_to_sql($_REQUEST["da_data"]),date_to_sql($_REQUEST["a_data"]));
elseif(($_GET["op"]=="stampa_foglio") && isset($_GET["foglio_to_print"]))
	report_foglio_viaggio($_GET["foglio_to_print"]);
elseif(($_GET["op"]=="stampa_mese") && isset($_GET["mese"]) && isset($_GET["anno"]))
{
	$query="SELECT ditte.* FROM ditte";
	$ditte=array();
	$ditte[-1]="nessuna";
	$result=mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));
	while($row=mysqli_fetch_assoc($result))
		$ditte[$row["id"]]=$row["ragione_sociale"];
	((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
	$mese=$_GET["mese"];
	$anno=$_GET["anno"];

	$tabella=tabella_presenze($conn,$_SESSION["id_edit"],date("Y-m-d",mktime(0,0,0,$mese,1,$anno)),date("Y-m-d",mktime(0,0,0,$mese+1,0,$anno)),$_SESSION["data_inizio_coll"],$_SESSION["data_fine_coll"],false);
	report_mese($tabella,$_SESSION["nome"],$_SESSION["cognome"],$mese,$anno,$ditte[$_SESSION["ditta"]]);
	((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
}
elseif(($_GET["op"]=="stampa_indennita") && isset($_GET["ind_mese"]) && isset($_GET["ind_anno"]))
	stampa_indennita($_GET["ind_mese"],$_GET["ind_anno"]);


function stampa_per_mese($mese,$anno)
{
	global $mesi,$giorni_settimana,$dbname,$myhost,$myuser,$mypass;

	require_once("fpdf/fpdf.php");

	$dal=date("Y-m-d",mktime(0,0,0,$mese,1,$anno));
	$al=date("Y-m-d",mktime(0,0,0,$mese+1,0,$anno));

	$conn=mysqli_connect($myhost, $myuser, $mypass)
		or die("Connessione non riuscita".mysqli_error($conn));
	((bool)mysqli_query($conn, "USE " . $dbname));

    if($_SESSION["livello"]==2)
		$query="SELECT utenti.*,ditte.ragione_sociale,localita.localita,utenti_localita.dal,utenti_localita.al
                 	FROM utenti JOIN ditte ON utenti.ditta=ditte.id
                         	JOIN utenti_localita ON utenti.id=utenti_localita.utente_id
                         	JOIN localita ON utenti_localita.localita_id=localita.id
                         WHERE utenti.stampe=1 AND ditte.id=".$_POST["ditta"]." AND localita.id=".$_POST["localita"]."
                         	AND (utenti_localita.dal < \"$al\" AND utenti_localita.al > \"$dal\")
                         ORDER BY ditte.ragione_sociale,utenti.cognome";
    else
		$query="SELECT utenti.*,ditte.ragione_sociale FROM utenti
			JOIN ditte ON utenti.ditta=ditte.id
			WHERE utenti.attivo=1
			AND utenti.stampe=1
			ORDER BY ditte.ragione_sociale,utenti.cognome";


	$result=mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));
	if(mysqli_num_rows($result)==0)
		return;

	$pdf=new FPDF("P","mm","A4");

	while($utente=mysqli_fetch_assoc($result))
	{

		$localita_text=@$utente["localita"];
		if(isset($tabella))
			unset($tabella);

		if($_SESSION["livello"]==2)
		{
			if($utente["dal"]>$dal)
				$qdal=$utente["dal"];
			else
				$qdal=$dal;
			if($utente["al"]<$al)
				$qal=$utente["al"];
			else
				$qal=$al;
		}
		else
		{
			$qdal=$dal;
			$qal=$al;
		}


		$str_mese_prec=calc_str_mese_prec($conn,$qdal,$qal,$utente["id"],$utente["data_inizio_coll"],$utente["data_fine_coll"]);

		$tabella=tabella_presenze($conn,$utente["id"],$qdal,$qal,$utente["data_inizio_coll"],$utente["data_fine_coll"],false);

		if(count($tabella))
		{
			$header=array();
			$i=0;

			$pdf->SetFont('Arial','',9);
			$header[0][$i]="Giorno";
			if(isset($w))
				unset($w);
			$w[$i]=$pdf->GetStringWidth("Giorno");
			$i++;
			$riga=0;
			$cicla=true;
			$num_fields=1;
			$i_ore=-1;
			$i_str=-1;
			$i_per=-1;
			$i_via=-1;
			$somma_ore=0;
			$somma_per=0;
			$somma_str=0;
			$str25=0;
			$str30=0;
			$str50=0;
			$str55=0;
			$somma_via=0;
			$straordinari=array();
			$settimana=0;

			while($cicla)
			{
				$cicla=false;
				if($riga>0)
					$header[$riga][0]="";
				foreach(current($tabella) as $field=>$void)
					if(substr($field,0,10)!="__hidden__")
					{
						if($riga==0)
							$num_fields++;
						$explode=explode("<br>",$field);
						$cicla|=(count($explode)-1>$riga);
						$header[$riga][$i]=$explode[$riga];
						if(($riga==0)||(($w[$i]<=$pdf->GetStringWidth($header[$riga][$i]))))
							$w[$i]=$pdf->GetStringWidth($header[$riga][$i]);
						if($field=="Ore<br>Str")
							$i_str=$i;
						elseif($field=="Ore<br>Perm")
							$i_per=$i;
						elseif($field=="Ore<br>Giorn")
							$i_ore=$i;
						elseif($field=="Ore<br>Viaggio")
							$i_via=$i;
						$i++;
					}
				$riga++;
				$i=1;
			}
			$i=0;
			$data=array();
			foreach($tabella as $giorno=>$row)
			{
				if(my_date_format($giorno,"w")==6)
					$settimana++;
				$j=0;
				$data[$i][$j]=my_date_format($giorno,"d ").substr($giorni_settimana[my_date_format($giorno,"w")],0,3);
				if($pdf->GetStringWidth($data[$i][$j])>$w[$j])
					$w[$j]=$pdf->GetStringWidth($data[$i][$j]);
				$j++;

				$ore_str=hour_to_int(@$row["Ore<br>Str"]);
				$ore_str=(int)($ore_str/15)*15;

				if((!$row["__hidden__festivo"])&&($ore_str>0))
					$straordinari[$settimana]+=$ore_str;

				foreach($row as $fieldname=>$field)
				{
					if(substr($fieldname,0,10)!="__hidden__")
					{
						$data[$i][$j]=$field;
						if((!strstr($data[$i][$j],"<img "))&&($pdf->GetStringWidth($data[$i][$j])>$w[$j]))
							$w[$j]=$pdf->GetStringWidth($data[$i][$j]);
						if(($j==$i_ore)&&(hour_to_int($data[$i][$j])>0))
							$somma_ore+=hour_to_int($data[$i][$j]);
						elseif(($j==$i_per)&&(hour_to_int($data[$i][$j])>0))
							$somma_per+=hour_to_int($data[$i][$j]);
						elseif(($j==$i_str)&&(hour_to_int($data[$i][$j])>0))
							$somma_str+=hour_to_int($data[$i][$j]);
						elseif(($j==$i_via)&&(hour_to_int($data[$i][$j])>0))
							$somma_via+=hour_to_int($data[$i][$j]);
						$j++;
					}
					elseif($fieldname=="__hidden__festivo")
						$data[$i]["festivo"]=$field;
				}
				$i++;
			}
			$pdf->AddPage();

			$pdf->SetTextColor(0);
			$pdf->SetDrawColor(20,20,20);
			$pdf->SetFont('Arial','B',22);
			$pdf->SetX(15);
                         if($_SESSION["livello"]!=2)
				$pdf->Cell(180,20,$utente["ragione_sociale"],1,2,'C',0);
                         else
	                	$pdf->MultiCell(180,10,$utente["ragione_sociale"]."\nSede di: ".$localita_text, 1, 'C',0);

			$pdf->SetFont('Arial','B',15);
			$pdf->Ln(5);
			$pdf->SetX(15);
			$pdf->Cell(180,8,$utente["nome"]." ".$utente["cognome"],0,2,'C',0);
			$pdf->SetX(15);
			$pdf->Cell(180,8,"presenze di ".$mesi[$mese-1]." $anno",0,2,'C',0);
			$pdf->Ln(5);
			$pdf->SetFillColor(240,240,240);
			$pdf->SetDrawColor(200,200,200);
			$pdf->SetLineWidth(.1);
			$pdf->SetFont('Arial','B',9);

			$leftpoint=(210-(array_sum($w)+3*count($w)))/2;
			if($leftpoint<5)
			{
				$leftpoint=5;
				$ii=0;
				$cum=0;
				$wd=200-3*count($w);
				while(($ii<count($w))&&($cum<=$wd)&&($cum+$w[$ii]<=$wd))
				{
					$cum+=$w[$ii];
					$ii++;
				}

				for($jj=$ii;$jj<count($w);$jj++)
				{
					$w[$jj]=($wd-$cum)/(count($w)-$ii);
				}
			}

			//Header
			for($i=0;$i<count($header);$i++)
			{
				$pdf->SetX($leftpoint);
				for($j=0;$j<$num_fields;$j++)
					$pdf->Cell($w[$j]+3,5,$header[$i][$j],'LR',0,'C',1);
				$pdf->Ln();
			}
			//$pdf->Ln();
			//Color and font restoration
			$pdf->SetFillColor(224,235,255);
			$pdf->SetTextColor(0);
			$pdf->SetFont('');
			//Data
			foreach($data as $row)
			{
				$pdf->SetX($leftpoint);
				$fill=$row["festivo"];

				if(($i_str!=-1)&&(hour_to_int($row[$i_str])>0))
				{
					if($row["festivo"])
					{
						if(hour_to_int($row[$i_str])<=480)
							$str50+=hour_to_int($row[$i_str]);
						else
						{
							$str50+=480;
							$str55+=(hour_to_int($row[$i_str])-480);
						}
					}
				}
				for($i=0;$i<$num_fields;$i++)
				{
					if(!strstr($row[$i],"<img "))
					{
						if($pdf->GetStringWidth($row[$i])<=($w[$i]+3))
							$pdf->Cell($w[$i]+3,5,$row[$i],1,0,($i>0?'C':'L'),$fill);
						else
						{
							$pdf->SetFontSize(7);
							if($pdf->GetStringWidth($row[$i])<=($w[$i]+3))
								$pdf->Cell($w[$i]+3,5,$row[$i],1,0,($i>0?'C':'L'),$fill);
							else
							{
								$x=$pdf->GetX();
								$y=$pdf->GetY();
								$pdf->MultiCell($w[$i]+3,2.5,$row[$i],0,'C',$fill);
								$pdf->SetXY($x,$y);
								$pdf->Cell($w[$i]+3,5,"",1,0,'L',$fill);
							}
							$pdf->SetFontSize(9);
						}
					}
					else
					{
						$pdf->Image("img/check_bianco.png",$pdf->GetX()+($w[$i]-1)/2,$pdf->GetY()+.5,4,4);
						$pdf->Cell($w[$i]+3,5,"",1,0,'C',$fill);
					}
				}
				$pdf->Ln();
				$fill=!$fill;
			}
			//$pdf->Cell(20*count($header),0,'','T');
			foreach($straordinari as $linea)
			{
				if($linea+$str_mese_prec<=120)
					$str25+=$linea;
				else
				{
					$str25+=120-$str_mese_prec;
					$str30+=($linea-120+$str_mese_prec);
				}
				$str_mese_prec=0;
			}

			if($i_ore!=-1)
			{
				$lp=$leftpoint;
				for($i=0;$i<$i_ore;$i++)
					$lp+=$w[$i]+3;
				$pdf->SetX($lp);
				$pdf->Cell($w[$i_ore]+3,5,int_to_hour($somma_ore),1,0,'C',1);
			}
			if($i_str!=-1)
			{
				$lp=$leftpoint;
				for($i=0;$i<$i_str;$i++)
					$lp+=($w[$i]+3);
				$pdf->SetX($lp);
				$pdf->Cell($w[$i_str]+3,5,int_to_hour($somma_str),1,0,'C',1);
			}
			if($i_per!=-1)
			{
				$lp=$leftpoint;
				for($i=0;$i<$i_per;$i++)
					$lp+=$w[$i]+3;
				$pdf->SetX($lp);
				$pdf->Cell($w[$i_per]+3,5,int_to_hour($somma_per),1,0,'C',1);
			}
			if($i_via!=-1)
			{
				$lp=$leftpoint;
				for($i=0;$i<$i_via;$i++)
					$lp+=$w[$i]+3;
				$pdf->SetX($lp);
				$pdf->Cell($w[$i_via]+3,5,int_to_hour($somma_via),1,0,'C',1);
			}
			//suddivisione straordinario
			$cellwidth=10;
			if($i_str!=-1)
			{
				$lp=$leftpoint;
				for($i=0;$i<$i_str;$i++)
					$lp+=($w[$i]+3);
				$pdf->Ln();
				$pdf->SetFillColor(240,240,240);
				$pdf->Ln(1);
				if($str25)
				{
					$pdf->SetX($lp-$cellwidth);
					$pdf->Cell($cellwidth,5,"25%:",'LTB',0,'R',1);
					$pdf->Cell($w[$i_str]+3,5,int_to_hour($str25),'RTB',0,'C',1);
					$pdf->Ln();
				}
				if($str30)
				{
					$pdf->SetX($lp-$cellwidth);
					$pdf->Cell($cellwidth,5,"30%:",'LTB',0,'R',1);
					$pdf->Cell($w[$i_str]+3,5,int_to_hour($str30),'RTB',0,'C',1);
					$pdf->Ln();
				}
				if($str50)
				{
					$pdf->SetX($lp-$cellwidth);
					$pdf->Cell($cellwidth,5,"50%:",'LTB',0,'R',1);
					$pdf->Cell($w[$i_str]+3,5,int_to_hour($str50),'RTB',0,'C',1);
					$pdf->Ln();
				}
				if($str55)
				{
					$pdf->SetX($lp-$cellwidth);
					$pdf->Cell($cellwidth,5,"55%:",'LTB',0,'R',1);
					$pdf->Cell($w[$i_str]+3,5,int_to_hour($str55),'RTB',0,'C',1);
					$pdf->Ln();
				}
			}

		}
	}

	((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
	((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
	$pdf->Output();
}


function stampa_buoni_pasto($mese,$anno)
{
	global $mesi,$giorni_settimana,$dbname,$myhost,$myuser,$mypass;


	$dal=date("Y-m-d",mktime(0,0,0,$mese,1,$anno));
	$al=date("Y-m-d",mktime(0,0,0,$mese+1,0,$anno));

	$conn=mysqli_connect($myhost, $myuser, $mypass)
		or die("Connessione non riuscita".mysqli_error($conn));
	((bool)mysqli_query($conn, "USE " . $dbname));

	$query="select utenti.id,utenti.cognome,utenti.nome
			from utenti left join ditte on utenti.ditta=ditte.id
			where buoni_pasto=1
			order by ditte.id,utenti.cognome";
	$result=mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));
	$out=array();
	while($row=mysqli_fetch_assoc($result))
		$out[$row["id"]]=array("nome"=>($row["cognome"]." ".$row["nome"]),"p"=>0);
	((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);

	$query="select utenti.id,count(presenze.id) as p
			from utenti left join presenze on presenze.id=utenti.id
			left join ditte on utenti.ditta=ditte.id
			where buoni_pasto=1
			and giorno between '$dal' and '$al'
			and presenze.trasferta<=0
			and presenze.malattia=0
			and ((presenze.ingresso!=-1)or(presenze.ingresso2!=-1))
			and (presenze.pausa!=-1)
			and (DAYOFWEEK(giorno)%7)>1
			group by utenti.login
			order by ditte.id,utenti.cognome";

	$result=mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));
	if(mysqli_num_rows($result)==0)
		return;


	echo "<table style='border-collapse:collapse;font: normal 14px Tahoma, \'Trebuchet MS\', Trebuchet, Helvetica, Verdana, sans-serif;color: #036;'>
			  <tr>
				  <td style='padding:4px'>Nome</td>
				  <td style='padding:4px'>Presenze</td>
			  </tr>";
	while($row=mysqli_fetch_assoc($result))
		$out[$row["id"]]["p"]=$row["p"];
	((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);

	foreach($out as $row)
	{
		$nome=$row["nome"];
		$p=$row["p"];

		echo "<tr>
				  <td style='padding:4px'>$nome</td>
				  <td style='padding:4px'>$p</td>
			  </tr>";

	}
	echo "</table>
		</body>
	</html>";

	((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
}

function report_mese_inside($pdf,$tabella,$nome,$cognome,$mese,$anno,$ditta)
{
	global $mesi,$giorni_settimana;
	$header=array();
	$i=0;
	$pdf->SetFont('Arial','',9);
	$header[0][$i]="Giorno";
	$w[$i]=$pdf->GetStringWidth("Giorno");
	$i++;
	$riga=0;
	$cicla=true;
	$num_fields=1;
	$i_ore=-1;
	$i_str=-1;
	$i_per=-1;
	$i_via=-1;
	$somma_ore=0;
	$somma_per=0;
	$somma_str=0;
	$somma_via=0;
	while($cicla)
	{
		$cicla=false;
		if($riga>0)
			$header[$riga][0]="";
		foreach(current($tabella) as $field=>$void)
			if(substr($field,0,10)!="__hidden__")
			{
				if($riga==0)
					$num_fields++;
				$explode=explode("<br>",$field);

				$cicla|=(count($explode)-1>$riga);
				if($riga<count($explode))
    				$header[$riga][$i]=$explode[$riga];
    			else
    				$header[$riga][$i]="";
				if(($riga==0)||(($w[$i]<=$pdf->GetStringWidth($header[$riga][$i]))))
					$w[$i]=$pdf->GetStringWidth($header[$riga][$i]);
				if($field=="Ore<br>Str")
					$i_str=$i;
				elseif($field=="Ore<br>Perm")
					$i_per=$i;
				elseif($field=="Ore<br>Giorn")
					$i_ore=$i;
				elseif($field=="Ore<br>Viaggio")
					$i_via=$i;
				$i++;
			}
		$riga++;
		$i=1;
	}
	$i=0;
	$data=array();
	foreach($tabella as $giorno=>$row)
	{
		$j=0;
		$data[$i][$j]=my_date_format($giorno,"d ").substr($giorni_settimana[my_date_format($giorno,"w")],0,3);
		if($pdf->GetStringWidth($data[$i][$j])>$w[$j])
			$w[$j]=$pdf->GetStringWidth($data[$i][$j]);
		$j++;
		foreach($row as $fieldname=>$field)
			if(substr($fieldname,0,10)!="__hidden__")
			{
				$data[$i][$j]=$field;
				if((!strstr($data[$i][$j],"<img "))&&($pdf->GetStringWidth($data[$i][$j])>$w[$j]))
					$w[$j]=$pdf->GetStringWidth($data[$i][$j]);
				if(($j==$i_ore)&&(hour_to_int($data[$i][$j])>0))
					$somma_ore+=hour_to_int($data[$i][$j]);
				elseif(($j==$i_per)&&(hour_to_int($data[$i][$j])>0))
					$somma_per+=hour_to_int($data[$i][$j]);
				elseif(($j==$i_str)&&(hour_to_int($data[$i][$j])>0))
					$somma_str+=hour_to_int($data[$i][$j]);
				elseif(($j==$i_via)&&(hour_to_int($data[$i][$j])>0))
					$somma_via+=hour_to_int($data[$i][$j]);

				$j++;
			}
			elseif($fieldname=="__hidden__festivo")
				$data[$i]["festivo"]=$field;
		$i++;
	}

	$pdf->SetTextColor(0);
	$pdf->SetDrawColor(20,20,20);
	$pdf->SetFont('Arial','B',22);
	$pdf->SetX(15);
	$pdf->Cell(180,20,$ditta,1,2,'C',0);
	$pdf->SetFont('Arial','B',15);
	$pdf->Ln(5);
	$pdf->SetX(15);
	$pdf->Cell(180,8,"$nome $cognome",0,2,'C',0);
	$pdf->SetX(15);
	$pdf->Cell(180,8,"presenze di ".$mesi[$mese-1]." $anno",0,2,'C',0);
	$pdf->Ln(5);
	$pdf->SetFillColor(240,240,240);
	$pdf->SetDrawColor(200,200,200);
	$pdf->SetLineWidth(.1);
	$pdf->SetFont('Arial','B',9);


	$leftpoint=(210-(array_sum($w)+3*count($w)))/2;
	if($leftpoint<5)
	{
		$leftpoint=5;
		$ii=0;
		$cum=0;
		$wd=200-3*count($w);
		while(($ii<count($w))&&($cum<=$wd)&&($cum+$w[$ii]<=$wd))
		{
			$cum+=$w[$ii];
			$ii++;
		}

		for($jj=$ii;$jj<count($w);$jj++)
		{
			$w[$jj]=($wd-$cum)/(count($w)-$ii);
		}
	}

	//Header
	for($i=0;$i<count($header);$i++)
	{
		$pdf->SetX($leftpoint);
		for($j=0;$j<$num_fields;$j++)
			$pdf->Cell($w[$j]+3,5,$header[$i][$j],'LR',0,'C',1);
		$pdf->Ln();
	}
	//$pdf->Ln();
	//Color and font restoration
	$pdf->SetFillColor(224,235,255);
	$pdf->SetTextColor(0);
	$pdf->SetFont('');
	//Data
	foreach($data as $row)
	{
		$pdf->SetX($leftpoint);
		$fill=$row["festivo"];
		for($i=0;$i<$num_fields;$i++)
		{
			if(!strstr($row[$i],"<img "))
			{
				if($pdf->GetStringWidth($row[$i])<=($w[$i]+3))
					$pdf->Cell($w[$i]+3,5,$row[$i],1,0,($i>0?'C':'L'),$fill);
				else
				{
					$pdf->SetFontSize(7);
					if($pdf->GetStringWidth($row[$i])<=($w[$i]+3))
						$pdf->Cell($w[$i]+3,5,$row[$i],1,0,($i>0?'C':'L'),$fill);
					else
					{
						$x=$pdf->GetX();
						$y=$pdf->GetY();
						$pdf->MultiCell($w[$i]+3,2.5,$row[$i],0,'C',$fill);
						$pdf->SetXY($x,$y);
						$pdf->Cell($w[$i]+3,5,"",1,0,'L',$fill);
					}
					$pdf->SetFontSize(9);
				}
			}
			else
			{
				$pdf->Image("img/check_bianco.png",$pdf->GetX()+($w[$i]-1)/2,$pdf->GetY()+.5,4,4);
				$pdf->Cell($w[$i]+3,5,"",1,0,'C',$fill);
			}
		}
		$pdf->Ln();
		$fill=!$fill;
	}
	//$pdf->Cell(20*count($header),0,'','T');

	if($i_ore!=-1)
	{
		$lp=$leftpoint;
		for($i=0;$i<$i_ore;$i++)
			$lp+=$w[$i]+3;
		$pdf->SetX($lp);
		$pdf->Cell($w[$i_ore]+3,5,int_to_hour($somma_ore),1,0,'C',1);
	}
	if($i_str!=-1)
	{
		$lp=$leftpoint;
		for($i=0;$i<$i_str;$i++)
			$lp+=($w[$i]+3);
		$pdf->SetX($lp);
		$pdf->Cell($w[$i_str]+3,5,int_to_hour($somma_str),1,0,'C',1);
	}
	if($i_per!=-1)
	{
		$lp=$leftpoint;
		for($i=0;$i<$i_per;$i++)
			$lp+=$w[$i]+3;
		$pdf->SetX($lp);
		$pdf->Cell($w[$i_per]+3,5,int_to_hour($somma_per),1,0,'C',1);
	}
	if($i_via!=-1)
	{
		$lp=$leftpoint;
		for($i=0;$i<$i_via;$i++)
			$lp+=$w[$i]+3;
		$pdf->SetX($lp);
		$pdf->Cell($w[$i_via]+3,5,int_to_hour($somma_via),1,0,'C',1);
	}
	$pdf->SetXY(20,250);
	$pdf->SetFont('Arial','',12);
	$pdf->Cell(50,10,"Il dipendente",2,1,'C',0);
	$pdf->SetX(20);
	$pdf->Cell(50,10,"_____________",2,1,'C',0);

	$pdf->SetXY(140,250);
	$pdf->SetFont('Arial','',12);
	$pdf->Cell(50,10,"Il responsabile",2,1,'C',0);
	$pdf->SetX(140);
	$pdf->Cell(50,10,"_____________",2,1,'C',0);

}
function report_mese($tabella,$nome,$cognome,$mese,$anno,$ditta)
{
	require_once("fpdf/fpdf.php");
	$pdf=new FPDF("P","mm","A4");
	$pdf->AddPage();
	report_mese_inside($pdf,$tabella,$nome,$cognome,$mese,$anno,$ditta);
	$pdf->Output();
}

function report_foglio_viaggio($foglio_to_print)
{
	global $myhost,$myuser,$mypass,$dbname,
		$indbase_val,$indsparo_val,$indnav_val,$paesi_non_ord,$paesi_non_base;

	require_once("fpdf/fpdf.php");

	$conn=mysqli_connect($myhost, $myuser, $mypass)
		or die("Connessione non riuscita".mysqli_error($conn));
	((bool)mysqli_query($conn, "USE " . $dbname));
	$query="SELECT fogliviaggio.*,commesse.*,
				utenti.nome,utenti.cognome ,ditte.ragione_sociale,
				trasferte_paesi.*
				FROM fogliviaggio LEFT JOIN commesse
					ON fogliviaggio.id_commessa=commesse.id
				LEFT JOIN utenti ON fogliviaggio.id_utente=utenti.id
				LEFT JOIN ditte ON utenti.ditta=ditte.id
				LEFT JOIN trasferte_paesi ON fogliviaggio.paese=trasferte_paesi.paese
				WHERE fogliviaggio.id_foglio='".$foglio_to_print."'";
	$result=mysqli_query($conn, $query)
		or die("$query<br>".mysqli_error($conn));
	$foglio=mysqli_fetch_assoc($result);
	((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);

	$showCommessa=($foglio["id_commessa"]>0);
	if($showCommessa)
		$commessa=$foglio["commessa"]." - ".$foglio["descrizione"];
	else
		$commessa="---";

	if($foglio["trattamento"]=="PDL")
		$foglio["trattamento"]="PIE' DI LISTA";
	$result=mysqli_query($conn, "SELECT * FROM fogliviaggio_dettagliospese
			WHERE id_foglio=".$foglio_to_print." ORDER BY fatt_ric,riga")
		or die(mysqli_error($conn));
	$totale["F"]=0;
	$totale_divisa["F"]=0;
	$totale["R"]=0;
	$totale_divisa["R"]=0;
	while($row=mysqli_fetch_assoc($result))
	{
		$spese[$row["fatt_ric"]][$row["riga"]]["desc"]=$row["descrizione"];
		$spese[$row["fatt_ric"]][$row["riga"]]["imp"]=$row["importo"];
		$spese[$row["fatt_ric"]][$row["riga"]]["imp_divisa"]=$row["importo_divisa"];
		$totale[$row["fatt_ric"]]+=$row["importo"];
		$totale_divisa[$row["fatt_ric"]]+=$row["importo_divisa"];
	}
	((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
	((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);

	require_once("foglio_viaggio_util.php");

	$giorni=calcolagiorni($foglio["datainizio"],
		$foglio["orainizio"],
		$foglio["datafine"],
		$foglio["orafine"]);


	$pdf=new FPDF("P","mm","A4");
	$pdf->AddPage();
	$pdf->SetTextColor(0);
	$pdf->SetDrawColor(20,20,20);

	$pdf->SetFont('Arial','B',22);
	$pdf->SetX(15);
	$pdf->Cell(180,20,$foglio["ragione_sociale"],0,2,'C',0);

	$pdf->SetFont('Arial','B',14);
	$pdf->Ln(0);
	$pdf->SetX(15);

	if(strlen($foglio["numero"]))
	{
		$divisa=$foglio["divisa"];
		$cambio=$foglio["cambio"];
		$pdf->Cell(150,15,"Riepilogativo trasferta di: ".$foglio["cognome"]." ".$foglio["nome"],1,0,'L',0);
		$pdf->Cell(30,15,pdfstring("N° ".sprintf("%03d",$foglio["numero"])."/".substr($foglio["anno"],2)),1,2,'R',0);
		$pdf->Ln(5);
		$pdf->SetX(15);

		if($showCommessa)
		{
			$pdf->Cell(90,8,"Trasferta a: ",1,0,'L',0);
			$pdf->Cell(90,8,"Motivo: ",1,0,'L',0);
			$width1=$pdf->GetStringWidth("Trasferta a: ");
			$width2=$pdf->GetStringWidth("Motivo: ");
			$pdf->SetFont('Arial','',14);
			$pdf->SetX(15+$width1);
			$pdf->Cell(90-$width1,8,pdfstring($foglio["loc"]),0,0,'L',0);
			$pdf->SetX(105+$width2);
			$pdf->Cell(90-$width2,8,pdfstring($foglio["motivo"]),0,1,'L',0);
			$pdf->SetX(15);
			$pdf->SetFont('Arial','B',14);
			$pdf->Cell(180,8,"Commessa: ",1,0,'L',0);
			$width=$pdf->GetStringWidth("Commessa: ");
			$pdf->SetFont('Arial','',14);
			$pdf->SetX(15+$width);
			$text=$commessa;
			$pdf->Cell(180-$width,8,pdfstring($text),0,1,'L',0);
		}
		else
		{
			$pdf->Cell(180,8,"Trasferta a: ",1,0,'L',0);
			$width=$pdf->GetStringWidth("Trasferta a: ");
			$pdf->SetFont('Arial','',14);
			$pdf->SetX(15+$width);
			$text=$foglio["paese"]." - ".$foglio["loc"];
			$pdf->Cell(180-$width,8,pdfstring($text),0,1,'L',0);

			$pdf->SetX(15);
			$pdf->SetFont('Arial','B',14);
			$pdf->Cell(180,8,"Motivo: ",1,0,'L',0);
			$width=$pdf->GetStringWidth("Motivo: ");
			$pdf->SetFont('Arial','',14);
			$pdf->SetX(15+$width);
			$text=$foglio["motivo"];
			$pdf->Cell(180-$width,8,pdfstring($text),0,1,'L',0);
		}

		$pdf->SetFont('Arial','B',14);
		$pdf->Ln(5);
		$pdf->SetX(15);
		$pdf->Cell(45,8,"Data inizio",1,0,'C',0);
		$pdf->Cell(45,8,"Ora inizio",1,0,'C',0);
		$pdf->Cell(45,8,"Data fine",1,0,'C',0);
		$pdf->Cell(45,8,"Ora fine",1,1,'C',0);

		$pdf->SetX(15);
		$pdf->SetFont('Arial','',14);
		$pdf->Cell(45,8,my_date_format($foglio["datainizio"],"d.m.Y"),1,0,'C',0);
		$pdf->Cell(45,8,int_to_hour($foglio["orainizio"]),1,0,'C',0);
		$pdf->Cell(45,8,my_date_format($foglio["datafine"],"d.m.Y"),1,0,'C',0);
		$pdf->Cell(45,8,int_to_hour($foglio["orafine"]),1,1,'C',0);


		if($foglio["piedilista"]==0)
		{
			$no_ord=!(strlen($foglio["ord"])>0);
			$no_base=!(strlen($foglio["base"])>0);
			$no_fest=!(strlen($foglio["fest"])>0);
			$cells=array();
			if(!$no_ord)
				$cells[]="ordinaria";
			if(!$no_base)
				$cells[]="in base";
			if(!$no_fest)
				$cells[]="festiva";

			$c=1+count($cells);
			$cw=180/$c;

			$pdf->SetFont('Arial','B',14);
			$pdf->Ln(5);
			$pdf->SetX(15);
			$pdf->Cell($cw,12,"Giorni trasferta",1,0,'C',0);
	//		$pdf->SetX(15+$cw);


			$y=$pdf->getY();
			for($i=1;$i<$c;$i++)
			{
				$pdf->SetXY($cw*$i+15,$y);
				$pdf->Cell($cw,1,"",'TR',2,'C',0);
				$pdf->Cell($cw,5,"Giorni trasferta",'R',2,'C',0);
				$pdf->Cell($cw,5,$cells[$i-1],'R',2,'C',0);
				$pdf->Cell($cw,1,"",'BR',0,'C',0);
			}
			$pdf->ln();


			$pdf->SetX(15);
			$pdf->SetFont('Arial','',14);
			$pdf->Cell($cw,8,$giorni,1,0,'C',0);

			$giorni_ord=($foglio["giorni_ord"]*2)/2;
			$giorni_base=($foglio["giorni_base"]*2)/2;
			$giorni_fest=($foglio["giorni_fest"]*2)/2;

			for($i=1;$i<$c;$i++)
			{
				$text="";
				switch($cells[$i-1])
				{
					case "ordinaria":
						$text=$giorni_ord;
						break;
					case "in base":
						$text=$giorni_base;
						break;
					case "festiva":
						$text=$giorni_fest;
						break;
				}
				$text=($text>0?$text:"---");
				$pdf->Cell($cw,8,$text,1,0,'C',0);
			}
			$pdf->Ln(8);
		}
	}
	else
	{
		$pdf->Cell(180,15,"Riepilogativo trasferta di: ".$foglio["cognome"]." ".$foglio["nome"],1,2,'L',0);
		$pdf->Ln(5);
		$pdf->SetX(15);

		$pdf->Cell(90,8,"Trasferta a: ",1,0,'L',0);
		$pdf->Cell(90,8,"Motivo: ",1,0,'L',0);
		$width1=$pdf->GetStringWidth("Trasferta a: ");
		$width2=$pdf->GetStringWidth("Motivo: ");
		$pdf->SetFont('Arial','',14);
		$pdf->SetX(15+$width1);
		$pdf->Cell(90-$width1,8,pdfstring($foglio["loc"]),0,0,'L',0);
		$pdf->SetX(105+$width2);
		$pdf->Cell(90-$width2,8,pdfstring($foglio["motivo"]),0,1,'L',0);

		$pdf->SetX(15);
		$pdf->SetFont('Arial','B',14);
		$pdf->Cell(180,8,"Commessa: ",1,0,'L',0);
		$width1=$pdf->GetStringWidth("Commessa: ");
		$pdf->SetFont('Arial','',14);
		$pdf->SetX(15+$width1);
		$pdf->Cell(180-$width1,8,pdfstring($commessa),0,1,'L',0);

		$pdf->SetFont('Arial','B',14);
		$pdf->Ln(5);
		$pdf->SetX(15);
		$pdf->Cell(30,8,"Data inizio",1,0,'C',0);
		$pdf->Cell(30,8,"Ora inizio",1,0,'C',0);
		$pdf->Cell(30,8,"Ore viaggio",1,0,'C',0);
		$pdf->Cell(30,8,"Data fine",1,0,'C',0);
		$pdf->Cell(30,8,"Ora fine",1,0,'C',0);
		$pdf->Cell(30,8,"Ore viaggio",1,1,'C',0);
		$pdf->SetX(15);
		$pdf->SetFont('Arial','',14);
		$pdf->Cell(30,8,my_date_format($foglio["datainizio"],"d.m.y"),1,0,'C',0);
		$pdf->Cell(30,8,int_to_hour($foglio["orainizio"]),1,0,'C',0);
		$pdf->Cell(30,8,int_to_hour($foglio["oreviaggioand"]),1,0,'C',0);
		$pdf->Cell(30,8,my_date_format($foglio["datafine"],"d.m.y"),1,0,'C',0);
		$pdf->Cell(30,8,int_to_hour($foglio["orafine"]),1,0,'C',0);
		$pdf->Cell(30,8,int_to_hour($foglio["oreviaggiorit"]),1,2,'C',0);

		$pdf->Ln(5);
		$pdf->SetX(15);
		$pdf->SetFont('Arial','B',14);
		$pdf->Cell(39,8,"Trattamento",1,0,'L',0);
		$pdf->SetFont('Arial','B',12);
		$pdf->Cell(47,8,"Indennit".chr(225)." di base n.",1,0,'L',0);
		$pdf->Cell(47,8,"Ind. di base + sparo n.",1,0,'L',0);
		$pdf->Cell(47,8,"Ind. di navigazione n.",1,2,'L',0);
		$pdf->SetX(15);
		$pdf->SetFont('Arial','',14);
		$pdf->Cell(39,8,$foglio["trattamento"],1,0,'L',0);
		$pdf->Cell(47,8,($foglio["indbase"]!=0?$foglio["indbase"]:"---"),1,0,'L',0);
		$pdf->Cell(47,8,($foglio["indsparo"]!=0?$foglio["indsparo"]:"---"),1,0,'L',0);
		$pdf->Cell(47,8,($foglio["indnav"]!=0?$foglio["indnav"]:"---"),1,2,'L',0);

		$pdf->Ln(5);
		$pdf->SetFont('Arial','B',14);
		$pdf->SetX(15);
		$pdf->Cell(180,8,"Trasferta giorni: ",1,0,'L',0);
		$width1=$pdf->GetStringWidth("Trasferta giorni: ");
		$pdf->SetFont('Arial','',14);
		$pdf->SetX(15+$width1);
		$pdf->Cell(180-$width1,8,$giorni,0,2,'L',0);

		$pdf->SetFont('Arial','B',14);
		$pdf->SetX(15);
	//	$pdf->Cell(90,8,"Diaria giornaliera: ",1,0,'L',0);
	//	$width1=$pdf->GetStringWidth("Diaria giornaliera: ");
	//	$pdf->Cell(90,8,"Totale trasferta: ",1,0,'L',0);
	//	$width2=$pdf->GetStringWidth("Totale trasferta: ");
	//	$pdf->SetFont('Arial','',14);
	//	$pdf->SetX(15+$width1);
	//	$pdf->Cell(90-$width1,8,chr(128)." ".$foglio["diaria"],0,0,'L',0);
	//	$pdf->SetX(105+$width2);
	//	$pdf->Cell(90-$width2,8,chr(128)." ".sprintf("%.2f",($giorni*$foglio["diaria"]+$indbase_val*$foglio["indbase"]+$indsparo_val*$foglio["indsparo"]+$indnav_val*$foglio["indnav"])),0,2,'L',0);
	}
	$pdf->Ln(5);
	if(count(@$spese["F"])>0)
	{
		$pdf->SetFont('Arial','B',13);
		$pdf->SetX(15);
		$pdf->Cell(180,8,"RIEPILOGATIVO SPESE SOSTENUTE DURANTE LA TRASFERTA - FATTURE:",1,2,'C',0);
		$pdf->Cell(130,8,"Descrizione",1,0,'C',0);
		if($divisa=="€")
			$pdf->Cell(50,8,"Importo (Euro)",1,2,'C',0);
		else
		{
			$pdf->SetFontSize(11);
			$pdf->Cell(50,4,"Importo",'TR',2,'C',0);
			$pdf->Cell(25,4,"Euro",'',0,'C',0);
			$pdf->Cell(25,4,$divisa,'BR',2,'C',0);
		}
		$pdf->SetFont('Arial','',13);
		foreach($spese["F"] as $value)
		{
			$pdf->SetX(15);
			$pdf->Cell(130,8,$value["desc"],1,0,'L',0);
			if($divisa=="€")
				$pdf->Cell(50,8,sprintf("%.2f",$value["imp"]),1,2,'C',0);
			else
			{
				$imp=($value["imp"]>0?sprintf("%.2f",$value["imp"]):sprintf("%.2f",$value["imp_divisa"]*$cambio));
				$imp_divisa=($value["imp_divisa"]>0?sprintf("%.2f",$value["imp_divisa"]):"");
				$pdf->Cell(25,8,$imp,1,0,'C',0);
				$pdf->Cell(25,8,$imp_divisa,1,2,'C',0);
			}

		}

		if($divisa!="€")
		{
			$pdf->SetX(15);
			$pdf->Cell(130,8,pdfstring("1 $divisa = $cambio Eur"),1,0,'L',0);
			$tot=$totale["F"]+$cambio*$totale_divisa["F"];
			$tot=sprintf("%.2f Eur",$tot);
		}
		else
			$tot=sprintf("%.2f",$totale["F"]);
		$pdf->SetX(15);
		$pdf->SetFont('Arial','B',13);
		$pdf->Cell(130,8,"Totale fatture",1,0,'R',0);
		$pdf->SetFont('Arial','',13);
		$pdf->Cell(50,8,pdfstring($tot),1,2,'C',0);
		$pdf->Ln(5);
	}
	$pdf->SetFont('Arial','B',13);
	$pdf->SetX(15);
	$pdf->Cell(180,8,"RIEPILOGATIVO SPESE SOSTENUTE DURANTE LA TRASFERTA - RICEVUTE:",1,2,'C',0);
	$pdf->Cell(130,8,"Descrizione",1,0,'C',0);
	if($divisa=="€")
		$pdf->Cell(50,8,"Importo (Euro)",1,2,'C',0);
	else
	{
		$pdf->SetFontSize(11);
		$pdf->Cell(50,4,"Importo",'TR',2,'C',0);
		$pdf->Cell(25,4,"Euro",'',0,'C',0);
		$pdf->Cell(25,4,$divisa,'BR',2,'C',0);
	}

	$pdf->SetFont('Arial','',13);
	if(count($spese["R"]))
	{
		foreach($spese["R"] as $value)
		{
			$pdf->SetX(15);
			$pdf->Cell(130,8,$value["desc"],1,0,'L',0);
			if($divisa=="€")
				$pdf->Cell(50,8,sprintf("%.2f",$value["imp"]),1,2,'C',0);
			else
			{
				$imp=($value["imp"]>0?sprintf("%.2f",$value["imp"]):sprintf("%.2f",$value["imp_divisa"]*$cambio));
				$imp_divisa=($value["imp_divisa"]>0?sprintf("%.2f",$value["imp_divisa"]):"");
				$pdf->Cell(25,8,$imp,1,0,'C',0);
				$pdf->Cell(25,8,$imp_divisa,1,2,'C',0);
			}
		}
	}
	else
	{
		$pdf->SetX(15);
		$pdf->Cell(130,8,"",1,0,'C',0);
		if($divisa=="€")
			$pdf->Cell(50,8,sprintf("%.2f",0),1,2,'C',0);
		else
		{
			$pdf->Cell(25,8,"---",1,0,'C',0);
			$pdf->Cell(25,8,"---",1,2,'C',0);
		}
	}

	if($divisa!="€")
	{
		$pdf->SetX(15);
		$pdf->Cell(130,8,pdfstring("1 $divisa = $cambio Eur"),1,0,'L',0);
		$tot=$totale["R"]+$cambio*$totale_divisa["R"];
		$tot=sprintf("%.2f Eur",$tot);
	}
	else
		$tot=sprintf("%.2f",$totale["R"]);

	$pdf->SetX(15);
	$pdf->SetFont('Arial','B',14);
	$pdf->Cell(130,8,"Totale ricevute",1,0,'R',0);
	$pdf->SetFont('Arial','',14);
	$pdf->Cell(50,8,pdfstring($tot),1,2,'C',0);

	$pdf->SetXY(15,255);
	$pdf->Cell(90,8,"Data e Firma _____________________",0,0,'L',0);
	$pdf->Cell(90,8,"Firma resp. S.ES _____________________",0,0,'L',0);
	$pdf->Output();
}

function formattaimporto_float($importo,$punti)
{
	if(!strlen($importo))
		$importo=0;
	$formatted=sprintf("%.2f",$importo);
	$formatted=str_replace(".",",",$formatted);
	if($punti)
	{
		$i=strlen($formatted)-6;
		for($i;$i>($importo>0?0:1);$i-=3)
			$formatted=substr($formatted,0,$i).".".substr($formatted,$i);
	}
	return $formatted;
}


function stampa_per_commessa($mese,$anno)
{
	global $mesi,$giorni_settimana,$myhost,$myuser,$mypass,$dbname;

	require_once("fpdf/fpdf.php");

	$conn=mysqli_connect($myhost, $myuser, $mypass)
		or die("Connessione non riuscita".mysqli_error($conn));
	((bool)mysqli_query($conn, "USE " . $dbname));

	$query="SELECT CONCAT(u.cognome,\" \",u.nome) as Nome,c.commessa as Commessa,SUM((((((p.uscita+1) DIV 5)*5-((p.ingresso+1) DIV 5)*5+1440) MOD 1440)-(((p.pausa+1) DIV 5)*5)) + (((((p.uscita2+1) DIV 5)*5+1440) MOD 1440)-((((p.ingresso2+1) DIV 5)*5+1440) mod 1440))) AS Ore FROM(utenti u LEFT JOIN presenze p ON u.id=p.id) LEFT JOIN commesse c ON p.id_commessa=c.id WHERE ((p.ingresso!=-1 AND p.uscita!=-1)OR(p.ingresso2!=-1 AND p.uscita2!=-1)) AND (p.giorno BETWEEN \"".date("Y-m-d",mktime(0,0,0,$mese,1,$anno))."\" AND \"".date("Y-m-d",mktime(0,0,0,$mese+1,0,$anno))."\") AND u.attivo=1 GROUP BY p.id,p.id_commessa";

	$result=mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));

	$pdf=new FPDF("P","mm","A4");
	$pdf->SetTextColor(0);
	$pdf->SetDrawColor(0);
	$pdf->SetFillColor(224,235,255);
	$pdf->SetFont('Arial','B',14);

	$w=array();


	while($row=mysqli_fetch_assoc($result))
	{
		foreach($row as $name=>$value)
		{
			if(!isset($w[$name]))
				$w[$name]=$pdf->GetStringWidth($name);
			else
			{
				$width=($name=="Ore"?$pdf->GetStringWidth(int_to_hour($value)):$pdf->GetStringWidth($value));
				if($w[$name]<=$width)
					$w[$name]=$width;
			}
		}
	}

	mysqli_data_seek($result,  0);
	$starty=30;
	$y=$starty;
	$leftpoint=(210-(array_sum($w)+5*count($w)))/2;
	if($leftpoint<5)
	{
		$leftpoint=5;
		$ii=0;
		$cum=0;
		$wd=200-3*count($w);
		while(($ii<count($w))&&($cum<=$wd)&&($cum+$w[$ii]<=$wd))
		{
			$cum+=$w[$ii];
			$ii++;
		}

		for($jj=$ii;$jj<count($w);$jj++)
		{
			$w[$jj]=($wd-$cum)/(count($w)-$ii);
		}
	}

	$pdf->AddPage();

	$pdf->SetY($y);
	$pdf->SetX($leftpoint);

	//Header
	$pdf->SetTextColor(0);
	$pdf->SetFont('Arial','B',14);
	foreach($w as $h=>$width)
		$pdf->Cell($width+5,7,$h,1,0,'C',1);
	$pdf->Ln();

	$nominativo="";
	while($row=mysqli_fetch_assoc($result))
	{
		//Color and font restoration
		$pdf->SetTextColor(0);
		$pdf->SetFont('');

		$pdf->SetX($leftpoint);
		$i=0;
		$fill=0;
		if($row["Nome"]!=$nominativo)
			$pdf->Line($leftpoint,$pdf->GetY(),210-$leftpoint,$pdf->GetY());

		foreach($row as $name=>$value)
		{
			$text=($name=="Ore"?int_to_hour($value):$value);

			if(($name=="Nome")&&($value==$nominativo))
				$text="";
			$pdf->Cell($w[$name]+5,7,$text,'LR',0,($i>0?'C':'L'),$fill);
			$i++;
		}
		$pdf->Ln();
		$nominativo=$row["Nome"];
		//$fill=!$fill;
	}
	$pdf->Line($leftpoint,$pdf->GetY(),210-$leftpoint,$pdf->GetY());
	((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
	((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
	$pdf->Output();
}

function stampa_fogli_viaggio($da_data,$a_data)
{
	global $mesi,$giorni_settimana,$myhost,$myuser,$mypass,$dbname;
	$conn=mysqli_connect($myhost, $myuser, $mypass)
		or die("Connessione non riuscita".mysqli_error($conn));
	((bool)mysqli_query($conn, "USE " . $dbname))
	    or die("$dbname,$conn: ".mysqli_error($conn));

	$query="SELECT fogliviaggio.id_foglio, utenti.nome, utenti.cognome,
					fogliviaggio.datainizio, fogliviaggio.orainizio,
					fogliviaggio.datafine, fogliviaggio.orafine,
					fogliviaggio.loc, fogliviaggio.paese, fogliviaggio.motivo,
					fogliviaggio.numero,fogliviaggio.anno, fogliviaggio.locked,
					commesse.commessa,commesse.descrizione AS commessa_desc,
					fogliviaggio.conguaglio,
					SUM( fogliviaggio_dettagliospese.importo ) AS spese,
					(fogliviaggio.cambio*SUM( fogliviaggio_dettagliospese.importo_divisa )) AS spese_divisa,
					ditte.ragione_sociale
		FROM  fogliviaggio
		LEFT JOIN commesse ON fogliviaggio.id_commessa = commesse.id
		LEFT JOIN utenti ON fogliviaggio.id_utente = utenti.id
		LEFT JOIN fogliviaggio_dettagliospese ON fogliviaggio.id_foglio = fogliviaggio_dettagliospese.id_foglio
		LEFT JOIN ditte ON utenti.ditta = ditte.id
		WHERE fogliviaggio.datafine >=  '$da_data'
		AND fogliviaggio.datainizio <=  '$a_data'
		GROUP BY fogliviaggio.id_foglio
		ORDER BY ditte.ragione_sociale,cognome,datainizio";
	$result=mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));
?>
	<style type="text/css">
		td {padding:5px;border:1px solid black;}
		table {border-collapse:collapse;}
	</style>
	<table>
		<tr>
			<td>ditta</td>
			<td>cognome</td>
			<td>nome</td>
			<td>numero</td>
			<td>paese</td>
			<td>luogo</td>
			<td>motivo</td>
			<td>datainizio</td>
			<td>orainizio</td>
			<td>datafine</td>
			<td>orafine</td>
			<td>commessa</td>
			<td>sommaspese</td>
			<td>link</td>
			<td>lock</td>
			<td>cong</td>
 		</tr>
<?
	while($row=mysqli_fetch_assoc($result))
	{
		$ditta=(strstr($row["ragione_sociale"],"Logistic")?"WWLS":"WWS");
		$cognome=$row["cognome"];
		$nome=$row["nome"];
		$numero_foglio=(strlen($row["numero"])?sprintf("%03d / %2d",$row["numero"],substr($row["anno"],2)):$row["id_foglio"]);
		$id_foglio=$row["id_foglio"];
		$stato=$row["paese"];
		$luogo=$row["loc"];
		$motivo=$row["motivo"];
		$datainizio=$row["datainizio"];
		$orainizio=int_to_hour($row["orainizio"]);
		$datafine=$row["datafine"];
		$orafine=int_to_hour($row["orafine"]);
		$commessa=sprintf("%s - %s",$row["commessa"],$row["commessa_desc"]);
		$cong=($row["conguaglio"]?"Si":"No");
		$foglio_action=($row["conguaglio"]?"cong_off":"cong_on");
		$cong_toggle="index.php?op=pa_foglio_viaggio";
		$cong_toggle.="&foglio_action=$foglio_action";
		$cong_toggle.="&foglio_to_edit=$id_foglio";
		$cong_toggle.="&redirect=da_data.".my_date_format($da_data,"d/m/Y");
		$cong_toggle.=",a_data.".my_date_format($a_data,"d/m/Y");
		$cong_toggle.=",submit_fogli_viaggio.stampa";

		$sommaspese=round($row["spese"]+$row["spese_divisa"],2);
		$link="index.php?op=stampa_foglio&foglio_to_print=$id_foglio";

		$locked=$row["locked"];
		$foglio_action=($locked?"lock_off":"lock_on");
		$link_lock="index.php?op=pa_foglio_viaggio";
		$link_lock.="&foglio_action=$foglio_action";
		$link_lock.="&foglio_to_edit=$id_foglio";
		$link_lock.="&redirect=da_data.".my_date_format($da_data,"d/m/Y");
		$link_lock.=",a_data.".my_date_format($a_data,"d/m/Y");
		$link_lock.=",submit_fogli_viaggio.stampa";
?>
		<tr>
			<td><?=$ditta;?></td>
			<td><?=$cognome;?></td>
			<td><?=$nome;?></td>
			<td><?=$numero_foglio;?></td>
			<td><?=$stato;?></td>
			<td><?=$luogo;?></td>
			<td><?=$motivo;?></td>
			<td><?=$datainizio;?></td>
			<td><?=$orainizio;?></td>
			<td><?=$datafine;?></td>
			<td><?=$orafine;?></td>
			<td><?=$commessa;?></td>
			<td><?=$sommaspese;?></td>
			<td><a href="<?=$link;?>">link</a></td>
			<td>
				<a href="<?=$link_lock;?>">
					<?=$locked?"unlock":"lock"?>
				</a>
			</td>
			<td>
				<a href="<?=$cong_toggle;?>">
					<?=$cong?>
				</a>
			</td>
 		</tr>
<?
	}
?>
	</table>
<?
	((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
	((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);

}

function stampa_conguagli($da_data,$a_data)
{
?>
	<style type="text/css">
		td {padding:5px;border:1px solid black;}
		table {border-collapse:collapse;}
	</style>
<?
	global $mesi,$giorni_settimana,$myhost,$myuser,$mypass,$dbname;
	$conn=mysqli_connect($myhost, $myuser, $mypass)
		or die("Connessione non riuscita".mysqli_error($conn));
	((bool)mysqli_query($conn, "USE " . $dbname))
	    or die("$dbname,$conn: ".mysqli_error($conn));

	$query="SELECT fogliviaggio.id_foglio, utenti.nome, utenti.cognome,
					fogliviaggio.datainizio, fogliviaggio.orainizio,
					fogliviaggio.datafine, fogliviaggio.orafine,
					fogliviaggio.loc, fogliviaggio.paese, fogliviaggio.motivo,
					fogliviaggio.giorni_ord,fogliviaggio.giorni_base,fogliviaggio.giorni_fest,
					fogliviaggio.cong_ord,fogliviaggio.cong_base,fogliviaggio.cong_fest,
					fogliviaggio.numero,fogliviaggio.anno, fogliviaggio.locked,
					commesse.commessa,commesse.descrizione AS comm_desc,
					ROUND(SUM( fogliviaggio_dettagliospese.importo )+(fogliviaggio.cambio*SUM( fogliviaggio_dettagliospese.importo_divisa)),2) AS spese,
					ditte.ragione_sociale
		FROM  fogliviaggio
		LEFT JOIN utenti ON fogliviaggio.id_utente = utenti.id
		LEFT JOIN fogliviaggio_dettagliospese ON fogliviaggio.id_foglio = fogliviaggio_dettagliospese.id_foglio
		LEFT JOIN ditte ON utenti.ditta = ditte.id
		LEFT JOIN commesse ON fogliviaggio.id_commessa=commesse.id
		WHERE fogliviaggio.datafine >=  '$da_data'
		AND fogliviaggio.datainizio <=  '$a_data'
		AND fogliviaggio.conguaglio=0
		GROUP BY fogliviaggio.id_foglio
		ORDER BY paese,datainizio,cognome";
	$result=mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));
	$lines=array();
	require_once("foglio_viaggio_util.php");
	while($row=mysqli_fetch_assoc($result))
	{
		$giorni=calcolagiorni($row["datainizio"],$row["orainizio"],$row["datafine"],$row["orafine"]);
		if(strlen($row["numero"]))
			$numero=sprintf("%03d / %2d",$row["numero"],substr($row["anno"],2));
		else
			$numero=$row["id_foglio"];
		$cognome=$row["cognome"];
		$nome=$row["nome"];
		$dal=$row["datainizio"];
		$al=$row["datafine"];
		$spese=$row["spese"];
		$giorni_ord=(int)$row["giorni_ord"];
		$giorni_base=(int)$row["giorni_base"];
		$giorni_fest=(int)$row["giorni_fest"];
		if(strlen($row["commessa"]))
			$commessa=sprintf("%s (%s)",$row["commessa"],$row["comm_desc"]);
		else
			$commessa="";

		$key=sprintf("%s;%s;%s;%s;%s",$row["paese"],$commessa,$row["cong_ord"],$row["cong_base"],$row["cong_fest"]);

		$lines[$key][]=array
		(
			"cognome"=>$cognome,
			"dal"=>$dal,
			"al"=>$al,
			"numero"=>$numero,
			"giorni"=>$giorni,
			"giorni_ord"=>$giorni_ord,
			"giorni_base"=>$giorni_base,
			"giorni_fest"=>$giorni_fest,
			"spese"=>$spese
		);
	}
	((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
	((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);

	foreach($lines as $k=>$v)
	{
		list($paese,$commessa,$cong_ord,$cong_base,$cong_fest)=explode(";",$k);
		$header=$paese;
		if(strlen($commessa))
			$header.=" - $commessa";
		ob_start();
?>
	<table>
		<tr>
			<td colspan="{colspan}">
				TRASFERTE <?=$header?>
			</td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td>NOME</td>
<?
		$i=3;
		foreach($v as $t)
		{
			$i+=2;
		?>
			<td colspan="2"><?=$t["cognome"]?></td>
<?		}?>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td>DAL</td>
<?
		foreach($v as $t)
		{
		?>
			<td colspan="2"><?=$t["dal"]?></td>
<?		}?>
		</tr>
		<tr>
			<td>VALORI EURO</td>
			<td></td>
			<td>AL</td>
<?
		foreach($v as $t)
		{
		?>
			<td colspan="2"><?=$t["al"]?></td>
<?		}?>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td>FOGLIO DI VIAGGIO</td>
<?
		foreach($v as $t)
		{
		?>
			<td colspan="2"><?=$t["numero"]?></td>
<?		}?>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td>GIORNI TOT</td>
<?
		foreach($v as $t)
		{
		?>
			<td colspan="2"><?=(int)$t["giorni"]?></td>
<?		}?>
		</tr>
<?
	if(strlen($cong_ord))
	{?>
		<tr>
			<td><?=$cong_ord?></td>
			<td></td>
			<td>Trasferta Ordinaria</td>
<?
		foreach($v as $t)
		{
		?>
			<td><?=$t["giorni_ord"]?></td>
			<td><?=to_comma($t["giorni_ord"]*$cong_ord)?></td>
<?		}?>
		</tr>
<?
	}
	if(strlen($cong_base))
	{?>
		<tr>
			<td><?=$cong_base?></td>
			<td></td>
			<td>Trasferta in Base</td>
<?
		foreach($v as $t)
		{
		?>
			<td><?=$t["giorni_base"]?></td>
			<td><?=to_comma($t["giorni_base"]*$cong_base)?></td>
<?		}?>
		</tr>
<?
	}
	if(strlen($cong_fest))
	{?>
		<tr>
			<td><?=$cong_fest?></td>
			<td></td>
			<td>Trasferta Festiva</td>
<?
		foreach($v as $t)
		{
		?>
			<td><?=$t["giorni_fest"]?></td>
			<td><?=to_comma($t["giorni_fest"]*$cong_fest)?></td>
<?		}?>
		</tr>
<?	}?>
		<tr>
			<td></td>
			<td></td>
			<td>SPESE</td>
<?
		$i=3;
		foreach($v as $t)
		{
			$i+=2;
		?>
			<td colspan="2"><?=to_comma($t["spese"])?></td>
<?		}?>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td>TOTALE TRASFERTA</td>
<?
		$i=3;
		foreach($v as $t)
		{
			$i+=2;
			$tot_ord=(strlen($cong_ord)?$t["giorni_ord"]*$cong_ord:0);
			$tot_base=(strlen($cong_base)?$t["giorni_base"]*$cong_base:0);
			$tot_fest=(strlen($cong_fest)?$t["giorni_fest"]*$cong_fest:0);
			$spese=$t["spese"];
		?>
			<td colspan="2"><?=to_comma($tot_ord+$tot_base+$tot_fest+$spese)?></td>
<?		}?>
		</tr>

	</table>
	<br>
<?
		echo str_replace("{colspan}",$i,ob_get_clean());
	}
}

function to_comma($importo)
{
	return str_replace(".",",",$importo);
}

function stampa_per_periodo($da_data,$a_data)
{
	global $mesi,$giorni_settimana,$myhost,$myuser,$mypass,$dbname;

	require_once("fpdf/fpdf.php");


	$conn=mysqli_connect($myhost, $myuser, $mypass)
		or die("Connessione non riuscita".mysqli_error($conn));
	((bool)mysqli_query($conn, "USE " . $dbname));
	$query="SELECT utenti.*,ditte.ragione_sociale FROM utenti JOIN ditte ON utenti.ditta=ditte.id WHERE utenti.attivo=1 ORDER BY ditte.ragione_sociale,utenti.cognome";
	$result=mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));
	$pdf=new FPDF("P","mm","A4");
	while($utente=mysqli_fetch_assoc($result))
	{
		if(isset($tabella))
			unset($tabella);

		$tabella=tabella_presenze($conn,$utente["id"],date_to_sql($da_data),date_to_sql($a_data),$utente["data_inizio_coll"],$utente["data_fine_coll"],false);
		if(count($tabella))
		{
			$header=array();
			$i=0;

			$pdf->SetFont('Arial','',9);
			$header[0][$i]="Giorno";
			if(isset($w))
				unset($w);
			$w[$i]=$pdf->GetStringWidth("Giorno");
			$i++;
			$riga=0;
			$cicla=true;
			$num_fields=1;
			$i_ore=-1;
			$i_str=-1;
			$i_per=-1;
			$i_via=-1;
			$somma_ore=0;
			$somma_per=0;
			$somma_str=0;
			$somma_via=0;
			while($cicla)
			{
				$cicla=false;
				if($riga>0)
					$header[$riga][0]="";
				foreach(current($tabella) as $field=>$void)
					if(substr($field,0,10)!="__hidden__")
					{
						if($riga==0)
							$num_fields++;
						$explode=explode("<br>",$field);
						$cicla|=(count($explode)-1>$riga);
						$header[$riga][$i]=$explode[$riga];
						if(($riga==0)||(($w[$i]<=$pdf->GetStringWidth($header[$riga][$i]))))
							$w[$i]=$pdf->GetStringWidth($header[$riga][$i]);
						if($field=="Ore<br>Str")
							$i_str=$i;
						elseif($field=="Ore<br>Perm")
							$i_per=$i;
						elseif($field=="Ore<br>Giorn")
							$i_ore=$i;
						elseif($field=="Ore<br>Viaggio")
							$i_via=$i;
						$i++;
					}
				$riga++;
				$i=1;
			}
			$i=0;
			$data=array();
			foreach($tabella as $giorno=>$row)
			{
				$j=0;
				$data[$i][$j]=my_date_format($giorno,"d ").substr($mesi[my_date_format($giorno,"n")-1],0,3).", ".substr($giorni_settimana[my_date_format($giorno,"w")],0,3);
				if($pdf->GetStringWidth($data[$i][$j])>$w[$j])
					$w[$j]=$pdf->GetStringWidth($data[$i][$j]);
				$j++;
				foreach($row as $fieldname=>$field)
					if(substr($fieldname,0,10)!="__hidden__")
					{
						$data[$i][$j]=$field;
						if((!strstr($data[$i][$j],"<img "))&&($pdf->GetStringWidth($data[$i][$j])>$w[$j]))
							$w[$j]=$pdf->GetStringWidth($data[$i][$j]);
						if(($j==$i_ore)&&(hour_to_int($data[$i][$j])>0))
							$somma_ore+=hour_to_int($data[$i][$j]);
						elseif(($j==$i_per)&&(hour_to_int($data[$i][$j])>0))
							$somma_per+=hour_to_int($data[$i][$j]);
						elseif(($j==$i_str)&&(hour_to_int($data[$i][$j])>0))
							$somma_str+=hour_to_int($data[$i][$j]);
						elseif(($j==$i_via)&&(hour_to_int($data[$i][$j])>0))
						$somma_via+=hour_to_int($data[$i][$j]);

					$j++;
				}
				elseif($fieldname=="__hidden__festivo")
					$data[$i]["festivo"]=$field;
				$i++;
			}
			$pdf->AddPage();

			$pdf->SetTextColor(0);
			$pdf->SetDrawColor(20,20,20);
			$pdf->SetFont('Arial','B',22);
			$pdf->SetX(15);
			$pdf->Cell(180,20,$utente["ragione_sociale"],1,2,'C',0);
			$pdf->SetFont('Arial','B',15);
			$pdf->Ln(5);
			$pdf->SetX(15);
			$pdf->Cell(180,8,$utente["nome"]." ".$utente["cognome"],0,2,'C',0);
			$pdf->SetX(15);
			$pdf->Cell(180,8,"presenze dal $da_data al $a_data",0,2,'C',0);
			$pdf->Ln(5);
			$pdf->SetFillColor(240,240,240);
			$pdf->SetDrawColor(200,200,200);
			$pdf->SetLineWidth(.1);
			$pdf->SetFont('Arial','B',9);

			$leftpoint=(210-(array_sum($w)+3*count($w)))/2;
			if($leftpoint<5)
			{
				$leftpoint=5;
				$ii=0;
				$cum=0;
				$wd=200-3*count($w);
				while(($ii<count($w))&&($cum<=$wd)&&($cum+$w[$ii]<=$wd))
				{
					$cum+=$w[$ii];
					$ii++;
				}
				for($jj=$ii;$jj<count($w);$jj++)
				{
					$w[$jj]=($wd-$cum)/(count($w)-$ii);
				}
			}

			//Header
			for($i=0;$i<count($header);$i++)
			{
				$pdf->SetX($leftpoint);
				for($j=0;$j<$num_fields;$j++)
					$pdf->Cell($w[$j]+3,5,$header[$i][$j],'LR',0,'C',1);
				$pdf->Ln();
			}
			//$pdf->Ln();
			//Color and font restoration
			$pdf->SetFillColor(224,235,255);
			$pdf->SetTextColor(0);
			$pdf->SetFont('');
			//Data
			foreach($data as $row)
			{
				$pdf->SetX($leftpoint);
				$fill=$row["festivo"];
				for($i=0;$i<$num_fields;$i++)
				{
					if(!strstr($row[$i],"<img "))
					{
						if($pdf->GetStringWidth($row[$i])<=($w[$i]+3))
							$pdf->Cell($w[$i]+3,5,$row[$i],1,0,($i>0?'C':'L'),$fill);
						else
						{
							$pdf->SetFontSize(7);
							if($pdf->GetStringWidth($row[$i])<=($w[$i]+3))
								$pdf->Cell($w[$i]+3,5,$row[$i],1,0,($i>0?'C':'L'),$fill);
							else
							{
								$x=$pdf->GetX();
								$y=$pdf->GetY();
								$pdf->MultiCell($w[$i]+3,2.5,$row[$i],0,'C',$fill);
								$pdf->SetXY($x,$y);
								$pdf->Cell($w[$i]+3,5,"",1,0,'L',$fill);
							}
							$pdf->SetFontSize(9);
						}
					}
					else
					{
						$pdf->Image("img/check_bianco.png",$pdf->GetX()+($w[$i]-1)/2,$pdf->GetY()+.5,4,4);
						$pdf->Cell($w[$i]+3,5,"",1,0,'C',$fill);
					}
				}
				$pdf->Ln();
				$fill=!$fill;
			}
			//$pdf->Cell(20*count($header),0,'','T');

			if($i_ore!=-1)
			{
				$lp=$leftpoint;
				for($i=0;$i<$i_ore;$i++)
					$lp+=$w[$i]+3;
				$pdf->SetX($lp);
				$pdf->Cell($w[$i_ore]+3,5,int_to_hour($somma_ore),1,0,'C',1);
			}
			if($i_str!=-1)
			{
				$lp=$leftpoint;
				for($i=0;$i<$i_str;$i++)
					$lp+=($w[$i]+3);
				$pdf->SetX($lp);
				$pdf->Cell($w[$i_str]+3,5,int_to_hour($somma_str),1,0,'C',1);
			}
			if($i_per!=-1)
			{
				$lp=$leftpoint;
				for($i=0;$i<$i_per;$i++)
					$lp+=$w[$i]+3;
				$pdf->SetX($lp);
				$pdf->Cell($w[$i_per]+3,5,int_to_hour($somma_per),1,0,'C',1);
			}
			if($i_via!=-1)
			{
				$lp=$leftpoint;
				for($i=0;$i<$i_via;$i++)
					$lp+=$w[$i]+3;
				$pdf->SetX($lp);
				$pdf->Cell($w[$i_via]+3,5,int_to_hour($somma_via),1,0,'C',1);
			}
		}
	}
	((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
	((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
	$pdf->Output();
}



function stampa_indennita($mese,$anno)
{
	global $mesi,$giorni_settimana,$myhost,$myuser,$mypass,$dbname;

	require_once("fpdf/fpdf.php");

	$da=date("Y-m-d",mktime(0,0,0,$mese,1,$anno));
	$a=date("Y-m-d",mktime(0,0,0,$mese+1,0,$anno));
	$conn=mysqli_connect($myhost, $myuser, $mypass)
		or die("Connessione non riuscita".mysqli_error($conn));
	((bool)mysqli_query($conn, "USE " . $dbname));

	$query="SELECT * FROM presenze WHERE id=".$_SESSION["id_edit"]." AND (giorno BETWEEN \"$da\" AND \"$a\")
				AND CHAR_LENGTH(tr_destinazione)>0 AND CHAR_LENGTH(tr_motivo)>0
				ORDER BY giorno";
			//AND CHAR_LENGTH(tr_destinazione)>0 AND CHAR_LENGTH(tr_motivo)>0
	$result=mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));
	$pdf=new FPDF("L","mm","A4");

	$pdf->SetAutoPageBreak(false);

	$pdf->AddPage();
	$pdf->SetMargins(7,7);
	$pdf->SetLineWidth(.05);
	$pdf->SetTextColor(0);
	$pdf->SetDrawColor(20,20,20);
	$pdf->SetFont('Arial','B',12);
	$pdf->SetXY(7,7);
	$pdf->Cell(283,5,"TRASFERTA",1,2,'C',0);
	$pdf->SetFont('Arial','B',11);
	$pdf->Ln(1);
	$pdf->Cell(283,5,"INCARICO E AUTORIZZAZIONE",1,2,'C',0);
	$pdf->SetFont('Arial','',10);

	$pdf->Cell(283,4,"La societ".chr(225)." ".$_SESSION["ditta_rs"]." rimborsa ".($_SESSION["sesso_dip"]=='F'?"alla Sig.ra ":"al Sig ").$_SESSION["cognome"]." ".$_SESSION["nome"]." l'indennit".chr(225)." forfetaria di cui all'art.51 comma 5 DPR 917/86",'LTR',2,'L',0);
	$pdf->Cell(283,4,"pi".chr(250)." le spese sostenute risultanti da prospetto che segue in relazione alle missioni e trasferte dallo stesso effettuate fuori dal territorio comunale ",'LR',2,'L',0);
	$pdf->Cell(283,4,"nel periodo dal ".date("d.m.Y",mktime(0,0,0,$mese,1,$anno))." al ".date("d.m.Y",mktime(0,0,0,$mese+1,0,$anno))." a seguito specifica e separata autorizzazione",'LBR',2,'L',0);

	$pdf->SetFont('Arial','B',11);
	$pdf->Ln(1);
	$pdf->Cell(283,5,"RIEPILOGO TRASFERTE FUORI DAL TERRITORIO COMUNALE",1,2,'C',0);

	$pdf->SetFillColor(220,220,220);
	$width=array(25,70,68,17,17,17,17,17,17,18);
	$pdf->Cell($width[0],12,"DATA",1,0,'C',1);
	$pdf->Cell($width[1],12,"DESTINAZIONE",1,0,'C',1);
	$pdf->Cell($width[2],12,"MOTIVO",1,0,'C',1);
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell($width[3],4,"","LTR",0,'C',1);

	$pdf->Cell($width[4]+$width[5]+$width[6],4,"RIMBORSO SPESE VIVE",1,0,'C',1);

	$pdf->Cell($width[7],4,"","LTR",0,'C',1);
	$pdf->Cell($width[8],4,"","LTR",0,'C',1);
	$pdf->Cell($width[9],4,"","LTR",1,'C',1);

	$pdf->SetX(7+$width[0]+$width[1]+$width[2]);
	$pdf->Cell($width[3],4,"Indennit".chr(225),"LR",0,'C',1);
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell($width[4],4,"Spese","LTR",0,'C',1);
	$pdf->Cell($width[5],4,"Spese","LTR",0,'C',1);
	$pdf->Cell($width[6],4,"Spese","LTR",0,'C',1);

	$pdf->Cell($width[7],4,"KM","LR",0,'C',1);
	$pdf->Cell($width[8],4,"rimb/KM","LR",0,'C',1);
	$pdf->Cell($width[9],4,"tot rimb","LR",2,'C',1);

	$pdf->SetX($pdf->GetX()-$width[8]-$width[7]-$width[6]-$width[5]-$width[4]-$width[3]);
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell($width[3],4,"forfetaria","LBR",0,'C',1);
	$pdf->Cell($width[4],4,"viaggio","LBR",0,'C',1);
	$pdf->Cell($width[5],4,"alloggio","LBR",0,'C',1);
	$pdf->Cell($width[6],4,"di vitto","LBR",0,'C',1);

	$pdf->Cell($width[7],4,"percorsi","LBR",0,'C',1);
	$pdf->Cell($width[8],4,"","LBR",0,'C',1);
	$pdf->Cell($width[9],4,"km","LBR",1,'C',1);

	$pdf->SetFont('Arial','',9);
	$somma_forf=0;
	$somma_via=0;
	$somma_all=0;
	$somma_vit=0;
	$somma_km=0;
	$somma_euri_km=0;
	while($row=mysqli_fetch_assoc($result))
	{
		$pdf->Cell($width[0],3.5,my_date_format($row["giorno"],"d/m/Y"),1,0,'C',0);
		$pdf->Cell($width[1],3.5,pdfstring($row["tr_destinazione"]),1,0,'L',0);
		$pdf->Cell($width[2],3.5,pdfstring($row["tr_motivo"]),1,0,'L',0);
		$pdf->Cell($width[3],3.5,formattaimporto_float($row["tr_ind_forf"],true),1,0,'C',0);
		$somma_forf+=$row["tr_ind_forf"];
		$pdf->Cell($width[4],3.5,formattaimporto_float($row["tr_spese_viaggio"],true),1,0,'C',0);
		$somma_via+=$row["tr_spese_viaggio"];
		$pdf->Cell($width[5],3.5,formattaimporto_float($row["tr_spese_alloggio"],true),1,0,'C',0);
		$somma_all+=$row["tr_spese_alloggio"];
		$pdf->Cell($width[6],3.5,formattaimporto_float($row["tr_spese_vitto"],true),1,0,'C',0);
		$somma_vit+=$row["tr_spese_vitto"];
		$pdf->Cell($width[7],3.5,$row["tr_km"],1,0,'C',0);
		$somma_km+=$row["tr_km"];
		$pdf->Cell($width[8],3.5,formattaimporto_float($row["tr_euri_km"],true),1,0,'C',0);
		$somma_euri_km+=($row["tr_km"]*$row["tr_euri_km"]);
		$pdf->Cell($width[9],3.5,formattaimporto_float($row["tr_euri_km"]*$row["tr_km"],true),1,1,'C',0);
	}
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell($width[0]+$width[1]+$width[2],5,"",0,0,'R',0);

	$pdf->Cell($width[3],5,formattaimporto_float($somma_forf,true),1,0,'C',1);
	$pdf->Cell($width[4],5,formattaimporto_float($somma_via,true),1,0,'C',1);
	$pdf->Cell($width[5],5,formattaimporto_float($somma_all,true),1,0,'C',1);
	$pdf->Cell($width[6],5,formattaimporto_float($somma_vit,true),1,0,'C',1);

	$pdf->Cell($width[7]+$width[8],6,"",0,0,'R',0);
	$pdf->Cell($width[9],5,formattaimporto_float($somma_euri_km,true),1,1,'C',1);
	$pdf->ln(1);

	$pdf->Cell($width[0]+$width[1]+$width[2],5,"TOTALE CORRISPOSTO (INDENNITA' + SPESE VIVE + RIMBORSI KM)",1,0,'L',0);
	$pdf->Cell($width[9]+$width[3]+$width[4]+$width[5]+$width[6]+$width[7]+$width[8],5,formattaimporto_float($somma_forf+$somma_via+$somma_all+$somma_vit+$somma_euri_km,true),1,1,'C',1);


//	$pdf->SetY(177.5);
	$pdf->ln(4);
	$y=$pdf->GetY()+2;
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(283,4,(@$_SESSION["sesso_dip"]=='F'?"La sottoscritta ":"Il sottoscritto ").$_SESSION["cognome"]." ".$_SESSION["nome"]." dichiara di ricevere in data odierna la somma a margine indicata",0,2,'L',0);
	$pdf->Cell(283,4,"quale indennit".chr(225)." forfetaria, ai sensi dell'art.51 comma 5 DPR 917/86 e rimborso delle spese vive ",0,2,'L',0);
	$pdf->Cell(283,4,"sostenute per le trasferte svolte fuori dal territorio comunale",0,2,'L',0);
	$pdf->ln(2);

	$pdf->Cell(90,25,"Data _________________",0,0,'L',0);
	$pdf->Cell(90,25,"Firma _____________________________",0,0,'R',0);
	$pdf->SetXY(245,$y);
	$pdf->SetFont('Arial','',18);
	$pdf->Multicell(45,11,"marca\nda\nbollo",1,'C',0);

	((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
	((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
	$pdf->Output();
}

function crea_tab_permessi($utente,$mese,$anno)
{
	global $mesi,$giorni_settimana,$myhost,$myuser,$mypass,$dbname;

	$da=date("Y-m-d",mktime(0,0,0,$mese,1,$anno));
	$a=date("Y-m-d",mktime(0,0,0,$mese+1,1,$anno));
	$conn=mysqli_connect($myhost, $myuser, $mypass)
		or die("Connessione non riuscita".mysqli_error($conn));
	((bool)mysqli_query($conn, "USE " . $dbname));
	$query="SELECT * FROM permessi_ferie WHERE id_utente=$utente AND (da>=\"$da\" OR a<=\"$a\") AND stato<2";
	$result=mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));
	$permessi=array();
	while($row=mysqli_fetch_assoc($result))
	{
		if(substr($row["da"],11)==substr($row["a"],11))
			for($i=strtotime($row["da"]);$i<=strtotime($row["a"]);$i+=86400)
				$permessi[date("d/m/Y",$i)]=$row["stato"]."480";
		else
			$permessi[date("d/m/Y",strtotime(substr($row["da"],0,10)))]=$row["stato"].(hour_to_int(substr($row["a"],11,5))-hour_to_int(substr($row["da"],11,5)));
	}
	((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
	((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
	return $permessi;
}


function stampa_pf($mese,$anno)
{
	global $mesi,$giorni_settimana,$myhost,$myuser,$mypass,$dbname;
	require_once("fpdf/fpdf.php");

	$da=mktime(0,0,0,$mese,1,$anno);
	$a=mktime(0,0,0,$mese+1,0,$anno);
	$conn2=mysqli_connect($myhost, $myuser, $mypass)
		or die("Connessione non riuscita".mysqli_error($conn));
	((bool)mysqli_query($conn2, "USE " . $dbname));
	$query="SELECT utenti.id,utenti.nome,utenti.cognome,utenti.ditta,ditte.ragione_sociale
				FROM utenti LEFT JOIN ditte
					ON utenti.ditta=ditte.id
				WHERE utenti.attivo=1
					AND utenti.livello<>2
					AND utenti.ditta<>-1
					AND utenti.stampe=1
				ORDER BY utenti.ditta,utenti.cognome,utenti.nome";
	$result=mysqli_query($conn2, $query) or die($query."<br>".mysqli_error($conn));

	$pdf=new FPDF("L","mm","A4");
	$pdf->SetAutoPageBreak(false);

	$cellh=5;
	$top=25;
	$cellw=18.5;
	$margins=7;
	$ncelle=(int)((292-2*$margins)/$cellw);
	$left=(292-$ncelle*$cellw)/2;

	$pdf->SetMargins($margins,$margins);
	$pdf->SetLineWidth(.05);
	$pdf->SetTextColor(0);
	$pdf->SetDrawColor(20,20,20);

	$ditta="";
	$ditta_id=0;

	while($row=mysqli_fetch_assoc($result))
	{
		if($row["ditta"]!=$ditta_id)
		{
			$ditta_id=$row["ditta"];
			$ditta=$row["ragione_sociale"];
			$pdf->AddPage();
			$pdf->SetFont('Arial','B',14);
			$pdf->Cell(297-2*$margins,6,$ditta,0,2,'C',0);
			$pdf->Cell(297-2*$margins,6,"Permessi / ferie ".$mesi[$mese-1]." $anno",0,2,'C',0);
			$conta=0;
			$l=$left;
		}

		if(($conta%$ncelle)==0)
		{
			if($conta)
			{
				$pdf->AddPage();
				$pdf->SetFont('Arial','B',14);
				$pdf->Cell(297-2*$margins,6,$ditta,0,2,'C',0);
				$pdf->Cell(297-2*$margins,6,"Permessi / ferie ".$mesi[$mese-1]." $anno",0,2,'C',0);
				$l=$left;
			}
			$pdf->SetXY($l,$top+2*$cellh-2);
			$d=1;
			$pdf->SetFont('Arial','B',10);
			for($i=$da;$i<$a;$i+=86400)
				$pdf->Cell(5,$cellh,$d++,1,2,'C',0);
			$l+=5;
		}
		$conta++;
		$pdf->SetXY($l,$top);
		$pdf->SetFont('Arial','',7);
		$pdf->Cell($cellw,$cellh-1,$row["cognome"],'LTR',2,'C',0);
		$pdf->Cell($cellw,$cellh-1,$row["nome"],'LBR',2,'C',0);

		$permessi=crea_tab_permessi($row["id"],$mese,$anno);
		for($i=$da;$i<$a;$i+=86400)
		{
			$dd=date("d/m/Y",$i);
			if(isset($permessi[$dd]))
			{
				$fill=(int)substr($permessi[$dd],0,1);
				if($fill==0)
					$pdf->SetFillColor(250,250,25);
				else
					$pdf->SetFillColor(0,200,0);

				if(substr($permessi[$dd],1)==480)
					$pdf->Cell($cellw,$cellh,"",1,2,'C',1);
				else
				{
					$w=$cellw/480*((int)substr($permessi[$dd],1));
					$pdf->Cell($w,$cellh,"",1,0,'C',1);
					$pdf->Cell($cellw-$w,$cellh,"",1,2,'C',0);
					$pdf->SetX($l);
				}
			}
			else
				$pdf->Cell($cellw,$cellh,"",1,2,'C',0);
		}
		$l+=$cellw;
	}
	((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
//	mysql_close($conn2);
	$pdf->Output();
}

function stampa_riepilogo($mese,$anno,$format="pdf")
{
	global $mesi,$giorni_settimana,$myhost,$myuser,$mypass,$dbname;

	require_once("fpdf/fpdf.php");


	$conn=mysqli_connect($myhost, $myuser, $mypass)
		or die("Connessione non riuscita".mysqli_error($conn));
	((bool)mysqli_query($conn, "USE " . $dbname));

	$contaCognomi=array();
	$query="SELECT cognome,nome FROM utenti
			WHERE attivo=1";
	$result=mysqli_query($conn, $query)
		or die($query."<br>".mysqli_error($conn));
	while($row=mysqli_fetch_assoc($result))
		$contaCognomi[$row["cognome"]][$row["nome"]]=1;
	((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);

	if($mese>0)
		$where="WHERE id=".$_SESSION["id"]." AND year(giorno)=$anno AND month(giorno)=$mese AND festivo=0";
	else
		$where="WHERE id=".$_SESSION["id"]." AND year(giorno)=$anno AND festivo=0";

	$query="SELECT count(giorno) AS conta FROM presenze $where";
	$result=mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));
	$row=mysqli_fetch_assoc($result);
	$giorni_lavorativi_mese=$row["conta"];
	((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);

	$query="SELECT utenti.*,ditte.ragione_sociale FROM utenti JOIN ditte ON utenti.ditta=ditte.id
			WHERE utenti.attivo=1 AND utenti.stampe=1
			ORDER BY ditte.ragione_sociale,utenti.cognome,utenti.nome";
	$result=mysqli_query($conn, $query) or die($query."<br>".mysqli_error($conn));


	$user_has_tr=array();
	$trasf=array();

	$ditta="";


	if($format=="csv")
		$output="";
	elseif($format=="pdf")
	{
		$pdf=new FPDF("P","mm","A4");
		$pdf->SetAutoPageBreak(false);
		$cellh=6;
		$top=25;
		$margins=10;
		$pdf->SetMargins($margins,$margins);
		$pdf->SetLineWidth(.05);
		$pdf->SetTextColor(0);
		$pdf->SetDrawColor(20,20,20);
	}
	elseif($format=="xls")
		$output="<?xml version='1.0'?>
					<?mso-application progid='Excel.Sheet'?>
					<Workbook xmlns='urn:schemas-microsoft-com:office:spreadsheet' xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel' xmlns:ss='urn:schemas-microsoft-com:office:spreadsheet' xmlns:html='http://www.w3.org/TR/REC-html40'>";


	$output="";
	while($utente=mysqli_fetch_assoc($result))
	{
		if($utente["ragione_sociale"]!=$ditta)
		{
			if($ditta!="")
				$output.="\n";

			if($format=="csv")
			{
				$output.=($utente["ragione_sociale"]."\n");
				$output.="\tGiorni Lavorati\tOre\tOre Str25%\tOre Str30%\tOre Str50%\tOre Str55%\tOre viaggio\tGiorni Ferie/Perm\tOre Permesso\tGiorni Malattia\n";
			}
			elseif($format=="pdf")
			{
				$pdf->AddPage();
				$pdf->SetFont('Arial','B',14);
				$pdf->Cell(190,16,$utente["ragione_sociale"],0,1,'C',0);

				$pdf->SetFont('Arial','B',12);
				if($mese>0)
					$pdf->Cell(190,6,strtoupper($mesi[$mese-1])." $anno ($giorni_lavorativi_mese gg lavorativi)",1,1,'L',0);
				else
					$pdf->Cell(190,6,"Anno $anno ($giorni_lavorativi_mese gg lavorativi)",1,1,'L',0);


				$pdf->SetFont('Arial','B',9);
				$pdf->Cell(30,$cellh,"",1,0,'L',0);
				$pdf->Cell(16,$cellh,"Giorni",1,0,'C',0);
				$pdf->Cell(16,$cellh,"Ore",1,0,'C',0);
				$pdf->Cell(16,$cellh,"Ore",1,0,'C',0);
				$pdf->Cell(16,$cellh,"Ore",1,0,'C',0);
				$pdf->Cell(16,$cellh,"Ore",1,0,'C',0);
				$pdf->Cell(16,$cellh,"Ore",1,0,'C',0);
				$pdf->Cell(16,$cellh,"Ore",1,0,'C',0);
				$pdf->Cell(16,$cellh,"Giorni",1,0,'C',0);
				$pdf->Cell(16,$cellh,"Ore",1,0,'C',0);
				$pdf->Cell(16,$cellh,"Giorni",1,1,'C',0);

				$pdf->Cell(30,$cellh,"",1,0,'L',0);
				$pdf->Cell(16,$cellh,"Lavorati",1,0,'C',0);
				$pdf->Cell(16,$cellh,"",1,0,'C',0);
				$pdf->Cell(16,$cellh,"Str 25%",1,0,'C',0);
				$pdf->Cell(16,$cellh,"Str 30%",1,0,'C',0);
				$pdf->Cell(16,$cellh,"Str 50%",1,0,'C',0);
				$pdf->Cell(16,$cellh,"Str 55%",1,0,'C',0);
				$pdf->Cell(16,$cellh,"Viaggio",1,0,'C',0);
				$pdf->Cell(16,$cellh,"Ferie/Perm",1,0,'C',0);
				$pdf->Cell(16,$cellh,"Permesso",1,0,'C',0);
				$pdf->Cell(16,$cellh,"Malattia",1,1,'C',0);
			}
			elseif($format=="xls")
			{
				if(strlen($ditta))
					$output.= "</Table></Worksheet>\n";
				$output.="<Worksheet ss:Name='ore ".$utente["ragione_sociale"]."'><Table>";
				$output.="<Row>";
				$output.="<Cell><Data ss:Type='String'></Data></Cell>";
				$output.="<Cell><Data ss:Type='String'>Giorni Lavorati</Data></Cell>";
				$output.="<Cell><Data ss:Type='String'>Ore</Data></Cell>";
				$output.="<Cell><Data ss:Type='String'>Ore Str25%</Data></Cell>";
				$output.="<Cell><Data ss:Type='String'>Ore Str30%</Data></Cell>";
				$output.="<Cell><Data ss:Type='String'>Ore Str50%</Data></Cell>";
				$output.="<Cell><Data ss:Type='String'>Ore Str55%</Data></Cell>";
				$output.="<Cell><Data ss:Type='String'>Ore viaggio</Data></Cell>";
				$output.="<Cell><Data ss:Type='String'>Giorni Ferie/Perm</Data></Cell>";
				$output.="<Cell><Data ss:Type='String'>Ore Permesso</Data></Cell>";
				$output.="<Cell><Data ss:Type='String'>Giorni Malattia</Data></Cell>";
				$output.="</Row>\n";
			}
			$ditta=$utente["ragione_sociale"];
		}
		if(isset($tabella))
			unset($tabella);

		if($mese>0)
		{
			$mesePresenze=$mese;
			$annoPresenze=$anno;
			if((date("Y-m-d",mktime(0,0,0,$mese,1,$anno))<=$utente["data_fine_coll"])
				||($utente["data_fine_coll"]=="0000-00-00"))
			{
				fillPresenze($utente["id"],
					$utente["data_inizio_coll"],
					$utente["data_fine_coll"],
					$utente["commessa_default"],
					$utente["patrono"],
					$utente["tr_euri_km"],
					$mesePresenze,
					$annoPresenze);
			}
		}
		else
		{
			$annoPresenze=$anno;
			for($mesePresenze=1;$mesePresenze<=12;$mesePresenze++)
			{
				if((date("Y-m-d",mktime(0,0,0,$mesePresenze,1,$anno))<=$utente["data_fine_coll"])
					||($utente["data_fine_coll"]=="0000-00-00"))
				{
					fillPresenze($utente["id"],
						$utente["data_inizio_coll"],
						$utente["data_fine_coll"],
						$utente["commessa_default"],
						$utente["patrono"],
						$utente["tr_euri_km"],
						$mesePresenze,
						$annoPresenze);
				}
			}
		}

		if($mese>0)
		{
			$qdal=date("Y-m-d",mktime(0,0,0,$mese,1,$anno));
			$qal=date("Y-m-d",mktime(0,0,0,$mese+1,0,$anno));
		}
		else
		{
			$qdal=date("Y-m-d",mktime(0,0,0,1,1,$anno));
			$qal=date("Y-m-d",mktime(0,0,0,12,31,$anno));
		}
		$str_mese_prec=calc_str_mese_prec(
			$conn,$qdal,$qal,$utente["id"],
			$utente["data_inizio_coll"],
			$utente["data_fine_coll"]);

		$tabella=tabella_presenze($conn,$utente["id"],
			$qdal,$qal,
			$utente["data_inizio_coll"],
			$utente["data_fine_coll"],false);

		if(count($tabella))
		{
			$somma_ore=0;
			$somma_per=0;
			$somma_str=0;
			$str25=0;
			$str30=0;
			$str50=0;
			$str55=0;
			$somma_via=0;
			$straordinari=array();
			$settimana=0;
			$giorni_lavorativi=0;
			$giorni_lavorati=0;
			$giorni_malattia=0;
			$giorni_ferie=0;


			$i=0;
			$data=array();
			foreach($tabella as $giorno=>$row)
			{
				if(my_date_format($giorno,"w")==6)
					$settimana++;
				$j=0;

				$ore_str=hour_to_int(@$row["Ore<br>Str"]);
				$ore_str=(int)($ore_str/15)*15;

				if(!$row["__hidden__festivo"])
				{
					$giorni_lavorativi++;
					if($ore_str>0)
						$straordinari[$settimana]+=$ore_str;
				}
				else
				{
					if($ore_str>0)
					{
						if($ore_str<=480)
							$str50+=$ore_str;
						else
						{
							$str50+=480;
							$str55+=($ore_str-480);
						}
					}
				}

				if(hour_to_int($row["Ore<br>Giorn"])>0)
				{
					$giorni_lavorati++;
					$somma_ore+=((int)(hour_to_int($row["Ore<br>Giorn"])/15)*15);
				}

				if(strstr(@$row["Malattia"],"<img "))
					$giorni_malattia++;
				elseif(strstr(@$row["Ferie"],"<img "))
					$giorni_ferie++;


				if(hour_to_int(@$row["Ore<br>Viaggio"])>0)
				{
					$ore_via=(int)(hour_to_int($row["Ore<br>Viaggio"])/15)*15;
					$somma_via+=$ore_via;
				}
				if(hour_to_int(@$row["Ore<br>Perm"])>0)
				{
					$ore_per=(int)(hour_to_int($row["Ore<br>Perm"])/15)*15;
					$somma_per+=$ore_per;
				}

				if((strlen(@$row["__hidden__trasferta"])>2)&&($row["__hidden__trasferta"]!="----"))
				{
					$cognome=$utente["cognome"];
					if(count($contaCognomi[$utente["cognome"]])>1)
						$cognome=uniqueUser($contaCognomi,$utente["cognome"],$utente["nome"]);

					$user_has_tr[$utente["ragione_sociale"]][$cognome]=1;
					$i=strpos($row["__hidden__trasferta"],":");
					if($i===false)
					{
						$explode=explode(" ",$row["__hidden__trasferta"]);
						$tr_loc=substr($explode[0],0,3).".".substr($explode[1],0,1);
					}
					else
					{
						if(strstr($row["__hidden__trasferta"],"ITALIA"))
							$tr_loc="ITALIA";
						else
							$tr_loc=trim(substr($row["__hidden__trasferta"],0,3));
					}
					$tr_euri=$row["__hidden__tr_euri"];
					$tr_tipo=$row["__hidden__tr_cod2"];
					if(!isset($trasf[$utente["ragione_sociale"]][$tr_tipo][$tr_loc][$tr_euri][$cognome]))
						$trasf[$utente["ragione_sociale"]][$tr_tipo][$tr_loc][$tr_euri][$cognome]=1;
					else
						$trasf[$utente["ragione_sociale"]][$tr_tipo][$tr_loc][$tr_euri][$cognome]++;
				}
			}
			foreach($straordinari as $linea)
			{
				if($linea+$str_mese_prec<=120)
					$str25+=$linea;
				else
				{
					$str25+=120-$str_mese_prec;
					$str30+=($linea-120+$str_mese_prec);
				}
				$str_mese_prec=0;
			}

			$ore_string=($somma_ore>0?sprintf("%.2f",($somma_ore/60)):"0");
			$str25_string=($str25>0?sprintf("%.2f",($str25/60)):"0");
			$str30_string=($str30>0?sprintf("%.2f",($str30/60)):"0");
			$str50_string=($str50>0?sprintf("%.2f",($str50/60)):"0");
			$str55_string=($str55>0?sprintf("%.2f",($str55/60)):"0");
			$per_string=($somma_per>0?sprintf("%.2f",($somma_per/60)):"0");
			$via_string=($somma_via>0?sprintf("%.2f",($somma_via/60)):"0");
			$cognome=$utente["cognome"];
			if(count($contaCognomi[$utente["cognome"]])>1)
				$cognome=uniqueUser($contaCognomi,$utente["cognome"],$utente["nome"]);

			if($format=="csv")
				$output.=$cognome."\t$giorni_lavorati\t$ore_string\t".
					"$str25_string\t$str30_string\t$str50_string\t$str55_string\t".
					"$via_string\t$giorni_ferie\t$per_string\t$giorni_malattia\n";
			elseif($format=="pdf")
			{
				$pdf->Cell(30,6,$cognome,1,0,'L',0);
				$pdf->Cell(16,6,$giorni_lavorati,1,0,'C',0);
				$pdf->Cell(16,6,$ore_string,1,0,'C',0);
				$pdf->Cell(16,6,$str25_string,1,0,'C',0);
				$pdf->Cell(16,6,$str30_string,1,0,'C',0);
				$pdf->Cell(16,6,$str50_string,1,0,'C',0);
				$pdf->Cell(16,6,$str55_string,1,0,'C',0);
				$pdf->Cell(16,6,$via_string,1,0,'C',0);
				$pdf->Cell(16,6,$giorni_ferie,1,0,'C',0);
				$pdf->Cell(16,6,$per_string,1,0,'C',0);
				$pdf->Cell(16,6,$giorni_malattia,1,1,'C',0);
			}
			elseif($format=="xls")
			{
				$output.="<Row>";
				$output.="<Cell><Data ss:Type='String'>".$cognome."</Data></Cell>";
				$output.="<Cell><Data ss:Type='String'>".$giorni_lavorati."</Data></Cell>";
				$output.="<Cell><Data ss:Type='String'>".$ore_string."</Data></Cell>";
				$output.="<Cell><Data ss:Type='String'>".$str25_string."</Data></Cell>";
				$output.="<Cell><Data ss:Type='String'>".$str30_string."</Data></Cell>";
				$output.="<Cell><Data ss:Type='String'>".$str50_string."</Data></Cell>";
				$output.="<Cell><Data ss:Type='String'>".$str55_string."</Data></Cell>";
				$output.="<Cell><Data ss:Type='String'>".$via_string."</Data></Cell>";
				$output.="<Cell><Data ss:Type='String'>".$giorni_ferie."</Data></Cell>";
				$output.="<Cell><Data ss:Type='String'>".$per_string."</Data></Cell>";
				$output.="<Cell><Data ss:Type='String'>".$giorni_malattia."</Data></Cell>";
				$output.="</Row>\n";
			}
		}
		if($format=="csv")
			$output."\n";
	}
	((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
	((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);

	if($format=="xls")
		$output.= "</Table></Worksheet>\n";

	$ditta="";
	foreach($user_has_tr as $ragione_sociale=>$utenti)
	{
		if($format=="csv")
			$output.="\n$ragione_sociale\nTipo\tLocalit".chr(225)."\tEuro";
		elseif($format=="pdf")
		{
			$smallCellsWidth=13;
			$topmargin=7;
			$leftmargin=7;
			$pagewidth=297-2*$leftmargin;

			$cellwidth=($pagewidth-(3*$smallCellsWidth))/count($utenti);
			if($cellwidth>30)
			{
				$cellwidth=30;
				$pagewidth=3*$smallCellsWidth+$cellwidth*count($utenti);
				$leftmargin=(297-$pagewidth)/2;
			}

			$pdf->SetMargins($leftmargin,$topmargin);
			$pdf->AddPage('L');



			$pdf->SetFont('Arial','B',14);
			$pdf->Cell($pagewidth,16,$ragione_sociale,0,1,'C',0);


			$pdf->SetFont('Arial','B',9);
			$pdf->Cell($smallCellsWidth,$cellh,"Tipo",1,0,'C',0);
			$pdf->Cell($smallCellsWidth,$cellh,"Localit".chr(225),1,0,'C',0);
			$pdf->Cell($smallCellsWidth,$cellh,"Euro",1,0,'C',0);
		}
		elseif($format=="xls")
		{
			$output.="<Worksheet ss:Name='Trasferte ".$ragione_sociale."'>";
			$output.="<Table>";
			$output.="<Row>";

			$output.="<Cell><Data ss:Type='String'>Tipo</Data></Cell>";
			$output.="<Cell><Data ss:Type='String'>Localit&amp;</Data></Cell>";
			$output.="<Cell><Data ss:Type='String'>Euro</Data></Cell>";
		}
		foreach($utenti as $cognome=>$foo)
			if($format=="csv")
				$output.="\t$cognome";
			elseif($format=="pdf")
				$pdf->Cell($cellwidth,$cellh,$cognome,1,0,'C',0);
			elseif($format=="xls")
				$output.="<Cell><Data ss:Type='String'>$cognome</Data></Cell>";

		if($format=="csv")
			$output.="\n";
		elseif($format=="pdf")
		{
			$pdf->Ln();
			$pdf->SetFont('Arial','',9);
		}
		elseif($format=="xls")
			$output.="</Row>\n";

		$trasferte=$trasf[$ragione_sociale];

		$loc_prec="";
		$euri_prec="";

		$tipo_prec="";
		foreach($trasferte as $tipo=>$array)
		{
			foreach($array as $loc=>$array2)
			{

				foreach($array2 as $pecunia=>$array3)
				{
					if($format=="csv")
						$output.="$tipo\t$loc\t".$pecunia;
					elseif($format=="pdf")
					{
						$pdf->Cell($smallCellsWidth,$cellh,$tipo,1,0,'C',0);
						$pdf->Cell($smallCellsWidth,$cellh,$loc,1,0,'C',0);
						$pdf->Cell($smallCellsWidth,$cellh,$pecunia,1,0,'C',0);
					}
					elseif($format=="xls")
					{
						$output.="<Row>";
						$output.="<Cell><Data ss:Type='String'>$tipo</Data></Cell>";
						$output.="<Cell><Data ss:Type='String'>$loc</Data></Cell>";
						$output.="<Cell><Data ss:Type='String'>$pecunia</Data></Cell>";
					}


					foreach($utenti as $cognome=>$foo)
						if(isset($array3[$cognome]))
						{
							if($format=="csv")
								$output.="\t".$array3[$cognome];
							elseif($format=="pdf")
								$pdf->Cell($cellwidth,$cellh,$array3[$cognome],1,0,'C',0);
							elseif($format=="xls")
								$output.="<Cell><Data ss:Type='String'>".$array3[$cognome]."</Data></Cell>";
						}
						else
						{
							if($format=="csv")
								$output.="\t";
							elseif($format=="pdf")
								$pdf->Cell($cellwidth,$cellh,"---",1,0,'C',0);
							elseif($format=="xls")
								$output.="<Cell><Data ss:Type='String'></Data></Cell>";
						}
					if($format=="csv")
						$output.="\n";
					elseif($format=="pdf")
						$pdf->Ln();
					elseif($format=="xls")
						$output.="</Row>\n";
				}
			}
		}
		if($format=="xls")
			$output.= "</Table></Worksheet>\n";
	}
	if($format=="pdf")
		$pdf->Output();
	else
	{
		if($format=="xls")
			$output.= "</Workbook>";
		if($mese>0)
			$filename="riepilogo_".$mesi[$mese-1]."_".$anno.".xls";
		else
			$filename="riepilogo_".$anno.".xls";
		header("Content-type: application/octet-stream");
		header("Content-Disposition: attachment; filename=$filename;");
		header("Content-Type: application/ms-excel");
		header("Pragma: no-cache");
		header("Expires: 0");
		echo $output;
	}
}


function pdfstring($string)
{
	global $accenti;

	foreach($accenti as $k=>$v)
		$string=str_replace($k,$v,$string);

	$out="";
	for($i=0;$i<strlen($string);$i++)
	{
		$c=substr($string,$i,1);
		if(ord($c)>128)
		{
			$i++;
			if($i<strlen($string))
			{
				$c=substr($string,$i,1);
				if(ord($c)==176)
					$out.=$c;
				else
					$out.=chr(ord($c)+64);
			}
		}
		else
			$out.=$c;
	}
	return $out;
}

function uniqueUser($doppioni,$cognome,$nome)
{
	$max=10;
	$i=0;
	$out=false;
	$count=count($doppioni[$cognome]);

	do
	{
		$i++;
		$temparray=array();
		foreach($doppioni[$cognome] as $n=>$foo)
			$temparray[substr($n,0,$i)]=1;
		$out=(($i==$max)||(count($temparray)==$count));
	}
	while($out==false);
	return($cognome." ".substr($nome,0,$i).".");
}

function calc_str_mese_prec($conn,$qdal,$qal,$id_utente,$data_inizio_coll,$data_fine_coll)
{
	$weekDay=(date("w",strtotime($qdal))+6)%7;
	$ldal=date("Y-m-d",strtotime("$qdal -$weekDay days"));
	$lal=date("Y-m-d",strtotime("$qdal -1 days"));
	$tabella=tabella_presenze($conn,$id_utente,$ldal,$lal,$data_inizio_coll,$data_fine_coll,false);

	$str_mese_prec=0;
	if(count($tabella))
	{
		foreach($tabella as $giorno=>$row)
		{
			$ore_str=hour_to_int(@$row["Ore<br>Str"]);
			$ore_str=(int)($ore_str/15)*15;
			if((!$row["__hidden__festivo"])&&($ore_str>0))
				$str_mese_prec+=$ore_str;
		}
		if($str_mese_prec>120)
			$str_mese_prec=120;
		unset($tabella);
	}
	return($str_mese_prec);
}

function stampa_per_ore_mese()
{
	global $dbname,$myhost,$myuser,$mypass;
	$conn=mysqli_connect($myhost, $myuser, $mypass)
		or die("Connessione non riuscita".mysqli_error($conn));
	((bool)mysqli_query($conn, "USE " . $dbname));

	$mese=$_POST["mese"];
	$anno=$_POST["anno"];
	$utenti=(isset($_POST["ore_utente"])?$_POST["ore_utente"]:array());
	$utenti_list="";
	foreach($utenti as $id)
		$utenti_list.="$id,";
	$utenti_list=rtrim($utenti_list,",");
	$query="UPDATE utenti SET stampa_ore=0 WHERE id NOT IN ($utenti_list)";
	mysqli_query($conn, $query)
		or die("$query<br>".mysqli_error($conn));
	$query="UPDATE utenti SET stampa_ore=1 WHERE id IN ($utenti_list)";
	mysqli_query($conn, $query)
		or die("$query<br>".mysqli_error($conn));

	$datainizio=date("Y-m-d",mktime(0,0,0,$mese,1,$anno));
	$datafine=date("Y-m-d",mktime(0,0,0,$mese+1,0,$anno));

	require_once("fpdf/fpdf.php");
	if(count($utenti))
	{
		$pdf=new FPDF("P","mm","A4");
		foreach($utenti as $id)
		{
			$pdf->AddPage();
			$query="SELECT nome,cognome,data_inizio_coll,data_fine_coll,ragione_sociale
				FROM utenti LEFT JOIN ditte ON utenti.ditta=ditte.id
				WHERE utenti.id='$id'";
			$result=mysqli_query($conn, $query);
			$row=mysqli_fetch_assoc($result);
			((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
			$tabella=tabella_presenze($conn,$id,$datainizio,$datafine,$row["data_inizio_coll"],$row["data_fine_coll"],false);
			report_mese_inside($pdf,$tabella,$row["nome"],$row["cognome"],$mese,$anno,$row["ragione_sociale"]);
		}
		$pdf->Output();
	}
	((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);
}
?>
