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
        <?php print t("Add Listing Block") ?>
    field;

    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("add_blocks"));

            if(isset($_REQUEST["btnSave"])  && !JarisCMS\Form\CheckFields("listing-blocks-add"))
            {
                $fields["description"] = $_REQUEST["description"];
                $fields["title"] = $_REQUEST["title"];
                $fields["groups"] = $_REQUEST["groups"];
                $fields["themes"] = $_REQUEST["themes"];
                $fields["order"] = 0;
                $fields["display_rule"] = $_REQUEST["display_rule"];
                $fields["pages"] = $_REQUEST["pages"];
                
                if(JarisCMS\Group\GetPermission("return_code_blocks", JarisCMS\Security\GetCurrentUserGroup()) || JarisCMS\Security\IsAdminLogged())
                {
                    $fields["return"] = $_REQUEST["return"];
                }
                
                $fields["content"] = "<div></div>"; //add dummy content
                
                $fields["pre_content"] = $_REQUEST["pre_content"];
                $fields["sub_content"] = $_REQUEST["sub_content"];
                
                $fields["is_listing_block"] = true;
                
                $fields["filter_types"] = serialize($_REQUEST["filter_types"]);
                $fields["filter_authors"] = $_REQUEST["filter_authors"];
                
                $fields["related_to_current_page"] = $_REQUEST["related_to_current_page"];
                
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
                $fields["display_title"] = $_REQUEST["display_title"];
                $fields["display_summary"] = $_REQUEST["display_summary"];
                $fields["display_more"] = $_REQUEST["display_more"];
                $fields["maximum_words"] = intval($_REQUEST["maximum_words"]);
                $fields["results_to_show"] = intval($_REQUEST["results_to_show"]);
                $fields["thumbnail_show"] = $_REQUEST["thumbnail_show"];
                $fields["thumbnail_width"] = intval($_REQUEST["thumbnail_width"]);
                $fields["thumbnail_height"] = intval($_REQUEST["thumbnail_height"]);
                $fields["thumbnail_bg"] = $_REQUEST["thumbnail_bg"];
                $fields["thumbnail_keep_aspectratio"] = $_REQUEST["thumbnail_keep_aspectratio"];

                if(JarisCMS\Block\Add($fields, $_REQUEST["position"], $page = ""))
                {
                    JarisCMS\System\AddMessage(t("The block was successfully created."));
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
                }

                JarisCMS\System\GoToPage("admin/blocks");
            }
            elseif(isset($_REQUEST["btnCancel"]))
            {
                JarisCMS\System\GoToPage("admin/blocks");
            }

            $parameters["name"] = "listing-blocks-add";
            $parameters["class"] = "listing-blocks-add";
            $parameters["action"] = JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/blocks/listing/add", "listing"));
            $parameters["method"] = "post";

            $positions[t("Header")] = "header";
            $positions[t("Left")] = "left";
            $positions[t("Right")] = "right";
            $positions[t("Center")] = "center";
            $positions[t("Footer")] = "footer";
            $positions[t("None")] = "none";

            $fields[] = array("type"=>"select", "name"=>"position", "label"=>t("Position:"), "id"=>"position", "value"=>$positions, "selected"=>"none");
            $fields[] = array("type"=>"text", "name"=>"description", "label"=>t("Description:"), "id"=>"description", "required"=>true);
            $fields[] = array("type"=>"text", "name"=>"title", "label"=>t("Title:"), "id"=>"title");
            $fields[] = array("type"=>"textarea", "name"=>"pre_content", "id"=>"pre_content", "label"=>t("Pre-content:"), "value"=>$_REQUEST["pre_content"], "description"=>t("Content that will appear above the results."));
            $fields[] = array("type"=>"textarea", "name"=>"sub_content", "id"=>"sub_content", "label"=>t("Sub-content:"), "value"=>$_REQUEST["sub_content"], "description"=>t("Content that will appear below the results."));
            
            $fieldset[] = array("fields"=>$fields);
            
            $criteria_types = array();
            $criteria_types_list = JarisCMS\Type\GetList(JarisCMS\Security\GetCurrentUserGroup());
            foreach($criteria_types_list as $machine_name=>$type_fields)
            {
                $criteria_types[t(trim($type_fields["name"]))] = $machine_name;
            }
            
            $fields_criteria[] = array("type"=>"select", "name"=>"filter_types[]", "id"=>"filter_types", "label"=>t("Content type:"), "multiple"=>true, "selected"=>$_REQUEST["filter_types"], "value"=>$criteria_types);
            $fields_criteria[] = array("type"=>"textarea", "name"=>"filter_authors", "id"=>"filter_authors", "label"=>t("Authors:"), "value"=>$_REQUEST["filter_authors"], "description"=>t("List of usernames separated by comma, for example: admin, joe, john"));
            $fields_criteria[] = array("type"=>"other", "html_code"=>"<br />");
            $fields_criteria[] = array("type"=>"checkbox", "name"=>"related_to_current_page", "id"=>"related_to_current_page", "label"=>t("Related?"), "checked"=>$_REQUEST["related_to_current_page"], "value"=>true, "description"=>t("The results shown are related to the page being displayed."));
            $fields_criteria[] = array("type"=>"other", "html_code"=>"<br />");
            $fields_criteria[] = array("type"=>"other", "html_code"=>"<h2>".t("Categories:")."</h2><hr />");
            $fields_criteria = array_merge($fields_criteria, JarisCMS\Module\ContentListing\CategoryFields(null, null));
            
            $fieldset[] = array("fields"=>$fields_criteria, "name"=>t("Filters"), "collapsible"=>true, "collapsed"=>true);
            
            $ordering[t("Newest to oldest")] = "date_desc";
            $ordering[t("Oldest to newest")] = "date_asc";
            $ordering[t("Title ascendent")] = "title_asc";
            $ordering[t("Title descendent")] = "title_desc";
            $ordering[t("Most viewed all time")] = "views_desc";
            $ordering[t("Most viewed today")] = "views_today_desc";
            $ordering[t("Most viewed this week")] = "views_week_desc";
            $ordering[t("Most viewed this month")] = "views_month_desc";
            
            $fields_ordering[] = array("type"=>"radio", "name"=>"filter_ordering", "value"=>$ordering, "checked"=>$_REQUEST["filter_ordering"]?$_REQUEST["filter_ordering"]:"date_desc");
            
            $fieldset[] = array("fields"=>$fields_ordering, "name"=>t("Ordering"), "collapsible"=>true, "collapsed"=>false);
            
            $fields_layout[] = array("type"=>"other", "html_code"=>$layout);
            $fields_layout[] = array("type"=>"checkbox", "name"=>"display_title", "id"=>"display_title", "label"=>t("Display title?"), "checked"=>$_REQUEST["display_title"], "value"=>true);
            $fields_layout[] = array("type"=>"other", "html_code"=>"<br />");
            $fields_layout[] = array("type"=>"checkbox", "name"=>"display_summary", "id"=>"display_summary", "label"=>t("Display summary?"), "checked"=>$_REQUEST["display_summary"], "value"=>true);
            $fields_layout[] = array("type"=>"other", "html_code"=>"<br />");
            $fields_layout[] = array("type"=>"checkbox", "name"=>"display_more", "id"=>"display_more", "label"=>t("Display view more link?"), "checked"=>$_REQUEST["display_more"], "value"=>true);
            $fields_layout[] = array("type"=>"text", "name"=>"maximum_words", "value"=>$_REQUEST["maximum_words"]?$_REQUEST["maximum_words"]:15, "label"=>t("Maximum amount of words:"), "id"=>"maximum_words", "required"=>true, "description"=>t("Amount of words displayed of the page summary."));
            $fields_layout[] = array("type"=>"text", "name"=>"results_to_show", "value"=>$_REQUEST["results_to_show"]?$_REQUEST["results_to_show"]:5, "label"=>t("Results to show:"), "id"=>"results_to_show", "required"=>true, "description"=>t("The amount of results to display."));
            
            $fieldset[] = array("fields"=>$fields_layout, "name"=>t("Layout"), "collapsible"=>true, "collapsed"=>false);
            
            $fields_thumbnail[] = array("type"=>"checkbox", "name"=>"thumbnail_show", "id"=>"thumbnail_show", "label"=>t("Show thumbnail?"), "checked"=>$_REQUEST["thumgnail_show"], "value"=>true);
            $fields_thumbnail[] = array("type"=>"text", "name"=>"thumbnail_width", "id"=>"thumbnail_width", "label"=>t("Width:"), "value"=>$_REQUEST["thumbnail_width"]?$_REQUEST["thumbnail_width"]:"60", "required"=>true, "description"=>t("The width of the thumbnail in pixels."));
            $fields_thumbnail[] = array("type"=>"text", "name"=>"thumbnail_height", "id"=>"thumbnail_height", "label"=>t("Height:"), "value"=>$_REQUEST["thumbnail_height"], "description"=>t("The height of the thumbnail in pixels."));
            $fields_thumbnail[] = array("type"=>"color", "name"=>"thumbnail_bg", "id"=>"thumbnail_bg", "label"=>t("Background color:"), "value"=>$_REQUEST["thumbnail_bg"]?$_REQUEST["thumbnail_bg"]:"FFFFFF", "description"=>t("The background color of the thumbnail in case is neccesary."));
            $fields_thumbnail[] = array("type"=>"other", "html_code"=>"<br />");
            $fields_thumbnail[] = array("type"=>"checkbox", "name"=>"thumbnail_keep_aspectratio", "id"=>"thumbnail_keep_aspectratio", "label"=>t("Keep aspect ratio?"), "checked"=>$_REQUEST["thumbnail_keep_aspectratio"], "value"=>true);
            
            $fieldset[] = array("fields"=>$fields_thumbnail, "name"=>t("Thumbnail"), "collapsible"=>true, "collapsed"=>false);
            
            $fieldset[] = array("fields"=>JarisCMS\Group\GetListForFields(), "name"=>t("Users Access"), "collapsed"=>true, "collapsible"=>true, "description"=>t("Select the groups that can see the block. Don't select anything to display block to everyone."));
            
            $fieldset[] = array("fields"=>JarisCMS\Block\GetThemeFields(), "name"=>t("Positions Per Theme"), "collapsed"=>true, "collapsible"=>true, "description"=>t("Select the position where the block is going to be displayed per theme."));
            
            $display_rules[t("Display in all pages except the listed ones.")] = "all_except_listed";
            $display_rules[t("Just display on the listed pages.")] = "just_listed";
            
            $fields_pages[] = array("type"=>"radio", "checked"=>"all_except_listed", "name"=>"display_rule", "id"=>"display_rule", "value"=>$display_rules);
            $fields_pages[] = array("type"=>"uriarea", "name"=>"pages", "label"=>t("Pages:"), "id"=>"pages");
            
            $fieldset[] = array("fields"=>$fields_pages, "name"=>"Pages to display", "description"=>t("List of uri's seperated by comma (,). Also supports the wildcard (*), for example: my-section/*"));
            
            if(JarisCMS\Group\GetPermission("return_code_blocks", JarisCMS\Security\GetCurrentUserGroup()) || JarisCMS\Security\IsAdminLogged())
            {
                $fields_other[] = array("type"=>"textarea", "name"=>"return", "label"=>t("Return Code:"), "id"=>"return", "description"=>t("PHP code enclosed with &lt;?php code ?&gt; to evaluate if block should display by printing true or false. for example: &lt;?php if(JarisCMS\\Security\\IsUserLogged()) print \"true\"; else print \"false\"; ?&gt;"));
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
