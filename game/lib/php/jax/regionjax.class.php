<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of regionjax
 *
 * @author windows7
 */
class regionjax extends jax {

    public function __construct() {
        parent::__construct();
        if ($this->isOk()) {//Porque jax::constructor llama a prepareRequestParams
            $this->init($this->getRequestParam('region_id'), _X_VIEW_TYPE_REGION);
        }
    }
    

    public function executePassTurn(){
        $player = $this->getCurrentPlayer();
        $player_id = $player->getId();
         $ok = true;
         //Validar si se esta en modo batalla
         $regionState = $this->getGs()->getOState();
          $phase= $regionState->getBattle()->getPhase();
          if(($ok) AND !($phase==_X_BATTLE_PHASE_BATTLE)){
            debug::error('No se encuentra en fase BATTLE, estas en ['.$phase.']');
            $this->setBadDt('No existen turnos si no estas en la fase de batalla');
            $ok = false;
          }

          //Validar que el nextArmie en cola sea igual a los datos de la unidad de origen
          $army_actual = $regionState->getNextArmyInBattle();
          if(($ok) AND ($army_actual->getPlayerId()!=$player_id)){
             $ok = false;
             debug::error('OriginalArmyPlayer['.$army_actual->getPlayerId().']!=ActualPlayer['.$player_id.']','ajaxRegion action');
             $this->setBadDt('No es el turno de ninguna de tus unidades');
          }

          if($ok){
            $regionState->advanceBattleTurn();
             $dt = new datatransfer();
             $action_id =$this->getRequestParam("action_id");
             if(!empty($action_id)){//Si existe una accion se ejecuta
              
               $dt->success('Finalizo turno');
             }
             else{
               $dt->validWarning('Turno Finalizado sin accion');  
             }
          }//if $ok
    }
    
    //Ejecutar las acciones del army
    public function executeArmyAction() {
        $this->setGameParam(_X_VAR_PLAYER_ID,$this->getCurrentPlayer()->getId());
        $this->doWork(new executearmyactionwork($this));
        if($this->isOk()){
        $player = $this->getCurrentPlayer();
        if($player->isTutorialActive() ){
            $menustate = $this->getGs()->getMenuState();
              if($menustate->actualTutorialStep()==10 ){
                  $menustate->setPostActionTutorialStep();
              }
              
          }
        }
    }
    
    public function executeTutorialOff(){
          $player = $this->getCurrentPlayer();
          $player->setTutorial(false);
          $dt = new datatransfer();
          $dt->success('El Tutorial se apago');
    }
     public function executeTutorialOn(){
          $player = $this->getCurrentPlayer();
          $player->setTutorial(true);
          $dt = new datatransfer();
          $dt->success('El Tutorial esta prendido');
    }   

    
    public function executeChangePlayerPhaseActionFromTacticToBattle(){
        //debug::log('ajaxREgion tactical 1');
        $player = $this->getCurrentPlayer();
        $player->startBattlePhase($this->getRequestParam("region_id"),$this->getRequestParam("battle_id"));
        $menustate = $this->getGs()->getMenuState();
        $menustate->setBattleTutorialStep(); 
        $this->getGs()->getOState()->updateState();//Actualizamos de nuevo el estado para maxima eficiencia nerf, osea para que en esta respuesta
    }
    

    public function executeCalculateState() {
        $regionState = $this->getGs()->getOState();
        $playerman = playerman::singleton();
        if ($regionState->isBattleActive()) {
            //[TODO] Si estamos en fase tactica no deberiamos de chekar nada de esto
            ////////////////////////Ciclo del AI//////////////////////////////
            $nextArmy = $regionState->getNextArmyInBattle();
            $armyPlayer = $playerman->findById($nextArmy->getPlayerId());
            if ($armyPlayer->isArtificial()) {
                $aiutil = new aibattleutil($regionState->getRegion(), $nextArmy, $regionState->getBattle());
                $movement = $aiutil->decideMovement();
                if ($movement) {
                    $movement->workActionDT();
                } else {
                    debug::log('No hay una accion elegida');
                }
                $regionState->advanceBattleTurn();
                if ($regionState->battleHasEnded()) {
                    $regionState->getBattle()->endBattlePhase();
                }
            }
            $dt = new datatransfer();
            $dt->success('Esperando a los demas jugadores');
            $this->setIfOldDtIsOk($dt);
        }
    }

    protected function prepareRequestParams() {
        $this->valStringRequestParam('ajax_action');
        $this->valNumericRequestParam('region_id');

        if (isset($_POST['battle_id'])) {
            $this->valNumericAndNullRequestParam('battle_id');
        }
        if (isset($_POST['action_id']) && (!empty($_POST['action_id']))) {
            $this->valNumericRequestParam("action_id");
        }
        if (isset($_POST['ty'])) {
            $this->valNumericRequestParam("ty");
            $this->valNumericRequestParam("tx");
            $this->valNumericRequestParam("oy");
            $this->valNumericRequestParam("ox");
        }
    }


}

?>
