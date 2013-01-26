<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the content types fields listing page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0

    field: title
        <?php print t("Contact Form Fields") ?>
    field;

    field: content
        <script>
            $(document).ready(function(){
                var fixHelper = function(e, ui) {
                    ui.children().each(function() {
                        $(this).width($(this).width());
                    });
                    return ui;
                };

                $(".types-list tbody").sortable({
                    cursor: 'crosshair',
                    helper: fixHelper,
                    handle: "a.sort-handle"
                });
            });
        </script>

        <?php
            JarisCMS\Security\ProtectPage(array("edit_content"));

            if(!JarisCMS\Page\IsOwner($_REQUEST["uri"]))
            {
                JarisCMS\Security\ProtectPage();
            }

            JarisCMS\System\AddScript("scripts/jquery-ui/jquery.ui.js");
            JarisCMS\System\AddScript("scripts/jquery-ui/jquery.ui.touch-punch.min.js");

            $arguments = array("uri"=>$_REQUEST["uri"]);

            JarisCMS\System\AddTab(t("Fields"), JarisCMS\Module\GetPageURI("admin/pages/contact-form/fields", "contact"), $arguments);

            JarisCMS\System\AddTab(t("Add Field"), JarisCMS\Module\GetPageURI("admin/pages/contact-form/fields/add", "contact"), $arguments, 1);

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

        ?>

        <form class="contact-form-fields" action="<?php print JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/pages/contact-form/fields", "contact")); ?>" method="post">
        <input type="hidden" name="uri" value="<?php print $_REQUEST["uri"] ?>" />

        <?php

            if(isset($_REQUEST["btnSave"]))
            {
                $saved = true;

                for($i=0; $i<count($_REQUEST["id"]); $i++)
                {
                    $new_field_data = JarisCMS\Module\ContactForms\GetFieldData($_REQUEST["id"][$i], $_REQUEST["uri"]);
                    $new_field_data["position"] = $i;

                    if(!JarisCMS\Module\ContactForms\EditField($_REQUEST["id"][$i], $new_field_data, $_REQUEST["uri"]))
                    {
                        $saved = false;
                        break;
                    }
                }

                if($saved)
                {
                    JarisCMS\System\AddMessage(t("Your changes have been saved."));
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
                }

                JarisCMS\System\GoToPage(JarisCMS\Module\GetPageURI("admin/pages/contact-form/fields", "contact"), array("uri"=>$_REQUEST["uri"]));
            }

            $fields_array = JarisCMS\Module\ContactForms\GetFields($_REQUEST["uri"]);

            if(!$fields_array)
            {
                print "<h3>" . t("No fields available click on Add Field to create one.") . "</h3>";
            }
            else
            {
                print "<table class=\"types-list\">\n";

                print "<thead><tr>\n";

                print "<td>" . t("Order") . "</td>\n";
                print "<td>" . t("Name") . "</td>\n";
                print "<td>" . t("Description") . "</td>\n";
                print "<td>" . t("Operation") . "</td>\n";

                print  "</tr></thead>\n";

                print "<tbody>\n";

                foreach($fields_array as $id=>$fields)
                {
                    print "<tr>\n";

                    print "<td>" .
                    "<a class=\"sort-handle\"></a>" .
                    "<input type=\"hidden\" name=\"id[]\" value=\"$id\" />" .
                    "<input type=\"hidden\" name=\"position[]\" value=\"{$fields['position']}\" />" .
                    "</td>\n";

                    print "<td>" . t($fields["name"]) . "</td>\n";

                    print "<td>" . t($fields["description"]) . "</td>\n";

                    $edit_url = JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/pages/contact-form/fields/edit", "contact"), array("id"=>$id, "uri"=>$_REQUEST["uri"]));
                    $delete_url = JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/pages/contact-form/fields/delete", "contact"), array("id"=>$id, "uri"=>$_REQUEST["uri"]));

                    $edit_text = t("Edit");
                    $delete_text = t("Delete");

                    print "<td>
                            <a href=\"$edit_url\">$edit_text</a>&nbsp;
                            <a href=\"$delete_url\">$delete_text</a>
                           </td>\n";

                    print "</tr>\n";
                }

                print "</tbody>\n";

                print "</table>\n";
            }
        ?>

        <div>
        <br />
        <input class="form-submit" type="submit" name="btnSave" value="<?php print t("Save") ?>" />
        &nbsp;
        <input class="form-submit" type="submit" name="btnCancel" value="<?php print t("Cancel") ?>" />
        </div>
        </form>
    field;

    field: is_system
        1
    field;
row;
