<?php
  if(!defined('_X_SECURE')){define("_X_SECURE",true);}
  require_once("lib/include.php"); 
  
   $gs = new globalview(_X_GALAXY_NAME,"galaxy");
  
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

<head>
<?php
    include('view/html-header.php');
    include_css("css/galaxy.css");
    include_js("lib/js/raphael.js");
    include_js("lib/js/galaxy.js");
?>

    <title><?php echo $gs->getTitle() ?></title>
</head>

<body>
    <div id='Wrapper'>
         <?php include('view/primary-menu.php') ?>
         <?php include('view/menu.php') ?>

          <div id="MainContainer">       
         <div id="Galaxy">
           <div id="GalaxySystem"><div id="GalaxySystemInner">
           <?php  $gs->render();?>
           </div></div>

             <div id="StarInfo" class="ui-state-transparent">
                 <div id="StarInfoInner" class="inner">
                     <p>Seleccione una estrella para ver su informacion </p>
                     <img src="img/web/title-block2.png" class="img-block">
                </div>
             </div>

         </div>

    </div>

    </div>
</body>

