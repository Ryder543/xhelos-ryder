<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/* * *****************************************************
 * armies: Clase para el manejo de varios armies 
 * **************************************************** */

class regionsbiomes extends identities { //[TODO]Deberiamos de instanciarlo de una interface objeto 
    //[TODO]El Refresh se esta llamando muchas veces, deberiamos tener una funcion
    //setObjectParam('position','P') por ejemplo que actualize la accion
    //////////////////////////////////////////////////////////////////////////////////
    //Variables
    ///////////////////////////////////////////////////////////////////////////////////
    //Variables Internas
//////////////////////////////////////////////////////////////////////////////////
//Metodos PRINCIPALES
//////////////////////////////////////////////////////////////////////////////////	
//////////////////////////////////////////////////////////////////////////////////
//Metodos GETTERS
//////////////////////////////////////////////////////////////////////////////////
    /*     * *******************************************************************************
     * stars: constructor
     * ******************************************************************************* */

    public function __construct() {
        $args = func_get_args();
        if (!empty($args)) {
            debug::error('regionsbiomes se inicio con argumentos', 'regionsbiomes->constructor');
        }
        parent::__construct('regionbiomeman'); //CARAJOOOO
    }

//////////////////////////////////////////////////////////////////////////////////
//Metodos INICIALIZADORES
//////////////////////////////////////////////////////////////////////////////////
    public function initAll() {
        $sql = "select * from regions_biomes";
        $resources = $this->db->vectorize($sql);
        $this->setRaw($resources);
        return $this->getRaw();
    }

    public function initAllActive() {
        $sql = "select * from regions_biomes WHERE status = '" . _X_ACTIVE . "'";
        $resources = $this->db->vectorize($sql);
        $this->setRaw($resources);
        return $this->getRaw();
    }
    
    public function initActiveByRegion($regionId) {
        $sql = "select * from regions_biomes WHERE status = '" . _X_ACTIVE . "' AND region_id = ".$regionId;
        $resources = $this->db->vectorize($sql);
        $this->setRaw($resources);
        return $this->getRaw();
    }   

    public function prepareRaw() {
        $preparedRaw = $this->raw;
        /* foreach ($preparedRaw as $key => $value) {          
          unset($preparedRaw[$key]['map']);
          } */
        return $preparedRaw;
    }

//////////////////////////////////////////////////////////////////////////////////
//Metodos BOOLEANOS
//////////////////////////////////////////////////////////////////////////////////
}

?>