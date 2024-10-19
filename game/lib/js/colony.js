
var g_colony_url = g_game_url+'ajax/ajaxColony.php';//direccion de accion [TODO]Hacer un json precharge de esto
var g_image_url =  g_game_url+'img/';
var old_xeno ='';
var iGlobalTime = 0;

 //Inicializar Recurso
 gResourceManager.add('b1_tiny',g_game_url+'img/building/1_tiny.png');
 gResourceManager.add('b2_tiny',g_game_url+'img/building/2_tiny.png');
 gResourceManager.add('b3_tiny',g_game_url+'img/building/3_tiny.png');
 gResourceManager.add('b1_small',g_game_url+'img/building/1_small.png');
 gResourceManager.add('b2_small',g_game_url+'img/building/2_small.png');
 gResourceManager.add('b3_small',g_game_url+'img/building/3_small.png');
 gResourceManager.add('b4_tiny',g_game_url+'img/building/4_tiny.png');
 gResourceManager.add('b4_small',g_game_url+'img/building/4_small.png');
 gResourceManager.add('small_ajax',g_game_url+'img/web/ajax_loader_small.gif');
 gResourceManager.add('wait_ajax',g_game_url+'img/web/ajax_wait.gif');
 gResourceManager.add('icon_building_limit',g_game_url+'img/web/icon_building_limit.png');
 
 gResourceManager.start();
    
$(document).ready(function(){
    
    if(validateInitialColonyXeno()){
        fillSecondaryPanel();
        fillPossibleBuildings();
        fillConstructionsInMap();
        $( ".building" ).draggable({
                cursor: "move",
                delay: 1,
                cursorAt: {top: 12, left: 12},
                opacity: 1,
                option: "snap",
                snapMode: 'inner',
                addClasses: false,
                snapTolerance: 1,
                helper: function( event ) {
                    var builds = xeno['buildings'];
                    var id = getId($(this));
                    //console.log(builds,'colony.js cons builds');
                    //console.log(id,'colony.js cons id');
                    b = builds[id];
                    //return $( '<img alt="'+b['name']+'" class="building" id="b'+b['id']+'" src="'+g_image_url+'building/'+b['id']+'_tiny.png" />' );
                    var rb = renderBuilding(b,'tiny');
                    return rb;
                }
        });


        $( ".column" ).droppable({
            hoverClass: "active",
            over: function(event, ui) { 
                $('.near-active').removeClass('near-active');
                var img = ui.draggable[0];
                //console.log(img,'colony const img');
                var id = getId(img);
                //console.log(id,'colony const id');
                var b= xeno['buildings'][id];

                var x = parseInt($(event.target).attr("x"));
                var y = parseInt($(event.target).attr("y"));

                var form = b['form'];

                var tiles = getBuildableTiles(b, event.target);
                for(i in tiles){
                        tiles[i].addClass('near-active');
                }
            },
            drop: function( event, ui ) {
                //console.log(event,'event');
                //console.log(ui,'ui');
                //console.log(ui.draggable,'ui.draggable');
                //console.log(ui.draggable[0],'ui.draggable[0]');
                $('.near-active').removeClass('near-active');
                var img = ui.draggable[0];

                //console.log(img,'colony const img');
                var id = getId(img);
                //console.log(id,'colony const id');
                var b= xeno['buildings'][id];

                var tiles = getBuildableTiles(b, event.target);
                if( isBuildable(tiles) ){
                    var counter = 1;
                    for(i in tiles){
                        classBuilding(tiles[i],b,counter);
                        counter++;
                    }
                    callCreateConstruct(b, event.target);
                }
                else{
                    //console.log('No se pudo construir coño');
                }
            }
        });
updateColonyState();
    }
});

/***********************************************************
 * PRINCIPAL
 **********************************************************/
 function validateInitialColonyXeno(){
    if(validateInitialXeno())
    {
         return true;
    }
    else{
        return false;
    }
}
 
function fillSecondaryPanel(){
    var DOM = 
        "<div id='BuildPanel'>"+
        "<div id='BuildingsWrapper'><h1>Edificios</h1>"+
        "<div id='Buildings'></div>"+
        "</div>"+
        "<div id='AddonsWrapper'><h1>Addons</h1>"+
        "<div id='Addons'></div>"+
        "</div>"+
        "</div>";
        //console.log(DOM,'fillSecondaryPanel');
    $("#SecondaryPanel").append(DOM);
}

function fillConstructionsInMap(){
    var constrs = xeno['constructions'];
    //console.log(xeno,'colony.js fillPossibleBuildings xeno');
    //console.log(builds,'colony.js fillPossibleBuildings builds');
    
    for(i in constrs){
        var c = constrs[i];
        var id = c['internal_building_id'];
        var b= xeno['buildings'][id]; //Ojo no funcionara si la construccion_id no existe en b;
        var target = getTile(c['city_x'],c['city_y']);
        
        var counter = 1;
        var tiles = getBuildableTiles(b,target);
        for(t in tiles){
            classBuilding(tiles[t],b,counter);
            counter++;
        }
        //renderConstruction(target,c);
        
        
        
    }
}

