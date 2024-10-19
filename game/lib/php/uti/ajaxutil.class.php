<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/* * **************************************************************
 * debug: Clase para medir performance y mandar mensajes de error 
 * ************************************************************* */

class ajaxutil {
 
    
    static function getActiveResources() {
        //Crear la estructura affect
        $resources = new resources();
        $resources->initAllActiveIndexedById();
        return $resources->getState();
    }
    
    static function getResourcesRatePerRegion() {
        //Crear la estructura affect
        $ru = new regionutil();
        $resources[_X_REGION_MAP_TYPE_PLAIN] = $ru->getBasicResourcesRatesPerTickByRegionType(_X_REGION_MAP_TYPE_PLAIN);
        $resources[_X_REGION_MAP_TYPE_DESERT] = $ru->getBasicResourcesRatesPerTickByRegionType(_X_REGION_MAP_TYPE_DESERT);
        $resources[_X_REGION_MAP_TYPE_MOUNTAIN] = $ru->getBasicResourcesRatesPerTickByRegionType(_X_REGION_MAP_TYPE_MOUNTAIN);
        $resources[_X_REGION_MAP_TYPE_LAGOS] = $ru->getBasicResourcesRatesPerTickByRegionType(_X_REGION_MAP_TYPE_LAGOS);
        $resources[_X_REGION_MAP_TYPE_NIEVE] = $ru->getBasicResourcesRatesPerTickByRegionType(_X_REGION_MAP_TYPE_NIEVE);
        $resources[_X_REGION_MAP_TYPE_INICIAL] = $ru->getBasicResourcesRatesPerTickByRegionType(_X_REGION_MAP_TYPE_INICIAL);        
        return $resources;
    }
    
    static function getAffectsConstants() {
        //Crear la estructura affect
        $affect[_X_AFFECTS_ATTACK] = "attack";
        $affect[_X_AFFECTS_ATTRITION] = "attack";
        $affect[_X_AFFECTS_ARMOR] = "armor";
        $affect[_X_AFFECTS_LIFE] = "life";
        $affect[_X_AFFECTS_UNIT] = "unit";
        $affect[_X_AFFECTS_MOVEMENT] = "movement";
        //$affect = json_encode($affect);
        return $affect;
    }

    static function getPositionsConstants() {
        $position[_X_POSITION_ORBIT] = "En Orbita";
        $position[_X_POSITION_ATERRIZAR] = "Aterrizando en region";
        //$position[_X_POSITION_SALIENDO]="Saliendo a la Orbita";
        $position[_X_POSITION_SUPERFICIE] = "En Superficie";
        $position[_X_POSITION_ESPACIO] = "En el Espacio";
        //$position[_X_POSITION_OUTSIDE]="Moviendose a Region";
        //$position[_X_POSITION_INSIDE]="Entrando a Region";
        $position[_X_POSITION_COORD_NULL] = "Nulo";
        //$position = json_encode($position);

        return $position;
    }
    static function getGameConstants() {
        $arr["g_game_url"] = _X_FILE_ROOT;
        return $arr;
    }
    
    static function getBattlePhases() {
        $phases[_X_BATTLE_PHASE_TACTIC] = "Tactica";
        $phases[_X_BATTLE_PHASE_BATTLE] = "Batalla";
        $phases[_X_BATTLE_PHASE_END] = "Finalizada";
        return $phases;
    }

    static function json_encode($a) {
        return json_encode($a);
        //json_encode($arr, JSON_FORCE_OBJECT);
    }

    /*     * ********************************************* */
    /* 	Caso: Mandar de PHP a Javascript	   */
    /*     * ********************************************* */

    static function php2js($var, $varname, $echo_on = true) {
        $script = "<script type='text/javascript'>";
        $script = $script . "$varname = eval($var)";
        $script = $script . "</script>";
        if ($echo_on) {
            echo $script;
        } else {
            return $script;
        }
    }

