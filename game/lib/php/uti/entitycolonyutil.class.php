<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad

/* * ****************************************************************
 * entityutil: Utilidad para creacion y mantenimiento de entidades
 * **************************************************************** */

class entitycolonyutil extends util {

    ///////////////////////////////////////////////////////////////////////////////////////
    //PROPIEDADES
    ///////////////////////////////////////////////////////////////////////////////////////    
    ///////////////////////////////////////////////////////////////////////////////////////
    //CONSTRUCTOR
    ///////////////////////////////////////////////////////////////////////////////////////    

    public function __construct() {
        parent::__construct();
    }

    /*     * *******************************************************************************
     * CREATE COLONY
     * ******************************************************************************* */

    public function createAlienPrimaryColony($regionId) {
        $resources = new resources();
        $resources->initAllActiveStrategic();
        $resource = $resources->rand();
        $cm = colonyman::singleton();
        $oRegion = $this->man("region")->findById($regionId);
        $data["id"] = $this->db->nextId('colonies');
        $data["name"] = $oRegion->getName(); //[TODO] Mover la creacion de nombres a algun lugar que valga la pena,.. si no
        $data['region_id'] = $regionId;
        $data['region_x'] = _X_COLONY_AI_INITIAL_X_POSITION;
        $data['region_y'] = _X_COLONY_AI_INITIAL_Y_POSITION;
        $data['type'] = _X_COLONY_TYPE_PRIMARY;
        $data['map'] = db::toPersistenceArray(array());
        $data['level'] = _X_COLONY_INITIAL_ALIEN_LEVEL;
        $data['owner_id'] = _X_REGION_ALIEN_PLAYER_ID_FOR_COLONIES;
        $data['size_x'] = _X_COLONY_INITIAL_X_SIZE;
        $data['size_y'] = _X_COLONY_INITIAL_Y_SIZE;
        $data['status'] = _X_ACTIVE;
        $data['build_start'] = " now()";
        $data['build_end'] = " now()";
        $data['build_state'] = _X_COLONY_BUILD_STATE_DONE;        
        $dt = $cm->createDt($data);
        $dt->setData($data);
        return $dt;
    }

    public function createPlayerHomePrimaryColony($regionId, $playerId) {
        $cm = colonyman::singleton();
        $oRegion = $this->man("region")->findById($regionId);
        $data["id"] = $this->db->nextId('colonies');
        $data["name"] = $oRegion->getName(); //[TODO] Mover la creacion de nombres a algun lugar que valga la pena,.. si no
        $data['region_id'] = $regionId;
        $data['region_x'] = _X_COLONY_INITIAL_X_POSITION;
        $data['region_y'] = _X_COLONY_INITIAL_Y_POSITION;
        $data['type'] = _X_COLONY_TYPE_PRIMARY;
        $data['map'] =  _X_COLONY_INITIAL_MAP;
        $data['level'] = _X_COLONY_INITIAL_LEVEL;
        $data['owner_id'] = $playerId;
        $data['size_x'] = _X_COLONY_INITIAL_X_SIZE;
        $data['size_y'] = _X_COLONY_INITIAL_Y_SIZE;
        $data['status'] = _X_ACTIVE;
        $data['build_start'] = " now()";
        $data['build_end'] = " now()";
        $data['build_state'] = _X_COLONY_BUILD_STATE_DONE;        
        $dt = $cm->createDt($data);
        $dt->setData($data);
        return $dt;
    }
    
    
     /*********************************************************************************
     * CREATE Colonia Secundaria
     * ******************************************************************************* */
    public function createHumanSecundaryColony($regionId, $playerId) {
        $cm = colonyman::singleton();
        $ru = new researchutil();
        
        $oRegion = $this->man("region")->findById($regionId);
        $data["id"] = $this->db->nextId('colonies');
        $data["name"] = $oRegion->getName(); //[TODO] Mover la creacion de nombres a algun lugar que valga la pena,.. si no
        $data['region_id'] = $regionId;
        $data['region_x'] = _X_COLONY_INITIAL_X_POSITION;
        $data['region_y'] = _X_COLONY_INITIAL_Y_POSITION;
        $data['type'] = _X_COLONY_TYPE_SECONDARY;
        $data['map'] =  _X_COLONY_INITIAL_SECONDARY_MAP;
        $data['level'] = _X_COLONY_INITIAL_LEVEL;
        $data['owner_id'] = $playerId;
        $data['size_x'] = _X_COLONY_INITIAL_X_SECONDARY_SIZE;
        $data['size_y'] = _X_COLONY_INITIAL_Y_SECONDARY_SIZE;
        $data['status'] = _X_ACTIVE;
        $data['build_start'] = " now()";
        $data['build_end'] = " now() + '".$ru->getPlayerBuildTimeOfSecondaryColony()." seconds'";
        $data['build_state'] = _X_COLONY_BUILD_STATE_BUILDING;                
        $dt = $cm->createDt($data);
        $dt->setData($data);
        return $dt;
    }
    
    
    /*********************************************************************************
     * CREATE COLONY CONSTRUCTIONS
     * ******************************************************************************* */

