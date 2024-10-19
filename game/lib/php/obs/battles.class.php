<?php
 require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad 
 /********************************************************
 * player: Clase para el manejo del jugador, esta en forma lazy 
 ******************************************************/

class battles  extends identities
{
	//////////////////////////////////////////////////////////////////////////////////	
	//Variables
	
	///////////////////////////////////////////////////////////////////////////////////
	//Variables Internas
	//private $raw;
        //private $db;
	
	//////////////////////////////////////////////////////////////////////////////////
	//Metodos
	//////////////////////////////////////////////////////////////////////////////////
	/*********************************************************************************
	* army: constructor
	*********************************************************************************/
	public function __construct(){
	  $this->db = db::singleton();  
	}
  
  /*********************************************************************************
  * initAll Inicia todas las batallas
  *********************************************************************************/
      public function initAll(){
      $sql ="SELECT * FROM battles";
      $battles_raw = $this->db->vectorize($sql);
      if(empty($battles_raw)){ 
        $players_raw[0]='No hay batallas';
      }
      $this->raw = $battles_raw;
      return $this->raw;
    }  
      
  /*********************************************************************************
  * initPrebattleByPlanet
  *********************************************************************************/
      public function initActiveByPlanet($planet_id){//Prebattle e Init
      $sql ="SELECT battles.* FROM battles INNER JOIN regions ON (battles.region_id= regions.id) INNER JOIN planets ON (regions.planet_id = planets.id) WHERE planets.id=".$planet_id." AND battles.phase IN ('"._X_BATTLE_PHASE_BATTLE."','"._X_BATTLE_PHASE_TACTIC."')";
      $battles_raw = $this->db->vectorize($sql);
      if(empty($battles_raw)){ 
        $battles_raw=array();
        //debug::warning('No se pudo obtener las batallas Activas por Planeta['.$planet_id.']');
      }
      else{
      $this->raw = $battles_raw;  
      }
      
      return $this->raw;
    }

    public function prepareRaw() {
        $preparedRaw = $this->raw;
        /*foreach ($preparedRaw as $key => $value) {          
            unset($preparedRaw[$key]['map']);            
        }*/
        return $preparedRaw;
    }   
}

?>