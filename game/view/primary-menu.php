 <?php
 //require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
 //global $gs;
 $player = $gs->getPlayer();
 $menu = $gs->getMenuState();
 //debug::log($player,'primary-menu.php');
?>

<div id="leaderboard" class="ui-state-active ui-corner-bottom ui-helper-clearfix">
<ul class="loginItem">
    
    <li class="left"><a class="button-on" title="Llena el cuestionario y ayudame a mejorar Xhelos" id="HelpMe" href="https://jeeba.wufoo.com/forms/m7x3w7/">
       Encuesta
    </a></li>        
<?php if($menu->getPage()=='region'){?>
<li class="left"><a class="button-on" id="CheatsOn" href="#cheatson">
        <span class="inline-block ui-button-icon-primary ui-icon ui-icon-triangle-1-s"></span>
       Cheats
</a></li>
<?php } ?>

        
<li class="left"><?php echo $menu->renderRegionTutorial() ?></li>

<li class="right"><a  class="button-on"  href="<?php echo _X_FILE_ROOT; ?>logout">Logout</a>  </li>

<li class="right"><a  class="button-on" href="<?php echo _X_FILE_ROOT; ?>user">Configuracion</a>    </li>
<li class="right"><a  class="button-on" href="<?php echo _X_FILE_ROOT; ?>forum">Foro</a>  </li>
<li  class="right">Ir a la <a href="<?php echo _X_FILE_ROOT; ?>">Pagina Principal</a>  </li>
<li  class="right">Ir al<a href="<?php echo _X_FILE_ROOT; ?>game/galaxy.php">Juego</a>  </li>

</ul>
    <div class=""></div>
</div>  


<div id="Options" class="ui-state-active">	
    <div class="small-inner">

<div class='last'>
<?php 
//if(user_is_anonymous()){
if($menu->getPage()=='region'){
?>
<a class="button" id="ReiniciarBatalla" href="javascript:void(0)">Reiniciar Batalla</a>
<a class="button" id="RevivirUnidadesWrapper" href="javascript:void(0)"><img id="RevivirUnidades" alt="Revivir a todas las unidades" src="img/web/icon-small-resurrect.png" /></a>
<a class="button" id="RegenerateEnergyWrapper" href="javascript:void(0)"><img id="RegenerateEnergy" alt="Regenera la energia" src="img/web/icon-small-energy.png" /></a>
<a class="button" id="CancelWrapper" href="javascript:void(0)"><img id="Cancel" alt="Cancelar accion" src="img/web/icon-small-rightclick.png" /></a>
<a class="button" id="TerminarAIWrapper" href="javascript:void(0)"><img id="TerminarAIWrapper" alt="Terminar el turno de la IA" src="img/web/icon-small-hourglass.png" /></a>
<a class="button" id="FingerOfDeathWrapper" href="javascript:void(0)"><img id="FingerOfDeath" alt="Matar a la unidad elegida" src="img/web/small-fingerofdeath.png" /></a>

<div class="clear"></div>
<?php } ?>  
</div>
<div class="clear"></div>
</div>
</div>