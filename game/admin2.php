<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

<head>
<?php

    include('view/html-header.php');
    include_css("css/region.css");
    include_css("css/region_colony.css");    
    include_js("lib/js/raphael.js");
    include_js("lib/jquery/jquery.countdown.js");
    //include_js("lib/jquery/jquery.qtip2.js");
    include_js("lib/js/region_minimap.js");
    include_js("lib/js/region_map.js");
    include_js("lib/js/region.js"); 
    include_js("lib/js/region_colony.js");     
    include_js("lib/js/region_battle.js");    
    include_js("lib/js/effects.js");

    include_js("lib/js/debug.js");
    include_js("lib/js/ai.js");
    include_js("lib/js/jclock.js");
    
    
    
    if($gs->getPlayer()->isTutorialActive()){
        include_js("lib/js/region-tutorial.js");
    }
    
?>

    <title><?php echo $gs->getTitle() ?></title>
</head>

<body>
<div id='Wrapper'>
<?php include('view/primary-menu.php') ?>
<?php include('view/menu.php') ?>

<?php include('view/message.php') ?>




<div id="MainContainer" class="block">

    
    
    <div id="SecondaryPanel">

        <!--InnerBattleReport-->
        
        
        <div id="BattleInfo" class="ui-state-transparent " >
            <div id="BattleState">
                <h2 id="PhaseTitle"></h2>
                <h2 id="Clockout"></h2>
            </div>
            <div id="BattlePhases">
                 <div id="BattlePhasesWrapper"></div><!-- Aqui apareceran todo lo referente a la situacion de los demas jugadores -->
                 <div id="BattlePhasesActions"></div><!-- Aqui apareceran todo lo referente a la las posibles acciones de las fases -->
            </div>
            
            
        </div>
        
        <div id="ArmyInfoContainer" class="block">
            <div id="ArmyInfo"  class="ui-state-transparent"></div>
            <div id="ArmyActions" class=""></div>
        </div>  

    </div>



    <div id="BattleMenu">
        
        <div class='ui-state-transparent'>
        <h2 id="MinimapTitle" class="ui-state-active">Mapa</h2>
        <div id="MinimapContainer" class="clearfix"></div>
        </div>
        
        <div class='ui-state-transparent block'>
        <h2 id="BattleReInforcementTitle" class="ui-state-active">Refuerzos</h2>
        <div id="BattleReinforcement" class=""></div>
        </div>
        
        <div id="AIPanelContainer" class="block"></div>
        
    </div>
    
    
    <div id='BattleMapWrapper' class="">
        <div id='ViewPort'>
        <?php $gs->render(); ?>
        </div>
    </div>

</div>
    
 
    <div id="BattleTurnsWrapper"></div>
    
    <div class="block">&nbsp;</div>
