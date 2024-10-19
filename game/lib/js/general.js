//var g_game_url = 'http://localhost/xhelos/game/';
//var g_game_url = 'http://192.168.1.37/xhelos/game/';
var g_game_url = game['g_game_url']+'game/';
//console.log(game,'game');
//console.log(affect,'affect');


$(document).ready(function(){ 
  console.log(datatransfer,"dt document->ready general.js");
  console.log(datatransfer.data,"dt.data document->ready general.js");
    xeno = datatransfer.data;
    console.log(xeno,"xeno document->ready general.js");
    if(validateInitialXeno()){
    
        updateGlobalState();
        /*$(document).bind('xeno.newXenoBattle',function(event,data){
            updateGlobalState();
            console.log('updateGlobalState()');
        });*/
      $('#Menu li').click(general_showSubmenu);

      //Seleccion de colonias
      $('li.colonyid').live('click',function(){
          //console.log(this,'Me clickearon');
          var id  = getId(this);
          xeno['global']['colony'] = xeno['global']['colonies'][id];
          //fillResourcesData(xeno['global']['colony']['resources']);
          $('#MoveToColony span.ui-button-text').text(xeno['global']['colony']['name']);
          //$('#ColonySelector').hide('fold',function(){$(this).remove()});
          $('#SelectColony').click();
          $('#MoveToColony').attr('href','colony.php?colony_id='+xeno['global']['colony']['id']);
          //console.log(id,'Me clickearon');
      });

      //Button de las colonias
      $( "#MoveToColony" ).button()
                            .click(function() {
                                    //alert( "Running the last action" );
                            })
                            .next()
                                    .button( {
                                            text: false,
                                            icons: {
                                                    primary: "ui-icon-triangle-1-s"
                                            }
                                    })
                                    .toggle(function() {
                                        var actualColonyId = general_getActualColonyId();
                                        var DOM = "<ul id='ColonySelector' class='ui-state-default'>";
                                        for(i in xeno['global']['colonies']){
                                            var colony = xeno['global']['colonies'][i];
                                            if(colony['id'] == actualColonyId){
                                                DOM = DOM + '<li id="ColonyId'+colony['id']+'" class="colonyid ui-state-active" >Seleccionar <span>'+colony['name']+'</span></li>';
                                            }
                                            else{
                                                DOM = DOM + '<li id="ColonyId'+colony['id']+'" class="colonyid ui-state-default" >Seleccionar <span>'+colony['name']+'</span></li>';
                                            }

                                        }
                                        DOM = DOM + "</ul>";
                                        $('#ColoniesSelectionWrapper').append(DOM);
                                        $('#ColonySelector li').hover(
                                            function() {$(this).addClass('ui-state-hover');},
                                            function() {$(this).removeClass('ui-state-hover');}
                                        ); 

                                    },function(){
                                         $('#ColonySelector').hide('fold',function(){$(this).remove()});
                                    })
                                    .parent()
                                            .buttonset();

      //OnHover

      $('#MenuContainer li').hover(
        function() {$(this).addClass('ui-state-hover');},
        function() {$(this).removeClass('ui-state-hover');}
        ); 
      //OnHover
              

    }        
    else{
        alert("Parece que no llegaron todos los datos - Actualiza la pagina en unos momentos para volver a disfrutar de Xeno");
    }
});

function validateInitialXeno(){
//console.log(xeno,'validateInitialXeno xeno');
//console.log(datatransfer,'validateInitialXeno xeno');
    if( typeof(xeno)==="undefined"){
        return false;        
    }
    else{
        return true;
    }
    
    
}

function updateGlobalState(){
    //console.log(xeno,'general.js updateGlobalState');
    fillResourcesData(xeno['global']['player']);
    fillEnergyData(xeno['global']['player']);
}


