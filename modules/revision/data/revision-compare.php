<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the revisions full compare page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Revisions Comparison") ?>
    field;

    field: content
        <?php
            $rev1 = intval($_REQUEST["rev1"]);
            $rev2 = intval($_REQUEST["rev2"]);
            
            if(
                !isset($_REQUEST["uri"]) ||
                trim($_REQUEST["uri"]) == "" ||
                !file_exists(JarisCMS\Page\GeneratePath($_REQUEST["uri"])."/data.php")
            )
                JarisCMS\System\GoToPage("access-denied");

            if(!JarisCMS\Page\IsOwner($_REQUEST["uri"]))
                JarisCMS\Security\ProtectPage();

            JarisCMS\Security\ProtectPage(array("view_revisions"));

            $revisions_path = JarisCMS\Page\GeneratePath($_REQUEST["uri"]) . "/revisions";

            if(!file_exists($revisions_path))
            {
                JarisCMS\System\AddMessage(t("No revisions found."));
                JarisCMS\System\GoToPage($_REQUEST["uri"]);
            }
            
            if(
                !file_exists($revisions_path . "/" . $rev1 . ".php") ||
                !file_exists($revisions_path . "/" . $rev2 . ".php")
            )
            {
                JarisCMS\System\AddMessage(t("Invalid revisions."), "error");
                JarisCMS\System\GoToPage($_REQUEST["uri"]);
            }

            $page_data = JarisCMS\Page\GetData($_REQUEST["uri"], JarisCMS\Language\GetCurrent());

            // Add Edit tab if current user has proper permissions
            if(JarisCMS\Group\GetPermission("edit_content", JarisCMS\Security\GetCurrentUserGroup()) && !trim($page_data["is_system"]))
            {
                if(JarisCMS\Page\IsOwner($_REQUEST["uri"]))
                {
                    JarisCMS\System\AddTab(t("Edit"), "admin/pages/edit", array("uri"=>$_REQUEST["uri"]));
                }
            }
            
            // Add additional tabs
            JarisCMS\System\AddTab(t("View"), $_REQUEST["uri"]);
            JarisCMS\System\AddTab(t("Revisions"), JarisCMS\Module\GetPageURI("revisions", "revision"), array("uri"=>$_REQUEST["uri"]));

            $revisions = JarisCMS\FileSystem\GetFiles($revisions_path);
            rsort($revisions);

            print "<h2>".t("Page:")." ".$page_data["title"]."</h2>";

            // Display comparison chooser form
            print "<form action=\"".JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("revision/compare", "revision"))."\" method=\"GET\">";
            print "<input type=\"hidden\" name=\"uri\" value=\"".$_REQUEST["uri"]."\">";

            $options1 = "";
            foreach($revisions as $revision)
            {   
                $revision = str_replace(array($revisions_path . "/", ".php"), "", $revision);
                $date = t(date("F", $revision)) . " " . date("d, Y (h:i:s a)", $revision);

                $selected = "";

                if($rev1 == $revision)
                    $selected = "selected=\"selected\"";

                $options1 .= "<option $selected value=\"$revision\">$date</option>";
            }

            print "<b>".t("Older:")."</b>&nbsp;";
            print "<select name=\"rev1\">";
            print $options1;
            print "</select>&nbsp;";

            $options2 = "";
            foreach($revisions as $revision)
            {
                $revision = str_replace(array($revisions_path . "/", ".php"), "", $revision);
                $date = t(date("F", $revision)) . " " . date("d, Y (h:i:s a)", $revision);

                $selected = "";

                if($rev2 == $revision)
                    $selected = "selected=\"selected\"";

                $options2 .= "<option $selected value=\"$revision\">$date</option>";
            }

            print "<b>".t("Newer:")."</b>&nbsp;";
            print "<select name=\"rev2\">";
            print $options2;
            print "</select>&nbsp;";

            print "<input type=\"submit\" name=\"btnCompare\" value=\"".t("Compare")."\">";
            print "</form>";

            print "<hr />";

            // Display comparison
            JarisCMS\System\AddStyle("modules/revision/styles/file.css");
            
            $rev1_file = $revisions_path . "/$rev1.php";
            $rev2_file = $revisions_path . "/$rev2.php";
            
            print JarisCMS\Module\Revision\DiffFiles($rev1_file, $rev2_file);
        ?>
    field;

    field: is_system
        1
    field;
row;