    static function var2json($var, $returnIsEchoed = true) {
        if (isset($var)) {
            if ($returnIsEchoed) {
                //echo "[".json_encode($var)."]";
                //echo "(".json_encode($var).")";
                //debug::log(json_encode($var),'var2json json_encode');
                //debug::log($var,'var2json var');
                echo json_encode($var);
            } else {
                return json_encode($var);
                //return json_encode($var);
            }
            //echo '{"hola:"'.json_encode($var).'"}';
            //echo json_encode($var);
        } else {
            //echo 'ERROR: La variable no esta seteada. FUNCION: json.var2json';
        }
    }

    static function calculateState(view $oState, view $oGlobal) {
        $state_raw = $oState->getState();
        $global = $oGlobal->getState();
        $state_raw['global'] = $global;
        return $state_raw;
    }
    /**
     * calculateMsgState: 
     *
     * @return  None
     * @since   2012-Dic-23
     * @author  Jeeba <designbyjeeba@gmail.com>
     *
     * @edit    2012-Dic-31<br />
     *          Jeeba <designbyjeeba@gmail.com>
     *          Cambiamos nombre a do y añadimos verificacion de parametros automatico<br/>
     *          #edit2
     * @edit    2012-Dic-23<br />
     *          Jeeba <designbyjeeba@gmail.com>
     *          Funcionalidad Inicial<br/>
     *          #edit1
     */
    static function calculateMsgState(datatransfer $dt, view $oState, view $oGlobal) {
        if($dt->ok()){
            $state = ajaxutil::calculateState($oState, $oGlobal);
            //$state['msg'] = $dt->getMsg();
            $dt->setData($state);
        }
        return $dt;
    }

    static function ajaxHeaders(){
        // Prevent caching.
        if(canDebug()){
        //http://stackoverflow.com/a/9866124/1924548
        //Aplicacion de CORS
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');    // cache for 1 day
        }

        // Access-Control headers are received during OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

            exit(0);
        }}
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 01 Jan 1996 00:00:00 GMT');
        // The JSON standard MIME header.
        header('Content-type: application/json ; charset=UTF-8');
        //header('Content-Type: text/javascript');
        //header('Content-Type: text/plain');
    }
    
    static function sendAjax(datatransfer $dt, view $oState, view $oGlobal) {
        ajaxutil::ajaxHeaders();
        $state = ajaxutil::calculateMsgState($dt, $oState, $oGlobal);
        echo ajaxutil::var2json(ajaxutil::json_encode($state, 'xeno'));
    }

    static function sendHtml(datatransfer $dt, view $oState, view $oGlobal) {
        $state_raw = ajaxutil::calculateMsgState($dt, $oState, $oGlobal);
        $state = ajaxutil::json_encode($state_raw);
        $affect = ajaxutil::json_encode(ajaxutil::getAffectsConstants());
        $position = ajaxutil::json_encode(ajaxutil::getPositionsConstants());
        $game = ajaxutil::json_encode(ajaxutil::getGameConstants());
        $phases = ajaxutil::json_encode(ajaxutil::getBattlePhases());
        $resources_rate = ajaxutil::json_encode(ajaxutil::getResourcesRatePerRegion ());
        $resources = ajaxutil::json_encode(ajaxutil::getActiveResources());
        echo ajaxutil::php2js($state, 'datatransfer');
        echo ajaxutil::php2js($affect, 'affect');
        echo ajaxutil::php2js($position, 'position');
        echo ajaxutil::php2js($phases, 'phases');
        echo ajaxutil::php2js($resources_rate, 'resource_rate');        
        echo ajaxutil::php2js($resources, '_x_resources');                
        echo ajaxutil::php2js(_X_HIDDEN_TIME, '_X_HIDDEN_TIME');
        echo ajaxutil::php2js(_X_TOTAL_RESOURCES, '_total_resources');
        echo ajaxutil::php2js($game, 'game');        
    }

    static function sendError(datatransfer $dt) {
        $state = ajaxutil::json_encode($sdt);
        echo ajaxutil::php2js($state, 'xeno');
    }

    static function sendAjaxError(datatransfer $dt) {
        ajaxutil::ajaxHeaders();
        echo ajaxutil::var2json(ajaxutil::json_encode($dt, 'xeno'));
    }

}

?>
