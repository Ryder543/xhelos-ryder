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
class createcolonywork extends work {
    
    public function __construct(){//Necesitamos llamar al constructor
        parent::__construct();
    }
    //put your code herepublic function execute(){ 
    public function execute() {
        $regionowningwork = new regionowningwork();
        switch ($this->getParam(_X_VAR_COLONY_TYPE)) {
            case _X_COLONY_TYPE_SECONDARY:
                $this->setIfOldDtIsOkWithCallback("executeCreateHumanSecondaryColonyDt");
                $this->setIfOldDtIsOkWithCallback("executeSubstractResourcesToPlayerDt");
                $regionowningwork->setOrigin(_X_WORK_ORIGIN_CREATESECONDARYCOLONY);
                break;

            case _X_COLONY_TYPE_PRIMARY:
                $this->setIfOldDtIsOkWithCallback("executeCreateHumanHomePrimaryColonyDt");
                $regionowningwork->setOrigin(_X_WORK_ORIGIN_CREATEPRIMARYCOLONY);                
                break;

            default:
                debug::warning("createColonyWork->execute No existe tipo de colonia[" . $type . "] seteado");
        }
        $this->doInnerWork($regionowningwork); // Hacemos que la region le pertenesca al usuario
        $this->setIfAllOkText("Su nueva colonia esta en proceso de creacion");
        //Grabamos el DT que devuelve la colonia al work
    }

    public function validate() {
        $this->setIfOldDtIsOkWithCallback("validateMaxColonyDt");
        $this->setIfOldDtIsOkWithCallback("validateMaxColoniesInRegionDt");
        $this->setIfOldDtIsOkWithCallback("validateResourcesAvailableDt");
        $this->setIfOldDtIsOkWithCallback("validateFreePositionDt");
        $this->setIfOldDtIsOkWithCallback("validateNotNearBordersDt");
        $this->setIfOldDtIsOkWithCallback("validateNotNearOtherBuildingsDt");
        $this->setIfOldDtIsOkWithCallback("validateRegionWithoutEnemyUnitsIfSecundaryDt");
        $this->setIfOldDtIsOkWithCallback("validatePlanetaryPassDt");
        $this->setIfOldDtIsOkWithCallback("validateRegionalPassDt");
        $this->setIfOldDtIsOkWithCallback("validateStellarPassDt");
        $this->setIfOldDtIsOkWithCallback("validateNotPrimaryColonyIndexIfColonySecundaryDt"); 
    }

    protected function executeCreateHumanSecondaryColonyDt() {
        $ecu = new entitycolonyutil();
        $playerId = $this->getPlayer()->getId();
        $dt = $ecu->createHumanSecundaryColony($this->getParam(_X_VAR_REGION_ID), $playerId);
        $this->setExecuteDt($dt);
        return $dt;
    }

    protected function executeCreateHumanHomePrimaryColonyDt() {
        $ecu = new entitycolonyutil();
        $playerId = $this->getPlayer()->getId();
        $dt = $ecu->createPlayerHomePrimaryColony($this->getParam(_X_VAR_REGION_ID), $playerId);
        $this->setExecuteDt($dt);
        return $dt;
    }

    protected function executeSubstractResourcesToPlayerDt() {
        $playerId = $this->getPlayer()->getId();
        $rbrman = regionbuildingresourceman::singleton();
        $oCost = $rbrman->findSecondaryColonyCost();
        $playerutil = new playerutil();
        $dt = $playerutil->substractResourcesToPlayerDt($oCost->getResources(), $playerId);
        if (!$dt->ok()) {
            $dt->error($dt->getText());
        } else {
            $dt->success("Se pudo substraer los recursos del player");
        }
        return $dt;
    }

    protected function validateMaxColonyDt() {
        $dt = new datatransfer();
        //1) Obtener el numero maximo de colonias que puede tener,
        $ru = new researchutil();
        $maxColNum = $ru->getPlayerMaxPossibleColonies();
        //$maxColNum = $this->getCurrentPlayer()->getMaxPossibleColonies();
        $playerId = $this->getPlayer()->getId();
        //) Obtener el numero maximo de colonias que tiene
        $colonies = new colonies();
        $colonies->initActiveByPlayerId($playerId);
        $playerColNum = $colonies->size();
        if ($maxColNum <= $playerColNum) {
            $dt->error("Ya tienes construidas[" . $colonies->size() . "] el numero maximo de colonias permitidas[" . $maxColNum . "]");
        } else {
            $dt->success("Puedes construir mas colonias");
        }
        return $dt;
    }

    protected function validateMaxColoniesInRegionDt() {
        $dt = new datatransfer();
        $colonies = new colonies();
        $regionId = $this->getParam(_X_VAR_REGION_ID);
        $colonies->initActiveByRegionId($regionId);
        if ($colonies->size() >= _X_COLONY_MAX_NUMBER_PER_REGION) {
            $dt->error("Superaste el limite de colonias por region[" . _X_COLONY_MAX_NUMBER_PER_REGION . "]");
        } else {
            $dt->success("No pasaste el limite de colonias por region");
        }
        return $dt;
    }

