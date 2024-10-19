<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/* * *****************************************************
 * armies: Clase para el manejo de varios armies 
 * **************************************************** */

class groupedregionsresources extends gidentities{ //[TODO]Deberiamos de instanciarlo de una interface objeto 

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
          debug::error('groupedregionsresources se inicio con argumentos','groupedregionsresources->constructor');
        }
        parent::__construct('regionresourcesman');//CARAJOOOO
    }

//////////////////////////////////////////////////////////////////////////////////
//Metodos INICIALIZADORES
//////////////////////////////////////////////////////////////////////////////////   
    public function initActiveByPlanetIdGroupedByRegionId($planetId){
        $sql = "SELECT c.* ,res.name,res.type FROM regions_resources AS c INNER JOIN resources as res  ON (c.resource_id = res.id) WHERE res.status ='A' AND c.region_id IN (SELECT r.id FROM regions AS r WHERE r.planet_id =".$planetId." )";
        $colonies = $this->db->sql2group($sql,'region_id');
        $this->setRaw($colonies);
        return $colonies;
    }
    

    public function prepareRaw() {
            $preparedRaw = $this->raw;
            if(!count($preparedRaw)<1){
                
                foreach($preparedRaw as $resourcesId => $resourcesInRegion ){//Es correcto el regionId, la funcion de armies->initByPlanetId.... devuelve un arreglo de arreglo
                    foreach($resourcesInRegion as $id => $resource){
                        //Añadir nuestras cosillas
                        $battleutil = new battleutil();
                        $preparedRaw[$resourcesId][$id]['allegiance'] = $battleutil->getAllegiance($resource['id'],"regionresource");
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