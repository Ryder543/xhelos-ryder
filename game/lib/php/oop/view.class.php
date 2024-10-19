<?php

  require_once(dirname ( __FILE__ ).'/../security.php'); //Advertencia de Seguridad

  abstract class view extends dtstate{
      
    protected $player;   
    protected $refresh = false;//Funcionara correctamente aun no llamando al consntructor?
    //////////////////////////////////////////////////////////////////////////////////
    //CONSTRUCTOR
    //////////////////////////////////////////////////////////////////////////////////
    public function __construct(){
      //$this->db = db::singleton();//No deberia tener acceso a la BD, en fin
    }

    //Retorna el Manager Asociado a esta variable!
    public function man($name){//[TODO] Eliminar esta funcion porque session puede obtenerlo todo :D
        return session::man($name);
        
        /*$managerClass = $name . 'man';
        //debug::log($managerClass,'identity->man1');
        $obj = eval('return '.$managerClass.'::singleton();'); //[TODO] no deberiamos pero...
        return $obj;*/
    }
    
    public function getPlayer() {
        return session::getCurrentPlayer();//[TODO] Eliminar esta funcion porque session puede obtenerlo todo
        
    }
    
    public function needRefresh(){
        $this->refresh = true;
    }
    
    public function isInNeedOfRefresh(){
        return $this->refresh;
    }
    
    public function getState(){
        $state = $this->prepareState();
        $state['refresh'] = $this->isInNeedOfRefresh();
        return $state;
    }
   
    /* FUNCIONES ABSTRACTAS*/
    abstract public function prepareState();//Porque necesitamos automatizar el refreshement, prepareState es el que realmente saca la data
    abstract public function getTitle();  
    abstract public function validateStateDt();
    abstract public function render();  
    
  }

?>