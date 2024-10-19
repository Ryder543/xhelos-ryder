<?php

  if(!defined('_X_SECURE')){define("_X_SECURE",true);}

  $game_ajaxplanet_directory = getcwd();
  chdir(dirname ( __FILE__ ));
  require_once("../lib/include.php");
  chdir($game_ajaxplanet_directory);
  //Inicializacion de Datos
  
  $db = db::singleton();
  $json = json::singleton();
  $msg = new datatransfer();
  $battleutil = new battleutil();
  
  
  //Sanitizacion de Variables Externas
  $ajaxAction = $_POST['ajax_action'];
  $region_id = validator::filter_integer($_POST['region_id']);
if(isset($_POST['battle_id'])){
  $battle_id = validator::filter_integer_and_null($_POST['battle_id']); 
}
 
  if(isset($_POST['action_id'])&&(!empty($_POST['action_id']))){
    $action_id = validator::filter_integer($_POST['action_id']);

  }
  
  if( isset($_POST['ty']) ){
    $ty = validator::filter_integer($_POST['ty']);
    $tx = validator::filter_integer($_POST['tx']);
    $ox = validator::filter_integer($_POST['ox']);
    $oy = validator::filter_integer($_POST['oy']);     
  }
  //debug::log('maputil->construct');
  //0) Obtencion de Armyman
  $armyman = armyman::singleton();
  $actionman = actionman::singleton();
  //$movementman = movementman::singleton();
  $battleman = battleman::singleton();
  // 1) Obtencion de la vista del Player
  $playerman = playerman::singleton();
  $player = $playerman->findById($_SESSION['xid']);
  $player_id = $player->getId();
  
  $menustate = new menuview();
  $menustate->initByRegionId($region_id); 
  $menustate->updateState();
    
  // 2) Obtencion de la vista del Planeta y Actualizacion de info
  $regionState = new regionview($region_id);
  $dt = $regionState->updateState();
  //2)Seguridad en la transaccion, temrina en la funcion de mas abajo
  if( $dt->ok() ){
      //debug::log('ajaxRegion Envio de datos');
    $db->begin();
    switch($ajaxAction)
    {
         /* *****************************************************
         * Fase Accion - Accion del turno de una unidad
         *******************************************************/
        case 'action':
          ///////////////////////////////////////////////////////////////////////
          //Validar que los datos de ingreso esten bien
          ///////////////////////////////////////////////////////////////////////
          $ok = true;
          //debug::warning($_POST,'ajaxRegion action _POST');
          if((!isset($action_id))||(empty($action_id))){
            debug::error('Se mando una accion pero no llego su id','ajaxRegion action');
            $msg->error('No hay ninguna accion seleccionada');
            $ok = false;
          }
          else{
            //debug::warning('action_id['.$action_id.'] esta seteado','ajaxRegion action');
          }
          if(($ok)&&(!isset($ox))){//Si el primero ta mal tonces dejar
            debug::error('ox no fue enviado','ajaxRegion action');
            $msg->error('No se envio el dato [origen_x]');
            $ok = false;
          }
          if(($ok)&&(!isset($oy))){//Si el primero ta mal tonces dejar
            debug::error('oy no fue enviado','ajaxRegion action');
            $msg->error('No se envio el dato [origen_y]');
            $ok = false;
          }
          if(($ok)&&(!isset($ty))){//Si el primero ta mal tonces dejar
            debug::error('ty no fue enviado','ajaxRegion action');
            $msg->error('No se envio el dato [target_x]');
            $ok = false;
          }
          if(($ok)&&(!isset($tx))){//Si el primero ta mal tonces dejar
            debug::error('tx no fue enviado','ajaxRegion action');
            $msg->error('No se envio el dato [target_x]');
            $ok = false;
          }
          //Validar si se esta en modo batalla
          $phase= $regionState->getBattle()->getPhase();
          if(($ok) AND !($phase==_X_BATTLE_PHASE_BATTLE)){
            debug::error('No se encuentra en fase BATTLE, estas en ['.$phase.']');
            $msg->error('Solo se pueden usar acciones en la fase de batalla');
            $ok = false;
          }
          //Validar que los datos de la unidad de origen existan
          $army_origin = $armyman->findByCoord($region_id,$ox,$oy);
          //$army_origin->initByCoord($region_id,$ox,$oy);
          if(($ok) AND !($army_origin->exist())){
            debug::error('1 No hay ningun army en la posicion indicada');
            $msg->error('1 No hay ningun army en la posicion indicada');
            $ok = false;
          }
          //Validar que el nextArmie en cola sea igual a los datos de la unidad de origen
          $army_actual = $regionState->getNextArmyInBattle();
          if(($ok) AND ($army_origin->getId()!=$army_actual->getId())){
            debug::error('ArmyActual['.$army_actual->getId().'] es diferente a ArmyOrigin['.$army_origin->getId().']');
            $msg->error('Ya paso el turno del army seleccionada');
            $ok = false;
          }

          //Se Valida que la unidad pertenezca al player actual
           if(($ok) AND ($army_actual->getPlayerId()!=$player_id)){
             $ok = false;
             debug::error('OriginalArmyPlayer['.$army_actual->getPlayerId().']!=ActualPlayer['.$player_id.']','ajaxRegion action');
             $msg->error('La Unidad no te pertenece');
           }
           
          //Si todo esta corecto se procede a calcular los datos de la accion y validarlo
          if($ok ) {
              $target = new target($region_id,$action_id,$ox,$oy,$tx,$ty);
              $action = $actionman->findById($action_id);
              $battle = $battleman->findById($battle_id);
              $mov = new movutil($army_origin,$action,$target,$battle);
              $validation = $mov->validateDT($army_actual,$action_id);
          }
          
          //debug::log($target,'ajaxRegion->target');
          //$new_movement = $movementman->create($battle_id,$region_id,$target);
          //$new_movement = $movementman->create($battle_id,$target);
          //debug::log($new_movement,'ajaxRegion action new_movement');
          


          //debug::log($army_origin,'ajaxRegion armyOrigin');
          //debug::log($action,'ajaxRegion action');
          //debug::log($target,'ajaxRegion target');
          //debug::log($battle,'ajaxRegion battle');


          

          //debug::warning($validation,'ajaxRegion->action->actionman->validation');
          if( ($ok) AND !( $validation->hasSuccess() ) ){
            $text = $validation->getText();
            if(empty($text)){
              $ok = false;
              debug::error('La validacion de la accion no envio ninguna respuesta y es invalida','ajaxRegion->action->actionman->validation');
              $msg->error('Error en la accion enviada, vuelva a intentarlo');
            }
            else{
             $ok = false;
             debug::warning($text,'ajaxRegion->action->actionman->validation');
             $msg = $validation;
            }
          }

          //Se valida si la unidad origen tiene el suficiente mana
          if( $ok ){
            $manaTD = $battleutil->validateManaDT($army_actual->getId(),$mov->getAction()->getId(),false);
            if(!$manaTD->hasSuccess()){
              debug::warning($manaTD->getText(),'ajaxRegion->action->actionman->dt_mana');
              $msg = $manaTD;
              $ok = false;
            }
          }
          ///////////////////////////////////////////////////////////////////////
          //Ejecucion de Acciones
          ///////////////////////////////////////////////////////////////////////
          if($ok){
            $reactionTD = $mov->workActionDT();//Se ejecuta TEMP
            $msg = $reactionTD;
            if(!$reactionTD->isValid()){
              debug::warning($reactionTD->getText(),'ajaxRegion->action->reaction');
              $ok = false;
            }
            else{
              //$new_movement->saveActionTD($army_actual->getId());
              $regionState->advanceBattleTurn();
              $regionState->newAction=true;
              //Preguntamos si la unidad finalizo la batalla despues del movimiento
               if($regionState->battleHasEnded()){
                    $regionState->getBattle()->endBattlePhase();
               }
            }
          }
          
          ///////////////////////////////////////////////////////////////////////
          //Movemos el tutorial?
          ///////////////////////////////////////////////////////////////////////
          if($player->isTutorialActive() ){
              if($menustate->actualTutorialStep()==10 ){
                  $menustate->setPostActionTutorialStep();
              }
              
          }
          
          
          sendInfo($msg);
          break;

         /* *****************************************************
         * Fase Batalla - Boton Finalizar Turno
         *******************************************************/
        case 'endmyturn':
         $ok = true;
         //Validar si se esta en modo batalla
          $phase= $regionState->getBattle()->getPhase();
          if(($ok) AND !($phase==_X_BATTLE_PHASE_BATTLE)){
            debug::error('No se encuentra en fase BATTLE, estas en ['.$phase.']');
            $msg->error('No existen turnos si no estas en la fase de batalla');
            $ok = false;
          }

          //Validar que el nextArmie en cola sea igual a los datos de la unidad de origen
          $army_actual = $regionState->getNextArmyInBattle();
          if(($ok) AND ($army_actual->getPlayerId()!=$player_id)){
             $ok = false;
             debug::error('OriginalArmyPlayer['.$army_actual->getPlayerId().']!=ActualPlayer['.$player_id.']','ajaxRegion action');
             $msg->error('No es el turno de ninguna de tus unidades');
          }

          if($ok){
            $regionState->advanceBattleTurn();
             if(!empty($action_id)){//Si existe una accion se ejecuta
               $msg->message('Finalizo turno');
             }
             else{
               $msg->validWarning('Turno Finalizado sin accion');
             }
          }//if $ok
          sendInfo($msg);
        break;

        /* *****************************************************
        * Para Obtener el estado Comun de la Region, ya sea por
        * ele stado de una batalla En Fase de Espera, En Fase de Batalla
         * Batalla terminada o una batalla que no existe
        *******************************************************/
        case 'status':
            //debug::log('ajaxRegion armyOrigin');
        if(!$regionState->isBattleActive()){
            //Si no existe una batalla activa
            $msg->success('Sin novedad en la region');
            sendInfo($msg);
        }
        else{
            //[TODO] Si estamos en fase tactica no deberiamos de chekar nada de esto
            ////////////////////////Ciclo del AI//////////////////////////////
            $nextArmy = $regionState->getNextArmyInBattle();
            $armyPlayer = $playerman->findById($nextArmy->getPlayerId());
            //debug::log($armyPlayer,'regionstate->getRegionState->armyPlayer');

            if($armyPlayer->isArtificial()){
                //[TODO][FACTORIZE] medio complicado esta este aibattlestae, porque enviamos un raw?

                $aiutil = new aibattleutil($regionState->getRegion(),$nextArmy,$regionState->getBattle());
                //$pt = $aistate->getMovement();
                $movement = $aiutil->decideMovement();
                if($movement){
                   $movement->workActionDT();
                }
                else{
                    debug::log('No hay una accion elegida');
                }
                
                //$movement->saveActionTD($nextArmy->getId());
                //debug::log($movement,'ajaxRegion status ai_movement');

                //Preguntar si continuamos la batalla o temrinamos
                $regionState->advanceBattleTurn();
                if($regionState->battleHasEnded()){
                    $regionState->getBattle()->endBattlePhase();
               }
                 //debug::log('Es un jugador artificial','regionstate->getRegionState');
              }
              else{
                // debug::log('Es un jugador humano','regionstate->getRegionState');
              }
              //////////////////////////////////////////////////////////////////

                $msg->success('Esperando a los demas jugadores');
                sendInfo($msg);
        }
        
        break;

        case 'error':
          $msg->error('Error en el envio', 'error');
          sendInfo($msg);
         break;

        case 'tutorial_on':
            debug::log('Envio de datos TutorialON');
          $msg->error('Se genero el tutorial ON', 'error');
          $player->setTutorial(true);
          sendInfo($msg);
         break;

        case 'tutorial_off':
            debug::log('Envio de datos  TutorialOFF');
          $msg->error('Se genero el tutorial OFF', 'error');
          $player->setTutorial(false);
          sendInfo($msg);
         break;



        default:
          debug::console('ajaxRegion ajaxAction default NO EXISTE['.$ajaxAction.']');
          $msg->validWarning('Sin datos - Default', 'warning');
          sendInfo($msg);
        break;
        /* *****************************************************
         * Fase Tactica
         *******************************************************/
        case 'tactical':
            //debug::log('ajaxREgion tactical 1');
            $player->startBattlePhase($region_id,$battle_id);
            $menustate->setBattleTutorialStep();
            $regionState->updateState();//Actualizamos de nuevo el estado para maxima eficiencia nerf, osea para que en esta respuesta
            $msg->success('Tu fase tactica a finalizado');
            //debug::log($msg,'ajaxREgion tactical 3');
            sendInfo($msg);
            break;
        }
  }
  else{
  //Hubo un Error en el update de la region, enviamos su DT
      //debug::log('ajaxREgion ERROR');
    sendInfo($regionState->getDt());
  }
//Funcion que Wrapea el envio de mensajes al Cliente
function sendInfo($msg)
{
  //debug::console($msg,'ajaxRegion sendInfo msg');
  global $regionState;  
  global $db; 
  global $menustate;
  $db->commit();
  ajaxutil::sendAjax($msg,$regionState, $menustate);
  //$state['msg'] = ->getMsg();
  //$state = json_encode($state);
  //$send['xeno'] = $state;
  // $debug->endProfiler();
  // $send['profiler'] = $debug->getTime();
  
  
   //debug::console($state,'ajaxRegion sendInfo state');
  //$json->var2json($state);
}
?>
