<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/* * *****************************************************
 * armies: Clase para el manejo de varios armies 
 * **************************************************** */

class stars extends identities{ //[TODO]Deberiamos de instanciarlo de una interface objeto 

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
          debug::error('stars se inicio con argumentos','stars->constructor');
        }
        parent::__construct('starman');//CARAJOOOO
    }

//////////////////////////////////////////////////////////////////////////////////
//Metodos INICIALIZADORES
//////////////////////////////////////////////////////////////////////////////////
   public function initByGalaxy($galaxyId = false){
       if(!$galaxyId){
           $sql = "SELECT * FROM stars WHERE state = '"._X_STAR_STATUS_ACTIVE."'";
           $stars = $this->db->vectorize($sql);
           $this->setRaw($stars);
       }
       else{
           debug::log($galaxyId,'stars->initByGalaxy No existe ninguna galaxia aun, menos con el id:');
       }
       return $this->getRaw();
   
   }
   
   public function initByPlayerId($playerId){
       if($playerId){
           $sql="select * from stars where stars.id IN (SELECT p.star_id FROM planets AS p WHERE p.id IN (SELECT r.planet_id FROM regions AS r WHERE r.id IN (SELECT a.region_id FROM armies AS a WHERE a.player_id = ".$playerId.")) )";
           $stars = $this->db->vectorize($sql);
           $this->setRaw($stars);
       }
       else{
           debug::log($playerId,'stars->initByPlayerId No se ingreso la variable player:');
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