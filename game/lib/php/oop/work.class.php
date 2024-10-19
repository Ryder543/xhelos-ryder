<?php

require_once(dirname(__FILE__) . '/../security.php'); //Advertencia de Seguridad

/**
 * Clase que nos permite controlar un datatransfer por objeto. Con este objeto
 * podemos saber facilmente cual es el estado success o error de la clase.
 * Muy util para clases que tienen que hacer un monton de validaciones y 
 * saber el estado interno del objeto sin necesidad de pasar un datatransfer
 * como parametro
 * is the main contact email.
 *
 * Nada especial con el constructor.
 *
 * @category  Xhelos
 * @package   Main
 * @license   http://www.opensource.org/licenses/BSD-3-Clause
 * @example   lib/php/act/regionColonyCreateColonyAction.php
 * @version   0.01
 * @since     2012-dic-23
 * @author    Jeeba <designbyjeeba@gmail.com>
 */
abstract class work extends dtstate {

    /**
     * Objeto Datatransfer que mantiene el estado success/error de la pagina.
     * @var datatransfer
     */
    protected $dt;
    protected $executeDt;
    protected $params;
    protected $breakWork; //Boleano
    protected $originOfWork;
    protected $oState;
    
    public function setOrigin($origin){
        $this->originOfWork = $origin;
    }
    
    public function getOrigin(){
        return $this->originOfWork;
    }
    
    public function __construct() {
        $this->breakWork = false; //Por defecto no necesitamos romper un trabajo
        $this->executeDt = new datatransfer();
        $this->executeDt->success("executeDt Inicial",false);
        $this->oState= null;
        $this->setOrigin(_X_WORK_ORIGIN_UNDEFINED);//Por defecto es indefinido, quien decide esto es el que lo llama
    }

    //////////////////////////////////////////////////////////////////////////////////
    //METODOS PRINCIPALES
    //////////////////////////////////////////////////////////////////////////////////  

    /**
     * do: Valida primero los parametros, verifica que los breaks funcionen  y despues ejecuta la accion. Una vez finalizada la accion
     * este objeto contiene en su dt el estado actual de la accion. Por lo que 
     * podemos saber si la accion ha sido realizada consultando al dt. Este se 
     * parara en el primer error encontrado si lo hubiere. Puede ademas parase si
     * el estado se breakea. No necesariamente un estado breakeado es error, puede 
     * deberse a que simplemente no es necesario ejecutar el trabajo.
     *
     * @return  None
     * @since   2012-Dic-23
     * @author  Jeeba <designbyjeeba@gmail.com>
     *
     * @edit    2012-Dic-31<br />
     *          Jeeba <designbyjeeba@gmail.com>
     *          Cambiamos nombre a do y añadimos verificacion de parametros automatico<br/>
     *          #edit2
     * @edit    2012-Dic-23<br />
     *          Jeeba <designbyjeeba@gmail.com>
     *          Funcionalidad Inicial<br/>
     *          #edit1
     */
    public function doIt() {
        $this->setIfOldDtIsOk($this->validateParams()); //1) Validamos los parametros
        $this->breakIf();                               //2) Rompemos  el work si no es necesario hacerlo
        if (!$this->isBreaked()) {
            $this->validate();
            if ($this->getDt()->ok()) {
                $this->execute();
            }
        }
    }
    
