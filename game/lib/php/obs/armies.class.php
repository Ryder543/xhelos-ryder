<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/* * *****************************************************
 * armies: Clase para el manejo de varios armies 
 * **************************************************** */
/* debug::trace('ARMIES');

  $arr = get_declared_classes();
  debug::log($arr);
  $str = dirname(__FILE__) . '/oop/identities.class.php';
  debug::log($str); */

class armies extends identities { //[TODO]Deberiamos de instanciarlo de una interface objeto 
//////////////////////////////////////////////////////////////////////////////////
//Metodos PRINCIPALES
//////////////////////////////////////////////////////////////////////////////////	

    /*     * *******************************************************************************
     * armies: constructor
     * ******************************************************************************* */

    public function __construct() {//No se reciben datos debido a todas las posibles formas de inicializacion del objeto
        $args = func_get_args();
        if (!empty($args)) {
            debug::error('armies se inicio con argumentos', 'armies->constructor');
        }
        parent::__construct('armyman');
    }

    public function resetEnergyDt() {
        $region_id = $this->getUnique('region_id');
        if ($region_id) {//Optimizador, si son de la misma region usar este update
            $sql = "UPDATE armies SET actual_energy = max_energy WHERE region_id = " . $region_id;
        } else {
            $ids = $this->getIds();
            $sql = "UPDATE armies SET actual_energy = max_energy WHERE id IN (" . $ids . ")";
        }
        debug::log($sql, 'armies->resetManaDt');

        $dt = $this->db->updateDt($sql);

        if (!$dt->isValid()) {
            debug::warning($dt, 'armies->resetManaDt, No se pudo resetear el mana');
        }
        $this->setDataDirty();
        return $dt;
    }

    /*     * *******************************************************************************
     * resetDt : Reinicia todos los datos de la unidad basandose en la criatura
     * ******************************************************************************* */

    public function resetDt() {
        $region_id = $this->getUnique('region_id');
        if ($region_id) {//Optimizador, si son de la misma region usar este update
            $sql = "UPDATE armies as a SET size = (SELECT max_size FROM creatures as c WHERE c.id = a.creature_id),
  		total_life = (SELECT (max_size*life) FROM creatures as c WHERE c.id = a.creature_id),
  		life = (SELECT life FROM creatures as c WHERE c.id = a.creature_id),
  		state = '" . _X_ARMY_STATE_ALIVE . "'
  		WHERE a.region_id = $region_id";
        } else {
            $ids = $this->getIds();
            $sql = "UPDATE armies as a SET size = (SELECT max_size FROM creatures as c WHERE c.id = a.creature_id),
  		total_life = (SELECT (max_size*life) FROM creatures as c WHERE c.id = a.creature_id),
  		life = (SELECT life FROM creatures as c WHERE c.id = a.creature_id),
  		state = '" . _X_ARMY_STATE_ALIVE . "'
  		WHERE a.id IN (" . $ids . ")";
        }
        $dt = $this->db->updateDt($sql);
        if (!$dt->isValid()) {
            debug::warning($dt, 'armies->reset, No se pudo resetear los datos de los armies');
        }
        $this->setDataDirty();
        return $dt;
    }

