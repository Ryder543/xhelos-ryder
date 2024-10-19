<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/* * *****************************************************
 * ib: Clase para el control de las internal buildings
 * **************************************************** */

class ib extends identity {

    //////////////////////////////////////////////////////////////////////////////////	
    //Variables
    ///////////////////////////////////////////////////////////////////////////////////

    //////////////////////////////////////////////////////////////////////////////////
    //Metodos
    //////////////////////////////////////////////////////////////////////////////////
    /*     * *******************************************************************************
     * ib: constructor
     * ******************************************************************************* */
    public function __construct() {
         $args = func_get_args();
            if(!empty($args)){
              debug::error('ic se inicio con argumentos','ib->constructor');
            }
            parent::__construct();
    }

    public function getLevel(){
        
        return $this->getParam('level');
    }
    
    public function postRefresh() {
        
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