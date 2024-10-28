 <?php
 
 //Requiere de lo siguiente:
 //variable $page que contiene el nombre identificador de la pagina
 //variable $menu que contiene el objeto del cual podremos obtener el nombre del menu
 
 require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
 
 //1) Ocultacion de Menus
 
 (empty($_SESSION['last_region']))? $showRegion=' hidden ':$showRegion='';
 (empty($_SESSION['last_planet']))? $showPlanet=' hidden ':$showPlanet='';
 (empty($_SESSION['last_star']))? $showStar=' hidden ':$showStar='';
 
 global $firephp;
 //$firephp->log($_SESSION,'Sesion');
 
 //$firephp->log($showPlanet,'planet');
 //$firephp->log($showRegion,'region');
 
 //2) Variables Propias del Menu
 $menu = $gs->getMenuState();
 $resources = ajaxutil::getActiveResources();
 
 switch($menu->getPage()){
      case 'colony':
        
     break;  
     
   case 'region':
     break;
  case 'planet': 
     break;

   case 'star':
     
     break;
     
   case 'galaxy':

     break;  
      
   case 'armies':
     $sub_nav_hidden='hidden';
     $sub_arm_hidden='';
     $sub_col_hidden='hidden';
     $sub_ali_hidden='hidden';
     
     $men_nav_active='';
     $men_arm_active='active';
     $men_col_active='';
     $men_ali_active='';
     break;
     

  }
global $firephp;


