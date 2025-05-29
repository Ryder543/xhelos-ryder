<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/* * *****************************************************
 * army: Clase para el manejo de armies 
 * **************************************************** */

class army extends identity { //[TODO]Deberiamos de instanciarlo de una interface objeto {
    //[TODO]El refreshData se esta llamando muchas veces, deberiamos tener una funcion
    //setObjectParam('position','P') por ejemplo que actualize la accion
    //////////////////////////////////////////////////////////////////////////////////
    //Variables
    ///////////////////////////////////////////////////////////////////////////////////
    //Variables Internas

    private $type;

//////////////////////////////////////////////////////////////////////////////////
//Metodos GETTERS
//////////////////////////////////////////////////////////////////////////////////
    /*     * *******************************************************************************
     * army: constructor
     * ******************************************************************************* */
    public function __construct() {//No se reciben datos debido a todas las posibles formas de inicializacion del objeto
        parent::__construct();
    }

    public function postRefresh() {
        //Do nothing
    }

    public function getCoord() {
        $coord = array();
        $coord['position_x'] = $this->getParam('position_x');
        $coord['position_y'] = $this->getParam('position_y');
        $coord['position'] = $this->getParam('position');
        //$coord['next_x'] = $this->getParam('next_x');
        //$coord['next_y'] = $this->getParam('next_y');
        $coord['next_position'] = $this->getParam('next_position');
        return $coord;
    }

    public function getActualPosition() {
        $position = $this->getParam('position');
        return $position;
    }

    public function getActualEnergy() {
        $actualEnergy = $this->getParam('actual_energy');
        return $actualEnergy;
    }

    public function getNextPosition() {
        $position = $this->getParam('next_position');
        return $position;
    }

    public function getName() {
        $data = $this->getParam('name');
        return $data;
    }

    public function getMovementPoints() {
        $data = $this->getParam('movement');
        return $data;
    }

    /*     * *******************************************************************************
     * getState: Obtiene el Estado de la unidad
     * ******************************************************************************* */

    public function getState() {
        return $this->getParam('state');
    }

    /*     * *******************************************************************************
     * getPlayerId: Obtiene informacion de player
     * ******************************************************************************* */

    public function getPlayerId() {
        return (int) $this->getParam('player_id');
    }

    /*     * *******************************************************************************
     * getRegion: Obtiene ID del Region
     * ******************************************************************************* */

    public function getRegionId() {
        return (int) $this->getParam('region_id'); //[TODO] NO MAGIC CONSTANT
    }

    /*     * *******************************************************************************
     * getRegion: Obtiene ID del Region
     * ******************************************************************************* */

    public function getRegionState() {
        return (int) $this->getParam('region_state'); //[TODO] NO MAGIC CONSTANT
    }

    /*     * *******************************************************************************
     * getArmor: Se Obtiene la Armadura del Army
     * ******************************************************************************* */

    public function getArmor() {
        return $this->getParam('armor'); //[TODO] NO MAGIC CONSTANT
    }

    /*     * *******************************************************************************
     * getSize: Se Obtiene el numero de undidades del Army
     * ******************************************************************************* */

    public function getSize() {
        return $this->getParam('size'); //[TODO] NO MAGIC CONSTANT
    }

    /*     * *******************************************************************************
     * getMaxSize: Se Obtiene el numero total (inicial) que deberia de tener el army actual
     * ******************************************************************************* */

    public function getMaxSize() {
        return $this->getParam('max_size'); //[TODO] NO MAGIC CONSTANT
    }

    /*     * *******************************************************************************
     * getMaxLife: Obtienes el maximo total de vida de este army que puede tener, ojo no 
     * es su vida actual
     * ******************************************************************************* */

    public function getMaxLife() {
        return $this->getParam('max_size') * $this->getParam('life'); //[TODO] NO MAGIC CONSTANT
    }

    /*     * *******************************************************************************
     * getMaxLife: Obtienes el maximo total de vida de este army que puede tener, ojo no 
     * es su vida actual
     * ******************************************************************************* */

    public function getMaxEnergy() {
        return $this->getParam('max_energy');
    }

    /*     * *******************************************************************************
     * getLife: Se Obtiene la Vida del Army
     * ******************************************************************************* */

    public function getLife() {
        return $this->getParam('life'); //[TODO] NO MAGIC CONSTANT
    }

    public function getX() {
        return $this->getParam('position_x'); //[TODO] NO MAGIC CONSTANT
    }

