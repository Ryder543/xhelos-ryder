/****************************************************************************
 * jQuery AutoTable [AC] Plugin
 * version: 0.1 (2008-05-08)
 * Author Jose Carlos Tamayo joseka17@hotmail.com
 *
 * This document is licensed as free software under the terms of the
 * MIT License: http://www.opensource.org/licenses/mit-license.php
 *
 * copywrited 2008 by Carlos Tamayo .
 
 * Mode of Use
/**********************************************************************/
/**********************************************************************/
//Botones
$(document).ready(function(){
	//$.log('hola');
});

(function($){
/***************************
	AUTOFORM VARIABLES
***************************/	  
	gXml = null;	//global XML, for Caching Purpose
	gUrl = null;	//global URL, the Url to look for Table Structure, for Caching Purpose
	//gParent = '';	//global Parent reference
	
/***************************
	AUTOFORM Names
***************************/	  
	gTh = 'th';		//global table row header class, the first row in the table have this class
	gEdit = 'editable';		//global table row header class, the first row in the table have this class
	gHcn='hiddenid';	//global Hidden Class Name -  It indicate the name to hide the class
	gTool='tool';	//global Hidden Class Name -  It indicate the name to hide the class
	gpFieldset='paf';	//global Prefix of the Parent fieldset and name of his class
	gFieldset='af';	//global Prefix of the fieldset and name of his class
	gLoadEvent='af_load';//global Prefix of the fieldset and name of his class
	gInitEvent='af_init';//global Prefix of the fieldset and name of his class
	gUpdateEvent='formDataUpdate';//global Prefix of the fieldset and name of his class
	gMsgDiv='Atmsg';//global Prefix of the fieldset and name of his ID
	gnEventXnew='xnew';//global Prefix of the fieldset and name of his class
	gnEventXupdate='xupdate';//global Prefix of the fieldset and name of his class
	gnMainLegend='af_mainlegend';//global Prefix of the fieldset and name of his class
/*******************************************************************************
	MAIN FUNCTIONS - Principal Functions for autoform
/******************************************************************************	  

	/*****************************************************************************************
	Function autoform
	@param xmlUrl	: Url of the XML
	@param post 	: Data to send to the Server to search for the info, returned
					as JSON
	/******************************************************************************/	  
	$.fn.autoform = function (xmlUrl){
		var parent = this;
		/*1) Get the POST or CACHED XML*/
		getXML(xmlUrl);
		/*2) Set the Entity (the XML structure)*/
		var entity = $(gXml).find('entity')[0];
		/*3) Create the Form (If it exist dont do a thing)*/	
		form = createForm(entity,parent);
		/*4) Create the Fieldset*/	
		createFieldset(entity,form);		
		/*5) Create the Fields*/		
		createField(entity,form);
		/*7) Create the DivMsg*/
		createMsg(form,entity);
		/*8) Init the Actions*/
		initAction(form,entity);
		/*9) Send the Load Event binden to the FORM*/
		parentEvent(form,gLoadEvent);
		return form;
	}//function:autotable
	
	var createMsg = function(form)
	{
		var pFieldset = $(form).children('fieldset')[0];
		var MSG = '<div id="'+gMsgDiv+'"></div>';
		$(pFieldset).prepend(MSG);
		$('div#'+gMsgDiv).hide();
	}
	var createForm = function(entity,parent)
	{
		var id =$(entity).attr('id');
		var url_action = $(entity).attr('url');
		var form = $(parent).find('form#'+id);
		//Buscamos si Existe
		
		if(form.length==0)//No Existe
		{			
			//1) Create the Form
			var FORM = '<form id="'+id+'" name="'+id+'" action="'+url_action+'"method="post"><fieldset class="'+gpFieldset+'"><legend id="'+gnMainLegend+'"></legend></fieldset></form>';
			$(FORM).appendTo(parent);
			var form = $(parent).find('form#'+id)[0];
		}
		return form;
	}
	
	var createFieldset = function(entity,form)
	{
			var pFieldset = $(form).children('fieldset')[0];
			$(entity).find('fieldset').each(function(){
				var cFieldset = $(this);
				var cName = $(cFieldset).attr('name');	//children name
				var cId = $(cFieldset).attr('id');
				var FIELDSET = '<fieldset class="'+gFieldset+'" id="'+cId+'"><legend>'+cName+'</legend></fieldset>';
				$(pFieldset).append(FIELDSET);
			});
	}
	var createField = function(entity,form)
	{
		var pFieldset = $(form).children('fieldset')[0];
		$(entity).find('data').each(function(){
			var text = '';
			var field = $(this);
			var cName = $(field).attr('name');
			var fieldname = $(field).attr('fieldset');
			var FIELD = parseType(field,text,form);
			insertField(fieldname,form,FIELD,pFieldset);
		});
	}
	var initAction = function(form,entity)
	{
		var pFieldset = $(form).children('fieldset')[0];
		var legend = $(form).find('legend#'+gnMainLegend)[0];
		$(entity).find('action').each(function(){
			var action = this;
			var type = $(action).attr('type');
			var name = $(action).attr('name');
			var url = $(action).attr('url');
			var btn = $(action).attr('btn');
			var id = $(action).attr('id');
			switch(type)
			{
				case 'init':
					//**********************************************************************************
					//	Iniciar Formulario
					//**********************************************************************************
					$(legend).html(name);					
					var data = new Object();
					$(action).find('param').each(function(){			
						var type = $(this).attr('type');
						var who = $(this).attr('value');
						switch(type)
						{
							case 'get':
								name = $(this).attr('name');
								ref = $(this).attr('value');
								value = $.jget[ref];
								data[name] = value;
								break;
								
							default:
								name = $(this).attr('name');
								value = $(this).attr('value');
								data[name] = value;
						}	
					});				
					var json = new Object();
					$.ajax({
						url:url,
						type:'post',
						async:false,
						data:data,
						success:function(resp){json = getJSON(resp);}			
					});
					$(entity).find('data').each(function(){
						var data = $(this);
						var sql = $(data).attr('sql');
						var text = json[0][sql];
						//$.log('sql:'+sql);
						//$.log(json[0]);
						var FIELD = parseType(data,text,form);//Se llena los valores
					});
					//Enviar la data por Evento
					parentEvent(form,gInitEvent,json);

					
					break;
					
					//**********************************************************************************
					//	Update Formulario
					//**********************************************************************************
			case 'xnew': //a)
				createParam(action,pFieldset,'set')
				$(legend).html(name);
				var BTN='<input id="'+id+'" type="button" value="'+btn+'" url="'+url+'"/>';
				$(pFieldset).append(BTN);
				var boton = $(pFieldset).find('input#'+id+'[type=button]')[0];
				$(boton).click(function(evento){xnew(evento)});
				break;
			case 'new': //a)
				createParam(action,pFieldset,'set')
				$(legend).html(name);
				var BTN='<input id="'+id+'" type="submit" value="'+btn+'"/>';
				$(pFieldset).append(BTN);
				$(form).attr('action',url);
				break;
			case'update': //b)
				createParam(action,pFieldset,'set')
				$(legend).html(name);
				var BTN='<input type="submit" value="'+btn+'"/>';
				$(pFieldset).append(BTN);
				$(form).attr('action',url);
				break;
			case'xupdate': //b)
				createParam(action,pFieldset,'set')
				$(legend).html(name);
				var BTN='<input id="'+id+'" type="button" value="'+btn+'" url="'+url+'"/>';
				$(pFieldset).append(BTN);
				var boton = $(pFieldset).find('input#'+id+'[type=button]')[0];
				$(boton).click(function(evento){xupdate(evento)});
				break;
			case 'msg':
				var mens =$(action).attr('value');
				$(form).find('#'+gMsgDiv).html(mens);
				break;
			default:
				$(legend).html(name);
				var BTN='<input type="button" value="DEFAULT:'+btn+'"/>';
				$(pFieldset).append(BTN);
				$(form).attr('action',set_url);
				break;
			}
		});
	}
/*****************************************************
TOOL AUXILIARES - Funciones Auxiliares para autotable
****************************************************/
	
	/*****************************************************************************************
	Function parseType - Devuelve cadena detexto con contenido DOM del tipo de varibale
	@param entity = xml que contiene todo
	@param texto = texto para llenar
	@param mode = mode of work
				-cell: print the info as a cell
				-label: print the info as a label
	*****************************************************************************************/	
	var parseType = function(data,texto,form)
	{
		var DATA='';
		var id=$(data).attr('id');
		var dsc=$(data).attr('dsc');
		var type=$(data).attr('type');
		var class=$(data).attr('class');
		var maxlength=$(data).attr('maxlength');
		var maxdom='';//No hay maxlength , enotnces es vacio
		//Calcular el string de maxlength
		if(typeof(maxlength)!='undefined')
		{
			maxdom=' maxlength='+maxlength+' ';
		}
		switch(type)
		{	
			case 'span':
				if(texto)
				{
					var span = $('span#'+id)[0];
					if(typeof(span)!='undefined')
					{
						$(span).html(texto);
					}
					else
					{
						DATA='<span id="'+id+'" name="'+id+'" class="'+class+' '+gFieldset+'" value="'+texto+'"></span>';
					}
				}
				else
				{
					DATA='<span id="'+id+'" name="'+id+'" class="'+class+' '+gFieldset+'" value=""></span>';
				}
				DATA='<label>'+dsc+DATA+'</label>';
				break;
				
			case 'hidden':
				if(texto)
				{
					var hidden = $('input#'+id)[0];
					if(typeof(hidden)!='undefined')
					{
						$(hidden).val(texto);
					}
					else
					{
						DATA='<input type="hidden" name="'+id+'" id="'+id+'" class="'+class+' '+gFieldset+'_hidden" value="'+texto+'"></td>';
					}
				}
				else
				{
					DATA='<input type="hidden" id="'+id+'" name="'+id+'" class="'+class+' '+gFieldset+'_hidden" value=""></td>';
				}
				break;
			
			case 'text':
				if(texto)
				{	
					var text = $('input#'+id)[0];
					if(typeof(text)!='undefined')
					{
						$(text).val(texto);
					}
					else
					{
						DATA='<input type="text" id="'+id+'" name="'+id+'" '+maxdom+' class="'+class+' '+gFieldset+'" value="'+texto+'"></td>';
					}
				}
				else
				{
					DATA='<input type="text" id="'+id+'" name="'+id+'" '+maxdom+' class="'+class+' '+gFieldset+'" value=""></td>';
				}
				DATA='<label>'+dsc+DATA+'</label>';
				break;
			
			case 'textarea':
				if(texto)
				{
					var textarea = $('textarea#'+id)[0];
					if(typeof(textarea)!='undefined')
					{
						$(textarea).html(texto);
					}
					else
					{
						DATA='<textarea  name="'+id+'" class="'+class+' '+gFieldset+'"  id="'+id+'">'+texto+'</textarea></td>';
					}
				}
				else
				{
					DATA='<textarea  name="'+id+'" class="'+class+' '+gFieldset+'"  id="'+id+'"></textarea></td>';
				}
				DATA='<label>'+dsc+DATA+'</label>';
				break;
			
			case 'date':
				if(texto)
				{
					var date = $('span#'+id)[0];
					if(typeof(date)!='undefined')
					{
						$(date).input(texto);//santo dios que hace esto!!!
					}
					else
					{
						DATA='<input type="text" maxvalue="10" name="'+id+'" id="'+id+'" class="date '+class+' '+gFieldset+'" value="'+texto+'" /></td>';
					}
				}
				else
				{
					DATA='<input type="text"  maxvalue="10" name="'+id+'" id="'+id+'" class="date '+class+' '+gFieldset+'" value="" /></td>';
				}
				DATA='<label>'+dsc+DATA+'</label>';
				break;
			case 'time':
				if(texto)
				{	
					var time = $('input#'+id)[0];
					if(typeof(time)!='undefined')
					{
						$(time).val(texto);
					}
					else
					{
						DATA='<input type="text" id="'+id+'" name="'+id+'" maxvalue="8" class=time "'+class+' '+gFieldset+'" value="'+texto+'"/></td>';
					}
				}
				else
				{
					DATA='<input type="text" id="'+id+'" name="'+id+'" class="time maxvalue="8" '+class+' '+gFieldset+'" value=""/></td>';
				}
				DATA='<label>'+dsc+'</label>'+DATA;
			break;
			case 'select'://dos casos A)por ajax B)embebido 
				//$.log('text:'+texto);
				if(texto!='')//Si texto existe entonces llenar el SELECT
				{
					var sel = $('select#'+id)[0];
					$(sel).val(texto);
				}
				else
				{
					//Caso de que DATA sea AJAX
					var url = $(data).attr('url');
					var who = $(data).attr('id');
					if(typeof(url)=='undefined')//Si no hay URL, entonces no se llenan los option con AJAX
					{	
						var DOM = '<select name="'+id+'" id="'+id+'" class="'+class+' '+gFieldset+'">"';
						$(data).find('option').each(function(){		
								var option = $(this);
								var value = $(option).attr('value');
								var name = $(option).attr('name');
								if(String(value)==String(texto))
								{
									DOM = DOM+"<option value='"+value+"' selected='selected'>"+name+"</option>";
								}
								else
								{
									DOM = DOM+"<option value='"+value+"'>"+name+"</option>";
								}
						});
						DOM = DOM + '</select>';
						DATA=DOM;
					}
					else//SI hay URL se llenan los option con ajax
					{
						var params = getParams(data,form);
						$.ajax({
							url:url,
							type:'POST',
							async: false,
							data:params,
							success:function(dato){
								var resp = getJSON(dato);
								var DOM = '<select name="'+id+'" id="'+id+'" class="'+class+' '+gFieldset+'">"';
								for(var x in resp)
								{
									var value = resp[x].value;
									var descr = resp[x].name;
									DOM = DOM+"<option value='"+value+"'>"+descr+"</option>";
								}
								DOM = DOM+"</select>";
								DATA=DOM;
							}//success
						});
					}//else			
				}//else text!=''
				DATA='<label>'+dsc+DATA+'</label>';
				break;

			default:
					var def = $('#'+id)[0];
					if(typeof(def)!='undefined')
					{
						$(def).html(texto);
					}
					else
					{
						DATA='<input type="text"  class="'+class+' '+gFieldset+'" value="DEFAULT:'+type+'">';
						DATA='<label>'+dsc+DATA+'</label>';
					}
			}
		return DATA;
	}//editableCell

	/*****************************************************************************************
	Function insertField - Obtiene la cabecera de una tabla, si ya existe guardarlo en memoria
	@param xmlUrl = direccion del xml
	*****************************************************************************************/
	var	insertField = function(fieldname,form,FIELD,pfield)
	{
		if(fieldname!='none')
		{
				var pFieldset = $(form).find('fieldset[id='+fieldname+']')[0];	
				$(pFieldset).append('<p>'+FIELD+'</p>');
		}
		else
		{
				$(pfield).append('<p>'+FIELD+'</p>');			
		}
	}

	/*****************************************************************************************
	Function getXML - Obtiene la cabecera de una tabla, si ya existe guardarlo en memoria
	@param xmlUrl = direccion del xml
	*****************************************************************************************/
	var	getXML = function(xmlUrl)
	{
		if(xmlUrl!=gUrl)
		{
			//$.log('Cacheando el xml');
			gUrl = xmlUrl;
			$.ajax({
				type: "POST",
				url: xmlUrl,
				async: false,
				dataType:'xml',
				success:function(data){gXml=data}//sucess
			});//ajax
		}
		else
		{
		}
	}
	/*****************************************************************************************
	Function formMode - Get and Set The mode of the form
	@param form = rferenced form
	@param mode = [OPTIONAL] mode to Set
	*****************************************************************************************/
	var createParam = function(action,pFieldset,mode){
		switch(mode)
		{
			case 'get':
				break;
			case 'set':
				var HIDDEN='';
				var id = $(action).attr('id');
				//Añadir getParams
				var form = $(pFieldset).parents('form');
				var param = getParams(action);//Obtiene los valores de los parametros (ojo solo los valores)
				//$.log(param);
				for (var key in param)
				{
					name = key;
					value = param[key];
					HIDDEN = HIDDEN+'<input type="hidden" class="'+id+'" id="'+name+'" name="'+name+'" value="'+value+'"/>';
				}
				$(pFieldset).append(HIDDEN);
				break;
		}
	}
	/*****************************************************************************************
	Function getParmas - Obtener los parametros de un Objeto XML
	@param form = rferenced form
	@param mode = [OPTIONAL] mode to Set
	*****************************************************************************************/
	var getParams = function(data,form){
		//**********************************************************************************
		//	Iniciar Formulario
		//**********************************************************************************			
		var param = new Object();
		$(data).find('param').each(function(){			
			var type = $(this).attr('type');
			switch(type)
			{
				case 'get'://GET the value from the URL
					name = $(this).attr('name');
					ref = $(this).attr('value');
					value = $.jget[ref];
					param[name] = value;
					break;
				case 'this'://Get the value from the form
					name = $(this).attr('name');
					value = $(this).attr('value');				
					param[name] = $(form).find('#'+value).val();
					break;
				case 'all'://Get the value from all the page, best to use this, for performance and seccurity
					name = $(this).attr('name');
					value = $(this).attr('value');				
					param[name] = $('#'+value).val();
					break;
					
				default:
					name = $(this).attr('name');
					value = $(this).attr('value');
					param[name] = value;
					break;
			}	
		});	
		return param;
		//$.log(param);	
	}
	
	var formMode = function(form,mode)
	{
		if(typeof(mode)!='undefined')
		{
			var modo = $(table).attr('mode',mode);
			
		}
		return $(table).attr('mode');
	}
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
	
	var parentEvent = function(dom,evento,data)
	{
		var form='';
		var parent='';
		//$(parent).trigger(gLoadEvent);
		if($(dom).is('form'))
		{
			form = dom;
		}
		else
		{
			form = $(dom).parents('form');
		}
		parent = $(form).parent();
		$(parent).trigger(evento,data);
		//$.log('EVENTO:'+evento);
	}
/*****************************************************
ACCIONES - Acciones de Autform
****************************************************/
	var xnew= function(evento){
		var boton = evento.target;
		var form = $(boton).parents('form');
		var seri = $(form).serialize();
		var url = $(boton).attr('url');
		$.ajax({
			url:url,
			type:'post',
			data:seri,
			success:function(dat){
				parentEvent(boton,gnEventXnew,dat)},
			error:function(){$.log('error en xnew')}
		});
	}
	var xupdate= function(evento){
		var boton = evento.target;
		var form = $(boton).parents('form');
		var seri = $(form).serialize();
		var url = $(boton).attr('url');
		$.ajax({
			url:url,
			type:'post',
			data:seri,
			success:function(dat){
				parentEvent(boton,gnEventXupdate,dat)},
			error:function(){$.log('error en xupdate')}
		});
	}

})(jQuery);



