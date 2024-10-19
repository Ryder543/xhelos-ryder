<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Clase que nos ayuda a obtener informacion de research del usuario actual,
 * por ejemplo preguntar si puede construir fuera del planeta una colonia
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
class researchutil extends util {

    public function __construct() {
        parent::__construct();
    }

    //Numero maximo de colonias que puede tener el jugador objetivo
    public function getPlayerMaxPossibleColonies(){
        return 3;
    }
    //En Segundos, el tiempo que demorara un jugador normal en construir una colonia
    public function getPlayerBuildTimeOfSecondaryColony(){
        $rbrman = $this->man("regionbuildingresource");
        $rbr = $rbrman->findSecondaryColonyCost();
        $time = $rbr->getBuildTime();
        
        //Aqui va el calculo de rsearchutil
        
        return $time;
    }
}

?>
