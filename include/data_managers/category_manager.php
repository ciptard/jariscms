<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file The functions to manage categories.
 */

namespace JarisCMS\Category;

/**
 * Creates a category.
 *
 * @param string $machine_name The machine name of the category to create 
 * @param array $data Data to store to the category in the format:
 *             data = array("name"=>"value", "description"=>value)
 *
 * @return bool True on success or false on fail.
*/
function Create($machine_name, $data)
{
    $path = GeneratePath($machine_name);

    //First we make category directory
    if(!file_exists($path))
    {
        \JarisCMS\FileSystem\MakeDir($path, 0755, true);
    }
    
    $category_data_path = $path . "/data.php";
    
    //Check if type already exist.
    if(file_exists($category_data_path))
    {
        return \JarisCMS\System\GetErrorMessage("category_exist");
    }
    
    //Call create_category hook before creating the category
    \JarisCMS\Module\Hook("Category", "Create", $machine_name, $data);

    //Add category data
    if(\JarisCMS\PHPDB\Add($data, $category_data_path))
    {
        AddMenuBlock($machine_name, $data);
        
        return "true";
    }
    else
    {
        return \JarisCMS\System\GetErrorMessage("write_error_data");
    }
}

/**
 * Deletes a category data directory.
 *
 * @param string $machine_name The machine name of the category.
 *
 * @return bool True on success or false on fail.
 */
function Delete($machine_name)
{
    $path = GeneratePath($machine_name);

    //Call delete_category hook before deleting the category
    \JarisCMS\Module\Hook("Category", "Delete", $machine_name, $path);

    //Clears the category directory and deletes it
    if(!\JarisCMS\FileSystem\RemoveDirRecursively($path))
    {
            return false;
    }
    
    \JarisCMS\Block\DeleteByField("category_name", $machine_name);

    return true;
}

/**
 * Modifies the data of a category.
 *
 * @param string $machine_name The machine name of the category.
 * @param array $new_data New data in the format:
 *        $data = array("name"=>value, "description"=>value)
 *
 * @return bool True on success or false on fail.
 */
function Edit($machine_name, $new_data)
{
    $path = GeneratePath($machine_name);

    //Call edit_category hook before editing the category
    \JarisCMS\Module\Hook("Category", "Edit", $machine_name, $new_data, $path);

    return \JarisCMS\PHPDB\Edit(0, $new_data, $path . "/data.php");
}

/**
 * Gets all the data of a category.
 *
 * @param string $machine_name The machine name of the category.
 *
 * @return array All the data fields of the category in the format
 *         $data["field_name"] = "value";
 */
function GetData($machine_name)
{
    static $data;
    
    if(!is_array($data[$machine_name]))
    {
        $path = GeneratePath($machine_name);
    
        $data[$machine_name] = \JarisCMS\PHPDB\GetData(0, $path . "/data.php");
    }

    //Call GetData hook before returning the data
    \JarisCMS\Module\Hook("Category", "GetData", $machine_name, $data[$machine_name]);

    return $data[$machine_name];
}

/**
 * Creates a subcategory.
 *
 * @param string $category The machine name of the main category. 
 * @param array $data Data to store to the sub category 
 *        in the format: data = array("name"=>"value", 
 *        "description"=>value)
 *
 *@return Bool True on success or false on fail.
*/
function AddChild($category, $data)
{
    $path = GeneratePath($category);

    //Call create_subcategory hook before creating the category
    \JarisCMS\Module\Hook("Category", "AddChild", $category, $data);

    return \JarisCMS\PHPDB\Add($data, $path . "/sub_categories.php");
}

/**
 * Deletes a subcategory.
 *
 * @param string $category The machine name of the main category.
 * @param integer $id The id of the subcategory to delete.
 *
 * @return bool True on success or false on fail.
 */
