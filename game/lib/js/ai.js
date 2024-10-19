/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
var g_ai_url = 'ajax/ajaxAI.php';
var _x_ai_map; //variable que contiene toda la infoormacion sobre la IA

$(document).ready(function(){
    fillAIPanel();
    fillAIMinimap();  
    
    //Bindings
    $(document).bind('xeno.armySelected',function(event,data){
        
        fillSelectActions();
    });
    
   // $('#AIPanelContainer').hide();
   // $('#BattleMenu .ui-state-transparent.block').hide();
    
})

function fillAIMinimap(){
    $('#MinimapActions').html('<button class="right box" id="ObtenerAIActionMinimap">Ver</button><button class="right box" id="RemoverAIActionMinimap">X</button>');
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
            var colorMinimap = $('#ColorMapMinimap');
            $('#ColorMapMinimap').css('border','2px solid green').width( mapWidth ).height(mapHeight).css('position','absolute').css('z-index','15');
            sizeHeight = xeno['region']['y'];
            sizeWidth = xeno['region']['x'];

            //var paper = Raphael("ColorMapMinimap", mapWidth, mapHeight);
            //var setMinimap  = paper.set();
            var map = _x_ai_map['movement']['map'];
            var opacity = getAIOpacity();
            var tileSize = Math.floor(150/sizeWidth);
            for(var y=0;y<sizeHeight;y++){
                var ysizeWidth = y*sizeWidth;
                 for(var x=0;x<sizeWidth;x++){
                    var num = ysizeWidth+x;
                    var total = map[num];
                    var value= map[num];
                    var inverse = Math.abs(100 - map[num]);
                    var tile = $('<div class="x'+x+' y'+y+' tile" style="height: 5px; width: 5px;background-color:rgb('+value+'%,0%,'+inverse+'%);opacity:'+opacity+';"></div>').appendTo(colorMinimap); 
                 }
            }
        }
    });

}

function fillSelectActions(){
    data = xeno;
    //console.log(data,'ai->fillSelectActions data');
    var id =  getSelectedArmyId();
    //console.log(id,'ai->fillSelectActions id');
    
    if( (isDefined(id)) && id ){
    //console.log(isDefined(id),'ai->fillSelectActions isDefined(id) entro');
    var actions = data['armies'][id]['actions'];
    var dom_option = '';
    
    for (var action_id in actions) {
            var action = actions[action_id];
            dom_option = dom_option + '<option value='+action.id+'>'+action.name+'</option>';
     }
     $('#AIAction').html(dom_option);
     }
} 

