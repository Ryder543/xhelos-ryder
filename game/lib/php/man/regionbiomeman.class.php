<?php
 require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
 /********************************************************
 * player: Clase para el manejo del jugador, esta en forma lazy 
 ******************************************************/

class regionbiomeman extends identitymap
{
        private static $instance;//Contiene la instancia unica
	
//////////////////////////////////////////////////////////////////////////////////
//Metodos
//////////////////////////////////////////////////////////////////////////////////
/*********************************************************************************
* army: constructor
*********************************************************************************/
protected function __construct(){
  parent::__construct('regionbiomeman', 'regionbiome','regions_biomes');
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
        $sql = "SELECT * FROM regions_biomes WHERE id = ".$id;
        return $sql;
    }
  
    public function createDt($data){
          $sql = "INSERT INTO regions_biomes(id, biome_id, region_id,status)".
          "VALUES (".$data["id"].",".$data["biome_id"].",".$data["region_id"].",'".$data["status"]."')";
          $dt = $this->db->insertDt($sql);
          if($dt->ok() ){
              $dt->setData($data);
          }
          return $dt;
      }
  
}

?>