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
		<?php print t("Export Users List") ?>
	field;
	field: content
		<?php
			JarisCMS\Security\ProtectPage(array("view_users"));
            
            JarisCMS\System\AddTab(t("Navigation View"), "admin/users");
            JarisCMS\System\AddTab(t("List View"), "admin/users/list");
            JarisCMS\System\AddTab(t("Create User"), "admin/users/add");
			JarisCMS\System\AddTab(t("Groups"), "admin/groups");
            JarisCMS\System\AddTab(t("Export"), "admin/users/export");
            
            $users_csv = JarisCMS\Setting\GetDataDirectory() . "users/users.csv";
			
            if(file_exists($users_csv))
            {
                JarisCMS\System\AddTab(t("Download Last Generated"), "admin/users/export", array("download"=>1), 1);
            }
           
            $page = 1;
			
			if(isset($_REQUEST["btnYes"]))
			{
				$file = fopen($users_csv, "w");
                
                if($file)
                {
                    ini_set('max_execution_time', '0');
                    
                    fputs($file, "username,email,register_date,user_group,picture,ip_address,gender,birth_date\n");
                    
                    $db = JarisCMS\SQLite\Open("users");
                    $select = "select * from users";
                    $result = JarisCMS\SQLite\Query($select, $db);
                    
                    while($data = JarisCMS\SQLite\FetchArray($result))
                    {
                        fputcsv($file, $data, ",", "\"");
                    }
                    
                    fclose($file);
                    JarisCMS\SQLite\Close($db);
                    
                    JarisCMS\FileSystem\PrintAllFiles($users_csv, "users.csv", true, true);
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
                }
			}
            elseif(isset($_REQUEST["download"]))
            {
                JarisCMS\FileSystem\PrintAllFiles($users_csv, "users.csv", true, true);
            }
            elseif(isset($_REQUEST["btnNo"]))
            {
                JarisCMS\System\GoToPage("admin/users");
            }
		?>
        
        <?php
            
        ?>
        
        <form class="export_users_list" method="post" action="<?php JarisCMS\URI\PrintURL("admin/users/export") ?>">
			<div><?php print t("The process of creating a csv file of the users database could take a long time.<br />Do you want to the generate export file?") ?></div>
			<input class="form-submit" type="submit" name="btnYes" value="<?php print t("Yes") ?>" />
			<input class="form-submit" type="submit" name="btnNo" value="<?php print t("No") ?>" />
		</form>
	field;

	field: is_system
		1
	field;
row;
