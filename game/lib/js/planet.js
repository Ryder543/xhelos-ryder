var g_planet_url = g_game_url+'ajax/ajaxPlanet.php';//direccion de accion [TODO]Hacer un json precharge de esto
var g_region_url = g_game_url+'region.php';
var g_image_url = g_game_url+'img/';

var g_region_action_class= 'actions';//Clase de boton de accion
var g_region_action_wrapper_class= 'region_action_wrapper';//Clase de boton de accion

var iGlobalTime = 0;
var old_xeno;
  
  
$(document).ready(function(){
    if(validateInitialPlanetXeno()){
        renderPlanetMap();
        addTime = function(){
            iGlobalTime++
        };
        setInterval(function() {
            addTime();
        }, 1000);
        xeno['time'] = iGlobalTime; //tiemplo de jclock se calcula
        //Inicializar jugador seleccionado
        //Inicializa Behaviors
        $('.region').live('click',function(){
            actionEngine(this)
        });

        //Acciones de la Region
        $('.planet_map .'+g_region_action_class).live('click',function(){
            attackRegionAction(this);
            return false
        });
        $('.actionNew').live('click',function(){
            createNewColonyAction(this);
            return false
        });
        $('.actionDelete').live('click',function(){
            deleteColonyAction(this);
            return false
        });    
        callStatus();
        
        
    }
});

/***********************************************************
 * PRINCIPAL
 **********************************************************/
function validateInitialPlanetXeno(){
    if(validateInitialXeno())
    {
        return true;
    }
    else{
        return false;
    }
}

function processAjaxError(XMLHttpRequest, textStatus, errorThrown){
    errorMsg('Error de Coneccion[1]. Si manda detalles de este error en el foro podra ganar algunos bonuses');
    continueMsg();
}
 
function processAjaxSuccess(data,force){

    if(data == 1){
        errorMsg('Error de Coneccion[2]. Si manda detalles de este error en el foro podra ganar algunos bonuses');
        continueMsg();
    }
    else{
        if(typeof(data) == 'string'){
            var data = $.parseJSON(data);
        }
        
        if(_.has(data, "type")){//Si no llega este mensaje, es que estamos mandando cosas que no son!
            if(data.type == "error"){
                errorMsg(data.text); 
                if(force == false){
                    errorMsg(data.type);
                }
            }else{
                successMsg(data.text);
                last_xeno = xeno;
                xeno = data.data;
                xeno['time'] = iGlobalTime;

                updateGlobalState();//general.js

                var activeRegionId = getActiveRegionId();
                deactiveRegions();
                activeRegion(activeRegionId);

                //fillMapWithBattles();
                fillMapWithResources();
                fillMapWithColonies();

                fillMapReinforcements();
                fillMapWithPortals();
                fillMapWithBuildings();
                fillMapWithBiomes();
                fillMapWithArmiesNumber();
                fillMapWithAllegiance();

            //fillTeamInfo(activeRegionId);
            }
        }
        else{
            errorMsg('Error de Coneccion[3]. Si manda detalles de este error en el foro podra ganar algunos bonuses');
        }
    
    }
}


function actionEngine(target_tile){
    var id = getRegionId(target_tile);     
    ////Llenamos el panelcito con info
    if(id){
        //fillMapWithBattles();
        fillRegionInfo(xeno['regions'][id]);
        fillInfoWithArmies(xeno['regions'][id]);
        fillResourcesInfo(xeno['regions'][id]);
        fillBuildingInfo(xeno['regions'][id]);
        fillReinforcement(xeno['regions'][id],xeno['battles']['sended']);
        activeRegion(id);
        fillTeamInfo(id);
        fillActionsInfo(id);
    }

}
function fillMapReinforcements(){
    //Imprimir los jugadores que estan llegando
    var list_player = new Array();
    var reinforcements = xeno['battles']['sended'];
    var players =  xeno['players'];
    //a la izquierda del origen siempre
    
    
    if (isDefined(reinforcements) ){
      
        var places = {};
        for(reinforcementId in reinforcements){
            var army = reinforcements[reinforcementId];
            var from = army['from_region'];
            var to = army['to_region'];
            var allegiance = army['allegiance'];          
            if(!exist(places[from])){//Llenamos army_from_region con objetos
                places[from] = {};
            }
            if(!exist(places[from][to])){//Llenamos army_to_region con objetos
                places[from][to] = {};
            }
            if(!exist(places[from][to][allegiance])){//Llenamos army_allegiance con objetos
                places[from][to][allegiance] = parseInt(0);
            }
            //console.log(places[from][to][allegiance],'antes');
            places[from][to][allegiance] = places[from][to][allegiance]+1;//Sumamos cuantos bichos hay aqui   
        //console.log(places[from][to][allegiance],'despues');
        }
        $(".planet_map .arriving").remove();
        $.each(places,function(fromKey,placeFrom){
          
            $.each(placeFrom,function(toKey,placeTo){
              
                $.each(placeTo,function(allegianceId,allegianceCount){
                    var position = getPositionRelativeToRegion(fromKey,toKey);
                    var reg = getRegion(fromKey);
                    $(reg).append('<div class="player arriving '+allegianceId+' '+position+'"><span class="count">'+allegianceCount+'</span></div> ');
                });
              
              
            });
        });
    } 
}
 

/***********************************************************
 * ARMIES
 **********************************************************/ 
function renderArmies(player_id,region_id){
   
    var armies = xeno['armies'][region_id];
    var DOM = '<ul class="inner-accord inner ui-helper-clearfix">';
    if(armies!=null){
        $.each(armies,function(key,army){
            if((army['player_id']==player_id)&&(army['region_id']==region_id)){
                var player = xeno['players'][army['player_id']];
                var myself = xeno['player'];

                DOM = DOM + '<li class="armyWrapper '+army['allegiance']+'">'+renderCreature(army,player,myself,false,'','',true)+'<br><span>'+army['size']+'</span></li>';
            }
       
        });
    }
    DOM = DOM + '</ul>';
    
    return DOM;
}
/***********************************************************
 * FUSIONAR ESTO ALGUNA VES
 **********************************************************/
