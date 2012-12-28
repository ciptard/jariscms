<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Functions to manage modules
 */

namespace JarisCMS\Module;

/**
 * Calls a hook function from a module functions.php file if available
 *
 * @param string $namespace The namespace where the hook function should reside inside the module's namespace.
 * @param string $function_name The name of the hook to call.
 * @param mixed $var1 Optional argument passed to the hook function.
 * @param mixed $var2 Optional argument passed to the hook function.
 * @param mixed $var3 Optional argument passed to the hook function.
 * @param mixed $var4 Optional argument passed to the hook function.
 */
function Hook($namespace, $function_name, &$var1="null", &$var2="null", &$var3="null", &$var4="null")
{
	static $modules_namespace;
	
	$modules_dir = GetInstalledNames();
	
	//Cache module namespaces if not already cached
	if(!is_array($modules_namespace))
	{
		$modules_namespace = array();
		
		$module = array(); //Intialize variable modified by module info.php file
		
		foreach($modules_dir as $dir)
		{
			include("modules/".$dir."/info.php");
			$modules_namespace[] = $module["namespace"];
			
			unset($module);
		}
	}

	foreach($modules_namespace as $index=>$name)
	{
		$functions_file = \JarisCMS\Setting\GetDataDirectory() . "modules/{$modules_dir[$index]}/functions.php";
		$hook_name = "\\JarisCMS\\Module\\".$name."\\".$namespace."\\".$function_name;

		if(!file_exists($functions_file))
		{
			//Skip if functions file doesnt exist on the current module
			continue;
		}

		include_once($functions_file);

		if(!function_exists($hook_name))
		{
			//Skip if function doesnt exist on the current module
			continue;
		}

		if($var1 != "null" && $var2 != "null" && $var3 != "null" && $var4 != "null")
		{
			$hook_name($var1, $var2, $var3, $var4);
		}
		else if($var1 != "null" && $var2 != "null" && $var3 != "null")
		{
			$hook_name($var1, $var2, $var3);
		}
		else if($var1 != "null" && $var2 != "null")
		{
			$hook_name($var1, $var2);
		}
		else if($var1 != "null")
		{
			$hook_name($var1);
		}
		else
		{
			$hook_name();
		}
	}
}

/**
 * Retreive all the info of available modules.
 *
 * @return array|null Info of all modules in the format
 *         modules["module_machine_name"] = array("field"=>"value")
 *         or null if no module available.
 */
function GetAll()
{
	$module_dir = "modules/";
	$dir_handle = opendir($module_dir);

	$modules = null;

	while(($file = readdir($dir_handle)) !== false)
	{
        //Deletes previous module data
        unset($module);
        
		if(strcmp($file, ".") != 0 && strcmp($file, "..") != 0)
		{
			$info_file = $module_dir . $file . "/info.php";

			if(file_exists($info_file))
			{
				include($info_file);
				$modules[$file] = $module;
			}
		}
	}
    
    ksort($modules);

	return $modules;
}

/**
 * Retreive the info of a specific module
 *
 * @param $name the machine name of the module usually its directory name
 *        on the modules directory.
 *
 * @return array|bool Info of the module or false if doesnt exist.
 */
function GetInfo($name)
{
	$module_dir = "modules/$name/";

	$info_file = $module_dir . "info.php";

	if(file_exists($info_file))
	{
		include($info_file);
		return $module;
	}

	return false;
}

/**
 * Check the modules that are installed.
 *
 * @return array Machine names of each installed module.
 */
function GetInstalledNames()
{
    static $modules;
    
    if(!$modules)
    {
        $modules = array();
        
    	$module_dir = \JarisCMS\Setting\GetDataDirectory() . "modules/";
		
		if(!is_dir($module_dir))
			return $modules;
		
    	$dir_handle = opendir($module_dir);
        
        if(!is_bool($dir_handle))
        {
        	while(($file = readdir($dir_handle)) !== false)
        	{
        		if(strcmp($file, ".") != 0 && strcmp($file, "..") != 0)
        		{
        			if(is_dir($module_dir . $file))
        			{
        				$modules[] = $file;
        			}
        		}
        	}
         }
    }

	return $modules;
}

/**
 * Get the current installed version of a module.
 *
 * @param string Name of the module to retrieve its version.
 *
 * @return bool true on success false if module is not installed.
 */
function GetVersion($name)
{
	$module_dir = \JarisCMS\Setting\GetDataDirectory() . "modules/$name/";

	$info_file = $module_dir . "info.php";

	if(file_exists($info_file))
	{
		include($info_file);
		return $module["version"];
	}

	return false;
}

/**
 * Checks if a module is installed to the system.
 *
 * @param string $name Machine name of the module.
 *
 * @return bool true if installed false if not.
 */
