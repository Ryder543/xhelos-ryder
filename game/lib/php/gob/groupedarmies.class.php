<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/* * *****************************************************
 * armies: Clase para el manejo de varios armies 
 * **************************************************** */

class groupedarmies extends gidentities { //[TODO]Deberiamos de instanciarlo de una interface objeto 
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
            debug::error('groupedbiomes se inicio con argumentos', 'groupedcolonies->constructor');
        }
        parent::__construct('colonyman'); //CARAJOOOO
    }

//////////////////////////////////////////////////////////////////////////////////
//Metodos INICIALIZADORES
//////////////////////////////////////////////////////////////////////////////////     
    public function initAliveByPlanetIdOrderByRegionId($planetId){
        $sql = "SELECT * FROM armies WHERE state = '"._X_ARMY_STATE_ALIVE."'AND region_id IN (SELECT id FROM regions WHERE regions.planet_id = " . $planetId . ") ORDER BY id";
        $armies_raw = $this->db->sql2group($sql,'region_id');
        $this->setRaw($armies_raw);
        return $armies_raw;
    }
    

    public function prepareRaw() {
        $preparedRaw = $this->raw;
        $battleutil = new battleutil();
        if (!count($preparedRaw) < 1) {

            foreach ($preparedRaw as $regionId => $armiesInRegion) {//Es correcto el regionId, la funcion de armies->initByPlanetId.... devuelve un arreglo de arreglo
                foreach ($armiesInRegion as $id => $army) {
                    //Añadir nuestras cosillas
                    $battleutil = new battleutil();
                    $preparedRaw[$regionId][$id]['allegiance'] = $battleutil->getAllegiance($army['id'], "army");
                }
            }

            //debug::warning('Las regions del planeta['.$this->getPlanetId().'] no pudo cargarse','planetstate->getRegions');  
        }
        return $preparedRaw;
    }

//////////////////////////////////////////////////////////////////////////////////
//Metodos BOOLEANOS
//////////////////////////////////////////////////////////////////////////////////
}

?>