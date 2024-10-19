<?php

require_once(dirname(__FILE__) . '/security.php'); //Advertencia de Seguridad

/* * *****************************************************
 * battleman: Gestion de Batallas 
 * **************************************************** */

class starview extends view {

    private $ext_star_id;
    private $star;
    private $db;
    private $max_orbit;
    private $planets;

    public function __construct($star_id) {
        $this->ext_star_id = $star_id;
        $this->db = db::singleton();
    }

    public function getStar(){
        if( empty($this->star) ){
            $this->star = $this->man("star")->findById($this->getStarId());
            if( empty($this->star) ){
                debug::error('starstate->getStar La estrella no existe');
            }
        }
        return $this->star;
    }
    
    public function getPlanets(){
        if( empty($this->planets) ){
            $this->planets = new planets();
            $this->planets->initByStar($this->getStarId());
            if( ! $this->planets->exist() ){
                debug::error('starstate->getStar La estrella no existe');
            }
        }
        return $this->planets;
    }
    
    
    public function getStarId(){
        return $this->ext_star_id;
    }
    public function setStarId($starId){
        $this->ext_star_id = $starId;
    }
    
    public function getStarMaxOrbit(){
        if(empty($this->orbit)){
            $sql = "SELECT (SELECT p.orbit FROM planets AS p WHERE p.star_id = " . $this->getStarId() . " AND p.state = '" . _X_PLANET_STATUS_ACTIVE . "' ORDER BY p.orbit DESC LIMIT 1) AS max_orbit FROM stars AS s WHERE s.id = " . $this->getStarId() . " AND s.state = '" . _X_STAR_STATUS_ACTIVE . "'";
            $resp =$this->db->fetch($sql);
            $this->max_orbit = $resp['max_orbit'];
        }
       return $this->max_orbit;
    }
    
    public function render() {//Obtener el Acceso a la Base de Datos
        $orbits = $this->getStarMaxOrbit();
        $DOM = '<ul id="StarSystem">';
        $planets = $this->getPlanets();
        
        $planetsPlayer = new planets();
        $planetsPlayer->initByPlayerId($this->getPlayer()->getId());
        
        //debug::Log($planets,'starstate->render planets');
        for ($x = $orbits; $x > 0; $x--) {
            //$planets = $this->star->getPlanetsInOrbit($x);
            $planetsInOrbit = $planets->getPlanetsInOrbit($x);
            $size = $planets->getMaxSizeInOrbit($x);
            //debug::log($planetsInOrbit,'starstate render');
            $DOM = $DOM . '<li id="Orbit' . $x . '" class="orbit size ui-state-transparent ' . $size . '">';
            $DOM = $DOM . '<div class="orbitname">Orbita ' . $x . '</div>';
            //debug::Log($planets,'starstate->render planets');
            if(count($planetsInOrbit) == 0){
               $DOM = $DOM . "<div class='object planet' ><div class='inner-object'><p>En esta orbita no existen planetas</p></div></div>"; 
            }
            else{   
                foreach ($planetsInOrbit as $key => $planet) {
                    $oplanet = $this->man('planet')->findById($planet['id']);

                    $link = '';
                    $regions = new regions();
                    $regions->initByPlanetId($oplanet->getId());

                    $myself = ''; 
                    if ($planetsPlayer->findByParam($planet['id'],'id')) {
                        $myself = 'ui-state-active'; 
                    } 
                    else{
                        $myself = 'ui-state-default';
                    }

                    //debug::log('Numero de Regiones de '.$planet['name'].' '.$regions->size(),'starstate->render->numberofregion');
                    if ($regions->size() > 0) {
                        $link = "<a class='".$myself."' href='planet.php?planet_id=" . $planet['id'] . "' >" . $planet['name'] . "</a>";
                    } else {
                        $link = $planet['name'];
                    }
                    $DOM = $DOM . "<div id='planet" . $planet['id'] . "' class='object size" . $planet['size'] . " planet ' ><div class='inner-object'><img src='./img/planet/planet" . $planet['size'] . $planet['color'] . "_small.png'/><p class='name'>" . $link . "</p></div></div>";
                }
            }
            
            $DOM = $DOM . '<div class="clear"></div></li>';
        }
        $DOM = $DOM . '<li class="star"><img src="./img/star/star' . $this->getStar()->getParam('color') . '.png"/></li>';
        $DOM = $DOM . '</ul>';
        echo $DOM;
    }


    public function prepareState() {
        //1) Informacion de los Planetas
        $_xeno = array();
        //1) Obtener info del planeta
        $_xeno['star']['name'] = $this->getStar()->getName();
        //2) Informacion de los Recursos
        $_xeno['resources'] = $this->getPlanetsTotalResources();
        //3) Informacion de los Recursos
        $_xeno['planets'] = $this->getPlanets()->getRaw();
        //4) Jugadores
        $_xeno['players'] = $this->getPlayersInPlanets();
        //5) Colonias
        //6) Informacion del Jugador
        $_xeno['player']['username'] = $this->getPlayer()->getParam('username');
        $_xeno['player']['id'] = $this->getPlayer()->getId();
        return $_xeno;
    }
    
    
     /*********************************************************************************
     * getPlayersInPlanets: Obtiene los jugadores en cada uno de los planetas
     * ******************************************************************************* */
    public function getPlayersInPlanets() {
        $planets = $this->getPlanets()->getRaw();
        $players = array();
        foreach ($planets as $key => $planet) {
            $val = $this->man('player')->initAllByPlanet($planet['id']);
            $players[$planet['id']] = $val;
        }
        return $players;
    }
    

    public function getPlanetsTotalResources() {
        //debug::trace('star->getPlanetsTotalResorces');
        $planets = $this->getPlanets()->getRaw();
        $resources = array();
        foreach ($planets as $key => $planet) {
            $oPlanet = $this->man("planet")->wrap($planet);
            //$oplanet = new planet($planet['id']);
            $resources[$oPlanet->getId()] = $oPlanet->getTotalResources();
        }
        return $resources;
    }
    public function updateState(){
        $dt = new datatransfer();
        $dt->success('Seleccione un planeta para navegar dentro de el');
        return $dt;
    }

    public function validateStateDt() {
         $dt = new datatransfer();
        $dt->success("[TODO]Validar el estado de la estrella de alguna manera");
        //$dt->success():
        debug::warning("[TODO]Validar el estado de la ESTRELLA de alguna manera","starstate->validateStateDt");    
        return $dt;
        //Verificar que la colonia le pertenezca al jugador       
    }

    public function getTitle() {
        return "Estrella ".$this->getStar()->getName();
    }

}

//starstate
?>
