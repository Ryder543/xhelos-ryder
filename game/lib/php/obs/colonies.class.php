<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/* * *****************************************************
 * armies: Clase para el manejo de varios armies 
 * **************************************************** */

class colonies extends identities{ //[TODO]Deberiamos de instanciarlo de una interface objeto 

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
          debug::error('colonies se inicio con argumentos','colonies->constructor');
        }
        parent::__construct('colonyman');//CARAJOOOO
    }

//////////////////////////////////////////////////////////////////////////////////
//Metodos INICIALIZADORES
//////////////////////////////////////////////////////////////////////////////////   
   public function initActiveByPlayerId($playerId){
       if($playerId){
           $sql="SELECT *,time_to_sec2(build_end,now()) AS time from colonies where owner_id  = ".$playerId." AND status = '"._X_ACTIVE."'";
           //debug::log($sql);
           $stars = $this->db->vectorize($sql);
           $this->setRaw($stars);
       }
       else{
           debug::log($playerId,'colonies->initActiveByPlayerId No se ingreso la variable player:');
       }
       return $this->getRaw();
}


   public function initActiveByRegionId($regionId){
       if($regionId){
           $sql="SELECT *,time_to_sec2(build_end,now()) AS time from colonies where region_id  = ".$regionId." AND status = '"._X_COLONY_STATE_ACTIVE."'";
           //debug::log($sql,'colonies.class->initByRegionId');
           $cols = $this->db->vectorize($sql);
           $this->setRaw($cols);
       }
       else{
           //debug::log($playerId,'colonies->initActiveByPlayerId No se ingreso la variable player:');
       }
       return $this->getRaw();
    }
    
    public function initActiveByPlayerIdByRegionId($playerId,$regionId){
       if($playerId){
           $sql="SELECT *,time_to_sec2(build_end,now()) AS time from colonies where owner_id  = ".$playerId." AND region_id = ".$regionId." AND status = '"._X_ACTIVE."'";
           $raw = $this->db->vectorize($sql);
           $this->setRaw($raw);
       }
       else{
           debug::log($playerId,'colonies->initActiveByPlayerIdByRegionId No se ingreso la variable player:');
       }
       return $this->getRaw();
}   
    
    
    
    /*
     */
 /*   public function initByPlanetIdOrderByRegionId($planetId){
        $sql = "SELECT c.* FROM colonies AS c WHERE c.region_id IN (SELECT r.id FROM regions AS r WHERE r.planet_id = ".$planetId." )";
        $armies_raw = $this->db->sql2group($sql,'region_id');
        return $armies_raw;
    }*/
    
        public function prepareRaw() {
            $battleutil = new battleutil();
            $preparedRaw = $this->raw;
            foreach ($preparedRaw as $key => $value) {          
                unset($preparedRaw[$key]['map']);
                //$preparedRaw[$key]['allegiance'] = $battleutil->getColonyAllegiance($preparedRaw[$key]['id']);
                $preparedRaw[$key]['allegiance'] = $battleutil->getAllegiance($preparedRaw[$key]['id'],'colony');
                
            }
            return $preparedRaw;
        }
   
   
//////////////////////////////////////////////////////////////////////////////////
//Metodos BOOLEANOS
//////////////////////////////////////////////////////////////////////////////////

}

?>