<?php
 require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
/* * ******************************************************
 * player: Clase para el manejo del jugador, esta en forma lazy 
 * **************************************************** */

class player extends identity {

    //////////////////////////////////////////////////////////////////////////////////
    //CAMPOS
    //////////////////////////////////////////////////////////////////////////////////  
    private $removePassword;
    //////////////////////////////////////////////////////////////////////////////////
    //CONSTRCUTOR
    //////////////////////////////////////////////////////////////////////////////////
    public function __construct($removePassword = true) {
        /* $args = func_get_args();
          if(!empty($args)){
          debug::error('player se inicio con argumentos','player con argumentos');
          } */
        parent::__construct();
        $this->removePassword = $removePassword;
    }

    public function postRefresh() {
        if ($this->removePassword && isset($this->raw['password'])) {
            unset($this->raw['password']);
        }
    }

    /*     * *****************************************************************
     * getBattlePhaseInRegion: Obtiene la informacion de batallajugador
     * ***************************************************************** */

    public function getBattlePhaseInRegion($region_id,$battle_id) {
        $player_id = $this->getId();
        //debug::console('Obteniendo el phase del player[' . $player_id . '] en la region[' . $region_id . ']', 'player.class getBattlePhaseInRegion 194');
        $sql = "SELECT * FROM battles_players WHERE player_id = " . $player_id . " AND region_id = " . $region_id ." AND state = '"._X_ACTIVE."' AND battle_id=".$battle_id;
        $exist_battle = $this->db->fetch($sql); 
        if (!empty($exist_battle)) {
            return $exist_battle;
        } else {
            debug::warning('No existe una batallajugador en la region[' . $region_id . '] con el player[' . $player_id . ']', 'player.class getBattlePhaseInRegion 201');
            return false;
        }
    }

    public function getAbilities() {


        $sql = "SELECT ab.* FROM abilities AS ab INNER JOIN abilities_players AS ap ON (ab.id = ap.ability_id) WHERE ap.player_id =" . $this->getId() . " AND state='A' ORDER BY ap.order DESC";
        $abilities_raw = $this->db->vectorize($sql);
        if (empty($abilities_raw)) {
            //Significa que no hay habilidades
            $abilities_raw = array();
            //debug::error('Error al cargar las habilidades del jugador','player.class.php');  
        }
        return $abilities_raw;
    }

    public function getEnergy() {
        return $this->getParam('actual_energy');
    }
    
    /*public function getMaxPossibleColonies(){
        return 3;//[TODO] Debemos de poner el verdadero calculo
    }
*/
    public function getName() {
        return $this->getParam('username');
    }
    public function getType() {
        return $this->getParam('type');
    }

    
    public function getHomeRegionId() {
        return $this->getParam('home_region_id');
    }
    
    public function getCurrentColonyId() {
        return $this->getParam('current_colony_id');
    }
    
    public function getTutorial() {
        return $this->getParam('tutorial');
    }
    public function isTutorialActive(){
        if($this->getTutorial()==true){
            return true;
        }
        else if($this->getTutorial()==false) {
            return false;
        }
        else{
            debug::warning('Variable tutorial no es falso ni verdadero es:['.$this->getTutorial().']','player->isTutorialActive');
        }
        
    }
    
    
    /* ********************************************************************
     * setTutorial: Setea el estado del tutorial
     * ********************************************************************/

    public function setTutorial($tutorial) {
        $playerId = $this->getId();
        if($tutorial==true){
           $sql = "UPDATE players SET tutorial = TRUE WHERE players.id=" . $playerId;
        }
        else{
           $sql = "UPDATE players SET tutorial = FALSE WHERE players.id=" . $playerId;
        }
        
        $this->db->fetch($sql);
        $this->setDataDirty();
    }    

    /* *******************************************************************
     * setHomeRegion: Actualiza la region inicial
     * ******************************************************************* */

    public function setHomeRegion($id) {
        $playerId = $this->getId();
        $sql = "UPDATE players SET home_region_id = " . $id . " WHERE players.id=" . $playerId;
        $this->db->fetch($sql);
        $this->setDataDirty();
    }

    /*     * *****************************************************************
     * prepareForBattle: Preparamos al Player para la batalla
     * ***************************************************************** */

