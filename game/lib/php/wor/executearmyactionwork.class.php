<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of regionColonyCreateColony
 *
 * @author windows7
 */
class executearmyactionwork extends work {
    
    private $mov;
    private $originArmy;  
    private $actualArmy;
    private $battle;

    public function __construct(){
                parent::__construct();
    }
    
    public function execute() {
        $reactionTD = $this->getMov()->workActionDT(); //Se ejecuta TEMP
        if (!$reactionTD->isValid()) {
            debug::warning($reactionTD->getText(), 'ajaxRegion->action->reaction');
            $this->setBadDt('No se pudo ejecutar la ac');
            $ok = false;
        } else {
            //$new_movement->saveActionTD($army_actual->getId());
            $regionState = $this->getOState();
            $regionState->advanceBattleTurn();
            //Preguntamos si la unidad finalizo la batalla despues del movimiento
            
            if ($regionState->battleHasEnded()) {
                $regionState->getBattle()->endBattlePhase();
            }
            $dt = new datatransfer();
            $dt->success("Se ejecuto la accion del army correctamente");
            $this->setIfOldDtIsOk($dt);
        }
    }

    public function validate() {
        $this->setIfOldDtIsOk($this->validateParams());
        $this->setIfOldDtIsOk($this->validateBattle());        
        $this->setIfOldDtIsOk($this->validateArmy());
        $this->setIfOldDtIsOk($this->validateAction());
        $this->setIfOldDtIsOk($this->validateMana());
    }

    protected function validateParams() {
        $ok = true;
        //debug::warning($_POST,'ajaxRegion action _POST');
        $action_id = $this->getParam("action_id");
        if ((!isset($action_id)) || (empty($action_id))) {
            debug::error('Se mando una accion pero no llego su id', 'ajaxRegion action');
            $this->setBadDt('No hay ninguna accion seleccionada');
            $ok = false;
        }
        $ox = $this->getParam("ox");
        if (($ok) && (!isset($ox))) {//Si el primero ta mal tonces dejar
            debug::error('ox no fue enviado', 'ajaxRegion action');
            $this->setBadDt('No se envio el dato [origen_x]');
            $ok = false;
        }
        $oy = $this->getParam("oy");        
        if (($ok) && (!isset($oy))) {//Si el primero ta mal tonces dejar
            debug::error('oy no fue enviado', 'ajaxRegion action');
            $this->setBadDt('No se envio el dato [origen_y]');
            $ok = false;
        }
        $ty = $this->getParam("ty");        
        if (($ok) && (!isset($ty))) {//Si el primero ta mal tonces dejar
            debug::error('ty no fue enviado', 'ajaxRegion action');
            $this->setBadDt('No se envio el dato [target_x]');
            $ok = false;
        }
        $tx = $this->getParam("tx");
        if (($ok) && (!isset($tx))) {//Si el primero ta mal tonces dejar
            debug::error('tx no fue enviado', 'ajaxRegion action');
            $this->setBadDt('No se envio el dato [target_x]');
            $ok = false;
        }
        if($ok){
            $dt = new datatransfer();
            $dt->success("La Batalla esta en la fase correcta");
            return $dt;
        }
    }
    private function validateBattle() {
        $battleman = battleman::singleton();
        $battle = $battleman->findById($this->getParam("battle_id"));
        $phase = $battle->getPhase();
        if (!($phase == _X_BATTLE_PHASE_BATTLE)) {
            $this->setBadDt('Solo se pueden usar acciones en la fase de batalla');
            debug::error('No se encuentra en fase BATTLE, estas en [' . $phase . ']');
        }
        else{
            $this->setBattle($battle);
            $dt = new datatransfer();
            $dt->success("La Batalla esta en la fase correcta");
            return $dt;
        }
    }

