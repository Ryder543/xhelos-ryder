<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad

/* * *******************************************************
 * regionstate: Encargado de tener el estado de la region 
 * ****************************************************** */

class battleutil extends util {

    ///////////////////////////////////////////////////////////////////////////////////////
    //PROPIEDADES
    ///////////////////////////////////////////////////////////////////////////////////////    
    ///////////////////////////////////////////////////////////////////////////////////////
    //CONSTRUCTOR
    ///////////////////////////////////////////////////////////////////////////////////////    
    /*     * ******************************************
     * Constructor: De regionState
     * Params: 
     * $region_id: ID de la region que quiere mantener el estado
     * $player_id: ID del jugador actual de la region
     * EXTRA: Obtener la lista de arg $args = func_get_args(); 
     * ****************************************** */
    public function __construct() {
        parent::__construct();
    }

    public function calculateInverseQuanta($oAction, $sizeOfObjetive) {
        $quanta = ceil(100 / $sizeOfObjetive);
        return $quanta;
    }

    /*     * *******************************************************************************
     * calculateActionQuanta: Calcula cuanto afecta una accion
     * ******************************************************************************* */

    public function calculateActionQuanta($originArmy, $objetives, $oAction) {
        $quantaAction = 0;
        if ($oAction->getScope() == _X_ACTION_SCOPE_SINGLE) {
            if ($oAction->targetIs(_X_TARGET_ENEMYANDARMY)) {
                $totalDamage = $originArmy->getTotalAttackDamage($objetives->getArmor());
                if ($totalDamage >= $objetives->getTotalLife()) {
                    $quantaAction = 100;
                } else if ($totalDamage <= 0) {
                    $quantaAction = 0;
                } else if ($objetives->getTotalLife <= 0) {
                    $quantaAction = 0;
                } else {
                    //Obtenemos el porcentaje de apreciacion
                    $quantaAction = (1 / ($objetives->getTotalLife() / $totalDamage)) * 100;
                }
            }
        } else {
            debug::error('No se a definido codigo para este scope[' . $action->getScope() . '], es multiple', 'battleutil->calculateActionQuanta');
        }
        return $quantaAction;
    }

    /*     * *******************************************************************************
     * calculateReactionQuanta: Calcula cuanto afecta una reaccion, ojo
     * no todas las habilidades tienen reaccion
     * ******************************************************************************* */

    public function calculateReactionQuanta($originArmy, $objetives, $oAction) {
        //TODO esto lo debe de calcular basandode en army->receiveAttack
        if ($oAction->getScope() == _X_ACTION_SCOPE_SINGLE) {
            if ($oAction->targetIs(_X_TARGET_ENEMYANDARMY)) {
                $totalDamage = $originArmy->getTotalAttackDamage($objetives->getArmor());
                if ($totalDamage >= $objetives->getTotalLife()) {
                    $quantaReaction = 0;
                } else {
                    //$totalDamage
                    $totalReactionDamage = $originArmy->getTotalAttackDamage($objetives->getArmor());
                    if ($totalDamage <= 0) {
                        $quantaReaction = 0;
                    } else if ($objetives->getTotalLife >= 0) {
                        $quantaReaction = 0;
                    } else {
                        //Obtenemos el porcentaje de apreciacion
                        $quantaReaction = (1 / ($objetives->getTotalLife() / $totalDamage)) * 100;
                    }
                }
            }
        } else if ($oAction->getScope() == _X_ACTION_SCOPE_MULTIPLE) {
            if ($oAction->targetIs(_X_TARGET_ENEMYANDARMY)) {
                debug::error('No se a definido codigo para este scope[' . $action->getScope() . '], es multiple', 'battleutil->calculateActionQuanta');
            } else {
                debug::error('No se a definido codigo para este scope[' . $action->getScope() . '], es multiple', 'battleutil->calculateActionQuanta');
            }
        } else {
            debug::error('No se a definido codigo para este scope[' . $action->getScope() . '], es multiple', 'battleutil->calculateActionQuanta');
        }
        return $quantaReaction;
    }

