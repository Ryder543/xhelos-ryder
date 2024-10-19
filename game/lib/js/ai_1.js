/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
var _x_ai_tiles = 'pepe';
var g_ai_url = 'ajax/ajaxAI.php';
var _x_ai_map; //variable que contiene toda la infoormacion sobre la IA
$(document).ready(function(){
    fillAIPanel();
    fillAIMinimap();
    $('#iamap').live('click',checkAIMap);
    $('#enemymap').live('click',checkEnemyMap);
    $('#calc').live('click',checkCalc);
    $('#test').live('click',check);

    if(xeno['battle']['phase']=='B'){//No se puede ejecutar en T o E
        $(document).trigger('xeno.newXenoBattle',xeno);
    }
})

function fillAIMinimap(){
    $('#Minimap').after('<button class="right box" id="ObtenerAIActionMinimap">Ver</button><button class="right box" id="RemoverAIActionMinimap">X</button>');
    $('#RemoverAIActionMinimap').click(function(){
        $('#ColorMapMinimap').remove();
        //console.log('Colormap removido');
    });
    $('#ObtenerAIActionMinimap').click(function(){
        $('#RemoverAIActionMinimap').click();
        if(typeof(_x_ai_map != 'string' )){
            $('#Minimap').prepend('<div id="ColorMapMinimap"></div>');

             var mapWidth = $('#Minimap').width();
             var mapHeight =$('#Minimap').height();

            $('#ColorMapMinimap').css('border','2px solid green').width( mapWidth ).height(mapHeight).css('position','absolute').css('z-index','15');
            sizeHeight = xeno['region']['y'];
            sizeWidth = xeno['region']['x'];

            var paper = Raphael("ColorMapMinimap", mapWidth, mapHeight);
            var setMinimap  = paper.set();
            var map = _x_ai_map['movement']['map'];

            var tileSize = Math.floor(150/sizeWidth);

            for(var x=0;x<sizeWidth;x++){
                 for(var y=0;y<sizeHeight;y++){
                    r = paper.rect(x*tileSize,y*tileSize,tileSize,tileSize);

                    var value= map[(y*sizeWidth)+x];
                    var inverse = Math.abs(100 - map[(y*sizeWidth)+x]);

                    r.attr({fill: "rgba("+value+"%,0%,"+inverse+"%,1)"});
                    setMinimap.push(r);
                 }
            }
        }
    });

}

function fillAIPanel(){

    var DOM = '<div><h1>Panel de IA</h1>';
    DOM = DOM + '<fieldset><button class="right box" id="ObtenerAIAction">Ver</button><button class="right box" id="RemoverAIAction">X</button><div><label>Accion</label><br/><select id="AIAction"><option>Nada</option></select></div><div class="clear"></div>';
    DOM = DOM + '<input type="radio" id="FlagAction" name="flagAction" value="flagAction" checked = "checked" /> Calculo Accion<br />';
    DOM = DOM + '<input type="radio" id="FlagDefense" name="flagAction" value="flagDefense" /> Calculo Obstaculo<br />';
    DOM = DOM + '<input type="radio" id="FlagDefenseAction" name="flagAction" value="flagDefenseAction" /> Calculo Obstaculo y Accion<br />';
    DOM = DOM + '<input type="radio" id="FlagTravel" name="flagAction" value="flagTravel" /> Calculo Viaje<br />';
    DOM = DOM + '<input type="radio" id="FlagTravel" name="flagAction" value="flagFull" /> Calculo Full<br />';
    DOM = DOM + '<div id="slider"></div>';

    DOM = DOM + '</fieldset>';
    $(document).bind('xeno.newXenoBattle',function(event,data){
        var selected = $('#AIAction').val();
        //console.log(selected,'ai.js fillAIPAnel');
        var id =  getActualArmyId();
        var actions = data['armies'][id]['actions'];
        var dom_option = '';
        for (var action_id in actions) {
            var action = actions[action_id];
            dom_option = dom_option + '<option value='+action.id+'>'+action.name+'</option>';
        }
        $('#AIAction').html(dom_option);
        $('#AIAction').val(selected);

        $('#RemoverAIAction').click(function(){$('#ColorMap').remove();});
    });





    //DOM = DOM + '<input type="checkbox" id="iamap" name="iamap" value="Height" /> Show Travel Map<br />';
    //DOM = DOM + '<input type="checkbox" id="enemymap" name="enemymap" value="Enemy" /> Show Enemy Map<br />';
    //DOM = DOM + '<input type="checkbox" id="calc" name="calc" value="Calc" /> Show Combined Map<br />';
    DOM = DOM + '<input type="checkbox" id="test" name="test" value="calcTest" /> Test<br />';
    DOM = DOM + '</<div>';
    $('#BattleMenu').append(DOM);

    $( "#slider" ).slider({
        value:100,
        min: 0,
        max: 100,
        step: 5,
        slide: function( event, ui ) {
            if(typeof(g_demo)!="string" ){
                _x_ai_tiles.attr({"fill-opacity":(ui.value/100)});
            }
        }
    });


    $('#ObtenerAIAction').click(function(){
           call('actionMap',{'action_id':$('#AIAction').val(),'flagAction':$("#:input[name='flagAction']:checked").val()});
       }
    );

}

