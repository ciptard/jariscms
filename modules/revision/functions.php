<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Jaris CMS module functions file
 *
 *@note File that stores all hook functions.
 */

namespace JarisCMS\Module\Revision\System
{
    use JarisCMS\URI;
    use JarisCMS\Page;
    use JarisCMS\PHPDB;
    use JarisCMS\Group;
    use JarisCMS\System;
    use JarisCMS\Security;
    use JarisCMS\Module\Revision;
    
    /**
     * If a person is trying to view a specific revision and has proper permissions
     * replace the current page content with the specified revision.
     */
    function GetPageData(&$page_data)
    {
        $uri = URI\Get();

        if(isset($_REQUEST["rev"]))
        {
            $_REQUEST["rev"] = intval($_REQUEST["rev"]);

            $revisions_file = Page\GeneratePath($uri) . "/revisions/" . $_REQUEST["rev"] . ".php";

            if(file_exists($revisions_file))
            {
                if(Group\GetPermission("view_revisions", Security\GetCurrentUserGroup()) && !trim($page_data[0]["is_system"]))
                {
                    if(Page\IsOwner($uri))
                    {   
                        $revision_data = PHPDB\GetData(0, $revisions_file);
                        $revision_data["groups"] = unserialize($revision_data[0]["groups"]);
                        $revision_data["categories"] = unserialize($revision_data[0]["categories"]);

                        $revision_data["title"] = Revision\DiffHTML(
                            $page_data[0]["title"], 
                            $revision_data["title"]
                        );

                        $revision_data["content"] = Revision\DiffHTML(
                            $page_data[0]["content"], 
                            $revision_data["content"]
                        );

                        $page_data[0] = $revision_data;

                        System\AddStyle("modules/revision/styles/html.css");

                        System\AddMessage(
                            t("You are viewing revision of:") . " " .
                            t(date("F", $_REQUEST["rev"])) . " " .
                            date("d, Y (h:i:s a)", $_REQUEST["rev"])
                        );
                    }
                }
            }
        }
    }
}

namespace JarisCMS\Module\Revision\Page
{
    use JarisCMS\Page;
    use JarisCMS\PHPDB;
    
    function Create(&$page, &$data, &$path)
    {
        $revisions_path = $path . "/revisions/";

        mkdir($revisions_path);

        $revision_data = $data;
        $revision_data["groups"] = serialize($revision_data["groups"]);
        $revision_data["categories"] = serialize($revision_data["categories"]);

        $revision_file = $revisions_path . "/" . time() . ".php";

        PHPDB\Add($revision_data, $revision_file);
    }

    function Edit(&$page, &$new_data, &$page_path)
    {
        $revisions_path = $page_path . "/revisions/";

        if(!file_exists($revisions_path))
            mkdir($revisions_path);

        // Check if something changed
        $current_data = Page\GetData($page);

        $has_changed = false;

        foreach($current_data as $field=>$value)
        {
            if($field != "views" && $field != "last_edit_by" && $field != "last_edit_date" && $value != $new_data[$field])
            {
                $has_changed = true;
                break;
            }
        }

        // Create revision
        if($has_changed)
        {
            $revision_data = $new_data;
            $revision_data["groups"] = serialize($revision_data["groups"]);
            $revision_data["categories"] = serialize($revision_data["categories"]);

            $revision_file = $revisions_path . "/" . time() . ".php";

            PHPDB\Add($revision_data, $revision_file);
        }
    }
}

namespace JarisCMS\Module\Revision\Group
{
    function GetPermissions(&$permissions, $group)
    {
        if($group != "guest")
        {
            $revisions = array();

            $revisions["view_revisions"] = t("View");
            $revisions["delete_revisions"] = t("Delete");
            $revisions["revert_revisions"] = t("Revert");

            $permissions[t("Revisions")] = $revisions;
        }
    }
}

namespace JarisCMS\Module\Revision\Theme
{
    use JarisCMS\URI;
    use JarisCMS\Page;
    use JarisCMS\Group;
    use JarisCMS\Module;
    use JarisCMS\Security;
    
    function MakeTabsCode(&$tabs_array)
    {
        global $page_data;

        $uri = Uri\Get();

        if(Group\GetPermission("view_revisions", Security\GetCurrentUserGroup()) && !trim($page_data[0]["is_system"]))
        {
            if(Page\IsOwner($uri))
            {
                $tabs_array[0][t("Revisions")] = array("uri"=>  Module\GetPageURI("revisions", "revision"), "arguments"=>array("uri"=>$uri));

                if(isset($_REQUEST["rev"]))
                {
                    $_REQUEST["rev"] = intval($_REQUEST["rev"]);

                    $revisions_file = Page\GeneratePath($uri) . "/revisions/" . $_REQUEST["rev"] . ".php";

                    if(file_exists($revisions_file))
                    {
                        if(Group\GetPermission("revert_revisions", Security\GetCurrentUserGroup()))
                        {
                            $tabs_array[1][t("Revert to this revision")] = array(
                                "uri"=>Module\GetPageURI("revision/revert", "revision"),
                                "arguments"=>array("uri"=>$uri, "rev"=>$_REQUEST["rev"])
                            );
                        }
                    }
                }
            }
        }
    }
}

?>
