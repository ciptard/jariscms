<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Contains functions to manage sqlite connections and data.
 */

namespace JarisCMS\SQLite;

/**
 * Open a database file for use in the data/sqlite directory.
 * 
 * @param string $name The name of the database file.
 *
 * @return resource|bool Database handle or false on failure. 
 */
function Open($name, $directory=null)
{	
    if($directory == null)
    {
        $directory = \JarisCMS\Setting\GetDataDirectory() . "sqlite/";
    }
    
    $db = null;
	$error = "";
    $opened = false;
    $db_path = $directory . $name;
    
    if(class_exists("SQLite3"))
    {
        $sqlite3_class_error = "";
        ob_start();
        	$db = new \SQLite3($db_path);
        	$result = $db->query("select * from sqlite_master");
        	$sqlite3_class_error = ob_get_contents();
        ob_end_clean();

        if($sqlite3_class_error == "")
        {
            $opened = true;
        }
    }
    
    if(class_exists("PDO") && !$opened)
    {
        try
        {
            $db = new \PDO("sqlite:$db_path");
            
            //Check if database format file is version 3
            $result = $db->query("select * from sqlite_master");
            
            if($result)
            {
                $opened = true;
            }
        }
        catch(\PDOException $exception)
        {
            $opened = false;
        }
    }
    
    if(!$opened)
    {
	   $db = sqlite_open($db_path, 0600, $error);
    }
    
	
	if($error != "")
	{
		\JarisCMS\System\AddMessage($error, "error");
	}
    else
    {
        //Inject text search functions to sqlite (UDF)
        $udf_text_search_functions = new Search($db);
        
        //Hook useful to inject user defined functions to data bases
        \JarisCMS\Module\Hook("SQLite", "Open", $name, $directory, $db);
    }
    
    return $db; 
}

/**
 * Attaches another database to an already opened database to be
 * able to make table joins from different databases
 * 
 * @param string $db_name Name of the database to attach.
 * @param resource $db Currently opened database object.
 * @param string $directory Optional path where the database to attach resides.
 */
function Attach($db_name, &$db, $directory=null)
{
    if($directory == null)
    {
        $directory = \JarisCMS\Setting\GetDataDirectory() . "sqlite/";
    }
    
    Query("attach database '{$directory}{$db_name}' as $db_name", $db);
}

/**
 * Close sqlite database connection
 * 
 * @param resource $db Object to opened database.
 */
function Close(&$db)
{
    if(gettype($db) == "object" && class_exists("SQLite3"))
    {
        $db->close();
    }
    elseif(gettype($db) == "object")
    {
        unset($db);
    } 
    else
    {
        sqlite_close($db);
    }
}

/**
 * Uninitialize sqlite database result. This is a dummy function to (seems not to work)
 * remember that sometimes not unsetting a database result can result
 * (:D) in database lock ups.
 * 
 * @param resource $result The result of a database query.
 */
function CloseResult(&$result)
{
	unset($result);
}

/**
 * Turns synchrounous off for more speed at writing
 * 
 * @param resource $db Object to opened database.
 */
function Turbo(&$db)
{
    Query("PRAGMA cache_size=10240", $db);
    Query("PRAGMA temp_store=MEMORY", $db); 
    Query("PRAGMA synchronous=OFF", $db);
	Query("PRAGMA journal_mode=OFF", $db);
}

/**
 * Function to escape quotes ' to doueble quotes ''
 *
 * @param array $fields Reference to the array to escape its values.
 */
function EscapeArray(&$fields)
{
    foreach($fields as $name=>$value)
    {
        $fields[$name] = str_replace("'", "''", $value);
    }
}

/**
 * Inserts an array to a table in a given database.
 *
 * @param string $table_name Name of the table.
 * @param array $data In the format $data["colum_name"] = "value"
 * @param resource $db Reference to the db that has the table where you want to insert data.
 * 
 * @return bool true on success or false on fail.
 */