    public function createDefaultConstructionInColonyDt($colonyId) {
        //Construir un Core
        $dt = $this->createConstructionInColonyDt($colonyId, _X_BUILDING_CORE, _X_BUILDING_DEFAULT_CORE_POSITION_X, _X_BUILDING_DEFAULT_CORE_POSITION_Y, true);
        if (!$dt->isValid()) {
            return $dt;
        }
        //Construir un Respiradero
        $dt = $this->createConstructionInColonyDt($colonyId, _X_BUILDING_RESPIRADERO, _X_BUILDING_DEFAULT_RESPIRADERO_POSITION_X, _X_BUILDING_DEFAULT_RESPIRADERO_POSITION_Y, true);
        return $dt;
    }

    public function createConstructionInColonyDt($colonyId, $buildingId, $buildingX, $buildingY, $rightNow = false) {
        //debug::log($dt,'entityutil->createConstructionInColonyDt dt');
        //$oBuilding = $this->man("ib")->findById($buildingId);
        $res = $this->man("ib")->getBuildingCost($buildingId, _X_COLONY_INITIAL_CONSTRUCTED_LEVEL);
        //debug::log($oBuilding,'entityutil->createConstructionInColonyDt oBuilding');
        $data = array();
        $data["id"] = $this->db->nextId('internal_constructions');
        $data["cityX"] = $buildingX;
        $data["cityY"] = $buildingY;
        $data["colony_id"] = $colonyId;
        $data["internal_building_id"] = $buildingId;
        $data["state"] = _X_ACTIVE;
        $data["build_start"] = 'now()';
        $data['name'] = $this->man('ib')->findById($buildingId)->getName();
        if ($rightNow) {
            $data["build_end"] = 'now()';
            $data["level"] = _X_COLONY_INITIAL_CONSTRUCTED_LEVEL;
            $data["build_state"] = _X_COLONY_BUILD_STATE_DONE;
        } else {
            $data["build_end"] = '(now() + \'' . $res['time'] . '\')';
            $data["level"] = _X_COLONY_INITIAL_BUILDING_LEVEL;
            $data["build_state"] = _X_COLONY_BUILD_STATE_BUILDING;
        }
        //$data['status'] = _X_ACTIVE;

        $dt = $this->man('ic')->createDt($data);

        $dt->setData($data);
        return $dt;
    }

    /*     * *******************************************************************************
     * Delete
     * ****************************************************************************** */
     public function deleteColoniesByRegionIdDt($regionId){
        $sql="delete from internal_constructions where colony_id IN (SELECT id FROM colonies WHERE region_id = ".$regionId.")" ;
        $dt = $this->db->deleteDt($sql);
        $continue = true;
    //Eliminar 
        if($dt->isValid()){
            $sql="DELETE from colonies WHERE region_id = ".$regionId;
            $dt = $this->db->deleteDt($sql);
        }
        else{
            debug::warning($dt,'entityutil->deleteColoniesByRegionIdDt Error Eliminando internal constructions de colonia['.$regionId.']');
        }
        return $dt;
    }
    public function deleteColonyByIdDt($colonyId){
        $sql="delete from internal_constructions where colony_id = ".$colonyId;
        $dt = $this->db->deleteDt($sql);
        $continue = true;
        //Eliminar 
        if($dt->isValid()){
            $sql="DELETE from colonies WHERE id = ".$colonyId;
            $dt = $this->db->deleteDt($sql);
        }
        else{
            debug::warning($dt,'entityutil->deleteColonyById Error Eliminando internal constructions de colonia['.$colonyId.']');
        }
        return $dt;
    }   
    /*     * *******************************************************************************
     * Deletes
     * ******************************************************************************* */
}

?>