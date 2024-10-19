var debug = '';
var g_image_url = g_game_url+'img/';
var g_region_url = g_game_url+'ajax/ajaxRegion.php?random=';

var g_ajax_region_lock=false;//lock de ajax, no se puede enviar un ajax hasta que este completo
var g_ajax_hidden_status_lock=false;//lock de ajax, no se puede enviar un ajax hasta que este completo

var last_xeno;

var g_tile_size = 60;

var g_first_battle_phase = false;

var g_xhr_hidden_status = false; //Variable Global que contiene la ultima llamada a mi AJAX

$(document).ready(function(){
    if(validateInitialRegionXeno()){
        
       renderRegionMap();
        
        //Boton de Opciones de Cheats
        $('#Options').hide();
        $('#CheatsOn').click(function(){
            $('#Options').toggle('fast');
        });

       //Ajustes iniciales al mapa para que las animaciones funcionen bien 
        $('#BattleMap').width(g_tile_size * xeno['region']['x']);
        $('#BattleMap').height(g_tile_size * xeno['region']['y']);

       last_xeno = xeno;
      //Inicializar jugador seleccionado
      setSelectedArmyId(false);// = false = No hay unidad seleccionada
      xeno['time'] = iGlobalTime; //tiemplo de jclock se calcula
      //$('td img.army').live('click',function(){armyManager(this)});
      $('td').live('click',function(){actionManager(this)});

    }
});

/***********************************************************
 * PRINCIPAL
 **********************************************************/
function validateInitialRegionXeno(){
    if(validateInitialXeno())
    {
         return true;
    }
    else{
        return false;
    }
}

function unlockHiddenStatus(){
  //console.info('Se desbloqueo el candado de AJAX');
  g_ajax_hidden_status_lock=false;
}
function lockHiddenStatus(){
  //console.info('Se bloqueo el candado de AJAX');
  g_ajax_hidden_status_lock=true;
}

function getPassedTime(){
  var subtime = iGlobalTime - xeno['time'];//Cuanto tiempo a pasado
  newtime = xeno['battle']['battle_seconds']- subtime;
  return newtime;
}
/**********************************************************
 * Managers
 *********************************************************/
function phaseManager(forced,battleStatus){
     
    //Necesitamos forzarla? 
    if(forced==false || !isDefined(forced) ){
        forced = doWheNeedToForce();
    }
    
      var phase = xeno['battle']['phase'];
      var timer_wrap = $('#Clockout');
      switch(phase){
        /***********************************************************
        * TACTICA
        ************************************************************/
        case 'T':

          if(forced){
             var player_id = xeno['player']['id'];
             console.log(player_id,"region.js phaseManager");
             if(isDefined(xeno['battle']['player_status'][player_id])){//Preguntar si existe el jugador en el planeta, quizas solo este observandolo
                if(xeno['battle']['player_status'][player_id]['phase']=='T'){
                    DOM ='<div class="inner"><a class="button" id="FinalizarClockOut" href="#100">Finalizar Fase Tactica</a></div>';
                    
                 }
                 else{
                   DOM = '<span id="FinalizarClockOut">Esperando Otros Jugadores</span>';
                   //DOM = '';
                 }
             }
             else{
                 DOM = '<span id="FinalizarClockOut">Observando la Region</span>';
             }
              
             renderCountdown(timer_wrap,getPassedTime(),callHiddenStatus);
             $('#PhaseTitle').html('Fase Tactica');//Cambiar el titulo
             var button_wrap= $('#BattlePhasesActions');
             button_wrap.html(DOM);
             $('#FinalizarClockOut').click(function(){
                 callEndTacticalPhase();
             });
             fillPlayersPhases();
             fillArmiesInMap();
             $('#BattleTurnsWrapper').hide();
         }
         
         if(arePlayersReadyToBattle()){
           callHiddenStatus();
         }
         unlockHiddenStatus();
         //Si todos los jugadores llegaron a Battle , si no esperar 10 segundos
        break;

        /***********************************************************
        * BATALLA
        ************************************************************/
        case 'B':
         
            //SI ACTUALIZAMOS LA PARTE IMPORTANTE
            if(forced){//No siempre se actualiza la interfaz
              
              $(timer_wrap).countdown('destroy');//por si las webs
              
                if( (isDefined(battleStatus)) && (isDefined(battleStatus.hasStarted)) && (battleStatus.hasStarted)   ){
                    textAdvice('Fase de Batalla');
                    $('#BattlePhasesWrapper').html('');
                    $('#FinalizarClockOut').remove();
                }
                $('#ToggleHistorial').show();
                $('#PhaseTitle').html('Fase Batalla');
                
                
                //Animacion Ocultar y despues eliminar el ultimo
                //$('#BattleTurnsWrapper').html('');
                var dat = $('.turn')[0];
                $(dat).hide(1000);
                
                var lastId = getLastArmyId();
                if(lastId){
                    fillArmyInfo(lastId);
                }
                reactionManager(xeno['movement'],function(){
                    fillArmiesTurn();
                    fillArmiesInMap();
                    fillHistorial();
                    
                    var id = getActualArmyId();
                    selectArmy(id);
                    renderCountdown($(timer_wrap),getPassedTime(),callHiddenStatus);
                    $('#BattleTurnsWrapper').show();
                    $(document).trigger('phase.battle.forced');
                    unlockHiddenStatus();
                });
            }
            else{
                if( (isDefined(battleStatus)) && (isDefined(battleStatus.hasWarning)) && (battleStatus.hasWarning)   ){
                    var id = getActualArmyId();
                    selectArmy(id);
                    unlockHiddenStatus();
                }
                else{
                    unlockHiddenStatus();
                }
            }
        
        
        break;
        
        default:
        
        case 'E':
           $('#ToggleHistorial').show();
            if(forced){
                $(timer_wrap).countdown('destroy');
               //$('#FinalizarTurno').remove();//Eliminar el boton de batalla
               
                if(typeof(xeno['movement']) != 'undefined'){//Para que rayos sirve esto?, ya se
                    // si no hay movimiento entonces ya se paso de turno, mejor pasarle una variable
                    // de battle status 
                    var lastId = getLastArmyId();
                    if(lastId){
                        fillArmyInfo(lastId);
                    }
                    
                    reactionManager(xeno['movement'],function(){
                        fillArmiesInMap();
                        
                        fillHistorial();
                        setTimeout(function(){
                            if( (isDefined( battleStatus)) && (isDefined(battleStatus.hasEnded)) && (battleStatus.hasEnded)){
                                fillArmiesTurn();
                                selectArmy(getActualArmyId()); //Realmente seleccionara a la unidad actualmente seleccionada
                                textAdvice('Fin de Batalla');
                            }
                            else{
                                fillArmiesTurn();
                                if( (isDefined(battleStatus)) && (isDefined(battleStatus.initial)) && (battleStatus.initial)   ){
                                    panToActiveColonyOrArmy();// Solo paneamos si no queda otra cosa que hacer
                                }
                                
                            }
                            unlockHiddenStatus();
                        }, 1000, true);//Retrasa todo un segundo
                        
                        
                        
                    });
                }
                else{
                    fillArmiesInMap();
                    fillArmiesTurn();
                    unlockHiddenStatus();
                    if( (isDefined(battleStatus)) && (isDefined(battleStatus.initial)) && (battleStatus.initial)   ){
                        panToActiveColonyOrArmy(); // Solo paneamos al comienzo
                    }
                    
                    
                }
               $('#PhaseTitle').html('');//Cambiar el titulo
               $('#BattleReInforcementTitle').html('Resumen Armies');
               
            }//No siempre se actualiza la interfaz
            else{
               unlockHiddenStatus();//Creo que mejor si, para reinforcements 01ene2013 Jeeba
            }
        break;

      }
      
      //Impresion de Refuerzos
      var reinfo = xeno['battle']['sended'];
      var wrapper = $('#BattleReinforcement');
      var region = xeno['region'];
      fillReinforcement(region,reinfo,wrapper);     
      if(forced){
          $(document).trigger('phase.manager.forced');
      }
  
}