function InsertArrayToTable($table_name, $data, &$db)
{  
    $columns = "";
    $values = "";
    foreach($data as $column_name=>$value)
    {
        $columns .= "$column_name,";
        $values .= "'" . str_replace("'", "''", $value) . "',";
    }
    
    $columns = trim($columns, ",");
    $values = trim($values, ",");
    
    $insert = "insert into $table_name ($columns) values($values)";
    
    if(!Query($insert, $db))
    {
        return false;
    }
    
    return true;
}

/**
 * Generic function to delete records from a database
 *
 * @param $database the file name of the database
 * @param $table the table where operating delete
 * @param $clause a condion clause like where
 * @param $directory optinal path to the database file
 * 
 * @return bool true on success or false on fail.
 */
function DeleteFromTable($database, $table, $clause, $directory=null)
{		
	if(DBExists($database, $directory))
	{
            $db = Open($database, $directory);
            Query("delete from $table $clause", $db);
            Close($db);
        
            return true;
	}
    
    return false;
}

/**
 * To retrieve a list of data from sqlite database to generate a browser
 *
 * @param string $database File name of the database.
 * @param stromg $table the Name were we are retrieving a list.
 * @param integer $page Current page count of pages list is being browser.
 * @param integer $limit Amount of data per page to display.
 * @param string $clause Optional clause for the query like where, order by etc.
 * @param string $fields Optional fields seperated by comma or functions like count(field) as result_name.
 * @param string $directory Optional path to database file.
 * 
 * @return array List of result data not longer than $limit
 */
function GetDataList($database, $table, $page=0, $limit=30, $clause="", $fields="*", $directory=null)
{
	// To protect against sql injections be sure $page is a int
	if(!is_numeric($page))
	{
		$page = 0;		
	}
	else 
	{
		$page = intval($page);
	}
	
	$db = null;
	$page *=  $limit;
	$data = array();
		
	if(DBExists($database, $directory))
	{
		$db = Open($database, $directory);
		$result = Query("select $fields from $table $clause limit $page, $limit", $db);
	}
	else
	{
		return $data;
	}
	
	$fields = array();
	if($fields = FetchArray($result))
	{
		$data[] = $fields;
		
		while($fields = FetchArray($result))
		{
			$data[] = $fields;
		}
		
		Close($db);
		return $data;
	}
	else
	{
		Close($db);
		return $data;
	}
}
 
/**
 * Same as normal sqlite_query but with the SQLITE_ASSOC passed on and error reporting.
 * 
 * @param string $query SQL statement to execute.
 * @param resource $db Database handle.
 *
 * @return resource|bool Result handle or false on failure. 
 */
function Query($query, &$db)
{
	$error = "";
    
    if(gettype($db) == "object" && class_exists("SQLite3"))
    {
        ob_start();
        $result = $db->query($query);
        $error = ob_get_contents();
        ob_end_clean();
    }
    elseif(gettype($db) == "object")
    {
        try
        {
            $result = $db->prepare($query);
            $result->execute();
        }
        catch(\PDOException $exception)
        {
            $error = $exception->getMessage();
        }
    } 
    else
    {
        $result = sqlite_unbuffered_query($query, $db, SQLITE_ASSOC ,$error);
    }
	
	if($error != "")
	{
		\JarisCMS\System\AddMessage($error, "error");
	}
	
	return $result;
}

/**
 * Same as normal sqlite_fetch_array but with the SQLITE_ASSOC passed.
 * 
 * @param resource $result An sqlite resource result of a statement.
 *
 * @return array|bool Data results or false for no data. 
 */
function FetchArray(&$result)
{
    if(gettype($result) == "object" && class_exists("SQLite3"))
    {
        return $result->fetchArray(SQLITE3_ASSOC);
    }
    elseif(gettype($result) == "object")
    {
        return $result->fetch(\PDO::FETCH_ASSOC);
    } 
    else
    {
        return sqlite_fetch_array($result, SQLITE_ASSOC);
    }
    
    return false;
}
 
/**
 * Checks if the given sqlite database exists in the data/sqlite directory
 * 
 * @param string $name The name of the database file.
 * @param string $directory Optional path to database file.
 *
 * @return True if exist false if not. 
 */
