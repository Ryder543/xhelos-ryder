<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Clase que permite a otras clases identificarse como una clase capas de enviar datos al cliente
 * por medio de JSON o lo que sea
 *
 * El constructor no recibe nada, no es necesario que tenga un constructor especificado
 *
 * @category  Xhelos
 * @package   Abstract
 * @license   http://www.opensource.org/licenses/BSD-3-Clause
 * @example   identities.class.php
 * @example
 * 	No Example
 * @version   0.01
 * @since     30/12/2012
 * @author Jeeba
 */
abstract class stateable{

    //////////////////////////////////////////////////////////////////////////////////
    //CONSTRUCTOR
    ////////////////////////////////////////////////////////////////////////////////// 
    public function __construct() {
        
    }

    //////////////////////////////////////////////////////////////////////////////////
    //METODOS PRINCIPALES
    //////////////////////////////////////////////////////////////////////////////////      

    /**
     * getState: Devuelve el estado de la clase como un array
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
    public function getState(){//getPreparedRaw
        if($this->exist()){
            $preparedRaw = $this->prepareRaw();
            return $preparedRaw;
        }
        else{
            return false;
        }
    }

    public function getCurrentPlayer() {
        return session::getCurrentPlayer();//[TODO] Eliminar esta funcion porque session puede obtenerlo todo
    }
    
    abstract public function prepareRaw();  //Prepara el raw para su envio por json
    abstract public function getRaw(); // Clase que obtiene el raw
    abstract public function exist();

    //////////////////////////////////////////////////////////////////////////////////
    //GETTERS AND SETTERS
    ////////////////////////////////////////////////////////////////////////////////// 
}

?>