    public function calculateQuanta($index, $action, $objetives, $maputil) {


        if ($action->getScope() == _X_ACTION_SCOPE_SINGLE) {

            $targetExist = false;

            foreach ($objetives->getRaw() AS $army_raw) {
                ///debug::log($objetives,'Objetivos del indice['.$index.']');
                //debug::log("armyIndex[".$armyIndex."] =? index[".$index."]",'battleutil->getQuanta');
                $army = $this->man('army')->wrap($army_raw);
                $armyIndex = $maputil->mapIndex($army->getX(), $army->getY());

                if ($index == $armyIndex) {

                    $targetExist = true;
                    break;
                }
            }
            //[TODO]Mejorar el Sistema De Quanta, unidades con menos vida, dan mejor resultado
            if ($targetExist) {
                return 100;
            } else {
                return 0;
            }
        } else if ($action->getScope() == _X_ACTION_SCOPE_MULTIPLE) {
            $total = 0;
            foreach ($objetives->getRaw() AS $army_raw) {
                ///debug::log($objetives,'Objetivos del indice['.$index.']');
                //debug::log("armyIndex[".$armyIndex."] =? index[".$index."]",'battleutil->getQuanta');
                $army = $this->man('army')->wrap($army_raw);
                $armyIndex = $maputil->mapIndex($army->getX(), $army->getY());

                if ($index == $armyIndex) {
                    $total = $total + 100;
                }
            }
            return $total;
        } else {
            debug::error('No se a definido codigo para este scope[' . $action->getScope() . ']', 'battleutil->getQuanta');
            return 0;
        }
    }

    public function calculateCoordsFromSide($army_id, $region_id, $side, $maputil = null) {

        //debug::trace('battleutil->calculateCoordsFromSide');  
        if ($maputil == null) {
            $region = $this->man('region')->findById($region_id);
            $maputil = $region->getCachedMapUtil();
            //$maputil = new maputil($region->getMap(), $region->getParam('x'), $region->getParam('y'),$region->getId());
            debug::warning('battleutil->calculateCoordsFromSide Se creo un maputil al no ser enviado como parametro');
        }

        // $maputil->debug();
        $itermap = $maputil->getWorkingMap($side);
        $army = $this->man('army')->findById($army_id);
        $coord = false;
        //debug::log($itermap,'battleutil->calculateCoordsFromSide itermap');
        foreach ($itermap AS $index => $tile) {
            if ($this->tileIsMovableByArmy($army_id, $region_id, $index, $tile, $maputil)) {//Enviamos aqui el maputil para evitar recalculos
                $maputil->removeFromAllWorkingMaps($index);
                $coord = $maputil->getCoordByIndex($index);
                break; //Esto es importante, calcula por una ultima vez y se sale, de lo contrario el bucle continua
            } else {
                $maputil->removeFromAllWorkingMaps($index);
            }
        }
        return $coord;
    }

    public function calculateArmyEnteringSide($planet_id, $travel) {
        $side = '';
        if ((!isset($travel) ) OR empty($travel)) {
            //debug::trace('El army no entro por ningun sitio['._X_ARMY_SIDE_NONE.']','calculateEnterSide army.class');
            $side = _X_ARMY_SIDE_NONE;
        } else {
            //debug::trace($last_travel,'armies->calculateArmyEnterSide->last_travel');
            $origin_region = $this->man("region")->findById($travel['from_region']);
            $target_region = $this->man("region")->findById($travel['to_region']);

            $regions = new regions();
            $regions->initByPlanetId($planet_id);

            $sides = $regions->getSides();
            $origenCoord = $origin_region->getCoord($sides['y']);
            $targetCoord = $target_region->getCoord($sides['y']);
            //debug::log($origenCoord,'planetstate->areRegionAdyacentDT->origin');
            // debug::log($targetCoord,'planetstate->areRegionAdyacentDT->target');
            if ($origenCoord['x'] == $targetCoord['x']) {
                $absolute = abs($origenCoord['y'] - $targetCoord['y']);
                if ($absolute == 1) {
                    if ($origenCoord['y'] > $targetCoord['y']) {
                        $side = _X_ARMY_SIDE_BOTTOM;
                    } else {
                        $side = _X_ARMY_SIDE_TOP;
                    }
                } else {
                    //debug::warning('Las regiones no son adyacentes en el eje Y','armies->calculateArmiesEnteringSide');
                    $side = _X_ARMY_SIDE_NONE;
                }
            } else if ($origenCoord['y'] == $targetCoord['y']) {
                //debug::log('Estan en el mismo y['.$origenCoord['y'].']','planetstate->areRegionAdyacentDT->samey');
                $absolute = abs($origenCoord['x'] - $targetCoord['x']);
                if ($absolute == 1) {
                    if ($origenCoord['x'] > $targetCoord['x']) {//WTF
                        $side = _X_ARMY_SIDE_RIGHT;
                    } else {
                        $side = _X_ARMY_SIDE_LEFT;
                    }
                    //debug::log('Estan al lado','planetstate->areRegionAdyacentDT->adaycentx');
                } else {
                    //debug::warning('Las regiones no son adyacentes en el eje X','armies->calculateArmiesEnteringSide');
                    $side = _X_ARMY_SIDE_NONE;
                }
            }
        }
        if ($side != _X_ARMY_SIDE_NONE) {
            //debug::trace($side,'armies->calculateEnterSide');
        }
        return $side;
    }

