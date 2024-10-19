<?php
 require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
/*******************************************************
 * planet: Clase para el control de un planet
 ******************************************************/
class regions  extends identities
{	
    //////////////////////////////////////////////////////////////////////////////////	
    //Variables
    ///////////////////////////////////////////////////////////////////////////////////
    private $resources_raw;
    //////////////////////////////////////////////////////////////////////////////////
    //Metodos
    //////////////////////////////////////////////////////////////////////////////////
    /*********************************************************************************
    * regions: constructor
    *********************************************************************************/
    public function __construct(){
        $args = func_get_args();
        if(!empty($args)){
          debug::error('planets se inicio con argumentos','regions->constructor');
        }
        parent::__construct('regionman');//CARAJOOOO
    }
    
    /*********************************************************************************
    * Iniciadores: Funciones que inician al planeta
    *********************************************************************************/  
    public function initByPlanetId($planetId){ //[TODO] Metodo de la interface 'objeto'
        //Validamos que el army
        $sql = "SELECT * FROM regions AS re WHERE re.planet_id = '".$planetId."' AND re.state= '". _X_ACTIVE."'";
        //debug::log($sql,'regions->initByPlanet');
        $regions_raw = $this->db->vectorize($sql,true);//Agrupado por su id
        //debug::log($regions_raw,'regions->initByPlanet regions_raw');
        if(empty($regions_raw)){
                //debug::warning('La estrella['.$starId.'] no tiene planetas');
        }
        else{
                $this->setRaw($regions_raw);
        }
        return $regions_raw;
    }
    
    
    
     public function getSides(){
      $nregions = $this->size();
        //Calculo de tamaño
        $max = ceil(sqrt($nregions));
        $sides['x']=$max;
        $sides['y']=$max;
        return $sides;
    }

    public function prepareRaw() { 
        $battleutil = new battleutil();
        $preparedRaw = $this->raw;
        foreach ($preparedRaw as $key => $value) {          
            unset($preparedRaw[$key]['map']);
            /*if( ($key ==13073) || ($key ==13074) ){
                debug::log($key);
            }*/
            $preparedRaw[$key]['allegiance'] = $battleutil->getAllegiance($preparedRaw[$key]['id'],'region');
            //debug::log("BORRAR");
        }
        return $preparedRaw;
    }
  
}

?>