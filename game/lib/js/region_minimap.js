/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(function(){
    $('#MinimapContainer').prepend('<div id="Minimap" class="infopanel"></div><div id="MinimapActions" class="clearfix"></div>');
    renderMinimap();
    renderMinimapViewPort();
    $(document).bind('phase.manager.forced',function(){
        // console.log('minimap phase.battle.forced');
        renderArmyInMinimap();
        renderColonyInMinimap();
    });

    $(document).bind('xhelos.map.move',function(e,data){
        renderMinimapViewPort(data);
    });
})

function renderMinimapViewPort(position){
    
    //console.log(renderMinimapViewPort);
    
    if(!isDefined(position)){
        var position = new Array();
        position['x']=0;
        position['y']=0;
    }
    //Calculos previos
    var tileSize = Math.floor(150/mapwidth);
    var tileHeight =  g_tile_size;
    var tileWidth = g_tile_size;
    var mapmaximunwidth =  xeno['region']['x']*tileWidth;//Obtenerlo de un tile;
    var mapmaximunheight =  xeno['region']['y']*tileHeight;
    var viewPortFactorWidth = ( mapmaximunwidth /  $('#ViewPort').width() ) ;
    var minimapViewPortWidth = ( $('#Minimap').width() / viewPortFactorWidth );
    var viewPortFactorHeight = ( mapmaximunheight /  $('#ViewPort').height() ) ;
    var minimapViewPortHeight = ( $('#Minimap').height() / viewPortFactorHeight );
    
    //Preguntar si existe, si no existe entonces crearlo y moverlo sin animacion
    if(!domExist('#MinimapViewPort')){
        $('#Minimap').append('<div id="MinimapViewPort"></div>');
        $('#MinimapViewPort').width(minimapViewPortWidth);
        $('#MinimapViewPort').height(minimapViewPortHeight);
        $('#MinimapViewPort').css('z-index',20);
        
        //Calcular el Punto Inicial del ViewPort
        var tileFactorWidth =  tileHeight/tileSize;
        var tileFactorHeight =  tileWidth/tileSize;
        var battleX = position['x'];
        var battleY = position['y'];
        var initX = (battleX/tileFactorWidth)*-1;
        var initY = (battleY/tileFactorHeight)*-1;
        //console.log('battleX['+typeof(battleX)+'] battleY['+typeof(battleY)+'] minimapPortWidth['+typeof(minimapViewPortWidth)+'] minimapPortHeight['+typeof(minimapViewPortHeight)+'] initX['+typeof(initX)+'] initY['+typeof(initY)+']');
        //console.log('battleX['+battleX+'] battleY['+battleY+'] initX['+initX+'] initY['+initY+']');
        $( '#MinimapViewPort' ).css('left',initX);
        $( '#MinimapViewPort' ).css('top',initY);
        
        //Le damos behaviour Drag&Drop
        $('#MinimapViewPort').draggable({
            containment: '#Minimap',
            drag: function(event, ui) {
                //console.log(event,'event minimapmap');
                var x = _makeCoords($('#MinimapViewPort').css('left'));
                var y = _makeCoords($('#MinimapViewPort').css('top'));

                var moveBattleMapX = (x / tileSize) * tileWidth;
                var moveBattleMapY = (y / tileSize) * tileHeight;
                //console.log(moveBattleMapX,'event moveBattleMapX');
                //console.log(moveBattleMapY,'event moveBattleMapY');
                $( '#BattleMap' ).css('left',moveBattleMapX);
                $( '#BattleMap' ).css('top',moveBattleMapY);

                $( '#ColorMap' ).css('left',moveBattleMapX);
                $( '#ColorMap' ).css('top',moveBattleMapY);
            }
        });
        
    }
    else{
        var tileFactorWidth =  tileHeight/tileSize;
        var tileFactorHeight =  tileWidth/tileSize;
        var battleX = position['x'];
        var battleY = position['y'];
        var initX = (battleX/tileFactorWidth)*-1;
        var initY = (battleY/tileFactorHeight)*-1;
        
        //console.log('battleX['+typeof(battleX)+'] battleY['+typeof(battleY)+'] minimapPortWidth['+typeof(minimapViewPortWidth)+'] minimapPortHeight['+typeof(minimapViewPortHeight)+'] initX['+typeof(initX)+'] initY['+typeof(initY)+']');
        //console.log('battleX['+battleX+'] battleY['+battleY+'] initX['+initX+'] initY['+initY+']');
        
        $('#MinimapViewPort').animate({
            left:initX,
            top:initY
        },1500,'easeOutQuart');
    //$('#MinimapViewPort').remove();
        
    }
}

