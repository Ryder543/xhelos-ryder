<?php
/**
 * Obtiene mapas que estan ordenados segun nuestras necesidades y ademas estan cacheados
 *
 * Informacion acerca del constructor de la clase
 *
 * @category  Xhelos
 * @package   Main
 * @license   http://www.opensource.org/licenses/BSD-3-Clause
 * @example   ../index.php
 * @example
 * @version   0.01
 * @since     02/01/2013
 * @author Jeeba
 */
require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/* * *****************************************************
 * maputil: Clase Utilitaria para Mapas 
 * **************************************************** */

class cachedmaputil extends maputil {

//[TODO]El reset se esta llamando muchas veces, deberiamos tener una funcion
//setObjectParam('position','P') por ejemplo que actualize la accion
//////////////////////////////////////////////////////////////////////////////////
//Variables
///////////////////////////////////////////////////////////////////////////////////
//Variables Internas
    private $internal_maps;//Mapas Internos
    private $coords; //Mapa de Coordenadas ya calculadas
    private $obstacle_map; //Un mapa almacenado en la cache que contiene los obstaculos

///////////////////////////////////////////////////////////////////////////////////////
//CONSTRUCTOR
///////////////////////////////////////////////////////////////////////////////////////
    /********************************************
     * Constructor: De maputil
     * Params:
     * $map: Mapa Original con el que se quiere trabajar
     * ****************************************** */
    public function __construct($map, $total_x, $total_y, $region_id) {
        parent::__construct($map,$total_x,$total_y,$region_id);
        $this->internal_maps = array(_X_MAPUTIL_NORMALMAP => $map);
        $this->coords = array();
           /* foreach ($this->ext_map as $i => $tile) {
                $this->coords[$i]['x'] = $this->calculateXByIndex($i);
                $this->coords[$i]['y'] = $this->calculateYByIndex($i);
            }*/
            
            //Constructor Optimizado!. No usar otras funciones para evitar el fin del mundo
            //y porque inicializarlo asi es veloz considerando que se crean bastantes copias
            //de este archivo
            $totalY=$this->getTotalY();
            $totalX=$this->getTotalX();            
            for($y=0;$y<$totalY;$y++){
                $mulY = ($y*$totalY);
                for($x=0;$x<$totalX;$x++)
                {
                    $i = $mulY + $x;
                    $this->coords[$i]['x'] = $x;
                    $this->coords[$i]['y'] = $y;                    
                }
            }
            //debug::log("Mensaje de Prueba");
           
    }

///////////////////////////////////////////////////////////////////////////////////////
//PRINCIPALES
///////////////////////////////////////////////////////////////////////////////////////
    /******************************************************************************************
     * debug: Imprime las variables que tenga, mejora de maputil para mostrar la data cacheada
     * ***************************************************************************************/
    public function debug() {
        global $firephp;
        $firephp->group('Mapas de Batalla');
        debug::info($this->ext_map, 'debug maputil');
        debug::info($this->ext_total_x, 'total_x debug maputil');
        debug::info($this->ext_total_y, 'total_y debug maputil');

        debug::info($this->internal_maps[_X_MAPUTIL_CENTERMAP], 'maputil->debug->center');
        debug::info($this->internal_maps[_X_MAPUTIL_TOPMAP], 'maputil->debug->top');
        debug::info($this->internal_maps[_X_MAPUTIL_BOTTOMMAP], 'maputil->debug->bottom');
        debug::info($this->internal_maps[_X_MAPUTIL_LEFTMAP], 'maputil->debug->left');
        debug::info($this->internal_maps[_X_MAPUTIL_RIGHTMAP], 'maputil->debug->right');
        $firephp->groupEnd();
    }
    
