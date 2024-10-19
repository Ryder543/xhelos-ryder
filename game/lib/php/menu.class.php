<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
require_once(dirname(__FILE__) . '/datatransfer.class.php');
require_once(dirname(__FILE__) . '/colony.class.php');
require_once(dirname(__FILE__) . '/region.class.php');
require_once(dirname(__FILE__) . '/planet.class.php');
require_once(dirname(__FILE__) . '/star.class.php');
require_once(dirname(__FILE__) . '/debug.class.php');
require_once(dirname(__FILE__) . '/oop/identity.class.php');
/* * ******************************************************
 * player: Clase para el manejo del jugador, esta en forma lazy 
 * **************************************************** */

class menu {

    //////////////////////////////////////////////////////////////////////////////////
    //CAMPOS
    //////////////////////////////////////////////////////////////////////////////////  
    private $ext_oColony;
    private $ext_oRegion;
    private $ext_oPlanet;    
    private $ext_oStar;        
    //////////////////////////////////////////////////////////////////////////////////
    //CONSTRCUTOR
    //////////////////////////////////////////////////////////////////////////////////
    public function __construct() {
    }
    
    public function setColony(identity $colony){
        $this->ext_oColony = $colony;
    }
    public function setRegion(identity $region){
        $this->ext_oRegion = $region;
    }    
    public function setPlanet(identity $planet){
        $this->ext_oPlanet = $planet;
    }        
    public function setStar(identity $star){
        $this->ext_oStar = $star;
    }        

}

?>