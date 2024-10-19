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
class removecolonywork extends work {

    private $oColony;

    //put your code herepublic function execute(){ 
    public function __construct(){//Necesitamos llamar al constructor
        parent::__construct();
    }
    public function execute() {
        $dt = null;
        $this->setIfOldDtIsOkWithCallback("executeDeleteColonyByIdDt");
        
        $regionId = $this->getColony()->getRegionId();
        $this->setParam(_X_VAR_REGION_ID,$regionId);
        $this->doInnerWork(new regiondeowningwork());
        $this->setIfAllOkText("Tu colonia ".$this->getColony()->getName()." a sido eliminada existosamente");
        return $dt;
    }
    
    protected function executeDeleteColonyByIdDt(){ 
       $colonyId = $this->getColony()->getId();  
       $ecu = new entitycolonyutil(); 
       $dt = $ecu->deleteColonyByIdDt($colonyId);
       if (!$dt->ok()) {
            $dt->error($dt->getText());
        } else {
            $dt->success("Se pudo eliminar la colonia del jugador");
        }
    }

    
    public function validate() {
        $this->setIfOldDtIsOkWithCallback("validateColonyExistDt");
        $this->setIfOldDtIsOkWithCallback("validateColonyTypeDt");   
        $this->setIfOldDtIsOkWithCallback("validateColonyOwnerIdDt");           
        
    }
  
    protected function validateColonyExistDt() {
        $dt = new datatransfer();
        $oColony = $this->getColony();
        
        if (!$oColony->exist() ) {
            $dt->error("La Colonia que quieres eliminar no existe");
        } else {
            $dt->success("La Colonia existe, preparandose para eliminarla");
        }
        return $dt;
    }
    protected function validateColonyTypeDt() {
        $dt = new datatransfer();
        $oColony = $this->getColony();
        $type = $this->getParam(_X_VAR_COLONY_TYPE);
        if ($oColony->getType() != $type ) {
            $dt->error("El tipo[".$oColony->getType()."] de la Colonia que desea borrar no es el indicado[".$type."]");
        } else {
            $dt->success("La Colonia es del tipo adecuado");
        }
        return $dt;
    } 
    
    
    protected function validateColonyOwnerIdDt() {
        $dt = new datatransfer();
        $oColony = $this->getColony();
        $ownerId = $this->getParam(_X_VAR_OWNER_ID);
        if ($oColony->getOwnerId() != $ownerId ) {
            $dt->error("Esta Colonia no le pertenece al usuario");
        } else {
            $dt->success("La Colonia le pertenece al usuario indicado");
        }
        return $dt;
    } 
    
    public function getColony(){
        if(empty($this->oColony)){
            $colonyman = colonyman::singleton();
            $colonyId = $this->getParam(_X_VAR_COLONY_ID);
            $this->oColony = $colonyman->findById($colonyId);
        }
        return $this->oColony;
    }
    

    protected function validateParams() {
        $ok = true;
        //debug::warning($_POST,'ajaxRegion action _POST');
        $colonyId = $this->getParam(_X_VAR_COLONY_ID);
        if ((!isset($colonyId)) || (empty($colonyId))) {
            debug::error('No se envio un colonyId a removecolonywork', 'removecolonywork colonyId');
            $this->setBadDt('No se enviaron los datos correctamente');
            $ok = false;
        }
        $colonyType = $this->getParam(_X_VAR_COLONY_TYPE);
        if (($ok) && (!isset($colonyType))) {//Si el primero ta mal tonces dejar
            debug::error('El tipo de colonia a borrar no fue enviado', 'removecolonywork colony_type');
            $this->setBadDt('No se enviaron los datos correctamente');
            $ok = false;
        }
        $ownerId = $this->getParam(_X_VAR_OWNER_ID);
        if (($ok) && (!isset($ownerId))) {//Si el primero ta mal tonces dejar
            debug::error('No se especifico a que owner debemos de borrar', 'removecolonywork colony_type');
            $this->setBadDt('No se enviaron los datos correctamente');
            $ok = false;
        }
        
        if($ok){
            $dt = new datatransfer();
            $dt->success("Los parametros fueron enviados correctamente");
            return $dt;
        }
    }

    ////////////////////////////////////
    // Privadad
    ///////////////////////////////////
    private function getXOfColony() {
        return _X_COLONY_INITIAL_X_POSITION;
    }

    private function getYOfColony() {
        return _X_COLONY_INITIAL_Y_POSITION;
    }

    public function breakif() {
        
    }

}

?>