     /********************************************************************************************************
     * initBattleMap: Inicializa solo el mapa de batalla indicado, esto es util para ingresar unidades al mapa
     * ****************************************************************************************************** */   
    public function initBattleMap($type) {
        //debug::trace("type","cachedmaputil->initBattleMap");
        switch ($type)
        {
            case _X_ARMY_SIDE_TOP:
                $this->internal_maps[_X_MAPUTIL_TOPMAP] = $this->ext_map;
                break;
            case _X_ARMY_SIDE_BOTTOM:
                $this->internal_maps[_X_MAPUTIL_BOTTOMMAP] = array_reverse($this->ext_map, true);
                break;
            case _X_ARMY_SIDE_LEFT:
                $this->calculateLeftMap();
                break;
            case _X_ARMY_SIDE_RIGHT:
                $this->calculateRightMap();
                break;
            
            //case _X_ARMY_SIDE_://[TODO]Esta correcto esto?
            case _X_ARMY_SIDE_CENTER://[TODO]Esta correcto esto?            
            case _X_ARMY_SIDE_NONE://[TODO]Esta correcto esto?
                $this->calculateCentralMap(); //Central Map
                break;
            
            default:
                debug::warning("Se ha mandado a iniciar un mapa que no existe[".$type."]","cachedmaputil->initBattleMap");
                $this->calculateCentralMap(); //Central Map
                break;
        }
    }
    /********************************************************************************************************
     * initBattleMaps: Inicializa TODOS los diferentes mapas de batalla, esto es util para ingresar unidades al mapa
     * ****************************************************************************************************** */
    public function initBattleMaps() {
        $this->initBattleMap(_X_MAPUTIL_TOPMAP);
        $this->initBattleMap(_X_MAPUTIL_BOTTOMMAP);
        $this->initBattleMap(_X_MAPUTIL_LEFTMAP);
        $this->initBattleMap(_X_MAPUTIL_RIGHTMAP);
        $this->initBattleMap(_X_MAPUTIL_CENTERMAP);
    }

    private function calculateLeftMap() {
        for ($x = 0; $x < $this->ext_total_x; $x++) {
            for ($y = 0; $y < $this->ext_total_y; $y++) {
                $index = $this->mapIndex($x, $y);
                $this->addTile($index, _X_MAPUTIL_LEFTMAP);                                                            
            }
        }
        //debug::log("Me quede aqui!");
    }
    /*
      getIndexesForColony: Obtiene un arreglo con los indices que ocupa una colonia
     * en el mapa dependiendo del tipo de colonia que es
     * 
     * @param integer $startIndex La ubicacion por indice de la base de la colonia, el punto central de la colonia
     * @param string $colonyType Indica el tipo de colonia que es, (P)rincipal o (S)ecundaria
     *
     * @return  array : Arreglo de los indices de esta colonia
     * @since   2012-Dic-26
     * @author  Jeeba <designbyjeeba@gmail.com>
     *
     * @edit    2012-Dic-26<br />
     *          Jeeba <designbyjeeba@gmail.com>
     *          Documentacion y Desarrollo Inicial <br/>
     *          #edit1
     */
    public function getIndexesForColony($startIndex, $colonyType) {
        $tiles = null;
        switch ($colonyType) {
            case _X_COLONY_TYPE_PRIMARY:
                $tiles = $this->getTilesAtRange($startIndex, _X_COLONY_TYPE_PRIMARY_RANGE, false);
                $tiles[] = $startIndex;
                break;
            case _X_COLONY_TYPE_SECONDARY:
                $tiles = $this->getTilesAtRange($startIndex, _X_COLONY_TYPE_SECONDARY_RANGE, true);
                $tiles[] = $startIndex;
                break;
        }
        return $tiles;
    }
    private function calculateRightMap() {
        for ($x = $this->ext_total_x - 1; $x >= 0; $x--) {
            for ($y = 0; $y < $this->ext_total_y; $y++) {
                $index = $this->mapIndex($x, $y);
                $this->addTile($index, _X_MAPUTIL_RIGHTMAP);
            }
        }
    }