/*renderBattle: Imprimir html de batalla*/
function renderBattleTag(region){
    ////console.log(battle_id,'Id de la batalla');
    var DOM = '';
    var battle = getBattleByRegion(region['id']);
    var name ='<a href="region.php?region_id='+region['id']+'"> '+ region['name']+'</a>';
    if(battle){
        var phase = battle['phase'];
        DOM = DOM + '<div class="battle_tag phase'+phase+'" id="BattleTag'+region['id']+'_'+battle['id']+'">'+name+'</div>';
    }
    else{
        DOM = DOM + '<div class="region_tag ui-state-transparent" id="RegionTag'+region['id']+'">'+name+'</div>';
    }
    
    return DOM;
} 
function getBattleByRegion(region_id){
    battles = xeno['battles']['active'];
    ////console.log(battles,'planet.js->getBattleByRegion');
    if(isDefined(battles)){
        ////console.log('battles esta definido','planet.js->getBattleByRegion ');
        var battle_return = false;
        $.each(battles,function(key,battle){
            ////console.log(battle['region_id']+'==?'+region_id,'planet.js->getBattleByRegion ->each battle[region_id]');
            if( parseInt(battle['region_id']) == parseInt(region_id ) ){
                // console.info('Esto esta funcionando');
                battle_return = battle;
                return false;
            }
        })
        ////console.log('No hay ninguna bata単a en la region['+region_id+']','planet.js->getBattleByRegion ');
        return battle_return;
    }
    else{
        ////console.log('battles no esta definido','planet.js->getBattleByRegion ');
        return false;
    }
}
  
/*renderArmyPosition: html de la posicion de la criatura*/
function renderArmyPosition(army_position){
    return position[army_position]
}
 
/***********************************************************
 * PLANETA
 **********************************************************/







function fillMapWithPortals(){  
    var regionWithPortals = xeno['portals'];
    if(regionWithPortals != null){
        $.each(regionWithPortals,function(regionId,portalsInRegion){
            var reg = getRegion(regionId);
            ////console.log(coloniesInRegion,'fillMapWithcolonies');
            $(reg).children('.resources').html("");
            $.each(portalsInRegion,function(portalsId,portals){
                var DOM = renderPortal(portals);
                var val= $(reg).children('.resources').append(DOM);
            });

        });  
    }

}
 
function fillMapWithBiomes(){
    $('.planet_map .biome').remove();
    var regionWithBiomes = xeno['biomes'];
    if(regionWithBiomes != null){
        $.each(regionWithBiomes,function(regionId,biomesInRegion){
            var reg = getRegion(regionId);
            //console.log(reg);
            ////console.log(coloniesInRegion,'fillMapWithcolonies');
            
            $.each(biomesInRegion,function(biomesId,biomes){
         
                var DOM = renderBiomes(biomes);     ////console.log(DOM);
                var val= $(reg).append(DOM);
            });

        });  
    }

}
 
function fillMapWithAllegiance(){
    $('.region').removeClass("myself");
    $('.region').removeClass("noallegiance");    
    $('.region').removeClass("enemy");    
    $('.region').removeClass("AI"); 
    
    var regions = xeno.regions;
    
    for(regionId in regions){
        var allegiance = regions[regionId]['allegiance'];
        $('#Region'+regionId).addClass(allegiance);
    }
}

function fillMapWithArmiesNumber(){ 
    $(".planet_map .armies").html("");
    var regionWithArmiesNumber = xeno['armies'];

    if(regionWithArmiesNumber != null){
        $('.armyInMap').remove();
        $.each(regionWithArmiesNumber,function(regionId,armiesNumberInRegion){
            var reg = getRegion(regionId);
            var enemyCont = parseInt(0);
            var aiCont = parseInt(0);
            var myselfCont = parseInt(0);
            $.each(armiesNumberInRegion,function(){

                if(this['allegiance'] == "enemy"){
                    enemyCont++ ;
                }else{
                    if(this['allegiance']== 'AI'){
                        aiCont++ ;
                    }else{
                        if(this['allegiance']== 'myself'){
                            myselfCont++ ;
                        }
                    }
                }
            });
            var DOM = renderArmiesNumber(enemyCont,aiCont,myselfCont);
            var val= $(reg).append(DOM);
            
            
            
            if(myselfCont>0){
                var DOM2 = '<img class="myself armyInMap" src="img/web/menu_army.png"></div>';
                $(reg).append(DOM2);
            }
                
                
        });  
    }

}


function fillMapWithBuildings(){  
    var regionWithBuildings = xeno['buildings'];
    $('.planet_map .building').remove();
    if(regionWithBuildings != null){
        for(var regionId in regionWithBuildings){
            var reg = getRegion(regionId);
            var buildingsInRegion = regionWithBuildings[regionId];
            var DOM = '';
            for(var buildingId in buildingsInRegion){
                DOM = DOM+renderBuildings(buildingsInRegion[buildingId]);
            }
            //console.log(DOM);
            $(reg).append(DOM);
        }
    }

}


function fillMapWithResources(){  
    var regionWithResources = xeno['resources'];
    $('.planet_map .resources').html("");
    //console.log($('.planet_map .resources'));
    if(regionWithResources != null){
        for(var regionId in regionWithResources){
            var reg = getRegion(regionId);
            var resourcesInRegion = regionWithResources[regionId];
            var DOM = '';
            for(var resourceId in resourcesInRegion){
                DOM = DOM+renderResource(resourcesInRegion[resourceId]);
            }
            $(reg).children('.resources').append(DOM);
        }
    }
}
 
function fillMapWithColonies(){
    $('.planet_map .colonies').html("");
    var regionWithColonies = xeno['colonies'];
    if(regionWithColonies != null){
        for(var regionId in regionWithColonies ){
            var coloniesInRegion = regionWithColonies[regionId];
            var reg = getRegion(regionId);
            ////console.log(coloniesInRegion,'fillMapWithcolonies');
            
            var DOM = '';
            for(var colonyId in coloniesInRegion){
                var colony = coloniesInRegion[colonyId];
                DOM = DOM + renderColony(colony);
            }
            $(reg).children('.colonies').append(DOM);
        } 
    }
}
/*
function fillMapWithBattles(){
    $('.battle_tag').remove();
    $('.region_tag').remove();
    var regions = xeno['regions'];
    if(regions != null){
    }
}*/
 
