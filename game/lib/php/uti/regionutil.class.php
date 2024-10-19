<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Clase que nos ayuda a obtener varias cosas de la region, como el total de recursos
 * que se saca normalmente por tipo de region
 *
 * 
 *
 * @category  Xhelos
 * @package   Util
 * @license   http://www.opensource.org/licenses/BSD-3-Clause
 * @example   ../index.php
 * @example
 * 	$oUser = new MyLibrary\User(new Mappers\UserMapper());
 *      $oUser->setUsername('swader');
 *      $aAllEmails = $oUser->getEmails();
 *      $oUser->addEmail('test@test.com');
 * @version   0.01
 * @since     17/01/2013
 * @author windows7
 */
class regionutil extends util {

    public function __construct() {
        parent::__construct();
    }

    public function getBasicResourcesRatesPerTickByRegionType($type) {
        $res = null;
        switch ($type) {
            case _X_REGION_MAP_TYPE_PLAIN:
                $res = [_X_RESOURCE_1 => 6, _X_RESOURCE_2 => 6, _X_RESOURCE_3 => 6];
                break;
            case _X_REGION_MAP_TYPE_DESERT:
                $res = [_X_RESOURCE_1 => 3, _X_RESOURCE_2 => 3, _X_RESOURCE_3 => 10];
                break;
            case _X_REGION_MAP_TYPE_MOUNTAIN:
                $res = [_X_RESOURCE_1 => 10, _X_RESOURCE_2 => 3, _X_RESOURCE_3 => 3];
                break;
            case _X_REGION_MAP_TYPE_LAGOS:
                $res = [_X_RESOURCE_1 => 3, _X_RESOURCE_2 => 10, _X_RESOURCE_3 => 3];
                break;
            case _X_REGION_MAP_TYPE_NIEVE:
                $res = [_X_RESOURCE_1 => 8, _X_RESOURCE_2 => 8, _X_RESOURCE_3 => 0];
            break;
            case _X_REGION_MAP_TYPE_INICIAL:
                $res = [_X_RESOURCE_1 => 7, _X_RESOURCE_2 => 7, _X_RESOURCE_3 => 7];
            break;        
        }
        return $res;
    }
    public function getElementalResourcesRatesPerTickByRegionId($regionId){
        $resources = new regionsresources();
        $resources->initActiveByRegion($regionId);
        $res = array();
        foreach($resources as $resource){
            $res[_X_RESOURCE_PREFIX.$resource->getResourceId()] = $resource->getQuality();
        }
        return $res;
    }
    

}

?>
