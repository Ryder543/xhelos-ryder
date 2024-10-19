<?php
 require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
  
 define("_X_DT_ERROR","error"); 
 define("_X_DT_WARNING","warning"); 
 define("_X_DT_SUCCESS","success"); 
  
  
/*******************************************************
 * datatransfer: Clase para la transferencia de datos con metadata - Martin Fowler, data transfer object 
 ******************************************************/
class datatransfer
{
	private $answer = '';
	public $text;
	public $type;
	public $data;
	public $isOk;
	private $json; 
	
	public function __construct()//Constructor Publico, inicializa los valores
	{
		$this->json = json::singleton();//Obtiene una instancia del singleton json
		if(!$this->json) 
		{
			//[TODO] No puede existir texto magico
			echo 'ERROR: No se pudo instanciar el singleton json - datatransfer.php';
		}
	}
  
  //////////////////////////////////////////////////////////////////////////////////
  //SETTERS
  //////////////////////////////////////////////////////////////////////////////////
  public function error($text,$data=''){
    $this->isOk = false;
    $this->text = $text;
    $this->data = $data;
    $this->type = _X_DT_ERROR;
  }
  
  public function success($text = '', $data = null) {
    $this->isOk = true;
    $this->text = $text;

    // Only update $this->data if $data is not null.
    if ($data !== null) {
        $this->data = $data;
    }

    $this->type = _X_DT_SUCCESS;
}

  
  
  //////////////////////////////////////////////////////////////////////////////////
  //BOLEANOS
  //////////////////////////////////////////////////////////////////////////////////
  public function isValid(){ //Eliminar porque es mas facil la ok
    return $this->isOk;
  }
  
  public function ok(){
      return $this->isOk;
  }
  
    
  public function hasSuccess(){
    if( ($this->type == _X_DT_SUCCESS) AND ($this->isOk) ){
      return true;  
    }
    else{
      return false;
    }
    
  }
  
  //////////////////////////////////////////////////////////////////////////////////
  //GETTERS
  //////////////////////////////////////////////////////////////////////////////////
  public function getText(){
    return $this->text;
  }
  
  public function getData(){
    return $this->data;
  }  
  

	public function invalidWarning($text,$debugdata=''){
		$this->isOk = false;
		$this->data = $debugdata;
		$this->text = $text;
		$this->type = _X_DT_WARNING;
	}
	public function validWarning($text,$debugdata=''){
		$this->isOk = true;
		$this->data = $debugdata;
		$this->text = $text;
		$this->type = _X_DT_WARNING;
	}
	public function send(){
		$msg = array();
		$msg['text'] = $this->text;
		$msg['type'] = $this->type;
		$container = array('msg'=>$msg);
		$this->json->var2json($container);
	}

	public function getMsg(){
		$msg = array();
		$msg['text'] = $this->text;
		$msg['type'] = $this->type;
		return $msg;
	}

	public function setText($text){
		$this->text = $text;
	}
	public function setData($data){
    //debug::log($data,"datatransfer->setdata");
    //debug::log($this,"datatransfer->setdata1");
		$this->data = $data;
    //debug::log($this,"datatransfer->setdata2");
	}

  /*setDataIfValid: Seteamos la data si el datatransfer es valido, si no mandamos warning*/
  public function setDataIfValid($data){
      if($this->isValid()){
          $this->data = $data;
      }
      else{
          //debug::warning($this,'datatransfer->setDataIfValid Advertencia, dt no es valido');
      }
  }
        
  public function getType(){
      return $this->type;
  }
  
  public function inform(){
      if(empty($this->isOk)){
          debug::trace('datatransfer->inform Datatransfer no esta inicializado');
      }
      else{
          if(!$this->isValid()){
              debug::error($this,'datatransfer->inform Error');
          }
      }           
  }
  
  public function debug(){
      $type = $this->getType();
      debug::$type($this->getText(),$type);
  }
        
        
        
}

	
?>
