<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/* * *****************************************************
 * actionman: Clase que maneja las acciones en el juego. No solo
 * las acciones si no la mezcla de accion con armies. Osea la accion
 * que le pertenece a un army 
 * **************************************************** */

class actionman extends identitymap {

    //////////////////////////////////////////////////////////////////////////////////
    //CONSTRUCTOR
    ///////////////////////////////////////////////////////////////////////////////////
    protected function __construct() {
        $this->armyman = armyman::singleton();
        parent::__construct('actionman', 'action','actions');
    }

    private $armyman;
    private $report;
    private $temp;
    private $actionTemp;
    private static $instance; //Contiene la instancia unica de la clase

    //////////////////////////////////////////////////////////////////////////////////
    //Metodos
    //////////////////////////////////////////////////////////////////////////////////

    public function getTemp() {
        return $this->temp;
    }

    /*     * *******************************************************************************
     * initTemp: Inicializa un arrglo temporal
     * ******************************************************************************* */

    public function initTemp($battle_id, $region_id, $action_id, $ox, $oy, $tx, $ty) {
        $this->temp = array();
        $this->temp['battle_id'] = $battle_id;
        $this->temp['region_id'] = $region_id;
        //$this->temp['player_id'] = $player_id;
        $this->temp['action_id'] = $action_id;
        $this->temp['ox'] = $ox;
        $this->temp['oy'] = $oy;
        $this->temp['tx'] = $tx;
        $this->temp['ty'] = $ty;
    }

    /*     * **************************************************************************************************
     * validateTempDT: Devuelve un datatransfer indicando si esta accion es valida con la undiad y target
     * ************************************************************************************************* */

    public function validateTempDT($army, $action_id) {
        $dt = new datatransfer();
        $dt->success('El Target es correcto');
        $ok = true;
        //1)Verificar que la accion exista
        $sql = "SELECT * FROM actions WHERE id = " . $action_id . " AND state = '" . _X_ACTION_STATE_ACTIVE . "'";
        $action_raw = $this->db->fetch($sql);
        if (empty($action_raw)) {
            //debug::warning('La accion con id['.$action_id.'] no existe o esta [I]nactiva','actionman->finByActionIdAndArmyId');
            $dt->error('La accion con id[' . $action_id . '] no existe');
            $ok = false;
        }

        //2)Verificar que le pertenezca a la unidad
        $this->actionTemp = $this->findByActionIdAndArmyId($army->getId(), $action_id);
        if (($ok) AND !( $this->actionTemp->exist())) {
            //debug::warning('La accion con id['.$action_id.'] no existe en el army['.$army->getId().']','actionman->validateTempTD');
            $dt->error('La accion con id[' . $action_id . '] no existe en el army[' . $army->getId() . ']');
            $ok = false;
        }
        //3)Verificar que el target sea el correcto
        $target_type = $this->actionTemp->getTarget();
        $position_type = $this->actionTemp->getPosition();
        //debug::warning($target,'actionman->validateTempDT->target');
        $target = new target($this->temp['region_id'], $action_id, $army->getX(), $army->getY(), $this->temp['tx'], $this->temp['ty']);
        //debug::warning($target,'actionman->validateDT->target');
        $dt_target = $target->typeIsValid($target_type, $position_type);
        if (($ok) AND !($dt_target->isValid())) {
            $text = $dt_target->getText();
            //debug::warning($text,'actionman->validateDT');
            $dt = $dt_target;
            //debug::warning($dt,'actionman->validateDT->dt_target');
            //debug::warning($dt,'actionman->validateDT->dt');
            $ok = false;
        }
        return $dt;
    }

    public function defaultsql($id) {
        /* $sql = "SELECT ar.army_id,ar.action_id,ar.id,ar.order,ar.state,ac.name,ac.path,"
          ." ac.desc,ac.wait,ac.movable,ac.precancel,ac.postcancel,ac.defendable,ac.energy,"
          ." ac.position,ac.target FROM armies_actions AS ar"
          ." INNER JOIN actions AS ac ON (ar.action_id = ac.id) WHERE ar.id = ".$id; */
        //debug::log($sql);
        $sql = "SELECT * FROM actions WHERE id =" . $id;
        return $sql;
    }

