<?

function safeString($stringa)
{
	return(trim(str_replace('"','&quot;',$stringa)));
}

function int_to_hour($int)
{
	if($int==-1)
		return "----";
	$minuti=$int%60;
	$ore=(int)($int/60);
	return sprintf("%d:%02d",$ore,$minuti);
}

function hour_to_int($hour)
{
	if($hour=="")
		return 0;
	if($hour=="----")
		return -1;
	$explode=explode(":",$hour);
	return ((int)$explode[0])*60+(int)$explode[1];
}


function my_date_format($date,$format)
{
	if($date=="----")
		return $date;
	if($date=="0000-00-00")
		return "----";
	$ts=strtotime($date);
	return date ($format,$ts);
}

function date_to_sql($date)
{
	if($date=="----")
		return "0000-00-00";
	else
	{
		$explode=explode("/",$date);
		$dd=(int)$explode[0];
		$mm=(int)$explode[1];
		$yy=(int)$explode[2];
		return(date("Y-m-d",mktime(0,0,0,$mm,$dd,$yy)));
	}
}

function format_minute($minute)
{
	return ($minute==-1)?0:$minute;
}

function is_hour($hour)
{
	if((!strlen($hour))||($hour=="----"))
		return true;
	if(!strstr($hour,":"))
		return false;
	$explode=explode(":",$hour);
	if(count($explode)!=2)
		return false;
	return (($explode[0]<24)&&($explode[1]<60));
}
function is_date($date)
{
	if($date=="----")
		return true;
	if(!strstr($date,"/"))
		return false;
	$explode=explode("/",$date);

	if(count($explode)!=3)
		return false;
	$dd=$explode[0];
	$mm=$explode[1];
	$yy=$explode[2];
	if(strlen($dd)==1)
		$dd="0".$dd;
	if(strlen($mm)==1)
		$mm="0".$mm;
	if(strlen($yy)==2)
		$yy="20".$yy;
	$date=$dd."/".$mm."/".$yy;
	return(date("d/m/Y",mktime(0,0,0,$mm,$dd,$yy))==$date);
}

function to_number($number)
{
	if(is_numeric($number))
		return($number);
	$out="";
	if(strstr($number,"."))
	{
		$out_array=explode(".",$number);
		if(count($out_array)>2)
			return("");
		foreach($out_array as $digit)
		{
			if(!is_numeric($digit))
				return("");
			if(strlen($out))
				$out=$out.".";
			$out=$out.$digit;
		}
	}
	elseif(strstr($number,","))
	{
		$out_array=explode(",",$number);
		if(count($out_array)>2)
			return("");
		foreach($out_array as $digit)
		{
			if(!is_numeric($digit))
				return("");
			if(strlen($out))
				$out=$out.".";
			$out=$out.$digit;
		}
	}
	elseif(is_int($number))
		$out=$number.".00";
	return $out;
}

function dateita_to_dateeng($data)
{
	$array1=explode("/",$data);
	return sprintf("%04d/%02d/%02d",$array1[2],$array1[1],$array1[0]);
}

function my_date_diff($data1,$data2)
{
	$array1=explode("-",$data1);
	if(count($array1)!=3)
		return(0);
	$array2=explode("-",$data2);
	if(count($array2)!=3)
		return(0);
	$ts1=mktime(0,0,0,(int)$array1[1],(int)$array1[2],(int)$array1[0]);
	$ts2=mktime(0,0,0,(int)$array2[1],(int)$array2[2],(int)$array2[0]);
	if($ts1>$ts2)
		return false;
	$giorni=0;
	for($i=$ts1;$i<=$ts2;$i=(mktime(0,0,0,date('n',$i),date('d',$i)+1,date('Y',$i))))
		$giorni++;
	return $giorni;
}

function datetime_diff($data1,$data2)
{
	$array1=explode("-",substr($data1,0,10));
	$array2=explode("-",substr($data2,0,10));
	if(strlen(substr($data1,11,8)))
		$array3=explode(":",substr($data1,11,8));
	else
		$array3=array(0,0,0);

	if(strlen(substr($data2,11,8)))
		$array4=explode(":",substr($data2,11,8));
	else
		$array4=array(0,0,0);

	$ts1=mktime((int)$array3[0],(int)$array3[1],(int)$array3[2],
		(int)$array1[1],(int)$array1[2],(int)$array1[0]);
	$ts2=mktime((int)$array4[0],(int)$array4[1],(int)$array4[2],
		(int)$array2[1],(int)$array2[2],(int)$array2[0]);
	return $ts2-$ts1;
}

?>
