<?php

  if(!defined('_X_SECURE')){define("_X_SECURE",true);}

  $game_main_directory = getcwd();
  chdir(dirname ( __FILE__ ));
	require_once("lib/include.php"); 
  require_once("lib/php/obj/player.class.php");  
  require_once("lib/php/json.class.php");
  chdir($game_main_directory);
  //global $firephp;
  //$firephp->log($_SESSION);
  //2) Inicializacion del Player
  $playerman = playerman::singleton();
  $player = $playerman->findById($_SESSION['xid']);
  
  //Ultima seguridad, si la variable player no se puede cargar entonces no hacer nada
  //hay un detenedor en el archivo init.php pero parece que no funciona
  if(!isset($player)||!($player->exist())){
    die();
  }
  //$result= $player->register('Mainolo','Mainolo@ymail.com','3hghfghffj');
  $page='armies';
  $gs = new globalview($regionId,"region");
   
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

<head>
<?php

  include_css("css/general.css");
	include_css("css/main.css");
	include_css("css/main_sendarmies.css");
	include_css("css/main_seeregion.css");
	include_css("css/jquery.tabs.css");
	include_js("lib/js/json_parse_state.js");
	include_js("lib/jquery/jquery.js");
	include_js("lib/jquery/jquery.ui.js");
	include_js("lib/jquery/jquery.countdown.js");
	include_js("lib/js/main.js");
	include_js("lib/js/main_sendarmies.js");
	include_js("lib/js/main_seeregion.js");
	include_js("lib/js/raphael-min.js");
  include_js("lib/js/general.js");
?>

<title><?php echo _TITLE._SUBTITLE ?></title>
</head>

<body>

	<div id='Wrapper'>
	  <?php include('view/primary-menu.php') ?>
	  <?php include('view/menu.php') ?>
		<!-- HEADER -->
		
		<!-- CONTENIDO -->		
		<div id="Manager" class="clear">
			<!-- CONTENIDO -->	<!-- MENU TAB -->
			<ul class="grindyMenu">
				<li><a href="#SendArmies">Enviar Unidades</a></li>
				<li><a href="#Avatar">Caracter</a></li>
				<li><a href="#SeeRegion">Navegacion</a></li>
			</ul>
		
			
			<!-- CONTENIDO -->	<!-- Enviar Unidades -->
			<div id="SendArmies"  class="ui-tabs-hide">
				
				<!-- CONTENIDO --><!-- Enviar Unidades --><!-- Unidades Disponibles -->
				<div id="AvailableArmies" class="container">
					<h3>Unidades Disponibles</h3>	
					<ul>
					</ul>
				</div>
				
				<!-- CONTENIDO --><!-- Enviar Unidades --><!-- Seleccionar Region -->
				<div id="SelectRegion" class="container">
					<h3>Selecciona Region</h3>
					<form action="<?php echo _X_FILE_REGION ?>" method=post target=_self>
					<select name="region_id" id="selRegion">
					</select>
					<input id="BtnSendArmies"  name="BtnsendArmies" type="button" value="Enviar Unidades" />
					<input id="Btn_sa_SeeRegion"  name="Btn_sa_SeeRegion" type="submit" value="Ver Region" />
					</form>	
					<div id="sa_helpWrapper" class="help_wrapper"></div>					
				</div>
				
				
				<!-- CONTENIDO --><!-- Enviar Unidades --><!-- Unidades a Enviar -->			
				<div id="ArmiesToSend"  class ="container">
					<h3>Unidades a Enviar</h3>
					<ul>	
					</ul>
				</div>
				
				<!-- CONTENIDO --><!-- Enviar Unidades --><!-- Unidades Enviadas -->			
				<div id="SendedArmies"  class ="container">
					<h3>Unidades Enviadas</h3>
					<ul>	
					</ul>
				</div>
	
			</div>
			
			     <!-- CONTENIDO -->  <!-- Visitar Regiones -->
      <div id="SeeRegion"  class="ui-tabs-hide">
        <!-- CONTENIDO --><!-- Visitar Regiones --><!-- ver Region -->
        <div id="SeeRegion" class="container">
          <form action="<?php echo _X_FILE_REGION ?>" method=post target=_self>
          <h3>Selecciona Region</h3>
          <select name="SelSeeRegion" id="SelSeeRegion">
          </select>
          <input id="BtnSeeRegion"  name="BtnSeeRegion" type="submit" value="ver Region" />
          </form>         
        </div>
      
      </div>  
			
			
			<!-- CONTENIDO -->	<!-- Avatar -->
			<div id="Avatar"  class="ui-tabs-hide">
				<div id="PlayerInfo" class="container">
					<img src="img/user/1.png" alt="Usuario 1" />
					<h1><?php echo $player->getParam('username');?></h1>
					<h2>Level 2 Warmonger</h2>
				</div>	
			</div>
	</div>	
		

	</div><!-- Wrapper -->
</body>