function renderBuildingTooltip(b){
    //console.log(b['costs'],'colony.js renderBuildingTooltip');
    //console.log(b,'colony.js renderBuildingTooltip');
    //Si es building
    var level ='';
    var name ='';
    //var intLevel = b['costs']['level'];
    var intLevel = b['costs'] && isDefined(b['costs']['level']) ? b['costs']['level'] : null;

    if(isDefined(intLevel)){

        if(intLevel == 1){
            level = 'Recursos necesarios';
            name = b['name']+' nivel '+(intLevel);
        }
        else{
            level = 'Mutar al nivel '+intLevel;
            name = b['name']+' nivel '+(intLevel-1);
        }
        var toolDOM = '<h3>'+name+'</h3><span>'+b['desc']+'<h3>'+level+'</h3><div class="building_resources">';

        for (x=1;x<=_total_resources;x++){
            var resnumber = 'res'+x;
            var tot =b['costs'][resnumber];
            if( isDefined(tot) && tot!= 0 ){
                toolDOM = toolDOM + "<img src='"+g_image_url+"web/icon_res"+x+".png' /> <span>"+tot+" </span> ";
            }
        }
        toolDOM = toolDOM +'<span><img src="'+g_image_url+'web/building_time_icon.png" alt="Duracion" ><span class="time">'+b['costs']['time']+'</span></span></div>';
    }
    else{//Ya no hay mas actualizaciones
        //console.log(b,'colony.js renderBuildingTool');
        name = b['name']+' nivel '+ b['level'];
        level = 'No existen mas actualizaciones';
        var toolDOM = '<h3>'+name+'</h3><span>'+b['desc']+'<h3>'+level+'</h3>';        
        
    }
    return toolDOM;
}

function fillPossibleBuildings(){
    var builds = xeno['buildings'];
    //console.log(xeno,'colony.js fillPossibleBuildings xeno');
    //console.log(builds,'colony.js fillPossibleBuildings builds');
    
    for(buildId in builds){
        var b = builds[buildId];
        var DOM = '';
        DOM = DOM + '<div class="buildListWrapper"><img class="building" id="b'+b['id']+'" src="'+g_image_url+'/building/'+b['id']+'_small.png" />';
        //DOM = DOM + '<div class="buildListWrapper">' + renderBuilding(b,'_small');
        DOM = DOM + '<h2>'+b['name']+'</h2">';
        DOM = DOM + '</div>';
        $('#Buildings').append(DOM);
        /*var toolDOM = '<h3>'+b['name']+'</h3><span>'+b['desc']+'<h3>Costos</h3><div class="building_resources">';
        for (x=1;x<=_total_resources;x++){
            var resnumber = 'res'+x;
            var tot =b['costs'][resnumber];
            if( isDefined(tot) && tot!= 0 ){
                toolDOM = toolDOM + "<img src='"+g_image_url+"web/icon_res"+x+".png' /> <span>"+tot+" </span> ";
            }
            
            
        }
        toolDOM = toolDOM +'</div>';*/
        toolDOM = renderBuildingTooltip(b);
        tooltip($('#Buildings .building:last'),toolDOM,'left');
        //console.log($('#Buildings .building:last'),'colony.js fillPossibleBuildings');
    }
   
}

function renderBuilding(b,size){
    DOM = '<img alt="'+b['name']+'" class="building" id="b'+b['id']+'" src="'+g_image_url+'building/'+b['id']+'_'+size+'.png" />';
    var key = 'b'+b['id']+'_'+size;
    DOM = gResourceManager.get(key)
    //console.log(DOM);
    return DOM;
}

