<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
// debug::trace('PLAYERS');
//$ret = new identities('Pollo');

/* * ******************************************************
 * player: Clase para el manejo del jugador, esta en forma lazy 
 * **************************************************** */

class players extends identities {

    //////////////////////////////////////////////////////////////////////////////////
    //Variables
    ///////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////
    //Metodos
    //////////////////////////////////////////////////////////////////////////////////
    /*     * *******************************************************************************
     * army: constructor
     * ******************************************************************************* */
    public function __construct() { //We call like this the constructor because it interfere with his parent
        $args = func_get_args();
        if (!empty($args)) {
            debug::error('planets se inicio con argumentos', 'planet->constructor');
        }
        parent::__construct('playerman');
    }

    //////////////////////////////////////////////////////////////////////////////////
    //Inicializadores
    //////////////////////////////////////////////////////////////////////////////////   

    /*     * *******************************************************************************
     * initByType: Obtiene todos los jugadores que son del tipo elegido
     * ******************************************************************************* */
    public function initByType($type) {
        $sql = "SELECT * FROM players AS pl WHERE pl.type = '" . $type . "'";
        $players_raw = $this->db->vectorize($sql);
        if (empty($players_raw)) {
            debug::error('No se pudo obtener las unidades artificales');
        } else {
            $this->raw = $players_raw;
        }
        return $this->raw;
    }

    /*     * ********************************************************************
     * getPlayersInPlanet: Obtiene los jugadores activos en el planeta
     * ******************************************************************** */

    public function initByPlanet($planet_id) {
        $players_raw = array();
        $sql = "SELECT DISTINCT p.type,p.id,p.username,a.region_id FROM armies AS a"
                . " INNER JOIN players AS p ON (a.player_id = p.id)"
                . " WHERE a.region_id IN(SELECT id FROM regions WHERE regions.planet_id =" . $planet_id . ")"
                . " AND p.state = '" . _X_PLAYER_STATUS_ACTIVE . "' ORDER BY p.type DESC";
        $players_raw = $this->db->vectorize($sql);
        if (count($players_raw) == 0) {
            $players_raw[0] = 'Este Planeta no tiene jugadores activos';
        }
        $this->raw = $players_raw;
        return $this->raw;
    }

    //[FINDED] CONCHA DE TUS MADRES
    public function initByRegionByAliveArmies($region_id) {
        $sql = "SELECT DISTINCT p.*,a.region_id FROM armies AS a"
                . " INNER JOIN players AS p ON (a.player_id = p.id) WHERE
           a.region_id =" . $region_id . " AND a.state != '" . _X_ARMY_STATE_DEAD . "' AND p.state = '" . _X_PLAYER_STATUS_ACTIVE . "' ORDER BY p.type DESC";

        $players_raw = $this->db->vectorize($sql);
        if (count($players_raw) == 0) {
            $players_raw = false;
        }
        $this->raw = $players_raw;
        return $this->raw;
    }

    public function initWithArmiesInRegion($region_id) {
        $sql = "SELECT DISTINCT p.*,a.region_id FROM armies AS a"
                . " INNER JOIN players AS p ON (a.player_id = p.id) WHERE a.region_id =" . $region_id . " AND p.state = '" . _X_PLAYER_STATUS_ACTIVE . "' ORDER BY p.type DESC";

        $players_raw = $this->db->vectorize($sql);
        //return $players_raw;//Players Vacio
        //$players_raw[0]='Este Planeta no tiene jugadores activos';
        $this->raw = $players_raw;
        return $this->raw;
    }

    public function initByLeastArmies($type) {
        $sql = "SELECT count(armies.player_id) AS count,players.id FROM players LEFT JOIN armies ON (players.id = armies.player_id)
      GROUP BY (armies.player_id),players.id,players.type,players.state
      HAVING players.type = '" . $type . "' AND players.state = '" . _X_PLAYER_STATUS_ACTIVE . "' ORDER BY count(armies.player_id) ASC";

        $players_raw = $this->db->vectorize($sql);
        if (count($players_raw) == 0) {
            //
        }
        $this->raw = $players_raw;
        return $this->raw;
    }

