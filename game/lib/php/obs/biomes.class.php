<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad 
/* * ******************************************************
 * player: Clase para el manejo del jugador, esta en forma lazy 
 * **************************************************** */

class biomes extends identities {

    //////////////////////////////////////////////////////////////////////////////////	
    //Variables
    ///////////////////////////////////////////////////////////////////////////////////
    //Variables Internas
    //private $raw;
    //private $db;
    //////////////////////////////////////////////////////////////////////////////////
    //Metodos
    //////////////////////////////////////////////////////////////////////////////////
    /*     * *******************************************************************************
     * army: constructor
     * ******************************************************************************* */
    public function __construct() {
        $args = func_get_args();
        if (!empty($args)) {
            debug::error('colonies se inicio con argumentos', 'colonies->constructor');
        }
        $this->db = db::singleton();
        parent::__construct('biomeman'); //CARAJOOOO
    }

    /*     * *******************************************************************************
     * initAll Inicia todas las batallas
     * ******************************************************************************* */

    public function initAll() {
        $sql = "SELECT * FROM biomes";
        $biomes_raw = $this->db->vectorize($sql);
        if (empty($biomes_raw)) {
            $biomes_raw = $this->dataForEmptyArray();
        }
        $this->raw = $biomes_raw;
        return $this->raw;
    }

    /*     * *******************************************************************************
     * initPrebattleByPlanet
     * ******************************************************************************* */

    public function initByTier($tier) {//Prebattle e Init
        $sql = "SELECT * FROM biomes WHERE tier = " . $tier;
        $biomes_raw = $this->db->vectorize($sql);
        if (empty($biomes_raw)) {
            $biomes_raw = $this->dataForEmptyArray();
        } else {
            $this->raw = $biomes_raw;
        }

        return $this->raw;
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