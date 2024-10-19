<?php
  require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
/*******************************************************
 * battle: Objeto que contiene datos de una batalla
 ******************************************************/
  class battle extends identity
  {
    public function __construct()
    {
        parent::__construct();
    }
    
    public function postRefresh(){}
  
  //////////////////////////////////////////////////////////////////////////////////
  //METODOS inicializacion
  //////////////////////////////////////////////////////////////////////////////////
  /*********************************************************************************
  * nextTurn: Siguiente Turno
  *********************************************************************************/
  public function nextTurn($ext_turn=0){
    //debug::info('Empezo Next Turn['.$this->getTurn().'] BATALLA','nextTurn battle.class');
    //Verificamos que batalla este en modo batalla //Con lo q haya en memoria :S
    $battle_id = $this->getId();
    $phase = $this->getPhase();
    if($phase == _X_BATTLE_PHASE_BATTLE){
      
      
      $actual_turn = $this->getTurn();
      if(!empty($ext_turn)){
        $next_turn = $ext_turn;
        debug::warning('Se paso del turno['.$actual_turn.'] al nuevo turno['.$next_turn.']','battle->nextTurn');
      }
      else{
        $next_turn = $actual_turn + 1;  
      }
      
      $sql = "UPDATE battles SET turn = ".$next_turn." WHERE battles.id =".$battle_id;
      //debug::info($sql,'sql updatebattle nextTurn battle.class');
      $this->db->query($sql);
      
      /*$sql = "select * from armies WHERE armies.region_id =".$this->getRegionId()." AND armies.turn<".$actual_turn;
      $before = $this->db->vectorize($sql);
      debug::info($before,'BEFORE nextTurn battle.class');*/
      
      //PELIGROSISIMOOO!!!! esto lo deberia de hacer desde afuera, battle no deberia de tener acceso al cambio de armies
      $sql = "UPDATE armies SET turn = ".$actual_turn." WHERE armies.region_id =".$this->getRegionId()." AND armies.turn<".$actual_turn;
      //debug::info($sql,'sql updatearmies nextTurn battle.class');
      /*[WHY armies.turn<".$actual_turn] Porque es posible que
       * algun poder mueva a los armies a muchos turnos de 
       * distancia, entonces si pasamos de turno, solo las unidades
       * que esten en un turno menor deberian de moverse*/
      //debug::console($sql,'nextTurn battle.class');
      $this->db->query($sql);
      
      /*$sql = "select * from armies WHERE armies.region_id =".$this->getRegionId();
      $after = $this->db->vectorize($sql);
      debug::info($after,'OJO No ta seteado en la principal,deberia setearse con setDataDirty AFTER:armies nextTurn battle.class');*/
      
      /*$sql = "select * from battles WHERE region_id =".$this->getRegionId();
      $after = $this->db->vectorize($sql);
      debug::info($after,'OJO No ta seteado en la principal,deberia setearse con setDataDirty AFTER:battle nextTurn battle.class');*/
      
      
      //$this->initById($battle_id);//Reiniciamos la batalla porque a sido actualziada
      $this->setDataDirty();//Reiniciamos la batalla porque a sido actualizada
      //debug::info($this,'ObjetoBatalla nextTurn battle.class');
    }
    else{
      debug::warning('La batalla['.$battle_id.'] esta en fase['.$phase.'], deberia de estar en fase['._X_BATTLE_PHASE_BATTLE.']','battle nextTurn 44');
    }
  }
  
  /*********************************************************************************
  * startBattlePhase: Actualiza la Batalla a Inicio
  *********************************************************************************/
  public function startBattlePhase(){
    $region = $this->getRaw();
    $battle_id = $region['id'];
    $region_id = $region['region_id'];
    
    $sql = "SELECT id FROM battles WHERE region_id =".$region_id." AND phase ='"._X_BATTLE_PHASE_TACTIC."'";
    $raw = $this->db->fetch($sql);
    if(empty($raw)){
      debug::warning('No hay batalla en region['.$region_id.'] en fase Tactic que no necesite de estar actualizada');
    } 
    else{
     // debug::trace('Vamos a empezar la fase de Batalla','battle->startBattlePhase');
      $sql = "UPDATE battles SET phase = '"._X_BATTLE_PHASE_BATTLE."' WHERE battles.id =".$battle_id;
      $this->db->query($sql);
      $this->setDataDirty();//Reiniciamos la batalla porque a sido actualizada
       
    }
    
  }

  /*********************************************************************************
  * endBattlePhase: Termina la batalla
  *********************************************************************************/
  public function endBattlePhase(){
      $battle_id = $this->getId();
      $sql = "UPDATE battles SET phase = '"._X_BATTLE_PHASE_END."',end_battle = now() WHERE battles.id =".$battle_id;
      $this->db->query($sql);
      $this->setDataDirty();//Reiniciamos la batalla porque a sido actualizada
  }
  //////////////////////////////////////////////////////////////////////////////////
  //METODODS GETTERS
  //////////////////////////////////////////////////////////////////////////////////
  public function getPhase(){
    $raw = $this->getRaw();
    return $raw['phase'];
  }
  
  
  public function getRegionId(){
    $raw = $this->getRaw();
    return $raw['region_id'];
  }
  public function getTurn(){
    $raw = $this->getRaw();
    return intval($raw['turn']);
  }
   
  public function getBattleSeconds(){
    $raw = $this->getRaw();//[TODO] No deberia de obtenerse esta variable defrente, se desactualizarara si no se llama a acheck old vvalue
    return $raw['battle_seconds'];
  }
   //////////////////////////////////////////////////////////////////////////////////
  //METODODS PREGUNTAS
  ////////////////////////////////////////////////////////////////////////////////// 
     
     /**
      isActive*  Pregunta si la batalla esta activa.
     *
     * @return  Boolean : TRUE la batalla a terminado
     *                  : FALSE la batalla continua
     * @since   2012-Dic-27
     * @author  Jeeba <designbyjeeba@gmail.com>
     *
     * @edit    2012-Dic-26<br />
     *          Jeeba <designbyjeeba@gmail.com>
     *          Documentacion Inicial<br/>
     *          #edit1
     */
  public function isActive(){
      if($this->exist()){
          $state = $this->getPhase();
          if ($state == _X_BATTLE_PHASE_END) {
                return false;
            } else {
                return true;
            }
      }
      else{
          //debug::trace("Trace de Chekeo, para preguntar como diablos se inicio una batalla sin data, battle->isActive");
          //SI es posible, debido a que los man(), especialmente battleman->initByRegion devuelve una batalla
          //vacia si no encuentra nada.
          return false;
      }
      
      
      
  }

        public function prepareRaw() {
            $preparedRaw = $this->raw;
            /*foreach ($preparedRaw as $key => $value) {          
                unset($preparedRaw[$key]['map']);
            }*/
            return $preparedRaw;
        }
  
}//battle
  ?>
