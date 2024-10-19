<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/* * *****************************************************
 * biome: Clase para el control de un bioma
 * **************************************************** */

class biome extends identity {

    //////////////////////////////////////////////////////////////////////////////////	
    //Variables
    ///////////////////////////////////////////////////////////////////////////////////

    //////////////////////////////////////////////////////////////////////////////////
    //Metodos
    //////////////////////////////////////////////////////////////////////////////////
    /********************************************************************************
     * biome: constructor
     * ******************************************************************************* */
    public function __construct() {
         $args = func_get_args();
            if(!empty($args)){
              debug::error('resource se inicio con argumentos','biome->constructor');
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

}

?>