    /**
     * prepareForRegionBattleDt2Wr : Tiene 2 objetivos este metodo. Hace entrar 
     * al army a la region indicada si todo esta correcto y despues actualiza el
     * estado de todas las unidades que recien hayan entrado a la region para
     * que esten listas para la batalla
     * 
     * Envia en su dt de respuesta si es necesario forzar
     * el redibujado del cliente. Esto es en Javascript mandando la variable force
     * para indicar que es necesario dibujar a los nuevos armies que han llegado.
     * Util en la fase de tactica. WT = With Refresh DT = DataTransfer
     *
     * @param region Objeto de la region a la cual se quiere ver si hay unidades 
     * que hayan llegado.
     * @param battle Objeto de la batalla que posiblemente se este llevando a cabo
     * @param planet Objeto del planeta donde estan las regiones. Esto es util para
     * calcular desde que lado del mapa deben de colocarse las unidades recien llegadas
     * 
     * @return  datatransfer Contiene en su data si es necesario refrescar la vista o no
     * @todo    Check to make sure the username isn't already taken
     *
     * @since   30/12/2012
     * @author  Jeeba
     *
     * @edit    30/12/2012<br />
     *          Jeeba<br />
     *          Creacion y Documentacion de la clase <br/>
     *          #edit1
     */
    public function prepareForRegionBattleDt($region, $battle, $planet) {
        $refresh = false;
        $dt = new datatransfer();
        $dt->success('armies->prepareForRegionBattleDt temporal');
        //[TODO] Probar que funcione, en fin
        $army_temp = new army();
        $region_id = $region->getId();
        $planet_id = $planet->getId();

        $battleutil = new battleutil();
        //$maputil = new maputil($region->getMap(), $region->getParam('x'), $region->getParam('y'),$region->getId());
        $maputil = $region->getCachedMapUtil();

        $turn = $battle->getTurn();
        $phase = $battle->getPhase();


        //Iterar en cada unidad para aterrizarla tomando en cuenta su terreno
        foreach ($this->getRaw() AS $id => $arriving_army) {
            //$battleutil = new battleutil();//No es necesario crearlo de nuevo, porque esta al inicio creado
            //debug::console($orbiting_army,' prepareForRegionBattle2 armies.class');
            $army = new army();
            $army->initByArray($arriving_army); //Inicializamos por el arreglo que ya tenemos

            if (!$army->isCapableOfBattle()) {//Si el army no esta listo para la batalla porque no esta dentro de la region, lo metemos para que empiece a pelear 
                //al siguiente turno
                if ($army->isArrivingToRegion($region_id)) {
                    $dt = $army->getTravelDt($region_id); //[TODO]Cachear la respuesta?
                    $travel = $dt->getData();
                    $side = $battleutil->calculateArmyEnteringSide($planet_id, $travel);
                    $coord = $battleutil->calculateCoordsFromSide($army->getId(), $region_id, $side, $maputil);
                    if ($coord) {
                        $refresh = true; //Tan solo con una unidad ya deberiamos de refrescar el cliente javascript
                        $army->enterRegion($region_id, $coord['x'], $coord['y']);
                    } else { //De reversa MAMI, la unidad no pudo entrar porque no hay coord libres
                        $battleutil->returnArmyToRegion($army->getId, $travel['from_region']);
                    }
                }
                //Ahora que nos aseguramos de que la unidad entro, la preparamos para batalla
                $army->prepareForBattle($region_id, $turn, $phase);
            } else {
                $army->prepareForBattle($region_id, $turn - 1, $phase); //Lo metemos para que batalle RAI nao
                //$army->prepareForBattle($region_id, $turn, $phase);//Lo metemos para que batalle RAI nao
                //La unidad posiblemente ya este dentro de la batalla
            }
        }
        $dt->setData($refresh);
        return $dt;
    }

    public function sendArmiesToRegion($origin_region, $target_region, $target_planet_id) {
        //$size=count($this->raw);//[TODO] Crear metodo privado para contar armies
        //Retorna un Arreglo para reporte

        /*
          foreach ($raw as $key => $army) {
          //Creacion del Ataque
          $army_temp->initByArray($army);
          //debug::console($army_temp,'Antes armies.class 139');
          $army_temp->sendFromRegionToRegion($origin_region, $target_region, $target_planet_id);
          //debug::console($army_temp,'Despues armies.class 141');
          } */

        foreach ($this as $key => $oArmy) {
            $oArmy->sendFromRegionToRegion($origin_region, $target_region, $target_planet_id);
            //debug::console($oArmy,'armies->sendArmiesToRegion army');
        }
        return true;
    }

    /*     * *******************************************************************************
     * damageUnits : Se les pasa el total de danio a hacer
     * ******************************************************************************* */

