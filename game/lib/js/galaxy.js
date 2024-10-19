
var g_action_url = g_game_url+'ajax/ajaxBattle.php?random=';//direccion de accion [TODO]Hacer un json precharge de esto
var g_reaction_url = g_game_url+'ajaxajaxReaction.php';//direccion de reaccion [TODO]Hacer un json precharge de esto
var g_image_url = g_game_url+'img/';


$(document).ready(function(){
    
    if(validateInitialGalaxyXeno()){
        $('.star').click(function(){starEngine(this)});
        initStarlane();
        $('#GalaxySystem .name').hover(
            function() {
                $(this).addClass('ui-state-hover');
            },
            function() {
                $(this).removeClass('ui-state-hover');
            }
            ); 
  }
    
});

/***********************************************************
 * PRINCIPAL
***********************************************************/
function validateInitialGalaxyXeno(){
    if(validateInitialXeno())
    {
         return true;
    }
    else{
        return false;
    }
    
}


function starEngine(star_dom){
  var id = getStarId(star_dom);
  activeStar(star_dom);
  star = xeno['stars'][id];
  
  myself = xeno['player'];
  players = xeno['players'][id];
  //var DOM = '<h1>'+star['name']+'</h1><div class="planetinfo"><img src="'+g_image_url+'planet/planet'+star['size']+planet['color']+'.png" />';
  var DOM = '<h1 class="ui-state-active">'+star['name']+'</h1>';
  DOM = DOM +'<div class="inner">'+renderStar(id)+'<p  class="desc">'+star['desc']+'</p>';
  if(star['total_planets']>0){
    DOM = DOM + '<form id="VisitStarForm" name="VisitStarForm" action="star.php?star_id='+star['id']+'" method="get">';  
    DOM = DOM + '<input id="VisitStar'+star['id']+'" class="visitstar button" type="submit" value="Visitar '+star['name']+'" />';
    DOM = DOM + '<input name="star_id" type="hidden" value="'+star['id']+'" />';
    DOM = DOM + '</form></div>';
    DOM = DOM + '<h2 class="ui-state-active">Jugadores</h2>';
    DOM = DOM + '<div class="inner">'+renderPlayers(players,myself)+'</div>';  
  }
  $('#StarInfo').html(DOM);
    
}

/***********************************************************
 * RENDERS
 **********************************************************/
 function renderStar(id){
   var DOM = '<img src="'+g_image_url+'star/smallstar'+xeno['stars'][id]['color']+'.png "/>';
   return DOM;
 }
 
/***********************************************************
 * ACTIVADORES
 **********************************************************/
  function activeStar(dom){
    if($(dom).hasClass('active')){
      deactiveStars();  
    }
    else{
      deactiveStars();
      $(dom).toggleClass("active");
    }
    id= getStarId(dom);
    getStarCenterLog(id);
  }
  
  
  function deactiveStars(){
    $('.star.active').removeClass('active');//Quita lo seleccionado de una accion
  }

 /***********************************************************
 * OBTENEDORES DE ID
 **********************************************************/
function getStarId(dom){
  var id_raw = $(dom).attr('id');
  if (typeof(id_raw)!='undefined')
  {
    return parseInt(id_raw.substring(4));}
  else{
    return false;
  }
}
function getStarDom(id){
  var dom = $('#Star'+id);
  return dom;
}

 /***********************************************************
 * GRAPHAEL
 **********************************************************/
 function getStarCenter(id){
   var dom = getStarDom(id);
   var pos = $(dom).position();
   
  var width = $(dom).width();
  var height = $(dom).height();
  
  pos.centerY = parseInt(pos.top+(height/2))-15;
  pos.centerX = parseInt(pos.left+(width/2))-3;
  
   /*console.log(pos);
   console.log('left['+pos.left+'] top['+pos.top+']','Position');
   console.log('width['+width+'] height['+height+']','Size');
   console.log('y['+pos.center_y+'] x['+pos.center_x+']','Center');*/
   return pos;
 }
  function getStarCenterLog(id){
   var dom = getStarDom(id);
   var pos = $(dom).position();
   
  var width = $(dom).width();
  var height = $(dom).height();
  
  pos.centerY = parseInt(pos.top+(height/2));
  pos.centerX = parseInt(pos.left+(width/2));
  
   /*console.log(pos);
   console.log('left['+pos.left+'] top['+pos.top+']','Position');
   console.log('width['+width+'] height['+height+']','Size');
   console.log('y['+pos.centerY+'] x['+pos.centerX+']','Center');*/
   return pos;
 }
 
 function initStarlane(){
  
  var sizex = xeno['galaxy']['sizex']; 
  var sizey = xeno['galaxy']['sizey'];
  
  var width = $('#GalaxySystem').width();
  var height = $('#GalaxySystem').height();
  
  var starWidth = width/sizex-2;
  var starHeight = height/sizey-2;
   
  //$("#GalaxySystemInner").remove(); 
  var myMap = Raphael("GalaxySystem", 750, 600);
  /*for(var y=1;y<=sizey;y++){
    for(var x=1;x<=sizex;x++){
      var c = myMap.rect(starWidth*(x-1),starHeight*(y-1),starWidth,starHeight).attr({stroke:"#111","stroke-width":1});
      //c.node.setAttribute('class','blanket'); 
      }
    }*/
  //console.log(xeno['starlanes']);
  lanes = xeno['starlanes'];
  for(lid in lanes){
    //console.log(lanes[lid]);
    posStart = getStarCenter(lanes[lid]['start']);
    posEnd = getStarCenter(lanes[lid]['end']);
    var myLane = myMap.path("M"+posStart.centerX+","+posStart.centerY+"L"+posEnd.centerX+","+posEnd.centerY).attr({stroke:"#555","stroke-width":5});
  }
  
  

   
   /*var myMarker = myMap.ellipse(513.859,35.333, 7, 7).attr({
      stroke: "none", opacity: .7,fill: "#f00" });*/
  //return myMarker;
}

/*
  public getStarFromAxis(x,y){
    //global $firephp;
    var stars = xeno['stars'];
    for(var index in stars){
      if((stars['axis_x']==x)&&(stars['axis_y']==y)){
        return star;
      }
    }
    return false;
  }
*/