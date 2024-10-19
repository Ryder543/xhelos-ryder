<?php
 require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad
 require_once(dirname ( __FILE__ ).'/uti/db.class.php');//DBInfo.php contiene los parametros de conexion a la Base de Datos, 
  //[UPDATE]Ya no mas ahora secarga defrente de include
/*----------------------------------------------------------
/*******************************************************
 * json: Transformacion de DATA en Json 
 ******************************************************/
class json
{

	private static $instance;//Contiene la instancia unica del parseador de json
	private $db;// Variable que contiene la instancia unica de la base de datos
	private $query;// contiene los querys
	
	public function test()//Obtener el Acceso a la Base de Datos
	{
		echo "Im Alive";
	}
	public function add($strQuery)
	{
		$this->query[]=$strQuery;
	}
	public function debug($query)
	{
		//foreach($this->query as $valor){
			print "<pre>";
			//print_r($valor);
			$row = $this->db->query($query) or die("Error en consulta a DB. json.php. Consulta =".$query);
			$arr=array();
			while ($result=mysqli_fetch_assoc($row))
			{
				$arr[]=$result;
			}
			print_r($arr);
			print "/nSQL:";
			print_r($query);
			print "</pre>";
	   //}
	}
        
        public static function toJson($var){
            return json_encode($var);
        }
        
	public function var2json($var,$returnIsEchoed=true)
	{
		if(isset($var))
		{
			if($returnIsEchoed)
			{
				//echo "[".json_encode($var)."]";
				//echo "(".json_encode($var).")";
				echo json_encode($var);
			}
			else{
				return json_encode($var);
				//return json_encode($var);
			}
			//echo '{"hola:"'.json_encode($var).'"}';
			//echo json_encode($var);
		}
		else
		{
			//echo 'ERROR: La variable no esta seteada. FUNCION: json.var2json';
		}
	}
	public function object2json($var,$returnIsEchoed=true){
		if(isset($var))
		{
			if($returnIsEchoed)
			{
				//echo "[".json_encode($var)."]";
				echo "[".json_encode($var)."]";
			}
			else{
				return "[".json_encode($var)."]";
				//return json_encode($var);
			}
			//echo '{"hola:"'.json_encode($var).'"}';
			//echo json_encode($var);
		}
		else
		{
			//echo 'ERROR: La variable no esta seteada. FUNCION: json.var2json';
		}
	}
	public function query2json2($query,$returnIsEchoed=true)
	{	
		$arr=array();
		for($k=0;$k<sizeof($query);$k++)
		{
			if(strlen($query[$k])>0)
			{
				$row = $this->db->query($query[$k]) or die("Error en consulta a DB. json.php. Consulta =".$paramQuery);
				if($row)
				{
						while ($result=mysqli_fetch_assoc($row))
						{$arr[]=$result;}
				}
				else
				{
					echo 'ERROR: La consulta no devuelve valor en json.php. Consulta ='.$paramQuery;
				}
			}//if strlen
		}//for
		if($returnIsEchoed){
			echo json_encode($arr);}
		else{
			return $arr;}
		
	}//function

	public function query2json($paramQuery, $isOne=false,$returnIsEchoed=true)
	{
		if(strlen($paramQuery) >0)
		{
			$row = $this->db->query($paramQuery) or die("Error en consulta a DB. json.php. Consulta =".$paramQuery);
			if($row)
			{
				if(!$isOne)//Devuelve Muchos Objetos ( Un arreglo de arreglos)
				{
					$arr=array();
					while ($result=mysqli_fetch_assoc($row))
					{
						$arr[]=$result;
					}
					if($returnIsEchoed)
					{
						echo json_encode($arr);
					}
					else
					{
						return $arr;
					}
					//echo "devuelvo false";
				}
				else//Devuelve un solo objeto
				{
					$result=mysqli_fetch_assoc($row);
					if($returnIsEchoed)
					{
						echo "[".json_encode($result)."]";
						//echo json_encode($result);
					}
					else
					{
						return $result;
					}
					//echo "devuelvo true";
				}
			}
			else
			{
				echo 'ERROR: La consulta no devuelve valor en json.php. Consulta ='.$paramQuery;
			}
		}
	}
	private	function __construct()//Constructor Privado de json, solo existe un objeto de este tipo
	{
		$this->init();
	}
	
	public static function singleton()//Obtiene la instancia unica de esta clase
    {
       if (!isset(self::$instance)) {
           $c = __CLASS__;
           self::$instance = new $c;
       }
       return self::$instance;
    }
   
   public function __clone() // Prevenimos que este objeto sea clonado
   {
       trigger_error('Clone is not allowed. db_pg_class', E_USER_ERROR);
   }
   
	private function init()
	{
		$this->db=db::singleton();
		if(!$this->db) 
		{
			echo 'ERROR: Could not connect to the database. json.php';
		}
		else
		{
			$this->query=array();
		}
	}//init()
}//json
?>
<?php

?>
