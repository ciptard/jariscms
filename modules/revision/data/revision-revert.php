<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the revisions revert page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("Revert to Revision"); ?>
	field;

	field: content
		<?php
            $_REQUEST["rev"] = intval($_REQUEST["rev"]);

            $revision = $_REQUEST["rev"];
            $revision_file = JarisCMS\Page\GeneratePath($_REQUEST["uri"]) . "/revisions/" . $revision . ".php";

            if(
                !isset($_REQUEST["uri"]) ||
                !isset($_REQUEST["rev"]) ||
                trim($_REQUEST["uri"]) == "" ||
                trim($_REQUEST["rev"]) == "" ||
                !file_exists(JarisCMS\Page\GeneratePath($_REQUEST["uri"])."/data.php") ||
                !file_exists($revision_file)
            )
                JarisCMS\System\GoToPage("access-denied");

            if(!JarisCMS\Page\IsOwner($_REQUEST["uri"]))
                JarisCMS\Security\ProtectPage();

            JarisCMS\Security\ProtectPage(array("revert_revisions"));


			if(isset($_REQUEST["btnYes"]))
			{
                $revision_data = JarisCMS\PHPDB\GetData(0, $revision_file);
                $page_data_path = JarisCMS\Page\GeneratePath($_REQUEST["uri"]) . "/data.php";

				if(JarisCMS\PHPDB\Edit(0, $revision_data, $page_data_path))
                {
                    JarisCMS\System\AddMessage(t("Revert to revision was successfull."));
                    JarisCMS\System\GoToPage($_REQUEST["uri"]);
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
                    JarisCMS\System\GoToPage(JarisCMS\Module\GetPageURI("revisions", "revision"), array("uri"=>$_REQUEST["uri"]));
                }
			}
			elseif(isset($_REQUEST["btnNo"]))
			{
                JarisCMS\System\GoToPage(JarisCMS\Module\GetPageURI("revisions", "revision"), array("uri"=>$_REQUEST["uri"]));
			}
		?>

    <form class="revision-revert" method="post" action="<?php JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("revision/revert", "revision")) ?>">
			<input type="hidden" name="uri" value="<?php print $_REQUEST["uri"] ?>" />
            <input type="hidden" name="rev" value="<?php print $_REQUEST["rev"] ?>" />
			<br />
			<div><?php print t("Are you sure you want to revert to this revision?") ?>
            <div><b><?php print t("Revision:") ?> <?php print t(date("F", $revision)) . " " . date("d, Y (h:i:s a)", $revision) ?></b></div>
			</div>
			<input class="form-submit" type="submit" name="btnYes" value="<?php print t("Yes") ?>" />
			<input class="form-submit" type="submit" name="btnNo" value="<?php print t("No") ?>" />
		</form>
	field;

	field: is_system
		1
	field;
row;
