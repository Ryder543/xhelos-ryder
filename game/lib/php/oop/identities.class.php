<?php

require_once(dirname(__FILE__) . '/../security.php'); //Advertencia de Seguridad



/* * *****************************************************
 * identities: Ideal para buscar muchas identidades de un tipo. SI tiene acceso a la base de datos
 * y se sugiere que se utilicen funciones del tipo identities cuando se quiere accesar una lista de
 * datos de un mismo tipo
 * **************************************************** */

abstract class identities extends stateable implements Iterator { //[TODO]Deberiamos de instanciarlo de una interface objeto 

    //[TODO]El Refresh se esta llamando muchas veces, deberiamos tener una funcion
    //setObjectParam('position','P') por ejemplo que actualize la accion
    //////////////////////////////////////////////////////////////////////////////////
    //Variables
    ///////////////////////////////////////////////////////////////////////////////////
    //Variables Internas
    protected $db;
    protected $raw; //DATA
    protected $rawIsOld; //Variable que sabe si se necesita actualizar los datos de raw
    protected $managerName;
    protected $usedSql;    

//////////////////////////////////////////////////////////////////////////////////
//Metodos PRINCIPALES
//////////////////////////////////////////////////////////////////////////////////	

    /*********************************************************************************
    * constructor
    * ******************************************************************************* */
    public function __construct($managerName) {//No se reciben datos debido a todas las posibles formas de inicializacion del objeto
        $this->db = db::singleton();
        $this->managerName = $managerName;
    }

//////////////////////////////////////////////////////////////////////////////////
//Metodos GETTERS
//////////////////////////////////////////////////////////////////////////////////

    /*********************************************************************************
     * refresh: Actualiza Raw, actualmente solo id esta protegido contra futuros cambios
     ******************************************************************************** */
    public function createParam($id,$paramName,$paramValue) {
         $this->raw[$id][$paramName] = $paramValue;
    }

    public function getRaw() {
        if ($this->isRawOld()) {
            $this->refresh();
        }
        return $this->raw;
    }

    public function size() {
        $raw = $this->getRaw();
        return count($raw);
    }
    
    
    public function getIds() {
        $size = count($this->raw);
        $id = '';
        foreach ($this->raw as $key => $army) {
            $id = $id . $army['id'] . ',';
        }
        $id = substr($id, 0, -1);
        return $id;
    }

    public function man(){
        //debug::log($this,'identities->man this');
        //debug::trace('identities->man');
        if(empty($this->managerName)){
            debug::error('identities->man manageName esta vacio');
        }
        /*if($this->managerName =='ibs'){
            debug::trace('ibs as manager');
        }*/
        //debug::log($managerClass,'identity->man1');
        $str = 'return '.$this->managerName.'::singleton();';
        //debug::trace($str,'identities->man:');
        $obj = eval($str); //[TODO] no deberiamos pero...
        //debug::log($obj,'identities->man');
        return $obj;
    }
    
    public function getUnique($paramName){
        //debug::info($this->raw,'getUniquePlayer armies.class');
        foreach ($this->raw AS $key => $object) {
            if (!isset($last_object)) {
                $last_object = $object[$paramName];
                //debug::info('getUniquePLayer['.$last_player.'] Seteo','getUniquePlayer armies.class');
                continue;
            }

            if ($last_object == $object[$paramName]) {
                $last_object = $object[$paramName];
                //debug::info('getUniquePLayer last['.$last_player.'] army_player['.$army['player_id'].'] Igualar','getUniquePlayer armies.class');
            } else {//No todas las unidades tienen el mismo player
                //debug::warning('UltimoPlayer['.$last_player.'] no es igual a playerARmy['.$army['player_id'].']','getUniquePlayer armies.class');
                return false;
                break;
            }
        }
        //debug::info('getUniquePLayer['.$last_player.'] Correcto','getUniquePlayer armies.class');
        return $last_object;
    }
    
    /*********************************************************************************
    * setRaw: Setea la variable en raw
    ******************************************************************************** */
    public function setRaw($raw) {
        /*if($raw){*/
            $this->raw = $raw;
        /*}*/
    }

    //Poderosa funcion que llena el manager de su identity base con toda la data que contiene. Recontra eficiente
    public function refillMan(){
        $manName = $this->managerName;
        $man = $manName::singleton();
        //debug::log($man,"refillMan");
        $man->setRaw($this->raw);
        /*foreach ($this->raw as $key => $value) {
            
        }*/
    }
    
    public function setDataDirty(){
        foreach($this AS $object){
            $object->setDataDirty();
        }
    }

    protected function setUsedSql($sql){
        $this->usedSql= $sql;
    }
    
//////////////////////////////////////////////////////////////////////////////////
//Metodos BOOLEANOS
//////////////////////////////////////////////////////////////////////////////////
    /****************************************************************
    * exist: Si los datos son antiguos y necesitan refrescarse
    *************************************************************** */
    public function exist() {
        $raw = $this->getRaw();
        if (!empty($raw)) {
            if( count($raw) > 0 ) {
                return true;
            }
            else{
                return false;
            }
            
        } else {
            
            return false;
        }
    }

    /****************************************************************
     * isRawOld: Si los datos son antiguos y necesitan refrescarse
     *************************************************************** */
    protected function isRawOld() {
        return $this->rawIsOld;
    }
    
    /***************************************************************************
     * METODOS ABSTRACTOS DE Iterator: Metodos que hay que sobrescargar
     ************************************************************************ */
    public function rand(){
        
        $varId = array_rand($this->raw);
        $var = $this->raw[$varId];
        //debug::log($var,'identities->rand var');
        $obj = $this->man()->findById($var['id']);
        return $obj;
    }
    
    
    public function first(){
        //debug::log($this,'identitiesfirst this');
        $var = reset($this->raw);
        //debug::log($var,'identities first array');
        $obj = $this->man()->findById($var['id']);
        //debug::log($this->man(),'identities first man');
        
        //debug::log($obj,'identities first obj');
        return $obj;
    }
    
    public function current() {
        //debug::log($this,'current');
        $var = current($this->raw);
        $obj = $this->man()->findById($var['id']);
        return $obj;
    }

    public function key() {
        
        $var = key($this->raw);
        //debug::log($var,'key');
        //$obj = $this->man()->wrap($var);
        return $var;
    }

    public function next() {
        //debug::log($this,'next');
        //debug::trace('next');
       $var = next($this->raw);
       //debug::log($var,'next');
       if($var){
        $obj = $this->man()->findById($var['id']);
        return $obj;
       }
       else{
           return $var;
       }
    }

    public function rewind() {
        //debug::log($this,'rewind');
        if($this->exist()){
            reset($this->raw);
        }
    }

    public function valid() {
        //debug::log($this,'valid');
        if($this->exist()){
            $key = key($this->raw);
            $var = ($key !== NULL && $key !== FALSE);
            return $var;
        }
        else{
            return false;
        }
        
    }
    public function remove($key){
        unset($this->raw[$key]);
    }
    
    public function findByParam($value,$paramName){
        $finded = false;
        foreach($this AS $object){
            //debug::log('param['.$paramName.'] == value['.$value.']','identites->findByParam');
            //debug::log($this,'identites->findByParam this');
            $param = $object->getParam($paramName);
            if($param == $value){
                //debug::log('param['.$param.'] == value['.$value.']','identites->findByParam');
                $finded = true;
                break;
            }
        }
        return $finded; 
    }
    
    public function dataForEmptyArray(){
        return false;
    }
    
    //Que hacemos con el raw, si esta lleno o si esta vacio, mejor es estandarizar esta nota

}

?>