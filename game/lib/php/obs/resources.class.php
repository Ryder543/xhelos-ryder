<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/* * *****************************************************
 * armies: Clase para el manejo de varios armies 
 * **************************************************** */

class resources extends identities{ //[TODO]Deberiamos de instanciarlo de una interface objeto 

    //[TODO]El Refresh se esta llamando muchas veces, deberiamos tener una funcion
    //setObjectParam('position','P') por ejemplo que actualize la accion
    //////////////////////////////////////////////////////////////////////////////////
    //Variables
    ///////////////////////////////////////////////////////////////////////////////////
    //Variables Internas


//////////////////////////////////////////////////////////////////////////////////
//Metodos PRINCIPALES
//////////////////////////////////////////////////////////////////////////////////	


//////////////////////////////////////////////////////////////////////////////////
//Metodos GETTERS
//////////////////////////////////////////////////////////////////////////////////
    /*********************************************************************************
    * stars: constructor
    *********************************************************************************/
    public function __construct(){
        $args = func_get_args();
        if(!empty($args)){
          debug::error('resources se inicio con argumentos','resources->constructor');
        }
        parent::__construct('resourceman');//CARAJOOOO
    }

//////////////////////////////////////////////////////////////////////////////////
//Metodos INICIALIZADORES
//////////////////////////////////////////////////////////////////////////////////
public function initAll(){
       $sql="select * from resources";
       $resources = $this->db->vectorize($sql);
       $this->setRaw($resources);
       return $this->getRaw();
}

 public function initAllActive(){
       $sql="select * from resources WHERE status = '"._X_ACTIVE."'";
       $resources = $this->db->vectorize($sql);
       $this->setRaw($resources);
       return $this->getRaw();
}
 public function initAllActiveIndexedById(){
       $sql="select * from resources WHERE status = '"._X_ACTIVE."'";
       $resources = $this->db->vectorize($sql,'id');
       $this->setRaw($resources);
       return $this->getRaw();
}




 public function initAllActiveStrategic(){
       $sql="select * from resources WHERE status = '"._X_ACTIVE."' AND type='"._X_RESOURCE_TYPE_STRATEGIC."'";
       $resources = $this->db->vectorize($sql);
       $this->setRaw($resources);
       return $this->getRaw();
}


    public function prepareRaw() {
        $preparedRaw = $this->raw;
        foreach ($preparedRaw as $key => $value) {          
            unset($preparedRaw[$key]['status']);            
        }
        return $preparedRaw;
    }
   
//////////////////////////////////////////////////////////////////////////////////
//Metodos BOOLEANOS
//////////////////////////////////////////////////////////////////////////////////

}

?>