<?php

  require_once(dirname ( __FILE__ ).'/../security.php'); //Advertencia de Seguridad

  abstract class util extends dtstate{
    protected $db;

    //////////////////////////////////////////////////////////////////////////////////
    //CONSTRUCTOR
    //////////////////////////////////////////////////////////////////////////////////
    public function __construct(){
        $this->db = db::singleton();
    }

    //Retorna el Manager Asociado a esta variable!
    public function man($name){
        $managerClass = $name . 'man';
        //debug::log($managerClass,'identity->man1');
        $obj = eval('return '.$managerClass.'::singleton();'); //[TODO] no deberiamos pero...
        return $obj;
    }
    
    public function getCurrentPlayer() {
        return session::getCurrentPlayer();//[TODO] Eliminar esta funcion porque session puede obtenerlo todo
    }
    
     public static function  orderMasculine($order){
        $list = array('primer','segundo','tercer','cuarto','quinto','sexto','septimo','octavo','noveno','decimo','decimoprimero','decimosegundo');
        if(!isset($list[$order])){
            return $order.'va';
        }
        else{
            return $list[$order];
        }
    }
    public static function  orderFemenine($order){
        $list = array('primera','segunda','tercera','cuarta','quinta','sexta','septima','octava','novena','decima','decimoprimera','decimosegunda','decimotercera','decimocuarta');
        if(!isset($list[$order])){
            return $order.'va';
        }
        else{
            return $list[$order];
        }
    }
    
   

  }

?>