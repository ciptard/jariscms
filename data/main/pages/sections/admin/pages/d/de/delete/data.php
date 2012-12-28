<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the content delete page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php 
            $type_data = JarisCMS\Type\GetData(JarisCMS\Page\GetType($_REQUEST["uri"]));
            
            print t("Delete") . " " . t($type_data["name"]);
        ?>
	field;
	field: content
		<?php
			JarisCMS\Security\ProtectPage(array("delete_content"));
            
            if(!JarisCMS\Page\IsOwner($_REQUEST["uri"]))
            {
                JarisCMS\Security\ProtectPage();
            }

			$arguments["uri"] = $_REQUEST["uri"];

			//Tabs
            if(JarisCMS\Group\GetPermission("edit_content", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("Edit"), "admin/pages/edit", $arguments);
            }
			JarisCMS\System\AddTab(t("View"), $_REQUEST["uri"]);
            if(JarisCMS\Group\GetPermission("view_content_blocks", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("Blocks"), "admin/pages/blocks", $arguments);
            }
            if(JarisCMS\Group\GetPermission("view_images", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("Images"), "admin/pages/images", $arguments);
            }
            if(JarisCMS\Group\GetPermission("view_files", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("Files"), "admin/pages/files", $arguments);
            }
            if(JarisCMS\Group\GetPermission("translate_languages", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("Translate"), "admin/pages/translate", $arguments);
            }
            if(JarisCMS\Group\GetPermission("delete_content", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("Delete"), "admin/pages/delete", $arguments);
            }

			$page_data = JarisCMS\Page\GetData($_REQUEST["uri"]);

			if(isset($_REQUEST["btnYes"]))
			{
				//Delete page
				if(JarisCMS\Page\Delete($_REQUEST["uri"]))
				{
					JarisCMS\System\AddMessage(t("Page successfully deleted."));
				}
				else
				{
					JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
				}

				//Also delete page translations
				if(JarisCMS\Language\DeletePageTranslations($_REQUEST["uri"]))
				{
					JarisCMS\System\AddMessage(t("Translations successfully deleted."));
				}
				else
				{
					JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("translations_not_deleted"), "error");
				}

                if(!JarisCMS\Group\GetPermission("view_content", JarisCMS\Security\GetCurrentUserGroup()))
                {
				    JarisCMS\System\GoToPage("admin/user/content");
                }
                else
                {
                    JarisCMS\System\GoToPage("admin/pages");
                }
			}
			elseif(isset($_REQUEST["btnNo"]))
			{
                if(JarisCMS\Group\GetPermission("edit_content", JarisCMS\Security\GetCurrentUserGroup()))
                {
				    JarisCMS\System\GoToPage("admin/pages/edit", array("uri"=>$_REQUEST["uri"]));
                }
                else
                {
                    JarisCMS\System\GoToPage($_REQUEST["uri"]);
                }
			}
		?>

		<form class="page-delete" method="post" action="<?php JarisCMS\URI\PrintURL("admin/pages/delete") ?>">
			<input type="hidden" name="uri" value="<?php print $_REQUEST["uri"] ?>" />
			<br />
			<div><?php print t("Are you sure you want to delete this?") ?>
			<div><b><?php print t("Title:") ?> <?php print t($page_data["title"]) ?></b></div>
			</div>
			<input class="form-submit" type="submit" name="btnYes" value="<?php print t("Yes") ?>" />
			<input class="form-submit" type="submit" name="btnNo" value="<?php print t("No") ?>" />
		</form>
	field;

	field: is_system
		1
	field;
row;