    public function tileIsMovableByArmy($army_id, $region_id, $index, $tile, $maputil) {
        /* Advertencia: El calculo deberia de hacerse teniendo en cuenta que las unidades estan aun
         * flotando en el aire. Por lo que no tienen ni x ni y */
        $army = $this->man('army')->findById($army_id);
        if ($army->isTerrainMovable($tile)) {
            //[TODO] Urgente 26dic2012 La info de aqui deberia de sacarla del mapa de obstaculos de maputl
            $region = $this->man('region')->findById($region_id);
            //$maputil = $region->getCachedMapUtil();
            //$index =  $maputil->mapIndex($x,$y);
            $obstaculized = $maputil->isTileObstaculized($index);
            if ($obstaculized == true) {
                return false; //Esta obstaculizado por lo que ese tile no es movible
            } else {
                return true;
            }

            /*
              $possibleObstacle = $this->man('army')->findByCoord($region_id,$x,$y);
              if( !$possibleObstacle->exist() ){
              //debug::log($possibleObstacle,'battleutil->tileIsMovable TRUE');
              return true;
              }
              else{
              //debug::log($possibleObstacle,'battleutil->tileIsMovable FALSE');
              return false;
              } */
        } else {
            return false; //Aqui es FALSO!!!
        }
    }

    /*     * *******************************************************************************
     * validateManaDT: Valida el mana descargado
     * ******************************************************************************* */

    public function validateManaDT($armyId, $actionId, $remove = true) {
        $oArmy = $this->man("army")->findById($armyId);
        $oAction = $this->man("action")->findById($actionId);

        $dt = new datatransfer();
        $action_mana = $oAction->getEnergy();
        $army_mana = $oArmy->getActualEnergy();

        if ($army_mana >= $action_mana) {
            $dt->success('Tu Army tiene la suficiente Energia:[' . $army_mana . ']');
            if ($remove) {
                $oArmy->removeEnergyDt($action_mana);
            }
        } else {
            //$dt->error($oArmy->getName().' no tiene suficiente '.viewutil::getImg('web/icon_mana.png',15,15).'('.$army_mana.') para la accion '.$oAction->getName().'['.$action_mana.']');
            $imgicon= viewutil::getImg('web/icon_mana.png', 15, 15);
            if($imgicon){
                $dt->invalidWarning('La accion ' . $oAction->getName() . ' cuesta  ' . $action_mana . ' ' . viewutil::getImg('web/icon_mana.png', 15, 15) . ' para ejecutarse y ' . $oArmy->getName() . ' tan solo tiene ' . $army_mana . ' ' . viewutil::getImg('web/icon_mana.png', 15, 15));
            }
            else{
               $dt->error("ERROR:EN EL JUEGO: _X_FILE_ROOT"); 
               debug::error(_X_ERROR_XFILEROOT_DONT_EXIST);
            }
            
        }
        return $dt;
    }

    public function movementCost($speed) {
        if ($speed == 0) {
            return 0;
            //debug::trace('battleutil->movementCost speedIsZero');
        } else {
            return $this->obtainNormalCostMovement() / $speed;
        }
    }

    public function obtainNormalCostMovement() {
        return 100;
    }

