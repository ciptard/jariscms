<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the content add page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Add Poll") ?>
    field;

    field: content    
        <script type="text/javascript">
            row_id = 1;
            
            $(document).ready(function() {
                $("#add-item").click(function(){
                    
                    row = "<tr id=\"table-row-" + row_id + "\">";
                    row += "<td style=\"width: auto\"><input style=\"width: 100%\" type=\"text\" name=\"option_name[]\" /></td>";
                    row += "<td style=\"width: auto; text-align: center\"><a href=\"javascript:remove_row(" + row_id + ")\"><?php print t("remove")?></a></td>";
                    row += "</tr>";
                    
                    $("#items-table > tbody").append($(row).hide().fadeIn("slow"));
                    
                    row_id++;
                });
            });
            
            function remove_row(id)
            {
                $("#table-row-" + id).fadeOut("slow", function(){
                    $(this).remove();
                });
            }
        </script>
            
        <?php
            JarisCMS\Security\ProtectPage(array("add_content"));
            
            if(!JarisCMS\Group\GetTypePermission("poll", JarisCMS\Security\GetCurrentUserGroup(), JarisCMS\Security\GetCurrentUser()))
            {
                JarisCMS\Security\ProtectPage();
            }

            if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("add-poll"))
            {
                //Trim uri spaces
                $_REQUEST["uri"] = trim($_REQUEST["uri"]);
                
                $fields["title"] = $_REQUEST["title"];
                $fields["content"] = $_REQUEST["content"];
                $fields["duration"] = $_REQUEST["duration"];
                $fields["option_name"] = serialize($_REQUEST["option_name"]);
                
                $option_values = array();
                foreach($_REQUEST["option_name"] as $option)
                {
                    $option_values[] = 0;
                }
                
                $fields["option_value"] = serialize($option_values);
                
                if(JarisCMS\Group\GetPermission("add_edit_meta_content", JarisCMS\Security\GetCurrentUserGroup()))
                {
                    $fields["meta_title"] = $_REQUEST["meta_title"];
                    $fields["description"] = $_REQUEST["description"];
                    $fields["keywords"] = $_REQUEST["keywords"];
                }
                
                if(JarisCMS\Group\GetPermission("select_content_groups", JarisCMS\Security\GetCurrentUserGroup()))
                {
                    $fields["groups"] = $_REQUEST["groups"];
                }
                else
                {
                    $fields["groups"] = array();
                }
                
                $categories = array();
                foreach(JarisCMS\Category\GetList("poll") as $machine_name=>$values)
                {
                    if(isset($_REQUEST[$machine_name]))
                    {
                        $categories[$machine_name] = $_REQUEST[$machine_name];
                    }
                }
                
                $fields["categories"] = $categories;
                if(JarisCMS\Group\GetPermission("input_format_content", JarisCMS\Security\GetCurrentUserGroup()) || JarisCMS\Security\IsAdminLogged())
                {
                    $fields["input_format"] = $_REQUEST["input_format"];
                }
                else
                {
                    $fields["input_format"] = JarisCMS\Type\GetDefaultInputFormat("poll");
                }
                $fields["created_date"] = time();
                $fields["author"] = JarisCMS\Security\GetCurrentUser();
                $fields["type"] = "poll";
                
                JarisCMS\Field\AppendFieldsToType($fields["type"], $fields);

                //Stores the uri of the page to display the edit page after saving.
                $uri = "";
                
                if(!JarisCMS\Group\GetPermission("manual_uri_content", JarisCMS\Security\GetCurrentUserGroup()) || $_REQUEST["uri"] == "")
                {
                    $_REQUEST["uri"] = JarisCMS\URI\GenerateForType($fields["type"], $fields["title"], $fields["author"]);
                }

                if(JarisCMS\Page\Create($_REQUEST["uri"], $fields, $uri))
                {
                    JarisCMS\Module\Polls\Core\SQLite\Add($uri, $fields["created_date"]);
                    
                    JarisCMS\Module\Polls\Core\Recent\Add($uri, $fields["title"]);
                    
                    JarisCMS\System\AddMessage(t("The poll was successfully added."));
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
                }

                JarisCMS\System\GoToPage(JarisCMS\Module\GetPageURI("admin/polls/edit", "polls"), array("uri"=>$uri));
            }
            elseif(isset($_REQUEST["btnCancel"]))
            {
                JarisCMS\System\GoToPage("");
            }

            $parameters["name"] = "add-poll";
            $parameters["class"] = "add-poll";
            $parameters["action"] = JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/polls/add", "polls"));
            $parameters["method"] = "post";
            
            $categories = JarisCMS\Category\GetList("poll");
            if(count($categories) > 0)
            {
                $fields_categories = JarisCMS\Category\GenerateFieldList(null, null, "poll");
                $fieldset[] = array("fields"=>$fields_categories, "name"=>t("Categories"), "collapsible"=>true);
            }

            $fields[] = array("type"=>"text", "name"=>"title", "value"=>$_REQUEST["title"], "label"=>t("Title:"), "id"=>"title", "required"=>true);
            $fields[] = array("type"=>"textarea", "name"=>"content", "value"=>$_REQUEST["content"], "label"=>t("Content:"), "id"=>"content");
            $fields[] = array("type"=>"text", "name"=>"duration", "value"=>$_REQUEST["duration"]?$_REQUEST["duration"]:"7", "label"=>t("Duration:"), "id"=>"duration", "description"=>t("The amount of days the poll is going to be active. Leave blank for unlimited time."));
            
            $fieldset[] = array("fields"=>$fields);
            
            $items = "<table id=\"items-table\" style=\"width: 100%\">";
            $items .= "<thead>";
            $items .= "<tr>";
            $items .= "<td style=\"width: auto\"><b>" . t("Name") . "</b></td>";
            $items .= "<td style=\"width: auto\"></td>";
            $items .= "</tr>";
            $items .= "</thead>";            
            $items .= "<tbody>";
            $items .= "<tr id=\"table-row-0\">";
            $items .= "<td style=\"width: auto\"><input style=\"width: 100%\" type=\"text\" name=\"option_name[]\" /></td>";
            $items .= "<td style=\"width: auto; text-align: center\"><a href=\"javascript:remove_row(0)\">" . t("remove") . "</a></td>";
            $items .= "</tr>";
            $items .= "</tbody>";
            $items .= "</table>";
            $items .= "<a id=\"add-item\" style=\"cursor: pointer\">" . t("Add another option") . "</a>";
            
            $fields_items[] = array("type"=>"other", "html_code"=>$items);
            
            $fieldset[] = array("name"=>t("Options"), "fields"=>$fields_items, "collapsible"=>true);
            
            if(JarisCMS\Group\GetPermission("add_edit_meta_content", JarisCMS\Security\GetCurrentUserGroup()))
            {
                $fields_meta[] = array("type"=>"textarea", "name"=>"meta_title", "value"=>$_REQUEST["meta_title"], "label"=>t("Title:"), "id"=>"meta_title", "description"=>t("Overrides the original page title on search engine results. Leave blank for default."));
                $fields_meta[] = array("type"=>"textarea", "name"=>"description", "value"=>$_REQUEST["description"], "label"=>t("Description:"), "id"=>"description", "description"=>t("Used to generate the meta description for search engines. Leave blank for default."));
                $fields_meta[] = array("type"=>"textarea", "name"=>"keywords", "value"=>$_REQUEST["keywords"], "label"=>t("Keywords:"), "id"=>"keywords", "description"=>t("List of words seperated by comma (,) used to generate the meta keywords for search engines. Leave blank for default."));
            
                $fieldset[] = array("fields"=>$fields_meta, "name"=>t("Meta tags"), "collapsible"=>true, "collapsed"=>true);
            }
            
            if(JarisCMS\Group\GetPermission("input_format_content", JarisCMS\Security\GetCurrentUserGroup()) || JarisCMS\Security\IsAdminLogged())
            {
                $fields_inputformats = array();
                foreach(JarisCMS\InputFormat\GetAll() as $machine_name=>$fields_formats)
                {
                    
                    $fields_inputformats[] = array("type"=>"radio", "checked"=>$machine_name=="full_html"?true:false, "name"=>"input_format", "description"=>$fields_formats["description"], "value"=>array($fields_formats["title"]=>$machine_name));
                }            
                $fieldset[] = array("fields"=>$fields_inputformats, "name"=>t("Input Format"));
            }
            
            $extra_fields = JarisCMS\Field\GenerateArrayFromType("poll");
            
            if($extra_fields)
            {
                $fieldset[] = array("fields"=>$extra_fields);
            }
            
            if(JarisCMS\Group\GetPermission("select_content_groups", JarisCMS\Security\GetCurrentUserGroup()))
            {
                $fieldset[] = array("fields"=>JarisCMS\Group\GetListForFields(), "name"=>t("Users Access"), "collapsed"=>true, "collapsible"=>true, "description"=>t("Select the groups that can see this content. Don't select anything to display content to everyone."));
            }
            
            if(JarisCMS\Group\GetPermission("manual_uri_content", JarisCMS\Security\GetCurrentUserGroup()))
            {
                $fields_other[] = array("type"=>"text", "name"=>"uri", "label"=>t("Uri:"), "id"=>"uri", "value"=>$_REQUEST["uri"], "description"=>t("The relative path to access the page, for example: section/page, section. Leave empty to auto-generate."));
            }

            $fields_other[] = array("type"=>"submit", "name"=>"btnSave", "value"=>t("Save"));
            $fields_other[] = array("type"=>"submit", "name"=>"btnCancel", "value"=>t("Cancel"));

            $fieldset[] = array("fields"=>$fields_other);

            print JarisCMS\Form\Generate($parameters, $fieldset);
        ?>
    field;

    field: is_system
        1
    field;
row;