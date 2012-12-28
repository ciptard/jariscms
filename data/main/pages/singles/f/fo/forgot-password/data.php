<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Page where users can reset their password.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php 
            http_status(401);
            print t("Forgot your password?") 
        ?>
    field;

    field: content
        <?php
            if(isset($_REQUEST["btnReset"]))
            {
                $message = "";
                if(isset($_REQUEST["username"]))
                {
                    $message = JarisCMS\User\ResetPasswordByName($_REQUEST["username"]);
                }
                
                if($message != "true" && isset($_REQUEST["email"]) && $_REQUEST["email"] != "")
                {
                    $message = JarisCMS\User\ResetPasswordByEmail($_REQUEST["email"]);
                }

                if($message == "true")
                {
                    JarisCMS\System\AddMessage(t("Your password has been reset successfully. Check your e-mail inbox for details."));
                }
                else
                {
                    JarisCMS\System\AddMessage($message, "error");
                    JarisCMS\System\GoToPage("forgot-password");
                }

                JarisCMS\System\GoToPage("");
            }
            elseif(isset($_REQUEST["btnCancel"]))
            {
                JarisCMS\System\GoToPage("");
            }

            $parameters["name"] = "reset-user-password";
            $parameters["class"] = "reset-user-password";
            $parameters["action"] = JarisCMS\URI\PrintURL("forgot-password");
            $parameters["method"] = "post";

            $fields[] = array("type"=>"text", "name"=>"username", "label"=>t("Username:"), "id"=>"username", "description"=>t("If you remember your username write it down."));
            
            $fields[] = array("type"=>"other", "html_code"=>"<h3>" . t("OR") . "</h3>");
            
            $fields[] = array("type"=>"text", "name"=>"email", "label"=>t("E-mail:"), "id"=>"email", "description"=>t("If you remember the e-mail that you used to register the account write it down."));

            $fields[] = array("type"=>"submit", "name"=>"btnReset", "value"=>t("Reset Password"));
            $fields[] = array("type"=>"submit", "name"=>"btnCancel", "value"=>t("Cancel"));

            $fieldset[] = array("fields"=>$fields);

            print JarisCMS\Form\Generate($parameters, $fieldset);
        ?>
    field;

    field: is_system
        1
    field;
row;
