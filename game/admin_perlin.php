<?php
  if(!defined('_X_SECURE')){define("_X_SECURE",true);}
  if(!defined('_X_NO_INIT')){define("_X_NO_INIT",true);}
  if(!defined('_X_ADMIN_INIT')){define("_X_ADMIN_INIT",true);}
  

 $game_admin_directory = getcwd();
  chdir(dirname ( __FILE__ ));
  include_once("lib/include.php");
  require_once("lib/php/perlinnoise.class.php");
  require_once("lib/php/debug.class.php");
  chdir($game_admin_directory);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

<head>
        <?php include_once("view/admin-header.php"); ?>
<title>Menu de Prueba de Perlin Noise</title>
</head>

<body>
        <div id='Wrapper'>
    <?php include_once("view/admin-menu.php"); ?>
<h1>Menu del Pruebas de Perlin Noise</h1>
<div id="PerlinNoiseContainer" style="width:710px">
    <?php 
    
  //This is a small test snippet that will output an example with DIV tags.
  //Feel free to fiddle with it.
  $bob = new Perlin(3000);

  $size = 30;
  $smooth = 25;
  $gridsize = 20;

  for($y=0; $y<$gridsize; $y+=1) {
      for($x=0; $x<$gridsize; $x+=1) {
      $num = $bob->noise($x,$y,100,$smooth);
      //debug::log($num,'num x['.$x.']y['.$y.']');
      $raw = ($num/2)+.5;
      //debug::log($raw,'raw');
      debug::log( ( ( $num +1 ) / 2 ),'valor xeno');
      //if ($num == 0) $raw = 0;
      //else $raw = 1/abs( $num );

      //$raw = pow((5*$raw)-4,3)+.5;
      //$raw = 1-pow(50 * ($raw - 1), 2);

      //if ($raw > .9) $raw = 1;
      //else $raw = 0;
      if ($raw < 0) $raw = 0;

      $num = dechex( $raw*255 );

      if (strlen($num) < 2) $num = "0".$num;
      //echo '<div style="display:inline-block;position:relative; background-color:#'.$num.$num.$num.'; width:'.$size.'px; height:'.$size.'px; top:'.($y*$size).'px; left:'.($x*$size).'px"></div><!-- '.$raw.' -->
      echo '<div style="display:inline-block;position:relative; background-color:#'.$num.$num.$num.'; width:'.$size.'px; height:'.$size.'px;margin:0;padding:0"></div><!-- '.$raw.' -->
      ';
      }
  }
  // */

/*
  $bob = new Perlin(1);

  $place = 0;

  //for ($i=0; $i<100000; $i+=100) {
  for ($i=0; $i<1000; $i++) {
  $num = round(($bob->random1D($i)/2)+.5,2);
  echo $num.'<BR/>';
  echo '<div style="position:absolute; left:'.$place.'px; top:'.($num*25).'px; width:1px; height:1px; background-color:#000000;"></div>';
  $place++;
  }
  // */
    
    ?> 
</div>
        </div>
</body>
</html>
