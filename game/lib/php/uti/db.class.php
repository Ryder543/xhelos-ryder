<?php
/*---------------------------------------------------------------------------------------------------
db.class.php - Class for MySQL database management
Author: Adapted from original by Jose Carlos Tamayo
Mejoras: Michi

PROPIEDADES:
------------------------------------------------------------------------------------------------------
-SINGLETON: Only one instance is created
----------------------------------------------------------------------------------------------------*/
require_once(dirname(__FILE__) . '/security.php'); // Security Notice

class db
{
    //////////////////////////////////////////////////////////////////////////////////////
    // Properties
    //////////////////////////////////////////////////////////////////////////////////////
    private static $instance; // Unique instance of the database class
    private $dbAccess; // The actual database connection

    ///////////////////////////////////////////////////////////////////////////////////////
    // Methods
    ///////////////////////////////////////////////////////////////////////////////////////

    public function getAccess() // Get the database access
    {
        return $this->dbAccess;
    }

    //*******************************************************************************************
    // debug: Prints the query and the fetched data
    //*******************************************************************************************
    //  PARAMETERS:
    //  $query: The query string to analyze
    //  RETURNS:
    //  [HTML] Prints the query and the returned data (in case of a select)
    //*******************************************************************************************
    public function debug($query) // Print a query in table form
    {
        echo "<div class='debug'>";
        echo "<span class='debug_title'>QUERY:</span><span class='debug'>" . $query . "</span><br>";

        $result = $this->getConnection()->query($query);

        if (!$result) {
            die('Failed to execute query:<br>' . $query . '<br>Error: ' . $this->getConnection()->error);
        }

        $num = $result->num_rows;
        for ($count = 0; $count < $num; $count++) {
            $objeto = $result->fetch_object();
            $vars = get_object_vars($objeto);
            echo "<br>OBJECT Nro:{$count}";
            foreach ($vars as $key => $var) {
                echo "<br>{$key}: {$var}";
            }
            echo "<br>END OF OBJECT Nro:{$count}<br>";
        }

        echo "</div>";
    }

    //*******************************************************************************************
    // status: Prints the status of the connection
    //*******************************************************************************************
    public function status() // Print the status of the database connection
    {
        echo "<br>Website Status\n";
        echo "<br>Connection Status:\n";

        if ($this->getConnection()->ping()) {
            echo 'OK';
        } else {
            echo '<br>BAD';
        }
    }

    //*******************************************************************************************
    // vectorize: Executes a query and returns the result as an array
    //*******************************************************************************************
    public function vectorize($query, $autoindex = true)
    {
        return $this->sql2array($query, $autoindex);
    }

