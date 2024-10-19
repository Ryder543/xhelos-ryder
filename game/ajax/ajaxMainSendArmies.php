<?php
  if(!defined('_X_SECURE')){define("_X_SECURE",true);}
require_once (dirname ( __FILE__ ).'/include.php');
require_once (dirname ( __FILE__ ).'/php/debug.class.php');
$debug = new debug();
require_once (dirname ( __FILE__ ).'/php/db.class.php');
require_once (dirname ( __FILE__ ).'/php/json.class.php');
require_once (dirname ( __FILE__ ).'/php/armies.class.php');
require_once (dirname ( __FILE__ ).'/php/army.class.php');
require_once (dirname ( __FILE__ ).'/php/gamestate.class.php');
require_once (dirname ( __FILE__ ).'/php/validator.class.php');
$player = $_SESSION['xid'];
$db = db::singleton();
$json = json::singleton();
$gamestate = gamestate::singleton();

//Variables Externas
$action = $_POST['action'];
$armies_id=validator::filter_integer($_POST['armies']);
$region_id=validator::filter_integer($_POST['region_id']);

//$debug->initProfiler();

switch($action)
{
    /* ************************************************************************************
    * getBattleAvailableArmies :  Obtener unidades que esten lista para batalla
    *************************************************************************************/
    case 'getBattleAvailableArmies':
		//SQL que obtiene las unidades listas para la batalla
		//[TODO] Ponerlo en armies o army?
		
		/*$sql = "SELECT * FROM armies
		WHERE armies.region_id IN (
			SELECT armies.region_id FROM armies WHERE armies.player_id =".$player."
			EXCEPT
			SELECT armies.region_id FROM armies WHERE armies.player_id !=".$player.")
		AND armies.state = '"._X_ARMY_STATE_ALIVE."'
		AND armies.position = '"._X_POSITION_ORBIT."' 
		AND armies.next_position = '"._X_POSITION_ORBIT."'";*/
		//updateAvailableUnits($player);
		$gamestate->updateState($player,$region);
		$ret = getTravelStatus($player);
		$json->var2json($ret);
	break;
    
	case 'getAvailableRegions':

		//[TODO] Obtenerlo con la clase regions??
		$sql="SELECT id,name FROM regions WHERE regions.state = '"._X_REGION_STATUS_ACTIVE."' ORDER by id";
		$regions = $db->sql2array($sql);
		//Transformamos el mapa en un arreglo
		/*foreach($regions as &$region){ //http://Para modificarel array usamos &  ver php.net/manual/en/control-structures.foreach.php
			$region['map'] = str2array($region['map']);
		}*/
		echo json_encode($regions);
		//$json->var2json($regions);
	break;
	
	case 'sendArmies':
	  //global $firephp;
		$gamestate->updateState($player,$region);
		//updateAvailableUnits($player);
		if(!empty($armies_id)){
			$sql="SELECT * FROM armies WHERE armies.id IN (".$armies_id.")";
			$armies_raw=$db->sql2array($sql);
			$armies = new armies();
			$armies->init($armies_raw);
			
			if($armies->areAllFromPlayer($player)){//Verificar que sean del jugador activo
				$resp = $armies->sendArmiesToRegion($region_id);
        //$firephp->log($resp,'resp');
				if($resp){
					$ret = getTravelStatus($player);
          //$firephp->log($resp,'ret');
					$json->var2json($ret);
				}
			}
			else{
				echo "No todas las unidades son del jugador elegido";
			}
		}
		else{
			echo "No Hay unidades seleccionadas";	
		}
		
	break;
    	
	default:
	    $msn->message('Sin datos-Default[ajaxMainSendArmies.php]', 'warning');
	    $send['msg'] = $msn->get();
	    $json->var2json($send);
    break;
}
	function getTravelStatus($playerId){
		$status['sended'] = getSendedArmies($playerId);
		$status['sendable'] = getSendableArmies($playerId);
		return $status;
	}

	function getSendableArmies($playerId){
		global $db;
		$sql="SELECT a.*,p.name as region_name FROM armies AS a
		INNER JOIN regions AS p ON (a.region_id = p.id)
		WHERE a.player_id =".$playerId."
		 AND a.state = 'A' 
		 AND a.position = 'O' 
		 AND a.next_position = 'O'";
    	return $db->sql2array($sql);
	}
	
	function getSendedArmies($playerId){
		global $db;
		$sql="SELECT a.*,t.id as travel_id,t.eta,t.from_region,t.to_region,
						t.state as time_state,fp.name AS from_region_name,
						tp.name AS to_region_name,time_to_sec2(t.eta,now()) as seconds
						FROM armies as a 
						INNER JOIN travel as t ON (a.id = t.armies_id)
						INNER JOIN regions as tp ON (t.to_region = tp.id)
						INNER JOIN regions as fp ON (t.from_region = fp.id)
						WHERE a.player_id = ".$playerId." AND t.state = 'A' AND a.state = 'A'";
						return $db->sql2array($sql);
	}
