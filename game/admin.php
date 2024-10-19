<?php
  if(!defined('_X_SECURE')){define("_X_SECURE",true);}
  if(!defined('_X_NO_INIT')){define("_X_NO_INIT",true);}
  if(!defined('_X_ADMIN_INIT')){define("_X_ADMIN_INIT",true);}

 $game_admin_directory = getcwd();
  chdir(dirname ( __FILE__ ));
  include_once("lib/include.php");
  chdir($game_admin_directory);

$regionman = regionman::singleton();
$starman = starman::singleton();
$db = db::singleton();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<?php 
$entityutil = new entityutil();
if(!empty($_GET['Action'])){
switch($_GET['Action']){
/////////////////////////Creacion de la IA 
   case "RecrearHomeColony": 
       
      $players = new players();
      $players->initByType(_X_PLAYER_TYPE_HUMAN);
      foreach($players as $i => $player){
          $regionId = $player->getHomeRegionId();

           if(!empty($regionId)){
               $dt = $entityutil->deleteColoniesByPlayerIdDt($player->getId());
               if(!$dt->isValid()){
                   debug::error($dt,'admin RecrearHomeColony Error al borrar colonias del jugador '.$player->getName().'['.$player->getId().']');
                   break;
               }               

               $dt = $entityutil->createRandomPrimaryColonydt($regionId, $player->getId());   
               if(!$dt->isValid()){
                   debug::error($dt,'admin RecrearHomeColony Error al crear colonias primaria de jugador '.$player->getName().'['.$player->getId().']');
                   break;
               } 
               
               $colony_raw = $dt->getData();
               $colonyId = $colony_raw['id'];
               $dt = $entityutil->createDefaultConstructionInColonyDt($colonyId);   
               if(!$dt->isValid()){
                   debug::error($dt,'admin RecrearHomeColony Error al crear las construcciones en la colonia '.$colony_raw['name'].'['.$colonyId.']');
                   break;
                }
                
           }
           else{
               debug::warning('El jugador '.$player->getName().'['.$player->getId().'] no tiene region inicial');
           }
           
      }
       
       
    break;
    
  case "RecrearIA":  
      debug::log("Recrear IA");
      $continue = true;
      $players = new players();
      $players->initByType(_X_PLAYER_TYPE_HUMAN);
      foreach($players as $i => $player){
          $playersIA = new players();
          $playersIA->initByLeastArmies(_X_PLAYER_TYPE_ARTIFICIAL);
          $region_id = $player->getHomeRegionId();
          
          if(!empty($region_id)){
              debug::info($region_id,'admin:RecrearIA Region a Crear Unidades Enemigas:');

              if($playersIA->size() > 0 ){    
                  
                  $dt = $entityutil->deleteArmiesFromRegionIdFromPlayerTypeDt($region_id, _X_PLAYER_TYPE_ARTIFICIAL);
                  if( $dt->isValid() ){
                      $playerIA = $playersIA->first();
                      debug::info($playerIA,'admin:RecrearIA Player Enemigas:');
                      $listCreatures = array(_X_CREATURE_TYPE_FIGHTER,_X_CREATURE_TYPE_FIGHTER,_X_CREATURE_TYPE_ARCHER,_X_CREATURE_TYPE_ARTILLERY,_X_CREATURE_TYPE_ARCHER,_X_CREATURE_TYPE_FIGHTER);
                      $dt = $entityutil->createArmiesDT($listCreatures, $region_id, $playerIA->getId(), _X_ARMY_SIDE_RIGHT);
                      if($dt->isValid()){
                          $dt2 = $entityutil->deleteBattlesFromRegionIdDt($region_id); 
                          debug::log($dt2,'admin:RecrearIA Resultado de eliminar las batallas de la region['.$region_id.']');
                      }
                      else{
                          $continue = false;
                          debug::warning(' admin:RecrearIA tuvo problema al crear Armies del player['.$playerIA->getName().']' );
                          break; 
                      }

                  }
                  else{
                      debug::warning($dt,'RecrearIA tuvo problema al eliminar antiguas unidadades AI' );
                  }
              }
              else{
                 debug::warning('No existen jugadores enemigos '.$player->getName().'['.$player->getId().']' ); 
              }

          }
          else{
              if(!$dt->isValid()){
                  $continue = false;
                  debug::warning(' RecrearIA no pudo crear unidades enemigas de player '.$player->getName().'['.$player->getId().']' );
                  break;
              }
          }
          
          
          
      }
      break;
    
  case "RecrearArmies":
      $continue = true;
      $players = new players();
      $players->initByType(_X_PLAYER_TYPE_HUMAN);
      foreach($players as $i => $player){
          $region_id = $player->getHomeRegionId();
          if(!empty($region_id)){
            $entityutil->deleteArmiesFromRegionIdDt($region_id);
            debug::log($region_id,'admin:RecrearArmies Region Del jugador '.$player->getName());
            $listCreatures = array(_X_CREATURE_TYPE_FIGHTER,_X_CREATURE_TYPE_FIGHTER,_X_CREATURE_TYPE_ARCHER,_X_CREATURE_TYPE_ARTILLERY,_X_CREATURE_TYPE_ARCHER,_X_CREATURE_TYPE_FIGHTER);
            $dt = $entityutil->createArmiesDT($listCreatures, $region_id, $player->getId(), _X_ARMY_SIDE_LEFT);
            debug::log($dt,'admin:RecrearArmies LastDT Region Del jugador '.$player->getName());
          }
          if($dt->isValid()){
              $dt2 = $entityutil->deleteBattlesFromRegionIdDt($region_id); 
              debug::log($dt2,'admin:RecrearArmies Resultado de eliminar las batallas de la region['.$region_id.']');
          }
          else{
              $continue = false;
              debug::warning(' admin:RecrearArmies tuvo problema al crear Armies del player['.$player->getName().']' );
              break; 
          }
      }
      
      if($continue){
          debug::log($dt,' RecrearArmies Se recrearon todos los armies de los jugadores ');
          $db->commit();
      }
      else{
          debug::log($dt,' RecrearArmies Hubo problemas en la creacion de los armies ');
          $db->rollback();
      }
      
      
  break;
    
  case 'RecrearHomeRegion':
      $db->begin();
      $players = new players();
      //debug::log($players,'admin RecrearHomeRegion players');
      $players->initByType(_X_PLAYER_TYPE_HUMAN);
      
      //debug::log($players,'Jugadores');
      
      $planets = new planets(); 
      $planets->initPlanetsWithoutArmies();
      
      foreach($players as $i => $player){
          debug::log('===============================================================');
          debug::log($player,'admin RecrearHomeRegion player');
          $homeRegionId = $player->getHomeRegionId();
          if(empty($homeRegionId)){
             // debug::log($player->getHomeRegionId(),'admin RecrearHomeRegion vacio');
              // A)Obtener ID de Planeta sin Armies
              $planet_id = 0;
              $continue = true;
              if($planets->size()>0){ //Si existe planeta obtenemos su ID
                  //debug::log($planets->size(),'admin RecrearHomeRegion Numero de Planetas sin Armies ');
                  $planets->rewind();
                  $planet = $planets->current();
                  $planet_id = $planet->getId();
                  $planets->remove($planets->key());
              }
              else{//Si no existe creamos un nuevo planeta y obtenemos su ID
                  $oStar = $starman->findByLeastPlanet();
                  //debug::log($oStar,'admin RecrearHomeRegion Estrella con menos planetas');
                  $dt = $entityutil->createRandomPlanetDt( $oStar->getId() );
                  $continue = $dt->isValid();
                  //debug::log($dt,'admin RecrearHomeRegion dt');
                  $data = $dt->getData();
                  $planet_id = $data['id'];
              }
              debug::log($planet_id,'admin RecrearHomeRegion Planeta Seleccionado');
              
            if(!$continue){//Sil algo se jodio creando planetas,salir
                break;
            }
              
            // B) Obtencion de ID de Regiones del Planeta Seleccionado  
            $region_id = 0;
            $regions = new regions();
            $regions->initByPlanetId( $planet_id );
            if($regions->size()>0){

            }
            else{
                $rand = rand(4,9);
                $dt = $entityutil->createRandomRegionsDt($planet_id,$rand);
                $continue = $dt->isValid();
                $regions->initByPlanetId($planet_id );
            }
            //$region = $regions->first();            
            $region = $regions->first();
            $region_id = $region->getId();
            //debug::log($regions,'admin RecrearHomeRegion Regiones Seleccionadas de planeta['.$planet_id.']'); 
            //debug::log($region,'admin RecrearHomeRegion Region'); 
            debug::log($region_id,'admin RecrearHomeRegion Region Id Seleccionado'); 
            if(!$continue){//Sil algo se jodio creando planetas,salir
                break;
            }         
            //3) Seteamos la region y la limpiamos de armies
            $player->setHomeRegion($region_id);
            
        }
        else{
            debug::log($homeRegionId,'Player['.$player->getId().'] tiene un homeRegion');
            $region_id = $player->getHomeRegionId();
        }
        $dt1 =  $entityutil->deleteArmiesFromRegionIdDt($region_id);
        $dt2 = $entityutil->deleteBattlesFromRegionIdDt($region_id);
        
        if(!$dt1->isValid()){
            debug::log($dt1,'error, leer abajo');
            debug::error('admin:RecrearHOmeRegion Problema al borrar armies');
        }
        else{
            debug::log($dt1,'admin:RecrearhomeRegion Se Borraron todis los armies');
            debug::log($dt2,'admin:RecrearhomeRegion Se Borraron todis los battles');
            
        }
        if(!$dt2->isValid()){
            debug::log($dt2,'error, leer abajo');
            debug::error('admin:RecrearHOmeRegion Problema al borrar battles');
        }
      }
      $db->commit();
      break;
    
  case 'AterrizarIA':
    $msn= 'IA Aterizada (bueno todavia no)';
    break;

 case 'ArreglarPlayers':
     $sql = "UPDATE players SET"
     ." actual_energy = "._X_PLAYER_INITIAL_ENERGY.",max_energy = "._X_PLAYER_INITIAL_MAX_ENERGY.",rate_energy = "._X_PLAYER_INITIAL_RATE_ENERGY
     .",res1="._X_RESOURCE_1_INITIAL_VALUE.",res2="._X_RESOURCE_2_INITIAL_VALUE.",res3="._X_RESOURCE_3_INITIAL_VALUE.
     ",res4="._X_RESOURCE_4_INITIAL_VALUE.",res5="._X_RESOURCE_5_INITIAL_VALUE.",res6="._X_RESOURCE_6_INITIAL_VALUE.",res7="._X_RESOURCE_7_INITIAL_VALUE.",res8="._X_RESOURCE_8_INITIAL_VALUE
     .",max_res6="._X_RESOURCE_6_MAX_INITIAL_VALUE.",max_res7="._X_RESOURCE_7_MAX_INITIAL_VALUE.",max_res8="._X_RESOURCE_8_MAX_INITIAL_VALUE
     .",rate_res6="._X_RESOURCE_6_INITIAL_RATE.",rate_res7="._X_RESOURCE_7_INITIAL_RATE.",rate_res8="._X_RESOURCE_8_INITIAL_RATE;
     $dt =  $db->queryDt($sql,'id');
     debug::log($dt,'admin:ArreglarPlayers');
     break;

  case 'ArreglarArmies':
    $sql = 'SELECT * FROM creatures';
    $db = db::singleton();
    $creatures = $db->vectorize($sql,'id');
    global $firephp;
    $firephp->log($creatures);
    
    $sql = 'SELECT * FROM armies ORDER BY id';
    $armies = $db->vectorize($sql,'id');
    $sql='';
    $arr= array();
    foreach($armies as $key => $army){
      $totalLife =  $creatures[$army['creature_id']]['max_size'] * $creatures[$army['creature_id']]['life'];
        
      $update = "UPDATE armies SET movement = ".$creatures[$army['creature_id']]['movement'].
        ", initiative =".$creatures[$army['creature_id']]['initiative'].
        ", attack_quantity = ".$creatures[$army['creature_id']]['attack_quantity'].
        ", size = ".$creatures[$army['creature_id']]['max_size'].
        ", max_energy = ".$creatures[$army['creature_id']]['max_energy'].
        ", life = ".$creatures[$army['creature_id']]['life'].
        ", attrition = ".$creatures[$army['creature_id']]['attrition'].
        ",speed = '".$creatures[$army['creature_id']]['speed']."'".
        ", armor = ".$creatures[$army['creature_id']]['armor'].
        ", attack = ".$creatures[$army['creature_id']]['attack'].
        ", max_size = ".$creatures[$army['creature_id']]['max_size'].
        ", name = '".$creatures[$army['creature_id']]['name']."'".
        ", total_life = '".$totalLife."'".
        " WHERE id =".$army['id'];
      
      $arr[] = $update;
      $sql = $sql.';'.$update;
    }
    $dbconn = $db->aquery($sql);
    $dom = '<ul>';
    $count =0;
    
    
    
    
    foreach($armies as $key => $army){
      $res1 = pg_get_result($dbconn);
      $rows1 = pg_num_rows($res1);     
      //$dom = $dom . "<li> Armies[".$key."] call to pg_get_result(): ".$res1." has ".$rows1." records </li>";
      $dom = $dom . "<li>".$arr[$count].";</li>";
      
      
      
      $count++;
    }
    $dom = $dom.'</ul>';
    
    //Arreglar Armies Actions
    $sql = 'SELECT * FROM actions';
    $actions = $db->vectorize($sql,'id');
    debug::log($actions,'admin::ArreglarArmies actions');
    $sql = 'SELECT * FROM armies_actions ORDER BY id';
    $armies_actions = $db->vectorize($sql,'id');
    debug::log($armies_actions,'admin::ArreglarArmies armies_actions');
        
    ///////////////////////////////////////////////////////////////////
    //$dom = $dom +'<ul>';
    //$dom = '<ul>';
    $arrayUpdatesArmiesActions= array();
    $sqlUpdatesArmiesActions = '';
    $count =0;
    foreach($armies_actions as $key => $army_action){
        $update ="UPDATE armies_actions AS aa SET order= 
	(SELECT order FROM creatures_actions WHERE creatures_actions.creature_id = 
	(SELECT armies.creature_id FROM armies WHERE armies.id = aa.army_id ) 
	AND creatures_actions.action_id = aa.action_id) ,
        state= 	(SELECT state FROM creatures_actions WHERE creatures_actions.creature_id = 
	(SELECT armies.creature_id FROM armies WHERE armies.id = aa.army_id) 
	AND creatures_actions.action_id = aa.action_id) WHERE id =".$army_action['id'];
        //$update = "UPDATE armies_actions SET army_id=".$army_action['army_id'].", action_id=".$army_action['action_id']." , order=".$actions[$army_action['action_id']]['order'].", state='".$actions[$army_action['action_id']]['state']."' WHERE id =".$army_action['id'];
        $arrayUpdatesArmiesActions[] = $update;
        $sqlUpdatesArmiesActions = $sqlUpdatesArmiesActions.';'.$update;
        //$dom = $dom . "<li>".$update.";</li>";
        $count++;
    }
    //$dom = $dom . '</ul>';
    $dbconn = $db->aquery($sqlUpdatesArmiesActions);
    debug::log($dbconn,'admin:ArreglarArmies Arreglar ArmiesActions');
    ////////////////////////////////////////////////////////////////////
  break; 
  

case 'CrearIA':
  /*
$player = new player();
  if(!empty($_GET['IARegionCreation'])){
    $resp = $player->registerIA($_GET['IAName'],$_GET['IARegionCreation']);
  }
  else{
      $resp = $player->registerIA($_GET['IAName'],0);
  }*/
    debug::log('admin.php CrearIA Desactivado hasta nuevo aviso');
  
  
  
  if($resp['registered']){

    $region = $regionman->findById($resp['region_id']);
    $msn = 'Se creo su IA en el planeta['.$region->getParam('name').']';
    
      
  }
  else{
    $msn = $resp['error'];
  }  
break;
/////////////////////////Eliminacion de la IA
case 'EliminarIA':
   $playerman = playerman::singleton();
  $player = $playerman->findById($_GET['IAId']);
  //if($player->isArtificial())
 // {
    $resp = $player->remove();
    global $firephp;
    $firephp->log($resp);
    if($resp['deleted']){
     $msn = $resp['success'];  
    }
    else{
     $msn = $resp['error'];
    }
  //}
  //else{
  //   $msn = 'El jugador no es artificial';
  //}
  break;

default:
  $msn = 'Sin novedad en el frente';
  break;
} 
}
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