//renombramos las funciones segun el nombre del arhcivo qe la contiene
function general_showSubmenu(){
  $('#Menu li').removeClass('ui-state-active');
  $('#Menu li').removeClass('ui-state-default');
  $('#Menu li').addClass('ui-state-default');
  $(this).addClass('ui-state-active');
  id='SubMenu-'+$(this).attr('id');
  $('.submenu').hide();
  $('.submenu').removeClass('hidden');
  $('#'+id).show();

}
function general_getActualColonyId(){
    return xeno['global']['colony']['id'];
}
function xenodebug(data,title){
  //console.log(data,title);
}


////////////////////////////////////////////////////////////////////////////////////////
// PANEL DE MENSAJES Manda mensajes a #Msg 
////////////////////////////////////////////////////////////////////////////////////////

 /******************************************************************
 * msg: Manda un mensaje a partir de la estructura de datos msg
 *****************************************************************/
function msg(msg){
  if(!isDefined(msg)){
    //console.error('No existe parametro msg','general.js');
    //console.error(msg,'general.js');
  }
  else{
    var last_msg = $('div#Msg p').html();
    if(msg['text'] != last_msg){
        message(msg['text'],msg['type']);
    }
    else{
        //console.warn('Los mensajes son identicos');
    }
    
  }
}

function successMsg(text){
    message(text,'success');
}

function warningMsg(text){
    message(text,'warning');
}
function errorMsg(text){
    message(text,'error');
}

/***********************************************************************
 * message: Manda un mensaje al sistema, importarte el div#Msg
 **********************************************************************/
function message(text,type){
/*console.groupCollapsed('message['+type+']');
console.trace(text);
console.groupEnd();*/
  var p_raw = $('div#Msg').find('p');
  //$(p_raw).slideUp('fast',function(){
    //var DOM = "<img src='../img/web/"++"' />";
    //$(this)append();
    $(p_raw).html(text);
    $(p_raw).removeClass();
    //$(this).addClass(type).show("slide",{ direction: "left" },'slow');
    //$(this).addClass(type).slideDown('fast'); 
    $(p_raw).addClass(type);//.effect("highlight", {color:"#01002E"}, 2000); 
    
  //})
}
/***********************************************************************
 * msgWait: Manda un mensaje al sistema, importarte el div#Msg
 **********************************************************************/
function waitMsg(){
  ///console.log('general.js->waitMsg');
  var p_raw = $('div#Msg').find('p');
  $(p_raw).html(renderWaitIcon());
}

function destroyWaitMsg(){
    var p_raw = $('div#Msg').find('p').find('.waitIcon');
    $(p_raw).remove();
    
    
}

function waitAIMsg(){
  var p_raw = $('div#Msg').find('p');
  $(p_raw).html('');
  renderWaitIcon(p_raw,'action/ajax_ai_wait.gif');
}



function continueMsg(){
  var p_raw = $('div#Msg').find('p');
  $(p_raw).html('');
}

////////////////////////////////////////////////////////////////////////////////////////
// WAIT ICON Mostrar Icono de Ocupado al ejecutar un Ajax 
////////////////////////////////////////////////////////////////////////////////////////
function createAjaxLoader(parent,type){
    if(!isDefined(type)){
        url = "web/ajax_loader_small.gif";
    }
    icon = renderWaitIcon(parent,url);
    return icon;
}
function destroyAjaxLoader(icon){
  $(icon).remove();
}

function debugMessage(msg){
    console.log(msg);
}

function renderWaitActionIcon(parent,actionId){
    var actionurl = 'action/'+actionId+'-small.png';
    var DOM = '<div class="ui-state-default waitIcon waitActionIcon"><img width="20" height="20" src="'+g_image_url+actionurl+'"/><img width="20" height="20" alt="espere" src="'+g_image_url+'web/ajax_loader_small.gif'+'"/></div>';
    var icon = $(DOM).prependTo(parent);
    //console.log(icon,'general.js renderWaitActionIcon');
    return icon;
}



function renderSmallWaitIcon(parent,position){
    var url = 'web/ajax_loader_small.gif';
    var dom = '<img width="20" height="20" class="waitIcon" alt="Wait a second" src="'+g_image_url+url+'"/>';
    //console.log(dom,'renderSmallWaitIcon');
    return renderWaitIcon(parent,url,position,dom);
}

