var language = 'it';
var enablePast = 1;
var enableFuture = 0;
var ie = document.all;
var fixedX = (ie ? 0 : -1);
var fixedY = (ie ? 0 : -1);
var startAt = 1;
var showWeekNumber = 1;
var showToday = 1;
var imgDir = 'include/img/';
var dayName = '';
var gotoString = {
	it: 'Vai al mese corrente'
};
var todayString = {
	it: 'Oggi e\''
};
var weekString = {
	it: 'Set'
};
var scrollLeftMessage = {
	it: 'Clicca per il mese precedente, tieni premuto per scrollare.'
};
var scrollRightMessage = {
	it: 'Clicca per il mese successivo, tieni premuto per scrollare.'
};
var selectMonthMessage = {
	it: 'Clicca per selezionare il mese'
};
var selectYearMessage = {
	it: "Clicca per selezionare l anno"
};
var selectDateMessage = {
	it: 'Seleziona [date] come data.'
};
var monthName = {
	it: new Array('Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre')
};
var monthName2 = {
	it: new Array('GEN', 'FEB', 'MAR', 'APR', 'MAG', 'GIU', 'LUG', 'AGO', 'SET', 'OTT', 'NOV', 'DIC')
};
if (startAt == 0) {
	dayName = {
		it: new Array('Do', 'Lu', 'Ma', 'Me', 'Gi', 'Ve', 'Sa')
	}
} else {
	dayName = {
		it: new Array('Lu', 'Ma', 'Me', 'Gi', 'Ve', 'Sa', 'Do')
	}
}
var crossobj, crossMonthObj, crossYearObj, monthSelected, yearSelected, dateSelected, omonthSelected, oyearSelected, odateSelected, monthConstructed, yearConstructed, intervalID1, intervalID2, timeoutID1, timeoutID2, ctlToPlaceValue, ctlNow, dateFormat, nStartingYear, selDayAction, isPast;
var visYear = 0;
var visMonth = 0;
var bPageLoaded = false;
var dom = document.getElementById;
var ns4 = document.layers;
var today = new Date();
var dateNow = today.getDate();
var monthNow = today.getMonth();
var yearNow = today.getYear();
var dateLimit = dateNow;
var monthLimit = monthNow;
var yearLimit = yearNow;
var imgsrc = new Array('drop1.gif', 'drop2.gif', 'left1.gif', 'left2.gif', 'right1.gif', 'right2.gif');
var img = new Array();
var bShow = false;

function hideElement(a, b) {
	if (ie) {
		for (i = 0; i < document.all.tags(a).length; i++) {
			var c = document.all.tags(a)[i];
			if (!c || !c.offsetParent) continue;
			objLeft = c.offsetLeft;
			objTop = c.offsetTop;
			objParent = c.offsetParent;
			while (objParent.tagName.toUpperCase() != 'BODY') {
				objLeft += objParent.offsetLeft;
				objTop += objParent.offsetTop;
				objParent = objParent.offsetParent
			}
			objHeight = c.offsetHeight;
			objWidth = c.offsetWidth;
			if ((b.offsetLeft + b.offsetWidth) <= objLeft);
			else if ((b.offsetTop + b.offsetHeight) <= objTop);
			else if (b.offsetTop >= (objTop + objHeight + c.height));
			else if (b.offsetLeft >= (objLeft + objWidth));
			else c.style.visibility = 'hidden'
		}
	}
}

function showElement(a) {
	if (ie) {
		for (i = 0; i < document.all.tags(a).length; i++) {
			var b = document.all.tags(a)[i];
			if (!b || !b.offsetParent) continue;
			b.style.visibility = ''
		}
	}
}

function HolidayRec(d, m, y, a) {
	this.d = d;
	this.m = m;
	this.y = y;
	this.desc = a
}
var HolidaysCounter = 0;
var Holidays = new Array();

