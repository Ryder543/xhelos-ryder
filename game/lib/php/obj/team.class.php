<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of team
 *
 * @author jeeba
 */
class team  extends identity {
    public function __construct() {
         $args = func_get_args();
            if(!empty($args)){
              debug::error('team se inicio con argumentos','team->constructor');
            }
            parent::__construct();
    }
    
    public function getPlayerId(){
        return $this->getParam('player_id');
    }
    
    public function getRegionId(){
        return $this->getParam('region_id');
    }    
    
    public function postRefresh() {
        
    }
    
    
    public function setRegionId($regionId){
        $id = $this->getId();
        $sql = "UPDATE teams SET region_id = ".$regionId." WHERE id=" . $id;
        $this->db->updateEntityDt($sql,"team",$id);
        //$this->setDataDirty();
    }

    public function prepareRaw() {
        $preparedRaw = $this->raw;       
        //unset($preparedRaw['map']);
        return $preparedRaw;
    }
    
    
    //put your code here
}

?>
