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
    if(validateInitialRegionBattleXeno()){
        
    }
});

/***********************************************************
 * PRINCIPAL
 **********************************************************/
function validateInitialRegionBattleXeno(){
    if(validateInitialXeno())
    {
               phaseManager(true,{initial:true});
      $('#ToggleHistorial').click(function(){ToggleHistorial()});
      $('#HistorialMsg').hide();
      $('#HistorialMsg li').live('hover',function(event){
          if(event.type=='mouseenter'){
               var army_id = $(this).attr('army_id');
               iluminateArmy(army_id);
          }
          else if(event.type=='mouseleave'){
              var army_id = $(this).attr('army_id');
              iniluminateArmy(army_id);
          }
      });

      $('.info').live('click',function(){
        //console.log(this,'region.js->constr');
        //console.log($(this).attr('id'),'region.js->constr');
        var id_raw =$(this).attr('id');
        var id = id_raw.substring(4);
        //console.log(id,'region.js->constr');
        selectArmy(id);
      });


      //Boton de Tutorial
      $('.tutorial-switch-button.button-on').live('click',function(){
         callTutorialOff();
         //console.log('Tutorial ON');
      });

      $('.tutorial-switch-button.button-off').live('click',function(){
         callTutorialOn();
         //console.log('Tutorial Off');
      });


      //Lock the Ajax
      $('body').ajaxSend(function(e, xhr, settings) {
        if (settings.url == g_region_url) {
          //console.info(settings,'Se envio un AJAX region.js');
          lockHiddenStatus();
        }
      });

      //Unlock the Ajax
      $('body').ajaxComplete(function(e, xhr, settings) {
        if (settings.url == g_region_url) {
          //console.info(settings,'ajaxComplete puede empesar un callHiddenStatus');
          //g_ajax_hidden_status_lock=false;
        }
      });


      //Unlock the Ajax
      $('body').ajaxError(function(e, xhr, settings) {
        if (settings.url == g_region_url) {
          //console.error(settings,'Error en el envio de datos');
          unlockHiddenStatus();
        }
      });  
      //callHiddenStatus();
        setInterval(callHiddenStatus,_X_HIDDEN_TIME);//A los _X_HIDDEN_TIME segundos, la variable
     //es creada en region.php

     //Inicializar Recurso
     gResourceManager.add('sword','img/action/animated_attack2.gif');
     gResourceManager.add('archer','img/action/animated_range.gif');
     gResourceManager.add('ajaxwait','img/action/ajax_wait.gif');
     gResourceManager.start();
    }
    else{
        return false;
    }
}
