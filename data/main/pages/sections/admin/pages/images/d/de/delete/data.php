<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the content images delete page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Delete Image") ?>
    field;
    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("delete_images"));
            
            if(!JarisCMS\Page\IsOwner($_REQUEST["uri"]))
            {
                JarisCMS\Security\ProtectPage();
            }

            $image_data = JarisCMS\Image\GetData($_REQUEST["id"], $_REQUEST["uri"]);

            if(isset($_REQUEST["btnYes"]))
            {
                if(JarisCMS\Image\Delete($_REQUEST["id"], $_REQUEST["uri"]))
                {
                    JarisCMS\System\AddMessage(t("Image successfully deleted."));
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
                }

                JarisCMS\System\GoToPage("admin/pages/images", array("uri"=>$_REQUEST["uri"]));
            }
            elseif(isset($_REQUEST["btnNo"]))
            {
                JarisCMS\System\GoToPage("admin/pages/images", array("uri"=>$_REQUEST["uri"]));
            }
        ?>

        <form class="images-delete" method="post" action="<?php JarisCMS\URI\PrintURL("admin/pages/images/delete") ?>">
            <input type="hidden" name="uri" value="<?php print $_REQUEST["uri"] ?>" />
            <input type="hidden" name="id" value="<?php print $_REQUEST["id"] ?>" />
            <div><?php print t("Are you sure you want to delete the image?") ?>
            <div>
                <a href="<?php print JarisCMS\URI\PrintURL("image/{$_REQUEST['uri']}/{$image_data['name']}"); ?>">
                    <img src="<?php print JarisCMS\URI\PrintURL("image/{$_REQUEST['uri']}/{$image_data['name']}", array("w"=>"100")); ?>" />
                </a>
            </div>
            </div>
            <input class="form-submit" type="submit" name="btnYes" value="<?php print t("Yes") ?>" />
            <input class="form-submit" type="submit" name="btnNo" value="<?php print t("No") ?>" />
        </form>
    field;

    field: is_system
        1
    field;
row;