function IsInstalled($name)
{
	if(file_exists(\JarisCMS\Setting\GetDataDirectory() . "modules/$name"))
	{
		return true;
	}

	return false;
}

/**
 * Check if a module dependencies are installed.
 *
 * @param string $name Machine name of the module currently its directory name
 *        on the modules directory.
 *
 * @return bool true if dependencies are installed false if not.
 */
function CheckDependencies($name)
{
    $module_data = GetInfo($name);
    
    if(isset($module_data["dependencies"]))
    {
        $some_modules_not_installed = false;
        $modules_not_installed = "";
        
        foreach($module_data["dependencies"] as $dependency_name)
        {
            if(!IsInstalled($dependency_name))
            {
                $dependency_data = GetInfo($dependency_name);
                $some_modules_not_installed = true;
                
                if($dependency_data)
                {
                    $modules_not_installed  .= $dependency_data["name"] . ", ";
                }
                else
                {
                    $modules_not_installed  .= $dependency_name . ", ";
                }
                
                unset($dependency_data);
            }
        }
        
        if($some_modules_not_installed)
        {
            $modules_not_installed = trim($modules_not_installed, ", ");
            
            \JarisCMS\System\AddMessage(t("The following modules need to be installed first:") . " $mo$modules_not_installed", "error");
            
            return false;
        }
    }
    
    return true;
}

/**
 * Check if the given module is a dependency of other.
 *
 * @param string $name Machine name of the module currently its directory name
 *        on the modules directory.
 *
 * @return bool true if is dependency false if not.
 */
function IsDependency($name)
{
    $installed_modules = GetInstalledNames();
    
    foreach($installed_modules as $module_name)
    {
        $module_data = GetInfo($module_name);
        
        if(isset($module_data["dependencies"]))
        {
            foreach($module_data["dependencies"] as $dependency_name)
            {
                if($dependency_name == $name)
                {
                    return true;
                }
            }
        }
    }
    
    return false;
}

/**
 * Enable a module to be usable by the system if all dependecies are satisfied.
 *
 * @param string $name Machine name of the module usually its directory name
 *        on the modules directory.
 *
 * @param bool $needs_dependency Reference that returns true if current module needs dependency.
 *
 * @return bool true on success false on fail.
 */
function Install($name, &$needs_dependency=null)
{
    if(!CheckDependencies($name))
    {
        $needs_dependency = true;
        return false;
    }
    
	$module_dir = "modules/$name";
	$module_installation = \JarisCMS\Setting\GetDataDirectory() . "modules/$name";

	//Firt we make the directory holding module installation files.
	if(!\JarisCMS\FileSystem\MakeDir($module_installation, 0755, true))
	{
		return false;
	}

	//Copy current module info file used to store the version
	copy($module_dir . "/info.php", $module_installation . "/info.php");

	//Copy current module functions file.
	if(file_exists($module_dir . "/functions.php"))
	{
		copy($module_dir . "/functions.php", $module_installation . "/functions.php");
	}
	
	//Copy current module uninstall function file.
	if(file_exists($module_dir . "/uninstall.php"))
	{
		copy($module_dir . "/uninstall.php", $module_installation . "/uninstall.php");
	}

	//Install module pages
	if(file_exists($module_dir . "/pages.php"))
	{
		//Store the uri of each page created in case uri is renamed since already
		//exist
		$pages_uri = array();

		$pages = \JarisCMS\PHPDB\Parse($module_dir . "/pages.php");

		foreach($pages as $id=>$fields)
		{
			$uri = trim($fields["uri"]);

			//Reference that stores the new page uri in case original already exist
			$new_uri = null;

			$data_file = $module_dir . "/data/" . str_replace("/", "-", str_replace("-", "_", $uri)) . ".php";

			$data = \JarisCMS\PHPDB\GetData(0, $data_file);

			if(!\JarisCMS\Page\Create($uri, $data, $new_uri))
			{
				return false;
			}

			$pages_uri[$id] = array("original_uri"=>$uri, "new_uri"=>$new_uri);
		}

		if(!\JarisCMS\PHPDB\Write($pages_uri, $module_installation . "/pages.php"))
		{
			return false;
		}
	}
	
	//Execute module install script function if available
	//This function is named with the module name and install word.
	//for example: modulename_install()
	if(file_exists($module_dir . "/install.php"))
	{
		include($module_dir . "/info.php"); //Get Module info
		
		include($module_dir . "/install.php"); //Module install function
		
		$install_function = "\\JarisCMS\\Module\\".$module["namespace"]."\\Install";
		
		$install_function();
	}

	return true;
}

/**
 * Removes a module from the system if not dependency.
 *
 * @param string $name Machine name of the module to remove.
 * @param bool $is_dependency reference variable that returns true if current module is dependency.
 *
 * @return bool true on success false on fail.
 */
