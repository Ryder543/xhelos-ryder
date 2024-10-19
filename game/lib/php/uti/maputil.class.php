<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/* * *****************************************************
 * maputil: Clase Utilitaria para Mapas 
 * **************************************************** */

class maputil extends util {

//[TODO]El reset se esta llamando muchas veces, deberiamos tener una funcion
//setObjectParam('position','P') por ejemplo que actualize la accion
//////////////////////////////////////////////////////////////////////////////////
//Variables
///////////////////////////////////////////////////////////////////////////////////
//Variables Internas
    protected $ext_map;
    protected $ext_total_x;
    protected $ext_total_y;
    protected $ext_region_id;
    protected $maxSize;
    protected $max_range;
    protected $difusion;

///////////////////////////////////////////////////////////////////////////////////////
//CONSTRUCTOR
///////////////////////////////////////////////////////////////////////////////////////
    /*     * ******************************************
     * Constructor: De maputil
     * Params:
     * $map: Mapa Original con el que se quiere trabajar
     * ****************************************** */
    public function __construct($map, $total_x, $total_y, $region_id) {

//debug::trace('maputil->construct');
        $this->ext_map = $map;
        $this->ext_total_x = $total_x;
        $this->ext_total_y = $total_y;
        $this->ext_region_id = $region_id;
        $this->max_range = max($this->getTotalX(), $this->getTotalY()); //MAX RANGE
        $max = $this->obtainMaximunRange() + 1; //Si hay 1 rango de distancia, el 
        $this->difusion = floor(($this->obtainMaximunQuanta() / $max)); //DIFUSION de cuanto en cuanto disminuye el cuanta 

        parent::__construct();
    }

///////////////////////////////////////////////////////////////////////////////////////
//PRINCIPALES
///////////////////////////////////////////////////////////////////////////////////////
    public function calculateDiffusion() {
//$max = $this->obtainMaximunRange() + 1; //Si hay 1 rango de distancia, el
//valor se dividira entre 2
//$value = floor( ($this->obtainMaximunQuanta() / $max) );
        return $this->difusion;
    }

    /*     * ************************************************************************
     * obtainMaximunQuanta: El maximo valor que tendra un mapa
     * ************************************************************************ */

    public function obtainMaximunQuanta() {
        return 100;
    }

    /*     * ************************************************************************
     * obtainMinimunQuanta: El minimo valor que tendra un mapa
     * ************************************************************************ */

    public function obtainMinimunQuanta() {
        return 0;
    }

    /*     * ************************************************************************
     * obtainMaximunRange: Obtenemos el rango maximo que tendra los campos
     * potenciales para actuar. Por ahora se tendra en cuenta cual es la
     * mayor distancia a calcular para que un campo potencial afecte a todo
     * el mapa
     * ************************************************************************ */

    public function obtainMaximunRange() {
        return $this->max_range;
    }

    /*     * *********************************************
     * debug: Imprime las variables que tenga
     * ******************************************** */

    public function debug() {
        global $firephp;
        $firephp->group('Mapas de Batalla');
        debug::info($this->ext_map, 'debug maputil');
        debug::info($this->ext_total_x, 'total_x debug maputil');
        debug::info($this->ext_total_y, 'total_y debug maputil');
        $firephp->groupEnd();
    }

    /*     * *********************************************
     * mapIndex: Obtiene el indice usando coordenadas
     * para calcular el mapa central;
     * ******************************************** */

    public function mergeMap($mapA, $mapB, $respectZero) {
        $newmap = array();
        foreach ($mapA as $key => $tile) {
            if ($respectZero) {
                if (($mapA[$key] == 0) || ($mapB[$key] == 0)) {
                    $newmap[$key] = 0;
                } else {
                    $newmap[$key] = $mapA[$key] + $mapB[$key];
                }
            } else {
                $newmap[$key] = $mapA[$key] + $mapB[$key];
            }


            if ($newmap[$key] != 0) {
                $newmap[$key] = round($newmap[$key] / 2);
            }
        }
//debug::log($newmap,'maputil mergeMap');
        return $newmap;
    }

    function mapIndex($x, $y) {
//debug::log('x['.$x.']y['.$y.'] totaly['.$this->ext_total_y.'] (y*totaly)+(x+1)-1 ','mapIndex maputil');
        $index = ($y * $this->ext_total_x) + $x; //(4*10)+4;
        return $index;
    }

    static function index($x, $y, $total_x) {
        $index = ($y * $total_x) + $x; //(4*10)+4;
//debug::log('x['.$x.']y['.$y.'] totalx['.$total_x.'] (y*totalx)+x = '.$index,'mapIndex maputil');
        return $index;
    }

