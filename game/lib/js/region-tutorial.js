/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


var g_region_tutorial_url = g_game_url+'ajax/ajaxRegionTutorial.php';
var g_region_tutorial_MovementLock = false;
var g_region_tutorial_waitIcon = null;

function xRegionTutoriallockOn(){
    g_region_tutorial_waitIcon = renderSmallWaitIcon($('#TutorialOn'),false);
    g_region_tutorial_MovementLock = true;
    //console.log('El seguro esta en ON');
}

function xRegionTutoriallockOff(){
    destroyWaitIcon(g_region_tutorial_waitIcon);
    g_region_tutorial_MovementLock = false;
    //console.log('El seguro esta en OFF');
}

$(document).ready(function()
{
    tutorialPhaseAction(obtenerPasoActual());
    
    //$(document).bind('phase.battle.forced',function(){
        //console.log('region-tutorial emepzo la fase de batalla');
        //tutorialPhaseAction(7);
        //xRegionTutoriallockOff();
        //$(document).unbind('phase.battle.forced');
    //});
    
    
    
    $('.continuar').live('click',function(){
        if(g_region_tutorial_MovementLock==false){
            continuar();
        }
        
    })
    
    $('.retroceder').live('click',function(){
        //console.log(g_region_tutorial_MovementLock,'region-tutorial->boton retroceder');
        if(g_region_tutorial_MovementLock==false){
            retroceder();
        }
        
    })
    
    $('.finalizar').live('click',function(){
        if($('#Tutorial'+12).hasClass("ui-dialog-content")){
            $('#Tutorial'+  12  ).dialog( "close" );
        }
        callTutorialOff();
    })
    
    $('#FinalizarClockOut').click(function(){
        if($('#Tutorial'+6).hasClass("ui-dialog-content")){
            $('#Tutorial'+ 6  ).dialog( "close" );
        }
        if($('#Tutorial'+8).hasClass("ui-dialog-content")){
            $('#Tutorial'+ 8  ).dialog( "close" );
        }        
       //tutorialPhaseAction(obtenerPasoActual());
       
       //[WHY] Paso 7 no se ejecutara porque si haces click
       //en el botonde finalizar turno, todavia no existe
       //el elemento de turnos, entonces hay que esperar que la batalla
       //sea pasada a forced
       
       $(document).bind('phase.battle.forced',function(){
            //console.log('region-tutorial emepzo la fase de batalla');
            tutorialPhaseAction(7);
            xRegionTutoriallockOff();
            $(document).unbind('phase.battle.forced');
        });
       
       
       
    });
    
    
    $('.path').live('click',function(){
        //continuar();
        if($('#Tutorial'+10).hasClass("ui-dialog-content")){
            $('#Tutorial'+ 10  ).dialog( "close" );
        }
        if($('#Tutorial'+12).hasClass("ui-dialog-content")){
            $('#Tutorial'+ 12  ).dialog( "close" );
        }
        //Debemos actualizar en el servidor la variable del paso del tutorial de la region,
        //por eso no podemos usar iniciarTutorial defrente. Otra cosa, solo aplicar esto
        //cuando estemos en el paso 10
        
            
        if(obtenerPasoActual() == 10  ){
            continuar();
        }
        else{
            //console.log('No estamos en el paso 10 bitch. region-tutorial.js');
        }
        
    });
})


function retroceder(){
   xRegionTutoriallockOn();
   callRetroceder(function(){
       var paso = obtenerPasoActual();
       //console.log(paso,'region-tutorial continuar');
       $('#Tutorial'+ (paso+1) ).dialog( "close" );
       tutorialPhaseAction(paso);
   });
   
}

function continuar(){
   xRegionTutoriallockOn();
   callContinuar(function(){
       
       var paso = obtenerPasoActual();
       $('#Tutorial'+ (paso-1) ).dialog( "close" );
       tutorialPhaseAction(paso);
   });
   
}


function callContinuar(callback){
    var region_id = xeno['region']['id'];
    $.ajax({
        type:'post',
        data:{'ajax_action':'next_step','region_id':region_id},
        url:g_region_tutorial_url,
        dataType:'json',
        cache:false,
        error:function(XMLHttpRequest, textStatus, errorThrown){msg('Error en Comunicacion')},
        success:function(data){
            setPasoActual(data.data);
            callback();
          }
    });
 }




function callRetroceder(callback){
    var region_id = xeno['region']['id'];
    $.ajax({
        type:'post',
        data:{'ajax_action':'prev_step','region_id':region_id},
        url:g_region_tutorial_url,
        dataType:'json',
        cache:false,
        error:function(XMLHttpRequest, textStatus, errorThrown){msg('Error en Comunicacion')},
        success:function(data){
                setPasoActual(data.data);
                callback();
          }
    })
 }


