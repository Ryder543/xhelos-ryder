<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/* * *****************************************************
 * armies: Clase para el manejo de varios armies 
 * **************************************************** */

class groupedregionsbiomes extends gidentities{ //[TODO]Deberiamos de instanciarlo de una interface objeto 

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
          debug::error('groupedbiomes se inicio con argumentos','groupedbiomes->constructor');
        }
        parent::__construct('regionbiomeman');//CARAJOOOO
    }

//////////////////////////////////////////////////////////////////////////////////
//Metodos INICIALIZADORES
//////////////////////////////////////////////////////////////////////////////////   
    public function initActiveByPlanetIdGroupedByRegionId($planetId){
        $sql = "SELECT c.*,b.name,b.tier,b.type,b.desc FROM regions_biomes AS c INNER JOIN biomes AS b ON (c.biome_id = b.id ) WHERE b.status ='"._X_ACTIVE."' AND c.region_id IN (SELECT r.id FROM regions AS r WHERE r.planet_id = ".$planetId." )";
        $colonies = $this->db->sql2group($sql,'region_id');
        $this->setRaw($colonies);
        return $colonies;
    }
    

    public function prepareRaw() {
            $preparedRaw = $this->raw;
            if(!count($preparedRaw)<1){
                
                foreach($preparedRaw as $biomesId => $biomesInRegion ){//Es correcto el regionId, la funcion de armies->initByPlanetId.... devuelve un arreglo de arreglo
                    foreach($biomesInRegion as $id => $biome){
                        //Añadir nuestras cosillas
                        $battleutil = new battleutil();
                        $preparedRaw[$biomesId][$id]['allegiance'] = $battleutil->getAllegiance($biome['id'],"regionbiome");
                        //unset($preparedRaw[$biomesId][$id]['map']);
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