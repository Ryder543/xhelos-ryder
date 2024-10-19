<?php 
  require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
    
/*********************************************************
 * regionstate: Encargado de tener el estado de la region 
 ********************************************************/
  class regionview extends view
  {
    ///////////////////////////////////////////////////////////////////////////////////////
    //PROPIEDADES
    ///////////////////////////////////////////////////////////////////////////////////////    
    private $ext_region_id;
    private $ext_player_id;
    private $region;
    private $planet;
    private $star;
    private $players;      
    private $db;     
    public $dt; //Aqui se almacenan llamadas anteriores hechas a $dt, si hay error no continuar
    private $actual_army;
    private $maputil;

    
    ///////////////////////////////////////////////////////////////////////////////////////
    //CONSTRUCTOR
    ///////////////////////////////////////////////////////////////////////////////////////    
    /********************************************
    * Constructor: De regionState
     * Params: 
     * $region_id: ID de la region que quiere mantener el estado
     * $player_id: ID del jugador actual de la region
     * EXTRA: Obtener la lista de arg $args = func_get_args(); 
    ********************************************/  
    public function __construct($region_id){
      $this->db = db::singleton();
      $this->ext_region_id = $region_id;
      $this->dt = new datatransfer();
      $this->actual_army = false;
      $this->maputil = $this->getRegion()->getCachedMapUtil();
      //debug::log($this->maputil,'regionstate cosntructor');
      //debug::trace('region_id['.$region_id.'] player_id['.$player_id.']','constructor regionstate.class.php');
      //debug::log($this,'constructor regionstate.class.php');
    }

    
    ///////////////////////////////////////////////////////////////////////////////////////
    //PRINCIPALES
    ///////////////////////////////////////////////////////////////////////////////////////        
    public function updateState(){
      $this->db->begin();
      $battleutil = new battleutil();
    
      //$battleutil->
      $dt = $battleutil->updateRegionMovementDt($this->getRegionId());
      if($dt->ok()){$dt = $battleutil->updatePlayerBattleStatusDt($this->getRegionId());}
      if($dt->ok()){$dt = $this->updateBattleInRegionDt();}//Solo actualiza unidades dentro de la region}
      if($dt->ok()){
          $dt->success('Esta en la region '.$this->getRegionName());
          //debug::log($dt,'regionstate->updateState commit');
          $this->db->commit();
      }
      else{
          //debug::log($dt,'regionstate->updateState rollback');
          $this->db->rollback();
      }
      return $dt;
    }
     
    /****************************************************
    * getRegionState(): Obtiene el estado de la region
    *****************************************************/ 
    public function prepareState(){
      $_xeno = array();
      
      //1) Obtener Player
      $_xeno['player']['username'] = $this->getPlayer()->getParam('username');
      $_xeno['player']['id'] = $this->getPlayer()->getId();
      $_xeno['player']['actual_energy'] = $this->getPlayer()->getEnergy();

      //2) Lista de Jugadores en el Planeta
      $_xeno['players'] = $this->getPlayers()->getState();
      //3) Info de la region
      $_xeno['region'] = $this->getRegion()->getState();
      $_xeno['region']['map'] = $this->getRegion()->getMap();
      //4) Info de los recursos
      $_xeno['resources'] = $this->getResources();
      //5) Lista de los armies
      $_xeno['armies'] = $this->getArmies();//Ummmm!!!
      //6) Lista de Colonias
      $_xeno['colonies'] = $this->getColonies()->getState();
      
      //7) Estado de la Batalla
      $battle = $this->getBattle();
      //debug::info($battle,'regionstate->getRegionState->getBattle');
      $_xeno['battle']['turn'] = $battle->getTurn();
      $_xeno['battle']['phase'] = $battle->getPhase();
      $_xeno['battle']['id'] = $battle->getId();
      $_xeno['battle']['sended'] = $this->initGoingToRegion();

      /****************************************************
       * BATALLAS
      ****************************************************/
      //8) Estado de los Jugadores en la batalla
      
      if($battle->getPhase()==_X_BATTLE_PHASE_TACTIC){
        $_xeno['battle']['player_status'] = $this->getPlayersStatus($battle->getId());
        $_xeno['battle']['armies_turn'] = array();
        $_xeno['battle']['battle_seconds'] = $battle->getBattleSeconds();

        //$_xeno['battle']['battle_seconds'] = 120;
      }
      elseif($battle->getPhase()==_X_BATTLE_PHASE_BATTLE){
        $_xeno['battle']['player_status'] = array();
        $_xeno['battle']['armies_turn'] = $this->getArmiesCycleTurn($battle->getId());
        $nextArmy = $this->getNextArmyInBattle();
        if(!$nextArmy->exist()){
            debug::error("NextArmy no existe pero se sigue en batalla",'regionstate->prepareState Batalla Fase Batalla');
        }
        else{
        $_xeno['battle']['battle_seconds'] = $nextArmy->getBattleSeconds();
        //debug::warning($_xeno['battle'],'regionstate->getRegionState Batalla Fase Batalla');
        //debug::warning($nextArmy,'regionstate->getRegionState Batalla Fase Batalla');
        $_xeno['movements'] = $this->getLastMovements();
        //debug::warning($this->actionman,'regionsstate->getRegionState->movement');
        //debug::warning($_xeno['movements'],'regionsstate->getRegionState->movements');
        $_xeno['movement'] = $this->getLastMovementOfPlayer($_xeno['movements']);
        }

        
      }
      elseif($battle->getPhase() == _X_BATTLE_PHASE_END){
        $_xeno['battle']['player_status'] = array();
        $_xeno['battle']['armies_turn'] = array();
        $_xeno['movements'] = $this->getLastMovements();
        //debug::warning($this->actionman,'regionsstate->getRegionState->movement');
        $_xeno['movement'] = $this->getLastMovementOfPlayer($_xeno['movements']);
        //debug::warning('La batalla finalizo','regionstate->getRegionState BatallaEnd?');
      }
      else{
        $_xeno['battle']['player_status'] = array();
        $_xeno['battle']['armies_turn'] = array();
        //debug::warning('La batalla no esta ni T ni B ni E','regionstate getRegionState 292');
      }
     return $_xeno;
    }

  public function initGoingToRegion(){
        $planet_id = $this->getPlanet()->getId();
        $sql = "SELECT time_to_sec2(travel.eta,now())as seconds,travel.to_region as to_region,armies.id,armies.size,armies.creature_id,armies.position,players.id as player_id ,players.username FROM travel
        INNER JOIN armies ON (travel.armies_id= armies.id)
        INNER JOIN players ON (armies.player_id = players.id)
        WHERE travel.state = '"._X_TRAVEL_STATE_ACTIVE."' AND travel.to_region = ".$this->getRegionId()." ORDER BY armies.player_id,armies.id";

        //debug::info($sql,'regionstate->initByPlanetGoingToRegion');
        $armies_raw = $this->db->vectorize($sql);
       // debug::info($armies_raw,'regionstate->initByPlanetGoingToRegion');

      if(empty($armies_raw)){
        //debug::warning('No existen armies en movimiento en planeta['.$planet_id.']');
      }
      else{
          return $armies_raw;
      }

  }

  public function getLastMovementOfPlayer($actual_movements){
      if(empty($actual_movements))
      {
          //debug::info('regionstate->getLastMovementOfPlayer actual_movements esta vacio');
          return false;
      }

      $last = reset($actual_movements);
      $last_id = $last['id'];

      $sql = "SELECT last_movement FROM battles_players WHERE player_id = ".$this->getPlayer()->getId()." AND battle_id=".$this->getBattle()->getId();
      //debug::log($sql);
      $player_last = $this->db->fetch($sql);
      $player_last_id = $player_last['last_movement'];

      if(empty($player_last)){
        debug::error('No existe un ultimo movimiento, lo cual es casi imposible','regionstate->getLastMovementOfPlayer');
        return false;
      }
      else{
          //Comparar si el movimiento actual es mayor al del player
          //debug::info('last_player_id['.$player_last_id.'] <>= last_id['.$last_id.']','regionstate->getRegionState->verificacion');
          if($player_last_id < $last_id ){
              $sql="UPDATE battles_players  SET last_movement = ".$last_id." WHERE player_id = ".$this->getPlayer()->getId()." and battle_id = ".$this->getBattle()->getId()."  ;";
              $this->db->query($sql);
              //debug::warning($sql,'regionstate->getRegionState->salvando ultimo movimiento');
              return $last;

          }
          else{
              //debug::info('regionstate->getLastMovementOfPlayer el ultimo movimiento de player es igual al ultimo id');
              return false;
          }
      }
  }
    public function getLastMovements(){
      $sql="SELECT id,info,region_id,army_id,action_id,battle_id,x_in,y_in,x_out,y_out,time_in,state, movement_raw
      FROM movements WHERE region_id = ".$this->getRegionId()." AND battle_id = ".$this->getBattle()->getId()." ORDER BY time_in desc  LIMIT "._X_MOVEMENT_LIMIT_VIEW;
      //debug::trace($sql,'regionstate->getLastMovement');
      $result_raw = $this->db->query($sql);
      $result = array();
      $index=0;
      while($raw = mysqli_fetch_assoc($result_raw)){
        //debug::warning($raw,'regionstate->getLastMovements');
          $result[$index] = $raw;
          $result[$index]['movement_raw'] = json_decode($result[$index]['movement_raw']);
          $index++;
      }
      //debug::log($result,'regionstate->getLastMovements.result');
      return $result;
    }
    
    
    /********************************************
    * render(): Imprime Mapa de Batalla
    ********************************************/ 
    public function render()//Obtener el Acceso a la Base de Datos
    {
      return $this->getRegion()->render();
    }

    ///////////////////////////////////////////////////////////////////////////////////////
    //GETTERS
    ///////////////////////////////////////////////////////////////////////////////////////

    /********************************************
    * getRegion: Obtener Region
    ********************************************/
    public function getRegion(){   
        if(empty($this->region)){
          $this->region = $this->man("region")->findById($this->getRegionId());
          if(empty($this->region))
          {
              debug::error('La region no pudo cargarse','getRegion regionstate.class.php');      
          } 
        }
        return $this->region;//[WHY] Evitar un bucle, por eso devolvemos region defrente
    }
    /********************************************
    * getRegionId: Obtiene el ID de la Region
    ********************************************/
    public function getRegionId(){
      if(!empty($this->ext_region_id))
      {
        return $this->ext_region_id;
      }
      else{
        debug::warning('El id de la region no existe ','getRegionId regionstate.class.php');
        return false;
      }
    }    
    /********************************************
    * getRegionName: Obtiene el Name de la Region
    ********************************************/   
    public function getRegionName(){
      return $this->getRegion()->getParam('name'); 
    }

    /********************************************
    * getPlanet: Obtener Planeta
    ********************************************/   
    public function getPlanet(){
      if(empty($this->planet)){ 
       $this->planet = $this->man("planet")->findById( $this->getRegion()->getPlanetId() );
       if(empty($this->planet))
        {
            debug::error('El planeta no se cargo','getPlanet regionstate.class.php'); 
        }
      }
      return $this->planet;    
    } 
    
   
    /********************************************
    * getStar: Obtiene la estrella de la Estrella
    ********************************************/  
    public function getStar(){
      if(empty($this->star)){
       $this->star = $this->man("star")->findByRegionId( $this->getRegionId() );
      }
      //debug::trace($this->star,'regionstate->getStar');
      return $this->star;
    }
 


    /************************************************
    * getPlayers: Obtiene los jugadores de la region
    ************************************************/ 
     private function getPlayers(){
      if(empty($this->players)){
        $this->players = new players();
        $this->players->initWithArmiesInRegion($this->getRegionId());
        if(empty($this->players))
        {
            debug::error('Los jugadores no se cargaron','getPlayers regionstate.class.php'); 
        }  
      }
      return $this->players;    
    }
     
    
    /************************************************
    * getResources: Obtiene los recursos por region
    ************************************************/ 
     private function getResources(){
         
      if(empty($this->regionsresources)){
                                      
        $this->regionsresources = new regionsresources();
        $this->regionsresources->initActiveByRegion($this->getRegionId());
        /*if(empty($this->regionsresources))
        {
            debug::error('Los jugadores no se cargaron','getPlayers regionstate.class.php'); 
        } */ 
      }
      return $this->regionsresources;    
    }   
    
         
    /********************************************
    * getBattle: Obtiene la Batalla
    ********************************************/
    public function getBattle(){
      if(empty($this->battle)){
        //debug::trace('getBattle va a obtener su data','getBattle regionstate.class.php');
        //debug::warning($this->battleman,'battleman getBattle regionstate.class.php');
          //debug::info('getBattle va a obtener una batalla de battleman en la region['.$this->getRegionId().']','regionstate->getBattle');
        $this->battle = $this->man("battle")->findByRegionId($this->getRegionId());   
        if(empty($this->battle)){//[TODO 27dic2012] Deberiamos de cambiarlo a this->exist?

          //debug::info('La batalla a pesar de la busqueda en battleman de la region['.$this->getRegionId().'] no pudo ser cargada','getBattle regionstate.class.php');
          $this->battle = new battle();
        }
        else{
          //debug::info('getBattle obtuvo de battleman una batalla en la region['.$this->getRegionId().']','regionstate->getBattle');
          //debug::log($this->battle,"regionstate->getBattle battle:");
        }
      }
      //debug::trace($this->battle->getRaw(),'regionstate->getBattle->return');
      return $this->battle;
    }
  /*  public function getDeathArmies(){
        $sql="SELECT * FROM armies WHERE region_id= ".$this->getRegionId()." AND state = '"._X_ARMY_STATE_DEAD."' ORDER BY id";
        $army_creature_raw=$this->db->query($sql);
        return $army_creature_raw;
    }*/
 
    /********************************************
    * getColonies: Obtiene las Colonias de la region indicada
    ********************************************/   
    public function getColonies(){
        $colonies = new colonies();
        $colonies->initActiveByRegionId($this->getRegionId());
        return $colonies;
    }
    
   /********************************************
    * getArmies: Obtiene los Armies de la region
    ********************************************/
    public function getArmies($movId=_X_MOV_NOMOV){

     $army_actual = $this->getNextArmyInBattle();

     //debug::trace($army_actual,'regionstate->getArmies->army_actual');
//      if(!$army_actual){
//          //Hay la posibilidad de que no haya ninguna unidad viva
//          $this->armies = $this->getDeathArmies();
//          return $this->amies;
//          //return false;
//      }
      //else{
          //debug::warning($army_actual,'regionstate->getArmies->army_actual');
          //(0.8)Obtencion de Jugadores
          $dt = new datatransfer();
          // $this->getPlayersInMap();

          //(2)Obtener Armies
          //[TODO] USar algo de armyman
          $this->armies = false;
          $sql="SELECT * FROM armies WHERE region_id= ".$this->getRegionId()." ORDER BY id";
          //debug::log($sql);
          $army_creature_raw=$this->db->query($sql);

          if($army_creature_raw)
          {
            while($army_creature = mysqli_fetch_assoc($army_creature_raw))
            {
               $oArmy = $this->man("army")->wrap($army_creature);
               $army_creature['is_actual'] = false;
               $army_creature['life_total'] = $oArmy->getTotalLife() ;
               $army_creature['max_life'] = $oArmy->getMaxLife() ;
               $army_creature['life_percent'] = $oArmy->getPercentLife();
               $army_creature['life_total_percent'] = $oArmy->getTotalPercentLife();
               $army_creature['life_residual'] = $oArmy->getResidualLife();
               $army_creature['energy_total_percent'] = $oArmy->getTotalPercentEnergy();
               $battleutil = new battleutil();
               $army_creature['allegiance'] = $battleutil->getAllegiance($oArmy->getId(),"army");
               if(($army_creature['player_id']==$this->getPlayer()->getId()) || _X_DEBUG_MODE){
                 // Si no existe army actual entonces es por gusto esto
                 if( ($army_actual && $army_actual->getId()==$army_creature['id']) || _X_DEBUG_MODE ) {
                   $army_creature['is_actual'] = true;
                   //(3) Obtener las Acciones de la Unidades solo del jugador
                    //debug::warning($this->getBattle(),'1regionstate->getArmies()');
                    //debug::warning($this->getBattle()->getId(),'2regionstate->getArmies()');
                     
                    //$actions_raw = $this->actionman->getArmyActionsByPosition($army_creature['id'],$army_creature['next_position']);
                    $actions = new actions();
                    $actions->initByArmyIdByArmyPosition($army_creature['id'], $army_creature['next_position']);
                    $actions_raw = $actions->getRaw();
                    if($actions_raw){
                      foreach($actions_raw AS $index => $action)
                      {
                        $action_id = $action['action_id'];
                        $army_creature['actions'][$action_id] = $action;
                        $startIndex = $this->maputil->mapIndex($army_creature['position_x'],$army_creature['position_y']);                       
                        $indexes = $this->maputil->getIndexesByPath($action['path'], $startIndex,$action['minrange'],$action['maxrange'],$army_creature['id']);
                        $army_creature['actions'][$action_id]['indexes'] = array();
                        foreach($indexes AS $i => $index ){
                            $army_creature['actions'][$action_id]['indexes'][$index] = array();
                            $army_creature['actions'][$action_id]['indexes'][$index]['x'] = $this->maputil->getXByIndex($index);
                            $army_creature['actions'][$action_id]['indexes'][$index]['y'] = $this->maputil->getYByIndex($index);
                        }
                        
                      }
                    }
                    
                  }
                }
              /////////////////(5) Obtener la velocidad
              $this->armies[$army_creature['id']] = $army_creature;
              $speed = $this->armies[$army_creature['id']]['speed'];
              $this->armies[$army_creature['id']]['speed']= str2array($speed);
              
            }
          }
          
          $dt->success('getArmies regionstate->Sucess Temporal');
          //debug::log($this->armies,'regionstate->getArmies');
          //return $dt;
          return $this->armies;
      //} else de death armies
    } 
  ///////////////////////////////////////////////////////////////////////////////////////
  //UTILITARIOS
  ///////////////////////////////////////////////////////////////////////////////////////
/* updateRegionArmiesTravel: Actualiza los viajes de region a region
 * 1 Obtenemos los armies que hayan llegado a alguna region
 * 2 Si hay, los preparamos para la batalla
 * 3 Obtenemos los players que necesiten ser actualizados
 * 4 Los preparamos para la fase tactica (inicial) pero si la batalla esta en fase batalla,
 * llamamos tambien a starBattlePhase
 * 5  Desactivamos los travel de las unidades
 */

  public function updateBattleInRegionDt(){
    $dt = new datatransfer();
    $dt->success('regionstate->updateBattleInRegionDt tempo');
    $region_id = $this->getRegionId();  
    $battle = $this->getBattle();

      if($battle->isActive()){
          //debug::warning($battle->getPhase(),'regionstate->updateBattleInRegion battle phase');
          switch($battle->getPhase()){
              case _X_BATTLE_PHASE_TACTIC:
                //debug::warning('Entre a la batalla en fase Tactica','regionstate->updateBattleInRegion tactic phase');
                $players = new players();
                $players->initByRegionByAliveArmies($region_id);
                $end= false;
                //Si paso el tiempo de tactica actualizamos phase a batalla y battles_players a batalla
                if($battle->getBattleSeconds()<1){
                  //debug::warning('Se termino la hora de la batalla tactica','regionstate->updateBattleInRegion tactic phase');
                  $players->startBattlePhase($region_id,$battle->getId());
                  $end=true;
                }
                //Si todos los jugadores aceptaron entrar en batalla
                elseif($players->areInBattlePhase($region_id,$battle->getId())){
                  //debug::warning('Todos los jugadores aceptaron el final de la fase','regionstate->updateBattleInRegion tactic phase');
                  $end=true;
                }
                //[WHY] Para no redundar en lo que se hace
                //debug::error('-------','4 before army BATTLE regionstate->nextArmy');
                if($end){
                  //debug::info('La Batalla pasa de tactic a battle','regionstate->UpdateBattleInRegion');
                  $battle->startBattlePhase();
                  
                  //[09ene2012]Iniciar a las unidades que entraran en batalla incialmente
                  $armies = new armies();
                  $armies->initActivesByRegion($region_id);
                  $armies->prepareForRegionBattleDt($this->getRegion(), $battle,$this->getPlanet());
                  //Actualizamos la primera unidad que entrara en batalla
                  //debug::error($army,'3 before army BATTLE regionstate->nextArmy');
                  //debug::error($army,'2 before army BATTLE regionstate->nextArmy');
                  $army = $this->getNextArmyInBattle();
                  //debug::error($army,'1 before army BATTLE regionstate->nextArmy');
                  //debug::error($army,'before army BATTLE regionstate->nextArmy');
                  //debug::error($army,'after army BATTLE regionstate->nextArmy');
                  if($army->exist())
                  {
                    //debug::error($army,'army->exist army BATTLE regionstate->updateBattleRegion');
                    $army->startLastUpdate();
                    //Expermiental: Llamar a BattlePhase
                  }
                  else{
                    debug::error($army,'army no existe BATTLE regionstate->nextArmy');
                    $dt->error('No existe algun armies disponible en el ciclo de turno [R001]');
                  }
                }

                //Si todos los jugadores aplicaron, actualizamos phase a batalla
                //if(!$end){break;}//Si termno la fase tactica pasamos a la fase de batalla
                break;

              case _X_BATTLE_PHASE_BATTLE:
                //Preguntamos si la batalla deberia de continuar
                if($this->battleHasEnded()){
                    $battle->endBattlePhase();
                }
                else{
                        //2) Preguntar el estado de la unidad que le toca el turno
                        $army = $this->getNextArmyInBattle();
                        //debug::log($army,'regionstate->UpdateBattleInRegion->BattlePhase');
                        
                        if(!$army->exist('battle_seconds')){
                          $last = $army->getBattleSeconds();
                          if($last<=0){
                              //debug::info($raw_actual,'se paso el turno['.$last.'] de la unidad['.$army->getId().'] BATTLE regionstate Obteniendo nextArmy');

                              $this->advanceBattleTurn();
                          }
                          else{
                            //debug::info('Last['.$last.'] de la unidad['.$army->getId().'] es mayor a cero aun','BATTLE regionstate Obteniendo nextArmy');
                          }
                        }
                        else{
                          if(!$army->exist()){//Unidad ya no existe, por algun motivo las unidades perdieron acceso al turno actual de la batalla, generalmente es por que alguna unidad que gana una mejor iniciativa

                            $this->advanceBattleTurn();//Avanzamos
                          }
                          else{
                            //debug::warning($army,'Last de la unidad['.$army->getId().'] no existe o es nulo, pero army si existe! BATTLE regionstate Obteniendo nextArmy');
                          }
                          //
                          //$this->sendError('No existe algun armies disponible en el ciclo de turno [R001]');
                        }
                }

             break;
    //          case _X_BATTLE_PHASE_END:
    //            //debug::error('Inicializacion Imposible, una batalla no puede inicializarse en Fase END','regionstate.class updateBattleInRegion 101');
    //            break;

             default:
                debug::warning('Inicializacion Imposible, Fase['.$battle->getPhase().'] no existe','regionstate.class uupdateBattleInRegionDt 536');
                break;
              }//END SWITCH

          }//END IF
        
      else{
       ////La batalla en nuevecita  o es una batalla terminada asi que la creamos aqui
       //debug::warning('SIN PHASE de batalla o batalla E','regionstate->updateBattleInRegion');
       $areEnemyPlayers = new players();
       $areEnemyPlayers->initByRegionByAliveArmies($region_id);
       if($areEnemyPlayers->exist()){//Si la batalla existe!
          if($areEnemyPlayers->areEnemies()){//Si son enemigos [TODO]
             //Inicializamos la batalla
             //$battle = &$this->battleman->createBattle($region_id);
             $this->battle = $this->man("battle")->insert($region_id);

             //debug::log($areEnemyPlayers,'regionstate->updateBattleInRegion Hay jugadores enemigos en la region');
             //debug::log($battle->getId(),'updateBattleInRegion2 regionstate');
             $areEnemyPlayers->startTacticalPhase($region_id,$this->getBattle()->getId());
             //Iniciamos a todos los armies que esten en la region actualmente
             $armies = new armies();
             $armies->initByRegion($region_id);
             if($armies->exist()){
               $region = $this->getRegion();
               $armies->prepareForRegionBattleDt($region,$this->getBattle(),$this->getPlanet());
             }
          }
          else{
            //debug::console('No hay jugadores enemigos en la region','regionstate updateBattleInRegion');
          }
       }
       else{
         //debug::console('No hay jugadores en la region','regionstate updateBattleInRegion');
       }
 
      }
      return $dt;
    }//end updataBattleInRegion
   
  /**************************************************************************
  * getArmyMovements: Obtiene todos los movemens de un army en especial
   * Params: 
   * $army_id: El ID de la unidad que se quiere obtener los datos
   * $region_id: El ID de la region que se quiere obtener los datos 
   * EXTRA: Obtener la lista de arg $args = func_get_args(); 
  ********************************************/    
  public function getArmyMovements($army_id,$region_id,$battle_id)
  {
    //$sql="SELECT now() as now,id,time_to_sec2(time_out,now()) AS clock,region_id,army_id,x_in,y_in,x_out,y_out,time_in,time_out,action_id 
    $sql="SELECT now() as now,id,time_to_sec2(time_in,now()) AS clock,region_id,battle_id,army_id,x_in,y_in,x_out,y_out,time_in,action_id,movement_raw
    FROM movements WHERE  region_id = ".$region_id." AND army_id = ".$army_id." AND battle_id = ".$battle_id." AND state IN('N','A','D')";
    //debug::warning($sql,'regionstate->getArmyMovements');
    return $sql;          
  }  

   
    private function getPlayersStatus($battle_id){
      $sql= "SELECT * FROM battles_players WHERE battle_id = ".$battle_id." AND region_id = ".$this->getRegionId()." AND state= '"._X_BATTLE_PLAYER_STATE_ALIVE."' AND phase !='"._X_BATTLE_PLAYER_PHASE_END."' ";
      //debug::log($sql);
      $raw = $this->db->vectorize($sql,'player_id');
      //[TODO] algun tipo de validacion??
      return $raw;
    }
    public function getMaxInitiative(){
      //[TODO]Almacenar Max
      $region_id = $this->getRegionId();
      $sql ="SELECT MAX(initiative) as max FROM armies WHERE armies.region_id = ".$region_id." AND armies.state = '"._X_ARMY_STATE_ALIVE."'";
      $max = $this->db->fetch($sql);
      $max = $max['max'];
      //debug::trace($max,'regionState->getMaxInitiative max');
      //debug::log($sql,'regionState->getMaxInitiative sql');
      if(empty($max)){
          return false;
      }
      return $max;  
    }
    
    private function getArmiesCycleTurn($battle_id){
      $battle = $this->getBattle();
      $turn = $battle->getTurn();

      $region_id = $this->getRegionId();

      $max = $this->getMaxInitiative();

      //Si Max esta vacio entonces no existen unidades, todas murieron, terminar la batalla
      if(!$max){
        $armiesTurn =false;
      }
      else{
        //$sql ="SELECT id,region_id,player_id,turn,initiative FROM armies WHERE armies.state ='"._X_ARMY_STATE_ALIVE."' AND armies.region_id = ".$region_id." AND TRUNC(".$turn."*(CAST(initiative as double precision)/".$max.")) > TRUNC(".($turn-1)."*(CAST(initiative as double precision)/".$max.")) AND armies.turn = ".($turn-1)." ORDER BY (".$turn."*initiative)%".$max." desc,initiative asc,id LIMIT "._X_ITF_REGION_LIMIT_TURNS;
        $sql = "
        SELECT id, region_id, player_id, turn, initiative 
        FROM armies 
        WHERE armies.state = '" . _X_ARMY_STATE_ALIVE . "' 
          AND armies.region_id = " . intval($region_id) . " 
          AND FLOOR(" . intval($turn) . " * (CAST(initiative AS DECIMAL(10, 2)) / " . intval($max) . ")) > 
              FLOOR(" . intval($turn - 1) . " * (CAST(initiative AS DECIMAL(10, 2)) / " . intval($max) . ")) 
          AND armies.turn = " . intval($turn - 1) . " 
        ORDER BY (" . intval($turn) . " * initiative) % " . intval($max) . " DESC, initiative ASC, id 
        LIMIT " . _X_ITF_REGION_LIMIT_TURNS;
    


        debug::log($sql,'regionState->getArmiesCycleTurn sql 1');
        $armiesTurn = $this->db->vectorize($sql,false);//Ordenados por orden de mecha
        
        if(empty($armiesTurn)){
            debug::error('Los turnos del ciclo['.$turn.'] de la batalla en region['.$region_id.'] estan vacios!','regionstate getArmiesCycleTurn 623');
            return false;
        }
        else{
            $count = count($armiesTurn);
            $tempo = 0;
            $newTurn= $turn;
            while($count<_X_ITF_REGION_LIMIT_TURNS){
              $newTurn++;
              //$sql ="SELECT id,region_id,player_id,turn,initiative FROM armies WHERE armies.state ='"._X_ARMY_STATE_ALIVE."' AND armies.region_id = ".$region_id." AND TRUNC(".$newTurn."*(CAST(initiative as double precision)/".$max.")) > TRUNC(".($newTurn-1)."*(CAST(initiative as double precision)/".$max.")) ORDER BY (".($newTurn)."*initiative)%".$max." desc,initiative asc,id LIMIT ".(_X_ITF_REGION_LIMIT_TURNS-$count);
              $sql = "
              SELECT id, region_id, player_id, turn, initiative 
              FROM armies 
              WHERE armies.state = '" . _X_ARMY_STATE_ALIVE . "' 
                AND armies.region_id = " . intval($region_id) . " 
                AND FLOOR(" . intval($newTurn) . " * (CAST(initiative AS DECIMAL(10, 2)) / " . intval($max) . ")) > 
                    FLOOR(" . intval($newTurn - 1) . " * (CAST(initiative AS DECIMAL(10, 2)) / " . intval($max) . ")) 
              ORDER BY (" . intval($newTurn) . " * initiative) % " . intval($max) . " DESC, initiative ASC, id 
              LIMIT " . (intval(_X_ITF_REGION_LIMIT_TURNS) - intval($count));

              //debug::log($sql,'regionState->getArmiesCycleTurn sql 2');
              $raw = $this->db->vectorize($sql,false);
              $armiesTurn = array_merge($armiesTurn,$raw);
              $count = count($armiesTurn);

              //debug::console($count,'Cuenta actual del turno['.$newTurn.']');
              //Nos aseguramos que no existan mas de 100 turnos,esto es casi imposible,
              //asi que mandaremos un console:error;
              $tempo++;
              if($tempo==_X_ITF_EMERGENCY_BREAK){
                debug::error('Se ha pasado las '._X_ITF_EMERGENCY_BREAK.' iteracion','regionstate getArmiesTurn');
                break;
              }
            }
        }
        //Obtener
      }
      //debug::log($armiesTurn,'regionState->getArmiesTurn sql');
      return $armiesTurn;
    }    
   
  /**********************************************************************
  * advanceBattleTurn: Avanzamos a la siguiente unidad que le toque turno
   * $dontPass: Si es verdadero entonces no se pasa su turno, esto porque
   * quizas la unidad actual a muerto y la siguiente unidad que toma su posta
   * es pasada y no se usa
  **********************************************************************/ 
  public function advanceBattleTurn(){
    if($this->battleHasEnded()){

        //debug::log('Ya no hay mas Turnos','regionstate->advanceBattleTurn');
    }
    else{
        //Pasamos el turno de la unidad actual, si no existe
        //es porqeu un poder a movido las iniciativas, y existe una iniciativa
        //tan grande que desplazo a la que deberia de ser la siguiente unidad
        $army_actual = $this->getNextArmyInBattle();
        if($army_actual->exist()){
            $army_actual->passNextTurn();
            $this->actual_army = false;
        }
        else{

        }

        //Preguntamos si es necesario pasar de turno


        $turn = $this->getBattle()->getTurn();

        if($this->areAnyArmiesForBattleTurn($turn)){
        }
        else{
          $this->getBattle()->nextTurn();
        }
        //Obtenemos la siguiente unidad y la inicializamos para el siguiente turno
        $army_siguiente = $this->getNextArmyInBattle();
        //debug::warning($army_siguiente,'regionstate->advanceBattleTurn army_siguiente');
        $army_siguiente->startLastUpdate();
    }
  }  
   
  public function getNextArmyInBattle(){
      if($this->actual_army && $this->actual_army->exist()){
          //debug::trace($this->actual_army,'regionstate->getNextArmyInBattle army del cache');
          return $this->actual_army;
      }
      else{
           //debug::info($this->getBattle(),'beforegetturn getNextArmyInBattle regionstate');
          $turn = $this->getBattle()->getTurn();
          //debug::info($this->getBattle(),'aftergetturn getNextArmyInBattle regionstate');
          //debug::trace('turno['.$turn.']','getNextArmyInBattle regionstate');
          $region_id = $this->getRegionId();
          $max = $this->getMaxInitiative();
          //debug::info($max,'regionstate->getNExtArmyInBattle max');
          if(empty($max)){
              //No existe ningun army en la region
              //debug::error($max,'regionstate->getNextArmyInBattle max no existe');
              return false;
          }
          //$sql ="SELECT time_to_sec2(last_update + INTERVAL '"._X_BATTLE_TIME."',now()) AS battle_seconds,* FROM armies WHERE  armies.state ='"._X_ARMY_STATE_ALIVE."' AND armies.region_id = ".$region_id." AND TRUNC(".$turn."*(CAST(initiative as double precision)/".$max.")) > TRUNC(".($turn-1)."*(CAST(initiative as double precision)/".$max.")) AND armies.turn = ".($turn-1)." ORDER BY (".$turn."*initiative)%".$max." desc,initiative asc,id LIMIT 1";
          //$sql ="SELECT * FROM armies WHERE  armies.state ='"._X_ARMY_STATE_ALIVE."' AND armies.region_id = ".$region_id." AND TRUNC(".$turn."*
          //(CAST(initiative as double precision)/".$max.")) > TRUNC(".($turn-1)."*(CAST(initiative as double precision)/".$max.")) 
          //AND armies.turn = ".($turn-1)." ORDER BY (".$turn."*initiative)%".$max." desc,initiative asc,id LIMIT 1";
         /* $sql ="SELECT * 
          FROM armies 
          WHERE armies.state = _X_ARMY_STATE_ALIVE 
            AND armies.region_id = ".$region_id."
            AND FLOOR(".$turn." * (CAST(initiative AS DOUBLE) / ".$max.")) > FLOOR(".($turn-1)." * (CAST(initiative AS DOUBLE) / ".$max.")) 
            AND armies.turn = ".($turn-1)." 
          ORDER BY (".$turn." * initiative) % ".$max." DESC, initiative ASC, id 
          LIMIT 1";*/

          $sql = "
          SELECT * 
          FROM armies 
          WHERE armies.state = '"._X_ARMY_STATE_ALIVE."' 
            AND armies.region_id = " . intval($region_id) . " 
            AND FLOOR(" . intval($turn) . " * (CAST(initiative AS DECIMAL(10, 2)) / " . intval($max) . ")) > 
                FLOOR(" . intval($turn - 1) . " * (CAST(initiative AS DECIMAL(10, 2)) / " . intval($max) . ")) 
            AND armies.turn = " . intval($turn - 1) . " 
          ORDER BY (" . intval($turn) . " * initiative) % " . intval($max) . " DESC, 
                    initiative ASC, 
                    id 
          LIMIT 1";

          

          //debug::log($sql,'regionState->getArmiesCycleTurn sql 3');
          //debug::error($sql,'regionstate->getNextArmyInBattle battleseconds sql');
          $army_raw = $this->db->fetch($sql,false);
          //debug::warning($army,'getNextArmyInBattle regionstate');
          $army = $this->man('army')->wrap($army_raw);
          $this->actual_army = $army;
          //debug::trace($this->actual_army,'regionstate->getNextArmyInBattle army dela persistencia');
          return $this->actual_army;
      }

    }  

   //////////////////////////////////////////////////////////////////////////////////
  // BOOLEANOS
  //////////////////////////////////////////////////////////////////////////////////
  /********************************************************
  * areAnyArmiesForBattleTurn: Existen Armies en este turno
  ********************************************************/
  public function areAnyArmiesForBattleTurn($turn){
    $battle = $this->getBattle();
    $max = $this->getMaxInitiative();
    //[MARK] Finalizacion de la Batalla porque max es falso
    if(!$max){
        return false;
    }
    else{
        //$sql="SELECT * FROM armies WHERE state ='"._X_ARMY_STATE_ALIVE."' AND region_id =".$battle->getRegionId()." AND turn =".($turn-1)." AND TRUNC(".$turn."*(CAST(initiative as double precision)/".$max.")) > TRUNC(".($turn-1)."*(CAST(initiative as double precision)/".$max."))";
        $sql = "
        SELECT * 
        FROM armies 
        WHERE state = '" . _X_ARMY_STATE_ALIVE . "' 
        AND region_id = " . $battle->getRegionId() . " 
        AND turn = " . ($turn - 1) . " 
        AND FLOOR(" . $turn . " * (CAST(initiative AS DECIMAL(10,2)) / " . $max . ")) > FLOOR(" . ($turn - 1) . " * (CAST(initiative AS DECIMAL(10,2)) / " . $max . "))
    ";
    
        debug::console($sql,'areAnyArmiesInBattleTurn regionstate 421');
        $raw = $this->db->vectorize($sql);
        //debug::console($raw,'areAnyArmiesInBattleTurn regionstate 423');
        if(!empty($raw)){
          return true;
        }
        else{
          return false;
        }

    }
  }
  public function isBattleActive(){
      if(!$this->getBattle()->exist()){
         // debug::log('La batalla no existe, esta inactiva','regionstate->isBattleActive');
          return false;
      }
      elseif($this->battleHasEnded()){
          //debug::log('La batalla a terminado, esta inactiva','regionstate->isBattleActive');
          return false;
      }
      else{
          //debug::log('La batalla no a terminado, es activa','regionstate->isBattleActive');
          return true;
      }
  }

  /* Verifica que la batalla actual a terminado, verificando */
  public function battleHasEnded(){
      // 1) Preguntamos si existe alguna unidad, getMaxInitiative devuelve false si no existe inciiativa
      if($this->getMaxInitiative()){
          //2) Preguntamos si las unidades son del mismo jugador
          $armies = new armies();
          $armies->initActivesByRegion($this->getRegionId());
          if($armies->areEnemies()){
            //debug::log('La batalla no ha terminado aun','regionstate->battleHasEnded');
            return false;
          }
          else{
              //debug::log('La batalla ha terminado con un ganador','regionstate->battleHasEnded');
              return true;
          }
      }
      else{
          //debug::trace('La batalla ha terminado en empate','regionstate->battleHasEnded');
          return true;
      }
  }
  
  /********************************************************
  * sendError
  ********************************************************/
  public function sendError($msg){
    $this->dt->error($msg);
  }
  public function getDt(){
      return $this->dt;
  }
  
  /********************************************************
  * validateStaeDT: Validamos, que validamos?
  ********************************************************/  
  public function validateStateDt(){
        $dt = new datatransfer();
        $dt->success("[TODO]Validar el estado de la region de alguna manera");
        //$dt->success():
        //debug::warning("[TODO]Validar el estado de la region de alguna manera","regionstate->validateStateDt");    
        return $dt;
        //Verificar que la colonia le pertenezca al jugador
    }

    public function getTitle() {
        return "Region ".$this->getRegion()->getName();
    }
    
} 
?>