    public function findByActionIdAndArmyId($army_id, $action_id) {
        $sql = "SELECT ar.army_id,ar.action_id,ar.id,ar.`order`,ar.state,ac.name,ac.path,"
                . " ac.desc,ac.wait,ac.movable,ac.precancel,ac.postcancel,ac.defendable,ac.energy,"
                . " ac.position,ac.target FROM armies_actions AS ar"
                . " INNER JOIN actions AS ac ON (ar.action_id = ac.id)"
                . " WHERE ar.army_id = " . $army_id
                . " AND ac.id = " . $action_id
                . " AND ar.state = '" . _X_ACTION_STATE_ACTIVE . "' ORDER BY ar.order ";
        //debug::warning($sql,'actionman->finByActionIdAndArmyId');
        $temp = $this->db->fetch($sql);
        $action = $this->wrap($temp);

        return $action;
    }

    public function saveTempActionTD($army_id) {
        $m = $this->temp;
        //$mov = pg_escape_string(utf8_encode(json_encode($this->getReport()->getRaw())));
        //$mov = mysqli_real_escape_string($this->getAccess(), utf8_encode(json_encode($this->getReport()->getRaw())));
        $mov = mysqli_real_escape_string(
            $this->db->getConnection(),
            mb_convert_encoding(json_encode($this->getReport()->getRaw()), 'UTF-8', 'auto')
        );
        


        $id = $this->db->nextId('movements');
        //debug::warning($mov,'actionman->saveTempActionTD.mov');
        $sql = "INSERT INTO movements(id,region_id,army_id,x_in,y_in,x_out,"
                . "y_out,time_in,action_id,state,movement_raw,battle_id,info) VALUES ("
                . $id . "," . $m['region_id'] . "," . $army_id . ","
                . $m['ox'] . "," . $m['oy'] . ","
                . $m['tx'] . ","
                . $m['ty'] . ",now(),"
                . $m['action_id'] . ",'"
                . _X_ACTIVE . "','" . $mov . "'," . $m['battle_id'] . ",'" . $m['info'] . "');";
        $this->db->query($sql);
        //debug::warning($m,'actionman->saveTempActionTD->m');
        //debug::warning($sql,'actionman->saveTempActionTD->s');
    }

