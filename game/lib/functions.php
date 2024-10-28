<?php


/* ----------------------------------------------------------
  FUNCIONES del Juego
  ---------------------------------------------------------- */
  function canDebug() {
    /*
 $allowed = array ('127.0.0.1','192.168.1.37','192.168.1.3', '81.1.1.1');
 if (in_array ($_SERVER['REMOTE_ADDR'], $allowed)) return true;
 else return false;*/
    return true;
}

//http://www.php.net/manual/en/function.fmod.php#83319
function modulo($n, $b) {
    // global $firephp;
    //$firephp->log($n."-".$b."*floor(".$n."/".$b.")");
    return $n - $b * floor($n / $b);
}

/* * ********************************************
 * MAP INDEX: Calcula el indice de una region
 * ****************************************** */

function mapIndex($total_x, $y, $x) {
    $index = ($y * $total_x) + ($x + 1) - 1;
    return $index;
}

function mapCoordFromIndex($total_x, $index) {
    $x = $index % $total_x;
    $y = ($index - $x) / $total_x;
    $coord['y'] = $y;
    $coord['x'] = $x;
    return $coord;
}

//$index=($y*$resp_y)+($x+1)-1;
if (!function_exists('user_is_anonymous')) {

    function user_is_anonymous() {
        return!$GLOBALS['user']->uid || !empty($GLOBALS['menu_admin']);
    }

}

/*
if(!function_exists('get_called_class'))
 {
  class classTools
   {
    static $i = 0;
    static $fl = null;
    static function get_called_class()
     {
      $bt = debug_backtrace();
      if(self::$fl == $bt[2]['file'].$bt[2]['line']) self::$i++;
      else {
       self::$i = 0;
       self::$fl = $bt[2]['file'].$bt[2]['line'];}
      $lines = file($bt[2]['file']);
      preg_match_all('/([a-zA-Z0-9\_]+)::'.$bt[2]['function'].'/', $lines[$bt[2]['line']-1], $matches);
      return $matches[1][self::$i];
     }
   }
  function get_called_class()
   {
    return classTools::get_called_class();
   }
 }*/


if (!function_exists('json_encode')) {

    function json_encode($a=false) {
        if (is_null($a))
            return 'null';
        if ($a === false)
            return 'false';
        if ($a === true)
            return 'true';
        if (is_scalar($a)) {
            if (is_float($a)) {
                // Always use "." for floats.
                return floatval(str_replace(",", ".", strval($a)));
            }

            if (is_string($a)) {
                static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
                return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
            }
            else
                return $a;
        }
        $isList = true;
        for ($i = 0, reset($a); $i < count($a); $i++, next($a)) {
            if (key($a) !== $i) {
                $isList = false;
                break;
            }
        }
        $result = array();
        if ($isList) {
            foreach ($a as $v)
                $result[] = json_encode($v);
            return '[' . join(',', $result) . ']';
        } else {
            foreach ($a as $k => $v)
                $result[] = json_encode($k) . ':' . json_encode($v);
            return '{' . join(',', $result) . '}';
        }
    }

}

function require_once2($file, $d="") {
    for ($i = 0; $i < 5; $i++) {
        if (!is_file($d . $file)) {
            $d.="../";
        } else {
            require_once $d . $file;
            break;
        }
    }
}

function include_once2($file, $d="") {
    for ($i = 0; $i < 5; $i++) {
        if (!is_file($d . $file)) {
            $d.="../";
        } else {
            include_once $d . $file;
            break;
        }
    }
}

function include_css($file, $type="NONE", $d="") {
//echo '<link href="php/'.$d.$file.'" rel="stylesheet" type="text/css" />' ;
    for ($i = 0; $i < 5; $i++) {
        if (!is_file($d . $file)) {
            $d.="../";
        } else {
            switch ($type) {
                case "PRINT":
                    echo '<link href="' . $d . $file . '" rel="stylesheet" media="print" type="text/css"  />';
                    break;
                default:
                    echo '<link href="' . $d . $file . '" rel="stylesheet" type="text/css" />';
            }//switch
            break;
        }//else
    }//for
}

//include_css

function include_js($file, $d="") {
//echo '<link href="php/'.$d.$file.'" rel="stylesheet" type="text/css" />' ;
    for ($i = 0; $i < 5; $i++) {
        if (!is_file($d . $file)) {
            $d.="../";
        } else {
            echo '<script type="text/javascript" src="' . $d . $file . '" /></script>';
            break;
        }
    }
}

