<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of drupalstate
 *
 * @author jeeba
 */
class playerutil extends util {

    public function __construct() {
        parent::__construct();
    }

    public function haveEnoughResourcesDt($resCostArray, $playerId) {
        $pman = playerman::singleton();
        $oPlayer = $pman->findById($playerId);
        $resPlayerArray = $oPlayer->getResources();

        $dt = new datatransfer();
        $dt->success();
        $resources = new resources();
        $resources->initAllActive();
         if (count($resCostArray) > 0) {
        foreach ($resources as $resource) {
            $resId = $resource->getId();
            if ($resCostArray[_X_RESOURCE_PREFIX.$resId] > 0) {
                if ($resCostArray[_X_RESOURCE_PREFIX.$resId] > $resPlayerArray[_X_RESOURCE_PREFIX.$resId]) {
                    $faltante = ceil($resCostArray[_X_RESOURCE_PREFIX.$resId] - $resPlayerArray[_X_RESOURCE_PREFIX.$resId]);
                    $dt->error($dt->getText() . "Te falta [" . $faltante . "] del recurso " . $resource->getName() . ".");
                }
            }
        }
         }
         else{
             $dt->success("Los recursos estan vacios"); 
         }
        return $dt;
    }

    public function substractResourcesToPlayerDt($resCostArray, $playerId) {

        $pman = playerman::singleton();
        $oPlayer = $pman->findById($playerId);
        $resPlayerArray = $oPlayer->getResources();

        $dt = new datatransfer();
        $dt->success();
        $resman = resourceman::singleton();
        $resources = new resources();
        $resources->initAllActive();
        $resourcesSize = $resources->size();
        $sql = "UPDATE players SET ";
        $count = 0;
         if (count($resCostArray) > 0) { 
        foreach ($resources as $resource) {//Mejor asi para solo obtener solo LOS RECURSOS ACTIVOS
            //Si usamos el resCostarray, entonces es posible que sumememos recursos que esten desactivados
            $resId = $resource->getId();
            if ($resCostArray[_X_RESOURCE_PREFIX.$resId] > 0) {
                $count++;
                if ($count != 1) {
                    $sql = $sql . " ,";
                }
                $faltante = $resPlayerArray[_X_RESOURCE_PREFIX.$resId] - $resCostArray[_X_RESOURCE_PREFIX.$resId];
                $sql = $sql . _X_RESOURCE_PREFIX . $resId . " = " . $faltante;
            }
        }
        $sql = $sql . " WHERE id =" . $playerId;
        $bd = db::singleton();
        $dt = $bd->updateEntityDt($sql,_X_MAN_PLAYER,$playerId);
}else {
            $dt->success("Los recursos estan vacios");
        }
        //debug::log($sql);
        return $dt;
    }
    
    public function removeBiomesFromPlayerFromRegionDt($regionId, $playerId){
        $biomeutil = new biomeutil();
        $biomesInRegion = new regionsbiomes();
        $biomesInRegion->initActiveByRegion($regionId);
        
        $dt = new datatransfer();
        $dt->success();
      
        $size = $biomesInRegion->size();

        if (count($size) > 0) {
            foreach ($biomesInRegion as $biomeInRegion) {
                
                $biomeutil->deleteBiomePlayerWithBiomeRegionIdDt($biomeInRegion->getId(),$playerId);
            }

        } else {
            $dt->success("No existen biomas en la region");
        }
        return $dt;        
    }
    
    public function addBiomesToPlayerFromRegionDt($regionId, $playerId) {

        $bpman = biomeplayerman::singleton();  
        $biomesInRegion = new regionsbiomes();
        $biomesInRegion->initActiveByRegion($regionId);
        
        $dt = new datatransfer();
        $dt->success();
      
        $size = $biomesInRegion->size();

        if (count($size) > 0) {
            foreach ($biomesInRegion as $biomeInRegion) {
                $data["id"] = $this->db->nextId('biomes_players');
                $data["biome_id"] = $biomeInRegion->getBiomeId();
                $data['player_id'] = $playerId;
                $data['region_biome_id'] = $biomeInRegion->getId();
                $bpman->createDt($data);
            }

        } else {
            $dt->success("No existen biomas en la region");
        }
        return $dt;
    }
    
    public function addResourcesRatesToPlayerDt($resArray, $playerId) {

        $pman = playerman::singleton();
        $oPlayer = $pman->findById($playerId);
        $dt = new datatransfer();
        $dt->success();
        $resman = resourceman::singleton();
        $resources = new resources();
        $resources->initAllActive();
        $resourcesSize = $resources->size();
        $sql = "UPDATE players SET ";
        $count = 0;
        if (count($resArray) > 0) {
            foreach ($resources as $resource) {

                $resId = $resource->getId();
                if ((isset($resArray[_X_RESOURCE_PREFIX.$resId])) && ($resArray[_X_RESOURCE_PREFIX.$resId] > 0)) {
                    $count++;
                    if ($count != 1) {
                        $sql = $sql . " ,";
                    }
                    $cantidad = $resArray[_X_RESOURCE_PREFIX.$resId];
                    $sql = $sql . " rate_" . _X_RESOURCE_PREFIX . $resId . " =  rate_" . _X_RESOURCE_PREFIX . $resId . "+" . $cantidad;
                }
            }
            $sql = $sql . " WHERE id =" . $playerId;
            $bd = db::singleton();
            $dt = $bd->updateEntityDt($sql,_X_MAN_PLAYER,$playerId);
        } else {
            $dt->success("Los recursos estan vacios");
        }
        //debug::log($sql);
        return $dt;
    }

    public function substractResourcesRatesToPlayerDt($resArray, $playerId) {
        //Esta Correcto, no necesitamos mas
        $dt = new datatransfer();
        $dt->success();
        $resources = new resources();
        $resources->initAllActive();
        $pman = playerman::singleton();
        $oPlayer = $pman->findById($playerId);
        $sql = "UPDATE players SET ";
        $count = 0;
        if (count($resArray) > 0) {
            foreach ($resources as $resource) {

                $resId = $resource->getId();
                if ((isset($resArray[_X_RESOURCE_PREFIX.$resId])) && ($resArray[_X_RESOURCE_PREFIX.$resId] > 0)) {
                    $count++;
                    if ($count != 1) {
                        $sql = $sql . " ,";
                    }
                    $cantidad = $resArray[_X_RESOURCE_PREFIX.$resId];
                    $sql = $sql . " rate_" . _X_RESOURCE_PREFIX . $resId . " =  rate_" . _X_RESOURCE_PREFIX . $resId . "-" . $cantidad;
                }
            }
            $sql = $sql . " WHERE id =" . $playerId;
            $bd = db::singleton();
            $dt = $bd->updateEntityDt($sql,_X_MAN_PLAYER,$playerId);
        } else {
            $dt->success("Los recursos estan vacios");
        }
        //debug::log($sql);
        return $dt;
    }

}

?>