function Uninstall($name, &$is_dependency=null)
{
    if(IsDependency($name))
    {
        $is_dependency = true;
        \JarisCMS\System\AddMessage(t("This module is a dependency and can't be uninstalled."), "error");
        return false;
    }
    
	$module_dir = \JarisCMS\Setting\GetDataDirectory() . "modules/$name";

	//Remove module pages
	if(file_exists($module_dir . "/pages.php"))
	{
		$pages = \JarisCMS\PHPDB\Parse($module_dir . "/pages.php");

		foreach($pages as $id=>$fields)
		{
			if(!\JarisCMS\Page\Delete($fields["new_uri"]))
			{
				return false;
			}
		}
	}
	
	//Execute module uninstall script function if available
	//This function is named with the module name and uninstall word.
	//for example: modulename_uninstall()
	if(file_exists($module_dir . "/uninstall.php"))
	{
		include($module_dir . "/info.php"); //Get Module info
		
		include($module_dir . "/uninstall.php"); //Module uninstall function
		
		$uninstall_function = "\\JarisCMS\\Module\\".$module["namespace"]."\\Uninstall";
		
		$uninstall_function();
	}

	if(!\JarisCMS\FileSystem\RemoveDirRecursively($module_dir))
	{
		return false;
	}
	
	return true;
}

/**
 * Upgrades a module if installed version is different from uploaded to modules directory.
 *
 * @param string $name Machine name of the module, currently its directory name
 *        on the modules directory.
 *
 * @return bool true on success false on fail.
 */
function Upgrade($name)
{
	$module_dir = "modules/$name";
	$module_installation = \JarisCMS\Setting\GetDataDirectory() . "modules/$name";

	//Remove module pages
	if(file_exists($module_installation . "/pages.php"))
	{
		$pages = \JarisCMS\PHPDB\Parse($module_installation . "/pages.php");

		foreach($pages as $id=>$fields)
		{
			if(!\JarisCMS\Page\Delete($fields["new_uri"]))
			{
				return false;
			}
		}
	}

	//Copy current module info file used to store the version
	copy($module_dir . "/info.php", $module_installation . "/info.php");

	//Copy current module functions file.
	if(file_exists($module_dir . "/functions.php"))
	{
		copy($module_dir . "/functions.php", $module_installation . "/functions.php");
	}
	
	//Copy current module uninstall function file.
	if(file_exists($module_dir . "/uninstall.php"))
	{
		copy($module_dir . "/uninstall.php", $module_installation . "/uninstall.php");
	}

	//Install module pages
	if(file_exists($module_dir . "/pages.php"))
	{
		//Store the uri of each page created in case uri is renamed since already
		//exist
		$pages_uri = array();

		$pages = \JarisCMS\PHPDB\Parse($module_dir . "/pages.php");

		foreach($pages as $id=>$fields)
		{
			$uri = trim($fields["uri"]);

			//Reference that stores the new page uri in case original already exist
			$new_uri = null;

			$data_file = $module_dir . "/data/" . str_replace("/", "-", str_replace("-", "_", $uri)) . ".php";

			$data = \JarisCMS\PHPDB\GetData(0, $data_file);

			if(!\JarisCMS\Page\Create($uri, $data, $new_uri))
			{
				return false;
			}

			$pages_uri[$id] = array("original_uri"=>$new_uri, "new_uri"=>$new_uri);
		}

		if(!\JarisCMS\PHPDB\Write($pages_uri, $module_installation . "/pages.php"))
		{
			return false;
		}
	}
	
	//Execute module upgrade script function if available
	//This function is named with the module name and upgrade word.
	//for example: modulename_upgrade()
	if(file_exists($module_dir . "/upgrade.php"))
	{
		include($module_dir . "/upgrade.php");
		
		$upgrade_function = $name . "Upgrade";
		
		$upgrade_function();
	}

	return true;
}

/**
 * Function to retrieve the uri of a page installed with a module. This function
 * is used in case the page installed with a module had to be renamed to another
 * uri since it already existed.
 *
 * @param string $original_uri Original uri of the page installed.
 * @param string Machine name of the module.
 *
 * @return string New uri of the page installed or the original one.
 */
function GetPageURI($original_uri, $module_name)
{
    static $module_pages;
    
    if($module_name != "")
    {
        if(!$module_pages[$module_name])
        {
            $module_pages[$module_name] = \JarisCMS\PHPDB\Parse(\JarisCMS\Setting\GetDataDirectory() . "modules/$module_name/pages.php");
        }
    	
    	if(is_array($module_pages[$module_name]))
    	{
    		foreach($module_pages[$module_name] as $id=>$fields)
    		{
    			if($fields["original_uri"] == $original_uri)
    			{
    				return $fields["new_uri"];
    			}
    		}
    	}
     }

	return $original_uri;
}
?>