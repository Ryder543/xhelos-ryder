<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Clase Abstracta que maneja identities agrupados por algun tipo. Por ejemplo
 * en planetstate, se obtienen colonias pero agrupadas por region. Muy genial!
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
 * @since     30/12/2012
 * @author windows7
 */
abstract class gidentities extends identities{

    /**
     * Descripcion de Campo de Clase
     * @var array|null
     */

    //////////////////////////////////////////////////////////////////////////////////
    //CONSTRUCTOR
    ////////////////////////////////////////////////////////////////////////////////// 
    public function __construct($managerName) {
        parent::__construct($managerName);//CARAJOOOO
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
     * @since   30/12/2012
     * @author  windows7
     *
     * @edit    30/12/2012<br />
     *          windows7<br />
     *          Creacion Inicial <br/>
     *          #edit1
     */
    
    //Retorna el Manager Asociado a esta variable!
    public function getPlayer() {
        return session::getCurrentPlayer();//[TODO] Eliminar esta funcion porque session puede obtenerlo todo
    } 
    
    
    //////////////////////////////////////////////////////////////////////////////////
    //GETTERS AND SETTERS
    ////////////////////////////////////////////////////////////////////////////////// 
}

?>