function addHoliday(d, m, y, a) {
	Holidays[HolidaysCounter++] = new HolidayRec(d, m, y, a)
}
if (dom) {
	for (i = 0; i < imgsrc.length; i++) {
		img[i] = new Image;
		img[i].src = imgDir + imgsrc[i]
	}
	document.write('<div onclick="bShow=true" id="calendar" style="z-index:+999;text-align:center;position:absolute;display:none;visibility:hidden;"><table width="' + ((showWeekNumber == 1) ? 250 : 220) + '" style="font-family:Arial;font-size:11px;border: 1px solid #A0A0A0;" bgcolor="#ffffff"><tr bgcolor="#000066"><td width="100%" style="padding:5px 2px;"><table style="border-width:0px;margin-right:0px;margin-left:0px;" width="100%"><tr><td style="white-space:nowrap;width:195px;border-style:none;padding:2px;font-family:Arial;font-size:11px;"><font color="#ffffff' + '' + '"><b><span id="caption"></span></b></font></td><td style="border-style:none;" align="right"><a href="javascript:hideCalendar()"><img src="' + imgDir + 'close.gif" width="15" height="13" border="0" /></a></td></tr></table></td></tr><tr><td style="padding:5px" bgcolor="#ffffff"><span id="content"></span></td></tr>');
	if (showToday == 1) document.write('<tr bgcolor="#f0f0f0"><td style="padding:5px" align="center"><span id="lblToday"></span></td></tr>');
	document.write('</table></div><div id="selectMonth" style="z-index:+999;position:absolute;visibility:hidden;"></div><div id="selectYear" style="z-index:+999;position:absolute;visibility:hidden;"></div>')
}
var styleAnchor = 'text-decoration:none;color:black;';
var styleLightBorder = 'border:1px solid #a0a0a0;';

function swapImage(a, b) {
	if (ie) document.getElementById(a).setAttribute('src', imgDir + b)
}

function init() {
	if (!ns4) {
		if (!ie) yearNow += 1900;
		crossobj = (dom) ? document.getElementById('calendar').style : ie ? document.all.calendar : document.calendar;
		hideCalendar();
		crossMonthObj = (dom) ? document.getElementById('selectMonth').style : ie ? document.all.selectMonth : document.selectMonth;
		crossYearObj = (dom) ? document.getElementById('selectYear').style : ie ? document.all.selectYear : document.selectYear;
		monthConstructed = false;
		yearConstructed = false;
		if (showToday == 1) document.getElementById('lblToday').innerHTML = '<font color="#000066">' + String(dateNow) + ' ' + String(monthNow) + ' ' + String(yearNow) + ' ' + todayString[language] + ' <a onmousemove="window.status=\'' + gotoString[language] + '\'" onmouseout="window.status=\'\'" title="' + gotoString[language] + '" style="' + styleAnchor + '" href="javascript:monthSelected=monthNow;yearSelected=yearNow;constructCalendar();">' + dayName[language][(today.getDay() - startAt == -1) ? 6 : (today.getDay() - startAt)] + ', ' + dateNow + ' ' + monthName[language][monthNow].substring(0, 3) + ' ' + yearNow + '</a></font>';
		sHTML1 = '<span id="spanLeft" style="display:inline;float:left;width:18px;height:14px;border:1px solid #36f;cursor:pointer" onmouseover="swapImage(\'changeLeft\',\'left2.gif\');this.style.borderColor=\'#8af\';window.status=\'' + scrollLeftMessage[language] + '\'" onclick="decMonth()" onmouseout="clearInterval(intervalID1);swapImage(\'changeLeft\',\'left1.gif\');this.style.borderColor=\'#36f\';window.status=\'\'" onmousedown="clearTimeout(timeoutID1);timeoutID1=setTimeout(\'StartDecMonth()\',500)" onmouseup="clearTimeout(timeoutID1);clearInterval(intervalID1)">&nbsp<img id="changeLeft" src="' + imgDir + 'left1.gif" width="10" height="11" border="0">&nbsp</span>';
		sHTML1 += '<span id="spanRight" style="display:inline;float:left;width:18px;height:14px;border:1px solid #36f;cursor:pointer" onmouseover="swapImage(\'changeRight\',\'right2.gif\');this.style.borderColor=\'#8af\';window.status=\'' + scrollRightMessage[language] + '\'" onmouseout="clearInterval(intervalID1);swapImage(\'changeRight\',\'right1.gif\');this.style.borderColor=\'#36f\';window.status=\'\'" onclick="incMonth()" onmousedown="clearTimeout(timeoutID1);timeoutID1=setTimeout(\'StartIncMonth()\',500)" onmouseup="clearTimeout(timeoutID1);clearInterval(intervalID1)">&nbsp<img id="changeRight" src="' + imgDir + 'right1.gif" width="10" height="11" border="0">&nbsp</span>';
		sHTML1 += '<span id="spanMonth" style="text-align:center;display:inline;float:left;width:95px;height:14px;border:1px solid #36f;cursor:pointer" onmouseover="swapImage(\'changeMonth\',\'drop2.gif\');this.style.borderColor=\'#8af\';window.status=\'' + selectMonthMessage[language] + '\'" onmouseout="swapImage(\'changeMonth\',\'drop1.gif\');this.style.borderColor=\'#36f\';window.status=\'\'" onclick="popUpMonth()"></span>';
		sHTML1 += '<span id="spanYear" style="text-align:center;display:inline;float:left;width:45px;height:14px;border:1px solid #36f;cursor:pointer" onmouseover="swapImage(\'changeYear\',\'drop2.gif\');this.style.borderColor=\'#8af\';window.status=\'' + selectYearMessage[language] + '\'" onmouseout="swapImage(\'changeYear\',\'drop1.gif\');this.style.borderColor=\'#36f\';window.status=\'\'" onclick="popUpYear()"></span>';
		document.getElementById('caption').innerHTML = sHTML1;
		bPageLoaded = true
	}
}