function renderArmyInMinimap(){
    //[TODO]Quitamos esta nota para el refresh :D
    $('#Minimap').children('.tile').removeClass('enemy').removeClass('myself').removeClass('AI');
    
    for(army_index in xeno['armies']){
        //console.log(xeno['armies'][army_index],'minimap.js renderArmyInMinimap');
        var x = xeno['armies'][army_index]['position_x'];
        var y = xeno['armies'][army_index]['position_y'];
        var army = xeno['armies'][army_index];
        var type = army['allegiance'];
        //console.log(temp_arm[army_index],'minimap.js borramos');
        //delete temp_arm[army_index];
        //console.log($('#Minimap .tile.x'+x+'.y'+y),'minimap.js renderArmyInMinimap');
        $('#Minimap .tile.x'+x+'.y'+y).addClass(type);
    }   
}

function renderColonyInMinimap(){
    $('#Minimap').children('.colony');
   
    for(colony_index in xeno['colonies']){
        //console.log("renderColonyInMiniMap");
        //console.log(xeno['armies'][army_index],'minimap.js renderArmyInMinimap');
        var x = xeno['colonies'][colony_index]['region_x'];
        var y = xeno['colonies'][colony_index]['region_y'];
        var colony = xeno['colonies'][colony_index];
        var allegiance = xeno['colonies'][colony_index]['allegiance'];
        /*console.log(colony);*/
        //console.log(temp_arm[army_index],'minimap.js borramos');
        //delete temp_arm[army_index];
        //console.log($('#Minimap .tile.x'+x+'.y'+y),'minimap.js renderArmyInMinimap');
        
        //$('#Minimap .tile.x'+x+'.y'+y).addClass(type);
        var imgDOM = '<img class="colony minimap '+allegiance+'" src="img/building/colony_minimap.png">';
        $('#Minimap .tile.x'+x+'.y'+y).append(imgDOM);
    }   
}


function renderMinimap(){
    $('#Minimap').children('.tile').remove();

    mapwidth = xeno['region']['x'];
    mapheight = xeno['region']['y'];

    var tileSize = Math.floor(150/mapwidth);
    $('#Minimap').width(tileSize*mapwidth);
    $('#Minimap').height(tileSize*mapheight);
    
    tileHeight =  g_tile_size;
    tileWidth = g_tile_size;

    factorx = $('#Minimap').height()/xeno['region']['x'];
    factory = $('#Minimap').width()/xeno['region']['y'];

    //for(y=0;y<xeno['region']['y'];y++){
    //for(x=0;x<xeno['region']['x'];x++){

    for(y=xeno['region']['y']-1;y>=0;y--){
        for(x=xeno['region']['x']-1;x>=0;x--){
            var index = (y*xeno['region']['x'])+ x;
            var terrainType = xeno['region']['map'][index];
            $('#Minimap').prepend('<div x="'+x+'" y="'+y+'" class="x'+x+' y'+y+' tile t'+terrainType+'" style="height:'+tileSize+'px; width:'+tileSize+'px"></div>');
        }
    }
    renderArmyInMinimap();
    renderColonyInMinimap();
    //mapmaximunwidth =  xeno['region']['x']*tileWidth;//Obtenerlo de un tile;
    //mapmaximunheight =  xeno['region']['y']*tileHeight;

    //$('#BattleMap').width(mapmaximunwidth);
    //$('#BattleMap').height(mapmaximunheight);
    
    $('#Minimap').click(function(e){
        var orig = e.originalEvent.target;
        //console.log(orig,'click al minimap');
        if($(orig).is('div.tile')){
            var mapDom = getTile($(orig).attr('x'),$(orig).attr('y'));
            panningToDom(mapDom);
        }
        
    });
    
    
    

}
//http://wayfarerweb.com/js/mapbox.js
function _makeCoords(s) {
    s = s.replace(/px/, "");
    s = 0 - s;
    return s;
}


function fillMinimapSquareAroundTile(x,y,rango,domClass){
    if(!isDefined(domClass)){
        domClass = false;
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

/**/
 /*function fillMinimapSquareAroundTile(x,y,rango,weightit){
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
  }*/