function DeleteChild($category, $id)
{
    $path = GeneratePath($category);

    //Call delete_subcategory hook before deleting the category
    \JarisCMS\Module\Hook("Category", "DeleteChild", $category, $id, $path);

    return \JarisCMS\PHPDB\Delete($id, $path . "/sub_categories.php");
}

/**
 * Modifies the data of a subcategory.
 *
 * @param string $category The machine name of the main category.
 * @param string $new_data New data in the format:
 *        $data = array("name"=>value, "description"=>value)
 * @param integer $id The id of the subcategory to edit.
 *
 * @return bool True on success or false on fail.
 */
function EditChild($category, $new_data, $id)
{
    $path = GeneratePath($category);

    //Call edit_subcategory hook before editing the page
    \JarisCMS\Module\Hook("Category", "EditChild", $category, $new_data, $id);

    return \JarisCMS\PHPDB\Edit($id, $new_data, $path . "/sub_categories.php");
}

/**
 * Gets all the data of a subcategory.
 *
 * @param string $category The machine name of the main category.
 * @param integer $id The id of the subcategory.
 *
 * @return array All the data fields of the subcategory in the format $data["field_name"] = "value";
 */
function GetChildData($category, $id)
{
    $sub_categories = GetChildrenList($category);

    $data = $sub_categories[$id];

    //Call GetData hook before returning the data
    \JarisCMS\Module\Hook("Category", "GetChildData", $category, $data);

    return $data;
}

/**
 * Recursive function that returns the subcategories of a subcategory.
 * 
 * @param $category the machine name of the main category.
 * @param $parent_id the id of the parent item.
 * 
 * @return array The parent subcategory with its subcategories and also 
 *            the subcategories of the subcategories in another array. For example:
 *            $parent_subcategory = array(..., subcategory_values, ..., "subcategories"=>array())
 */
function GetChildrenRecursively($category, $parent_id="root")
{
    $subcategories = GetChildrenList($category);
    
    $subcategory_childrens = array();
    if($subcategories)
    {
        foreach($subcategories as $id=>$fields)
        {
            if("" . $fields["parent"] . "" == "" . $parent_id . "")
            {
                //get the sub items of this item
                $sub_items["sub_items"] = \JarisCMS\PHPDB\Sort(GetChildrenRecursively($category, $id), "order");
                
                if(count($sub_items["sub_items"]) > 0)
                {
                    $fields += $sub_items;
                }
                
                $subcategory_childrens[$id] = $fields;
            }
        }
    }
    
    return $subcategory_childrens;
}

/**
 * Gets the list of available subcategories.
 * 
 * @param string $category The machine name main category.
 *
 * @return array|bool All subcategories in the format categories["id"] =
 *          array(
 *            "name"=>"string",
 *            "description"=>"string"
 *          )
 *        or false if no subcategory is found
 */
function GetChildrenList($category)
{
    static $categories;
    
    if(!isset($categories[$category]))
    {
        $path = GeneratePath($category);
        
        $category_data = GetData($category);
        
        if(!$category_data["sorting"])
        {
            $categories[$category] = \JarisCMS\PHPDB\Sort(\JarisCMS\PHPDB\Parse($path . "/sub_categories.php"), "order");
        }
        else
        {
            $categories[$category] = \JarisCMS\PHPDB\Sort(\JarisCMS\PHPDB\Parse($path . "/sub_categories.php"), "title");
        }
    }

    return $categories[$category];
}

/**
 * Gets the list of available categories.
 *
 * @param string $type Optional value to only get the categories available for a content type.
 * 
 * @return array|null All categories in the format categories["machine name"] =
 *          array(
 *            "name"=>"string",
 *            "description"=>"string"
 *          )
 *        or null if no category is found.
 */