    public function workTempActionDT() {
        debug::trace('actionman->workTempActionTD->traza');
        //$m = $this->temp; //Arreglo que contiene las variables temporales de mov
        //$a = $this->actionTemp->getRaw();
        $m = array_merge($this->temp, $this->actionTemp->getRaw());
        $action_id = $this->temp['action_id'];
        $this->report = new report();
        $originArmy = $this->armyman->findBycoord($m['region_id'], $m['ox'], $m['oy']);
        $enemyArmy = $this->armyman->findByCoord($m['region_id'], $m['tx'], $m['ty']);
        $dt = new datatransfer();
        $dt->success();


        /*         * **************************************************
         * Movimientos Globales
         * ************************************************** */
        //A)GLOBAL MOVIBLE:Si es Movible, mover las unidad
        //debug::info($this->actionTemp,'actionman->workTempActionTD->dt before global actionTemp');
        //debug::info($m,'actionman->workTempActionTD->dt before global m hay movable');
        if ($m['movable'] == _X_SI) {
            //echo "ES recontra movible";
            if ($enemyArmy->exist()) {//[TODO]Exista en Coordenadas
                $dt->error('Unidad[' . $m['army_id'] . '] esta ocupando el territorio');
                //debug::info($dt,'actionman->workTempActionTD->dt inside global error unidad ocupa sitio');
            } else {
                if (!$originArmy->isInPosition(_X_POSITION_ORBIT) || $origin->isInPosition(_X_POSITION_ATERRIZAR)) {//No se pueden mover unidades en el espacio
                    $originArmy->setCoord($m['tx'], $m['ty']);
                    //debug::info($dt,'actionman->workTempActionTD->dt inside global success se movio');
                } else {
                    //Se esta moviendo una unidad en el espacio
                    $origin->setActualAndNextCoord(_X_MOV_NOMOV, _X_MOV_NOMOV, _X_MOV_NOMOV, _X_MOV_NOMOV);
                    $dt->$error('La Unidad esta en orbita');
                    //debug::info($dt,'actionman->workTempActionTD->dt inside global unidad esta en el espacio');
                    //[TODO] Dar Error de que no se puede mover una unidad en el espacio;
                }
            }
        }

        //debug::info($dt,'actionman->workTempActionTD->dt after global');

        /*         * **************************************************
         * Movimientos Particulares
         * ************************************************** */
        if ($dt->hasSuccess()) {
            switch ($action_id) {

                case 1://Mover la Unidad, army_id,x_in,y_in,x_out,y_out

                    $this->report->addCauseMovement($originArmy->getId(), $m['ox'], $m['oy']);
                    $dt->success('El Army procede a moverse', 'success');
                    //debug::info($dt,'actionman->workTempActionTD->dt after mov[1]');
                    //$report->addCauseMovement($report_id,$m['army_id'],$m['x_out'],$m['y_out']);
                    //$report->addMessage($report_id,"La unidad se movio a x:".$m['x_out']." y:".$m['y_out']);
                    break;

                case 2://Atacar Unidad

                    if (!$enemyArmy->exist()) {
                        $dt->error('Criatura Enemiga no existe en posicion [' . $m['x_out'] . '][' . $m['y_out'] . ']');
                        break;
                    }
                    //[TODO] Se tiene que verificar que mi unidad ademas de existir, este en las coordenadas asignadas
                    if (!$originArmy->exist()) {
                        //MANDAR RESPUESTA
                        $dt->error($report_id, 'Criatura Mia no existe en posicion [' . $m['x_in'] . '][' . $m['y_in'] . ']');
                        break;
                    }
                    if (($enemyArmy->exist()) && ($originArmy->exist())) {
                        $dt = $this->armyman->simpleAttackTD($originArmy, $enemyArmy, $this->getReport()); //Envio del Report
                        //$dt->success('El Army  a fijado su objetivo', 'success');
                    }
                    break;

                case 3://Arrollar

                    if (!$enemyArmy->exist()) {
                        $dt->error('Criatura Enemiga no fue arrollada en posicion x:' . $m['x_out'] . ' y:' . $m['y_out']);
                        break;
                    }
                    //[TODO] Se tiene que verificar que mi unidad ademas de existir, este en las coordenadas asignadas
                    if (!$originArmy->exist()) {
                        $dt->error('Criatura Mia no fue arrollada en posicion x:' . $m['x_in'] . ' y:' . $m['y_in']);
                        break;
                    }
                    if (($enemyArmy->exist()) && ($originArmy->exist())) {
                        $dt = $this->armyman->simpleAttack($originArmy, $enemyArmy, $this->getReport());
                    }
                    break;

                case 4://Rango
                    if (!$enemyArmy->exist()) {
                        $dt->error('Army Enemiga no existe en posicion:' . $m['x_in'] . ' y:' . $m['y_in']);
                        break;
                    }
                    //[TODO] Se tiene que verificar que mi unidad ademas de existir, este en las coordenadas asignadas
                    if (!$originArmy->exist()) {
                        $dt->error('Mi Army no existe en posicion x:' . $m['x_in'] . ' y:' . $m['y_in']);
                        break;
                    }
                    if (($enemyArmy->exist()) && ($originArmy->exist())) {
                        $dt = $this->armyman->simpleRangeAttackTD($originArmy, $enemyArmy, $this->getReport());
                    }
                    break;

                case 5://Defenderse
                    //$report2->addCauseUnit($report_id,$m['army_id'],'');
                    $dt->success("La unidad se esta defendiendo del ataque");
                    break;

                    break;
                case 7: //Aterrizaje
                    if ($originArmy->isInPosition(_X_POSITION_ORBIT)) {
                        $originArmy->setPosition(_X_POSITION_SUPERFICIE);
                        $this->report->addCauseMovement($originArmy->getId(), $m['tx'], $m['ty']);
                        $dt->success('La unidad aterrizo con exito');
                    } else {
                        $dt->error('El Army no pudo aterrizar');
                    }


                    break;
                case 8: //Orbiting
                    if ($originArmy->isInPosition(_X_POSITION_SUPERFICIE)) {
                        $originArmy->setPosition(_X_POSITION_ORBIT); //[TODO] cero significa que estara en la esquina

                        $td->success($report_id, "La unidad Orbito con exito");
                    } else {
                        $dt->error('El Army se prepara a salir de la region', 'success');
                    }
                    break;

                case 9: //Cañon Solar$enemyArmies = new armies();
                    $enemyArmies = new armies();
                    $enemyArmies->setRaw($this->armyman->getArmiesInFrontOfArmy($originArmy, $m['region_id'], $m['tx'], $m['ty']));
                    $damagedArmies = $enemyArmies->damageUnits($originArmy->getAttack() * 2, $originArmy->getTimes());
                    //$report2->addDummy($report_id,$myArmy->getId());
                    $this->report->addEffectArmies($report_id, $damagedArmies);
                    $dt->success('El Cañon repartio su daño a todas las unidades en su rango de ataque');
                    break;

                case 10://Mover la Unidad rapidamente, army_id,x_in,y_in,x_out,y_out
                    $this->report->addCauseMovement($originArmy->getId(), $m['ox'], $m['oy']);
                    $dt->success('La Unidad procede a moverse rapidamente', 'success');
                    //debug::info($dt,'actionman->workTempActionTD->dt after mov[10]');
                    break;

                case 11://Mover la Unidad lentamente, army_id,x_in,y_in,x_out,y_out
                    $this->report->addCauseMovement($originArmy->getId(), $m['ox'], $m['oy']);
                    $dt->success('La Unidad procede a moverse lentamente', 'success');
                    //debug::info($dt,'actionman->workTempActionTD->dt after mov[11]');
                    break;
            }
        } else {
            debug::warning($dt, 'actionman->workTempActionTD->no se entro al bucle particular');
        }

        if ($dt->hasSuccess()) {
            $this->temp['info'] = $dt->getText();
            //debug::warning($this->temp,'actionman->workTempActionTD->temp');
            $this->saveTempActionTD($originArmy->getId());
        }
        return $dt;
    }

