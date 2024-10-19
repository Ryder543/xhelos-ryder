<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/* * *****************************************************
 * planet: Clase para el control de un planet
 * **************************************************** */

class globalview extends view {

    //////////////////////////////////////////////////////////////////////////////////	
    //Variables
    ///////////////////////////////////////////////////////////////////////////////////
    //Variables Externas
    //Variables Internas
    private $db;
    private $oState; //Obtener el Estado objetivo
    private $userState; //Obtener el Estado del Usuario     
    private $menuState; //Obtener el Estado del Menu 
    private $type;
    private $className;    
    private $id;
    //////////////////////////////////////////////////////////////////////////////////
    //Metodos
    //////////////////////////////////////////////////////////////////////////////////
    
    /*********************************************************************************
     * globalstate: constructor
     * PARAMETROS:
     * $type = colony, region,planet,star,galaxy, que tipo de constructor estaremos usando!
     * ******************************************************************************* */
    public function __construct($id,$type) {
        $this->db = db::singleton();
        $this->init($id,$type);
        //$this->initPlayer();
        //$this->armyman = armyman::singleton();
        //$this->ext_galaxy_id = $galaxy_id;
    }
    
   /***************************************************************************************************
   * init: Inicializa los valores que deberia de tener globalState
   * **************************************************************************************************/
    public function init($id,$type) {
        //Inicializa el Estado de Pagina Actual
        $className = $type._X_CLASS_VIEW_SUFFIX;
        if($className == 'globalstate'){
            debug::error("GlobalState esta inicializandose a si misma");
            exit;
        }
        else{
            $this->setType($type);
            $this->setOId($id);
            $this->setClassNameX($className);
        }
        $dt = $this->updateState();
        if(!$dt->ok()){
            $dt->inform();
            exit;
        }
        return true;
    }

   /***************************************************************************************************
   * renderContent: Imprime el contenido segun la situacion
   * ************************************************************************************************* */
   
    
    public function render() {
        $this->getOState()->render();
    }
    
   /***************************************************************************************************
   * renderMenu: Imprime el menu del juego
   * ************************************************************************************************* */
    public function renderMenu(){
        
    }

   /***************************************************************************************************
   * renderData: Imprime la data en JSON necesaria para el cliente (navegador) del juego
   * ************************************************************************************************* */
    public function renderData(){
        if($this->getDt()->ok()){
         ajaxutil::sendHtml($this->getDt(),$this->getOState(),$this->getMenuState());
        }
        else{
         ajaxutil::sendError($this->getDt());
        }
    }
    
    public function renderAjaxData(){
        if($this->getDt()->ok()){
         ajaxutil::sendAjax($this->getDt(),$this->getOState(),$this->getMenuState());
        }
        else{
         ajaxutil::sendAjaxError($this->getDt());
        }
    }


   /***************************************************************************************************
   * updateState: Inicializa todo los states necesarios del juego
   * **************************************************************************************************/   
    public function updateState(){
       
        /*Importantisimo: El Orden de Ejecucion de los Estados! UserState debe de ser ejecutado
        antes de menu state. Los demas estados ya corren por cuenta del sistema*/
        
        //con setIfOldDtIsOk almacenamos el ultimo lugar donde hubo error si es que lo hubo
        $this->setIfOldDtIsOk($this->getUserState()->updateState());
        $this->setIfOldDtIsOk($this->getMenuState()->updateState());
        $this->setIfOldDtIsOk($this->getOState()->updateState());
        return $this->getDt();
    }
    
   /***************************************************************************************************
   * getState: Clase sobrecargada, obtiene el estado de todo esto en un arreglo que se pasara a JSON
   * **************************************************************************************************/  
    public function prepareState() {
        
    }
    
    
     /**
     * @assert (0, 0) == 0
     * @assert (0, 1) == 1
     * @assert (1, 0) == 1
     * @assert (1, 1) == 2
     * @assert (1, 2) == 4
     */
    public function add($a, $b)
    {
        return $a + $b;
    }
 
    ///////////////////////////////////////////////////////////////////////////////////////
    //GETTERS
    ///////////////////////////////////////////////////////////////////////////////////////
    public function getOState(){
         if(empty ($this->oState)){
            //[14oct2024 jsk fix]
             $cn = $this->getClassNameX();
             //$cn = get_class($this);
             $this->oState = new $cn($this->getOId(),$this->getType());
             $dt = $this->oState->validateStateDt();
             if(!$dt->isValid()){
                debug::error("dt no es valido cuando se creo la clase de estado");
             exit;
             }
         }
         return $this->oState;
    }
    public function getUserState(){
         if(empty ($this->userState)){
             $this->userState = new userview();
        }
        return $this->userState;
    }
    
    public function getMenuState(){
        if(empty ($this->menuState)){
            $this->menuState = new menuview();
            ////Capitalizamos el nombre de la clase
            $methodName = "initBy".ucfirst($this->getType())."Id";
            $this->menuState->$methodName($this->getOId());
        }
        return $this->menuState;
    }
    
    public function getType(){
        return $this->type;
    }
    public function setType($type){
        $this->type = $type;
    } 
    public function getOId(){
        return $this->id;
    }
    public function setOId($id){
        $this->id = $id;
    }

    public function setClassNameX($className) {
        $this->className = $className;
    }
    public function getClassNameX(){
        return $this->className;
    }

    public function validateStateDt() {
        $dt = new datatransfer();
        $dt->success("[TODO]Validar el estado del global de alguna manera");
        //$dt->success():
        debug::warning("[TODO]Validar el estado del global de alguna manera","regionstate->validateStateDt");    
        return $dt;
        //Verificar que la colonia le pertenezca al jugador
    }

    public function getTitle() {
        return $this->getOState()->getTitle(). " - "._TITLE._SUBTITLE;
    }
}

?>