function check(){
    var check = this;
    if($(check).is(':checked')){
        //console.log('Calc Chekeado');
        call($(this).val());
    }
    else{
        //console.log('Calc No Chekeado');
        $('#ColorMap').remove();
    }
}
function call(action,data){
       $('#ColorMap').remove();
       var region_id = xeno['region']['id'];
       var battle_id = xeno['battle']['id'];
       var selected_army_id = getSelectedArmyId();
       //console.log(selected_army_id,'ai.js call selected_army_id');
        $.ajax({
            type:'post',
            data:$.extend(data, {'ajax_action':action,'region_id':region_id,'battle_id':battle_id,'selected_army_id':selected_army_id} ),
            url:g_ai_url,
            dataType:'json',
            error:function(XMLHttpRequest, textStatus, errorThrown){processAjaxError(XMLHttpRequest, textStatus, errorThrown);},
            success:function(data){
             _x_ai_map= json_parse(data);
             $('#ViewPort').append('<div id="ColorMap"></div>');

            tileHeight =  g_tile_size;
            tileWidth = g_tile_size;
            sizeHeight = xeno['region']['y'];
            sizeWidth = xeno['region']['x'];

            mapHeight = tileHeight*sizeHeight;
            mapWidth = tileWidth * sizeWidth;

             $('#ColorMap').css('border','2px solid green').width(mapWidth).height(mapHeight).css('position','absolute').css('z-index','20');
             var paper = Raphael("ColorMap", mapWidth, mapHeight);
             var set  = paper.set();


             var map = _x_ai_map['movement']['map'];
             for(var x=0;x<sizeWidth;x++){
                 for(var y=0;y<sizeHeight;y++){
                    r = paper.rect(x*tileWidth,y*tileHeight,tileWidth,tileHeight);
                    var num = (y*sizeWidth)+x;
                    t = paper.text(x*tileWidth+10,y*tileHeight+10, num + '['+x+']['+y+']' );
                    t.attr({font: "14px Fontin-Sans, Arial", fill: "#bbb", "text-anchor": "start"});

                    var total = map[num];
                    v = paper.text(x*tileWidth+15,y*tileHeight+30,total);
                    v.attr({font: "16px Fontin-Sans, Arial", fill: "#ddd", "text-anchor": "start"});

                    //r.attr({fill: "rgba(0%, 0%,"+map[(y*10)+x]+"%, "+map[(y*10)+x]+"%)"});
                    //r.attr({fill: "rgba(0%,0%,50%, "+map[(y*10)+x]+"%)"});
                    //r.attr({fill: "rgba("+map[(y*10)+x]+"%,"+map[(y*10)+x]+"%,"+map[(y*10)+x]+"%,75%)"});

                    var opacity = ($("#slider").slider("value"))/100;
                    var value= map[(y*sizeWidth)+x];
                    var inverse = Math.abs(100 - map[(y*sizeWidth)+x]);
                    r.attr({fill: "rgba("+value+"%,0%,"+inverse+"%,"+opacity+")"});
                    set.push(r);
                 }
             }
             //console.log(set,'ai.js call set');
             _x_ai_tiles = set;
             $('#ObtenerAIActionMinimap').click();
              }
        });
}


function checkCalc(){
    var check = this;
    if($(check).is(':checked')){
        //console.log('Calc Chekeado');
        callCalc();
    }
    else{
        //console.log('Calc No Chekeado');
        $('#ColorMap').remove();
    }
}
function checkEnemyMap(){
    var check = this;
    if($(check).is(':checked')){
        //console.log('Enemy Chekeado');
        callEnemyMap();
    }
    else{
        //console.log('Enemy No Chekeado');
        $('#ColorMap').remove();
    }
}

