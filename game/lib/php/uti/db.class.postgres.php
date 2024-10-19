<?php
/*---------------------------------------------------------------------------------------------------
CDB_PostGres.PHP -Archivo que contiene a la Clase CDB
Clase para el manejo de la base de datos Postgres
Autor: Jose Carlos Tamayo joseka17@hotmail.com
Mejoras: Michi

PROPIEDADES:
------------------------------------------------------------------------------------------------------
-SINGLETON: Solo se Instancia una ves
----------------------------------------------------------------------------------------------------*/
require_once(dirname ( __FILE__ ).'/security.php'); //Advertencia de Seguridad

class db
{
//////////////////////////////////////////////////////////////////////////////////////
//Propiedades de la Clase
///////////////////////////////////////////////////////////////////////////////////////

	private static $instance;//Contiene la instancia unica de la base de datos
	private $strAccess;// Cadena de Conexion a la base de datos
	private $dbAccess;// Variable que contiene la instancia unica de la base de datos
	
///////////////////////////////////////////////////////////////////////////////////////
//Metodos de la Clase
///////////////////////////////////////////////////////////////////////////////////////
	public function getAccess()//Obtener el Acceso a la Base de Datos
	{
		return $this->dbAccess;
	}
	
	//*******************************************************************************************
	//	debug: Imprime la cadena de query y los datos obtenidos      	          					   
	//*******************************************************************************************
	//  PARAMETROS:
	//	$query: La cadena a analizar
	//  RETORNA:
	//	[HTML]Imprime la cadena y lo retornado ( caso de ser un select)
	//*******************************************************************************************	
	public function debug($query)// Imprime en tabla un Query 
	{
		echo "<div class='debug'>";
		echo "<span class='debug_title'>QUERY:</span><span class='debug'>".$query."</span><br>";
		$result = pg_query($this->getAccess(),$query) or die('No se pudo hacer el Query:<br>'.$query);
		$num = pg_numrows($result);
		for($count=0;$count<$num;$count++)
		{
			$objeto=pg_fetch_object($result,$count);
		 	$vars = get_object_vars($objeto);
			echo "<BR>OBJETO Nro:{$count}";
			foreach ( $vars as $key => $var )
			{
				echo "<BR>{$key}:{$var}";
			}
			echo"<BR>FIN DE OBJETO Nro:{$count}<BR>";
		}
		echo "</div>";
	}
	//*******************************************************************************************
	//	status: Imprime el Estado de la Conexion a la BD      	          					   
	//*******************************************************************************************
	//  PARAMETROS:
	//	$stringMe: Devuelve una cadena Mostrando el estado [NOT IMPLEMENTED]
	//  RETORNA:
	//	[HTML]Imprime el estado,y otras variables
	//*******************************************************************************************
	public function status($stringMe=false)// Imprime el Estado de la Base de Datos
	{	
		$stat = pg_connection_status($this->getAccess());
		/* Showing current php.ini settings to be sure that persistent connections s  are allowed. -1 means 'unlimited'*/
		echo "<br>Status de la Pagina Web\n";
		echo "<br>Estado de la Conexion:\n";
		if ($stat === PGSQL_CONNECTION_OK) 
			{echo 'OK';}
		else
    		{echo '<br>BAD';}
			
		echo '<br>pgsql.allow_persistent: ' . ini_get('pgsql.allow_persistent');
		echo '<br>pgsql.max_persistent: ' . ini_get('pgsql.max_persistent');
		echo '<br>pgsql.max_links: ' . ini_get('pgsql.max_links');
		echo '<br><br>';
	}
  //*******************************************************************************************
  //  vectorize: Transforma un $query en un array                               
  //*******************************************************************************************
  //  PARAMETROS:
  //  $query: Cadena que contiene el query para la BD
  //  RETORNA:
  //  $result: Retorna un array
  //*******************************************************************************************  
  public function vectorize($query,$autoindex = true)// Devuelve un query a la BD
  {
    return $this->sql2array($query,$autoindex);
  }
  
