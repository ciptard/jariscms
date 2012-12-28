<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the menu delete page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("Delete Menu") ?>
	field;
	field: content
		<?php
			JarisCMS\Security\ProtectPage(array("view_menus", "delete_menus"));

			if(isset($_REQUEST["btnYes"]))
			{
				//Store the current primary and secondary menus names
				$primary = JarisCMS\Setting\Get("primary_menu", "main");
				$secondary = JarisCMS\Setting\Get("secondary_menu", "main");
				
				$is_primary = $primary==$_REQUEST["menu"]?true:false;
				$is_secondary = $secondary==$_REQUEST["menu"] && $primary != ""?true:false;
				
				//Check if no primary or secondary menu configuration exist and checks if system default
				if(!$primary && $_REQUEST["menu"] == "primary")
				{
					$is_primary = true;
				}
				else if(!$secondary && $_REQUEST["menu"] == "secondary")
				{
					$is_secondary = true;
				}
				
				if(!$is_primary && !$is_secondary)
				{
					if(JarisCMS\Menu\Delete($_REQUEST["menu"]))
					{
						//Delete the menu block
						JarisCMS\Block\DeleteByField("menu_name", $_REQUEST["menu"]);
						
						JarisCMS\System\AddMessage(t("Menu successfully deleted."));
					}
					else
					{
						JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
					}
				}
				else
				{
					if($is_primary)
					{
						JarisCMS\System\AddMessage(t("Can't delete primary menu."), "error");
					}
					else
					{
						JarisCMS\System\AddMessage(t("Can't delete secondary menu."), "error");
					}
				}

				JarisCMS\System\GoToPage("admin/menus");
			}
			elseif(isset($_REQUEST["btnNo"]))
			{
				JarisCMS\System\GoToPage("admin/menus");
			}
		?>

		<form class="menus-delete" method="post" action="<?php JarisCMS\URI\PrintURL("admin/menus/delete") ?>">
			<input type="hidden" name="menu" value="<?php print $_REQUEST["menu"] ?>" />
			<div><?php print t("Are you sure you want to delete the menu?") ?>
			<div><b><?php print t("Name:") ?> <?php print t($_REQUEST["menu"]) ?></b></div>
			</div>
			<input class="form-submit" type="submit" name="btnYes" value="<?php print t("Yes") ?>" />
			<input class="form-submit" type="submit" name="btnNo" value="<?php print t("No") ?>" />
		</form>
	field;
	
	field: is_system
		1
	field;
row;