    function getRegionId() {
        return $this->ext_region_id;
    }

    function getTotalX() {
        return $this->ext_total_x;
    }

    function getTotalY() {
        return $this->ext_total_y;
    }

    function obtainZeroMap($original, $tozero) {
        foreach ($original as $index => $tile) {
            if ($tozero[$index] == 0) {
                $original[$index] = 0;
            }
        }
        return $original;
    }

    /*     * ********************************************
     * getXByIndex: Obtenemos x a partir del indice ingresado
     * ******************************************** */

    protected function calculateXByIndex($index) {
        $index++; //Indice especial para este caso
        $residuo = modulo($index, $this->getTotalX());
        if ($residuo > 0) {
            $resp = $residuo - 1;
        } else {
            $resp = ($this->getTotalX() - 1);
        }
        //debug::warning($resp,' getXByIndex Indice['.$index.'] X:');
        return $resp;
    }

    /*     * ********************************************
     * getYByIndex: Obtenemos y a partir del indice ingresado
     * ******************************************** */

    protected function calculateYByIndex($index) {
        $index++; //Indice especial para este caso
        $residuo = modulo($index, $this->getTotalX());
        if ($residuo > 0) {
            $resp = floor($index / $this->getTotalX());
        } else {
            $resp = floor($index / $this->getTotalX()) - 1;
        }
        //debug::warning($resp,'getYByIndex Indice['.$index.'] Y:');
        return $resp;
    }

    function getTotalSize() {
        if (!$this->maxSize) {
            $this->maxSize = $this->ext_total_x * $this->ext_total_y;
        }
        return $this->maxSize;
    }

    function emptyMap($data) {
        return array_fill(0, $this->getTotalSize(), $data);
    }

    function obtainMaxNumberOfTerrain() {
        return _X_TERRAIN_MAX;
    }

    function obtainMaxIndexes($map) {
        $max = max($map);
        $indexes = array();
        foreach ($map as $index => $tile) {
            if ($tile == $max) {
                $indexes[] = $index;
            }
        }
        return $indexes;
    }

    function obtainMinIndexes($map) {
        $min = min($map);
        $indexes = array();
        foreach ($map as $index => $tile) {
            if ($tile == $min) {
                $indexes[] = $index;
            }
        }
        return $indexes;
    }

    /*     * ************************************************
     * fillPotentialField: Llena un punto con campos
     * potenciales
     * *********************************************** */

    function fillPotentialField($map, $index, $respectZero = false, $maxradio = false) {
        if (!$maxradio) {
            $maxradio = $this->obtainMaximunRange();
        }
        $diffusion = $this->calculateDiffusion();
        for ($radio = 1; $radio <= $maxradio; $radio++) {
            $ranges = $this->getTilesAtRange($index, $radio);

            $value = $diffusion * ( ($maxradio + 1) - $radio );
            if ($value == 90) {
//debug::log($ranges,'maputil->fillPotentialField ranges index['.$index.'] value['.$value.'] radio['.$radio.'] ');
            }
//$value = round(( $map[$index] / ($maxradio + 1))) * ( ($maxradio + 1) - $radio );
//debug::log($value,'maputil->fillPotentialField value');
            /* if( ($value = 90) && ($index == 28) ) {
              debug::trace('maputil fillPotentialField de 90 con indice de 28');
              } */

            foreach ($ranges as $range) {
                if ($map[$range] < $value) {
                    if ($respectZero) {
                        if ($map[$range] != 0) {
                            $map[$range] = $value;
                        }
                    } else {
                        $map[$range] = $value;
                    }
                }
            }
        }
        return $map;
    }

    /*     * ************************************************
     * promediate: LLena un mapa promediando tomando
     * en cuenta este maximo nivel
     * $max : entero maximo para el ataque
     *
     * *********************************************** */

    function promediate($map, $max, $respectZero = true, $debug = false) {
        $map_maximun = max($map);

        if ($debug) {
            debug::log($map, 'maputil->promediate');
        }

        foreach ($map as $index => $tile) {
            if ($respectZero) {
                if ($tile == 0) {
                    $map[$index] = 0;
                } else {
                    $resp = round(($tile * $max) / $map_maximun);
                    if ($debug) {
                        debug::log('round(' . $tile . '+' . $max . ')/' . $map_maximun, 'maputil->promediate [' . $index . ']:' . $resp);
                    }

                    $map[$index] = $resp;
                }
            } else {
                $resp = round(($tile * $max) / $map_maximun);
                $map[$index] = $resp;
            }
        };
        return $map;
    }

