var prevPage=null;
var curPage=null;
var nextPage=null;



$(function() 
{
	$("#thepage").bind("swipeLeft",function()
	{
		var mese=Number($("#mese").val())+1;
		var anno=Number($("#anno").val());
		goHome(mese,anno);
	});
	$("#thepage").bind("swipeRight",function()
	{
		var mese=Number($("#mese").val())-1;
		var anno=Number($("#anno").val());
		goHome(mese,anno);
	});

	$("#login").click(function(){loginClick()});
	main();
});

function safeParseJson(data)
{
	out="";
	try
	{
		out=JSON.parse(data);
//		out=$.parseJSON(data);
	}
	catch (e) 
	{
		$("#afui").popup("error parsing "+data);

	}
	return out;
}

function main()
{
	showLoader();
	$.post( "include/main.php",
		function(data) 
		{
			data=safeParseJson(data);
			if(data.length==0)
				return;

			$("#home").unbind("click");
			$("#home").click(function()
				{
					var mese=$("#mese").val();
					var anno=$("#anno").val();
					goHome(mese,anno);
				});
			$("#logout").unbind("click");
			$("#logout").click(function()
				{
					logoutClick();
				});

			module=data.module;
			switch(module)
			{
				case "login":
					showLogin();
					break;
				case "home":
					var d = new Date();
					var mese=1+d.getMonth();

					var anno=1900+d.getYear();
					goHome(mese,anno);
					break;
				default:
					break;
			}
			hideLoader();
		});
}

function setHeaderFooterContent(page,header,footer,content)
{
	$.ui.updatePanel("#"+page,content);
	$.ui.loadContent("#"+page,false,true,"slide");
	$.ui.setTitle(header);
}

function showLoader()
{
	$("#afui_mask").show();
}

function hideLoader()
{
	$("#afui_mask").hide();
}

		
