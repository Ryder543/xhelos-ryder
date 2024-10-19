<?php 
require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad

    
/*********************************************************
 * menustate: Encargado de mantener el estado del menu
 ********************************************************/
  class userview extends view
  {
    ///////////////////////////////////////////////////////////////////////////////////////
    //PROPIEDADES
    ///////////////////////////////////////////////////////////////////////////////////////    
    //private $menu;
 
     private $oPlayer;     
     private $db;
     
    ///////////////////////////////////////////////////////////////////////////////////////
    //CONSTRUCTOR
    ///////////////////////////////////////////////////////////////////////////////////////    
    /********************************************
    * Constructor: De menuState
     * Params: 
     * $region_id: ID de la region que quiere mantener el estado
     * $player_id: ID del jugador actual de la region
     * EXTRA: Obtener la lista de arg $args = func_get_args(); 
    ********************************************/  
    public function __construct(){
        //$this->menu = new menu();
        
        $this->oPlayer = $this->man("player")->findById($_SESSION['xid']);
        $this->db = db::singleton();
    }
    ///////////////////////////////////////////////////////////////////////////////////////
    //PRINCIPALES
    ///////////////////////////////////////////////////////////////////////////////////////        
   
    /****************************************************
    * getMenuState(): Obtenemos el estado de los menus
    *****************************************************/ 
    public function prepareState(){
        $colony = $this->getActualColony();
        //$res = $colony->getResources();
       // $res = $this->getPlayer()->getResources();
        $_xeno_state = array();
        //$_xeno_state['resources'] = $res;
        $_xeno_state['colony'] = $colony->getRaw();
        $_xeno_state['player'] = $this->getPlayer()->getRaw();
        $_xeno_state['player']['actual_energy'] = round($_xeno_state['player']['actual_energy'],2);
 
        if($this->getPlayer()->isTutorialActive() ){
            if($this->getPage() == 'region'){
                $_xeno_state['tutorial']['step'] = $this->actualTutorialStep();
            }
            
        }
        $_xeno_state['colonies'] = $this->getColonies()->getRaw();
        return $_xeno_state;
    }

    public function updateState(){
        //TODO la mayor parte del codigo lo debe de hacer la clase player
        $oPlayer = $this->getPlayer();
        $playerId = $oPlayer->getId();
        $sql="SELECT abs(time_to_sec2(last_update,now()))/"._TICK." AS debe
        FROM players WHERE id = ".$playerId;
        $time_raw = $this->db->fetch($sql);
        //debug::log($sql,'menustate->updatePlayer time_sql');
        //debug::log($time_raw,'menustate->updatePlayer time_raw');
        
        if($time_raw['debe']>0){		
                $time = ($time_raw['debe']/_X_SECONDS_IN_DAY );
                $sql="UPDATE players SET  
                actual_energy =LEAST(actual_energy+(rate_energy)*".$time.",max_energy),
                res1 = LEAST(res1+(rate_res1)*".$time.",max_res1),
                res2 = LEAST(res2+(rate_res2)*".$time.",max_res2),
                res3 = LEAST(res3+(rate_res3)*".$time.",max_res3),
                last_update = now()
                WHERE id = ".$playerId;
                //debug::log($sql,'menustate->updatePlayer');
                $oPlayer->setDataDirty();
                $this->db->query($sql);
        }
        $dt = new datatransfer();
        $dt->success("TODO cambiar esto por pruebas reales");
        return $dt;
    }
    
    ///////////////////////////////////////////////////////////////////////////////////////
    //GETTERS
    ///////////////////////////////////////////////////////////////////////////////////////
  public function getPlayer(){
      return $this->oPlayer;
  } 
  
  public function getPlayerId(){
      return $this->oPlayer->getId();
  }

    public function validateStateDt() {
        $dt = new datatransfer();
        $dt->success("[TODO]Validar el estado del user de alguna manera");
        //$dt->success():
        debug::warning("[TODO]Validar el estado del user de alguna manera","regionstate->validateStateDt");    
        return $dt;
        //Verificar que la colonia le pertenezca al jugador
    }

    public function render() {
        
    }

    public function getTitle() {
        return "Usuario ".$this->getPlayer()->getName();
    }        
  
    
}
?>