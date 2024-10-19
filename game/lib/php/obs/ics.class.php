<?php
 require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
/*******************************************************
 * ics: Clase agrupacion de las construcciones internas internal constructions
 ******************************************************/
class ics  extends identities
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
          debug::error('ics se inicio con argumentos','ics->constructor');
        }
        parent::__construct('icman');
    }
    
    /*********************************************************************************
    * Iniciadores: Funciones que inician al planeta
    *********************************************************************************/  
    public function initActiveByColonyId($colonyId){ //[TODO] Metodo de la interface 'objeto'
        //Validamos que el army
        $sql = "SELECT ic.*,ib.name,ib.form,ib.desc,time_to_sec2(ic.build_end,now()) as time FROM internal_constructions AS ic
        INNER JOIN internal_buildings AS ib ON (ic.internal_building_id = ib.id)           
        WHERE ic.colony_id = '".$colonyId."' AND ic.state = '". _X_ACTIVE."'";
        //debug::log($sql,'regions->initByPlanet');
        $icons_raw = $this->db->vectorize($sql);
        //debug::log($regions_raw,'regions->initByPlanet regions_raw');
        if(empty($icons_raw)){
                //debug::warning('La estrella['.$starId.'] no tiene planetas');
        }
        else{
                $this->setRaw($icons_raw);
        }
        return $this->getRaw();
    }
    public function initUpdateableActiveByColonyId($colonyId){ //[TODO] Metodo de la interface 'objeto'
        //Validamos que el army
        $sql = "SELECT ic.*,ib.name,ib.form,ib.desc,time_to_sec2(ic.build_end,now()) as time FROM internal_constructions AS ic
        INNER JOIN internal_buildings AS ib ON (ic.internal_building_id = ib.id)           
        WHERE ic.colony_id = '".$colonyId."' AND ic.state = '". _X_ACTIVE."' AND ic.build_state = '"._X_COLONY_BUILD_STATE_BUILDING."' AND build_end <= now() ";
        //debug::log($sql,'ics->initUpdateableActiveByColonyId');
        $icons_raw = $this->db->vectorize($sql);
        //debug::log($icons_raw,'ics->initUpdateableActiveByColonyId');
        if(empty($icons_raw)){
                //debug::warning('ics no existen construciones en la colouniaId['.$colonyId.']');
        }
        else{
                $this->setRaw($icons_raw);
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