    public function initThoseNotPreparedForTacticalBattle($region_id) {
        $players_raw = array();
        $sql = "SELECT * FROM players AS p  WHERE id IN (SELECT player_id FROM armies WHERE region_id = " . $region_id . ") AND id NOT IN (SELECT player_id FROM battles_players AS bp WHERE region_id = " . $region_id . ")";
        //debug::warning($sql,'players->initByArmiesArrivingToRegion');
        $players_raw = $this->db->vectorize($sql);
        //debug::warning($sql,'players->initByArmiesArrivingToRegion');
        $this->raw = $players_raw;
        return $this->raw;
    }

    /*     * *****************************************************************************************************
     * initByArmiesArrivingToRegion: Obtiene los jugadores cuyas unidades han llegado a la region_id
     * **************************************************************************************************** */

    public function initByArmiesArrivingToRegion($region_id) {
        $players_raw = array();
        $sql = " SELECT DISTINCT p.*,travel.state,travel.to_region FROM players as p
        INNER JOIN armies ON (armies.player_id = p.id)
        INNER JOIN travel ON (armies.id = travel.armies_id)
        WHERE travel.state = '" . _X_TRAVEL_STATE_ARRIVED . "' 
        AND travel.to_region = " . $region_id;
        //debug::warning($sql,'players->initByArmiesArrivingToRegion');
        $players_raw = $this->db->vectorize($sql);
        //debug::warning($sql,'players->initByArmiesArrivingToRegion');
        $this->raw = $players_raw;
        return $this->raw;
    }

    /**********************************************************************
     * getPlayersByPlanetRegions: Obtiene los jugadores activos en el planeta
     * dividido entre todas las regiones del planeta
     * ******************************************************************** */

    //Usado por planetview.class , cambie el vectorizado 
    public function initByPlanetRegions($planet_id) {
        $players_raw = array();
        //Todos los armies que esten en regiones
        $sql1 = "SELECT DISTINCT p.type,p.id as player_id,p.id as id,p.username,a.region_id FROM armies AS a"
                . " INNER JOIN players AS p ON (a.player_id = p.id)"
                . " WHERE a.region_id IN(SELECT id FROM regions WHERE regions.planet_id =" . $planet_id . ")"
                . " AND p.state = '" . _X_PLAYER_STATUS_ACTIVE . "'";
        
        //Todos los armies que esten viajando en el planeta        
        $sql2="SELECT DISTINCT  p.type,p.id as player_id,p.id as id,p.username,a.region_id " 
        ."FROM armies AS a INNER JOIN players AS p ON (a.player_id = p.id)"
        ."INNER JOIN travel as t ON (t.armies_id = a.id)"
        ."WHERE t.to_planet = " . $planet_id . " AND p.state = '" . _X_PLAYER_STATUS_ACTIVE . "'";
        
        //Union de estos dos sqls
        $sql = $sql1." UNION ".$sql2." ORDER BY type DESC";
        
        //debug::log($sql);
        $players_raw = $this->db->vectorize($sql);
        if (count($players_raw) == 0) {
            $players_raw[0] = 'Este Planeta no tiene jugadores activos';
        }
        $this->raw = $players_raw;
        return $this->raw;
    }

    /*     * ********************************************************************
     * initByAll: Inicia todos los jugadores!
     * ******************************************************************** */

    public function initAll() {
        $sql = "SELECT *FROM  players";
        $players_raw = $this->db->vectorize($sql);
        $this->raw = $players_raw;
        return $this->raw;
    }

    /*     * ********************************************************************
     * initByAll: Inicia todos los jugadores!
     * ******************************************************************** */

    public function initAllHuman() {
        $sql = "SELECT *FROM  players WHERE type = '" . _X_PLAYER_TYPE_HUMAN . "'";
        $players_raw = $this->db->vectorize($sql);
        $this->raw = $players_raw;
        return $this->raw;
    }

    /*     * ********************************************************************
     * initByStar: Obtiene los jugadores activos en la estrella
     * ******************************************************************** */

