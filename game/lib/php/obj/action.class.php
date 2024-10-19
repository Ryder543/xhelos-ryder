<?php
  require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad 
  
/*******************************************************
 * action: Clase para el manejo de armies 
 ******************************************************/
class action extends identity
{	
	//////////////////////////////////////////////////////////////////////////////////	
	//Variables
	///////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////
	//CONSTRUCTOR
	//////////////////////////////////////////////////////////////////////////////////
    public function __construct(){
        parent::__construct();
    }
  
    //////////////////////////////////////////////////////////////////////////////////
    //METODOS ABSTRACTOS HEREDADOS
    //////////////////////////////////////////////////////////////////////////////////
    public function postRefresh(){
    //debug::warning('Estoy usando el post refresh','action->postRefresh');
    //debug::warning($this->raw,'action->postRefresh');
    }
    /************************************************
     * BOLEANOS (is) DE MOVIMIENTO Y ACCIONES
    ************************************************/
    public function isDefendable($action_id){
        $movable=$this->getParam('defendable');
        if($movable!=_X_SI){
                return false;
        }
        else{
                  return true;
        }
    }
    public function isMovable(){
        $movable=$this->getParam('movable');
        if($movable!=_X_SI){
                return false;
        }
        else{
                  return true;
        }
    }
    public function isAttacker(){
        $movable=$this->getParam('movable');
        if($movable!=_X_SI){
                return true;
        }
        else{
                  return false;
        }
    }


    public function isMovPostCancel(){//Si cancela movimientos despues de terminar esta action
        $movable=$this->getParam('postcancel');
        if($movable!=_X_SI){
                return false;
        }
        else{
                  return true;
        }
    }
    public function isMovPreCancel(){//Si cancela movimientos despues de comenzar esta action
        $movable=$this->getParam('precancel');
        if($movable!=_X_SI){
                return false;
        }
        else{
                  return true;
        }
    }
    /************************************************
     * GETTERS
    ************************************************/
    public function getEnergy(){//Si cancela movimientos despues de comenzar esta action
        $energy=$this->getParam('energy');
//debug::info($energy,'action->getEnergy');
        if(!isset($energy)){
                return false;
        }
        else{
                 return $energy;
        }
    }

    public function getPath(){//Si cancela movimientos despues de comenzar esta action
        $target=$this->getParam('path');
        if(!isset($target)){
                return false;
        }
        else{
                  return $target;
        }
    }
    public function getMinRange(){
        $minrange=$this->getParam('minrange');
        if(!isset($minrange)){
                return false;
        }
        else{
            return $minrange;
        }
    }
    public function getMaxRange(){
        $maxrange=$this->getParam('maxrange');
        if(!isset($maxrange)){
                return false;
        }
        else{
            return $maxrange;
        }
    }

    public function getScope(){
        $scope=$this->getParam('scope');
        if(!isset($scope)){
                return false;
        }
        else{
            return $scope;
        }
    }

    public function getTarget(){//Si cancela movimientos despues de comenzar esta action
        $target=$this->getParam('target');
        if(!isset($target)){
                return false;
        }
        else{
                  return $target;
        }
    }
    public function getPosition(){//Si cancela movimientos despues de comenzar esta action
        $position=$this->getParam('position');
        if(!isset($position)){
                return false;
        }
        else{
                  return $position;
        }
    }

    public function targetIs($type){
        $target_type = $this->getTarget();
        if( ($target_type&_X_TARGET_ARMY) == _X_TARGET_ARMY ){
            return true;
        }
        else{
            return false;
        }
    }

        public function prepareRaw() {
            $preparedRaw = $this->raw;
            /*foreach ($preparedRaw as $key => $value) {          
                unset($preparedRaw[$key]['map']);
            }*/
            return $preparedRaw;
        }
   


}

?>