/***********************************************************
 * PANEL DE BATALLA
 **********************************************************/ 
function fillInfoWithArmies(regionInfo){
    //Actualizar la lista de Jugadores
    ////console.log(regionInfo,'fillBattleInfo');
    var region_id = regionInfo['id'];
    //var myself = xeno['player'];
    //var players = xeno['players'];
    var DOM = '';
    var count=0;
    var hasI = false; //variable que indica si tu army tiene unidades por ingresa
    var IDOM ='';
    var myselfPlayer = xeno['player'];
    //var armiesInRegion =xeno['armies'][];
    if(exist(xeno['armies'][region_id])){
        var armiesInRegion =xeno['armies'][region_id]
        var players = {};// VIRI IMPURTAN osea muy importante, que sea un objeto en lugar de un array y se arregla todo, fuck arrays
        for(var armyId in armiesInRegion){
            var army = armiesInRegion[armyId];
            var playerId = army['player_id'];
            var myselfPlayerId = myselfPlayer['id'];               
            if(playerId != myselfPlayerId){//No nos metemos a nosotros mismos
                //console.log(playerId);
                if(!exist(players[playerId])){
                    players[playerId] = new Array();
                }
                
                players[playerId].push(army); 
            }

        }
      
        DOM = '';
        for(var playerIdInPlayers in players){
            count++;
            var player = xeno.players[playerIdInPlayers];
            DOM = DOM + '<h3 class="'+player['allegiance']+'">'+player['username']+'</h3><div>'+renderArmies(playerIdInPlayers,regionInfo['id']);
          
        }
    }
    
  
    if(count==0){
        //DOM ='<img src="img/web/title-block2.jpg" class="img-block">';
        DOM ='<p class="inner ">La region no tiene ninguna unidad</p>';
        $('#ArmyInfoInner').html(DOM);
    }
    else{
        $('#ArmyInfoInner').html(DOM);
    }
     
}

/***********************************************************
 * REGIONES
 **********************************************************/
 
function renderActionPictureEntrar(regionId){
    return '<div class="actionEnter actions"><a href="region.php?region_id='+regionId+'"><img   src="img/action/entrar.png "><span>Entrar</span></a></div>';
}
 
function renderActionPictureEliminarColonia(){
    return '<div class="actionDelete actions"><img   src="img/action/delete_colony.png "><span>Eliminar Colonia</span></div>';
}
 
function renderActionPictureNuevaColonia(){
    return '<div class="actionNew actions"><img src="img/action/new_colony.png "><span>Crear Colonia</span></div>';
}
 
 
function fillActionsInfo(regionId){
    var renderActionPictureEntrar = '<div class="actionEnter actions"><a href="region.php?region_id='+regionId+'"><img   src="img/action/entrar.png "><span>Entrar</span></a></div>';
    var renderActionPictureEliminarColonia = '<div class="actionDelete actions"><img   src="img/action/delete_colony.png "><span>Eliminar Colonia</span></div>';
    var renderActionPictureNuevaColonia = '<div class="actionNew actions"><img src="img/action/new_colony.png "><span>Crear Colonia</span></div>';
    if(region_id == getActiveRegionId()){
        var DOM = renderActionPictureEntrar + renderActionPictureEliminarColonia + renderActionPictureNuevaColonia;
        $('#ActionsMenu').html(DOM);
    }
    else{
    //errorMsg('No se puede');
    }
}
 
function fillBuildingInfo(regionInfo){
   
    var resource3 = xeno.buildings[regionInfo["id"]];
    var resource4 = xeno.colonies[regionInfo["id"]];
    var resource5 = xeno.portals[regionInfo["id"]];
    
    //var id = getColonyId(resource4);
   
    if(typeof(BuildingsInfo) == 'undefined'){
        $('#BuildingsInfo').html('La Region no tiene construcciones');
    } 
  
    else{
        var printed = false;
        var iDOM2 ='';
        if(resource3 != null){
            $.each(resource3,function(key, resources){
                iDOM2 = iDOM2 +'<div id=" BuildingInfo'+resources['id']+'"  class="buildingInfo">'+renderBuildings(resources)+'<h4>'+resources['name']+'</h4></div>';        
            });
            var printed = true;
        }
        if(resource4 != null){
            $.each(resource4,function(key, resources){
                iDOM2 = iDOM2 +'<div id="ColonyInfo'+resources['id']+'"  class="buildingInfo">'+renderColony(resources)+'<h4>'+resources['name']+'</h4></div>';                            
            //iDOM2 = iDOM2+renderColony(resources);        
            });
            var printed = true;
        }
        if(resource5 != null){
            $.each(resource5,function(key, resources){
                iDOM2 = iDOM2 +'<div id="PortalInfo'+resources['id']+'"  class="buildingInfo">'+renderPortal(resources)+'<h4>'+resources['name']+'</h4></div>';                            
            //iDOM2 = iDOM2+renderPortal(resources);        
            });
            var printed = true;
        }
      
        if(printed){
            $('#BuildingsInfo').html('<div id = "RegionBuildings" >'+iDOM2+'</div>');
        }
        else{
            $('#BuildingsInfo').html('<div id = "RegionBuildings" >La Region no tiene construcciones</div>');
        }
        

    }//else inicial si tiene datos
}

 

function fillResourcesInfo(regionInfo){

    //Crear los recursos por region
    var iDOM ='';
    var resource = xeno.resources[regionInfo["id"]];
    var resource2 = xeno.biomes[regionInfo["id"]];   
    
    var printed = false;
    if(resource != null){
        $.each(resource,function(key, resources){
            iDOM = iDOM +renderResource(resources);        
        });
        printed = true;
    }
    if(resource2 != null){
        $.each(resource2,function(key, resources){
            iDOM = iDOM+renderBiomes(resources);        
        });
        printed = true;   
    }
     

    if(!printed ){
        iDOM = iDOM + '<p>Esta Region no Tiene recursos</p>';
    }
    else{
        $('#ResourcesInfo').html('<div class="resourceInfoWrapper"> '+iDOM+'</div>');
        
    }
}


