<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad

/* * *********************************************************************************
 * entitregionyutil: Utilidad para creacion y mantenimiento de entidades de region
 * ******************************************************************************** */

class entityregionutil extends util {

    ///////////////////////////////////////////////////////////////////////////////////////
    //PROPIEDADES
    ///////////////////////////////////////////////////////////////////////////////////////    
    ///////////////////////////////////////////////////////////////////////////////////////
    //CONSTRUCTOR
    ///////////////////////////////////////////////////////////////////////////////////////    
    /*     * ******************************************
     * Constructor: De regionState
     * Params: 
     * $region_id: ID de la region que quiere mantener el estado
     * $player_id: ID del jugador actual de la region
     * EXTRA: Obtener la lista de arg $args = func_get_args(); 
     * ****************************************** */
    public function __construct() {
        parent::__construct();
    }

    public function obtainInitialPlayerHomeRegion() {
        //Paso A Preguntar si existe algun planeta aun no cubierto, si no crearlo
        $planetId = null;
        $regionOrder = null;        
        $planetman = planetman::singleton();
        $counted = $planetman->countPrimaryPlanetsThatAreNotFilledWithColonies();
        if(!isset($counted) || empty($counted) ){
            $oStar= $this->man('star')->findByLeastPlanet();
            $epu = new entityplanetutil();
            $dt = $epu->createInitialPlanetWithRegionsWithouthMapsDt($oStar->getId());
            if($dt->ok()){
                $data = $dt->getData();
                $planetId = $data['id'];
                $regionOrder = _X_COLONY_FIRST_POSITION;
            }
        }
        else{
            $order = $counted[0]['total'];//El primer objeto que devuelve
            $planetId = $counted[0]['planet_id'];
            $regionList = entityregionutil::IndexOfColoniesInInitialPlanet();
            $regionOrder = $regionList[$order];
        }
        $oRegion = $this->man("region")->findByPlanetIdByPosition($planetId,$regionOrder); 
        //Jeeba 31ene2013 si la region no se puede construir colonia entonces buclear por todas las posiciones
        $paramArray = array(_X_VAR_REGION_ID=>$oRegion->getId(),_X_VAR_COLONY_TYPE=>_X_COLONY_TYPE_PRIMARY);
        $rw = new createcolonywork();
        $rw->initParamsFromArray($paramArray);
        $rw->preValidatePrimaryColony();
        if(!$rw->isOk()){
            $planetPositionCounter = 0;
            while($planetPositionCounter<=_X_COLONY_MAX_NUMBER_PER_PLANET){
                $regionOrder = $regionList[$planetPositionCounter];
                $oRegion = $this->man("region")->findByPlanetIdByPosition($planetId,$regionOrder); 
                $paramArray2 = array(_X_VAR_REGION_ID=>$oRegion->getId(),_X_VAR_COLONY_TYPE=>_X_COLONY_TYPE_PRIMARY);
                $rw2 = new createcolonywork();
                $rw2->initParamsFromArray($paramArray2);
                $rw2->preValidatePrimaryColony();
                
                
                if($rw2->isOk()){
                    break;
                }
                else{
                    $planetPositionCounter++;
                }
                
            }
        }     
        $dt = new datatransfer();
        $dt->success("Success entityregionUtil->obtainInitialPlayerHomeRegion");
        $dt->setData($oRegion);
        return $dt;
    }
    
    
    

    public function createInitialRegionsWithouthMapDt($planetData) {
        $xTotal = entityregionutil::xOfInitialPlanet();
        $yTotal = entityregionutil::yOfInitialPlanet();
        $dt = null;
        
        debug::initProfiler();
        for ($y = 0; $y < $yTotal; $y++) {
            for ($x = 0; $x < $xTotal; $x++) {
                $index = maputil::index($x, $y, $xTotal);
                $dt = $this->createInitialRegionWithouthMapDt($planetData['id'], $index);
                $regionData = $dt->getData();
                if ($dt->ok()) {
                    $dt = $this->createVerifiedInitialPortalDt($regionData['id'], $index);
                }
                if ($dt->ok()) {
                    $dt = $this->createVerifiedInitialBiomeTier1Dt($regionData['id'], $index);
                }
                if ($dt->ok()) {
                    $dt = $this->createVerifiedInitialBiomeTier2Dt($regionData['id'], $index);
                }
                if ($dt->ok()) {
                    $dt = $this->createVerifiedInitialBiomeTier3Dt($regionData['id'], $index);
                }
                if ($dt->ok()) {
                    $dt = $this->createVerifiedInitialResourcesDt($regionData['id'], $index);
                }
                if ($dt->ok()) {
                    $dt = $this->createVerifiedInitialAlienColonyDt($regionData['id'], $index);
                }
                if ($dt->ok()) {
                    $dt = $this->createVerifiedInitialConstructionsDt($regionData['id'], $index);
                }
                if ($dt->ok()) {
                    $dt = $this->createVerifiedIntialGreatDominionDt($regionData['id'], $index);
                }

                if (!$dt->ok()) {
                    break;
                }
            }
            if (!$dt->ok()) {
                break;
            }
        }
        debug::endProfiler();
        debug::log(debug::getTime());
        return $dt;
    }