function hideCalendar() {
	crossobj.visibility = 'hidden';
	crossobj.display = 'none';
	if (crossMonthObj != null) crossMonthObj.visibility = 'hidden';
	if (crossYearObj != null) crossYearObj.visibility = 'hidden';
	showElement('SELECT');
	showElement('APPLET')
}

function padZero(a) {
	return (a < 10) ? '0' + a : a
}

function constructDate(d, m, y) {
	sTmp = dateFormat;
	sTmp = sTmp.replace('dd', '<e>');
	sTmp = sTmp.replace('d', '<d>');
	sTmp = sTmp.replace('<e>', padZero(d));
	sTmp = sTmp.replace('<d>', d);
	sTmp = sTmp.replace('mmmm', '<p>');
	sTmp = sTmp.replace('mmm', '<o>');
	sTmp = sTmp.replace('mm', '<n>');
	sTmp = sTmp.replace('m', '<m>');
	sTmp = sTmp.replace('<m>', m + 1);
	sTmp = sTmp.replace('<n>', padZero(m + 1));
	sTmp = sTmp.replace('<o>', monthName[language][m]);
	sTmp = sTmp.replace('<p>', monthName2[language][m]);
	sTmp = sTmp.replace('yyyy', y);
	return sTmp.replace('yy', padZero(y % 100))
}

function closeCalendar() {
	hideCalendar();
	ctlToPlaceValue.readOnly = false;
	ctlToPlaceValue.value = constructDate(dateSelected, monthSelected, yearSelected);
	ctlToPlaceValue.onchange();
	ctlToPlaceValue.readOnly = true
}

function StartDecMonth() {
	intervalID1 = setInterval("decMonth()", 80)
}

function StartIncMonth() {
	intervalID1 = setInterval("incMonth()", 80)
}

function incMonth() {
	monthSelected++;
	if (monthSelected > 11) {
		monthSelected = 0;
		yearSelected++
	}
	constructCalendar()
}

function decMonth() {
	monthSelected--;
	if (monthSelected < 0) {
		monthSelected = 11;
		yearSelected--
	}
	constructCalendar()
}

