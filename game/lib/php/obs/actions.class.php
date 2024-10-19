<?php
 require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
 
// debug::trace('PLAYERS');
 //$ret = new identities('Pollo');
 
 /********************************************************
 * player: Clase para el manejo del jugador, esta en forma lazy 
 ******************************************************/

class actions  extends identities
{
    //////////////////////////////////////////////////////////////////////////////////
    //Variables
    ///////////////////////////////////////////////////////////////////////////////////


    //////////////////////////////////////////////////////////////////////////////////
    //Metodos
    //////////////////////////////////////////////////////////////////////////////////
    /*********************************************************************************
    * army: constructor
    *********************************************************************************/
    public function __construct(){ //We call like this the constructor because it interfere with his parent
      $args = func_get_args();
        if(!empty($args)){
          debug::error('actions se inicio con argumentos','actions->constructor');
        }
        parent::__construct('actionman');
    }
    
    //////////////////////////////////////////////////////////////////////////////////
    //Inicializadores
    //////////////////////////////////////////////////////////////////////////////////   
     /**************************************************************************
     * initByArmyIdByArmyByArmyPosition: Obtiene todas las acciones de una unidad en la posicion
     *  indicada, es llamado por regionstate.class
     * Params:
     * $army_id: El ID de la unidad que se quiere obtener los datos
     * $army_position: La posicion actual de la unidad
     * EXTRA: Obtener la lista de arg $args = func_get_args();
     * ************************************************************************/
    public function initByArmyIdByArmyPosition($army_id, $army_position) {
      $sql = "SELECT actions.id,actions.target,actions.energy,actions.name,armies_actions.action_id,armies_actions.army_id,actions.path,actions.desc,
      actions.movable,actions.scope,actions.minrange,actions.maxrange FROM actions INNER JOIN armies_actions ON (actions.id = armies_actions.action_id)
      WHERE  armies_actions.army_id = " . $army_id . " AND armies_actions.state = '" . _X_ACTIVE . "'
      AND actions.position = '" . $army_position . "' ORDER BY armies_actions.order";
        //'A' = Aterrizando, se activan las acciones si esta aterrizando
        //debug::log($sql);
        $action_raw = $this->db->vectorize($sql);
        $this->setRaw($action_raw);
        return $action_raw;
    }

     /**************************************************************************
     * initByArmyIdByArmy: Obtiene todas las acciones de una unidad en la posicion
     *  indicada, es llamado por regionstate.class
     * Params:
     * $army_id: El ID de la unidad que se quiere obtener los datos
     * $army_position: La posicion actual de la unidad
     * EXTRA: Obtener la lista de arg $args = func_get_args();
     * ************************************************************************/
    public function initByArmyId($army_id) {
      $sql = "SELECT actions.id,actions.target,actions.energy,actions.name,armies_actions.action_id,armies_actions.army_id,actions.path,actions.desc,
      actions.movable,actions.scope,actions.minrange,actions.maxrange FROM actions INNER JOIN armies_actions ON (actions.id = armies_actions.action_id)
      WHERE  armies_actions.army_id = " . $army_id . " AND armies_actions.state = '" . _X_ACTIVE . "' ORDER BY armies_actions.order";
        //'A' = Aterrizando, se activan las acciones si esta aterrizando
        //debug::log($sql);
        $action_raw = $this->db->vectorize($sql);
        $this->setRaw($action_raw);
        return $action_raw;
    }

    public function prepareRaw() {
        $preparedRaw = $this->raw;
        /*foreach ($preparedRaw as $key => $value) {          
            unset($preparedRaw[$key]['map']);            
        }*/
        return $preparedRaw;
    }

 
}

?>