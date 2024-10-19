var g_sa_action_url = g_game_url+'ajax/ajaxMainSendArmies.php?random=';//direccion de accion [TODO]Hacer un json precharge de esto
var g_sa_id_army_prefix = 'saiap'; //send armies id  army prefix 5 letras

$(document).ready(function() { 
	ajaxBattleAvailableArmies();
	ajaxAvailableRegions();
	//Cuando hacen click down
	$('#AvailableArmies .button.down').live('click',function(){
		prepareArmy(this);
	});
	$('#ArmiesToSend .button.up').live('click',function(){
		desprepareArmy(this);
	});
	$('#BtnSendArmies').click(function(){ajaxSendArmies()});
	
});

function ajaxBattleAvailableArmies(){
	json_ok=true;
	$.ajax({ //Envio hacia el server
	type: "POST",
	url: g_sa_action_url,
	cache: false,
	data: "action=getBattleAvailableArmies",
	success: function(resp_raw){
			try{
				var armies = json_parse(resp_raw);
				 
				// var armies = eval(resp_raw);
			}
			catch(er){
				json_ok=false;
			}
			if(json_ok){
				fillSendedArmies(armies['sended'],$('#SendedArmies'));
				fillAvailableArmies(armies['sendable'],$('#AvailableArmies'));
				$('#ArmiesToSend ul').html('');	
			}
			else{
				
			}
			fillSendedArmies(armies['sended'],$('#SendedArmies'));
			fillAvailableArmies(armies['sendable'],$('#AvailableArmies'));
		}
	});
}
function ajaxSendArmies(){
	var json_ok = true;
	var armies = new Array;
	$('#ArmiesToSend ul').find('li').each(function(){
		armies.push(getArmyId(this));
	});
	var region_id = $('#selRegion').val();
	$.ajax({ //Envio hacia el server
	type: "POST",
	dataType:'json',
	url: g_sa_action_url,
	cache: false,
	data: {action:'sendArmies',armies:armies.toString(),region_id:region_id},
	success: function(resp_raw){
			try{
				 //var armies = eval(resp_raw);
				 var armies = resp_raw;
			}
			catch(er){
				json_ok=false;
				//console.log(er);			
			}
			if(json_ok){
				fillSendedArmies(armies['sended'],$('#SendedArmies'));
				fillAvailableArmies(armies['sendable'],$('#AvailableArmies'));
				$('#ArmiesToSend ul').html('');	
			}
			else{
				//console.log('Se muestre el error');
				var DOM = '<div class="help_message">Seleccione al menos una unidad</div>';
				$('#sa_helpWrapper').html(DOM);
				$('.help_message').show('slow');
			}
		
		}
		
	});
}

function ajaxAvailableRegions(){
	$.ajax({ //Envio hacia el server
	type: "POST",
	url: g_sa_action_url,
	cache: false,
	data: "action=getAvailableRegions",
	dataType:'json',
	success: function(regions_raw){
			fillAvailableRegions(regions_raw);
		}
	});
}
function fillAvailableRegions(regions){
	var SEL_DOM = '';
	for(var region in regions){
		SEL_DOM = SEL_DOM + "<option value='"+regions[region]['id']+"'>"+regions[region]['name']+"</option>";
	}
	$('#selRegion').html(SEL_DOM);
	$('#SelSeeRegion').html(SEL_DOM);
}
function fillAvailableArmies(armies,parent){
	var DOM = '';
	for(var army in armies){
		LI_DOM =  '<li id="'+g_sa_id_army_prefix+armies[army]['id']+'" class="unit"><div class="data">'+
					'<div class="level">nivel<span>'+armies[army]['level']+'</span></div>'+
					'<img src="img/unit/'+armies[army]['creature_id']+'.png"/>'+
					'<h4>'+armies[army]['name']+'</h4>'+
					'<h4 class="region">'+armies[army]['region_name']+'</h4></div>'+
					'<a href="#void" class="button down" ></a></li>';
		DOM = DOM + LI_DOM;
	}
	$(parent).children('ul').html('').append(DOM);
}
function fillSendedArmies(armies,parent){
	var DOM = '';
	for(var army in armies){
		LI_DOM =  '<li id="'+g_sa_id_army_prefix+armies[army]['id']+'" class="unit"><div class="data">'+
					'<div class="level">nivel<span>'+armies[army]['level']+'</span></div>'+
					'<img src="img/unit/'+armies[army]['creature_id']+'.png"/>'+
					'<h4>'+armies[army]['name']+'</h4>'+
					'<p>de <span class="region">'+armies[army]['from_region_name']+'</span></p>'+
					'<p>a <span class="region">'+armies[army]['to_region_name']+'</span></p>'+
					'<div class="cd_wrapper">'+armies[army]['seconds']+'</div></div>';
		DOM = DOM + LI_DOM;
	}
	$(parent).children('ul').html('').append(DOM);
	
	$(parent).find('li').each(function(){
		var armyId = getArmyId(this);
		//if(armies[armyId])
		var sec = $(this).find('.cd_wrapper').text();
		var coundown_div = $(this).find('.cd_wrapper'); 
		$(coundown_div).countdown({
					until: '+' + sec + 's',
					format: 'HMS',
					compact: true,
					onExpiry: function(){ajaxBattleAvailableArmies();}
		});
	});
	
}

function getArmyId(li){
	var id_raw = $(li).attr('id');
	return id_raw.substr(g_sa_id_army_prefix.length);
}

function prepareArmy(a){
	var li = $(a).parent('li');
	$(li).find('.button.down').remove();
	$(li).prepend('<a href="#void" class="button up" ></a>');
	$(li).appendTo($('#ArmiesToSend ul'));
}
function desprepareArmy(a){
	var li = $(a).parent('li');
	$(li).find('.button.up').remove();
	$(li).append('<a href="#void" class="button down" ></a>');
	$(li).appendTo($('#AvailableArmies ul'));
}
