<?php
//header('Content-type: application/json');

  if(!defined('_X_SECURE')){define("_X_SECURE",true);}

  $game_ajaxplanet_directory = getcwd();
  chdir(dirname ( __FILE__ ));
  require_once("../lib/include.php");
  chdir($game_ajaxplanet_directory);
  //Inicializacion de Datos
  $db = db::singleton();
  $json = json::singleton();
  $msg = new datatransfer();
  
  //Sanitizacion de Variables Externa 
  $ajaxAction = $_POST['ajax_action'];
  $colonyId = validator::filter_integer($_POST['colony_id']);
  
  $colonyState = new colonyview($colonyId);
  $colonyState->updateState();
  
  $menu = new menuview();
  
  //2)Seguridad en la transaccion, temrina en la funcion de mas abajo
  if($colonyState->getDt()->isValid()){
    $db->begin();
    switch($ajaxAction)
    {
         /* *****************************************************
         * Fase Accion - Accion del turno de una unidad
         *******************************************************/
        case 'create_construction':
            
            //Validar si puede con los costes
            
            $colonyId = validator::filter_integer($_POST['colony_id']);
            $buildingId = validator::filter_integer($_POST['building_id']);
            $buildingX = validator::filter_integer($_POST['building_x']);
            $buildingY = validator::filter_integer($_POST['building_y']);
            $entityutil = new entityutil();
            $dt = $colonyState->validateAndSubstracInternalBuildingCostDt($colonyId,$buildingId,_X_COLONY_INITIAL_CONSTRUCTED_LEVEL); //valor inicial
            if(! $dt->isValid() ){
                //debug::log($dt,'ajaxColony create_construction');
                $db->rollback();
            }
            else{
                $dt = $entityutil->createConstructionInColonyDt($colonyId, $buildingId, $buildingX, $buildingY);
                //debug::log($dt,'ajaxColony create_construction');
                if(! $dt->isValid() ){
                    $db->rollback();
                }
                else{
                    $constr_raw = $dt->getData();
                    //debug::log($constr_raw,'ajaxColony create_construction constr_raw');
                    $dt-> setText('Se empezo a construir un '.$constr_raw['name'].' en la colonia '.$colonyState->getColony()->getName());
                }
            }

            $msg =$dt->getMsg();
            sendInfo($msg);
        break;
         case 'update_state': //Llamado para obtener el estado actual
             $msg = array();
             $msg['text'] = 'Mensaje Default';
             $msg['type'] = 'success';
             sendInfo($msg);
             break;
             
         case 'upgrade_construction':
            $colonyId = validator::filter_integer($_POST['colony_id']);
            $buildingId = validator::filter_integer($_POST['building_id']);
            $coordX = validator::filter_integer($_POST['building_x']);
            $coordY = validator::filter_integer($_POST['building_y']);
            //$dt = new datatransfer();
            //$dt->success('JODER');
            
            $dt = $colonyState->upgradeConstructionDt($colonyId,$coordX,$coordY);
            //debug::log($dt,'ajaxColony->upgrade_construction dt upgradeConstructionDt');
            if(! $dt->isValid() ){
                    $db->rollback();
            }
            else{
                $oIC = $dt->getData();
                $oIB = $colonyState->construction2building($oIC);
                //debug::log($oIC,'ajaxColony uograde_construction oIC');
                $dt-> setText('Se empezo a mutar el '.$oIB->getName().' en la colonia '.$colonyState->getColony()->getName().' al nivel '.$oIC->getNextLevel());
            }
            
            $msg =$dt->getMsg();
            sendInfo($msg);
    }
  }
  else{
  //Hubo un Error en el update de la region, enviamos su DT
    sendInfo($colonyState->getDt());
  }
//Funcion que Wrapea el envio de mensajes al Cliente
function sendInfo($msg)
{
  global $colonyState;  
  global $json;
  global $db; 
  global $menu; 
  $state = $colonyState->getState();
  $state['msg'] = $msg;
  $menu->updateState();
  $state['global'] = $menu->getState();
  $state = json_encode($state);
  $db->commit();
  $json->var2json($state);
}
?>