    public function getTeamId() {
        return $this->getParam('team_id');
    }

    public function getY() {
        return $this->getParam('position_y'); //[TODO] NO MAGIC CONSTANT
    }

    public function getPheromone() {
        return $this->getParam('pheromone'); //[TODO] NO MAGIC CONSTANT
    }

    public function getPosition() {
        return $this->getParam('position'); //[TODO] NO MAGIC CONSTANT
    }

    public function getPercentLife() {
        $restante = $this->getResidualLife();
        //debug::log('restate['.$restante.'] = totallife['.$this->getTotalLife().'] % life['.$this->getLife().']');
        $percent = ( $restante * 100 ) / $this->getLife();
        return $percent;
    }

    public function getTotalPercentEnergy() {
        if ($this->getActualEnergy() == 0) {
            $percent = 0;
        } else {
            $percent = ( 100 * $this->getActualEnergy() ) / $this->getMaxEnergy();
        }
        return $percent;
    }

    public function getTotalPercentLife() {
        if ($this->getTotalLife() == 0) {
            $percent = 0; //Esta muerto! Esto impide que restante de abajo mande cero
        } else {
            $totalArmyLife = $this->getMaxSize() * $this->getLife();
            //debug::log($this,'army->getTotalPercentLife['.$totalArmyLife.'] getMaxSize['.$this->getMaxSize().'] * getLife['.$this->getLife().']');
            $percent = (100 * $this->getTotalLife()) / $totalArmyLife;
        }
        return $percent;


        //$restante =  $this->getResidualLife(); 
        //debug::log('restate['.$restante.'] = totallife['.$this->getTotalLife().'] % life['.$this->getLife().']');
        //$percent = ( $restante *100 ) / $this->getLife();
        //return $percent;
    }

    public function getResidualLife() {
        if ($this->getTotalLife() == 0) {
            return 0; //Esta muerto! Esto impide que restante de abajo mande cero
        } else {

            $restante = $this->getTotalLife() % $this->getLife();
            if ($restante == 0) {
                return $this->getLife(); //Si devuelve cero entonces tiene su vida completa
            } else {
                return $restante;
            }
        }
    }

    /*     * *******************************************************************************
     * getAttrition: Se Obtiene el Attrition del Army
     * ******************************************************************************* */

    public function getAttrition() {
        return $this->getParam('attrition'); //[TODO] NO MAGIC CONSTANT
    }

    /*     * *******************************************************************************
     * getAttrition: Se Obtiene el Attrition del Army
     * ******************************************************************************* */

    public function getAttack() {
        return $this->getParam('attack'); //[TODO] NO MAGIC CONSTANT
    }

    public function getAttackQuantity() {
        return $this->getParam('attack_quantity'); //[TODO] NO MAGIC CONSTANT
    }

    /*     * *******************************************************************************
     * getTotalAttackDamage: Se Obtiene el da�o total que puede generar el army, contando con la armadura contraria
     * ******************************************************************************* */

    public function getTotalAttackDamage($enemy_armor) {
        $tot_army_damage = ($this->getAttack() - $enemy_armor) * $this->getSize();
        return $tot_army_damage;
    }

    /*     * *******************************************************************************
     * getTotalLife: Se Obtiene la vida total del army, multiplicando life por size
     * ******************************************************************************* */

    public function getTotalLife() {
        return $this->getParam('total_life'); //[TODO] NO MAGIC CONSTANT
    }

    /*     * *******************************************************************************
     * getTimes: Obtiene la cantidad de veces que puede atacar a una unidad
     * ******************************************************************************* */

    public function getTimes() {
        $times = $this->getSize() * $this->getAttackQuantity();
        return $times;
    }

    public function getTurn() {
        return $this->getParam('turn');
    }

    public function getLastUpdate() {
        $ret = $this->getParam('last_update');
        if (!isset($ret)) {
            return false;
        }
        return $this->getParam('last_update');
    }