  /*
   * sql2array: Devuelve un query como un arreglo, agrupado por la variable autoindex.
   * Si la variable autoindex es true, entonces lo agrupa por el dato id, si 
   * es una cadena, lo agrupa por el campo de esa cadena, si es false entonces
   * el arreglo es ordenado normalmente
   */
public function sql2array($query,$autoindex = false)// Devuelve un query a la BD
{
    //pg_set_client_encoding($this->getAccess(),'UTF8');
    //$sqlSet0="set datestyle to 'SQL'";
    //$resSet0 = pg_exec($this->getAccess(),$sqlSet0) or die("No se pudo Cambiar la fecha en BD 0 php/conectar.php");

    //Output de DD-MM-AAAA
    //$sqlSet1="set datestyle to 'Euro'";
    //$resSet1 = pg_exec($this->getAccess(),$sqlSet1) or die("No se pudo Cambiar la fecha en BD 1 php/conectar.php");
    global $firephp;
    $result_raw = pg_query($this->getAccess(),$query)  or die($firephp->trace('Trace Label:'.pg_last_error()));
    $result = array();
    while($result_fila = pg_fetch_assoc($result_raw)){
        if((!empty($result_fila['id']))&&$autoindex){
            if(is_string($autoindex))
            {
                $result[$result_fila[$autoindex]] =$result_fila;
            }
            else{
                $result[$result_fila['id']] =$result_fila;  
            }
        }
        else{
            $result[] =$result_fila;  
        }
    }
    return $result;
}

  /*
   * sql2array: Devuelve un query como un arreglo, agrupado por la variable autoindex.
   * Si la variable autoindex es true, entonces lo agrupa por el dato id, si 
   * es una cadena, lo agrupa por el campo de esa cadena, si es false entonces
   * el arreglo es ordenado normalmente
   */
public function sql2group($query,$autoindex = false)// Devuelve un query a la BD
{
    //pg_set_client_encoding($this->getAccess(),'UTF8');
    //$sqlSet0="set datestyle to 'SQL'";
    //$resSet0 = pg_exec($this->getAccess(),$sqlSet0) or die("No se pudo Cambiar la fecha en BD 0 php/conectar.php");

    //Output de DD-MM-AAAA
    //$sqlSet1="set datestyle to 'Euro'";
    //$resSet1 = pg_exec($this->getAccess(),$sqlSet1) or die("No se pudo Cambiar la fecha en BD 1 php/conectar.php");
    global $firephp;
    $result_raw = pg_query($this->getAccess(),$query)  or die($firephp->trace('Trace Label:'.pg_last_error()));
    $result = array();
    while($result_fila = pg_fetch_assoc($result_raw)){
        if((!empty($result_fila['id']))&&$autoindex){
            if(is_string($autoindex))
            {
                $result[$result_fila[$autoindex]][$result_fila['id']] =$result_fila;
            }
            else{
                $result[$result_fila['id']] =$result_fila;  
            }
        }
        else{
            $result[] =$result_fila;  
        }
    }
    return $result;
}

	public function result2array($resultRaw)// Devuelve un query a la BD
	{
             /*if ($this->class == 'planet') {
                debug::log($result, 'identitymap->find3.-2');
            }*/
		//pg_set_client_encoding($this->getAccess(),'UTF8');
		//$sqlSet0="set datestyle to 'SQL'";
		//$resSet0 = pg_exec($this->getAccess(),$sqlSet0) or die("No se pudo Cambiar la fecha en BD 0 php/conectar.php");
	
	//Output de DD-MM-AAAA
		//$sqlSet1="set datestyle to 'Euro'";
		//$resSet1 = pg_exec($this->getAccess(),$sqlSet1) or die("No se pudo Cambiar la fecha en BD 1 php/conectar.php");
	

		while($result_fila = pg_fetch_assoc($resultRaw)){
			$result[] =$result_fila; 
		}
		return $result;
	}
        
  //*******************************************************************************************
  //  insert: Funcion para insertar algun dato en la bd                               
  //*******************************************************************************************
  //  PARAMETROS:
  //  $query: Cadena que contiene el query para la BD
  //  RETORNA:
  //  $result: Retorna un result Resource
  //****      
  public function insert($query){
      $result = $this->query($query);

     return $result;
  }  
  
    public function insertDt($query){
      $dt = $this->queryDt($query);
      return $dt;
  } 

  public function delete($query){
      $result = $this->query($query);

     return $result;
  }
  
  public function deleteDt($query){
      $dt = $this->queryDt($query);
      return $dt;
  } 
  //Esta funcion se llama y no hace debug:trace, solo usarla en casos donde no se deba de actualizar el objeto en memoria por su
  //identitymap
   public function secureUpdate($query){
      $result = $this->query($query);
      //debug::trace("db->update Peligro, si actualizamos un objeto sin indicarle a su manager que se ha actualizado su valor, entonces podremos tener data desactualizada");      
     return $result;
  }
  
