<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the administration page for codemirror.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Codemirror Settings") ?>
    field;

    field: content
        <style>
            .groups td
            {
                width: auto;
                padding: 5px;
                border-bottom: solid 1px #000;
            }

            .groups thead td
            {
                width: auto;
                font-weight:  bold;
                border-bottom: 0;
            }
        </style>

        <?php
            JarisCMS\Security\ProtectPage(array("edit_settings"));

            $classes = unserialize(JarisCMS\Setting\Get("teaxtarea_id", "codemirror"));
            $forms_to_display = unserialize(JarisCMS\Setting\Get("forms", "codemirror"));
            $groups = unserialize(JarisCMS\Setting\Get("groups", "codemirror"));

            if(isset($_REQUEST["btnSave"], $_REQUEST["group"]))
            {
                $classes[$_REQUEST["group"]] = $_REQUEST["teaxtarea_id"];
                $forms_to_display[$_REQUEST["group"]] = $_REQUEST["forms"];
                $groups[$_REQUEST["group"]] = $_REQUEST["groups"];

                if(JarisCMS\Setting\Save("teaxtarea_id", serialize($classes), "codemirror"))
                {
                    JarisCMS\Setting\Save("forms", serialize($forms_to_display), "codemirror");
                    JarisCMS\Setting\Save("groups", serialize($groups), "codemirror");

                    JarisCMS\System\AddMessage(t("Your changes have been saved."));
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"));
                }

                JarisCMS\System\GoToPage(JarisCMS\Module\GetPageURI("admin/settings/codemirror", "codemirror"));
            }

            print "<table class=\"groups\">\n";
            print "<thead>\n";
            print "<tr>\n";

            print "<td>\n";
            print t("Groups");
            print "</td>\n";

            print "<td>\n";
            print t("Description");
            print "</td>\n";

            print "<td>\n";
            print "</td>\n";

            print "</tr>\n";
            print "</thead>\n";

            $groups_list = JarisCMS\Group\GetList();
            $groups_list[] = "guest";

            foreach($groups_list as $group)
            {
                $group_data = JarisCMS\Group\GetData($group);

                print "<tr>\n";

                print "<td>\n";
                print $group_data["name"];
                print "</td>\n";

                print "<td>\n";
                print $group_data["description"];
                print "</td>\n";

                $edit_url = JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/settings/codemirror", "codemirror"), array("group"=>$group));

                print "<td>\n";
                print "<a href=\"$edit_url\">" . t("edit") . "</a>";
                print "</td>\n";

                print "</tr>\n";
            }

            print "</table>";

            print "<br />";

            if(isset($_REQUEST["group"]))
            {
                $parameters["name"] = "codemirror-settings";
                $parameters["class"] = "codemirror-settings";
                $parameters["action"] = JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/settings/codemirror", "codemirror"));
                $parameters["method"] = "post";

                $fields_enable[] = array("type"=>"other", "html_code"=>"<br />");
                $fields_enable[] = array("type"=>"checkbox", "checked"=>$groups[$_REQUEST["group"]], "name"=>"groups", "label"=>t("Enable Codemirror?"), "id"=>"groups");
                $fieldset[] = array("fields"=>$fields_enable);

                $fields_pages[] = array("type"=>"textarea", "name"=>"teaxtarea_id", "label"=>t("Textarea Id:"), "id"=>"teaxtarea_id", "value"=>$classes[$_REQUEST["group"]]?$classes[$_REQUEST["group"]]:"content, return", "description"=>t("List of textarea id's seperated by comma (,)."));
                $fields_pages[] = array("type"=>"textarea", "name"=>"forms", "label"=>t("Form names:"), "id"=>"forms", "value"=>$forms_to_display[$_REQUEST["group"]]?$forms_to_display[$_REQUEST["group"]]:"add-page-pages,edit-page-pages,translate-page,add-page-block,block-page-edit,add-block,block-edit,add-page-block-page");

                $fieldset[] = array("fields"=>$fields_pages, "name"=>"Forms to display", "description"=>t("List of form names seperated by comma (,)."));

                $fields[] = array("type"=>"hidden", "name"=>"group", "value"=>$_REQUEST["group"]);
                $fields[] = array("type"=>"submit", "name"=>"btnSave", "value"=>t("Save"));
                $fields[] = array("type"=>"submit", "name"=>"btnCancel", "value"=>t("Cancel"));

                $fieldset[] = array("fields"=>$fields);

                $group_data = JarisCMS\Group\GetData($_REQUEST["group"]);
                print "<b>" . t("Selected group:") . "</b> " . $group_data["name"];
                print JarisCMS\Form\Generate($parameters, $fieldset);
            }

        ?>
    field;

    field: is_system
        1
    field;
row;
