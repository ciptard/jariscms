<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the user list page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("Re-index Users Database") ?>
	field;
	field: content
		<?php
			JarisCMS\Security\ProtectPage(array("edit_users"));
            
            JarisCMS\System\AddTab(t("Navigation View"), "admin/users");
            JarisCMS\System\AddTab(t("List View"), "admin/users/list");
            JarisCMS\System\AddTab(t("Create User"), "admin/users/add");
			JarisCMS\System\AddTab(t("Groups"), "admin/groups");
			JarisCMS\System\AddTab(t("Export"), "admin/users/export");
			
			JarisCMS\System\AddTab(t("Re-index Users List"), "admin/users/re-index", null, 1);
           
			if(isset($_REQUEST["btnYes"]))
			{
				ini_set('max_execution_time', '0');
			
				if(users_reindex_sqlite())
				{
					JarisCMS\System\AddMessage(t("Indexation of users database completed."));
				}
				else
				{
					JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
				}
			
				JarisCMS\System\GoToPage("admin/users/list");
			}
			elseif(isset($_REQUEST["btnNo"]))
			{
				JarisCMS\System\GoToPage("admin/users/list");
			}
			
			function users_reindex_sqlite()
			{
				if(JarisCMS\SQLite\DBExists("users"))
				{
					unlink(JarisCMS\Setting\GetDataDirectory() . "sqlite/users");
				}
			
				//Recreate database and table
				$db = JarisCMS\SQLite\Open("users");
			
				if(!$db)
				{
					return false;
				}
			
				JarisCMS\SQLite\Query("create table users (username text, email text, register_date text, user_group text, picture text, ip_address text, gender text, birth_date text, status text)", $db);
        
        		JarisCMS\SQLite\Query("create index users_index on users (username desc, email desc, register_date desc, user_group asc, gender desc, birth_date desc, status desc)", $db);
			
				JarisCMS\SQLite\Close($db);
			
				JarisCMS\FileSystem\SearchFiles(JarisCMS\Setting\GetDataDirectory() . "users", "/.*data\.php/", "users_reindex_callback");
			
				return true;
			}
			
			function users_reindex_callback($content_path)
			{
				$user_path = str_replace("/data.php", "", $content_path);
				$path_array = explode("/", $user_path);
				$username = $path_array[count($path_array)-1];
			
				$user_data = JarisCMS\User\GetData($username);
				
				//Marks users as active on older versions of jaris cms that dont had the user status field
				$status = isset($user_data["status"])?$user_data["status"]:"1";
				
				$db = JarisCMS\SQLite\Open("users");
				JarisCMS\SQLite\Turbo($db);
				
				$data = $user_data;
				$data["username"] = $username;
				JarisCMS\SQLite\EscapeArray($data);
				JarisCMS\SQLite\Query("insert into users (username, email, register_date, user_group, picture, ip_address, gender, birth_date, status)
				values ('{$data['username']}','{$data['email']}','{$data['register_date']}','{$data['group']}','{$data['picture']}','{$data['ip_address']}','{$data['gender']}','{$data['birth_date']}','$status')", $db);
				
				JarisCMS\SQLite\Close($db);
			}
			
			?>
			
			<form class="reindex-search-engine" method="post" action="<?php JarisCMS\URI\PrintURL("admin/users/re-index") ?>">
			<div><?php print t("The process of recreating the users database list could take a long time. Are you sure you want to do this?") ?></div>
			<input class="form-submit" type="submit" name="btnYes" value="<?php print t("Yes") ?>" />
			<input class="form-submit" type="submit" name="btnNo" value="<?php print t("No") ?>" />
			</form>
	field;

	field: is_system
		1
	field;
row;
