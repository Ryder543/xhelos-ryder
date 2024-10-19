/****************************************************************************
 * jQuery AutoComplete [AC] Plugin
 * version: 0.1 (2008-05-08)
 * Author Jose Carlos Tamayo joseka17@hotmail.com
 *
 * This document is licensed as free software under the terms of the
 * MIT License: http://www.opensource.org/licenses/mit-license.php
 *
 *Based somewhat on Remy Sharp example 	 
 *http://remysharp.com/2007/01/20/auto-populating-select-boxes-using-jquery-ajax/
 *Basicaly i just remade it to fill my needs. Also i do it like a Jquery plugin.
 * copywrited 2008 by Carlos Tamayo .
 
 * Modo de Uso
/**********************************************************************
 -Añadir la clase definida en la variable global ac_class para idnicar que elemento tiene el autocomplete
 -El atributo id sera enviado como la variable llamada 'accion'
 -El atributo alt sera usado como la direccion a mandar

-Ejemplo:
 <input type="text" id="look_cie10" class="ac" alt="../../lib/medi/ajaxInter.php">

/**********************************************************************/

(function($){
//Variables Globales
	var edited_class = 'ac_edited';
	var box_class = 'ac_box';
	var list_class = 'ac_list';
	var item_class = 'ac_item';
	var ac_class = 'ac';
	var gEvent = 'acAjaxData';

/**********************************************************************/
//DOM
	var box='<div class="'+box_class+'" style="display: none;">'
			+'<img src="../../lib/jquery/upArrow.png" style="position: relative; top: -17px; left: 0px;" alt="" />'
			+'<div class='+list_class+'>&nbsp;</div>'
			+'</div>';	


/**********************************************************************/
//Funciones
//function lookup(url,data,btn,txt)
	$.fn.autocomplete = function (url,data){
		
		var obj = this;
		var buscar = $(obj).val();
		data['var1'] = buscar;//MASAKA!!!

		//Eliminar todos los suggestionsBox
		$('.'+box_class).remove();
		$('.'+edited_class).removeClass(edited_class);
		
		//Create the BOX and give it position
		var offset = $(obj).offset();
		var arriba =offset.top+$(obj).height()+5;
		var izquierda =offset.left;
		$(obj).after(box);
		$('.'+box_class).css({ left:izquierda, top:arriba});
		$(obj).addClass(edited_class);
		var inputString = $(obj).val();
		
		if(inputString.length == 0)
		{
			$('.'+box_class).hide();
		}
		else
		{
			$.getJSON(url,data, function(data){
				fillbox(data);
				parentEvent(obj,gEvent,data);
			});//post
				
		}//else*/
	} // autocomplete
	
	var fillbox = function(data){
		var resp = data;
		if(resp.length >0) {
				$('.'+list_class).html('');
				for(var x=0; x<resp.length;x++)
				{
					//Generar el Valor
					var valor="";
					if(typeof(resp[x].val)!='undefined')
					{
						valor=resp[x].val.substr(0);
	
					}
					else
					{
						valor=resp[x].dsc.substr(0);
					}	
					if(typeof(resp[x].sec)!='undefined')//Si hay datos Secundarios
				   {	
						list='<li valor="'+valor+'" class="'+item_class+'">'+resp[x].dsc+'<span class="sec"> '+resp[x].sec+'</span></li>';
				   }
				   else
				   {
						list='<li valor="'+valor+'" class="'+item_class+'">'+resp[x].dsc+'</li>';				   
				   }
					$('.'+list_class).append(list);
				}//for
				$("li."+item_class).click(fillinput);
				$('.'+box_class).show();
		}//if
		else{
			$('.'+list_class).html('');
			list='<span>No hay resultados</span>';
			$('.'+list_class).append(list);
			$('.'+box_class).show();
		}
	}
	
	var fillinput = function() {
		var value = $(this).attr('valor');//DANGER DANGER WILL ROBINSON, node beriamos usar DOM que no existe,
		$('.'+edited_class).val(value);
		$('.'+box_class).hide('slow');
		//$('.'+box_class).remove();
	}

	var parentEvent = function(dom,evento,data)
	{
		var input='';
		var parent='';
		//$(parent).trigger(gLoadEvent);
		if($(dom).is('input'))
		{
			input = dom;
		}
		else
		{
			input = $(dom).parents('input');
		}
		$(input).trigger(evento,data);
	}


})(jQuery);
