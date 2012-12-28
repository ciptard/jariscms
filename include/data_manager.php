<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Functions to manage and parse the php files database system.
 */

namespace JarisCMS\PHPDB;

/**
 * Parses the file database system.
 *
 * @param string $file The path of the file to parse.
 *
 * @return array All the rows with a subarray of it fields
 *         in the format row[id] = array(field_name=>value) or false if error.
 */
function Parse($file)
{    
    //In case file is been write wait to not get empty content
    WaitForUnLock($file);
    
    if(!file_exists($file))
    {
        return false;
    }
    
    //If data is stored serialized get that instead since it is faster than reparsing.
    //This is specially useful for less powered embedded devices
    if(file_exists(\JarisCMS\Setting\GetDataDirectory() . "data_cache/" . \JarisCMS\URI\FromText($file)))
    {
        return unserialize(file_get_contents(\JarisCMS\Setting\GetDataDirectory() . "data_cache/" . \JarisCMS\URI\FromText($file)));
    }

    $arrFile = file($file);

    $row = array();

    $insideRow = false;
    $insideField = false;
    $currentRow = "";
    $currentField = "";

    for($i=0; $i<count($arrFile); ++$i)
    {
        if($insideField)
        {
            if(substr(trim($arrFile[$i]),0,6) == "field;")
            {
                $insideField = false;

                $row[$currentRow][$currentField] = rtrim($row[$currentRow][$currentField]);
            }
            else
            {
                $field =  $arrFile[$i];
                $field = ltrim($field, "\t");

                if($row[$currentRow][$currentField] != "")
                {
                    $row[$currentRow][$currentField] .= $field . "";
                }
                else
                {
                    $field = trim($field, "\t");
                    $row[$currentRow][$currentField] .= $field;
                }
            }
        }
        else if($insideRow)
        {
            if(substr(trim($arrFile[$i]),0,6) == "field:")
            {
                $arrField = explode(":", $arrFile[$i]);
                $currentField = trim($arrField[1]);
                $insideField = true;

                $row[$currentRow][$currentField] = "";
            }
            else if(substr(trim($arrFile[$i]),0,4) == "row;")
            {
                $insideRow = false;
            }
        }
        else if(!$insideRow)
        {
            if(substr(trim($arrFile[$i]),0,4) == "row:")
            {
                $arrRow = explode(":", $arrFile[$i]);
                $currentRow = trim($arrRow[1]);
                $insideRow = true;
            }
        }
    }

    unset($arrFile);
    
    //Store retrieved data in serialized form for faster retreival next time
    //This is specially useful for less powered embedded devices
    if(is_dir(\JarisCMS\Setting\GetDataDirectory() . "data_cache"))
        file_put_contents(\JarisCMS\Setting\GetDataDirectory() . "data_cache/" . \JarisCMS\URI\FromText($file), serialize($row));

    return $row;
}

/**
 * Writes a php database file with the correct format.
 *
 * @param array $data With the format array[row_number] = array("field_name"=>"field_value")
 *                    used to populate the content of the file.
 * @param string $file The path of the file to write on.
 *
 * @return bool false if failed to write data otherwise true.
 */
function Write($data, $file)
{
    //Wait if file is been modified
    WaitForUnLock($file);
    
    //Check if a file could no be lock and keep trying until locked
    while(!Lock($file))
    {
        continue;
    }
    
    //For security we place this at the top of the file to make it unreadable by
    //external users
    $content = "<?php exit; ?>\n\n\n";

    foreach($data as $row => $fields)
    {
        $content .= "row: $row\n\n";

        foreach($fields as $name => $value)
        {
            $content .= "\tfield: $name\n";
            $content .= "\t\t" . trim($value);
            $content .= "\n\tfield;\n\n";
        }

        $content .= "row;\n\n\n";
    }

    if(!file_put_contents($file, $content))
    {
        //Unlock file
        Unlock($file);
        
        return false;
    }
    
    //Store data in serialized form for faster reads by Parse() function
    //This is specially useful for less powered embedded devices
    if(is_dir(\JarisCMS\Setting\GetDataDirectory() . "data_cache"))
        file_put_contents(\JarisCMS\Setting\GetDataDirectory() . "data_cache/" . \JarisCMS\URI\FromText($file), serialize($data));
    
    //Unlock file
    Unlock($file);

    return true;
}