function GetList($type=null)
{   
    $dir = opendir(\JarisCMS\Setting\GetDataDirectory() . "categories");

    $categories = null;

    while(($file = readdir($dir)) !== false)
    {
        if($file != "." && $file != ".." && $file != "readme.txt")
        {
            $machine_name = $file;

            $categories[$machine_name] = GetData($machine_name);
        }
    }

    closedir($dir);
    
    if(is_array($categories))
    {
        ksort($categories);
    }
    
    if($type && is_array($categories))
    {
        $type_data = \JarisCMS\Type\GetData($type);
        
        //Only if user selected specific categories
        if(is_array($type_data["categories"]))
        {
            foreach($categories as $category_name=>$category_data)
            {
                $is_available = false;
                foreach($type_data["categories"] as $available_categories)
                {
                    if($category_name == $available_categories)
                    {
                        $is_available = true;
                        break;
                    }
                }
                
                if(!$is_available)
                {
                    unset($categories[$category_name]);
                }
            }
        }
        else
        {
            $categories = null;
        }
    }
    
    if(is_array($categories))
    {
        $categories = \JarisCMS\PHPDB\Sort($categories, "order");
    }

    return $categories;
}

/**
 * Recursively organize subcategories and illustrate its childs as parents with white spaces.
 * 
 * @param string $category_name The machine name of the main category.
 * @param string $parent The parent of the subcategory, root for main categories.
 * @param string $position
 * 
 * @return array All subcategories.
 */
function GetChildrenInParentOrder($category_name, $parent="root", $position="")
{
    $category_data = GetData($category_name);
    
    if(!$category_data["sorting"])
    {
        $subcategories_list = \JarisCMS\PHPDB\Sort(GetChildrenRecursively($category_name, $parent), "order");
    }
    else
    {
        $subcategories_list = \JarisCMS\PHPDB\Sort(GetChildrenRecursively($category_name, $parent), "title");
    }
    
    $subcategories = array();

    if($subcategories_list)
    {
        foreach($subcategories_list as $id => $fields)
        {
            $subcategories[$id] = $fields; 
            $subcategories[$id]["title"] = $position . t($fields["title"]);

            $subcategories += GetChildrenInParentOrder($category_name, $id, $position . "- ");
        }
    }

    return $subcategories;
}

/**
 * Generates the neccesary array of all available categories for the form fields.
 *
 * @param array $selected The array of selected categories on the control.
 * @param string $main_category Machine name of category to generate the form field for a specific and single category.
 * @param string $type The type to generate the available categories for it.
 * 
 * @return array Data that represent a series of fields that can
 *         be used when generating a form on a fieldset.
 */
function GenerateFieldList($selected=null, $main_category=null, $type=null)
{
    $fields = array();
    
    $categories_list = array();
 
    if(!$main_category)
    {
        $categories_list = GetList($type);
    }
    else
    {
        $categories_list[$main_category] = GetData($main_category);
    }
    
    foreach($categories_list as $machine_name=>$values)
    {
        $subcategories = GetChildrenInParentOrder($machine_name);

        $select_values = null;

        if(!$values["multiple"])
        {
                $select_values[t("-None Selected-")] = "-1";
        }

        foreach($subcategories as $id=>$sub_values)
        {
            //In case person created categories with the same name
            if(isset($select_values[t($sub_values["title"])]))
            {
                $title = t($sub_values["title"]) . " ";
                while(isset($select_values[$title]))
                {
                    $title .= " ";
                }

                $select_values[$title] = $id;
            }
            else
            {
                $select_values[t($sub_values["title"])] = $id;
            }
        }

        $multiple = false;

        if($values["multiple"])
        {
            $multiple = true;
        }

        if(count($select_values) > 1)
        {    
            if(count($selected) > 0)
            {
                $fields[] = array("type"=>"select", "multiple"=>$multiple, "selected"=>$selected[$machine_name], "name"=>"{$machine_name}[]", "label"=>t($values["name"]), "id"=>$machine_name, "value"=>$select_values);
            }
            else
            {
                $fields[] = array("type"=>"select", "multiple"=>$multiple, "name"=>"{$machine_name}[]", "label"=>t($values["name"]), "id"=>$machine_name, "value"=>$select_values);
            }
        }
    }

    return $fields;
}

