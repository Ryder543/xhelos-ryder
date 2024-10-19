<?php
require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
/*******************************************************
 * region: Clase para el control de una region
 ******************************************************/
class region extends identity
{	
    //////////////////////////////////////////////////////////////////////////////////
    //Variables
    ///////////////////////////////////////////////////////////////////////////////////
    //Variables Internas
    private $array_map;
    private $resources_raw;
    private $maputil; //Lo usamos solo cuando lo necesitemos!
    private $cachedmaputil; //Version con data de la region
    //////////////////////////////////////////////////////////////////////////////////
    //Metodos
    //////////////////////////////////////////////////////////////////////////////////
    /*********************************************************************************
    * army: constructor
    *********************************************************************************/
    public function __construct($region_id=0){//Para otro tipo de inicializaciones, este
        $args = func_get_args();
        if(!empty($args)){
          debug::error('region se inicio con argumentos','region->constructor');
        }

        parent::__construct();
    }

  public function  postRefresh() {
      //La variable de mapa de array (texto de la bd) es transformada a arreglo
        if(empty($this->array_map)){
          //$this->array_map = str2array($this->getParam('map'));
            $this->array_map = $this->getMap();
        }
    }

  /*********************************************************************************
  * getParam: Obtiene parametros de esta clase [TODO]Metodo de la interface 'objeto'
  *********************************************************************************/
public function getPlanetId(){
    return $this->getParam('planet_id');
}                
 public function getOwnerId(){
    return $this->getParam('owner_id');
}
 public function getPlanetPosition(){
    return $this->getParam('planet_position');
}

/*
  public function getResources(){
    $sql ="SELECT COALESCE(p.username,'Sin Dueño') as username,rs.* FROM regions_resources AS rs LEFT JOIN players AS p ON (rs.player_owner_id = p.id) WHERE rs.region_id = ".$this->getId();
 
    $resources_raw = $this->db->vectorize($sql);
    if(empty($resources_raw)){
      //debug::error('No se pueden cargar los recursos del planeta ['.$this->getName().']','planet.class.php');
      $resources_raw="La Region ".$this->getName()." no tiene recursos";
    }
    else{
      $this->resources_raw=$resources_raw;
    }
    return $this->resources_raw;
  }*/

  public function getMap(){
    //Por defecto grabamos una region sin mapa, pero como no podemos mandar null a un array de postgres le mandamos un arreglo vacio en su primer miembro
    if(!isset($this->array_map) || empty($this->array_map[0])){ 
        
        if( (!isset($this->raw['map'])) || (empty($this->raw['map'])) || ($this->raw['map'] == '{}') ){
            $eru = new entityregionutil();
            $dt = $eru->setMapOfRegion($this->getId(),$this->getX(),$this->getY(),$this->getType());
            //$this->setDataDirty();//Um veamos que pasa pue!
            $data = $dt->getData();
            //Con esto nos aseguramos de tener todos los datos correctos
            $this->array_map = str2array($data['stringMap']);
            $this->raw['map'] = $data['stringMap'];
        }
        else{
            //Serge Gainbourg
            //TODO: si quisieramos podriamos hacer lo siguiente:
            //PReguntar si existe array_map, si no existe entonces preguntar si existe en la variable raw['map'] esta con buena data, si no creamos
            //el mapa y mandamos al carajo todo France Gall - Laisse tomber les filles - 1964
            $this->array_map = str2array($this->getParam('map'));
        }
    }
    return $this->array_map;
  }
  
  public function getMapUtil(){
      if(empty($this->maputil)){
          //debug::log('region->getMapUtil esta vacio');
          $this->maputil = new maputil($this->getMap(),$this->getX(),$this->getY(),$this->getId());
          //debug::log($this->maputil);
      }
      
      return $this->maputil;
  }
  public function getCachedMapUtil(){
      if(empty($this->cachedmaputil)){
          //debug::log('region->getMapUtil esta vacio');
          $this->cachedmaputil = new cachedmaputil($this->getMap(),$this->getX(),$this->getY(),$this->getId());
          //debug::log($this->maputil);
      }
      return $this->cachedmaputil;
  }
  
  public function getTotalTiles(){
      $val= $this->getParam('x')*$this->getParam('y');
      $count = count($this->array_map);
      if($val != $count){
          debug::warning('x['+$this->getParam['x']+']* y['+$this->getParam['y']+'] != count(arraymap)['+$count+']');
      }
      return $count;
  }
  public function getCoord($total_y){

      $index = $this->getParam('planet_position');
      //[Jeeba 12feb2013] Le quite el menos 1 porque me daba errores en la validacon.. pero que raro!
      $index= $index;//[WHY]PORQUE el indice siempre empieza en cero
      
      $x = $index%$total_y;
      $y = ($index-$x)/$total_y;
      $coord['y'] = $y;
      $coord['x'] = $x;
  return $coord;
  }