//Indicamos si necesitamos forzar a force, por ejemplo si existen nuevos armies
function doWheNeedToForce(){
    ///////////////////////////////////////////////////////////////////////////
    //Verificaciones del lado tactico
    ///////////////////////////////////////////////////////////////////////////   
    var battle = xeno['battle'];
    var oldbattle = last_xeno['battle'];  
  
    //1ra Comprobacion Existen un numero diferente de armies          
    if(xeno.armies && !last_xeno.armies){// Significa que no existe xeno.armies
        return true;
    }
    
    if(xeno.armies && last_xeno.armies && _.size(xeno.armies) != _.size(last_xeno.armies) ){//n Si existe los xenos y son diferentes en armies
        return true;
    }
    else{//[OJO]Si añadimos mas comprobaciones mover este else al final
        return false;
    }
    
}


function reactionManager(movement,callback){
        //console.log(movement,'region.js->reactionManager');
        if(movement!=false){
            //console.log(movement,'region.js->reactionManager ENTRO');
            switch(jQuery.trim(movement['action_id'])){
                    case '1': //Mover
                            var tile_in = getTile();
                            var army_in = getArmyInCoord(movement['x_in'],movement['y_in']);
                            var tile_out = getTile(movement['x_out'],movement['y_out']);
                            //console.log(army_in,'reactionManager->armyin');
                            $(army_in).effect("transfer", {to:tile_out}, 1500,callback);

                            break;
                    case '2': //Atacar
                            var army_id = getId(getArmyInCoord(movement['x_in'],movement['y_in']));
                            var enemy_id = getId(getArmyInCoord(movement['x_out'],movement['y_out']));
                            eAttack(army_id,enemy_id,movement,callback);
                            break;

                    case '4': //Atacar
                            var army_id = getId(getArmyInCoord(movement['x_in'],movement['y_in']));
                            var enemy_id = getId(getArmyInCoord(movement['x_out'],movement['y_out']));
                            eRangeAttack(army_id,enemy_id,movement,callback);
                            break;

                    case '9': //Canon Solar
                            var tileIn = getTile(movement['x_in'],movement['y_in']);
                            //[TODO] Mover getTile a otro js
                            //console.log(movement,'region.js->reactionManager Artilleria');
                            var xIn =movement['x_in'];
                            var yIn =movement['y_in'];
                            var xOut =movement['x_out'];
                            var yOut =movement['y_out'];
                            eRay(tileIn,xIn,yIn,xOut,yOut,callback);
                            break;
                    case '10': //Mover Rapidamente

                           var tile_in = getTile();
                            var army_in = getArmyInCoord(movement['x_in'],movement['y_in']);
                            var tile_out = getTile(movement['x_out'],movement['y_out']);
                           // console.log(army_in,'reactionManager->armyin');
                            $(army_in).effect("transfer", {to:tile_out}, 1500,callback);
                            break;

                    case '11': //Mover Lentamente
                            var tile_in = getTile();
                            var army_in = getArmyInCoord(movement['x_in'],movement['y_in']);
                            var tile_out = getTile(movement['x_out'],movement['y_out']);
                           // console.log(army_in,'reactionManager->armyin');
                            $(army_in).effect("transfer", {to:tile_out}, 1500,callback);
                            break;

                    default:
                            callback();
                            //$.log('DEFAULT REACTION:'+movement['action_id']+'WTF');
                            break;
            }
        }
        else{
           //console.log(movement,'region.js->reactionManager NO ENTRO');
           callback();
        }
	
}


function actionManager(target_tile){ 
  armies = xeno['armies'];
  //Si se selecciono un pathable
    //Si existe una accion seleccionada
      //Si el Wrapper tiene Coolddown
  //Sino Si se selecciono un tile con unidad
  //if ($(target_tile).is('.path')) {
  if (isPathable(target_tile)) {
    // Obtener Accion
    var action_id = getActualActionId();
    if((action_id!=-1))//Existe una accion
    {
      //Obtenemos el tile de origen
     var selected_army_id = getSelectedArmyId();
     var selected_army = getArmy(selected_army_id);
     var origin_tile = $(selected_army).parent('td');
    resetArmyActions();
    callExecAction(action_id,origin_tile,target_tile);
    //ajaxAction(target_tile);
    }
    else
    {
      message('Accion No Seleccionada','warning');
    }
  }
  else {
    if ($(target_tile).is(":has('img.army')")) {
        var army = $(target_tile).find('img.army');
        var armyId = getId(army);
        selectArmy(armyId);
    }
    else
    {
      message('No se puede realizar una accion en este tile','warning');
    }
  }
  
}
/*************************************************
 * Callers
 ************************************************/
 function callExecAction(action_id,origin_tile,target_tile){
   if(g_xhr_hidden_status){ //Derrepente esto se llama primero, antes que un hidden
       g_xhr_hidden_status.abort();
   }
   
   waitMsg();
   $('#ArmyActionsWrapper').hide();//Evitar que clickeen mas en las acciones
   var tx = getId(target_tile);//Objetivo
   var ty = getId($(target_tile).parents('tr'))//Objetivo;
   //var icon = renderWaitIcon(target_tile,false,false);
   var icon = renderWaitActionIcon(target_tile,action_id);
   //Gracias al error de Mozilla no podemos usar css:top en un absolute dentro de una tabla
   /*$(icon ).position({
    of: target_tile,
    my: ('center'),
    at: ('center')
    });*/

   //console.log(icon,'renderWaitIcon icon');
   //console.log(target_tile,'renderWaitIcon targetTile');
   var ox = getId(origin_tile);//Objetivo
   var oy = getId($(origin_tile).parents('tr'))//Objetivo;
   //console.log('tx['+tx+'] ty['+ty+'] ox['+ox+'] oy['+oy+']');
   var region_id = xeno['region']['id'];
   var battle_id = xeno['battle']['id'];
   
   //Terminar el reloj
   $('#Clockout').countdown('destroy');
   console.log("region.js->callExecAction");
   $.ajax({
      type:'post',
      cache:false,
      data:{'ajax_action':'action','action_id':action_id,'region_id':region_id,'battle_id':battle_id,'ox':ox,'oy':oy,'tx':tx,'ty':ty},
      url:g_region_url,
      dataType:'json',
      error:function(XMLHttpRequest, textStatus, errorThrown){
          //console.log("hubo un error en el AJAX","callExecAction region.js");
          destroyWaitIcon(icon);
          processAjaxError(XMLHttpRequest, textStatus, errorThrown);
      },
      success:function(data){
          //console.log("hubo un success en el AJAX","callExecAction region.js");
          destroyWaitIcon(icon);
          processAjaxAnswer(data);
      }
    })
 }
 
function callTutorialOn(){
   var region_id = xeno['region']['id'];
var parent = $('#TutorialOff').parent();
var icon = renderSmallWaitIcon(parent,false);
  $.ajax({
      type:'post',
      cache:false,
      data:{'ajax_action':'tutorial_on','region_id':region_id},
      //data:{'ajax_action':'action','region_id':region_id,'battle_id':battle_id,'ox':ox,'oy':oy,'tx':tx,'ty':ty},
      url:g_region_url,
      dataType:'json',
      error:function(XMLHttpRequest, textStatus, errorThrown){
          destroyWaitIcon(icon);
          processAjaxError(XMLHttpRequest, textStatus, errorThrown);
      },
      success:function(data){
          
          location.reload();

          
      }
    })
}



