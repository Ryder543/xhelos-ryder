<?php
 require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
 /********************************************************
 * player: Clase para el manejo del jugador, esta en forma lazy 
 ******************************************************/

class planetman  extends identitymap
{
  protected  function __construct(){
    parent::__construct('planetman','planet','planets');
  }
    
	//////////////////////////////////////////////////////////////////////////////////	
	//Variables
	///////////////////////////////////////////////////////////////////////////////////
        private static $instance;//Contiene la instancia unica
	
    //////////////////////////////////////////////////////////////////////////////////
    //Metodos
    //////////////////////////////////////////////////////////////////////////////////
      public function createDt($data){
          $sql="INSERT INTO planets(id, name, state, desc, star_id, orbit, size, color, variation,`order`,type,creation_time)".
          "VALUES (".$data["id"].",'".$data["name"]."','".$data["state"]."','".$data["desc"]."',".$data["star_id"].",".$data["orbit"].
          ",'".$data["size"]."', '".$data["color"]."', '".$data["variation"]."', ".$data["order"].",'".$data["type"]."','".$data["creation_time"]."')";
          $dt = $this->db->insertDt($sql);
          if($dt->ok() ){
              $dt->setData($data);
          }
          return $dt;
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
        
        $sql = "SELECT * FROM planets WHERE id = ".$id;
        return $sql;
    }
    
    //OJO no retorna data
      public function countPrimaryPlanetsThatAreNotFilledWithColonies(){

         $sql = "SELECT count(pla.id) as total,pla.name as planet_name,pla.id AS planet_id,pla.creation_time from colonies AS c" .
                " INNER JOIN players AS p ON (c.owner_id = p.id)" .
                " INNER JOIN regions AS r ON (c.region_id = r.id)" .
                " INNER JOIN planets AS pla ON (r.planet_id = pla.id)" .
                " WHERE c.type = '" . _X_COLONY_TYPE_PRIMARY . "' AND p.type != '" . _X_PLAYER_TYPE_ARTIFICIAL . "'  AND pla.type = '" . _X_PLANET_TYPE_PRIMARY . "'" .
                " GROUP BY pla.id,pla.name,pla.creation_time" .
                " HAVING count(c.id) <" . _X_COLONY_MAX_NUMBER_PER_PLANET . " ORDER BY pla.creation_time asc LIMIT 1";
         //Menor a 8 , osea 7, porque si tiene 8 entonces no hay donde colocarlo
        //debug::log($sql,'planets->initPLanetsWithoutArmies');
        $planets_raw = $this->db->vectorize($sql);
        return $planets_raw; 
    }
    
}

?>