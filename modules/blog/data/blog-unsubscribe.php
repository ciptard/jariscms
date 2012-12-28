<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the video upload script.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		Blog Unsubscribe
	field;

	field: content
        <?php
            if(!JarisCMS\Group\GetTypePermission("blog", JarisCMS\Security\GetCurrentUserGroup()))
            { 
                JarisCMS\Security\ProtectPage();
            }
            
            if(isset($_REQUEST["user"]))
            {
                if($user_data = JarisCMS\User\GetData($_REQUEST["user"]))
                {
                    if(JarisCMS\Group\GetTypePermission("blog", $user_data["group"]))
                    {
                        JarisCMS\Module\Blog\Unsubscribe($_REQUEST["user"], JarisCMS\Security\GetCurrentUser());
                        JarisCMS\System\AddMessage(t("Unsubscribtion done."));
                        JarisCMS\System\GoToPage(JarisCMS\Module\GetPageURI("blog/user", "blog") . "/" . $_REQUEST["user"]);
                    }
                }
            }
            
            JarisCMS\System\AddMessage(t("Blog does not exist."));
            JarisCMS\System\GoToPage("");  
            
		?>
	field;

	field: is_system
		1
	field;
row;