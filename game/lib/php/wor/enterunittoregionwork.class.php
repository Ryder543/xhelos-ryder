<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Clase Work que mueve unidades hacia varias direcciones
 *
 * @author windows7
 */
class enterunittoregionwork extends work {
    //put your code herepublic function execute(){

      public function __construct()  {//Necesitamos llamar al constructor
        parent::__construct();
    }  
    
    public function execute() {
        debug::log("TEMPORAL 10 -  Ejecucion Creacion de Colonia","enterunittoregionwork");
        
        
        
    }

    public function validate() {
        //$this->setIfOldDtIsOk($this->validateMaxColonyDt());
    }

    protected function validateParams() {
        
    }

    public function breakIf() {
        
    }
    
 
}

?>