function DBExists($name, $directory=null)
{
    if($directory == null)
    {
        $directory = \JarisCMS\Setting\GetDataDirectory() . "sqlite/";
    }
    
    return file_exists($directory . $name);
}

/**
 * List all the databases available on the system.
 * 
 * @param string $directory Optional path for database files.
 *
 * @return  array All the databases available on the system.
 */ 
function ListDB($directory=null)
{
    if($directory == null)
    {
        $directory = \JarisCMS\Setting\GetDataDirectory() . "sqlite/";
    }
    
    $dh = opendir($directory);

    $databases = array();

    while(($file = readdir($dh)) !== false) 
    {
            if(is_file($directory . $file) && !preg_match("/(.*)(\.sql)/", $file))
            {
                    $databases[] = $file;
            }
    }
    
    closedir($dh);
    
    return $databases;
}

/**
 * Counts a given colum
 *
 * @param string $database Name of database where table resides
 * @param string $table Name of table where column resides.
 * @param string $column The column to count.
 * @param string $where Optional parameter to indicate a where clause, example: "where user='test'"
 * @param string $directory Optional path to database file.
 *  
 * @return integer count
 */
function CountColumn($database, $table, $column, $where="", $directory=null)
{
    if(DBExists($database, $directory))
    {
        $db = Open($database, $directory);
        $result = Query("select count($column) as 'total_count' from $table $where", $db);

        $count = FetchArray($result);

        Close($db);

        return $count["total_count"];
    }
    else
    {
        return 0;
    } 
}

/**
 *

/**
 * Creates an sql file backup of all database tables.
 *
 * @param string $name The name of the database to backup.
 */
function Backup($name)
{
	if(DBExists($name))
	{
		$backup_path = \JarisCMS\Setting\GetDataDirectory() . "sqlite/" . $name . ".sql";
		
		$db = Open($name);
		
		$result = Query("select * from sqlite_master where type = 'table' order by name asc", $db);
		
		$tables = array();
		
		while($row = FetchArray($result))
		{
			$tables[] = $row;
		}
		
		$backup_file = fopen($backup_path, "w");
		
		foreach($tables as $values)
		{
			fwrite($backup_file, "/*CREATE " . strtoupper($values["name"]) . " TABLE*/" . "\n");
			fwrite($backup_file, $values["sql"] . ";\n\n");
			
			fwrite($backup_file, "/*INSERT ALL " . strtoupper($values["name"]) . " TABLE DATA*/" . "\n");
			
			$result = Query("select * from " . $values["name"], $db);
			
			while($row = FetchArray($result))
			{
				$column_name_string = "";
				$column_name_array = array();
				
				$column_value_string = "";
				$column_value_array = array();
				
				foreach($row as $colum_name=>$colum_value)
				{
					$column_name_array[] = $colum_name;
					$column_value_array[] = "'" . str_replace(array("'", "\r", "\n"), array("''", "\\r", "\\n"), $colum_value) . "'";
				}
				
				$column_name_string = "(" . implode(",", $column_name_array) . ")";
				$column_value_string = "(" . implode(",", $column_value_array) . ")";
				
				$insert = "insert into " . $values["name"] . " " . $column_name_string . " values " . $column_value_string;
				
				fwrite($backup_file, $insert . ";\n");
			}
			
			fwrite($backup_file, "\n");		
		}
		
		fclose($backup_file);
		Close($db);		
	}
}

/**
 * Restores or creates a database from a .sql file pointer.
 *
 * @param string $name The name of the database.
 * @param resource $fp A pointer to a file.
 */
function Restore($name, &$fp)
{
	unlink(\JarisCMS\Setting\GetDataDirectory() . "sqlite/$name");
	
	$db = Open($name);
	
	while(!feof($fp))
	{
		$sql_statement = fgets($fp);
		
		//Ignore empty lines and comments
		if($sql_statement != "" && !preg_match("/^(\/\*)(.*)(\*\/)$/", $sql_statement))
		{
            $sql_statement = str_replace(array("\\r", "\\n"), array("\r", "\n"), $sql_statement);
            
			Query($sql_statement, $db);
		}
	}
}
 
?>