var g_debug_url =g_game_url+'ajax/ajaxDebug.php';//direccion de reaccion
$(document).ready(function(){
        $('a#ReiniciarBatalla').click(reiniciarBatalla);
	$('#RevivirUnidadesWrapper').click(revivirUnidades);
	$('#RegenerateEnergy').click(regenerateEnergy);
	$('#TerminarAIWrapper').click(terminarTurnoAI);
        $('#FingerOfDeathWrapper').click(fingerOfDeath);

        tooltip( $('#RevivirUnidadesWrapper'),  'Revive a todas las unidades en el mapa','bottom');
        tooltip( $('#FingerOfDeathWrapper'),  'Asesina la unidad seleccionada cuando esta activo. Haz click para activarlo','bottom');
        tooltip( $('#TerminarAIWrapper'),  'Termina el turno de la unidad actual','bottom');
        tooltip( $('#RegenerateEnergyWrapper'),  'Regenera la energia a todas las tropas, incluyendo enemigos','bottom');
        tooltip( $('#CancelWrapper'),  'Deselecciona lo que tengas seleccionado','bottom');
        tooltip( $('#ReiniciarBatalla'),  'Reinicia la batalla, reviviendo a las unidades y moviendolas a lugares separados','bottom');
        

	//Lock the Ajax
  $('body').ajaxSend(function(e, xhr, settings) {
    if (settings.url == g_debug_url) {
      //console.info(settings,'Se envio un AJAX region.js');
      g_ajax_region_lock=true;
    }
  });
    
  //Unlock the Ajax
  $('body').ajaxComplete(function(e, xhr, settings) {
    if (settings.url == g_debug_url) {
      //console.info(settings,'Se envio un AJAX region.js');
      g_ajax_region_lock=false;
    }
  });
  
  
  //Unlock the Ajax
  $('body').ajaxError(function(e, xhr, settings) {
    if (settings.url == g_debug_url) {
      console.error(settings,'Error en el envio de datos');
      g_ajax_region_lock=false;
    }
  });  

});


function fingerOfDeath(){
  
    $('body').css('cursor', 'crosshair');  
    $('.army').css('cursor', 'cell'); 
    
    $('body').bind('click.finger_of_death',function(event){
        if( $(event.target).is('#FingerOfDeathWrapper') || $(event.target).is('#FingerOfDeath')  ){
        }
        else{
            $('body').removeAttr('style');
            $('.army').removeAttr('style');
            $('body').unbind('click.finger_of_death');
            $('.army').unbind('click.finger_of_death');
            
            if($(event.target).is('.army')){
                var army_id = getId(event.target);
                var region_id = xeno['region']['id'];
                var battle_id = xeno['battle']['id'];
                
                var icon = renderWaitIcon($(event.target).parent(),false,false);
                waitMsg();
                $.ajax({
                  type:'post',
                  data:{'ajax_action':'killunit','army_id':army_id,'region_id':region_id,'battle_id':battle_id},
                  url:g_debug_url,
                  dataType:'json',
                  error:function(XMLHttpRequest, textStatus, errorThrown){
                    destroyWaitIcon(icon);
                    console.log(XMLHttpRequest,'callExecAction error');
                    console.log(textStatus,'callExecAction error');
                    console.log(errorThrown,'callExecAction error');
                    var error = [];
                    error['text']= 'Error de Coneccion';
                    error['type']= 'error';
                    msg(error);
                    continueMsg();
                  },
                  success:function(data){
                      destroyWaitIcon(icon)
                      processAjaxAnswer(data,true); //Forzamos
                  }
                });
                
            }
        }
        
    })    
}