    public function add($mapA, $mapB, $respectZero = true) {
        $newmap = array();
        foreach ($mapA as $key => $tile) {
            if ($respectZero) {
                if (($mapA[$key] == 0) || ($mapB[$key] == 0)) {
                    $newmap[$key] = 0;
                } else {
                    $newmap[$key] = $mapA[$key] + $mapB[$key];
                }
            } else {
                $newmap[$key] = $mapA[$key] + $mapB[$key];
            }
        }
        return $newmap;
    }

    /*     * ************************************************
     * fillMapWithValue: Llena un  mapa con ceros o
     * el valor elegido si esta contenido en los indices
     * indicados en el arreglo
     * *********************************************** */

    function fillMapWithValue($indexes, $value) {
        $newmap = array();
        for ($i = 0; $i < $this->getTotalSize(); $i++) {
            if (in_array($i, $indexes)) {
                $newmap[$i] = $value;
            } else {
                $newmap[$i] = 0;
            }
        }
        return $newmap;
    }

    /*
      getTilesAtRange  La funcion mas importante de maputil. Obtiene los indices
     * de los cuadrados que esten a cierto rango del indice indicado. Podemos 
     * calcular estos indices con overflow, es decir, si lo que buscamos se sale
     * del mapa entonces toma el siguiente valor al otro lado del mapa
     *  como si no fuera un mapa si no un mundo redondo y unido por sus edges
     * 
     * @param Integer $indice El indice alrededor del cual queremos calcular que tiles
     * estan cerca
     * @param Integer $range  El rango de distancia alrededor del cual queremos 
     * calculas los tiles. Si es cero entonces es el indice, si es uno entonces son
     * los 8 cuadrados adjuntos 
     * @param Boolean $overflow Indicamos si queremos calcular los indices que se
     * salen de sus edges
     *
     * @return  Array : Con los indices alrededor de nuestro indice
     * @since   2011
     * @author  Jeeba <designbyjeeba@gmail.com>
     *
     * @edit    2012-Dic-30<br />
     *          Jeeba <designbyjeeba@gmail.com>
     *          Documentacion Inicial<br/>
     *          #edit1
     */
    function getTilesAtRange($indice, $rango, $overflow = false) {

        if ($rango == 0) {
            $indices = array();
            $indices[] = $indice;
            return $indices;
        } else {

//$indice++;
            $x = $this->getTotalX();
            $y = $this->getTotalY();
            $total = $this->getTotalSize();

//debug::log('index:'.$indice. ' x:'.$x.' y:'.$y,'maputil->getTilesatRange');
//a) Obtener el primer indice segun el rango
            $restar = $rango * ($x + 1);
//42 = 2 * (20+1)
            $primer = $indice - ( $rango * ($x + 1) );
            /* if( ($indice == 29) && ($rango == 1)  ){
              debug::log('index:'.$indice. ' x:'.$x.' y:'.$y,'maputil->getTilesatRange');
              debug::log('restar['.$restar.'] = rango['.$rango.'] * (x['.$x.'] + 1)');
              debug::log('primer['.$primer.'] = indice['.$indice.'] - restar['.$restar.']');
              } */
//
//--21  =  21 -  42
            $ultimo = $primer; //Variable que mantiene el ultimo indice puesto, a pesar que no exista en el arreglo por overlfow
//debug::log($ultimo,'maputil->getTilesAtRange ultimo primer');

            $indices = array();
            $rangoYInferior = -1; //Porque -1? Esto es para evitar el BUG donde en un mapa de 30*30 , si se quiere
//obtener el ID del elemento 29, entonces el primer elemento sera -2 con y[-1], despues -1 con  y[-1]
//Y finalmente 0 con [y] = 0 que cuadra con el anterior rangoYInferior = 0,
//rangoYinferior 1 -1 =0;
//debug::warning($rangoYInferior,'Rango Y Inferior Indice['.$indice.'] = y['.$this->getYByIndex($indice).']-rango['.$rango.']');

            if ($primer >= 0) { //No primer si es negativo, no puede existir un indice negativo!
                $rangoYInferior = ($this->getYByIndex($indice) - $rango);

                if (!$overflow) {
                    if ($rangoYInferior == $this->getYByIndex($primer)) {

                        $indices[] = $primer;
//debug::console($indices,'Rango Correcto Indice['. $primer.']');
                    }
                } else {
                    $indices[] = $primer;
                }
            }

////////////////////////////////////////////////////////////////////////
//VARIABLES COMUNES
////////////////////////////////////////////////////////////////////////
            $dobleRango = 2 * $rango;

/////////////////////////////////////////////////////////////////////////////
//b) Obtener la primera fila de valores
/////////////////////////////////////////////////////////////////////////////
            $nuevo_indice = 0;
//$maxBucleTemp = $rango * 2;
            for ($i = 1; $i <= $dobleRango; $i++) {
//e)Evitar Overflow
                $nuevo_indice = $ultimo + $i;
                if (($nuevo_indice >= 0) && ($nuevo_indice < $total)) {
                    if (!$overflow) {

                        /* if($nuevo_indice <= 0){
                          debug::log('RangoInferiorY['.$rangoYInferior.'] NuevoIndice['.$nuevo_indice.'] IndiceYdeNuevoIndice['.$this->getYByIndex($nuevo_indice).']');
                          } */

                        if ($rangoYInferior == $this->getYByIndex($nuevo_indice)) {
                            $indices[] = $nuevo_indice;
//debug::console($indices,'Rango Correcto Inicio['. $primer.']');
//::console('Rango Correcto Indice['.$nuevo_indice.']');
                        } else {
//debug::console('Rango Incorrecto Indice['.$nuevo_indice.']');
                        }
                    } else {
                        $indices[] = $nuevo_indice;
                    }
                }
            }
            $ultimo = $nuevo_indice; //Grabamos al ultimo indice calculado
//debug::log($indices,'maputil->getTilesAtRange BeforeLados');
///////////////////////////////////////////////////
//c) Calcular los lados
///////////////////////////////////////////////////
//debug::log($ultimo,'maputil->getTilesAtRange ultimo primer');
            $intercalador = true;
            $maxBucleTemp = 4 * $rango - 1;
            $xByIndexTemp = $this->getXByIndex($indice);
            $rangoXInferior = $this->getXByIndex($indice) - $rango;
            $rangoXSuperior = ($this->getXByIndex($indice) + $rango);
            $restaInferior = $x - ($dobleRango);
//$sumaSuperior = 2 * $rango;

            for ($i = 1; $i <= $maxBucleTemp; $i++) {
                if ($intercalador) {
                    $nuevo_indice = $ultimo + $restaInferior; // -17 + 15-(2*2)= ;
                    if (($nuevo_indice >= 0) && ($nuevo_indice < $total)) {//No calcular nada si el indice es menor a cero o mayor a $total de tiles
//debug::console($nuevo_indice,'maputil->getTilesAtRange Intercalado');
                        if (!$overflow) {//Calculamos si lo hacemo con overflow
//$rangoXInferior = ($this->getXByIndex($indice) - $rango);
                            if ($rangoXInferior >= 0) {
                                $indices[] = $nuevo_indice;
//debug::console('Rango Medio Inferior['.$rangoXInferior.'] Correcto Indice['.$nuevo_indice.']');
                            }
                        } else {
                            $indices[] = $nuevo_indice;
//$ultimo = $ultimo + $y-(2*$rango);
                        }
                    }
                    $ultimo = $nuevo_indice;
                    $intercalador = false;
                } else {
                    $nuevo_indice = $ultimo + $dobleRango;
                    if (($nuevo_indice >= 0) && ($nuevo_indice < $total)) {
                        if (!$overflow) {//Calculamos si lo hacemo con overflow
                            if ($rangoXSuperior < $x) {
                                $indices[] = $nuevo_indice;
//debug::console('Rango Medio Superior['.$rangoXSuperior.'] Correcto Indice['.$nuevo_indice.']');
                            }
                        } else {
                            $indices[] = $nuevo_indice;
                        }
                    }
                    $ultimo = $nuevo_indice;
                    $intercalador = true;
                }
            }

//debug::log($indices,'maputil->getTilesAtRange BeforeBottom');
///////////////////////////////////////////////////////////////////////////////
//d) Calcular lo que sobre de lados, que es el lado de abajo del cuadrado
/////////////////////////////////////////////////////////////////////////////
            $rangoYSuperior = ($this->getYByIndex($indice) + $rango);
            for ($i = 1; $i <= ($dobleRango); $i++) {
                $nuevo_indice = $ultimo + 1;
                if ($nuevo_indice < $total) {
                    if (!$overflow) {
                        if ($rangoYSuperior == $this->getYByIndex($nuevo_indice)) {
//debug::console('Rango Superior Correcto['.$rangoYSuperior.'] Indice['. $nuevo_indice.']');
                            $indices[] = $nuevo_indice;
                        } else {
//debug::console('Rango Superior Incorrecto['.$rangoYSuperior.'] Indice['. $nuevo_indice.']');
                        }
                    } else {
                        $indices[] = $nuevo_indice;
                    }
                }
                $ultimo++;
            }
/////////////////////////////////////////////////////////////////////////////
//e) Overflow
/////////////////////////////////////////////////////////////////////////////
//debug::log($indices,'maputil->getTilesAtRange preoverflow');
            /*
              if (!$overflow) {
              $total = $this->getTotalSize();
              //debug::log($total,'maputil->getTilesAtRange');
              foreach ($indices as $key => $data) {
              if ($data < 0) {
              //debug::log('Hemos unseteado indices['.$key.']'.$data.' porque es menor a 0');
              unset($indices[$key]);

              }
              if ($data > $total) {

              unset($indices[$key]);
              //debug::log('Hemos unseteado indices['.$key.']'.$data.' porque es mayor a '.$total);
              }
              }
              } */
//debug::log($indices,'maputil->getTilesAtRange');
//debug::log($indices,'getTilesARange indice['.$indice.'] rango['.$rango.'] overflow['.$overflow.']');
            return $indices;
        }
    }