function fillAIPanel(){

    var DOM = '<div class="ui-state-transparent"> <div clas="inner"><h1>Panel de IA</h1>';
    DOM = DOM + '<fieldset><button class="right box" id="ObtenerAIAction">Ver</button><button class="right box" id="RemoverAIAction">X</button><div><label>Accion</label><br/><select id="AIAction"><option>Nada</option></select></div><div class="clear"></div>';
    DOM = DOM + '<input type="radio" id="FlagAction" name="flagAction" value="flagAction" checked = "checked" /> Calculo Accion<br />';
    DOM = DOM + '<input type="radio" id="FlagDefense" name="flagAction" value="flagDefense" /> Calculo Obstaculo<br />';
    DOM = DOM + '<input type="radio" id="FlagDefenseAction" name="flagAction" value="flagDefenseAction" /> Calculo Obstaculo y Accion<br />';
    DOM = DOM + '<input type="radio" id="FlagTravel" name="flagAction" value="flagTravel" /> Calculo Viaje<br />';
    DOM = DOM + '<input type="radio" id="FlagTravel" name="flagAction" value="flagFull" /> Calculo Full<br />';
    DOM = DOM + '<div id="slider" class="ui-state-active"></div>';

    DOM = DOM + '</fieldset>';
    $(document).bind('xeno.newXenoBattle',function(event,data){
        //var selected = $('#AIAction').val();
        //console.log(selected,'ai.js fillAIPAnel');
        /*var id =  getActualArmyId();
        var actions = data['armies'][id]['actions'];
        var dom_option = '';*/
        //var id =  getActualArmyId();
        //var actions = data['armies'][id]['actions'];
        fillSelectActions();
        /*
        for (var action_id in actions) {
            var action = actions[action_id];
            dom_option = dom_option + '<option value='+action.id+'>'+action.name+'</option>';
        }*/
        //$('#AIAction').html(dom_option);
        //$('#AIAction').val(selected);

        $('#RemoverAIAction').click(function(){
            $('#ColorMap').remove();
        });
    });
    DOM = DOM + '</div></div>';
    $('#AIPanelContainer').append(DOM);

    $( "#slider" ).slider({
        value:100,
        min: 0,
        max: 100,
        step: 10,
        slide: function( event, ui ) {
            if(typeof(g_demo)!="string" ){
                var opacity = ui.value/100;
                    //console.log($(this).slider("value"),'getAIOpacity INNER');
                
                
                //$('#ColorMap td').each(function(){
                //var value = $(this).data('value');    
                //var inverse = $(this).data('inverse'); 
                
                //$(this).css("backgroundColor",'rgba('+value+'%,0%,'+inverse+'%,'+opacity+')');
                //$(this).css("opacity",opacity);
                $('#ColorMap td').css("opacity",opacity);
                //});
            }
        }
    });


    $('#ObtenerAIAction').click(function(){
        call('actionMap',{
            'action_id':$('#AIAction').val(),
            //'flagAction':$("#:input[name='flagAction']:checked").val()
            'flagAction':$("input[name='flagAction']:checked").val()
            });
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
    waitMsg();
    $.ajax({
        type:'post',
        data:$.extend(data, {
            'ajax_action':action,
            'region_id':region_id,
            'battle_id':battle_id,
            'selected_army_id':selected_army_id
        } ),
        url:g_ai_url,
        dataType:'json',
        error:function(XMLHttpRequest, textStatus, errorThrown){
            processAjaxError(XMLHttpRequest, textStatus, errorThrown);
        },
        success:function(data){
            _x_ai_map= json_parse(data);
            $('#ViewPort').append('<table id="ColorMap" class="battle_map"></table>');
             
            tileHeight =  g_tile_size;
            tileWidth = g_tile_size;
            sizeHeight = xeno['region']['y'];
            sizeWidth = xeno['region']['x'];

            mapHeight = tileHeight*sizeHeight;
            mapWidth = tileWidth * sizeWidth;
            
            $('#ColorMap').css('border','2px solid green').width(mapWidth).height(mapHeight).css('position','absolute').css('z-index','20');
            var sizeHeight = xeno['region']['y'];
            var sizeWidth = xeno['region']['x'];
            var map = _x_ai_map['movement']['map'];
            var opacity = getAIOpacity();
            for(var y=0;y<sizeHeight;y++){
                var tr = $('<tr id="y'+y+'" class="terrain colormap"></tr>').appendTo('#ColorMap'); 
                var ysizeWidth = y*sizeWidth;
                for(var x=0;x<sizeWidth;x++){    
                    var num = ysizeWidth+x;
                    var total = map[num];
                    var value= map[num];
                    var inverse = Math.abs(100 - map[num]);
                    //var  tile = $('#y'+y+' #x'+x);    
                    var  tile =  $('<td class="terrain" id="x'+x+'" style="background-color:rgb('+value+'%,0%,'+inverse+'%);opacity:'+opacity+';" ></td>').appendTo(tr); 
                     $(tile).text(map[num] + ' x['+x+']y['+y+'] i['+num+']' );
                    //var  tile =  $('<td class="terrain" id="x'+x+'" style="background-color:rgba('+value+'%,0%,'+inverse+'%,'+opacity+');" ></td>').appendTo(tr); 
                    //tile.data('value',value);
                    //tile.data('inverse',inverse);
                    //$(tile).css('backgroundImage','none');
                    //$(tile).css('backgroundColor','rgba('+value+'%,0%,'+inverse+'%,'+opacity+')');
                }
            }
            
            $('#ObtenerAIActionMinimap').click();
        }
    });
}

function  getAIOpacity(value){
    return ($("#slider").slider("value"))/100;

    
}
