<?php
    require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
/*******************************************************
 * battleman: Gestion de Batallas 
 ******************************************************/
    class planetview extends view 
    {
	private $ext_planet_id;
        //private $ext_player_id;

        private $planet;
        private $star;
        private $db;
        private $regions;
        private $teams;
        private $colonies;

    
		
        public function __construct($planet_id)
        {
            $this->ext_planet_id = $planet_id;
            //$this->ext_player_id = $player_id;
            $this->db = db::singleton();
            //debug::log($this,'planetstate->constructor');
        }

    
    public function getRegions($forced = false){
        if( (empty($this->regions)) || $forced ){
            $this->regions = new regions();
            $this->regions->initByPlanetId($this->getPlanetId());
            if($this->regions->size()<1){
                debug::warning('Las regions del planeta['.$this->getPlanetId().'] no pudo cargarse','planetstate->getRegions');  
            }
            if($forced){
                //OJO rellenamos de dato el manager
                $this->regions->refillMan();
            }
            //Quitar los mapas

            
        }
        return $this->regions;
    }
    
        public function getArmies(){
        if( empty($this->armies) ){      
            $oArmies = new groupedarmies();
            $oArmies->initAliveByPlanetIdOrderByRegionId($this->getPlanetId());
            $this->armies = $oArmies;
            
            //$this->armies =  $this->man("army")->getArmiesByPlanetIdOrderByRegionId($this->getPlanetId());
            /*if(!count($this->armies)<1){
                
                foreach($this->armies as $regionId => $armiesInRegion ){//Es correcto el regionId, la funcion de armies->initByPlanetId.... devuelve un arreglo de arreglo
                    foreach($armiesInRegion as $id => $army){
                        //Añadir nuestras cosillas
                        $battleutil = new battleutil();
                        $this->armies[$regionId][$id]['allegiance'] = $battleutil->getAllegiance($army['id'],"army");
                    }
                }
                //debug::warning('Las regions del planeta['.$this->getPlanetId().'] no pudo cargarse','planetstate->getRegions');  
            }*/
        }
        return $this->armies;
    }
    
     public function getResources(){
            $colonies = new groupedregionsresources();
            $colonies->initActiveByPlanetIdGroupedByRegionId($this->getPlanetId());
            return $colonies;
    }
    
    public function getColonies(){
            $colonies = new groupedcolonies();
            $colonies->initActiveByPlanetIdGroupedByRegionId($this->getPlanetId());
            return $colonies;
    }
    
     public function getBiomes(){
            $biomes = new groupedregionsbiomes();
            $biomes->initActiveByPlanetIdGroupedByRegionId($this->getPlanetId());
            return $biomes;
    }
    
    public function getPortals(){
            $portals = new groupedregionsportals();
            $portals->initActiveByPlanetIdGroupedByRegionId($this->getPlanetId());
            return $portals;
    } 
    
    public function getBuildings(){
            $buildings = new groupedregionsbuildings();
            $buildings->initActiveByPlanetIdGroupedByRegionId($this->getPlanetId());
            return $buildings;
    }     
    
    
    public function getTeams($forced = false){
        if( (empty($this->teams)) || $forced ){
            $this->teams = new teams();
            $this->teams->initByPlanetIdByPlayerIdOrderByRegion($this->getPlanetId(),$this->getPlayerId());
            if($this->teams->size()<1){
                //debug::warning('Las regions del planeta['.$this->getPlanetId().'] no pudo cargarse','planetstate->getRegions');  
            }
        }
        return $this->teams;
    }
    
    
    public function getPlayerId(){
      return $this->getPlayer()->getId();// ext_player_id;
    }
    
    
    /********************************************
    * getPlanet: Obtener Planeta 
    ********************************************/
    public function getRegionIndex(){

    }


    public function getPlanet(){
      if(empty($this->planet)){
        $this->planet = $this->man("planet")->findById($this->getPlanetId());
        //$this->planet ->initById($this->getPlanetId());
        if(empty($this->planet)){
            debug::error('El planeta no pudo cargarse','planetstate->getPlanet');  
        }
      }
      return $this->planet;
    }
    public function getPlanetId(){
      return $this->ext_planet_id;
    }
    
    
    public function getStar(){
      if(empty($this->star)){
       $id = $this->getPlanetId(); 
       $this->star = $this->man("star")->findByPlanet($id); 
      }
      //debug::log($this->star,'planetstate->getStar');
      return $this->star;
    }
    
public function render()//Obtener el Acceso a la Base de Datos
{ 
    if($this->getPlanet()->exist())
    {
        $regions = $this->getRegions();
        if($regions->size()>0){

            $dom='<div class="planet_map" id="PlanetMap'.$this->getPlanetId().'" style="background-image:url(img/region/region'.$this->getPlanet()->getColor().'.jpg);width: 600px;">';
            $region_raw = $regions->getRaw();
            $sides = $regions->getSides();
            //debug::log($sides,'planetstate->render1');
            //Calcular la anchura
            $width = ceil( _X_ITF_PLANET_WIDTH/($sides['y']+2) ); //Mas dos de los bordes
            $widthClass='side'.$width;
            for($y=0;$y<$sides['y'];$y++)
            {
                //debug::log('planetstate->render2');
                //$dom=$dom."<tr id='y".$y."' class='".$widthClass."'>";
                for($x=0;$x<$sides['x'];$x++)
                {
                    //debug::log('planetstate->render3');
                    $data='';
                    $col = '<div class="colonies"></div>';
                    $res = '<div class="resources"></div>';
                    $index=($y*$sides['y'])+($x+1);
                    $region_id = $this->findRegionByPosition($region_raw,$index);
                    if($region_id){
                           
                        $dom=$dom."<div x=".$x." y=".$y." index=".$index." id='Region".$region_id."' class='x".$x." y".$y." region index".$index." ".$widthClass."' >".$data.$col.$res.'</div>';
                    }
                    else{
                        $dom=$dom."<div class='noregion ".$widthClass."' >".$data.$col.$res.'</div>';
                    }
                }
                //$dom=$dom."</tr>";
            }
            echo $dom,'</div>';
            }
        else{
            $dom='<table class="battle_map"><tr><td>El Planeta no tiene regiones</td></tr></table>';
            echo $dom;
        }
    }

    else{
        $dom='<table class="battle_map"><tr><td>Planeta No Accesible</td></tr></table>';
        echo $dom;
    }

}    
    
    
public function render_old()//Obtener el Acceso a la Base de Datos
{ 
    if($this->getPlanet()->exist())
    {
        $regions = $this->getRegions();
        if($regions->size()>0){

            $dom='<table class="planet_map" id="PlanetMap'.$this->getPlanetId().'" style="background-image:url(img/region/region'.$this->getPlanet()->getColor().'.jpg)">';
            $region_raw = $regions->getRaw();
            $sides = $regions->getSides();
            //debug::log($sides,'planetstate->render1');
            //Calcular la anchura
            $width = ceil( _X_ITF_PLANET_WIDTH/$sides['y'] );
            $widthClass='side'.$width;
            for($y=0;$y<$sides['y'];$y++)
            {
                //debug::log('planetstate->render2');
                $dom=$dom."<tr id='y".$y."' class='".$widthClass."'>";
                for($x=0;$x<$sides['x'];$x++)
                {
                    //debug::log('planetstate->render3');
                    $data='';
                    $col = '<div class="colonies"></div>';
                    $res = '<div class="resources"></div>';
                    $index=($y*$sides['y'])+($x+1);
                    $region_id = $this->findRegionByPosition($region_raw,$index);
                    if($region_id){
                           
                        $dom=$dom."<td x=".$x." y=".$y." index=".$index." id='Region".$region_id."' class='x".$x." y".$y." region index".$index." ".$widthClass."' >".$data.$col.$res.'</td>';
                    }
                    else{
                    $dom=$dom."<td class='noregion ".$widthClass."' >".$data.$col.$res.'</td>';
                    }
                }
                $dom=$dom."</tr>";
            }
            echo $dom,'</table>';
            }
        else{
            $dom='<table class="battle_map"><tr><td>El Planeta no tiene regiones</td></tr></table>';
            echo $dom;
        }
    }

    else{
        $dom='<table class="battle_map"><tr><td>Planeta No Accesible</td></tr></table>';
        echo $dom;
    }

}

public function deleteTeamDt($team_id){
    $oTeam = $this->man("team")->findById($team_id);
    $oPlayer = $this->man("player")->findById($this->getPlayerId());
    $dt = new datatransfer();
    $dt->success('Temporal deleteTeamDt');
    $battleutil = new battleutil();
    //3) Validar si team es del mismo player
    
   if(!$oTeam->exist()){
       $dt->error('Este equipo no existe');
   } 
   
   if( $dt->isValid() && !( $battleutil->colonyExistInRegionIdByPlayerId($oTeam->getRegionId(),$this->getPlayerId() )) ){
       $dt->error('Para eliminar al equipo necesita que la region tenga una colonia que le pertenezca');
   }
   
   
   if( $dt->isValid() && ($oTeam->getPlayerId() != $oPlayer->getId() )  ){
       //debug::warning('planetState el equipo no me pertenece');
       $dt->error('Este equipo no te pertenece');
   }
   
   
   if( $dt->isValid()){
       $armies = new armies();
       $armies->initByTeamId($team_id);
       if($armies->size()>0){
        $armies->setTeam('null');
       }
       $dt = $this->man("team")->deleteDt($team_id);
       
       if( !$dt->isValid()){
        $dt->error('No existe el team');
        }
   }

   if( $dt->isValid()){
       $dt->success('Se elimino '.$oTeam->getName());
   }
   /*else{
       debug::log($dt,'No existe el team???');
       $dt->error('No existe el team');
   }*/
   return $dt;
}


public function createNewTeamDt($regionId){
    $dt = new datatransfer();
    $dt->success('planetState->createNewTeamDt temporal success');
    $oPlayer = $this->man("player")->findById($this->getPlayerId());
    $oRegion = $this->man("region")->findById($regionId);
    $battleutil = new battleutil();
    
    if(!$battleutil->canPlayerIdCreateTeamInRegionId( $this->getPlayerId(), $regionId) ){
        $dt->error('No puede crear mas equipos en esta region. Su limite maximo es '.$oPlayer->maxTeamsInRegionId($regionId).' equipos en la region '.$oRegion->getName() );
    }
    
    if( $dt->isValid() && !( $battleutil->colonyExistInRegionIdByPlayerId($regionId,$this->getPlayerId() )) ){
       $dt->error('Tiene que crear el equipo dentro de una colonia que le pertenezca');
    }
    
    if( $dt->isValid() ){
        
        $entityutil = new entityutil();
        $dt = $entityutil->createNextTeamDt($this->getPlayerId(), $regionId);
        if( !$dt->isValid() ){
            $dt->error('Hubo un error en la creacion del Equipo, intente de nuevo');
        }
        else{
            $dt->success('Tienes un nuevo equipo en '.$oRegion->getName());
        }
        
    }
    
    //debug::log($dt,'planetState createNewTeamDt');
    return $dt;
    
}



    public function transferArmyTeam($army_id,$team_id){
       $tu = new transferutil();
        
        $dt = new datatransfer();
       $oArmy = $this->man("army")->findById($army_id);

       
       $oTeam = $this->man("team")->findById($team_id);
       $oRegion = $this->man("region")->findById($oArmy->getRegionId());
       $oPlayer = $this->man("player")->findById($this->getPlayerId());
       $dt->success('temporal');
       //1) Validar si es del mismo player
       if($oArmy->getPlayerId() != $oPlayer->getId() ){
           $dt->error('Este Army no te pertenece');
       }
       
       if($dt->isValid() &&  ( $team_id == 0) ){
           $battleutil = new battleutil();
           if($battleutil->colonyExistInRegionIdByPlayerId($oRegion->getId(), $this->getPlayerId()) ){
               $tu->transferArmyToTeam($oArmy, $oTeam);
               
               //deleteTeamDt($oOldTeam->getId());
               $dt->success('El army regreso al equipo regional');
           }
           else{
               $dt->error('Necesitas una colonia en la region '.$oRegion->getName().' para transferir la unidad' );
           }
           
           
       }
       else{
           //2) Validar si army ta en la misma region que team
           if( $dt->isValid() && ($oArmy->getRegionId() != $oTeam->getRegionId())  ){
               debug::warning($oTeam,'planetState el equipo['.$oTeam->getRegionId().'] y el army['.$oArmy->getRegionId().'] no tan en la misma region :S');
               $dt->error('El equipo y el army no estan en la misma region');
           }

           //3) Validar si team es del mismo player
           if( $dt->isValid() && ($oTeam->getPlayerId() != $oPlayer->getId() )  ){
               //debug::warning('planetState el equipo no me pertenece');
               $dt->error('Este equipo no te pertenece');
           }
           if( $dt->isValid()){
               //debug::log('planetState se esta cambiando el estado 2');
               //$oArmy->setTeam($team_id);
               $tu->transferArmyToTeam($oArmy, $oTeam);
               $dt->success('El army se unio a '.$oTeam->getName() );
               
           }
       }
       //debug::log('planetState se esta cambiando el estado 3');
       return $dt;
    }

    public function prepareState(){
        //Actualizar Estado del Planeta Primero;
        //0) Preparar la variable a usar _xeno
        $_xeno = array();
        //1) Obtener info del planeta
        $_xeno['teams'] = $this->getTeams(true)->getRaw();
        /*$_xeno['planet']['name'] = $this->getPlanet()->getParam('name');
        $_xeno['planet']['desc'] = $this->getPlanet()->getParam('desc');
        $_xeno['planet']['id'] = $this->getPlanetId();*/
        $_xeno['planet'] = $this->getPlanet()->getState();
        //2) Obtener info de las habilidades del jugador
        $_xeno['player']['username'] = $this->getPlayer()->getParam('username');
        $_xeno['player']['abilities'] = $this->getPlayer()->getAbilities();
        $_xeno['player']['id'] = $this->getPlayerId();
        //3) Obtener info de la region
        $_xeno['regions'] = $this->getRegions(true)->getState();
        //4)Obtener lista de los recursos
        $_xeno['resources'] = $this->getResources()->getState();

        //4.2) Obtener el Batallas Enviadas
        $_xeno['battles']['sended'] = $this->getArmiesByPlanetGoingToRegion()->getState();

        //$players_sended = new players();
        //$players_raw = $players_sended->initByArmiesMovingByPlanet($this->getPlanet()->getId());
        //$_xeno['battles']['sended']['players'] = $players_raw;

        //4.4 Obtener Batallas ACtivas
        $active_battles = new battles();
        $active = $active_battles->initActiveByPlanet($this->getPlanetId());
        $_xeno['battles']['active'] = $active;


        //$region_raw = $this->getRegions()->getRaw();
        $sides = $this->getRegions()->getSides();
        $_xeno['planet']['size']= $sides;


        //5)[TODO] Obtener Info de las Construcciones ?? Todavia no
        //6)[TODO] Obtener info de lo clickeado???

        //7)Obtener Ejercitos
        $_xeno['armies'] = $this->getArmies()->getState();  //$this->man("army")->getArmiesByPlanetIdOrderByRegionId($this->getPlanetId());
        //8) Obtener Jugadores en el Planeta
        //$players = $this->playerman->initByPlanetRegions($this->getPlanet()->getId());
        
        //9) Obtener colonias y cosas de la region
        $_xeno['colonies'] = $this->getColonies()->getState();
        $_xeno['biomes'] = $this->getBiomes()->getState();
        $_xeno['portals'] = $this->getPortals()->getState();        
        $_xeno['buildings'] = $this->getBuildings()->getState();         
        
        $players = new players();
        $players->initByPlanetRegions($this->getPlanetId());
        $_xeno['players']  =  $players->getState();
        return $_xeno;
    }

    public function attackRegion($origin_region_id,$target_region_id,$team_id){
        $oPlayer = $this->getPlayer();
        $oPlanet = $this->getPlanet();
        $dt = new datatransfer();
        $dt->success('planetstate->atackRegion temporal success'); 
        
        //////////////////////////////////////////////////////////////////
        
        //1) Validamos que origin exista
        
        if( $dt->ok() ){
          $oOriginRegion = $this->man("region")->findById($origin_region_id);
          if(!$oOriginRegion->exist() ){
              $dt->error('La region de origen del ataque no existe');
          }
        }
        
        //2) Validamos que target exista
        if( $dt->ok() ){//Si el primero ta mal tonces dejar
          $oTargetRegion = $this->man("region")->findById($target_region_id);
          if(!$oTargetRegion->exist() ){
              $dt->error('La region objetivo del ataque no existe');
          }
        }
        
        //3) Validamos que team exista
         if( $dt->ok() ){//Si el primero ta mal tonces dejar
          $oTeam = $this->man("team")->findById($team_id);
          if(!$oTeam->exist() ){
              $dt->error('El equipo de ataque no existe');
          }
        }       
        //4) Validamos que origin este en planeta
         if( ( $dt->ok()) && ($oOriginRegion->getPlanetId() != $oPlanet->getId() ) ){
            $dt->error('La region de origen del ataque no esta en el planeta '.$oPlanet->getName() );  
         }
        //5) Validamos que target este en planeta
         if( ( $dt->ok()) && ($oTargetRegion->getPlanetId() != $oPlanet->getId() ) ){
            $dt->error('La region objetivo del ataque no esta en el planeta '.$oPlanet->getName() );  
         }
        //6) Validamos que todo los armies no esten vacios
         $oArmies = new armies();
        if( $dt->ok() ){
            
            $oArmies->initByTeamId($team_id);
            if( $oArmies->size() ==0 ){
                $dt->error('El equipo no contiene unidades, ingrese al menos una unidad al equipo ');  
            }
        }
        //7) Validamos que los armies esten todos en la region de origen
        if( ($dt->ok()) &&( !( $oArmies->getUnique('region_id') )  ) ){
            $dt->error('No todas las unidades del equipo estan en la region de origen');  
        }
        
        
        //8) Validamos que el equipo sea de tu jugador
        if( ( $dt->ok()) && ($oTeam->getPlayerId() != $oPlayer->getId() ) ){
           $dt->error('El equipo no te pertenecen'); 
        }
        
        
        
        //9)Validar que armies a mover sean del jugador
        if( ( $dt->ok()) && !($oArmies->areAllFromPlayer($oPlayer->getId() )) ){//Si el primero ta mal tonces dejar
          //debug::warning('Las unidades no son de un solo jugador['.$player_id.'] en la region ['.$origin_region_id.']','ajax^Planet->action');
          $dt->error('Las unidades del equipo no te pertenecen');
        }
        
        //10) Validar que no haya una batalla en la region origen de origen
        if( $dt->ok()  ){//Si el primero ta mal tonces dejar
            
            $battle = $this->man("battle")->findByRegionId($origin_region_id);
            //debug::log($battle);
            if($battle->isActive()){
                //debug::log($battle,"planetstate->attackRegion");
                //debug::warning('Hay una batalla en la region de origen['.$origin_region_id.']','ajaxPlanet->action');
                $dt->error('El Team no puede salir de la region de origen hasta que haya terminado la batalla');
            }
        }

        //11) Validar que sea una region adyacente
        if( $dt->ok() ){
          $dt = $this->areRegionAdyacentDT($origin_region_id,$target_region_id);
          //debug::error('Las regiones O['.$origin_region_id.'] y T['.$target_region_id.'] no son adyacentes','ajaxRegion->action');
          //$dt = $dt_adjacentRegions;
        }

        // 12) Las unidades no pueden atacar si estan en movimiento
        
        if($dt->ok()){
        //if(!$armies->areInBattle($origin_region_id)){//Si no estan en batalla
          $oArmies->sendArmiesToRegion($origin_region_id,$target_region_id,$this->getPlanetId() );
          $oTeam->setRegionId(_X_TEAM_TRAVEL_REGION);
          $dt->success('Se Mando a Atacar');
        }
        return $dt;
    }
    
    /**********************************************************
     *Funciones Busqueda
    * *******************************************************/
    private function findRegionByPosition($regions,$position){
      foreach($regions as $key => $region){
        if($region['planet_position']==$position){
          return $key;
        }
      }
    return false;
    }   
		
   /**********************************************************
   *Funciones MIGRADAS DE GAMESTATE
   * *******************************************************/   
   public function updateState(){
     $this->db->begin();
     $this->setIfOldDtIsOkWithCallback("updatePlanetTravelDt");
     $this->setIfOldDtIsOkWithCallback("updateColoniesBuildStateDt");         
     
     if($this->isOk()){
        $this->db->commit(); 
        $dt = new datatransfer();
        $dt->success('Bienvenido al Planeta '.$this->getPlanet()->getName());
        $this->setIfOldDtIsOk($dt);
     }
     else{
         $this->db->rollback(); 
     }
     return $this->getDt();
  }  
  
  
   protected function updatePlanetTravelDt(){
   //debug::trace("Llamando");
   $regions = $this->getRegions()->getRaw();
    $battleutil = new battleutil();
    $dt = new datatransfer();
    $dt->success('planetstate->udpatePlanetArmiesTravelDt temporalDT');
    foreach($regions as $id => $region){
        $dt = $battleutil->updateRegionMovementDt($region['id']);
        if(!$dt->ok()){
            break;
        }
    }
    return $dt;
  }    
  
  protected function updateColoniesBuildStateDt(){
      $sql = "UPDATE colonies AS c SET build_state = '"._X_COLONY_BUILD_STATE_DONE."' WHERE c.status = '"._X_ACTIVE."' AND c.region_id IN (SELECT r.id FROM regions AS r WHERE r.planet_id = ".$this->getPlanet()->getId().")".
      " AND build_state = '"._X_COLONY_BUILD_STATE_BUILDING."' AND time_to_sec2(c.build_end,now()) <= 0";
      $dt = $this->db->updateEntitiesDt($sql,"colony");
      return $dt;       
  }
  

    public function getArmiesByPlanetGoingToRegion(){
        $planet_id = $this->getPlanetId();
        $sql = "SELECT time_to_sec2(travel.eta,now())as seconds,travel.from_region as from_region,travel.to_region as to_region,armies.id,armies.size,armies.creature_id,armies.position,players.id as player_id ,players.username,armies.region_state FROM travel
        INNER JOIN armies ON (travel.armies_id= armies.id)
        INNER JOIN players ON (armies.player_id = players.id)
        WHERE travel.state = '"._X_TRAVEL_STATE_ACTIVE."' AND travel.to_planet = ".$planet_id." ORDER BY armies.player_id,armies.id";

        //debug::info($sql,'planetstate->initByPlanetGoingToRegion');
        $armies_raw = $this->db->vectorize($sql);
        $armies = new armies();
        $armies->setRaw($armies_raw);
      return $armies;

  }

    ///////////////////////////////////////////////////////////////////////////////////////
  //UTILITARIOS
  ///////////////////////////////////////////////////////////////////////////////////////
  public function areRegionAdyacentDT($origin_id,$target_id){
      $dt = new datatransfer();
      $origin_region = $this->man("region")->findById($origin_id);
      $target_region = $this->man("region")->findById($target_id);

      $sides =  $this->getRegions()->getSides();
      $origenCoord = $origin_region->getCoord($sides['y']);
      $targetCoord = $target_region->getCoord($sides['y']);
      //debug::log($origenCoord,'planetstate->areRegionAdyacentDT->origin');
      //debug::log($targetCoord,'planetstate->areRegionAdyacentDT->target');
      if($origenCoord['x'] == $targetCoord['x']){
        //debug::log('Estan en el mismo x['.$origenCoord['x'].']','planetstate->areRegionAdyacentDT->samex');
        $absolute = abs($origenCoord['y'] - $targetCoord['y']);
        if($absolute==1){
            //debug::log('Estan al lado','planetstate->areRegionAdyacentDT->adaycenty');
            $dt->success('Estan al lado');
        }
        else{
            //debug::log('No Estan al lado','planetstate->areRegionAdyacentDT->adaycenty');
            $dt->error('Las regiones no son adyacentes');
        }
      }
      else if($origenCoord['y'] == $targetCoord['y']){
        //debug::log('Estan en el mismo y['.$origenCoord['y'].']','planetstate->areRegionAdyacentDT->samey');
        $absolute = abs($origenCoord['x'] - $targetCoord['x']);
        if($absolute==1){
            $dt->success('Estan al lado');
            //debug::log('Estan al lado','planetstate->areRegionAdyacentDT->adaycentx');
        }
        else{
            debug::log('No Estan al lado','planetstate->areRegionAdyacentDT->adaycentx');
            $dt->error('Las regiones no son adyacentes');
        }
      }
      else{
        debug::warning('No coinciden origen x['.$origenCoord['x'].']y['.$origenCoord['y'].'] con target x['.$targetCoord['x'].']y['.$targetCoord['y'].'] ','planetstate->areRegionAdyacentDT->nonex');
        $dt->error('Las regiones no son adyacentes');
      }
      return $dt;
  }

    public function validateStateDt() {
        $dt = new datatransfer();
        $dt->success("[TODO]Validar el estado del planeta de alguna manera");
        //$dt->success():
        debug::warning("[TODO]Validar el estado del planeta de alguna manera","planetstate->validateStateDt");    
        return $dt;
        //Verificar que la colonia le pertenezca al jugador
    }

    public function getTitle() {
       return "Planeta ".$this->getPlanet()->getName();
    }
  
  /*
    public function areRegionInSamePlanetDT($regionA_Id,$regionB_Id){
        $dt = new datatransfer();
        $origin_region = $this->regionman->findById($regionA_Id);
        $target_region = $this->regionman->findById($regionB_Id);

        if($origin_region->getParam('planet_id')==$target_region->getParam('planet_id')){
            $dt->success('Las regiones se encuentran en el mismo planeta['.$target_region->getParam('planet_id').']');

        }
        else {
            $dt->error('Las regiones estan en diferentes planetas planetaA['.$target_region->getParam('planet_id').'] y planetaA['.$origin_region->getParam('planet_id').'] ');
        }
        return $dt;

    }*/
    
    
}//planetstate
?>
