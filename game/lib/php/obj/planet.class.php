<?php
 require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
/*******************************************************
 * planet: Clase para el control de un planet
 ******************************************************/
class planet  extends identity
{	
	//////////////////////////////////////////////////////////////////////////////////	
	//Variables
	///////////////////////////////////////////////////////////////////////////////////
        private $regions_raw;
        private $resources_raw;
	//////////////////////////////////////////////////////////////////////////////////
	//Metodos
	//////////////////////////////////////////////////////////////////////////////////
	/*********************************************************************************
	* army: constructor
	*********************************************************************************/
	public function __construct(){
            $args = func_get_args();
            if(!empty($args)){
              debug::error('planet se inicio con argumentos','planet->constructor');
            }

            parent::__construct();
        }

  
  public function initFromRegion($regionId){ 
    $sql = "SELECT * FROM planets WHERE planets.id = (SELECT planet_id FROM regions WHERE regions.id =".$regionId.")";       
    $planet_raw = $this->db->fetch($sql);
    if(empty($planet_raw['id'])){
      debug::error('No se puede cargar el planeta a partir de la region ['.$regionId.']','planet.class.php');
    }
    else{
      $this->raw = $planet_raw;
      $this->ext_planet_id = $planet_raw['id'];
    } 
    return $this->raw;
  }
  
  
  /*********************************************************************************
  * Funciones Utilitarias
  *********************************************************************************/
  
    public function getStarId(){
        //debug::log($this->getParam('star_id'),'planet->getStarId');
        //debug::log($this,'planet->getStarId2');
        //debug::trace($this,'planet->getStarId23');
            return $this->getParam('star_id');
    }
    
    public function getColor(){
            return $this->getParam('color');
    }

  

  public function getTotalResources(){
    $sql="SELECT rr.resource_id as id,sum(rr.quality) as quality_sum FROM regions_resources rr WHERE rr.region_id IN (SELECT id FROM regions r where r.planet_id =".$this->getId().") GROUP BY rr.resource_id";
    $resources_raw = $this->db->vectorize($sql);
    $planetId = $this->getId();
    if(!empty( $planetId ) ){
      $this->resources_raw =$resources_raw; 
    }
    else{
     debug::error('No se pueden obtener los totalizados del planeta porque no existe el ID del planeta','planet->getPlanetTotalResources'); 
    }
    if(count($resources_raw)==0){
      $resources_raw[0] = 'El Planeta no tiene recursos';
    }
    
    return $resources_raw;
  }
  /*
  public function getResources(){
    $sql ="SELECT COALESCE(p.username,'Sin Dueño') as username,rs.* FROM regions_resources AS rs LEFT JOIN players AS p ON (rs.player_owner_id = p.id) WHERE rs.region_id IN (SELECT id FROM regions AS rg where rg.planet_id=".$this->getId().")";
    //$sql ="SELECT p.name,rs.* FROM regions_resources AS rs LEFT JOIN publicplayers AS p ON (rs.player_owner_id = p.id) WHERE rs.region_id IN (SELECT id FROM regions AS rg where rg.planet_id=".$this->getId().")";
    $resources_raw = $this->db->vectorize($sql);
    if(empty($resources_raw)){
      //debug::error('No se pueden cargar los recursos del planeta ['.$this->getName().']','planet.class.php');
      $resources_raw="El Planeta ".$this->getName()." no tiene recursos";
    }
    else{
      $this->resources_raw=$resources_raw;
    }
    return $this->resources_raw;
  }*/
  
  
  public static function planetNames(){
      $names = array();
      $names[] = array('Alpha','Beta','Delta', 'Gama','Epsilon','Zeta','Eta','Theta','Iota','Kappa','Lambda');
      $names[] = array('Fehu','Uruz','Thurizas', 'Ansuz','Raido','Kaunan','Gebo','Wunjo','Hagalaz','Naudiz','Isaz');
      $key = array_rand($names);
      return $names[$key];
  }
  
  public static function planetSizesId(){
      $sizes = array('A','B','C','D');
      return $sizes;
  }
  
  public static function planetSizes(){
      $sizes = array('A'=>'Diminuto','B'=>'Mediano','C'=>'Grande','D'=>'Gigantesco');
      return $sizes;
  }
  
  public static function planetSize($sizeId){
      $planetSizes = planet::planetSizes();
      return $planetSizes[$sizeId]; 
  }
  
  
  public static function planetColorsId(){
      return array('A','B','C','D','E');
      
  }
  
  public static function planetVariationId(){
      return array('A','B','C','D');
      
  }

    public function postRefresh() {
        
    }

        public function prepareRaw() {
            $preparedRaw = $this->raw;
            //$preparedRaw['name'] = htmlentities($preparedRaw['name'], null, "UTF-8");
            //$preparedRaw['desc'] = htmlentities($preparedRaw['desc'], null, "UTF-8");            
            /*foreach ($preparedRaw as $key => $value) {          
                unset($preparedRaw[$key]['map']);
            }*/
            return $preparedRaw;
        }
  
}

?>