function checkAIMap(){
    var check = this;
    if($(check).is(':checked')){
        //console.log('Valor Chekeado');
        callAIMap();
    }
    else{
        //console.log('Valor No Chekeado');
        $('#ColorMap').remove();
    }
}
function callEnemyMap(){
   var region_id = xeno['region']['id'];
   var battle_id = xeno['battle']['id'];

    $.ajax({
        type:'post',
        data:{'ajax_action':'renderEnemyMap','region_id':region_id,'battle_id':battle_id},
        url:g_ai_url,
        dataType:'json',
        error:function(XMLHttpRequest, textStatus, errorThrown){processAjaxError(XMLHttpRequest, textStatus, errorThrown);},
        success:function(data){
         _x_ai_map= json_parse(data);
         $('#BattleMapWrapper').append('<div id="ColorMap"></div>');
         $('#ColorMap').css('border','2px solid green').width(600).height(600).css('position','relative').css('top','-600px').css('left','145px');
         var paper = Raphael("ColorMap", 600, 600);
         var map = _x_ai_map['movement']['map'];
         for(var x=0;x<10;x++){
             for(var y=0;y<10;y++){
                r = paper.rect(x*60,y*60,60,60);
                var num = (y*10)+x;
                t = paper.text(x*60+10,y*60+10, num + '['+x+']['+y+']' );
                //t.attr({fill : "white"});
                t.attr({font: "12px Fontin-Sans, Arial", fill: "#fff", "text-anchor": "start"});
                //r.attr({fill: "rgba(0%, 0%,"+map[(y*10)+x]+"%, "+map[(y*10)+x]+"%)"});
                //r.attr({fill: "rgba(0%,0%,50%, "+map[(y*10)+x]+"%)"});
                r.attr({fill: "rgba("+map[(y*10)+x]+"%,"+map[(y*10)+x]+"%,"+map[(y*10)+x]+"%,80%)"});
             }

         }

          }
    });
}


 function callAIMap(){
   var region_id = xeno['region']['id'];
   var battle_id = xeno['battle']['id'];

    $.ajax({
        type:'post',
        data:{'ajax_action':'renderHeightMap','region_id':region_id,'battle_id':battle_id},
        url:g_ai_url,
        dataType:'json',
        error:function(XMLHttpRequest, textStatus, errorThrown){processAjaxError(XMLHttpRequest, textStatus, errorThrown);},
        success:function(data){
         _x_ai_map= json_parse(data);
         $('#BattleMapWrapper').append('<div id="ColorMap"></div>');
         $('#ColorMap').css('border','2px solid green').width(600).height(600).css('position','relative').css('top','-600px').css('left','145px');
         var paper = Raphael("ColorMap", 600, 600);
         var map = _x_ai_map['movement']['map'];
         for(var x=0;x<10;x++){
             for(var y=0;y<10;y++){
                r = paper.rect(x*60,y*60,60,60);

                //r.attr({fill: "rgba(0%, 0%,"+map[(y*10)+x]+"%, "+map[(y*10)+x]+"%)"});
                //r.attr({fill: "rgba(0%,0%,50%, "+map[(y*10)+x]+"%)"});
                r.attr({fill: "rgba("+map[(y*10)+x]+1+"%,"+map[(y*10)+x]+1+"%,"+map[(y*10)+x]+1+"%,80%)"});

             }

         }

          }
    });
 }


 function callCalc(){
   var region_id = xeno['region']['id'];
   var battle_id = xeno['battle']['id'];

    $.ajax({
        type:'post',
        data:{'ajax_action':'calcRango','region_id':region_id,'battle_id':battle_id},
        url:g_ai_url,
        dataType:'json',
        error:function(XMLHttpRequest, textStatus, errorThrown){processAjaxError(XMLHttpRequest, textStatus, errorThrown);},
         success:function(data){
             _x_ai_map= json_parse(data);
             $('#BattleMapWrapper').append('<div id="ColorMap"></div>');
             $('#ColorMap').css('border','2px solid green').width(600).height(600).css('position','relative').css('top','-600px').css('left','145px');
             var paper = Raphael("ColorMap", 600, 600);
             var map = _x_ai_map['movement']['map'];
             for(var x=0;x<10;x++){
                 for(var y=0;y<10;y++){
                    r = paper.rect(x*60,y*60,60,60);
                    var num = (y*10)+x;
                   // t = paper.text(x*60+10,y*60+10, map[num] + '['+x+']['+y+']->'+num );
                     t = paper.text(x*60+10,y*60+10, map[num] + '->'+num );
                    //t.attr({fill : "white"});
                    t.attr({font: "12px Fontin-Sans, Arial", fill: "#fff", "text-anchor": "start"});
                    //r.attr({fill: "rgba(0%, 0%,"+map[(y*10)+x]+"%, "+map[(y*10)+x]+"%)"});
                    //r.attr({fill: "rgba(0%,0%,50%, "+map[(y*10)+x]+"%)"});
                    r.attr({fill: "rgba("+map[(y*10)+x]+"%,"+map[(y*10)+x]+"%,"+map[(y*10)+x]+"%,80%)"});
                 }

             }
         }
     });
}
