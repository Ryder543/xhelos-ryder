<?php
 require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
/****************************************************************
 * singleton: TEMPLATE para crear singletons 
 ***************************************************************/
class singleton
{
	////////////////////////////////////////////////////////////////////////////////////
	////// CAMPOS
	////////////////////////////////////////////////////////////////////////////////////
	private static $instance;//Contiene la instancia unica del parseador de json
	private $db;// Variable que contiene la instancia unica de la base de datos
	
	////////////////////////////////////////////////////////////////////////////////////
	////// METODOS Principales
	////////////////////////////////////////////////////////////////////////////////////
	
	
	
	////////////////////////////////////////////////////////////////////////////////////
	////// METODOS AUXILIARES
	////////////////////////////////////////////////////////////////////////////////////
	/*********************************************************************************
	* Constructor: El constructor de la Clase
	*********************************************************************************/
	private	function __construct()//Constructor Privado de json, solo existe un objeto de este tipo
	{
		$this->init();
	}
	/*********************************************************************************
	* singleton: Obtiene la instancia del singleton
	*********************************************************************************/		
	public static function singleton()//Obtiene la instancia unica de esta clase
    {
       if (!isset(self::$instance)) {
           $c = __CLASS__;
           self::$instance = new $c;
       }
       return self::$instance;
    }
	
	/*********************************************************************************
	* _clone: Impide que se copie 
	*********************************************************************************/	  
   public function __clone() // Prevenimos que este objeto sea clonado
   {
       trigger_error('Clone is not allowed. db_pg_class', E_USER_ERROR);
   }
 	/*********************************************************************************
	* init: Iniciamos la clase [TODO]Eliminarlo 
	*********************************************************************************/	  
	private function init()
	{
		$this->db=db::singleton();
		if(!$this->db) 
		{
			echo 'ERROR: Could not connect to the database. json.php';
		}
		else
		{
			$this->query=array();
		}
	}
}//singleton
?>