function reiniciarBatalla(){
    waitMsg();
    var region_id = xeno['region']['id'];
    var battle_id = xeno['battle']['id'];
    $.ajax({ //Envio hacia el server
        type: "POST",
        dataType:'json',
        url: g_debug_url,
        cache: false,
        data: {'ajax_action':'reiniciarBatalla','region_id':region_id,'battle_id':battle_id},
        error:function(XMLHttpRequest, textStatus, errorThrown){
                    destroyWaitIcon(icon);
                    console.log(XMLHttpRequest,'callExecAction error');
                    console.log(textStatus,'callExecAction error');
                    console.log(errorThrown,'callExecAction error');
                    var error = [];
                    error['text']= 'Error de Coneccion';
                    error['type']= 'error';
                    msg(error);
                    continueMsg();
                  },
        success: function(data){processAjaxAnswer(data,true);}	
    });
}

function terminarTurnoAI(){
   waitMsg();
   var region_id = xeno['region']['id'];
   var battle_id = xeno['battle']['id'];
   $('#Clockout').countdown('destroy');
   $.ajax({
      type:'post',
      data:{'ajax_action':'endai','region_id':region_id,'battle_id':battle_id,'ox':0,'oy':0,'tx':0,'ty':0},
      url:g_debug_url,
      dataType:'json',
      error:function(XMLHttpRequest, textStatus, errorThrown){
        console.log(XMLHttpRequest,'callExecAction error');
        console.log(textStatus,'callExecAction error');
        console.log(errorThrown,'callExecAction error');
        var error = [];
        error['text']= 'Error de Coneccion';
        error['type']= 'error';
        msg(error);
        continueMsg();
      },
      success:function(data){processAjaxAnswer(data);}
    });
  
}

function revivirUnidades(){
  waitMsg();
   var region_id = xeno['region']['id'];
   var battle_id = xeno['battle']['id'];
	$.ajax({ //Envio hacia el server
		type: "POST",
		dataType:'json',
		url: g_debug_url,
		cache: false,
		data: {'ajax_action':'revivirUnidades','region_id':region_id,'battle_id':battle_id},
                error:function(XMLHttpRequest, textStatus, errorThrown){
                    destroyWaitIcon(icon);
                    console.log(XMLHttpRequest,'callExecAction error');
                    console.log(textStatus,'callExecAction error');
                    console.log(errorThrown,'callExecAction error');
                    var error = [];
                    error['text']= 'Error de Coneccion';
                    error['type']= 'error';
                    msg(error);
                    continueMsg();
                  },
		success: function(data){processAjaxAnswer(data,true);}	
	});
}
function regenerateEnergy(){
  var region_id = xeno['region']['id'];
  var battle_id = xeno['battle']['id'];
  waitMsg();
	$.ajax({ //Envio hacia el server
		type: "POST",
		dataType:'json',
		url: g_debug_url,
		cache: false,
		data: {'ajax_action':'regenerateEnergy','region_id':region_id,'battle_id':battle_id},
                error:function(XMLHttpRequest, textStatus, errorThrown){
                    destroyWaitIcon(icon);
                    console.log(XMLHttpRequest,'callExecAction error');
                    console.log(textStatus,'callExecAction error');
                    console.log(errorThrown,'callExecAction error');
                    var error = [];
                    error['text']= 'Error de Coneccion';
                    error['type']= 'error';
                    msg(error);
                    continueMsg();
                  },
		success: function(data){
                    selectActualArmy();
                    processAjaxAnswer(data);
                    
                }	
	});
}

function profiler(ajax){
	var DOM = "<li>TimeDiff:"+ajax['profiler']+"</li>";
	$('div#Debug ul').prepend(DOM);
}	

function callDebug(index){
    
  if(!isDefined(index)){
      var index=30;
  }  
    
  var region_id = xeno['region']['id'];
  var battle_id = xeno['battle']['id'];
    $.ajax({
                  type:'post',
                  data:{ajax_action:'prueba','region_id':region_id,'battle_id':battle_id,index:index},
                  url:g_debug_url,
                  dataType:'json',
                  success:function(data){
                     console.log(data);
                     lockHiddenStatus();
                  }
                });
}
		
		
