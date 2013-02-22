<?php
/**
 *Copyright 2008, Jefferson Gonzàlez (JegoYalu.com)
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
        <?php print t("Polls Vote") ?>
    field;

    field: content        
        <?php
            if(isset($_REQUEST["uri"]))
            {
                if(!isset($_COOKIE["poll"][$_REQUEST["uri"]]) && !JarisCMS\Module\Polls\Core\Expired($_REQUEST["uri"]))
                {
                    $page_data = JarisCMS\Page\GetData($_REQUEST["uri"]);
                    
                    if($page_data["type"] == "poll")
                    {
                        $page_data["option_value"] = unserialize($page_data["option_value"]);
                        
                        $page_data["option_value"][$_REQUEST["id"]] += 1;
                        
                        $page_data["option_value"] = serialize($page_data["option_value"]);
                        
                        JarisCMS\Page\Edit($_REQUEST["uri"], $page_data);
                        
                        setcookie("poll[{$_REQUEST['uri']}]", "1", time() + ((((60 * 60) * 24) * 365) * 10), "/"); 
                        
                        JarisCMS\System\AddMessage(t("Your vote was successfully submited."));
                    }
                }
                else
                {
                    JarisCMS\System\AddMessage(t("You have already voted!"), "error");
                }
                
                if(isset($_REQUEST["actual_uri"]))
                {
                    JarisCMS\System\GoToPage($_REQUEST["actual_uri"]);
                }
                else
                {
                    JarisCMS\System\GoToPage($_REQUEST["uri"]);
                }
            }
            else
            {
                JarisCMS\System\GoToPage("");
            }
        ?>
    field;

    field: is_system
        1
    field;
row;