function tutorialPhaseAction(phase){
    //console.trace('tutorialPhaseAction:'+phase);
    
    switch(phase){
        case 1:
            //$('.army.AI').qtip("destroy");
            $('#Tutorial'+phase ).dialog({modal:true});
            $('.tutorial-switch-button').qtip("destroy");
            xRegionTutoriallockOff();
            break;
            
        case 2:
            //Eliminar Paso 3
            $('.army.AI').qtip("destroy");
            bubbletip(
            $('.tutorial-switch-button'),
            'Boton de Activacion de Unidades',
            'left','ui-tooltip-green ui-tooltip-rounded',
            function(){
                xRegionTutoriallockOff();
                $('#Tutorial'+phase ).dialog({modal:false});}
            );
            //Abrir la ventana de tutorial
            
            break;
        case 3://Seleccionar tropas enemigas
            //Eliminar Paso 2
            $('.tutorial-switch-button').qtip("destroy");
            //Eliminar Paso 4
            $('.army.myself').qtip("destroy");
            
           // console.log('tutorialPahse 2');
            var armyId =getId($('.army.AI:first'));
            selectArmy(armyId,function(){
                //console.log('region-tutorial tutorialPhaseAction');
                bubbletip($('.army.AI:first'),
                    'Enemigos', 
                    'left',
                    'ui-tooltip-AI  ui-tooltip-rounded',
                    function(){xRegionTutoriallockOff();
                    $('#Tutorial'+phase ).dialog({modal:true});}
                );
            });
            //$('.army.AI:first').click();
            //$('.army.AI').qtip('show');
            //
            
            //Abrir la ventana de tutorial
            
            break;
         case 4://Mostrar el boton de continuar batalla
             //Eliminar Paso 2
             $('.army.AI').qtip("destroy");
             //Eliminar Pas0 5
             $('#MinimapViewPort').qtip("destroy");
             
             var armyId =getId($('.army.myself:first'));
             selectArmy(armyId,function(){
                //console.log('region-tutorial tutorialPhaseAction');
                bubbletip(
                    $('.army.myself:first'),
                    'Unidades Propias',
                    'right',
                    'ui-tooltip-myself  ui-tooltip-rounded',
                    function(){xRegionTutoriallockOff();
                    $('#Tutorial'+phase ).dialog({modal:true});}
                );
            });
             //console.log('tutorialPhase 3');
             //
             //Abrir la ventana de tutorial
            
             break;
        
        case 5: 
             //Eliminar Pas0 5
             $('.army.myself').qtip("destroy");
             //Eliminar Pas0 7
             $('#FinalizarClockOut').qtip("destroy");
             $('#Clockout').qtip("destroy");
         
            //Abrir la ventana de tutorial
            bubbletip(
                $('#MinimapViewPort'),
                'Arrastra este cuadro para moverte a traves del mapa',
                'left',
                'ui-tooltip-myself  ui-tooltip-rounded',
                function(){xRegionTutoriallockOff();
                $('#Tutorial'+phase ).dialog({modal:false});}
            );
            
            
            
            break;
             
         case 6: 
             //Eliminar Pas0 5
             $('#MinimapViewPort').qtip("destroy");
             //Eliminar Paso 07
             $('.turn.myself.actual').qtip("destroy");
             $('#Clockout').qtip("destroy");
         
            
            
            if($('#FinalizarClockOut').length>0){
                
                
                bubbletip(
                    $('#FinalizarClockOut'),
                    'Haz click aqui para finalizar la fase tactica manualmente',
                    'top',
                    'ui-tooltip-green  ui-tooltip-rounded',
                    function(){
                        $('#Tutorial'+phase ).dialog({modal:false, 
                        open:function(){
                            //Abrir la ventana de tutorial
                            //console.log('El dialog esta abierto number 6');
                            if( (exist(xeno['battle']['phase']) ) &&  (xeno['battle']['phase']== 'B') ){
                                $('.showOnTactic6').hide();
                                $('.showOnBattle6').show();
                            }
                            else if( (exist(xeno['battle']['phase']) ) &&  (xeno['battle']['phase']== 'T') ){
                                $('.showOnTactic6').show();
                                $('.showOnBattle6').hide();
                            }
                        }});
                        
                        bubbletip( $('#Clockout'),
                        'Al finalizar la fase tactica pasaras a la siguiente fase',
                        'top',
                        'ui-tooltip-cluetip  ui-tooltip-rounded',
                        function(){xRegionTutoriallockOff();}
                        );
                        
                        
                    }
                );
            }
            else{
                $('#Tutorial'+phase ).dialog({modal:false, 
                        open:function(){
                            //Abrir la ventana de tutorial
                            //console.log('El dialog esta abierto number 6');
                            if( (exist(xeno['battle']['phase']) ) &&  (xeno['battle']['phase']== 'B') ){
                                $('.showOnTactic6').hide();
                                $('.showOnBattle6').show();
                            }
                            else if( (exist(xeno['battle']['phase']) ) &&  (xeno['battle']['phase']== 'T') ){
                                $('.showOnTactic6').show();
                                $('.showOnBattle6').hide();
                             }
                            }});
                xRegionTutoriallockOff();
            }
            
            
            
            
            
            break;
         
         case 7:
             //console.log('region-tutorial paso 7');
            //Eliminar Pas0 6
            $('#Clockout').qtip("destroy");
             $('#FinalizarClockOut').qtip("destroy");
             $('.actionWrapper:last').qtip("destroy");


             //Abrir la ventana de tutorial
             bubbletip(
                $('.turn.myself.actual'),
                'La primera unidad es la unidad actual',
                'top',
                'ui-tooltip-green  ui-tooltip-rounded',
                function(){
                    $('#Tutorial'+phase ).dialog({modal:false});
                    
                    
                    bubbletip($('#Clockout'),
                    'Al finalizar el contador el turno pasara a la siguiente unidad',
                    'top',
                    'ui-tooltip-cluetip  ui-tooltip-rounded',
                    function(){
                        xRegionTutoriallockOff();
                    }
                );
                    
                    
                }
             );
            
            
            break;
            
        case 8:
            $('.turn.myself.actual').qtip("destroy");
            $('.terrain').qtip("destroy");
            $('#Clockout').qtip("destroy");
            
            bubbletip(
                $('.actionWrapper:last'),
                'Ejemplo de Accion',
                'right',
                'ui-tooltip-green  ui-tooltip-rounded',
                function(){xRegionTutoriallockOff();
                $('#Tutorial'+phase ).dialog({modal:false});}
            );
            
            break;
                
        case 9:
            //Eliminar Paso 8
            $('.actionWrapper:last').qtip("destroy");
            $('.terrain').qtip("destroy");
            $('#g1').qtip("destroy");
            
            
            bubbletip(
                $('.path:first'),
                'En estos cuadrados verdes puedes aplicar la accion seleccionada',
                'left',
                'ui-tooltip-green  ui-tooltip-rounded',
                function(){xRegionTutoriallockOff();
                $('#Tutorial'+phase ).dialog({modal:true});}
            );
            
            break;

        case 10:
            //Eliminar Paso 9
            $('.terrain').qtip("destroy");
            
            
            //Anidamos un bubbltip dentro de otro bubbletip
            bubbletip(
                $('#g1'),
                '(1) Selecciona esta accion',
                'right',
                'ui-tooltip-green  ui-tooltip-rounded',
                function(){
                    bubbletip(
                    $('.path:first'),
                    '(2) Despues haz click en este cuadrado o cualquier cuadrado verde que desees',
                    'right','ui-tooltip-green  ui-tooltip-rounded',
                    function(){xRegionTutoriallockOff();
                    $('#Tutorial'+phase ).dialog({modal:false});}
                    );
                }            
                    
            );
            
            break;

        case 11:
            //console.log('No se quiere destruir el puto tooltip');
            //$('.path').qtip("destroy");
            $('#g1').qtip("destroy");
            
            //POrque no path? porque la clase path se quita antes de ejecutar esto.
            $('.terrain').qtip("destroy");
            
            $('#Tutorial'+phase ).dialog({modal:false});
            //$('.path:first').qtip("destroy");
            xRegionTutoriallockOff();
            break;
        
        case 12:
            $('#Tutorial'+phase ).dialog({modal:false});
            xRegionTutoriallockOff();
        break;
        
        case 13:
            $('#Tutorial'+phase ).dialog({modal:false});
            xRegionTutoriallockOff();
            
         default:
             //Abrir la ventana de tutorial
            $('#Tutorial'+phase ).dialog({modal:true});
            // console.log('Phase['+phase+'] Default');
    }
}

function setPasoActual(data){
    xeno['global']['tutorial']['step'] = data;
}

function obtenerPasoActual(){
    //console.log(xeno['global'],'region-tutorial obtenerPasoActual');
    return xeno['global']['tutorial']['step'];
}