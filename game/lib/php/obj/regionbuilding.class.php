<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/* * *****************************************************
 * regionbuilding: Las construcciones en la region indicada, la diferencia con regionconstrucion, es que
 * regionconstrucion tiene solo el esquema, regionconstruction no es la construccion
 * **************************************************** */

class regionbuilding extends identity {

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
              debug::error('resource se inicio con argumentos','regionbuildingn->constructor');
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
}

?>