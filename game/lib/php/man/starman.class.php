<?php
 require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
 /********************************************************
 * player: Clase para el manejo del jugador, esta en forma lazy 
 ******************************************************/

class starman extends identitymap
{
        private static $instance;//Contiene la instancia unica
	
//////////////////////////////////////////////////////////////////////////////////
//Metodos
//////////////////////////////////////////////////////////////////////////////////
/*********************************************************************************
* army: constructor
*********************************************************************************/
protected function __construct(){
  parent::__construct('starman', 'star','stars');
}
  
public function findByLeastPlanet(){
    //$sql ="SELECT * FROM stars WHERE stars.id  = (SELECT star_id FROM planets GROUP BY star_id ORDER BY count(star_id) asc LIMIT 1)"; //No obtiene las que no tienen planetas
    $sql = "SELECT count(planets.star_id),stars.* FROM stars LEFT JOIN planets ON (stars.id = planets.star_id)
    GROUP BY planets.star_id,stars.id,stars.name,stars.color,stars.state,stars.axis_x,stars.axis_y,stars.desc
    ORDER BY count(planets.star_id),star_id";
    $stars_raw = $this->db->vectorize($sql,false);
    //debug::log($stars_raw,'starman');
    $star_raw = $stars_raw[0];
    if (empty($star_raw['id'])) {
            debug::error('No se puede cargar la estrella con menos planetas [' . $planetId . ']', 'star.class.php');
            $star = null;
        } else {
            $star = $this->wrap($star_raw);
            //$this->ext_star_id = $star_raw['id'];
            //$this->init();
        }
        return $star;
}

 public function findByPlanet($planetId) {
        //1) Primero Obtenemos el id de la estrella
        $sql = "SELECT * FROM stars WHERE stars.id = (SELECT planets.star_id FROM planets WHERE planets.id=" . $planetId . ")";
        $star_raw = $this->db->fetch($sql);
        //2) Despues llamamos a init para obtener los datos, con esto nos aseguramos que iniciar la estrella desde cualquier lugar da lo mismo
        // Es mas lento pero por ahora funcionara correctamente
        if (empty($star_raw['id'])) {
            debug::error('No se puede cargar la estrella a partir de un planeta [' . $planetId . ']', 'star.class.php');
            $star = null;
        } else {
            $star = $this->wrap($star_raw);
            //$this->ext_star_id = $star_raw['id'];
            //$this->init();
        }
        return $star;
    }
    
    public function findByRegionId($regionId) {
        //debug::trace('starman->findByRegion');
        //1) Primero Obtenemos el id de la estrella
        //$sql = "SELECT id FROM stars WHERE stars.id = (SELECT planets.star_id FROM planets WHERE planets.id=".$regionId.")";
        $sql = "SELECT * FROM stars WHERE stars.id = (SELECT planets.star_id FROM planets WHERE planets.id=(SELECT regions.planet_id FROM regions WHERE regions.id =" . $regionId . "))";
        $star_raw = $this->db->fetch($sql);
        //sdebug::log($star_raw,'starman->findByRegionId');
        //2) Despues llamamos a init para obtener los datos, con esto nos aseguramos que iniciar la estrella desde cualquier lugar da lo mismo
        // Es mas lento pero por ahora funcionara correctamente
        if (empty($star_raw['id'])) {
            //debug::error('No se puede cargar la estrella a partir de la region [' . $regionId . ']', 'star.class.php');
            
            $star = null;
        } else {
            $star = $this->wrap($star_raw);
        }
        return $star;
    }
    


  public function initAll(){
    if(!empty($star_id)){
      $sql="SELECT p.*,(SELECT count(*) FROM regions r WHERE r.planet_id=p.id) AS total_regions FROM planets AS p WHERE p.star_id = ".$star_id." AND p.state = '"._X_PLANET_STATUS_ACTIVE."' ORDER BY p.orbit asc,p.order asc";
      $planets_raw = $this->db->vectorize($sql);
      if(empty($planets_raw)){
        debug::error('No se pueden cargar los planetas de la estrella id['.$star_id.']','planetman.class.php');
      }
      else{
        $this->raw=$planets_raw;
      } 
    }
    else{
        debug::error('La estrella id ['.$star_id.'] no tiene planetas','planetman.class.php');      
    }
    return $this->raw;
     
  }
  public function exist(){
    global $firephp;
    if(!$this->raw){
        debug::error('starman->noexiste');
      //$firephp->log('No existe');
      return false; 
    }
    else{
       return true; 
    }  
  }
  
  public function getPlanets(){
    if($this->exist()){
      return $this->raw;  
    }
    else{
      debug::error('No se ha inicializado la lista de planetas','planetman.class.php');
    }
    
  }
  
  
  
  
  public function getPlanetByStar($star_id){
    if(!empty($star_id)){
      $sql="SELECT p.*,(SELECT count(*) FROM regions r WHERE r.planet_id=p.id) AS total_regions FROM planets AS p WHERE p.star_id = ".$star_id." AND p.state = '"._X_PLANET_STATUS_ACTIVE."' ORDER BY p.orbit asc,p.order asc";
      $planets_raw = $this->db->vectorize($sql);
      if(empty($planets_raw)){
          $planets_raw = array();
        //debug::error('No se pueden cargar los planetas de la estrella id['.$star_id.']','planetman.class.php');
      }
      else{
        $this->raw=$planets_raw;
      } 
    }
    else{
        debug::warning('La estrella id ['.$star_id.'] no tiene planetas','planetman.class.php');      
    }
    return $this->raw;
     
  }
  
 
  
  public function createDt($data){
      $dt = new datatransfer();
      try{
          $sql = "INSERT INTO stars(id,name, color, state, axis_x, axis_y,desc ) VALUES (
          ".$data['id'].",'".$data['name']."','".$data['color']."','".$data['state']
          ."',".$data['axis_x'].",".$data['axis_y'].", '".$data['desc']."')";
          $dt = $this->db->insertDt($sql);
          debug::log($sql,'starman->creteDt');
          
      }
      catch(Exception $e){
          $dt->error($e->getMessage());
      }
      $dt->setData($data);
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
        $sql = "SELECT * FROM stars WHERE id = ".$id;
        return $sql;
    }
  
  
}

?>