function callTutorialOff(){
   var region_id = xeno['region']['id'];
var parent = $('#TutorialOn').parent();
var icon = renderSmallWaitIcon(parent,false);
  $.ajax({
      type:'post',
      cache:false,
      data:{'ajax_action':'tutorial_off','region_id':region_id},
      //data:{'ajax_action':'action','region_id':region_id,'battle_id':battle_id,'ox':ox,'oy':oy,'tx':tx,'ty':ty},
      url:g_region_url,
      dataType:'json',
      error:function(XMLHttpRequest, textStatus, errorThrown){
          destroyWaitIcon(icon);
          processAjaxError(XMLHttpRequest, textStatus, errorThrown);
      },
      success:function(data){ 
          location.reload();
          
      }
    })
}



 function ajaxbusymsg(){
   var ms = [];
   ms['text']= 'Esperando Mensaje de Retorno';
   ms['type']= 'warning';
   msg(ms);
 }
 

 function callEndTacticalPhase(){
     
if(g_xhr_hidden_status){ //Derrepente esto se llama primero, antes que un hidden
       g_xhr_hidden_status.abort();
   }
     
   //console.log('callEndTacticalPhase');
   //console.log('Los jugadores estan listos para la batalla2','callEndTacticalPhase');
   /*console.groupCollapsed('callEndTaticalPhase');
   console.groupEnd();*/

   //if(!g_ajax_region_lock){  
   
       waitMsg();
       var region_id = xeno['region']['id'];
       var battle_id = xeno['battle']['id'];
       $('#Clockout').countdown('destroy');

       $.ajax({
          type:'post',
          cache:false,
          data:{'ajax_action':'tactical','region_id':region_id,'battle_id':battle_id},
          url:g_region_url,
          dataType:'json',
          error:function(XMLHttpRequest, textStatus, errorThrown){processAjaxError(XMLHttpRequest, textStatus, errorThrown);},
          success:function(data){
            destroyWaitMsg();
            processAjaxAnswer(data,true);
            }
        });
 }
 function callEndMyBattleTurn(){
if(g_xhr_hidden_status){ //Derrepente esto se llama primero, antes que un hidden
       g_xhr_hidden_status.abort();
   }
   $('#ArmyActionsWrapper').hide();
    //console.log('callEndMyBattleTurn');  
    var id = getActualArmyId();
    var selected_army = getArmy(id);
    var origin_tile = $(selected_army).parent('td');
    /*console.groupCollapsed('callEndMyBattleTurn');
    console.trace('AJAX','callEndMyBattleTurn');
    console.info(origin_tile,'callEndMyBattleTurn');
    console.groupEnd();*/
    
   //if(!g_ajax_region_lock){        
       
       waitMsg();
       var region_id = xeno['region']['id'];
       var battle_id = xeno['battle']['id'];
       var action_id = 0;//[TODO] Cambair esta accion a una real

       var tx = getId(origin_tile);//Objetivo
       var ty = getId($(origin_tile).parents('tr'))//Objetivo;

       var ox = getId(origin_tile);//Objetivo
       var oy = getId($(origin_tile).parents('tr'))//Objetivo;

        $.ajax({
            type:'post',
            data:{'ox':ox,'oy':oy,'tx':tx,'ty':ty,'ajax_action':'endmyturn','region_id':region_id,'battle_id':battle_id,'action_id':action_id},
            url:g_region_url,
            dataType:'json',
            cache:false,
            error:function(XMLHttpRequest, textStatus, errorThrown){processAjaxError(XMLHttpRequest, textStatus, errorThrown);},
            success:function(data){
              processAjaxAnswer(data);
              }
        });
    /*}
    else{
    ajaxbusymsg();
    }*/
 }

 function callHiddenStatus(){
  /* console.groupCollapsed('callHiddenStatus');
   console.trace('AJAX','callHiddenStatus');
   console.groupEnd();*/
//console.log('callHiddenStatus');

    //Codigo del Profiler




   /*console.groupCollapsed('callHiddenBattleStatus');
   console.trace('AJAX','callHiddenBattleStatus');
   console.groupEnd();*/
       //console.info(newtime,'callHiddenBattleStatus mayor a 10');
       if(!g_ajax_hidden_status_lock){
           //console.info(newtime,'Entro a la llamada de Ajax');
            var id = getActualArmyId();
            var selected_army = getArmy(id);
            var origin_tile = $(selected_army).parent('td');
           
            var xdebug_profile = 0;
            if(xeno['battle']['phase'] == 'B'){
                var player = getPlayerByActualArmy();
                if( player['type']=='A'){
                    waitAIMsg();
                    xdebug_profile = 1;
                    //console.log('callHiddenStatus de un AI');
                    //xdebug_profile = 0;
                }    

            }
           
           
           
           var region_id = xeno['region']['id'];
           var battle_id = xeno['battle']['id'];
           var action_id = 0;//[TODO] Cambair esta accion a una real

           var tx = getId(origin_tile);//Objetivo
           var ty = getId($(origin_tile).parents('tr'))//Objetivo;

           var ox = getId(origin_tile);//Objetivo
           var oy = getId($(origin_tile).parents('tr'))//Objetivo;

           //[jsk fix] comment this to not call the ajax
            g_xhr_hidden_status = $.ajax({
                type:'post',
                cache:false,
                //data:{'ox':ox,'oy':oy,'tx':tx,'ty':ty,'ajax_action':'status','region_id':region_id,'battle_id':battle_id,'action_id':action_id},
                data:{'ox':ox,'oy':oy,'tx':tx,'ty':ty,'ajax_action':'status','region_id':region_id,'battle_id':battle_id,'action_id':action_id,'XDEBUG_PROFILE':xdebug_profile},
                url:g_region_url,
                dataType:'json',
                success:function(data){processAjaxAnswer(data);}
            });
        }
        else{
          //ajaxbusymsg();
        }
 }

