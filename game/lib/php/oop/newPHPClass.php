<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Descripcion de lo que hace la clase
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
class newPHPClass {

    /**
     * Descripcion de Campo de Clase
     * @var array|null
     */
    protected $aEmails = null;

    //////////////////////////////////////////////////////////////////////////////////
    //CONSTRUCTOR
    ////////////////////////////////////////////////////////////////////////////////// 
    public function __construct() {
        
    }

    //////////////////////////////////////////////////////////////////////////////////
    //METODOS PRINCIPALES
    //////////////////////////////////////////////////////////////////////////////////      

    /**
     * Descripcion de lo que hace la clase.
     *
     * @param string $parametro Un parametro que se pasa a la clase
     *
     * @return  string
     * @todo    Check to make sure the username isn't already taken
     *
     * @since   26/01/2013
     * @author  windows7
     *
     * @edit    26/01/2013<br />
     *          windows7<br />
     *          Creacion Inicial <br/>
     *          #edit1
     */
    public function nombreFuncion($parametro) {
        return $parametro;
    }

    //////////////////////////////////////////////////////////////////////////////////
    //GETTERS AND SETTERS
    ////////////////////////////////////////////////////////////////////////////////// 
}

?>