    public function getBattleSeconds() {
        //debug::trace($this->getParam('last_update'), 'army['.$this->getId().']->getBattleSeconds->last_update');
        if (!$this->exist('battle_seconds')) {
            //debug::log('army->getBattleSeconds battlesecond no existe');
            $ret = $this->getParam('last_update');
            if (!isset($ret)) {
                //debug::warning('No se seteo battle seconds','army['.$this->getId().']->getBattleSeconds');
                return false;
            } else {
                $sql = "SELECT time_to_sec2(last_update + " . _X_BATTLE_TIME . ",now()) AS battle_seconds FROM armies where id =" . $this->getId();
                //debug::log($sql,"army.class->getBattleSecods");
                $raw = $this->db->fetch($sql);
                $this->setParam('battle_seconds', $raw['battle_seconds']);
                $bt = $this->getParam('battle_seconds');
                //debug::warning($raw,'army['.$this->getId().']->getBattleSeconds raw del sql ');
            }
        } else {
            //debug::log('army->getBattleSeconds battlesecond existe');
            $bt = $this->getParam('battle_seconds');
        }
        //debug::trace($bt, 'army[' . $this->getId() . ']->getBattleSeconds');
        return $bt;
    }

//////////////////////////////////////////////////////////////////////////////////
//METODODS SETTERS  //Tienen que Actualizarse en la BD y en el Objeto 
//IMPORTANTE: Si se va a crear nuevas funciones que actualizan datos 
//debe de llamarse a la funcion $this->setDataDirty(); al final de todo
//////////////////////////////////////////////////////////////////////////////////
    /*     * *******************************************************************************
     * removeEnergyDt: Descarga Mana de las Unidades
     * ******************************************************************************* */
    public function removeEnergyDt($energy) {
        $dt = new datatransfer();
        if ($this->getActualEnergy() < $energy) {
            $error = 'Mana a Remover[' . $energy . '] es mayor del mana[' . $this->getActualEnergy() . '] lo que hay en la unidad[' . $this->getId() . ']';
            debug::error($error);
            $dt->error($error, 'army->removeEnergyDt');
        } else {
            $sql = "UPDATE armies  SET actual_energy = actual_energy-" . $energy . " WHERE id = " . $this->getId();
            $this->db->query($sql);
            $this->setDataDirty();
            $dt->success('Se removio energia[' . $energy . '] de la unidad[' . $this->getId() . ']');
        }
        return $dt;
    }

    public function recoverEnergy() {
        $recoveryAmount = 200; // Por ejemplo, recupera 10 puntos de energía
        $currentEnergy = $this->getActualEnergy();
        $maxEnergy = $this->getParam('max_energy'); // o el método que uses para obtener el máximo
    
        $newEnergy = min($currentEnergy + $recoveryAmount, $maxEnergy);
    
        // Actualizar en base de datos
        $sql = "UPDATE armies SET actual_energy = $newEnergy WHERE id = " . $this->getId();
        $this->db->query($sql);
    
        $this->setDataDirty();
    
        return $newEnergy - $currentEnergy; // Cantidad realmente recuperada
    }
    
    public function setActualEnergy($value) {
        $this->setParam('actual_energy', $value);
    }
    
    

    /*     * ***********************************************************
     * calculateDeath: La Unidad Recibe da�o
     * ************************************************************ */

    public function calculateDeath($damage) {
        
    }

    /*     * ***********************************************************
     * receiveDamage: La Unidad Recibe da�o
     * ************************************************************ */

    public function receiveAttack($damage, $times, $persist = true) {
        //Inicializacion de Variables
        $deads = 0;
        global $firephp;
        //Esto es para al final saber si se imprime o no el ataque
        $oldTotalLife = $this->getTotalLife();
        //Da�o Real que se le aplica a la unidad objetivo
        $realDamage = ($damage - $this->getArmor()) * $times;
        //$firephp->group('Daño al Army['.$this->getId().']');
        //$firephp->log("realDamage[".$realDamage."]");
        //PUM = posibles unidades muertas
        if ($realDamage < 1) {
            //$firephp->log("No se hizo nada de daño[".$realDamage."] por lo que salimos");
            return false;
        }

        $pum = 0;
        if ($this->getSize() == 0) {//Esta muerto por lo que no podemos atacarlo
            //$firephp->log("posibles unidades muertas[".$pum."]=0 porque esta muerto");
        } else {// Se calcula cuantas unidades moriran
            $pum = floor(($realDamage) / ($this->getLife()));
            //$firephp->log("posibles unidades muertas[".$pum."]=realDamage[".$realDamage."]/getLife[".$this->getLife()."]");
        }
        $residualLife = 0;
        $residualSize = 0;
        //$firephp->log(" = posibles unidades muertas[".$pum."] times[".$times."]");
        if ($pum > $times) {
            $residualLife = $this->getTotalLife() - ($times * $this->getLife());
            //$firephp->log("residualLife[".$residualLife."] =totalLife[".$this->getTotalLife()."]-(times[".$times."]*getLife[".$this->getLife()."]");
        } else {
            $residualLife = $this->getTotalLife() - ($realDamage);
            //$firephp->log("residualLife[".$residualLife."] =totalLife[".$this->getTotalLife()."]-(realDamage[".$realDamage."]");
        }
        if ($residualLife <= 0) {
            $residualSize = 0;
            $deads = $this->getSize();
            //$firephp->log("residualSize[".$residualSize."]=0");
        } else {
            $residualSize = ceil($residualLife / $this->getLife());
            //$firephp->log("residualSize[".$residualSize."]=residualLife[".$residualLife."]/getLife[".$this->getLife()."]");
            $deads = $this->getSize() - $residualSize;
        }


        $state = $this->getState();
        if ($residualLife <= 0) {
            $residualLife = 0;
            $residualSize = 0;
            $this->killMe();
            //$firephp->log("Se Murio la Unidad");
        }

        $id = $this->getId();
        $sql = "UPDATE armies SET total_life = " . $residualLife . ",size = " . $residualSize . " WHERE id = " . $id;
        $this->db->query($sql);
        $this->setDataDirty();


        //$firephp->log("residualLife[".$residualLife."]");
        //$firephp->log("-------------");
        //$firephp->groupEnd();
        if ($oldTotalLife <= 0) {
            return false;
        } else {
            return floor($deads);
        }
    }

