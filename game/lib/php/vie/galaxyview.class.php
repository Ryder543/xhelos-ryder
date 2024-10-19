<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad
/* * *****************************************************
 * planet: Clase para el control de un planet
 * **************************************************** */

class galaxyview extends view {

    //////////////////////////////////////////////////////////////////////////////////	
    //Variables
    ///////////////////////////////////////////////////////////////////////////////////
    //Variables Externas
    //Variables Internas
    private $db;
    private $stars_raw;
    private $starlanes_raw;

    //////////////////////////////////////////////////////////////////////////////////
    //Metodos
    //////////////////////////////////////////////////////////////////////////////////
    /*     * *******************************************************************************
     * army: constructor
     * ******************************************************************************* */
    public function __construct() {
        $this->db = db::singleton();
        $this->init();
        //$this->initPlayer();
        //$this->armyman = armyman::singleton();
        //$this->ext_galaxy_id = $galaxy_id;
    }

    /*     * *************************************************************************************************
     * getStars: Obtiene las estrellas de toda la galaxia, por ahora no hay en BD nada sobre la galaxia
     * ************************************************************************************************* */

    public function getStarLanes() {
        if (empty($this->starlanes_raw)) {
            $sql = "SELECT * FROM starlanes WHERE starlanes.state = '" . _X_STARLANE_STATUS_ACTIVE . "'";

            $starlanes_raw = $this->db->vectorize($sql);
            if (empty($starlanes_raw)) {
                debug::error('No se pueden cargar los starlanes', 'galaxystate.class.php');
            } else {
                $this->starlanes_raw = $starlanes_raw;
            }
        }
        return $this->starlanes_raw;
    }

    public function getStars() {
        if (empty($this->stars_raw)) {
            $sql = "SELECT s.*,(SELECT count(*) FROM planets p WHERE p.star_id=s.id) AS total_planets FROM stars AS s WHERE s.state = '" . _X_STAR_STATUS_ACTIVE . "'";
            $stars_raw = $this->db->vectorize($sql);
            if (empty($stars_raw)) {
                debug::error('No se pueden cargar las estrellas', 'galaxystate.class.php');
            } else {
                $this->stars_raw = $stars_raw;
            }
        }
        return $this->stars_raw;
    }

    public function getName() {
        return _X_GALAXY_NAME;
    }

    public function getStarFromAxis($x, $y) {
        //global $firephp;
        foreach ($this->stars_raw as $key => $star) {

            //$firephp->log('Orig X['.$x.']Y['.$y.'] VS star X['.$star['axis_x'].']Y['.$star['axis_y'].']');
            if (($star['axis_x'] == $x) && ($star['axis_y'] == $y)) {

                return $star;
            }
        }
        return false;
    }

    public function render() {//Obtener el Acceso a la Base de Datos
        $totalX = _X_GALAXY_SIZE_X;
        $totalY = _X_GALAXY_SIZE_Y;

        $DOM = '';
        //global  $firephp;
        
        $starsPlayer = new stars();
        $starsPlayer->initByPlayerId($this->getPlayer()->getId());
        
        for ($y = 1; $y <= $totalY; $y++) {
            $DOM = $DOM . '<div class="row" id="Row' . $y . '">';
            for ($x = 1; $x <= $totalX; $x++) {
                $star = $this->getStarFromAxis($x, $y);
                if($star){
                    
                    $mystarClass = ''; 
                    if ($starsPlayer->findByParam($star['id'],'id')) {
                        $mystarClass = 'ui-state-active'; 
                    }
                    else{
                        $mystarClass = 'ui-state-default'; 
                    }
                    
                    //$ostar = new star($star['id']);
                    $link = '';
                    $planets = new planets();
                    $planets->initByStar($star['id']);
                    //debug::log($planets,'Planetas de la estrella['.$star['id'].'] tiene '.$planets->size().' planeta(s)');
                    if ($planets->size() > 0) {
                        $link = "<a class='' href='star.php?star_id=" . $star['id'] . "' >" . $star['name'] . "</a>";
                        //$link = $star['name'];
                    } else {
                        $link = $star['name'];
                    }
                    //debug::log('Numero de planetas de '.$star['name'].' '.$ostar->getNumberOfPlanets(),'galaxystate->render->numberofplanets');
                    $DOM = $DOM . '<div class="star column" id="Star' . $star['id'] . '"><img src="img/star/smallstar' . $star['color'] . '.png" /> <span class="name '.$mystarClass.'">' . $link . '</span></div>';
                } else {
                    //$DOM = $DOM . '<li>['.$y.']['.$x.']</li></li>';
                    $DOM = $DOM . '<div class="empty column"></div>';
                }
            }
            $DOM = $DOM . '<div class="clear"></div></div>';
        }
        // Colocamos la estrella
        echo $DOM;
    }

    public function getGalaxyInfo() {
        $info = array();
        $info['sizex'] = _X_GALAXY_SIZE_X;
        $info['sizey'] = _X_GALAXY_SIZE_Y;
        return $info;
    }

    public function prepareState() {
        //0) Preparar la variable a usar _xeno
        $_xeno = array();
        //1) Obtener Estrellas
        $_xeno['stars'] = $this->getStars();
        //2) Obtener Starlanes
        $_xeno['starlanes'] = $this->getStarLanes();
        //2) Obtener GalaxyInfo
        $_xeno['galaxy'] = $this->getGalaxyInfo();
        $_xeno['players'] = $this->getPlayersInStars();

        $_xeno['player']['username'] = $this->getPlayer()->getParam('username');
        $_xeno['player']['id'] = $this->getPlayer()->getId();

        return $_xeno;
    }

    

     public function getPlayersInStars() {
        $val = $this->man("player")->initAllWithStar();
        //debug::log($val);
        return $val;
    }
    

    public function init() { 
        $this->getStars();
        if (empty($this->stars_raw)) {
            debug::error('La Galaxia no pudo obtener estrellas', 'galaxy.class');
            return false;
        }
        return true;
    }

    public function updateState(){
        $dt = new datatransfer();
        $dt->success('TIP: Las estrellas con nombre azul, son estrellas donde encontraras colonias y/o ejercito de tu propiedad');
        return $dt;
    }

    public function validateStateDt() {
        $dt = new datatransfer();
        $dt->success("[TODO]Validar el estado de la galaxia de alguna manera");
        //$dt->success():
        debug::warning("[TODO]Validar el estado de la GALAXIA de alguna manera","galaxystate->validateStateDt");    
        return $dt;
        //Verificar que la colonia le pertenezca al jugador             
    }

    public function getTitle() {
        return "Galaxia";
    }
}

?>