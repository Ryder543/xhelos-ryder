<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad

/* * ****************************************************************
 * entityutil: Utilidad para creacion y mantenimiento de entidades
 * **************************************************************** */

class entityplayerutil extends util {

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

    public function removePlayer($xid) {
        $dt = new datatransfer();

        $removedPlayer = $this->man('player')->findById($xid);

        $dt->success('entityutil->removePlayer success inicial');
        if ((!isset($xid)) || (empty($xid))) {
            $dt->error('entityutil-removePlayer el parametro xid esta vacio! o no existe');
        }

        if ($dt->isValid()) {
            $this->db->begin();
            $sql = 'delete from travel where armies_id IN( select id from armies where player_id = ' . $xid . ')';
            $dt = $this->db->deleteDt($sql, true);
        }

        if ($dt->isValid()) {
            $sql = "DELETE FROM abilities_players WHERE id = " . $xid;
            $dt = $this->db->deleteDt($sql, true);
        }

        if ($dt->isValid()) {
            $sql = "DELETE FROM battles_players WHERE id = " . $xid;
            $dt = $this->db->deleteDt($sql, true);
        }

        if ($dt->isValid()) {
            $sql = 'delete from movements where army_id IN( select id from armies where player_id = ' . $xid . ')';
            $this->db->query($sql, true);
        }

        if ($dt->isValid()) {
            $sql = 'delete from armies_actions where army_id IN( select id from armies where player_id = ' . $xid . ')';
            $this->db->query($sql, true);
        }

        if ($dt->isValid()) {
            $sql = 'delete from armies where player_id = ' . $xid;
            $this->db->query($sql, true);
        }

        if ($dt->isValid()) {
            $sql = 'delete from players where id = ' . $xid;
            $this->db->query($sql, true);
        }

        if ($dt->isValid()) {
            $sql = 'delete from internal_constructions where id  IN (SELECT colony_id FROM colonies WHERE owner_id  = ' . $xid . ')';
            $dt = $this->db->deleteDt($sql, true);
        }

        if ($dt->isValid()) {
            $sql = 'delete from colonies WHERE owner_id = ' . $xid;
            $dt = $this->db->deleteDt($sql, true);
        }

        if ($dt->isValid()) {
            $this->db->commit();
            $dt->success('Usuario [' . $removedPlayer->getParam("username") . '] eliminado');
            $dt->setData($xid);
        } else {
            $this->db->rollback();
        }
        return $dt;
    }

