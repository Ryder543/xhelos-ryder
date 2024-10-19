<?php

  if(!defined('_X_SECURE')){define("_X_SECURE",true);}

  $game_ajax_directory = getcwd();
  chdir(dirname ( __FILE__ ));
  require_once("../lib/include.php");
  chdir($game_ajax_directory);
  
  //Inicializacion de Datos
  $debug = new debug();
  $db = db::singleton();
  $json = json::singleton();
  $dt = new datatransfer();
    
  //Sanitizacion de Variables Externas
  $action = $_POST['ajax_action'];
  $region_id = validator::filter_integer($_POST['region_id']);
  $battle_id = validator::filter_integer($_POST['battle_id']);  

  $armyman = armyman::singleton();

  // 3) Obtencion de la vista del Player
  $playerman = playerman::singleton();
  $player = $playerman->findById($_SESSION['xid']);
  $player_id = $player->getId();

  $region_state = new regionview($region_id);
  $region_state->updateState();  
  $actual_army = $region_state->getNextArmyInBattle();
  
  //2)Seguridad en la transaccion, temrina en la funcion de mas abajo
  $db->begin();
  
//$debug->initProfiler();
$msg = new datatransfer();
//debug::log('entrando a actionMap','ajaxAI->actionMap');
switch($action)
{
    case 'actionMap':
        $action_id = validator::filter_integer($_POST['action_id']);
        $selected_army_id = validator::filter_integer($_POST['selected_army_id']);
        $ai = new aibattleutil($region_state->getRegion(),$actual_army,$region_state->getNextArmyInBattle(),$region_state->getBattle());
        if(!empty($_POST['flagAction'])){
            $flag = $_POST['flagAction'];
            switch($flag){
                case 'flagAction':
                    $newmap = $ai->actionMap($action_id,$selected_army_id);
                    $newmap = $ai->getMapUtil()->promediate($newmap,100);
                    //debug::info('ajaxAI flagAction');
                break;

                case 'flagDefense':
                    $newmap = $ai->obstacleMap();
                    //debug::info('ajaxAI flagDefense');
                break;

                case 'flagDefenseAction':
                    $actionmap = $ai->actionMap($action_id,$selected_army_id);
                    $actionmap = $ai->getMapUtil()->promediate($actionmap,100);
                    $obstaclemap = $ai->obstacleMap();
                    $newmap = $ai->getMapUtil()->obtainZeroMap($actionmap,$obstaclemap);
                    $newmap = $ai->getMapUtil()->promediate($newmap,100);
                    //$newmap = $ai->mergeMap($actionmap,$obstaclemap);
                    //debug::info('ajaxAI flagDefenseAction');
                break;

                case 'flagTravel':
                    $newmap = $ai->travelMap($selected_army_id);
                    //$newmap = $ai->getMapUtil()->promediate($newmap,20);
                    //debug::info('ajaxAI flagDefenseAction');
                break;

                case 'flagFull':
                    $newmap = $ai->fullMap($action_id,$selected_army_id);
                break;


                default:
                   $newmap = $ai->actionMap($action_id,$selected_army_id);
                   debug::warning('ajaxAI flagAction por defecto');
                break;
            }
        }
        else{
           $newmap = $ai->actionMap($action_id,$selected_army_id);
           debug::warning('ajaxAI flagAction no existe');
        }
       
        $msg->validWarning('renderTestMap');
        $min = min($newmap);
        $max = max($newmap);
        sendInfo($msg,$newmap,$min,$max);
    break;

    case 'calcTest':
            $msg->validWarning('renderTestMap');
            $ai = new aibattlestate($region_state->getRegion(),$actual_army,$region_state->getNextArmyInBattle(),$region_state->getBattle());
            $newmap = $ai->decideMovement(true);
            $min = min($newmap);
            $max = max($newmap);
            sendInfo($msg,$newmap,$min,$max);
        break;

    /* *****************************************************
     * Obtener Armies
     ********************************************************/
    case 'renderHeightMap':
        $msg->validWarning('renderTravelMap');

        $ai = new aibattlestate($region_state->getRegion(),$actual_army,$region_state->getNextArmyInBattle(),$region_state->getBattle());
        $newmap =$ai->travelMap();
        /*
        $unit = $region_state->getNextArmyInBattle();
        $speed = $unit->getSpeed();
        debug::log($speed,'ajaxAI renderHeightMap speed');
        //Obtener Mapa
        $region = $region_state->getRegion();
        $map = $region->getMap();
        $newmap = array();
        foreach($map as $key => $tile){
            $newmap[$key] = $speed[$tile-1];
            //debug::log($key,'ajaxAI renderHeightMap');
        }*/

        //debug::log($map,'ajaxAI renderHeightMap map');
        //debug::log($newmap,'ajaxAI renderHeightMap newmap');
        $min = min($newmap);
        //debug::log($min,'ajaxAI renderHeightMap min');
        $max = max($newmap);
        //debug::log($max,'ajaxAI renderHeightMap max');
        sendInfo($msg,$newmap,$min,$max);
    break;

    case 'renderEnemyMap':
        $newmap = enemyMap();
        $msg->validWarning('renderEnemyMap');
        sendInfo($msg,$newmap,0,0);
    break;

    case 'calcRango':
        $msg->validWarning('Mapa de Movimientos');
        $ai = new aibattlestate($region_state->getRegion(),$actual_army,$region_state->getNextArmyInBattle(),$region_state->getBattle());
        /*$travelmap =$ai->travelMap();
        $enemymap =$ai->enemyMap();

        $newmap = array();
        foreach($enemymap as $index=>$tile){
            if($travelmap[$index]==0){
                $newmap[$index]=0;
            }
            elseif($enemymap[$index]==100){
                $newmap[$index] = 100;
            }
            else{
                $tot = ($travelmap[$index]+$enemymap[$index])/2;
                $newmap[$index] = floor($tot);
            }
        }*/
        $newmap = $ai->movementMap();
        $min = min($newmap);
        //debug::log($min,'ajaxAI renderHeightMap min');
        $max = max($newmap);
        //debug::log($max,'ajaxAI renderHeightMap max');
        sendInfo($msg,$newmap,$min,$max);



    break;
    case 'calcRangoOldie':
        $rango =1;
        $x=6;
        $y=6;
        $indice = 23;
        //a) Obtener el primer indice segun el rango
        $restar = $rango *($y+1);
        $primer = $indice- $restar;

        $indices = array();
        $indices[] = $primer;

        //b) Obtener la primera fila de valores 
        for($i=1;$i<=($rango*2);$i++){
            $indices[] = $primer+$i;
        }

        debug::log($restar,'Prueb de mi WebService');
        debug::log($primer,'Prueb de mi WebService');

        $newmap = array();
        $msg->validWarning('renderEnemyMap');
        sendInfo($msg,$newmap,0,0);

        //c) Calcular los lados
        $intercalador = true;
        for($i=1;$i<=(4*$rango-1);$i++){
            $ultimo = end($indices);
            if($intercalador){
                $indices[] = $ultimo + $y-(2*$rango);
                $intercalador = false;
            }
            else{
                $indices[] = $ultimo + 2*$rango;
                $intercalador = true;
            }
        }
        //d) Calcular lo que sobre de lados, que es el lado de abajo del cuadrado
        for($i=1;$i<=($rango*2);$i++){
            $ultimo = end($indices);
            $indices[] = $ultimo+1;
        }
        debug::log($indices,'Prueb de mi WebService');
        break;

    /* *****************************************************
     * Accion enviad a desde el Server
     ********************************************************/
    default:
        $msg->validWarning('Sin datos DEBUG - Default');
        $json->var2json($msg);
        break;

}
function enemyMap(){
    global $region_state;
    global $player_id;
    global $region_id;
    global $armyman;
    $region = $region_state->getRegion();
    $total = $region->getTotalTiles();
    $maputil =  new maputil($region->getMap(),$region->getParam('x'),$region->getParam('y'),$region->getId());
    $enemies = new armies();

    $unit = $region_state->getNextArmyInBattle();
    $unit_index = $maputil->mapIndex($unit->getX(),$unit->getY());
    $enemy_player_id = $unit->getPlayerId();

    $enemies_raw = $enemies->initByAliveEnemiesOfPlayerInRegion($enemy_player_id,$region_id);
    

    $newmap = array();
    for($index=0;$index<$total;$index++){
        $newmap[$index] = 0;
    }

    foreach($enemies_raw as $enemy_id => $enemy){
        $enemy_index = $maputil->mapIndex($enemy['position_x'],$enemy['position_y']);
        //debug::log("enemyIndex:".$enemy_index,'['.$index.']');
        //$maputil->getXByIndex($enemy_index);
        
        $range1 = $maputil->getTilesAtRange($enemy_index , 1);
        $newmap[$enemy_index] = 100;
        foreach($range1 as $range){
            if($newmap[$range]<75){
            $newmap[$range]=75;
            }
        }
        $range2 = $maputil->getTilesAtRange($enemy_index, 2);
        foreach($range2 as $range){
            if($newmap[$range]<50){
            $newmap[$range]=50;
            }
        }
        $range3 = $maputil->getTilesAtRange($enemy_index, 3);
        foreach($range3 as $range){
            if($newmap[$range]<25){
            $newmap[$range]=25;
            }
        }
        
    }
    debug::log($newmap,'ajaxAI enemyMap');
    
    
    return $newmap;
}

//Funcion que Wrapea el envio de mensajes al Cliente
function sendInfo($msg,$map,$min,$max)
{
  global $region_state;  
  global $json;
  global $db; 
  //$state = $region_state->getRegionState();
  $state = array();
  $state['msg'] = $msg->getMsg();
  $state['movement']['map'] = $map;
  $state['movement']['min'] = $min;
  $state['movement']['max'] = $max;
  $state = json_encode($state);
  //$send['xeno'] = $state;
  // $debug->endProfiler();
  // $send['profiler'] = $debug->getTime();
  
  $db->commit();
  $json->var2json($state);
}
?>
