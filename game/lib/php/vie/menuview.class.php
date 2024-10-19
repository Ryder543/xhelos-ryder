<?php 
require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad

    
/*********************************************************
 * menustate: Encargado de mantener el estado del menu
 ********************************************************/
  class menuview extends view
  {
    ///////////////////////////////////////////////////////////////////////////////////////
    //PROPIEDADES
    ///////////////////////////////////////////////////////////////////////////////////////    
    //private $menu;
     public $sub_nav_hidden;
     public $sub_arm_hidden;
     public $sub_col_hidden;
     public $sub_ali_hidden;
     
     public $men_nav_active;
     public $men_arm_active;
     public $men_col_active;
     public $men_ali_active;
    
     private $oColony;
     private $oRegion;
     private $oPlanet;
     private $oStar;
     private $oPlayer;     
     private $title;          
     private $page;  
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
        
        //$this->oPlayer = $this->man("player")->findById($_SESSION['xid']);
        $this->db = db::singleton();
        //debug::log($this->oPlayer,'menustate->constructor oPlayer');
        //debug::log($_SESSION,'menustate->constructor session');
    }
    ///////////////////////////////////////////////////////////////////////////////////////
    //PRINCIPALES
    ///////////////////////////////////////////////////////////////////////////////////////        
    public function getActualColony(){
        $id = $this->getColonyId();
        $colony = $this->man("colony")->findById($id);
        return $colony;
    }
    
    public function getPageState($queryState){
        if( (strcmp($queryState, $this->getPage()) )==0 ){
            return 'ui-state-active';
        }
        else{
            return 'ui-state-default';
        }
    }
    
    public function getColonies(){
        $colonies = new colonies();
        $colonies->initActiveByPlayerId($this->getPlayerId());
        
        
        //Jeeba 30ene2013 ya no es necesario mandar la lista de recursos por colonia
        /*$res = $this->getPlayer()->getResources();
        foreach($colonies as $i =>$c)
        {
            //$res = $c->getResources();
            $colonies->createParam($i,'resources',$res);
        }*/
        return $colonies;
    }
    
    public function renderRegionTutorial(){
        $output = '';
        if($this->getPage()=='region'){
            //debug::log($this->getPlayer()->getTutorial(),'menustate getplayer gettutorial');
           if($this->getPlayer()->isTutorialActive()){
               $output = '<a href="#desactivartutorial" id="TutorialOn" class="tutorial-switch-button button-on">Tutorial Activo</a>';
           }
           else{
               $output = '<a href="#activartutorial" id="TutorialOff" class="tutorial-switch-button button-off">Tutorial Apagado</a>';
           }
        }
        
        return $output;
    }    


    /****************************************************
    * getMenuState(): Obtenemos el estado de los menus
    *****************************************************/ 
    public function prepareState(){
        
        $colony = $this->getActualColony();
        $_xeno_state = array();
        if(isset($colony) && $colony->exist() ){
            $_xeno_state['colony'] = $colony->getState();    
        }
        else{
            $_xeno_state['colony'] = false;
            debug::warning("Posible Error: La Colonia Actual del Jugador no existe");
        }
        $_xeno_state['player'] = $this->getPlayer()->getState();
        $_xeno_state['player']['actual_energy'] = round($_xeno_state['player']['actual_energy'],2);
        /*$_xeno_state['player']['res6'] = floor($_xeno_state['player']['res6']);
        $_xeno_state['player']['res7'] = floor($_xeno_state['player']['res7']);
        $_xeno_state['player']['res8'] = floor($_xeno_state['player']['res8']);*/
        
        
        
        if($this->getPlayer()->isTutorialActive() ){
            if($this->getPage() == 'region'){
                $_xeno_state['tutorial']['step'] = $this->actualTutorialStep();
            }
            
        }
        
        
        $_xeno_state['colonies'] = $this->getColonies()->getState();
        return $_xeno_state;
    }

    public function updateState(){
  
    }

   public function initByDefault(){
          $this->oColony = $this->getActualColony();
    $this->oRegion = false;
    $this->oPlanet = false;
    $this->oStar = false;
      
     $this->sub_nav_hidden='';
     $this->sub_arm_hidden='hidden';
     $this->sub_col_hidden='hidden';
     $this->sub_ali_hidden='hidden';
     
     $this->men_nav_active='ui-state-active';
     $this->men_arm_active='ui-state-default';
     $this->men_col_active='ui-state-default';
     $this->men_ali_active='ui-state-default';
  
     //$this->oPlayer = $this->man("player")->findById($_SESSION['xid']);
     $this->page = 'galaxy'; 
     $this->title = 'Galaxia '.$this->getGalaxyName();
   } 
  
  public function initByGalaxyId($galaxyId = false){
    $this->oColony = $this->getActualColony();
    $this->oRegion = false;
    $this->oPlanet = false;
    $this->oStar = false;
      
     $this->sub_nav_hidden='';
     $this->sub_arm_hidden='hidden';
     $this->sub_col_hidden='hidden';
     $this->sub_ali_hidden='hidden';
     
     $this->men_nav_active='ui-state-active';
     $this->men_arm_active='ui-state-default';
     $this->men_col_active='ui-state-default';
     $this->men_ali_active='ui-state-default';
  
     //$this->oPlayer = $this->man("player")->findById($_SESSION['xid']);
     $this->page = 'galaxy'; 
     $this->title = 'Galaxia '.$this->getGalaxyName();
  }
  
  public function initByStarId($starId){
      
    $this->oColony = $this->getActualColony();
    $this->oRegion = false;
    $this->oPlanet = false;
    $this->oStar = $this->man("star")->findById($starId);
      
     $this->sub_nav_hidden='';
     $this->sub_arm_hidden='hidden';
     $this->sub_col_hidden='hidden';
     $this->sub_ali_hidden='hidden';
     
     $this->men_nav_active='ui-state-active';
     $this->men_arm_active='ui-state-default';
     $this->men_col_active='ui-state-default';
     $this->men_ali_active='ui-state-default';
  
     //$this->player = $this->man("player")->findById($_SESSION['xid']);
     //$this->oPlayer = $this->man("player")->findById($_SESSION['xid']);
     $this->page = 'star'; 
     $this->title = 'Sistema Solar '.$this->getGalaxyName();
      
  }
  
  public function initByPlanetId($planetId){
    $this->oColony = $this->getActualColony();
    $this->oRegion = false;
    $this->oPlanet = $this->man("planet")->findById($planetId);
    $this->oStar = $this->man("star")->findById($this->oPlanet->getStarId());
      
     $this->sub_nav_hidden='';
     $this->sub_arm_hidden='hidden';
     $this->sub_col_hidden='hidden';
     $this->sub_ali_hidden='hidden';
     
     $this->men_nav_active='ui-state-active';
     $this->men_arm_active='ui-state-default';
     $this->men_col_active='ui-state-default';
     $this->men_ali_active='ui-state-default';
  
     //$this->player = $this->man("player")->findById($_SESSION['xid']);
     
     $this->page = 'planet'; 
     $this->title = 'Planeta '.$this->getPlanetName();
  }
  
  public function initByRegionId($regionId){
    $this->oColony = $this->getActualColony();
    $this->oRegion = $this->man("region")->findById($regionId);
    $this->oPlanet = $this->man("planet")->findById($this->oRegion->getPlanetId());
    $this->oStar = $this->man("star")->findById($this->oPlanet->getStarId());
      
     $this->sub_nav_hidden='';
     $this->sub_arm_hidden='hidden';
     $this->sub_col_hidden='hidden';
     $this->sub_ali_hidden='hidden';
     
     $this->men_nav_active='ui-state-active';
     $this->men_arm_active='ui-state-default';
     $this->men_col_active='ui-state-default';
     $this->men_ali_active='ui-state-default';
  
     //$this->player = $this->man("player")->findById($_SESSION['xid']);
     $this->page = 'region'; 
     $this->title = 'Region '.$this->getRegionName();
  }
  
  public function actualTutorialStep(){
      //debug::log($this->getPage(),'menustate->actualTutorialStep');
      if($this->getPage()=='region'){
          if(!( isset ($_SESSION['tutorial_step']) ) ){  

            $battle = $this->man("battle")->findActiveByRegionId($this->getRegionId());
            if(!empty($battle)){
              if($battle->getPhase()==_X_BATTLE_PHASE_BATTLE){
                  $_SESSION['tutorial_step'] = _X_PLAYER_BATTLE_TUTORIAL_STEP;
              }
              else{
                  if(!( isset ($_SESSION['tutorial_step']) ) ){
                    $_SESSION['tutorial_step'] = _X_PLAYER_INITIAL_TUTORIAL_STEP;
                  }    
              }
            }
          }
      }
          
      
      //debug::log($_SESSION['tutorial_step'],'menustate->actualTutorialStep');
      return $_SESSION['tutorial_step'];
  }
  
  public function setPostActionTutorialStep(){
      $_SESSION['tutorial_step'] = _X_PLAYER_POSTACTION_TUTORIAL_STEP;
  }  
  public function setBattleTutorialStep(){
      $_SESSION['tutorial_step'] = _X_PLAYER_BATTLE_TUTORIAL_STEP;
  }
  
  public function nextTutorialStep(){
      $tut = $this->actualTutorialStep();
      $_SESSION['tutorial_step'] = min(_X_PLAYER_MAX_TUTORIAL_STEP,$_SESSION['tutorial_step']+1);
      return $_SESSION['tutorial_step'];
  }
  
    public function prevTutorialStep(){
      $tut = $this->actualTutorialStep();
      $_SESSION['tutorial_step'] = max(_X_PLAYER_INITIAL_TUTORIAL_STEP,$_SESSION['tutorial_step']-1);
      return $_SESSION['tutorial_step'];
  }
  
  
  public function initByColonyId($colonyId){
    //Es la unica forma de saben donde esta el jugador
    $this->man("player")->updateDt($this->getPlayer()->getId(),'current_colony_id',$colonyId);  
    //debug::log($this,'meunstate->initByColonyId');  
    $this->oColony = $this->man("colony")->findById($colonyId);
    $this->oRegion = $this->man("region")->findById($this->oColony->getRegionId());
    $this->oPlanet = $this->man("planet")->findById($this->oRegion->getPlanetId());
    $this->oStar = $this->man("star")->findById($this->oPlanet->getStarId());
      
     $this->sub_nav_hidden='';
     $this->sub_arm_hidden='hidden';
     $this->sub_col_hidden='hidden';
     $this->sub_ali_hidden='hidden';
     
     $this->men_nav_active='ui-state-active';
     $this->men_arm_active='ui-state-default';
     $this->men_col_active='ui-state-default';
     $this->men_ali_active='ui-state-default';
  
     //$this->player = $this->man("player")->findById($_SESSION['xid']);
     $this->page = 'colony'; 
     $this->title = 'Colonia '.$this->getColonyName();
     
  } 
    ///////////////////////////////////////////////////////////////////////////////////////
    //GETTERS
    ///////////////////////////////////////////////////////////////////////////////////////
  public function getRegionId(){
      return $this->oRegion->getId();
  }
  public function getColonyId(){
      //debug::log($this->getPlayer(),'menustate->getColonyId');
      return $this->getPlayer()->getCurrentColonyId();
  }  
  public function getPlanetId(){
      return $this->oPlanet->getId();
  }    
  public function getStarId(){
      return $this->oStar->getId();
  }      
  public function getPlayerId(){
      return $this->getPlayer()->getId();
  }        
  
   public function getColonyName(){
       return $this->oColony->getName();
    }
    public function getRegionName(){
       return $this->oRegion->getName();
    }
    public function getPlanetName(){
       return $this->oPlanet->getName();
    } 
    public function getStarName(){
       return $this->oStar->getName();
    }    
    public function getGalaxyName(){
        return _X_GALAXY_NAME;
    }
    public function getPage(){
        return $this->page;
    }
 
    public function getTitle(){
        return $this->title;
        //[TODO] Que caraio?
    }
    
    public function isColonySet(){
        //debug::trace('menustate->isColonySet');
        //debug::log($this,'menustate->isColonySet this');
        /*if(! $this->oColony){
            return false;
        }
        else{
            return true;
        }*/
        if($this->page == 'colony'){
            return true;
        }
        else{
            return false;
        }
        
    }
    public function isRegionSet(){
        if(! $this->oRegion){
            return false;
        }
        else{
            return true;
        }
    }
    public function isPlanetSet(){
        if(! $this->oPlanet){
            return false;
        }
        else{
            return true;
        }
    }
    public function isStarSet(){
        if(! $this->oStar){
            return false;
        }
        else{
            return true;
        }
    }
    public function isGalaxySet(){
      return true;

    }    
    
    
    public function getRes($resId){
        $colony = $this->man("colony")->findById($this->getColonyId());
        return $colony->getParam($resId);
    }

    public function validateStateDt() {
        $dt = new datatransfer();
        $dt->success("[TODO]Validar el estado del menu de alguna manera");
        //$dt->success():
        debug::warning("[TODO]Validar el estado del menu de alguna manera","menustate->validateStateDt");    
        return $dt;
        //Verificar que la colonia le pertenezca al jugador
    }

    public function render() {
        
    }
    
    
    
}
?>