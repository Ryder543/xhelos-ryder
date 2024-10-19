<?php
  require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
   
/******************************************************************
 * entityutil: Utilidad para creacion y mantenimiento de entidades
 ******************************************************************/
  class entityutil  extends util
  {
    ///////////////////////////////////////////////////////////////////////////////////////
    //PROPIEDADES
    ///////////////////////////////////////////////////////////////////////////////////////    
 
    ///////////////////////////////////////////////////////////////////////////////////////
    //CONSTRUCTOR
    ///////////////////////////////////////////////////////////////////////////////////////    
    /********************************************
    * Constructor: De regionState
     * Params: 
     * $region_id: ID de la region que quiere mantener el estado
     * $player_id: ID del jugador actual de la region
     * EXTRA: Obtener la lista de arg $args = func_get_args(); 
    ********************************************/  
    public function __construct(){
        parent::__construct();
    }
    
    public function createRandomStarDt($name){
        
        //buscar si ya existe el nombre
        $sql = "SELECT * FROM stars WHERE name = '".$name."'";
        $exist = $this->db->vectorize($sql);
        $dt = new datatransfer();
        if(count($exist)>0){
            debug::warning('Ya existe una estrella con ese nombre, escriba otro');
            $dt->invalidWarning('entityutil->createRandomStarDt Ya existe una estrella con ese nombre, escriba otro');            
        }
        else{
            $data = array();
            $data['id']= $this->db->nextId('stars');
            $data['name']=$name;
            $colors = star::colorList();
            $data['color']=  $colors[ $this->random($colors) ];
            $data['state']= 'A';

            $freePositions = $this->galaxyFreePositions();
            $randomY = array_rand ($freePositions);
            $randomX = array_rand ($freePositions[$randomY]);

            $data['axis_x']=$randomX;
            $data['axis_y']=$randomY;
            $data['desc']=$name . ' es un Sistema Solar de clase '. $data['color'];
            $dt = $this->man('star')->createDt($data);
            
            //debug::log($dt,'entityutil->createRandomStar dt');
            //debug::log($data,'entityutil->createRandomStar data');
            
        }
        return $dt;
    }
    
    public function createRandomPlanetsDt($quantity,$starId){
        $minOrbit = floor($quantity/2) + 1;
        if($minOrbit>$maxOrbit){
            $maxOrbit = $minOrbit;
        }
        $maxOrbit = rand($minOrbit,$quantity);
        //debug::log($maxOrbit,'planet minorbit');
        $orderList = array_fill(1, $maxOrbit, 1);//Lista de Orbitas del Planeta
        $namePrefixesList =  $this->obtainPlanetsNamesFromStar($starId);
        
        $dt = new datatransfer();
        $dt->success('entityutil->createRandomPlanetsDt La creacion de '.$quantity. ' planetas es correcto');
        //debug::log($orderList,'planet orderList');
        for($n = 0 ; $n < $quantity ; $n++){
            $orbit = rand(1,$maxOrbit);
            //debug::log($orbit,'planet orbit');
            $order = $orderList[$orbit];
            $dt = $this->createRandomPlanetDt($starId,$orbit,$order,$namePrefixesList);
            $orderList[$orbit]++; //Subimos el numero de orden
            $data = $dt->getData();
            $namePrefixesList[] = $data['namePrefix'];
            //debug::log($namePrefixesList,'entityutil createRandomPlanetDt namePrefixesList');
            if( !$dt->isValid() ){
                //debug::log($dt,'Dejo de ser valido');
                break;
            }
            
        }
        return $dt;
    }
    public function obtainPlanetsNamesFromStar($starId){
        $oStar = $this->man("star")->findById($starId);
        $planets = new planets();
        $planets->initByStar($starId);
        $namePrefixesList = array();
        foreach($planets as $i => $oPlanet){
            $name = str_replace($oStar->getName(),"",$oPlanet->getName());
            $namePrefixesList[] =  trim($name);
        }
        return $namePrefixesList;
    }
    
    public function createRandomPlanetDt($starId,$orbit=false,$order=false,$namePrefixesList=false){
        $star = $this->man('star')->findById($starId);
        $planetNames = planet::planetNames();
        //debug::log($planetNames,'Antes');
        
        if(!$orbit){
            $orbit = rand(1,10);
        }
        
        if(!$order){
            $planets = new planets();
            $planets->initByStar($starId);
            $planetsInOrbit = $planets->getPlanetsInOrbit($orbit);
            $order = count($planetsInOrbit) +1 ;
        }
        
        if(!$namePrefixesList){
                //$namePrefixesList[] =  $this->obtainPlanetsNamesFromStar($starId);
                $namePrefixesList[] =  $this->obtainPlanetsNamesFromStar($starId);
        }
        
        $arrayPlanetNames = array_diff($planetNames,$namePrefixesList);
        //debug::log($planetNames,'Despues');
        $dt = new datatransfer();
        if(count($arrayPlanetNames) == 0 ){
            $dt->error('createRandomPlanetDt Se acabaron los nombres de planeta');
            debug::error($namePrefixesList,'createRandomPlanetDt Se acabaron los nombres de planeta');
        }
        else{
            $planetSufijo = $arrayPlanetNames[array_rand ($arrayPlanetNames)];       
            $planetName =$planetSufijo.' '.$star->getName();
            $planetColorsId = planet::planetColorsId();
            $planetColorId = $planetColorsId[array_rand ($planetColorsId)];
            $planetSizesId = planet::planetSizesId();
            $planetSizeId = $planetSizesId[array_rand ($planetSizesId)];
            $planetSize = planet::planetSize($planetSizeId);
            $position = entityutil::orderMasculine($order-1);
            $orbita = entityutil::orderFemenine($orbit-1);

            $planetVariationsId = planet::planetVariationId();
            $planetVariationId = $planetVariationsId[array_rand ($planetVariationsId)];

            $data = array();
            $data["id"] = $this->db->nextId('planets');
            $data["name"] = $planetName;
            $data["state"] = _X_ACTIVE;
            $data["desc"] = $planetName.' es un planeta de tamaño '.$planetSize.' y de clase '.$planetColorId.
            '. Es el '.$position. ' planeta de la '.$orbita.' orbita';
            $data["star_id"] = $starId;
            $data["orbit"] = $orbit;
            $data["size"] = $planetSizeId;
            $data["color"] = $planetColorId;
            $data["variation"] = $planetVariationId;
            $data["order"] = $order;
            $data["namePrefix"] = $planetSufijo;
            //debug::log($data,'createRandomPlanet');
            $dt = $this->man('planet')->createDt($data);
            if($dt->isValid()){
                $dt->setData($data);
            }
            //debug::log($dt,'entityutil createRandomPlanet Dt');
        }
        return $dt;
    }
    
    public function createRandomRegionsDt($planetId,$total){
        
        //Localizacion de la region extra home region
        $homeOrder = rand(1,$total);
        $homeRegionId = 0;
        $dt = new datatransfer();
        $dt->success('entityutil->createRandomPlanetsDt La creacion de '.$total. ' regiones en el planeta['.$planetId.'] es correcto');
        $createdRegionList = array();
        $regions = new regions();
        $regions-> initByPlanetId($planetId);
        $size = $regions->size();
        for($i= $size+1;$i<$total+$size+1;$i++ ){
            
            if($i == $homeOrder ){
                $dt = $this->createInitialRegionDt($planetId,$i);
                if( !$dt->isValid() ){
                    //debug::log($dt,'Dejo de ser valido');
                    break;
                }
                else{
                    $createdRegionList[] = $dt->getData();
                    $homeRegionId = $dt->getData();
                }
            }
            else{
                $dt = $this->createRandomRegionDt($planetId,$i);
                if( !$dt->isValid() ){
                    //debug::log($dt,'Dejo de ser valido');
                    break;
                }
                else{
                    $createdRegionList[] = $dt->getData();
                }
            }
            
            
        }
        
        $dt->setData($homeRegionId);
        return $dt;
    }
    
    
    public function createInitialRegionDt($planetId,$order){
        $planet = $this->man('planet')->findById($planetId);
        $bob = new perlinnoise(rand(1,1000));
        
        $region_fem = entityutil::orderFemenine($order-1);
        $data['id'] = $this->db->nextId('regions');
        $data['name'] = $planet->getName() . ' ' . $order;
        $data['x'] = _X_REGION_INITIAL_HOME_X_SIZE;
        $data['y'] = _X_REGION_INITIAL_HOME_Y_SIZE;
        $data['map'] = db::toPersistenceArray( $this->createHomeMapWithPersinNoise($data['x'],$data['y'], $bob));
        //$data['map'] = db::toPersistenceArray( $this->createRandomMap($data['x'],$data['y']),$bob  );
        $data['state'] = 'A';
        $data['planet_id'] = $planetId;
        $data['desc'] = "La ".$region_fem." region del planeta " .$planet->getName()." Su tamaño es de ".$data['x']." x ".$data['y']." cuadrados de movimiento"  ;
        $data['player_owner_id'] = 0;
        $data['planet_position'] = $order;
        
        $dt = $this->man('region')->createDt($data);
        $dt->setData($data['id']);
        //debug::log($data,'createRandomRegionDt');
        return $dt;
        
        
    }
    
    
    public function createRandomRegionDt($planetId,$order){
        $planet = $this->man('planet')->findById($planetId);
        $bob = new perlinnoise(rand(1,1000));
        
        $region_fem = entityutil::orderFemenine($order-1);
        $data['id'] = $this->db->nextId('regions');
        $data['name'] = $planet->getName() . ' ' . $order;
        $data['x'] = 22;
        $data['y'] = 22;
        $data['map'] = db::toPersistenceArray( $this->createRandomMapWithPersinNoise($data['x'],$data['y'], $bob));
        //$data['map'] = db::toPersistenceArray( $this->createRandomMap($data['x'],$data['y']),$bob  );
        $data['state'] = 'A';
        $data['planet_id'] = $planetId;
        $data['desc'] = "La ".$region_fem." region del planeta " .$planet->getName()." Su tamaño es de ".$data['x']." x ".$data['y']." cuadrados de movimiento"  ;
        $data['player_owner_id'] = 0;
        $data['planet_position'] = $order;
        
        $dt = $this->man('region')->createDt($data);
        $dt->setData($data['id']);
        //debug::log($data,'createRandomRegionDt');
        return $dt;
        
        
    }
    /*
     * createRandomPrimaryColony: Crea una colonia primaria para el jugador objetivo en la reion decidida
     */
    public function createRandomPrimaryColonydt($region_id,$playerId){
        $oRegion = $this->man("region")->findById($region_id);
        $data = array();
        //calculamos regiones del mapa libres basandonos desde el centro
        $maputil = $oRegion->getCachedMapUtil();
        $index = $maputil->getFirstNonObstacleTile(_X_MAPUTIL_CENTERMAP);
        $colonyId = $this->db->nextId('colonies');
        $region_x = $maputil->getXByIndex($index);
        $region_y = $maputil->getyByIndex($index);
        $data["id"] = $colonyId;
        $data["name"] =$oRegion->getName();
        $data['region_id'] = $region_id;
        $data['region_x'] =$region_x;
        $data['region_y'] =$region_y;
        $data['type'] = _X_COLONY_TYPE_PRIMARY;
        $data['map'] = _X_COLONY_INITIAL_MAP;
        $data['level'] = _X_COLONY_INITIAL_LEVEL;
        $data['owner_id']= $playerId;
        $data['size_x']= _X_COLONY_SIZE_X_INITIAL_VALUE;
        $data['size_y']=_X_COLONY_SIZE_Y_INITIAL_VALUE;
        
        $data['state']= _X_ACTIVE;
        
        
        $dt = $this->man("colony")->createDt($data);
        if(!$dt->isValid()){
            return $dt;
        }
        
        //Seteamos esta colonia como la actual al jugador
        $oPlayer = $this->man('player')->findById($playerId);
        $oPlayer = new player();
        $dt =  $this->man("player")->updateDt($playerId, "current_colony_id", $colonyId);    
        //debug::log($dt,'entityutil->createRandomPrimaryColony dt');
        $dt->setData($data);
        return $dt;
    }
 
    
    public function deleteColoniesByPlayerIdDt($playerId){
        $sql="delete from internal_constructions where colony_id IN (SELECT id FROM colonies WHERE owner_id = ".$playerId.")" ;
        $dt = $this->db->deleteDt($sql);
        $continue = true;
    //Eliminar 
        if($dt->isValid()){
            $sql="DELETE from colonies WHERE owner_id = ".$playerId;
            $dt = $this->db->deleteDt($sql);
        }
        else{
            debug::warning($dt,'entityutil->deleteColoniesByPlayerIdDt Error Eliminando internal constructions de colonias con player['.$playerId.']');
        }
        return $dt;
    }
    
    public function removePlayer($xid){
        $dt = new datatransfer();
        
        $removedPlayer = $this->man('player')->findById($xid);
        
        $dt->success('entityutil->removePlayer success inicial');
        if ( (!isset($xid)) || (empty($xid))) {
            $dt->error('entityutil-removePlayer el parametro xid esta vacio! o no existe');
        }
        
        if($dt->isValid()){
            $this->db->begin();
            $sql = 'delete from travel where armies_id IN( select id from armies where player_id = ' . $xid . ')';
            $dt = $this->db->deleteDt($sql, true);
        }
            
        if($dt->isValid()){
            $sql = "DELETE FROM abilities_players WHERE id = " . $xid;
            $dt = $this->db->deleteDt($sql, true);
        }

        if($dt->isValid()){
            $sql = "DELETE FROM battles_players WHERE id = " . $xid;
            $dt = $this->db->deleteDt($sql, true);
        }

        if($dt->isValid()){
            $sql = 'delete from movements where army_id IN( select id from armies where player_id = ' . $xid . ')';
            $this->db->query($sql, true);
        }

        if($dt->isValid()){
            $sql = 'delete from armies_actions where army_id IN( select id from armies where player_id = ' . $xid . ')';
            $this->db->query($sql, true);
        }

        if($dt->isValid()){
            $sql = 'delete from armies where player_id = ' . $xid;
            $this->db->query($sql, true);
        }

        if($dt->isValid()){
            $sql = 'delete from players where id = ' . $xid;
            $this->db->query($sql, true);
        }

        if($dt->isValid()){
            $sql = 'delete from internal_constructions where id  IN (SELECT colony_id FROM colonies WHERE owner_id  = ' . $xid . ')';
            $dt = $this->db->deleteDt($sql, true);
        }

        if($dt->isValid()){
            $sql = 'delete from colonies WHERE owner_id = '.$xid;
            $dt = $this->db->deleteDt($sql, true);
        }

        if($dt->isValid()){
            $this->db->commit();
            $dt->success('Usuario [' . $removedPlayer->getParam("username") . '] eliminado');
            $dt->setData($xid);
        }
        else{
            $this->db->rollback();
        }
        return $dt;
    }

    
    
    public function deleteColoniesByRegionIdDt($regionId){
        $sql="delete from internal_constructions where colony_id IN (SELECT id FROM colonies WHERE region_id = ".$regionId.")" ;
        $dt = $this->db->deleteDt($sql);
        $continue = true;
    //Eliminar 
        if($dt->isValid()){
            $sql="DELETE from colonies WHERE region_id = ".$regionId;
            $dt = $this->db->deleteDt($sql);
        }
        else{
            debug::warning($dt,'entityutil->deleteColoniesByRegionIdDt Error Eliminando internal constructions de colonia['.$regionId.']');
        }
        return $dt;
    }
    
    
    public function createRandomMap($x,$y){
        $mapTypeList = region::mapTypeList();
        $mapType = $mapTypeList[array_rand($mapTypeList)];
        $normalizedMap = $this->normalizedMapList($mapType);
        //debug::log($normalizedMap,'entityutil->mapType');
        $map = array();
        for($yIter = 0; $yIter < $y ; $yIter++){
            $map[$yIter] = array();
            for($xIter = 0; $xIter < $x ; $xIter++){
                $randomize = rand(1,100);
                //debug::log($randomize,'entityutil->mapType');
                $map[$yIter][$xIter] = $this->randomProb($randomize,$normalizedMap) ; //Pradera es 1 no 0
            }
        }
        return $map;
    }
 
    
    
    /*
     * funcion createMapWithPersinNoise: Funcion que crea un mapa usando Perlin
     * Noise y el Id que nosotros le asignemos, pradera, nieve, etc
     */
    public function createHomeMapWithPersinNoise($xMax,$yMax,$bob){
      //prado =1 ; mountain = 2; desert = 3 sea = 4 ; snow = 5
      $mapType = array(1=>45,2=>25,3=>30,4=>0,5=>0);//Inicial
      $normalizedMap =  $this->normalizedMapList($mapType);
      
      
      
      //debug::warning($normalizedMap,'entityutil->createHomeMapWithPersinNoise['.$mapRandId.'] maptype');
      $map = array();
      for($yi=0; $yi<$yMax; $yi+=1) {
          $map[$yi] = array();
          for($xi=0; $xi<$xMax; $xi+=1) {
              
              $num = $bob->noise($xi,$yi,0,10);
              //debug::log($num,'entityutil->persinnoise');
              $xenovalue =  ceil( ( ( $num +1 ) / 2 )* 100);
              //$xenovalue =  ceil( ( ( $num +1 ) / 2 )* 5);
              //debug::log($xenovalue,'entityutil->persinnoise');
              //$map[$yi][$xi] = $xenovalue;
              $tempTerrain = $this->randomProb($xenovalue,$normalizedMap);
              if($tempTerrain<1){
                  $map[$yi][$xi] = 1;
              }
              elseif ($tempTerrain >_X_TERRAIN_MAX){
                  $map[$yi][$xi] = _X_TERRAIN_MAX;
              }
              else{
                  $map[$yi][$xi] = $tempTerrain; //Pradera es 1 no 0
              }
              
          }
      }
      //debug::log($map,'entityutil->persinnoise map');
      return $map;
    }
    
    
    /*
     * funcion createRandomMapWithPersinNoise: Funcion que crea de forma random
     * un mapa usando PersinNoise. 
     */
    public function createRandomMapWithPersinNoise($xMax,$yMax,$bob){
      $mapTypeList = region::mapTypeList();
      $mapRandId = array_rand($mapTypeList);
      $mapType = $mapTypeList[$mapRandId];
      $normalizedMap =  $this->normalizedMapList($mapType);
      
      //debug::warning($normalizedMap,'entityutil->createRandomMapWithPersinNoise['.$mapRandId.'] maptype');
      $map = array();
      for($yi=0; $yi<$yMax; $yi+=1) {
          $map[$yi] = array();
          for($xi=0; $xi<$xMax; $xi+=1) {
              
              $num = $bob->noise($xi,$yi,0,10);
              //debug::log($num,'entityutil->persinnoise');
              $xenovalue =  ceil( ( ( $num +1 ) / 2 )* 100);
              //$xenovalue =  ceil( ( ( $num +1 ) / 2 )* 5);
              //debug::log($xenovalue,'entityutil->persinnoise');
              //$map[$yi][$xi] = $xenovalue;
              $tempTerrain = $this->randomProb($xenovalue,$normalizedMap);
              if($tempTerrain<1){
                  $map[$yi][$xi] = 1;
              }
              elseif ($tempTerrain >_X_TERRAIN_MAX){
                  $map[$yi][$xi] = _X_TERRAIN_MAX;
              }
              else{
                  $map[$yi][$xi] = $tempTerrain; //Pradera es 1 no 0
              }
              
          }
      }
      //debug::log($map,'entityutil->persinnoise map');
      return $map;
    }
    /*
     * normalizedMapList, enviandole un mapa de planet, sumamos cada valor hasta obtener
     * rangos de valores crecientes hasta 100. Ejemplo de un arreglo{20,30,15,35}
     * termina {20,50,65,100}, sumamos cada valor
     */
    private function normalizedMapList($probList){
        $probModifiedList = array();
        $counter = 0;
        $icon = 0;
        foreach($probList as $i => $prob){
            if($prob>0){
                $counter = $counter + $prob;
                $probModifiedList[$i] = $counter;
            }
            $icon++;
        }
        return $probModifiedList;
    }
    
    private function randomProb($randomValue,$probModifiedList){
        $iResp = 0;
        foreach($probModifiedList as $i => $probList){
            if($randomValue<= $probList){
                $iResp = $i;
                //debug::log('iResp:'.$iResp.' random:'.$randomize,'entityutil->probModifiedLst');
                break;
            }
        }
        //debug::log($probModifiedList,'entityutil->probModifiedLst');
        
        return $iResp;
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
    
    public static function listDefaultCreatures(){
        $listCreatures = array(_X_CREATURE_TYPE_FIGHTER,_X_CREATURE_TYPE_FIGHTER,_X_CREATURE_TYPE_ARCHER,_X_CREATURE_TYPE_ARTILLERY,_X_CREATURE_TYPE_ARCHER,_X_CREATURE_TYPE_FIGHTER);
        return $listCreatures;
    }
    
    
    public function galaxyFreePositions(){
        $galaxyX = _X_GALAXY_SIZE_X;
        $galaxyY = _X_GALAXY_SIZE_Y;
        $ok = false;
        $counter = 10;
        $stars = new stars();
        $stars->initByGalaxy();
        $starsEmptyMaps  = array_fill(1, $galaxyY, array_fill(1,$galaxyX,true));
        foreach($stars->getRaw() as $i =>$star){
            //debug::log($star,'entityutil->galaxyFreePositions');
            unset($starsEmptyMaps[$star['axis_y'] ][ $star['axis_x'] ]);
        }
        return $starsEmptyMaps;
    }

public function createHumanPlayerDt($username,$mail){
    
    $continue = true;
    $oStar= $this->man('star')->findByLeastPlanet();
    $dt = $this->createRandomPlanetDt($oStar->getId());
    
    //Se creo correctamente el planeta?
    if( ($continue) && $dt->isValid() ){
        $planet_raw = $dt->getData();
        $planetId = $planet_raw['id'];
        $total = rand(9,16);//Un planeta tiene entre 9 a 16 regiones,
        //La funcion createRandomRegionsDT crea una region random que sera
        //la region inicial que se enviara al dt como id
        $dt = $this->createRandomRegionsDt($planetId, $total);
    }
    else{
        $continue = false;
    }
    //Se creo correctamente la region, crear una region inicial?
    if( ($continue) && $dt->isValid() ){
        
        $homeRegionId = $dt->getData();
        $oHomeRegion = $this->man("region")->findById($homeRegionId);
        //$regions = new regions();
        //$regions->initByPlanetId($planetId);
        //debug::log($regions,'entityutil->createHumanPlayerDt regions');
        //$oHomeRegion  = $regions->rand();
        
        //debug::log($oHomeRegion,'entityutil->createHumanPlayerDt region elegida');
        //$homeRegionId = $oHomeRegion->getId();
    }
    else{
        $continue = fase;
    }
    
    //Todo normal con los datos?
    if( ($continue) && $dt->isValid() ){
        $data["id"] = $this->db->nextId('players');
        $data["username"] = $username;
        $data["max_energy"] = _X_PLAYER_INITIAL_MAX_ENERGY;
        $data["actual_energy"] = _X_PLAYER_INITIAL_MAX_ENERGY;
        $data["rate_energy"] = _X_PLAYER_INITIAL_ENERGY_RATE;
        $data["state"] = _X_ACTIVE;
        $data["mail"] = $mail;
        $data["current_region"] = $homeRegionId;
        $data["type"] = _X_PLAYER_TYPE_HUMAN;
        $data["home_region_id"] = $homeRegionId;
        $data["current_colony_id"] = 0;
        
        $data['res1']= _X_RESOURCE_1_INITIAL_VALUE;
        $data['res2']= _X_RESOURCE_2_INITIAL_VALUE;
        $data['res3']= _X_RESOURCE_3_INITIAL_VALUE;
        $data['res4']= _X_RESOURCE_4_INITIAL_VALUE;
        $data['res5']= _X_RESOURCE_5_INITIAL_VALUE;
        $data['res6']= _X_RESOURCE_6_INITIAL_VALUE;
        $data['res7']= _X_RESOURCE_7_INITIAL_VALUE;
        $data['res8']= _X_RESOURCE_8_INITIAL_VALUE;
        
        $data['max_res6']= _X_RESOURCE_6_MAX_INITIAL_VALUE;
        $data['max_res7']= _X_RESOURCE_7_MAX_INITIAL_VALUE;
        $data['max_res8']= _X_RESOURCE_8_MAX_INITIAL_VALUE;
        
        $data['rate_res6']= _X_RESOURCE_6_INITIAL_RATE;
        $data['rate_res7']= _X_RESOURCE_7_INITIAL_RATE;
        $data['rate_res8']= _X_RESOURCE_8_INITIAL_RATE;
        
        $data['tutorial']= _X_PLAYER_INITIAL_TUTORIAL;
        
        
        
       // debug::log($data,'createRandomRegionDt');
        $dt = $this->man('player')->createDt($data);
        $dt->setDataIfValid($data);
        
    }
    return $dt;
}
    
    
public function createArmiesDT($listOfCreatures,$regionId,$playerId,$position){
    $dt = new datatransfer();
    $dt->success("Se crearon los armies del jugador [".$playerId."]");
    foreach($listOfCreatures as $creature_id){
        $dt = $this->createArmyDT($creature_id,$regionId,$playerId,$position);
        if(!$dt->isValid()){
            break;
        }
    }

    
    if($dt->isValid()){
         $armies = new armies();
         $armies->initByPlayerIdByRegionId($regionId, $playerId);
         $oRegion = $this->man('region')->findById($regionId);
         $cm = $oRegion->getCachedMapUtil();
         $dt = $armies->moveArmiesToSideDt($position,$cm);
         if(!$dt->isValid()){
              debug::warning($dt,' admin:RecrearIA No se pudo mover las unidades recien creadas' );
         }
    }   
    return $dt;
}

  /*********************************************************************************
  * createArmyDT: Crea una unidad asignada
  *********************************************************************************/
public function createArmyDT($creatureId,$regionId,$playerId,$position){//Lo deberia de hacer otra clase quizas armyman

    $dt = new datatransfer();

    //$sql="SELECT * FROM creatures WHERE creatures.id =".$creatureId;
    //$resp = $this->db->vectorize($sql);
    //if(count($resp)==0){
    if(true){

        $sql="SELECT * FROM creatures WHERE creatures.id =".$creatureId;
        $creature = $this->db->fetch($sql);
        $armyId = $this->db->nextId('armies','id');
        $sql='INSERT INTO
          armies(id,region_id,player_id,position_x,position_y,name,size,creature_id,attack,attrition,armor,life,
          speed,state,max_energy,actual_energy,type,"position",next_position,total_life,attack_quantity,
          level,experience,initiative,movement,max_size,region_state)
        VALUES (
          '.$armyId.','.$regionId.','.$playerId.','._X_POSITION_COORD_NULL.','._X_POSITION_COORD_NULL.',
          \''.$creature['name'].'\','.$creature['max_size'].','.$creatureId.','.$creature['attack'].','.$creature['attrition'].',
          '.$creature['armor'].','.$creature['life'].',\''.$creature['speed'].'\',\''._X_ARMY_STATE_ALIVE.'\','.$creature['max_energy'].','.$creature['max_energy'].',
          \''._X_ARMY_TYPE_NORMAL.'\',\''._X_POSITION_ORBIT.'\',\''._X_POSITION_ORBIT.'\',
          '.$creature['life']*$creature['max_size'].','.$creature['attack_quantity'].',
          '._X_ARMY_INITIAL_LEVEL.','._X_ARMY_INITIAL_XP.','.$creature['initiative'].','.$creature['movement'].','.$creature['max_size'].',\''._X_REGION_STATE_INSIDE.'\')';

          debug::log($sql,"entityutil.class->createArmyDt");  
         $this->db->query($sql);
         //Agregar posicion de entrada de acuerdo a lo seleccionado
         

         //Agregar acciones a los armies
         $sql = 'SELECT creature_id,action_id,id,`order` FROM creatures_actions
         WHERE creatures_actions.creature_id ='.$creatureId;
         $creatureActions = $this->db->sql2array($sql);
         $order =1;
         foreach($creatureActions as $army_action){
                $armyActionId = $this->db->nextId('armies_actions','id');
                $sql="INSERT INTO armies_actions(army_id,action_id,id,order,state)
                VALUES (".$armyId.",".$army_action['action_id'].",".$armyActionId.",".$army_action['order'].",'"._X_CREATURE_ACTION_STATE_ACTIVE."');";
                $this->db->query($sql);
                $order++;
                debug::log($sql,"entityutil.class->createArmyDT");
         }

         $dt->success('Unidad Creada:['.$armyId.']');
         $data = array();
         $data['army_id'] = $armyId;
         $dt->setData($data);
         
         if( $dt->isValid() ){
             
         }
         
     }
     else{
         $dt->error($resp,'Existe una unidad en el lugar seleccionado');
     }
     return $dt;
  }

public function deleteBattlesFromRegionIdDt($regionId){
    $sql="DELETE from battles_players WHERE battle_id IN (SELECT id FROM battles WHERE region_id = ".$regionId." )";
    $dt = $this->db->deleteDt($sql);
    $continue = true;
    //Eliminar 
    if($dt->isValid()){
        $sql="DELETE from battles WHERE region_id = ".$regionId;
        $dt = $this->db->deleteDt($sql);
    }
    else{
        debug::warning($dt,'entityutil->deleteBAttlesFromRegionIdDt Error Elimnando battles_players');
    }
    return $dt;
}

  /*********************************************************************************
  * Deletes
  *********************************************************************************/
    public function deleteArmiesFromRegionIdDt($regionId){
        $sql="delete from movements where region_id =".$regionId;
        $dt = $this->db->deleteDt($sql);
        $continue = true;
        
        if($dt->isValid()){
            $sql="delete from armies_actions where army_id IN (SELECT id FROM armies WHERE region_id =".$regionId.");";
            $dt = $this->db->deleteDt($sql);
        }
        else{
            $continue = false;
            debug::warning($dt,'entityutil->deleteArmiesFromRegionIdDt Error Elimnando movements');    
        }
        
        if( ( $continue ) && ( $dt->isValid() ) ){
            $sql="delete from armies where region_id =".$regionId;
            debug::info($sql,'entityutil->deleteArmiesFromRegionIdDt');
            $dt = $this->db->deleteDt($sql);
        }
        else{
            $continue = false;
            debug::warning($dt,'entityutil->deleteArmiesFromRegionIdFromPlayerTypeDt Error Elimnando armies_actions');
        }
        
        
        if( !( $continue ) || !( $dt->isValid() ) ){
            debug::warning($dt,'entityutil->deleteArmiesFromRegionIdDt Error Elimnando armies');    
        }
        return $dt;
    }
      /*********************************************************************************
  * Deletes
  *********************************************************************************/
    public function deleteArmiesFromRegionIdFromPlayerTypeDt($regionId,$playerType){
        $sql="delete from movements where region_id = ".$regionId." AND army_id IN (SELECT armies.id FROM armies 
INNER JOIN players ON armies.player_id = players.id WHERE players.type ='".$playerType."' )";
        $dt = $this->db->deleteDt($sql);
        $continue = true;
        //Eliminar 
        if($dt->isValid()){
            $sql="delete from armies_actions where army_id IN (SELECT armies.id FROM armies 
            INNER JOIN players ON armies.player_id = players.id WHERE players.type ='".$playerType."' AND region_id =".$regionId.");";
            $dt = $this->db->deleteDt($sql);
        }
        else{
            $continue = false;
            debug::warning($dt,'entityutil->deleteArmiesFromRegionIdFromPlayerTypeDt Error Elimnando movements');
            
        }
        
        if( ( $continue ) && ( $dt->isValid() ) ){
            $sql="delete from armies where region_id =".$regionId." AND player_id IN (SELECT id FROM players WHERE players.type = '".$playerType."' )";
            
            $dt = $this->db->deleteDt($sql);
        }
        else{
            $continue = false;
            debug::warning($dt,'entityutil->deleteArmiesFromRegionIdFromPlayerTypeDt Error Elimnando armies_actions');
        }
        //////////////////////////////////////////////////////////////////////////////////
        if( !( $continue ) || !( $dt->isValid() ) ){
            debug::warning($dt,'entityutil->deleteArmiesFromRegionIdFromPlayerTypeDt Error Elimnando armies');    
        }
        
        return $dt;
    }
    
    
        /*********************************************************************************
     * register: [TODO]MOVER ESTO A OTRO LUGAR Nuevo Usuario
     * ******************************************************************************* */

    public function registerUser($name, $mail, $pass,$commit = false) {
        $dt = new datatransfer();
        $dt->success('Success Inicial');
        $this->db->begin();
        $playerId= 0;
        
        $rawUser = $this->man("player")->findByUsername($name);
        if($rawUser){ /* Existe usuario con ese nombre */
            $dt->error('El nombre de usuario ya existe, intenta con un nombre mas excentrico');
            $position = 'name';
        }
        //debug::log($dt,'userutil step1');
        
        if($dt->isValid()){ /* Existe usuario con ese correo */
            $rawUser = $this->man("player")->findByMail($mail);
            if($rawUser){
                $dt->error('Parece que tu correo ya esta registrado, quizas se te olvido el nombre de usuario o tu password?');
                $position = 'mail';
            }
        }
        //debug::log($dt,'userutil step2');
        
        if($dt->isValid()){ /* Tamaño del password es el adecuado*/
            $numChar = strlen(utf8_decode($pass));
            if ( !($numChar >= _X_PLAYER_MIN_PASS_SIZE) ) {
                $dt->error('Tu Password tiene que ser de mas de ' . _X_PLAYER_MIN_PASS_SIZE . ' caracteres');
                $position = 'password';
            }
        }
        //debug::log($dt,'userutil step3');
        
        if($dt->isValid()){ /* Correo Electronico es el adecuado */
            if (! (validEmail($mail))) {
                $dt->error('Ingresa un Correo Valido');
                $position = 'mail';
            }
        }
        
        //debug::log($dt,'userutil step4');
        if($dt->isValid()){ /* Creacion del jugador humano*/
            $dt = $this->createHumanPlayerDt($name, $mail);
            if(!$dt->isValid()){
                $dt->error('No se pudo crear al jugador, por favor reportalo en el foro y gana un premio!');
                $position = 'xeno';
            }
        }
        
        //debug::log($dt,'userutil step5');
        if($dt->isValid()){ /* Creacion de su armies*/
            $player_raw = $dt->getData();
            //debug::log($player_raw,'player->register player_raw');
            $playerId = $player_raw['id'];
            $oPlayer = $this->man('player')->findById($playerId);
            
            //debug::log($oPlayer,'player->register oPlayer');
            $listOfCreatures = $this->listDefaultCreatures();
            $dt = $this->createArmiesDT($listOfCreatures,$oPlayer->getHomeRegionId(),$oPlayer->getId(),_X_LEFT);
                        
            if(!$dt->isValid()){
                $dt->error('No se pudo crear las unidades del jugador');
                $position = 'xeno';
                $this->db->rollback(); 
            }
        }
        //debug::log($dt,'userutil step6');
        if($dt->isValid()){ /* Creacion de sus enemigos iniciales*/
            $listOfCreatures = $this->listDefaultCreatures();
            $oIAPlayers = new players();
            $oIAPlayers->initByLeastArmies(_X_PLAYER_TYPE_ARTIFICIAL);
            $oIAPlayer = $oIAPlayers->first();
            $dt = $this->createArmiesDT($listOfCreatures,$oPlayer->getHomeRegionId(),$oIAPlayer->getId(),_X_RIGHT);
            //debug::log($dt,'userutil-> prestep7');
            if(!$dt->isValid()){
                
                $dt->error('No se pudo crear las unidades enemigas de este jugador');
                $position = 'xeno';
            }
        }

        //debug::log($dt,'userutil step7');
        if($dt->isValid()){ /* Creacion de su colonia primaria*/                                                        
           $dt = $this->createRandomPrimaryColonydt($oPlayer->getHomeRegionId(), $oPlayer->getId());   
           if(!$dt->isValid()){
               
               $dt->error('No se pudo crear la colonia primaria inicial del jugador'); 
           } 
           else{
               
           }                        
        }
        //debug::log($dt,'userutil step8');
        if($dt->isValid()){ /* Creacion de las construcciones primarias*/                                                        
            $colony_raw = $dt->getData();
            $colonyId = $colony_raw['id'];
            $dt = $this->createDefaultConstructionInColonyDt($colonyId);   
            if(!$dt->isValid()){
               //debug::error($dt,'userutil RecrearHomeColony Error al crear las construcciones en la colonia '.$colony_raw['name'].'['.$colonyId.']');
               $dt->error('No se pudo crear las construcciones en la colonia '.$colony_raw['name']);
            }
        }
        
        //debug::log($dt,'userutil step9');
        if($dt->isValid()){    
            $dt->success('Bienvenido a ' . _X_GAMENAME . ' ' . $name);
            $dt->setData($playerId);
            if($commit){
                $this->db->commit();
            }
        }
        else{
            $this->db->rollback(); 
        }
        //debug::log($dt,'userutil laststep');
        return $dt;
    }
    
    
    /***************************************************************************
     *   TEAMS
     ***************************************************************************/
    public function createNextTeamDt($playerId,$regionId){
        $data = array();
        $data["id"] = $this->db->nextId('teams');
        $data["player_id"] = $playerId;
        $data["region_id"] = $regionId; 
        $value = $this->man("team")->countByPlayerIdByRegionId($playerId,$regionId) + 1;
        $data["name"] = 'Equipo '.$value ;
        $data["state"] = _X_ACTIVE;
        $dt = $this->man("team")->createDt($data);
        return $dt;
    }
}
?>