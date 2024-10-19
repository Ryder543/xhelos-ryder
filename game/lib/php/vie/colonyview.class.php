<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad

/* * *****************************************************
 * battleman: Gestion de Batallas 
 * **************************************************** */

class colonyview extends view {

    private $ext_colony_id;
    private $oColony;
    
    private $db;


    public function __construct($colony_id) {
        $this->ext_colony_id = $colony_id;
        $this->db = db::singleton();
    }
    
    public function validateStateDt(){
        $dt = new datatransfer();
        $colonies = new colonies();
        $colonies->initActiveByPlayerId($this->getPlayer()->getId());
        if($colonies->findByParam($this->getColonyId(),"id")){
            $dt->success('La colonia['.$this->getColonyId().'] le pertenece al jugador '.$this->getPlayer()->getParam("username"));
        }
        else{
            $dt->error('Esta colonia no te pertenece');
        }
        return $dt;
        //Verificar que la colonia le pertenezca al jugador 
    }
    
    /********************************************
    * render: Muestra la imagen de la colonia
    ********************************************/    
    public function render() {

        $totalX = $this->getColony()->getSizeX();
        $totalY = $this->getColony()->getSizeY();
        $map = $this->getColony()->getArrayMap();
        //debug::log($map,'colonystate->render Map x['.$totalX.']y['.$totalY.']');
        
        $DOM = ''; 
        for ($y = 0; $y < $totalY; $y++) {
            $DOM = $DOM . '<div class="row" id="Row' . $y . '">';
            for ($x = 0; $x < $totalX; $x++) {
                $index = maputil::index($x, $y, $totalX);
                //debug::log($index,'colonystate->render index');
                $DOM = $DOM . '<div class="column buildable type'.$map[$index].'" id="Column'.$x.'" x="'.$x.'" y="'.$y.'"></div>';
            }
            $DOM = $DOM . '<div class="clear"></div></div>';
        }
        // Colocamos la estrella
        echo $DOM;
        
        /*$ics = new ics();
        $ics->initActiveByColonyId($this->getColonyId());
        debug::log($ics,'colonystate->render ics');*/
        
    }

    /********************************************
    * getState: Obtiene el estado de la colonia
    ********************************************/
    public function prepareState() {
        $_xeno = array();
        $_xeno['buildings'] = $this->getPossibleBuildings()->getRaw();
        $_xeno['constructions'] = $this->getConstructions()->getRaw();
        $_xeno['colony'] = $this->getColony()->getRaw();
        $_xeno['player'] = $this->getPlayer()->getRaw();
        return $_xeno;
    }
    
    public function updateState(){
        $ics = new ics();
        $ics->initUpdateableActiveByColonyId($this->getColonyId());
        //debug::log($ics,'colonystate->updateState ICS colonyId['.$this->getColonyId().']');
        if($ics->exist()){
            foreach($ics as $i => $ic ){
                $dt = $ic->levelUpDt();
                //debug::log($dt,'colonystate->updateState IC');
            }
        }
    }
    public function getDt(){
        //debug::info('colonystate->getDt estamos entregando un Dt sin validar nada');
        $dt = new datatransfer();
        $dt->success('Esto es temporal');
        return $dt;
    }
    
    
    /********************************************
    * getRegionId: Obtiene el ID de la Region
    ********************************************/
    public function getColonyId(){
      if(!empty($this->ext_colony_id))
      {
        return $this->ext_colony_id;
      }
      else{
        debug::warning('El id de la colonia no existe ','colonystate->getColonyId ');
        return false;
      }
    }    
    /********************************************
    * getColony: Obtener Region
    ********************************************/
    public function getColony(){   
        if(empty($this->oColony)){
          $this->oColony = $this->man("colony")->findById($this->getColonyId());
          if(empty($this->oColony))
          {
              debug::error('La colonia no pudo cargarse','getColony colonystate.class.php');      
          } 
        }
        return $this->oColony;//[WHY] Evitar un bucle, por eso devolvemos region defrente
    }
    /***************************************************************************************
    * getPossibleBuildings: Obtener las construcciones que puede construir el jugador actual
    ***************************************************************************************/
    public function getPossibleBuildings(){
        $ibs = new ibs();
        $ibs->initActive();
        //debug::log($ibs,'colonystate->getPossibleBuildings');
        foreach($ibs as $i =>$ib)
        {
            $costs = $this->man('ib')->getBuildingCost($ib->getId(),_X_COLONY_INITIAL_CONSTRUCTED_LEVEL);
            $ibs->createParam($i,'costs',$costs  );
        }
        //debug::log($ibs,'colonystate getPossibleBuildings ibs');
        return $ibs;
    }
    
