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
class biomeutil extends util {

    public function __construct() {
        parent::__construct();
    }

     public function deleteBiomePlayerWithBiomeRegionIdDt($regionBiomeId,$playerId){
        $sql="delete from biomes_players where region_biome_id =".$regionBiomeId." AND player_id = ".$playerId;
        $dt = $this->db->deleteDt($sql);
        return $dt;
     }
    
    

}

?>