    /*     * ***********************************************************
     * killMe: Destruye a la Unidad
     * ************************************************************ */

    public function killMe() {
        $sql = "UPDATE armies SET state = '" . _X_ARMY_STATE_DEAD . "', size=0,total_life=0 WHERE id =" . $this->getId();
        $this->db->query($sql);
        $this->setDataDirty();
    }

    /*     * ***********************************************************
     * killArmyUnits: Mata Unidades
     * ************************************************************ */

    public function killUnits($killed) {

        $alive = round($this->getSize() - $killed);
        if ($alive <= 0) {//[TODO][MAGIC] Constante Magica 0
            //[TODO] No se deberia de usar Update aqui si hay otra funcion que actualice el estado y el tama�o
            $sql = "UPDATE armies SET size = $alive,state='D' WHERE armies.id=" . $this->getId();
        } else {
            $sql = "UPDATE armies SET size = $alive WHERE armies.id=" . $this->getId();
        }
        $this->db->update($sql);
        $this->setDataDirty();
    }

    /*     * ******************************************************************
     * setRegionState: Cambia la Posicion del Objetivo
     * ******************************************************************* */

    public function setRegionState($regionState) {
        //O = Orbitando, P = En Region, E = En Espacio, A = Aterrizando, S = Saliendo del Region
        $army_id = $this->getId();
        $sql = "UPDATE armies SET region_state = '" . $regionState . "' WHERE armies.id=" . $army_id;
        $this->db->updateEntityDt($sql,"army",$army_id);
        $this->setDataDirty();
    }

    /*     * ******************************************************************
     * setPosition: Cambia la Posicion del Objetivo
     * ******************************************************************* */

    public function setPosition($position) {
        //O = Orbitando, P = En Region, E = En Espacio, A = Aterrizando, S = Saliendo del Region
        $army_id = $this->getId();
        $sql = "UPDATE armies SET position = '" . $position . "' WHERE armies.id=" . $army_id;
        $this->db->update($sql);
        $this->setDataDirty();
    }

    /*     * ******************************************************************
     * setRegion: Cambia el Region del Objetivo
     * ******************************************************************* */

    public function setRegion($regionId) {
        //debug::trace("army->setRegion");
        $armyId = $this->getId();
        $sql = "UPDATE armies SET region_id = '" . $regionId . "' WHERE armies.id=" . $armyId;
        $this->db->update($sql);
        $this->setDataDirty();
    }

    /*     * ******************************************************************
     * setTeam: Cambia el Equipo
     * ******************************************************************* */

    public function setTeam($teamId) {
        $armyId = $this->getId();
        $sql = "UPDATE armies SET team_id = " . $teamId . " WHERE armies.id=" . $armyId;
        $this->db->updateEntityDt($sql,"army",$armyId);
        $this->setDataDirty();
    }

    /*     * ******************************************************************
     * setPheromone: Actualiza el lugar donde se dejo la pheronoma
     * ******************************************************************* */

