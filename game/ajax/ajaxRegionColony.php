<?php

  if(!defined('_X_SECURE')){define("_X_SECURE",true);}

  $game_ajaxplanet_directory = getcwd();
  chdir(dirname ( __FILE__ ));
  require_once("../lib/include.php");
  chdir($game_ajaxplanet_directory);


  $rcj = new regioncolonyjax();
  if( $rcj->isOk() ){
    switch($rcj->getRequestParam("ajax_action"))
    {
         /* *****************************************************
         * createColony - Crea una colonia en el lugar deseado
         *******************************************************/
        case 'createColony': 
            $rcj->createColony();
        break;
  }
}
?>
