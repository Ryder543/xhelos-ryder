<?php

  if(!defined('_X_SECURE')){define("_X_SECURE",true);}

  $game_ajaxplanet_directory = getcwd();
  chdir(dirname ( __FILE__ ));
  require_once("lib/include.php");
  
  require_once("lib/php/json.class.php");
  require_once ("lib/php/debug.class.php");
  require_once ("lib/php/db.class.php");
  require_once ("lib/php/planetstate.class.php");
  require_once ("lib/php/datatransfer.class.php");
  require_once ("lib/php/validator.class.php");
  require_once ("lib/php/battle.class.php");
  require_once ("lib/php/battleman.class.php");
 // require_once ("lib/php/region.class.php");
  //require_once ("lib/php/regionman.class.php");


  chdir($game_ajaxplanet_directory);
  //Inicializacion de Dato
  //$db = db::singleton();
 // $json = json::singleton();
  $dt = new datatransfer();
 // $regionman = regionman::singleton();
  
  //Sanitizacion de Variables Externas
  $action = $_POST['ajax_action'];
  $origin_region_id = validator::filter_integer($_POST['origin_region_id']);
  $target_region_id = validator::filter_integer($_POST['target_region_id']);
  //$planet_id = validator::filter_integer($_POST['planet_id']); //[TODO]Planeta depende de las regiones
  
  //Validamos si las regiones vienen del mismo planeta
  //$planet_id = validateRegions($origin_region_id,$target_region_id);
 /// if(!$planet_id){
  //  $action = 'error';
  //}
  
  // 3) Obtencion de la vista del Player
 // $playerman = playerman::singleton();
 // $player = $playerman->findById($_SESSION['xid']);
 // $player_id = $player->getId();

  //1)gamestate actualiza la informacion del planeta
 // $planetstate = new planetstate($planet_id,$player_id);
 // $planetstate->updateState();
  
  
