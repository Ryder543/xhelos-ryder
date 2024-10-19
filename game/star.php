<?php
  if(!defined('_X_SECURE')){define("_X_SECURE",true);}
  require_once("lib/include.php");
  
  $starId = viewutil::validParam("star_id");
  $gs = new globalview($starId,"star");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

<head>
<?php
    include('view/html-header.php');
    include_css("css/star.css");
    include_js("lib/js/star.js");
?>

    <title><?php echo $gs->getTitle() ?></title>
</head>

<body>
	
	<div id='Wrapper'>
	     <?php include('view/primary-menu.php') ?>
	     <?php include('view/menu.php') ?>
	      
	      <div id="MainContainer">    
    	      <!-- <div id="StarInfo" class="ui-state-transparent"> -->
              <div id="StarInfo" class="ui-state-transparent">
                
                     <div id="ArmyInfoInner" class="inner">
                         <p>Seleccione un planeta para ver su informacion </p>
                         <img src="img/web/title-block2.png" class="img-block">
                     </div>  
           </div>
           
    	     <div id="Star">
    	       <?php $gs->render();?>  
    	     </div>
</body>

