<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/* * *****************************************************
 * armies: Clase para el manejo de varios armies 
 * **************************************************** */

class groupedcolonies extends gidentities{ //[TODO]Deberiamos de instanciarlo de una interface objeto 

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
    /*********************************************************************************
    * stars: constructor
    *********************************************************************************/
    public function __construct(){
        $args = func_get_args();
        if(!empty($args)){
          debug::error('groupedbiomes se inicio con argumentos','groupedcolonies->constructor');
        }
        parent::__construct('colonyman');//CARAJOOOO
    }

//////////////////////////////////////////////////////////////////////////////////
//Metodos INICIALIZADORES
//////////////////////////////////////////////////////////////////////////////////   
    public function initActiveByPlanetIdGroupedByRegionId($planetId){
        $sql = "SELECT c.*,time_to_sec2(c.build_end,now()) AS time FROM colonies AS c WHERE c.status = '"._X_COLONY_STATE_ACTIVE."' AND c.region_id IN (SELECT r.id FROM regions AS r WHERE r.planet_id = ".$planetId." )";
        $colonies = $this->db->sql2group($sql,'region_id');
        $this->setRaw($colonies);
        return $colonies;
    }
    

    public function prepareRaw() {
            $preparedRaw = $this->raw;
            if(!count($preparedRaw)<1){
                
                foreach($preparedRaw as $regionId => $coloniesInRegion ){//Es correcto el regionId, la funcion de armies->initByPlanetId.... devuelve un arreglo de arreglo
                    foreach($coloniesInRegion as $id => $colony){
                        //Añadir nuestras cosillas
                        $battleutil = new battleutil();
                        //$preparedRaw[$regionId][$id]['allegiance'] = $battleutil->getColonyAllegiance($colony['id'],$this->getPlayer()->getId());
                        $preparedRaw[$regionId][$id]['allegiance'] = $battleutil->getAllegiance($colony['id'],"colony");
                        unset($preparedRaw[$regionId][$id]['map']);
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