/*BUGEADO*/
function renderConstruction(target,c){
    var DOM = '<img alt="'+c['name']+'" class="construction" id="c'+c['id']+'" src="'+g_image_url+'building/'+c['internal_building_id']+'_map.png" />';
    $('#PrimaryPanel').append(DOM);
    
    var size = new Object();    
    var tCenter = new Object(); //target Center
    switch(c['form']){
        case 'B': //Nueve cuadrados

        size.width =75;
        size.height =75;
        o = target.position();
        console.log(o,'colony renderConstruction position');
        o = target.offsetParent();
        console.log(o,'colony renderConstruction offsetParent');
        o = target.offset();
        console.log(o,'colony renderConstruction offset');
        

        tCenter.left = o.left;
        tCenter.top = o.top;
        break;
        
        case 'R': // 6 cuadrados hechados
        size.width =75;
        size.height = 50;
        o = target.offset();
        tCenter.left = o.left + ( 23/2) +3; //El centro del target + la mitad de un cuadrado
        tCenter.top = o.top + 1 -9; //El centro del target + 1 del border
        tCenter.left = o.left;
        tCenter.top = o.top;
        break;
        
        case 'L': // 6 cuadrados parados
        size.width =50;
        size.height = 75;
        o = target.offset();
        tCenter.left = o.left + (23) +3; //El centro del target + la mitad de un cuadrado
        tCenter.top = o.top +3; //El centro del target + la mitad de un cuadrado par q de en la mitad
        tCenter.left = o.left;
        tCenter.top = o.top;
        break;
    }
    
    var oConstr = $('#c'+c['id']);
    //$(oConstr).css('left',tCenter.left -( size.width/2 ) );
    //$(oConstr).css('top',tCenter.top - ( size.height/2 )  );
    $(oConstr).css('left',tCenter.left);
    $(oConstr).css('top',tCenter.top);
    
    console.log(oConstr.position(),'colony renderConstruction position2');
    /*$(oConstr).position({
        of: $('body'),
        my:'center center',
        at:'left top' ,
        offset: o.left+" "+o.top
    })*/
    console.log(target,'fillConstructionsInMap target');
    console.log(oConstr,'fillConstructionsInMap oConstr');
}


function classBuilding(dom,b,section){
    id = b['id'];
    form = b['form'];
    $(dom).addClass(form+section);
    $(dom).addClass('builded');
    $(dom).removeClass('buildable');
    //$(dom).droppable( "disable" );
    $(dom).css('backgroundImage','url("'+g_image_url+'building/'+id+'_map.png")');
    $(dom).css('backgroundColor','none');
}

function getBuildableTiles(b,target){
    var x = parseInt($(target).attr("x"));
    var y = parseInt($(target).attr("y"));

    var tiles = new Array();

    var form = b['form'];
    switch(form){
        case 'B': //9 cuadrados a la redonda
            tiles.push($('#Row'+(y-1)+' #Column'+ (x-1) ));
            tiles.push($('#Row'+(y-1)+' #Column'+ (x) ));
            tiles.push($('#Row'+(y-1)+' #Column'+ (x+1) ));
            tiles.push($('#Row'+(y)+' #Column'+ (x-1) ));
            tiles.push($('#Row'+(y)+' #Column'+ (x) ));
            tiles.push($('#Row'+(y)+' #Column'+ (x+1) ));
            tiles.push($('#Row'+(y+1)+' #Column'+ (x-1) ));
            tiles.push($('#Row'+(y+1)+' #Column'+ (x) ));
            tiles.push($('#Row'+(y+1)+' #Column'+ (x+1) ));
         break;
         
         case 'R':
             tiles.push($('#Row'+(y-1)+' #Column'+ (x-1) ));
             tiles.push($('#Row'+(y-1)+' #Column'+ (x) ));
             tiles.push($('#Row'+(y-1)+' #Column'+ (x+1) ));
             tiles.push($('#Row'+(y)+' #Column'+ (x-1) ));
             tiles.push($('#Row'+(y)+' #Column'+ (x) ));
             tiles.push($('#Row'+(y)+' #Column'+ (x+1) ));
         break;
         
         case 'L':
             tiles.push($('#Row'+(y-1)+' #Column'+ (x) ));
             tiles.push($('#Row'+(y-1)+' #Column'+ (x+1) ));
             tiles.push($('#Row'+(y)+' #Column'+ (x) ));
             tiles.push($('#Row'+(y)+' #Column'+ (x+1) ));            
             tiles.push($('#Row'+(y+1)+' #Column'+ (x) ));
             tiles.push($('#Row'+(y+1)+' #Column'+ (x+1) ));
         break;
         
         default:
             console.error('colony.js getBuildableTiles La forma no esta definida aun:'+form);
             break;
            
    }
    return tiles;
}

function isBuildable(tiles){
    var buildable = true;
    for(i in tiles){
        if($(tiles[i]).is('.builded')){
            buildable = false;
            break;
        }
    }
    return buildable;
    
}
function callUpdateState(){
    waitMsg();
    var colony_id = xeno['colony']['id'];//[TODO] eliminar obtener el ID de sesion
    
    $.ajax({
        type:'post',
        data:{'colony_id':colony_id,'ajax_action':'update_state'},
        url:g_colony_url,
        dataType:'json',
        success:function(rawData){
            var data = $.parseJSON(rawData);
            processAjaxAnswer(data,null,false);
            continueMsg()
        }
    });
 }
 