function fillRegionInfo(regionInfo){
    //console.log(regionInfo,"planet.js fillRegionInfo");
    if(typeof(regionInfo) == 'undefined'){
        $('#RegionInfo').html('Elija una region');
    }
    else{
        //Llenar la informacion de la region
        var DOM = '<h4>'+regionInfo['name']+'</h4>';
        $('#RegionInfo').html(DOM);
  
        //Crear los recursos por region
        var regions = xeno.regions[regionInfo["id"]];
        //console.log(regions,"planet.js fillRegionInfo2");
        var type = regions["type"];     
        var iDOM ='<div class="basicResourceWrapper">';
        iDOM = iDOM + renderBasicResource(type,1);
        iDOM = iDOM + renderBasicResource(type,2);
        iDOM = iDOM + renderBasicResource(type,3);
        iDOM = iDOM + '</div>';
        $('#RegionInfo').append(iDOM);
        var regionType = "type"+regionInfo["type"];
        $('#RegionInfo').removeClass("type0");        
        $('#RegionInfo').removeClass("type1");
        $('#RegionInfo').removeClass("type2");
        $('#RegionInfo').removeClass("type3");
        $('#RegionInfo').removeClass("type4");        
        $('#RegionInfo').removeClass("type5");                
        $('#RegionInfo').removeClass("type6");                
        $('#RegionInfo').removeClass("type7");                        
        $('#RegionInfo').removeClass("type8");        
        $('#RegionInfo').addClass(regionType);
        
    }//else inicial si tiene datos
}

function getPlayerColonyByRegion(regionId){
    var regionColonies = xeno['colonies'][regionId];
    var isMyColony = false;
    var colonyToReturn = false;
    if(exist(regionColonies)){
        var colony = false;
        for (colonyId in regionColonies){
            colony = regionColonies[colonyId];
            if( colony['allegiance'] == 'myself' ){
                isMyColony = true;
            }
            if(isMyColony){
                colonyToReturn = colony;
            }   
        }
    }
    return colonyToReturn;
}

function fillTeamInfo(region_id){

    var teams = xeno['teams'][region_id];
    var armies  = xeno['armies'][region_id];
    var region = xeno['regions'][region_id];
    
    var colony = getPlayerColonyByRegion(region_id);
    var unitRegionCounter=0;
    ////////////////////////////////////Rendering de las unidades regionales //////////////////////////////
    if(colony!= false){
        var regionColonies = xeno['colonies'][region_id];  

       
        var regionTeamInfo = $('#RegionTeamInfo'); 
        $(regionTeamInfo).html('');    
        var teamWrapper = $('<div class="ui-state-transparent"></div>').appendTo(regionTeamInfo);
        $(teamWrapper).data('team_id',null);
        $(teamWrapper).append('<h4 class="ui-helper-clearfix ">Colonia '+colony['name']+'</h4>');
        var regionUl = $('<ul id="t0" class="team ui-helper-clearfix"></ul>').appendTo(teamWrapper);

        for(aKey in armies){
            var army = armies[aKey];
            if(army['team_id'] == null){
                if(army['allegiance'] =='myself'){
                    var dom = '<li id="a'+army['id']+'"  class="armyWrapper '+army['allegiance']+'"><div class="army"><img src="img/unit/'+army['creature_id']+'_small.png" id="a384" class="army_turn myself small"><p>'+army['size']+'</p></div></li>';
                    $(regionUl).append(dom);
                    unitRegionCounter++;
                }
            }
        }    

    }
    else{
        var regionTeamInfoDom = $('#RegionTeamInfo'); 
        $(regionTeamInfoDom).html('');
    }

    //////////////////////////Rendering de los equipos ///////////////////////////////
    $('#TeamInfo').html('');
    //[WHY] poruqe no un $.each, porque no se puede detener
    var unitTeamCounter=0;
    for (key in teams){
        team = teams[key];
        var teamDOM = renderTeam(team,true,region_id);
        $(teamDOM).appendTo('#TeamInfo');
        unitTeamCounter++;
    }
   
    //////////////////////////Rendering de boton de nuevos equipos ///////////////////////////////
    if(colony != false){
        var buttonDom = '<span class="button newteam">Crear equipo</span>';
        $('#TeamInfo').append(buttonDom);
    }

    if( (unitTeamCounter==0) && (unitRegionCounter == 0) && (colony==false) ){
        $('#TeamInfo').append('<p>La region no tiene tropas amigas</p>');

    }
    ///////BEHAVIOUR : Nuevo Equipo //////////////////   
    $( ".newteam" ).click(function(){
        callNewTeam();
    });
   
    ///////BEHAVIOUR : Sortable //////////////////
    $( ".team" ).sortable({
        connectWith: ".team,.teamWrapper",
        placeholder: "ui-state-highlight armyPlaceholder",
        forcePlaceholderSize:true,
        receive: function(event, ui) {
            var dom =ui.item[0];
            var army_id = getId(dom);
            var parent = $(dom).parent('ul');
            var team_id = getId(parent) ;
            ////console.log(ui,'ui');
            ////console.log(team_id,'team_id');
            callTransferUnit(army_id,team_id);

        }
    }).disableSelection();
    ///////BEHAVIOUR : Close Button //////////////////
    $('.deleteTeam').click(function(){
        var id = $(this).parent('h4').data('team_id');
        callDeleteTeam(id);
    });
   
    ///////BEHAVIOUR : Active //////////////////
    $('.teamWrapper').click(function(){
        var team_id = $(this).data('team_id');
        activeTeam(team_id,this);
    })
 
    //BEHAVIOUR : Click the first Team
    if(exist( $('.teamWrapper')[0] )){
        $('.teamWrapper')[0].click();
    }
 
   
}