    //////////////////////////////////////////////////////////////////////////////////
    //METODOS PRINCIPALES
    //////////////////////////////////////////////////////////////////////////////////      

    /**
     * createInitialRegionWithouthMapDt Creamos una region sin mapa, el mapa se creara
     * la primera ves que se use la region, porque crear 121 regiones al vuelo, pues,
     * es demasiado hardcore.
     *
     * @param string $parametro Un parametro que se pasa a la clase
     *
     * @return  string
     * @todo    Check to make sure the username isn't already taken
     *
     * @since   17/01/2013
     * @author  windows7
     *
     * @edit    17/01/2013<br />
     *          windows7<br />
     *          Creacion Inicial <br/>
     *          #edit1
     */
    public function createInitialRegionWithouthMapDt($planetId, $order) {
        $planet = $this->man('planet')->findById($planetId);
        //$bob = new perlinnoise(rand(1,1000));

        $region_fem = entityutil::orderFemenine($order);
        $data['id'] = $this->db->nextId('regions');
        $data['name'] = $planet->getName() . ' ' . $order;
        $data['x'] = _X_REGION_INITIAL_HOME_X_SIZE;
        $data['y'] = _X_REGION_INITIAL_HOME_Y_SIZE;
        //$data['map'] = db::toPersistenceArray( $this->createRandomMapWithPersinNoise($data['x'],$data['y'], $bob));
        //$data['map'] = db::toPersistenceArray( $this->createRandomMap($data['x'],$data['y']),$bob  );
        $data['state'] = 'A';
        $data['planet_id'] = $planetId;
        $data['desc'] = "La " . $region_fem . " region del planeta " . $planet->getName() . " Su tamaño es de " . $data['x'] . " x " . $data['y'] . " cuadrados de movimiento";
        $data['player_owner_id'] = 0;
        $data['planet_position'] = $order;
        
        //Definimos el tipo de region que deberia de ser
        if(in_array($order,entityregionutil::IndexOfColoniesInInitialPlanet())){
            $data['type'] = _X_REGION_MAP_TYPE_INICIAL;
            $data['map'] = db::toPersistenceArray($this->createMapWithPersinNoise($data['x'],$data['y'], $data['type']));
        }
        else{
            $types = region::mapTypes();
            $type=_X_REGION_MAP_TYPE_INICIAL;//
            while($type==_X_REGION_MAP_TYPE_INICIAL){//No puede ser un lugar inicial
                $type = array_rand($types);               
            }
            $data['type'] = $type;
            $data['map'] = db::toPersistenceArray(array());
        }
        
        //$data['map'] = db::toPersistenceArray(array());
        
        
        
        $dt = $this->man('region')->createDt($data);
        $dt->setData($data);
        //debug::log($data,'createRandomRegionDt');
        return $dt;
    }

    public function createVerifiedInitialPortalDt($regionId, $regionPosition) {
        $dt = new datatransfer();
        $possibleIndexes = entityregionutil::IndexOfPortalsInInitialPlanet();
        if (in_array($regionPosition, $possibleIndexes)) {
            $dt = $this->createPortalDt($regionId);
        } else {
            $dt->success("Se creo correctamente el portal en una region inicial");
        }

        return $dt;
    }

    public function createPortalDt($regionId) {
        $rm = regionman::singleton();
        $rpm = regionportalman::singleton();
        $oRegion = $rm->findById($regionId);
        $data["id"] = $this->db->nextId('regions_portals');
        $data["name"] = "Portal en " . $oRegion->getName();
        $data["region_id"] = $regionId;
        $data["target_id"] = "null";
        $data["travel_time"] = _X_REGION_PORTAL_INITIAL_TIME_DURATION;
        /* $data["x"] = rand(0,$oRegion->getX()-1);
          $data["y"] = rand(0,$oRegion->getX()-1); */
        $data["x"] = _X_REGION_PORTAL_INITIAL_X_POSITION;
        $data["y"] = _X_REGION_PORTAL_INITIAL_Y_POSITION;
        $data["status"] = _X_ACTIVE;      
        $dt = $rpm->createDt($data);
        $dt->setData($data);
        return $dt;
    }

