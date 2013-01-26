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

namespace JarisCMS\Module\ContactForms\System
{
    use JarisCMS\URI;
    use JarisCMS\Page;
    use JarisCMS\Module;
    use JarisCMS\System;

    function Initialization()
    {
        $uri = $_REQUEST["uri"];

        if($uri && URI\Get() != "admin/pages/add")
        {
            $page_data = Page\GetData($uri);
            if($page_data["type"] == "contact-form")
            {
                switch(URI\Get())
                {
                    case "admin/pages/edit":
                        System\GoToPage(Module\GetPageURI("admin/pages/contact-form/edit", "contact"), array("uri"=>$uri));
                    default:
                        break;
                }
            }
        }
        else if($_REQUEST["type"])
        {
            $page = URI\Get();
            if($page == "admin/pages/add" && $_REQUEST["type"] == "contact-form")
            {
                System\GoToPage(Module\GetPageURI("admin/pages/contact-form/add", "contact"), array("type"=>"contact-form", "uri"=>$uri));
            }
        }
    }
}

namespace JarisCMS\Module\ContactForms\Theme
{
    use JarisCMS\URI;
    use JarisCMS\Page;
    use JarisCMS\Email;
    use JarisCMS\Form;
    use JarisCMS\Module;
    use JarisCMS\System;
    use JarisCMS\Setting;
    use JarisCMS\Module\ContactForms;

    function MakeContent(&$content, &$content_title, &$content_data)
    {
        if($content_data["type"] == "contact-form")
        {
            $form_name = str_replace("/", "-", URI\Get());
            $subjects = unserialize($content_data["subjects"]);

            $valid_email = true;

            if(trim($_REQUEST["e_mail"]) != "")
            {
                if(!Form\CheckEmail(trim($_REQUEST["e_mail"])))
                {
                    System\AddMessage(t("The e-mail you entered appears to be invalid."), "error");
                    $valid_email = false;
                }
            }

            if(isset($_REQUEST["btnContact"]) &&
               !Form\CheckFields($form_name) &&
               ContactForms\AttachmentsValid(URI\Get()) &&
               $valid_email)
            {
                $fields = ContactForms\GetFields(URI\Get());
                $fields_values = array();

                ContactForms\AppendFields(URI\Get(), $fields_values);

                $html_message = "<b>" . t("Contact form:") . "</b> <a href=\"" . URI\PrintURL(URI\Get()) . "\">" . t($content_title) . "</a><br />";

                $html_message .= "<hr />";

                $to = array();
                if(is_array($subjects) && count($subjects) > 0)
                {
                    foreach($subjects as $subject_title=>$subject_to)
                    {
                        if($subject_title == $_REQUEST["subject"])
                        {
                            if(trim($subject_to) != "")
                            {
                                $to[$subject_to] = $subject_to;
                            }

                            $html_message .= "<b>" . t("Subject") . ":</b> " . t($subject_title) . "<br /><br />";
                            break;
                        }
                    }
                }

                if(count($to) <= 0)
                {
                    $to[$content_data["mail_recipient"]] = $content_data["mail_recipient"];
                }

                $cc = array();

                if(trim($content_data["mail_carbon_copy"]) != "")
                {
                    $cc_array = explode(",", $content_data["mail_carbon_copy"]);
                    foreach($cc_array as $cc_email)
                    {
                        $cc[trim($cc_email)] = trim($cc_email);
                    }
                }

                $from = array();
                if(trim($_REQUEST["name"]) != "" && trim($_REQUEST["e_mail"]) != "")
                {
                    $from[trim($_REQUEST["name"])] = trim($_REQUEST["e_mail"]);
                }

                foreach($fields as $id=>$field)
                {
                    $html_message .= "<b>" . t($field['name']) . ":</b> " . $fields_values[$field['variable_name']] . "<br /><br />";
                }

                $html_message .= "<hr />";
                $html_message .= t("IP address:") . " " . $_SERVER["REMOTE_ADDR"] . "<br />";
                $html_message .= t("User agent:") . " " . $_SERVER["HTTP_USER_AGENT"];

                $subject = t("Contact from ") . " " . Setting\Get("mailer_from_name", "main");

                $attachments = ContactForms\GetAttachments(URI\Get());

                if(Email\Send($to, $subject, $html_message, $alt_message=null, $attachments, $reply_to=array(), $bcc=array(), $cc, $from))
                {
                    System\AddMessage(t("Message successfully sent!"));
                }
                else
                {
                    System\AddMessage(t("An error occurred while sending the message. Please try again later."), "error");
                }
            }

            //Generate contact form
            $parameters["name"] = "$form_name";
            $parameters["class"] = "$form_name";
            $parameters["action"] = URI\PrintURL(URI\Get());
            $parameters["method"] = "post";

            $fieldset = array();

            if(is_array($subjects) && count($subjects) > 0)
            {
                $subject_values = array();
                foreach($subjects as $subject_title=>$subject_to)
                {
                    $subject_values[t($subject_title)] = $subject_title;
                }

                $fields_subject[] = array("type"=>"select", "selected"=>$_REQUEST["subject"], "name"=>"subject", "label"=>t("Subject:"), "id"=>"subject", "value"=>$subject_values, "required"=>true);
                $fieldset[] = array("fields"=>$fields_subject);
            }

            $fields = ContactForms\GenerateFormFields(URI\Get());

            if(count($fields) > 0)
            {
                $fieldset[] = array("fields"=>$fields);
            }

            $fields_validate[] = array("type"=>"validate_sum", "name"=>"validation", "label"=>t("Validation:"), "id"=>"validation", "description"=>"");

            $fieldset[] = array("fields"=>$fields_validate);

            $fields_buttons[] = array("type"=>"submit", "name"=>"btnContact", "value"=>t("Send"));
            $fields_buttons[] = array("type"=>"submit", "name"=>"btnCancel", "value"=>t("Cancel"));

            $fieldset[] = array("fields"=>$fields_buttons);

            $contact_content = Form\Generate($parameters, $fieldset);

            $content .= $contact_content;

            $content_data["contact_content"] = $contact_content;
        }
    }

    function MakeTabsCode(&$tabs_array)
    {
        if(isset($_REQUEST["uri"]))
        {
            $type = Page\GetType($_REQUEST["uri"]);

            if($type == "contact-form")
            {
                switch(URI\Get())
                {
                    case Module\GetPageURI("admin/pages/contact-form/edit", "contact"):
                    case "admin/pages/delete":
                    case "admin/pages/blocks":
                    case "admin/pages/files":
                    case "admin/pages/images":
                    case "admin/pages/translate":
                    case "admin/pages/blocks/post/settings":
                    {
                        $new_tab[t("Fields")] = array("uri"=>Module\GetPageURI("admin/pages/contact-form/fields", "contact"), "arguments"=>array("uri"=>$_REQUEST["uri"]));
                        $tabs_array[0] = array_merge($new_tab, $tabs_array[0]);
                    }
                }
            }
        }
    }
}

?>
