<?php

if (!defined('_X_SECURE')) {
    define("_X_SECURE", true);
}

$game_ajaxplanet_directory = getcwd();
chdir(dirname(__FILE__));
require_once("../lib/include.php");
chdir($game_ajaxplanet_directory);
//Inicializacion de Datos
$debug = new debug();
$db = db::singleton();
$json = json::singleton();
$dt = new datatransfer();


//Sanitizacion de Variables Externas
$action = $_POST['ajax_action'];
$region_id = validator::filter_integer($_POST['region_id']);
$battle_id = validator::filter_integer($_POST['battle_id']);

if (isset($_POST['action_id']) && (!empty($_POST['action_id']))) {
    $action_id = validator::filter_integer($_POST['action_id']);
    $ty = validator::filter_integer($_POST['ty']);
    $tx = validator::filter_integer($_POST['tx']);
    $ox = validator::filter_integer($_POST['ox']);
    $oy = validator::filter_integer($_POST['oy']);
}

// 1) Obtencion de la vista del Planeta
// 3) Obtencion de la vista del Player
$playerman = playerman::singleton();
$armyman = armyman::singleton();

$player = $playerman->findById($_SESSION['xid']);
$player_id = $player->getId();

//Alguna razon en especial para enviar primero el menu? en update? no lo se
  $menustate = new menuview();
  $menustate->updateState();

$regionState = new regionview($region_id);
$regionState->updateState();



//2)Seguridad en la transaccion, temrina en la funcion de mas abajo
$db->begin();

//$debug->initProfiler();
$msg = new datatransfer();
switch ($action) {
    
    case 'prueba':  
    $index = validator::filter_integer($_POST['index']);
    $mapUtil2 = $regionState->getRegion()->getCachedMapUtil();
    //debug::console($mapUtil2);
    
    $indexes = $mapUtil2->getIndexesForColony($index,_X_COLONY_TYPE_PRIMARY);
    //debug::console($indexes);
    $mapUtil2->obtainObstacleMap(422,true);
     break;   
        
    /******************************************************
     * Obtener Armies
     * ****************************************************** */
    
    case 'killunit':
        $army_id = validator::filter_integer($_POST['army_id']);
        $oArmy = $armyman->findById($army_id);
        $oArmy->killMe();
        $actualArmy = $regionState->getNextArmyInBattle();
        if($oArmy->getId() == $actualArmy->getId()){
            $regionState->advanceBattleTurn(); //Si mate al actual, pasar su turno
        }
        
        $msg->success('Se murio '.$oArmy->getName().'['.$oArmy->getId().']');
        sendInfo($msg);
        break;
    
    case 'endai':
        //Actual:iza siguiente turno de la unidad actual
        $regionState->advanceBattleTurn();
        $msg->success('El AI se movio');
        sendInfo($msg);

        break;

    case 'reiniciarBatalla':
        //Reiniciar la batalla
        $battle = $regionState->getBattle();
        $battle->endBattlePhase();
        $entityutil = new entityutil();
        //Seleccionar a todos los jugadores
        $players = new players();
        $players->initWithArmiesInRegion($region_id);
        $sideCounter = 1;
        foreach ($players as $i => $oPlayer) {
            //Seleccionar sus tropas y moverlas a un lugar diferente
            $armies = new armies();
            $armies->initByPlayerIdByRegionId($region_id, $oPlayer->getId());
            $side = maputil::mapUtilSideByNumber($sideCounter);
            $mapUtil = $regionState->getRegion()->getCachedMapUtil();
            $armies->moveArmiesToSideDt($side,$mapUtil);
            $sideCounter++;
        }
        //$msg->success('El AI se movio');
        //sendInfo($msg);

    //
    //Obtener a todos los jugadores

    case 'revivirUnidades':

        /* 	  $sql = "UPDATE armies as a SET size = 
          (SELECT max_size FROM creatures as c WHERE c.id = a.creature_id),
          total_life = (SELECT (max_size*life) FROM creatures as c WHERE c.id = a.creature_id),
          life = (SELECT life FROM creatures as c WHERE c.id = a.creature_id),
          state = '"._X_ARMY_STATE_ALIVE."'
          WHERE a.region_id = $region_id";
          // debug::warning($sql,'ajaxDebug->revivirUnidades');
          $db->query($sql); */
        //sleep(1);//[TODO]no deberia de tener este sleep, pero asi funcionara por mientras
        $armies = new armies();
        $armies->initByRegion($region_id);
        $dt1 = $armies->resetDt();

        $msg->success('Las unidades cobran vida!!!');
        ($dt1->isValid() ) ? : ($msg->error('No se pudo revivir a las unidades!!!') );
        sendInfo($msg);
        break;

    case 'regenerateEnergy':

        $players = new players();
        $players->initWithArmiesInRegion($region_id);
        $dt1 = $players->resetManaDt();

        $armies = new armies();
        $armies->initByRegion($region_id);
        $dt2 = $armies->resetEnergyDt();

        $msg->success('Se regenero la Energia!!!');
        ($dt2->isValid() ) ? : ($msg->error('No se pudo actualizar la energia a las unidades') );
        ($dt1->isValid() ) ? : ($msg->error('No se pudo actualizar la energia a los jugadores') );

        sendInfo($msg);

        break;
    /*     * ****************************************************
     * Accion enviada desde el Server
     * ****************************************************** */
    default:
        $msg->message('Sin datos DEBUG - Default', 'warning');
        $json->var2json($msg);
        break;
}

//Funcion que Wrapea el envio de mensajes al Cliente
function sendInfo($msg) {
    
  global $regionState;
  $regionState->updateState();
  global $db; 
  global $menustate;
  $db->commit();
  ajaxutil::sendAjax($msg,$regionState, $menustate);
}

?>
