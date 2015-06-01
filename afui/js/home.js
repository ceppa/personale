function goHome(mese,anno)
{
	showLoader();
	$.post("include/home.php",
		{"op":"goHome","mese":mese,"anno":anno},
		function(data)
		{
			$.ui.updatePanel("#thepage","");
			data=safeParseJson(data);
			if(data.length==0)
				return;
			header=data.header;
			footer=data.footer;
			var mese=data.mese;
			var anno=data.anno;
			var page=createHomeTable(data.content);
			page+='<input type="hidden" id="mese" value="'+mese+'">';
			page+='<input type="hidden" id="anno" value="'+anno+'">';
			setHeaderFooterContent("thepage",header,footer,page);
			$("#homeTable tr").click(function()
				{
					var tr=$(this);
					if(tr.hasClass("selected"))
						alert($(this).attr("id"));
					else
					{
						$("#homeTable tr").removeClass("selected");
						tr.addClass("selected");
					}
				});
			$("#header span").show();
			hideLoader();
		} 
	);
}

function createHomeTable(page)
{
	var table='';
	var festivo;

	if(page.length)
	{
		page=safeParseJson(page);
		$("[id^='homeTable']" ).remove();
		table='<div class="table"><table id="homeTable">'
	        +'<tr>';

		var firstline=page[0];
		var conta=0;
		for(var key in firstline)
			if(key.indexOf("__hidden")!=0)
			{
				
				table+='<td>'+key+'</td>';
				conta++;
			}
        table+='</tr>';

		$.each(page, function(giorno, item) 
		{
			festivo='';
			var idrow=" id='"+giorno+"'";
			if(Number(item["__hidden__festivo"]==1))
				festivo=' class="festivo"';

			table+='<tr'+festivo+idrow+'>';
			for (var property in item) 
			{
					
				if (item.hasOwnProperty(property)) 
				{
					if(property.indexOf("__hidden")!=0)
						table+='<td>'+item[property]+'</td>';
				}
			}
			table+='</tr>';
		});
		table+='</table></div>';

	}
	return table;
}
