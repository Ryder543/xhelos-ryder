/****************************************************************************
 * jQuery AutoError [AC] Plugin
 * version: 1.0 (13-JUL-2008)
 * Author Jose Carlos Tamayo designbyjeeba@gmail.com
 * Web Page : www.designbyjeeba.blogspot.com
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
	  
	gnError = 'error';	//Nombre del numero de errores
	gnSuccess = 'success';	//Nombre del numero de errores
	gnWarning = 'warning';	//Nombre del numero de errores
	gnWarningMsg = 'warningmsg';	//Nombre del numero de errores
	gnClassError = 'ae_error';	//Nombre del numero de errores
	
//*******************************************************************************
//	MAIN FUNCTIONS - Principal Functions for autoerror
//******************************************************************************	  
	$.fn.ae_cleanerror = function (){
		var form = this;
		$(form).find('span.'+gnClassError).remove();
	}
	$.fn.autoerror = function (data){	/*0 Setting the variables*/
		var form = this;
		//1) Eliminar todos los anteriores spans
		$(form).find('span.'+gnClassError).remove();
		//2) Añadir todos los errores en sus respectivos sitios
		if($(form).is('form'))
		{
			for(var id in data[gnError])//itera por todos los id con errores
			{
				var input = $(form).find('#'+id)[0];
				if(typeof(input)!='undefined')//Previene de que error sin ID aparezca
				{
					var buc = data[gnError][id].length;//itera por todos los errores de ese ID
					for(var y=0;y<buc;y++)
					{
						var text = data[gnError][id][y];
						DOM = '<span class="'+gnClassError+'">'+text+'</span>';
						$(input).after(DOM);
					}
				}
				$(input).focus();
			}
			
		}
		else
		{
			$.log('No es un form:');
			$.log(form);
		}
		return form;
	}//function:autoterror
})(jQuery);
// JavaScript Document