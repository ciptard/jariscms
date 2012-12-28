<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the menu add page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Create Menu") ?>
    field;

    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("view_menus", "add_menus"));

            if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("add-menu"))
            {
                $message = JarisCMS\Menu\Create($_REQUEST["menu_name"]);
                
                if($message == "true")
                {
                    //Create block for the menu
                    $menu_block["description"] = $_REQUEST["menu_name"] . " " . "menu";
                    $menu_block["title"] = $_REQUEST["menu_name"] . " " . "menu";
                    $menu_block["content"] = "<?php\nprint JarisCMS\Theme\MakeLinks(JarisCMS\PHPDB\Sort(JarisCMS\Menu\GetSubItems(\"{$_REQUEST['menu_name']}\"),\"order\"), \"{$_REQUEST['menu_name']}\");\n?>";
                    $menu_block["order"] = "0";
                    $menu_block["display_rule"] = "all_except_listed";
                    $menu_block["pages"] = "";
                    $menu_block["return"] = "";
                    $menu_block["is_system"] = "1";
                    $menu_block["menu_name"] = $_REQUEST["menu_name"];
                    
                    JarisCMS\Block\Add($menu_block, "none");
                    
                    JarisCMS\System\AddMessage(t("Menu successfully created."));
                }
                else
                {
                    JarisCMS\System\AddMessage($message, "error");
                }

                JarisCMS\System\GoToPage("admin/menus");
            }
            elseif(isset($_REQUEST["btnCancel"]))
            {
                JarisCMS\System\GoToPage("admin/menus");
            }

            $parameters["name"] = "add-menu";
            $parameters["class"] = "add-menu";
            $parameters["action"] = JarisCMS\URI\PrintURL("admin/menus/add");
            $parameters["method"] = "post";

            $fields[] = array("type"=>"text", "name"=>"menu_name", "label"=>t("Name:"), "id"=>"menu_name", "description"=>t("A machine readable name. For example: my-menu"), "required"=>true);

            $fields[] = array("type"=>"submit", "name"=>"btnSave", "value"=>t("Save"));
            $fields[] = array("type"=>"submit", "name"=>"btnCancel", "value"=>t("Cancel"));

            $fieldset[] = array("fields"=>$fields);

            print JarisCMS\Form\Generate($parameters, $fieldset);
        ?>
    field;
    
    field: is_system
        1
    field;
row;
