#Modules System

* Introduction
* Structure
* How it works
	* data
	* include
	* language
	* functions.php
	* info.php
	* pages.php
	* install.php
	* uninstall.php
	* upgrade.php
* Hooks Reference
	* Category
	* Form
	* Group
	* InputFormat
	* Page
	* SQLite
	* System
	* Theme
	* Type
	* User

##Introduction

Jaris can be extended with features it doesn't support by installing modules. A module is a way to provide additional functionality to the core system without having to modify the core's code base.

The core provides a hook system which automatically calls a module function if available for the current task been processed. An example would be when the system creates a new user account. The function used for this purpose is JarisCMS\User\Add($username, $group, $fields). 

If your module has a corresponding hook function for User\Add it will be called before creating the user account so the module can execute a custom set of tasks.

##Structure

Modules go inside the 'modules' directory, the name given to each module directory should be unique. The directory name of the module is used as its machine name. For example:

jaris-src/modules/

* module1
* module2
* module3

Each module can or should have the following list of files and directories:

* **data** - Directory that holds all pages that are going to be installed for the module. (optional)
* **include** - Directory with php files that are automatically included by jaris if module is installed. (optional)
* **functions.php** - On this file goes every hook function that gets automatically called when needed. (optional)
* **info.php** - Stores version, description and some other details of a module. (required)
* **install.php** - Script with install function that gets called when the module is being installed. (optional)
* **language** - Directory that holds translations of your module using po files. (optional)
* **pages.php** - List of uri's for the pages on the 'data' directory that are going to be installed. (optional)
* **uninstall.php** - Script with uninstall function that gets called when module is being uninstalled. (optional)
* **upgrade.php** - Script with upgrade function that gets called when module is being upgraded. (optional)

##How it works

Now that you briefly know each of the components that can or should be added to a module directory, we are going to go into more detail for each one.

###data

The 'data' directory as explained before holds pages that are going to be installed with your module. Now lets say you want to add a new page to configure some settings that belong to your module. The page uri is going to be 'admin/settings/mymodule', you would need to create a file named admin-settings-mymodule.php. This page uri would need to be listed on the pages.php file in order to be automatically installed.

As you can notice dashes (-) are replaced by slashes (/). If you want the uri of the page to look like 'admin/settings/my-module' then you would need to use the name admin-settings-my_module.php. In a brief:

* dash == slash 
* underscore == dash