    public function createHumanPlayerDt($name, $pass, $mail, $homeRegionId) {

        $oHomeRegion = $this->man("region")->findById($homeRegionId);

        $dt = new datatransfer();
        $dt->success('Success Inicial');

        $rawUser = $this->man("player")->findByUsername($name);
        if ($rawUser) { /* Existe usuario con ese nombre */
            $dt->error('El nombre de usuario ya existe, intenta con un nombre mas excentrico');
            $position = 'name';
        }
        //debug::log($dt,'userutil step1');

        if ($dt->isValid()) { /* Existe usuario con ese correo */
            $rawUser = $this->man("player")->findByMail($mail);
            if ($rawUser) {
                $dt->error('Parece que tu correo ya esta registrado, quizas se te olvido el nombre de usuario o tu password?');
                $position = 'mail';
            }
        }
        //debug::log($dt,'userutil step2');

        if ($dt->isValid()) { /* Tamaño del password es el adecuado */
            $numChar = strlen(utf8_decode($pass));
            if (!($numChar >= _X_PLAYER_MIN_PASS_SIZE)) {
                $dt->error('Tu Password tiene que ser de mas de ' . _X_PLAYER_MIN_PASS_SIZE . ' caracteres');
                $position = 'password';
            }
        }
        //debug::log($dt,'userutil step3');

        if ($dt->isValid()) { /* Correo Electronico es el adecuado */
            if (!(validEmail($mail))) {
                $dt->error('Ingresa un Correo Valido');
                $position = 'mail';
            }
        }


        //Todo normal con los datos?
        if ($dt->isValid()) {
            $data["id"] = $this->db->nextId('players');
            $data["username"] = $name;
            $data["max_energy"] = _X_PLAYER_INITIAL_MAX_ENERGY;
            $data["actual_energy"] = _X_PLAYER_INITIAL_MAX_ENERGY;
            $data["rate_energy"] = _X_PLAYER_INITIAL_ENERGY_RATE;
            $data["last_update"] = " now() ";
            $data["state"] = _X_ACTIVE;
            $data["mail"] = $mail;
            $data["alliance_id"] = _X_ALLIANCE_NOALLIANCE_ID;
            $data["current_region"] = $homeRegionId;
            $data["type"] = _X_PLAYER_TYPE_HUMAN;
            $data["home_region_id"] = $homeRegionId;
            $data["current_colony_id"] = 0;
            $data['res1'] = _X_RESOURCE_1_INITIAL_VALUE;
            $data['res2'] = _X_RESOURCE_2_INITIAL_VALUE;
            $data['res3'] = _X_RESOURCE_3_INITIAL_VALUE;
            $data['res4'] = _X_RESOURCE_4_INITIAL_VALUE;
            $data['res5'] = _X_RESOURCE_5_INITIAL_VALUE;
            $data['res6'] = _X_RESOURCE_6_INITIAL_VALUE;
            $data['res7'] = _X_RESOURCE_7_INITIAL_VALUE;
            $data['res8'] = _X_RESOURCE_8_INITIAL_VALUE;
            $data['max_res1'] = _X_RESOURCE_1_MAX_INITIAL_VALUE;
            $data['max_res2'] = _X_RESOURCE_2_MAX_INITIAL_VALUE;
            $data['max_res3'] = _X_RESOURCE_3_MAX_INITIAL_VALUE;
            $data['max_res4'] = _X_RESOURCE_4_MAX_INITIAL_VALUE;
            $data['max_res5'] = _X_RESOURCE_5_MAX_INITIAL_VALUE;
            $data['max_res6'] = _X_RESOURCE_6_MAX_INITIAL_VALUE;
            $data['max_res7'] = _X_RESOURCE_7_MAX_INITIAL_VALUE;
            $data['max_res8'] = _X_RESOURCE_8_MAX_INITIAL_VALUE;
            $data['rate_res1'] = _X_RESOURCE_1_INITIAL_RATE;
            $data['rate_res2'] = _X_RESOURCE_2_INITIAL_RATE;
            $data['rate_res3'] = _X_RESOURCE_3_INITIAL_RATE;
            $data['rate_res4'] = _X_RESOURCE_4_INITIAL_RATE;
            $data['rate_res5'] = _X_RESOURCE_5_INITIAL_RATE;
            $data['rate_res6'] = _X_RESOURCE_6_INITIAL_RATE;
            $data['rate_res7'] = _X_RESOURCE_7_INITIAL_RATE;
            $data['rate_res8'] = _X_RESOURCE_8_INITIAL_RATE;
            $data['tutorial'] = _X_PLAYER_INITIAL_TUTORIAL;
            $data['creation_date'] = " now() ";
            $dt = $this->man('player')->createDt($data);
            $dt->setDataIfValid($data);
        }
        return $dt;
    }

