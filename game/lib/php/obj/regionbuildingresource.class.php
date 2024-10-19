<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/* * *****************************************************
 * planet: Clase para el control de un planet
 * **************************************************** */

class regionbuildingresource extends identity {

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
              debug::error('regionbuildingresource se inicio con argumentos','regionbuildingresource->constructor');
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
    
    public function getResources(){
        $data = array();
        $data[_X_RESOURCE_1] = doubleval($this->getResource1());
        $data[_X_RESOURCE_2] = doubleval($this->getResource2());
        $data[_X_RESOURCE_3] = doubleval($this->getResource3());
        $data[_X_RESOURCE_4] = doubleval($this->getResource4());
        $data[_X_RESOURCE_5] = doubleval($this->getResource5());
        $data[_X_RESOURCE_6] = doubleval($this->getResource6());
        $data[_X_RESOURCE_7] = doubleval($this->getResource7());
        $data[_X_RESOURCE_8] = doubleval($this->getResource8());
        return $data;
    }
    
    public function getBuildTime(){
        return $this->getParam("build_time");
    }
    
    public function getResource1(){
        return $this->getParam("res1");
    }
    public function getResource2(){
        return $this->getParam("res2");
    }
    public function getResource3(){
        return $this->getParam("res3");
    }
    public function getResource4(){
        return $this->getParam("res4");
    }
    public function getResource5(){
        return $this->getParam("res5");
    }
    public function getResource6(){
        return $this->getParam("res6");
    }
    public function getResource7(){
        return $this->getParam("res7");
    }
    public function getResource8(){
        return $this->getParam("res8");
    }    
}

?>