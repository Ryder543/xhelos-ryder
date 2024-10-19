<?php

  if(!defined('_X_SECURE')){define("_X_SECURE",true);}

  $game_ajaxplanet_directory = getcwd();
  chdir(dirname ( __FILE__ ));
  require_once("../lib/include.php");
  chdir($game_ajaxplanet_directory);
  //Inicializacion de Datos
  $debug = new debug();
  $db = db::singleton();
  $json = json::singleton();
  $dt = new datatransfer();
  $dt->success('ajaxRegionTutorial temporal');//  Debe de haber algun tipo de validacion aqui?
  $regionman = regionman::singleton();
  
  
  
  $regionId = validator::filter_integer($_POST['region_id']);
  $ajaxAction = $_POST['ajax_action'];
  
  $menustate = new menuview();
  $menustate->initByRegionId($regionId);
  //$menustate->updateState();
  
  //Sanitizacion de Variables Externas
  //$action = $_POST['ajax_action'];
  


  //1)gamestate actualiza la informacion del planeta
  //$regionstate = new regionstate($region_id,$player_id);
  //(($dt = $planetstate->updateState();
  $db->begin();
  

//$debug->initProfiler();
  
if($dt->ok()){
    
    switch($ajaxAction)
    {

        case 'next_step':
            $phase = $menustate->nextTutorialStep();
            $dt = new datatransfer();
            $dt->success('Se avanzo el tutorial');
            $dt->setData($phase);
            sendInfo($dt);
            
            break;

        case 'prev_step':
            $phase = $menustate->prevTutorialStep();
            $dt = new datatransfer();
            $dt->success('Se retrocedio el tutorial');
            $dt->setData($phase);
            sendInfo($dt);
            break;

        default:
           $dt->validWarning('Sin datos - Default');
          //$msn->message('Sin datos - Default', 'warning');
          sendInfo($dt->getMsg());
        break;


    }//switch
}//if
else{
   sendInfo($dt->getMsg()); 
}




//Funcion que Wrapea el envio de mensajes al Cliente
function sendInfo($msg)
{
  global $db; 
  if($msg->ok())  {
        $db->commit();
        //debug::log($msg,'ajaxPLanet Commit');
  }
  else{
        $db->rollback();
        //debug::log($msg,'ajaxPLanet Rollback');
  }
  global $planetstate;  

  global $menustate;
  //$db->commit();
  
  $ret = Array();
  $ret['msg'] = $msg;
  //debug::log($msg,'ajaxRegionTutorial');
  
  echo ajaxutil::var2json($msg);
  //ajaxutil::sendAjax($msg,$planetstate, $menustate);
  
}
?>
