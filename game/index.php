<?php

  if(!defined('_X_SECURE')){define("_X_SECURE",true);}
  
  
  $game_main_directory = getcwd();
  chdir(dirname ( __FILE__ ));
  require_once("lib/include.php");
  chdir($game_main_directory);
  
  /*****************************************************
  *  DATOS INICIO
  *****************************************************/
  /*
  $menu = new menuview();
  $menu->updateState();
  $menu->initByDefault();*/
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

<head>
<?php
    include_css("css/reset.css");
    include_css("css/jquery-ui/jquery-ui.css");
    include_css("css/jquery.qtip.css");
    include_css("css/general.css");
    include_css("css/colony.css");    
    include_css("css/start/jquery-ui.css");
    include_js("lib/jquery/jquery.js");
    include_js("lib/jquery/jquery.ui.js");
    include_js("lib/jquery/jquery.qtip2.js");
    include_js("lib/jquery/jquery.countdown.js");
    include_js("lib/js/general.js");    
    include_js("lib/js/colony.js");        
?>

<title><?php echo "Xhelos - Estrategia Via Web" ?></title>
</head>

<body id="Index">
<div id='Wrapper'>
<?php include('view/primary-menu.php') ?>
<?php include('view/menu.php') ?>
<div id="Options" class="mainpanel">	
<div class='first'>
    <h1><?php echo $menu->getTitle() ?></h1>
</div>
<div class='last'></div>
<div class="clear"></div>


</div>

<?php include('view/message.php') ?>

<div id="MainContainer">

    <div id="Data">
        <?php
        //Utils y Envio de Variables
        $util = new ajaxutil();
        $json = json::singleton();
        $state['global'] = $menu->getState();
        $state = json_encode($state);
        php2js($state,'xeno');
        php2js(_X_TOTAL_RESOURCES,'_total_resources');
        ?>
    </div>    
</div>	
    
</div>
</body>
</html>

