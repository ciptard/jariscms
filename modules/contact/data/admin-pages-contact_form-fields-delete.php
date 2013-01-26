<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the contact form delete field page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Delete Contact Form Field") ?>
    field;

    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("edit_content"));

            if(!JarisCMS\Page\IsOwner($_REQUEST["uri"]))
            {
                JarisCMS\Security\ProtectPage();
            }

            $field_data = JarisCMS\Module\ContactForms\GetFieldData($_REQUEST["id"], $_REQUEST["uri"]);

            if(isset($_REQUEST["btnYes"]))
            {
                if(JarisCMS\Module\ContactForms\DeleteField($_REQUEST["id"], $_REQUEST["uri"]))
                {
                    JarisCMS\System\AddMessage(t("Contact form field successfully deleted."));
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
                }

                JarisCMS\System\GoToPage(JarisCMS\Module\GetPageURI("admin/pages/contact-form/fields", "contact"), array("uri"=>$_REQUEST["uri"]));
            }
            elseif(isset($_REQUEST["btnNo"]))
            {
                JarisCMS\System\GoToPage(JarisCMS\Module\GetPageURI("admin/pages/contact-form/fields", "contact"), array("uri"=>$_REQUEST["uri"]));
            }
        ?>

        <form class="contact-form-field-delete" method="post" action="<?php JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/pages/contact-form/fields/delete", "contact")) ?>">
            <input type="hidden" name="id" value="<?php print $_REQUEST["id"] ?>" />
            <input type="hidden" name="uri" value="<?php print $_REQUEST["uri"] ?>" />
            <br />
            <div><?php print t("Are you sure you want to delete the field?") ?>
            <div><b><?php print t("Field:") ?> <?php print t($field_data["name"]) ?></b></div>
            </div>
            <input class="form-submit" type="submit" name="btnYes" value="<?php print t("Yes") ?>" />
            <input class="form-submit" type="submit" name="btnNo" value="<?php print t("No") ?>" />
        </form>
    field;

    field: is_system
        1
    field;
row;