/**
 * Creates a category menu block.
 *
 * @param string $machine_name The machine name of the category.
 * @param array $data The category data.
 */
function AddMenuBlock($machine_name, $data)
{
    $category_block["description"] = $machine_name . " " . "menu";
    $category_block["title"] = $data["name"] . " " . "menu";
    $category_block["content"] = "<?php\nprint \\\JarisCMS\\Category\\GetMenuHtml(\"$machine_name\");\n?>";
    $category_block["order"] = "0";
    $category_block["display_rule"] = "all_except_listed";
    $category_block["pages"] = "";
    $category_block["return"] = "";
    $category_block["is_system"] = "1";
    $category_block["category_name"] = $machine_name;

    \JarisCMS\Block\Add($category_block, "none");
}

/**
 * Generate the html needed for a system generated block that displays a menu
 * of categories.
 *
 * @param string $machine_name The machine name of the category.
 */
function GetMenuHtml($machine_name)
{
    $position = 1;
    $subcategories_array = GetChildrenRecursively($machine_name);
    $count_subcategories = count($subcategories_array);

    if($count_subcategories > 0)
    {
        $links = "<ul class=\"menu $machine_name\">";
    
        foreach($subcategories_array as $subcategory)
        {
            $list_class = "";
            
            $subcategory["url"] = "category/$machine_name/" . \JarisCMS\URI\FromText($subcategory["title"]);
    
            if($position == 1)
            {
                $list_class = " class=\"first\"";
            }
            elseif($position == $count_subcategories)
            {
                $list_class = " class=\"last\"";
            }
            else
            {
                $list_class = "";
            }
    
            //Translate the title and description using the strings.php file if available.
            $subcategory['title'] = t($subcategory['title']);
            $subcategory['description'] = t($subcategory['description']);
            
            $active = \JarisCMS\URI\Get() == $subcategory["url"]?"class=\"active\"":"";
    
            $links .= "<li{$list_class}><span><a $active title=\"{$subcategory['description']}\" href=\"" . \JarisCMS\URI\PrintURL($subcategory['url']) . "\">" . $subcategory['title'] . "</a></span>";
            
            $links .= "</li>\n";
    
            $position++;
        }
    
        $links .= "</ul>";
    }
    
    return $links;
}

/**
 * Prepares the corresponding $_REQUEST variables to display the search
 * results of a category using an alias.
 *
 * @param $page The uri alias of the category.
 *
 * @return string Path to category data directory.
 */
function ShowResults(&$page)
{
    $sections = explode("/", $page);
    
    $category_name = $sections[1];
    
    $path = GeneratePath($category_name);
    
    if(file_exists($path))
    {
        $subcategories = GetChildrenList($category_name);
        
        if(isset($sections[2]))
        {
            if(is_array($subcategories))
            {
                foreach($subcategories as $id=>$data)
                {
                    $category_uri = \JarisCMS\URI\FromText($data["title"]);
                    
                    if($category_uri == $sections[2])
                    {
                        $_REQUEST[$category_name][] = $id;
                        
                        $page = "search";
            
                        if(!isset($_REQUEST["page"]))
                        {
                            $_REQUEST["search"] = 1;
                        }
                        
                        break;
                    }
                }
            }
        }
        else if(isset($sections[1]))
        {
            if(is_array($subcategories))
            {
                foreach($subcategories as $id=>$data)
                {
                    $_REQUEST[$category_name][] = $id;  
                }
            }
            
            $page = "search";
        
            if(!isset($_REQUEST["page"]))
            {
                $_REQUEST["search"] = 1;
            }
        }
    }
}

/**
 * Generates the system path to the category data directory.
 *
 * @param string $machine_name the machine name of the category.
 *
 * @return string Path to category data directory.
 */
function GeneratePath($machine_name)
{
    $path = \JarisCMS\Setting\GetDataDirectory() . "categories/$machine_name";

    return $path;
}
?>
