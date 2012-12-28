<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the user list page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        RSS
    field;
    field: content
        <?php 
            header('Content-type: text/xml; charset=UTF-8', true);
            
            $type_data = "";
            if(trim($_REQUEST["type"]) != "")
            {
                $type_data = JarisCMS\Type\GetData($_REQUEST["type"]);
            }
            
            if($type_data["name"])
            {
                $type_name = t($type_data["name"]) . " - ";
            }
            
            $title = str_replace("&", "and", t(JarisCMS\Setting\Get("title", "main")));
            $description = t(JarisCMS\Setting\Get("title", "slogan"));
            $link = JarisCMS\URI\PrintURL("");
            $last_build_date = date("r", time());
            
            print "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
            <rss version=\"2.0\">
            <channel>
                    <title>{$type_name}$title</title>
                    <description>$description</description>
                    <link>$link</link>
                    <lastBuildDate>$last_build_date</lastBuildDate>
            ";        

            $type = "";
            if(trim($_REQUEST["type"]) != "")
            {
                $type = str_replace("'", "''", $_REQUEST["type"]);
                $type = "and type='$type'";
            }
            
            $group = JarisCMS\Security\GetCurrentUserGroup();
            
            $pages = JarisCMS\SQLite\GetDataList("search_engine", "uris", $page - 1, 20, "where has_permissions > 0 $type order by created_date desc", "uri, haspermission(groups, '$group') as has_permissions");
            
            $is_first = true;
            
            foreach($pages as $data)
            {
                $page_data = JarisCMS\Page\GetData($data["uri"], JarisCMS\Language\GetCurrent());
                
                $search = array ("'<script[^>]*?>.*?</script>'si",  // Strip out javascript
                 "'<[\/\!]*?[^<>]*?>'si",           // Strip out html tags
                 "'([\r\n])[\s]+'",                 // Strip out white space
                 "'&(quot|#34);'i",                 // Replace html entities
                 //"'&(amp|#38);'i",
                 "'&(nbsp|#160);'i",
                 "'&(iexcl|#161);'i",
                 "'&(cent|#162);'i",
                 "'&(pound|#163);'i",
                 "'&(copy|#169);'i",
                 "'&#(\d+);'e");                    // evaluate as php

                $replace = array ("",
                  "",
                  "\\1",
                  "\"",
                  //"&",
                  " ",
                  chr(161),
                  chr(162),
                  chr(163),
                  chr(169),
                  "chr(\\1)");
                
                
                $title = str_replace(array("&", "&amp;"), "and", $page_data["title"]);
                $description = preg_replace($search, $replace, JarisCMS\System\PrintContentPreview($page_data["content"], 45, true));
                $link = JarisCMS\URI\PrintURL($data["uri"]);
                $date = date("r", $page_data["created_date"]);
                
                //Adds to the channel the publication date of latest content
                if($is_first)
                {
                    "<pubDate>$date</pubDate>";
                    $is_first = false;
                }
                
                print "
                <item>
                    <title>$title</title>
                    <description>$description</description>
                    <link>$link</link>
                    <pubDate>$date</pubDate>
                </item>";
            }
            
            print "
            </channel>
            </rss>
            ";
        ?>
    field;

    field: is_system
        1
    field;
row;
