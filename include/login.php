<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Functions to manage login and logout as protected pages.
 */

namespace JarisCMS\Security;

/**
 * Checks if a user is logged in.
 *
 * @return bool true if user is logged or false if not.
 */
function IsUserLogged()
{
    global $base_url;
    static $user_data;
    
    if(!isset($_SESSION))
        return false;
    
    //To reduce file access
    if(!$user_data)
    {
       $user_data = \JarisCMS\User\GetData($_SESSION["logged"]["username"]);
    }
    
    //Remove the optional wwww for problems from www and non www links
    $logged_site = str_replace("http://www.", "http://", $_SESSION["logged"]["site"]);
    $base_url_parsed =  str_replace("http://www.", "http://", $base_url);
    
    if($logged_site == $base_url_parsed && 
       $user_data["password"] == $_SESSION["logged"]["password"] &&
       ($_SESSION["logged"]["user_agent"] == $_SERVER["HTTP_USER_AGENT"] || 
        ($_SERVER["HTTP_USER_AGENT"] == "Shockwave Flash" && isset($_FILES)) //Enable flash uploaders that send another agent
       )
      )
    {
        //If validation by ip is enabled check if ip the same to continue
        if(\JarisCMS\Setting\Get("validate_ip", "main"))
        {
            if($_SESSION["logged"]["ip_address"] != $_SERVER["REMOTE_ADDR"])
            {
                LogoutUser();
                return false;
            }
        }
        
        $_SESSION["logged"]["group"] = $user_data["group"];
        
        return true;
    }
    else
    {
        LogoutUser();
        return false;
    }
}

/**
 * Checks if the administrator is logged in.
 *
 * @return bool true if the admin is logged or false if not.
 */
function IsAdminLogged()
{
    if(IsUserLogged() && GetCurrentUserGroup() == "administrator")
    {
        return true;
    }
    else
    {
        return false;
    }
}

/**
 * Login a user to the site if username and password
 * is correct on a form submit.
 *
 * @return bool true on success or false on incorrect login. 
 */
function LoginUser()
{
    global $base_url;
    
    $is_logged = false;

    if($_SESSION["logged"]["site"] != $base_url)
    {
        $user_data = false;
        
        if(\JarisCMS\Form\CheckEmail($_REQUEST["username"]))
        {
            $user_data = \JarisCMS\User\GetDataByEmail($_REQUEST["username"]);
            $_REQUEST["username"] = $user_data["username"];
        }
        else
        {
            $user_data = \JarisCMS\User\GetData($_REQUEST["username"]);
        }
        
        if($user_data && crypt($_REQUEST["password"], $user_data["password"]) == $user_data["password"])
        {
            $groups_approval = unserialize(\JarisCMS\Setting\Get("registration_groups_approval", "main"));
            
            if((\JarisCMS\Setting\Get("registration_needs_approval", "main") && $user_data["status"] == "0" && !\JarisCMS\Setting\Get("registration_can_select_group", "main")) ||
               (\JarisCMS\Setting\Get("registration_can_select_group", "main") && $user_data["status"] == "0" && in_array($user_data["group"], $groups_approval)))
            {
                \JarisCMS\System\AddMessage(t("Your registration is awaiting for approval. If the registration is approved you will receive an email notification."));
                
                return $is_logged;
            }
            
            $_SESSION["logged"]["site"] = $base_url;
            $_SESSION["logged"]["username"] = strtolower($_REQUEST["username"]);
            $_SESSION["logged"]["password"] = $user_data["password"];
            $_SESSION["logged"]["group"] = $user_data["group"];
            $_SESSION["logged"]["ip_address"] = $_SERVER["REMOTE_ADDR"];
            $_SESSION["logged"]["user_agent"] = $_SERVER["HTTP_USER_AGENT"];

            //Save last ip used
            $user_data["ip_address"] = $_SERVER["REMOTE_ADDR"];
            \JarisCMS\User\Edit($_REQUEST["username"], $user_data["group"], $user_data);
            
            //Keep user uploads dir clean
            \JarisCMS\Form\DeleteAllUploads();

            $is_logged = true;
        }
        else
        {
            $_SESSION["logged"]["site"] = false;            
            $is_logged = false;
        }

        if(isset($_REQUEST["username"]) && isset($_REQUEST["password"]) && $is_logged == false)
        {
            \JarisCMS\System\AddMessage(t("The username or password you entered is incorrect."), "error");
        }
    }

    return $is_logged;
}