function processAjaxError(XMLHttpRequest, textStatus, errorThrown){
 /*console.log(XMLHttpRequest,'callExecAction error');
 console.log(textStatus,'callExecAction error');
 console.log(errorThrown,'callExecAction error');*/
        var error = [];
        error['text']= 'Error de Coneccion[1]';
        error['type']= 'error';
        msg(error);
        continueMsg();
 }
 function processAjaxAnswer(data,force){
    
    if(data == 1){
          var error = [];
          error['text']= 'Error de Coneccion [2]';
          error['type']= 'error';
          msg(error);
          continueMsg();
     }
     else{
      var temp = $.parseJSON(data);
      console.log(temp,"processAjaxAnswer region.js");
      if(!temp.isOk){
        var error = [];
        error['text']= temp.text;
        error['type']= temp-type;
        msg(error);
      }
      else{
        last_xeno = xeno;
        xeno = temp.data;
      }

      

      
      
     updateGlobalState();
      $(document).trigger('xeno.newXeno');//Esto solo funciona una puta ves!!??
      //console.groupCollapsed('processAjaxAnswer');
      //console.log('Xeno:['+xeno['battle']['phase']+ '] LastXeno:['+last_xeno['battle']['phase']+']','processAjaxAnswer before');
      xeno['time'] = iGlobalTime;
      
      if( isDefined(force) && force ){
          //console.log(xeno['battle'],'processAjaxAnswer phase['+xeno['battle']['phase']+'] FORZADO');
          phaseManager(force);
          msg(temp);
      }
      
      else if(xeno['battle']['phase']=='T'){
        if(last_xeno['battle']['phase']!='T'){
            phaseManager(true);    
            msg(temp);
        }
        else{
            if(xeno['battle']['battle_seconds']<0){
                phaseManager(true);
            }
            else{
                phaseManager();
            }
            
        }
        
        
      }
      else if (xeno['battle']['phase']=='B'){
          if(!g_first_battle_phase){
              $(document).trigger('xeno.newXenoBattle',xeno);
              g_first_battle_phase = true;
              //console.info('xeno.newXenoBattle a sido trigereado');
          }
          
            //console.warn(xeno['battle'],'region.js processAjaxAnswer new xeno');
//            console.warn(last_xeno['battle'],'region.js processAjaxAnswer old xeno');
          //
          if(last_xeno['battle']['phase']!='T'){//Mejor preguntar si ha sido B? pero peude ser E tambien
              var x = xeno['battle']['armies_turn'][0]['id'];
              var y = last_xeno['battle']['armies_turn'][0]['id'];
              //console.log('x['+x+'] != y['+y+']','processAjaxAnswer else');
               if(xeno['battle']['armies_turn'][0]['id']!=last_xeno['battle']['armies_turn'][0]['id']){
                phaseManager(true);
                msg(temp);
                //console.log('Las unidades son diferente','processAjaxAnswer else');
               }
               else{
                   /*console.log('Las unidades son iguales','processAjaxAnswer else');
                   console.log('x['+xeno['battle']['turn']+'] != y['+last_xeno['battle']['turn']+']','processAjaxAnswer else');*/
                   if(xeno['battle']['turn']!=last_xeno['battle']['turn']){
                        phaseManager(true);
                        msg(temp);
                        //console.log('Los ciclos son diferente','processAjaxAnswer else');
                   }
                   else{
                       if(temp['type'] == 'warning'){
                           phaseManager(true,{hasWarning:true});
                       }
                       else{
                           phaseManager();
                       }
                        msg(temp);
                       //console.log('Los ciclos son iguales','processAjaxAnswer else');
                   }
               }
           }
           else{//IMPORTANTE esto significa que se cambio de tactica a batalla, podemos usar una animacion aqui
               phaseManager(true,{hasStarted:true});
                msg(temp);
           }
            //continueMsg();
      }
      else if(xeno['battle']['phase']=='E'){//Si termino la batalla
        if(last_xeno['battle']['phase']!='B'){
            phaseManager();
            //console.log(last_xeno['battle']['phase'],'region->processAjaxAnswer Last no es B');
        }
        else{//IMPORTANTE esto significa que se cambio de fase: de batalla a end, podemos usar una animacion aqui
            phaseManager(true,{hasEnded:true});//Es forzdo y si es final de batalla
            //console.log(last_xeno['battle']['phase'],'region->processAjaxAnswer Last es B');
        }
        msg(temp);
      }
      else{
        //[WHAT]La region no tiene aun una batalla
        phaseManager();
        msg(temp);
      }
    }
    //console.groupEnd();
 }


////////////////////////////////////////////////////
// FILLERS
/////////////////////////////////////////////////////

 function fillHistorial(){
  var wrapper = $('#HistorialMsg');
  var movements = xeno['movements'];
  var dom = '';
  var idstr='';
  var classstr='movement';
  //console.log(movements.length);
  if(movements.length<1){
     dom = dom + '<li class="historic"><label>El historial esta vacio</li>';
  }
  else
  {
      $.each(movements,function(index,movement){
        var army_id = movement['army_id'];
        if(isDefined(xeno['armies'][army_id])){
            var army = xeno['armies'][army_id];
            var player = xeno['players'][[army_id]['player_id']];
            var myself = xeno['player'];
            dom = dom + '<li class="historic" army_id="'+army_id+'" ><label>'+movement['time_in']+'</label> '+renderCreature(army,player,myself,false,idstr,classstr,true)+movement['info']+'</li>';
        }
        else{//La unidad ya no esta en el show
            dom = dom + '<li class="historic" army_id="'+army_id+'" ><label>'+movement['time_in']+'</label> '+ renderUnknowCreature(true)+movement['info']+'</li>';
            //dom = dom + '<li class="historic" army_id="'+army_id+'" ><label>'+movement['time_in']+'</label> '+movement['info']+'</li>';
        }
        });

  }
   wrapper.html(dom);
  //console.log(wrapper);
}
function ToggleHistorial(){
    $("#HistorialMsg").toggle();
}




 function fillArmiesTurn(){
   var turns = xeno['battle']['armies_turn'];
   var Mdom ='<div class="block ui-state-transparent"><h1 id="TurnosTitle" class="ui-state-active">T u r n o s</h1><div class="inner"><ul id="ArmiesTurn" class="ui-helper-clearfix"></ul></div> </div>';
   var counter =1; 
   $('#BattleTurnsWrapper').html(Mdom);
   
   if(turns.length>0){
       
   
   
   $.each(turns,function(index, turn){
     var extraclass='';
     var creatureDom='';
     var title = 'Esta unidad le tocara el turno en '+counter+' turnos';
     var army = xeno['armies'][turn['id']];
     var player = xeno['players'][turn['player_id']];
     var myself = xeno['player'];
     var armyPlayerType = army['allegiance'];
     if(counter == 1){
       //extraclass= 'active '+armyPlayerType;
       extraclass= armyPlayerType +' actual';
       creatureDom = renderCreature(army,player,myself,false,'a','army_turn',true);
       title = 'Esta unidad tiene el turno actual, usa alguna accion de la unidad y pasa al siguiente turno';
       
     }
     else if (counter == 2){
         title = 'A esta unidad le toca el siguiente turno';
         extraclass= armyPlayerType;
         creatureDom = renderCreature(army,player,myself,false,'a','army_turn',true);
     }
     else{
         
         extraclass= armyPlayerType;
         creatureDom = renderCreature(army,player,myself,false,'a','army_turn',true);
     }
   
     
     var dom ='';
     
     //dom = dom + '<li class="turn '+extraclass+'"><div id="Info'+turn['id']+'" class="info" >'+renderPlayer(player,xeno['player']);
     dom = dom + '<li class="turn '+extraclass+'"><div id="Info'+turn['id']+'" class="info" >';
     
     //var player = xeno['players'][army['player_id']];
     dom = dom +'<p class="ui-state-default turnInfo" title="'+title+'">'+counter+'</p>'+'<span>'+army['size']+' </span>'+creatureDom;
     //dom = dom +'<p>'+army['size']+'</p></div></li>';
     dom = dom +'</div></li>';
     //dom = dom + '<div class="mediumLifeBar"></div>'    +'</div></li>';
     //console.log(dom,'fillArmiesTurn');
     //console.log($('#ArmiesTurn'),'fillArmiesTurn parent');
     $('#ArmiesTurn').append(dom);
     //console.log(dame,'fillArmiesTurn dame');
     //$('#Info'+turn['id']+' .mediumLifeBar').progressbar({value: army['life_total_percent']});
     counter++;

   })
   $('.turnInfo').qtip();
   }
   else{
      $('#ArmiesTurn').append('<p>La Batalla a finalizado, ya no hay mas turnos</p>');
   }
 }
 
 function fillPlayersPhases(){
   var playersbattles = xeno['battle']['player_status'];
   //console.log(playersbattles,"region->fillPlayersPhases");
   var infopanel = $('#BattlePhasesWrapper');
   $(infopanel).html('');
   DOM = '';
   for(id in playersbattles){
       /*console.log(playersbattles[id]['phase']);
       console.log(playersbattles[id][1]);
       console.log(playersbattles[id]);
       console.log(phases);*/
       //console.log(id,"region->fillPlayersPhases 2");
       var player_id = playersbattles[id]['player_id'];
       //console.log(player_id,"region->fillPlayersPhases 2");
     var player = xeno['players'][player_id];
     
     DOM=DOM+'<div class="players ui-state-default">'+renderPlayer(player,xeno['player'])+' Fase'+phases[playersbattles[id]['phase']]+'</div>';
   }
   $(infopanel).append(DOM);
   
 }
 
 
