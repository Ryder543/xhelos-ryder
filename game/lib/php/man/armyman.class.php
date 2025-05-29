<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/* * *****************************************************
 * armyman: Clase para el manejo de armies 
 * **************************************************** */

class armyman extends identitymap {

    //////////////////////////////////////////////////////////////////////////////////	
    //CAMPOS
    ///////////////////////////////////////////////////////////////////////////////////
    private static $instance; //Contiene la instancia unica	

    //////////////////////////////////////////////////////////////////////////////////  
    //CONSTRUCTOR
    ///////////////////////////////////////////////////////////////////////////////////   

    protected function __construct() {
        parent::__construct('armyman', 'army', 'armies');
    }

    /*     * ******************************************************************
     * defaultsql: sql por defecto
     * ******************************************************************* */

    public function defaultsql($id) {
        $sql = "SELECT * FROM armies WHERE armies.id=" . $id . " AND armies.state !='" . _X_ARMY_STATE_INACTIVE . "'";
        return $sql;
    }

    /*     * ******************************************************************
     * findByCoord: Encuentrqa una unidad por coordenada
     * ******************************************************************* */

    public function findByCoord($region_id, $x, $y) {
        if (!empty($region_id)) {
            $sql = "SELECT * FROM armies WHERE region_id= " . $region_id . " AND position_x =" . $x . " AND position_y=" . $y . " AND state = '" . _X_ARMY_STATE_ALIVE . "'";
            //debug::log($sql,'army->initByCoord');
            $army_raw = $this->getFromPersistence($sql);
            if (empty($army_raw)) {
                //debug::warning('No existe army en region['.$region_id.'] posicion x['.$x.'] y['.$y.'] ','initByCoord armyman.class');
                return new army();
            } else {
                /*$this->add($army_raw['id'], $army_raw);
                $obj = $this->findById($army_raw['id']);
                return $obj;*/
                $obj = $this->wrap($army_raw);
                return $obj;
            }
        } else {
            debug::error('No se envio parametros para buscar unidades por region y coordenadas', 'initByCoord armyman');
            return new army();
        }
    }

    /*     * ******************************************************************
     * getArmyOrbitingRegion: Obtener Army que esta orbitando la region
     * ******************************************************************* */

    public function getArmyOrbitingRegion($army_id, $region_id) {//[TOD cambiar esto con getArmy]
        $sql = "SELECT * FROM armies WHERE id= " . $army_id . " AND region_id = " . $region_id . " AND next_position = '" . _X_POSITION_ORBIT . "'";
        $army = $this->db->fetch($sql);
        //$army = mysql_fetch_array($army_raw);
        if (empty($army['id'])) {
            return false;
        } else {
            return $army;
            //return $sql;
        }
    }

//////////////////////////////////////////////////////////////////////////////////
//HELPERS DE ACCIONES
//////////////////////////////////////////////////////////////////////////////////
    public function simpleRangeAttackTD($myArmy, $enemyArmy, $report) {
        /* Calculo de la Batalla... debe de objetizarse y mejorarse */
        //Total de Da�o = (myDamage-yourArmor)*mysize;
        $dt = new datatransfer();
        $totEnemyDeads = $enemyArmy->receiveAttack($myArmy->getAttack(), $myArmy->getTimes());
        $report->addEffectUnit($enemyArmy->getId(), $totEnemyDeads);
        $dt->success('Cazamos ' . round($totEnemyDeads) . ' unidad(es) enemigas');
        return $dt;
    }
    public function recoverEnergy($myArmy, $report) {
        $dt = new datatransfer();
    
        $energyRecovered = $myArmy->recoverEnergy();
    
        if ($energyRecovered > 0) {
            $report->addEffectEnergy($myArmy->getId(), $energyRecovered);
            $dt->success("La unidad ha recuperado $energyRecovered puntos de energía.");
        } else {
            $dt->success("La unidad ya tiene la energía al máximo. No se recuperó energía.");
        }
        
        // Indicar que esta acción debe pasar el turno automáticamente
        $dt->setData(array('pass_turn' => true));
    
        return $dt;
    }


