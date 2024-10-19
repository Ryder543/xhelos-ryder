<?php
 require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
 /********************************************************
 * teamman: Clase para el manejo de las construcciones internas
 ******************************************************/

class teamman extends identitymap
{
        private static $instance;//Contiene la instancia unica
	
//////////////////////////////////////////////////////////////////////////////////
//Metodos
//////////////////////////////////////////////////////////////////////////////////
/*********************************************************************************
* army: constructor
*********************************************************************************/
protected function __construct(){
  parent::__construct('teamman', 'team','teams');
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
        $sql = "SELECT * FROM teams WHERE id = ".$id;
        return $sql;
    }
    
     public function deleteDt($teamId){
          $sql="DELETE FROM teams WHERE id = ".$teamId;
          $dt = $this->db->deleteDt($sql);
          return $dt;
      } 
      
    public function createDt($data){
          $sql="INSERT INTO teams(
          id, player_id, name, region_id,state)
          VALUES (".$data["id"].",".$data["player_id"].",'".$data["name"]."',".$data["region_id"].",'".$data['state']."')";
          $dt = $this->db->insertDt($sql);
          if($dt->isValid()){
              $dt->setData($data);
          }
          return $dt;
      } 

      public function countByPlayerIdByRegionId($playerId,$regionId){
          $sql="SELECT count(*) as total FROM teams WHERE player_id = ".$playerId." AND region_id =".$regionId;
          $count_raw = $this->db->fetch($sql);
          return $count_raw['total'];
      }
  
}

?>