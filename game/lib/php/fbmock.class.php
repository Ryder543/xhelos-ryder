<?php
 require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
/****************************************************************
 * fbmock: Clase para mentir al sistema de que existe firebug
 ***************************************************************/
class fbmock
{
    public function trace($data,$label =''){

    }
    public function warn($data,$label =''){
    }

    public function log($data,$label =''){
    
    }
    
    public function info($data,$label =''){

    }  
    
    public function error($data,$label =''){

    } 
}
?>