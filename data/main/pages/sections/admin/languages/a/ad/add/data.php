<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the language add page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Add Language") ?>
    field;

    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("view_languages", "add_languages"));
            
            $languages = JarisCMS\Language\GetCodes();
            
            foreach($languages as $name=>$code)
            {
                if($code == $_REQUEST["code"])
                {
                    $_REQUEST["name"] = $name;
                }
            }

            if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("add-language"))
            {
                if(JarisCMS\Language\Add($_REQUEST["code"], $_REQUEST["name"], $_REQUEST["translator"], $_REQUEST["translator_email"], $_REQUEST["contributors"]))
                {
                    JarisCMS\System\AddMessage(t("The language was successfully created."));
                    JarisCMS\System\GoToPage("admin/languages");
                }
            }
            elseif(isset($_REQUEST["btnCancel"]))
            {
                JarisCMS\System\GoToPage("admin/languages");
            }

            $parameters["name"] = "add-language";
            $parameters["class"] = "add-language";
            $parameters["action"] = JarisCMS\URI\PrintURL("admin/languages/add");
            $parameters["method"] = "post";

            $fields[] = array("type"=>"select", "value"=>JarisCMS\Language\GetCodes(), "name"=>"code", "label"=>t("Language:"), "id"=>"code", "description"=>t("Select the language you want to add to the system."), "required"=>true);
            
            $fields[] = array("type"=>"text", "name"=>"translator", "label"=>t("Translator:"), "id"=>"translator", "description"=>t("Main translator for this language."));
            
            $fields[] = array("type"=>"text", "name"=>"translator_email", "label"=>t("E-mail:"), "id"=>"translator_email", "description"=>t("E-mail of the main translator."));
            
            $fields[] = array("type"=>"textarea", "name"=>"contributors", "label"=>t("Contributors:"), "id"=>"contributors", "description"=>t("A list of contributors seperated by a new line for this translation."));

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
