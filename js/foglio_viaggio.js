<script type="text/javascript">
	var MINI = require('minified');
	var _=MINI._, $=MINI.$, $$=MINI.$$, EE=MINI.EE, HTML=MINI.HTML;

	function updateDays()
	{
		var datainizio=$$("#datainizio").value;
		var orainizio=$$("#orainizio").value;
		var datafine=$$("#datafine").value;
		var orafine=$$("#orafine").value;
		$.request('post', 'include/foglio_viaggio_util.php', 
			{
				func: 'calcolagiorni', 
				datainizio:  datainizio,
				datafine:  datafine,
				orainizio: orainizio,
				orafine: orafine
			})
	    .then(function success(result) 
	    {
			if((result.length==0)||(result=='0'))
				result="-";
			$$("#giorni_cell").value=result;
	    })
	    .error(function(status, statusText, responseText) 
	    {
			$$("#giorni_cell").value="-";
		});
	}

	$(function() 
	{
		updateDays();
		$('#piedilista').trigger('change');
	});

	$("#piedilista").on('change', function()
		{
			showHideOrdBase();
		});

	$("#datainizio").on('change', function() {$$(this).value=formattadata($$(this).value);updateDays();});
	$("#orainizio").on('change', function() {if(is_hour($$(this).value))$$(this).value=formattaora($$(this));updateDays();});
	$("#datafine").on('change', function() {$$(this).value=formattadata($$(this).value);updateDays();});
	$("#orafine").on('change', function() {if(is_hour($$(this).value))$$(this).value=formattaora($$(this));updateDays();});

	var input_cambio = document.getElementById('input_cambio');
	input_cambio.onkeydown=function(e)
		{
			return onlyNumbersFloat(e,this)
		};
	var input_ord = document.getElementById('input_ord');
	input_ord.onkeydown=function(e)
		{
			return onlyNumbers(e);
		};
	var input_base = document.getElementById('input_base');
	input_base.onkeydown=function(e)
		{
			return onlyNumbers(e);
		};
	var input_fest = document.getElementById('input_fest');
	input_fest.onkeydown=function(e)
		{
			return onlyNumbers(e);
		};

	Array.prototype.has=function(v)
	{
		for (i=0;i<this.length;i++)
		{
			if (this[i]==v) return true;
		}
		return false;
	}

	function showHideOrdBase() 
	{
		var paese=$$('#paese').value;
		if($$('#piedilista').checked)
		{
			$$('#input_ord').value='0';
			$('#ord').hide();
			$$('#input_base').value='0';
			$('#base').hide();
			$$('#input_fest').value='0';
			$('#fest').hide();
			return;
		}
		paesiNonEuro={<?=$lista_non_euro?>};
		paesiEsclusiOrd=new Array(<?=$lista_escl_ord?>);
		paesiEsclusiBase=new Array(<?=$lista_escl_base?>);
		paesiEsclusiFest=new Array(<?=$lista_escl_fest?>);

		var rowcambio=document.getElementById('row_cambio');
		var inputcambio=document.getElementById('input_cambio');
		var labelcambio=document.getElementById('label_cambio');
		var labeldivisa=document.getElementById('label_divisa');
		var labeleuro=document.getElementById('label_euro');
		if(typeof(paesiNonEuro[paese])!="undefined")
		{
			rowcambio.style.display="";
			labelcambio.innerHTML=paesiNonEuro[paese];
			labeldivisa.innerHTML=paesiNonEuro[paese];
			labeleuro.style.display='';
			show_hide_column('table_detail',2, true);
		}
		else
		{
			rowcambio.style.display="none";
			inputcambio.value='1';
			show_hide_column('table_detail',2, false);
			for(var i=0;i<10;i++)
				document.getElementById("impdivisa_"+String(i)).value="";
			labeleuro.style.display='none';
		}


		var roword=document.getElementById('ord');
		var inputord=document.getElementById('input_ord');
		var tdfestiva=document.getElementById('festiva');

		if(paesiEsclusiOrd.has(paese))
		{
			roword.style.display="none";
			inputord.value='0';
			tdfestiva.innerHTML="giorni trasferta ordinaria/festiva";
		}
		else
		{
			roword.style.display="";
			tdfestiva.innerHTML="giorni trasferta festiva";
		}

		var rowbase=document.getElementById('base');
		var inputbase=document.getElementById('input_base');
		if(paesiEsclusiBase.has(paese))
		{
			rowbase.style.display="none";
			inputbase.value='0';
		}
		else
			rowbase.style.display="";

		var rowfest=document.getElementById('fest');
		var inputfest=document.getElementById('input_fest');
		if(paesiEsclusiFest.has(paese))
		{
			rowfest.style.display="none";
			inputfest.value='0';
		}
		else
			rowfest.style.display="";

	}
	document.getElementById("loc").focus();
	showHideOrdBase(document.getElementById("paese"));



	var misto=new Array();
	var diaria=new Array();
	<?
		foreach($misto as $k=>$v)
		{?>
			misto['<?=$k?>']=1;
		<?}
		foreach($diaria as $k=>$v)
		{?>
			diaria['<?=$k?>']=1;
		<?}
	?>
	function viaggioand(orainizio,base)
	{
		if(is_hour(orainizio.value))
		{
			if(ora2int(orainizio.value)<base)
				return(int2ora(base-Number(ora2int(orainizio.value))));
			else
				return("0:00");
		}
		else
			return("");
	}

	function viaggiorit(orafine,base)
	{
		if(is_hour(orafine.value))
		{
	//		if(ora2int(orafine.value)>base)
				return(int2ora((Number(ora2int(orafine.value))-base+1440) % 1440));
	//		else
	//			return("0:00");
		}
		else 
			return("");
	}
	function imp_change(sender)
	{
		sender.value=sender.value.replace(',','.');
		var i=sender.getAttribute("id").split("_")[1];
		document.getElementById("impdivisa_"+i).value="";
	}
	function impdivisa_change(sender)
	{
		sender.value=sender.value.replace(',','.');
		var i=sender.getAttribute("id").split("_")[1];
		document.getElementById("imp_"+i).value="";
	}
	function check_post_foglio(form)
	{
		var out=true;
		
		if(!trim(form.loc.value).length)
		{
			showMessage("località vuota");
			return false;
		}
		if(!is_date(form.datainizio.value))
		{
			showMessage("Data inizio non valida");
			return false;
		}
		if(!is_hour(form.orainizio.value))
		{
			showMessage("Ora inizio non valida");
			return false;
		}
		if(!is_date(form.datafine.value))
		{
			showMessage("Data termine non valida");
			return false;
		}
		if(datetime_diff(form.datainizio.value,form.datafine.value)<0)
		{
			showMessage("Data termine precedente data inizio");
			return false;
		}
		if(!is_hour(form.orafine.value))
		{
			showMessage("Ora termine non valida");
			return false;
		}
		if((typeof(form.oreviaggioand)!="undefined")&&(!is_hour(form.oreviaggioand.value)))
		{
			showMessage("Durata viaggio andata non valida");
			return false;
		}
		if((typeof(form.oreviaggiorit)!="undefined")&&(!is_hour(form.oreviaggiorit.value)))
		{
			showMessage("Durata viaggio ritorno non valida");
			return false;
		}
		if((typeof(form.commessa)!="undefined")&&(form.commessa.value==0))
		{
			showMessage("Seleziona una commessa");
			return false;
		}
		for(var i=0;i<<?=DETTAGLIO_SPESE_LINES?>;i++)
		{
			if(trim(document.getElementById("desc_"+String(i)).value).length)
			{
				if(!is_number(document.getElementById("imp_"+String(i)).value))
				{
					showMessage(document.getElementById("imp_"+String(i)).value+" non è un importo corretto");
					return false;
				}
				if(!is_number(document.getElementById("impdivisa_"+String(i)).value))
				{
					showMessage(document.getElementById("impdivisa_"+String(i)).value+" non è un importo corretto");
					return false;
				}
			}
		}
		var trattamento=(typeof(form.trattamento)!="undefined"?form.trattamento.value:"");
		if(((trattamento=="MISTO") && (misto[form.paese.value]==null))
			||((trattamento=="DIARIA") && (diaria[form.paese.value]==null)))
		{
			
			showMessage("Tipologia trasferta '"+form.paese.value+" "+
						trattamento+"' non presente");
			return false;
		}
		return out;
	}
	document.getElementById('edit_form').loc.focus();					
</script>