  public function update($query){
      $result = $this->query($query);
      debug::trace("db->update Peligro, si actualizamos un objeto sin indicarle a su manager que se ha actualizado su valor, entonces podremos tener data desactualizada");      

     return $result;
  }
  
  public function updateDt($query){
      debug::trace("db->updateDt Peligro, si actualizamos un objeto sin indicarle a su manager que se ha actualizado su valor, entonces podremos tener data desactualizada");
      //debug::log($query,'db->updateDt');
      $dt = $this->queryDt($query);
      //debug::log($dt,'db->updateDt dt');
      return $dt;
  } 
  
  public function updateEntityDt($query,$entityName,$entityId){
      //debug::trace("db->updateDt Peligro, si actualizamos un objeto sin indicarle a su manager que se ha actualizado su valor, entonces podremos tener data desactualizada");
      //debug::log($query,'db->updateDt');
      $dt = $this->queryDt($query);
      $managerName = $entityName._X_MAN_SUFFIX;
      $oManager = $managerName::singleton();
      $objet = $oManager->findById($entityId);
      $objet->setDataDirty();
     // $manager = 
      //debug::log($dt,'db->updateDt dt');
      return $dt;
  }  
  public function updateEntitiesDt($query,$entityName){
      //debug::trace("db->updateDt Peligro, si actualizamos un objeto sin indicarle a su manager que se ha actualizado su valor, entonces podremos tener data desactualizada");
      //debug::log($query,'db->updateDt');
      $dt = $this->queryDt($query);
      $managerName = $entityName._X_MAN_SUFFIX;
      $oManager = $managerName::singleton();
      $oManager->defileAll();
     // $manager = 
      //debug::log($dt,'db->updateDt dt');
      return $dt;
  }    
  
        
  //*******************************************************************************************
  //  query: Para hacer querys a la base de datos, cambia el esquema de tiempo del servidor DB                               
  //*******************************************************************************************
    //  PARAMETROS:
    //  $query: Cadena que contiene el query para la BD
    //  RETORNA:
    //  $result: Retorna un result Resource
    //*******************************************************************************************

    public function query($query, $rollback=false) {// Devuelve un query a la BD
        //debug::log($query,'db->fetch');
        //pg_set_client_encoding($this->getAccess(),'UTF8');
        //$sqlSet0="set datestyle to 'SQL'";
        //$resSet0 = pg_exec($this->getAccess(),$sqlSet0) or die("No se pudo Cambiar la fecha en BD 0 php/conectar.php");
        //Output de DD-MM-AAAA
        //$sqlSet1="set datestyle to 'Euro'";
        //$resSet1 = pg_exec($this->getAccess(),$sqlSet1) or die("No se pudo Cambiar la fecha en BD 1 php/conectar.php");
        if (!$rollback) {
            global $firephp;
            $result = pg_query($this->getAccess(), $query) or $this->error($query);
            //die($firephp->trace('Trace Label:'.pg_last_error())); 		  
        } else {
            $result = pg_query($this->getAccess(), $query);
            if ($result === false) {
                $this->rollback();
            }
        }
        return $result;
    }
  