function fillArmiesInMap(){
    var armies = xeno['armies'];
    //console.log('region.js fillArmiesInMap');
    if(isDefined(armies)){
        for(var unit_id in armies)
        {
          //console.log(armies[unit_id],'fillArmiesInMap');
          if(!isNaN(unit_id)){//Solo regule los ID
            if(armies[unit_id].position=='P' || armies[unit_id].position=='S'){
              // Si esta en el [P]laneta o esta [S]aliendo de la region
              createUnit(unit_id);
            }
            else if(armies[unit_id].position=='O'){//Esta orbitando
              //resetStatus();
              destroyUnit(unit_id);
            }

          }
        }
        $('table img.unit').mouseenter(function(){showUnitBubble(this)});
        $('table img.unit').mouseleave(function(){hideUnitBubble(this)});
    }
 }
 
 
 function fillArmyInfo(id){
    if(isDefined(xeno['armies'])){
        
    
    armies = xeno['armies'];
    //console.log(id,'fillArmyInfo id-predefined');
    if(isDefined(id)){
      var div = $('div#ArmyInfo');
      //console.log(id,'fillArmyInfo id');
      //console.log(armies[id],'fillArmyInfo army');
      //Si la unidad tiene energia
      if(armies[id]['max_energy']>0){
        var dom_energy ='<div class="barWrapper"><div class="energybar"></div></div>';
      }
      else{
        var dom_energy ='';
      }
      
      //Si la unidad tiene mas de un ataque
      var dom_quantity = '';
      if(armies[id]['attack_quantity']>1){
        dom_quantity = ' x '+armies[id]['attack_quantity'];
      }
      var player = xeno['players'][armies[id]['player_id']];
      var myself = xeno['player'];
      var dom = '<div class="xenoborder">'+renderPlayer(player,myself)+' '+armies[id]['name']+'</div>'+
      '<div class="portrait">'+
      '<img src="'+g_image_url+'unit/'+armies[id]['creature_id']+'.png"/>'+
      'x <span>'+armies[id]['size']+'</span>'+
      '<div class="barWrapper"><div class="lifebar"></div></div>'+
      dom_energy+ 
      '</div>'+ 
      '<div id="Creature-Stat" class="ui-helper-clearfix">'+
        '<div class="statscontainer"><div class="Stats Attack"></div><p class="statsdata">'+armies[id]['attack']+dom_quantity+'</p></div>'+
        '<div class="statscontainer"><div class="Stats Armor"></div><p class="statsdata">'+armies[id]['armor']+'</p></div>'+
        '<div class="statscontainer"><div class="Stats Life"></div><p class="statsdata">'+armies[id]['life']+'</p></div>'+
      '</div>';
      $(div).html('').append('<div class="inner">'+dom+'</div>');
      $('.lifebar').progressbar({value: armies[id]['life_total_percent']});
      $('.energybar').progressbar({value: armies[id]['energy_total_percent']});
      
     tooltip( $('.lifebar'),'<p>Total de vida:'+armies[id]['life_total']+'/'+armies[id]['max_life']+'</p><p>Puntos de Vida faltantes para eliminar la siguiente unidad de este ejercito: '+ armies[id]['life_residual'] +' / ' + armies[id]['life']+'</p>','top');
     tooltip( $('.energybar'),'<p>Total de energia:'+armies[id]['actual_energy']+'/'+armies[id]['max_energy']+' '+renderSmallEnergyIcon()+'</p><p>Algunas acciones requieren de energia. Si no tienes energia no podras realizar estas acciones</p>','top');
     tooltip ($('#Creature-Stat .Attack'),'Puntos de Ataque de la unidad y numero de veces que ataca por turno','top');
     tooltip ($('#Creature-Stat .Armor'),'Puntos de Armadura de la unidad, a mas armadura, la unidad recibe menos ataque','top');
     tooltip ($('#Creature-Stat .Life'),'Puntos de Vida por cada unidad. Multiplicalo por el numero de unidades para obtener el total de vida. Actualmente este ejercito tiene un total de '+armies[id]['life_total'] +' puntos de vida','top');
     tooltip ($('.portrait span'),'Tienes '+armies[id]['size']+' '+armies[id]['name']+' en este ejercito','top');
    }
    else{
      //$.log('fillArmyInfo:No hay Unidad Seleccionada')
    }
    }
    else//No esta definida la variable army
    {
        console.log('Armies no existe','region->fillArmyInfo');
    }

  } 
 
  function fillArmyActions(army_id){
    //console.log('Entrando FillArmy','fillARmyAction');
    armies = xeno['armies'];
    var actualArmy = getSelectedArmyId();//[Esta no es la actual]
    //var actualArmy = getActualArmyId();
    //console.log(actualArmy+' == '+army_id,'preg1 fillARmyAction');
    //if(actualArmy == army_id){
      if(getActualArmyId() == getSelectedArmyId()){
      //console.log(isDefined(army_id),'preg2 fillARmyAction');
      if (isDefined(army_id)) {
        //console.log(isDefined(armies[army_id]['actions']),'preg3A fillARmyAction');
        //console.log(armies[army_id]['state']+' == A','preg3B fillARmyAction');
        var army = armies[army_id];
        
        if( army['is_actual']==true && army['state'] == 'A' &&( army['allegiance'] == 'myself') ){
            var div = $('#ArmyActions').html('<ul id="ArmyActionsWrapper"></ul>');//.append(dom_title);
            if (isDefined(army['actions'])  ) {
                for (var action_loop in armies[army_id]['actions']) {
                    var action = armies[army_id]['actions'][action_loop];
                    var actionDom = renderAction(action);
                    //$('#ArmyActionsWrapper').append('<li class="ui-state-transparent actionWrapper"><div class="action button" id="g'+ action['action_id']+'">'+actionDom+'</div></li>');
                    var actionDomObject = $('<li class=" actionWrapper"><div class="action button" id="g'+ action['action_id']+'">'+actionDom+'</div></li>').appendTo('#ArmyActionsWrapper');
                    var tooltipDom = '<div><p><span class="title">'+action['name']+'</span>: '+action['desc']+'</p>';
                    if(action['energy']>0){
                      tooltipDom = tooltipDom +'<p>Costo: <span class="title">'+action['energy']+' </span>'+renderSmallEnergyIcon()+'</p>';
                    }
                    tooltipDom = tooltipDom +'</div>';
                    tooltip(actionDomObject,tooltipDom,'top');
                }
            }
            
            /* Añardir la accion de pasar turno a todas las unidades de la unidad activa*/
            var endTurnActioDomObject = $('<li class="actionWrapper"><div class="button" id="FinalizarTurno">'+renderEndTurn()+'</div></li>').appendTo($('#ArmyActionsWrapper') );
            var endTurnTooltipDom = '<div><p><span class="title">Finalizar turno</span>: Pasa el turno al siguiente ejercito</p></div>';
            tooltip(endTurnActioDomObject,endTurnTooltipDom,'top');
            $('#FinalizarTurno').bind('click',callEndMyBattleTurn);
            
            /*Inicializamos la ultima accion seleccionada*/
            $('#ArmyActionsWrapper .action').bind('click', selectAction);
              //La ultma accion seleccionada se quede
              var lastAction = getActualActionId();// La ultma accion
              if (lastAction) {
                //console.log('Entro','res2 fillARmyAction');
                $('#g' + lastAction + '.action').click();
              }
              else {
                //console.log('Entro','res3 fillARmyAction');
                var first_action = $('.action')[0];
                $(first_action).click();//Seleccionar la primera opcion de Acciones
              }
              if (!getActualActionId()) {//Si es que existe una last action pero no en esa unidad
                //console.log('Entro','res4 fillARmyAction');
                $(first_action).click();
              }
              $('#ArmyActionsWrapper').show();
            
            
        }
        else{
          //console.log('No Entro1','res2 fillARmyAction');
          $('div#ArmyActions').html('');
          resetTiles();
        }
        
        
      }
      else{
        //console.log('No Entro2, no eixste ese id','res3 fillARmyAction');
        $('div#ArmyActions').html('');
        resetTiles();
      } 
    }
    else{
      //console.log('No Entro3,no es unidad actual','res4 fillARmyAction');
      $('div#ArmyActions').html('');
      resetTiles();
    }
  }
  
    /***********************************************************   
   * fillPath: Rellenamos el mapa con el path de la accion y unidad seleccionada
   * PARAMS:
   *    army_id: id de la unidad selecionada
   *    action_id: id de la accion selecionada
   ******************************************************** */
  function fillPath(army_id,action_id)
  {
    var armies = xeno['armies'];
    var indexes = armies[army_id]['actions'][action_id]['indexes'];
    for(ind in indexes){
       var index_x = indexes[ind]['x']; 
       var index_y = indexes[ind]['y'];
       addTerrainPathClass(getTile(index_x,index_y));
    }
    //var path = armies[army_id]['actions'][action_id]['path'];
    //var x=parseInt(armies[army_id]['position_x']);
    //var y=parseInt(armies[army_id]['position_y']);
  } 
 

  function isCoordInBoundaries(x,y){

  }

  function fillSquarePath(x,y,rango,weightit){
    if(!isDefined(weightit)){
      weightit = false;
      
    }
    var x_init = x-rango;
    var y_init = y-rango;
    var x_end=x+rango;
    var y_end=y+rango;
    var tot = (rango*2)+1;
    for(var bucle=0;bucle<tot;bucle++){
        fillWeightTile(x_init,y_init+bucle,weightit);//Left      
        fillWeightTile(x_end,y_init+bucle,weightit);//right
        fillWeightTile(x_init+bucle,y_init,weightit);//top
        fillWeightTile(x_init+bucle,y_end,weightit);//top
    }
  }
  
  function addTerrainPathClass(tile){
    //console.log(tile,'ANTES');
    if (isDefined(tile)) {
      var type = getTerrainType(tile);
      $(tile).addClass('path path'+type);
      //console.log(tile,'OBTUVO CLASE:'+type);
      //$(tile).addClass('path');
    }
    else{
      //console.trace('No esta definido el tile','regions.js->addTerrainPathClass');
    }
    
  }
  //Llena un tile con .path o .nopath dependiendo del peso que tenga ese tile
  function  fillWeightTile(x,y,weightit){
    //console.log(arguments,'fillWeightTile');
    if((x<0)||(y<0)||(x>=xeno['region']['x'])||(y>=xeno['region']['y'])){
      //console.log('x['+x+'] y['+y+'] region_x['+xeno['region']['x']+'] region_y['+xeno['region']['y']+']', 'region.js->fillWeightTile');
      return false;
    }
    var tile = getTile(x,y);//tile
    var army_id = getActualArmyId();
    var action_id = getActualActionId();  
    var armies = xeno['armies'];
    var action = xeno['armies'][army_id]['actions'][action_id];


    if(isDefined(tile)){
      var type = getTerrainType(tile);

      //
      if( (weightit) && (armies[army_id]['speed'][type]>0) ){
        $(tile).attr('weigth',armies[army_id]['speed'][type]);
        switch(action['target']){
          case '1'://Solo a Armies
              if($(tile).children('img').is('.army'))
              { //El Tile Contiene otra unidad
                addTerrainPathClass(tile);
              }
              else{$(tile).addClass('nopath');}
          break;
          
          case '2'://Solo a Terrain
            if($(tile).children('img').is('.army'))
              { //El Tile Contiene otra unidad
                $(tile).addClass('nopath');
              }
              else{addTerrainPathClass(tile);}
          break;
          
          case '3':
              addTerrainPathClass(tile);
          break;

          case '4':
              console.warn('X_TARTGET_ENEMY no implementado','region->fillWeightTile');
          break;

          case '5':
              console.warn('X_TARTGET_ENEMYANDARMY no implementado','region->fillWeightTile');
          break;

          case '6':
              console.warn('_X_TARGET_ENEMYANDARMYANDTERRAIN no implementado','region->fillWeightTile');
          break;
          
          default:
            console.log(action,'Target por Default['+action['target']+']','weight yes');
          break;
        }
      }
      else{
        switch(action['target']){
          case '1'://Solo a Armies
              if($(tile).children('img').is('.army'))
              { //El Tile Contiene otra unidad
                addTerrainPathClass(tile);
              }
              else{$(tile).addClass('nopath');}
          break;
          
          case '2'://Solo a Terrain
            if($(tile).children('img').is('.army'))
              { //El Tile Contiene otra unidad
                $(tile).addClass('nopath');
              }
              else{addTerrainPathClass(tile);}
          break;
          
          case '3':
              addTerrainPathClass(tile);
          break;

          case '4':
              console.warn('X_TARTGET_ENEMY no implementado','region->fillWeightTile');
          break;

          case '5':
              //console.warn('X_TARTGET_ENEMYANDARMY no implementado','region->fillWeightTile');
               if($(tile).children('img').is('.army'))
               { //El Tile Contiene otra unidad
                  if($(tile).children('img').is('.AI'))
                  {
                    addTerrainPathClass(tile);
                  }
                  else if($(tile).children('img').is('.enemy')){
                      addTerrainPathClass(tile);
                  }
                  else{
                      $(tile).addClass('nopath');
                  }

              }
              else{$(tile).addClass('nopath');}

          break;

          case '6':
              console.warn('_X_TARGET_ENEMYANDARMYANDTERRAIN no implementado','region->fillWeightTile');
          break;

          default:
           console.warn('Target por Default['+action['target']+']','weight no');
          break
        }
      }  
  }
  else{
   //console.warn('Tile Nodefined x:'+x+' y:'+y);
  }
}
  
  

    function fillLinePath(vector,range,x,y){
    tot = getMapLimit(vector);
    if(vector=='x'){
      for(var bucle=0;bucle<tot;bucle++){
        addTerrainPathClass(getTile(bucle,y));
      } 
    }
    else if(vector=='y'){
      for(var bucle=0;bucle<tot;bucle++){
        addTerrainPathClass(getTile(x,bucle));
      }
    }
  }
  
  
  function getMapLimit(vector){
    if(vector=='x'){
      
      return xeno['region']['x'];
    }
    else if(vector=='y'){
      return xeno['region']['y'];
    }
  }
  

