<?php

/*
 * Esta clase sirve para ayudar en los movimientos de las unidades
 */

/**
 * Descripcion de lo que hace la clase
 *
 * @category  Xhelos
 * @package   Main
 * @license   http://www.opensource.org/licenses/BSD-3-Clause
 * @example   ../index.php
 * @example
 * 	$planetview.class 
 * @version   0.01
 * @since     02/01/2013
 * @author Jeeba
 */
require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad

/* * *******************************************************
 * regionstate: Encargado de tener el estado de la region 
 * ****************************************************** */

class transferutil extends util {

    ///////////////////////////////////////////////////////////////////////////////////////
    //PROPIEDADES
    ///////////////////////////////////////////////////////////////////////////////////////    
    ///////////////////////////////////////////////////////////////////////////////////////
    //CONSTRUCTOR
    ///////////////////////////////////////////////////////////////////////////////////////    
    /*     * ******************************************
     * Constructor: De regionState
     * Params: 
     * $region_id: ID de la region que quiere mantener el estado
     * $player_id: ID del jugador actual de la region
     * EXTRA: Obtener la lista de arg $args = func_get_args(); 
     * ****************************************** */
    public function __construct() {
        parent::__construct();
    }

    public function transferArmyToTeam($oArmy, $oTeam) {
        $oldTeamId =  $oArmy->getTeamId();
        if (!empty($oldTeamId)) {
            $oOldTeam = $this->man("team")->findById($oArmy->getTeamId());
        }


        if ($oTeam->exist()) {
            $oArmy->setTeam($oTeam->getId());
        } else {
            $oArmy->setTeam('null');
        }

        //Calcular si quedan armies aun en oldTeam

        if (!empty($oldTeamId)) {
            $armies = new armies();
            $armies->initByTeamId($oOldTeam->getId());
            if (!$armies->exist()) {
                $dt = $this->man("team")->deleteDt($oOldTeam->getId());
            }
        }
    }

}

?>