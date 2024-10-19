<?php

  if(!defined('_X_SECURE')){define("_X_SECURE",true);}

  $game_ajaxplanet_directory = getcwd();
  chdir(dirname ( __FILE__ ));
  require_once("../lib/include.php");
  chdir($game_ajaxplanet_directory);
  //Inicializacion de Datos
  $regionJax = new regionjax();
  
  if( $regionJax->isOk() ){
    switch($regionJax->getRequestParam("ajax_action"))
    {
         /* *****************************************************
         * Fase Accion - Accion del turno de una unidad
         *******************************************************/
        case 'action':
          $regionJax->executeArmyAction();
          $regionJax->send();          
          
          break;

         /* *****************************************************
         * Fase Batalla - Boton Finalizar Turno
         *******************************************************/
        case 'endmyturn':
         $regionJax->executePassTurn();
         $regionJax->send();   
        break;

        /* *****************************************************
        * Para Obtener el estado Comun de la Region, ya sea por
        * ele stado de una batalla En Fase de Espera, En Fase de Batalla
         * Batalla terminada o una batalla que no existe
        *******************************************************/
        case 'status':
            $regionJax->executeCalculateState();
            $regionJax->send();
        break;

        case 'error':
          $msg->error('Error en el envio', 'error');
          $regionJax->setBadDt('Error en el envio');
          $regionJax->send();
         break;

        case 'tutorial_on':
            $regionJax->executeTutorialOn();
            $regionJax->send();            
         break;

        case 'tutorial_off':
            $regionJax->executeTutorialOff();
            $regionJax->send();                
         break;

        default:
          debug::console('ajaxRegion ajaxAction default NO EXISTE['.$ajaxAction.']');
          $dt = new datatransfer();
          $dt->validWarning("Sin datos - Default");
          $regionJax->setIfOldDtIsOk($dt);
          $regionJax->send();   
        break;
        /* *****************************************************
         * Fase Tactica
         *******************************************************/
        case 'tactical':
            
            $regionJax->executeChangePlayerPhaseActionFromTacticToBattle();
            $regionJax->send();
            break;
        }
  }
  else{
      $regionJax->send();
  }
?>