function str2array($str) {
    $delete = array("{", "}");
    $replaced = str_replace($delete, "", $str);
    return explode(",", $replaced);
}

function varDebug($var, $strName) {
    if ($strName == null) {
        $strName = "VARDEBUG";
    }
    echo '***' . $strName . '***';
    print "<pre>";
    print_r($var);
    print "</pre>";
}

function varDebug2($var, $strName) {
    if ($strName == null) {
        $strName = "VARDEBUG";
    }
    $str = '***' . $strName . '***' . print_r($var, true);
    return $str;
}

function debugVar($var, $strName) {
    if ($strName == null) {
        $strName = "VARDEBUG";
    }
    echo '***' . $strName . '***';
    print "<pre>";
    print_r($var);
    print "</pre>";
}

/* * ********************************************* */
/* 	Caso: Mandar de PHP a Javascript	 	 */
/* * ********************************************* */

function php2js($var, $varname, $echo_on = true) {
    $script = "<script type='text/javascript'>";
    $script = $script . "$varname = eval($var)";
    $script = $script . "</script>";
    if ($echo_on) {
        echo $script;
    } else {
        return $script;
    }
}

/* * ***************************************************************** */
/*  redirectHome: Redirecciona a la pagina inicial    */
/* * ***************************************************************** */

function redirectHome() {
    $host = $_SERVER['HTTP_HOST'];
    $uri = '/xhelos';
    $extra = 'index.php';
    header("Location: http://$host$uri/$extra");
}

function redirectGameInit($msg=false) {
    debug::log('include redirectGameInit1');
    $host = $_SERVER['HTTP_HOST'];
    $uri = '/xhelos/game';
    $extra = 'galaxy.php';
    
    if($msg){
        $extra = $extra.'?error='.$msg;
    }
    
    header("Location: http://$host$uri/$extra");
    debug::log('include redirectGameInit2');
}

function redirectNotFound() {
    $host = $_SERVER['HTTP_HOST'];
    $uri = '/xhelos';
    $extra = '/' . _X_URL_ERROR;
    header("Location: http://$host$uri$extra");
}

function redirectAdmin() {
    $host = $_SERVER['HTTP_HOST'];
    $uri = '/xhelos/game';
    $extra = '/' . _X_URL_ADMIN;
    header("Location: http://$host$uri$extra");
}

/* * ***************************************************************** */
/* 	validEmail: Valida que el formato del email sea correcto	 	 */
/* * ***************************************************************** */

/**
  Validate an email address.
  Provide email address (raw input)
  Returns true if the email address has the email
  address format and the domain exists.
 */
function validEmail($email) {
    $isValid = true;
    $atIndex = strrpos($email, "@");
    if (is_bool($atIndex) && !$atIndex) {
        $isValid = false;
    } else {
        $domain = substr($email, $atIndex + 1);
        $local = substr($email, 0, $atIndex);
        $localLen = strlen($local);
        $domainLen = strlen($domain);
        if ($localLen < 1 || $localLen > 64) {
            // local part length exceeded
            $isValid = false;
        } else if ($domainLen < 1 || $domainLen > 255) {
            // domain part length exceeded
            $isValid = false;
        } else if ($local[0] == '.' || $local[$localLen - 1] == '.') {
            // local part starts or ends with '.'
            $isValid = false;
        } else if (preg_match('/\\.\\./', $local)) {
            // local part has two consecutive dots
            $isValid = false;
        } else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
            // character not valid in domain part
            $isValid = false;
        } else if (preg_match('/\\.\\./', $domain)) {
            // domain part has two consecutive dots
            $isValid = false;
        } else if
        (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
                        str_replace("\\\\", "", $local))) {
            // character not valid in local part unless
            // local part is quoted
            if (!preg_match('/^"(\\\\"|[^"])+"$/',
                            str_replace("\\\\", "", $local))) {
                $isValid = false;
            }
        }
        //[TODO] Arreglar esta funcion con loque hay en PHP http://www.php.net/manual/en/function.checkdnsrr.php#85198
        if (function_exists('checkdnsrr')) {
            if ($isValid && !(checkdnsrr($domain, "MX") || checkdnsrr($domain, "A"))) {
                // domain not found in DNS
                $isValid = false;
            }
        }
    }
    return $isValid;
}

?>