function callUpgradeConstruct(b,target){
    var colony_id = xeno['colony']['id'];
    var x = parseInt($(target).attr("x"));
    var y = parseInt($(target).attr("y"));
    var icon = createAjaxLoader(target);
    $.ajax({
        type:'post',
        data:{'colony_id':colony_id,'ajax_action':'upgrade_construction',building_id:b['id'],building_x:x,building_y:y},
        url:g_colony_url,
        dataType:'json',
        success:function(rawData){
            var data = $.parseJSON(rawData);
            processAjaxAnswer(data,target);
            destroyAjaxLoader(icon);
        }
    });
 }

function callCreateConstruct(b,target){
    var colony_id = xeno['colony']['id'];
    var x = parseInt($(target).attr("x"));
    var y = parseInt($(target).attr("y"));
    
    $.ajax({
        type:'post',
        data:{'colony_id':colony_id,'ajax_action':'create_construction',building_id:b['id'],building_x:x,building_y:y},
        url:g_colony_url,
        dataType:'json',
        success:function(rawData){
            var data = $.parseJSON(rawData);
            processAjaxAnswer(data,target);}
    });
 }
 
 function updateColonyState(){
     //Actualiza el reloj de las construcciones
     //console.log(constr,'colony.js processAjaxAnswer no esta en estado B o no tiene time');
     for(i in xeno['constructions']){
         constr =xeno['constructions'][i];

         var target = getTile(constr['city_x'],constr['city_y']);
         //Crear el countdown
         if( (isDefined(constr['time'])) && constr['build_state'] == 'B' && constr['time']>0 ){
             var updateButton = $(target).find('.construction_button');
             $(updateButton).qtip("hide");
             $(updateButton).remove();

             //console.log(constr,'colony.js updateState esta buildable');
             
             var countdown_father = $(target).append('<div class="countdown_wrapper"></div>');
             var countdown = $(countdown_father).find('.countdown_wrapper');
             renderCountdown(countdown,constr['time'],function(){
                 callUpdateState();
             });
             
             //console.log(countdown,'colony.js updateState countdown');
             tooltip(countdown,'Mutando '+constr['name']+' al nivel '+( parseInt( constr['level'] )+1 ),'top');
         }
         //Crear el boton
         else{
             $(target).children('.countdown_wrapper').qtip("destroy");
             $(target).children('.countdown_wrapper').remove();
             
             var updateButton = $(target).find('.construction_button');
             if( ! ( $(updateButton).is('.construction_button') ) ){
                drawUpdateButton(target);
             }
             else{
                // console.log(target,'target tiene un boton de updateo');
             }
         }

     }
     //Actualiza los recursos
     fillResourcesData(xeno['global']['player']);
         
       updateGlobalState(); 
 }
 
 /*A diferencia del render, este no devuelve un string, pone defrente el boton con sus caracteristicas*/
 function drawUpdateButton(target){
     var construction_id = getConstructionId(target.attr('x'),target.attr('y'));
     //console.log(construction_id,'drawUpdteButton');
     c = xeno['constructions'][construction_id];
     var DOM = '';
     if(c['costs']){
         var DOM = '<span class ="construction_button update iconbutton" ><img src="'+g_image_url+'web/upgrade_building_icon.png" /></span>';
     }
     else{
         var DOM = '<span class ="construction_button" ><img src="'+g_image_url+'web/icon_building_limit.png" /></span>';
     }
     
     
     $(target).append(DOM);
     var updt = $(target).find('.construction_button')[0];
     
     //console.log(c,'colony drawUpdateButton');
     toolDOM = renderBuildingTooltip(c);
     tooltip($(updt),toolDOM,'top');
     if(c['costs']){
         $(updt).click(function(){callUpgradeConstruct(c,target)});
     }
     
     //console.log(updt,'colony.js drawUpdteButton');
     
 }
 
 function processAjaxAnswer(data,optionalData,printMessage){
     if(!isDefined(printMessage)){
         printMessage = true;
     }
     
     //console.log(data,'processAjaxAnswer');
     //Crear Coutdowns a las cosntrucciones
     if(isDefined(data)){     
         if(  isDefined(data['msg']) ){
             
             if( printMessage ){
                 msg(data['msg']);
             }
             
             //Si es SUCCESS
             if( data['msg']['type'] == 'success' ){
                 old_xeno = xeno;
                 xeno = data;   
                 //console.log(data,'processAjaxAnswer SI entra a update');
                  updateColonyState();
             }
             else{
                //console.log(data,'processAjaxAnswer NO entra a update');
             }    
         }
     }
 }
 
 function getTile(x,y){
     return $( '#Row'+(y)+' #Column'+ (x) );
 }
 
 function getConstructionId(x,y){
     var constr = xeno['constructions'];
     var id = false;
     for(i in constr){
         c = constr[i];
         if( (c['city_x'] == x) && (c['city_y']==y)){
             id = c['id'];
             break;
         }
     }
     return id;
 }
 
 