     public function getConstructions(){
        $colonyId = $this->getColonyId();
        $ics = new ics();
        $ics->initActiveByColonyId($colonyId);
        //debug::log($ics,'colonystate->getConstructions');
        //debug::log($ics->exist(),'colonystate->getConstructions exists');

        foreach($ics as $i =>$ic)
        {
            //debug::log($ic,'colonystate->getConstructions');
            $costs = $this->man('ib')->getBuildingCost($ic->getBuildingId(),$ic->getNextLevel());
            $ics->createParam($i,'costs',$costs  );
        } 

        
        return $ics;
    }   
    
    public function upgradeConstructionDt($colonyId,$coordX,$coordY){
        $ic = $this->man("ic")->findByColonyIdByCoord($colonyId,$coordX,$coordY);
        $dt = $this->validateAndSubstracInternalBuildingCostDt($colonyId, $ic->getBuildingId(), $ic->getNextLevel());
        //debug::log($dt,'colonystate->upgradeConstructionDt dt->validacion');
        if($dt->isValid()){
            $costs = $this->man("ib")->getBuildingCost($ic->getBuildingId(),$ic->getNextLevel());
            //debug::log($costs,'colonystate->upgradeConstructionDt costes');
            $dt = $ic->initUpgradeNextLevelDt($costs['time']);
            //Para obtener los nuevos datos de actualizacion
            $updated_ic = $this->man("ic")->findByColonyIdByCoord($colonyId,$coordX,$coordY);
            $dt->setData($updated_ic);
        }
        $dt->setData($ic);
        return $dt;
    }
    
    public function construction2building(ic $constr){
        return $this->man('ib')->findById($constr->getBuildingId());
    }    
    
    
    public function validateAndSubstracInternalBuildingCostDt($colonyId,$buildingId,$level){
        $colony = $this->getColony();
        //$res = $colony->getResources();
        $res = $this->getPlayer()->getResources();
        $ib = $this->man("ib")->findById($buildingId);
        $cost = $this->man("ib")->getBuildingCost($buildingId,$level);
        $dt = new datatransfer();
        $successText ="Se puede construir el building ".$ib->getName()."[".$ib->getId()."] en la colonia ".$colony->getName()."[".$colony->getId()."]";
        $dt->success($successText);
        
        if(!$cost){
            $dt->error('El maximo nivel para la construccion actual es el nivel '. ($level-1));
        }
        else{
            for($i=1;$i<=_X_TOTAL_RESOURCES;$i++){
                //debug::log('entityutil->validateAndSubstracInternalBuildingCostDt');
                $resIndex = 'res'.$i;
                if(!empty($cost[$resIndex]) ){

                    if( !($res[$resIndex] >= $cost[$resIndex]) ){
                        //debug::log('No Se puede construir el building '.$ib->getName().'['.$ib->getId().'] en la colonia '.$colony->getName().'['.$colony->getId().'], Recurso['.$resIndex.'] en la colonia['.$res[$resIndex].'] < costo['.$cost[$resIndex].']');
                        $dt->error('No se puede construir el building '.$ib->getName().'['.$ib->getId().'] en la colonia '.$colony->getName().'['.$colony->getId().'], Recurso['.$resIndex.'] en la colonia['.$res[$resIndex].'] < costo['.$cost[$resIndex].']');
                        break;
                    }
                    else{
                        //debug::log('Se puede construir el building '.$ib->getName().'['.$ib->getId().'] en la colonia '.$colony->getName().'['.$colony->getId().'], Recurso['.$resIndex.'] en la colonia['.$res[$resIndex].'] < costo['.$cost[$resIndex].']');
                    }
                }
                else{
                    //debug::log($cost[$resIndex],'Es empty');
                    //debug::log($cost,'Es empty cost');
                    //debug::log($resIndex,'Es empty index');
                }
            }
            //debug::log($dt,'entityutil->validateAndSubstracInternalBuildingCostDt dt 1');
            if($dt->isValid()){
                //debug::log($dt,'entityutil->validateAndSubstracInternalBuildingCostDt dt 2');
                $dt = $this->getColony()->subtractResourcesDt($cost);
                //debug::log($dt,'entityutil->validateAndSubstracInternalBuildingCostDt dt 4');
            }
            else{
                //debug::log($dt,'entityutil->validateAndSubstracInternalBuildingCostDt NO dt 3');
            }
        }
        return $dt;
    }

    public function getTitle() {
        return "Colonia ".$this->getColony()->getName();
    }
    
}

//starstate
?>
