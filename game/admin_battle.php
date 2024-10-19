<?php
  if(!defined('_X_SECURE')){define("_X_SECURE",true);}
  if(!defined('_X_NO_INIT')){define("_X_NO_INIT",true);}
  if(!defined('_X_ADMIN_INIT')){define("_X_ADMIN_INIT",true);}
  

 $game_admin_directory = getcwd();
  chdir(dirname ( __FILE__ ));
  include_once("lib/include.php");
  require_once("lib/php/battle.class.php");  
  require_once("lib/php/battles.class.php");
  require_once("lib/php/region.class.php");
  require_once("lib/php/regionman.class.php");
  require_once("lib/php/player.class.php");
  require_once("lib/php/playerman.class.php");
  require_once("lib/php/datatransfer.class.php");
  chdir($game_admin_directory);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

<head>
        <?php include_once("view/admin-header.php"); ?>
<title>Menu de Prueba</title>
</head>

<body>
    <div id='Wrapper'>
    <?php include_once("view/admin-menu.php"); ?>
<h1>Menu del Pruebas</h1>

<h3>Crear nuevo AI</h3>
<ul>
  <li>
    <form id="CrearBatalla" name="CrearBatalla" action="admin_test.php" method="get">
    ID de la region de batalla:<input name="region_id" type="text" value="" />
    ID del jugador que inicia la batalla:<input name="player_attacker" type="text" value="" />
    <input name="Action" type="hidden" value="CrearBatalla"/>
    <input id="SubmitCrearIA" type="submit" value="Crear una Batalla" />
    </form>      
  </li>
</ul>

<?php 
switch($_GET['Action']){
/////////////////////////Creacion de la IA  
  case 'CrearBatalla':
    $pid = $_GET['player_attacker'];
    $rid = $_GET['region_id'];
    
    $battle = new battle();
    
    $dt = $battle->register($rid,$pid);
    if($dt->isValid()){
      $msn= 'Batalla Creada';  
    }
    else{
      $text = $dt->msg();
      $msn = $text['text'];
      
    }
    break;

  default:
    $msn = 'Sin novedad en el frente';
    break;
}	
?>
<div><h2><?php echo $msn;?></h2></div>

<h1>Lista de Batallas</h1>
<ul>
 <?php 
  $battles = new battles();
  $raw = $battles->initAll();
  
  $playerman = playerman::singleton();
  foreach($raw AS $id => $battle){

    $player = $playerman->findById($battle['player_attacker']);
    //$resp = $player->initById($battle['player_attacker']);

    if($player->exist()){
        $regionman = regionman::singleton();
        $region = $regionman->findById($battle['region_id']);
        echo "<li>ID[".$id."] Atacante[".$player->getName()."] Region[".$region->getName()."] Start[".$battle['start']."] End[".$battle['end']."] Turno[".$battle['turn']."]  Estado[".$battle['state']."]</li>";
    }
   }  
 ?> 
</ul>
    </div>
</body>
</html>