    public function simpleAttackTD($myArmy, $enemyArmy, $report) {
        $dt = new datatransfer();
        //$oldEnemySize = $enemyArmy->getSize();//[TEMPO]Por mientras calcularemos
        //las viejas unidades de otra manera
        $totEnemyDeads = $enemyArmy->receiveAttack($myArmy->getAttack(), $myArmy->getTimes());
        //debug::log($totEnemyDeads,'armyman->simpleAttackTD Total de Enemigos Muertos');
        //debug::log($enemyArmy,'armyman->simpleAttackTD Enemigo a Defender');
        $oldEnemySize = $enemyArmy->getSize(); //[TEMPO]Si quisieramos que exista un first strike
        //esta linea debe de estar arriba
        $totMyDeads = $myArmy->receiveAttack($enemyArmy->getAttrition(), $enemyArmy->getAttackQuantity() * $oldEnemySize);

        //Crear Reporte			
        $report->addEffectUnit($myArmy->getId(), $totMyDeads * (-1));
        //$report->addEffectDamage($myArmy->getId(),$damage);
        $report->addEffectUnit($enemyArmy->getId(), $totEnemyDeads * (-1));
        $dt->success('Cazamos ' . $totEnemyDeads . ' unidad(es) enemigas, perdimos ' . $totMyDeads . ' unidad(es)');
        return $dt;
    }

    /*     * ******************************************************************
     * NUEVAS, estas funciones se quedan en la nueva version0.6.5 !!!!!!!!
     * ********************************************************************
      /********************************************************************
     * validateArmy: Valida que exista la unidad en todas estas variables
     * ****************************************************************** */

    public function validateArmy($region_id, $x, $y, $army_id, $player_id, $future=false, $position) {
        //[TODO-Immediatly]Almacenar a este Wey como un ARMY en un arreglo de Armies jeje
        $dt = new datatransfer();
        $army_raw = '';
        if ($position == 'O') {
            $army_raw = $this->getArmyOrbitingRegion($army_id, $region_id);
            if (!isset($army_raw['id'])) {
                $dt->invalidError('La Unidad[' . $army_id . '] no esta orbitando el Region[' . $region_id . ']');
                return $dt;
            }
        } else {
            if ($future) {//Significa que se debe de buscar tambien en futuros movimientos
                $army_raw = $this->validateArmyInMov($army_id, $region_id, $x, $y);

                //Si la Unidad esta orbitando, esto no es necesario

                if (!isset($army_raw['id'])) {
                    $dt->invalidError('No existe unidad moviendose en Region[' . $region_id . '], X[' . $x . '],Y[' . $y . ']');
                    return $dt;
                }
            } else {//No busca en Futuros Movimientos next_y y next_x
                $army_raw = $this->getArmyInCoord($region_id, $x, $y);
                if (!isset($army_raw['id'])) {
                    $dt->invalidError('No existe unidad en Region[' . $region_id . '], X[' . $x . '],Y[' . $y . ']');
                    return $dt;
                }
            }
        }

        if ($army_id != $army_raw['id']) {
            $dt->invalidBug('El Id[' . $army_id . '] no corresponde con la unidad[' . $army_raw['id'] . ']', 'armyman->validateArmy');

            return $dt;
        }
        if ($player_id != $army_raw['player_id']) {
            $dt->invalidBug('El Id[' . $army_id . '] no corresponde con el jugador[' . $army_raw['id'] . ']', 'armyman->validateArmy');
            return $dt;
        }

        $army = new army(0, 0, 0, 0, 0);
        $army->init($army_raw);
        $dt->validSuccess('Unidad Validada', $army);
        return $dt;
    }

    /*     * ********************************************************************
     * validateArmyInMov: Obtiene la Coordenada de alguna unidad
     * ******************************************************************** */

