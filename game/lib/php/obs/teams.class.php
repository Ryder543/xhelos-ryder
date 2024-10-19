<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/* * *****************************************************
 * armies: Clase para el manejo de varios armies 
 * **************************************************** */

class teams extends identities{ //[TODO]Deberiamos de instanciarlo de una interface objeto 

   

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
          debug::error('teams se inicio con argumentos','teams->constructor');
        }
        parent::__construct('teamman');//CARAJOOOO
    }

//////////////////////////////////////////////////////////////////////////////////
//Metodos INICIALIZADORES
//////////////////////////////////////////////////////////////////////////////////
    
   /*
    * initByPlanetIdByPlayerIdOrderByRegion: Obtiene un arreglo ordenado por region, creo que esto no va aqui [TODO] Esto existe? porque crea un array de array
    */ 
    
   public function initByPlanetIdByPlayerIdOrderByRegion($planetId,$playerId){
       if( (!empty($planetId)) && (!empty($playerId)) ){
           $sql = "SELECT * FROM teams WHERE player_id = ".$playerId." AND region_id IN (SELECT id FROM regions WHERE planet_id = ".$planetId.") ORDER BY region_id,id";
           $stars = $this->db->sql2group($sql,'region_id');
           $this->setRaw($stars);
       }
       else{
           debug::log($galaxyId,'teams->initByPlanetIdByPlayerIdOrderByRegion ingrese bien los parametros planetId['.$planetId.'] playerId['.$playerId.']:');
       }
       return $this->getRaw();
   }
   
   public function initByRegionIdByPlayerId($regionId,$playerId){
      if( (!empty($regionId)) && (!empty($playerId)) ){
           $sql = "SELECT * FROM teams WHERE player_id = ".$playerId." AND region_id = ".$regionId." ORDER BY id";
           $teams = $this->db->vectorize($sql);
           $this->setRaw($teams);
       }
       else{
           debug::log('teams->initByRegionIdByPlayerId ingrese bien los parametros regionId['.$regionId.'] playerId['.$playerId.']:');
       }
       return $this->getRaw(); 
   }

    public function prepareRaw() {
        $preparedRaw = $this->raw;
        /*foreach ($preparedRaw as $key => $value) {          
            unset($preparedRaw[$key]['map']);            
        }*/
        return $preparedRaw;
    }

   
   
//////////////////////////////////////////////////////////////////////////////////
//Metodos BOOLEANOS
//////////////////////////////////////////////////////////////////////////////////

}

?>