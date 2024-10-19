
var g_action_url = g_game_url+'lib/ajaxBattle.php?random=';//direccion de accion [TODO]Hacer un json precharge de esto
var g_reaction_url = g_game_url+'lib/ajaxReaction.php';//direccion de reaccion [TODO]Hacer un json precharge de esto
var g_image_url = g_game_url+'img/';


$(document).ready(function(){
    if(validateInitialStarXeno()){
        $('.object').click(function(){planetEngine(this)});
        //$('li.ability').live('click',function(){activeAbility(this)});

          $('#StarSystem .name a').hover(
          function() { $(this).addClass('ui-state-hover'); },
          function() { $(this).removeClass('ui-state-hover'); }
          ); 
    }
});

/***********************************************************
 * PRINCIPAL
***********************************************************/
function validateInitialStarXeno(){
    if(validateInitialXeno())
    {
         return true;
    }
    else{
        return false;
    }
}


function planetEngine(planet_dom){
  var id = getPlanetId(planet_dom);
  activePlanet(planet_dom);
  planet = xeno['planets'][id];
  var DOM = '<h1 class="ui-state-active">'+planet['name']+'</h1 class="ui-state-active"><div class="inner"><img src="'+g_image_url+'planet/planet'+planet['size']+planet['color']+'.png" />';
  DOM = DOM +'<div class="planettext"><p>'+planet['desc']+'</p></div>';
  if(planet['total_regions']>0){
    DOM = DOM + '<form id="VisitPlanetForm" name="VisitPlanetForm" action="planet.php?selPlanet='+planet['id']+'" method="get">';  
    DOM = DOM + '<input id="VisitPlanet'+planet['id']+'" class="visitplanet" type="submit" value="Visitar '+planet['name']+'" />';
    DOM = DOM + '<input name="planet_id" type="hidden" value="'+planet['id']+'" />';
    DOM = DOM + '</form>';  
  }
  
  DOM = DOM +'</div>';
  DOM = DOM +'<h2 class="ui-state-active">Recursos</h2>';
  DOM = DOM + '<div class="planetresources inner">'+renderPlanetResources(id)+'</div>';
  DOM = DOM +'<h2 class="ui-state-active">Jugadores</h2>';
  DOM = DOM + '<div class="planetplayers inner">'+renderPlanetPlayers(id)+'</div>';
  DOM = DOM +'<h2 class="ui-state-active">Colonias</h2>';
  DOM = DOM + '<div class="planetcolonies inner">No existen colonias en el planeta</div>';
  $('#StarInfo').html(DOM);
    
}

function renderResource(resource_id){
  var DOM = '<img class="resource" src="'+g_image_url+'resource/'+resource_id+'_1.png ">';  
  return DOM;
}
function renderPlanetResources($planet_id){
  var DOM ='<ul id="PlanetTotalResources" class="ui-helper-clearfix">';
  var resources = xeno['resources'][$planet_id];
  if (typeof(resources[0])!='undefined')
  {
    //El planeta no tiene recursos
    DOM = DOM + '<li>'+resources[0]+'</li>';
  }
  else{
    $.each(resources,function(key,resource){
      DOM = DOM + '<li>'+resource['quality_sum']+renderResource(key)+'</li>';
    });
  }
  DOM = DOM +'</ul>';
  return DOM;
}
/***********************************************************
 * ACTIVADORES
 **********************************************************/
  function activePlanet(dom){
    if($(dom).hasClass('active')){
      deactivePlanets();  
    }
    else{
      deactivePlanets();
      $(dom).toggleClass("active");
    }
  }
  function deactivePlanets(){
    $('.object.active').removeClass('active');//Quita lo seleccionado de una accion
  }
/***********************************************************
 * Jugadores
 **********************************************************/
 
 function renderPlanetPlayers($planet_id){
   return renderPlayers(xeno['players'][$planet_id],xeno['player']);
   /*var DOM ='<ul id="PlanetPlayers">';
   var players = xeno['players'][$planet_id];
   var myself = xeno['player'];
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
            DOM = DOM + '<li class="'+who+'">'+player['username']+'</li>';   
       });
    }
  DOM = DOM +'</ul>';
  return DOM;*/ 
 }
/***********************************************************
 * OBTENEDORES DE ID
 **********************************************************/
function getPlanetId(dom){
  var id_raw = $(dom).attr('id');
  if (typeof(id_raw)!='undefined')
  {
    return parseInt(id_raw.substring(6));}
  else{
    return false;
  }
}