function renderWaitIcon(){
   var url = 'action/ajax_wait.gif';
   var DOM = '<img class="waitIcon" alt="Wait a second" src="'+g_image_url+url+'"/>'; 
   return DOM;
}
//BORRAR

function destroyWaitIcon(icon){
  $(icon).remove();
}



////////////////////////////////////////////////////////////////////////////////////////
// RENDERIZACIONES
////////////////////////////////////////////////////////////////////////////////////////

/* Renderizado de Jugadores para el InfoPanel*/
 function renderPlayers(players,myself){
   var DOM ='<ul id="PlanetPlayers" class="players ui-helper-clearfix">';

    if (typeof(players[0])!='undefined')
    { 
      DOM = DOM + '<li>'+players[0]+'</li>';
    }
    else{
       $.each(players,function(key,player){
         
            var who ='enemy';
            if(player['id']==myself['id']){
              who = 'myself'
            }
            if(player['type']=='A'){
              who = 'AI'
            }
            DOM = DOM + '<li class="'+who+'">'+player['username']+'</li>';   
       });
    }
  DOM = DOM +'</ul>';
  return DOM; 
 }
 /* Renderizado de un Jugador*/
 
 function renderPlayer(player,myself){
   var DOM = '';
   var who ='enemy';
   if(player['id']==myself['id']){
    who = 'myself'
   }
   if(player['type']=='A'){
     who = 'AI'
   }
   DOM = DOM + '<span class="player '+who+'">'+player['username']+'</span>';
   return DOM;
 }
 
 function playerType(player,myself){
    var who ='enemy';
    //console.log(player,'PLAYER');
    //console.log(myself,'MYSELF');
    if(player['id']==myself['id']){
        who = 'myself'
    }
    if(player['type']=='A'){
        who = 'AI'
    } 
    return who;
 }
////////////////////////////////////////////////////////////////////////////////////////
// RELOJ COUNTDOWN
////////////////////////////////////////////////////////////////////////////////////////




////////////////////////////////////////////////////////////////////////////////////////
// FILLERS : Funciones que llenan de DOM alguna seccion del documento
////////////////////////////////////////////////////////////////////////////////////////
/***************************************************************************
 *Funcion fillReinforcement: Imprime en alguna region la lista de refuerzos
 *necesarios
 *PARAMETROS:
 *region: Un arreglo que contiene todos los datos de la region
 *reinforcements: Un arreglo que contiene los refuerzos que estan llegando al planeta
 *wrapper: Un objeto dom-jquery donde se colocara el codigo del refuerzo, por defecto #BattleReinforcement
***************************************************************************/
function fillReinforcement(region,reinforcements,wrapper){
    //console.log('Entre a Refuerzos','general.js->fillReinforcement');
    if(!isDefined(wrapper)){
        wrapper = $('#BattleReinforcement');
    }

     var region_id = region['id'];
     if( isDefined(reinforcements) ){
         //console.log('Refuerzos esta definido','general.js->fillReinforcement');
          ///////////////////////////////
          //Actualizar Lista de Refuerzos
          ///////////////////////////////
          //console.log(region,'planet.js->fillReinforcement->param');
         var myself_id = xeno['player']['id'];
         var DOM = '';
         var list_player = new Array();
          //console.log(reinforcements,'planet.js->fillReinforcement');
          $.each(reinforcements,function(key,reinforcement){
              //Calculo de Color de Nombre
              //console.log(reinforcement['to_region']+'=='+region_id,'planet.js->fillReinforcement');
              if(reinforcement['to_region'] == region_id){
                var type = 'enemy';
                var player_id=reinforcement['player_id'];
                if(player_id==myself_id){
                  type = 'myself';
                }
                //console.log(reinfoplayer,'planet.js->fillReinforcement');
                var player_name = reinforcement['username'];
                //console.log('NombrePlayer='+player_name,'planet.js->fillReinforcement');
                if(($.inArray(player_name,list_player))==-1){
                  DOM = DOM + '<h3 class="'+type+'">'+player_name+'</h3>';
                  DOM = DOM + '<div>'+renderReinforcements(reinforcement['player_id'],region_id,reinforcements)+'</div>';
                  list_player.push(player_name);
                }
               }
            })

            //$(wrapper).accordion("destroy");
            $(wrapper).html(DOM);
            var subtime = iGlobalTime - xeno['time'];
            //console.log(iGlobalTime,'general.js->fillReinforcement->iGlobalTime');
            //console.log(xeno['time'],'general.js->fillReinforcement->xeno[time]');
            //console.log(subtime,'general.js->fillReinforcement->subtime');
            //Imprimir los countdown
            $.each($('.army_arrival_countdown'),function(key,timer){
                //a) Calculamos cuanto tiempo le queda
                var id=getCountdownId(timer);
                //console.log(id,'general.js->fillReinforcement->id');
                newtime=reinforcements[id]['seconds']-subtime;
                //console.log(newtime,'general.js->fillReinforcement->newtime');
                //b) Renderizamos el Coundown
                var timer_wrap = $('#countdown'+id);
                renderCountdown(timer_wrap,newtime,callCountdownEnd,timer_wrap);
            });

            $(wrapper).accordion({autoHeight: false});
        }
        else{
            //console.log('Refuerzos no estan definidos','general.js->fillReinforcement');
             DOM ='<p class="inner">No hay Refuerzos para esta region</p>';
             $(wrapper).html(DOM);
        }
 }

