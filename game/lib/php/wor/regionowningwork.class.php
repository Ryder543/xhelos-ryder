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
class regionowningwork extends work {

    public function __construct(){
        parent::__construct();
    }
    
    public function execute() {
        $this->setIfOldDtIsOkWithCallback("executeAddBasicResourcesRatesToPlayerDt");
        $this->setIfOldDtIsOkWithCallback("executeAddElementalResourcesRatesToPlayerDt");
        $this->setIfOldDtIsOkWithCallback("executeAddBiomesToPlayerDt");        
        $this->setIfOldDtIsOkWithCallback("executeRegionOwningToPlayerDt");
    }

    public function validate() {
        
    }

    public function breakIf() {
        $this->setIfOldDtIsOkWithCallback("breakIfRegionOwnerIsPlayerIdParam");
        $this->setIfOldDtIsOkWithCallback("breakIfRegionHasEnemyUnitsIfNotPrimary");
        $this->setIfOldDtIsOkWithCallback("breakIfRegionHasColonies");
        $this->setIfOldDtIsOkWithCallback("breakIfRegionHasAnotherColony");
    }

    ///////////////////////////////////
    //Executes
    ///////////////////////////////////

    protected function executeRegionOwningToPlayerDt() {
        $oRegion = $this->getRegion();
        $oPlayer = $this->getPlayer();
        $dt = $oRegion->setOwnerIdDt($this->getPlayer()->getId());
        if (!$dt->ok()) {
            $dt->error("No se pudo cambiar la pertenencia de la region[" . $oRegion->getName() . "] al jugador[" . $oPlayer->getName() . "]");
        } else {
            $dt->success("Se cambio la pertenencia de la region[" . $oRegion->getName() . "] al jugador[" . $oPlayer->getName() . "]");
        }
        return $dt;
    }

    
    
    
    protected function executeAddBasicResourcesRatesToPlayerDt() {
        //Aumentamos al player estos rates
        $playerutil = new playerutil();
        $playerId = $this->getPlayer()->getId();
        $regutil = new regionutil();
        $oRegion = $this->getRegion();
        $resRateArray = $regutil->getBasicResourcesRatesPerTickByRegionType($oRegion->getType());
        $dt = $playerutil->addResourcesRatesToPlayerDt($resRateArray, $playerId);
        if (!$dt->ok()) {
            $dt->error("No se pudo aumentar los resources basicos al jugador");
        } else {
            $dt->success("Se pudo aumentar los resources rates del player");
        }
        return $dt;
    }
    
 protected function executeAddBiomesToPlayerDt() {
        //Aumentamos al player estos rates
        $playerutil = new playerutil();
        $playerId = $this->getPlayer()->getId();
        $oRegion = $this->getRegion();

        $dt = $playerutil->addBiomesToPlayerFromRegionDt($oRegion->getId(), $playerId);
        if (!$dt->ok()) {
            $dt->error("No se pudo aumentar los biomas de la region[".$oRegion->getName()."] al jugador");
        } else {
            $dt->success("Se pudo aumentar los biomas de la region al jugador");
        }
        return $dt;
    }   
    
    

    protected function executeAddElementalResourcesRatesToPlayerDt() {
        //Aumentamos al player estos rates
        $playerutil = new playerutil();
        $playerId = $this->getPlayer()->getId();
        $regutil = new regionutil();
        $oRegion = $this->getRegion();
        $resRateArray = $regutil->getElementalResourcesRatesPerTickByRegionId($oRegion->getId());
        $dt = $playerutil->addResourcesRatesToPlayerDt($resRateArray, $playerId);
        if (!$dt->ok()) {
            $dt->error("No se pudo aumentar los resources elementales al jugador");
        } else {
            $dt->success("Se pudo aumentar los resources rates del player");
        }
        return $dt;
    }

