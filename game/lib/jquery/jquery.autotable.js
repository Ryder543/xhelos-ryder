/****************************************************************************
 * jQuery AutoTable [AC] Plugin
 * version: 0.1 (2008-05-08)
 * Author Jose Carlos Tamayo joseka17@hotmail.com
 *
 * This document is licensed as free software under the terms of the
 * MIT License: http://www.opensource.org/licenses/mit-license.php
 *
 * copywrited 2008 by Carlos Tamayo .
 
 * Modo de Uso
/**********************************************************************/
/**********************************************************************/
//Botones
(function($){
	/***************************
	Autotable Variables
	***************************/	  
	gXml = null;	//global XML, for Caching Purpose
	gUrl = null;	//global URL, the Url to look for Table Structure, for Caching Purpose
	gParent = '';	//global Parent reference//
	gnResp = null;  //Respuesta de la BD
	
	/***************************
	Autotable Names
	***************************/	  
	gTh = 'th';		//global table row header class, the first row in the table have this class
	gEdit = 'editable';		//global table row header class, the first row in the table have this class
	gHcn='hidden';	//global Hidden Class Name -  It indicate the name to hide the class
	gTool='tool';	//global Hidden Class Name -  It indicate the name to hide the class
	gLimit='limit';	//global Variable to send to the server , indicates the number of rows to fetch
	gOffset='offset'//global Variable to send to the server , indicates the offset of rows to fetch
	
	/***************************
	Autotable Action Names
	***************************/	 
	gnNew = 'new'; 			// global variable for the action param
	gnUpdate = 'update';	// global variable for the action param
	gnNone = 'none';		// global variable for the mode param 
	gnRow = 'row';			// global variable for the mode param 
	gnCell = 'cell';		// global variable for the mode param 
	gnFull = 'full';		// global variable for the mode param 
	
	/***************************
	Autotable Events Names
	***************************/
	gToolSuperDelete = 'toolSuperDelete';
	gToolUpdate = 'toolUpdate';
	gToolRefresh = 'toolRefresh';
	/*******************************************************************************
	Function Autotable
	@param xmlUrl	: Url of the XML
	@param post 	: Data to send to the Server to search for the info, returned
					as JSON
	@param action	: Action to do with the data
					'new' Deletes Old Rows and insert new data to the table 	[gnNew]
					'update' Insert new data after the old rows					[gnUpdate]
	@param mode		: Mode of work of the table
					'none' the info is parsed as a Simple Table
					'row'  click a row an you can edit it
					'cell' click a cell an you can edit it
					'full' The Table is Parsed as Full Editable
	******************************************************************************/	  
	$.fn.autotable = function (xmlUrl,post,action,mode){
		/*0 Setting the variables*/
		gParent = this;
		var parent = this;
		/*1) Get the POST or CACHED XML*/
		getXML(xmlUrl);
		/*2) Set the Entity (the XML structure)*/
		var entity = $(gXml).find('entity')[0];
		/*3) Create the Table (If it exist dont do a thing)*/

		var table = getTable(entity,action,mode,parent);
		/*4) Fill the table with data if needed*/
		fillTable(entity,post,mode,table);
		/*5) Add the table tools*/
		createTool(table,mode,entity);
		/*6) Funcionalidad FInal*/
		createPagination(entity,table,post,mode);
		lastAdjust(entity,table);
	}//function:autotable

	/*************************************************************************
	Function lastAdjust - Give some last adjust to the table
	@param post = parametros enviados al server
	@param url = url a dodne se hace la peticion
	@param template = la forma como estan los datos en la tabla
	************************************************************************/
	var createPagination = function(entity,table,post,mode)
	{
		var npage = $(entity).attr('pagination');
		if((typeof(npage)!='undefined'))
		{
			npage = parseInt(npage);
			var page= $(table).attr('page');
			var nrows= $(table).attr('nrows');
			var tpages = Math.ceil(nrows/npage);
			var th = $(table).find('tfoot th')[0];
			$(th).html('');
			for(var x=1;x<=tpages;x++)
			{
				if(x !=page){
					DOM='<a class="page" href="#1">'+x+'</a>';
				}
				else{
					DOM='<span class="page" href="#1">'+x+'</span>';
				}
				$(th).append(DOM);
			}
			$(th).find('a.page').each(function(){
				var a = this;
				$(a).click(function(){
				   	pagination(this,entity,post,mode,table);
				});
			});
		}
	}
	var lastAdjust = function(entity,table)
	{
		/* poner invisible lo invisible*/
		/* ocultar tool*/
		/* 1) Añadir	Funcionalidad dependiendo del modo*/
		var mode = $(table).attr('mode');
		$(table).find('tr:not(.'+gTh+')').each(function(){
			switch(mode)
			{
				case 'row':
					//$(this).click(function(evento){changeRow(evento)});								
					$(this).click(changeRow);								
				break;
				
				case 'cell':
					$(this).find('td').each(function(){
					$(this).click(changeCell);
					});
				break;
				default:
			}//switch
		});//each
		
		/* 2) Añadir Clickabilidad al url*/
		$(table).find('td[type=url] a').click(function(){
			var cell = $(this).parents('td');
			var id = $(cell).attr('id');
			var table = $(this).parents('table');
			var tablecol = $(cell).parents('tr');
			var url = $(table).attr('url');
			getXML(url);
			var xml = gXml;
			var entity = $(xml).find('entity')[0];	
			var col = $(entity).find('col[id='+id+']')[0];
			var param='?';
			var obj = colSerialize(tablecol);
			$(col).find('param').each(function(){
				var id = $(this).attr('id');
				if(typeof(obj[id])!='undefined')
				{
					var texto = obj[id];
					param = param + $(this).attr('name') + '=' +texto+'&';
				}
				else
					$.log('No existe parametro mandado - lastAdjust');
			});
			//param = escape(param);
			param=$(this).attr('href')+param;
			$(this).attr('href',param);
			//return false;
		});
		
	}
	

	/*************************************************************************
	Function getTable - obtiene la tabla en cuestion
	@param post = parametros enviados al server
	@param url = url a dodne se hace la peticion
	@param template = la forma como estan los datos en la tabla
	************************************************************************/
	var getTable = function(entity,action,mode,parent)
	{
		/* 1) Verify if the table exist*/
		//var pId=$(entity).attr('id');
		var pId = tableId(parent,entity);
		var table = $(parent).find('Table#'+pId);
		if(table.length==0)//If table dont exist, create one
		{
			/* 1.A) Create the Table*/
			DOM = '<table url="'+gUrl+'" id="'+pId+'" page="'+1+'"><thead><tr class="'+gTh+'"></tr></thead></table>';
			$(DOM).appendTo(parent);
			table = $(parent).find('table#'+pId);
			var tableheader = $(parent).find('table#'+pId+' thead.'+gTh)[0];
			var therow = $(tableheader).find('tr')[0];
			var name = $(entity).attr('name');
			var size = $(entity).find('col').length;
			$(entity).find('col').each(function(){
				var dsc = $(this).attr('dsc');
				var type = $(this).attr('type');
				var id = $(this).attr('id');
				var DOM_COL = '<th id="'+id+'" class='+type+'>'+dsc+'</th>';
				$(therow).append(DOM_COL);
			});
			$(table).prepend('<caption>'+name+'</caption>');
			//createTool(tableheader);
			tableMode(table,mode);//insert the table model to the table
			
			/* B) Create the table Footer*/
			var page = $(entity).attr('pagination');
			if((typeof(page)!='undefined'))
			{
				var FOOTER = '<tfoot><tr class="'+gTh+'"><th  colspan="'+size+'">FOOTER</th></tr></tfooter>';
				$(table).append(FOOTER);
			}
		}
		else//Else if aciton=new then delete it
		{
			if(action==gnNew)
			{
				$(table).find('tr').not('.'+gTh).remove();
			}
		}
		return table;
		
	}
	
	var createTool = function(table,mode,entity)
	{
		/*1)Add Tool header if not exists*/
	
		var pId = $(table).attr('id');
		var tableHeader = $(table).find('tr.'+gTh)[0];
		var oldtool = $(tableHeader).find('th.'+gTool)[0];
		if(typeof(oldtool)=='undefined')
		{
			var TOOL_DOM = '<th id="'+gTool+'" class="'+gTool+'"><img src="../../img/tool.gif"/></th>';
			$(tableHeader).append(TOOL_DOM);
		}
		
		/* 2) Create the cell of tools in all rows*/
		var TOOL='';
		var contador=0;
		$(table).find('tr').not('.'+gTh).each(function()
		{
			var row = this;
			var TOOL ='';
			switch(mode)
			{
				case 'none':
					TOOL=parseTool(entity,'disabled',contador);
					break;
				case 'row':
					TOOL=parseTool(entity,'disabled',contador);
					break;
				case 'cell':
					TOOL=parseTool(entity,'disabled',contador);
					break;
				case 'full':
					TOOL=parseTool(entity,'enabled',contador);
					break;
				default:
					TOOL=parseTool(entity,'disabled',contador);
			}
			$(row).append(TOOL);
			contador++;
		});
		/* 3) Adds the actions to the Tool Cell*/
		toolAction(table,entity);
	}

	var toolAction = function(table,entity)
	{
		$(table).find("td.tool input[type='button'].tool,input[type='image'].tool").each(function(){
			$(this).click(function(){
				var val = $(this).attr('id');
				var func = eval(val);
				func(this,entity);
			});
			
		});
	}
	
	var fillTable = function(entity,post,mode,table,offset)
	{
		/*1 Definicion de Variables*/
		var pId	 =	$(entity).attr('id');
		$(table).addClass(mode);

		if (typeof gXml != "undefined")
		{
			// Add Limit if Pagination exist
			var npages = $(entity).attr('pagination');
			if((typeof(npages)!='undefined'))
			{
				npages = parseInt(npages);
				post[gLimit]=npages;
			}
			if((typeof(offset)!='undefined'))
			{
				post[gOffset]=parseInt(offset);
			}
			else
			{
				offset=0;
				post[gOffset]=parseInt(offset)
			}
			var url = $(entity).attr('url');
			$.ajax({
				   url:url,
				   type:'post',
				   async:false,
				   data:post,
				   success:function(json){
						resp = getJSON(json);
						gnResp= resp;// Variable Global de Respuesta
						/*Inquirir el maximo dependiendo de si hay paginado o no*/
						var currentPage = $(table).attr('page');
						var npages = $(entity).attr('pagination');
						var maxim = 0;
						if((typeof(npages)!='undefined'))
						{
							var lastobj = resp[resp.length-1];
							nrows = lastobj['nrows'];
							$(table).attr('nrows',nrows);
							maxim = resp.length-1;

						}
						else
						{
							maxim = resp.length;
						}
						/*2 Parseado por Cada Objeto JSON devuelto por la BD y crear una fila nueva*/
						for(var x=0; x<maxim;x++)
						{
							$(table).append(createRow(resp[x],entity,mode,table));	//Inserta la Fila
							//Añadir el Color Adecuado
							var last = $(table).find('tr:last')[0];
							if($(last).is(':nth-child(even)'))
							{
								$(last).addClass('even');
							}
							else
							{
								$(last).addClass('odd');
							}
						}//for
						
				   }//sucess
			});//ajax	
		}//if
	}//fillTable

	/*************************************************************************
	Function parseCell - Crea la cabecera de una tabla
	@param post = parametros enviados al server
	@param url = url a dodne se hace la peticion
	@param template = la forma como estan los datos en la tabla
	************************************************************************/
	var changeRow = function(evento)
	//var changeRow = function()
	{
		if($(evento.target).is('a'))
		{
			//$.log('Se presioono en a');
		}
		else
		{
			var col = $(this);
			//var col = row;
			var table = $(col).parents('table');
			var url = $(col).parents('table').attr('url');
			getXML(url);
			var entity = $(gXml).find('entity')[0];
			var COLS='';
			/* 1) Si la columna NO es editable cambiarla a editable*/
			
			if($(col).is(':not(.'+gEdit+')'))
			{
				/* 2) Fill the data to insert */
				resp = colSerialize(col);
				/* 3) Create the row */
				$(entity).find('col').each(function(){
					var id = $(this).attr('id');
					var cell = $(col).find('td#'+id);
					var texto = resp[id];
					var type = $(this).attr('type');				
					COLS = COLS + parseRow(id,type,parseCell(table,entity,texto,id,type,'editable'));
				});
				var TOOL = parseTool(entity,'enabled','NaN');
				COLS = '<tr class="'+gEdit+'">'+COLS+TOOL+'</tr>';
				$(col).replaceWith(COLS);
				toolAction(table);
			}
		}
	}	
	/*************************************************************************
	Function parseCol - Devuelve una Columna
	@param post = parametros enviados al server
	@param url = url a dodne se hace la peticion
	@param template = la forma como estan los datos en la tabla
	************************************************************************/
	var createRow = function(json,entity,mode,table)
	{
		/*1 Parsear los textos*/
		var COLS ='';		//String que contiene el texto parseado
		$(entity).find('col').each(function(){	
			var type = $(this).attr('type');
			var sql = $(this).attr('sql');	
			var id = $(this).attr('id');	
			var texto = json[sql];				
			switch(mode)
			{
				case 'none':
					COLS = COLS + parseRow(id,type,parseCell(table,entity,texto,id,type,'uneditable'));
				break;
				
				case 'row':
					COLS = COLS + parseRow(id,type,parseCell(table,entity,texto,id,type,'uneditable'));
				break;
				
				case 'cell':
					COLS = COLS + parseRow(id,type,parseCell(table,entity,texto,id,type,'uneditable'));
				break;
				
				case 'full':
					COLS = COLS + parseRow(id,type,parseCell(table,entity,texto,id,type,'editable'));
				break;
				default: 
					COLS = COLS + parseRow(id,type,parseCell(table,entity,texto,id,type,'uneditable'));
				
			}//switch:mode
		});//each
		

		switch(mode)
		{
			case 'full':
				//return '<tr class="'+gEdit+'">'+COLS+TOOL+'</tr>';
				return '<tr class="'+gEdit+'">'+COLS+'</tr>';
			break;
			default:
				//return '<tr>'+COLS+TOOL+'</tr>';			
				return '<tr>'+COLS+'</tr>';			
		}

	}//function:parseColType
	
	var parseTool = function(entity,mode,contador)
	{	var TOOL='';
		//var id=$(entity).attr('id');
		$(entity).find('tool').each(function(){
			var type = $(this).attr('type');
			var url = $(this).attr('url');
			switch(mode)
			{
				case 'enabled':
					switch(type)
					{
						case 'toolUpdate':
							TOOL=TOOL+'<input type="button" id="'+type+'" class="tool" value="U"/>';				
						break;	
						case 'toolRefresh':
							TOOL=TOOL+'<input type="button" id="'+type+'" class="tool" value="Cambiar"/>';				
						break;
						case 'toolDelete':
							TOOL=TOOL+'<input type="button" id="'+type+'" class="tool" value="D"/>';					
						break;
						default:
							TOOL=TOOL+'<input type="button" id="'+type+'" class="tool" value="D"/>';					
					}
				break;
				
				case 'disabled':
					switch(type)
					{
						case 'toolRefresh':
							TOOL=TOOL+'<input type="button" id="'+type+'" url="'+url+'" class="tool" value="Actualizar"/>';
						break;
						case 'toolUpdate':
							TOOL=TOOL+'<input type="button" id="'+type+'" class="tool" value="U" disabled="disabled"/>';				
						break;
						case 'toolDelete':
							TOOL=TOOL+'<input type="button" id="'+type+'" class="tool" value="D" disabled="disabled"/>';					
						break;
						case 'toolSuperDelete':
							TOOL=TOOL+'<input type="image" id="'+type+'" url="'+url+'" class="tool" src="../../img/bigborrar.gif"/>';					
						break;
						case 'multiButton':
							var sql = $(this).attr('sql');
							var sql_value=gnResp[contador][sql];							
							$(this).find('button').each(function(){		
								var label = $(this).attr('label');
								var value = $(this).attr('value');
								var url = $(this).attr('url');
								var method = $(this).attr('method');
								if(typeof(method)=='undefined')
								{//por default
									method = 'POST';
									//$.log(method);
								}
								if(value==sql_value){
									TOOL=TOOL+'<form method="'+method+'" class="inner" action="'+url+'" name="gopost">';
									TOOL=TOOL+'<input type="button" id="'+type+'" url="'+url+'" value="'+label+'" class="tool tool_button"/>';
									$(this).find('param').each(function(){
										var send= $(this).attr('value');									
										var param_name= $(this).attr('name');										
										var param_id= $(this).attr('id');
										TOOL=TOOL+'<input type="hidden" id="'+param_id+'" name="'+param_name+'" value="'+gnResp[contador][send]+'" />';
									});
									TOOL=TOOL+'</form>';
								}
							});

						break;

						default:
							TOOL=TOOL+'<input type="button" id="'+type+'" url="'+url+'" class="tool" name="'+type+'" value="D" disabled="disabled"/>';					
					}
				break;
			}
			
		});
		return '<td class="'+gTool+'">'+TOOL+'</td>';
	}
	
	/*************************************************************************
	Function parseCell - Crea la cabecera de una tabla
	@param post = parametros enviados al server
	@param url = url a dodne se hace la peticion
	@param template = la forma como estan los datos en la tabla
	************************************************************************/
	var changeCell = function()
	{
		if($(this).is('tr'))
		{
			if($(this).is('.'+gEdit))
			{
				changeRow($(this));
			}
			else
			{	
				changeRow($(this));
			}
		}
		else if($(this).is('td'))
		{
		}
	}
	var parseRow = function(id,type,cell)
	{
		var TD='<td id="'+id+'" type="'+type+'">';
		var TDD='</td>';
		var resp = TD+cell+TDD;
		return resp;
	}
	var parseCell = function(table,entity,texto,id,type,mode)
	{
		
		var COLS='';
		switch(mode)
		{
			case 'editable':
				switch(type)
				{	
				case 'autoincrement':
					var row;
					if($(table).find('tr:last').not('.'+gTh).html())//Sacar el ID de la tabla
					{
						row = parseInt($(table).find('tr:last').children('td#'+id).html());
						row++;
					}
					else//Ponerlo a Cero
					{
						row=1;
					}
					COLS=row;
				break;
				case 'hidden':
					if(texto)
					{
						COLS=texto;
					}
					else
					{
						COLS='HIDDEN';
					}
				break;
				
				case 'text':
					if(texto)
					{
						COLS=texto;
					}
					else
					{
						COLS='';
					}
				break;
				
				case 'textarea':
					if(texto)
					{
						COLS='<textarea>'+texto+'</textarea>';
					}
					else
					{
						COLS='<textarea></textarea>';
					}
				break;
				
				case 'url':/*Es un URL*/
						if(texto)
						{
							var col = $(entity).find('col[id='+id+']')[0];
							var url = $(col).attr('url');
							texto='<a href="'+url+'" id="'+id+'">'+texto+'</a>';
							COLS=texto;
						}
						else
						{
							//texto='<a href="'+url+'">UUURL</a>';
							texto='---';
							COLS=texto;
						}
						break;
				
				case 'date':
					if(texto)
					{
						COLS='<input type="text" class="date" value="'+texto+'" />';
					}
					else
					{
						COLS='<input type="text" class="date" value="" />';
					}
				break;
				
				case 'select'://dos casos A)por ajax B)embebido 
					//Caso de que DATA sea AJAX
	
					var xmlSel = $(entity).find('col#'+id)[0];
					var url = $(xmlSel).attr('url');
					if(typeof(url)=='undefined')
					{	
						var DOM = '<select name="'+id+'" id="'+id+'">"';
						$(xmlSel).find('option').each(function(){		
								var option = $(this);
								var value = $(option).attr('value');
								var name = $(option).attr('name');
								if(String(name)==String(texto))
								{
									DOM = DOM+"<option value='"+value+"' selected='selected'>"+name+"</option>";
								}
								else
								{
									DOM = DOM+"<option value='"+value+"'>"+name+"</option>";
								}
						});
						DOM = DOM + '</select>';
						COLS=COLS+DOM;
					}
					else
					{/*
						var accion = $(data).attr('accion');
						$.ajax({
						url:url,
						type:'POST',
						async: false,
						data:{accion:accion,id:id},
						success:function(dato){
							var resp = eval(dato);
							var DOM = "<p><label>"+desc+"<select name='"+id+"' id='"+id+"'>";
							for(var x in resp)
							{
								var value = resp[x].value;
								var descr = resp[x].desc;
								DOM = DOM+"<option value='"+value+"'>"+descr+"</option>";
							}
							DOM = DOM+"</select></label></p>";
							$(where).find('fieldset').append(DOM);
							$.log('tengo url');*/
					}
				break;
	
				default:
					COLS='DEFAULT:'+type;
				}
			break;//case 'editable'
				
		case 'uneditable':
			switch(type)
			{	
			case 'autoincrement':
				var row;
				if($(table).find('tr:last').not('.'+gTh).html())//Sacar el ID de la tabla
				{
					row = parseInt($(table).find('tr:last').children('td#'+id).html());
					row++;
				}
				else//Ponerlo a Cero
				{
					row=1;
				}
				COLS=row;
				break;
			case 'hidden':/*ID Oculto*/
				if(texto)
				{
					COLS=texto;
				}
				else
				{
					COLS='';
				}
			break;
			
			case 'url':/*ID Oculto*/
				if(texto)
				{

					var col = $(entity).find('col[id='+id+']')[0];
					var url=$(col).attr('url');
					texto='<a href="'+url+'">'+texto+'</a>';
					COLS=texto;
				}
				else
				{
					var col = $(entity).find('col[id='+id+']')[0];
					var url=$(col).attr('url');
					//texto='<a href="'+url+'">URL</a>';
					texto='---';
					COLS=texto;
				}
			break;
			
			default:
				if(texto)
				{
					COLS=texto;
				}
				else
				{
					COLS='';
				}
			break;
		}//SWITCH
				break;
		}
		return COLS;
	}//editableCell

	var colSerialize = function(col){
		/* 1) If it is editable*/
		var obj = new Object();
		if($(col).is('.'+gEdit))
		{
			$(col).find('td').not('.'+gTool).each(function(){
				var type = $(this).attr('type');
				var id = $(this).attr('id');
				switch(type)
				{
					case 'select':
						var sel = $(this).find('select')[0];
						var option = $(sel).find('option:selected')[0];						
						obj[id] = $(option).html();
					break;
					
					case 'textarea':
						var sel = $(this).find('textarea')[0];
						obj[id] = $(sel).html();						
					break;
					
					case 'date':
						var sel = $(this).find('input[type=text]')[0];
						obj[id] = $(sel).val();						
					break;
					
					case 'url':
						//var sel = $(this).find('a')[0];
						var sel = $(this);
						var text = $(sel).html();
						obj[id] = text;						
					break;
					
					default:
						obj[id] = $(this).html();
				}
			});
			return obj;
		}
		/* 2) If is not editable*/
		else
		{
			$(col).find('td').not('.'+gTool).each(function(){
				var type = $(this).attr('type');
				var id = $(this).attr('id');
				switch(type)
				{
					case 'url':
						var sel = $(this).find('a')[0];
						var text = $(sel).html();
						obj[id] = text;						
					break;
					
					default:
						obj[id] = $(this).html();
				}
			});
			return obj;
		}
		/*Si la Tabla no es Editable*/
	}//col Serialize
	
/*********************************************************************
ACCIONES - Funciones que controlan las acciones que se hacen
********************************************************************/
	/*****************************************************************************************
	Function rowEdit - Transforma un row en row editable
	@param xmlUrl = direccion del xml
	*****************************************************************************************/
		
	var toolOk = function(button){
		var COLS='';
		var row= $(button).parents('tr');
		var table = $(row).parents('table');
		var objData = colSerialize(row);
		var url = $(table).attr('url');
		getXML(url);
		entity = $(gXml).find('entity')[0];
		$(row).find('td').not('.'+gTool).each(function(){
			var id = $(this).attr('id');
			var type = $(this).attr('type');
			var texto = objData[id];
			COLS = COLS + parseRow(id,type,parseCell(table,entity,texto,id,type,'uneditable'));
		});
	
		//var TOOL = parseTool(entity,'disabled');
		//if(table).find('td.');

		COLS = '<tr>'+COLS+TOOL+'</tr>';
		$(row).replaceWith(COLS);
		var mode = tableMode(table);
		
		toolAction(table);
		lastAdjust(entity,table);
	}
	
	var toolUpdate = function(button){
		var row = $(button).parents('tr');
		var table = $(row).parents('table');
		var url = $(button).attr('url');
		var obj = colSerialize(row);
		//2) Añadir variables ocultas
		var sd = $(entity).find('tool[type=toolUpdate]')[0];
		$(sd).find('param').each(function(){
			var param = this;
			var name=$(param).attr('name');
			var value=$(param).attr('value');
			obj[name] = value;			
		});
		$.ajax({
			url:url,
			type:'post',
			async:false,
			data:obj,
			success:function(){
				parentEvent(button,gToolUpdate);
			}
		});
		$(this);
	}
	var toolRefresh = function(button,entity){
		var row = $(button).parents('tr');
		var table = $(row).parents('table');
		var url = $(button).attr('url');
		var obj = colSerialize(row);
		//2) Añadir variables ocultas
		var sd = $(entity).find('tool[type=toolRefresh]')[0];
		$(sd).find('param').each(function(){
			var param = this;
			var name=$(param).attr('name');
			var value=$(param).attr('value');
			obj[name] = value;			
		});
		//$.log(obj);
		$.ajax({
			url:url,
			type:'post',
			async:false,
			data:obj,
			success:function(){
				parentEvent(button,gToolRefresh);
				$(row).remove();
			}
		});
	}	
	var toolDelete = function(){
		$(this);
	}

	var multiButton = function(button,entity){
		var form = $(button).parents('form');		
		$(form).submit();
		
		$.log(button);
		
		//var DOM= '<form type=POST url="'+url+'" name="gopost">';
		//$.log(DOM);		
		//parentEvent(button,gToolSuperDelete);

	}


	var toolSuperDelete = function(button,entity){
		var row = $(button).parents('tr');
		var table = $(row).parents('table');
		var url = $(button).attr('url');
		var obj = colSerialize(row);
		//2) Añadir variables ocultas
		var sd = $(entity).find('tool[type=toolSuperDelete]')[0];
		$(sd).find('param').each(function(){
			var param = this;
			var name=$(param).attr('name');
			var value=$(param).attr('value');
			obj[name] = value;			
		});
		$.ajax({
			url:url,
			type:'post',
			async:false,
			data:obj,
			success:function(){
				parentEvent(button,gToolSuperDelete);
				$(row).remove();
			}
		});
	}
	var pagination = function(a,entity,post,mode,table)
	{
		var gotopage = $(a).html();
		$(table).attr('page',gotopage);
		var pagination = parseInt($(entity).attr('pagination'));
		var offset = parseInt(pagination)*(gotopage-1);
		$(table).find('tr').not('.'+gTh).remove();
		fillTable(entity,post,mode,table,offset);
		createTool(table,mode,entity);
		createPagination(entity,table,post,mode);
		lastAdjust(entity,table);
		return false;
	}

	
/*****************************************************
TOOL AUXILIARES - Funciones Auxiliares para autotable
****************************************************/

	/*****************************************************************************************
	Function getXML - Obtiene la cabecera de una tabla, si ya existe guardarlo en memoria
	@param xmlUrl = direccion del xml
	*****************************************************************************************/
	var	getXML = function(xmlUrl)
	{
		if(xmlUrl!=gUrl)
		{
			gUrl = xmlUrl;
			$.ajax({
				type: "POST",
				url: xmlUrl,
				async: false,
				dataType:'xml',
				success:function(data){
					gXml=data;
				},//sucess
				error:function(data){
					$.log('Error en AJAX del XML');
				}
			});//ajax
		}
		else
		{

		}

	}
	/*****************************************************************************************
	Function tableMode - GET y SET el mdo de una tabla
	@param xmlUrl = direccion del xml
	*****************************************************************************************/
	var tableMode = function(table,mode)
	{
		if(typeof(mode)!='undefined')
		{
			var modo = $(table).attr('mode',mode);
			
		}
		
		return $(table).attr('mode');
	}
	var tableId = function(parent,entity)
	{
		var id='';
		if((typeof(parent)!='undefined')&&(typeof(entity)!='undefined'))
		{
			pId = $(parent).attr('id');
			tId = $(entity).attr('id');
			return id= pId+tId;
		}
		else
		{

			$.log('No se puede encontrar el id de la tabla');//JSK_MSG	
			return id='undefined - getTableId';
		}
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
		if($(dom).is('table'))
		{
			form = dom;
		}
		else
		{
			form = $(dom).parents('table');
		}
		parent = $(form).parent();
		$(parent).trigger(evento,data);
		//$.log(parent);
		//$.log('EVENTO:'+evento);
	}

 })(jQuery);