    public function damageUnits($damage, $times) {

        $size = count($this->raw);
        $army_temp = new army();

//Retorna un Arreglo para reporte
        $armies = array();
//$armies[_X_UNIT] = array();
        foreach ($this->raw as $key => $value) {
            $army_temp->initByArray($value);
            $deads = $army_temp->receiveAttack($damage, $times);
            //Creacion del Reporte
            if (!isset($armies[$army_temp->getId()])) {
                $armies[$army_temp->getId()] = array();
            }
            $armies[$army_temp->getId()][_X_SIZE] = (-1) * $deads;
        }
        return $armies;
    }

    /*     * *******************************************************************************
     * army: Obtener Un Unico Player, si no existe retorna falso
     * ******************************************************************************* */

    private function getUniquePlayer() {
        //debug::info($this->raw,'getUniquePlayer armies.class');
        foreach ($this->raw AS $key => $army) {
            if (!isset($last_player)) {
                $last_player = $army['player_id'];
                //debug::info('getUniquePLayer['.$last_player.'] Seteo','getUniquePlayer armies.class');
                continue;
            }

            if ($last_player == $army['player_id']) {
                $last_player = $army['player_id'];
                //debug::info('getUniquePLayer last['.$last_player.'] army_player['.$army['player_id'].'] Igualar','getUniquePlayer armies.class');
            } else {//No todas las unidades tienen el mismo player
                //debug::warning('UltimoPlayer['.$last_player.'] no es igual a playerARmy['.$army['player_id'].']','getUniquePlayer armies.class');
                return false;
                break;
            }
        }
        //debug::info('getUniquePLayer['.$last_player.'] Correcto','getUniquePlayer armies.class');
        return $last_player;
    }

    private function getUniqueRegion() {
        //debug::info($this->raw,'getUniquePlayer armies.class');
        foreach ($this->raw AS $key => $army) {
            if (!isset($last_region)) {
                $last_region = $army['region_id'];
                //debug::info('getUniquePLayer['.$last_player.'] Seteo','getUniquePlayer armies.class');
                continue;
            }

            if ($last_region == $army['region_id']) {
                $last_region = $army['region_id'];
                //debug::info('getUniquePLayer last['.$last_player.'] army_player['.$army['player_id'].'] Igualar','getUniquePlayer armies.class');
            } else {//No todas las unidades tienen el mismo player
                debug::warning('UltimoRegion[' . $last_region . '] no es igual a playerARmy[' . $army['region_id'] . ']', 'armies->getUniqueRegion ');
                return false;
                break;
            }
        }
        //debug::info('getUniquePLayer['.$last_player.'] Correcto','getUniquePlayer armies.class');
        return $last_region;
    }

//////////////////////////////////////////////////////////////////////////////////
//Metodos GETTERS
//////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////
//Metodos INICIALIZADORES
//////////////////////////////////////////////////////////////////////////////////

    public function initOrbitingRegion($region_id) {
        $sql = "SELECT * FROM armies WHERE region_id = " . $region_id . " AND next_position = '" . _X_POSITION_ORBIT . "'";
        $armies_raw = $this->db->vectorize($sql);
        if (empty($armies_raw)) {
            $this->raw = array(); //Inicializamos vacio
            debug::info('No existen armies orbitando en la region_id[' . $region_id . ']');
        } else {
            $this->setRaw($armies_raw);
        }
        return $this->getRaw();
    }

    public function initByPlayerOrbitingRegion($player_id, $region_id) {
        $sql = "SELECT * FROM armies WHERE player_id= " . $player_id . " AND region_id = " . $region_id . " AND next_position = '" . _X_POSITION_ORBIT . "'";
        $armies_raw = $this->db->vectorize($sql);
        if (empty($armies_raw)) {
            debug::warning('No existen armies de player_id[' . $player_id . ']aterrizados en la region_id[' . $region_id . ']');
        } else {
            $this->raw = $armies_raw;
        }
        return $armies_raw;
    }

    public function initByPlayer($player_id) {
        $sql = "SELECT * FROM armies WHERE player_id= " . $player_id;
        $armies_raw = $this->db->vectorize($sql);
        if (empty($armies_raw)) {
            debug::warning('No existen armies de player_id[' . $player_id . ']');
        } else {
            $this->raw = $armies_raw;
        }
        return $armies_raw;
    }

