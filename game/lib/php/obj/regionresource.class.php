<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/* * *****************************************************
 * planet: Clase para el control de un planet
 * **************************************************** */

class regionresource extends identity {

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
              debug::error('regionresource se inicio con argumentos','regionresource->constructor');
            }
            parent::__construct();
    }
 
    public function postRefresh() {

    }

    public function prepareRaw() {
        $preparedRaw = $this->raw;       
        //unset($preparedRaw['map']);
        return $preparedRaw;
    }
    
    public function getRegionId() {
        $o = $this->getParam('region_id'); 
        return $o;
    }
    
    public function getQuality() {
        $o = $this->getParam('quality'); 
        return $o;
    }
     public function getResourceId() {
        $o = $this->getParam('resource_id'); 
        return $o;
    }   
}

?>