    public function setPheromone($index) {
        $armyId = $this->getId();
        $sql = "UPDATE armies SET pheromone = " . $index . " WHERE armies.id=" . $armyId;
        $this->db->update($sql);
        $this->setDataDirty();
    }

    /*     * ******************************************************************
     * setNullRegion: Cambia el estado de la region a null, osea esta de viaje la unidad
     * ******************************************************************* */

    public function setNullRegion() {
        $armyId = $this->getId();
        $sql = "UPDATE armies SET region_id = NULL WHERE armies.id=" . $armyId;
        $this->db->update($sql);
        $this->setDataDirty();
    }

    /*     * ******************************************************************
     * passNextTurn: Pasa al turno siguiente
     * ******************************************************************* */

    public function passNextTurn() {
        //debug::info('Entre a Parando a la unidad','passNextTurn army.class');
        //$last = $this->getBattleSeconds();
        //if($last<=0){//[WHY] Esto no puede estar aqui, este no decide
        //debug::trace('ArmyId:' . $this->getId(), 'army->passNextTurn');
        $armyId = $this->getId();
        $turn = $this->getTurn(); //[OJO][WARNING]Mejor ponerle el turno desde afuera, posible bug
        $sql = "UPDATE armies SET turn = " . ($turn + 1) . ",last_update = NULL WHERE armies.id=" . $armyId;
        $this->db->update($sql);
        $this->setDataDirty();
        return true;
    }

    /*     * ******************************************************************
     * setPosition: Cambia la Posicion del Objetivo
     * ******************************************************************* */

    public function setNextPosition($nextPosition) {
        //O = Orbitando, P = En Region, E = En Espacio, A = Aterrizando, S = Saliendo del Region
        $army_id = $this->getId();
        $sql = "UPDATE armies SET next_position = '" . $nextPosition . "' WHERE armies.id=" . $army_id;
        $this->db->update($sql);
        $this->setDataDirty();
    }

    /*     * ******************************************************************
     * setCoord: Cambia las Coordenadas del Objetivo
     * ******************************************************************* */

    public function setCoord($x_out, $y_out) {
        $army_id = $this->getId();
        //[TODO] Aumentar Validaciones
        $sql = "UPDATE armies SET position_x=$x_out,position_y=$y_out WHERE id = $army_id;";
        $this->db->update($sql);
        $this->setDataDirty();
    }

    /*     * ***************************************************************************************************
     * setCoordAndFutureCoord: Cambia las Coordenadas y los movimientos siguientes del ejercito objetivo
     * **************************************************************************************************** */

    public function setActualCoord($x, $y) {
        //[TODO] Aumentar Validaciones
        $army_id = $this->getId();
        $region_id = $this->getParam('region_id');
        $sql = "UPDATE armies SET position_x=" . $x . ",position_y=" . $y . " WHERE id = " . $army_id;
        $this->db->update($sql);
        $this->setDataDirty();
    }

    /*     * ***************************************************************************************************
     * setActualCoordAndRegionState: Cambia las Coordenadas y el estado de viaje a region, y la region en nulo
     * **************************************************************************************************** */

    /*public function setActualCoordAndRegionStateAndNullRegion($x, $y, $regionState) {
        //[TODO] Aumentar Validaciones
        $army_id = $this->getId();
        $region_id = $this->getParam('region_id');
        $sql = "UPDATE armies SET position_x=" . $x . ",position_y=" . $y . ",region_state=" . $regionState . ",region_id = NULL WHERE id = " . $army_id;
        $this->db->update($sql);
        $this->setDataDirty();
    }*/

    /*     * *********************************************************************************
     * startLastUpdate: SE inicializa Last Update
     * ******************************************************************++++++++++++++* */

    public function startLastUpdate() {
        if ($this->exist()) {
            if (!$this->getLastUpdate()) {
                //debug::info('Se actualizo army['.$this->getId().'] lastUpdate','startLastUpdate army.class 493');
                $sql = "UPDATE armies SET last_update = now() WHERE armies.id = " . $this->getId();
                //debug::info($sql, 'army->startLastUpdate');
                $this->db->update($sql);
                $this->setDataDirty();
                $upd = $this->getLastUpdate();
                //debug::info($upd, 'army->startLastUpdate');
            } else {
                //debug::info('NNOO Se actualizo army['.$this->getId().'] lastUpdate','startLastUpdate army.class 443');
                //Ya tiene un dato no necesita actualizarse
            }
        } else {
            debug::trace('Army no fue inicializado nunca', 'startLastUpdate army.class 443');
        }
    }