  //*******************************************************************************************
  //  aquery: Para hacer querys a la base de datos, cambia el esquema de tiempo del servidor DB                               
  //*******************************************************************************************
  //  PARAMETROS:
  //  $query: Cadena que contiene el query para la BD
  //  RETORNA:
  //  $result: Retorna un result Resource
  //*******************************************************************************************
  public function aquery($query,$rollback=false)// Devuelve un query a la BD
  {
    //pg_set_client_encoding($this->getAccess(),'UTF8');
    //$sqlSet0="set datestyle to 'SQL'";
    //$resSet0 = pg_exec($this->getAccess(),$sqlSet0) or die("No se pudo Cambiar la fecha en BD 0 php/conectar.php");
  
  //Output de DD-MM-AAAA
    //$sqlSet1="set datestyle to 'Euro'";
    //$resSet1 = pg_exec($this->getAccess(),$sqlSet1) or die("No se pudo Cambiar la fecha en BD 1 php/conectar.php");
    if(!$rollback){
      global $firephp;
      $dbconn = $this->getAccess();
        if (!pg_connection_busy($dbconn)) {
          pg_send_query($this->getAccess(),$query)  or $this->error();
        }
      //die($firephp->trace('Trace Label:'.pg_last_error()));       
  
    }
    else{
      $result = pg_query($this->getAccess(),$query);
      if($result===false){
        $this->rollback();        
      }

    }   
    return $this->getAccess();
  }	
    //*******************************************************************************************
    //	fetch: Retorna el primer resultado de un query como array   	          					   
    //*******************************************************************************************
    //  PARAMETROS:
    //	$query: Cadena que contiene el query para la BD
    //  RETORNA:
    //	$result: Retorna un array ( el rpimer resultado de un query
    //*******************************************************************************************
    public function fetch($query)// Devuelve un query a la BD
    {
        //debug::log($query,'db->fetch');
        $result = pg_query($this->getAccess(),$query) or die(pg_last_error());
        $rows = pg_num_rows($result);
        if($rows>1){
          debug::error('El numero de filas['.$rows.'] es mayor a 1','db->fetch');
                debug::log($this->result2array($result),'db->fetch');
        }
        elseif($rows == 0){
            $resp = false;    
                
        }
        else{
            $resp = @pg_fetch_assoc($result,0);
        }
        
        return $resp;
    }
    
    //*******************************************************************************************
    //	fetch: Retorna el primer resultado de un query como array   	          					   
    //*******************************************************************************************
    //  PARAMETROS:
    //	$query: Cadena que contiene el query para la BD
    //  RETORNA:
    //	$result: Retorna un array ( el rpimer resultado de un query
    //*******************************************************************************************
    public function fetchDt($query)// Devuelve un query a la BD
    {
        $dt = new datatransfer();
        $dt->success('Fetch Correcto');
        //debug::log($query,'db->fetch');
        $result = pg_query($this->getAccess(),$query);// or die(pg_last_error());
        if($result === false){
            $dt->error(pg_last_error());
        }
        
        else{
            $rows = pg_num_rows($result);
            if($rows>1){
              debug::error('El numero de filas['.$rows.'] es mayor a 1','db->fetch');
              debug::log($this->result2array($result),'db->fetch');
              $dt->error('El numero de filas['.$rows.'] es mayor a 1','db->fetch');
            }
            elseif($rows == 0){
                $dt->success('db->fetchDt No hay ningun resultado');
                $dt->setData(false);
            }
            else{
                $resp = @pg_fetch_assoc($result,0);
                $dt->setData($resp);
            }
        }
        return $dt;
    }
    
    

     //////////////////////////////////////////////////////////////////////////////////
    //METODOS PRINCIPALES
    //////////////////////////////////////////////////////////////////////////////////      

    /**
     * toPersistenceArray Transforma un arreglo de PHP a un arreglo de Postgres
     *
     * @param array $set El arreglo a transformar
     *
     * @return  string La Cadena de Persistencia
     *
     * @since   2011
     * @author  Jeeba
     *
     * @edit    17/01/2013<br />
     *          Jeeba<br />
     *          Creacion Inicial <br/>
     *          #edit1
     */
        public static function toPersistenceArray($set) {
            settype($set, 'array'); // can be called with a scalar or array
            $result = array();
            foreach ($set as $t) {
                if (is_array($t)) {
                    $result[] = db::toPersistenceArray($t);
                } else {
                    $t = str_replace('"', '\\"', $t); // escape double quote
                    if (! is_numeric($t)) // quote only non-numeric values
                        $t = '"' . $t . '"';
                    $result[] = $t;
                }
            }
            return '{' . implode(",", $result) . '}'; // format
        }
        