/**
 * Logs out the user from the system by clearing the needed session variables.
 */
function LogoutUser()
{
    global $base_url;
    
    unset($_SESSION["logged"]);
}

/**
 * Send an email notification to all administrators when a new user is registered
 * and the registration needs approval flag is turned on. Used on the register page.
 * 
 * @param string $username
 */
function NotifyAdminsForRegApproval($username)
{
    $user_data = \JarisCMS\User\GetData($username);
    
    $select = "select * from users where user_group='administrator'";
    
    $db = \JarisCMS\SQLite\Open("users");
    
    $result = \JarisCMS\SQLite\Query($select, $db);
    
    $to = array();
    while($data = \JarisCMS\SQLite\FetchArray($result))
    {
        $admin_data = get_user_data($data["username"]);
        $to[$admin_data["name"]] = $data["email"];
    }
    
    \JarisCMS\SQLite\Close($db);
    
    $html_message = t("A new account has been created and is pending for administration approval.") . "<br /><br />";
    $html_message .= "<b>".t("Fullname:")."</b>" . " " . $user_data["name"] . "<br />";
    $html_message .= "<b>".t("Username:")."</b>" . " " . $username . "<br />";
    $html_message .= "<b>".t("E-mail:")."</b>" . " " . $user_data["email"] . "<br /><br />";
    $html_message .= t("For more details or approve this registration visit the users management page:") . "<br />";
    $html_message .= "<a target=\"_blank\" href=\"".\JarisCMS\URI\PrintURL("admin/user", array("return"=>"admin/users/list"))."\">" . \JarisCMS\URI\PrintURL("admin/user", array("return"=>"admin/users/list")) . "</a>";
    
    \JarisCMS\Email\Send($to, t("New registration pending for approval"), $html_message);
}

/**
 * Get the group of the current logged user.
 *
 * @return string The user group if logged or guest if anonymous.
 */
function GetCurrentUserGroup()
{
    global $base_url;
    
    if(IsUserLogged())
    {
        return $_SESSION["logged"]["group"];
    }
    else
    {
        return "guest";
    }
}

/**
 * Get the current logged user.
 *
 * @return string The machine name of the logged user.
 */
function GetCurrentUser()
{
    global $base_url;
    
    if(IsUserLogged())
    {
        return $_SESSION["logged"]["username"];
    }
    else
    {
        return "Guest";
    }
}

/**
 * Protects a page from guess access redirecting to an access denied page.
 * Used on pages where the administrator should be logged in or user with
 * proper permissions.
 *
 * @param array $permissions In the format permissions[] = machine_name
 */
function ProtectPage($permissions = "")
{
    if(IsAdminLogged())
    {
        return;
    }
    elseif($permissions != "")
    {
        $group = GetCurrentUserGroup();
        
        foreach($permissions as $machine_name)
        {
            if(!\JarisCMS\Group\GetPermission($machine_name, $group))
            {
                \JarisCMS\System\GetHTTPStatusHeader(401);
                \JarisCMS\System\GoToPage("access-denied");
            }
        }

        return;
    }

    \JarisCMS\System\GetHTTPStatusHeader(401);
    \JarisCMS\System\GoToPage("access-denied");
}

/**
 * Check if a user has the given permissions.
 *
 * @param array $permissions In the format permissions[] = machine_name
 * @param string $username If not specified current user permissions are checked.
 *
 * @return bool True if has permissions false otherwise.
 */
function HasUserPermissions($permissions, $username=null)
{
    if(!IsAdminLogged())
    {
        $group = GetCurrentUserGroup();
        
        if($username != null)
        {
            $user_data = \JarisCMS\User\GetData($username);
            $group = $user_data["group"];
        }
        
        foreach($permissions as $machine_name)
        {
            if(!\JarisCMS\Group\GetPermission($machine_name, $group))
            {
                return false;
            }
        }
    }

    return true;
}
?>
