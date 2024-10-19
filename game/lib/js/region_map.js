/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


function MoveBattleMap() {

    var tileSize = Math.floor(150/mapwidth);
    var tileHeight =  g_tile_size;
    var tileWidth = g_tile_size;

    $('#BattleMap').draggable({
            
        cursor: "move", 
        containment: '#body', 
        scroll: false,
            
        drag: function(event, ui) {
            //console.log(event,'event minimapmap');
            //console.log(ui);
            var x = _makeCoords($('#BattleMap').css('left'));
            var y = _makeCoords($('#BattleMap').css('top'));
                
			//console.log ($('#BattleMap').css('left'));
			//console.log("x="+x+"y="+y);

            var limiteAltura = g_tile_size*(xeno.region.y)+_makeCoords($( '#ViewPort' ).css('height'));
            var limiteAncho = g_tile_size*(xeno.region.x)+_makeCoords($( '#ViewPort' ).css('width'));
                
            if(x < 0){
				//console.log("x("+x+")");
                //console.log("x("+y+")");
                $( "#BattleMap" ).css('left',x+'px');
                //console.log("xx("+$( "#BattleMap" ).css('left')+")");
                //console.log("yy("+$( "#BattleMap" ).css('top')+")");

                return false;
            } else{
            if(y < 0){
           
                $( "#BattleMap" ).css('top',y+'px');

                return false;
            } 
           }
            if(x > limiteAncho){
                               
                $( "#BattleMap" ).css('left',(-1)*limiteAncho+'px');
                
                return false;
            } 
            
            
            if(y > limiteAltura ){
                               
                $( "#BattleMap" ).css('top',(-1)*limiteAltura+'px');
                    
                return false;
            } 
            
            var moveMinimapViewPortX = (x * tileSize) / tileWidth;
            var moveMinimapViewPortY = (y * tileSize) / tileHeight;
            //console.log(moveBattleMapX,'event moveBattleMapX');
            //console.log(moveBattleMapY,'event moveBattleMapY');
            $( '#MinimapViewPort' ).css('left',moveMinimapViewPortX);
            $( '#MinimapViewPort' ).css('top',moveMinimapViewPortY);
                 
            $( '#ColorMap' ).css('left',moveMinimapViewPortX);
            $( '#ColorMap' ).css('top',moveMinimapViewPortY);
                
        }
            
    });

}