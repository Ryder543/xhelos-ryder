<?php

$msn='Bienvenido';
  if(!defined('_X_SECURE')){define("_X_SECURE",true);}
  if(!defined('_X_NO_INIT')){define("_X_NO_INIT",true);}
  if(!defined('_X_ADMIN_INIT')){define("_X_ADMIN_INIT",true);}

 $game_admin_directory = getcwd();
  chdir(dirname ( __FILE__ ));
   require_once("lib/include.php"); 
  chdir($game_admin_directory);
    $playerman = playerman::singleton(); 
  //////////////////////////CALCULO DE ACCIONES//////////////
  $dom = "";
  switch($_GET['action']){
/////////////////////////Creacion de la IA  
  
 
   case 'deletePlayer':
       $playerId = $_GET['id'];
       $entityutil = new entityutil();
       $dt = $entityutil->removePlayer($playerId);
       $oPlayer = $playerman->findById($playerId);
       if($dt->ok()){
           $msn = 'Borrando Player '.$oPlayer->getName."[".$oPlayer->getId()."][".$oPlayer->getParam("mail")."]";  
       }
       else{
           $msn = 'No se pudo borrar Player '.$oPlayer->getName."[".$oPlayer->getId()."] ".$dt->getText();  
       }
       $dom = searchSpammers();
       break;
   case 'searchOrphanPlayer':
       
       $dom = searchSpammers();
     // debug::log($orphan);
     
     break;
 
     
   case 'deleteOrphanPlayer':   
   //Obteniendo la Lista de Jugadores
   /*$sql = "SELECT u.uid, u.name FROM {users} u
   INNER JOIN {users_roles} ur ON u.uid = ur.uid
   WHERE ur.rid = %d";
    //replace the 3 with whatever role id you are looking for
    $results = db_query($sql, 3);

    while ($result = db_fetch_object($results)) {
       //do whatever you need to with the results
     }*/  
        
      $msn = 'Borrando Player';        
      break;
    default:
      $msn = 'Sin novedad en el frente';
    break;
} 
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

<head>

<title>Menu Administrador de Players</title>
        <?php include_once("view/admin-header.php"); ?>
</head>

<body>
    <div id='Wrapper'>
    <?php include_once("view/admin-menu.php"); ?>
        
<h1>Mantenimiento de Players</h1>
<h2><?php echo $msn;?></h2>
        <form action="admin_players.php">
            <fieldset>
            <legend>Eliminacion de Player Huerfano</legend>
            <label for="player_id">Id Jugador</label> <input type="text" name="player_id"></input>
            <input type="hidden" value="deleteOrphanPlayer" name="action"/>
            <input type="submit" value="Eliminar Player" name="BotonEliminarArmies"  />
            </fieldset>
        </form>

        <form action="admin_players.php">
            <fieldset>
            <legend>Busqueda de Players Huerfano</legend>
            <input type="hidden" value="searchOrphanPlayer" name="action"/>
            <input type="submit" value="Buscar Players Huerfanos" name="BotonBuscarPlayers"  />
            </fieldset>
        </form>
        
        <div id="Content"><?php echo $dom?></div>
    
    </div>
</body>
</html>


<?php
function searchSpammers(){
     $dom = '';     //1)Buscar de Drupal a los huerfanos
   $sql = "SELECT u.uid, u.name, xs.xid FROM {users} u
   INNER JOIN {users_roles} ur ON u.uid = ur.uid
   INNER JOIN {xeno_session} xs ON u.uid = xs.uid 
   WHERE ur.rid = %d";
    //replace the 3 with whatever role id you are looking for
    $results = db_query($sql, 4); //4 son solo jugadores

    $players = new players();
    $players->initAllHuman();
    $orphans = array();
    $druplayers = array();
    while ($result = db_fetch_array($results)) {
        $druplayers[]= $result;
    }
     
    
    foreach ($players as $key2 => $player) {
        $finded = false;
        foreach ($druplayers as $key => $druplayer){
            if($player->getId() == $druplayer['xid']){
                $finded = true;
                break;
            }
        }
        if(!$finded){
                  $orphans[] = $player;
        }

      }
      
      foreach($orphans  as $key3 => $orphan){
          $dom = $dom."<a href='admin_players.php?action=deletePlayer&id=".$orphan->getId()."' > Eliminar a ".$orphan->getName()."</a><span>[".$orphan->getParam("mail")."]</span></br>";
      }
      return $dom;
}
?>