<?php

$text='Bienvenido';
  if(!defined('_X_SECURE')){define("_X_SECURE",true);}
  if(!defined('_X_NO_INIT')){define("_X_NO_INIT",true);}
  if(!defined('_X_ADMIN_INIT')){define("_X_ADMIN_INIT",true);}

 $game_admin_directory = getcwd();
  chdir(dirname ( __FILE__ ));
  
  include_once("lib/include.php");
  require_once("lib/php/player.class.php");
  require_once("lib/php/playerman.class.php");
  require_once("lib/php/armies.class.php");
  require_once("lib/php/army.class.php");
  require_once("lib/php/playerman.class.php");
  require_once("lib/php/db.class.php");
  require_once("lib/php/region.class.php");
  require_once("lib/php/regionman.class.php");
  require_once("lib/php/battleutil.class.php");
  require_once("lib/php/battleman.class.php");
  

  chdir($game_admin_directory);
  $db = db_conectar();
  $playerman = playerman::singleton();
  $armyman = armyman::singleton();
  $battleutil = new battleutil();
  $battleman = battleman::singleton();
  $regionman = regionman::singleton();

  //////////////////////////CALCULO DE ACCIONES//////////////
  switch($_GET['action']){
/////////////////////////Creacion de la IA  
case 'crearArmies':
    $cantidad = $_GET['cantidad'];
    $creature_id = $_GET['creature_id'];
    $region_id = $_GET['region_id'];
    $player_id = $_GET['player_id'];
    $side = $_GET['side'];

    $region = $regionman->findById($region_id);
    $maputil = new maputil($region->getMap(), $region->getParam('x'), $region->getParam('y'),$region_id);
    $maputil->initBattleMaps();

    for($x=0;$x<$cantidad;$x++){
        //$player = $playerman->findById($player_id);
        $dt = $battleutil->addArmyInRegionDT($creature_id,$region_id,$player_id,$position);
        if(!$dt->isValid()){
            $text = $dt->getText();
            break;
        }
        else{
            $data = $dt->getData();
            $coord = $battleutil->calculateCoordsFromSide($data['army_id'],$region_id,$side,$maputil);
            $army = $armyman->findById($data['army_id']);
            $battle = $battleman->findActiveByRegionId($region_id);
            $turn = $battle->getTurn();
            $phase = $battle->getPhase();
            $army->prepareForBattle($region_id, $turn, $phase);
            $army->enterRegion($region_id,$coord['x'],$coord['y']);
        }
    }
    $text = $dt->getText();

  break;

case 'OrbitarIA':
  
break;
/////////////////////////Eliminacion de la IA
case 'EliminarIA':
  break;

default:
  $msn = 'Sin novedad en el frente';
        debug::log("DEFAULT:".$_GET['action'],"admin_armies");
  break;
} 
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

<head>

<title>Menu del Administrador Armies</title>
        <?php include_once("view/admin-header.php"); ?>
</head>

<body>
    <div id='Wrapper'>
    <?php include_once("view/admin-menu.php"); ?>
        
<h1>Mantenimiento de Armies</h1>
<h2><?php echo $text;?></h2>
        <form action="admin_armies.php">
            <legend>Creacion de Nuevos Armies</legend>
            <label for="cantidad">Cantidad</label> <input type="text" name="cantidad"></input>
            <label for="creature_id">Id Criatura</label> <input type="text" name="creature_id"></input>
            <label for="region_id">Id Region</label> <input type="text" name="region_id"></input>
            <label for="player_id">Id Jugador</label> <input type="text" name="player_id"></input>
            <label for="side">Id Jugador</label> <select name="side">
                <option value="<?php echo _X_ARMY_SIDE_NONE ?>">CENTER</option>
                <option value="<?php echo _X_ARMY_SIDE_TOP ?>">TOP</option>
                <option value="<?php echo _X_ARMY_SIDE_BOTTOM ?>">BOTTOM</option>
                <option value="<?php echo _X_ARMY_SIDE_LEFT ?>">LEFT</option>
                <option value="<?php echo _X_ARMY_SIDE_RIGHT ?>">RIGHT</option>

            </select>
            <input type="hidden" value="crearArmies" name="action"/>

            <input type="submit" value="Crear Nuevo Arm(y)(es)" name="BotonCrearArmies"  />
        </form>
    </div>
</body>
</html>
