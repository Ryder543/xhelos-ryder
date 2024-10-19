<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/* * *****************************************************
 * ic: Clase para el control de las internal constructions
 * **************************************************** */

class ic extends identity {

    //////////////////////////////////////////////////////////////////////////////////	
    //Variables
    ///////////////////////////////////////////////////////////////////////////////////
    private $localPlanets;

    //////////////////////////////////////////////////////////////////////////////////
    //Metodos
    //////////////////////////////////////////////////////////////////////////////////
    /*     * *******************************************************************************
     * army: constructor
     * ******************************************************************************* */
    public function __construct() {
         $args = func_get_args();
            if(!empty($args)){
              debug::error('ic se inicio con argumentos','ic->constructor');
            }
            parent::__construct();
    }

    public function postRefresh() {
        
    }
    
    public function  getBuildingId(){
        return $this->getParam('internal_building_id');
    }
    public function getNextLevel(){
        return $this->getParam('level')+1;
    }
    
    public function initUpgradeNextLevelDt($time_end){
        $dt = $this->man()->initConstructionDt($this->getId(),$time_end);
        $this->setDataDirty();
        return $dt;
    }
    public function levelUpDt(){
        $dt = $this->man()->levelUpDt($this->getId(),$this->getNextLevel());
        $this->setDataDirty();
        return $dt;
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