<?php
  require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
   
/******************************************************************
 * entityutil: Utilidad para creacion y mantenimiento de entidades
 ******************************************************************/
  class entityarmyutil  extends util
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
    
    public static function listDefaultCreatures(){
        $listCreatures = array(_X_CREATURE_TYPE_FIGHTER,_X_CREATURE_TYPE_FIGHTER,_X_CREATURE_TYPE_ARCHER,_X_CREATURE_TYPE_ARTILLERY,_X_CREATURE_TYPE_ARCHER,_X_CREATURE_TYPE_FIGHTER);
        return $listCreatures;
    }
    
   
    
public function createArmiesDT($listOfCreatures,$regionId,$playerId,$position){
    $dt = new datatransfer();
    $dt->success("Se crearon los armies del jugador [".$playerId."]");
    foreach($listOfCreatures as $creature_id){
        $dt = $this->createArmyDT($creature_id,$regionId,$playerId,$position);
        if(!$dt->isValid()){
            break;
        }
    }

    
    if($dt->isValid()){
         $armies = new armies();
         $armies->initByPlayerIdByRegionId($regionId, $playerId);
         $oRegion = $this->man('region')->findById($regionId);
         $cm = $oRegion->getCachedMapUtil();
         $dt = $armies->moveArmiesToSideDt($position,$cm);
         if(!$dt->isValid()){
              debug::warning($dt,' admin:RecrearIA No se pudo mover las unidades recien creadas' );
         }
    }   
    return $dt;
}

  /*********************************************************************************
  * createArmyDT: Crea una unidad asignada
  *********************************************************************************/