    public function doInnerWork(work $innerWork) {
        //Se ejecuta si o si el inner work
        $innerWork->initParamsFromWork($this);
        $innerWork->doIt();
        //[OJO] Jeeba 01feb2013 La data de executeDt de this puede ser usada afuera, si la cambias por la del innerWork cosas malas
        //van a pasar, por ejemplo en el registro de un jugador en entityplayerutil, linea 244
        //$this->setExecuteDt($innerWork->getExecuteDt());
        $this->setIfOldDtIsOk($innerWork->getDt());
    }
    /**
     * isBreaked() Nos indica si debemos de parar la ejecucion del work, no porque
     * haya un error si no porque ya no es necesario, por ejemplo ownear una region
     * que ya esta owneada por el usuario que quiere ownearla
     *
     * @return  boolean, true si debemos de breakear el work, false lo contrario
     * @since   2013-Ene-31
     * @author  Jeeba <designbyjeeba@gmail.com>
     *
     * @edit    2013-Ene-31<br />
     *          Jeeba <designbyjeeba@gmail.com>
     *          Funcionalidad Inicial<br/>
     *          #edit1
     */
    public function isBreaked() {
        if ($this->breakWork == true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * breakWork() Breakeamos el work, para que ya no se ejecute mas
     *
     * @return  nada
     * @since   2013-Ene-31
     * @author  Jeeba <designbyjeeba@gmail.com>
     *
     * @edit    2013-Ene-31<br />
     *          Jeeba <designbyjeeba@gmail.com>
     *          Funcionalidad Inicial<br/>
     *          #edit1
     */
    public function breakWork() {
        $this->breakWork = true;
    }

    /**
     * setIfOldDtIsOk() Override de dtstate, preguntamos si ademas esta breakeado el
     * work, para no setear el dt al que enviamos
     *
     * @return  $dt
     * @since   2013-Ene-31
     * @author  Jeeba <designbyjeeba@gmail.com>
     *
     * @edit    2013-Ene-31<br />
     *          Jeeba <designbyjeeba@gmail.com>
     *          Funcionalidad Inicial<br/>
     *          #edit1
     */
    public function setIfOldDtIsOk($dt) {
        if (!$this->isBreaked()) {
            if ($this->getDt()->ok()) {
                $this->dt = $dt;
            }
        }
    }
    
    public function setIfAllOkText($text) {
        if (!$this->isBreaked()) {
            if ($this->getDt()->ok()) {
                $this->dt->setText($text);
            }
        }
    }
    /**
     * setIfOldDtIsOkWithCallback() Override de dtstate, preguntamos si ademas esta breakeado el
     * work, para no seguir ejecutando nada, ni el callback
     *
     * @return  $dt
     * @since   2013-Ene-31
     * @author  Jeeba <designbyjeeba@gmail.com>
     *
     * @edit    2013-Ene-31<br />
     *          Jeeba <designbyjeeba@gmail.com>
     *          Funcionalidad Inicial<br/>
     *          #edit1
     */
    public function setIfOldDtIsOkWithCallback($callback) {
        if (!$this->isBreaked()) {
            if ($this->getDt()->ok()) {
                $dt = $this->$callback();
                $this->dt = $dt;
            }
        }
    }
    
    //////////////////////////////////////////////////////////////////////////////////
    //GETTER AND SETTER
    //////////////////////////////////////////////////////////////////////////////////   
    public function getExecuteDt() {
        //Preguntamos si el DT esta normal, si no kaput
        if ($this->isOk()) {
            return $this->executeDt;
        } else {
            return $this->getDt();
        }
    }

    public function setExecuteDt(datatransfer $dt) {
        $this->executeDt = $dt;
        ;
    }

    public function setParam($paramName,$paramValue){
        $this->params[$paramName] = $paramValue;
    }
    
    protected function getParam($varName) {
        if (isset($this->params[$varName])) {
            $varParam = $this->params[$varName];
        } else {
            debug::warning("work->getParam Variable[" . $varName . "] no esta seteada");
        }

        return $varParam;
    }

    //Inicializamos la data desde un Jax
    public function initParamsFromJax(jax $jax) {
        $this->params = array_merge($jax->getRequestParams(), $jax->getGameParams());
        $this->oState = $jax->getOState();
    }

    //Inicializamos la data desde un array
    public function initParamsFromArray(array $arraySource) {
        $this->params = $arraySource;
    }

    //Inicializamos la data desde otro Work
    public function initParamsFromWork(work $parentWork) {
        $this->params = $parentWork->getParams();
    }

    public function getParams() {
        return $this->params;
    }

    protected function getPlayer() {
        //debug::log($this->getParams(),'params');
        $playerId = $this->getParam(_X_VAR_PLAYER_ID);
        $playerman = playerman::singleton();
        $oPlayer = $playerman->findById($playerId);
        return $oPlayer;
    }
    //Solo funciona si quien lo inicializa fue un JAX!
    protected function  getOState(){
        return $this->oState;
    }

    protected function validateParamIfNotEmpty($paramName){
        if($this->isOk()){
            $param = $this->getParam($paramName);
            if ((!isset($param)) || (empty($param))) {
                debug::error('El parametro ['.$paramName.'] esta vacio', 'work->validateParamIfNotEmpty');
                $this->setBadDt('No hay ninguna accion seleccionada');
            }
        }
    }
    
    //////////////////////////////////////////////////////////////////////////////////
    //METODOS ABSTRACTOS
    //////////////////////////////////////////////////////////////////////////////////
    //Parametro mandamos el objeto que nos dara los parametros que necesitamos
    abstract public function validate(); //Envia los datos como ajax
    abstract public function breakIf(); //Envia los datos como ajax    

    /**
     * execute() Ejecutamos el trabajo!. Peligro es PROTECTED porque no deberia de ejecutarlo nadie 
     * mas que sus instancias. Para ejecutar un trabajo deberia de lamarse a doIt(). Si lo ejecutamos
     * defrente no haremos las validaciones de break y de dt.
     *
     * @return  $dt
     * @since   2013-Ene-31
     * @author  Jeeba <designbyjeeba@gmail.com>
     *
     * @edit    2013-Ene-31<br />
     *          Jeeba <designbyjeeba@gmail.com>
     *          Funcionalidad Inicial<br/>
     *          #edit1
     */
    abstract protected function execute(); //Ej

    abstract protected function validateParams();
    
}

?>