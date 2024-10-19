<?php
/*
 * Archivo de Inicializacion del Juego init.php VERSION: 0.1
 * 
 * Aqui nos encargaremos de cargar todas las acciones necesarias que vayan 
 * a necesitar los archivos interfases (los php que esten al inicio de
 * de la carpeta raiz /game
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
  //Si no se ejecuta esto entonces se usara el sistema de manejo de 

// Remove the ini_set for 'user' save handler
//ini_set('session.save_handler', 'user');


  require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
  //1) MANEJO DE SESIONES EN DRUPAL
  $game_lib_init_directory =getcwd();
  //chdir('/home/iasoft/public_html');
  //chdir('E:/wamp/www/xeno');
  $game_lib_init =$_SERVER['DOCUMENT_ROOT']._X_FILE_EXTRA_URL;
  //global $firephp;
  //$firephp->log($game_lib_init,"_SERVER['DOCUMENT_ROOT']");
  //$firephp->log($game_lib_init_directory,'ActualDirectory');
  //$firephp->log($_SERVER,'SERVER');
  chdir($game_lib_init);
  //$firephp->log(getcwd(),'currentdirectory');
  require_once 'includes/bootstrap.inc';
  //drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
  drupal_bootstrap(DRUPAL_BOOTSTRAP_SESSION);
  chdir($game_lib_init_directory);
  unset($game_lib_init_directory);

  //Obtener el usuario de la Cookie que tenemos
  //$_COOKIE[]

  //session_start();  
  //2) CONTROL DE ACCESO DE USUARIOS
  global $locale;
  global $user;
 //$firephp->log($locale,'locale');
  //$firephp->log($user,'user init.php');
  //$firephp->log($_SESSION,'sesion');
  //$user->roles = array('player','admin');
  //$user->uid = 5;
  //Preguntar si se entro por Drupal, si no salir
  if(!isset($user)) 
  {
    //redirectHome();
    exit;
  }
  
  
 //2.1)USUARIO REGISTRADO
 if(in_array('player',$user->roles)) {
	$_SESSION['xid'] = db_result(db_query("SELECT xid FROM {xeno_session} WHERE uid = %d", $user->uid));  
	//$_SESSION['xid'] = 1;//db_result(db_query("SELECT xid FROM {xeno_session} WHERE uid = %d", $user->uid));
        if(empty($_SESSION['xid'])){
          drupal_set_message(_X_ERROR_PLAYER_DONT_EXIST, $type = 'error', $repeat = FALSE);
          redirectHome();
          exit;
        }
 }//2.2)USUARIO ANONIMO
 elseif (in_array('anonymous user',$user->roles)) {     
        $_SESSION['xid'] = _X_DEMO_USER_ID;   
        if(empty($_SESSION['xid'])){// MUY IMPROBABLE
          drupal_set_message(_X_ERROR_PLAYER_DONT_EXIST, $type = 'error', $repeat = FALSE);
        
          redirectHome();
          exit;
        }
 }
 else{// No es ninguno de los roles
          redirectHome();       
          exit; 
 } 
 //3) CONSTANTE DE SEGURIDAD 
 //define("_X_SECURE",true);
  ?>