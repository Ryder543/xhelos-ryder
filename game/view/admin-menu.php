<?php
//require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
global $user;
?>

<div id="leaderboard"><div class="block odd block-count-1 at-align-left-content at-inline-menu clearfix" id="block-block-5">
        <div class="block-inner">
            <div class="content">
                <ul class=" menu loginItem">
                    <li><a href="<?php echo _X_FILE_ROOT; ?>game/admin.php">Global</a></li>
                    <li> | <a href="<?php echo _X_FILE_ROOT; ?>game/admin_places.php">Lugares</a></li>
                    <li> | <a href="<?php echo _X_FILE_ROOT; ?>game/admin_armies.php">Ejercito</a></li>
                    <li> | <a href="<?php echo _X_FILE_ROOT; ?>game/admin_battle.php">Batallas</a></li>
                    <li> | <a href="<?php echo _X_FILE_ROOT; ?>game/admin_perlin.php">Perlin Noise</a></li>
                    <li> | <a href="<?php echo _X_FILE_ROOT; ?>game/admin_players.php">Players</a></li>
                    <li> | <a href="<?php echo _X_FILE_ROOT; ?>game/admin_planet.php">Planets</a></li>                    
                    <li> | <a href="<?php echo _X_FILE_ROOT; ?>logout">Logout <?php echo $user->name; ?></a></li>
                </ul>
            </div>
        </div>
    </div> <!-- /block -->
</div>  