    //LLamarlo validateArmyInMov, no se pueden obtener posiciones si el target esta orbitando
    public function validateArmyInMov($army_id, $region_id, $x, $y) {
        if (!isset($region_id) || !isset($x) || !isset($y)) {
            debug::error('Parametros no existen', 'armyman->validateArmyInMov');
            return false;
        }
        $army_raw = $this->getArmy($army_id);
        if (empty($army_raw['id'])) {
            //return $sql;
            return false;
        }
        if (($army_raw['position_x'] != $x) || (($army_raw['position_y'] != $y))) {
            if (($army_raw['next_x'] != $x) || (($army_raw['next_y'] != $y))) {
                return false;
            } else {
                return $army_raw;
            }
        } else {
            return $army_raw;
        }
    }

    //////////////////////////////////////////////////////////////////////////////////	
    //METODOS PRINCIPALES ARMIES ( Varios Army, para la clase armies )
    ///////////////////////////////////////////////////////////////////////////////////

     /*********************************************************************************************************
     * getArmiesByPlanetIdOrderByRegionId: Obtiene los armies activos en el planeta oirdenados por region
     * ************************************************************************************************** */   
/*  public function getArmiesByPlanetIdOrderByRegionId($planetId){
        $sql = "SELECT * FROM armies WHERE region_id IN (SELECT id FROM regions WHERE regions.planet_id = " . $planetId . ") ORDER BY id";
        $armies_raw = $this->db->sql2group($sql,'region_id');
        return $armies_raw;
    }*/
    
    /* * ********************************************************************
     * getArmiesInAxis: Obtiene la Coordenada de alguna unidad
     * ******************************************************************** */

    public function getArmiesInFrontOfArmy($army, $region_id, $target_x, $target_y) {
        $target_x = (int) $target_x;
        $target_y = (int) $target_y;
        //debug::log('army[] regionId['.$region_id.'] targetX['.$target_x.'] targetY['.$target_y.']');
        //debug::log('typeof: regionId['.gettype($region_id).'] targetX['.gettype($target_x).'] targetY['.gettype($target_y).']');
        $coord = $army->getCoord();
        $init_x = $coord['position_x'];
        $init_y = $coord['position_y'];
        $sql = '';
        if ($init_y == $target_y) {//Entonces estan en la misma fila
            $start = 0;
            $finish = 0;
            $value = $init_y;

            if ($init_x < $target_x) {//Si el ataque es hacia la izquierda
                $start = $init_x + 1;
                $finish = $target_x;
            } else {//Si el ataque es hacia la derecha
                $start = $target_x;
                $finish = $init_x - 1;
            }
            $sql = "SELECT * FROM armies WHERE position_y = $value AND (position_x>=$start AND position_x<=$finish) AND region_id = $region_id";
        } elseif ($init_x == $target_x) {//Entonces estan en la misma columna
            $start = 0;
            $finish = 0;
            $value = $init_x;

            if ($init_y < $target_y) { // Si es hacia arriba
                $start = $init_y + 1;
                $finish = $target_y;
            } else {// Si es hacia abajo
                $start = $target_y;
                $finish = $init_y - 1;
            }
            $sql = "SELECT * FROM armies WHERE position_x = $value AND (position_y>=$start AND position_y<=$finish) AND region_id = $region_id";
            //global $firephp;
            //$firephp->log($sql);
        } else {
            debug::error('La unidad y la accion no estan en el mismo AXIS', 'armyman->getArmiesInFrontOfArmy');
        }
        //debug::log($sql,'armyman->getArmiesInFrontOfArmies sql');
        return $this->db->sql2array($sql);
    }

    //////////////////////////////////////////////////////////////////////////////////  
    //UTILITARIOS
    ///////////////////////////////////////////////////////////////////////////////////   	
    /*     * ******************************************************************
     * Singleton, devuelve la instancia del singleton
     * ******************************************************************* */
    public static function singleton() {//Obtiene la instancia unica de esta clase
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }
        return self::$instance;
    }

    /*     * ******************************************************************
     * Clone: Prohibe que algun sapazo clone un singleton
     * ******************************************************************* */

    public function __clone() { // Prevenimos que este objeto sea clonado
        trigger_error('Clone is not allowed. db_pg_class', E_USER_ERROR);
    }

}

//army
?>