public function createArmyDT($creatureId,$regionId,$playerId,$position){//Lo deberia de hacer otra clase quizas armyman

    $dt = new datatransfer();

    //$sql="SELECT * FROM creatures WHERE creatures.id =".$creatureId;
    //$resp = $this->db->vectorize($sql);
    //if(count($resp)==0){
    if(true){

        $sql="SELECT * FROM creatures WHERE creatures.id =".$creatureId;
        $creature = $this->db->fetch($sql);
        $armyId = $this->db->nextId('armies','id');
        $sql='INSERT INTO
          armies(id,region_id,player_id,position_x,position_y,name,size,creature_id,attack,attrition,armor,life,
          speed,state,max_energy,actual_energy,type,position,next_position,total_life,attack_quantity,
          level,experience,initiative,movement,max_size,region_state)
        VALUES (
          '.$armyId.','.$regionId.','.$playerId.','._X_POSITION_COORD_NULL.','._X_POSITION_COORD_NULL.',
          \''.$creature['name'].'\','.$creature['max_size'].','.$creatureId.','.$creature['attack'].','.$creature['attrition'].',
          '.$creature['armor'].','.$creature['life'].',\''.$creature['speed'].'\',\''._X_ARMY_STATE_ALIVE.'\','.$creature['max_energy'].','.$creature['max_energy'].',
          \''._X_ARMY_TYPE_NORMAL.'\',\''._X_POSITION_ORBIT.'\',\''._X_POSITION_ORBIT.'\',
          '.$creature['life']*$creature['max_size'].','.$creature['attack_quantity'].',
          '._X_ARMY_INITIAL_LEVEL.','._X_ARMY_INITIAL_XP.','.$creature['initiative'].','.$creature['movement'].','.$creature['max_size'].',\''._X_REGION_STATE_OUTSIDE.'\')';

         debug::log($sql,"entityarmyutil.class->createArmyDt"); 
         $this->db->query($sql);
         //Agregar posicion de entrada de acuerdo a lo seleccionado
         

         //Agregar acciones a los armies
         $sql = 'SELECT creature_id,action_id,id,`order` FROM creatures_actions
         WHERE creatures_actions.creature_id ='.$creatureId;
         $creatureActions = $this->db->sql2array($sql);
         debug::log($sql,"entityarmyutil.class->createArmyDT -1");
         debug::log($creatureActions,"entityarmyutil.class->createArmyDT 0");
         $order =1;
         foreach($creatureActions as $army_action){
            debug::log($army_action,"entityarmyutil.class->createArmyDT 1");
                $armyActionId = $this->db->nextId('armies_actions','id');
                //$sql="INSERT INTO armies_actions(army_id,action_id,id,`order`,state)
                //VALUES (".$armyId.",".$army_action['action_id'].",".$armyActionId.",".$army_action['order'].",'"._X_CREATURE_ACTION_STATE_ACTIVE."');";

                $sql = "INSERT INTO armies_actions(army_id,action_id,id,`order`,state)
                VALUES (".$armyId.",".$army_action['action_id'].",".$armyActionId.",'".$army_action['order']."','"
                ._X_CREATURE_ACTION_STATE_ACTIVE."');";
        

                debug::log($sql,"entityarmyutil.class->createArmyDT 2");
                $this->db->query($sql);
                $order++;
                
         }

         $dt->success('Unidad Creada:['.$armyId.']');
         $data = array();
         $data['army_id'] = $armyId;
         $dt->setData($data);
         
         if( $dt->isValid() ){
             
         }
         
     }
     else{
         $dt->error($resp,'Existe una unidad en el lugar seleccionado');
     }
     return $dt;
  }

  /*********************************************************************************
  * Deletes
  *********************************************************************************/
    public function deleteArmiesFromRegionIdDt($regionId){
        $sql="delete from movements where region_id =".$regionId;
        $dt = $this->db->deleteDt($sql);
        $continue = true;
        
        if($dt->isValid()){
            $sql="delete from armies_actions where army_id IN (SELECT id FROM armies WHERE region_id =".$regionId.");";
            $dt = $this->db->deleteDt($sql);
        }
        else{
            $continue = false;
            debug::warning($dt,'entityutil->deleteArmiesFromRegionIdDt Error Elimnando movements');    
        }
        
        if( ( $continue ) && ( $dt->isValid() ) ){
            $sql="delete from armies where region_id =".$regionId;
            debug::info($sql,'entityutil->deleteArmiesFromRegionIdDt');
            $dt = $this->db->deleteDt($sql);
        }
        else{
            $continue = false;
            debug::warning($dt,'entityutil->deleteArmiesFromRegionIdFromPlayerTypeDt Error Elimnando armies_actions');
        }
        
        
        if( !( $continue ) || !( $dt->isValid() ) ){
            debug::warning($dt,'entityutil->deleteArmiesFromRegionIdDt Error Elimnando armies');    
        }
        return $dt;
    }
  /*********************************************************************************
  * Deletes
  *********************************************************************************/
    public function deleteArmiesFromRegionIdFromPlayerTypeDt($regionId,$playerType){
        $sql="delete from movements where region_id = ".$regionId." AND army_id IN (SELECT armies.id FROM armies 
INNER JOIN players ON armies.player_id = players.id WHERE players.type ='".$playerType."' )";
        $dt = $this->db->deleteDt($sql);
        $continue = true;
        //Eliminar 
        if($dt->isValid()){
            $sql="delete from armies_actions where army_id IN (SELECT armies.id FROM armies 
            INNER JOIN players ON armies.player_id = players.id WHERE players.type ='".$playerType."' AND region_id =".$regionId.");";
            $dt = $this->db->deleteDt($sql);
        }
        else{
            $continue = false;
            debug::warning($dt,'entityutil->deleteArmiesFromRegionIdFromPlayerTypeDt Error Elimnando movements');
            
        }
        
        if( ( $continue ) && ( $dt->isValid() ) ){
            $sql="delete from armies where region_id =".$regionId." AND player_id IN (SELECT id FROM players WHERE players.type = '".$playerType."' )";
            
            $dt = $this->db->deleteDt($sql);
        }
        else{
            $continue = false;
            debug::warning($dt,'entityutil->deleteArmiesFromRegionIdFromPlayerTypeDt Error Elimnando armies_actions');
        }
        //////////////////////////////////////////////////////////////////////////////////
        if( !( $continue ) || !( $dt->isValid() ) ){
            debug::warning($dt,'entityutil->deleteArmiesFromRegionIdFromPlayerTypeDt Error Elimnando armies');    
        }
        
        return $dt;
    }
    
   
}
?>