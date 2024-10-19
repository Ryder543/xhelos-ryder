<?php
require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
/****************************************************************
 * debug: Clase para medir performance y mandar mensajes de error 
 ***************************************************************/
	class debug
	{
		private static $time_in;
		private static $time_out;
		private static $diff;		
		private $grupo;
		private $version;
		private $db;
                private static $counters = array();
                private static $fb;
		
		
		
		public static function getfb(){
                   /* if( !isset(debug::$fb) ){
                        debug::$fb = new FirePHP();
                        debug::$fb->registerErrorHandler($throwErrorExceptions=true);
                        debug::$fb->registerExceptionHandler();
                        debug::$fb->registerAssertionHandler($convertAssertionErrorsToExceptions=true,$throwAssertionExceptions=false);
                        }
                    
                    //$firephp = new fbmock();
		return debug::$fb;*/
		global $firephp;
                if(!canDebug()){
                    	$firephp = new fbmock();
                }
		else if(!isset($firephp)){
			$firephp = new fbmock();
		}	
		return $firephp;
		}
		
		public function __construct($grupo=0,$version=0)
		{
			$this->db = db::singleton();
			$this->grupo = $grupo;
			$this->version = $version;
		}
    
    
		public static function initProfiler(){
			debug::$time_in= microtime(true); //Si no valores negativos
		}
		public static function endProfiler(){
			debug::$time_out= microtime(true);//tiene que ser true
			//http://stackoverflow.com/questions/2607150/php-profiling-with-microtime-negative-time
		}
		public static function getTime(){
			debug::$diff= debug::$time_out- debug::$time_in;
			return debug::$diff;
		}
		public static function logError($text,$zone){
			$firephp = debug::getfb();
			$firephp->log($text,'[DEBUG]'.$zone);
			$firephp->trace('[DEBUG]');
		}
    public static function error($data,$label =''){
			$firephp = debug::getfb();
      $firephp->error($data,$label);
      //$firephp->trace('dsad');
      $firephp->trace($label);
    }
    public static function trace($label =''){
			$firephp = debug::getfb();
      //$firephp->error($text,'[ERROR]'.$zone);
      $firephp->trace($label);
    }
    
    public static function warning($data,$label =''){
			$firephp = debug::getfb();
      $firephp->warn($data,$label);
    }
    public static function console($data,$label =''){
			$firephp = debug::getfb();
      $firephp->log($data,$label);
    }
    public static function log($data,$label =''){
			$firephp = debug::getfb();
      $firephp->log($data,$label);
    }
    public static function info($data,$label =''){
			$firephp = debug::getfb();
      $firephp->info($data,$label);
    }  
    
    public static function addCounter($param){

        if(empty(debug::$counters[$param])){
            debug::$counters[$param] = 0;
        }
        $this->counters[$param]++;
    }
    public static function getCounter($param){
        return debug::$counters[$param];
    }
    
	}//debug
	?>
