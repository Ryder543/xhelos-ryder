<?php
  require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad

/*********************************************************
 * movstate: Encargado de tener el estado de los mov
 ********************************************************/
  class movutil extends util
  {
      public $army;
      public $action;
      public $target;
      public $battle;
      public $region;
      public $player;
      public $report;
      public $dt;

      public function __construct($army,$action,$target,$battle){
        parent::__construct();
        debug::log($army,"movutil->constructor army");

        // Check if battle and army have the required IDs
      if (empty($battle)){
        debug::ERROR("movutil constructor Battle is Empty");
        debug::log($army,"movutil->constructor army");
        debug::log($action,"movutil->constructor action");
        debug::log($target,"movutil->constructor target");
        debug::log($battle,"movutil->constructor battle");
        debug::trace("movutil constructor");
        return; // Exit the constructor if either is empty
      }

      $regionId = $battle->getRegionId();
      if (empty($regionId)){
        debug::ERROR("movutil constructor RegionId is Empty");
        debug::log($army,"movutil->constructor army");
        debug::log($action,"movutil->constructor action");
        debug::log($target,"movutil->constructor target");
        debug::log($battle,"movutil->constructor battle");
        debug::trace("movutil constructor");
        return; // Exit the constructor if either is empty
      }
      $playerId = $army->getPlayerId();
      if (empty($playerId)) {
        debug::ERROR("movutil constructor PlayerId is Empty");
        debug::log($army,"movutil->constructor army");
        debug::log($action,"movutil->constructor action");
        debug::log($target,"movutil->constructor target");
        debug::log($battle,"movutil->constructor battle");
        debug::trace("movutil constructor");
        return; // Exit the constructor if either is empty
      }

      $this->army = $army;
      $this->action = $action;
      $this->target = $target;
      $this->battle = $battle;

      $this->region = $this->man("region")->findById($this->battle->getRegionId());
      $this->player = $this->man("player")->findById($this->army->getPlayerId());

      parent::__construct();
    }

    public function getArmy(){
        return $this->army;
    }
    public function getRegion(){
        return $this->region;
    }
    public function getTarget(){
        return $this->target;
    }
    public function getAction(){
        return $this->action;
    }
    public function getReport(){
        return $this->report;
    }
    public function getBattle(){
        return $this->battle;
    }
    public function getDT(){
        return $this->dt;
    }

    public function getTX(){
        return $this->getTarget()->getTX();
    }

    public function getTY(){
        return $this->getTarget()->getTY();
    }

    public function getOX(){
        //debug::error($this->army,'movstate->getOX army');
        return $this->getTarget()->getOX();
    }

    public function getOY(){
        return $this->getTarget()->getOY();
    }


    public function saveActionDT(){
      //$m = $this->temp;
      //$mov = pg_escape_string(utf8_encode(json_encode($this->getReport()->getRaw())));

      $mov = mysqli_real_escape_string(
        $this->db->getConnection(),
        mb_convert_encoding(json_encode($this->getReport()->getRaw()), 'UTF-8', 'auto')
    );

      //debug::log($mov,'movstate->saveActionDT');

      $id = $this->db->nextId('movements');
     // debug::warning($mov,'movement->saveTempActionTD->mov');
      //debug::warning($m,'movement->saveTempActionTD->m');
      $sql = "INSERT INTO movements(id,region_id,army_id,x_in,y_in,x_out,"
      ."y_out,time_in,action_id,state,movement_raw,battle_id,info) VALUES ("
      .$id.",".$this->getRegion()->getId().",".$this->getArmy()->getId().","
      .$this->getOX().",".$this->getOY().","
      .$this->getTX().","
      .$this->getTY().",now(),"
      .$this->getAction()->getId().",'"
      ._X_ACTIVE."','".$mov."',".$this->getBattle()->getId().",'".$this->getDT()->getText()."');";
      $this->db->query($sql);
      //debug::warning($m,'actionman->saveTempActionTD->m');
      //debug::trace($sql,'movstate->saveTempActionTD->s');
    }

    public function validateDT($army_actual,$action_id){
      // $validation = $this->actionman->validateTempDT($army_actual,$action_id);
       //return $validation;
        //$validation = $this->actionman->validateTempDT($army_actual,$action_id);
        $action_id = $this->getAction()->getId();
        $dt = new datatransfer();
        $dt->success('El Target es correcto');
        $ok = true;
        //1)Verificar que la accion exista
        $sql = "SELECT * FROM actions WHERE id = " . $action_id . " AND state = '" . _X_ACTION_STATE_ACTIVE . "'";
        $action_raw = $this->db->fetch($sql);
        if (empty($action_raw)) {
            //debug::warning('La accion con id['.$action_id.'] no existe o esta [I]nactiva','actionman->finByActionIdAndArmyId');
            $dt->error('La accion con id[' . $action_id . '] no existe');
            $ok = false;
        }

        //2)Verificar que le pertenezca a la unidad
        $actionTemp = $this->man("action")->findByActionIdAndArmyId($this->getArmy()->getId(), $action_id);
        if (($ok) AND !( $actionTemp->exist())) {
            //debug::warning('La accion con id['.$action_id.'] no existe en el army['.$army->getId().']','actionman->validateTempTD');
            $dt->error('La accion con id[' . $action_id . '] no existe en el army[' . $this->getArmy()->getId() . ']');
            $ok = false;
        }
        //3)Verificar que el target sea el correcto
        $dt_target = $this->getTarget()->typeIsValid($actionTemp->getTarget(),$actionTemp->getPosition());
        if (($ok) AND !($dt_target->isValid())) {
            //debug::warning($text,'actionman->validateDT');
            $dt = $dt_target;
            //debug::warning($dt,'actionman->validateDT->dt_target');
            //debug::warning($dt,'actionman->validateDT->dt');
            return $dt;
        }
        return $dt;


    }

    public function workActionDT(){
    //$m = array_merge( $this->temp,$this->getAction()->getRaw() );
    //debug::log($this,'movement->workActionDT this');
   // debug::log($this->temp,'movement->workActionDT temp');
    //debug::log($this->getAction()->getRaw(),'movement->workActionDT raw');
    //debug::log($m,'movement->workActionDT m');
    $action_id = $this->getAction()->getId();

    $this->report = new report();
    //$originArmy = $this->armyman->findBycoord($m['region_id'],$m['ox'],$m['oy']);
    $originArmy = $this->getArmy();
    $enemyArmy = $this->man("army")->findByCoord($this->getRegion()->getId(),$this->getTX(),$this->getTY());
    ///debug::log($enemyArmy,'movstate workActioDT enemyArmy');
    ////debug::log($this->man("army"),'movstate workActioDT getArmy');
    $dt = new datatransfer();
    $dt->success();

    //debug::log($action_id,'movement->workActionDT->action_id');
    //debug::log($dt,'movement->workActionDT->dt');
    /****************************************************
    * Movimientos Globales
    ****************************************************/
      //A)GLOBAL MOVIBLE:Si es Movible, mover las unidad
      //debug::info($this->actionTemp,'actionman->workTempActionTD->dt before global actionTemp');
      //debug::info($m,'actionman->workTempActionTD->dt before global m hay movable');
      //if($m['movable']==_X_SI){
      if($this->getAction()->isMovable()){
        //echo "ES recontra movible";
        if($enemyArmy->exist()){//[TODO]Exista en Coordenadas
          $dt->error('Unidad['.$this->army->getId().'] esta ocupando el territorio');
          //debug::info($dt,'actionman->workTempActionTD->dt inside global error unidad ocupa sitio');
        }
        else{
          if(!$originArmy->isInPosition(_X_POSITION_ORBIT)||$originArmy->isInPosition(_X_POSITION_ATERRIZAR)){//No se pueden mover unidades en el espacio
            //debug::log('totalx:'.$this->getRegion()->getParam('x').''.);
            $index = mapIndex($this->getRegion()->getParam('x'),$this->getTarget()->getOY(),$this->getTarget()->getOX());
            $originArmy->setPheromone($index);
            $originArmy->setCoord($this->getTX(),$this->getTY());

            //debug::info($dt,'actionman->workTempActionTD->dt inside global success se movio');
          }
          else{
            //Se esta moviendo una unidad en el espacio
            $originArmy->setActualAndNextCoord(_X_MOV_NOMOV,_X_MOV_NOMOV,_X_MOV_NOMOV,_X_MOV_NOMOV);
            $dt->$error('La Unidad esta en orbita');
            //debug::info($dt,'actionman->workTempActionTD->dt inside global unidad esta en el espacio');
            //[TODO] Dar Error de que no se puede mover una unidad en el espacio;
          }
        }
      }

      //debug::info($dt,'actionman->workTempActionTD->dt after global');

    /****************************************************
    * Movimientos Particulares
    ****************************************************/
    if($dt->hasSuccess()){
      switch($action_id){

        case 1://Mover la Unidad, army_id,x_in,y_in,x_out,y_out

          $this->report->addCauseMovement($originArmy->getId(),$this->getOX(),$this->getOY());
          $dt->success('El Army procede a moverse', 'success');
          //debug::info($dt,'actionman->workTempActionTD->dt after mov[1]');
          //$report->addCauseMovement($report_id,$m['army_id'],$m['x_out'],$m['y_out']);
          //$report->addMessage($report_id,"La unidad se movio a x:".$m['x_out']." y:".$m['y_out']);
        break;

        case 2://Atacar Unidad

          if(!$enemyArmy->exist()){
            $dt->error('Criatura Enemiga no existe en posicion ['.$this->getTX().']['.$this->getTY().']');
            break;
          }
          //[TODO] Se tiene que verificar que mi unidad ademas de existir, este en las coordenadas asignadas
          if(!$originArmy->exist()){
            //MANDAR RESPUESTA
            $dt->error('Criatura Mia no existe en posicion ['.$this->getOX().']['.$this->getOY().']');
            break;
          }
          if(($enemyArmy->exist())&&($originArmy->exist())){
            $dt = $this->man("army")->simpleAttackTD($originArmy,$enemyArmy,$this->getReport());//Envio del Report
            //$dt->success('El Army  a fijado su objetivo', 'success');
          }
        break;

        case 3://Arrollar

          if(!$enemyArmy->exist()){
            $dt->error('Criatura Enemiga no fue arrollada en posicion x:'.$this->getTX().' y:'.$this->getTY());
            break;
          }
          //[TODO] Se tiene que verificar que mi unidad ademas de existir, este en las coordenadas asignadas
          if(!$originArmy->exist()){
            $dt->error('Criatura Mia no fue arrollada en posicion x:'.$this->getOX().' y:'.$this->getOY());
            break;
          }
          if(($enemyArmy->exist())&&($originArmy->exist())){
            $dt = $this->man("army")->simpleAttack($originArmy,$enemyArmy,$this->getReport() );
          }
        break;

        case 4://Rango
        // debug::log('Entrando al rango','');
        if(!$enemyArmy->exist()){
            $dt->error('Army Enemiga no existe en posicion:'.$this->getTX().' y:'.$this->getTY());
            break;
          }
          //[TODO] Se tiene que verificar que mi unidad ademas de existir, este en las coordenadas asignadas
          if(!$originArmy->exist()){
            $dt->error('Mi Army no existe en posicion x:'.$this->getOX().' y:'.$this->getOY());
            break;
          }
          if(($enemyArmy->exist())&&($originArmy->exist())){
            $dt = $this->man("army")->simpleRangeAttackTD($originArmy,$enemyArmy,$this->getReport());
            //debug::log($dt,'movstate Ataque de rango');
          }
          break;

        case 5://Defenderse
            //$report2->addCauseUnit($report_id,$m['army_id'],'');
            $dt->success("La unidad se esta defendiendo del ataque");
        break;

        break;
        case 7: //Aterrizaje
          if($originArmy->isInPosition(_X_POSITION_ORBIT)){
            $originArmy->setPosition(_X_POSITION_SUPERFICIE);
            $this->report->addCauseMovement($originArmy->getId(),$this->getTX(),$this->getTY());
            $dt->success('La unidad aterrizo con exito');
          }
          else{
            $dt->error('El Army no pudo aterrizar');
          }


          break;
        case 8: //Orbiting
         if($originArmy->isInPosition(_X_POSITION_SUPERFICIE)){
            $originArmy->setPosition(_X_POSITION_ORBIT);//[TODO] cero significa que estara en la esquina

            $td->success("La unidad Orbito con exito");

          }
          else{
            $dt->error('El Army se prepara a salir de la region', 'success');
          }
         break;

        case 9: //Cañon Solar$enemyArmies = new armies();
          $enemyArmies = new armies();
          $enemyArmies->setRaw($this->man("army")->getArmiesInFrontOfArmy($originArmy,$this->getRegion()->getId(),$this->getTX(),$this->getTY()));
          //debug::log($enemyArmies,'movutil->workActionDT case:Artilleria enemyArmies');
          $damagedArmies = $enemyArmies->damageUnits($originArmy->getAttack()*2,$originArmy->getTimes());
          //debug::log($damagedArmies,'movutil->workActionDT case:Artilleria damagedArmies');
          //$report2->addDummy($report_id,$myArmy->getId());
          //$this->report->addEffectArmies($report_id,$damagedArmies);
          $this->report->addEffectArmies($damagedArmies);
          $dt->success('El Cañon repartio su daño a todas las unidades en su rango de ataque');
        break;

        case 10://Mover la Unidad rapidamente, army_id,x_in,y_in,x_out,y_out
          $this->report->addCauseMovement($originArmy->getId(),$this->getOX(),$this->getOY());
          $dt->success('La Unidad procede a moverse rapidamente', 'success');
          //debug::info($dt,'actionman->workTempActionTD->dt after mov[10]');
        break;

        case 11://Mover la Unidad lentamente, army_id,x_in,y_in,x_out,y_out
          $this->report->addCauseMovement($originArmy->getId(),$this->getOX(),$this->getOY());
          $dt->success('La Unidad procede a moverse lentamente', 'success');
          //debug::info($dt,'actionman->workTempActionTD->dt after mov[11]');
        break;
      }

    }
    else{
      debug::warning($dt,'actionman->workTempActionTD->no se entro al bucle particular');
    }

    if($dt->hasSuccess()){
      $this->dt= $dt;
      //debug::warning($this->temp,'actionman->workTempActionTD->temp');
      $battleutil = new battleutil();
      $battleutil->validateManaDT($originArmy->getId(),$action_id);
      
      $this->saveActionDT();
    }
    else{
        debug::error('Dt no success','actionman->workTempActionTD->temp No pudo ser aplicado');
        debug::log($dt,'actionman->workTempActionTD->temp');
    }
        return $dt;
    }



  }
?>