    /*     * *********************************************************************************
     * sendFromRegionToRegion: Envia a las unidades que esten en el planeta a la region
     * ******************************************************************++++++++++++++* */

    public function sendFromRegionToRegion($origin_region, $target_region, $target_planet_id) {
        //debug::trace('army->sendFromRegionToRegion');
        //debug::console('origin:'.$origin_region.' target:'.$target_region.' region['.$this->getRegionId().']unidad['.$this->getId().']');
        if ($target_region != $this->getRegionId()) {
            $armyId = $this->getId();
            if ($this->isCapableOfTravel()) {
                /* Cambiamos sus Movimientos anteriores a I,esto por seguridad[TODO][PERFORMANCE] mejor que lo haga armies */
                $sql = "UPDATE travel SET state = '" . _X_TRAVEL_STATE_INACTIVE . "' WHERE armies_id = " . $armyId;
                $this->db->secureUpdate($sql); //[Jeeba 12 Febrero 2013] Actualizar el travel no actualiza los datos de army, por lo tanto no los ensucia

                $travel_id = $this->db->nextId('travel', 'id');
                $sql = "INSERT INTO travel(id,armies_id,eta,from_region,to_region,state,to_planet)
        VALUES (" . $travel_id . "," . $armyId . ",now() + interval '" . _X_TRAVEL_TICK . "'," . $origin_region . "," . $target_region . ",'" . _X_TRAVEL_STATE_ACTIVE . "'," . $target_planet_id . ")";
                $this->db->insert($sql);
                $this->leaveRegion();
                // debug::log($this->getRaw(),'army->sendFromRegionToRegion');
                return true;
                //$time = $this->movman->getTimesFromActionAndArmy(_X_ACTION_TRAVEL,$armyId);
                //$this->movman->insertMovement($regionId,$armyId,_X_POSITION_COORD_NULL,_X_POSITION_COORD_NULL,_X_POSITION_COORD_NULL,_X_POSITION_COORD_NULL,$time['time_in'],$time['time_out'],_X_ACTION_TRAVEL);
            } else {
                debug::console('No pasa nada con capable', 'army.class sendFromRegionToRegion 417');
                //Para enviar la unidad a la region debe de estar listo para el movimiento
                return false;
            }
        } else {
            debug::error('Estas tratando de enviar al army[' . $this->getId() . '] a la misma region de origen', 'army.class sendFromRegionToRegion 417');
            return false;
        }
    }

    /*     * *********************************************************************************
     * getTravelDt: Obtenemos el TravelDT
     * ******************************************************************************** */

    public function getTravelDt($region_id) {
        $army_id = $this->getId();
        $sql = "SELECT * FROM travel WHERE state = '" . _X_TRAVEL_STATE_ACTIVE . "' AND travel.armies_id =" . $army_id . " AND travel.to_region = " . $region_id;
        //debug::log($sql);
        $dt = $this->db->fetchDt($sql);
        //$last_travel = $dt->getData();
        return $dt;
    }

    public function prepareForBattle($region_id, $turn, $phase) {
        /* Aqui es donde se setea la region a la que se llega, quizas el nombre esta medio mal, no es prepareForBattle, es comeRegion */
        // debug::console('Army['.$this->getId().']Preparandose para la batalla Fase['.$phase.'] Turno['.$turn.']','prepareForBattle army.class');
        $army_id = $this->getId();
        //Actualizar Travel [TODO][PERFORMANCE] Mejor lo borramos, en fin :P
        $sql = "UPDATE travel SET state = '" . _X_TRAVEL_STATE_ARRIVED . "' WHERE travel.armies_id =" . $army_id . " AND travel.to_region = " . $region_id . " AND travel.state ='" . _X_TRAVEL_STATE_ACTIVE . "'";
        //debug::console($sql);
        $this->db->secureUpdate($sql);//Poruqe estamos actualizando el travel
        if ($phase == _X_BATTLE_PHASE_TACTIC) {
            //La unidad esta entrando en una batalla tactica
            $sql = "UPDATE armies SET turn = " . _X_ARMY_TURN_INITIAL . ",last_update = NULL,battle_state = '" . _X_ARMY_BATTLESTATE_TACTIC . "'  WHERE armies.id =" . $army_id;
            // debug::warning($sql,'army.class prepareForBattle');
        } elseif ($phase == _X_BATTLE_PHASE_BATTLE) {
            //La unidad esta entrando en una batalla ya existente
            $sql = "UPDATE armies SET turn = " . $turn . ",last_update = NULL,battle_state='" . _X_ARMY_BATTLESTATE_BATTLE . "'  WHERE armies.id =" . $army_id;
            //debug::warning($sql,'army.class prepareForBattle 595');
        } else {
            // La batalla es inexistente
            $sql = "UPDATE armies SET turn = " . _X_ARMY_TURN_INITIAL . ",last_update = NULL,battle_state ='" . _X_ARMY_BATTLESTATE_END . "'  WHERE armies.id =" . $army_id;
            //debug::warning('No existe una batalla en fase['.$phase.'] creada, o no hay batalla, o recien se va a crear despues de esto, turno del army['.$army_id.'] ='._X_ARMY_TURN_INITIAL,'army.class prepareForBattle 454');
        }
        $this->db->secureUpdate($sql);
        $this->setDataDirty();//Never ever borrar esta linea
        //debug::info($this,'AFTER SETTING como esta dirty? army->prepareForBattle');
        return true;
    }

