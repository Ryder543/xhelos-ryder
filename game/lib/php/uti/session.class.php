<?php

/* ---------------------------------------------------------------------------------------------------
  CDB_PostGres.PHP -Archivo que contiene a la Clase CDB
  Clase para el manejo de la base de datos Postgres
  Autor: Jose Carlos Tamayo joseka17@hotmail.com
  Mejoras: Michi

  PROPIEDADES:
  ------------------------------------------------------------------------------------------------------
  -SINGLETON: Solo se Instancia una ves
  ---------------------------------------------------------------------------------------------------- */
require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad

class session {

//////////////////////////////////////////////////////////////////////////////////////
//Propiedades de la Clase
///////////////////////////////////////////////////////////////////////////////////////

    private static $instance; //Contiene la instancia unica de la base de datos
    //private static $oPlayer; //Contiene la instancia unica de la base de datos

///////////////////////////////////////////////////////////////////////////////////////
//Metodos de la Clase
///////////////////////////////////////////////////////////////////////////////////////

    public static function getCurrentPlayer() {

        $player = session::man("player")->findById($_SESSION['xid']);
        if (!$player) {
            debug::error('El jugador no se cargo', 'abstract state.class.php');
        }
        return $player;
    }

    //Retorna el Manager Asociado a esta variable! //[TODO] state usa esta funcion, deberia?
    public static function man($name) {
        $managerClass = $name . 'man';
        //debug::log($managerClass,'identity->man1');
        $obj = eval('return ' . $managerClass . '::singleton();'); //[TODO] no deberiamos pero...
        return $obj;
    }

    /* /////////////////////////////////////////////////////////////////////////////////////
      Metodos UTILITARIOS de la Clase
     *//////////////////////////////////////////////////////////////////////////////////////

    private function __construct() {//Constructor Privado, las constantes estan definidas en DBInfo.php
        
    }

    public static function singleton() {//Obtiene la instancia unica de esta clase
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }
        return self::$instance;
    }

    public function __clone() { // Prevenimos que este objeto sea clonado
        trigger_error('Clone is not allowed. session_class', E_USER_ERROR);
    }

}

?>