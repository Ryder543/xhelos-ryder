<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/* * *****************************************************
 * planet: Clase para el control de un planet
 * **************************************************** */

class star extends identity {

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
              debug::error('star se inicio con argumentos','star->constructor');
            }
            parent::__construct();
    }

   

    public function init(){
        debug::trace('init star');
    }
    
    public function getQuantityPlanetsInOrbit(){
        
    }

    public function getName() {
        return $this->getParam('name');
    }

    public static function colorList() {
        return array('A', 'B', 'C');
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