function constructMonth() {
	popDownYear();
	if (!monthConstructed) {
		sHTML = "";
		for (i = 0; i < 12; i++) {
			sName = monthName[language][i];
			if (i == monthSelected) sName = '<b>' + sName + '</b>';
			sHTML += '<tr><td id="m' + i + '" onmouseover="this.style.backgroundColor=\'#CCCCCC\'" onmouseout="this.style.backgroundColor=\'\'" style="cursor:pointer" onclick="monthConstructed=false;monthSelected=' + i + ';constructCalendar();popDownMonth();event.cancelBubble=true"><font color="#000066">&nbsp;' + sName + '&nbsp;</font></td></tr>'
		}
		document.getElementById('selectMonth').innerHTML = '<table style="width:100px;font-family:Arial;font-size:11px;border:1px solid #a0a0a0;" bgcolor="#f0f0f0" cellspacing="0" onmouseover="clearTimeout(timeoutID1)" onmouseout="clearTimeout(timeoutID1);timeoutID1=setTimeout(\'popDownMonth()\',100);event.cancelBubble=true">' + sHTML + '</table>';
		monthConstructed = true
	}
}

function popUpMonth() {
	if (visMonth == 1) {
		popDownMonth();
		visMonth--
	} else {
		constructMonth();
		crossMonthObj.visibility = (dom || ie) ? 'visible' : 'show';
		crossMonthObj.left = String(parseInt(crossobj.left) + 46) + 'px';
		crossMonthObj.top = String(parseInt(crossobj.top) + 25) + 'px';
		visMonth++
	}
}

function popDownMonth() {
	crossMonthObj.visibility = 'hidden';
	visMonth = 0
}

function incYear() {
	for (i = 0; i < 7; i++) {
		newYear = (i + nStartingYear) + 1;
		if (newYear == yearSelected) txtYear = '<span style="color:#006;font-weight:bold;">&nbsp;' + newYear + '&nbsp;</span>';
		else txtYear = '<span style="color:#006;">&nbsp;' + newYear + '&nbsp;</span>';
		document.getElementById('y' + i).innerHTML = txtYear
	}
	nStartingYear++;
	bShow = true
}

function decYear() {
	for (i = 0; i < 7; i++) {
		newYear = (i + nStartingYear) - 1;
		if (newYear == yearSelected) txtYear = '<span style="color:#006;font-weight:bold">&nbsp;' + newYear + '&nbsp;</span>';
		else txtYear = '<span style="color:#006;">&nbsp;' + newYear + '&nbsp;</span>';
		document.getElementById('y' + i).innerHTML = txtYear
	}
	nStartingYear--;
	bShow = true
}

function selectYear(a) {
	yearSelected = parseInt(a + nStartingYear);
	yearConstructed = false;
	constructCalendar();
	popDownYear()
}

function constructYear() {
	popDownMonth();
	sHTML = '';
	if (!yearConstructed) {
		sHTML = '<tr><td align="center" onmouseover="this.style.backgroundColor=\'#CCCCCC\'" onmouseout="clearInterval(intervalID1);this.style.backgroundColor=\'\'" style="cursor:pointer" onmousedown="clearInterval(intervalID1);intervalID1=setInterval(\'decYear()\',30)" onmouseup="clearInterval(intervalID1)"><font color="#000066">-</font></td></tr>';
		j = 0;
		nStartingYear = yearSelected - 3;
		for (i = (yearSelected - 3); i <= (yearSelected + 3); i++) {
			sName = i;
			if (i == yearSelected) sName = '<b>' + sName + '</b>';
			sHTML += '<tr><td id="y' + j + '" onmouseover="this.style.backgroundColor=\'#CCCCCC\'" onmouseout="this.style.backgroundColor=\'\'" style="cursor:pointer" onclick="selectYear(' + j + ');event.cancelBubble=true"><font color="#000066">&nbsp;' + sName + '&nbsp;</font></td></tr>';
			j++
		}
		sHTML += '<tr><td align="center" onmouseover="this.style.backgroundColor=\'#CCCCCC\'" onmouseout="clearInterval(intervalID2);this.style.backgroundColor=\'\'" style="cursor:pointer" onmousedown="clearInterval(intervalID2);intervalID2=setInterval(\'incYear()\',30)" onmouseup="clearInterval(intervalID2)"><font color="#000066">+</font></td></tr>';
		document.getElementById('selectYear').innerHTML = '<table cellspacing="0" bgcolor="#f0f0f0" style="width:50px;font-family:Arial;font-size:11px;border:1px solid #a0a0a0;" onmouseover="clearTimeout(timeoutID2)" onmouseout="clearTimeout(timeoutID2);timeoutID2=setTimeout(\'popDownYear()\',100)">' + sHTML + '</table>';
		yearConstructed = true
	}
}

