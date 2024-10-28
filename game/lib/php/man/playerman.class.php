<?php
require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad


 /********************************************************
 * player: Clase para el manejo del jugador, esta en forma lazy 
 ******************************************************/
class playerman extends identitymap
{
	//////////////////////////////////////////////////////////////////////////////////	
	//CAMPOS
	///////////////////////////////////////////////////////////////////////////////////
	private static $instance;//Contiene la instancia unica de la base de datos	

  ///////////////////////////////////////////////////////////////////////////////////////
  //CONSTRUCTOR
  ///////////////////////////////////////////////////////////////////////////////////////  
  protected function __construct(){    
    parent::__construct('playerman','player','players');
  }
	
  public function defaultsql($id){
     //debug::trace('playerman->defaultsql'); 
     $sql = "SELECT * FROM players WHERE players.id=".$id." AND players.state ='"._X_PLAYER_STATUS_ACTIVE."'";
     return $sql;
  }
  
   /**********************************************************************
    * getPlayersInStar: Obtiene los jugadores activos en la estrella
    **********************************************************************/
    public function getPlayersInStar($star_id){
      $sql ="SELECT DISTINCT p.username FROM armies a INNER JOIN players p ON (a.player_id=p.id) WHERE a.region_id IN (SELECT id FROM regions WHERE regions.planet_id IN (SELECT id FROM planets WHERE planets.star_id =".$star_id." AND planets.state ='"._X_PLAYER_STATUS_ACTIVE."'))";
      $players_raw = $this->db->vectorize($sql,false);
      return $players_raw;
    } 

  /**********************************************************************
    * getPlayersInPlanet: Obtiene los jugadores activos en el planeta
    **********************************************************************/
    public function initAllByPlanet($planet_id){
        //debug::trace('playerman initByPlanet');
       $players_raw = array();  
       $sql = "SELECT DISTINCT p.*,a.region_id FROM armies AS a"
      ." INNER JOIN players AS p ON (a.player_id = p.id)"
      ." WHERE a.region_id IN(SELECT id FROM regions WHERE regions.planet_id =".$planet_id.")"
      ." AND p.state = '"._X_PLAYER_STATUS_ACTIVE."' ORDER BY p.type DESC";
      $players_raw = $this->db->vectorize($sql);
      if(count($players_raw)==0){ 
        $players_raw[0]='Este Planeta no tiene jugadores activos';
      }
      $this->raw = $players_raw;
      return $this->raw;
    }
/*
    public function &findById($id, $removePassword = true) {
      if ((isset($this->status)) && (isset($this->status[$id])) && (!$this->isDefiled($id)) && isset($this->oraw[$id])) {
          $obj = $this->getObj($id);
          return $obj;
      } else {
          $sql = $this->defaultsql($id);
          $raw = &$this->find($id, $sql);
          $obj = $this->wrap($raw, $removePassword);
      }
      debug::log($obj,'playerman->findById obj');
      return $obj;
  }*/
  
    /*
    public function findById($id){
        debug::log($this,'playerman->findById before');
        debug::trace('playerman->findById');
        $oPlayer =  parent::findById($id);
        debug::log($this,'playerman->findById after');
        debug::log($oPlayer,'playerman->findById after player');
        return $oPlayer;
    }*/
    
     //[CALL] Desde starstate.class;/**********************************************************************
    /* getPlayersByPlanetRegions: Obtiene los jugadores activos en el planeta
     * dividido entre todas las regiones del planeta
     * Lo usa planetview.class.php
    **********************************************************************/
    /*public function initAllByPlanetRegions($planet_id){
       $players_raw = array();  
       $sql = "SELECT DISTINCT p.type,p.id as player_id,p.id as id,p.username,a.region_id FROM armies AS a"
      ." INNER JOIN players AS p ON (a.player_id = p.id)"
      ." WHERE a.region_id IN(SELECT id FROM regions WHERE regions.planet_id =".$planet_id.")"
      ." AND p.state = '"._X_PLAYER_STATUS_ACTIVE."' ORDER BY p.type DESC"; 
      $players_raw = $this->db->vectorize($sql);
      if(count($players_raw)==0){ 
        $players_raw[0]='Este Planeta no tiene jugadores activos';
      }
      $this->raw = $players_raw;
      return $this->raw;
    }
      
    /**********************************************************************
    * initByStar: Obtiene los jugadores activos en la estrella
    **********************************************************************/
   //[CALL] Desde galaxystate.class;
    public function initAllByStar($star_id){
      $sql ="SELECT DISTINCT p.type,p.username,p.id FROM armies a INNER JOIN players p ON (a.player_id=p.id) WHERE a.region_id IN (SELECT id FROM regions WHERE regions.planet_id IN (SELECT id FROM planets WHERE planets.star_id =".$star_id." AND planets.state ='"._X_PLAYER_STATUS_ACTIVE."') ORDER BY p.type DESC)";
      $players_raw = $this->db->vectorize($sql);
      if(count($players_raw)==0){ 
        $players_raw[0]='Esta estrella no tiene jugadores activos';
      }
      $this->raw = $players_raw;
      return $this->raw;
    }  
    
