<?php
  if(!defined('_X_SECURE')){define("_X_SECURE",true);}
  $game_main_directory = getcwd();
  chdir(dirname ( __FILE__ ));
  require_once("lib/include.php");
  chdir($game_main_directory);
  
  /*****************************************************
  *  DATOS INICIO
  *****************************************************/
  //Obtener Estado
  $colonyId = viewutil::validParam("colony_id");//[TODO] Cambiar esto por amor a dios  [TODO2] Que porque!?
  //Generacion del Objeto $gs
  $gs = new globalview($colonyId,"colony");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

<head>
<?php

    include('view/html-header.php');
    include_css("css/colony.css");    

    include_css("css/start/jquery-ui.css");

    //include_js("lib/jquery/jquery.qtip2.js");
    include_js("lib/jquery/jquery.countdown.js"); 
    include_js("lib/js/colony.js");        
?>

    <title><?php echo $gs->getTitle() ?></title>
</head>

<body id="Colony">
    
<div id='Wrapper' class='colony'>
<?php include('view/primary-menu.php') ?>
<?php include('view/menu.php') ?>
<?php include('view/message.php') ?>

    <div id="MainContainer">
    <div id="SecondaryPanel">
    </div>
    <div id="PrimaryPanel">
    <?php 
    $gs->render(); ?>
    </div> 
</div>	
    
</div>
</body>
</html>