/************************************************
 *  UTILITARIOS
 * *********************************************/

  
  /*function moveUnit(unit_id){
    var id = getSelectedArmyId();
    destroyUnit(unit_id); 
    if(id == unit_id)
    {
      createUnit(unit_id,true); 
    }
    else
    {
      createUnit(unit_id,false);
    } 
  }*/
  
  function killUnit(unit_id){
    var armies = xeno['armies'];
    var par = $("img#u"+unit_id).parent();
    $("img#u"+unit_id).hide();
    var rand = parseInt(Math.random()*100000);
    var DOM ="<div class='doodad' id='d"+rand+"'>"
        +"<img id='u" + rand + "' src='"+g_image_url+"unit/"+armies[unit_id]['creature_id']+".png'/>"
        +"</div>";
    $(par).append(DOM);
    var doodad=$('div#d'+rand);
    var topleft= $(par).offset();
    $(doodad).css("top",topleft['top']+4).css("left",topleft['left']+5);
    $(doodad).show('slow').hide("explode", {number: 4}, 1000,function(){
      $(this).remove();
    });
    //$(doodad).show('slow').hide("explode", { number: 9 }, 3000);
  }
function destroyUnit(unit_id){
    var unit = $('img#u'+unit_id);
    $(unit).remove();
}
  
  function createUnit(army_id){
    destroyUnit(army_id);
    var selected_id = getSelectedArmyId();
    var selected =false;
    if(selected_id == army_id){selected = true}
    var armies = xeno['armies'];
    var tile = getTile(armies[army_id]['position_x'],armies[army_id]['position_y'])

     var army = xeno['armies'][army_id];
     var player = xeno['players'][army['player_id']];
     var myself = xeno['player'];

    var UNIT_DOM = renderCreature(army,player,myself,selected,'u','army');
    $(tile).append(UNIT_DOM);
  }  