</div>
    
  <?php 
  if($gs->getPlayer()->getTutorial()){
  ?>
  <div id="Tutorial1" title="Tutorial Parte 1/<?php echo _X_PLAYER_MAX_TUTORIAL_STEP ?>" class="ui-helper-hidden">
        <h2>Bienvenido a Xhelos!</h2>
        
        <img src="img/web/title-block2.png" />
        
	<p>
            <span class="title">Xhelos</span> es un juego de estrategia donde controlas unidades por <span class="title">turnos</span> .</p>
        <p>Este tutorial te ayudara a conoceras mejor la interface del juego. Presiona en <span class="title">continuar</span> para seguir con el tutorial. </p>
             
        <div class="block">
            <a href="#" class="button continuar" >Continuar </a>
        </div>
        
        <div class="block ui-state-active">
            <div class="small-inner">
            Xhelos esta actualmente en Desarrollo. Cualquier duda o consulta por favor visita <a class="link"  href="<?php echo _X_FILE_ROOT; ?>forum">nuestro foro</a>
        </div></div>

    </div>
    
    <div id="Tutorial2" title="Tutorial Parte 2/<?php echo _X_PLAYER_MAX_TUTORIAL_STEP ?>" class="ui-helper-hidden">
        <h2>Activar/Desactivar el Tutorial</h2>
	 <p>Si deseas, puedes activar o desactivar el tutorial usando la opcion de la parte superior</p>
         <img src="img/web/tut01A.jpg" />
         
         <div class="block">
            <a href="#" class="button retroceder" >Retroceder</a>
            <a href="#" class="button continuar" >Continuar </a>
        </div>
    </div>
    
    
    
    <div id="Tutorial3" title="Tutorial Parte 3/<?php echo _X_PLAYER_MAX_TUTORIAL_STEP ?>" class="ui-helper-hidden">
        <h2>Objetivo del Juego</h2>
	<p>
        El objetivo del juego es  <span class="title">eliminar a todas las unidades enemigas del mapa</span>.
        </p>
        <p>Las unidades en <span class="player AI title">magenta</span> son unidades enemigas controladas por el <span class="title">computador</span> </p>        
        <div class="block">
            <a href="#" class="button retroceder" >Retroceder</a>
            <a href="#" class="button continuar" >Continuar </a>
        </div>
    </div>
    
    <div id="Tutorial4" title="Tutorial Parte 4/<?php echo _X_PLAYER_MAX_TUTORIAL_STEP ?>" class="ui-helper-hidden">
	<h2>Unidades Propias</h2>
        <p>Para combatir estas unidades tienes a tu disposicion unidades propias</p>
        <p>Las unidades con fondo <span class="player myself title">azul</span> son <span class="title">tus unidades</span></p>
        <div class="block">
            <a href="#" class="button retroceder" >Retroceder</a>
            <a href="#" class="button continuar" >Continuar</a>
        </div>
    </div>
     <div id="Tutorial5" title="Tutorial Parte 5/<?php echo _X_PLAYER_MAX_TUTORIAL_STEP ?>" class="ui-helper-hidden">
	<h2>Mapa del Juego</h2>
       
        <p>Puedes moverte a traves del mapa usando el <span class="title">minimapa</span>. </p>
        <div class="block ui-state-active">
       
        <img src="img/web/tut05C.jpg" />
         <p>Tan solo arrastra el cuadro celestre a traves del minimapa para moverte</p>

         </div>
        <div class="block">
            
            <?php if( (!empty($battle)) && ($battle->getPhase() == _X_BATTLE_PHASE_TACTIC )  ){?>
                <a href="#" class="button retroceder" >Retroceder</a>   
            <?php } ?>
                <a href="#" class="button continuar" >Continuar</a>    
        </div>
    </div>
    
     <div id="Tutorial6" title="Tutorial Parte 6/<?php echo _X_PLAYER_MAX_TUTORIAL_STEP ?>" class="ui-helper-hidden">
	<h2>Fase Tactica</h2>
       
        <p>Las batallas estan dividas en dos fases:
            <ul>
                <li>La Fase Tactica</li>
                <li>La Fase de Batalla</li>
            </ul>
        </p>
        <p>La Fase Tactica es un tiempo previo antes de la batalla. Por ahora solo podras ver el mapa y las posiciones enemigas, conforme avances en el juego tendras mas opciones para esta fase. </p>
        <div class="block ui-state-active">
        
        <div class="showOnTactic6">   
            <img src="img/web/tut04A.jpg" />
            <p>Presiona el boton de Finalizar Fase Tactica para continuar</p>
        </div>
         <p class="showOnBattle6">Ya termino su fase tactica, presione el boton continuar para seguir con el tutorial</p>
         
        
         </div>
        <div class="block">
            <a href="#" class="button retroceder" >Retroceder</a>
            <a href="#" class="showOnBattle6 button continuar" >Continuar</a>    
        </div>
    </div>
    
    <div id="Tutorial7" title="Tutorial Parte 7/<?php echo _X_PLAYER_MAX_TUTORIAL_STEP ?>" class="ui-helper-hidden">
	<h2>Turnos de Batalla</h2>
        <p>Has pasado a la <span class="title">Fase de Batalla</span>. En esta fase podras controlar a tus unidades cuando le toque su turno.</p>
        <p>Podras ver la lista de turnos en la seccion de turnos</p>
        <div class="block ui-state-active">
        <p><img src="img/web/tut05B.jpg" /></p>
                <p>Dependiendo de la <span class="title">iniciativa</span> de una unidad, esta aparecera con mas frecuencia en la barra de turnos.</p>
        </div>
        <div class="block">
        <a href="#" class="button retroceder" >Retroceder</a>
        <a href="#" class="button continuar" >Continuar</a>
        </div>
    </div>
    
    <div id="Tutorial8" title="Tutorial Parte 8/<?php echo _X_PLAYER_MAX_TUTORIAL_STEP ?>" class="ui-helper-hidden">
	<h2>Habilidades de las unidades</h2>
        <p>Todas las unidades tienen diferentes habilidades que podras usar cuando le toque su turno.</p>
        <img src="img/web/tut06A.jpg" />
        
        
        <div class="block">
        <a href="#" class="button retroceder" >Retroceder</a>
        <a href="#" class="button continuar" >Continuar</a>
        </div>
    </div>
    
    <div id="Tutorial9" title="Tutorial Parte 9/<?php echo _X_PLAYER_MAX_TUTORIAL_STEP ?>" class="ui-helper-hidden">
	<h2>Rango de las Habilidades</h2>
        <p>Todas las acciones tienen un rango de movimiento mostrado por los cuadrados verdes en el mapa. Estos cuadrados
        indican donde se podra ejecutar la accion</p>
        <img src="img/web/tut06B.jpg" />
   
        <div class="block">
        <a href="#" class="button retroceder" >Retroceder</a>
        <a href="#" class="button continuar" >Continuar</a>
        </div>     
    </div>
    
    <div id="Tutorial10" title="Tutorial Parte 10/<?php echo _X_PLAYER_MAX_TUTORIAL_STEP ?>" class="ui-helper-hidden">
	<h2>Ejecucion de una habilidad</h2>
        <p>Algunas habilidades se aplican solo a unidades como la habilidad de atacar, otras solo se pueden aplicar a terreno sin unidades como la habilidad de moverse!.</p>
        <div class="block ui-state-active">
        <img src="img/web/tut06C.jpg" />
         <p>Selecciona la habilidad de moverse y aplicala en cualquiera de los cuadrados verdes de accion para
         continuar con el tutorial</p>
         </div>
        
        <div class="block">
        <a href="#" class="button retroceder" >Retroceder</a>
        </div>
    </div>
    
    
        <div id="Tutorial11" title="Tutorial Parte 11/<?php echo _X_PLAYER_MAX_TUTORIAL_STEP ?>" class="ui-helper-hidden">
	<h2>Propiedades de las Unidades</h2>
        <p>Cada unidad tiene un grupo diferente de propiedades</p>
        <img src="img/web/tut07A.jpg" />
        <p><span class="title">A) Cantidad:</span>Numero de unidades en el grupo, si llega a cero, el grupo muere</p>
        <p><span class="title">B) Total Vida:</span>Es el total de vida que tiene todo el grupo.</p>
        <p><span class="title">C) Mana:</span>Es la energia que tiene la unidad para ejecutar habilidades que requieran energia</p>
        <p><span class="title"><span class="Stats Attack">&nbsp;</span> Ataque:</span>La cantidad de damage que hace con sus ataques. Un multiplicador implica que la unidad hace mas ataques por turno</p>
        <p><span class="title"><span class="Stats Armor">&nbsp;</span>Armadura:</span>La capacidad de resistir damage. Se resta el damage que hace la unidad enemiga menos la armadura de la unidad atacada</p>
        <p><span class="title"><span class="Stats Life">&nbsp;</span>Vida por Unidad:</span>Cuanta vida tiene cada unidad dentro del grupo</p>
        
        
        
        <div class="block">
        <a href="#" class="button retroceder" >Retroceder</a>
        <a href="#" class="button continuar" >Continuar</a>
        </div>
        
        </div>
        
      <div id="Tutorial12" title="Tutorial Parte 12/<?php echo _X_PLAYER_MAX_TUTORIAL_STEP ?>" class="ui-helper-hidden">
            <h2>Unidades del Juego</h2>
            <p><img src="img/unit/1.png" /><span class="title">Fighter</span>: Unidad con buen ataque y defensa. Solo pueden atacar unidades que esten a su lado. Las unidades atacadas pueden contraatacar </p>
            <p><img src="img/unit/2.png" /><span class="title">Archer</span>: Unidad con baja defensa y un ataque regular.  Pero su ataque de rango no puede ser contraatacado </p>
            <p><img src="img/unit/3.png" /><span class="title">Artillery</span>: Unidad con excelente ataque y buena defensa pero baja movilidad. Su ataque hace damage a todas las unidades que se encuentren en su linea de ataque, incluyendo las tuyas propias! </p>
            


            <div class="block">
            <a href="#" class="button retroceder" >Retroceder</a>
            <a href="#" class="button continuar" >Continuar</a>
            </div>
            
            <div class="block ui-state-active">
                <p>Conforme avances en el juego, podras obtener nuevas unidades y habilidades!</p>
                   
            </div>
            
        </div>
    
        
        <div id="Tutorial13" title="Tutorial Parte 13/<?php echo _X_PLAYER_MAX_TUTORIAL_STEP ?>" class="ui-helper-hidden">
            <h2>Ultimos Tips</h2>
            <p>Existen unidades con el fondo <span class="player enemy title">rojo</span>. Estas unidades son unidades enemigas controladas por humanos</p>
            <p>Recuerda vigilar tu nivel de mana, si no tienes mana, no podras ejecutar mas habilidades.</p>
            <p>Si tienes dudas sobre alguna propiedad y/o habilidad, pon el mouse encima del mismo, es posible que aparezca un tooltip explicandolo.</p>
            <p>En este tutorial las habilidades que hacen damage son Atacar, Artilleria, y Ataque de Rango, varia dependiendo del tipo de unidad con turno actual.</p>        
            <p>Y eso seria todo, tu mision ahora es eliminar a las unidades enemigas. Disfruta el juego!</p>

            <div class="block">
            <a href="#" class="button retroceder" >Retroceder</a>
            <a href="#" class="button finalizar" >Finalizar</a>
            </div>
        </div>
    
    
    
    
  <?php
  }
  ?>  
 
   
    
    

        
        
    
</body>
</html>

