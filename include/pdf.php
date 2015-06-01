<?
	function formatFloat($number)
	{
		$out="";

		$len=strlen($number);

		if($len==1)
			return "0,0".$number;
		elseif($len==2)
			return "0,".$number;
		else
		{
			$i=(strlen($number)>=5?5:strlen($number));
			$l=$i-2;
			$out=substr(substr($number,-$i),0,$l).",".substr($number,-2);


			for($i=$len-5;$i>0;$i-=3)
			{
				$j=($i>3?3:$i);
				$out=substr(substr($number,0,$i),-$j).".".$out;
			}
			return $out;
		}
	}

	function num2text($num)
	{
		$unita=array("zero","uno","due","tre","quattro","cinque","sei","sette","otto","nove");
		$dieci=array("dieci","undici","dodici","tredici",
						"quattordici","quindici","sedici",
						"diciassette","diciotto","diciannove");
		$decine=array(2=>"venti",3=>"trenta",4=>"quaranta",5=>"cinquanta",
					6=>"sessanta",7=>"settanta",8=>"ottanta",9=>"novanta");

		$out="";

		$terzetti=(int)((strlen($num)-1)/3)+1;
		for($i=0;$i<$terzetti;$i++)
		{
			$token="";
			$n=1+((strlen($num)-1) % 3);
			$tz=substr($num,0,$n);
			$num=substr($num,$n);
			if(strlen($tz)==3)
			{
				if(substr($tz,0,1)!=1)
					$token.=$unita[substr($tz,0,1)];
				$token.="cento";
			}
			if(strlen($tz)>=2)
			{
				if(substr($tz,-2,1)==1)
					$token.=$dieci[substr($tz,-1,1)];
				elseif(substr($tz,-2,1)>1)
					$token.=$decine[substr($tz,-2,1)];
			}

			if((strlen($tz)>=1)&&(substr($tz,-2,1)!=1)&&(substr($tz,-1)!=0))
				$token.=$unita[substr($tz,-1,1)];

			$token=str_replace("iu","u",$token);
			$token=str_replace("au","u",$token);
			$token=str_replace("io","o",$token);
			$token=str_replace("ao","o",$token);


			$suffisso="";
			if($terzetti-$i>1)
			{
				switch(($terzetti-2-$i)%3)
				{
					case 0:
						if($token=="uno")
							$token="mille";
						else
							$suffisso="mila";
						break;
					case 1:
						if($token=="uno")
							$token="unmilione";
						else
							$suffisso="milioni";
						break;
					case 2;
						if($token=="uno")
							$token="unmiliardo";
						else
							$suffisso="miliardi";
						break;
				}
			}
			$out.=($token.$suffisso);

		}
		return($out);
	}

	function pdfstring($string)
	{
		$out="";
		for($i=0;$i<strlen($string);$i++)
		{
			$c=substr($string,$i,1);
			if(ord($c)>128)
			{
				$i++;
				if($i<strlen($string))
					$out.=chr(ord(substr($string,$i,1))+64);
			}
			else
				$out.=$c;
		}
		return $out;
	}

	function textLine($pdf,$text,$x,$y,$xspacing,$length=0,$alignment="L")
	{
		$curx=$x;
		$strlen=strlen($text);

		for($i=0;$i<$strlen;$i++)
		{
			if($alignment=="R")
				$curx=$x+($length-$strlen+$i)*$xspacing;
			else
				$curx=$x+$i*$xspacing;
			$pdf->SetXY($curx,$y);
			$pdf->Cell(4,5,substr($text,$i,1),0,0,'C',0);
		}
	}

	function textMultiLine($pdf,$text,$x,$y,$length,$xspacing,$yspacing)
	{
		$line=0;
		$count=0;
		$curx=$x;
		$cury=$y;

		$words=explode(" ",$text);

		foreach($words as $word)
		{
			if($count+strlen($word)>$length)
			{
				$count=strlen($word)+1;
				$curx=$x;
				$line++;
				$cury=$y+$line*$yspacing;
			}
			else
				$count+=strlen($word)+1;

			textLine($pdf,$word,$curx,$cury,$xspacing);
			$curx+=(strlen($word)+1)*$xspacing;
		}
	}
?>
