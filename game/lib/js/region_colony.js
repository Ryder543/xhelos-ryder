/*var debug = '';
var g_image_url = 'img/';

var g_ajax_region_lock=false;//lock de ajax, no se puede enviar un ajax hasta que este completo
var g_ajax_hidden_status_lock=false;//lock de ajax, no se puede enviar un ajax hasta que este completo
var last_xeno;
var g_tile_size = 60;
var g_first_battle_phase = false;
var g_xhr_hidden_status = false; //Variable Global que contiene la ultima llamada a mi AJAX*/

var g_region_colony_url = g_game_url+'ajax/ajaxRegionColony.php?random=';

$(document).ready(function(){
    if(validateInitialRegionColonyXeno()){
        fillColoniesInMap();
    }   
    $('.colony').live('click',function(){actionManagerColony(this)}); 
});

/***********************************************************
 * PRINCIPAL
 **********************************************************/
function validateInitialRegionColonyXeno(){
    if(validateInitialXeno())
    {
         return true;
    }
    else{
        return false;
    }
}

/***********************************************************
 * AJAX
 **********************************************************/
function callCreateColony(){
  if(g_xhr_hidden_status){ //Derrepente esto se llama primero, antes que un hidden
       g_xhr_hidden_status.abort();
   }
   waitMsg();
   var region_id = xeno['region']['id'];
   
   g_xhr_hidden_status = $.ajax({
      type:'post',
      cache:false,
      data:{'ajax_action':'createColony','region_id':region_id},
      url:g_region_colony_url,
      dataType:'json',
      error:function(XMLHttpRequest, textStatus, errorThrown){
          processAjaxError(XMLHttpRequest, textStatus, errorThrown);
      },
      success:function(data){
          //console.log("Region_colony completado");
          processAjaxAnswer(data);
      }
    })
 }
 
 
 function fillColoniesInMap(){
    var colonies = xeno['colonies'];
    //console.log('region.js fillArmiesInMap');
    if(isDefined(colonies)){
        for(var colony_id in colonies)
        {
          //console.log(armies[unit_id],'fillArmiesInMap');
          if(!isNaN(colony_id)){//Solo regule los ID
            if(colonies[colony_id].type=='P' || colonies[colony_id].type=='S'){
              // Si esta en el [P]rimario o es [S]ecundario
              createColony(colony_id);
            }
 
          }
        }
        $('table img.unit').mouseenter(function(){showUnitBubble(this)});
        $('table img.unit').mouseleave(function(){hideUnitBubble(this)});
    }
 }
 
function createColony(colony_id){
    destroyColony(colony_id);
    /*var selected_id = getSelectedArmyId();
    var selected =false;
    if(selected_id == colony_id){selected = true}*/
    var colonies = xeno['colonies'];
    var tile = getTile(colonies[colony_id]['region_x'],colonies[colony_id]['region_y'])

     var colony = xeno['colonies'][colony_id];
     /*var player = xeno['players'][colony['player_id']];
     var myself = xeno['player'];*/

    var UNIT_DOM = renderColony(colony,true,'col','colony');
   // console.log(tile);
   // console.log(UNIT_DOM);
    $(tile).append(UNIT_DOM);
  }  
 
 function destroyColony(colony_id){
    var unit = $('img#colony'+colony_id);
    $(unit).remove();
}



function renderColony(colony,selected,idstr,classstr,renderSmall){
  var small=false;
  if(isDefined(renderSmall) && renderSmall ){
    small=true;
  }

  var colony_id = colony['id'];
  var UNIT_DOM = '';
  //var player_id = colony['player_id'];

  var assoc ='';
    /*if(isDefined(player)){
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
    }*/

    assoc = colony["allegiance"];

  //ESTA VIVO O MUERTO
  var sufijo ='';
  if(colony['state']=='D'){
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
      idstr= " id='" +idstr + colony_id+ "' ";
     // console.log(idstr,'region.js->renderCreature');
  }

  //if(selected){var UNIT_DOM = "<img class='"+classstr+" selected "+assoc+"' "+ idstr + colony_id + "' src='"+g_image_url+"building/colony"+colony['type']+sufijo+".png'/>";}
  if(selected){var UNIT_DOM = "<img src='"+g_image_url+"building/colony"+colony['type']+sufijo+".png' class='"+classstr+" "+assoc+" selected"+"' "+ idstr + " />";}  
  else{var UNIT_DOM = "<img class='"+classstr+" "+assoc+"' "+ idstr + " src='"+g_image_url+"building/colony"+colony['type']+sufijo+".png'/>";}
  return UNIT_DOM;
}


function actionManagerColony(target_tile){ 
  $(".colony").removeClass("selected");
  $(target_tile).addClass("selected");
  panningToDom(target_tile);
}