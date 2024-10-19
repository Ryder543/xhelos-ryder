<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/* * ******************************************************
 * colony: Clase para el manejo de las colonias
 * **************************************************** */

class colony extends identity {

    //////////////////////////////////////////////////////////////////////////////////
    //CAMPOS
    ////////////////////////////////////////////////////////////////////////////////// 
    private $array_map; 
    //////////////////////////////////////////////////////////////////////////////////
    //CONSTRCUTOR
    //////////////////////////////////////////////////////////////////////////////////
    public function __construct() {
        $args = func_get_args();
        if (!empty($args)) {
            debug::error('player se inicio con argumentos', 'player con argumentos');
        }
        parent::__construct();
    }

    public function postRefresh() {
        //La variable de mapa de array (texto de la bd) es transformada a arreglo
        if (empty($this->array_map)) {
            $this->array_map = str2array($this->getParam('map'));
        }
    }
    
    public function subtractResourcesDt($cost){
        $dt = $this->man()->subtractResourcesDt($this->getId(),$cost);
        $this->setDataDirty();
        return $dt;
    }
    
    public function substracResource($resId,$quantity){
        $this->man()->updateRes($this->getId(),$resId,$newQuantity);
        $this->setDataDirty();
    }
    

    public function getArrayMap() {
        return $this->array_map;
    }

    public function getRegionId() {
        return $this->getParam("region_id");
    }
    
    public function getOwnerId() {
        return $this->getParam("owner_id");
    }
    
    public function getPlayerId() {
        return $this->getParam("owner_id");
    }
    
     public function getType(){
        return $this->getParam('type');
    }   
    
    public function getSizeX(){
        return $this->getParam('size_x');
    }
    public function getSizeY(){
        return $this->getParam('size_y');
    }
    
    
    /*Ya no se utiliza pero bueno...*/
    public function getResources(){
        $data = array();
        $data[_X_RESOURCE_1] = $this->getParam(_X_RESOURCE_1);
        $data[_X_RESOURCE_2] = $this->getParam(_X_RESOURCE_2);
        $data[_X_RESOURCE_3] = $this->getParam(_X_RESOURCE_3);
        $data[_X_RESOURCE_4] = $this->getParam(_X_RESOURCE_4);
        $data[_X_RESOURCE_5] = $this->getParam(_X_RESOURCE_5);
        $data[_X_RESOURCE_6] = $this->getParam(_X_RESOURCE_6);
        $data[_X_RESOURCE_7] = $this->getParam(_X_RESOURCE_7);
        $data[_X_RESOURCE_8] = $this->getParam(_X_RESOURCE_8);
        return $data;
    }

    public function prepareRaw() {
        $preparedRaw = $this->raw;
        unset($preparedRaw['map']);
        /*foreach ($preparedRaw as $key => $value) {          
            unset($preparedRaw[$key]['map']);
        }*/
        return $preparedRaw;
    }
    
}

?>