?>
<div id="Header">
  <div id="DataCenter" class="ui-state-hover">
     <div id="ExtractedRes" class="ui-state-active">
        <ul>
          <li id="Res1W"><span  id="Res1"></span><img src="img/web/icon_res<?php echo $resources[1]['id']?>.png" alt="<?php echo $resources[1]['name'] ?>"></li>
          <li id="Res2W"><span  id="Res2"></span><img src="img/web/icon_res<?php echo $resources[2]['id']?>.png" alt="<?php echo $resources[2]['name'] ?>"></li>
          <li id="Res3W"><span  id="Res3"></span><img src="img/web/icon_res<?php echo $resources[3]['id']?>.png" alt="<?php echo $resources[3]['name'] ?>"></li>
        </ul>
     </div>

    <div id="PlayerInfo" class="ui-state-active">
        <img class="user-small" src="img/user/1_small.png" alt="<?php echo 'El Señor '.$menu->getPlayer()->getParam('username') ?>"/>
        <h1><?php echo $menu->getPlayer()->getParam('username') ?></h1>
        <h2>Level 2 Warmonger</h2>
        <div id="PlayerEnergyW"><h3><span class="title" id="PlayerEnergy"><?php //echo $menu->getPlayer()->getParam('actual_energy') ?></span><img src="img/web/icon_mana.png" alt="Mana"></h3></div>
        <div class="ui-buttonset" id="ColoniesSelectionWrapper"><a href="colony.php?colony_id=<?php echo $menu->getColonyId() ?>" id="MoveToColony"><?php echo $menu->getColonyName() ?></a><button id="SelectColony">Seleccionar</button></div>
        
    </div>

    <?php
    $activeArmies = 0;
    $controlledPlanets = 0;
    $unitCounts = [];

    if ($menu->getPlayer()) {
      $player = $menu->getPlayer();

      $activeArmies = method_exists($player, 'getActiveArmies') ? $player->getActiveArmies() : 0;
      $controlledPlanets = method_exists($player, 'getControlledPlanets') ? $player->getControlledPlanets() : 0;
      $unitCounts = method_exists($player, 'getUnitCounts') ? $player->getUnitCounts() : [];
    }
    ?>

    <?php include(dirname(__FILE__) . '/../player_view.php'); ?>
    
      <!-- <div class="clear">.</div> -->
     <div id="NormalRes" class="ui-state-active ui-helper-clearfix">
        <ul>
          <li id="Res4W"><span  id="Res4"></span> <img src="img/web/icon_res<?php echo $resources[4]['id']?>.png" alt="<?php echo $resources[4]['name'] ?>"></li>
          <li id="Res5W"><span  id="Res5"></span> <img src="img/web/icon_res<?php echo $resources[5]['id']?>.png" alt="<?php echo $resources[5]['name'] ?>"></li>
          <li id="Res6W"><span  id="Res6"></span> <img src="img/web/icon_res<?php echo $resources[6]['id']?>.png" alt="<?php echo $resources[6]['name'] ?>"></li>
          <li id="Res7W"><span  id="Res7"></span> <img src="img/web/icon_res<?php echo $resources[7]['id']?>.png" alt="<?php echo $resources[7]['name'] ?>"></li>
          <li id="Res8W"><span  id="Res8"></span> <img src="img/web/icon_res<?php echo $resources[8]['id']?>.png" alt="<?php echo $resources[8]['name'] ?>"></li>
        </ul>
     </div>
  </div>
  
  
  <div id="MenuContainer" class="ui-helper-clearfix">
  <ul id="Menu" class="menu ui-helper-clearfix" >
  <li id="Navigation" class="<?php echo $menu->men_nav_active;?>"><img src="img/web/menu_navigation.png"><span class="title">Navegacion</span></li>
  <li id="Army"  class="<?php echo $menu->men_arm_active;?>"><img src="img/web/menu_army.png"><span class="title">Ejercito</span></li>
  <li id="Colony"  class="<?php echo $menu->men_col_active;?>"><img src="img/web/menu_colony.png"><span class="title">Colonia</span></li>
  <li id="Allies"  class="<?php echo $menu->men_ali_active;?>"><img src="img/web/menu_allies.png"><span class="title">Alianzas</span></li>
  </ul>
  
  <div id="SubMenu-Container" class="ui-helper-clearfix">
    <ul id="SubMenu-Navigation" class="submenu ui-helper-clearfix <?php echo $menu->sub_nav_hidden;?>">
      <li class="<?php echo $menu->getPageState('galaxy') ?>"><a href="<?php echo _X_FILE_GALAXY;?>"><img src="img/web/submenu_universe.png"><span class='title'><?php echo ($menu->isGalaxySet()) ? $menu->getGalaxyName() : 'Seleccione' ?></span><span class='subtitle'>Galaxia</span></a></li>
      <li class="<?php echo $menu->getPageState('star') ?>"><?php echo ($menu->isStarSet()) ? "<a href='"._X_FILE_STAR."?star_id=".$menu->getStarId()."'>" : '<span class="container">' ?><img src="img/web/submenu_solar.png"><span class='title'><?php echo ($menu->isStarSet()) ? $menu->getStarName() : 'Seleccione ' ?></span><span class='subtitle'>Estrella</span><?php echo ($menu->isStarSet()) ? "</a>" : '</span>' ?></li>
      <li class="<?php echo $menu->getPageState('planet') ?>"><?php echo ($menu->isPlanetSet()) ? "<a href='"._X_FILE_PLANET."?planet_id=".$menu->getPlanetId()."'>" : '<span class="container">' ?><img src="img/web/submenu_planet.png"><span class='title'><?php echo ($menu->isPlanetSet()) ? $menu->getPlanetName() : 'Seleccione ' ?></span><span class='subtitle'>Planeta</span><?php echo ($menu->isPlanetSet()) ? "</a>" : '</span>' ?></li>
      <li class="extra <?php echo $menu->getPageState('region') ?>"><?php echo ($menu->isRegionSet()) ? "<a href='"._X_FILE_REGION."?region_id=".$menu->getRegionId()."'>" : '<span class="container">' ?><img src="img/web/submenu_region.png"><span class='title'><?php echo ($menu->isRegionSet()) ? $menu->getRegionName() : 'Seleccione ' ?></span><span class='subtitle'>Region</span><?php echo ($menu->isRegionSet()  ) ? "</a>" : '</span>' ?></li>
      <li class="extra <?php echo $menu->getPageState('colony')  ?>"><?php echo ($menu->isColonySet()) ? "<a href='"._X_FILE_COLONY."?colony_id=".$menu->getColonyId()."'>" : '<span class="container">' ?><img src="img/web/submenu_colony.png"><span class='title'><?php echo ($menu->isColonySet()) ? $menu->getColonyName() : 'Seleccione ' ?></span><span class='subtitle'>Colonia</span><?php echo ($menu->isColonySet()) ? "</a>" : '</span>' ?></li>
      
    </ul>
    <ul id="SubMenu-Army" class="submenu ui-helper-clearfix <?php echo $menu->sub_arm_hidden;?>">
      <li class="<?php echo $menu->getPageState('battle') ?>"><span class='subtitle'>Batallas</span></li>
      <li class="<?php echo $menu->getPageState('armies') ?>"><a href="<?php echo _X_FILE_ARMIES;?>"><img src="img/web/submenu_armies.png"><span class='title'>Ejercito</span><span class='subtitle'>Envio</span></a></li>
      <li class="<?php echo $menu->getPageState('genolab')  ?>"><span class='subtitle'>Laboratorio</span></li>
    </ul>
    <ul id="SubMenu-Colony" class="submenu ui-helper-clearfix <?php echo $menu->sub_col_hidden;?>">
      <li class="<?php echo $menu->getPageState('colony') ?>"><span class='subtitle'>Lista de Colonias</span></li>
      <li class="<?php echo $menu->getPageState('building')  ?>"><span class='subtitle'>Construcciones</span></li>
      <li class="<?php echo $menu->getPageState('resources')  ?>"><span class='subtitle'>Recursos</span></li>
      <li class="<?php echo $menu->getPageState('trade') ?>"><span class='subtitle'>Trading</span></li>
    </ul>
    <ul id="SubMenu-Allies" class="submenu ui-helper-clearfix <?php echo $menu->sub_ali_hidden;?>">
      <li class="<?php echo $menu->getPageState('create')  ?>"><span class='subtitle'>Crear Alianzas</span></li>
      <li class="<?php echo $menu->getPageState('alliance') ?>"><span class='subtitle'>Alianzas</span></li>
      <li class="<?php echo $menu->getPageState('enemy')  ?>"><span class='subtitle'>Enemigos</span></li>
      <li class="<?php echo $menu->getPageState('message')  ?>"><span class='subtitle'>Mensajes</span></li>
    </ul>
  </div>
      
      <div id="HeaderTitle" class="ui-state-hover ui-corner-bottom"><h1><?php echo $menu->getTitle()  ?></h1></div>    
  </div>
    <div class="clear"></div>
</div>