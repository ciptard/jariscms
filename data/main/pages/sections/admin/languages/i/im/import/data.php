<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the language edit strings page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Import POT File") ?>
    field;

    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("view_languages", "edit_languages"));

            $lang_code = $_REQUEST["code"];
            
            if(!isset($_REQUEST["btnUpload"]) && !isset($_REQUEST["btnCancel"]))
            {
                JarisCMS\System\AddMessage(t("Here you can update language strings by uploading a po translation file."));
            }

            if(isset($_REQUEST["btnUpload"]) && !JarisCMS\Form\CheckFields("language-import"))
            {
                if("" . stristr($_FILES["po_file"]["type"], ".po") . "" == "")
                {
                    $main_file = JarisCMS\Setting\GetDataDirectory() . "language/strings.po";
                    $language_file = JarisCMS\Setting\GetDataDirectory() . "language/" . $lang_code . "/" . "strings.po";
                    
                    //Parse the uploaded file
                    $new_strings = JarisCMS\Language\ParsePO($_FILES["po_file"]["tmp_name"]);
                    
                    //First update empty strings pot file
                    $empty_strings = JarisCMS\Language\ParsePO($main_file);
                    foreach($new_strings as $original=>$translation)
                    {
                        if(!isset($empty_strings[$original]))
                        {
                            $empty_strings[$original] = "";
                        }
                    }
                    JarisCMS\Language\WritePO($empty_strings, $main_file);
                    
                    if($_REQUEST["option"] == "insert_new")
                    {
                        $count_new = 0;
                        $language_strings = JarisCMS\Language\ParsePO($language_file);
                        foreach($new_strings as $original=>$translation)
                        {
                            if(!isset($language_strings[$original]))
                            {
                                $language_strings[$original] = $translation;
                                $count_new++;
                            }
                        }
                        JarisCMS\Language\WritePO($language_strings, $language_file);
                        JarisCMS\System\AddMessage(t("Imported a total of") . " <b>$count_new</b> ". t("new strings"));
                    }
                    elseif($_REQUEST["option"] == "update_all")
                    {
                        $count_new = 0;
                        $count_updated = 0;
                        $language_strings = JarisCMS\Language\ParsePO($language_file);
                        foreach($new_strings as $original=>$translation)
                        {
                            if(!isset($language_strings[$original]))
                            {
                                $language_strings[$original] = $translation;
                                $count_new++;
                            }
                            else
                            {
                                $language_strings[$original] = $translation;
                                $count_updated++;
                            }
                        }
                        JarisCMS\Language\WritePO($language_strings, $language_file);
                        JarisCMS\System\AddMessage(t("Imported a total of") . " <b>$count_new</b> ". t("new strings and updated a total of") . " <b>$count_updated</b> ". t("strings"));
                    }
                    
                    JarisCMS\System\GoToPage("admin/languages/edit", array("code"=>$lang_code));
                }
                else
                {
                    JarisCMS\System\AddMessage(t("The uploaded file is not supported."), "error");
                    JarisCMS\System\GoToPage("admin/languages/import", array("code"=>$lang_code));
                }
            }
            elseif(isset($_REQUEST["btnCancel"]))
            {
                JarisCMS\System\GoToPage("admin/languages/edit", array("code"=>$lang_code));
            }
            
            $parameters["name"] = "language-import";
            $parameters["class"] = "language-import";
            $parameters["action"] = JarisCMS\URI\PrintURL("admin/languages/import");
            $parameters["method"] = "post";
            $parameters["enctype"] = "multipart/form-data";
            
            $fields[] = array("type"=>"hidden", "name"=>"code", "value"=>$lang_code);
            
            $fields[] = array("type"=>"file", "label"=>t("PO file:"), "name"=>"po_file", "id"=>"po_file", "valid_types"=>"po", "description"=>t("A po translation file to import into current translations."));
            $fieldset[] = array("fields"=>$fields);
            
            $options[t("Just insert new strings")] = "insert_new";
            $options[t("Update and insert new strings")] = "update_all";
            
            $options_fields[] = array("type"=>"radio", "name"=>"option", "id"=>"option", "value"=>$options, "checked"=>"insert_new");

            $fieldset[] = array("name"=>t("Import method"), "fields"=>$options_fields, "collapsible"=>true, "collapsed"=>false);

            $fields_submit[] = array("type"=>"submit", "name"=>"btnUpload", "value"=>t("Upload"));
            $fields_submit[] = array("type"=>"submit", "name"=>"btnCancel", "value"=>t("Cancel"));

            $fieldset[] = array("fields"=>$fields_submit);

            print JarisCMS\Form\Generate($parameters, $fieldset);
        ?>
    field;
    
    field: is_system
        1
    field;
row;