    public function initAllWithStar(){
      $sql ="SELECT DISTINCT p.type,p.username,p.id,s.id AS star_id FROM armies a 
    INNER JOIN players p ON (a.player_id=p.id)
    INNER JOIN regions r ON (r.id = a.region_id)
    INNER JOIN planets pl ON (pl.id = r.planet_id)
    INNER JOIN stars s ON (s.id = pl.star_id) 
    WHERE r.state = 'A' AND a.state = 'A'
    ORDER BY star_id";
      $players_raw = $this->db->sql2group($sql,'star_id');
      if(count($players_raw)==0){ 
        $players_raw[0]='Esta estrella no tiene jugadores activos';
      }
      $this->raw = $players_raw;
      return $this->raw;
    }  
    
    

    public function findByUsername($username){
        $sql = "SELECT * FROM players as p WHERE p.username =  '" . $username . "'";
        $rawUser = $this->db->fetch($sql);
        if(empty($rawUser)){
            return false;
        }
        else{
            return $rawUser;
        }
    }
    
    public function findByMail($mail){
        $sql = "SELECT mail FROM players as p WHERE p.mail =  '" . $mail . "'";
        $rawUser = $this->db->fetch($sql);
        if(empty($rawUser)){
            return false;
        }
        else{
            return $rawUser;
        }
    }
    public function findByIdByStarId(){
        //? deberiamos de rellenar esto para el render de galaxystate
    }
    
    
    
    
    
///////////////////////////////////////////////////////////////////////////////////////
//CREACION
///////////////////////////////////////////////////////////////////////////////////////   
    public function createDt($data){
        //debug::log($data,'playerman->createDt current data');
        
        $sql = "INSERT INTO players("
            ."id, username, max_energy, actual_energy, last_update,"
            ."rate_energy, state, mail, current_colony_id, `type`, alliance_id,"
            ."home_region_id, res1, res2, res3, res4, res5, res6, res7, res8, "
            ."max_res1, max_res2, max_res3, max_res4, max_res5, max_res6, max_res7, "
            ."max_res8, rate_res1, rate_res2, rate_res3, tutorial, creation_date, "
            ."rate_res4, rate_res5, rate_res6, rate_res7, rate_res8)"
    ."VALUES (".$data["id"].", '".$data["username"]."',".$data["max_energy"].",".$data["actual_energy"].",".$data["last_update"]."," 
            .$data["rate_energy"].",'".$data["state"]."','".$data["mail"]."',".$data["current_colony_id"].",'".$data["type"]."',".$data["alliance_id"].", "
            .$data["home_region_id"].",".$data["res1"].",".$data["res2"].",".$data["res3"].",".$data["res4"].",".$data["res5"].",".$data["res6"].", ".$data["res7"].", ".$data["res8"].", "
            .$data["max_res1"].",".$data["max_res2"].",".$data["max_res3"].",".$data["max_res4"].",".$data["max_res5"].",".$data["max_res6"].", ".$data["max_res7"].", "
            .$data["max_res8"].",".$data["rate_res1"].",".$data["rate_res2"].",".$data["rate_res3"].",".$data["tutorial"].",".$data["creation_date"].", "
            .$data["rate_res4"].",".$data["rate_res5"].",".$data["rate_res6"].",".$data["rate_res7"].",".$data["rate_res8"].");";
        
        debug::log($sql,'playerman->createDt current data');
        $dt = $this->db->insertDt($sql);
        $dt->setDataIfValid($data);
        return $dt;
   }

   public function wrap(&$data, $removePassword = true) {
    // Create a new player instance with the $removePassword parameter
    $obj = new $this->class($removePassword);
    $obj->initByArray($data);
    $this->setObj($obj->getId(), $obj);
    return $obj;
}

   
   ///////////////////////////////////////////////////////////////////////////////////////
   //UTILITARIOS
   ///////////////////////////////////////////////////////////////////////////////////////
  public static function singleton()//Obtiene la instancia unica de esta clase
  {
     //debug::error('singletom','playerman-singleton1');
     if (!isset(self::$instance)) {
         $c = __CLASS__;
         //debug::log($c,'playerman-singleton2');
         self::$instance = new $c;
         
     }
     //debug::log(self::$instance,'playerman-singleton');
     return self::$instance;
  }
 
  public function __clone() // Prevenimos que este objeto sea clonado
  {
     trigger_error('Clone is not allowed. playerman.class', E_USER_ERROR);
  }
}

?>