    /*     * **************************************************************************************
     * getMapUtil: Quizas no deberia de estar aqui, obtenemos un MapUtil basado en una region
     * ************************************************************************************** */
    /* public function getMapUtil($region_id){
      $region = $this->man('region')->findById($region_id);
      //debug::log($region,'battleutil->getMapUtil region');
      //debug::log($this->man('region'),'battleutil->getMapUtil regionman');
      $maputil = new maputil($region->getMap(), $region->getParam('x'), $region->getParam('y'),$region->getId());
      return $maputil;
      } */

    public function returnArmyToRegion($army_id, $region_id) {
        $origin_region = $this->man("region")->findById($region_id);
        $origin_planet_id = $origin_region->getPlanetId();
        $army = $this->man("army")->findById($army_id);
        //debug::log($travel,'armies->prepareForRegionBattle no hay espacio para esta unidad');
        $army->sendFromRegionToRegion($travel['to_region'], $travel['from_region'], $origin_planet_id);
    }

    public function getAllegiance($objectId, $objectType, $playerId = false) {
        //Obtenemos al Jugador Actual
        $oActualPlayer = null;
        $type = 'notdefined';
        if (!$playerId) {
            $oActualPlayer = $this->getCurrentPlayer();
        } else {
            $oActualPlayer = $this->man('player')->findById($playerId);
        }
        //Si el objeto es de tipo player
        if ($objectType == 'player') {
            $object = $this->man('player')->findById($objectId);
            //debug::log($oActualPlayer,'battleutil->getAllegiance actualPlayer['.$oActualPlayer->getId().']');
            //debug::log($object,'battleutil->getAllegiance objectPlayer['.$object->getId().']');
            if ($object->getId() == $oActualPlayer->getId()) {
                $type = 'myself';
            } elseif ($object->getType() == 'A') {
                $type = 'AI';
            } else {
                $type = 'enemy';
            }
            //debug::info($type,"battleutil->getAllegiance");
            return $type;
        } else {
            $oObject = $this->man($objectType)->findById($objectId);

            $continue = false;

            if (method_exists($oObject, 'getOwnerId')) {
                $ownerId = $oObject->getOwnerId();
                if (empty($ownerId) || ($ownerId == _X_PLAYER_NO_PLAYER_ID)) {
                    $type = 'noallegiance';
                } else {
                    $oObjectPlayer = $this->man('player')->findById($ownerId);
                    $continue = true;
                }
            } elseif (method_exists($oObject, 'getPlayerId')) {
                $ownerId = $oObject->getPlayerId();
                if (empty($ownerId) || ($ownerId == _X_PLAYER_NO_PLAYER_ID)) {
                    $type = 'noallegiance';
                } else {
                    $oObjectPlayer = $this->man('player')->findById($ownerId);
                    $continue = true;
                }
            } elseif (method_exists($oObject, 'getRegionId')) {

                $regionId = $oObject->getRegionId();
                $oRegion = $this->man("region")->findById($regionId);

                $ownerId = $oRegion->getOwnerId();
                if (empty($ownerId) || ($ownerId == _X_PLAYER_NO_PLAYER_ID)) {
                    $type = 'noallegiance';
                } else {
                    $oObjectPlayer = $this->man('player')->findById($ownerId);
                    $continue = true;
                }
            } else {
                debug::warning($oObject, "El Objeto oObject no tiene definido algun metodo para acceder al player");
            }

            if ($continue) {
                if(!$oObjectPlayer->isEmpty()){
                    if ($oObjectPlayer->getId() == $oActualPlayer->getId()) {
                        $type = 'myself';
                    } elseif ($oObjectPlayer->isArtificial()) {
                        $type = 'AI';
                    } else {
                        $type = 'enemy';
                    }
                }else{
                    $type = 'inactive';
                }

            }
            return $type;
        }
    }