    public function initByPlanet($planet_id) {
        $sql = "SELECT * FROM armies INNER JOIN regions ON (armies.region_id= regions.id) WHERE regions.planet_id= " . $planet_id;
        $armies_raw = $this->db->vectorize($sql);
        if (empty($armies_raw)) {
            debug::warning('No existen armies de planet_id[' . $planet_id . ']');
        } else {
            $this->raw = $armies_raw;
        }
        return $armies_raw;
    }

    /*     * *******************************************************************************************************
     * getArmiesByPlanetIdOrderByRegionId: Obtiene los armies activos en el planeta oirdenados por region
     * ************************************************************************************************** */

    public function initByPlanetIdOrderByRegionId($planetId) {

        //$sql = "SELECT * FROM armies INNER JOIN regions ON (armies.region_id= regions.id) WHERE regions.planet_id= " . $planet_id;

        $sql = "SELECT * FROM armies WHERE region_id IN (SELECT id FROM regions WHERE regions.planet_id = " . $planetId . ") ORDER BY id";
        $armies_raw = $this->db->sql2group($sql, 'region_id');
        $this->raw = $armies_raw;
        if (empty($armies_raw)) {
            //debug::warning('No existen armies de planet_id[' . $planet_id . ']');
        } else {
            
        }
        return $this->raw;


        /* $sql = "SELECT * FROM armies WHERE region_id IN (SELECT id FROM regions WHERE regions.planet_id = " . $planetId . ") ORDER BY id";
          $armies_raw = $this->db->sql2group($sql,'region_id');
          return $armies_raw; */
    }

    public function initByPlayerIdByRegionId($region_id, $player_id) {
        $sql = "SELECT armies.* FROM armies INNER JOIN regions ON (armies.region_id= regions.id) WHERE armies.player_id= " . $player_id . " AND regions.id=" . $region_id;
        //debug::console($sql,'armies->initByPLayerRegion');
        $armies_raw = $this->db->vectorize($sql);
        //debug::console($armies_raw);
        if (empty($armies_raw)) {
            debug::trace('armies->initByPlayerIdByRegionId No existen armies de player_id[' . $player_id . '] y region_id[' . $region_id . ']');
        } else {
            $this->raw = $armies_raw;
        }
        return $this->raw;
    }

    public function initAliveByPlayerIdByRegionId($region_id, $player_id) {
        $sql = "SELECT armies.* FROM armies INNER JOIN regions ON (armies.region_id= regions.id) WHERE armies.player_id= " . $player_id . " AND regions.id=" . $region_id . " AND armies.state='" . _X_ARMY_STATE_ALIVE . "'";
        //debug::console($sql,'armies->initByPLayerRegion');
        $armies_raw = $this->db->vectorize($sql);
        //debug::console($armies_raw);
        if (empty($armies_raw)) {
            //debug::trace('armies->initAliveByPlayerIdByRegionId No existen armies de player_id[' . $player_id . '] y region_id[' . $region_id . ']');
        } else {
            $this->raw = $armies_raw;
        }
        return $this->raw;
    }

    public function initByAliveEnemiesOfPlayerInRegion($player_id, $region_id) {
        $sql = "SELECT * FROM armies WHERE player_id != " . $player_id . " AND region_id=" . $region_id . " AND armies.state='" . _X_ARMY_STATE_ALIVE . "'";

        $armies_raw = $this->db->vectorize($sql);
        if (empty($armies_raw)) {
            //debug::warning('No existen armies enemigos de player_id[' . $player_id . '] en la region[' . $region_id . ']');
        } else {
            $this->raw = $armies_raw;
        }
        return $armies_raw;
    }

    /* /7Jeeba 31ene2013, si lo vamos a usar entonces cambiemosle el nombre como alive and dead mejor porque ufff que tales errores que va a dar...
      public function initByEnemiesOfPlayerInRegion($player_id, $region_id) {
      $sql = "SELECT * FROM armies WHERE player_id != " . $player_id . " AND region_id=" . $region_id;

      $armies_raw = $this->db->vectorize($sql);
      if (empty($armies_raw)) {
      //debug::warning('No existen armies enemigos de player_id[' . $player_id . '] en la region[' . $region_id . ']');
      } else {
      $this->raw = $armies_raw;
      }
      return $armies_raw;
      } */

