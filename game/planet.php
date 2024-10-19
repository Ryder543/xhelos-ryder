<?php
if (!defined('_X_SECURE')) {
    define("_X_SECURE", true);
}
require_once("lib/include.php");


$planetId = viewutil::validParam("planet_id");
$gs = new globalview($planetId, "planet");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <head>
        <?php
        include('view/html-header.php');
        include_css("css/planet.css");
        include_js("lib/jquery/jquery.countdown.js");
        //include_js("lib/jquery/jquery.qtip2.js");
        include_js("lib/js/planet.js");
        ?>

        <title><?php echo $gs->getTitle() ?></title>
    </head>

    <body>
        <div id='Wrapper'>
            <?php include('view/primary-menu.php') ?>
            <?php include('view/menu.php') ?>
            <?php include('view/message.php') ?>

            <div id="MainContainer">

                <div id="SideBarLeft"  class="ui-state-transparent">


                        <div id="InnerSideBarLeft" class="">
                            <h3 class="ui-state-active">Acciones</h3>
                            <div id="ActionsMenu" class="inner ui-helper-clearfix">Seleccione una region</div>
                            
                            <h3 class="ui-state-active">Region</h3>
                            <div id="RegionInfo" class="inner ui-helper-clearfix">Seleccione una region</div>

                            

                            <h3 class="ui-state-active">Recursos Adicionales</h3>
                            <div id="ResourcesInfo" class="inner ui-helper-clearfix">Seleccione una region</div>

                            <h3 class="ui-state-active">Construcciones</h3>
                            <div id="BuildingsInfo" class="inner ui-helper-clearfix">Seleccione una region</div>


                            <div id="TeamInfoWrapper">
                                <h3 class="ui-state-active">Amigos</h3>
                                <div class="inner ui-helper-clearfix">
                                    <div id="RegionTeamInfo">Seleccione una region para mostrar tu ejercito</div>
                                    <div id="TeamInfo"></div>
                                </div>
                            </div> 

                            <div id="ArmyInfo">
                                <h3 class="ui-state-active">Enemigos</h3>
                                <div id="ArmyInfoInner" >
                                    <p class="inner ui-helper-clearfix">Seleccione una region para que aparezcan las unidades enemigas</p>
                                </div>
                            </div>

                            <div id="Battle" >
                                <h3 class="ui-state-active" id="BattleReInforcementTitle">Refuerzos</h3>
                                <div id="BattleReinforcement" class="">
                                    <p class="inner ui-helper-clearfix">Vea los refuerzos que llegaran a una region seleccionandola</p>
                                </div>
                            </div>


                        </div><!--InnerBattleReport-->

                </div> <!--SideBarLeft-->   
                <div id="Content">
                    <?php
                    //$gs->render();
                    ?>
                </div>
            </div>
            <!-- Aqui van todas las pantallas modales-->
            <div id="AttackRegionModal">

                <div id="AttackActiveTeam"></div>
                <h1>VS</h1>
                <div id="AttackRegionEnemies"></div>

            </div>

        </div>
    </body>