    /*     * *********************************************************************************
     * cancelActualMovements: Devuelve true o false dependiendo si la posicion esta vacia o no, exluyendo unidad
     * ******************************************************************++++++++++++++* */

    public function cancelActualMovements() {
        $army_id = $this->getId();
        $region_id = $this->getParam('region_id');
        $this->movman->deleteActualMovementFromArmy($region_id, $army_id);
        $this->setNextCoordLikeActualCoord();
    }

    /*     * *********************************************************************************
     * cancelNewMovements: Cancelamos Movimientos Nuevos de la Unidad Objetivo
     * ******************************************************************++++++++++++++* */

    public function cancelNewMovements() {
        $army_id = $this->getId();
        $region_id = $this->getParam('region_id');
        $this->movman->deleteNewMovementFromArmy($region_id, $army_id);
        $this->setNextCoordLikeActualCoord();
    }

    /*     * *******************************************************************************
     * armyDefend: Valida el mana descargado
     * ******************************************************************************* */

    public function defend($action_id_to_defend, $time_in, $time_out) {
        $region_id = $this->getParam('region_id');
        $x = $this->getParam('position_x');
        $y = $this->getParam('position_y');
        //Elimina
        $this->movman->insertMovement($region_id, $this->getId(), $x, $y, $x, $y, $time_in, $time_out, _X_ACTION_DEFEND);
    }

    public function enterRegion($regionId, $x, $y) {//[TODO] Verificar que no haya nada raro
   
        $armyId = $this->getId();
        $regionState = _X_REGION_STATE_INSIDE;
        $sql = "UPDATE armies SET position='"._X_POSITION_SUPERFICIE."' ,next_position='"._X_POSITION_SUPERFICIE."',position_x=".$x.",position_y=".$y.",region_state='" . $regionState . "',region_id = ".$regionId." WHERE id = " . $armyId;
        $this->db->updateEntityDt($sql,"army",$armyId);
        //$this->setPosition(_X_POSITION_SUPERFICIE);
        //$this->setNextPosition(_X_POSITION_SUPERFICIE);
        //$this->setRegionState(_X_REGION_STATE_INSIDE);
        //$this->setRegion($regionId);
        //$this->setActualCoord($x, $y);
    }

    public function leaveRegion() {//[TODO] Verificar que no haya nada raro
        //[Jeeba 12feb2013] Cambiamos todo esto a una sola funcion para mejorar la eficiencia
        $x = _X_POSITION_COORD_NULL;$y=_X_POSITION_COORD_NULL;$regionState=_X_REGION_STATE_OUTSIDE;
        $army_id = $this->getId();
        $region_id = $this->getParam('region_id');
        $sql = "UPDATE armies SET position_x=" . $x . ",position_y=" . $y . ",region_state='" . $regionState . "',region_id = NULL WHERE id = " . $army_id;
        $this->db->updateEntityDt($sql,"army",$army_id);
        //$this->setDataDirty();
        
        //$this->setActualCoordAndRegionStateAndNullRegion(_X_POSITION_COORD_NULL, _X_POSITION_COORD_NULL, _X_REGION_STATE_OUTSIDE);
        /* $this->setActualCoord(_X_POSITION_COORD_NULL, _X_POSITION_COORD_NULL);
          $this->setRegionState(_X_REGION_STATE_OUTSIDE);
          $this->setNullRegion(); */
    }