    /*     * *******************************************************************************
     * initByRegion: Inicia todas las unidades que se encuentren en la region_id
     * se llama al inicializar la batalla ^^
     * ******************************************************************************* */

    public function initActivesByRegion($region_id) {
        //$sql = "SELECT armies.* FROM armies INNER JOIN regions ON (armies.region_id= regions.id) LEFT JOIN travel ON (travel.armies_id = armies.id) WHERE regions.id=".$region_id." AND (travel.state = '"._X_TRAVEL_STATE_INACTIVE."' OR travel.state IS NULL)";
        $sql = "SELECT armies.* FROM armies WHERE region_id=" . $region_id . " AND armies.state !='" . _X_ARMY_STATE_DEAD . "'";
        //debug::console($sql,'initByRegion  armies.class');
        $armies_raw = $this->db->vectorize($sql);
        //debug::console($armies_raw,'initByRegion armies.class');
        if (empty($armies_raw)) {
            //debug::warning('No existen armies de region_id['.$region_id.']','armies->initActivesByRegion ');
            $this->setRaw(array());
        } else {
            $this->setRaw($armies_raw);
        }
        return $this->getRaw();
    }

    /*     * *******************************************************************************
     * initByRegion: Inicia todas las unidades que se encuentren en la region_id
     * se llama al inicializar la batalla ^^
     * ******************************************************************************* */

    public function initByRegion($region_id) {
        //$sql = "SELECT armies.* FROM armies INNER JOIN regions ON (armies.region_id= regions.id) LEFT JOIN travel ON (travel.armies_id = armies.id) WHERE regions.id=".$region_id." AND (travel.state = '"._X_TRAVEL_STATE_INACTIVE."' OR travel.state IS NULL)";
        $sql = "SELECT armies.* FROM armies WHERE region_id=" . $region_id;
        //debug::console($sql,'initByRegion  armies.class');
        $armies_raw = $this->db->vectorize($sql);
        //debug::console($armies_raw,'initByRegion armies.class');
        if (empty($armies_raw)) {
            //debug::warning('No existen armies de region_id['.$region_id.']','armies->initByRegion ');
            $this->setRaw(array());
        } else {
            $this->setRaw($armies_raw);
        }
        return $this->getRaw();
    }

    /*     * *******************************************************************************
     * initInsideRegion: Inicia todas las unidades que se encuentren en la region_id
     * pero que ya estan dentro de la region con _X_REGION_STATE_INSIDE
     * ******************************************************************************* */

    public function initInsideRegion($region_id) {
        //$sql = "SELECT armies.* FROM armies INNER JOIN regions ON (armies.region_id= regions.id) LEFT JOIN travel ON (travel.armies_id = armies.id) WHERE regions.id=".$region_id." AND (travel.state = '"._X_TRAVEL_STATE_INACTIVE."' OR travel.state IS NULL)";
        $sql = "SELECT armies.* FROM armies WHERE region_id=" . $region_id . " AND region_state = '" . _X_REGION_STATE_INSIDE . "'";
        //debug::console($sql,'initByRegion  armies.class');
        $armies_raw = $this->db->vectorize($sql);
        //debug::console($armies_raw,'initByRegion armies.class');
        if (empty($armies_raw)) {
            //debug::warning('No existen armies de region_id['.$region_id.']','armies->initByRegion ');
            $this->setRaw(array());
        } else {
            $this->setRaw($armies_raw);
        }
        return $this->getRaw();
    }