    public function registerUser($name, $mail, $pass) {
        //Paso 0, obtenemos su region inicial
        $eru = new entityregionutil();
        $dt = $eru->obtainInitialPlayerHomeRegion(); //El Mapa se crea solito cuando lo getean!

        $oHomeRegion = $dt->getData();
        $HomeRegionId = $oHomeRegion->getId();

        //debug::log($dt,'userutil step4');
        if ($dt->ok()) { /* Creacion del jugador humano */

            $dt = $this->createHumanPlayerDt($name, $pass, $mail, $HomeRegionId);
            if (!$dt->isValid()) {
                //$dt->error('No se pudo crear al jugador, por favor reportalo en el foro y gana un premio!');
                $position = 'xeno';
            }
        }
        $entityarmyutil = new entityarmyutil();



        //debug::log($dt,'userutil step5');

        if ($dt->isValid()) { /* Creacion de su armies */
            $player_raw = $dt->getData();
            //debug::log($player_raw,'player->register player_raw');
            $playerId = $player_raw['id'];
            $oPlayer = $this->man('player')->findById($playerId);

            //Paso Previo, removemos todas las unidades de la region. JustInCase
            $dt = $entityarmyutil->deleteArmiesFromRegionIdDt($oPlayer->getHomeRegionId());
            if ($dt->ok()) {
                //debug::log($oPlayer,'player->register oPlayer');
                $listOfCreatures = entityarmyutil::listDefaultCreatures();
                $dt = $entityarmyutil->createArmiesDT($listOfCreatures, $oPlayer->getHomeRegionId(), $oPlayer->getId(), _X_LEFT);

                if (!$dt->isValid()) {
                    $dt->error('No se pudo crear las unidades del jugador');
                    $position = 'xeno';
                }
            }else{
                $dt->error('No se pudo eliminar las anteriores unidades que habia en la region');
                $position = 'xeno';              
            }
        }
        //debug::log($dt,'userutil step6');
        if ($dt->isValid()) { /* Creacion de sus enemigos iniciales */
            $listOfCreatures = entityarmyutil::listDefaultCreatures();
            $oIAPlayers = new players();
            $oIAPlayers->initByLeastArmies(_X_PLAYER_TYPE_ARTIFICIAL);
            $oIAPlayer = $oIAPlayers->first();
            $dt = $entityarmyutil->createArmiesDT($listOfCreatures, $oPlayer->getHomeRegionId(), $oIAPlayer->getId(), _X_RIGHT);
            //debug::log($dt,'userutil-> prestep7');
            if (!$dt->isValid()) {

                $dt->error('No se pudo crear las unidades enemigas de este jugador');
                $position = 'xeno';
            }
        }

        //debug::log($dt,'userutil step7');
        $entityColonyUtil = new entitycolonyutil();
        if ($dt->ok()) { /* Creacion de su colonia primaria */

            $colonyWork = new createcolonywork();
            $arraySource = [_X_VAR_COLONY_TYPE => _X_COLONY_TYPE_PRIMARY, _X_VAR_PLAYER_ID => $oPlayer->getId(), _X_VAR_REGION_ID => $oPlayer->getHomeRegionId()];
            $colonyWork->initParamsFromArray($arraySource);
            $colonyWork->doIt();
            $dt = $colonyWork->getExecuteDt();
            if (!$colonyWork->isOk()) {
                $dt->error('No se pudo crear la colonia primaria inicial del jugador');
            } else {
                $data = $dt->getData();
                $dtInner = $this->man("player")->updateDt($oPlayer->getId(), "current_colony_id", $data["id"]);
                if (!$dtInner->ok()) {
                    $dt->error('No se pudo actualizar la colonia actual del jugador');
                }
            }
        }
        //debug::log($dt,'userutil step8');
        if ($dt->isValid()) { /* Creacion de las construcciones primarias */
            $colony_raw = $dt->getData();
            $colonyId = $colony_raw['id'];
            $dt = $entityColonyUtil->createDefaultConstructionInColonyDt($colonyId);
            if (!$dt->isValid()) {
                //debug::error($dt,'userutil RecrearHomeColony Error al crear las construcciones en la colonia '.$colony_raw['name'].'['.$colonyId.']');
                $dt->error('No se pudo crear las construcciones en la colonia ' . $colony_raw['name']);
            }
        }

        //debug::log($dt,'userutil step9');
        if ($dt->isValid()) {
            $dt->success('Bienvenido a ' . _X_GAMENAME . ' ' . $name);
            $dt->setData($playerId);
        }
        return $dt;
    }

}

?>