function renderTeam(team,deleteButton,regionId){
    ////console.log(deleteButton,'render deleteButton');
    if(!exist(deleteButton)){
        deleteButton = true;
    }
 
    var deleteButtonDom = '';
    if(deleteButton== true){
        deleteButtonDom = '<span class="deleteTeam button">x</span>';
    }
    ////console.log(deleteButton,'render deleteButton');
    ////console.log(deleteButtonDom,'render deleteButton');
 
    var teamWrapper = $('<div class="teamWrapper ui-state-transparent"></div>');
    $(teamWrapper).data('team_id',team['id']);
    var h4 = $('<h4 class="ui-helper-clearfix ui-state-default">'+team['name']+deleteButtonDom+'</h4>').appendTo(teamWrapper);
    $(h4).data('team_id',team['id']);
    var ul = $('<ul id="t'+team['id']+'" class="team ui-helper-clearfix"></ul>').appendTo(teamWrapper);

    var armies = xeno['armies'][regionId];
    for(aKey in armies){
        var army = armies[aKey];
        if(army['team_id'] == team['id']){  
            var dom = '<li id="a'+army['id']+'" class="armyWrapper  '+army['allegiance']+'"><div class="army" ><img src="img/unit/'+army['creature_id']+'_small.png" class="army_turn myself small"><p>'+army['size']+'</p></div></li>';
            $(ul).append(dom);
        }
    }
    return teamWrapper;
}

function activeTeam(team_id,dom){
    ////console.log('planet.js activeTeam team_id:'+team_id);
    $('.teamWrapper.ui-state-highlight').removeClass('ui-state-highlight').addClass('ui-state-transparent').find('h4').removeClass('ui-state-active').addClass('ui-state-default');
    $(dom).removeClass('ui-state-transparent').addClass('ui-state-highlight').find('h4').addClass('ui-state-active');
}

function getActiveTeamId(){
    var activeWrapper = $('#TeamInfoWrapper').find('.teamWrapper.ui-state-highlight');
    return $(activeWrapper).data('team_id');
}

function callDeleteTeam(team_id){
    waitMsg();
    $.ajax({
        type:'post',
        data:{
            'ajax_action':'delete_team',
            'team_id':team_id,
            'planet_id':xeno['planet']['id']
        },
        url:g_planet_url,
        dataType:'json',
        error:function(XMLHttpRequest, textStatus, errorThrown){
            processAjaxError(XMLHttpRequest, textStatus, errorThrown);
        },
        success:function(data){
            processAjaxSuccess(data);
            fillTeamInfo(getActiveRegionId());
        }
    });
    return false;//Evitar la propagacion
}

function callNewTeam(){
    waitMsg();
    $.ajax({
        type:'post',
        data:{
            'ajax_action':'new_team',
            'planet_id':xeno['planet']['id'],
            'region_id':getActiveRegionId()
        },
        url:g_planet_url,
        dataType:'json',
        error:function(XMLHttpRequest, textStatus, errorThrown){
            processAjaxError(XMLHttpRequest, textStatus, errorThrown);
        },
        success:function(data){
            processAjaxSuccess(data);
            fillTeamInfo(getActiveRegionId());
        }
    });
    return false;//Evitar la propagacion
}

function callTransferUnit(army_id,team_id){
    waitMsg();
    $.ajax({
        type:'post',
        data:{
            'ajax_action':'army_transfer_team',
            'army_id':army_id,
            'team_id':team_id,
            'planet_id':xeno['planet']['id']
        },
        url:g_planet_url,
        dataType:'json',
        error:function(XMLHttpRequest, textStatus, errorThrown){
            processAjaxError(XMLHttpRequest, textStatus, errorThrown);
        },
        success:function(data){
            processAjaxSuccess(data);
            fillTeamInfo(getActiveRegionId());
        }
    });
    return false;//Evitar la propagacion
}
//Devuelve dodne esta la region de acuerdo a la region de origen, a la izquierda del origen
function getPositionRelativeToRegion(originRegionId,targetRegionId){
    //console.log(originRegionDOM,'getPositionRelativeToRegion');
    var originX = getRegionX(originRegionId);
    var originY = getRegionY(originRegionId);
    var targetX = getRegionX(targetRegionId);
    var targetY = getRegionY(targetRegionId);
    
    if(targetY==originY+1){
        return "bottom";
    }
    else if(targetY==originY-1){
        return "top";
    }
    else if(targetX == originX+1){
        return "right";
    }
    else if(targetX == originX-1){
        return "left";
    }
    else{
        console.warn("No existe un resultado","planet.js->getPositionRelativeToRegion");
        return false;
    }
}

function renderRegionActions(regionDOM){
    ////console.log(regionDOM,'renderRegionsActions regionDOM');
    region_id = getRegionId(regionDOM);
    ////console.log(region_id,'renderRegionsActions region_id');
    player_id = xeno['player']['id'];
  
    //console.log(player_id);
  
    if(regionHasPlayer(region_id,player_id)){
        ////console.log('renderREgionActions la region['+region_id+'] tiene jugaores');
        var x = parseInt(getRegionX(getRegionId(regionDOM)));
        var y = parseInt(getRegionY(getRegionId(regionDOM)));
        var max_x = parseInt(xeno['planet']['size']['x']);
        var max_y = parseInt(xeno['planet']['size']['y']);
        var list = Array();
        var position = Array();
        var c=0;
        
        
        //Obtenemos todas los tiles X adyacentes [maxima forma como cruz]
    
        //console.log('renderRegionAction x['+x+'] max_x['+max_x+']');
        //console.log('renderRegionAction y['+y+'] max_y['+max_y+']');        
        if(x==0){
            list[c] = getRegionInXY(x+parseInt(1),y);
            position[c] = 'right';
            c++; 
        }
        else if(x==(max_x-1)){
            list[c] = getRegionInXY(x-1,y);
            position[c] = 'left';
            c++;
        }
        else{
            list[c] = getRegionInXY(x+parseInt(1),y);
            position[c] = 'right';
            c++;
            list[c] = getRegionInXY(x-1,y);
            position[c] = 'left';
            c++;
        }
    
        //console.log(list,'renderRegionAtions list');
        //console.log(position,'renderRegionAtions position');
    
        //Obtenemos todas los tiles y adyacentes [maxima forma como cruz]
        if(y==0){
            list[c]= getRegionInXY(x,y+parseInt(1));
            position[c] = 'bottom';
            c++;
        }
        else if(y==(max_y-1)){
            list[c]= getRegionInXY(x,y-1);
            position[c] = 'top';
            c++;
        }
        else{
            list[c]= getRegionInXY(x,y+parseInt(1));
            position[c] = 'bottom';
            c++;
            list[c]= getRegionInXY(x,y-1);
            position[c] = 'top';
            c++;
        }
    
        ////console.log(list,'renderRegionAtions list');
        ////console.log(position,'renderRegionAtions position');
    
        $.each(list,function(este){
            var DOM = "<div class='region_action_wrapper'>";
            DOM = DOM + "<div class='"+g_region_action_class+" "+position[este]+"'></div>";
            DOM = DOM +"</div>";
            $(list[este]).prepend(DOM);
        });
    }
    else{

    }
}
function unrenderRegionActions(){
    $('.'+g_region_action_wrapper_class).remove();
}