/*
function getPassedTime(){
  var subtime = iGlobalTime - xeno['time'];//Cuanto tiempo a pasado
  newtime = xeno['battle']['battle_seconds']- subtime;
  return newtime;
}*/

/***************************************************************************
 *Funcion renderCountdown: crea un contador negativo, se indica el padre=dom
 * el numero de segundos = seconds, y callbacks de expiracion y tick, con
 * sus parametros 
***************************************************************************/
function renderCountdown(dom,seconds,callExpiry,expiryParam,callTick,tickParam){
    if(typeof(callExpiry)!='function'){
        callExpiry = function(){};
    }
    if(typeof(callTick)!='function'){
        callTick = function(){};
    }
    
    if(seconds>0){
      //console.warn('Seconds es mayor a 0','general.js');
      //console.warn(dom,'general.js');
      $(dom).countdown({
          until: '+' + seconds + 's',
          format: 'HMS',
          compact: true,
          onExpiry: function(){
            $(dom).countdown('destroy');
            callExpiry(expiryParam)},
          onTick: function(){callTick(tickParam)}
      });
    }
    else{
      //console.warn('Seconds['+seconds+'] es menor a cero','general.js');
      $(dom).countdown('destroy');
      callExpiry(expiryParam);
    }  
}

function fillEnergyData(player){
  $('#PlayerEnergy').text(player['actual_energy']);
  tooltip($('#PlayerEnergyW'), '<span class="title">Energia del Jugador:  </span> La energia del jugador se obtiene sumando las energias del total de colonias que tiene el jugador actual. <p><span class="title">'+player['actual_energy']+'/'+player['max_energy']+' +'+player['rate_energy']+' cada 24 horas</span></p>', 'bottom');
}

