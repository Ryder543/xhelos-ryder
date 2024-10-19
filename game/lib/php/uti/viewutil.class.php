<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad

/* * *******************************************************
 * viewutil: Encargado de ayudar a las vistas en general
 * ****************************************************** */

class viewutil extends util {

    ///////////////////////////////////////////////////////////////////////////////////////
    //PROPIEDADES
    ///////////////////////////////////////////////////////////////////////////////////////    
    ///////////////////////////////////////////////////////////////////////////////////////
    //CONSTRUCTOR
    ///////////////////////////////////////////////////////////////////////////////////////    
    /*     * ******************************************
     * Constructor: De viewutil
     * Params: 
     * $region_id: ID de la region que quiere mantener el estado
     * $player_id: ID del jugador actual de la region
     * EXTRA: Obtener la lista de arg $args = func_get_args(); 
     * ****************************************** */
    public function __construct() {
        parent::__construct();
        }


    public static function validParam($paramName, $paramType=false){//Si paramType es falso
        $param =0;
        if (!$paramType) {
            //debug::log('vieutil->validParam no se puso valor paramType, paramName:'.$paramName);
            if (isset($_GET[$paramName])) {
                //debug::log('viewutil->validParam esta seteado en GET:'.$paramName);
                $param = validator::filter_integer($_GET[$paramName]);
                if (!$param) {//Redirecciona porque la variable no fue bien enviada
                    redirectGameInit();
                    exit;
                }
            }
            elseif (isset($_POST[$paramName])) {
                //debug::log('viewutil->validParam esta seteado en POST:'.$paramName);
                $param = validator::filter_integer($_POST[$paramName]);
                if (!$param) {//Redirecciona porque la variable no fue bien enviada
                    redirectGameInit();
                    exit;
                }
            }
            else{
                //debug::log('viewutil->validParam No esta seteado nada!:'.$paramName);
                redirectGameInit();
                exit;
            }
        }
        else{
            debug::error('viewutil->validParam Aun no se implementa el parametro  paramType['.$paramType.']');
        }
        return $param;
    }
    
    public static function getImgUrl(){
        if(viewutil::getGameUrl()){
            return viewutil::getGameUrl().'img/';
        }
        return false;
        
    }
    
    public static function getHostUrl(){
        if (defined('_X_FILE_ROOT')) {
            $host = _X_FILE_ROOT;
        } else {
            $host = false;
        }
        return $host;
    }
    
    public static function getGameUrl(){
        if(viewutil::getHostUrl()){
            return viewutil::getHostUrl().'game/';
        }
        return false;
        
    }
    
    public static function getImg($url,$width,$height){
        if(viewutil::getImgUrl()){
            return '<img src="'.viewutil::getImgUrl().$url.'" width="'.$width.'" height="'.$height.'" /> '; 
        }
        return false;
        
    }
    
}

?>