function deleteColonyAction(dom_action){
    waitMsg();
    
    //console.log(dom_action);
   
    var target_region_id = getActiveRegionId();
    //console.log(target_region_id);
    for(var i in xeno['colonies'][target_region_id]){
        var colonyId = xeno['colonies'][target_region_id][i]['id'];
    }
    //console.log(colonyId);
    //console.log(dom_action);
    var regionEmphy = false;
    if(regionEmphy == true){
        errorMsg('No puede eliminar Colonias en esta region');
    }

    
    else{
        callDeleteColony(colonyId,target_region_id);
    }
    
    
}



function createNewColonyAction(dom_action){
    waitMsg();
    var target_region_id = getActiveRegionId();
    //console.log(dom_action);
    var regionEmphy = true;
    if(regionEmphy == false){
        warningMsg('No puede contruir Colonias en esta region');
    }

    
    else{
         
        callCreateColony(target_region_id);
    }
    
    
}


function attackRegionAction(dom_action){
    waitMsg();
    var target_region_id = getRegionId($(dom_action).parents('.region'));
    var origin_region_id = getActiveRegionId();
    var teams = xeno['teams'][origin_region_id];
    var players =  xeno['players'];
    var myself = xeno['player'];
    var region = xeno['regions'][target_region_id];
    
    var activeTeamId = getActiveTeamId();
    if(!exist(activeTeamId)){
        warningMsg('Para enviar a atacar necesita enviar a las unidades dentro de un equipo');
    }
    else{
        $("#AttackRegionModal").dialog(
        {
            width: 240,
            modal: true,
            buttons: {
                "Atacar": function() {
                    //var team = teams[activeTeamId];
                    callAttackRegionAction(target_region_id,origin_region_id,activeTeamId);
                    $( this ).dialog( "close" );
                },
                Cancel: function() {
                    $( this ).dialog( "close" );
                }
            },
            title:'Atacar Region '+region['name'],
            open: function(event, ui) {

                //renderizar el equipo atacante

                var team = teams[activeTeamId];
                var teamDOM = renderTeam(team,false,origin_region_id);
                $('#AttackActiveTeam').html('');
                $(teamDOM).appendTo('#AttackActiveTeam');
                continueMsg();
                //renderizar unidades enemigas
                var armies = xeno['armies'][target_region_id];
                $('#AttackRegionEnemies').html('');
                if(exist(armies)){
                    //Obtener todos los players que no sean enemigos;
                    var enemyPlayersInRegion = new Array;
                    for(key in armies){
                        if(armies[key]["allegiance"]!='myself'){
                            enemyPlayersInRegion[armies[key]['player_id']] = armies[key]['player_id'];
                        }
                    }
                    if(enemyPlayersInRegion.length>0){
                        for( enemyKey in enemyPlayersInRegion){
                            var player = players[enemyKey];
                            var enemiesDom = renderArmies(enemyKey,target_region_id);
                            var title = '<h3 class="'+playerType(player,myself)+'">'+player['username']+'</h3>';
                            $('#AttackRegionEnemies').append(title+enemiesDom);
                        }
                        
                    }
                    else{
                        $('#AttackRegionEnemies').html('<p>No hay unidades enemigas</p>');
                    }
                    
                }
                else{
                    $('#AttackRegionEnemies').html('<p>No hay unidades enemigas</p>');
                }
                
                


            }
        });
    }
}
function callStatus(){
    //deactiveRegions();
    //console.log("planet.js->callStatus");
    waitMsg();
    $.ajax({
        type:'post',
        data:{
            'ajax_action':'status',
            'planet_id':xeno['planet']['id']
        },
        url:g_planet_url,
        dataType:'json',
        error:function(XMLHttpRequest, textStatus, errorThrown){
            processAjaxError(XMLHttpRequest, textStatus, errorThrown);
        },
        success:function(data){
            processAjaxSuccess(data);  
        //activeRegion(origin_region_id);
        }
        
    });
}



function callDeleteColony(colony_id,region_id){
    //console.log(colony_id,'callDeleteColony target_region_id');
    //console.log(region_id,'callDeleteColony region_id');    
    if(colony_id == false){
        errorMsg('no existe colonia');
        return false;
    }
    else{
        waitMsg();
        
        $.ajax({
            type:'post',
            data:{
                'ajax_action':'delete_colony',
                'colony_id':colony_id,
                'planet_id':xeno['planet']['id']
            },
            url:g_planet_url,
            dataType:'json',
            error:function(XMLHttpRequest, textStatus, errorThrown){
                processAjaxError(XMLHttpRequest, textStatus, errorThrown);
            },
            success:function(data){
                processAjaxSuccess(data);
            }
        
        });
    }
}




function callCreateColony(target_region_id){
    //deactiveRegions();
    
    if(target_region_id == false){
        errorMsg('no puede construir en esta region');
        return false;
    }
    else{
    
        waitMsg();

        $.ajax({
            type:'post',
            data:{
                'ajax_action':'create_colony',
                'region_id':target_region_id,
                'planet_id':xeno['planet']['id']
            },
            url:g_planet_url,
            dataType:'json',
            error:function(XMLHttpRequest, textStatus, errorThrown){
                processAjaxError(XMLHttpRequest, textStatus, errorThrown);
            },
            success:function(data){
                processAjaxSuccess(data);
                    
       
            }
        
        });
    }
}


