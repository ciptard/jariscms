<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the input format delete page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("Delete Input Format") ?>
	field;
	field: content
		<?php
			JarisCMS\Security\ProtectPage(array("delete_input_formats"));

			$input_format_data = JarisCMS\InputFormat\GetData($_REQUEST["input_format"]);

			if(isset($_REQUEST["btnYes"]))
			{
				$message = JarisCMS\InputFormat\Delete($_REQUEST["input_format"]);

				if($message == "true")
				{
					JarisCMS\System\AddMessage(t("Input format successfully deleted."));
				}
				else
				{
					JarisCMS\System\AddMessage($message, "error");
				}

				JarisCMS\System\GoToPage("admin/input-formats");
			}
			elseif(isset($_REQUEST["btnNo"]))
			{
				JarisCMS\System\GoToPage("admin/input-formats");
			}
		?>

		<form class="input-format-delete" method="post" action="<?php JarisCMS\URI\PrintURL("admin/input-formats/delete") ?>">
			<input type="hidden" name="input_format" value="<?php print $_REQUEST["input_format"] ?>" />
			<br />
			<div><?php print t("Are you sure you want to delete the input format?") ?>
			<div><b><?php print t("Input format:") ?> <?php print t($input_format_data["name"]) ?></b></div>
			</div>
			<input class="form-submit" type="submit" name="btnYes" value="<?php print t("Yes") ?>" />
			<input class="form-submit" type="submit" name="btnNo" value="<?php print t("No") ?>" />
		</form>
	field;

	field: is_system
		1
	field;
row;