function fillResourcesData(resources){
   var res = resources;
  $('#Res1').text(res['res1']);
  $('#Res2').text(res['res2']);
  $('#Res3').text(res['res3']);
  $('#Res4').text(res['res4']);
  $('#Res5').text(res['res5']);
  $('#Res6').text(res['res6']);
  $('#Res7').text(res['res7']);
  $('#Res8').text(res['res8']);
  tooltip($('#Res4W'), '<span class="title">'+_x_resources[4].name+':</span>Recurso activo, se obtiene mejorando las construcciones de extraccion de recursos, se gasta al ser. <p><span class="title">'+res['res4']+'/'+res['max_res4']+' +'+res['rate_res4']+' cada 24 horas</span></p>', 'left');
  tooltip($('#Res5W'), '<span class="title">'+_x_resources[5].name+':</span>Recurso activo, se obtiene mejorando las construcciones de extraccion de recursos, se gasta al ser. <p><span class="title">'+res['res5']+'/'+res['max_res5']+' +'+res['rate_res5']+' cada 24 horas</span></p>', 'left');
  tooltip($('#Res6W'), '<span class="title">'+_x_resources[6].name+':</span>Recurso activo, se obtiene mejorando las construcciones de extraccion de recursos, se gasta al ser. <p><span class="title">'+res['res6']+'/'+res['max_res6']+' +'+res['rate_res6']+' cada 24 horas</span></p>', 'left');
  tooltip($('#Res7W'), '<span class="title">'+_x_resources[7].name+':</span>Recurso activo, se obtiene mejorando las construcciones de extraccion de recursos, se gasta al ser. <p><span class="title">'+res['res7']+'/'+res['max_res7']+' +'+res['rate_res7']+' cada 24 horas</span></p>', 'left');
  tooltip($('#Res8W'), '<span class="title">'+_x_resources[8].name+':</span>Recurso activo, se obtiene mejorando las construcciones de extraccion de recursos, se gasta al ser. <p><span class="title">'+res['res8']+'/'+res['max_res8']+' +'+res['rate_res8']+' cada 24 horas</span></p>', 'left');
  tooltip($('#Res1W'), '<span class="title">'+_x_resources[1].name+':</span>Recurso activo, se obtiene mejorando las construcciones de extraccion de recursos, se gasta al ser. <p><span class="title">'+res['res1']+'/'+res['max_res1']+' +'+res['rate_res1']+' cada 24 horas</span></p>', 'left');
  tooltip($('#Res2W'), '<span class="title">'+_x_resources[2].name+':</span>Recurso activo, se obtiene mejorando las construcciones de extraccion de recursos, se gasta al ser usado <p><span class="title">'+res['res2']+'/'+res['max_res2']+' +'+res['rate_res2']+' cada 24 horas</span></p>', 'left');
  tooltip($('#Res3W'), '<span class="title">'+_x_resources[3].name+':</span>Recurso activo, se obtiene mejorando las construcciones de extraccion de recursos, se gasta al ser usado <p><span class="title">'+res['res3']+'/'+res['max_res3']+' +'+res['rate_res3']+' cada 24 horas</span></p>', 'left');
}
function renderReinforcements(player_id,region_id,armies){

   //var armies = xeno['battles']['sended'];
   var DOM = '<ul class="armiesregionplayer">';
   if(armies!=null){
     $.each(armies,function(key,army){
       //if((army['player_id']==player_id)&&(army['to_region']==region_id)&&army['region_state']=='O'){//No creo que debamos de ser tan excesivos
       if( (army['to_region']==region_id) ){
        var clockid= 'countdown'+key;
        DOM = DOM + '<li><span>'+army['size']+'</span>'+renderCreature(army,null,null,false,'','',true)+' :<span class="army_arrival_countdown" id="'+clockid+'"></span></li>';
       }
     });
   }
   else{
    //console.log('No existen refuerzos? no deberia pasar, la funcion que lo llama pregunta primero si hay refuerzos','planet->renderReinforcement');
   }
   DOM = DOM + '</ul>';
   //console.warn(DOM,'general.js->renderReinforcements');
   return DOM;
 }

function renderUnknowCreature(renderSmall){
 var small=false;
  if(isDefined(renderSmall) && renderSmall ){
    small=true;
  }
  var sufijo = '';
  var assoc='';
  if(small){
      sufijo = '_small';
      assoc = assoc + ' small';
  }
  return  "<img class='"+assoc+"' src='"+g_image_url+"unit/unknow"+sufijo+".png'/>"
}
/* //Reemplazado desde el servidor por army['allegiance'];
function obtainArmyPlayerType(army,myself,player){
    var player_id = army['player_id'];
    var assoc ='';
    if(isDefined(player_id)){
        if(isDefined(myself)){
          //PROPIO,ENEMIGO O ALIADO
          if(myself['id']==player_id){
            assoc = 'myself';
          }
          else{
            assoc = 'enemy';
           //$.log('Es del enemigo player:'+player+' armies:'+armies[unit_id]['player_id']);
          }
          if(player['type']=='A'){
            assoc = 'AI'
          }
        }
    }
    return assoc;
}*/

