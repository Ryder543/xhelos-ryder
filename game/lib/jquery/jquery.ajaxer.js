var ajaxer_time =30000;
var ajaxer_name ='ajaxer';//Cambiar Tambien en el medi.css 
var ajaxer_text ='warning';//Cambiar el CSS en ajaxer.css tambien
var img_load = '<img class="'+ajaxer_name+'" src="../../img/loading.gif">';
var img_yes = '<img class="'+ajaxer_name+'" src="../../img/ok.gif">';


function ajaxLoad(obj)
{

	/*//Funcion con Checks
	$(obj).siblings('.'+ajaxer_name).remove();
	$(img_load).insertBefore($(obj));
	*/
	$(img_load).insertAfter($(obj));
	$(obj).siblings('.'+ajaxer_text).remove();

	if(typeof(arguments[1])=='string')
	{
		texto=arguments[1];
		span=arguments[2];		
		if(span)
		{
			$(span).html(texto);
			bool_arg=true;
		}
		else
		{
			$(span).after(texto);
		}

		
	}
}
function ajaxFinish(obj){
	//alert('Su pagina se cargo con exito');
	/*//Funcion con Checks*/
	//$(obj).siblings('.'+ajaxer_name).replaceWith(img_yes);

	$(obj).siblings('.'+ajaxer_name).remove();
	if(typeof(arguments[1])=='string')
	{
		texto = '<br><span class="'+ajaxer_text+'">'+arguments[1]+'</span>';
		texto2 = arguments[1];
		var bool_arg=false;
		var obj_argu = arguments[2];
		if(obj_argu)
		{
			$(obj_argu).html(texto2);
			bool_arg=true;
		}
		else
		{
			$(obj).after(texto);
		}

		setTimeout(function(){				
			if(bool_arg)
			{
				$(obj_argu).html('...');
			}
			else
			{
				//$(obj).siblings('.'+ajaxer_text).remove();
			}
		}, ajaxer_time);
	}
	return false;
}
