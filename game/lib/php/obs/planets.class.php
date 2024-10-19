<?php
 require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
/*******************************************************
 * planet: Clase para el control de un planet
 ******************************************************/
class planets  extends identities
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
          debug::error('planets se inicio con argumentos','planet->constructor');
        }
        parent::__construct('planetman');
    }
    
    
    public function getPlanetsInOrbit($orbit) {
        $planetsinorbit = array();
        $raw = $this->getRaw();
        if (!empty($raw)) {
            $planets = $this->getRaw();
            foreach ($planets as $key => $planet) {
                
                if ($planet['orbit'] == $orbit) {
                    $planetsinorbit[] = $planet;
                }
            }
        } else {
            debug::error('No se pueden obtener los planetas en orbita porque planets esta vacio', 'planets->getPlanetsInOrbit');
        }
        return $planetsinorbit;
    }
    
    public function getMaxSizeInOrbit($orbit) {

        $planetsinorbit = $this->getPlanetsInOrbit($orbit);
        if (!empty($planetsinorbit)) {
            $temp = '';
            foreach ($planetsinorbit as $key => $planet) {
                if ($planet['size'] > $temp) {
                    $temp = $planet['size'];
                }
            }
        } else {
            //debug::warning('No se puede obtener el numero maximo de orbitas porque no hay orbita','star.class.php/getMaxSizeInOrbit');
            $temp = 0;
        }
        return $temp;
    }
    
    
    /*********************************************************************************
    * Iniciadores: Funciones que inician al planeta
    *********************************************************************************/  
    public function initByStar($starId){ //[TODO] Metodo de la interface 'objeto'
        //Validamos que el army
        $sql = "SELECT * FROM planets AS pl WHERE pl.star_id = '".$starId."' AND pl.state = '". _X_PLANET_STATUS_ACTIVE."'";
        $planets_raw = $this->db->vectorize($sql);
        if(empty($planets_raw)){
                //debug::warning('La estrella['.$starId.'] no tiene planetas');
        }
        else{
                $this->setRaw($planets_raw);
        }
        return $this->getRaw();
    }
     public function initByPlayerId($playerId){ //[TODO] Metodo de la interface 'objeto'
        //Validamos que el army
        $sql = "SELECT * FROM planets AS p WHERE p.id IN (SELECT r.planet_id FROM regions AS r WHERE r.id IN (SELECT a.region_id FROM armies AS a WHERE a.player_id = ".$playerId.")) ";
        $planets_raw = $this->db->vectorize($sql);
        if(empty($planets_raw)){
                //debug::warning('La estrella['.$starId.'] no tiene planetas');
        }
        else{
                $this->setRaw($planets_raw);
        }
        return $this->getRaw();
    }
    
    public function initPlanetsWithoutArmies(){
        $sql="SELECT count(regions.planet_id),planets.name as planet_name,planets.id FROM regions
        INNER JOIN armies ON (regions.id = armies.region_id)
        RIGHT JOIN planets ON (planets.id = regions.planet_id)
        GROUP BY regions.planet_id,planets.name,planets.id
        HAVING count(regions.planet_id) = 0
        ORDER BY planets.id ";
        //debug::log($sql,'planets->initPLanetsWithoutArmies');
        $planets_raw = $this->db->vectorize($sql);
        if(empty($planets_raw)){
                //debug::warning('La estrella['.$starId.'] no tiene planetas');
        }
        else{
                $this->setRaw($planets_raw);
        }
        return $planets_raw;
        
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