    private function validateArmy(){
        //Validar que los datos de la unidad de origen existan
        $ok = true;
        $region_id = $this->getParam("region_id");
        debug::log($region_id,"executeaiactionwork->validateArmy region");
        
        $ox = $this->getParam("ox");
        $oy = $this->getParam("oy");
        debug::log($ox,"executeaiactionwork->validateArmy ox");
        debug::log($oy,"executeaiactionwork->validateArmy oy");
        $armyman = armyman::singleton();
        $army_origin = $armyman->findByCoord($region_id, $ox, $oy);
        debug::log($army_origin,"executeaiactionwork->validateArmy armyman_origin");
        $player_id = $this->getPlayer()->getId();
        //$army_origin->initByCoord($region_id,$ox,$oy);
        if (($ok) AND !($army_origin->exist())) {
           debug::error('3 No hay ningun army en la posicion indicada');
            //debug::trace("executearmyacionwork");
           // debug::log($this);
            $this->setBadDt('3 No hay ningun army en la posicion indicada');
            $ok = false;
        }
        //Validar que el nextArmie en cola sea igual a los datos de la unidad de origen
        $regionState = $this->getOState();
        $army_actual = $regionState->getNextArmyInBattle();
        if (($ok) AND ($army_origin->getId() != $army_actual->getId())) {
            debug::error('ArmyActual[' . $army_actual->getId() . '] es diferente a ArmyOrigin[' . $army_origin->getId() . ']');
            $this->setBadDt('Ya paso el turno del army seleccionada');
            $ok = false;
        }

        //Se Valida que la unidad pertenezca al player actual
        if (($ok) AND ($army_actual->getPlayerId() != $player_id)) {
            $ok = false;
            debug::error('OriginalArmyPlayer[' . $army_actual->getPlayerId() . ']!=ActualPlayer[' . $player_id . ']', 'ajaxRegion action');
            $this->setBadDt('La Unidad no te pertenece');
        }
        if($ok){
            $this->setOriginArmy($army_origin);
            $this->setActualArmy($army_origin);
            $dt = new datatransfer();
            $dt->success("El Army es el correcto");
            return $dt;
        }
    }
    //OJO: $battle se genera en validateBattle por lo que esta funcion validateAction debe de ser llamada despues de validateBattle
    private function validateAction(){
        //Si todo esta corecto se procede a calcular los datos de la accion y validarlo
        $ok = true;
        $region_id = $this->getParam('region_id');
        $action_id = $this->getParam('action_id');
        $ox = $this->getParam('ox');
        $oy = $this->getParam('oy');
        $tx = $this->getParam('tx');
        $ty = $this->getParam('ty');
        $battle = $this->getBattle();        
        $actionman = actionman::singleton();
        
        $army_origin = $this->getOriginArmy();
        debug::log($this,"executearmyactionwork->validateAction");
        if ($ok) {
            $target = new target($region_id, $action_id, $ox, $oy, $tx, $ty);
            $action = $actionman->findById($action_id);
            $mov = new movutil($army_origin, $action, $target, $battle);
            $validation = $mov->validateDT($this->getActualArmy(), $action_id);
        }

        if (($ok) AND !( $validation->hasSuccess() )) {
            $text = $validation->getText();
            if (empty($text)) {
                $ok = false;
                debug::error('La validacion de la accion no envio ninguna respuesta y es invalida', 'ajaxRegion->action->actionman->validation');
               $this->setBadDt('Error en la accion enviada, vuelva a intentarlo');
            } else {
                $ok = false;
                debug::warning($text, 'ajaxRegion->action->actionman->validation');
                $this->setBadDt($text);
            }
        }
        if($ok){
            $this->setMov($mov);
            $dt = new datatransfer();
            $dt->success("La validacion de la Accion es correcta");
            return $dt;
        }
    }
    
    ///////////////////////////////////////////////////////////////////////
    //Validar que los datos de ingreso esten bien
    ///////////////////////////////////////////////////////////////////////
    //Validar si se esta en modo batalla
    private function validateMana(){
        //Se valida si la unidad origen tiene el suficiente mana
        $army_origin = $this->getOriginArmy();
        $mov = $this->getMov();
        $battleutil = new battleutil();
        
        $ok = true;
        if ($ok) {
            $manaDT = $battleutil->validateManaDT($army_origin->getId(), $mov->getAction()->getId(), false);
            if (!$manaDT->ok()) {
                debug::trace($manaDT->getText(), 'ajaxRegion->action->actionman->dt_mana');
                $this->setWarningDt($manaDT->getText());
                $ok = false;
            }
        }
        if($ok){
            $dt = new datatransfer();
            $dt->success("La validacion del mana de la unidad es correcta");
            return $dt;
        }
    }
    
    
    //////////////////////////////////////////////////////////////////////////////////
    //GETTERS and SETTERS
    //////////////////////////////////////////////////////////////////////////////////   
    private function getMov(){
        return $this->mov;
    }

    private function setMov($mov){
        $this->mov = $mov;
    }
    private function getOriginArmy(){
        return $this->originArmy;
    }

    private function setOriginArmy($oArmy){
        $this->originArmy = $oArmy;
    }
    
    private function getActualArmy(){
        return $this->actualArmy;
    }

    private function setActualArmy($oArmy){
        $this->actualArmy = $oArmy;
    }   
    
    
    
    
     private function getBattle(){
        return $this->battle;
    }

    private function setBattle($oBattle){
        $this->battle = $oBattle;
    }

    public function breakIf() {
        
    }  
}

?>
