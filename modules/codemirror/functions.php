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

namespace
{
    $display_codemirror_on_current_page = false;
}

namespace JarisCMS\Module\CodeMirror\Form
{
    use JarisCMS\URI;
    use JarisCMS\Setting;
    use JarisCMS\Security;
    
    function Generate(&$parameters, &$fieldsets)
    {
        global $display_codemirror_on_current_page;

        $textarea_id = unserialize(Setting\Get("teaxtarea_id", "codemirror"));
        $forms_to_display = unserialize(Setting\Get("forms", "codemirror"));
        $groups = unserialize(Setting\Get("groups", "codemirror"));

        if(!is_array($textarea_id)) $textarea_id = array();
        if(!is_array($forms_to_display)) $forms_to_display = array();
        if(!is_array($groups)) $groups = array();

        if(!$textarea_id[Security\GetCurrentUserGroup()])
        {
            $textarea_id[Security\GetCurrentUserGroup()] = "content, return";
        }
        else
        {
            $textarea_id[Security\GetCurrentUserGroup()] = explode(",", $textarea_id[Security\GetCurrentUserGroup()]);
        }

        if(!$forms_to_display[Security\GetCurrentUserGroup()])
        {
            $forms_to_display[] = "add-page,edit-page,translate-page,add-page-block,block-page-edit,add-block,block-edit,add-page-block-page";
        }
        else
        {
            $forms_to_display[Security\GetCurrentUserGroup()] = explode(",", $forms_to_display[Security\GetCurrentUserGroup()]);
        }

        //Check if current user is on one of the groups that can use the editor
        if(!$groups[Security\GetCurrentUserGroup()])
        {
            return;
        }

        foreach($forms_to_display[Security\GetCurrentUserGroup()] as $form_name)
        {
            $form_name = trim($form_name);

            if($parameters["name"] == $form_name)
            {
                foreach($textarea_id[Security\GetCurrentUserGroup()] as $id)
                {
                    $id = trim($id);

                    $full_id = $parameters["name"] . "-" . $id;

                    $editor = '
                    <script type="text/javascript">
                    var textarea = document.getElementById("'.$full_id.'");
                    var uiOptions = {
                        path : "'.URI\PrintURL("modules/codemirror/codemirror-ui/js/").'",
                        imagePath : "'.URI\PrintURL("modules/codemirror/codemirror-ui/images/silk").'",
                        searchMode : "popup"
                    };
                    var codeMirrorOptions = {
                        lineNumbers: true,
                        matchBrackets: true,
                        mode: "application/x-httpd-php",
                        indentUnit: 4,
                        indentWithTabs: true,
                        lineWrapping: true,
                        tabMode: "shift"
                    };

                    var editor = new CodeMirrorUI(textarea,uiOptions,codeMirrorOptions);
                    </script>';

                    $fields = array();

                    foreach($fieldsets as $fieldsets_index=>$fieldset_fields)
                    {
                        $fields = array();

                        foreach($fieldset_fields["fields"] as $fields_index=>$values)
                        {
                            if($values["type"] == "textarea" && $values["id"] == $id)
                            {
                                $values["class"] = "codemirror";
                                $fields[] = $values;
                                $fields[] = array("type"=>"other", "html_code"=>$editor);

                                $new_fields = array();

                                foreach($fieldset_fields["fields"]  as $check_index=>$field_data)
                                {
                                    //Copy new fields to the position of replaced textarea with codemirror
                                    if($check_index == $fields_index)
                                    {
                                        foreach($fields as $field)
                                        {
                                            $new_fields[] = $field;
                                        }
                                    }

                                    //Copy the other fields on the fieldset
                                    else
                                    {
                                        $new_fields[] = $field_data;
                                    }
                                }

                                //Replace original fields with newly fields with codemirror added
                                $fieldsets[$fieldsets_index]["fields"] = $new_fields;

                                //Exit the fields check loop and fieldsets loop
                                break 2;
                            }
                        }
                    }
                }

                //Indicates that a field that matched was found and codemirror should be displayed
                $display_codemirror_on_current_page = true;

                //Exit the form name search loop since the form name was already found
                break;
            }
        }
    }
}

namespace JarisCMS\Module\CodeMirror\System
{
    use JarisCMS\URI;
    
    function GetStyles(&$styles)
    {
        global $display_codemirror_on_current_page;

        if($display_codemirror_on_current_page)
        {
            $styles[] = URI\PrintURL("modules/codemirror/codemirror-3.0/lib/codemirror.css");
            $styles[] = URI\PrintURL("modules/codemirror/codemirror-ui/css/codemirror-ui.css");
        }
    }

    function GetScripts(&$scripts)
    {
        global $display_codemirror_on_current_page;

        if($display_codemirror_on_current_page)
        {
            $scripts[] = URI\PrintURL("modules/codemirror/codemirror-3.0/lib/codemirror.js");
            $scripts[] = URI\PrintURL("modules/codemirror/codemirror-3.0/lib/util/matchbrackets.js");
            $scripts[] = URI\PrintURL("modules/codemirror/codemirror-3.0/lib/util/searchcursor.js");
            $scripts[] = URI\PrintURL("modules/codemirror/codemirror-3.0/mode/htmlmixed/htmlmixed.js");
            $scripts[] = URI\PrintURL("modules/codemirror/codemirror-3.0/mode/xml/xml.js");
            $scripts[] = URI\PrintURL("modules/codemirror/codemirror-3.0/mode/javascript/javascript.js");
            $scripts[] = URI\PrintURL("modules/codemirror/codemirror-3.0/mode/css/css.js");
            $scripts[] = URI\PrintURL("modules/codemirror/codemirror-3.0/mode/clike/clike.js");
            $scripts[] = URI\PrintURL("modules/codemirror/codemirror-3.0/mode/php/php.js");
            $scripts[] = URI\PrintURL("modules/codemirror/codemirror-ui/js/codemirror-ui.js");
        }
    }
}

namespace JarisCMS\Module\CodeMirror\Theme
{
    use JarisCMS\URI;
    use JarisCMS\Module;
    
    function MakeTabsCode(&$tabs_array)
    {
        if(URI\Get() == "admin/settings")
        {
            $tabs_array[0][t("Codemirror Editor")] = array("uri"=>Module\GetPageURI("admin/settings/codemirror", "codemirror"), "arguments"=>null);
        }
    }
}

?>