    public function prepareForBattle($battle) {
        if ((isset($battle)) && ($battle->isActive())) {
            $phase = $battle->getPhase();
            // $phase = $battle;
            switch ($phase) {
                case _X_BATTLE_PHASE_TACTIC:
                    //debug::log('Player['.$this->getName().'] entro en fase tactica','player->prepareForBattle');
                    $this->startTacticalPhase($battle->GetRegionId(), $battle->getId());
                    break;

                case _X_BATTLE_PHASE_BATTLE:
                    //debug::log('Player['.$this->getName().'] entro en fase batalla','player->prepareForBattle');
                    $this->startBattlePhase($battle->GetRegionId(), $battle->getId());
                    break;

                case _X_BATTLE_PHASE_END:
                    debug::warning('No deberia de haber llegado hasta aqui por el filtro de battle->isActive','player->prepareForBattle');
                    break;

                default:
                    debug::error('No existe un case para la phase[' . $phase . ']', 'player->prepareForBattle');
            }
        } else {
            debug::warning('No existe el parametro battle, quizas no exista la batalla, osea la region no tiene enemigos, o se esta empezando a crear la batalla, esto se llama antes', 'player->prepareForBattle');
        }
    }

    /*     * *****************************************************************
     * startTacticalPhase: El player entra en modo tactico en la region
     * ***************************************************************** */

    public function startTacticalPhase($region_id, $battle_id) {
        //Verificar que la fase de tactica no se halla iniciado 
        $player_id = $this->getId();
        $sql = "SELECT * FROM battles_players WHERE player_id = " . $player_id . " AND battle_id = " . $battle_id . " AND region_id = " . $region_id . " AND state= '" . _X_BATTLE_PLAYER_STATE_ALIVE . "'";
        $exist_battle = $this->db->fetch($sql);
        if (empty($exist_battle)) {
            $id = $this->db->nextId('battles_players');
            //Si es Artificial entran a la batalla defrente, solo si var magica esta true
            if (($this->isArtificial()) && _X_AUTOBATTLE_ARTIFICIAL_PLAYER) {
                $sql = "INSERT INTO battles_players(id,battle_id,player_id,phase,state,region_id,last_movement)
      VALUES (" . $id . "," . $battle_id . "," . $this->getId() . ",'" . _X_BATTLE_PLAYER_PHASE_BATTLE . "','" . _X_BATTLE_PLAYER_STATE_ALIVE . "'," . $region_id . "," . _X_INITIAL_PLAYER_MOVEMENT_ID . ");";
            } else {
                $sql = "INSERT INTO battles_players(id,battle_id,player_id,phase,state,region_id,last_movement)
      VALUES (" . $id . "," . $battle_id . "," . $this->getId() . ",'" . _X_BATTLE_PLAYER_PHASE_TACTIC . "','" . _X_BATTLE_PLAYER_STATE_ALIVE . "'," . $region_id . "," . _X_INITIAL_PLAYER_MOVEMENT_ID . ");";
            }
            $this->db->query($sql);
            return true;
        } else {

            //debug::warning('Ya existe una batalla['.$battle_id.'] en fase tactica en la region['.$region_id.'] con el player['.$this->getId().']','player.class startTacticalPhase 181');
            return false;
        }
    }

    /*******************************************************************
     * startBattlePhase: El player cambia de Fase Tactica a Fase de Batalla
     ***********************************************************************/

    public function startBattlePhase($region_id, $battle_id) {
        
        $battleState = $this->getBattlePhaseInRegion($region_id,$battle_id);
        if($battleState['phase']==_X_BATTLE_PHASE_BATTLE){
            return true;
        }
        else{//Verificar que la fase de tactica no se halla iniciado
        $player_id = $this->getId();
        $sql = "SELECT * FROM battles_players WHERE player_id = " . $player_id . " AND battle_id = " . $battle_id . " AND region_id = " . $region_id . " AND phase = '" . _X_BATTLE_PLAYER_PHASE_TACTIC . "' AND state= '" . _X_BATTLE_PLAYER_STATE_ALIVE . "'";
        $exist_battle = $this->db->fetch($sql);

        //debug::info($exist_battle,'exist_battle starBattlePhase player.class');
        if (!empty($exist_battle)) {
            $last_movement = $exist_battle['last_movement'];
            $sql = "UPDATE battles_players  SET phase = '" . _X_BATTLE_PLAYER_PHASE_BATTLE . "', last_movement = '" . $last_movement . "' WHERE player_id = " . $player_id . " AND battle_id = " . $battle_id . " AND region_id = " . $region_id . " AND phase = '" . _X_BATTLE_PLAYER_PHASE_TACTIC . "' AND state= '" . _X_BATTLE_PLAYER_STATE_ALIVE . "'";
            $this->db->query($sql);
            //debug::log('player->starBattlePhase se updatep');
            return true;
        } else {
            $id = $this->db->nextId('battles_players');
            $sql = "INSERT INTO battles_players(id,battle_id,player_id,phase,state,region_id,last_movement)
         VALUES (" . $id . "," . $battle_id . "," . $this->getId() . ",'" . _X_BATTLE_PLAYER_PHASE_BATTLE . "','" . _X_BATTLE_PLAYER_STATE_ALIVE . "'," . $region_id . "," . _X_INITIAL_PLAYER_MOVEMENT_ID . ");";
            //debug::log($sql,'player->starBattlePhase se inserto');
            return true;
        }
        
        }
    }

