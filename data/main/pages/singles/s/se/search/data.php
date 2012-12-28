<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that contains the search page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php 
            if(JarisCMS\Setting\Get("search_display_category_titles", "main"))
            {
                $categories_title = "";
                
                $categories = JarisCMS\Category\GetList();
                
                if(is_array($categories))
                {
                    foreach($categories as $machine_name=>$category_data)
                    {
                        if(isset($_REQUEST[$machine_name]) && $_REQUEST[$machine_name][0] != "-1")
                        {
                            $categories_title .= t($category_data["name"]);
                            $categories_title .= " > ";
                            
                            if(count($_REQUEST[$machine_name]) == count(JarisCMS\Category\GetChildrenList($machine_name)))
                            {
                                $categories_title .= t("All");
                            }
                            else if(count($_REQUEST[$machine_name]) > 1)
                            {
                                $subcategory_titles = "";
                                foreach($_REQUEST[$machine_name] as $subcategory_id)
                                {
                                    $subcategory_data = JarisCMS\Category\GetChildData($machine_name, $subcategory_id);
                                    
                                    $subcategory_titles .= t($subcategory_data["title"]) . " | ";
                                }
                                
                                $categories_title .= trim($subcategory_titles, "| ");
                            }
                            else
                            {
                                $subcategory_data = JarisCMS\Category\GetChildData($machine_name, $_REQUEST[$machine_name][0]);
                                
                                $categories_title .= t($subcategory_data["title"]);
                            }
                            
                            $categories_title .= ", ";
                        }
                    }
                }
                
                if($categories_title != "")
                {
                    print trim($categories_title, ", ");
                }
                else
                {
                    print t("Search");
                }
            }
            else
            {
                print t("Search");
            } 
        ?>
    field;

    field: content
        <?php
            //Variables that hold settings to display search preview images.
            $search_settings = JarisCMS\Setting\GetAll("main");
            $search_types = unserialize($search_settings["search_images_types"]);
            $type_display_image = array();
            
            if(is_array($search_types))
            {            
                foreach($search_types as $type)
                {
                    $type_display_image[$type] = 1;
                }
            }
            
            // To protect agains sql injections be sure $page is a int
            if(!is_numeric($_REQUEST["page"]))
            {
                $_REQUEST["page"] = 1;        
            }
            else 
            {
                $_REQUEST["page"]=intval($_REQUEST["page"]);
            }

            //Delete search results from session variable if user clicks reset
            if(isset($_REQUEST["btnReset"]))
            {
                unset($_REQUEST["keywords"]);
                JarisCMS\Search\ResetResults();
            }

            print "<fieldset style=\"margin-bottom: 5px;\" class=\"collapsible collapsed\">";
            print "<legend><a class=\"expand\" href=\"javascript:void(0)\">" . t("Search Options") . "</a></legend>";

            $parameters["action"] = JarisCMS\URI\PrintURL("search");
            $parameters["method"] = "get";

            $fields[] = array("type"=>"hidden", "name"=>"search", "value"=>1);
              
            $categories = JarisCMS\Category\GetList($_REQUEST["type"]);
            
            $selected_categories = array();
            if(count($categories) > 0)
            {
                foreach($categories as $machine_name=>$values)
                {
                    if(isset($_REQUEST[$machine_name]))
                    {
                        $selected_categories[$machine_name] = $_REQUEST[$machine_name];
                    }
                }
            }
            
            if(count($selected_categories) > 0)
            {
                $fields[] = array("type"=>"other", "html_code"=>"<fieldset style=\"margin-bottom: 5px;\" class=\"collapsible collapsed\">");
                $fields[] = array("type"=>"other", "html_code"=>"<legend><a class=\"expand\" href=\"javascript:void(0)\">" . t("Sorting") . "</a></legend>");
                $ordering_options[t("Title ascending")] = "title_asc";
                $ordering_options[t("Title descending")] = "title_desc";
                $ordering_options[t("Newest first")] = "newest";
                $ordering_options[t("Oldest first")] = "oldest";
                $fields[] = array("type"=>"select", "code"=>"onchange=\"this.form.submit()\"", "name"=>"order", "label"=>t("Sort by:"), "id"=>"order", "value"=>$ordering_options, "selected"=>$_REQUEST["order"]);
                $fields[] = array("type"=>"other", "html_code"=>"</fieldset>\n");                
            }
            
            if(count($categories) > 0)
            {
                $fields_categories = JarisCMS\Category\GenerateFieldList($selected_categories, null, $_REQUEST["type"]);
                $fieldset[] = array("fields"=>$fields_categories, "name"=>t("Categories"), "collapsible"=>true, "collapsed"=>false);
            }
            
            $types[t("-All-")] = "";
            $types_array = JarisCMS\Type\GetList();
            foreach($types_array as $machine_name=>$type_fields)
            {
                $types[t(trim($type_fields["name"]))] = $machine_name;
            }
        
            $fields_type[] = array("type"=>"select", "code"=>"onchange=\"this.form.submit()\"", "selected"=>$_REQUEST["type"], "name"=>"type", "id"=>"type", "value"=>$types);
            
            $fieldset[] = array("fields"=>$fields_type, "name"=>t("Content type"), "collapsible"=>true, "collapsed"=>false, "description"=>t("The type of content you are searching."));
            
            
            $fields[] = array("type"=>"other", "html_code"=>"</fieldset>\n");
            
            $fields[] = array("type"=>"other", "html_code"=>"<div style=\"clear: both\"></div>");
            
            $fields[] = array("type"=>"text", "code"=>"style=\"margin: 5px 0 5px 0; width: 400px; float: left\"", "name"=>"keywords", "label"=>t("Search text:"), "id"=>"search", "value"=>$_REQUEST["keywords"]);
            
            $fields[] = array("type"=>"submit", "code"=>"style=\"float: left; margin: 4px 0 0 5px;\"", "value"=>t("Search"));
            
            $fields[] = array("type"=>"other", "html_code"=>"<div style=\"clear: both\"></div>");

            $fieldset[] = array("fields"=>$fields);

            print JarisCMS\Form\Generate($parameters, $fieldset);
            
            //The amount of results to display per page
            $results_per_page = isset($_REQUEST["results_count"]) && intval($_REQUEST["results_count"])<=50?$_REQUEST["results_count"]:10;

            $results = array();
            if(isset($_REQUEST["search"]))
            {    
                if(trim($_REQUEST["keywords"]) != "")
                {
                    JarisCMS\Search\Content($_REQUEST["keywords"], null, $selected_categories, 1, $results_per_page);
                }
                else if(count($categories) > 0)
                {
                    JarisCMS\Search\Content(null, null, $selected_categories, 1, $results_per_page);
                }

                $results = JarisCMS\Search\GetResults(1, $results_per_page);
            }
            elseif((isset($_REQUEST["page"]) && trim($_REQUEST["keywords"]) != "") || (isset($_REQUEST["page"]) && count($selected_categories) > 0))
            {
                $results = JarisCMS\Search\GetResults($_REQUEST["page"], $results_per_page);
                
                //In case a search engine indexed a search page we research to be able to show data
                //since all search results are stored on session variable
                if(count($results) <= 0)
                {
                    if(trim($_REQUEST["keywords"]) != "")
                    {
                        JarisCMS\Search\Content($_REQUEST["keywords"], null, $selected_categories, $_REQUEST["page"], $results_per_page);
                    }
                    else if(count($categories) > 0)
                    {
                        JarisCMS\Search\Content(null, null, $selected_categories, $_REQUEST["page"], $results_per_page);
                    }
                    
                    $results = JarisCMS\Search\GetResults($_REQUEST["page"], $results_per_page);
                }
            }
            
            print "<h2 class=\"search-results-title\">" . t("Results") . "</h2>\n";

            //Print header template if available or default
            if($header_template = JarisCMS\Theme\GetSearchTemplateFile(JarisCMS\URI\Get(), $_REQUEST["type"], "header"))
            {
                ob_start();
                    include($header_template);
                    $html = ob_get_contents();
                ob_end_clean();
                
                print $html;
            }
            else 
            {
                print "<div class=\"search-results\">\n";
            }

            //Print page top navigation menu
            /*if(isset($_REQUEST["search"]) && $results)
            {
                JarisCMS\Search\PrintNavigation(1, $results_per_page);
            }
            elseif(isset($_REQUEST["page"]) && $results)
            {
                JarisCMS\Search\PrintNavigation($_REQUEST["page"], $results_per_page);
            }*/

            foreach($results as $fields)
            {
                $url = JarisCMS\URI\PrintURL($fields["uri"]);
                
                //Display content preview image
                $image = "";
                if($search_settings["search_display_images"] && isset($type_display_image[$fields["type"]]))
                {
                    $images = JarisCMS\Image\GetList($fields["uri"]);
                    
                    $image="";
                    if(is_array($images))
                    {
                        foreach($images as $id=>$image_fields)
                        {
                            $image="<a title=\"{$image_fields['description']}\" style=\"float: left; padding-right: 4px; padding-bottom: 4px;\" href=\"$url\"><img alt=\"{$image_fields['description']}\" src=\"" . JarisCMS\URI\PrintURL("image/" . $fields["uri"] . "/{$image_fields['name']}", array("w"=>$search_settings["search_images_width"]?$search_settings["search_images_width"]:60, "h"=>$search_settings["search_images_height"]?$search_settings["search_images_height"]:"", "ar"=>$search_settings["search_images_aspect_ratio"]?$search_settings["search_images_aspect_ratio"]:"", "bg"=>$search_settings["search_images_background_color"]?$search_settings["search_images_background_color"]:"")) . "\" /></a>";
                            break;
                        }
                    }
                }
                
                $title = JarisCMS\Search\HighlightResults($fields["title"]);
                $content = JarisCMS\Search\HighlightResults($fields["content"], $fields["input_format"], "content");
                
                //Print result template if available or default
                if($result_template = JarisCMS\Theme\GetSearchTemplateFile(JarisCMS\URI\Get(), $_REQUEST["type"]))
                {
                    ob_start();
                        include($result_template);
                        $html = ob_get_contents();
                    ob_end_clean();
                    
                    print $html;
                }
                else 
                {
                    print "<div class=\"title\">\n";
                    print "<li><a href=\"$url\">$title</a></li>\n";
                    print "</div>\n";
    
                    print "<div class=\"text\">\n";
                    print "$image ";
                    
                    foreach(JarisCMS\Search\GetTypeFields($fields["type"]) as $label=>$fields_name)
                    {
                        if($fields_name == "content")
                        {
                            if(is_numeric($label))
                            {
                                print "<div>" . $content . "</div>";
                            }
                            else
                            {
                                print "<span class=\"label\">" . $label . "</span> ";
                                print "<span class=\"value\">" . $content . "</span> ";
                            }
                        }
                        else if(is_numeric($label))
                        {
                            print "<span class=\"value\">" . $fields[$fields_name] . "</span> ";
                        }
                        else
                        {
                            print "<span class=\"label\">" . $label . "</span> ";
                            print "<span class=\"value\">" . $fields[$fields_name] . "</span> ";
                        }
                        
                    }
                    
                    print "<div style=\"clear: both\"></div>";
                    print "</div>\n";
                }
            }

            //Print footer template if available or default
            if($footer_template = JarisCMS\Theme\GetSearchTemplateFile(JarisCMS\URI\Get(), $_REQUEST["type"], "footer"))
            {
                ob_start();
                    include($footer_template);
                    $html = ob_get_contents();
                ob_end_clean();
                
                print $html;
            }
            else 
            {
                print "</div>\n";
            }
            
            //Print page navigation menu
            print "<div class=\"search-results\">\n";
            if(isset($_REQUEST["search"]) && $results)
            {
                JarisCMS\Search\PrintNavigation(1, $results_per_page);
            }
            elseif(isset($_REQUEST["page"]) && $results)
            {
                JarisCMS\Search\PrintNavigation($_REQUEST["page"], $results_per_page);
            }
            print "</div>\n";

            //If nothing was found
            if(isset($_REQUEST["search"]) && !$results)
            {
                print t("Nothing was found.");
            }

        ?>
    field;

    field: is_system
        1
    field;
row;
