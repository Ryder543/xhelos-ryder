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
class regiondeowningwork extends work {

     public function __construct(){
                parent::__construct();
    }   
    
    public function execute() {
        $this->setIfOldDtIsOkWithCallback("executeSubsractBasicResourcesRatesToPlayerDt");        
        $this->setIfOldDtIsOkWithCallback("executeSubsractElementalResourcesRatesToPlayerDt");
        $this->setIfOldDtIsOkWithCallback("executeRemoveBiomesFromPlayerDt");        
        $this->setIfOldDtIsOkWithCallback("executeRegionDeOwningToPlayerDt");
    }

    public function validate() {
        
    }

    public function breakIf() {
         $this->setIfOldDtIsOkWithCallback("breakIfRegionOwnerDoesntExist");
         $this->setIfOldDtIsOkWithCallback("breakIfRegionHasStillColoniesFromRegionOwner");
         $this->setIfOldDtIsOkWithCallback("breakIfRegionHasStillAliveUnitsFromRegionOwner");

    }

    ///////////////////////////////////
    //Executes
    ///////////////////////////////////

    protected function executeRegionDeOwningToPlayerDt() {
        $oRegion = $this->getRegion();
        $dt = $oRegion->setOwnerIdDt(_X_PLAYER_NO_PLAYER_ID);
        if (!$dt->ok()) {
            $dt->error("No se pudo cambiar la pertenencia de la region[" . $oRegion->getName() . "] al  un jugador inexistente");
        } else {
            $dt->success("Se cambio la pertenencia de la region[" . $oRegion->getName() . "] a ningun jugador");
        }
        return $dt;
    }

    
    protected function executeRemoveBiomesFromPlayerDt() {
        //Aumentamos al player estos rates
        $playerutil = new playerutil();
        $playerId = $this->getPlayer()->getId();
        $oRegion = $this->getRegion();
        $dt = $playerutil->removeBiomesFromPlayerFromRegionDt($oRegion->getId(), $playerId);
        if (!$dt->ok()) {
            $dt->error("No se pudo remover los biomas en la region[".$oRegion->getName()."] al jugador");
        } else {
            $dt->success("Se pudo remover los biomas  de la region[".$oRegion->getName()."] al jugador[".$this->getPlayer()->getName()."]");
        }
        return $dt;
    }   
    
     protected function executeSubsractBasicResourcesRatesToPlayerDt() {
        $oRegion = $this->getRegion();
        $playerId = $oRegion->getOwnerId();
        
        $regutil = new regionutil();
        $playerutil = new playerutil();            
        $resRateArray = $regutil->getBasicResourcesRatesPerTickByRegionType($oRegion->getType());
        $dt = $playerutil->substractResourcesRatesToPlayerDt($resRateArray, $playerId);      
        if (!$dt->ok()) {
            $dt->error("No se pudo restar los resources basicos al jugador");
        } else {
            $dt->success("Se pudo restar los resources basicos del player");
        }
        return $dt;
    }
    
     protected function executeSubsractElementalResourcesRatesToPlayerDt() {
        $oRegion = $this->getRegion();
        $playerId = $oRegion->getOwnerId();
        
        $regutil = new regionutil();
        $playerutil = new playerutil();            
        $resRateArray = $regutil->getElementalResourcesRatesPerTickByRegionId($oRegion->getId());
        $dt = $playerutil->substractResourcesRatesToPlayerDt($resRateArray, $playerId);      
        if (!$dt->ok()) {
            $dt->error("No se pudo restar los resources elementales al jugador");
        } else {
            $dt->success("Se pudo restar los resources elementales del player");
        }
        return $dt;
    }   

    ///////////////////////////////////
    //Validates
    ///////////////////////////////////
    ///////////////////////////////////
    //BreakIf
    ///////////////////////////////////    
    protected function breakIfRegionOwnerDoesntExist() {
        $dt = new datatransfer();
        $oRegion = $this->getRegion();
        $ownerId = $oRegion->getOwnerId();
        if (empty($ownerId) || ( $ownerId == _X_PLAYER_NO_PLAYER_ID) ) {
            $dt->validWarning("La Region actualmente ya no le pertenece a nadie");
            $this->breakWork();
        }
        else{
            $dt->success("Aun hay un jugador que le pertenece esta region");
        }
        return $dt;
    }
    protected function breakIfRegionHasStillColoniesFromRegionOwner(){
        $dt = new datatransfer();
        $oRegion = $this->getRegion();
        $ownerId = $oRegion->getOwnerId();
        
        $colonies = new colonies();
        $colonies->initActiveByPlayerIdByRegionId($ownerId,$oRegion->getId());
        
        if($colonies->size()>0){
        $dt->validWarning("La Region actualmente todavia cuenta con colonias del jugador");
            $this->breakWork();
        }
        else{
            $dt->success("La region puede desownearse porque no tiene colonias del jugador");
        }
        return $dt;
    }
    
    protected function breakIfRegionHasStillAliveUnitsFromRegionOwner(){
       $dt = new datatransfer();
        $oRegion = $this->getRegion();
        $ownerId = $oRegion->getOwnerId();
        
        $armies = new armies();
        $armies->initAliveByPlayerIdByRegionId($oRegion->getId(), $ownerId);
        if($armies->size()>0){
        $dt->validWarning("La Region actualmente todavia cuenta con armies vivos del jugador");
            $this->breakWork();
        }
        else{
            $dt->success("La region puede desownearse porque no tiene armies vivos del jugador");
        }
        return $dt;
    }
    
         /*$this->setIfOldDtIsOkWithCallback("");
         $this->setIfOldDtIsOkWithCallback("breakIfRegionHasStillAliveUnitsFromRegionOwner");*/
 
    
    protected function validateParams() {
        $this->validateParamIfNotEmpty(_X_VAR_REGION_ID);
    }

    public function getRegion() {
        if (empty($this->oRegion)) {
            $regionman = regionman::singleton();
            $regionId = $this->getParam(_X_VAR_REGION_ID);
            $this->oRegion = $regionman->findById($regionId);
        }
        return $this->oRegion;
    }

    ////////////////////////////////////
    // Privadas
    ///////////////////////////////////
}

?>
