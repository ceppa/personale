function trim(stringa)
{
    while (stringa.substring(0,1) == ' ')
        stringa = stringa.substring(1, stringa.length);
    while (stringa.substring(stringa.length-1, stringa.length) == ' ')
        stringa = stringa.substring(0,stringa.length-1);
    return stringa;
}

function formatFloat(number)
{
	var out="";
	var stringa=String(number);

	if(stringa.length==1)
		return "0,0"+stringa;
	else if(number.length==2)
		return "0,"+stringa;
	else
	{
		out=stringa.substring((stringa.length-5)>=0 ? stringa.length-5 : 0,stringa.length-2)+","+stringa.substring(stringa.length-2,stringa.length);

		for(var i=0;i<stringa.length -5;i+=3)
			out=stringa.substring(((stringa.length-8-i)>=0 ? stringa.length-8-i : 0),(stringa.length-5-i))+"."+out;
		return out;
	}
}

function onlyNumbers(e)
{
	var keynum;
	var keychar;
	var numcheck;

	if(window.event) // IE
	{
		keynum = e.keyCode;
	}
	else if(e.which) // Netscape/Firefox/Opera
	{
		keynum = e.which;
	}

	if((keynum==8)
			||(keynum==9)
			||(keynum==13)
			||(keynum==116)
			||(keynum==37)
			||(keynum==39)
			||(keynum==46)
			||(keynum==44))
		return true;

	keychar = String.fromCharCode(keynum);
	numcheck = /\d/;
	return numcheck.test(keychar);
}

function onlyTime(e,sender)
{
	var keynum;
	var keychar;
	var numcheck;

	if(window.event) // IE
	{
		keynum = e.keyCode;
	}
	else if(e.which) // Netscape/Firefox/Opera
	{
		keynum = e.which;
	}

	if((keynum==8)
			||(keynum==9)
			||(keynum==13)
			||(keynum==17)
			||(keynum==37)
			||(keynum==39)
			||(keynum==46)
  			||(keynum==116))
  		return true;
	if((keynum==59)||(keynum==190))
	{
		if(sender.value.length
				&&(sender.value.indexOf(":")==-1)
				&&(sender.value.indexOf(".")==-1))
			sender.value=sender.value+":";
		return false;
  	}
  	else
		keychar = String.fromCharCode(keynum);
/*	if(((sender.value.length>1)
			&&(sender.value.indexOf(":")==-1))
		||((sender.value.indexOf(":")!=-1)
			&&(sender.value.length-sender.value.indexOf(":")>2)))
		return false;*/
	numcheck = /\d/;
	return numcheck.test(keychar);
}

function onlyNumbersFloat(e,sender)
{
	var keynum;
	var keychar;
	var numcheck;

	if(window.event) // IE
	{
		keynum = e.keyCode;
	}
	else if(e.which) // Netscape/Firefox/Opera
	{
		keynum = e.which;
	}
	if((keynum==8)
			||(keynum==9)
			||(keynum==13)
			||(keynum==17)
			||(keynum==37)
			||(keynum==39)
			||(keynum==46)
  			||(keynum==116))
  		return true;
	if((keynum==188)||(keynum==190))
	{
		if(sender.value.length
				&&(sender.value.indexOf(",")==-1)
				&&(sender.value.indexOf(".")==-1))
			sender.value=sender.value+",";
		return false;
  	}
  	else
		keychar = String.fromCharCode(keynum);
	numcheck = /\d/;
	return numcheck.test(keychar);
}


function show_hide_column(id_of_table,col_no, do_show)
{
	var stl;
	if (do_show)
		stl = ''
	else
		stl = 'none';

	var tbl  = document.getElementById(id_of_table);
	var rows = tbl.getElementsByTagName('tr');

	for (var row=0; row<rows.length;row++)
	{
		var cels = rows[row].getElementsByTagName('td');
		cels[col_no].style.display=stl;
	}
}