    /**
     * 
     * updatePlayerBattleStatusDt : Actualiza el estado de todos los jugadores que no tienen aun un battle_players en la batalla
     *
     * @param integer regionId Id de la region en la que se buscara si hay armies nuevos
     *
     * @return  datatransfer 
     *
     * @since   20/20/2013
     * @author  Jeeba
     *
     * @edit    20/01/2013<br />
     *          Jeeba<br />
     *          Creacion y Documentacion de la clase <br/>
     *          #edit1
     *          *********<br/>
     *         
     */
    public function updatePlayerBattleStatusDt($regionId) {
        $arrivedPlayers = new players();
        $arrivedPlayers->initThoseNotPreparedForTacticalBattle($regionId);
        //Es posible que los arrivedPlayers No existan si se da el caso de ninguna unidad
        //haya podido ingresar al planeta, en ese caso se mandan de reversa a las unidades.
        //Si sucede esto entonces no existe ningun player arrivado
        $dt = new datatransfer();
        $dt->success("upadtePlayerBattleStatusDt va viento en popa");
        if ($arrivedPlayers->exist()) {
            $oBattle = $this->man("battle")->findByRegionId($regionId);
            $dt = $arrivedPlayers->prepareForBattle($oBattle);
        }
        return $dt;
    }

    /**
     * 
     * updateRegionMovementDt : Actualiza los armies, teams y players que llegan a una region
     * y ademas envia en el DT si es necesario refrescar o no el cliente. En Javascript esto es
     * forzando a dibujar los armies de nuevo. WT = With Refresh
     *
     * @param integer regionId Id de la region en la que se buscara si hay armies nuevos
     *
     * @return  datatransfer Contiene en su data si es necesario refrescar la vista o no
     * @todo    Check to make sure the username isn't already taken
     *
     * @since   30/12/2012
     * @author  Jeeba
     *
     * @edit    30/12/2012<br />
     *          Jeeba<br />
     *          Creacion y Documentacion de la clase <br/>
     *          #edit1
     *          Cambio de nombre de updateRegionsMovementDtWr a updateRegionsMovementDt<br/>
     *          #edit2* 
     */
    public function updateRegionMovementDt($regionId) {
        $refresh = false;

        $dt = new datatransfer();
        $dt->success('battleutil->updateRegionMovement temporal');
        $arrived_armies = new armies();
        $arrived_armies->initArrivedToRegion($regionId);
        if ($arrived_armies->exist()) {
            $oRegion = $this->man("region")->findById($regionId);
            $oBattle = $this->man("battle")->findByRegionId($regionId);
            $oPlanet = $this->man("planet")->findById($oRegion->getPlanetId());

            foreach ($arrived_armies as $key => $army) {
                $oTeam = $this->man("team")->findById($army->getParam('team_id'));
                //debug::log($oTeam,'planetState->updatePlanetArmiesTravel team se actualizara a region['.$regionId.']');
                if ($oTeam->getRegionId() != $regionId) {
                    $oTeam->setRegionId($regionId);

                    //Ejecutamos el work como debe de ser, le pasamo la data, le indicamos el origen y ejecutamos
                    $work = new regionowningwork();
                    $dataArray = array(_X_VAR_REGION_ID => $regionId, _X_VAR_PLAYER_ID => $this->getCurrentPlayer()->getId());
                    $work->initParamsFromArray($dataArray);
                    $work->setOrigin(_X_WORK_ORIGIN_TEAMMOVEMENT);
                    $work->doIt();
                    $dt = $work->getDt();
                }
            }
            //1) Players que no estan preparados para la batalla
            if ($dt->ok()) {
                $dt = $arrived_armies->prepareForRegionBattleDt($oRegion, $oBattle, $oPlanet);
            }
            //Players que estan en la region pero no tienen estado de batalla 
        }
        return $dt;
    }

    //////////////////////////////////////////////////////////////
    ////  BOOLEANOS
    //////////////////////////////////////////////////////////////

    /*     * ***********************************************************************************
     * canPlayerIdCreateTeamInRegionId: Pregunta si player puede crear un team en la region
     * *********************************************************************************** */
    public function canPlayerIdCreateTeamInRegionId($playerId, $regionId) {
        $oPlayer = $this->man("player")->findById($playerId);
        $teams = new teams();
        $teams->initByRegionIdByPlayerId($regionId, $playerId);
        if ($teams->size() >= $oPlayer->maxTeamsInRegionId($regionId)) {
            return false;
        } else {
            return true;
        }
    }

    /*
     * colonyExistInRegionIdByPlayerId: Si existe una colonia en la region y con el jugador
     */

    public function colonyExistInRegionIdByPlayerId($regionId, $playerId) {
        $colonies = new colonies();
        $colonies->initActiveByRegionId($regionId);
        if ($colonies->exist()) {

            if ($colonies->findByParam($playerId, 'owner_id')) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}

?>