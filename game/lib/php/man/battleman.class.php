<?php
 require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
 /****************************************************************
 * player: Clase para el manejo del jugador, esta en forma lazy 
 ****************************************************************/

class battleman extends identitymap
{
  //////////////////////////////////////////////////////////////////////////////////  
  //CAMPOS
  ///////////////////////////////////////////////////////////////////////////////////
  private static $instance;//Contiene la instancia unica de la base de datos  
  //Variables Internas  
  
  ///////////////////////////////////////////////////////////////////////////////////////
  //CONSTRUCTOR
  ///////////////////////////////////////////////////////////////////////////////////////  
  protected function __construct(){
    parent::__construct('battleman','battle','battles');     
  }
  
  //////////////////////////////////////////////////////////////////////////////////
  //PRINCIPALES
  //////////////////////////////////////////////////////////////////////////////////  
  public function &findByRegionId($region_id){
    //debug::warning('Battle buscado por region['.$region_id.']','findByRegionId battleman.php');
    //$sql = "SELECT *,time_to_sec2(battles.start_battle,now()) AS battle_seconds FROM battles WHERE battles.region_id=".$region_id." AND battles.phase !='"._X_BATTLE_PHASE_END."'";
    $sql = "SELECT *,time_to_sec2(battles.start_battle,now()) AS battle_seconds
        FROM battles WHERE battles.region_id=".$region_id."
            ORDER BY battles.start_battle DESC LIMIT 1";
    //debug::log($sql,'battleman->findByRegionID 1');
    //debug::warning('Battle buscado por region['.$region_id.']:'.$sql,'findByRegionId battleman.php');
    $battle_raw = $this->getFromPersistence($sql);
    if(!empty($battle_raw)){
      //debug::warning('Battle buscado por region['.$region_id.'] existe','findByRegionId battleman.php');
      $this->add($battle_raw['id'],$battle_raw);
      $obj = $this->wrap($this->raw[$battle_raw['id']]);
      //debug::warning($obj,'return de findByRegionId battleman.php');
      return $obj;
    }
    else{
      //debug::warning('Battle buscado por region['.$region_id.'] no existe','findByRegionId battleman.php');
      $battle = new battle();
      return $battle;
    }
  }

  //////////////////////////////////////////////////////////////////////////////////
  //PRINCIPALES
  //////////////////////////////////////////////////////////////////////////////////
  public function &findActiveByRegionId($region_id){
    //debug::warning('Battle buscado por region['.$region_id.']','findByRegionId battleman.php');
    //$sql = "SELECT *,time_to_sec2(battles.start_battle,now()) AS battle_seconds FROM battles WHERE battles.region_id=".$region_id." AND battles.phase !='"._X_BATTLE_PHASE_END."'";
    $sql = "SELECT *,time_to_sec2(battles.start_battle,now()) AS battle_seconds
        FROM battles WHERE battles.region_id=".$region_id." AND battles.phase!='"._X_BATTLE_PHASE_END."' ORDER BY battles.start_battle DESC LIMIT 1";
        debug::log($sql,'battleman->findActiveByRegionId 2');
    //debug::warning('Battle buscado por region['.$region_id.']:'.$sql,'findByRegionId battleman.php');
    $battle_raw = $this->getFromPersistence($sql);
    if(!empty($battle_raw)){
      //debug::warning('Battle buscado por region['.$region_id.'] existe','battleman->findActiveByRegionId ');
      $this->add($battle_raw['id'],$battle_raw);
      $obj = $this->wrap($this->raw[$battle_raw['id']]);
      return $obj;
    }
    else{
      //debug::warning('Battle buscado por region['.$region_id.'] no existe','battleman->findActiveByRegionId');
      $battle =  new battle();
      return $battle;
    }
  }

  public function defaultsql($id){
    //Las batallas funcionan aunque esten en END, si solo quieres buscar batallas activas usa la funcion findActiveByRegion
    $sql = "SELECT *,time_to_sec2(battles.start_battle,now()) AS battle_seconds FROM battles WHERE battles.id=".$id;
    debug::log($sql,'battleman->defaultsql 3');
    return $sql;
  }
 
  public function insert($region_id){
    $maybebattle = $this->findActiveByRegionId($region_id);
    //debug::error($maybebattle,'battleman->create->maybebattle');
    if(!$maybebattle->exist()){
      //debug::trace('Se esta creando una batalla en ['.$region_id.']','create battleman.class');      
      $id = $this->db->nextId('battles','id');
      //$sql="INSERT INTO battles(id,region_id,start_tactic,start_battle,turn,phase) VALUES (".$id.",".$region_id.",now(), now() + time '"._X_TACTIC_TIME."',"._X_BATTLE_INITIAL_TURN.",'"._X_BATTLE_PHASE_TACTIC."')";
      $sql = "
      INSERT INTO battles (id, region_id, start_tactic, start_battle, turn, phase) 
      VALUES (
          " . $id . ",
          " . $region_id . ",
          NOW(), 
          NOW() + " . _X_TACTIC_TIME . ", 
          " . _X_BATTLE_INITIAL_TURN . ",
          '" . _X_BATTLE_PHASE_TACTIC . "'
      )
  ";
  

      debug::log($sql,'battleman->create 4');
      $this->db->query($sql);
      
      $obj = $this->findById($id);
      return $obj;
    }
    else{
      //debug::warning('No se puede crear una batalla en ['.$region_id.'] porque ya existe una batalla actualmente en la region['.$region_id.']','create battleman.class');   
    }
  } 
 
  ///////////////////////////////////////////////////////////////////////////////////////
  //UTILITARIOS
  ///////////////////////////////////////////////////////////////////////////////////////  
  public static function singleton()//Obtiene la instancia unica de esta clase
  {
     if (!isset(self::$instance)) {
         $c = __CLASS__;
         self::$instance = new $c;
     }
     return self::$instance;
  }
 
  public function __clone() // Prevenimos que este objeto sea clonado
  {
     trigger_error('Clone is not allowed. battleman.class', E_USER_ERROR);
  }
}

?>