    public function getWorkingMap($side) {
        if (!$this->isBattleMapInited($side)) { //Inicializamos los mapas si no fueron inicializados
            $this->initBattleMap($side);
        }
        switch ($side) {
            case _X_ARMY_SIDE_TOP:
                $workMap = $this->internal_maps[_X_MAPUTIL_TOPMAP];
                break;

            case _X_ARMY_SIDE_BOTTOM:
                $workMap = $this->internal_maps[_X_MAPUTIL_BOTTOMMAP];
                break;

            case _X_ARMY_SIDE_RIGHT:
                $workMap = $this->internal_maps[_X_MAPUTIL_RIGHTMAP];
                break;

            case _X_ARMY_SIDE_LEFT:
                $workMap = $this->internal_maps[_X_MAPUTIL_LEFTMAP];
                break;

            //case _X_ARMY_SIDE_NONE: //Se repite pue,[TODO] este debe de ser eliminado
            case _X_ARMY_SIDE_CENTER:
                $workMap = $this->internal_maps[_X_MAPUTIL_CENTERMAP];
//debug::trace('Lado['.$side.'] enviado por NONE','getWorkingMap maputil');
                break;

            default:
                debug::trace('Lado[' . $side . '] enviado por default', 'getWorkingMap maputil');
                $workMap = $this->internal_maps[_X_MAPUTIL_CENTERMAP];
        }
//debug::log($workMap,'Mapa de lado:['. $side .']');
        return $workMap;
    }

    public function removeFromAllWorkingMaps($index) {
        foreach ($this->internal_maps as $key => $value) {
//debug::info($this->internal_maps[$key],'removeFromAllWorkingMaps maputil');
            unset($this->internal_maps[$key][$index]);
//debug::info($this->internal_maps[$key],'removeFromAllWorkingMaps maputil');
        }
    }

    public function calculateCentralMap() {
        $centralx = floor($this->ext_total_x / 2);
        $centraly = floor($this->ext_total_y / 2);
        $loop = $this->getMaxLoopCenterMap();
//for ($iter = 1; $iter <= $loop; $iter++) {
        for ($iter = 0; $iter < $loop; $iter++) {
            $this->addTileToCentralMap($centralx, $centraly, $iter);
        }
    }

    public function addTileToCentralMap($x, $y, $rango) {
        $x_init = $x - $rango; //5-1
        $y_init = $y - $rango; //5-1
        $x_end = $x + $rango; //5+1
        $y_end = $y + $rango; //5+1


        $tot = ($rango * 2) + 1; //(1*2)+1=3
        for ($bucle = 0; $bucle < $tot; $bucle++) {//0
            $index = $this->mapIndex($x_init, $y_init + $bucle); //Left 4,4
            $this->addTile($index, _X_MAPUTIL_CENTERMAP);
            $index = $this->mapIndex($x_end, $y_init + $bucle); //right
            $this->addTile($index, _X_MAPUTIL_CENTERMAP);
            $index = $this->mapIndex($x_init + $bucle, $y_init); //top
            $this->addTile($index, _X_MAPUTIL_CENTERMAP);
            $index = $this->mapIndex($x_init + $bucle, $y_end); //bottom
            $this->addTile($index, _X_MAPUTIL_CENTERMAP);
        }
    }

    public function addTile($index, $side) {
//$index--;
        if (!isset($this->internal_maps[$side][$index])) {
//debug::log('Tile['.$index.'] se creo','addTile maputil');
            $this->internal_maps[$side][$index] = $this->ext_map[$index];
        } else {
//debug::log('Tile['.$index.'] ya existe','addTile maputil');
        }
    }

    /***********************************************
     * getXByIndex: Obtenemos x de la variable coords
     * que ya fue precalculada en el Constructor
     * *********************************************/
    function getXByIndex($index) {
        /* if($index<0){debug::trace('maputils->getYByIndex index es menor a 1');} */
        return $this->coords[$index]['x'];
    }
    
    /***********************************************
     * getYByIndex: Obtenemos y de la variable coords
     * que ya fue precalculada en el Constructor
     * *********************************************/
    function getYByIndex($index) {
        /* if($index<0){debug::trace('maputils->getYByIndex index['.$index.'] es menor a 1');}*/
        return $this->coords[$index]['y'];
    }

    function getCoordByIndex($index) {
        $coord = array();
        $coord['x'] = $this->getXByIndex($index);
        $coord['y'] = $this->getYByIndex($index);
        return $coord;
    }