    public function createVerifiedInitialBiomeTier1Dt($regionId, $regionPosition) {
        $dt = new datatransfer();
        $possibleIndexes = entityregionutil::IndexOfBiomesTier1InInitialPlanet();
        if (in_array($regionPosition, $possibleIndexes)) {
            $dt = $this->createRandomBiome($regionId, _X_REGION_BIOME_TIER_1);
        } else {
            $dt->success("Se creo correctamente el portal en una region inicial");
        }

        return $dt;
    }

    public function createRandomBiome($regionId, $tier) {
        $biomes = new biomes();
        $biomes->initByTier($tier);
        $biome = $biomes->rand();

        $rbm = regionbiomeman::singleton();
        $data["id"] = $this->db->nextId('regions_biomes');
        $data["biome_id"] = $biome->getId();
        $data["region_id"] = $regionId;
        $data["status"] = _X_ACTIVE;
        $dt = $rbm->createDt($data);
        $dt->setData($data);
        return $dt;
    }

    public function createVerifiedInitialBiomeTier2Dt($regionId, $regionPosition) {
        $dt = new datatransfer();
        $possibleIndexes = entityregionutil::IndexOfBiomesTier2InInitialPlanet();
        if (in_array($regionPosition, $possibleIndexes)) {
            $dt = $this->createRandomBiome($regionId, _X_REGION_BIOME_TIER_2);
        } else {
            $dt->success("Se creo correctamente el portal en una region inicial");
        }
        return $dt;
    }

    public function createVerifiedInitialBiomeTier3Dt($regionId, $regionPosition) {
        $dt = new datatransfer();
        $possibleIndexes = entityregionutil::IndexOfBiomesTier3InInitialPlanet();
        if (in_array($regionPosition, $possibleIndexes)) {
            $dt = $this->createRandomBiome($regionId, _X_REGION_BIOME_TIER_3);
        } else {
            $dt->success("Se creo correctamente el portal en una region inicial");
        }

        return $dt;
    }

    public function createVerifiedInitialResourcesDt($regionId, $regionPosition) {
        $dt = new datatransfer();
        $possibleIndexes = entityregionutil::IndexOfResourcesInInitialPlanet();
        if (in_array($regionPosition, $possibleIndexes)) {
            $dt = $this->createRandomResource($regionId);
        } else {
            $dt->success("Se creo correctamente el portal en una region inicial");
        }
        return $dt;
    }

    public function createRandomResource($regionId) {
        $resources = new resources();
        $resources->initAllActiveStrategic();
        $resource = $resources->rand();
        $rrm = regionresourceman::singleton();
        $data["id"] = $this->db->nextId('regions_resources');
        $data["region_id"] = $regionId;
        $data["resource_id"] = $resource->getId();
        $data["position_x"] = _X_REGION_RESOURCE_INITIAL_X_POSITION;
        $data["position_y"] = _X_REGION_RESOURCE_INITIAL_Y_POSITION;
        $data["quality"] = _X_RESOURCE_INITIAL_QUALITY;
        $data["status"] = _X_ACTIVE;        
        $dt = $rrm->createDt($data);
        $dt->setData($data);
        return $dt;
    }

    public function createVerifiedInitialAlienColonyDt($regionId, $regionPosition) {
        $dt = new datatransfer();
        $possibleIndexes = entityregionutil::IndexOfAlienColoniesInInitialPlanet();
        if (in_array($regionPosition, $possibleIndexes)) {
            $ecu = new entitycolonyutil();
            $dt = $ecu->createAlienPrimaryColony($regionId);
        } else {
            $dt->success("Se creo correctamente el portal en una region inicial");
        }
        return $dt;
    }

    public function createVerifiedInitialConstructionsDt($regionId, $regionPosition) {
        $dt = new datatransfer();
        $possibleIndexes = entityregionutil::IndexOfBasicRegionalConstructionsInInitialPlanet();
        if (in_array($regionPosition, $possibleIndexes)) {
            $dt = $this->createBasicRegionBuilding($regionId, _X_REGION_CONSTRUCTION_FOUNDATIONS);
        } else {
            $dt->success("Se creo correctamente el gran dominio en una region inicial");
        }
        return $dt;
    }

