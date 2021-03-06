<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Edit Listing") ?>
    field;

    field: content        
        <?php
            JarisCMS\Security\ProtectPage(array("edit_content"));
            
            if(!JarisCMS\Page\IsOwner(trim($_REQUEST["actual_uri"]) != ""?$_REQUEST["actual_uri"]:$_REQUEST["uri"]))
            {
                JarisCMS\Security\ProtectPage();
            }

            if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("edit-listing"))
            {
                //Check if client is trying to submit content to a system page sending variables thru GET
                if(JarisCMS\System\IsSystemPage($_REQUEST["actual_uri"]))
                {
                    JarisCMS\System\AddMessage(t("The content you was trying to edit is a system page."), "error");
                    JarisCMS\System\GoToPage("admin/pages");
                }
                
                $fields = JarisCMS\Page\GetData($_REQUEST["actual_uri"]);

                $fields["title"] = $_REQUEST["title"];
                $fields["content"] = $_REQUEST["content"];
                
                if(JarisCMS\Group\GetPermission("add_edit_meta_content", JarisCMS\Security\GetCurrentUserGroup()))
                {
                    $fields["meta_title"] = $_REQUEST["meta_title"];
                    $fields["description"] = $_REQUEST["description"];
                    $fields["keywords"] = $_REQUEST["keywords"];
                }
                
                $fields["filter_types"] = serialize($_REQUEST["filter_types"]);
                $fields["filter_authors"] = $_REQUEST["filter_authors"];
                
                $filter_categories_list = JarisCMS\Category\GetList();
                $filter_categories = array();
                foreach($filter_categories_list as $machine_name=>$data)
                {
                    if(isset($_REQUEST["filter_category_$machine_name"]))
                    {
                        $filter_categories[$machine_name] = $_REQUEST["filter_category_$machine_name"];
                    }
                }
                $fields["filter_categories"] = serialize($filter_categories);
                
                $fields["filter_ordering"] = $_REQUEST["filter_ordering"];
                $fields["layout"] = $_REQUEST["layout"];
                $fields["display_title"] = $_REQUEST["display_title"];
                $fields["display_summary"] = $_REQUEST["display_summary"];
                $fields["display_more"] = $_REQUEST["display_more"];
                $fields["maximum_words"] = intval($_REQUEST["maximum_words"]);
                $fields["display_navigation"] = $_REQUEST["display_navigation"];
                $fields["results_per_page"] = intval($_REQUEST["results_per_page"]);
                $fields["results_per_row"] = intval($_REQUEST["results_per_row"]);
                $fields["thumbnail_show"] = $_REQUEST["thumbnail_show"];
                $fields["thumbnail_width"] = intval($_REQUEST["thumbnail_width"]);
                $fields["thumbnail_height"] = intval($_REQUEST["thumbnail_height"]);
                $fields["thumbnail_bg"] = $_REQUEST["thumbnail_bg"];
                $fields["thumbnail_keep_aspectratio"] = $_REQUEST["thumbnail_keep_aspectratio"];
                
                if(JarisCMS\Group\GetPermission("select_content_groups", JarisCMS\Security\GetCurrentUserGroup()))
                {
                    $fields["groups"] = $_REQUEST["groups"];
                }
                
                $categories = array();
                foreach(JarisCMS\Category\GetList("listing") as $machine_name=>$values)
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
                
                $fields["last_edit_by"] = JarisCMS\Security\GetCurrentUser();
                $fields["last_edit_date"] = time();
                
                JarisCMS\Field\AppendFieldsToType($fields["type"], $fields);
                
                if(!JarisCMS\Group\GetPermission("manual_uri_content", JarisCMS\Security\GetCurrentUserGroup()) || $_REQUEST["uri"] == "")
                {
                    $_REQUEST["uri"] = JarisCMS\URI\GenerateForType($fields["type"], $fields["title"], $fields["author"]);
                }

                if(JarisCMS\Page\Edit($_REQUEST["actual_uri"], $fields))
                {
                    //Update all translations
                    $new_page_data = JarisCMS\Page\GetData($_REQUEST["actual_uri"]);
                    foreach(JarisCMS\Language\GetAll() as $code=>$name)
                    {
                        $translation_path = dt(JarisCMS\Page\GeneratePath($_REQUEST["actual_uri"]), $code);
                        $original_path = JarisCMS\Page\GeneratePath($_REQUEST["actual_uri"]);
                        
                        if($translation_path != $original_path)
                        {
                            $translation_data = JarisCMS\Page\GetData($_REQUEST["actual_uri"], $code);
                            
                            $new_page_data["title"] = $translation_data["title"];
                            $new_page_data["content"] = $translation_data["content"];
                            
                            JarisCMS\Language\TranslatePage($_REQUEST["actual_uri"], $new_page_data, $code);
                        }
                    }
                    
                    //Move page to new location
                    if($_REQUEST["actual_uri"] != $_REQUEST["uri"])
                    {
                        JarisCMS\Page\Move($_REQUEST["actual_uri"], $_REQUEST["uri"]);

                        //Also move its translations on the language directory
                        if(JarisCMS\Language\MovePageTranslations($_REQUEST["actual_uri"], $_REQUEST["uri"]))
                        {
                            JarisCMS\System\AddMessage(t("Translations repositioned."));
                        }
                        else
                        {
                            JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("translations_not_moved"), "error");
                        }
                    }

                    JarisCMS\System\AddMessage(t("Your changes have been successfully saved."));
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
                }

                JarisCMS\System\GoToPage(JarisCMS\Module\GetPageURI("admin/pages/listing/edit", "listing"), array("uri"=>$_REQUEST["uri"]));
            }
            elseif(isset($_REQUEST["btnCancel"]))
            {
                JarisCMS\System\GoToPage(JarisCMS\Module\GetPageURI("admin/pages/listing/edit", "listing"), array("uri"=>$_REQUEST["actual_uri"]));
            }

            $arguments["uri"] = $_REQUEST["uri"];

            //Tabs
            if(JarisCMS\Group\GetPermission("edit_content", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("Edit listing"), JarisCMS\Module\GetPageURI("admin/pages/listing/edit", "listing"), $arguments);
            }
            JarisCMS\System\AddTab(t("View listing"), $_REQUEST["uri"]);
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
            $page_data["filter_types"] = unserialize($page_data["filter_types"]);
            $page_data["filter_categories"] = unserialize($page_data["filter_categories"]);

            $parameters["name"] = "edit-listing";
            $parameters["class"] = "edit-listing";
            $parameters["action"] = JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/pages/listing/edit", "listing"));
            $parameters["method"] = "post";
            
            $categories = JarisCMS\Category\GetList("listing");
            if(count($categories) > 0)
            {
                $fields_categories = JarisCMS\Category\GenerateFieldList($page_data["categories"], null, "listing");
                $fieldset[] = array("fields"=>$fields_categories, "name"=>t("Categories"), "collapsible"=>true);
            }

            $fields[] = array("type"=>"hidden", "name"=>"actual_uri", "value"=>$_REQUEST["actual_uri"]?$_REQUEST["actual_uri"]:$_REQUEST["uri"]);
            $fields[] = array("type"=>"text", "value"=>$page_data["title"], "name"=>"title", "label"=>t("Title:"), "id"=>"title", "required"=>true);
            $fields[] = array("type"=>"textarea", "value"=>$page_data["content"], "name"=>"content", "label"=>t("Content:"), "id"=>"content");
            
            $fieldset[] = array("fields"=>$fields);
            
            $criteria_types = array();
            $criteria_types_list = JarisCMS\Type\GetList(JarisCMS\Security\GetCurrentUserGroup());
            foreach($criteria_types_list as $machine_name=>$type_fields)
            {
                $criteria_types[t(trim($type_fields["name"]))] = $machine_name;
            }
            
            $fields_criteria[] = array("type"=>"select", "name"=>"filter_types[]", "id"=>"filter_types", "label"=>t("Content type:"), "multiple"=>true, "selected"=>$_REQUEST["filter_types"]?$_REQUEST["filter_types"]:$page_data["filter_types"], "value"=>$criteria_types);
            $fields_criteria[] = array("type"=>"textarea", "name"=>"filter_authors", "id"=>"filter_authors", "label"=>t("Authors:"), "value"=>$_REQUEST["filter_authors"]?$_REQUEST["filter_authors"]:$page_data["filter_authors"], "description"=>t("List of usernames separated by comma, for example: admin, joe, john"));
            $fields_criteria[] = array("type"=>"other", "html_code"=>"<h2>".t("Categories:")."</h2><hr />");
            $fields_criteria = array_merge($fields_criteria, JarisCMS\Module\ContentListing\CategoryFields($page_data["filter_categories"], null));
            
            $fieldset[] = array("fields"=>$fields_criteria, "name"=>t("Filters"), "collapsible"=>true, "collapsed"=>true);
            
            $ordering[t("Newest to oldest")] = "date_desc";
            $ordering[t("Oldest to newest")] = "date_asc";
            $ordering[t("Title ascendent")] = "title_asc";
            $ordering[t("Title descendent")] = "title_desc";
            $ordering[t("Most viewed all time")] = "views_desc";
            $ordering[t("Most viewed today")] = "views_today_desc";
            $ordering[t("Most viewed this week")] = "views_week_desc";
            $ordering[t("Most viewed this month")] = "views_month_desc";
            
            $fields_ordering[] = array("type"=>"radio", "name"=>"filter_ordering", "value"=>$ordering, "checked"=>$_REQUEST["filter_ordering"]?$_REQUEST["filter_ordering"]:$page_data["filter_ordering"]);
            
            $fieldset[] = array("fields"=>$fields_ordering, "name"=>t("Ordering"), "collapsible"=>true, "collapsed"=>false);
            
            $teaser_checked = $page_data["layout"]=="teaser"?"checked":"";
            $grid_checked = $page_data["layout"]=="grid"?"checked":"";
            $list_checked = $page_data["layout"]=="list"?"checked":"";
            
            $layout = "<table>";
            $layout .= "<thead>";
            $layout .= "<tr>";
            $layout .= "<td></td>";
            $layout .= "<td>".t("Teaser")."</td>";
            $layout .= "<td></td>";
            $layout .= "<td>".t("Grid")."</td>";
            $layout .= "<td></td>";
            $layout .= "<td>".t("List")."</td>";
            $layout .= "</tr>";
            $layout .= "</thead>";
            $layout .= "<tr>";
            $layout .= "<td><input $teaser_checked type=\"radio\" name=\"layout\" value=\"teaser\" /></td>";
            $layout .= "<td><img src=\"".JarisCMS\URI\PrintURL("modules/listing/images/listing-teaser.png")."\" /></td>";
            $layout .= "<td><input $grid_checked type=\"radio\" name=\"layout\" value=\"grid\" /></td>";
            $layout .= "<td><img src=\"".JarisCMS\URI\PrintURL("modules/listing/images/listing-grid.png")."\" /></td>";
            $layout .= "<td><input $list_checked type=\"radio\" name=\"layout\" value=\"list\" /></td>";
            $layout .= "<td><img src=\"".JarisCMS\URI\PrintURL("modules/listing/images/listing-list.png")."\" /></td>";
            $layout .= "</tr>";
            $layout .= "</table><hr />";
            
            $fields_layout[] = array("type"=>"other", "html_code"=>$layout);
            $fields_layout[] = array("type"=>"checkbox", "name"=>"display_title", "id"=>"display_title", "label"=>t("Display title?"), "checked"=>$_REQUEST["display_title"]?$_REQUEST["display_title"]:$page_data["display_title"], "value"=>true);
            $fields_layout[] = array("type"=>"other", "html_code"=>"<br />");
            $fields_layout[] = array("type"=>"checkbox", "name"=>"display_summary", "id"=>"display_summary", "label"=>t("Display summary?"), "checked"=>$_REQUEST["display_summary"]?$_REQUEST["display_summary"]:$page_data["display_summary"], "value"=>true);
            $fields_layout[] = array("type"=>"other", "html_code"=>"<br />");
            $fields_layout[] = array("type"=>"checkbox", "name"=>"display_more", "id"=>"display_more", "label"=>t("Display view more link?"), "checked"=>$_REQUEST["display_more"]?$_REQUEST["display_more"]:$page_data["display_more"], "value"=>true);
            $fields_layout[] = array("type"=>"text", "name"=>"maximum_words", "value"=>$_REQUEST["maximum_words"]?$_REQUEST["maximum_words"]:$page_data["maximum_words"], "label"=>t("Maximum amount of words:"), "id"=>"maximum_words", "required"=>true, "description"=>t("Amount of words displayed of the page summary."));
            $fields_layout[] = array("type"=>"other", "html_code"=>"<br />");
            $fields_layout[] = array("type"=>"checkbox", "name"=>"display_navigation", "id"=>"display_navigation", "label"=>t("Display navigation?"), "checked"=>$_REQUEST["display_navigation"]?$_REQUEST["display_navigation"]:$page_data["display_navigation"], "value"=>true);
            $fields_layout[] = array("type"=>"text", "name"=>"results_per_page", "value"=>$_REQUEST["results_per_page"]?$_REQUEST["results_per_page"]:$page_data["results_per_page"], "label"=>t("Results per page:"), "id"=>"results_per_page", "required"=>true, "description"=>t("The amount of results to display in case the navigation is enabled."));
            $fields_layout[] = array("type"=>"text", "name"=>"results_per_row", "value"=>$_REQUEST["results_per_row"]?$_REQUEST["results_per_row"]:$page_data["results_per_row"], "label"=>t("Results per row:"), "id"=>"results_per_row", "required"=>true, "description"=>t("The amount of columns per row in case grid was select as layout."));
            
            $fieldset[] = array("fields"=>$fields_layout, "name"=>t("Layout"), "collapsible"=>true, "collapsed"=>false);
            
            $fields_thumbnail[] = array("type"=>"checkbox", "name"=>"thumbnail_show", "id"=>"thumbnail_show", "label"=>t("Show thumbnail?"), "checked"=>$_REQUEST["thumgnail_show"]?$_REQUEST["thumbnail_show"]:$page_data["thumbnail_show"], "value"=>true);
            $fields_thumbnail[] = array("type"=>"text", "name"=>"thumbnail_width", "id"=>"thumbnail_width", "label"=>t("Width:"), "value"=>$_REQUEST["thumbnail_width"]?$_REQUEST["thumbnail_width"]:$page_data["thumbnail_width"], "required"=>true, "description"=>t("The width of the thumbnail in pixels."));
            $fields_thumbnail[] = array("type"=>"text", "name"=>"thumbnail_height", "id"=>"thumbnail_height", "label"=>t("Height:"), "value"=>$_REQUEST["thumbnail_height"]?$_REQUEST["thumbnail_height"]:$page_data["thumbnail_height"], "description"=>t("The height of the thumbnail in pixels."));
            $fields_thumbnail[] = array("type"=>"color", "name"=>"thumbnail_bg", "id"=>"thumbnail_bg", "label"=>t("Background color:"), "value"=>$_REQUEST["thumbnail_bg"]?$_REQUEST["thumbnail_bg"]:$page_data["thumbnail_bg"], "description"=>t("The background color of the thumbnail in case is neccesary."));
            $fields_thumbnail[] = array("type"=>"other", "html_code"=>"<br />");
            $fields_thumbnail[] = array("type"=>"checkbox", "name"=>"thumbnail_keep_aspectratio", "id"=>"thumbnail_keep_aspectratio", "label"=>t("Keep aspect ratio?"), "checked"=>$_REQUEST["thumbnail_keep_aspectratio"]?$_REQUEST["thumbnail_keep_aspectratio"]:$page_data["thumbnail_keep_aspectratio"], "value"=>true);
            
            $fieldset[] = array("fields"=>$fields_thumbnail, "name"=>t("Thumbnail"), "collapsible"=>true, "collapsed"=>false);
            
            if(JarisCMS\Group\GetPermission("add_edit_meta_content", JarisCMS\Security\GetCurrentUserGroup()))
            {
                $fields_meta[] = array("type"=>"textarea", "value"=>$page_data["meta_title"], "name"=>"meta_title", "label"=>t("Title:"), "id"=>"meta_title", "description"=>t("Overrides the original page title on search engine results. Leave blank for default."));
                $fields_meta[] = array("type"=>"textarea", "value"=>$page_data["description"], "name"=>"description", "label"=>t("Description:"), "id"=>"description", "description"=>t("Used to generate the meta description for search engines. Leave blank for default."));
                $fields_meta[] = array("type"=>"textarea", "value"=>$page_data["keywords"], "name"=>"keywords", "label"=>t("Keywords:"), "id"=>"keywords", "description"=>t("List of words seperated by comma (,) used to generate the meta keywords for search engines. Leave blank for default."));
            
                $fieldset[] = array("fields"=>$fields_meta, "name"=>t("Meta tags"), "collapsible"=>true, "collapsed"=>true);
            }
            
            if(JarisCMS\Group\GetPermission("input_format_content", JarisCMS\Security\GetCurrentUserGroup()) || JarisCMS\Security\IsAdminLogged())
            {
                $fields_inputformats = array();
                foreach(JarisCMS\InputFormat\GetAll() as $machine_name=>$fields_formats)
                {
                    
                    $fields_inputformats[] = array("type"=>"radio", "checked"=>$machine_name==$page_data["input_format"]?true:false, "name"=>"input_format", "description"=>$fields_formats["description"], "value"=>array($fields_formats["title"]=>$machine_name));
                }            
                $fieldset[] = array("fields"=>$fields_inputformats, "name"=>t("Input Format"));
            }
            
            $extra_fields = JarisCMS\Field\GenerateArrayFromType("listing", $page_data);
            
            if($extra_fields)
            {
                $fieldset[] = array("fields"=>$extra_fields);
            }
            
            if(JarisCMS\Group\GetPermission("select_content_groups", JarisCMS\Security\GetCurrentUserGroup()))
            {
                $fieldset[] = array("fields"=>JarisCMS\Group\GetListForFields($page_data["groups"]), "name"=>t("Users Access"), "collapsed"=>true, "collapsible"=>true, "description"=>t("Select the groups that can see this content. Don't select anything to display content to everyone."));
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