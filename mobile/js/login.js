function loginClick()
{
	showLoader();
	var pw=$.md5($.md5($("#password").val())+$("#rnd").val());
	var postVars="op=doLogin&username="+$("#username").val()+"&password="+pw;

	$.post("include/login.php",
		postVars,
		function(data)
		{
			data=safeParseJson(data);
			if(data.length==0)
				return;
			
			if(data.logged=="1")
			{
				if(data.expired=="1")
				{
					$("#afui").popup("expired");
				}
				else
				{
					var mese=4;
					var anno=2014;
					goHome(mese,anno);
				}
			}
			else
			{
				if(data.message.length)
					$("#afui").popup(data.message);
			}
			hideLoader();
		} 
	);
}

function logoutClick()
{
	showLoader();
	$.post("include/login.php",
		{"op":"doLogout"},
		function(data)
		{
			if(data.length)
				$("#afui").popup(data);
			main();
			hideLoader();
		} 
	);

}

function showLogin()
{
	$("#header span").hide();
	$.ui.updatePanel("#thepage","");
	$.ui.loadContent("#main",true,false,"fade");
	$("#username").focus();
}