function popDownYear() {
	clearInterval(intervalID1);
	clearTimeout(timeoutID1);
	clearInterval(intervalID2);
	clearTimeout(timeoutID2);
	crossYearObj.visibility = 'hidden';
	visYear = 0
}

function popUpYear() {
	var a;
	if (visYear == 1) {
		popDownYear();
		visYear--
	} else {
		constructYear();
		crossYearObj.visibility = (dom || ie) ? 'visible' : 'show';
		a = parseInt(crossobj.left) + document.getElementById('spanYear').offsetLeft;
		a += 4;
		crossYearObj.left = String(a) + 'px';
		crossYearObj.top = String(parseInt(crossobj.top) + 25) + 'px';
		visYear++
	}
}

function WeekNbr(n) {
	year = n.getFullYear();
	month = n.getMonth() + 1;
	if (startAt == 0) day = n.getDate() + 1;
	else day = n.getDate();
	a = Math.floor((14 - month) / 12);
	y = year + 4800 - a;
	m = month + 12 * a - 3;
	b = Math.floor(y / 4) - Math.floor(y / 100) + Math.floor(y / 400);
	J = day + Math.floor((153 * m + 2) / 5) + 365 * y + b - 32045;
	d4 = (((J + 31741 - (J % 7)) % 146097) % 36524) % 1461;
	L = Math.floor(d4 / 1460);
	d1 = ((d4 - L) % 365) + L;
	week = Math.floor(d1 / 7) + 1;
	return week
}