<head>

<title>Menu del Administrador</title>
<?php include_once("view/admin-header.php"); ?>
</head>

<body>
    <div id='Wrapper'>
    <?php include_once("view/admin-menu.php"); ?>
 
<h1>Menu del Administrador</h1>

    <form id="RecrearHomeColony" name="RecrearHomeColony" action="admin.php" method="get">
        <fieldset>
            <legend>Recrear Home Colony de todos los players humanos</legend>
    <input name="Action" type="hidden" value="RecrearHomeColony"/>
    <input id="RecrearHomeColonyButton" type="submit" value="Recrear Home Colony" />
    </fieldset>
    </form>  

    <form id="RecrearHomeRegion" name="RecrearHomeRegion" action="admin.php" method="get">
        <fieldset>
            <legend>Recrear Home Region de todos los players humanos</legend>
    <input name="Action" type="hidden" value="RecrearHomeRegion"/>
    <input id="RecrearHomeRegionButton" type="submit" value="Recrear Home Regions" />
    </fieldset>
    </form>     

    <form id="RecrearArmies" name="RecrearArmies" action="admin.php" method="get">
        <fieldset>
            <legend>Recrear Armies en Home Planet</legend>
    <input name="Action" type="hidden" value="RecrearArmies"/>
    <input id="RecrearArmies" type="submit" value="Recrear Armies" />
    </fieldset>
        <?php 
         //echo $dom;
        ?>
    </form>   

    <form id="RecrearIA" name="RecrearIA" action="admin.php" method="get">
        <fieldset>
            <legend>Recrear IA en Home Planet de todos los players humanos</legend>
    <input name="Action" type="hidden" value="RecrearIA"/>
    <input id="RecrearHomeRegionButton" type="submit" value="Recrear IA" />
    </fieldset>

    </form> 


    <form id="ArreglarArmies" name="ArreglarArmies" action="admin.php" method="get">
        <fieldset>
            <legend>Actualizar Armies con Info de Creatures</legend>
    <input name="Action" type="hidden" value="ArreglarArmies"/>
    <input id="ArreglarArmies" type="submit" value="Arreglar Armies" />
    </fieldset>

    </form>      

