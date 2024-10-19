/****************************************************************************
 * jQuery AutoError [AC] Plugin
 * version: 0.1 (10-JUL-2008)
 * Author Jose Carlos Tamayo designbyjeeba@gmail.com
 *
 * This document is licensed as free software under the terms of the
 * MIT License: http://www.opensource.org/licenses/mit-license.php
 *
 * copywrited 2008 by Carlos Tamayo .
 
 * Mode of Use
/**********************************************************************/
//Apply this plugin to a form and send it the error data as parameter
/**********************************************************************/
//Botones

(function($){
/***************************
	AUTOFORM Names
***************************/	  
	  
	gnClassSelect = 'as';	//Nombre del numero de errores
	gnClassNoExist = 'as_n';	//Nombre del numero de errores
	gValueNoExist = -1;	//Nombre del numero de errores
	gnEvent = 'sel_load';	//Nombre del numero de errores
	
//*******************************************************************************
//	MAIN FUNCTIONS - Principal Functions for autoselect
//******************************************************************************	  
	$.fn.autoselect = function (url,data){	/*0 Setting the variables*/
		var sel = this;
		//1) Eliminar todos los anteriores spans
		$(sel).find('option').remove();
		$(sel).removeClass(gnClassNoExist);
		
		$.ajax({
					url:url,
					type:'post',
					async:false,
					data:data,
					success:function(json)
					{
						resp = getJSON(json);			
						//////////////////////////////////////////////////////////////////////////
						if(resp.length>0){
							for(var x in resp)
							{
								var value  = resp[x]['value'];
								var name  = resp[x]['name'];
								var DOM = '<option value="'+value+'">'+name+'</option>';
								if(typeof(resp[x]['actual'])!='undefined')
								{
									var actual = resp[x]['actual'];
									if(actual == 1)
									{
										DOM = '<option class="'+gnClassSelect+'" value="'+value+'">'+name+'</option>';
									}
								}
								$(sel).append(DOM);
							}//for
						}//if
						else
						{
							var DOM = '<option class="'+gnClassSelect+'" value="'+gValueNoExist+'">Sin Resultados</option>';	
							$(sel).addClass(gnClassNoExist);
							$(sel).append(DOM);
						}//else
						///////////////////////////////////////////////////////////////////////////
						$(sel).trigger(gnEvent);
					}//sucess			
		});
		
		//chain
		/*if(typeof(chain)!='undefined')
		{
			if($(chain).is('select'))
			{
				var var1 = $(chain).val();
				$(chain).change(function(var1){
					//$(sel).autoselect(url,{data});	
					$.log(data);
				});
			}
			else
			{
				$.log('autoselect-chain no es un objeto select');
			}
		}*/
		return sel;
	}
	
	//Transforma de JSON a variable javascript
	var getJSON = function(resp){
		if(resp.length>0)
		{
			var json = eval("(" +resp + ")" );
			if(json){
				return json;}
			else{
				$.log('No se puede evaluar lo recibido en el servidor - getJSON')//JSK_MSG
				return false;}
		}
		else{
			$.log('La respuesta del servidor es demasiado pequeña - getJSON')//JSK_MSG
			return false;}
	}	
	
	
})(jQuery);
// JavaScript Document