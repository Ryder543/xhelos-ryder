<?php
 require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
/*******************************************************
 * target: Indica si un blanco es posible 
 ******************************************************/
class target   extends identity//[TODO]Deberiamos de instanciarlo de una interface objeto
{	
	//////////////////////////////////////////////////////////////////////////////////	
	//Variables
	///////////////////////////////////////////////////////////////////////////////////
	//Variables Externas almacenadas en el cosntructor como lazy charge
	private $ext_region_id;
	private $ext_x_origen;
	private $ext_y_origen;
        
        private $ext_x_target;
        private $ext_y_target;
        

        private $ext_action_id;

        private $ext_quanta;

	//Variables Internas
	//////////////////////////////////////////////////////////////////////////////////
	//Metodos
	//////////////////////////////////////////////////////////////////////////////////
	/*********************************************************************************
	* army: constructor
	*********************************************************************************/
	public function __construct($region_id,$actionId,$xOrigen,$yOrigen,$xTarget,$yTarget){//[TODO]No Magic Palabras, debemos de poner 0 como no atrako
		$this->db = db::singleton();
		//Inicializacion Variables Externas
		$this->ext_region_id = $region_id;
		$this->ext_x_origen = $xOrigen;
		$this->ext_y_origen = $yOrigen;
                $this->ext_x_target = $xTarget;
                $this->ext_y_target = $yTarget;
                $this->ext_action_id = $actionId;
                //debug::log($this,'target->constructor');
                //debug::trace('HOla','target->constructor');
	}
	/*********************************************************************************
	* getParam: Obtiene parametros de esta clase [TODO]Metodo de la interface 'objeto'
	*********************************************************************************/	
	public function getActionId(){
            //debug::log($this->ext_action_id,'target->getActionId');
            if(!empty($this->ext_action_id)){
                return $this->ext_action_id;
            }
            else{
                debug::error('action_id esta vacio','target->getActionId');
            }
        }

        public function getRegionId(){
            if(!empty($this->ext_region_id)){
                return $this->ext_region_id;
            }
            else{
                debug::error('region_id esta vacio','target->getRegionId');
            }
        }

        public function getOX(){
            if(!empty($this->ext_x_origen)){
                return $this->ext_x_origen;
            }
            else{
                if($this->ext_x_origen==0){
                   return  $this->ext_x_origen;
                }
                debug::error('X _Out esta vacio','target->getOx');
            }
        }

        public function getOY(){
            if(!empty($this->ext_y_origen)){
                return $this->ext_y_origen;
            }
            else{
                if($this->ext_y_origen==0){
                    return $this->ext_y_origen;
                }
                debug::error('y _Out esta vacio','target->getOY');
            }
        }

        public function getTX(){
            //debug::error($this->ext_x_target,'target->getTX');
            if(!empty($this->ext_x_target)){
                return $this->ext_x_target;
            }
            else{
                if($this->ext_x_target == 0){
                    return $this->ext_x_target;
                }
                else{
                    debug::error('x_In esta vacio','target->getxIn');
                }
            }
        }

        public function getTY(){
            if(!empty($this->ext_y_target)){
                return $this->ext_y_target;
            }
            else{
                if($this->ext_y_target == 0){
                    return $this->ext_y_target;
                }
                else{
                    debug::error('y_In esta vacio','target->getyIn');
                }
                
            }
        }


        public function getParam($param){
		return $this->raw[$param];
	}

        public function setQuanta($quanta){
            $this->ext_quanta = $quanta;
        }

        public function getQuanta(){
            return $this->ext_quanta;
        }

