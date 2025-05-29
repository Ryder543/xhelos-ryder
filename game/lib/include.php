<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
define('DEBUG_ENABLED', false); // Toggle this to enable/disable debugging

//INCLUDES - INICIO
require_once(dirname(__FILE__) . '/security.php');

function XHELOS__autoload($class)
{
    if(file_exists($class . '.class.php')){include($class . '.class.php');}
    if(file_exists(dirname(__FILE__).'/php/man/' . $class . '.class.php')){include(dirname(__FILE__).'/php/man/' . $class . '.class.php');}
    if(file_exists(dirname(__FILE__).'/php/obj/' . $class . '.class.php')){include(dirname(__FILE__).'/php/obj/' . $class . '.class.php');}
    if(file_exists(dirname(__FILE__).'/php/obs/' . $class . '.class.php')){include(dirname(__FILE__).'/php/obs/' . $class . '.class.php');}
    if(file_exists(dirname(__FILE__).'/php/oop/' . $class . '.class.php')){include(dirname(__FILE__).'/php/oop/' . $class . '.class.php');}
    if(file_exists(dirname(__FILE__).'/php/vie/' . $class . '.class.php')){include(dirname(__FILE__).'/php/vie/' . $class . '.class.php');}
    if(file_exists(dirname(__FILE__).'/php/uti/' . $class . '.class.php')){include(dirname(__FILE__).'/php/uti/' . $class . '.class.php');}
    if(file_exists(dirname(__FILE__).'/php/wor/' . $class . '.class.php')){include(dirname(__FILE__).'/php/wor/' . $class . '.class.php');}    
    if(file_exists(dirname(__FILE__).'/php/jax/' . $class . '.class.php')){include(dirname(__FILE__).'/php/jax/' . $class . '.class.php');}    
    if(file_exists(dirname(__FILE__).'/php/gob/' . $class . '.class.php')){include(dirname(__FILE__).'/php/gob/' . $class . '.class.php');}        
    if(file_exists(dirname(__FILE__).'/php/' . $class . '.class.php')){include(dirname(__FILE__).'/php/' . $class . '.class.php');}
} 
spl_autoload_register('XHELOS__autoload');

$game_lib_include_directory = getcwd();
chdir(dirname(__FILE__));
ob_start();


/* ----------------------------------------------------------
  Seteo de la Variable de Server
  ---------------------------------------------------------- */
//CURRENT SERVER URL
$currentServerUrl = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$currentServerUrl .= "://".$_SERVER['HTTP_HOST'];

// Check if the current server URL matches any specific conditions
if ($currentServerUrl == 'http://localhost' || $currentServerUrl == 'http://192.168.1.37') {
    define("_X_FILE_EXTRA_URL", '/xhelos');
} else {
    define("_X_FILE_EXTRA_URL", '');
}

// Define the root URL based on the current server URL and additional path
define("_X_FILE_ROOT", $currentServerUrl . _X_FILE_EXTRA_URL . "/");



/* ----------------------------------------------------------
  Database Values, Postgres and Mysql
  ---------------------------------------------------------- */

//////////////////////VARIABLES BD/////////////////////////////
define("BD_TRUE", "t");
define("BD_FALSE", "f");
define("SERVER_PSGR", "localhost");
define("USER_PSGR", "xhelos_xhelos2");
define("PASSWORD_PSGR", "l=f6oLV^qU+6");
define("DATABASE_PSGR", "xhelos_xhelos");
define("PORT_PSGR", "5432");

define("USER_MYSQL", "xhelos");
define("PASSWORD_MYSQL", "xhelos");
define("SERVER_MYSQL", "database");
define("DATABASE_MYSQL", "xhelos");
define("PORT_MYSQL", "3306");

/* ----------------------------------------------------------
  Funciones y Defines
  ---------------------------------------------------------- */
require_once(dirname(__FILE__) . '/defines.php');
require_once(dirname(__FILE__) . '/functions.php');


//require_once('php/FirePHP.class.php');
/* ----------------------------------------------------------
  Manejo de Sesiones
  ---------------------------------------------------------- */

//[17oct2024 jsk] Creacion de la clase que gestionara las sessiones en Xhelos.
/*A partir de ahora las sessiones se gestionaran directamente con Xhelos aunque
copiaremos un poco el funcionamiento*/
//$sessionHandler = new XhelosSessionHandler();

//require_once($game_lib_include_directory . '/lib/php/session.class.php');
$sessionHandler = new XhelosSessionHandler(db::singleton());
//session_set_save_handler($sessionHandler, true);
session_start(); // Start the session here!


/* ----------------------------------------------------------
  Unsetting Variables que no necesitare
  ---------------------------------------------------------- */
chdir($game_lib_include_directory);
unset($game_lib_include_directory);


//INCLUDES - FIN
/*if (!defined('_X_NO_INIT')) {
    require_once('init.php');
}*/

//INCLUDES - FIN
if (defined('_X_ADMIN_INIT')) {
    require_once('admin_init.php');
}

/*
// Set a custom error handler
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    // Check if the error message is related to SELECT command denial
    if (strpos($errstr, 'SELECT command denied to user') !== false) {
        echo "Custom Error: $errstr in $errfile on line $errline\n";
        echo "Backtrace:\n";
        debug_print_backtrace();
    }
    // Allow the default PHP error handler to proceed as usual
    return false;
});*/

?>