    // Converts a query result into an array, optionally indexing by a specified field
    public function sql2array($query, $autoindex = false)
    {
        $result = $this->getConnection()->query($query) or die('Error in query: ' . $this->getConnection()->error);
        $resultArray = [];

        while ($row = $result->fetch_assoc()) {
            if ($autoindex && isset($row['id'])) {
                $resultArray[$row['id']] = $row;
            } else {
                $resultArray[] = $row;
            }
        }

        return $resultArray;
    }


//*******************************************************************************************
//  sql2group: Executes a query and groups the result into an array by a specified index
//*******************************************************************************************
//  PARAMETERS:
//  $query: The SQL query to execute
//  $autoindex: If true or a string, groups by the specified field
//  RETURNS:
//  $result: An associative array grouped by the specified index
//*******************************************************************************************
public function sql2group($query, $autoindex = false)
{
    $result = [];
    $result_raw = $this->getConnection()->query($query);

    if (!$result_raw) {
        die('Error executing query: ' . $this->getConnection()->error);
    }

    while ($row = $result_raw->fetch_assoc()) {
        if (!empty($row['id']) && $autoindex) {
            if (is_string($autoindex) && isset($row[$autoindex])) {
                $result[$row[$autoindex]][$row['id']] = $row;
            } else {
                $result[$row['id']] = $row;
            }
        } else {
            $result[] = $row;
        }
    }

    return $result;
}

    
public function result2array($resultRaw)
{
    $result = array();
    while ($result_fila = mysqli_fetch_assoc($resultRaw)) {
        $result[] = $result_fila;
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
    debug::trace("1 db->update Peligro, si actualizamos un objeto sin indicarle a su manager que se ha actualizado su valor, entonces podremos tener data desactualizada");      

   return $result;
}
public function updateDt($query){
    debug::trace("2 db->updateDt Peligro, si actualizamos un objeto sin indicarle a su manager que se ha actualizado su valor, entonces podremos tener data desactualizada");
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
    // Executes a query and returns the result
    public function query($query, $rollback = false)
    {
        $result = $this->getConnection()->query($query);

        if ($rollback && $result === false) {
            $this->rollback();
        }

        if (!$result) {
            die('Error in query: ' . $this->getConnection()->error);
        }

        return $result;
    }
    //*******************************************************************************************
    //  aquery: Executes a query, with optional rollback on failure
    //*******************************************************************************************
    //  PARAMETERS:
    //  $query: The SQL query to execute
    //  $rollback: If true, will attempt a rollback if the query fails
    //  RETURNS:
    //  Result of the query or rolls back the transaction
    //*******************************************************************************************
    public function aquery($query, $rollback = false)
    {
        $connection = $this->getConnection();
        
        if ($rollback) {
            $connection->begin_transaction();
        }

        $result = $connection->query($query);

        if ($result === false && $rollback) {
            $connection->rollback();
            die('Error executing query with rollback: ' . $connection->error);
        } elseif ($result === false) {
            die('Error executing query: ' . $connection->error);
        }

        if ($rollback) {
            $connection->commit();
        }

        return $result;
    }



    // Fetches a single result as an associative array
    public function fetch($query)
    {
        $result = $this->getConnection()->query($query);

        if (!$result) {
            die('Error: ' . $this->getConnection()->error);
        }

        return $result->fetch_assoc();
    }

//*******************************************************************************************
    //  fetchDt: Executes a query and returns a datatransfer object containing the result
    //*******************************************************************************************
    //  PARAMETERS:
    //  $query: The SQL query to execute
    //  RETURNS:
    //  A datatransfer object containing the result or an error message
    //*******************************************************************************************
    public function fetchDt($query)
    {
        // Create a new datatransfer object
        $dt = new datatransfer();
        
        // Execute the query
        $result = $this->getConnection()->query($query);

        // Check if query execution was successful
        if ($result === false) {
            $dt->error('Error: ' . $this->getConnection()->error);
        } else {
            // Check number of rows returned
            
            $rows = $result->num_rows;
            
            if ($rows > 1) {
                $dt->error('db->fetchDt: El número de filas [' . $rows . '] es mayor a 1');
            } elseif ($rows == 0) {
                $dt->success('db->fetchDt: No hay ningún resultado');
                $dt->setData(false);
            } else {
                // Fetch the associative array of the first row
                
                $resp = $result->fetch_assoc();
                //debug::log($resp,"dbclass rows");
                $dt->setData($resp);
                $dt->success('Fetch successful');
                //debug::log($dt,"db->fetchDt3");
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
        settype($set, 'array'); // Ensure $set is an array
        $result = array();
        foreach ($set as $t) {
            if (is_array($t)) {
                $result[] = db::toPersistenceArray($t);
            } else {
                // Escape double quotes and quote non-numeric values
                $t = str_replace('"', '\\"', $t);
                if (!is_numeric($t)) {
                    $t = '"' . $t . '"';
                }
                $result[] = $t;
            }
        }
        // Join the array elements into a string and return
        return '{' . implode(",", $result) . '}';
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
    public function exist($table, $aCol = null, $aVal = null, $aType = null) {
        // If $table is an array, you may want to handle that scenario differently
        if (is_array($table)) {
            // Handle array case if needed
        } else {
            $where = '';
            // Build the WHERE clause if columns, values, and types are provided
            if (!empty($aCol) && !empty($aVal) && !empty($aType)) {
                $where = $this->where('col', $aCol, $aVal, $aType);
            }
    
            // MySQL query to count records based on conditions
            $sql = "SELECT COUNT(*) as count FROM {$table} as col {$where}";
            $res = $this->fetch($sql) or die("Error in query: " . $sql);
            $n = $res['count'];
            if ($n == 0) {
                return false;
            } else {
                // If a record exists, return it or run a select query to retrieve it
                $sql = "SELECT * FROM {$table} as col {$where}";
                $res = $this->fetch($sql) or die("Error in query: " . $sql);
                return $res;
            }
        }
    }

    public function exist2($table, $aCol = null, $aVal = null, $aType = null) {
        if (is_array($table)) {
            // Handle array case if needed
        } else {
            $where = '';
            if (!empty($aCol) && !empty($aVal) && !empty($aType)) {
                $where = $this->where('col', $aCol, $aVal, $aType);
            }
    
            // Count query
            $sql = "SELECT COUNT(*) as count FROM {$table} as col {$where}";
            $res = $this->fetch($sql) or die("Error in query: " . $sql);
            $n = $res['count'];
            if ($n == 0) {
                return false;
            } else {
                // Return the SQL for debugging or execution
                $sql = "SELECT * FROM {$table} as col {$where}";
                $res = $this->fetch($sql) or die("Error in query: " . $sql);
                return $sql;
            }
        }
    }
    
    

//*******************************************************************************************
//  nextId: Gets the next ID value for a specified table
//*******************************************************************************************
//  PARAMETERS:
//  $table: The name of the table
//  $id: The ID column (default 'id')
//  $aCol: Optional columns for WHERE clause
//  $aVal: Values for the WHERE clause
//  $aType: Types of columns (text, number)
//  RETURNS:
//  The next available ID value
//*******************************************************************************************
public function nextId($table, $id = 'id', $aCol = null, $aVal = null, $aType = null)
{
    $where = '';
    if (!empty($aCol) && !empty($aVal) && !empty($aType)) {
        $where = $this->where($table, $aCol, $aVal, $aType);
    }

    $sql = "SELECT MAX($id) + 1 AS nrow FROM $table" . $where;
    $resp = $this->fetch($sql);
    $id = $resp['nrow'];
    return $id ? $id : 1; // Default to 1 if no rows found
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
    private function where($tableName, $aCol = null, $aVal = null, $aType = null)
    {
        $where = '';
        // Check if columns, values, and types are provided
        if (!empty($aCol) && !empty($aVal) && !empty($aType)) {
            $where = ' WHERE ';
            // Loop through the columns to build the condition
            for ($x = 0; $x < sizeof($aCol); $x++) {
                // Determine if the value is text or number
                $start = '';
                $end = '';
                if ($aType[$x] == 'text') {
                    $start = "'";
                    $end = "'";
                    // Escape the value to prevent SQL injection
                    $aVal[$x] = mysqli_real_escape_string($this->getAccess(), $aVal[$x]);
                }
    
                // Build the WHERE clause
                $where .= $tableName . '.' . $aCol[$x] . '=' . $start . $aVal[$x] . $end;
                // Add AND if not the last condition
                if ($x != sizeof($aCol) - 1) {
                    $where .= " AND ";
                }
            }
        }
        return $where;
    }
    


    // Constructor (private for singleton pattern)
    private function __construct()
    {
        $xhelos_db_host = SERVER_MYSQL;
        $xhelos_db_user = USER_MYSQL;
        $xhelos_db_password = PASSWORD_MYSQL;
        $xhelos_db_database = DATABASE_MYSQL;

        $this->dbAccess = new mysqli($xhelos_db_host, $xhelos_db_user, $xhelos_db_password, $xhelos_db_database);

        if ($this->dbAccess->connect_error) {
            die('Connection failed: ' . $this->dbAccess->connect_error);
        }

        $this->dbAccess->set_charset("utf8");
    }

    private function connectDB()
    {
        // Establish a connection to the MySQL database
        $this->dbAccess = mysqli_connect($this->strAccess['host'], $this->strAccess['user'], $this->strAccess['password'], $this->strAccess['database'])
            or die('No se pudo conectar a la base de datos ' . mysqli_connect_error());
        
        // Set the character encoding to UTF-8 for the connection
        mysqli_set_charset($this->dbAccess, 'utf8');
    
        // Reset access credentials for security
        $this->strAccess = 'SECUDATA';
        unset($this->strAccess);
    }

    // Destructor to close the connection
    public function __destruct()
    {
        $this->dbAccess->close();
    }
    
    	private function disconnectDB()
	{
		// Closing connection
		//Las conexiones las cierra PHP automaticamente, al final del script, por eso no usamos pg_close();
		//pg_close($this->getConexion());
		//pg_close();
	}


    // Singleton pattern to ensure only one instance of the database connection is created
    public static function singleton()
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }
        return self::$instance;
    }

    // Prevent cloning of the instance
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }


//*-------------------------------------------------------------------------------------------------------*
// Empieza transaccin
//*-------------------------------------------------------------------------------------------------------*
	
    public function begin() 
{
    global $firephp;
    //$firephp->log('Llamando a begin');
    $lcSql = "START TRANSACTION";
    $RS = @mysqli_query($this->getAccess(), $lcSql);
    if (!$RS) {
        die('Error starting transaction: ' . mysqli_error($this->getAccess()));
    }
}

public function commit()
{
    global $firephp;
    //$firephp->log('Llamando a Commit');
    $lcSql = "COMMIT";
    $RS = @mysqli_query($this->getAccess(), $lcSql);
    if (!$RS) {
        die('Error committing transaction: ' . mysqli_error($this->getAccess()));
    }
}

public function rollback()
{
    global $firephp;
    //$firephp->trace('Llamando a Rollback');
    $lcSql = "ROLLBACK";
    $RS = @mysqli_query($this->getAccess(), $lcSql);
    if (!$RS) {
        die('Error rolling back transaction: ' . mysqli_error($this->getAccess()));
    }
}


//*******************************************************************************************
//  error: Handles and logs errors
//*******************************************************************************************
//  PARAMETERS:
//  $sql: The SQL query that caused the error
//  RETURNS:
//  Dies with an error message and rollback
//*******************************************************************************************
public function error($sql)
{
    $this->rollback(); // Rollback any transaction in progress
    global $firephp;
    $firephp->info($sql,"db->error");
    $firephp->trace('db->error:');
    die('[Xhelos] Error executing query: ' . $sql . ' - ' . $this->getConnection()->error);
}



/*
    // Starts a transaction
    public function begin()
    {
        $this->getConnection()->begin_transaction();
    }

    // Commits the current transaction
    public function commit()
    {
        $this->getConnection()->commit();
    }

    // Rolls back the current transaction
    public function rollback()
    {
        $this->getConnection()->rollback();
    }*/



//*******************************************************************************************
//  queryDt: Executes a query and returns a datatransfer object
//*******************************************************************************************
//  PARAMETERS:
//  $query: The SQL query to execute
//  $rollback: If true, will attempt a rollback if the query fails
//  RETURNS:
//  $dt: A datatransfer object containing the query result or error
//*******************************************************************************************
public function queryDt($query, $rollback = false)
{
    $dt = new datatransfer();
    $dt->success('Query executed successfully', $query);

    if ($rollback) {
        $this->getConnection()->begin_transaction();
    }

    $result = $this->getConnection()->query($query);

    if ($result === false) {
        $dt->error('Error: ' . $this->getConnection()->error);
        if ($rollback) {
            $this->getConnection()->rollback();
        }
    } else {
        $dt->setData($result);
        if ($rollback) {
            $this->getConnection()->commit();
        }
    }

    return $dt;
}



    // Returns the current connection instance
    public function getConnection()
    {
        return $this->dbAccess;
    }
}

// Function to obtain the singleton instance
function db_conectar()
{
    return db::singleton();
}
?>
