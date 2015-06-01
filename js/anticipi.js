<script type="text/javascript">
	var MINI = require('minified');
	var _=MINI._, $=MINI.$, $$=MINI.$$, EE=MINI.EE, HTML=MINI.HTML;

	$(function()
	{
		listAnticipi();
	});

	function listAnticipi()
	{
		$(".tab_header tr td")[4].innerHTML="Lista anticipi";
		var user=$$("#hiddenUser").value;
		var anno=$$("#hiddenAnno").value;
		updateDiv(user,anno);
	}

	function submitClick()
	{
		var ok=check_post_anticipi();
		if(ok)
		{
			var form=$$("#edit_form");
			var id_anticipi=Number(form.id_anticipi.value);
			var id_utente=Number(form.id_utente.value);
			var data_valuta=form.data_valuta.value;
			var euri=form.euri.value;
			var note=form.note.value;
			var message=(id_anticipi>0?"Modifica effettuata":"Inserimento effettuato");
			$("#listDiv").hide();
			$("#editDiv").hide();
			$("#waitDiv").show();

			$.request('post', 'include/anticipi.php',
				{
					ajax: 'submitForm',
					id_anticipi: id_anticipi,
					id_utente: id_utente,
					data_valuta: data_valuta,
					euri: euri,
					note: note
				})
		    .then(function success(result)
		    {
				if(result.length==0)
					showMessage(message);
				else
				{
					showMessage("Errore");
				}
				listAnticipi();
		    })
		    .error(function(status, statusText, responseText)
		    {
				showMessage("Errore");
				listAnticipi();
			});
		}
	}
	function switchStato(id)
	{
		$.request('post', 'include/anticipi.php',
			{
				ajax: 'anticipi_sw',
				id: id
			})
	    .then(function success(result)
	    {
			if(result.length)
				alert(result);
			listAnticipi();
	    })
	    .error(function(status, statusText, responseText)
	    {
			showMessage("Errore");
		});
	}
	function anticipi_del(id,cognome)
	{
		if(confirm('Elimino richiesta '+cognome))
		{
			$.request('post','include/anticipi.php',
				{
					ajax: 'anticipi_del',
					id: id
				})
		    .then(function success(result)
		    {
				if(result.length)
					alert(result);
				listAnticipi();
		    })
		    .error(function(status, statusText, responseText)
		    {
				showMessage("Errore");
			});
		}
	}

	function annullaClick()
	{
		$(".tab_header tr td")[4].innerHTML="Lista anticipi";
		$("#editDiv").hide();
		$("#waitDiv").hide();
		$("#listDiv").show();
	}
	function userSelectChange()
	{
		var userSelect=$$('#userSelect').value;
		var annoSelect=0;
		updateDiv(userSelect,annoSelect);
	}
	function annoSelectChange()
	{
		var userSelect=$$('#userSelect').value;
		var annoSelect=$$('#annoSelect').value;
		updateDiv(userSelect,annoSelect);
	}

	function editAnticipoForm(id_anticipo)
	{
		var askForm=($$("#editDiv").innerHTML.length==0?1:0);
		$("#listDiv").hide();
		$("#editDiv").hide();
		$("#waitDiv").show();

		$.request('post', 'include/anticipi.php', {ajax: 'editAnticipoForm',
				anticipi_to_edit: id_anticipo, askForm: askForm})
		    .then(function success(result)
		    {
				result=$.parseJSON(result);

				if(askForm)
					$$("#editDiv").innerHTML=result["form"];
				var valori=result["valori"];
				var inputs=$("#edit_form input");
				for(i=0;i<inputs.length;i++)
					if(inputs[i].type!="button")
						inputs[i].value=valori[inputs[i].name];
				var textareas=$("#edit_form textarea");
				for(i=0;i<textareas.length;i++)
					textareas[i].value=valori[textareas[i].name];
				if(id_anticipo>0)
					$(".tab_header tr td")[4].innerHTML="Modifica anticipo";
				else
					$(".tab_header tr td")[4].innerHTML="Nuovo anticipo";

				$("#listDiv").hide();
				$("#waitDiv").hide();
				$("#editDiv").show();
		    })
		    .error(function(status, statusText, responseText)
		    {
				showMessage("Errore");
				listAnticipi();
			});
	}

	function updateDiv(userSelect,annoSelect)
	{
		$$("#hiddenUser").value=userSelect;
		$$("#hiddenAnno").value=annoSelect;
		$("#listDiv").hide();
		$("#editDiv").hide();
		$("#waitDiv").show();
		$.request('post', 'include/anticipi.php', {ajax: 'update', user: userSelect, anno: annoSelect})
		    .then(function success(result)
		    {
		    	$$('#listDiv').innerHTML=result;
				$("#editDiv").hide();
				$("#waitDiv").hide();
				$('#listDiv').show();
		    })
		    .error(function(status, statusText, responseText)
		    {
				showMessage('Got an error.');
				$("#editDiv").hide();
				$("#waitDiv").hide();
				$('#listDiv').show();
			});
	}

	function check_post_anticipi()
	{
		var form=$$("#edit_form");
		var livello=$$("#hiddenLivello").value;
		out=true;
		if(!is_date(form.data_valuta.value))
		{
			showMessage("data valuta "+form.data_valuta.value+" non valida");
			return false;
		}
		if((livello!=1)&&(datetime_diff("<?=date("d/m/Y H:i")?>",form.data_valuta.value)<172800))
		{
			showMessage("data valuta troppo prossima");
			return false;
		}
		if(((parseFloat(form.euri.value))==0)||
			(!is_number(form.euri.value))||
			(form.euri.value.length==0))
		{
			showMessage("importo non valido");
			return false;
		}
		return out;
	}


</script>