function callAttackRegionAction(target_region_id,origin_region_id,team_id){
    //deactiveRegions();
    waitMsg();
   
    $.ajax({
        type:'post',
        data:{
            'ajax_action':'attack_region',
            'planet_id':xeno['planet']['id'],
            'target_region_id':target_region_id,
            'origin_region_id':origin_region_id,
            'team_id':team_id
        },
        url:g_planet_url,
        dataType:'json',
        error:function(XMLHttpRequest, textStatus, errorThrown){
            processAjaxError(XMLHttpRequest, textStatus, errorThrown);
        },
        success:function(data){
            processAjaxSuccess(data);  
            activeRegion(origin_region_id);
          
        }
        
    });
}


function renderColony(colony){
    var alle = colony["allegiance"];
    //var DOM = '<div id=" ColonyInfo'+colony['id']+'"  class="colonyInfo"><img class="colony'+alle+'" src="'+g_image_url+'building/colony'+colony['type']+'_small.png "><h4>'+colony['name']+'</h4>'; 
    var DOM = '<img class="colony '+alle+'" src="'+g_image_url+'building/colony'+colony['type']+'_small.png ">';     
    

    return DOM;
}

function renderBasicResource(type,number){
    //console.log(resource_rate,"resourcerate");
   // console.log(resource_rate['res'+number],"resourcerate["+ 'res'+number +"]");
    var DOM = '<div class="basicResource"><span>'+resource_rate[type]['res'+number]+'</span><img src="img/web/icon_res'+number+'.png "></div>';  
    //var DOM = '<div class="basicResource"><span>'+resource_rate['res'+number]+'</span><img src="img/web/icon_res'+number+'.png "></div>';  
    return DOM;
}

function renderResource(resource){
    //var regRes = resource["region_id"];
    //var DOM = '<img class="resource" src="'+g_image_url+'resource/'+resource['resource_id']+'_'+resource['quality']+'.png ">';  
    var DOM = '<img class="resource" src="img/resource/'+resource['resource_id']+'_'+resource['quality']+'.png ">';  
    return DOM;
}

function renderPortal(portal){
    //var DOM = '<div id=" PortalInfo'+portal['id']+'"  class="portalInfo"><img class="portal" src="img/portal/portal.png "><h4>'+portal['name']+'</h4>'; 
    var DOM = '<img class="portal" src="img/portal/portal.png ">'; 
    return DOM;
}

function renderBuildings(building){
    //var regRes = resource["region_id"];
    //'+buildings['name']+'
    //var DOM = '<div id=" BuildingInfo'+building['id']+'"  class="buildingInfo"><img class="building" src="img/building/'+building['region_construction_id']+'.png "><h4>'+building['name']+'</h4>'; 
    var DOM = '<img class="building" src="img/building/'+building['region_construction_id']+'.png ">'; 
    return DOM;
}

function renderBiomes(biome){
    //var regRes = resource["region_id"];
    //'+buildings['name']+'
    //OTRA SOLUCION, PONER LA IMAGEN COMO BACKGROUND DEL DIV
    var DOM ='<div class="biome"><span>'+biome['tier']+'</span><img src="img/biome/'+biome['type']+'.png "></div>';
    //$(".region").prepend(DOM);
    return DOM;
}

function renderArmiesNumber(enemy,AI,myself){
    var enemyString = '';
    var AIString = '';
    var myselfString = '';    
    enemyString = '<div class="army AI" ><h3>'+AI+'</h3></div>';
    myselfString = '<div class="armies"><div class="army myself"><h3>'+myself+'</h3></div>';
    AIString = '<div class="army enemy"><h3>'+enemy+'</h3></div>';
    var dom = myselfString+enemyString+AIString;  
    return dom;
}




function renderResourceProportion(resource){
    prop = 0;
    switch(resource['quality']){
        case 'P':
            prop =1;
            break;
        case 'R':
            prop =2;
            break;
        case 'H':
            prop =3;
            break;
        case 'S':
            prop =5;
            break; 
    }
    DOM ='';
    for(var x=0;x<prop;x++){
        DOM = DOM + '<img class="resource" src="'+g_image_url+'resource/'+resource['resource_id']+'_1.png ">'; 
    }
    
    return DOM;
  
}


/***********************************************************
 * ACTIVADORES
 **********************************************************/
function activeRegion(id){
    dom = getRegion(id);
    if($(dom).hasClass('active')){
        deactiveRegions();  
    }
    else{
        deactiveRegions();
        $(dom).toggleClass("active");
        renderRegionActions(dom);
    }
}
  
function deactiveRegions(){
    unrenderRegionActions();
    $('.region.active').removeClass('active');//Quita lo seleccionado de una accion
}
  
/***********************************************************
 * OBTENEDORES DE ID
 **********************************************************/
function getAbilityId(dom){
    var id_raw = $(dom).attr('id');
    if (typeof(id_raw)!='undefined')
    {
        return parseInt(id_raw.substring(7));
    }
    else{
        return false;
    }
}
function getActiveAbilityId(){
    var ability_id = getAbilityId($('ul#Abilities li.ability.active').eq(0));
    return ability_id;
}

function getRegionId(dom){
    var id_raw = $(dom).attr('id');
    if (typeof(id_raw)!='undefined')
    {
        return parseInt(id_raw.substring(6));
    }
    else{
        return false;
    }
}
 
function getColonyId(dom){
    var id_raw = $(dom).attr('id');
    if (typeof(id_raw)!='undefined')
    {
        return parseInt(id_raw.substring(6));
    }
    else{
        return false;
    }
}
/*function isPathable(target_tile){
   return false;
 }*/
/***********************************************************
 * OBTENEDORES DE DOM
 **********************************************************/
