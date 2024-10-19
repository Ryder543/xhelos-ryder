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
  require_once("lib/php/entityutil.class.php");
  require_once("lib/php/regionstate.class.php");
  chdir($game_admin_directory);  
  $db = db_conectar();
  $playerman = playerman::singleton();
  $armyman = armyman::singleton();

  $battleutil = new battleutil();
  $battleman = battleman::singleton();
  $regionman = regionman::singleton();

  $entityutil = new entityutil();
  $db->begin();
  
  $regionCreated = array();
  
  //////////////////////////CALCULO DE ACCIONES//////////////
   switch($_GET['action']){
       case 'SolarSystemCreation':    
           $name = $_GET['SolarSystemName'];
           $quantity = $_GET['SolarSystemPlanetNumber'];
           $dt = $entityutil->createRandomStarDt($name);
           debug::log($dt,'admin_places.php->SolarSystemCreation dt');
           if($dt->isValid()){
               $starCreationData = $dt->getData();
               $dt2 = $entityutil->createRandomPlanetsDt($quantity,$starCreationData['id']);
               if($dt2->isValid()){
                    $db->commit();    
               }
               else{
                   debug::warning($dt2,'admin_places.php->SolarSystemCreation Planetas no fueron creados');
                   $db->rollback();
               }
               
           }
           else{
               debug::warning($dt,'admin_places.php->SolarSystemCreation Estrella no fue creado');
               $db->rollback();
           }   
        break;
        
        case 'RegionCreation':
            $planetId = $_GET['PlanetId'];
            $regionNumber = $_GET['RegionsNumber'];
            $dt = $entityutil->createRandomRegionsDt($planetId, $regionNumber);
            
            if($dt->isValid()){
                $db->commit();
                $regionCreated = $dt->getData();
            }
            else{
                debug::error('admin_places RegionCreation');
                debug::log($dt,'DataTransfer');
                $db->rollback();
            }
        break;
           
       break;
       
    default:
        debug::log("Default");
}
  ?>
  
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <?php include_css("css/region.css"); ?>
        <?php include_once("view/admin-header.php"); ?>
        <title>Menu de Administracion de Lugares</title>
    </head>
    <body>
         <div id='Wrapper'>
    <?php include_once("view/admin-menu.php"); ?>
             
        <h1>Creacion de Lugares</h1>
        <form>
            <fieldset>
                <legend>Creacion del Sistema Solar</legend>
                <label for="SolarSystemName">Nombre del Sol:</label><input id="SolarSystemName" name="SolarSystemName" />
                <label for="SolarSystemPlanetNumber">Numero de Planetas:</label><input id="SolarSystemPlanetNumber" name="SolarSystemPlanetNumber" />
                <input type="hidden" id="SolarSystemCreation" name="action" value="SolarSystemCreation" />
                <input type="submit" val="Crear Sistema Solar" />   
            </fieldset>
        </form>
        
        <form>
            <fieldset>
                <legend>Creacion de Regiones</legend>
                <label for="PlanetId">Id del Planeta: </label><input id="PlanetId" name="PlanetId" />
                <label for="RegionsNumber">Numero de Regiones: </label><input id="RegionsNumber" name="RegionsNumber" />
                <input type="hidden" id="RegionCreation" name="action" value="RegionCreation" />
                <input type="submit" val="Crear Regiones" />   
                
                
            </fieldset>
        </form>
        
        <?php
        /*
        foreach($regionCreated as $i => $region_id){
            debug::info('Region Creada['.$region_id.']','admin_places');
            echo "<div class='ViewPort'>";
            $regionstate = new regionstate($region_id,1);
            $regionstate->renderMap();
            echo "</div>";
        }*/
        
        
        ?>
         </div>
    </body>
</html>
