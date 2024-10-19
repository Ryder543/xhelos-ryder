<?php
 require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
/*******************************************************
 * validator: Para validar formularios por XML
 ******************************************************/
class validator			//Change the name of the class Jose Carlos designbyjeeba@gmail.com
{	
	public	function __construct()//Constructor Publico, inicializa los valores
	{
	}

  public static function sanitize_integer($integer){
      $resp = filter_var($integer,FILTER_SANITIZE_NUMBER_INT);
      if($resp){
        return $resp;
      }
      debug::error('No es un entero Sanitizado! Variable['.$integer.']','validator');
      return $resp;
  }
  
  
  public static function filter_integer_and_null($integer){
          if(!isset($integer)){
      debug::trace('Variable no existe','validator.class filter_integer');
      return false;
    }  
    $resp = filter_var(intval($integer),FILTER_SANITIZE_NUMBER_INT);
    
    if($resp){
      //debug::warning('Si existe Resp','validator.class filter_integer');
      return $resp;
    }
    elseif($integer ===0){//Doble === porque un String == 0 es siempre TRUE :S http://bugs.php.net/bug.php?id=39579
    debug::trace('Variable es cero','validator.class filter_integer');
      return $integer;
    }
    elseif($integer == '0'){
      //debug::trace('Si Variable['.$integer.'] no es 0, entonces hay un problema','validator.class filter_integer');
      return $integer;
    }
    elseif($integer == null){
        return $integer;
    }
    //debug::error('Variable no es un entero Filtrado! Variable['.$integer.'] es del tipo '.gettype($integer),'validator.class filter_integer');
    return $resp;
  }
  
  public static function filter_string($string){
     if(!isset($string)){
      debug::trace('Variable no existe','validator.class filter_string');
      return false;
    }  
    $resp = filter_var($string,FILTER_SANITIZE_STRIPPED);
    return $resp;
  }
  
  public static function filter_integer($integer){
    if(!isset($integer)){
      debug::trace('Variable no existe','validator.class filter_integer');
      return false;
    }  
    $resp = filter_var(intval($integer),FILTER_SANITIZE_NUMBER_INT);
    
    if($resp){
      //debug::warning('Si existe Resp','validator.class filter_integer');
      return $resp;
    }
    elseif($integer ===0){//Doble === porque un String == 0 es siempre TRUE :S http://bugs.php.net/bug.php?id=39579
    debug::trace('Variable es cero','validator.class filter_integer');
      return $integer;
    }
    elseif($integer == '0'){
      //debug::trace('Si Variable['.$integer.'] no es 0, entonces hay un problema','validator.class filter_integer');
      return $integer;
    }
    debug::error('Variable no es un entero Filtrado! Variable['.$integer.'] es del tipo '.gettype($integer),'validator.class filter_integer');
    return $resp;
    
  }

}//class

?>