    /*
      obtainObstacleMap  Obtiene un mapa con obstaculos, si $obstaclesIndexes es TRUE entonces
     * devuelve un arreglo con solo indices, si es FALSE devuelve un arreglo cuyo
     *  indice es el indice del mapa y su contenido es el peso minimo que se
     * puede colocar para motivos de movimiento (osea cero, cosa que una unidad 
     * al tratar de moverse ahi, se le muestra como cero ese valor cosa que no 
     * puede moverse
     * 
     * @param Integer $regionId  Si enviamos alguna region en especial cuando no inicializamos el mapa,
     * por defecto es FALSE para que utilice el id que obtiene al momento de llamar a su constructor
     * @param Boolean $obstaclesIndexes  Si es TRUE devuelve un arreglo con contenido con indices, 
     * si es FALSE el arreglo tiene indices con los indices y el contenido es $this->obtainMinimunQuanta()
     *
     * @return  Array : Con indices
     *         Array : Con pesos
     * @since   2011
     * @author  Jeeba <designbyjeeba@gmail.com>
     *
     * @edit    2012-Dic-26<br />
     *          Jeeba <designbyjeeba@gmail.com>
     *          Documentacion<br/>
     *          #edit2
     */
    function obtainObstacleMap($regionId = false, $obstaclesIndexes = false) {
        
        if(empty($this->obstacle_map)){
        
            if (!$regionId) {
                $regionId = $this->getRegionId();
            }


            if ($obstaclesIndexes) {

                $map = array();
                $armies = new armies();
                $armies_raw = $armies->initInsideRegion($regionId);

                //1) Sacar los obstaculos de Armies
                foreach ($armies_raw as $key => $army) {
                    $index = $this->mapIndex($army['position_x'], $army['position_y']);
                    $map[] = $index;
                }

                $colonies = new colonies();
                $colonies_raw = $colonies->initActiveByRegionId($regionId);

                //2) Sacar los obstaculos de Colonias
                foreach ($colonies_raw as $keyc => $colony) {
                    $index = $this->mapIndex($colony['region_x'], $colony['region_y']);
                    $arr = $this->getIndexesForColony($index, $colony["type"]);
                    $map = array_merge($map,$arr);
                }            

                //3) Sacar los obstaculos de Colonias
            } else {
    //debug::log('Obstaculo falso');
                $map = $this->emptyMap(100);
                $armies = new armies();
                $armies_raw = $armies->initInsideRegion($regionId);

                foreach ($armies_raw as $key => $army) {
                    $index = $this->mapIndex($army['position_x'], $army['position_y']);
                    $map[$index] = $this->obtainMinimunQuanta();
                }

                $colonies = new colonies();
                $colonies_raw = $colonies->initActiveByRegionId($regionId);
                foreach ($colonies_raw as $keyc => $colony) {
                    $index = $this->mapIndex($colony['region_x'], $colony['region_y']);
                    $arr = $this->getIndexesForColony($index, $colony["type"]);
                    foreach ($arr as $key => $value) {
                        $map[$value] = $this->obtainMinimunQuanta();
                    }

                }            
            }
            $this->obstacle_map = $map;
        }
        return $this->obstacle_map;
    }

    /***********************************************
     * areBattleMapsInitializiated: 
     * ******************************************** */
    public function areBattleMapsInitializiated() {
        $initializiated = true;
        /* if (!array_key_exists ( array(_X_MAPUTIL_TOPMAP,_X_MAPUTIL_BOTTOMMAP,_X_MAPUTIL_LEFTMAP,_X_MAPUTIL_RIGHTMAP) , $this->internal_maps )){
          $initializiated = false;
          } */
        if ( !isBattleMapInited(_X_MAPUTIL_TOPMAP)) {//yes inited dont exist as a word... sue me
            $initializiated = false;
        }
        if ( !isBattleMapInited(_X_MAPUTIL_BOTTOMMAP)) {
            $initializiated = false;
        }

        if ( !isBattleMapInited(_X_MAPUTIL_LEFTMAP)){
            $initializiated = false;
        }
        if ( !isBattleMapInited(_X_MAPUTIL_RIGHTMAP)){
            $initializiated = false;
        }
        if ( !isBattleMapInited(_X_MAPUTIL_CENTRALMAP)){
            $initializiated = false;
        }
        return $initializiated;
    }
    /***********************************************
    * areBattleMapsInitializiated: 
    * ******************************************** */
    public function isBattleMapInited($type) {
        $inited = true;
        if (!array_key_exists($type, $this->internal_maps) || (count($this->internal_maps[$type]) == 0) ) {
            $inited = false;
        }
        return $inited;
    }