function renderCreature(army,player,myself,selected,idstr,classstr,renderSmall){
  var small=false;
  if(isDefined(renderSmall) && renderSmall ){
    small=true;
  }

  var army_id = army['id'];
  var UNIT_DOM = '';
  var player_id = army['player_id'];

  var assoc ='';
    if(isDefined(player)){
        if(isDefined(myself)){
          //PROPIO,ENEMIGO O ALIADO
          if(myself['id']==player_id){
            assoc = 'myself';
          }
          else{
            assoc = 'enemy';
           //$.log('Es del enemigo player:'+player+' armies:'+armies[unit_id]['player_id']);
          }
          if(player['type']=='A'){
            assoc = 'AI'
          }
        }
    }


  //ESTA VIVO O MUERTO
  var sufijo ='';
  if(army['state']=='D'){
      sufijo = '_dead';
  }

  if(small){
      sufijo = '_small';
      assoc = assoc + ' small';
  }


  //Calculo del ID
  if(!isDefined(idstr)){
      idstr='';
  }
  else{
      idstr= " id='" +idstr + army_id+ "' ";
     // console.log(idstr,'region.js->renderCreature');
  }

  if(selected){var UNIT_DOM = "<img class='"+classstr+" selected "+assoc+"' "+ idstr + army_id + "' src='"+g_image_url+"unit/"+army['creature_id']+sufijo+".png'/>";}
  else{var UNIT_DOM = "<img class='"+classstr+" "+assoc+"' "+ idstr + " src='"+g_image_url+"unit/"+army['creature_id']+sufijo+".png'/>";}
  return UNIT_DOM
}

function renderAction(action){
    var DOM = "<img src='"+g_image_url+"action/"+action['action_id']+"-small.png'/>";
    return DOM;
}

function renderEndTurn(){
    var DOM = "<img src='"+g_image_url+"action/endturn-small.png'/>";
    return DOM;
}
function renderSmallEnergyIcon(){
    var DOM = '<img alt="Energia" src="'+g_image_url+'/web/icon_mana.png" width="15px" height="15px">';
    return DOM;
}



////////////////////////////////////////////////////////////////////////////////////////
// UTILITARIOS
////////////////////////////////////////////////////////////////////////////////////////
function domExist(string){
    if($(string).get(0)){
        return true;
    }
    else{
        return false;
    } 
}


/**********************************************************************
 *Funcion isDefined: Indica si la variable esta definida correctamente
 * esto incluye arreglos, que estan vacios
 * 02 mayo 2011: Si NO es un EmptyObject igual devolvemos false,
 * verificar donde no funcionaria por que , esto es un warning, se modifico
 * esto para que region.js->fillPath de Cross funcione bien
**********************************************************************/  

function exist(variable){
    return isDefined(variable);
}

function isDefined(variable){
  if(typeof variable =='undefined')  {
      //console.warn('general.js isDefined variable es undefined FALSE');
      return false;
  }
  
  else if(variable != null){
        if(typeof(variable)=='function'){
            return true;
        }
        else if(variable.length != 0){
            //return variable;//No es necesario pero bueno
            //console.warn(variable,'general.js isDefined variable logitud es amyor a cero TRUE');
            return true;
        }
        else{
          //console.log(typeof(variable),'general.js isDefined length es cero');
          if(typeof(variable)=='array'){
            //console.warn(variable,'general.js isDefined variable es arreglo FALSE');
            return false;
          }
          else{
              if(!$.isEmptyObject(variable)){
                //console.warn(variable,'general.js isDefined length es cero pero  es jquery NO objeto vacio FALSE');
                return false;
              }
              else{
              // console.warn(variable,'general.js isDefined length es jquery objeto vacio FALSE');  
               return false;
              }
           }
        }
  }
  else{
      //console.warn(variable,'general.js isDefined variable es null FALSE');
      return false;
  }
}  

 function callCountdownEnd(timer_wrap){
   $(timer_wrap).html('Llego a Region');
 }