function getRegionInXY(x,y){
    // //console.log('x['+x+']  y['+y+']');
    total_y = xeno['planet']['size']['y'];  
    x= parseInt(x);
    y= parseInt(y);
    total_y= parseInt(total_y);
    index=(y*total_y)+x;
    //console.log(' (y* total_Y) + x + 1');
    //console.log('x['+x+'] total_y['+total_y+'] y['+y+'] index['+index+']');
    tile = $('.index'+index); 
    return tile;
}
function getActiveRegionId(){
    var active_region = $('.region.active')[0];
    var id = getRegionId(active_region);
    return id;
}

 
function getRegion(id){
    var reg = $('#Region'+id);
    return reg;
}
function getRegionX(id){
    var reg = getRegion(id);
    return parseInt($(reg).attr('x'));
}
function getRegionY(id){
    var reg = getRegion(id);
    return parseInt($(reg).attr('y'));
}
function getRegionIndex(id){
    var reg = getRegion(id);
    return parseInt($(reg).attr('index'));
}
 
function getPlayersByRegionId(region_id){
    var armies = xeno['armies'][region_id];
    var players = new Object;
    /*//console.log(players,'planet.js getPlayersByRegionId players Empty?');
    //console.log(armies,'planet.js getPlayersByRegionId armies');
    //console.log(typeof(armies),'planet.js getPlayersByRegionId typeofarmies');
    //console.log(armies.length,'planet.js getPlayersByRegionId armies.lenght');*/
    
    for(key in armies){
        ////console.log(players,'getPLayersByRegionId player iteration pre');
        var army = armies[key];
        var player_id = army['player_id'];
        players[player_id] = xeno['players'][player_id];
    ////console.log(players,'getPLayersByRegionId player iteration post');
    }
    return players;
}

 
function getPlayerByIdAndRegionId(player_id,region_id){
    var regionvalue = 'to_region';//Id para buscar la region
    ////console.log(player_id,'planet.js->getPlayerByIdAndRegionId->player_id');
    ////console.log(region_id,'planet.js->getPlayerByIdAndRegionId->region_id');
    players = xeno['players'];
    //[WHY] poruqe no un $.each, porque no se puede detener
    for (key in players){
        var player = players[key];
        // //console.log(player,'planet.js->getPlayerByIdAndRegionId->player tiene id?');
        if(player['id']==player_id){
            if(player[regionvalue]==region_id){
                // console.info('Player['+player['id']+' ]reg['+player['region_id']+'] encontrado','getPlayerByIdAndRegionId');
                return player;
            }
            else{
            //console.info('Player['+player['id']+' ]reg['+player[regionvalue]+'] != region['+region_id+']','getPlayerByIdAndRegionId');
            }
        }
    //console.info('Player['+player['id']+' ] != player['+player_id+'] buscado','getPlayerByIdAndRegionId');
    }
    return false;
}
 
 
 
/***********************************************************
 * BOLEANOS
 **********************************************************/ 
function armiesHasPosition(position,player_id,region_id){
    var myArmies = xeno['armies'][region_id];
    var hasPosition = false;
    $.each(myArmies,function(key,army){
        if((army['player_id']==player_id)&&(army['region_id']==region_id)){
            if(army['position']==position){
                hasPosition = true;           
            }
        }
        
    })
    return hasPosition;
   
}
function regionHasPlayer(region_id,player_id){
    players = getPlayersByRegionId(region_id);
    ////console.log(players,'region['+region_id+']HasPlayer['+player_id+'] players');
    hasPlayer = false;
   
    for(key in players){       
        var player = players[key];
        if(player['id'] == player_id){
            //console.info(players[player_id]['region_id']+ '==' +region_id,'regionHasPlayer planet.js');
            hasPlayer = true;
            return hasPlayer;  
        }
        else{
        ////console.log('regionHasPLayer2 player['+player['id']+']!= player_id['+player_id+']');
        }
    }
    return hasPlayer;
}


function renderPlanetMap(){
    var regions = xeno['regions'];
    var planet = xeno['planet'];
    if(isDefined(regions))
    {
        
            
        var sides = planet['size'];
        //debug::log($sides,'planetstate->render1');
        //Calcular la anchura
        var containerWidth = $("#Content").width();
            
        var calc = (containerWidth - (sides['x']+1 )) /sides['y'];// container menos los border-right y el unico border-left 
        var width = Math.floor(calc);//_X_ITF_PLANET_WIDTH
        //width = width - 4 //Calculando el borde;
        var widthClass='side'+width;
        var dom='<div class="planet_map" id="PlanetMap'+planet['id']+'" style="background-image:url('+g_image_url+'region/region'+planet['color']+'.jpg)">';
        for(var y=0;y<sides['y'];y++)
        {
            //dom=dom+"<tr id='y"+y+"' class='"+widthClass+"'>";
            for(var x=0;x<sides['x'];x++)
            {
                //debug::log('planetstate->render3');
                var data='';
                var col = '<div class="colonies" ></div>';
                var res = '<div class="resources"></div>';
                var index=(y*sides['y'])+(x);
                var regionMAU = findRegionByPosition(regions,index);
                if(isDefined(regionMAU) ){
                    var region_id = regionMAU['id']; 
                    var allegiance = xeno.regions[region_id]['allegiance'];
                    dom=dom+"<div style='width:"+width+"px;height:"+width+"px' x="+x+" y="+y+" index="+index+" id='Region"+region_id+"' class='x"+x+" y"+y+" region index"+index+" "+widthClass+' '+allegiance+"' >"+data+res+col+'</div>';
                //dom=dom+"<div x="+x+" y="+y+" index="+index+" id='Region"+region_id+"' class='x"+x+" y"+y+" region index"+index+" "+widthClass+"' >"+data+col+res+'</div>';
                }
                else{
                    dom=dom+"<div class='noregion "+widthClass+"' >"+data+col+res+'</div>';
                }
            }
        //dom=dom+"</tr>";
        }
        dom=dom+'</div>';
    }
    else{
        dom='<div class="battle_map">Planeta No Accesible</div>';
    }
    $('#Content').html(dom);
}

function findRegionByPosition(regions,position){
    var region = _.find(regions, function(region){ 
        if(region['planet_position']== position){
            return true
        }else{
            return false
        }
    });
    return region;
}