    public function initByStar($star_id) {
        $sql = "SELECT DISTINCT p.type,p.username,p.id FROM armies a INNER JOIN players p ON (a.player_id=p.id) WHERE a.region_id IN (SELECT id FROM regions WHERE regions.planet_id IN (SELECT id FROM planets WHERE planets.star_id =" . $star_id . " AND planets.state ='" . _X_PLAYER_STATUS_ACTIVE . "') ORDER BY p.type DESC)";
        $players_raw = $this->db->vectorize($sql);
        if (count($players_raw) == 0) {
            $players_raw[0] = 'Esta estrella no tiene jugadores activos';
        }
        $this->raw = $players_raw;
        return $this->raw;
    }

    /*     * ********************************************************************
     * prepareForBattle($region_id): Prepara a los jugadores para la batalla
     * ******************************************************************** */

    public function prepareForBattle($battle) {
        $dt = new datatransfer();
        $dt->success("players->prepareForBattle Temporal");
        foreach ($this AS $key => $player) {
            $player->prepareForBattle($battle);
        }
        return $dt;
    }

    /*     * ********************************************************************
     * startTacticalPhase($region_id): Inicia la fase tactica de todos
     * los jugadores que se encuentren en esta region
     * ******************************************************************** */

    public function startTacticalPhase($region_id, $battle_id) {
        foreach ($this AS $key => $player) {
            $player->startTacticalPhase($region_id, $battle_id);
        }
    }

    public function startBattlePhase($region_id, $battle_id) {
        foreach ($this AS $key => $player) {
            $player->startBattlePhase($region_id, $battle_id);
        }
    }

    /*     * ********************************************************************
     * endBattlePhase($region_id): Inicia la fase tactica de todos
     * los jugadores que se encuentren en esta region
     * ******************************************************************** */

    public function endBattlePhase($region_id, $battle_id) {
        foreach ($this AS $key => $player) {
            $player->endBattlePhase($region_id, $battle_id);
        }
    }

    public function resetManaDt() {
        $temp = $this->getIds();
        $sql = "UPDATE players SET actual_energy = max_energy WHERE players.id IN (" . $temp . ")";
        $dt = $this->db->updateDt($sql);
        if (!$dt->isValid()) {
            debug::log($dt, 'players->resetMana, No se pudo resetear el mana');
        }
        $this->setDataDirty();
        return $dt;
    }

    /*     * ********************************************************************
     * areEnemies(): Retorna true si hay jugadores enemigos en esta lista
     * ******************************************************************** */

    public function areEnemies() {
        if ($this->exist()) {
            $raw = $this->getRaw();
            if (count($raw) > 1) {//Hay mas de un jugador por lo q son enemigos
                return true;
            } else {
                return false;
            }
        } else {
            debug::warning('No existen players para verificar si son enemigos', 'players.class startTacticalPhase 151');
            return false;
        }
    }

    public function areInBattlePhase($region_id, $battle_id) {
        if ($this->exist()) {
            $raw = $this->getRaw();

            //Obtenenemos todos los jugadores con el id y la batalla
            $sql = "SELECT * FROM battles_players WHERE region_id = " . $region_id . " AND battle_id = " . $battle_id . " AND phase ='" . _X_BATTLE_PLAYER_PHASE_BATTLE . "'";
            $battlesplayers = $this->db->vectorize($sql, 'player_id');
//        debug::console($sql,'players areInBattlePhase');
//        debug::console($raw,'players areInBattlePhase');
//        debug::console($battlesplayers,'players areInBattlePhase');
//        debug::console(count($raw)." ".count($battlesplayers),'players areInBattlePhase');
            if (count($raw) == count($battlesplayers)) {
                return true;
            } else {
                return false;
            }
        } else {
            debug::warning('No existen players para verificar si entraron todos a modo batalla', 'players.class startTacticalPhase 151');
            return false;
        }
    }

    public function prepareRaw() {
        $battleutil = new battleutil();
        $preparedRaw = $this->raw;
        foreach ($preparedRaw as $key => $value) {
            unset($preparedRaw[$key]['password']);
            unset($preparedRaw[$key]['mail']);
            unset($preparedRaw[$key]['last_update']);
            $preparedRaw[$key]['allegiance'] = $battleutil->getAllegiance($preparedRaw[$key]['id'],'player');
        }
        return $preparedRaw;
    }

}

?>