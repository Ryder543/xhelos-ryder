<?php
 require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
 /********************************************************
 * icman: Clase para el manejo de las construcciones internas
 ******************************************************/

class icman extends identitymap
{
        private static $instance;//Contiene la instancia unica
	
//////////////////////////////////////////////////////////////////////////////////
//Metodos
//////////////////////////////////////////////////////////////////////////////////
/*********************************************************************************
* army: constructor
*********************************************************************************/
protected function __construct(){
  parent::__construct('icman', 'ic','internal_constructions');
}
  
  
    //////////////////////////////////////////////////////////////////////////////////  
    //UTILITARIOS
    ///////////////////////////////////////////////////////////////////////////////////   	
    /********************************************************************
    * Singleton, devuelve la instancia del singleton
    *********************************************************************/
    public static function singleton(){//Obtiene la instancia unica de esta clase
            if(!isset(self::$instance)) {
                    $c = __CLASS__;
                    self::$instance = new $c;
            }
            return self::$instance;
    }
    /********************************************************************
    * Clone: Prohibe que algun sapazo clone un singleton
    *********************************************************************/
   public function __clone() // Prevenimos que este objeto sea clonado
   {
       trigger_error('Clone is not allowed. db_pg_class', E_USER_ERROR);
   }

    public function defaultsql($id) {
        $sql = "SELECT *,time_to_sec2(build_end,now()) AS time FROM internal_constructions WHERE id = ".$id;
        return $sql;
    }
    
    public function findByColonyIdByCoord($colonyId,$coordX,$coordY){
        $sql = "SELECT *,time_to_sec2(build_end,now()) AS time FROM internal_constructions WHERE colony_id = ".$colonyId ." AND city_x=".$coordX." AND city_y = ".$coordY." AND state='"._X_ACTIVE."'";
        $ic_raw = $this->db->fetch($sql);
        $ic = $this->wrap($ic_raw);
        return $ic;
    }
    
    
    public function createDt($data){
          $sql="INSERT INTO internal_constructions(
          id, city_x, city_y, colony_id, internal_building_id, level, state,build_start,build_end,build_state)
          VALUES (".$data["id"].",".$data["cityX"].",".$data["cityY"].",".$data["colony_id"].",".$data["internal_building_id"].",".$data["level"].
          ",'".$data["state"]."',".$data["build_start"].",".$data["build_end"].",'".$data["build_state"]."')";
          $dt = $this->db->insertDt($sql);
          if($dt->isValid()){
              $dt->setData($data);
          }
          return $dt;
      } 
      
      public function  initConstructionDt($id,$time_end){
          $sql="UPDATE internal_constructions SET build_start = now(),build_end = ( now() + '".$time_end."' ), build_state = '"._X_COLONY_BUILD_STATE_BUILDING."' WHERE id = ".$id;
          $dt = $this->db->updateDt($sql);
          return $dt;
      }
      public function levelUpDt($id,$level){
          $sql="UPDATE internal_constructions SET level = ".$level.",build_state = '"._X_COLONY_BUILD_STATE_DONE."' WHERE id = ".$id;
          //debug::log($sql,'icman->levelUpDt');
          $dt = $this->db->updateDt($sql);
          return $dt;
      }
      
      

  
}

?>