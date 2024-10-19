<?php

require_once(dirname(__FILE__) . '/../security.php'); //Advertencia de Seguridad
//////////////////////////////////////////////////////////////////////////////////
//DEFINES - Unicos para el identitymap, nadie mas los usa
//////////////////////////////////////////////////////////////////////////////////
define('_X_IDENTITYMAP_DEFILED', 'defiled');
define('_X_IDENTITYMAP_CLEAN', 'clean');

abstract class identitymap {

    protected $db;
    protected $raw;
    protected $oraw;    //Raw de Objetos ya calculados!
    protected $status;
    protected $class; //Nombre de la clases que manipulara ejm player
    protected $name;  //Nombre de su propia clase ejm playerman
    protected $table; //Nombre de la tabla base de este identitymap
    protected $globalStatus; //Boleano que nos indica si debemos de defilar todo!

    //////////////////////////////////////////////////////////////////////////////////
    //CONSTRUCTOR
    //////////////////////////////////////////////////////////////////////////////////

    protected function __construct($name, $class, $table) {
        $this->db = db::singleton();
        $this->raw = array();
        $this->status = array();

        $this->name = $name;
        $this->class = $class;
        $this->table = $table;
    }

    //////////////////////////////////////////////////////////////////////////////////
    //FUNCIONES PRINCIPALES
    //////////////////////////////////////////////////////////////////////////////////
    /*     * *******************************************************************************
     * defaultsql: Abstracto, devolver un sql que permita obtener un objeto por $id
     * ******************************************************************************* */
    abstract public function defaultsql($id); //Funcion Externa de busquedas

    /*     * *******************************************************************************
     * find: Funcion externa de busqueda, devuelve un OBJETO
     * ******************************************************************************* */

    public function &findById($id) {
       // debug::log($id,'indentitymap findById');
        //20ene2013 jeeba, como al final vamos a devolver un objeto, hemos creado un arreglo de objetos. Solo si esta defileado o no existe obtenemos de find la data

        if ((isset($this->status)) && (isset($this->status[$id])) && (!$this->isDefiled($id)) && isset($this->oraw[$id])) {
            $obj = $this->getObj($id);
            return $obj;
        } else {
            $sql = $this->defaultsql($id);
            $raw = &$this->find($id, $sql);
            /* if($this->class=='planet'){*/
            //debug::log($sql,'identitymap->findbyId sql');
            //  debug::log($raw,'identitymap->findbyId');
             // debug::log($this,'identitymap->findbyId');
             /* } */
            $obj = $this->wrap($raw);
        }


        return $obj;
    }

    /*     * *******************************************************************************
     * find: Funcion interna de busqueda, devuelve un puntero al ARREGLO principal
     * ******************************************************************************* */

    public function &find($id, $sql = '') {
        $raw = array();

        //0)Datos de inicio
        if (empty($sql)) {
            //$sql = "SELECT *,time_to_sec2(battles.start_battle,now()) AS battle_seconds FROM battles WHERE battles.id=".$id." AND battles.phase !='"._X_BATTLE_PHASE_END."'";
            $sql = $this->defaultsql($id);
            //$sql = $this->default_sql;
            //debug::warning('Busqueda directa,devuelve un arreglo en lugar de objeto','find '.$this->name.'.php');
        }
        //debug::warning($this->raw,'raw['.$id.'] find '.$this->name.'.php');
        //debug::warning($this->status,'status['.$id.'] '.$this->name.'.php');
        //1)Pregunta si existe raw
        if (!empty($this->raw[$id])) {

            //1.1) Si esta defileado obtenerlo de la BD (hacer select)
            if (isset($this->status[$id]) AND ($this->status[$id] == _X_IDENTITYMAP_DEFILED)) {
                //debug::warning($this->class.'['.$id.'] esta sucio, se buscara en la BD','find '.$this->name.'.php');
                $raw = $this->getFromPersistence($sql);
                $this->add($id, $raw);
            }
            //1.2) Si no esta defileado, y status existe entonces enviarlo desde el raw
            elseif (isset($this->status[$id]) AND ($this->status[$id] == _X_IDENTITYMAP_CLEAN)) {
                //debug::warning($this->class.'['.$id.'] no esta sucio, se enviara desde el raw','find '.$this->name.'.php');
            }
            //1.3) Si no esta defileado, pero status no existe mandar mensaje de error
            else {
                //debug::error('No existe status para '.$this->class.'['.$id.']','find '.$this->name.'.php');
                return false;
            }
        }
        //2) Si no existe entonces obtenerlo de la BD (hacer select)
        else {
            $raw = $this->getFromPersistence($sql);
            $this->add($id, $raw);        
        }
        return $this->raw[$id];
    }

    /*     * *******************************************************************************
     * defile: Indica que el dato en el indice $id esta desactualizado, la proxima vez
     * que se pregunte por el, se obtendra el dato desde el lugar de persistencia
     * ******************************************************************************* */