	//***************************************************************************************
	//	exist: VER SI EXISTE UN DATO EN UNA TABLA      	          					   
	//***************************************************************************************
	//  PARAMETROS:
	//	$table: nombre de la tabla ( ponerle esquema si es necesario
	//	$aCol: array con el nombre de las columnas para el WHERE
	//  $aVal: array con el valor de las columnas para el WHERE
	//  $aType: array con el tipo de las columnas (text,number) para el WHERE                                                             
	//  RETORNA:
	//	TRUE: Si existe el dato buscado
	//	FALSE: Si no existe el dato buscado
	//***************************************************************************************
	public function exist2($table,$aCol=null,$aVal=null,$aType=null)
	{
		if(is_array($table))
		{
			
		}
		else
		{
			$where='';
			if((!empty($aCol))&&(!empty($aVal))&&(!empty($aType)))//Si existe where
			{
				$where=$this->where('col',$aCol,$aVal,$aType);
			}
			//$sql="SELECT COUNT(*) as count from {$table} as col ".$where;
			$sql="SELECT COUNT(*) as count from {$table} as col ".$where;
			$res = $this->fetch($sql) or die("Error en la consulta:".$sql);
			$n=$res['count'];
			if($n==0)
			{
				//echo "FALSE";
				return FALSE;
				//return  $sql;
			}
			else
			{
				//echo "TRUE";
				//return TRUE;
				$sql="SELECT * from {$table} as col ".$where;
				$res = $this->fetch($sql) or die("Error en la consulta:".$sql);
				//return $res;
				return  $sql;
			}
		}
	}
		public function exist($table,$aCol=null,$aVal=null,$aType=null)
	{
		if(is_array($table))
		{
			
		}
		else
		{
			$where='';
			if((!empty($aCol))&&(!empty($aVal))&&(!empty($aType)))//Si existe where
			{
				$where=$this->where('col',$aCol,$aVal,$aType);
			}
			//$sql="SELECT COUNT(*) as count from {$table} as col ".$where;
			$sql="SELECT COUNT(*) as count from {$table} as col ".$where;
			$res = $this->fetch($sql) or die("Error en la consulta:".$sql);
			$n=$res['count'];
			if($n==0)
			{
				//echo "FALSE";
				return FALSE;
				//return  $sql;
			}
			else
			{
				//echo "TRUE";
				//return TRUE;
				$sql="SELECT * from {$table} as col ".$where;
				$res = $this->fetch($sql) or die("Error en la consulta:".$sql);
				return $res;
				//return  $sql;
			}
		}
	}
	//***************************************************************************************
	//	nextId: Obtiene el Siguiente valor de ID de una tabla      	          					   
	//***************************************************************************************
	//  PARAMETROS:
	//	$table: nombre de la tabla ( ponerle esquema si es necesario
	//	$aCol: array con el nombre de las columnas para el WHERE
	//  $aVal: array con el valor de las columnas para el WHERE
	//  $aType: array con el tipo de las columnas (text,number) para el WHERE                                                             
	//  RETORNA:
	//	TRUE: Si existe el dato buscado
	//	FALSE: Si no existe el dato buscado
	//***************************************************************************************
	function nextId($table,$id='id',$aCol=null,$aVal=null,$aType=null)
	{
		$where='';
		if((!empty($aCol))&&(!empty($aVal))&&(!empty($aType)))//Si eixste where
		{
			$where=$this->where($table,$aCol,$aVal,$aType);			
		}
		
		$count=1;
		
		//Generacion del nuevo id
		//$sql="SELECT  COUNT(*)+$count AS nrow from $table".$where;
		$sql="SELECT  MAX($id)+$count AS nrow from $table".$where;
                //debug::log($sql,'db->nextId');
		$resp = $this->fetch($sql);
		$id=$resp['nrow'];
		if($id=="") $id=1;//puede darse el caso que el id ya exista???????Ya no, si no existe se paasa a 1				
		
		return $id;
		//return $sql;//debug
	}
	
	
		
	
/*/////////////////////////////////////////////////////////////////////////////////////
Metodos UTILITARIOS de la Clase
*//////////////////////////////////////////////////////////////////////////////////////

	//***************************************************************************************
	//	where: CREA UN WHERE      	          					   
	//***************************************************************************************
	//  PARAMETROS:
	//	$tableName: Nombre de la tabla ( puede ser un ALIAS)
	//	$aCol: array con el nombre de las columnas para el WHERE
	//  $aVal: array con el valor de las columnas para el WHERE
	//  $aType: array con el tipo de las columnas (text,number) para el WHERE                                                             
	//  RETORNA:
	//	$where: CADENA DE WHERE creado a paritr de los arreglos
	//***************************************************************************************	
	private function where($tableName,$aCol=null,$aVal=null,$aType=null)
	{
		$where = '';
		if((!empty($aCol))&&(!empty($aVal))&&(!empty($aType)))//Si existe where
		{
			$where = ' WHERE ';			
			for($x=0;$x<sizeof($aCol);$x++)
			{
				//Where es texto o numero
				$start='';
				$end='';
				if($aType[$x]=='text')
				{
					$start="'";
					$end="'";
				}
				//Creacion de la sentencia where
				$where = $where.$tableName.'.'.$aCol[$x].'='.$start.$aVal[$x].$end;
				if($x!=sizeof($aCol)-1)
				{
					$where = $where." AND ";
				}
			}//for
		}//if
		return $where;
	}
	