function getCountdownId(dom){
   var id_raw = $(dom).attr('id');
  if (typeof(id_raw)!='undefined')
  {
    //subcadena es countdown##
    return parseInt(id_raw.substring(9));}
  else{
    return false;
  }
 }

function tooltip(dom,message,position){
    if(isDefined(position)){
        switch(position){
            case 'bottom':
            dom.qtip({
                    content: message,
                    position: {at: "bottom center",my: "top center"},
                    show: {delay: 0},
                    style: {tip: {corner: "topMiddle",width: 12,height: 18}}
            });
            break;
            
            case 'left':
            dom.qtip({
                content: message,
                position: {at: "left center",my: "right center"},
                show: {delay: 0},
                style: {tip: {corner: "rightMiddle",width: 18,height: 12}}
            });
            break;    
            
            case 'right':
                
            dom.qtip({
                content: message,
                position: {at: "right center",my: "left center"},
                show: {delay: 0},
                style: {tip: {corner: "leftMiddle",width: 18,height: 12}}
            });    
            break;    
            
            case 'top':
            dom.qtip({
                content: message,
                position: {at: "top center",my: "bottom center"},
                show: {delay: 0},
                style: {tip: {corner: "bottomMiddle",width: 12,height: 18}}
            });    
            
            break;  
            
            default :
                console.error('general.js->tooltip La position no esta definida o es['+position+']');
            break;
                
        }
        
    }
        
}




function bubbletip(dom,message,position,classes,callback){
    if(!isDefined(classes)){
        var classes = '';
    }
    if(!isDefined(callback)){
        var callback = null;
    }
    
    
    if(isDefined(position)){
        switch(position){
            case 'bottom':
            dom.qtip({
                     prerender: true,
                    content: message,
                    position: {at: "bottom center",my: "top center"},
                    show: {delay: 0,event: false,ready: true},
                    hide: false, // Don't specify a hide event either!
                    style: {
                        tip: {corner: "topMiddle",width: 12,height: 18},
                        classes:classes
                    },
                    events: {
                      render: function() {
                         callback();
                      }
                    }
            });
            break;
            
            case 'left':
            
            dom.qtip({
                prerender: true,
                content: message,
                position: {at: "left center",my: "right center"},
                show: {delay: 100,event: false,ready: true},
                hide:false,
                style: {
                    classes:classes,
                    tip: {corner: "rightMiddle",width: 18,height: 12}
                        },
                events: {
                  render: function() {
                     callback();
                  }
                }
            });
            
            break;    
            
            case 'right':
                
            dom.qtip({
                prerender: true,
                content: message,
                position: {at: "right center",my: "left center"},
                show: {delay: 0,event: false,ready: true},
                hide: false, // Don't specify a hide event either!
                style: {tip: {corner: "leftMiddle",width: 18,height: 12},
                        classes:classes},
                events: {
                  render: function() {
                     callback();
                  }
                }
            });    
            break;    
            
            case 'top':
            dom.qtip({
                prerender: true,
                content: message,
                position: {at: "top center",my: "bottom center"},
                show: {delay: 0,event: false,ready: true},
                hide: false, // Don't specify a hide event either!
                style: {tip: {corner: "bottomMiddle",width: 12,height: 18},
                        classes:classes},
                events: {
                  render: function() {
                     callback();
                  }
                }
            });    
            
            break;  
            
            default :
                console.error('general.js->tooltip La position no esta definida o es['+position+']');
            break;
                
        }
        
    }
        
}

    /*
     * Clase para inicializar recursos de imagenes, el funcionamiento es simple, en la parte
     * de arriba de este archivo general.js inicializamos una variable global y la asignamos a esta
     * funcion. Le pasamos
    */

    /**
         A database for the external resources used by the game
         @author <a href="mailto:matthewcasperson@gmail.com">Matthew Casperson</a>
         @class
     */
    var imagenes = null;
    
     var gResourceManager = {
         imagesStr: new Array(),
         images: new Array(),
         constructor : function ResourceManager(){},
         add : function(name,url){this.imagesStr[name] = url},
         start : function(){
            // for each image, call preload()
            for (image in this.imagesStr )
            {
                 // create new Image object and add to array
                 var thisImage = new Image;
                 thisImage.src = this.imagesStr[image];
                 this.images[image] = thisImage;
                 
          }
          imagenes = this.images;
        },
        get : function(key){
            //console.log(this,'general gResourceManager this');
            //console.log(this.images,'general gResourceManager this.images');
            //console.log(key,'general gResourceManager key');
            //console.log(this.images[key],'general gResourceManager this.images[key]');
            var thisImage = new Image;
             thisImage.src = this.imagesStr[key];
             this.images[key] = thisImage;
            return this.images[key];
        }
    }
    
