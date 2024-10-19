<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/* * *****************************************************
 * planet: Clase para el control de un planet
 * **************************************************** */

class resource extends identity {

    //////////////////////////////////////////////////////////////////////////////////	
    //Variables
    ///////////////////////////////////////////////////////////////////////////////////

    //////////////////////////////////////////////////////////////////////////////////
    //Metodos
    //////////////////////////////////////////////////////////////////////////////////
    /*     * *******************************************************************************
     * army: constructor
     * ******************************************************************************* */
    public function __construct() {
         $args = func_get_args();
            if(!empty($args)){
              debug::error('resource se inicio con argumentos','resource->constructor');
            }
            parent::__construct();
    }

    public function getType(){
        return $this->getParam('type');
    }
    
    public function isStrategic(){
        if ($this->getType() == _X_RESOURCE_TYPE_STRATEGIC) {
            //debug::log($this-getParam('type'),'player['._X_PLAYER_TYPE_ARTIFICIAL.']->isArtificial->Verdadero');
            return true;
        } else {
            //debug::log($this-getParam('type'),'player['._X_PLAYER_TYPE_ARTIFICIAL.']->isArtificial->Falso');
            return false;
        }
    }
    public function isDinamic(){
        if ($this->getType() == _X_RESOURCE_TYPE_DINAMIC) {
            //debug::log($this-getParam('type'),'player['._X_PLAYER_TYPE_ARTIFICIAL.']->isArtificial->Verdadero');
            return true;
        } else {
            //debug::log($this-getParam('type'),'player['._X_PLAYER_TYPE_ARTIFICIAL.']->isArtificial->Falso');
            return false;
        }
    }
    
    
    public function postRefresh() {

    }

    public function prepareRaw() {
        $preparedRaw = $this->raw;       
        //unset($preparedRaw['map']);
        return $preparedRaw;
    }

}

?>