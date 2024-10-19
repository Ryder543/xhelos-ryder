<?php
$msn = 'Testing de Utilidades';
if (!defined('_X_SECURE')) {
    define("_X_SECURE", true);
}
if (!defined('_X_NO_INIT')) {
    define("_X_NO_INIT", true);
}
if (!defined('_X_ADMIN_INIT')) {
    define("_X_ADMIN_INIT", true);
}

//////////////////////////Directorios y Variables//////////////
$game_admin_directory = getcwd();
chdir(dirname(__FILE__));
require_once("lib/include.php");
chdir($game_admin_directory);
$playerman = playerman::singleton();
//////////////////////////CALCULO DE ACCIONES//////////////
$dom = "";
if (!isset($_GET['action'])) {
    $_GET['action'] = 'defaultea';
}
switch ($_GET['action']) {
/////////////////////////Creacion de la IA  
    case 'deletePlanet':
        $planetId = $_GET['planet_id'];        
        $epu = new entityplanetutil();
        $dt = $epu->deletePlanetDt($planetId);
        if($dt->ok()){
            $msn = 'Planeta Eliminado';
        }
        else{
            $msn = 'Planeta NO pudo ser Eliminado. '.$dt->getText();
        }
        
        break;

    case 'createInitialPlanet':
        $starId = $_GET['star_id'];
        $entityutil = new entityutil();
        $epu = new entityplanetutil();
        $eru = new entityregionutil();

        $dt = $epu->createInitialPlanetWithRegionsWithouthMapsDt($starId);
        if ($dt->ok()) {
            $data = $dt->getData();
            $pv = new planetview($data['id']);
            $dom = $pv->render();
            $msn = 'Planeta Creado';
        } else {
            $msn = 'No se creo Planeta.'.$dt->getText();
        }
        break;

    default:
        $msn = 'Sin novedad en el frente';
        break;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

    <head>

        <title>Menu Administrador de Pruebas de Codigo</title>
        <?php include_once("view/admin-header.php"); ?>
    </head>

    <body>
        <div id='Wrapper'>
            <?php include_once("view/admin-menu.php"); ?>

            <h1>Pruebas</h1>
            <h2><?php echo $msn; ?></h2>
            <form action="admin_planet.php">
                <fieldset>
                    <legend>Creacion de Planeta Inicial</legend>
                    <label for="star_id">Id Estrella</label> <input type="text" name="star_id"></input>
                    <input type="hidden" value="createInitialPlanet" name="action"/>
                    <input type="submit" value="Crear nuevo Planeta" name="BotonEliminarArmies"  />
                </fieldset>
            </form>

            <form action="admin_planet.php">
                <fieldset>
                    <legend>Eliminacion de Planeta con sus Regiones</legend>
                    <label for="star_id">Id Planeta</label> <input type="text" name="planet_id"></input>
                    <input type="hidden" value="deletePlanet" name="action"/>
                    <input type="submit" value="Eliminar Planeta" name="BotonEliminarPlaneta"  />
                </fieldset>
            </form>


            <div id="Content"><?php echo $dom ?></div>

        </div>
    </body>
</html>