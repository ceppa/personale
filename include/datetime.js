
function int2ora(minuti)
{
	var h,m;

	if(minuti==-1)
		return("----");
	m=minuti % 60;
	h=(minuti-m)/60;
	if(m<10)
		m="0"+m;
	return h + ":" + m;
}

function ora2int(ora)
{
	var s,out,orain;

	if(!is_hour(ora))
		return -1;
	orain=String(ora);
	s=orain.split(":");
	if(s.length!=2)
		out=-1;
	else
	{
		s[1]-=(s[1] % 5)
		out=Number(s[0])*60 + Number(s[1]);
	}
	return out;
}

function is_date(valuein)
{
	var s,out,datain;
	datain=String(valuein);
	s=datain.split("/");
	if(s.length!=3)
		out=false;
	else
	{
		d=Number(s[0]);
		m=Number(s[1])-1;
		y=Number(s[2]);

		var dateVar=new Date(y,m,d);
		out=((dateVar.getYear()>0)
			&&(dateVar.getMonth()==m)
			&&(dateVar.getDate()==d));
	}
	return out;
}

function is_hour(valuein)
{
	var orain,s,out;
	orain=String(valuein);
	s=orain.split(":");
	if(s.length!=2)
		out=false;
	else
	{
		if(isNaN(s[0])||isNaN(s[1]))
			return false;
		h=Number(s[0]);
		m=Number(s[1]);

		out=((h<24)
			&&(m<60));
	}
	return out;
}



function formattaora(ora)
{
	if(Number(ora2int(ora.value))!=-1)
		return(int2ora(Number(ora2int(ora.value))));
	else
		return ora.value;
}

function formattavaluta(valuta)
{
	var newvalue=valuta.value.replace(",",".");
	var splitted=newvalue.split(".");
	if(splitted.length>2)
		return valuta.value;
	for(var i=0;i<splitted.length;i++)
		if(!is_number(splitted[i]))
			return valuta.value;
	if(splitted[1]!=null)
	{
		splitted[1]=splitted[1].substr(0,2);
		if(splitted[1].length==1)
			splitted[1]=splitted[1]+"0";
	}
	else
		splitted[1]="00";
	return splitted[0]+"."+splitted[1];
}


function formattadata(data)
{
	var array=trim(data).split("/");
	if(array.length!=3)
		return data;
	for(var i=0;i<3;i++)
		if((!is_number(array[i]))
				||(array[i].indexOf('.')!=-1)
				||((i==2)&&(array[i].length>4))
				||((i<2)&&(array[i].length>2)))
			return(data);
	if(array[0].length<2)
		array[0]="0"+array[0];
	if(array[1].length<2)
		array[1]="0"+array[1];
	if(array[2].length<4)
		array[2]=String(Number(array[2])+2000);
	return array[0]+"/"+array[1]+"/"+array[2];
}

function datetime_diff(data1,data2)
{
	var dh1=data1.split(" ");
	var dh2=data2.split(" ");
	var array1=dh1[0].split("/");
	var array2=dh2[0].split("/");

	while(array1[2].length<3)
		array1[2]="0"+array1[2];
	if(array1[2].length==3)
		array1[2]="2"+array1[2];
	while(array2[2].length<3)
		array2[2]="0"+array2[2];
	if(array2[2].length==3)
		array2[2]="2"+array2[2];
	if(dh1[1]!=null)
		var array3=dh1[1].split(":");
	else
		var array3=new Array(0,0);

	if(dh2[1]!=null)
		var array4=dh2[1].split(":");
	else
		var array4=new Array(0,0);

	var date1Var=new Date(array1[2],array1[1]-1,array1[0]);
	var date2Var=new Date(array2[2],array2[1]-1,array2[0]);


	date1Var.setHours(Number(array3[0]));
	date1Var.setMinutes(array3[1]);
	date2Var.setHours(array4[0]);
	date2Var.setMinutes(array4[1]);

	ts1=date1Var.getTime();
	ts2=date2Var.getTime();

	return (ts2-ts1)/1000;
}


function is_number(text)
{
	var ValidChars = "0123456789.";
	var IsNumber=true;
	var Char;

	for (i = 0; i < text.length && IsNumber == true; i++)
	{
		Char = text.charAt(i);
		if (ValidChars.indexOf(Char) == -1)
			IsNumber = false;
		if(Char=='.')
			ValidChars=ValidChars.substr(0,ValidChars.length-1);
	}
	return IsNumber;
}

function trim(stringa)
{
	while (stringa.substring(0,1) == ' ')
		stringa = stringa.substring(1, stringa.length);
	while (stringa.substring(stringa.length-1, stringa.length) == ' ')
		stringa = stringa.substring(0,stringa.length-1);
    return stringa;
}