/************************************************
 *  RENDERS
 * *********************************************/


function resetTiles(){
  $('table.battle_map td.path').each(function(){
    resetTile(this);  
  });
}
function resetTile(tile){
  //Iteramo por todos las velocidades del seleccionado y borramos 
  $(tile).removeClass('path1');
  $(tile).removeClass('path2');
  $(tile).removeClass('path3');
  $(tile).removeClass('path4');
  $(tile).removeClass('path5');
  $(tile).removeClass('path0');  
  $(tile).removeClass('path');
  $(tile).removeClass('nopath');
  $(tile).attr('weigth',0);
  //var count = $(tile).find('div.countdown_wrapper')[0];
  //$(count).remove();
}

/************************************************
 *  RESET
 * *********************************************/
function resetArmyActions(){
  //Borramos Menu del Lado 
  //$('div#ArmyActions').html('<h3>Acciones</h3>');
  resetTiles();
}

/************************************************
 *  SETTERS
 * *********************************************/
  function setSelectedArmyId(id){//[TODO]Para setear el valor inicial de Ary
    $('body').data('xeno_selected_army_id',id);
    var dom = getArmy(id);
    $('.army.selected').removeClass('selected');
    $(dom).addClass('selected');
  }
  
  function setSelectedInfoArmyId(id){//[TODO]Para setear el valor inicial de Ary
    //console.warn(id,'region.js->setSelectedInfoArmyId');
    $('ul#ArmiesTurn').data('xeno_selected_army_id',id);
    var dom = $('#Info'+id);
    $('.info.selected').removeClass('selected');    
    $(dom).addClass('selected');
  }
   
  function panningToDom(dom,callback){
      
      var offsetDom = dom;
      //Obtener el td que contiene a este parent
      if(!$(dom).is('td')){
          //offsetDom = $(dom).offsetParent(); //Porque offsetParent?, no tengo idea, cambiado el 01ene2013
          offsetDom = $(dom).parent(".terrain");
          //console.log(offsetDom);
      }
      //debug = offsetDom;
      
      //Obtenemos las coordenadas reales del tile contenedor del dom
      positionO = getTileCoords(offsetDom);
      //console.log('positionOX['+positionO.x+'] positionOY['+positionO.y+']');
      //position = $(offsetDom).position();
      position.left = positionO.x* g_tile_size;
      position.top = positionO.y* g_tile_size;
      //console.log('positionX['+position.left+'] positionY['+position.top+']');
      
      //Encontrar las distancias minimas y maximas
      var viewportX = $('#ViewPort').width();
      var viewportY = $('#ViewPort').height();
      
      //Distancia minima desde la cual, a mas distancia se puede hacer un panning
      var distanceX = (viewportX-60)/2;
      var distanceY = (viewportY-60)/2;
      //console.log('#battlemap width['+$('#BattleMap').width()+'] height['+$('#BattleMap').height()+']');
      var MaxDistanceX = $('#BattleMap').width() - viewportX;
      var MaxDistanceY = $('#BattleMap').height() - viewportY;
      
      //console.log('viewportX['+viewportX+'] viewportY['+viewportY+'] distanceX['+distanceX+'] distanceY['+distanceY+'] MaxX['+MaxDistanceX+'] MaxY['+MaxDistanceY+']');
      
      var MovX = 0;
      var MovY = 0;
      
      var possibleX = position['left']-distanceX;
      var possibleY = position['top']-distanceY;
      
      MovX = -possibleX;
      if(possibleX<=0){
          MovX = 0; //
      }
      else if(possibleX>= MaxDistanceX){
          MovX = -MaxDistanceX;
      }
      
      MovY = -possibleY;
      if(possibleY<=0){
          MovY = 0; //
      }
      else if(possibleY>= MaxDistanceY){
          MovY = -MaxDistanceY;
      }
      
      //Trigger
      var mov = new Object();
      mov['x'] = MovX;
      mov['y'] = MovY;
      //console.log(dom,'region.js->panningToDom pretrigger');
      $(document).trigger('xhelos.map.move',mov);
      
      //console.log('viewportX['+viewportX+'] viewportY['+viewportY+'] distanceX['+distanceX+'] distanceY['+distanceY+'] MaxX['+MaxDistanceX+'] MaxY['+MaxDistanceY+']');
      //console.log('possibleX['+possibleX+'] possibleY['+possibleY+'] MovX['+MovX+'] MovY['+MovY+']');
      $('#BattleMap').animate({left:MovX,top:MovY},1500,'easeOutQuart',function(){
          //console.log('panningToDom callback');
          if(isDefined(callback)){
              //console.log('panningToDom callback YES');
              callback();
          }
          else{
              //console.log('panningToDom callback NO');
          }
          
      });
      
      
      
      
  } 
/************************************************
 *  SELECT
 * *****************body************************/