Is important to note that these files have to be written using the PHPDB format used by the core for system pages like 'admin/settings'. Here an example of the structure used for these files:

	<?php exit //Protect the page content and prevent direct access errors ?>

	field: title
		<?php print t("My Module Settings) ?>
	field;

	field: content
		<?php
			//My settings code
		?>
	field;

	field: is_system
		1 //This identifies the page as system generated instead of user generated for various purposes.
	field;

###include

On the 'include' directory you can place php library files like classes and functions used by your module, that will be automatically included by Jaris to the global scope. In this way you can make use of them from any part of the code. You can take as an example the 'markdown' or 'mobile_detect' modules. Both require third party libraries that are automatically included in the system and can be used from anywhere. The mobile detect module uses the Mobile_Detect class for checking the device currently being used to access the site. Since the Mobile_Detect class is being automatically included from the 'include' directory of the module you can even use this class from your theme template files. Here some example:

jaris-src/modules/mymodule/include

* some_class.php     //Stores a class used in the module pages
* some_functions.php //Stores some global functions that can be used by the module or theme writers.

###language

If you are going to ship your module in more than one language you can add translations into the 'language' directory. If your module main language is english and you want to translate it to spanish you would need to add a strings.po file inside a directory with the language code. An example of the 'language' directory structure:

jaris-src/modules/mymodule/language

* es/strings.po
* it/strings.po

When the module is installed these translations are automatically read depending on current language and appended to core translations.

###functions.php

This file stores each of the hook functions implemented by a module. As mentioned before, when a user account is created a hook process is executed that will check each of the installed modules that define the User\Add function. With some of the given hints here is an example for a module that needs to take some actions when a user account is created.

	<?php
	namespace JarisCMS\Module\MyModule\User
	{
		use JarisCMS\System;
		
		function Add(&$username, &$group, &$fields, &$picture)
		{
			if($group == "administrator")
			{
				System\AddMessage(t("Another administrator account! You are nuts..."));
			}
		}
	}
	?>

As you can notice from the example below the Add function parameters are set as references so you can modify the original values from within your hook function. Now take a look at the namespace declaration. Most of the core namespaced functions call hooks, like JarisCMS\User\Add calls the hook process. To find each of the modules hook functions they have to be on the namespace JarisCMS\Module\ModuleNameSpaceName\CoreNameSpace where ModuleNameSpaceName is a namespace string given on the info.php file of the module and the CoreNameSpace is the name of a jaris namespace that groups core functions as in the case of 'User'.

###info.php

In contrast to all other components that form a module the info.php files is vital for any module you create as it holds information of it used in several parts of the system. It consists of an array named $module with the following indexes:

* **name** - Human readable name for the module.
* **description** - A brief description of the functionality shipped with the module.
* **namespace** - The main namespace of the module that will hold it's code and hooks.
* **version** - The current version of the module to help the system identify if upgrade is needed.
* **author** - Name of the module developer.
* **email** - E-mail of developer.
* **website** - Website of developer.

Here is an example of the content that goes on info.php:

	<?php
	$module["name"] = "My Module Name";
	$module["description"] = "This is a module to test some functionality not provided by core.";
	$module["namespace"] = "MyModule";
	$module["version"] = "1.0";
	$module["author"] = "My Name";
	$module["email"] = "my@my-email.tld";
	$module["website"] = "http://www.mydevelopmentwebsite.tld";
	?>

###pages.php

For every page you add on the data directory you have to place its uri on this file in order to get installed. You may be thinking that this seems unnecessary and should be done automatically, and why name it data instead of pages? You are right, but at the time I was developing Jaris I had so many things on my head and thought of using the data directory to store several things to be installed like blocks, content types, menus, etc... After a lot of coding I started doing installation of all those elements on the install.php script when developing modules. So this requirement may change on the future. For now, here is an example of this file:

	<?php exit; ?>

	row: 0
		field: uri
			admin/settings/mymodule
		field;
	row;
	
	row: 2
		field: uri
			some/other/page
		field;
	row;
	
	row: 3
		field: uri
			some/other-other/page
		field;
	row;


###install.php

This file consist of a function named 'Install' inside the namespace that identifies the module. Taking the example used on info.php the content of install.php would look as follows:

	<?php
	namespace JarisCMS\Module\MyModule;

	use JarisCMS\System;

	function Install()
	{
		System\AddMessage(t("The module was properly configured."));
	}
	?>

###uninstall.php

This file consist of a function named 'Uninstall' inside the namespace that identifies the module. Taking the example used on info.php the content of uninstall.php would look as follows:

	<?php
	namespace JarisCMS\Module\MyModule;

	use JarisCMS\System;

	function Uninstall()
	{
		System\AddMessage(t("Hey you uninstalled me!"));
	}
	?>

###upgrade.php

This file consist of a function named 'Upgrade' inside the namespace that identifies the module. Taking the example used on info.php the content of upgrade.php would look as follows:

	<?php
	namespace JarisCMS\Module\MyModule;

	use JarisCMS\System;

	function Upgrade()
	{
		//mmm, some database scheme changed lets re-generate it.
	}
	?>

##Hooks Reference

With some of the basic concepts discussed before you should be able to start developing a module. You can use one of the existing modules as a base/reference to develop yours. Remember we talked about hooks before? This section will list most of them, but before remember that hooks go on the functions.php file and that they need to be on a particular namespace where they can be found by the core to be called. Refer to the Structure/functions.php section for example.

###Category

* Create($machine_name, $data)
* Delete($machine_name, $path)
* Edit($machine_name, $new_data, $path)
* GetData($machine_name, $machine_name)
* AddChild($category, $data)
* DeleteChild($category, $id, $path)
* EditChild($category, $new_data, $id)
* GetChildData($category, $data)

###Form
* Generate($parameters, $fieldsets)

###Group

* GetPermissions($permissions, $group)

###InputFormat

* Add($name, $fields)
* Edit($name, $fields)

###Page

* Create($uri, $data, $path)
* Delete($page, $page_path)
* Edit($page, $new_data, $page_path)
* GetData($page, $data, $language_code)
* Move($actual_uri, $new_uri)

###SQLite

* Open($name, $directory, $db)

###System

* CronJob()
* Initialization()
* GetPageData($page_data)
* MakePageNotFound($page, $tabs)
* GetStyles($styles)
* GetScripts($scripts)	
* GetPageMetaTags($meta_tags)
* MakePagesBlacklist($list)
* IsSystemPage($page, $is_system_page)
* GenerateAdminPage($sections)
* PrintBreadcrumb($breadcrumb, $found_sections)

###Theme

* MakeContent($content, $content_title, $content_data)
* MakeBlocks($position, $page, $field)
* MakeCSSLinks($styles, $styles_code)
* MakeJSLinks($scripts, $scripts_code)
* MakeTabsCode($tabs_array)
* Display($page)
* GetBlockTemplateFile($position, $page, $id, $template_path)
* GetContentBlockTemplateFile($position, $page, $template_path)
* GetPageTemplateFile($page, $template_path)
* GetContentTemplateFile($page, $type, $template_path)
* GetUserProfileTemplateFile($group, $username, $template_path)
* GetSearchTemplateFile($page, $results_type, $template_type, $template_path)
* GetEnabled($themes)

###Type

* Add($name, $fields)
* Edit($name, $fields)

###User

* Add($username, $group, $fields, $picture)
* Delete($username, $group)
* Edit($username, $group, $new_data, $picture)
* GetData($username, $user_data)
* GetDataByEmail($username, $user_data)
* PrintPage($content, $tabs)
* ShowProfile($page, $username)