    public function obtainCostOfTravel($indexes, $speed) {
        $resp = array();
        $battleutil = new battleutil();
        foreach ($indexes AS $index) {
            /* if($index == 147){

              } */
            $value = $battleutil->movementCost($speed[($this->ext_map[$index] - 1)]);
            $resp[$index] = $value;
        }
        return $resp;
    }

//////////////////////////////////////////////////////////////////////////////////
//METODOS BOOLEANOS
//////////////////////////////////////////////////////////////////////////////////  
    public function isCoordInBoundaries($x, $y, $overflow = false) {

//        $index = $this->mapIndex($x, $y);
//        if($index == 331){
//                    debug::log('maputil->isCoordInBoundaries X['.$x.'] >totalX['.$this->getTotalX().'] && Y['.$y.'] > totalY['.$this->getTotalY().']');
//        }

        if (($x < 0) || ($y < 0) || ($x >= $this->getTotalX()) || ($y >= $this->getTotalY())) {
            return false;
        } else {
            if (!$overflow) {

//                //$index = $this->mapIndex($x, $y);
//                if($index == 330){
//                    debug::log('maputil->isCoordInBoundaries X['.$x.'] >totalX['.$this->getTotalX().'] && Y['.$y.'] > totalY['.$this->getTotalY().']');
//                }
//                elseif($index == 331){
//                  debug::log('maputil->isCoordInBoundaries X['.$x.'] >totalX['.$this->getTotalX().'] && Y['.$y.'] > totalY['.$this->getTotalY().']');  
//                }
                return true;
            } else {
                return true;
            }
        }
    }