function getId(dom){
  if($(dom).hasClass('army')){//Si es un Army
    var id_raw = $(dom).attr('id');
    if (typeof(id_raw)!='undefined'){return parseInt(id_raw.substring(1));}
    else{return false;} 
  }
  else if($(dom).hasClass('army_turn')){//Si es un Turno
    var id_raw = $(dom).attr('id');
    if (typeof(id_raw)!='undefined'){return parseInt(id_raw.substring(1));}
    else{return false;}
  }
  else if($(dom).hasClass('terrain')){//Si es un Terreno
    var id_raw = $(dom).attr('id');
    if (typeof(id_raw)!='undefined'){return parseInt(id_raw.substring(1));}
    else{return false;}
  }
  else if($(dom).hasClass('action')){//Si es un Terreno
    var id_raw = $(dom).attr('id');
    if (typeof(id_raw)!='undefined'){return parseInt(id_raw.substring(1));}
    else{return false;}
  }
    else if($(dom).hasClass('building')){//Si es un Terreno
    var id_raw = $(dom).attr('id');
    if (typeof(id_raw)!='undefined'){return parseInt(id_raw.substring(1));}
    else{return false;}
  }
   else if($(dom).hasClass('construction')){//Si es un Terreno
    var id_raw = $(dom).attr('id');
    if (typeof(id_raw)!='undefined'){return parseInt(id_raw.substring(1));}
    else{return false;}
   }
   else if($(dom).hasClass('colonyid')){//Si es un Terreno
    var id_raw = $(dom).attr('id');
    if (typeof(id_raw)!='undefined'){return parseInt(id_raw.substring(8));}
    else{return false;}   
  }
    else if($(dom).hasClass('armyWrapper')){//Si es un Terreno
    var id_raw = $(dom).attr('id');
    if (typeof(id_raw)!='undefined'){return parseInt(id_raw.substring(1));}
    else{return false;}   
  }
  else if($(dom).hasClass('team')){//Si es un Terreno
    var id_raw = $(dom).attr('id');
    if (typeof(id_raw)!='undefined'){return parseInt(id_raw.substring(1));}
    else{return false;}   
  }
  else{
    if(!isDefined(dom)){
      //Error?
    }
    else{
      console.warn(dom,'Error en getId , la clase del objeto DOM no ha sido reconocida, por lo tanto no entro a los if getId region.js');
    }
  }  
  
}

function calculateCenter(dom){
    var o = dom.offset();
    var h= dom.height();
    var w= dom.width();
    var data = new Object();
    data.top = o.top + ( (h/2) ) ;
    data.left = o.left + ( (w/2) ) ;
    return data;
}
function moveCenter(){
    
}

function mapIndex(total_x, y, x) {
    var index = (y * total_x) + (x + 1) - 1;
    return index;
}