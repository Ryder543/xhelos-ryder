<?php

  if(!defined('_X_SECURE')){define("_X_SECURE",true);}

  $game_ajaxplanet_directory = getcwd();
  chdir(dirname ( __FILE__ ));
  require_once("../lib/include.php");
  chdir($game_ajaxplanet_directory);


  $pj = new planetjax();
  if( $pj->isOk() ){
    switch($pj->getRequestParam("ajax_action"))
    {
         /* *****************************************************
         * createColony - Crea una colonia en el lugar deseado
         *******************************************************/
        case 'create_colony': 
            $pj->executeCreateSecundaryColony();
            $pj->send();
        break;
         /* *****************************************************
         * deleteColony - Crea una colonia en el lugar deseado
         *******************************************************/
        case 'delete_colony': 
            $pj->executeDeleteSecundaryColony();
            $pj->send();
        break;
    
     case 'status':
         
            $pj->setIfAllOkText("Estas en el ".$pj->getOState()->getTitle());
            $pj->send();
            //$dt->success('Sin novedad en el frente');
            // $msn->message('Se Mando a mover', 'success');
            //sendInfo($dt);
        break;
        
        case 'new_team':
           $pj->executeCreateNewTeam();
           $pj->send();            
            break;

        case 'army_transfer_team':
           $pj->executeArmyTransferTeam();
           $pj->send();          
           break;

         case 'delete_team':   
           $pj->executeDeleteTeam();
           $pj->send(); 
        
            break;      
        /* *****************************************************
         * Atacar Region
         ********************************************************/
        case 'attack_region':
           $pj->executeAttackRegion();
           $pj->send(); 
            break;

 
        default:
           $pj->setWarningDt('Sin datos - Default['.$pj->getRequestParam("ajax_action").']');
            debug::warning('Sin datos - Default['.$pj->getRequestParam("ajax_action").']');
           $pj->send(); 
        break;
  }
}
?>