/**
 * Gets a row and all its fields from a data file.
 *
 * @param integer $position The number or id of the row to retrieve.
 * @param string $file Path to the data file.
 *
 * @return Array in the format fields["name"] = "value"
 */
function GetData($position, $file)
{
    //In case file is been write wait to not get empty content
    WaitForUnLock($file);
    
    $actual_data = array();
    if(file_exists($file))
    {
        $actual_data = Parse($file);
    }

    return $actual_data[$position];
}

/**
 * Appends a new row to a database file and creates the file if doesnt exist.
 *
 * @param array $fields Fields in the format fields["name"] = "value"
 * @param string $file The path to the data file.
 *
 * @return bool False if failed to add data otherwise true.
 */
function Add($fields, $file)
{    
    $actual_data = array();
    if(file_exists($file))
    {
        $actual_data = Parse($file);
    }

    $actual_data[] = $fields;

    if(!Write($actual_data, $file))
    {
        return false;
    }

    return true;
}

/**
 * Delete a row from a database file and all its fields.
 *
 * @param integer $position The position or id of the row to delete.
 * @param string $file The path to the file.
 *
 * @return bool False if failed to delete data otherwise true.
 */
function Delete($position, $file)
{
    $actual_data = Parse($file);

    unset($actual_data[$position]);

    if(!Write($actual_data, $file))
    {
        return false;
    }

    return true;
}

/**
 * Deletes a row from a database file when a field matches a specific value.
 *
 * @param string $field_name Name of the field to match.
 * @param string $value Value of the field.
 * @param string $file The path to the file.
 *
 * @return bool False if failed to delete data otherwise true.
 */
function DeleteByField($field_name, $value, $file)
{
    $data = Parse($file);
    
    foreach($data as $position=>$fields)
    {
        if($fields[$field_name] == $value)
        {
            if(!Delete($position, $file))
            {
                return false;
            } 
        }
    }
    
    return true;
}

/**
 * Edits all the fields from a row on a database file.
 *
 * @param integer $position The position or id of the row to edit.
 * @param array $new_data Fields in the format fields["name"] = "value"
 *                        with the new data to be written to the row.
 * @param string $file The path to the database file.
 *
 * @return bool False if failed to edit data otherwise true.
 */
function Edit($position, $new_data, $file)
{
    $actual_data = Parse($file);

    $actual_data[$position] = $new_data;

    if(!Write($actual_data, $file))
    {
        return false;
    }

    return true;
}

/**
 * Locks a file for write protection.
 *
 * @param string $file The file path to lock.
 *
 * @return bool true on success false on fail.
 */
function Lock($file)
{
    //File to block file from modifications.
    $file_lock = $file . ".lock";
    
    //Create lock file
    if(file_exists($file_lock))
    {
        return false;
    }
    else
    {
        file_put_contents($file_lock, "");
    }
    
    return true;
}

/**
 * Unlocks a write protected file.
 *
 * @param string $file The path of the file to unlock.
 */
function Unlock($file)
{
    //File to block file from modifications.
    $file_lock = $file . ".lock";
    
    //Delete lock file
    unlink($file_lock);
}

/**
 * Checks if a file is been modified and waits until is modified.
 * 
 * @param string $file the file to check.
 */
function WaitForUnLock($file)
{
    //File to block file from modifications until is modified here first.
    $file_lock = $file . ".lock";
    
    //Check if $file is not been modified already.
    if(file_exists($file_lock))
    {
        //Wait until the file is written by the other process
        while(file_exists($file_lock))
        {
            continue;
        }
    }
}

/**
 * Sorts an array returned by the Parse function using bubble sort.
 *
 * @param array $data_array The array to sort in the format returned by Parse().
 * @param string $field_name The field we are using to sort the array by.
 * @param mixed $sort_method The type of sorting, default is ascending. 
 *
 * @return array The same array but sorted by the given field name.
 */
function Sort($data_array, $field_name, $sort_method = SORT_ASC)
{
    $sorted_array = array();
    
    if(is_array($data_array))
    {
        $field_to_sort_by = array();
        $new_id_position = array();
        
        foreach($data_array as $key=>$fields)
        {
            $field_to_sort_by[$key] = $fields[$field_name];
            $new_id_position[$key] = $key;
        }
        
        array_multisort($field_to_sort_by, $sort_method, $new_id_position, $sort_method);
        
        foreach($new_id_position as $id)
        {
            $sorted_array[$id] = $data_array[$id];
        }
    }
    
    return $sorted_array;
}
?>
