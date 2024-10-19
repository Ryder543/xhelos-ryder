<?php
 require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
/*******************************************************
 * ibs: Clase agrupacion de las buildings internas internal buildings, buildings son las genericas
 * constructions son las buldings con coordenadas y colonia
 ******************************************************/
class ibs  extends identities
{	
    //////////////////////////////////////////////////////////////////////////////////	
    //Variables
    ///////////////////////////////////////////////////////////////////////////////////

    //////////////////////////////////////////////////////////////////////////////////
    //Metodos
    //////////////////////////////////////////////////////////////////////////////////
    /*********************************************************************************
    * regions: constructor
    *********************************************************************************/
    public function __construct(){
        $args = func_get_args();
        if(!empty($args)){
          debug::error('ibs se inicio con argumentos','ibs->constructor');
        }
        parent::__construct('ibman');
    }
    
    /*********************************************************************************
    * Iniciadores: Funciones que inician al planeta
    *********************************************************************************/  
    public function initActive(){ //[TODO] Metodo de la interface 'objeto'
        //Validamos que el army
        $sql = "SELECT * FROM internal_buildings WHERE state = '"._X_ACTIVE."'";
        //debug::log($sql,'regions->initByPlanet');
        $ibuild_raw = $this->db->vectorize($sql);
        //debug::log($regions_raw,'regions->initByPlanet regions_raw');
        if(empty($ibuild_raw)){
                //debug::warning('La estrella['.$starId.'] no tiene planetas');
        }
        else{
                $this->setRaw($ibuild_raw);
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

  
}

?>