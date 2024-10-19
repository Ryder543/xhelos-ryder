<?php
require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Segurida
/*******************************************************
 * armyman: Clase para el manejo de armies 
 ******************************************************/
 class aibattleutil extends util{
	////////////////////////////////////////////////////////////////////////////////// 
  //Variables
  
  ///////////////////////////////////////////////////////////////////////////////////
  //Variables Internas
  private $region;
  private $battle;

  private $actual_unit;

  private $maputil;
  private $movementman;
  private $actionman;
  
  //////////////////////////////////////////////////////////////////////////////////
  //Metodos
  //////////////////////////////////////////////////////////////////////////////////
  /*********************************************************************************
  * army: constructor
  *********************************************************************************/
  public function __construct($region,$actual_unit,$battle=null){
    $this->region = $region;
    if(isset($actual_unit) && ($actual_unit != false) ){
        $player_id = $actual_unit->getPlayerId();
        $this->actual_unit = $actual_unit;
    }
    //$playerman = playerman::singleton();
    //$this->player = $playerman->findById($player_id);
    //$this->armyman = armyman::singleton();
    //$this->actionman = actionman::singleton();
    
    
    //$this->maputil = new maputil($region->getMap(),$region->getParam('x'),$region->getParam('y'),$region->getId());
    $this->maputil = $this->region->getCachedMapUtil();
    //debug::log($this->maputil,'aibattleutil constructor');
    $this->battleutil = new battleutil();
    //$this->movementman = movementman::singleton();
    $this->battle = $battle;
    
    parent::__construct();
  }


  public function getMovement(){
    $value = $this->enemyMap();
      return $value;//Siempre retorna algo
  }

  public function getActualArmy(){
      return $this->actual_unit;
  }
  private function getMovableActions($armyId = 0 ){
       if($armyId ==0){
            $army = $this->getActualArmy();
        }
        else{
            $army = $this->man("army")->findById($armyId);
        }
        $player = $this->man("player")->findById($army->getPlayerId());
        
        //$actions = $this->man("action")->getArmyActionsByPosition($army->getId(),$army->getActualPosition());
        $actions = new actions();
        $actions->initByArmyIdByArmyPosition($army->getId(), $army->getActualPosition());
        $possibleActions = array();
        $actions_raw = $actions->getRaw();
        foreach($actions_raw as $index=> $action_raw){
            $action = $this->man("action")->wrap($action_raw);
            $dt = $this->battleutil->validateManaDT($army->getId(),$action->getId(),false);
            if($dt->isValid()){
                //Preguntamos si este movimiento sirve para atacar
                
                if($action->isMovable()){
                    $possibleActions[$index] = $action;
                    unset($action_raw);//Importantisimo, con esto ya no ocurre el bug de que
                    //al siguiente foreach, action_raw se convertia en el raw que
                    //conservava la accion
                    
                }                
            }
            else{
                debug::warning('1 Se acabo el mana','aibattlestate->getActiveActions');
            }            
        }

        if(count($possibleActions)==0){
            //debug::warning('No tiene existen movientos de accion','aibattlestate->getMovableActions');
        }

        //debug::warning($player->getEnergy(),'aibattlestate->getMovableActions energia');
        return $possibleActions;
  }

  private function getActiveActions($armyId = 0 ){
        if($armyId ==0){
            $army = $this->getActualArmy();
        }
        else{
            $army = $this->man("army")->findById($armyId);
        }
        $player = $this->man("player")->findById($army->getPlayerId());
        //$actions = $this->man("action")->getArmyActionsByPosition($army->getId(),$army->getActualPosition());
        $actions = new actions();
        $actions->initByArmyIdByArmyPosition($army->getId(), $army->getActualPosition());
        $actions_raw = $actions->getRaw();
        $possibleActions = array();
        foreach($actions_raw as $index=> $action_raw){
            $action = $this->man("action")->wrap($action_raw);
            $dt = $this->battleutil->validateManaDT($army->getId(),$action->getId(),false);
            if($dt->isValid()){
                //Preguntamos si este movimiento sirve para atacar
                
                if(!$action->isMovable()){
                    $possibleActions[$index] = $action;
                    unset($action_raw);//Importantisimo, con esto ya no ocurre el bug de que
                    //al siguiente foreach, action_raw se convertia en el raw que
                    //conservava la accion
                }                
            }
            else{
                debug::warning('2 Se acabo el mana','aibattlestate->getActiveActions');
            }            
        }

        if(count($possibleActions)==0){
            //debug::warning('No tiene existen movientos de accion','aibattlestate->getActiveActions');
        }

        //debug::warning($player->getEnergy(),'aibattlestate->getActiveActions energia');
        return $possibleActions;
  }

  /************************************************************************************************
   *obtainObjetivesByTargetType Obtiene todos los objetivos en una region dependiendo del
   * tipo de target que maneje la accion actual
  ****************************************************************************************************** */
  public function obtainObjetivesByTargetType($oAction,$armyId=null){
      //[TODO]Crear una interface llamada objetivo, y devolver objetivos!, terrenos
      //unidades y construcciones pueden ser objetivos
      $army = 0;
      if(empty($armyId)){
          $army = $this->getActualArmy();
      }
      else{
          $army = $this->man("army")->findById($armyId);
      }
      
      if($oAction->targetIs(_X_TARGET_ENEMYANDARMY)){
            $armiesObjetive = new armies();
            $armiesObjetive->initByAliveEnemiesOfPlayerInRegion($army->getPlayerId(),$this->region->getId());
            //debug::log($armiesObjetive,'aibattlestate->obtainObjectivesByTargetType');
            return $armiesObjetive;
            
        }
        elseif($oAction->targetIs(_X_TARGET_TERRAIN)){
            return $this->travelMap($armyId);
            debug::warning($oAction,'Los targets de tipo TERRAIN aun no estan diseñados');
        }
        else{
            //debug::log($oAction,'aibattlestate->decideMovement');
            
            
            debug::error('Esto TARGET['.$oAction->getTarget().'] no esta desarrollado aun','aibattlestate->decideMovement');
            return false;
        }
  }


   public function decideMovement($returnMap=false){//pt = potential field
    $possibleActions = $this->getActiveActions();

    if(!empty($possibleActions) ){

        //debug::log($possibleActions,'aibattlestate->decideMovement possibleActions');
        $newmap = '';
        $targets_array = array();
        foreach($possibleActions as $index => $oAction){
            $armiesObjetive = $this->obtainObjetivesByTargetType($oAction);
            //debug::log($armiesObjetive,'aibattlestate->decideMovement armiesObjetive');
            $startIndex = $this->maputil->mapIndex($this->getActualArmy()->getX(),$this->getActualArmy()->getY());
            $possibleIndexes = $this->maputil->getIndexesByPath($oAction->getPath(), $startIndex,$oAction->getMinRange(),$oAction->getMaxRange(),$this->getActualArmy()->getId());
            //debug::log($possibleIndexes,'aibattlestate->decideMovement possibleIndexes');
            $quantaindex = array();
            foreach($possibleIndexes as $possibleIndex){
                $target = new target($this->region->getId(),$oAction->getId(),$this->getActualArmy()->getX(),$this->getActualArmy()->getY(),$this->maputil->getXByIndex($possibleIndex), $this->maputil->getYByIndex($possibleIndex));
                $quanta = $this->battleutil->calculateQuanta($possibleIndex, $oAction,  $armiesObjetive, $this->maputil);
                $target->setQuanta($quanta);
                $targets_array[] = $target;
                if($quanta==100){$quantaindex[] = $possibleIndex;}
            }
            $newmap = $this->maputil->fillMapWithValue($quantaindex,100);
       }
       $selected_target='';
       foreach($targets_array AS $target){
         if($selected_target ==''){
             //debug::log('vacio');
             $selected_target = $target;
         }
         else if($target->getQuanta() > $selected_target->getQuanta()){
                 $selected_target = $target;
         }
       }
       //debug::warning($selected_target,'aibattlestate->decideMovement target seleccionado');

   ////////////////////////////////////////////////////////////////////////////////
   //Si no hay un accion interesante para esta unidad la movemos de locacion
   ////////////////////////////////////////////////////////////////////////////////
   //debug::log($selected_target,'aibattlestate->decideMovement PreifQuanta');
   if( $selected_target->getQuanta() < 1){
          //Obtenemos el Movimiento Movible objetivo
          $movActions = $this->getMovableActions();
          $oAction ='';
          if( count($movActions) == 1 ){
              $oAction = $movActions[1];
          }
          else{
              //[TODO] Iteramos entre los movimientos movibles para descubrir cual es el mas util
              debug::error('Aun no hemos implementado para ['.count($movActions).']movimientos movibles','aibattlestate->decideMovement');
          }
          
          $movMap = $this->fullMap($selected_target->getActionId());
          $newmap = $movMap;
          $unit = $this->getActualArmy();
          $unit_index = $this->maputil->mapIndex($unit->getX(),$unit->getY());
          //$tiles = $this->maputil->getTilesAtRange($unit_index,1);//Esto es para mvimiento 1
          //$tiles = $this->maputil->getIndexesByPath(_X_ACTION_PATH_MOV, $unit_index,1,1,$unit->getId());
          $tiles = $this->maputil->getIndexesByPath($oAction->getPath(), $startIndex,$oAction->getMinRange(),$oAction->getMaxRange(),$this->getActualArmy()->getId());
          $possibleMovs = array();
          //debug::log($tiles,'aibattlestate tiles');
          foreach( $tiles as $index => $tile){
              $possibleMovs[$tile] = $movMap[$tile];
          }
          //debug::log($possibleMovs,'aibattlestate->possibleMovs');
          $maximus = max($possibleMovs);//Obtenemos el maximo lugar para moverse
          $where = array_search($maximus,$possibleMovs);
          //debug::log($where,'aibattlestate->where to move');
          //$this->maputil->debug();
          $ox = $this->maputil->getXByIndex($unit_index);
          $oy = $this->maputil->getYByIndex($unit_index);
          $tx = $this->maputil->getXByIndex($where);
          $ty = $this->maputil->getYByIndex($where);
         // debug::log($oy,'aibattlestate->oy');

          if(!$returnMap){
              $target = new target($this->region->getId(),1,$ox,$oy,$tx,$ty);
              //$movement = $this->man("movement")->create($this->battle->getId(),$target);
              $action = $this->man("action")->findById(1);//TODO hacerlo solo si la unidad tiene movimientos
              $mov = new movutil($unit,$action,$target,$this->battle);
          }
      }
      else{
          $action = $this->man("action")->findById($selected_target->getActionId());//TODO hacerlo solo si la unidad tiene movimientos
          $mov = new movutil($this->getActualArmy(),$action,$selected_target,$this->battle);
          //$movement = $this->man("movement")->create($this->battle->getId(),$selected_target);
      }
      
      if($returnMap){
        //debug::log('retornando mapa','aibattlestate');
        return $newmap;
      }
      else{
        //debug::log('retornando accion','aibattlestate');
        return $mov;
      }
    }
    else{
        return false;
    }

}


/*[TODO] enemymap a objtive map*/
  public function movementMap($enemymap){
      
       $armiesMap = $this->obstacleMap(); //Para no movernos donde estan nuestros amigos
       $travelmap =$this->travelMap(); // Para no movernos donde estamos nosotros
  

       
        $enemymap =$this->enemyMap();

        $newmap = array();


        foreach($enemymap as $index=>$tile){
            if($travelmap[$index]==0){
                $newmap[$index]=0;
            }
            elseif($enemymap[$index]==100){
                $newmap[$index] = 100;
            }
            else{
                //El objetivo es que travelmap no sobrepase los 25 puntos
                //para que no parcialice totalmente todo
                $tot = (($travelmap[$index])/10+$enemymap[$index])/2;
                $newmap[$index] = floor($tot);
            }
        }

        //debug::log($newmap,'antes');
        foreach($armiesMap as $index => $tile){
            $newmap[$index] = 0;
        }

        //debug::log($newmap,'despues');

        return $newmap;
  }
  /* Mapa de Objetos*/
  public function obstacleMap(){
    return $this->maputil->obtainObstacleMap($this->region->getId());
  }

  public function defenseMap($armyId=0){
    if($armyId!=0){
        $army = $this->man("army")->findById($armyId);
    }
    else{
        $army = $this->getActualArmy();
    }

    $newmap = $this->getMapUtil()->emptyMap(100);//Para rellenar con 0 si no se puede o 100 si conviene mover ahi
    //debug::log($newmap,'aibattlestate newmap');
    $enemies = new armies();
    debug::log($army->getPlayerId(),'aibattlestate The Enemy Player');
    $enemies_raw = $enemies->initByAliveEnemiesOfPlayerInRegion($army->getPlayerId(),$this->getRegion()->getId());
    //debug::log($enemies_raw,'aibattlestate defenseMap possibleIndexes['+$startIndex+']');
    foreach ($enemies_raw as $index => $army_raw) {
        $army = $this->man("army")->wrap($army_raw);
        $possibleActions = $this->getActiveActions($army->getId());
        foreach($possibleActions as $index => $oAction){
            if($oAction->targetIs(_X_TARGET_ARMY)){

                $quanta = $this->battleutil->calculateInverseQuanta($oAction,count($enemies_raw));
                $startIndex = $this->maputil->mapIndex($army->getX(), $army->getY());
                $possibleIndexes = $this->maputil->getIndexesByPath($oAction->getPath(), $startIndex,$oAction->getMinRange(),$oAction->getMaxRange(),$army->getId());
                //debug::log($possibleIndexes,'aibattlestate defenseMap possibleIndexes['+$startIndex+']');

                foreach ($possibleIndexes as $indicex) {
                    $newmap[$indicex] = $newmap[$indicex]-$quanta;
                }
                //break;
                //debug::log($newmap,'aibattlestate defenseMap possibleIndexes['+$startIndex+']');
            }
            //debug::log($oAction,'defenseMap');
        }
    }
    return $newmap;
  }


public function fullMap($action_id,$selected_army_id=0){

    if($selected_army_id!=0){
        $army = $this->man("army")->findById($selected_army_id);
    }
    else{
        $army = $this->getActualArmy();
    }
    
    $actionmap = $this->actionMap($action_id,$army->getId());
    $actionmap = $this->getMapUtil()->promediate($actionmap,100);
    $obstaclemap = $this->obstacleMap();
    $fusionmap = $this->getMapUtil()->obtainZeroMap($actionmap,$obstaclemap);
    $fusionmap = $this->getMapUtil()->promediate($fusionmap,100);
    $newmap = $fusionmap;

    $index = $army->getPheromone();
    //Si existe feronoma, porque puede que no exista
    if(!empty($index)){
        $newmap[$index] = $newmap[$index] - $this->getMapUtil()->calculateDiffusion()*1;
    }
    //debug::log('index:'.$index.' newmap:'.$newmap[$index],'aibattlestate pheronome antes');
    
    //debug::log('diffusion:'.$this->getMapUtil()->calculateDiffusion().' newmap:'.$newmap[$index],'aibattlestate pheronome dspues');


    $travelmap = $this->travelMap($army->getId());
    $travelmap = $this->getMapUtil()->promediate($travelmap,$this->getMapUtil()->calculateDiffusion(),true);

    $newmap = $this->getMapUtil()->add($fusionmap,$travelmap);
    $newmap = $this->getMapUtil()->promediate($newmap,100);

    return $newmap;
}

/*
 * Este actionMap funciona pero no da campos ponteciales. Aburrido
 */
    public function actionMap($action_id,$armyId=0) {

    
    $oAction =   $this->man("action")->findById($action_id);
    if($oAction->isMovable() ){
        $newmap = $this->travelMap($armyId);
    }
    else{  
        $newmap = $this->getMapUtil()->emptyMap(5);
        $armiesObjetive = $this->obtainObjetivesByTargetType($oAction,$armyId);
        if($armiesObjetive->size()>0){
            foreach ($armiesObjetive->getRaw() as $index => $army_raw) {
                $army = $this->man("army")->wrap($army_raw);
                //debug::log($army,'aibattle->decideMovement travel Army');
                $startIndex = $this->maputil->mapIndex($army->getX(), $army->getY());
                $possibleIndexes = $this->maputil->getIndexesByPath($oAction->getPath(), $startIndex,$oAction->getMinRange(),$oAction->getMaxRange(),$army->getId());

               // debug::log($possibleIndexes,'aibattlestate->actionMap possibleIndexes['.$startIndex.']');
                $quanta = $this->battleutil->calculateInverseQuanta($oAction,count($armiesObjetive->getRaw()));
                $maximun = array();
                foreach ($possibleIndexes as $indicex) {
                    //if ($newmap[$indicex] == 0) {
                        //$maximun[$indicex] = $newmap[$indicex] + $quanta;
                        $maximun[$indicex] = $this->getMapUtil()->obtainMaximunQuanta();
                        //$maximun = $indecex;
                    //}
                }
                //debug::log($maximun,'aibattlestate->actionMap maximun['.$startIndex.']');

                $maxIndexes = $this->maputil->obtainMaxIndexes($maximun);
                //debug::log($maxIndexes,'aibattlestate->actionMap maxIndexesIndexes['.$startIndex.']');
                foreach($maxIndexes as $index){
                    //debug::log($newmap,'aibattle->actionMap pre1');
                    $newmap[$index] = $maximun[$index];
                    //debug::log($newmap,'aibattle->actionMap pre2');
                    $newmap = $this->maputil->fillPotentialField($newmap,$index);
                    //debug::log($newmap,'aibattle->actionMap post');
                }

                //$tempomap = $this->maputil->fillMapWithValue($possibleIndexes,100);
                //debug::log($maxIndexes,'aibattle->decideMovement travel Army');
                //$newmap = $tempomap;
            }
        }
    }


    return $newmap;
}


  /* Mapa de Viaje de la unidad*/
  public function travelMap($armyId=0){
    if($armyId!=0){
        $army = $this->man("army")->findById($armyId);
    }
    else{
        $army = $this->getActualArmy();
    }
    $speed = $army->getSpeed();
    //Obtener Mapa
    $map = $this->region->getMap();
    $newmap = array();
    foreach($map as $key => $tile){
        $newmap[$key] = $speed[$tile-1];
        //debug::log($key,'ajaxAI renderHeightMap');
    }
    return $newmap;
  }
  
  public function enemyMap(){
    $region = $this->region;
    $total = $region->getTotalTiles();
    $region_id = $region->getId();
    $maputil =  $this->maputil;
    $enemies = new armies();

   
    $unit = $this->actual_unit;
    $unit_index = $maputil->mapIndex($unit->getX(),$unit->getY());
    $enemy_player_id = $unit->getPlayerId();

    $enemies_raw = $enemies->initByAliveEnemiesOfPlayerInRegion($enemy_player_id,$region_id);


    $newmap = array();
    for($index=0;$index<$total;$index++){
        $newmap[$index] = 0;
    }

    foreach($enemies_raw as $enemy_id => $enemy){
        $enemy_index = $maputil->mapIndex($enemy['position_x'],$enemy['position_y']);
        //debug::log("enemyIndex:".$enemy_index,'['.$index.']');
        //$maputil->getXByIndex($enemy_index);

        $range1 = $maputil->getTilesAtRange($enemy_index , 1);
        $newmap[$enemy_index] = 100;
        foreach($range1 as $range){
            if($newmap[$range]<75){
            $newmap[$range]=75;
            }
        }
        $range2 = $maputil->getTilesAtRange($enemy_index, 2);
        foreach($range2 as $range){
            if($newmap[$range]<50){
            $newmap[$range]=50;
            }
        }
        $range3 = $maputil->getTilesAtRange($enemy_index, 3);
        foreach($range3 as $range){
            if($newmap[$range]<25){
            $newmap[$range]=25;
            }
        }

    }
    //debug::log($newmap,'aibattlestate->enemyMap');
    return $newmap;
}

public function getRegion(){
    return $this->region;
}
public function getMapUtil(){
    return $this->maputil;
}


}//aiplayers
?>