    public function createBasicRegionBuilding($regionId, $constructionId) {
        $data["id"] = $this->db->nextId('regions_buildings');
        $data["region_construction_id"] = $constructionId;
        $data["x"] = _X_REGION_CONSTRUCTION_INITIAL_X_POSITION;
        $data["y"] = _X_REGION_CONSTRUCTION_INITIAL_X_POSITION;
        $data["status"] = _X_REGION_CONSTRUCTION_STATUS_ACTIVE;
        $data["region_id"] = $regionId;
        $rbm = regionbuildingman::singleton();
        $dt = $rbm->createDt($data);
        $dt->setData($data);
        return $dt;
    }

    public function createVerifiedIntialGreatDominionDt($regionId, $regionPosition) {
        $dt = new datatransfer();
        $possibleIndexes = entityregionutil::IndexOfGreatDominionInInitialPlanet();
        if (in_array($regionPosition, $possibleIndexes)) {
            $dt = $this->createBasicRegionBuilding($regionId, _X_REGION_CONSTRUCTION_GREAT_DOMINION);
        } else {
            $dt->success("Se creo correctamente el Gran Dominio en una region inicial");
        }
        return $dt;
    }

    //////////////////////////////////////////////////////////////////////////////////
    //REGION MAPAS
    //////////////////////////////////////////////////////////////////////////////////     
    public function setMapOfRegion($regionId,$x,$y,$type) {
        
        $map = $this->createMapWithPersinNoise($x,$y,$type);//No necesitamos enviarlo a persistence, porque ya esa persistente
        $stringMap = db::toPersistenceArray($map);
        $sql = "UPDATE regions SET map='".$stringMap."' WHERE id=".$regionId;
        $dt =$this->db->updateDt($sql);
        //$data['map']=$map;JODER lo que devuelve esto es un mapa bidimensional, aqui usamos uno de una dimension nomas
        $data['stringMap'] = $stringMap;
        $dt->setData($data);//Por ejemplo en region->getMap();*/
        return $dt;   
    }

    
    
     /*
     * funcion createMapWithPersinNoise: Funcion que crea un mapa usando nuestros propios randoms
     * Este tipo de mapa sale realmente random, usarlo en asteroides u otro tipo de mapa
     */   
    public function createRandomMap($x, $y) {
        $mapTypeList = region::mapTypeList();
        $mapType = $mapTypeList[array_rand($mapTypeList)];
        $normalizedMap = $this->normalizedMapList($mapType);
        //debug::log($normalizedMap,'entityutil->mapType');
        $map = array();
        for ($yIter = 0; $yIter < $y; $yIter++) {
            $map[$yIter] = array();
            for ($xIter = 0; $xIter < $x; $xIter++) {
                $randomize = rand(1, 100);
                //debug::log($randomize,'entityutil->mapType');
                $map[$yIter][$xIter] = $this->randomProb($randomize, $normalizedMap); //Pradera es 1 no 0
            }
        }
        return $map;
    }