    //[En AjaxPlanet ya dejamos de usarlo]
    public function initByNotTravelingPlayerRegion($region_id, $player_id) {
        $sql = "SELECT armies.* FROM armies INNER JOIN regions ON (armies.region_id= regions.id) INNER JOIN travel ON (travel.armies_id = armies.id) WHERE armies.player_id= " . $player_id . " AND regions.id=" . $region_id . " AND travel.state = '" . _X_TRAVEL_STATE_INACTIVE . "'";
        //debug::console($sql,'initByNotTravelingPlayerRegion armies.class');
        $armies_raw = $this->db->vectorize($sql);
        //debug::console($armies_raw,'initByNotTravelingPlayerRegion armies.class');
        if (empty($armies_raw)) {
            debug::warning('No existen armies de player_id[' . $player_id . '] y region_id[' . $region_id . ']');
            $this->setRaw(array());
        } else {
            $this->setRaw($armies_raw);
        }
        return $this->getRaw();
    }

    public function initArrivedToRegion($region_id) {
        //[WHERE] Esto donde se usa? eliminar
        //Se esta usando en gamestate.php region gamestate
        //$sql = "SELECT time_to_sec2(travel.eta,now())as seconds,armies.* FROM travel INNER JOIN armies ON (travel.armies_id= armies.id) INNER JOIN regions ON (regions.id = armies.region_id) WHERE time_to_sec2(travel.eta,now())<1 AND regions.id =".$region_id." AND travel.state = '"._X_TRAVEL_STATE_ACTIVE."'";
        $sql = "SELECT time_to_sec2(travel.eta,now())as seconds,armies.* FROM travel INNER JOIN armies ON (travel.armies_id= armies.id) WHERE time_to_sec2(travel.eta,now())<1 AND travel.to_region =" . $region_id . " AND travel.state = '" . _X_TRAVEL_STATE_ACTIVE . "'";
        //debug::log($sql,'armies->initArrivedToRegion');
        $armies_raw = $this->db->vectorize($sql);
        if (empty($armies_raw)) {
            //debug::warning('No existen armies que llegaron al planeta['.$planet_id.']','armies.class initInsideRegion 401');
            $this->raw = false;
        } else {
            $this->setRaw($armies_raw);
        }
        return $this->getRaw();
    }