  public function getTileTypeByIndex($index){
    $map_raw = $this->getMap();
    return $map_raw[$index];
  }

  
  public function initRandom(){
    //[TODO]  mover a regionman
    //Obten una region randonmente
    $sql = "SELECT id FROM regions WHERE state = '"._X_REGION_STATUS_ACTIVE."' ORDER BY RANDOM() LIMIT 1";
    $region_raw = $this->db->fetch($sql);
    
    if(empty($region_raw)){
      debug::error('La region no pudo inicializarse randommente','region.class.php');
    }
    else{
      $this->initByArray($region_raw);
    }
  }

    public function getName(){
            return $this->getParam('name');
    }
  
    public function getX(){
        return $this->getParam('x');
    }
    
    public function getType(){
        return $this->getParam('type');
    }
    
    public function getY(){
        return $this->getParam('y');
    }
  
   ///////////////////////////////////////////////////////////////////////////////////////
   //PRINCIPALES
   ///////////////////////////////////////////////////////////////////////////////////////    
   
   /********************************************
   * render(): Imprime Mapa de Batalla
   ********************************************/  
   public function render()//Obtener el Acceso a la Base de Datos
    {
     /* $regionId = $this->getId();
      if(!empty($regionId))
      {
        $dom='<table class="battle_map" id="BattleMap">';
        $resp_y =$this->getParam('y');
        $resp_x =$this->getParam('x');

        //debug::log($resp_y,'region render resp_y');
        //debug::log($resp_x,'region render resp_x');
        
        for($y=0;$y<$resp_y;$y++)
        {
          $dom=$dom."<tr class='terrain' id='y".$y."'>";
          for($x=0;$x<$resp_x;$x++)
          {
            
            $data='';
            $index= mapIndex($resp_x,$y,$x);
            //debug::log($index,'region render mapindex');
            $type=$this->getTileTypeByIndex($index);
            $dom=$dom."<td id='x".$x."' class='terrain t".$type."'>".$data.'</td>';
            
          }
          $dom=$dom."</tr>";
        }
        echo $dom,'</table>';       
      }
      else{
        $dom='<table class="battle_map"><tr><td>Mapa No Accesible</td></tr></table>';
        echo $dom;
      }*/
    }
    
    public static function mapTypeList(){
        //prado =1 ; mountain = 2; desert = 3 sea = 4 ; snow = 5//6 inicial
        //[OJO] Si se van a añadir nuesvos tipos, entonces tengan en cuenta de poner los valores mas pequeños en los extremos como en MOUNTAIN,
        //si no, Perlin Noise no le dara mucha bola a los demas valores
        $mapTypes = array();
        $mapTypes[_X_REGION_MAP_TYPE_PLAIN] = array(4=>30,1=>26,3=>20,2=>25,5=>0);//Prado 0
        $mapTypes[_X_REGION_MAP_TYPE_DESERT] = array(1=>0,3=>40,2=>20,5=>35,4=>0); //Desierto 1
        $mapTypes[_X_REGION_MAP_TYPE_MOUNTAIN] = array(4=>0,2=>33,3=>32,1=>30,5=>0); //Montaña 2
        $mapTypes[_X_REGION_MAP_TYPE_LAGOS] = array(2=>20,1=>35,4=>25,3=>20,5=>0);//LAgos 3
        $mapTypes[_X_REGION_MAP_TYPE_NIEVE] = array(1=>20,2=>30,5=>30,4=>20,3=>0);//Nieve y Rocas 4
        $mapTypes[_X_REGION_MAP_TYPE_INICIAL] = array(1 => 40, 2 => 25, 3 => 30, 4 => 5, 5 => 0); //Tipo de Mapa Inicial 5
        return $mapTypes;
    }

    public static function mapTypes(){
        //prado =1 ; mountain = 2; desert = 3 sea = 4 ; snow = 5//6 inicial
        //[OJO] Si se van a añadir nuesvos tipos, entonces tengan en cuenta de poner los valores mas pequeños en los extremos como en MOUNTAIN,
        //si no, Perlin Noise no le dara mucha bola a los demas valores
        $mapTypes = array();
        $mapTypes[_X_REGION_MAP_TYPE_PLAIN] = _X_REGION_MAP_TYPE_PLAIN;
        $mapTypes[_X_REGION_MAP_TYPE_DESERT] = _X_REGION_MAP_TYPE_DESERT;
        $mapTypes[_X_REGION_MAP_TYPE_MOUNTAIN] = _X_REGION_MAP_TYPE_MOUNTAIN;
        $mapTypes[_X_REGION_MAP_TYPE_LAGOS] = _X_REGION_MAP_TYPE_LAGOS;
        $mapTypes[_X_REGION_MAP_TYPE_NIEVE] = _X_REGION_MAP_TYPE_NIEVE;
        $mapTypes[_X_REGION_MAP_TYPE_INICIAL] = _X_REGION_MAP_TYPE_INICIAL;
        return $mapTypes;
    }
    
    
    public function prepareRaw() {
        $preparedRaw = $this->raw;       
        unset($preparedRaw['map']);
        return $preparedRaw;
    }
    
    
    public function setOwnerIdDt($ownerId) {
        $regionId = $this->getId();
        $sql = "UPDATE regions SET owner_id = " . $ownerId . " WHERE regions.id=" . $regionId;
        $dt = $this->db->updateEntityDt($sql,_X_MAN_COLONY,$this->getId());
        return $dt;
    }
}

?>