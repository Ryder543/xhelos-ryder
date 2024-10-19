<?php

  require_once(dirname ( __FILE__ ).'/../security.php'); //Advertencia de Seguridad

  /*Clase Abstracta que indica que tiene un datatransfer que puede ser manejado
  * Util si se quiere concatenar varias llamadas de un dt y preguntar si todo salio
  *  a pedir de boca   */
  
  abstract class dtstate{
    //protected $refresh;   
    protected $dt;    
    //////////////////////////////////////////////////////////////////////////////////
    //CONSTRUCTOR
    //////////////////////////////////////////////////////////////////////////////////
    public function __construct(){
          /* $dt = new datatransfer();
           $dt->success("Success Inicial");*/
    }
    
    public function getDt(){
        if(empty ($this->dt)){
            $this->dt = new datatransfer();
            $this->dt->success("DATATRANSFER INICIAL");
        }
        return $this->dt;
    }
    
    //Setea esta variable SOLO si DT antiguo es ok (valido) si no no setea nada. Util para guardar la ultima ves que exploto algo
    public function setIfOldDtIsOk($dt){
        if($this->getDt()->ok()){
            $this->dt = $dt;
        }
    }
     /**
     * Genial funcion que llama a la siguiente funcion mandada como String para
     * que sea ejecutada solo si el actual DT es success. Lo genial de esto es que
     * mandamos a la funcion como una especie de callback. Solo funciona para metodos
     * que no reciben parametros
     *
     * @param string $callback Nombre del metodo a ejecutar si el estado actual del dt es ok. 
     * Ojo con los private, debe de ser protected el metodo para que esto funcione
     *
     * @return  string
     * @todo    Buscar en otras clases donde ejecutar esto
     *
     * @since   27/01/2013
     * @author  Jeeba
     *
     * @edit    27/01/2013<br />
     *          Jeeba<br />
     *          Creacion Inicial <br/>
     *          #edit1
     */
    public function setIfOldDtIsOkWithCallback($callback){
        if($this->getDt()->ok()){
            $dt = $this->$callback();
            $this->dt = $dt;
        }
    }   
    //Setea esta variablea BAD
    public function setBadDt($text,$data=false){
        $dt = new datatransfer();
        $dt->error($text,$data);
        $this->dt = $dt;
    }
    
     //Setea esta variablea BAD
    public function setWarningDt($text,$data=false){
        $dt = new datatransfer();
        $dt->invalidWarning($text,$data);
        $this->dt = $dt;
    }
    
    public function isOk(){
        return $this->getDt()->ok();
    }
    
    public function setIfAllOkText($text) {
            if ($this->getDt()->ok()) {
                $this->dt->setText($text);
            }
    }
  }

?>