    ///////////////////////////////////
    //Validates
    ///////////////////////////////////
    ///////////////////////////////////
    //BreakIf
    ///////////////////////////////////    
    protected function breakIfRegionOwnerIsPlayerIdParam() {
        $dt = new datatransfer();
        $oRegion = $this->getRegion();
        if ($oRegion->getOwnerId() == $this->getPlayer()->getId()) {

            $dt->validWarning("El Jugador[" . $this->getPlayer()->getName() . "] ya es el dueño de la region[" . $oRegion->getName() . "]");
            $this->breakWork();
        } else {
            $dt->success("El Jugador puede apropiarse de la region ya que no es el dueño aun");
        }
        return $dt;
    }

    protected function breakIfRegionHasEnemyUnitsIfNotPrimary() {
        $dt = new datatransfer();
        if($this->getOrigin()!=_X_WORK_ORIGIN_CREATEPRIMARYCOLONY){
            $oRegion = $this->getRegion();
            $regionId = $oRegion->getId();
            $playerId = $this->getPlayer()->getId();
            $armies = new armies();
            $armies->initByAliveEnemiesOfPlayerInRegion($playerId, $regionId);
            if ($armies->size() > 0) {
                $dt->validWarning("El Jugador[" . $this->getPlayer()->getName() . "] no puede apropiarse de la region porque tiene jugadores enemigos");
                $this->breakWork();
            } else {
                $dt->success("La region puede apropiarse ya que esta libre de unidades enemigas del jugador[" . $this->getPlayer()->getName() . "]");
            }
        } else {
            $dt->success("La region puede apropiarse ya que se va a construir una colonia primaria");
        }
        return $dt;
    }

    protected function breakIfRegionHasColonies() {
        $dt = new datatransfer();
        if( ($this->getOrigin()!=_X_WORK_ORIGIN_CREATEPRIMARYCOLONY) && ($this->getOrigin()!=_X_WORK_ORIGIN_CREATESECONDARYCOLONY) ){
            $dt = new datatransfer();
            $oRegion = $this->getRegion();
            $regionId = $oRegion->getId();
            $colonies = new colonies();
            $colonies->initActiveByRegionId($regionId);
            if ($colonies->size() > 0) {
                $dt->validWarning("Ya existe(n) [" . $colonies->size() . "] colonia(s) en la region en la region[" . $oRegion->getName() . "]");
                $this->breakWork();
            } else {
                $dt->success("La region[" . $oRegion->getName() . "] esta con una o 0 colonias, por lo que puedes apropiarte de esta region");
            }
        } else {
            $dt->success("La region puede apropiarse ya que es para una region primaria o secundaria");
        }
        return $dt;
    }
    protected function breakIfRegionHasAnotherColony() {
        $dt = new datatransfer();
        if( ($this->getOrigin()==_X_WORK_ORIGIN_CREATEPRIMARYCOLONY) || ($this->getOrigin()==_X_WORK_ORIGIN_CREATESECONDARYCOLONY) ){
            $dt = new datatransfer();
            $oRegion = $this->getRegion();
            $regionId = $oRegion->getId();
            $colonies = new colonies();
            $colonies->initActiveByRegionId($regionId);
            if ($colonies->size() > 1) {//Porque esta es la colonia que se esta construyendo, si hay mas de una entonces no debe de recibir el owning
                $dt->validWarning("Ya existe(n) [" . $colonies->size() . "] colonia(s) en la region en la region[" . $oRegion->getName() . "]");
                $this->breakWork();
            } else {
                $dt->success("La region[" . $oRegion->getName() . "] esta con una o 0 colonias, por lo que puedes apropiarte de esta region");
            }
        } else {
            $dt->success("La region puede apropiarse ya que es para una region primaria o secundaria");
        }
        return $dt;
    }
    
    protected function validateParams() {
        $this->validateParamIfNotEmpty(_X_VAR_REGION_ID);
        $this->validateParamIfNotEmpty(_X_VAR_PLAYER_ID); //Usamos getPlayer, definido en work.class
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
