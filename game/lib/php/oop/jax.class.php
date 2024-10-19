<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Las Clases Jax se encargan de conectar las llamadas a Ajax en el juego.
 * Su primera mision es validar todas las variables que POST o GET que entran al sistema
 * Su segunda mision es tener toda la data inicial necesaria para ejecutar una accion
 * Su tercera mision es la de ejecutar acciones de clase work
 * Su cuarta mision es devolver lo que corresponsa al servidor
 * Debe de tener un estado datatransfer global que indica en cualquier momento si tuvo errores o warnings en todo este proceso
 *
 * Informacion acerca del constructor de la clase
 *
 * @category  Xhelos
 * @package   Main
 * @license   http://www.opensource.org/licenses/BSD-3-Clause
 * @example   ../index.php
 * @example
 * 	$oUser = new MyLibrary\User(new Mappers\UserMapper());
 *      $oUser->setUsername('swader');
 *      $aAllEmails = $oUser->getEmails();
 *      $oUser->addEmail('test@test.com');
 * @version   0.01
 * @since     26/01/2013
 * @author windows7
 */
require_once(dirname(__FILE__) . '/../security.php'); //Advertencia de Seguridad

abstract class jax extends dtstate {

    protected $id;
    protected $gs;
    protected $type;
    protected $requestParams;
    protected $gameParams;

    public function __construct() {
        $this->prepareRequestParams();
        //$this->init();
        parent::__construct();
    }

    public function init($id, $type) {
        $planetman = planetman::singleton();
        //debug::log($planetman,"jax->init");
        $this->setId($id);
        $this->setType($type);
        $this->gs = new globalview($this->getId(), $this->getType());
        $db = db::singleton();
        $db->begin();
    }

    public function send() {
        $db = db::singleton();
        if ($this->isOk()) {
            $this->getGs()->setIfOldDtIsOk($this->getDt());
            $db->commit();
            $this->getGs()->renderAjaxData();
        } else {
            $db->rollback();
            ajaxutil::sendAjaxError($this->getDt());
        }
    }

    //////////////////////////////////////////////////////////////////////////////////
    //GETTERS and SETTERS
    //////////////////////////////////////////////////////////////////////////////////   
    public function setId($id) {
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }

    public function getGs() {
        return $this->gs;
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function getType() {
        return $this->type;
    }

    public function getRequestParams() {
        return $this->requestParams;
    }

    public function getGameParams() {
        return $this->gameParams;
    }

    public function getRequestParam($varName) {
        if (isset($this->requestParams[$varName])) {
            return $this->requestParams[$varName];
        } else {
            $this->setBadDt("No se envio la variable [" . $varName . "]");
            return false;
        }
    }

    /* public function getParam($varName){
      return $this->getRequestParam($varName);

      } */

    public function getGameParam($varName) {
        if (!isset($this->gameParams[$varName])) {
            //debug::warning("GameParam no Existe:[".$varName."]");
            return false;
        } else {
            return $this->gameParams[$varName];
        }
    }

    //Private porque deberian de usar los valNumericRequestParam
    private function setRequestParam($varName, $value) {
        $this->requestParams[$varName] = $value;
    }

    //Public porque sabremos que tipo de param ponemos aqui, no vienen del cliente, si no dentro del juego.
    public function setGameParam($varName, $value) {
        $this->gameParams[$varName] = $value;
    }

    protected function valNumericRequestParam($varName) {
        if ($this->isOk()) {
            if (isset($_POST[$varName])) {
                $numeric = validator::filter_integer($_POST[$varName]);
                if (!$numeric) {
                    if ($numeric == 0) {
                        $this->setRequestParam($varName, $numeric);
                    } else {
                        debug::error("jax.class->valNumericRequestParam variable REQUEST[" . $varName . "][" . $_POST[$varName] . "] no es un integer");
                        $this->setBadDt("Variable [" . $varName . "] no existe");
                    }
                } else {
                    $this->setRequestParam($varName, $numeric);
                }
            } else {
                debug::error("1 jax.class->valNumericRequestParam variable REQUEST[" . $varName . "] no esta definida");
                $this->setBadDt("Variable [" . $varName . "] no existe");
            }
        }
    }

    protected function valNumericAndNullRequestParam($varName) {
        if ($this->isOk()) {
            if (isset($_POST[$varName])) {
                $numeric = validator::filter_integer_and_null($_POST[$varName]);
                if (!$numeric) {
                    if ($numeric == 0) {
                        $this->setRequestParam($varName, $numeric);
                    } else {
                        debug::error("jax.class->valNumericRequestParam variable REQUEST[" . $varName . "][" . $_POST[$varName] . "] no es un integer");
                        $this->setBadDt("Variable [" . $varName . "] no existe");
                    }
                } else {
                    $this->setRequestParam($varName, $numeric);
                }
            } else {
                debug::error("2 jax.class->valNumericRequestParam variable REQUEST[" . $varName . "] no esta definida");
                $this->setBadDt("Variable [" . $varName . "] no existe");
            }
        }
    }

    protected function valStringRequestParam($varName) {
        //debug::trace("jax.class valStringRequestParam");
        if ($this->isOk()) {
            if (isset($_POST[$varName])) {
                $string = validator::filter_string($_POST[$varName]);
                if (!$string) {
                    debug::error("jax.class->valStringRequestParam variable REQUEST[" . $varName . "][" . $_POST[$varName] . "] no es un string o no se pudo validar");
                    $this->setBadDt("Variable [" . $varName . "] no se valido o filtro como string");
                } else {
                    $this->setRequestParam($varName, $string);
                }
            } else {
                debug::error("3 jax.class->valStringRequestParam variable REQUEST[" . $varName . "] no esta definida");
                $this->setBadDt("Variable [" . $varName . "] no existe");
            }
        }
    }

    //////////////////////////////////////////////////////////////////////////////////
    //METODOS UTILITARIOS
    //////////////////////////////////////////////////////////////////////////////////   
    public function getCurrentPlayer() {
        if ($this->isOk()) {
            return $this->gs->getPlayer();
        }
    }

    public function getOState() {
        return $this->getGs()->getOState();
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////
    //doWork, esta clase ejeucta la accion y cambia el estado de jax segun lo que devuelva el work, inical al 
    //work con data de Jax
    /////////////////////////////////////////////////////////////////////////////////////////////////////
    protected function doWork(work $work) {
        $work->initParamsFromJax($this);
        $work->doIt();
        if ($work->isOk()) {
            $this->setIfOldDtIsOk($work->getDt());
        } else {
            $this->setBadDt($work->getDt()->getText());
        }
    }

    //////////////////////////////////////////////////////////////////////////////////
    //METODOS ABSTRACTOS
    //////////////////////////////////////////////////////////////////////////////////
    abstract protected function prepareRequestParams();
}

?>