	public function typeIsValid($target_type,$position_type){
		$armyman = armyman::singleton();
		global $firephp;
		//$firephp->log('Entrando a typeIsValid','target->TypeIsValid');
		$dt = new datatransfer();
		
		if($target_type == _X_TARGET_NONE){//No se encesita blanco,accion  VIAJANDO[10]
			$dt->validWarning('Blanco Valido, no se necesita blanco');
			return $dt;
		}
		else{
                    //debug::error('target->typeISValid errors');
                   // debug::log('region['.$this->getRegionId().'] tx['.$this->getTX().'] ty['.$this->getTY().']','target->typeIsValid');
			$myArmy = $armyman->findByCoord($this->getRegionId() ,$this->getTX(),$this->getTY());
			//debug::info($myArmy,'target->typeIsValid->myArmy');
			if(($target_type&_X_TARGET_ARMYANDTERRAIN)==_X_TARGET_ARMYANDTERRAIN)//Si el Target puede ser Vacio o Unidad
			{
				//$firephp->log('TARGET es cualqeuir cosa','target->TypeIsValid');
				$dt->success('Blanco Valido, puede ser vacio o contener una unidad');	
			}
			elseif(($target_type&_X_TARGET_ARMY)==_X_TARGET_ARMY)// Si target_type PUEDE ser una unidad
			{
				//$firephp->log('TARGET tiene que ser un ARMY','target->TypeIsValid');
                                //debug::log($myArmy,'target->typeIsValid->myArmy');
				if( !$myArmy->exist() ){//Si NO EXISTE 
					//$firephp->log('TARGET ARMY no existe','target->TypeIsValid');
					$dt->invalidWarning('Blanco Invalido, no existe una unidad en esas coordenadas');

				}
				else{
					//[TODO]Validar que la unidad este en la posicion elegida Orbitando,Terreno,Subsuelo
					//$firephp->log('TARGET ARMY existe','target->TypeIsValid');
					if(!$myArmy->isInPosition($position_type)){
						//$firephp->log('TARGET ARMY esta en posicion Invalida','target->TypeIsValid');
						$dt->invalidWarning('Blanco Invalido,la Posicion['.$position_type.'] no concuerda con el Blanco');
					}
					else{
						//$firephp->log('TARGET ARMY esta en posicion Valida','target->TypeIsValid');
						$dt->success('Blanco Valido,Existe una unidad en esas Coordenadas');	
					}
					
				}
			}
			elseif(($target_type&_X_TARGET_TERRAIN)==_X_TARGET_TERRAIN)//Si target_type puede ser un terreno vacio
			{
				//$firephp->log('TARGET tiene que ser TERRAIN','target->TypeIsValid');
				//1) Verificar que no exista unidad ahi
				if($myArmy->exist()){
					//[TODO]Si es una unidad Orbitando, no tomarla en cuenta
					//$firephp->log('TARGET TERRAIN tiene una unidad','target->TypeIsValid');
					if($myArmy->isInPosition(_X_POSITION_ORBIT)){
						//$firephp->log('TARGET TERRAIN tiene una unidad Orbitando','target->TypeIsValid');
						$dt->validWarning('Blanco Valido, la posicion esta libre');
					}
					else{
						//$firephp->log('TARGET TERRAIN tiene una unidad en tierra','target->TypeIsValid');
						$dt->invalidWarning('Blanco Invalido, existe una unidad en esas coordenadas');
					}			
				}
				//2) Verificar que no exista a futuro una unidad ahi
				/*elseif($this->armyExistInFutureCoord()){
					//[TODO]Validar que la unidad este en la posicion elegida Orbitando,Terreno,Subsuelo
					$dt->invalidWarning('Blanco Invalido, una unidad se esta moviendo en esas coordenadas');
				}*/
				else{
					//$dt->validSuccess('Blanco Valido, las coordenadas estan libres');
					//$firephp->log('TARGET TERRAIN esta libre','target->TypeIsValid');
					$dt->validWarning('Blanco Valido, las coordenadas estan libres');
				}
			}
			else{
				$dt->validWarning('Tipo de Target['.$target_type.'] no definido');
			}
			return $dt;
		}
	}

    public function postRefresh() {
        
    }

    public function prepareRaw() {
        $preparedRaw = $this->raw;       
        //unset($preparedRaw['map']);
        return $preparedRaw;
    }
}

?>