function panToActiveColonyOrArmy(){
    if( isDefined(xeno['colonies']) && xeno['colonies']!= false ){
        var colonies = xeno['colonies'];
        var selectedColony =  _.find(colonies,function(colony){
             if(colony['allegiance'] == 'myself'){
                 return true;
             }
             else{
                 return false;
             }
         })
        if(isDefined(selectedColony)) {
            var str = '#col'+selectedColony['id'];    
            panningToDom($(str));
        }
    }
    else if( isDefined(xeno['armies']) && xeno['armies']!= false ) {
        var armies = xeno['armies'];
        var selectedArmy =  _.find(armies,function(army){
             if(army['allegiance'] == 'myself'){
                 return true;
             }
             else{
                 return false;
             }
         })
         if(isDefined(selectedArmy)) {
         var str = '#u'+selectedArmy['id'];    
        panningToDom($(str));
        }

    }
    else{
        //console.log('No hay nada que panear');
    }
}

  function selectArmy(armyId,callback){
     if(isDefined(armyId) && armyId  ) {
      
        if(isDefined(xeno['battle']['phase'])){
            if(xeno['battle']['phase']=='T'){
                setSelectedArmyId(armyId);
                fillArmyInfo(armyId);
                //console.log('selecteArmy1');
                panningToDom(getArmy(armyId),callback);
                
            }
            else if(xeno['battle']['phase']=='B'){
                setSelectedArmyId(armyId);
                setSelectedInfoArmyId(armyId);
                fillArmyInfo(armyId);
                fillArmyActions(armyId);
                //console.log('selecteArmy2');
                panningToDom(getArmy(armyId),callback);
                var armyactual = getActualArmyId();
                if(armyactual!=armyId){//[WHY] Para que el msn del server no se borre
                  message(armies[armyId]['name']+' listo para la accion','success');
                }
                else{
                  msg(xeno['msg']);
                }
                

            }
            else if(xeno['battle']['phase']=='E'){//Estamos en END of battle
                setSelectedArmyId(armyId);
                fillArmyInfo(armyId);
                fillArmyActions(armyId);
                //console.log('selecteArmy3');
                panningToDom(getArmy(armyId),callback);
            }
            else{}
            $(document).trigger('xeno.armySelected',xeno);
        }
    }
    else{
        console.trace('region.js->selectArmy');
        debugMessage('region.js->selectArmy param[id] es falso');
    }
  }

  function selectAction(){
    //console.trace();
    $('.action.selected').removeClass('selected');//Quita lo seleccionado de una accion
    $(this).toggleClass("selected");
    resetTiles();
    //console.log(this,'DOM actualAction selectAction');
    //console.log(getActualActionId(),'actualAction selectAction');
    fillPath(getActualArmyId(),getActualActionId());
  }  
  function selectActualArmy(){
    //console.log('*******seleccioando*******','selectActualArmy');
    var armyId = getActualArmyId();
    //console.log(armyId,'selectActualArmy');
    var armyDom = getArmy(armyId);
    //console.log(armyDom,'selectActualArmy');
    $(armyDom).click();//Seleccionarlo realmente
    //$('img.army').removeClass('selected');//Quita lo seleccionado de una unidad
    //$(armyDom).addClass("selected");//posible error
  }
  
////////////////////////////////////////////////////////////////////////////////////////
// UTILITARIOS
////////////////////////////////////////////////////////////////////////////////////////
  
/************************************************
 *  getId: Obtiene el Id de cualquier DOM
 * *********************************************/
function getArmyInCoord(x,y){
	var row = $('tr#y'+y).eq(0);
        //console.log(row,'region.js getArmyInCoord row');
	var tile = $(row).children('td#x'+x).eq(0);
        //console.log(tile,'region.js getArmyInCoord tile');
	//var unit = $(tile).children('img.army').eq(0);
        var unit = $(tile).children('.army');
        //console.log(unit,'region.js getArmyInCoord unit');
	return unit;
}

/*************************************************************************
 *  getACtualActionId: Obtiene el Id de la accion actualmente seleccionada
 * **********************************************************************/
  function getActualActionId(){//Accion Seleccionada
    var action_id = getId($('#ArmyActions .action.selected').eq(0));
    //console.log($('#ArmyActions .action.selected'),'actualAction complete');
    //console.log($('#ArmyActions .action'),'actualAction mediocomplete');
    //console.log($('#ArmyActions'),'actualAction nocomplete');
    if(!isDefined(action_id)){
      //console.warn('No esta definida la action_id','getActualActionId');
    }
    return action_id;
  }

/*************************************************************************
 * getSelectedArmyId: Selecciona la unidad que esta actualmente seleccionada
 * ojo es diferente a la unidad actual. La unidad actual es a la que le toca
 * el turno, la seleccionada es a la que le han hecho click 
 * **********************************************************************/
 function getSelectedArmyId(){//Accion Seleccionada
    var resp = $('body').data('xeno_selected_army_id');
    return resp;
  }
 
function getLastArmyId(){ 
   if(!isDefined(last_xeno['battle']['armies_turn']) ){     
    return false;  
  }
  else{
    var turns = last_xeno['battle']['armies_turn'];
    return turns[0]['id'];
  }

}

 function getActualArmyId(){
  if(!isDefined(xeno['battle']['armies_turn']) ){
      var id = getSelectedArmyId();
      if(!isDefined(id) ){
        return false;  
      }
      else{
          return id;
      }
      
  }
  var turns = xeno['battle']['armies_turn'];
  /*for(turn in turns){
    var first =turn;
    break;
  }
  //console.log(first,'getActualArmyId');*/
  //[WHY] Desde que ordenamos el envio, el primero siempre es 0
  return turns[0]['id'];
  
} 
  
function getPlayerByActualArmy(){
  army_id = getActualArmyId();
  army = xeno['armies'][army_id];
  var player = xeno['players'][army['player_id']];  
  return player;
}
  
 function getTileCoords(dom) {
     var position = new Object();
     position.x = getId(dom);
     position.y = getId( $(dom).parent('tr'));
     return position;
 }
  
 function getTile(x,y){
  //console.trace('Como va las llamadas region.js->getTile'); 
  var row = $('table.battle_map tr#y'+y).eq(0);
  var tile = $(row).children('td#x'+x).eq(0);
  //console.log(tile,'region.js getTile');
  return tile;  
}
function getTerrainType(dom){
  //console.log(dom,'region.js getTerrainType');
  var type_raw = (dom).attr('class').split(' ');
  //console.log(type_raw,'region.js->getTerrainType');
  type = type_raw[1];//porque el primero es terrain
  //console.warn(type,'region.js->getTerrainType1');
  type = parseInt(type.substring(1));
  //console.warn(type,'region.js->getTerrainType2');
  return type;
  //console.log(type,'typefillWeightTile');
}



function getArmy(armyId){
  var unit = $('img.army#u'+armyId);
  return unit;  
}

/************************************************
 *  UTILS
 * *********************************************/
function iluminateArmy(army_id){
    var army = getArmy(army_id);
    $(army).addClass('iluminate');
}
function iniluminateArmy(army_id){
    var army = getArmy(army_id);
    $(army).removeClass('iluminate');
}

/************************************************
 *  BOOLEANS
 * *********************************************/
function isBattleEnd(){
    //console.log('retorna siempre true','[TODO] region.js->isBattleEnd');
    return true;
}

function arePlayersReadyToBattle(){
  var players = xeno['battle']['player_status'];
  var ready = true;
  for(var player in players){
    if(players[player]['phase']=='T'){
      ready = false;
      break;
    }
  }
  return ready;
}

function isPathable(target_tile){
  if ($(target_tile).is('.path')){
    return true;
  }
  else{
    return false;
  }
  /*if ($(target_tile).is('.path2')){
    return true;
  }
  if ($(target_tile).is('.path3')){
    return true;
  }
  if ($(target_tile).is('.path4')){
    return true;
  }*/
  
}


/*****************************************
 *Map Rendering
 ****************************************/


function renderRegionMap(){
    var dom='<table class="battle_map" id="BattleMap">';
    var region = xeno['region'];
    var map = region['map'];    
    var resp_y = region['y'];
    var resp_x = region['x'];
    if(isDefined(map)){
        for(var y=0;y<resp_y;y++)
        {
          dom = dom+"<tr class='terrain' id='y"+y+"'>";
          for(var x=0;x<resp_x;x++)
          {
            var data='';
            var index= mapIndex(resp_x,y,x);
            //debug::log($index,'region render mapindex');
            var type=map[index];
            dom=dom+"<td id='x"+x+"' class='terrain t"+type+"'>"+data+'</td>';

          }
          dom = dom+"</tr>";
        }
        dom = dom+'</table>';       
    }
    else{
        dom = "<table class='battle_map'><tr><td>Mapa No Accesible</td></tr></table>";
        
    }
    $("#ViewPort").html(dom);
    MoveBattleMap();
}