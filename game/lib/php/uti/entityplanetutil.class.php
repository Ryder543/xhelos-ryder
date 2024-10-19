<?php
  require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
   
/******************************************************************
 * entityutil: Utilidad para creacion y mantenimiento de entidades
 ******************************************************************/
  class entityplanetutil  extends util
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
    
    public function searchOrCreateInitialPlayerBase(){
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
    }
    
    public function createInitialPlanetWithRegionsWithouthMapsDt($starId,$orbit=false,$order=false,$namePrefixesList=false){
        $star = $this->man('star')->findById($starId);
        $planetNames = planet::planetNames();
        $data = array();
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
                $namePrefixesList =  $this->obtainPlanetsNamesFromStar($starId);
        } 
        $arrayPlanetNames = array_diff($planetNames,$namePrefixesList);

        $dt = new datatransfer();
        if(count($arrayPlanetNames) == 0 ){
            //$dt->error('createRandomPlanetDt Se acabaron los nombres de planeta');
            //debug::error($namePrefixesList,'createRandomPlanetDt Se acabaron los nombres de planeta');
            
            $name = $star->getName() ." ". $planets->size()+1;
            $arrayPlanetNames[] = $name;
        }
            $planetSufijo = $arrayPlanetNames[array_rand ($arrayPlanetNames)];       
            $planetName =$planetSufijo.' '.$star->getName();
            $planetColorsId = planet::planetColorsId();
            $planetColorId = $planetColorsId[array_rand ($planetColorsId)];
            $planetSizeId = $this->sizeOfInitialPlanet();
            $planetSize = planet::planetSize($planetSizeId);
            $position = entityutil::orderMasculine($order-1);
            $orbita = entityutil::orderFemenine($orbit-1);
            $planetVariationsId = planet::planetVariationId();
            $planetVariationId = $planetVariationsId[array_rand ($planetVariationsId)];

            
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
            $data["type"] = _X_PLANET_TYPE_PRIMARY;
            $data["creation_time"] = "now";
            //debug::log($data,'createRandomPlanet');
            $dt = $this->man('planet')->createDt($data);
            if($dt->isValid()){
                $dt->setData($data);
            }
            //debug::log($dt,'entityutil createRandomPlanet Dt');
        
        if($dt->isValid()){
            //Mandar a Crear las regiones
            $eru = new entityregionutil();
            $dt = $eru->createInitialRegionsWithouthMapDt($data);
        }
        $dt->setData($data);        
        return $dt;
    }  
   
    
    
    public function deletePlanetDt($planet_id){
        $sql="DELETE FROM planets WHERE id = ".$planet_id;
        $dt = $this->db->deleteDt($sql);
        return $dt;
    }
    
     ///////////////////////////////////////////////////////////////////////////////////////
    //ANTIGUAS FUNCIONES
    ///////////////////////////////////////////////////////////////////////////////////////    
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
    
    
    
    
    //////////////////////////////////////////////////////////////////////////////////
    //METODOS ESTATICOS
    //////////////////////////////////////////////////////////////////////////////////    
    public static function sizeOfInitialPlanet(){
        return _X_PLANET_SIZE_BIG;
    } 
 
}
?>