    public function initByArrivedToRegionsByPlanet($planet_id) {
        $sql = "SELECT time_to_sec2(travel.eta,now())as seconds,armies.* FROM travel INNER JOIN armies ON (travel.armies_id= armies.id) INNER JOIN regions ON (regions.id = armies.region_id) INNER JOIN planets ON (planets.id = regions.planet_id) WHERE travel.state = '" . _X_TRAVEL_STATE_ACTIVE . "' AND time_to_sec2(travel.eta,now())<1 AND planets.id =" . $planet_id . " AND armies.region_state = '" . _X_REGION_STATE_OUTSIDE . "' ORDER BY planets.id";

        /* $sql = "SELECT time_to_sec2(travel.eta,now())as seconds,armies.player_id,regions.id AS region_id FROM travel
          INNER JOIN armies ON (travel.armies_id= armies.id)
          INNER JOIN regions ON (regions.id = armies.region_id)
          INNER JOIN planets ON (planets.id = regions.planet_id)
          WHERE planets.id =".$planet_id." GROUP BY seconds,armies.player_id,planets.id,regions.id
          ORDER BY planets.id"; */
        $armies_raw = $this->db->vectorize($sql);
        if (empty($armies_raw)) {
            //debug::warning('No existen armies en movimiento en planeta['.$planet_id.']');
        } else {
            
        }
        $this->raw = $armies_raw;
        return $this->raw;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////

    /*     * **************************************************************
     * initByTeamId: Inicializa un army por team_id
     * ************************************************************** */
    public function initByTeamId($team_id) {
        $sql = "SELECT * FROM armies WHERE team_id =" . $team_id;
        $armies_raw = $this->db->vectorize($sql);
        
        $this->raw = $armies_raw;
        return $this->raw;
    }

    public function setTeam($teamId) {
        $sql = "UPDATE armies SET team_id = " . $teamId . " WHERE armies.id IN(" . $this->getIds() . ")";
        //debug::log($sql,'armies->setTeamId');
        $this->db->update($sql);
        $this->setDataDirty();
    }

    //////////////////////////////////////////////////////////////////////////////////
//Metodos BOOLEANOS
//////////////////////////////////////////////////////////////////////////////////

    /*     * **************************************************************
     * areAllFromPlayer: Si todos los armies son de un solo player
     * ************************************************************** */
    public function areAllFromPlayer($player_id) {
        //[TODO] Usar las clases respectivas de army.class y player.class
        //global $firephp;
        //$size=count($this->raw);
        foreach ($this->raw AS $key => $army) {
            //for($x=0;$x<$size;$x++){
            //$firephp->log($army,'army');
            if ($army['player_id'] != $player_id) {
                $this->debug->logError("Armies[" . $x . "]player_id[" . $army['player_id'] . "]!=" . $player_id, 'armies.class.php');
                return false;
            }
        }
        return $player_id;
    }

    /*     * **************************************************************
     * areAllInRegion: Si todos los armies son de un solo player
     * ************************************************************** */

    public function areAllInRegion($region_id) {
        //[TODO] Usar las clases respectivas de army.class y player.class
        //global $firephp;
        //$size=count($this->raw);
        foreach ($this->raw AS $key => $army) {
            //for($x=0;$x<$size;$x++){
            //$firephp->log($army,'army');
            if ($army['region_id'] != $region_id) {
                debug::error("Armies[" . $key . "] region_id[" . $army['region_id'] . "]!=" . $region_id, 'armies.class areAllInregion 430');
                return false;
            }
        }
        return $region_id;
    }

    /*     * **************************************************************
     * areInBattle: Si se encuentran actualmente en batalla
     * ************************************************************** */

    public function areInBattle($region_id) {
        $raw = $this->getRaw();
        $battle = new battle();
        $battle->initByRegion($region_id);
        if (!$this->areAllInRegion($region_id)) {
            console::warning('No todas las unidades estan en batalla', 'armies.class areInBattle');
            return false;
        } elseif (!$battle->isActive()) {
            console::warning('No existe una batalla activa en region[' . $region_id . ']', 'armies.class areInBattle');
            return false;
        }
        //debug::error('Si existe una batalla en region['.$region_id.']','armies.class areInBattle 447');
        return true;
    }

    public function areEnemies() {
        //Devuelve true si hay enemigos
        $resp = $this->getUniquePlayer();
        if ($resp) {
            //debug::info($resp,'armies->areEnemies Solo hay un jugador');
            return false;
        } else {
            //debug::info($resp,'armies->areEnemies Hay Enemigos');
            return true;
        }
        //Devuelve False si no hay enemigos
    }

    //////////////////////////////////////////////////////////////////////////////////
    //Metodos UTILITARIOS
    //////////////////////////////////////////////////////////////////////////////////

    public function moveArmiesToSideDt($side, $cachedMapUtil) {
        $dt = new datatransfer();
        $dt->success('armies->moveArmiesToSideDt success por defecto'); //[TODO] Debe de haber errores siempre
        $battleutil = new battleutil();
        $region_id = $this->getUnique("region_id");
        $maputil = $cachedMapUtil;
        //debug::log($maputil,'armies->moveArmiesToSideDt maputil');
        //debug::log($this,'armies->moveArmiesToSideDt');
        foreach ($this as $i => $oArmy) {
            //debug::log($oArmy,'armies->moveArmiesToSideDt');
            $army_id = $oArmy->getId();
            $region_id = $oArmy->getRegionId();
            $coords = $battleutil->calculateCoordsFromSide($army_id, $region_id, $side, $maputil);
            /* if(!$coords){
              debug::error('armies->moveArmiesToSide No se pudo obtener coordenadas para insertar la unidad');
              $dt->error('No se pudo obtener coordenadas para inserccion');
              break;
              } */
            //debug::log($coords,'armies->moveArmiesToSideDt coords');
            $oArmy->enterRegion($region_id, $coords['x'], $coords['y']);
        }
        return $dt;
    }

    public function prepareRaw() {       
        $battleutil = new battleutil();
        $preparedRaw = $this->raw;
        foreach ($preparedRaw as $key => $value) {
            $preparedRaw[$key]['allegiance'] = $battleutil->getAllegiance($preparedRaw[$key]['id'], 'army');
        }
        return $preparedRaw;
    }

}

?>