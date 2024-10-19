<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/* * ******************************************************
 * player: Clase para el manejo del jugador, esta en forma lazy 
 * **************************************************** */

class colonyman extends identitymap {

    private static $instance; //Contiene la instancia unica

//////////////////////////////////////////////////////////////////////////////////
//Metodos
//////////////////////////////////////////////////////////////////////////////////
    /*     * *******************************************************************************
     * army: constructor
     * ******************************************************************************* */

    protected function __construct() {
        parent::__construct('colonyman', 'colony','colonies');
    }
    
    //////////////////////////////////////////////////////////////////////////////////  
    //PRINCIPALES
    ///////////////////////////////////////////////////////////////////////////////////
    public function subtractResourcesDt($colonyId,$resources){
        $r = $resources;
       //debug::trace('JODISTE BUG'); 
       //debug::log($r,'colonyman->substractResourcesDt1'); 
       for($i = 1; $i<=_X_TOTAL_RESOURCES; $i++){
           $resIndex = 'res'.$i;
           if(empty($r[$resIndex])){
               $r[$resIndex] = 0;
           }
       }
       //debug::log($r,'colonyman->substractResourcesDt2');  
        $sql="UPDATE colonies SET res1= (res1 - ".$r[_X_RESOURCE_1]."), res2=(res2 - ".$r[_X_RESOURCE_2]."), 
           res3=(res3 - ".$r[_X_RESOURCE_3]."), res4=(res4 - ".$r[_X_RESOURCE_4]."),
           res5=(res5 - ".$r[_X_RESOURCE_5]."), res6=(res6 - ".$r[_X_RESOURCE_6]."),
           res7=(res7 - ".$r[_X_RESOURCE_7]."), res8=(res8 - ".$r[_X_RESOURCE_8].")
           WHERE id=".$colonyId;
        $dt = $this->db->updateDt($sql);
        //debug::log($dt,'colonyman->substractResourcesDt dt');  
        return $dt;
        
    }
    
    //////////////////////////////////////////////////////////////////////////////////  
    //UTILITARIOS
    ///////////////////////////////////////////////////////////////////////////////////   	
    /*     * ******************************************************************
     * Singleton, devuelve la instancia del singleton
     * ******************************************************************* */
    public static function singleton() {//Obtiene la instancia unica de esta clase
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }
        return self::$instance;
    }
    

    /*     * ******************************************************************
     * Clone: Prohibe que algun sapazo clone un singleton
     * ******************************************************************* */

    public function __clone() { // Prevenimos que este objeto sea clonado
        trigger_error('Clone is not allowed. db_pg_class', E_USER_ERROR);
    }

    public function defaultsql($id) {
        //debug::trace('colonyman->defaultsql');
        //debug::log($this,'colonyman->defaultsql');
        $sql = "SELECT *,time_to_sec2(build_end,now()) AS time FROM colonies WHERE id = " . $id;
        return $sql;
    }
    
    public function createDt($data){
        $sql="INSERT INTO colonies(id, name, region_id, region_x, region_y, type, map, level, 
        owner_id, size_x, size_y,status,build_start,build_end,build_state)
        VALUES (".$data["id"].",'".$data["name"]."',".$data['region_id'].",".$data['region_x'].",".$data['region_y'].",
        '".$data['type']."','".$data['map']."',".$data['level'].",".$data['owner_id'].",".$data['size_x'].",
        ".$data['size_y'].",'".$data['status']."',".$data['build_start'].",".$data['build_end'].",'".$data['build_state']."')";
        
       //debug::log($sql,'colonyman>createDt');
        
        $dt = $this->db->insertDt($sql);
        if($dt->isValid()){
          $dt->setData($data);
        }
        return $dt;
    }
}

?>