function constructCalendar() {
	var a = Array(31, 0, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	var b;
	var c = new Date(yearSelected, monthSelected, 1);
	var d;
	if (monthSelected == 1) {
		d = new Date(yearSelected, monthSelected + 1, 1);
		d = new Date(d - (24 * 60 * 60 * 1000));
		numDaysInMonth = d.getDate()
	} else numDaysInMonth = a[monthSelected];
	datePointer = 0;
	dayPointer = c.getDay() - startAt;
	if (dayPointer < 0) dayPointer = 6;
	sHTML = '<table border="0" style="font-family:verdana;font-size:10px;"><tr>';
	if (showWeekNumber == 1) sHTML += '<td width="27"><b>' + weekString[language] + '</b></td><td width="1" rowspan="7" bgcolor="#d0d0d0" style="padding:0px"><img src="' + imgDir + 'divider.gif" width="1"></td>';
	for (i = 0; i < 7; i++) sHTML += '<td width="27" align="right"><b><font color="#000066">' + dayName[language][i] + '</font></b></td>';
	sHTML += '</tr><tr>';
	if (showWeekNumber == 1) sHTML += '<td align="right">' + WeekNbr(c) + '&nbsp;</td>';
	for (var i = 1; i <= dayPointer; i++) sHTML += '<td>&nbsp;</td>';
	for (datePointer = 1; datePointer <= numDaysInMonth; datePointer++) {
		dayPointer++;
		sHTML += '<td align="right"';
		sStyle = styleAnchor;
		if ((datePointer == odateSelected) && (monthSelected == omonthSelected) && (yearSelected == oyearSelected)) {
			bgColor = '#FDD';
			hoverColor = '#FCC'
		} else {
			bgColor = '#FFF';
			hoverColor = '#FEE'
		}
		sHTML += ' style="background-color:' + bgColor + '"';
		sHint = '';
		for (k = 0; k < HolidaysCounter; k++) {
			if ((parseInt(Holidays[k].d) == datePointer) && (parseInt(Holidays[k].m) == (monthSelected + 1))) {
				if ((parseInt(Holidays[k].y) == 0) || ((parseInt(Holidays[k].y) == yearSelected) && (parseInt(Holidays[k].y) != 0))) {
					sStyle += 'background-color:#ccc;';
					sHint += sHint == "" ? Holidays[k].desc : "\n" + Holidays[k].desc
				}
			}
		}
		sHint = sHint.replace('/\"/g', '&quot;');
		if ((enablePast == 0 && ((yearSelected < yearLimit) || (monthSelected < monthLimit) && (yearSelected == yearLimit) || (datePointer < dateLimit) && (monthSelected == monthLimit) && (yearSelected == yearLimit))) || (enableFuture == 0 && ((yearSelected > yearLimit) || (monthSelected > monthLimit) && (yearSelected == yearLimit) || (datePointer > dateLimit) && (monthSelected == monthLimit) && (yearSelected == yearLimit)))) {
			selDayAction = '';
			isPast = 1
		} else {
			selDayAction = 'href="javascript:dateSelected=' + datePointer + ';closeCalendar();"';
			sHTML += ' onMouseOver="window.status=\'' + selectDateMessage[language].replace('[date]', constructDate(datePointer, monthSelected, yearSelected)) + '\';style.cursor=\'pointer\';style.backgroundColor=\'' + hoverColor + '\';" onMouseOut="window.status=\'\';style.backgroundColor=\'' + bgColor + '\'" ONCLICK="dateSelected=' + datePointer + ';closeCalendar();"';
			isPast = 0
		}
		sHTML += '>';
		if ((datePointer == dateNow) && (monthSelected == monthNow) && (yearSelected == yearNow)) {
			sHTML += "<b><font color=#ff0000>&nbsp;" + datePointer + "</font>&nbsp;</b>"
		} else if (dayPointer % 7 == (startAt * -1) + 1) {
			if (isPast == 1) sHTML += "&nbsp;<font color=#909090>" + datePointer + "</font>&nbsp;";
			else sHTML += "&nbsp;<font color=#54A6E2>" + datePointer + "</font>&nbsp;"
		} else if ((dayPointer % 7 == (startAt * -1) + 7 && startAt == 1) || (dayPointer % 7 == startAt && startAt == 0)) {
			if (isPast == 1) sHTML += "&nbsp;<font color=#909090>" + datePointer + "</font>&nbsp;";
			else sHTML += "&nbsp;<font color=#54A6E2>" + datePointer + "</font>&nbsp;"
		} else {
			if (isPast == 1) sHTML += "&nbsp;<font color=#909090>" + datePointer + "</font>&nbsp;";
			else sHTML += "&nbsp;<font color=#000066>" + datePointer + "</font>&nbsp;"
		}
		sHTML += '';
		if ((dayPointer + startAt) % 7 == startAt) {
			sHTML += '</tr><tr>';
			if ((showWeekNumber == 1) && (datePointer < numDaysInMonth)) sHTML += '<td align="right">' + (WeekNbr(new Date(yearSelected, monthSelected, datePointer + 1))) + '&nbsp;</td>'
		}
	}
	for (dayPointer;
		((dayPointer + startAt) % 7) != startAt; dayPointer++) sHTML += '<td>&nbsp;</td>';
	document.getElementById('content').innerHTML = sHTML;
	document.getElementById('spanMonth').innerHTML = '&nbsp;' + monthName[language][monthSelected] + '&nbsp;<img id="changeMonth" src="' + imgDir + 'drop1.gif" width="12" height="10" border="0">';
	document.getElementById('spanYear').innerHTML = '&nbsp;' + yearSelected + '&nbsp;<img id="changeYear" src="' + imgDir + 'drop1.gif" width="12" height="10" border="0">'
}

function showCalendar(a, b, c, d, e, f, g, h, i) {
	if (a.length > 0) {
		dateLimit = Number(a.substring(0, 2));
		monthLimit = Number(a.substring(3, 5)) - 1;
		yearLimit = Number(a.substring(6))
	} else {
		dateLimit = today.getDate();
		monthLimit = today.getMonth();
		yearLimit = today.getYear();
		if (!ie) yearLimit += 1900
	} if (e != null && e != '') language = e;
	if (f != null) enablePast = f;
	else enablePast = 0; if (g != null) enableFuture = g;
	else enableFuture = 0; if (h != null) fixedX = h;
	if (i != null) fixedY = i;
	if (showToday == 1) {
		document.getElementById('lblToday').innerHTML = '<font color="#000066">' + todayString[language] + ' <a onmousemove="window.status=\'' + gotoString[language] + '\'" onmouseout="window.status=\'\'" title="' + gotoString[language] + '" style="' + styleAnchor + '" href="javascript:monthSelected=monthNow;yearSelected=yearNow;constructCalendar();">' + dayName[language][(today.getDay() - startAt == -1) ? 6 : (today.getDay() - startAt)] + ', ' + dateNow + ' ' + monthName[language][monthNow].substring(0, 3) + ' ' + yearNow + '</a></font>'
	}
	popUpCalendar(b, c, d)
}

function popUpCalendar(a, b, c) {
	var d = 0;
	var e = 0;
	if (bPageLoaded) {
		if (crossobj.visibility == 'hidden') {
			ctlToPlaceValue = b;
			dateFormat = c;
			formatChar = ' ';
			aFormat = dateFormat.split(formatChar);
			if (aFormat.length < 3) {
				formatChar = '/';
				aFormat = dateFormat.split(formatChar);
				if (aFormat.length < 3) {
					formatChar = '.';
					aFormat = dateFormat.split(formatChar);
					if (aFormat.length < 3) {
						formatChar = '-';
						aFormat = dateFormat.split(formatChar);
						if (aFormat.length < 3) formatChar = ''
					}
				}
			}
			tokensChanged = 0;
			if (formatChar != "") {
				aData = b.value.split(formatChar);
				for (i = 0; i < 3; i++) {
					if ((aFormat[i] == "d") || (aFormat[i] == "dd")) {
						dateSelected = parseInt(aData[i], 10);
						tokensChanged++
					} else if ((aFormat[i] == "m") || (aFormat[i] == "mm")) {
						monthSelected = parseInt(aData[i], 10) - 1;
						tokensChanged++
					} else if (aFormat[i] == "yyyy") {
						yearSelected = parseInt(aData[i], 10);
						tokensChanged++
					} else if (aFormat[i] == "mmm") {
						for (j = 0; j < 12; j++) {
							if (aData[i] == monthName[language][j]) {
								monthSelected = j;
								tokensChanged++
							}
						}
					} else if (aFormat[i] == "mmmm") {
						for (j = 0; j < 12; j++) {
							if (aData[i] == monthName2[language][j]) {
								monthSelected = j;
								tokensChanged++
							}
						}
					}
				}
			}
			if ((tokensChanged != 3) || isNaN(dateSelected) || isNaN(monthSelected) || isNaN(yearSelected)) {
				dateSelected = dateNow;
				monthSelected = monthNow;
				yearSelected = yearNow
			}
			odateSelected = dateSelected;
			omonthSelected = monthSelected;
			oyearSelected = yearSelected;
			aTag = a;
			do {
				aTag = aTag.offsetParent;
				d += aTag.offsetLeft;
				e += aTag.offsetTop
			} while (aTag.offsetParent && (aTag.tagName != 'HTML') && (aTag.tagName != 'BODY'));
			if (window.innerWidth) width = window.innerWidth;
			else if (document.documentElement && document.documentElement.clientWidth) width = document.documentElement.clientWidth;
			else if (document.body) width = document.body.clientwidth;
			else width = document.width;
			d += a.offsetLeft;
			if (d + 250 > width) d = (width > 230 ? width - 230 : 0);
			d = String((fixedX == -1) ? d : fixedX) + 'px';
			crossobj.left = d;
			crossobj.top = String((fixedY == -1) ? a.offsetTop + e + a.offsetHeight + 2 : fixedY) + 'px';
			constructCalendar(1, monthSelected, yearSelected);
			crossobj.visibility = (dom || ie) ? "visible" : "show";
			crossobj.display = "block";
			bShow = true
		} else {
			hideCalendar();
			if (ctlNow != a) popUpCalendar(a, b, c)
		}
		ctlNow = a
	}
}
document.onkeypress = function hidecal1(a) {
	if (((ie) && (event.keyCode == 27)) || (a.keyCode == 27)) hideCalendar()
};
document.onclick = function hidecal2() {
	if (!bShow) hideCalendar();
	bShow = false
};
if (ie) init();
else window.onload = init;
