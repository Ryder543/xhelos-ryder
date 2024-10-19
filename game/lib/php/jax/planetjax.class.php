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

class planetjax extends jax {
    
    public function __construct() {
        parent::__construct();
        if($this->isOk()){
        $this->init($this->getRequestParam('planet_id'),_X_VIEW_TYPE_PLANET);
        }
    }

    public function executeCreateSecundaryColony(){  
        $this->setGameParam(_X_VAR_COLONY_TYPE,_X_COLONY_TYPE_SECONDARY);
        $this->setGameParam(_X_VAR_PLAYER_ID,$this->getCurrentPlayer()->getId());
        $this->doWork(new createcolonywork());
    }
    
    public function executeDeleteSecundaryColony(){ 
        $this->setGameParam(_X_VAR_PLAYER_ID,$this->getCurrentPlayer()->getId());        
        $this->setGameParam(_X_VAR_COLONY_TYPE,_X_COLONY_TYPE_SECONDARY);
        $this->setGameParam(_X_VAR_OWNER_ID,$this->getCurrentPlayer()->getId());        
        $this->doWork(new removecolonywork());        
    }

    
    public function executeCreateNewTeam(){
         $regionId = $this->getRequestParam("region_id");
         $dt = $this->getOState()->createNewTeamDt($regionId) ;
         $this->setIfOldDtIsOk($dt);
    }
    
    public function executeArmyTransferTeam(){ 
         $dt = $this->getOState()->transferArmyTeam($this->getRequestParam("army_id"),$this->getRequestParam("team_id"));
         $this->setIfOldDtIsOk($dt);            
    }
    
    public function executeDeleteTeam(){
        $dt = $this->getOState()->deleteTeamDt($this->getRequestParam("team_id"));
        $this->setIfOldDtIsOk($dt);            
    }
    
    public function executeAttackRegion(){
        $this->setGameParam(_X_VAR_PLAYER_ID,$this->getCurrentPlayer()->getId());        
        $this->doWork(new movearmytoregionwork());      
        /*
        $dt = $this->getOState()->attackRegion($this->getRequestParam("origin_region_id"),$this->getRequestParam("target_region_id"),$this->getRequestParam("team_id"));
        $this->setIfOldDtIsOk($dt);   */         
    }
    
    protected function prepareRequestParams() {
        $this->valStringRequestParam('ajax_action');
        $this->valNumericRequestParam('planet_id');
        if (isset($_POST['region_id']) && (!empty($_POST['region_id']))) {
            $this->valNumericRequestParam('region_id');
        }
        if (isset($_POST['army_id']) && (!empty($_POST['army_id']))) {
            $this->valNumericRequestParam("army_id");
        }
        if (isset($_POST['team_id'])) {//Porque puede ser cero
            $this->valNumericAndNullRequestParam("team_id");
        }
        if (isset($_POST['origin_region_id']) && (!empty($_POST['origin_region_id']))) {
            $this->valNumericRequestParam("origin_region_id");
        }
        if (isset($_POST['target_region_id']) && (!empty($_POST['target_region_id']))) {
            $this->valNumericRequestParam("target_region_id");
        }
        if (isset($_POST['colony_id']) && (!empty($_POST['colony_id']))) {
            $this->valNumericRequestParam("colony_id");
        }          
    }

    
}

?>