    protected function validateResourcesAvailableDt() {
        $rbrman = regionbuildingresourceman::singleton();
        $oCost = $rbrman->findSecondaryColonyCost();
        $oPlayer = $this->getPlayer();
        $playerutil = new playerutil();
        $dt = $playerutil->haveEnoughResourcesDt($oCost->getResources(), $oPlayer->getId());
        return $dt;
    }

    protected function validateFreePositionDt() {
        //Validar que la posicion este libre
        $colonyType = $this->getParam(_X_VAR_COLONY_TYPE);
        $oRegion = $this->getRegion();
        $cachedMap = $oRegion->getCachedMapUtil();
        //$cachedMap = new cachedmaputil();
        //Obtencion de la Posicion X y Y de la colonia a crear
        $startIndex = cachedmaputil::index($this->getXOfColony(), $this->getYOfColony(), $cachedMap->getTotalX());
        $indexes = $cachedMap->getIndexesForColony($startIndex, $colonyType);
        $dt = new datatransfer();
        if ($cachedMap->areTilesObstaculized($indexes)) {
            $dt->error("No se puede crear la colonia porque la posicion de la colonia en x[" . $this->getXOfColony() . "]y[" . $this->getYOfColony() . "] y alrededores esta obstaculizado");
        } else {
            $dt->success("La posicion esta libre");
        }
        return $dt;
    }

    protected function validateNotNearBordersDt() {
        $dt = new datatransfer();
        $colonyType = $this->getParam(_X_VAR_COLONY_TYPE);
        $oRegion = $this->getRegion();
        $map = $oRegion->getCachedMapUtil();
        $startIndex = maputil::index($this->getXOfColony(), $this->getYOfColony(), $map->getTotalX());
        $indexes = $map->getIndexesForColony($startIndex, $colonyType);
        if ($map->areIndexesNearBorder($indexes, _X_COLONY_MINIMUN_RANGE_TO_BORDER)) {
            $dt->error("No se puede crear la colonia porque esta cerca del borde[" . $this->getXOfColony() . "]y[" . $this->getYOfColony() . "] del mapa");
        } else {
            $dt->success("La Colonia no esta cerca del borde");
        }
        return $dt;
    }

    protected function validateNotNearOtherBuildingsDt() {
        //Bueee, aun no definamos esto!
        $dt = new datatransfer();
        $dt->success("TEMPORAL2", "regionColonyCreateColonyAction");
        return $dt;
    }

    protected function validateRegionalPassDt() {
        $dt = new datatransfer();
        $dt->success("TEMPORAL2", "regionColonyCreateColonyAction");
        return $dt;
    }

    protected function validatePlanetaryPassDt() {
        $dt = new datatransfer();
        $dt->success("TEMPORAL2", "regionColonyCreateColonyAction");
        return $dt;
    }

    protected function validateStellarPassDt() {
        $dt = new datatransfer();
        $dt->success("TEMPORAL2", "regionColonyCreateColonyAction");
        return $dt;
    }

    protected function validateRegionWithoutEnemyUnitsIfSecundaryDt() {
        $dt = new datatransfer();
        if ($this->getParam(_X_VAR_COLONY_TYPE) == _X_COLONY_TYPE_SECONDARY) {
            
            $regionId = $this->getRegion()->getId();
            $playerId = $this->getPlayer()->getId();
            $armies = new armies();
            $armies->initByAliveEnemiesOfPlayerInRegion($playerId, $regionId);
            if ($armies->size() > 0) {
                $dt->error("Existen unidades enemigas en la region");
            } else {
                $dt->success("La region esta libre de unidades enemigas al jugador[" . $this->getPlayer()->getName() . "]");
            }
        } else {
            $dt->success("Es una colonia primaria");
        }

        return $dt;
    }

    //Validamos de que si es una colonia secundaria, no creemos una colonia en 
    //Una posicion que le pertenece a una colonia primaria
    protected function validateNotPrimaryColonyIndexIfColonySecundaryDt() {
        $dt = new datatransfer();
        $type = $this->getParam(_X_VAR_COLONY_TYPE);
        if ($type == _X_COLONY_TYPE_SECONDARY) {

            $indexes = entityregionutil::IndexOfPrimaryPlayerColoniesInInitialPlanet();
            $oRegion = $this->getRegion();
            $map = $oRegion->getCachedMapUtil();
            $startIndex = $oRegion->getPlanetPosition();

            if (in_array($startIndex, $indexes)) {
                $dt->error("El destino dicta que no se pueda construir una colonia en esta region");
            } else {
                $dt->success("La region no es una region de colonia primaria inicial");
            }
        } else {
            $dt->success("La region  es colonia primaria por lo que puede crearse aqui");
        }
        return $dt;
    }

    protected function validateParams() {
        $this->validateParamIfNotEmpty(_X_VAR_REGION_ID);
        $this->validateParamIfNotEmpty(_X_VAR_COLONY_TYPE);
        $this->validateParamIfNotEmpty(_X_VAR_PLAYER_ID); //Usamos getPlayer, definido en work.class
    }

    public function preValidatePrimaryColony() {
        $this->setIfOldDtIsOkWithCallback("validateMaxColoniesInRegionDt");
        $this->setIfOldDtIsOkWithCallback("validateFreePositionDt");
        $this->setIfOldDtIsOkWithCallback("validateNotNearBordersDt");
        $this->setIfOldDtIsOkWithCallback("validateNotNearOtherBuildingsDt");
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
