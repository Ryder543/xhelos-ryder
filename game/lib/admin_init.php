<?php
/*
 * Archivo de Inicializacion del Administrador admin_init.php VERSION: 0.1
 * 
 * Aqui nos encargaremos de cargar todas las acciones necesarias para mantener
 * la seguridad con el administrador
 * 
 * Orden de Carga
 * --------------
 * 1) Cargamos el Drupal Bootstrap hasta el manejo de sesiones 
 * DRUPAL_BOOTSTRAP_SESSION, no usar DRUPAL_BOOTSTRAP_FULL porque
 * carga todo el Drupal y lo hace todo mas lento
 * 
 * Nos aseguramos ademas que llamamos a la direccion correcta del bootstrap
 * y despues retornamos a la antigua direccion, esta variable es luego 
 * deseteada para maxima performance, ademas de evitar que se siga usando
 * 
 * 2) Verificar que el usuario este logeado correctamente, si no lo esta
 * enviarlo a la direccion inicial de la web, esto debe de ser dinamico
 * 2.1) Si es usuario de demostracion
 * 2.2) Si es usuario contectado  
 * 
 * 3) Cargamos la variable xid como variable de sesion. Esta variable es
 * la que contiene el ID del jugador de Xeno
 * 
 * 4) Definimos una constante unica que implica que Xeno se esta ejecutando
 * desde un archivo interfase (index.php,main.php,ajaxDebug.php etc)
 * 
 */
  require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
  //1) MANEJO DE SESIONES EN DRUPAL
  $game_lib_init_directory =getcwd();
  //chdir('/home/iasoft/public_html/xeno');
  //chdir('E:/wamp/www/xeno');
  //$game_lib_init =$_SERVER['DOCUMENT_ROOT'].'/xeno';
  $game_lib_init =$_SERVER['DOCUMENT_ROOT']._X_FILE_EXTRA_URL;
  chdir($game_lib_init); 
  
  require_once 'includes/bootstrap.inc';
  //drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
  drupal_bootstrap(DRUPAL_BOOTSTRAP_SESSION);
  chdir($game_lib_init_directory);
  unset($game_lib_init_directory);
  //session_start();  
  //2) CONTROL DE ACCESO DE USUARIOS
  
  global $user;
  //$user->roles = array('player','admin');
  //$user->uid = 5;
  //Preguntar si se entro por Drupal, si no salir
  if(!isset($user)) 
  {
    //redirectHome();
    exit;
  }
  
  
 //2.1)USUARIO ADMINISTRADOR ONLY
 if(!in_array('admin',$user->roles)) {
      //drupal_set_message(_X_ERROR_PLAYER_DONT_EXIST, $type = 'error', $repeat = FALSE);
      redirectNotFound();
      exit;
 } 

  ?>