    public function removeEnergy($mana) {
        $sql = "UPDATE players  SET actual_energy = actual_energy-" . $mana . " WHERE id = " . $this->getParam('id');
        $this->db->query($sql);
        $this->setDataDirty();
        return true;
    }
    

    /*
     */


    /*     * ***************************************************************************
     * BOLEANOS
     * *************************************************************************** */

    public function isArtificial() {
        if ($this->getParam('type') == _X_PLAYER_TYPE_ARTIFICIAL) {
            //debug::log($this-getParam('type'),'player['._X_PLAYER_TYPE_ARTIFICIAL.']->isArtificial->Verdadero');
            return true;
        } else {
            //debug::log($this-getParam('type'),'player['._X_PLAYER_TYPE_ARTIFICIAL.']->isArtificial->Falso');
            return false;
        }
    }

    //////////////////////////////////////////////////////////////////////////////////
    //UTILITARIOS
    //////////////////////////////////////////////////////////////////////////////////  
    /*********************************************************************************
     * maxTeamsInRegionId: Obtiene el maximo numero de equipos en la region dada
     * ******************************************************************************* */
    public function maxTeamsInRegionId($regionId){
        return _X_PLAYER_MAX_TEAM_PER_REGION;
    }


    /*********************************************************************************
     * remove: Elimina al player de la BD de Xeno
     * ******************************************************************************* */


    /*     * *******************************************************************************
     * update: Actualiza al usuario en la BD de Xeno
     * ******************************************************************************* */

    public function update($name, $mail, $pass, $xid) {//[TODO] Verificar que se haya upgradeado?
        $this->db->begin();
        $sql = "UPDATE players SET username = '" . $name . "',password = '" . $pass . "',mail = '" . $mail . "' WHERE id = " . $xid;
        $rawUser = $this->db->query($sql);
        $this->db->commit();
        $retorna['updated'] = true;
        $retorna['success'] = 'Los datos han sido actualizados en el juego';
        $retorna['error'] = 'Los datos no han sido actualizados en el juego';
        return $retorna;
    }
    
    public function getResources(){
        $data = array();
        $data[_X_RESOURCE_1] = doubleval($this->getParam(_X_RESOURCE_1));
        $data[_X_RESOURCE_2] = doubleval($this->getParam(_X_RESOURCE_2));
        $data[_X_RESOURCE_3] = doubleval($this->getParam(_X_RESOURCE_3));
        $data[_X_RESOURCE_4] = doubleval($this->getParam(_X_RESOURCE_4));
        $data[_X_RESOURCE_5] = doubleval($this->getParam(_X_RESOURCE_5));
        $data[_X_RESOURCE_6] = doubleval($this->getParam(_X_RESOURCE_6));
        $data[_X_RESOURCE_7] = doubleval($this->getParam(_X_RESOURCE_7));
        $data[_X_RESOURCE_8] = doubleval($this->getParam(_X_RESOURCE_8));
        return $data;
    }
    
 
    

        public function prepareRaw() {
            $preparedRaw = $this->raw;      
            unset($preparedRaw['password']);
            unset($preparedRaw['mail']);            
            $preparedRaw['res1'] = floor($preparedRaw['res1']);
            $preparedRaw['res2'] = floor($preparedRaw['res2']);
            $preparedRaw['res3'] = floor($preparedRaw['res3']);
            return $preparedRaw;
        }

        public function validatePassword($password) {
            // Hash the provided password using MD5
            $hashedPassword = md5($password);
    
            // Get the stored password from player data
            $storedPassword = $this->getParam('password');
            if(empty($storedPassword)){
                debug::log($this,"player.class no tiene password");
            }
    
            // Compare the hashed password to the stored one
            if ($hashedPassword === $storedPassword) {
                return true;
            } else {
                return false;
            }
        }
    

}

?>