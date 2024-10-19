<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/* * *****************************************************
 * region: Clase para el control de una region
 * **************************************************** */

class regionman extends identitymap {

    private static $instance; //Contiene la instancia unica de la base de datos	

    ///////////////////////////////////////////////////////////////////////////////////////
    //CONSTRUCTOR
    ///////////////////////////////////////////////////////////////////////////////////////

    protected function __construct() {
        parent::__construct('regionman', 'region', 'regions');
    }

    public function defaultsql($id) {
        //debug::trace('regionman->defaultsql');
        $sql = "SELECT * FROM regions WHERE regions.id=" . $id . " AND regions.state ='" . _X_ACTIVE . "'";
        return $sql;
    }

    ///////////////////////////////////////////////////////////////////////////////////////
    //METODOS PRINCIPALES
    ///////////////////////////////////////////////////////////////////////////////////////
    //La usaba el admin
    public function findByRandom() {
        //Obten una region randonmente
        $sql = "SELECT id FROM regions WHERE state = '" . _X_REGION_STATUS_ACTIVE . "' ORDER BY RANDOM() LIMIT 1";
        $region_raw = $this->db->fetch($sql);

        if (empty($region_raw)) {
            debug::error('La region no pudo inicializarse randommente', 'region.class.php');
        } else {
            $obj = $this->wrap($region_raw);
            return $obj;
        }
    }

    public function findByLessArmies() {
        //[TODO]  mover a regionman
        //Obten laregion con menos unidades
        $sql = "SELECT * FROM regions WHERE regions.id = (SELECT r.id as id from regions as r 
   LEFT JOIN armies as a ON (a.region_id = r.id) GROUP BY r.id,r.name ORDER BY count(a.id) asc,id asc LIMIT 1)";
        $region_raw = $this->db->fetch($sql);

        if (empty($region_raw)) {
            debug::error('La region con menos armies no pudo inicializarse', 'regionman->findByLessArmies');
        } else {
            $this->initByArray($region_raw);
        }
    }

    //Encotnrar por planeta y posicion
    public function findByPlanetIdByPosition($planetId, $position) {
        $sql = "SELECT * FROM regions WHERE planet_id = " . $planetId . " AND planet_position = " . $position;
        $region_raw = $this->db->fetch($sql);

        if (!empty($region_raw)) {
            //debug::warning('Battle buscado por region['.$region_id.'] existe','findByRegionId battleman.php');
            $this->add($region_raw['id'], $region_raw);
            $obj = $this->wrap($this->raw[$region_raw['id']]);
            //debug::warning($obj,'return de findByRegionId battleman.php');
            return $obj;
        } else {
            debug::warning('La region en el planeta[' . $planetId . '] en la posicion [' . $position . '] no existe', 'regionman->findByPlanetIdByPosition');
            $region = new region();
            return $region;
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////
    //UTILITARIOS
    ///////////////////////////////////////////////////////////////////////////////////////
    public static function singleton() {//Obtiene la instancia unica de esta clase
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }
        return self::$instance;
    }

    public function __clone() { // Prevenimos que este objeto sea clonado
        trigger_error('Clone is not allowed. regionman.class', E_USER_ERROR);
    }

    public function createDt($data) {
        $sql = "INSERT INTO regions(id, name, x, y, map, state, planet_id, desc, owner_id,planet_position,type)"
                . "VALUES (" . $data['id'] . ",'" . $data['name'] . "'," . $data['x'] . "," . $data['y'] . ",'" . $data['map'] .
                "','" . $data['state'] . "'," . $data['planet_id'] . ",'" . $data['desc'] . "'," . $data['player_owner_id'] . "," . $data['planet_position'] . ",'" . $data['type'] . "')";
        //debug::log($sql,'regionman->createDt');
        $dt = $this->db->insertDt($sql);
        //debug::log($dt,'regionman->createDt');
        return $dt;
    }

}

?>