    public function defile($id) {
        //debug::warning('Data['.$id.'] ensuciada','defile '.$this->name.'.php');
        $this->status[$id] = _X_IDENTITYMAP_DEFILED;
    }

    /*     * ***********************************************************************************************
     * defileAll: Hacemos que todo el contenido sea defilado. Esto es util por ejemplo cuando hacemos
     * actualizaciones masivas, updates masivos, cosa que lo que se encuentra aqui deja de estar actualizado
     * ********************************************************************************************** */

    public function defileAll() {
        foreach ($this->status as $arrayStatus) {
            $arrayStatus = _X_IDENTITYMAP_DEFILED;
        }
    }

    /*     * ***********************************************************************************************
     * cleanAll: Hacemos que todo el contenido sea limpiado, a diferencia del defileAll, usamos el arreglo 
     * de data para hacer eso
     * ********************************************************************************************** */

    public function cleanAll() {
        foreach ($this->raw as $key => $rawFila) {
            $this->status[$key] = _X_IDENTITYMAP_CLEAN;
        }
    }

    /*     * *******************************************************************************
     * clean: Indica que el dato en el indice $id a sido actualizado, la proxima vez
     * que se pregunte por el, se obtendra el dato desde el ARREGLO principal
     * ******************************************************************************* */

    public function clean($id) {
        $this->status[$id] = _X_IDENTITYMAP_CLEAN;
    }

    /*     * *******************************************************************************
     * add: Añade el valor $raw a la posicion $id del ARREGLO principal, ademas
     * lo indica como limpio en el ARREGLO de estados
     * ******************************************************************************* */

    public function add($id, $raw) {
        //if($this->class == 'player'){
        //debug::trace('identitymap->add de '.$this->class.'['.$id.']');
        //}
        /*
          if($this->class=='region'){

          } */ //Deberia de haber una validacion en cada identity
        //debug::trace($id,'id add '.$this->name.'.php');
        //debug::warning($this->raw,'before raw['.$id.'] add '.$this->name.'.php');
        //debug::warning($this->status,'before status['.$id.'] add battleman.php');

       
        $this->raw[$id] = $raw;
       

        $this->clean($id);
        

        /* debug::warning($this->raw,'after raw['.$id.'] add '.$this->name.'.php');
          debug::warning($this->status,'after status['.$id.'] add '.$this->name.'.php'); */
    }

    /*     * *******************************************************************************
     * getFromPersistence: Obtiene un dato mediante llamada a la persistencia de datos
     * ******************************************************************************* */

    public function getFromPersistence($sql) {
        $dt = $this->db->fetchDt($sql);
        //debug::log($dt,"getFromPersistence");
        if (!$dt->isValid()) {
            debug::warning($dt, 'identitymap->getFromPersistence: Posible Error');
            debug::trace('indetitymap->getFromPersistence');
        }
        //debug::warning($sql,'getFromPersistence '.$this->name.'.php');
        //debug::warning($raw,'getFromPersistence '.$this->name.'.php');
        return $dt->getData();
    }

    /*     * **********************************************************************************
     * wrap: Devuelve un Objeto del tipo identity, al cual se le añade el arreglo de $data
     * ********************************************************************************** */

    public function wrap(&$data) {

        $obj = new $this->class;
        $obj->initByArray($data);
        $this->setObj($obj->getId(), $obj); //2oene2013 jeeba Ahora tenemos un array de objetos
        return $obj;
    }

    public function getRaw() {
        return $this->raw;
    }

    //[OJO Jeeba 13feb2013] Funcion rapida para setear al instante todo el array si se obtiene de una forma masiva
    public function setRaw($raw) {
        /* if($this->table == 'planet'){
          debug::log("identitymap->setRaw");
          } */
        $this->raw = $raw;
        $this->cleanAll();
        $this->oraw = null;
    }

    public function getState() {
        return $this->status;
    }

    public function updateDt($id, $param, $value) {
        $dt = new datatransfer();
        $dt->error('update->identitymap->update Error Por defecto');
        if (empty($this->table)) {
            debug::error('La variable tabla esta vacia, asegurese de pasarla por el constructior del map', 'identittmap->update');
        } else {
            $sql = "UPDATE " . $this->table . " SET " . $param . " = " . $value . " WHERE id = " . $id;
            $dt = $this->db->updateDt($sql);
        }
        return $dt;
    }

    public function isDefiled($id) {
        $statusArray = $this->getState();
        $status = $statusArray[$id];
        if ($status == _X_IDENTITYMAP_DEFILED) {
            return true;
        } else {
            return false;
        }
    }

    //[TODO Jeeba 13feb2013] Me parece que esto deberia de ser protected
    public function setObj($id, $obj) {
        $this->oraw[$id] = $obj;
    }

    public function &getObj($id) {
        return $this->oraw[$id];
    }

    public function processSql($sql) {
        $o = false;
        $raw = $this->db->fetch($sql);
        if (!empty($raw['id'])) {
            $o = $this->wrap($raw);
        }
        return $o;
    }

}

?>