<form id="ArreglarPlayers" name="ArreglarPlayers" action="admin.php" method="get">
        <fieldset>
            <legend>Actualizar Players con Info de Creatures</legend>
    <input name="Action" type="hidden" value="ArreglarPlayers"/>
    <input id="ArreglarPlayers" type="submit" value="Arreglar Players" />
    </fieldset>

    </form> 

<form id="CrearIA" name="CrearIA" action="admin.php" method="get">
    <fieldset><legend>Crear nuevo AI</legend>
        Nombre de la IA:<input name="IAName" type="text" value="" />
        <br/>Id Region <input name="IARegionCreation" type="text" value="" />
        <input name="Action" type="hidden" value="CrearIA"/>
        <input id="SubmitCrearIA" type="submit" value="Crear una IA" />
    </fieldset>
</form>      


    <form id="EliminarIA" name="EliminarIA" action="admin.php" method="get">
        <fieldset><legend>Eliminar AI</legend>
            
    ID de la IA:<input name="IAId" type="text" value="" />
    <input name="Action" type="hidden" value="EliminarIA"/>
    <input id="SubmitEliminarIA" type="submit" value="Eliminar una IA" />
    </fieldset>
    </form>      

    <form id="AterrizarIA" name="AterrizarIA" action="admin.php" method="get">
    <fieldset><legend>Aterrizar Armies AI</legend>
    ID de la IA:<input name="IAId" type="text" value="" />
    <input name="Action" type="hidden" value="AterrizarIA"/>
    <input id="SubmitAterrizarIA" type="submit" value="Aterrizar la IA" />
    </fieldset>
    </form>      

<div><h2><?php //echo $msn;?></h2></div>

<h4>Lista de Jugadores</h4>
<table>
  <tr>
    <th>Jugadores Artificiales</th>
    <th>Jugadores Humanos</th>
  </tr>
  <tr>
    <td>
      <ul>
       <?php 
       $AIPlayers = new players();
       $AIPlayers->initByType(_X_PLAYER_TYPE_ARTIFICIAL);
       $raw = $AIPlayers->getRaw();
       foreach($raw AS $id => $player){
         echo "<li><a href='admin_armies.php?player_id=".$id."'>".$player['username']." [".$id."]</a></li>";
       }
       ?>
      </ul>      
    </td>
    <td>
      <?php 
       $HPlayers = new players();
       $HPlayers->initByType(_X_PLAYER_TYPE_HUMAN);
       $raw = $HPlayers->getRaw();
       foreach($raw AS $id => $player){
         echo "<li><a href='admin_armies.php?player_id=".$id."'>".$player['username']." [".$id."]</a></li>";
       }
       ?>
    </td>
  </tr>





</table>
    </div>
</body>
</html>