    public function orbit() {//[TODO] Verificar que este no este en O que no tenga acciones realizandose
        //Deberia de Ejecutarse la accion Orbitar
        $this->setPosition(_X_POSITION_ORBIT);
        $this->setNextPosition(_X_POSITION_ORBIT);
        $this->setActualAndNextCoord(_X_POSITION_COORD_NULL, _X_POSITION_COORD_NULL, _X_POSITION_COORD_NULL, _X_POSITION_COORD_NULL);
    }

    /*     * *******************************************************************************
     * countMovement: Cuenta los movimientos activos de las unidades
     * ******************************************************************************* */

    public function countMovements() {
        $dt = new datatransfer();
        $army_id = $this->getParam('id');
        $sql = "SELECT count(*) as count FROM
		movements WHERE movements.army_id=$army_id AND movements.time_out>now()";
        $mov = $this->db->fetch($sql);
        if ($mov['count'] >= _MAXMOV) {
            $dt->invalidWarning('Solo puede tener ' . _MAXMOV . ' movimientos por unidad');
        } else {
            $dt->validSuccess();
        }
        return $dt;
    }

    /*     * **********************************************
     * BOLEANOS (is) DE UNIDAD
     * ********************************************** */

    public function isInPosition($request_position) {
        $unit_id = $this->getId();
        //$region_id = $this->getParam('region_id');
        $position = $this->getParam('position');
        if ($position != $request_position) {
            return false;
        } else {
            return true;
        }
    }

    /*     * *************************************************************************
     * isCapableOfTravel: Si puede viajar por regiones, tiene que estar en inside
     * ************************************************************************* */

    public function isArrivingToRegion($region_id) {
        $dt = $this->getTravelDt($region_id); //Preguntaremos si ha llegado a la region
        if ($dt->ok()) {
            $data = $dt->getData();
            if ($data) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /*     * *************************************************************************
     * isCapableOfTravel: Si puede viajar por regiones, tiene que estar en inside
     * ************************************************************************* */

    public function isCapableOfTravel() {
        $rs = $this->getParam('region_state');
        if ($rs == _X_REGION_STATE_INSIDE) {
            if ($this->isAlive()) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /*     * *************************************************************************
     * isCapableOfBattle: Si ya esta listo para poder batallar
     * ************************************************************************* */

    public function isCapableOfBattle() {
        if ($this->getRegionState() == _X_REGION_STATE_INSIDE) {
            if ($this->getX() != _X_POSITION_COORD_NULL) {
                if ($this->getY() != _X_POSITION_COORD_NULL) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getSpeed() {
        $speed_raw = $this->getParam('speed');
        $speed = str2array($speed_raw);
        return $speed;
    }

    /*     * ****************************************************************************************************
     * isTerrainMovable: Devuelve true o false dependiendo si la unidad tiene la suficiente velocidad
     * para moverse a traves de ese terreno
     * ************************************************************************************************************* */

    public function isTerrainMovable($terrain_id) {
        $terrain_id = $terrain_id - 1; // Como str2array comienza en 0..., y terrain en 1...
        //global $firephp;
        //debug::log($this->raw,'army terrain raw');
        $speed_raw = $this->getParam('speed');
        //debug::log($speed_raw,'army terrain raw');
        $speed = str2array($speed_raw);
        //debug::log($speed,'terrain['.$terrain_id.']');
        //debug::log($speed[$terrain_id],'speed en terrain['.$terrain_id.']');
        if ($speed[$terrain_id] > 0) {
            //debug::log($speed[$terrain_id] . '>0','army->isTerrainMovable');
            return true;
        }
        return false;
    }

    /*     * ************************************************************************
      isAlive*  La Unidad esta viva
     * @return  Boolean : TRUE si esta viva, FALSE si esta muerta
     * @since   2012-Dic-30
     * @author  Jeeba <designbyjeeba@gmail.com>
     * @edit    2012-Dic-30<br />
     *          Jeeba <designbyjeeba@gmail.com>
     *          Documentacion Inicial<br/>
     *          #edit1
     */

    public function isAlive() {

        $state = $this->getParam('state');
        if ($state == _X_ARMY_STATE_ALIVE) {
            return true;
        } else {
            return false;
        }
    }

    public function prepareRaw() {
        $preparedRaw = $this->raw;
        /* foreach ($preparedRaw as $key => $value) {          
          unset($preparedRaw[$key]['map']);
          } */
        return $preparedRaw;
    }

}

?>