    /**     * ********************************************
     * getMaxLoop: Obtiene el maximo de vueltas que se debe de iterar
     * para calcular el mapa central;
     * ******************************************** */
    public function getMaxLoopCenterMap() {
        $sqr = $this->ext_total_x * $this->ext_total_x; //Esto es correcto?
        $sqr = floor(sqrt($sqr) / 2);
//debug::log($sqr,'getMaxLoop maputil');
        return $sqr;
    }
    
//////////////////////////////////////////////////////////////////////////////////
//METODOS BOOLEANOS
//////////////////////////////////////////////////////////////////////////////////  
     public function areTilesObstaculized($indexes){
         $obstaculized = false;
        foreach($indexes as $index){
            if($this->isTileObstaculized($index)){
                $obstaculized = true;
                break;
            }
        }
        return $obstaculized;        
    } 
    
    public function isTileObstaculized($index){
        $arr = $this->obtainObstacleMap();
        if($arr[$index]==0){
            return true;
        }
        else{
            return false;
        }
    } 

//////////////////////////////////////////////////////////////////////////////////
//METODOS UTILITARIOS
//////////////////////////////////////////////////////////////////////////////////  
    /**
      getFirstNonObstacleTile* Obtiene el primer tile libre de acuerdo a la posicion especificada
     * segun el case. Con libre nos referimos a la posicion libre de obstaculos
     * 
     * _X_MAPUTIL_CENTERMAP: Indica que se quiere obtener el primer tile del centro del mapa
     *
     * @return  Integer : El indice del primer elemento libre
     * @since   2011
     * @author  Jeeba <designbyjeeba@gmail.com>
     *
     * @edit    2012-Dic-26<br />
     *          Jeeba <designbyjeeba@gmail.com>
     *          Documentacion y cambio de nombre de la funcion<br/>
     *          #edit2
     */
    
    public function getFirstNonObstacleTile($type) {
        $index = 0;
        switch ($type):
            case _X_MAPUTIL_CENTERMAP:
                $centermap = $this->getWorkingMap(_X_ARMY_SIDE_CENTER);
                $obstaclemap = $this->obtainObstacleMap(false, true); //Obtenemos los indices
                $map = maputil::removeIndexesFromMap($centermap, $obstaclemap);
                $index = key($map);

                /* debug::log($centermap,'mpautil->createRandomPrimaryColony centermap['.count($centermap).']');
                  debug::log($obstaclemap,'maputil->createRandomPrimaryColony obstacle['.count($obstaclemap).']');
                  debug::log($map,'maputil->createRandomPrimaryColony map['.count($map).']');
                  debug::log(key($map),'maputil->createRandomPrimaryColony map['.count($map).']'); */

                break;

            default:
                debug::error('maputil->getFirstTile param type[' . $type . '] no esta siendo calculado, devolvemos 0 por defecto');
                break;
        endswitch;

        return $index;
    }
    