	private	function __construct()//Constructor Privado, las constantes estan definidas en DBInfo.php
	{
		$host=SERVER_PSGR;
		$port=PORT_PSGR;
		$database=DATABASE_PSGR;
		$user=USER_PSGR;
		$password=PASSWORD_PSGR;
		$this->strAccess = "host=".$host." port=".$port." dbname=".$database." user=".$user." password=".$password;
		$this->connectDB();
	}
	private function connectDB()
	{
		$this->dbAccess = pg_connect($this->strAccess)		
   		or die('No se pudo conectar a la base de datos '.pg_last_error());
		pg_set_client_encoding($this->getAccess(),'UTF8');	
		// === identical, si son los mismos valores y el mismo tipo de dato
		$this->strAccess='SECUDATA';
    unset($this->strAccess);//Por seguridad
		}
	
	public function __destruct()//Destructor Publico
	{
		$this->disconnectDB();
	}
	private function disconnectDB()
	{
		// Closing connection
		//Las conexiones las cierra PHP automaticamente, al final del script, por eso no usamos pg_close();
		//pg_close($this->getConexion());
		//pg_close();
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
	
//*-------------------------------------------------------------------------------------------------------*
// Empieza transaccin
//*-------------------------------------------------------------------------------------------------------*
	public function begin() 
	{
	   global $firephp;
     //$firephp->log('Llamando a begin');
	  $lcSql = "BEGIN TRANSACTION";
	  $RS = @pg_exec($this->getAccess(), $lcSql);   
	       
	}  
	//*-------------------------------------------------------------------------------------------------------*
	// Graba modificaciones
	//*-------------------------------------------------------------------------------------------------------*
	public function commit()
	{
	   global $firephp;
     //$firephp->log('Llamando a Commit');
	  $lcSql = "COMMIT";
	   $RS = pg_exec($this->getAccess(), $lcSql);   	  	
	}  
	//*-------------------------------------------------------------------------------------------------------*
	// Ejecuta Rollback
	//*-------------------------------------------------------------------------------------------------------*
	public function rollback()
	{
	   global $firephp;
     //$firephp->trace('Llamando a Rollback');  
	   $lcSql = "ROLLBACK";
	   $RS = pg_exec($this->getAccess(), $lcSql);      
	}
  //*-------------------------------------------------------------------------------------------------------*
  // error: Funcion a ejecutar si tenemos un error
  //*-------------------------------------------------------------------------------------------------------*
  public function error($sql){
    global $firephp;
    //$firephp->log('Se ha llamado a Error Coño');
    //Funcion a llamar sitenemos un error
    $this->rollback();
    $firephp->info($sql);
    die($firephp->trace('db->error:'.pg_last_error() ) );
    
  }
  
  
    //*******************************************************************************************
  //  query: Para hacer querys a la base de datos, cambia el esquema de tiempo del servidor DB                               
  //*******************************************************************************************
    //  PARAMETROS:
    //  $query: Cadena que contiene el query para la BD
    //  RETORNA:
    //  $result: Retorna un result Resource
    //*******************************************************************************************

    public function queryDt($query, $rollback=false) {// Devuelve un query a la BD 
        $dt = new datatransfer();
        $dt->success('db->queryDt: La transaccion fue correcta',$query); 
        if (!$rollback) {
            //global $firephp;
            $result = pg_query($this->getAccess(), $query) or $dt->error('Trace Label:'.pg_last_error());
            //die($firephp->trace('Trace Label:'.pg_last_error())); 		  
        } else {
            $result = pg_query($this->getAccess(), $query) or $dt->error('Trace Label:'.pg_last_error());
            if ($result === false) {
                $this->rollback();
            }
        }
        if(!$result){
            //debug::log($query,'db queryDT preerror');
            $dt->error('db->queryDt error:'.  pg_last_error(),$query);
            //$dt->sql = $query;
        }
        return $dt;
    }
  
  
  
}
/*********************************
**	devuelve la coneccion a BD
*********************************/
function db_conectar(){
	//return CDB_PostGres::Singleton()->getAccess();
	return db::Singleton();
}




?>