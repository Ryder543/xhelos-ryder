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
class movearmytoregionwork extends work {

    private $oRegion;
    private $oOriginRegion;
    private $oTargetRegion;
    private $oPlanet;
    private $oArmies;
    private $oTeam;
    private $oOriginBattle;

    //put your code herepublic function execute(){ 
    public function __construct() {//Necesitamos llamar al constructor
        parent::__construct();
    }

    public function execute() {
        $dt = null;
        $this->setIfOldDtIsOkWithCallback("executeSendArmiesToRegionDt");
        $this->setIfOldDtIsOkWithCallback("executeChangeTeamRegionIdDt");

        $this->setParam(_X_VAR_REGION_ID, $this->getOriginRegion()->getId() );
        $this->doInnerWork(new regiondeowningwork());

        //Ya no se actualiza la region aqui, si no cuando recien llega en battleutil
        //$this->setParam(_X_VAR_REGION_ID, $this->getTargetRegion()->getId() );
        //$this->doInnerWork(new regionowningwork());

        $this->setIfAllOkText("El Equipo procede a moverse a la nueva region");
        return $dt;
    }

    protected function executeSendArmiesToRegionDt() {
        $dt = new datatransfer();
        $oArmies = $this->getArmies();
        $oOriginRegion = $this->getOriginRegion();
        $oTargetRegion = $this->getTargetRegion();
        $oPlanet = $this->getPlanet();        
        $resp = $oArmies->sendArmiesToRegion($oOriginRegion->getId(), $oTargetRegion->getId(), $oPlanet->getId());
        if (!$resp) {
            $dt->error("No se puedo mover las unidades de region a region");
        } else {
            $dt->success("Se pudo mover las unidades");
        }
        return $dt;
    }

    protected function executeChangeTeamRegionIdDt() {
        $dt = new datatransfer();
        $oTeam = $this->getTeam();
        $oTeam->setRegionId(_X_TEAM_TRAVEL_REGION);
        $dt->success("Se pudo mover el team");
        return $dt;
    }

    public function validate() {
        $this->setIfOldDtIsOkWithCallback("validateOriginRegionExistDt");
        $this->setIfOldDtIsOkWithCallback("validateTargetRegionExistDt");
        $this->setIfOldDtIsOkWithCallback("validateTeamExistDt");
        $this->setIfOldDtIsOkWithCallback("validateOriginRegionIsInPlanetDt");
        $this->setIfOldDtIsOkWithCallback("validateTargetRegionIsInPlanetDt");
        $this->setIfOldDtIsOkWithCallback("validateTeamWithAtLeastOneArmyDt");
        $this->setIfOldDtIsOkWithCallback("validateAllArmiesOfTeamInOriginRegionDt");
        $this->setIfOldDtIsOkWithCallback("validateTeamIsFromPlayerDt");
        $this->setIfOldDtIsOkWithCallback("validateAllArmiesAreFromPlayerDt");
        $this->setIfOldDtIsOkWithCallback("validateNoBattleInOriginRegionDt");
        $this->setIfOldDtIsOkWithCallback("validateAdyacentsRegionsDt");        
    }

    protected function validateOriginRegionExistDt() {
        $dt = new datatransfer();
        $oRegion = $this->getOriginRegion();

        if (!$oRegion->exist()) {
            $dt->error("La Region desde donde parten no existe");
        } else {
            $dt->success("La Region desde donde parten existe");
        }
        return $dt;
    }

    protected function validateTargetRegionExistDt() {
        $dt = new datatransfer();
        $oRegion = $this->getTargetRegion();
        if (!$oRegion->exist()) {
            $dt->error("La Region hasta donde quieren llegar no existe");
        } else {
            $dt->success("La Region hasta donde quieren llegar si existe");
        }
        return $dt;
    }

    protected function validateTeamExistDt() {
        $dt = new datatransfer();
        $oTeam = $this->getTeam();
        if (!$oTeam->exist()) {
            $dt->error("El equipo no existe");
        } else {
            $dt->success("Si existe el equipo");
        }
        return $dt;
    }

    protected function validateOriginRegionIsInPlanetDt() {
        $dt = new datatransfer();
        $oPlanet = $this->getPlanet();
        $oOriginRegion = $this->getOriginRegion();

        if ($oOriginRegion->getPlanetId() != $oPlanet->getId()) {
            $dt->error("'La region de origen del ataque no esta en el planeta " . $oPlanet->getName());
        } else {
            $dt->success('La region de origen SI esta en el planeta ' . $oPlanet->getName());
        }
        return $dt;
    }

    protected function validateTargetRegionIsInPlanetDt() {
        $dt = new datatransfer();
        $oPlanet = $this->getPlanet();
        $oTargetRegion = $this->getTargetRegion();

        if ($oTargetRegion->getPlanetId() != $oPlanet->getId()) {
            $dt->error("'La region objetivo no esta en el planeta " . $oPlanet->getName());
        } else {
            $dt->success('La region objetivo SI esta en el planeta ' . $oPlanet->getName());
        }
        return $dt;
    }