//$debug->initProfiler();
debug::error($action,'ajaxRegion->action');
switch($action)
{
     
    /* *****************************************************
     * Atacar Region
     ********************************************************/
    case 'attack':
        //Validaciones
        $ok = true;
        //1)Validar que region origen exista
        /*
        $region_origen = $regionman->findById($origin_region_id);
        if(($ok)&&(empty($region_origen))){//Si el primero ta mal tonces dejar
          debug::error('La region de origen no existe','ajaxRegion->action');
          $dt->error('La region de origen no existe');
          $ok = false; 
        }
        //2)Validar que region origen contenga al army del jugador
        $armies = new armies();
        $armies->initByPlayerRegion($origin_region_id,$player_id);
        if( ($ok) AND !($armies->exist()) ){//Si el primero ta mal tonces dejar
          debug::warning('No hay unidades del jugador['.$player_id.'] en la region ['.$origin_region_id.']','ajaxRegion->action');
          $dt->error('No tienes armies en la region de origen');
          $ok = false;
        }
        
        
        //3)Validar que armies a mover sean del jugador
        if( ($ok) AND !($armies->areAllFromPlayer($player_id)) ){//Si el primero ta mal tonces dejar
          debug::warning('Las unidades no son de un solo jugador['.$player_id.'] en la region ['.$origin_region_id.']','ajaxRegion->action');
          debug::log($armies,'ajaxRegion->action');
          $dt->error('Las unidades seleccionadas no son de un solo jugador');
          $ok = false;
        }
        
        
        //4) Validar que no haya una batalla en la region origen
        $battleman = battleman::singleton();
        $battle = $battleman->findByRegionId($origin_region_id);
        //debug::warning($battle,'battle ajaxPlanet');
        if( ($ok) AND ($battle->exist()) ){//Si el primero ta mal tonces dejar
          debug::warning('Hay una batalla en la region de origen['.$origin_region_id.']','ajaxRegion->action');
          $dt->error('Tus armies no se pueden mover debido a la batalla actual en la region de origen');
          $ok = false;
        }
        //5) Validar que region target exista
        $region_target = $regionman->findById($target_region_id);
        if( ($ok) && (empty($region_target)) ){//Si el primero ta mal tonces dejar
          debug::error('La region objetivo no existe','ajaxRegion->action');
          $dt->error('La region objetivo no existe');
          $ok = false; 
        }
        
        
        //6) Validar que sea una region adyacente
        $dt_adjacentRegions = validateAdyacentRegionsDT($origin_region_id,$target_region_id);
        if( ($ok) AND !($dt_adjacentRegions->isValid()) ){//Si el primero ta mal tonces dejar
          debug::error('Las regiones O['.$origin_region_id.'] y T['.$target_region_id.'] no son adyacentes','ajaxRegion->action');
          $dt = $dt_adjacentRegions;
          $ok = false; 
        }*/

        // Verificacion y Sanitizacion de la Batalla
        //1) Las unidades no pueden atacar si estan en movimiento
        //$armies->initByNotTravelingPlayerRegion($origin_region_id,$player_id);
        
        
        //3) No pueden estar en guerra o en otra batalla[HACER][TODO]

        /*if($ok){
        //if(!$armies->areInBattle($origin_region_id)){//Si no estan en batalla
          $armies->sendArmiesToRegion($origin_region_id,$target_region_id);
          $dt->success('Se Mando a Atacar');
        }
        //}
        //else{
         // $dt->warning('No se pudo enviar porque estan actualmente en batalla');
        //}
        
        // Ejecucion de Attack si todo es correcto, se manda las unidades a la batalla
         
        //2) Se crea la batalla en estado tactico solo si han llegado a su lugar
        //$battle = new battle();
        //$battle_id = $battle->startTacticPhase($target_region_id,$player_id);
        //[WHY] La batalla no se crea hasta que hallan llegado a su lugar
        
        //se espera 2 minutos hasta que acepten la batalla todas las partes
        // mientras tanto se verifica el terreno, una vez verificado empeiza la
        //batalla pero esto ya en la vista
        //3) El crear la batalla implica tambien añadir datos en battles_players
        //[HACER]???
         
        // Envio de Datos a Planet
        //0) Si todo esta correcto actualizamos armies
        //2) Se envian las batallas actuales! y su estado!
           
        //Ejecutamos las ordenes
        
        
        sendInfo($dt->getMsg());*/
        break;
    /* *****************************************************
     * Defender Region
     ********************************************************/
    case 'defend':
        $dt->success('Se Mando a Defender');
        //        $msn->message();     
        sendInfo($dt->getMsg());
    break;

    /* *****************************************************
     * Mover Region
     ********************************************************/
    case 'move':
        $dt->success('Se Mando a mover');
        // $msn->message('Se Mando a mover', 'success');
        sendInfo($dt->getMsg());
    break;

    case 'error':
      $dt->error('Error en el envio');
      sendInfo($dt->getMsg());
    break;

    default:
       $dt->validWarning('Sin datos - Default');
      //$msn->message('Sin datos - Default', 'warning');
      sendInfo($dt->getMsg());
    break;
    


}
function validateRegions($origin_id,$target_id){
  //Mejorar esto
    global $regionman;
  $origin_region = new region();
  $target_region = new region();
  $origin_region = $regionman->findById($origin_id);
  $target_region = $regionman->findById($target_id);
  if($origin_region->getParam('planet_id')==$target_region->getParam('planet_id')){
    return $origin_region->getParam('planet_id');
  }
  else {
   return false; 
  } 
}

function validateAdyacentRegionsDT($origin_id,$target_id){
  //Mejorar esto
  $dt = new datatransfer();
  global $regionman;
  $origin_region = $regionman->findById($origin_id);
  $target_region = $regionman->findById($target_id);
  debug::warning('[TODO] validar correctamente esto','ajaxPlanet->validateAdtacentRegions');
  //Verificar que las regiones esten en el mismo planeta
  if($origin_region->getParam('planet_id')==$target_region->getParam('planet_id')){
      //Regiones en el mismo planeta, ahora er si estan adyacentes
    $dt->success('Las regiones estan en el mismo planeta');
  }
  else {
    $dt->error('Las regiones no estan en el mismo planeta');
  }
  return $dt;
}



//Funcion que Wrapea el envio de mensajes al Cliente
function sendInfo($msg)
{
  global $planetstate;  
  global $json;
  $state = $planetstate->getPlanetState();

  $state['msg'] = $msg;
  //$firephp->log($state);  
  $state = json_encode($state);
  //$send['xeno'] = $state;
  // $debug->endProfiler();
  // $send['profiler'] = $debug->getTime();
  $json->var2json($state);
}
?>
