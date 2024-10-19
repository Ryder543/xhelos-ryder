<?php 

  require_once(dirname ( __FILE__ ).'/../security.php'); //Advertencia de Seguridad
 
  /*Identity no puede manejar por si mismo el acceso a la BD, son creados por identitymap */
  
  abstract class identity extends stateable {    
    protected $db;
    protected $raw;
    protected $rawIsOld;
    protected $ext_id;
    //protected $man;

    //////////////////////////////////////////////////////////////////////////////////
    //CONSTRUCTOR
    //////////////////////////////////////////////////////////////////////////////////
    public function __construct(){
      $this->db = db::singleton();//No deberia tener acceso a la BD, en fin
    }


    public function __toString(){
      $name = get_class($this);
      $id = $this->getId();
      if( !(isset($id)) OR (empty($id)) ){
        $id='NoInstanciado';
      }
      return $name.'['.$id.']';
    }
    

    abstract public function postRefresh();//Que hacer con el raw despues de un reresh,
    
    public function getName(){
        return $this->getParam("name");
    }//Nos tiene que devolver su nombre basico y siempre
    //como en el caso de player, buscar si tenemos raw[password] y unsetearlo
    //////////////////////////////////////////////////////////////////////////////////
    //INICIALIZADORES
    //////////////////////////////////////////////////////////////////////////////////    
    /*********************************************************************************
    * initByArray: Le pasamos un arreglo del tipo battle, no llama a la bd
    *********************************************************************************/
    public function initByArray(&$raw){
      //debug::trace('identity->initByArray');
      //debug::log($raw,'identity->initByArray');
      $this->setDataClean();
      $this->raw = &$raw;
      $this->ext_id = $raw['id'];
      //[WHY] Las iniciadas por array no aparecen en nuestro mapa por defecto
      //[UPDATE] Deberiamos de impedir esto, haciendo que todas las llamadas
      //a initByArray sean hechas igualando a identitymap identity = identitymap->findByWhatever
      //debug::log($this,'identity');
      //debug::trace('La accion a pesar de la busqueda en el mapa no pudo ser cargada','movement->getAction');
      
      
      ////////////////////////////////////////////////NO SE SI TIENE ALGUN EFECTO ADVERSO
      //
      //$this->man()->add($this->ext_id,$raw);
      //
      ///////////////////////////////////////////////////////////////////////////////////
      
      $this->postRefresh();
    }
    
    //////////////////////////////////////////////////////////////////////////////////
    //GETTERS
    //////////////////////////////////////////////////////////////////////////////////
    public function &getRaw(){
      
      $dirty = $this->isDataDirty(); 
      if($dirty){
        //debug::warning('Datos Sucios, getRaw identity');
        $this->refreshData();
      }
      else{
        //debug::warning('Datos Limpios, getRaw identity');
      }
      return $this->raw;
    }
    
    /******************************************************************************************************
    * getId: Es la unica funcion que puede ser llamada sin peligro de corrupcion
    * de datos, debido a que un id diferente es simplemente otro objeto
    ******************************************************************************************************/ 
    public function getId(){
      return $this->ext_id;
    }
    
    public function getParam($param){
     if($this->isDataDirty()){
       /*if($param=='player_id'){
        debug::info($this,'AFTER SETTING como esta dirty? identity->getParam');  
       }*/
       
        $this->refreshData();
      }
     
      $raw = $this->getRaw();
      return $raw[$param];
    }
    //Deprecated// Mejor usar paramDT
    public function setParam($param,$value){
        //debug::info($param,'indetity->setParam param');
        //debug::info($value,'indetity->setParam value');
        //debug::info($this,'indetity->setParam before');
         if($this->isDataDirty()){
            $this->refreshData();
          }
          $this->raw[$param] = $value;
         $this->man()->add($this->getId(),$this->getRaw());
         //debug::info($this,'indetity->setParam after');
    }
    
    public function createParam($paramName,$paramValue){
        
        $this->raw[$paramName] = $paramValue;
        //$this->updateMan();
        //debug::log($this->man(),'identity createParam man');
        //return $raw;
    }

    

    //Retorna el Manager Asociado a esta variable!
    public function man($name=null){
       // if(empty($name)){
            $managerClass = get_class($this) . 'man';
        /*}
        else{
            $managerClass = $name . 'man';
        } */
        //debug::log($managerClass,'identity->man1');
        $obj = eval('return '.$managerClass.'::singleton();'); //[TODO] no deberiamos pero...
        return $obj;
    }

    //////////////////////////////////////////////////////////////////////////////////
    //SETTERS
    //////////////////////////////////////////////////////////////////////////////////
    /******************************************************************************************************
    * setDataDirty: Esta funcion se invoca para decirle al objeto que sus datos raw son antiguos,
    * Ademas llama a su mapa de identidad y le indica tambien que sus datos son antiguos
    ******************************************************************************************************/ 
    public function setDataDirty(){
      $this->rawIsOld = true;
      $this->man()->defile($this->getId());
    }
    
    public function setDataClean(){
      $this->rawIsOld = false;
    } 
        
    /******************************************************************************************************
    * refreshData: Se invoca para rerescar los datos del objeto
    ******************************************************************************************************/ 
    public function refreshData(){
      $this->setDataClean(); 
      $id = $this->getId();
      //debug::info($this->raw,'BEFORE refreshData{'.$this.'}  identity.class');
      $this->initByArray($this->man()->find($id));
      //debug::trace($this->raw,'AFTER refreshData{'.$this.'}  identity.class');
    }
         
    
    
    //////////////////////////////////////////////////////////////////////////////////
    //METODODS BOLEANOS
    //////////////////////////////////////////////////////////////////////////////////
    public function exist($paramName = false){
      if($paramName)  {
        if(array_key_exists($paramName,$this->raw)){
            return true;
        }
        else{
            return false;
        }
      }
      else{
          if(!$this->getRaw()){
            return false; 
          }
          else{
             return true; 
          }
      
      }
    }
    
    public function isDataDirty(){
      return $this->rawIsOld;
    }
     
  }
  
?>