    public function isIndexNearBorder($index, $range) {
        if ($range == 0) {
            return false;
        } else {
            $x = $this->calculateXByIndex($index);
            $y = $this->calculateYByIndex($index);
            //Si x esta cerca al borde izquierdo o si esta cerca al borde derecho
            if (($x <= ($range-1) ) || ($x >= ($this->getTotalX() - $range) )) {
                return true;
            } elseif (($y<= ($range-1) ) || ($y >= ( $this->getTotalY()-$range ))  ) {
                return true;
            }
            else{
                return false;
            }
        }
    }
    
    
    public function areIndexesNearBorder($indexes,$range){
        $nearBorder = false;
        foreach ($indexes as $index){
            if($this->isIndexNearBorder($index, $range)){
                $nearBorder = true;
                break;
            }
            
            
        }
        return $nearBorder;
    }

//////////////////////////////////////////////////////////////////////////////////
//METODOS UTILITARIOS
//////////////////////////////////////////////////////////////////////////////////  
    public static function removeIndexesFromMap($map, $indexes) {
        foreach ($map as $i => $tile) {
            foreach ($indexes as $o => $value) {
                if ($i == $value) {
                    unset($indexes[$o]);
                    unset($map[$i]);
                }
            }
        }
        return $map;
    }

    /*     * ****************************************************
     * Funciones Estaticas
     * *************************************************** */

    public static function mapUtilSides() {
        $sides = array(1 => _X_LEFT, 2 => _X_RIGHT, 3 => _X_TOP, 4 => _X_BOTTOM);
        return $sides;
    }

    public static function mapUtilSideByNumber($number) {
        $sides = maputil::mapUtilSides();
        if ($number <= count($sides)) {
            return $sides[$number];
        } else {
            $i = $number % count($sides);
            return $sides[$i];
        }
    }

}

?>