    protected function validateTeamWithAtLeastOneArmyDt() {
        $dt = new datatransfer();
        $oArmies = $this->getArmies();
        if ($oArmies->size() == 0) {
            $dt->error('El equipo no contiene unidades, ingrese al menos una unidad al equipo ');
        } else {
            $dt->success('El equipo contiene al menos una unidad');
        }
        return $dt;
    }

    protected function validateAllArmiesOfTeamInOriginRegionDt() {
        $dt = new datatransfer();
        $oArmies = $this->getArmies();
        if (!$oArmies->getUnique(_X_VAR_REGION_ID)) {
            $dt->error('No todas las unidades del equipo estan en la region de origen');
        } else {
            $dt->success('Todas las unidades estan en la region de origen');
        }
        return $dt;
    }

    protected function validateTeamIsFromPlayerDt() {
        $dt = new datatransfer();
        $oTeam = $this->getTeam();
        $oPlayer = $this->getPlayer();

        if ($oTeam->getPlayerId() != $oPlayer->getId()) {
            $dt->error('El equipo no te pertenece');
        } else {
            $dt->success('El equipo le pertenece');
        }
        return $dt;
    }

    protected function validateAllArmiesAreFromPlayerDt() {
        $dt = new datatransfer();
        $oArmies = $this->getArmies();
        $oPlayer = $this->getPlayer();

        if (!$oArmies->areAllFromPlayer($oPlayer->getId())) {
            $dt->error('Las unidades del equipo no te pertenecen');
        } else {
            $dt->success('Las unidades le pertenecen al jugador elegido');
        }
        return $dt;
    }

    protected function validateNoBattleInOriginRegionDt() {
        $dt = new datatransfer();
        $oBattle = $this->getOriginBattle();
        if ($oBattle->isActive()) {
            $dt->error('El Equipo no puede salir de la region de origen hasta que haya terminado la batalla');
        } else {
            $dt->success('Se puede mover porque no existe una batalla en la region de origen');
        }
        return $dt;
    }
     protected function validateAdyacentsRegionsDt() {
        //$dt = new datatransfer();
        $oOriginRegion = $this->getOriginRegion();
        $oTargetRegion = $this->getTargetRegion();        
        $dt = $this->getOState()->areRegionAdyacentDT($oOriginRegion->getId(),$oTargetRegion->getId());

        if (!$dt->ok()) {
            $dt->error($dt->getText());
        } else {
            $dt->success('Son regiones adyacentes');
        }
        return $dt;
    }   
    

    ////////////////////////////////////
    // GETTERS
    /////////////////////////////////// 
    public function getOriginBattle() {
        if (empty($this->oOriginBattle)) {
            $battleman = battleman::singleton();
            $oOriginRegion = $this->getOriginRegion();
            $battle = $battleman->findByRegionId($oOriginRegion->getId());
            $this->oOriginBattle = $battle;
        }
        return $this->oOriginBattle;
    }

    public function getPlanet() {
         if (empty($this->oPlanet)) {
            $planetman = planetman::singleton();
            $planetId = $this->getParam(_X_VAR_PLANET_ID);
            $oPlanet = $planetman->findById($planetId);
            $this->oPlanet = $oPlanet;
        }
        return $this->oPlanet;       
    }

    public function getTargetRegion() {
          if (empty($this->oTargetRegion)) {
            $regionman = regionman::singleton();
            $targetRegionId = $this->getParam(_X_VAR_TARGET_REGION_ID);
            $object = $regionman->findById($targetRegionId);
            $this->oTargetRegion = $object;
        }
        return $this->oTargetRegion;        
    }

    public function getOriginRegion() {
        if (empty($this->oOriginRegion)) {
            $regionman = regionman::singleton();
            $targetRegionId = $this->getParam(_X_VAR_ORIGIN_REGION_ID);
            $object = $regionman->findById($targetRegionId);
            $this->oOriginRegion = $object;
        }
        return $this->oOriginRegion;    
    }

    public function getTeam() {
         if (empty($this->oTeam)) {
            $teamman = teamman::singleton();
            $teamId = $this->getParam(_X_VAR_TEAM_ID);
            $object = $teamman->findById($teamId);
            $this->oTeam = $object;
        }
        return $this->oTeam;  
    }

    public function getArmies() {
        if (empty($this->oArmies)) {
            $oArmies = new armies();
            $oTeam = $this->getTeam();
            $oArmies->initByTeamId($oTeam->getId());
            $this->oArmies = $oArmies;
        }
        return $this->oArmies;
    }


    protected function validateParams() {
        $this->validateParamIfNotEmpty(_X_VAR_PLAYER_ID);                        
        $this->validateParamIfNotEmpty(_X_VAR_PLANET_ID);
        $this->validateParamIfNotEmpty(_X_VAR_ORIGIN_REGION_ID);        
        $this->validateParamIfNotEmpty(_X_VAR_TARGET_REGION_ID);                
        $this->validateParamIfNotEmpty(_X_VAR_TEAM_ID);
    }

    ////////////////////////////////////
    // Privadad
    ///////////////////////////////////
    public function breakif() {
        
    }

}

?>