    /*
     * ESTAS DE AQUI quizas no se usen
     * */

    ////////////////////////////////////////////////////////////////////////////////////
    ////// METODOS UTILITARIOS
    ////////////////////////////////////////////////////////////////////////////////////
    /*     * ************************************************************************
     * getArmyActionsByPosition: Obtiene todas las acciones de una unidad en la posicion
     *  indicada, es llamado por regionstate.class
     * Params:
     * $army_id: El ID de la unidad que se quiere obtener los datos
     * $army_position: La posicion actual de la unidad
     * EXTRA: Obtener la lista de arg $args = func_get_args();
     * ****************************************** */
    /*public function getArmyActionsByPosition($army_id, $army_position) {
        $sql = "SELECT actions.id,actions.target,actions.energy,actions.name,armies_actions.action_id,armies_actions.army_id,actions.path,actions.desc,
      actions.movable,actions.scope,actions.minrange,actions.maxrange FROM actions INNER JOIN armies_actions ON (actions.id = armies_actions.action_id)
      WHERE  armies_actions.army_id = " . $army_id . " AND armies_actions.state = '" . _X_ARMY_ACTION_STATE_ACTIVE . "'
      AND actions.position = '" . $army_position . "' ORDER BY armies_actions.order";
        //'A' = Aterrizando, se activan las acciones si esta aterrizando
        debug::log($sql);
        $action_raw = $this->db->vectorize($sql);
        return $action_raw;
    }*/

    public function getReport() {
        return $this->report;
    }

    ////////////////////////////////////////////////////////////////////////////////////
    ////// METODOS AUXILIARES
    ////////////////////////////////////////////////////////////////////////////////////
    /*     * *******************************************************************************
     * singleton: Obtiene la instancia del singleton
     * ******************************************************************************* */
    public static function singleton() {//Obtiene la instancia unica de esta clase
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }
        return self::$instance;
    }

    /*     * *******************************************************************************
     * _clone: Impide que se copie
     * ******************************************************************************* */

    public function __clone() { // Prevenimos que este objeto sea clonado
        trigger_error('Clone is not allowed. db_pg_class', E_USER_ERROR);
    }

}

//actionman
?>
