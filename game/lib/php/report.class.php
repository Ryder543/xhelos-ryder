<?php
 require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad

 /****************************************************************
 * report: Clase que maneja el panel de reportes de las batallas 
 ***************************************************************/
class report //[TODO]Deberiamos de instanciarlo de una interface objeto
{	
	//////////////////////////////////////////////////////////////////////////////////	
	//Variables
	///////////////////////////////////////////////////////////////////////////////////
	//Variables Internas
	private $type;
	private $id;
	private $raw;//DATA
	private $player;
	private static $instance;//Contiene la instancia unica del parseador de json
	//////////////////////////////////////////////////////////////////////////////////
	//Metodos
	//////////////////////////////////////////////////////////////////////////////////
	/*********************************************************************************
	* army: constructor
	*********************************************************************************/
	public function __construct(){//No se reciben datos debido a todas las posibles formas de inicializacion del objeto
            $this->raw= array();
	}
  
  public function getRaw(){
    return $this->raw;
  }
  
  
  public function addCauseMovement($armyId,$x,$y){
    //Verificar que causa exista
    $this->add(_X_AFFECTS_CAUSE,_X_TYPE_ARMY,$armyId,_X_AFFECTS_MOVEMENT,"[".$x."][".$y."]");
  }
  
  public function addEffectUnit($armyId,$deads){
    //Verificar que causa exista
    $this->add(_X_AFFECTS_EFFECT,_X_TYPE_ARMY,$armyId,_X_AFFECTS_UNIT,$deads); 
  }

  public function addEffectDamage($armyId,$damage){
      $this->add(_X_AFFECTS_EFFECT,_X_TYPE_ARMY,$armyId,_X_AFFECTS_DAMAGE,$damage);
  }
  
  public function addEffectArmies($armiesArray){
    //Se Itera a traves de todos los Affects
    foreach ($armiesArray as $armyId => $army){
      foreach($army as $armyAffectsId => $armyAffectVal){//Efecto
        $this->add(_X_AFFECTS_EFFECT,_X_TYPE_ARMY,$armyId,$armyAffectsId,$armyAffectVal);
        //$this->add($armyId,$armyAffectsId,$armyAffectVal,_X_AFFECTS_EFFECT);
      } 
    }   
  }

  
  private function add($position,$type,$armyId,$affectId,$affect){
    
      if(!isset($this->raw[$position][$type][$armyId])){
        $this->raw[$position][$type][$armyId] = array();
      }
      if($affect){
        $this->raw[$position][$type][$armyId][$affectId]=$affect; 
      }
      else{
        $this->raw[$position][$type][$armyId][$affectId]=0;
      }
  }
  
  

}

?>