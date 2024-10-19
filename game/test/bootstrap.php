<?php
  if(!defined('_X_SECURE')){define("_X_SECURE",true);}
  $game_lib_include_directory = getcwd();
  chdir(dirname(__FILE__));
  
  require_once '../lib/include.php';
  
  chdir($game_lib_include_directory);
  unset($game_lib_include_directory);

  $_SERVER['SERVER_NAME'] = 'http://localhost';
  $_SERVER['HTTP_HOST'] == 'localhost';
  ?>