     /***********************************************
     * getIndexesByPath: Obtiene un arreglo con indices dependiendo
     * del PATH que se muestre;
     * ******************************************** */
    public function getIndexesByPath($path, $startIndex, $minrange, $maxrange, $army_id) {
//debug::trace('mautil->getIndexesByPath');
        $result = array();
        switch ($path) {
            case _X_ACTION_PATH_SQUARE:
                $result = array();
                for ($iter = $minrange; $iter <= $maxrange; $iter++) {
                    $temp_indexes = $this->getTilesAtRange($startIndex, $iter);
                    $result = array_merge($result, $temp_indexes);
                }
                break;
            case _X_ACTION_PATH_CROSS:

                $x = $this->getXByIndex($startIndex);
                $y = $this->getYByIndex($startIndex);
                for ($iter = 1; $iter < ($maxrange - $minrange); $iter++) {
                    if ($this->isCoordInBoundaries($x, $y - $iter)) {
                        $result[] = $this->mapIndex($x, $y - $iter);
                    }
                    if ($this->isCoordInBoundaries($x, $y + $iter)) {
                        $result[] = $this->mapIndex($x, $y + $iter);
                    }
                    if ($this->isCoordInBoundaries($x - $iter, $y)) {
                        $result[] = $this->mapIndex($x - $iter, $y);
                    }
                    if ($this->isCoordInBoundaries($x + $iter, $y)) {
                        $result[] = $this->mapIndex($x + $iter, $y);
                    }
                }
//debug::log($result,'maputil getIndexesByPath CROSS');
                break;
            case _X_ACTION_PATH_MOV:

                $obstacles = $this->obtainObstacleMap(false, true);
                $battleutil = new battleutil();
                $army = $this->man('army')->findById($army_id);
//debug::log($army,'maputil->getIndexesByPath');
                $speed = $army->getSpeed();
                $totalMovementPoints = $army->getMovementPoints();
                $counter = $totalMovementPoints;
                $c = 0;
                $brake_counter = 0;
                $globalCounter = array();
                $parentTiles = array($startIndex);
                $max_tiles = $this->getTotalSize();
                while ($counter > 0) {
                    $childrenTiles = $this->getTilesAtRange($startIndex, $c + 1);
                    $childrenTiles = array_diff($childrenTiles, $obstacles);
                    $childrenTiles = array_filter($childrenTiles, function($var) use ($max_tiles) {

                                if ($var < 0) {
                                    return false;
                                } else if ($var >= $max_tiles) {
                                    return false;
                                } else {
                                    return true;
                                }
                            });
// debug::log($childrenTiles,'maputil->getIndexesByPath childrenTiles');    

                    foreach ($childrenTiles AS $i => $children) {
//debug::log($childrenTiles,'ChildrenTiles['.$counter.']');
                        $possibleParents = $this->getTilesAtRange($children, 1);
                        $realParents = array_intersect($possibleParents, $parentTiles);

                        if (count($realParents) == 0) {
//debug::log($childrenTiles[$i],'Deseteamos a:');
                            unset($childrenTiles[$i]);
                        } else {
                            if ($c == 0) {
                                $movementCost = $battleutil->movementCost($speed[($this->ext_map[$children] - 1)]);
                                $movementResidual = $totalMovementPoints - $movementCost;
                                if (($movementResidual >= 0) && ($movementCost > 0)) {
// Si el residuo de movimiento es mayor a cero y el
//costo del movimiento no es cero (no puede moverse
//en un tile con costo cero como el mar)
                                    $globalCounter[$children] = $movementResidual;
                                } else {
//debug::log('['.$children.'] MovementResidual:'.$movementResidual.' MovementCost:'.$movementCost,'C='.$c);
                                    unset($childrenTiles[$i]);
                                }
                            } else {

                                $realParentsValue = $this->obtainCostOfTravel($realParents, $speed);
//debug::log($realParentsValue,'Valor Real Padre');
                                $min = min($realParentsValue);
                                $minKey = array_search($min, $realParentsValue);
                                $parentResidual = $globalCounter[$minKey];

                                $army->getRegionId();
                                $movementCost = $battleutil->movementCost($speed[($this->ext_map[$children] - 1)]);
                                $movementResidual = $parentResidual - $movementCost;
                                if (($movementResidual >= 0) && ( $movementCost > 0 )) {
                                    $globalCounter[$children] = $movementResidual;
                                } else {
//debug::log('parentResidual['.$parentResidual.'] = globalCounter['.$minKey.']');
//debug::log('['.$children.'] MovementResidual:'.$movementResidual.' MovementCost:'.$movementCost,'C='.$c);
                                    unset($childrenTiles[$i]);
                                }
                            }
                        }
//debug::log($childrenTiles,'maputil->actionMov realParents');
                    }

                    $parentTiles = $childrenTiles;


                    $counter = count($parentTiles);
// debug::log($globalCounter,'['.$c.']['.$counter.']ContadorGlobal:');


                    $brake_counter++;
                    $c++;
                    if ($brake_counter > 5) {
                        break;
                    }
                    /* if(empty($minvalue)){
                      break;
                      } */
                }
                $result = array_keys($globalCounter);
                break;

            default:
                debug::error('No se implemento aun el path[' . $path . ']', 'maputil->getIndexesByPath');
        }
        return $result;
    }
    
}

?>