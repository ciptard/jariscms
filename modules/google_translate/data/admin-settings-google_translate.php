<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the administration page for ecommerce.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Google Translate Settings") ?>
    field;

    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("edit_settings"));
            
            JarisCMS\System\AddTab(t("Settings"), "admin/settings");

            if(isset($_REQUEST["btnSave"]))
            {
                if(JarisCMS\Setting\Save("input_language", $_REQUEST["input_language"], "google_translate"))
                {
                    JarisCMS\System\AddMessage(t("Your changes have been saved."));
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"));
                }

                JarisCMS\System\GoToPage("admin/settings");
            }

            $settings = JarisCMS\Setting\GetAll("google_translate");

            $parameters["name"] = "google-translate-settings";
            $parameters["class"] = "google-translate-settings";
            $parameters["action"] = JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/settings/google-translate", "google_translate"));
            $parameters["method"] = "post";
            
            $languages[t("Afrikaans")] = "af";
            $languages[t("Albanian")] = "sq";
            $languages[t("Arabic")] = "ar";
            $languages[t("Belarusian")] = "be";
            $languages[t("Bulgarian")] = "bg";
            $languages[t("Catalan")] = "ca";
            $languages[t("Chinese (Simplified)")] = "zh-CN";
            $languages[t("Chinese (Traditional)")] = "zh-TW";
            $languages[t("Croatian")] = "hr";
            $languages[t("Czech")] = "cs";
            $languages[t("Danish")] = "da";
            $languages[t("Dutch")] = "nl";
            $languages[t("English")] = "en";
            $languages[t("Estonian")] = "et";
            $languages[t("Filipino")] = "tl";
            $languages[t("Finnish")] = "fi";
            $languages[t("French")] = "fr";
            $languages[t("Galician")] = "gl";
            $languages[t("German")] = "de";
            $languages[t("Greek")] = "el";
            $languages[t("Haitian Creole")] = "ht";
            $languages[t("Hebrew")] = "iw";
            $languages[t("Hindi")] = "hi";
            $languages[t("Hungarian")] = "hu";
            $languages[t("Icelandic")] = "is";
            $languages[t("Indonesian")] = "id";
            $languages[t("Irish")] = "ga";
            $languages[t("Italian")] = "it";
            $languages[t("Japanese")] = "ja";
            $languages[t("Korean")] = "ko";
            $languages[t("Latvian")] = "lv";
            $languages[t("Lithuanian")] = "lt";
            $languages[t("Macedonian")] = "mk";
            $languages[t("Malay")] = "ms";
            $languages[t("Maltese")] = "mt";
            $languages[t("Norwegian")] = "no";
            $languages[t("Persian")] = "fa";
            $languages[t("Polish")] = "pl";
            $languages[t("Portuguese")] = "pt";
            $languages[t("Romanian")] = "ro";
            $languages[t("Russian")] = "ru";
            $languages[t("Serbian")] = "sr";
            $languages[t("Slovak")] = "sl";
            $languages[t("Slovenian")] = "af";
            $languages[t("Spanish")] = "es";
            $languages[t("Swahili")] = "sw";
            $languages[t("Swedish")] = "sv";
            $languages[t("Thai")] = "th";
            $languages[t("Turkish")] = "tr";
            $languages[t("Ukrainian")] = "uk";
            $languages[t("Vietnamese")] = "vi";
            $languages[t("Welsh")] = "cy";
            $languages[t("Yiddish")] = "yi";
            
            $fields[] = array("type"=>"select", "label"=>t("Input language:"), "name"=>"input_language", "id"=>"input_language", "value"=>$languages, "selected"=>$settings["input_language"], "description"=>t("The original language of the website."));
            
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