    /*
     * funcion createMapWithPersinNoise: Funcion que crea un mapa usando Perlin
     * Noise y el Id que nosotros le asignemos, pradera, nieve, etc
     */
    public function createMapWithPersinNoise($xMax, $yMax,$type) {
        $bob = new perlinnoise(rand(1, 1000));
        //prado =0 ; mountain = 1; desert = 2 sea = 3 ; snow = 4; inicial = 5
        $mapTypeList = region::mapTypeList();
        $mapType = $mapTypeList[$type];
        $normalizedMap = $this->normalizedMapList($mapType);
        //debug::warning($normalizedMap,'entityutil->createHomeMapWithPersinNoise['.$mapRandId.'] maptype');
        $map = array();
        for ($yi = 0; $yi < $yMax; $yi+=1) {
            $map[$yi] = array();
            for ($xi = 0; $xi < $xMax; $xi+=1) {

                $num = $bob->noise($xi, $yi, 0, 10);
                //debug::log($num,'entityutil->persinnoise');
                $xenovalue = ceil(( ( $num + 1 ) / 2 ) * 100);
                //$xenovalue =  ceil( ( ( $num +1 ) / 2 )* 5);
                //debug::log($xenovalue,'entityutil->persinnoise');
                //$map[$yi][$xi] = $xenovalue;
                $tempTerrain = $this->randomProb($xenovalue, $normalizedMap);
                if ($tempTerrain < 1) {
                    $map[$yi][$xi] = 1;
                } elseif ($tempTerrain > _X_TERRAIN_MAX) {
                    $map[$yi][$xi] = _X_TERRAIN_MAX;
                } else {
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
    public function createRandomMapWithPersinNoise($xMax, $yMax, $bob) {
        $mapTypeList = region::mapTypeList();
        $mapRandId = array_rand($mapTypeList);
        $map = $this->createMapWithPersinNoise($xMax, $yMax, $bob, $mapRandId);
        return $map;
    }

    /*
     * normalizedMapList, enviandole un mapa de planet, sumamos cada valor hasta obtener
     * rangos de valores crecientes hasta 100. Ejemplo de un arreglo{20,30,15,35}
     * termina {20,50,65,100}, sumamos cada valor
     */

    private function normalizedMapList($probList) {
        $probModifiedList = array();
        $counter = 0;
        $icon = 0;
        foreach ($probList as $i => $prob) {
            if ($prob > 0) {
                $counter = $counter + $prob;
                $probModifiedList[$i] = $counter;
            }
            $icon++;
        }
        return $probModifiedList;
    }

    private function randomProb($randomValue, $probModifiedList) {
        $iResp = 0;
        foreach ($probModifiedList as $i => $probList) {
            if ($randomValue <= $probList) {
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

    /* IndexOfPortalsInInitialPlanet: devuelve la lista de indices donde deberian de ir portales */
    public static function IndexOfPortalsInInitialPlanet() {
        $index = array(3, 7, 33, 43, 77, 87, 113, 117);
        return $index;
    }

    /* IndexOfBiomesTier1InInitialPlanet: devuelve la lista de indices donde deberian de ir Biomas de Level1 */

    public static function IndexOfBiomesTier1InInitialPlanet() {
        $index = array(0, 5, 10, 14, 18, 34, 42, 55, 65, 78, 86, 102, 106, 110, 115, 120);
        return $index;
    }

    /* IndexOfColInInitialPlanet: devuelve la lista de indices donde deberian de ir las colonias */

    public static function IndexOfColoniesInInitialPlanet() {
        $index = array(_X_COLONY_FIRST_POSITION, 16, 20, 56, 64, 100, 104, 108);
        return $index;
    }

    /* IndexOfResourcesInInitialPlanet: devuelve la lista de indices donde deberian de ir los recursos */

    public static function IndexOfResourcesInInitialPlanet() {
        $index = array(24, 27, 30, 57, 63, 90, 93, 96);
        return $index;
    }

    /* IndexOfAlienColoniesInInitialPlanet: devuelve la lista de indices donde deberian de ir las colonias alienigenas */

    public static function IndexOfAlienColoniesInInitialPlanet() {
        $index = array(36, 38, 40, 58, 62, 80, 82, 84);
        return $index;
    }

    /* IndexOfBiomesTier2InInitialPlanet: devuelve la lista de indices donde deberian de ir los biomas nivel 2 */

    public static function IndexOfBiomesTier2InInitialPlanet() {
        $index = array(37, 39, 47, 51, 69, 73, 81, 83);
        return $index;
    }

    /* IndexOfBiomesTier3InInitialPlanet: devuelve la lista de indices donde deberian de ir los biomas nivel 3 */

    public static function IndexOfBiomesTier3InInitialPlanet() {
        $index = array(49, 59, 61, 71);
        return $index;
    }

    /* IndexOfBasicRegionalConstructionsInInitialPlanet: devuelve la lista de indices donde deberian de ir las construcciones cimientos */

    public static function IndexOfBasicRegionalConstructionsInInitialPlanet() {
        $index = array(48, 50, 70, 72);
        return $index;
    }
    
    /* IndexOfBasicRegionalConstructionsInInitialPlanet: devuelve la lista de indices donde deberian de ir las construcciones cimientos */

    public static function IndexOfPrimaryPlayerColoniesInInitialPlanet() {
        $index = array(12, 16, 20, 56,64,100,104,108);
        return $index;
    }
    /* IndexOfGreatDominionInInitialPlanet: devuelve la lista de indices donde deberian de ir los Great Dominion */

    public static function IndexOfGreatDominionInInitialPlanet() {
        $index = array(60);
        return $index;
    }

    //Numero de Tiles X en un planeta de inicio
    public static function xOfInitialPlanet() {
        return 11;
    }

    //Numero de Tiles y en un planeta de inicio    
    public static function yOfInitialPlanet() {
        return 11;
    }
    
    
    

}

?>