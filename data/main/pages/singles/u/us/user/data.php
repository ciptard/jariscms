<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file The page that serve for displaying user profiles
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php
            $username = $_REQUEST["username"];
            $user_data = JarisCMS\User\GetData($username);
            
            if(!$user_data)
            {
                JarisCMS\System\GetHTTPStatusHeader(404);
                print t("Profile not found");
            }
            elseif(!JarisCMS\Setting\Get("user_profiles_public", "main") && !JarisCMS\Security\IsUserLogged())
            {
                JarisCMS\System\AddMessage(t("In order to view other people profiles you need to login."));
                JarisCMS\System\GoToPage("admin/user", array("return"=>JarisCMS\URI\Get()));
            }
            else
            {
                if($user_data["name"])
                    print $user_data["name"];
                else
                    print $username;
            }
        ?>
    field;

    field: content
        <?php
            $username = $_REQUEST["username"];
            $user_data = JarisCMS\User\GetData($username);
            
            if(!$user_data)
            {
                print t("The given username does not exist.");
            }
            else
            {
                //Age
                $t = time();
                $age = ($user_data["birth_date"] < 0) ? ( $t + ($user_data["birth_date"] * -1) ) : $t - $user_data["birth_date"];
                $age = floor($age/31536000);

                //Gender
                $gender = "";

                if($user_data["gender"] == "m")
                    $gender = t("Male");
                else
                    $gender = t("Female");
                
                //Personal text
                $personal_text = str_replace("\n", "<br />", trim($user_data["personal_text"]));
                
                //Birth date
                $birth_date = t(date("F", $user_data["birth_date"]));
                $birth_date .= " " . t(date("d", $user_data["birth_date"]));
                
                //Registration date
                $register_date = date("d/m/Y", $user_data["register_date"]);
                
                //10 Latest post
                $latest_post = "<h3>" . t("Latest post") . "</h3>";

                $pages = JarisCMS\SQLite\GetDataList("search_engine", "uris", 0, 10, "where author='$username' order by created_date desc");

                $latest_post .= "<table class=\"navigation-list\">";
                $latest_post .= "<thead>";
                $latest_post .= "<tr>";
                $latest_post .= "<td>" . t("Title") . "</td>";
                $latest_post .= "<td>" . t("Date") . "</td>";

                $latest_post .= "</tr>";
                $latest_post .= "</thead>";

                foreach($pages as $data)
                {
                    $page_data = JarisCMS\Page\GetData($data["uri"]);

                    $latest_post .= "<tr>";

                    $latest_post .= "<td><a href=\"" . JarisCMS\URI\PrintURL($data["uri"]) . "\">" . JarisCMS\System\PHPEval($page_data["title"]) . "</a></td>";

                    $latest_post .= "<td>" . date("d/m/Y", $page_data["created_date"]) . "</td>";

                    $latest_post .= "</tr>";
                }

                $latest_post .= "</table>";
                
                
                ob_start();
                    if(file_exists($theme = JarisCMS\Theme\GetUserProfileTemplateFile($user_data["group"], $username)))
                    {
                        include($theme);
                    }

                    $html = ob_get_contents();
                ob_end_clean();

                print $html;
            }
            
            //Strings for translation
            $string = t("Member since:");
            $string = t("Gender:");
            $string = t("Birth date:");

        ?>
    field;

    field: is_system
        1
    field;
row;
