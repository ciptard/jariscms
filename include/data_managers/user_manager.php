<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file The functions to manage users.
 */

namespace JarisCMS\User;

/**
 * Adds a new username record to the system.
 *
 * @param stirng $username The username to create on the system.
 * @param string $group The group where the user is going to belong.
 * @param array $fields An array with the needed fields to write to the username.
 * @param array $picture An array in the format returned by $_FILES["element"] array.
 *
 * @return string "true" string on success error message on false.
 */
function Add($username, $group, $fields, $picture=null)
{
    $username = strtolower($username);
    $user_exist = Exists($username);
    
    $fields["group"] = $group;

    if(!$user_exist)
    {
        //Call \JarisCMS\User\Add hook before adding the user
        \JarisCMS\Module\Hook("User", "Add", $username, $group, $fields, $picture);

        $user_data_path = GeneratePath($username, $group);

        //Create user directory
        $path = str_replace("data.php", "", $user_data_path);
        \JarisCMS\FileSystem\MakeDir($path, 0755, true);

        //If uploaded picture save it
        if(isset($picture["tmp_name"]) && trim($picture["tmp_name"]) != "")
        {
            $picture_path = $path . $picture["name"];

            $picture_name = \JarisCMS\FileSystem\MoveFile($picture["tmp_name"], $picture_path);

            $fields["picture"] = $picture_name;
        }

        //Encrypt user password
        $fields["password"] = crypt($fields["password"]);

        if(!\JarisCMS\PHPDB\Add($fields, $user_data_path))
        {
            return \JarisCMS\System\GetErrorMessage("write_error_data");
        }
    }
    else
    {
        return \JarisCMS\System\GetErrorMessage("Exists");
    }
    
    AddToDB($username, $fields);
    
    //Update cache_events folder
    if(!file_exists(\JarisCMS\Setting\GetDataDirectory() . "cache_events"))
    {
        \JarisCMS\FileSystem\MakeDir(\JarisCMS\Setting\GetDataDirectory() . "cache_events");
    }
    file_put_contents(\JarisCMS\Setting\GetDataDirectory() . "cache_events/new_user", "");

    return "true";
}

/**
 * Deletes an existing username.
 *
 * @param string $username The username to delete.
 *
 * @return bool True on success false on fail.
 */
function Delete($username)
{
    $username = strtolower($username);
    $user_exist = Exists($username);

    if($user_exist)
    {
        //Call delete_user hook before deleting the user
        \JarisCMS\Module\Hook("User", "Delete", $username, $user_exist["group"]);

        $user_data_path = $user_exist["path"];

        $user_path = str_replace("/data.php", "", $user_data_path);

        //Remove main user directory
        if(!\JarisCMS\FileSystem\RemoveDirRecursively($user_path))
        {
            return false;
        }
        
        RemoveUserFromDB($username);

        //Remove old data/users/group_name/X/XX if empty
        rmdir(\JarisCMS\Setting\GetDataDirectory() . "users/{$user_exist['group']}/" . substr($username, 0, 1) . "/" . substr($username, 0, 2));

        //Remove old data/users/group_name/X if empty
        rmdir(\JarisCMS\Setting\GetDataDirectory() . "users/{$user_exist['group']}/" . substr($username, 0, 1));
    }

    return true;
}

/**
 * Edits or changes the data of an existing user.
 *
 * @param string $username The username.
 * @param string $group A group where we want to change the user or the same actual group
 * @param array $new_data An array of the fields that will substitue the old values.
 * @param array $picture An array in the format returned by $_FILES["element"] array.
 *
 * @return string "true" string on success error message on false.
 */
function Edit($username, $group, $new_data, $picture=null)
{
    $username = strtolower($username);
    $user_exist = Exists($username);

    if($user_exist)
    {
        //Call Edit hook before editing the user
        \JarisCMS\Module\Hook("User", "Edit", $username, $user_exist["group"], $new_data, $picture);

        $user_data_path = $user_exist["path"];

        if(strlen($picture["tmp_name"]) > 0)
        {
            $path = str_replace("data.php", "", $user_data_path);
            $picture_path = $path . $picture["name"];

            //In case picture already exist delete it.
            unlink($picture_path);

            //Delete old picture
            unlink(GetAvatarPath($username));

            $picture_name = \JarisCMS\FileSystem\MoveFile($picture["tmp_name"], $picture_path);

            $new_data["picture"] = $picture_name;
        }

        if(!\JarisCMS\PHPDB\Edit(0, $new_data, $user_data_path))
        {
            return \JarisCMS\System\GetErrorMessage("write_error_data");
        }
        
        EditDB($username, $new_data);

        //Change user group
        if($group != $user_exist["group"])
        {
            $user_path = str_replace("/data.php", "", $user_data_path);

            $new_path = GeneratePath($username, $group);
            $new_path = str_replace("/data.php", "", $new_path);

            //Make new user path
            \JarisCMS\FileSystem\MakeDir($new_path, 0755, true);

            //Move user data to new group
            \JarisCMS\FileSystem\MoveDirRecursively($user_path, $new_path);

            //Remove old main user directory
            \JarisCMS\FileSystem\RemoveDirRecursively($user_path);

            //Remove old data/users/group_name/X/XX if empty
            rmdir(\JarisCMS\Setting\GetDataDirectory() . "users/{$user_exist['group']}/" . substr($username, 0, 1) . "/" . substr($username, 0, 2));

            //Remove old data/users/group_name/X if empty
            rmdir(\JarisCMS\Setting\GetDataDirectory() . "users/{$user_exist['group']}/" . substr($username, 0, 1));
        }
    }
    else
    {
        return \JarisCMS\System\GetErrorMessage("user_not_exist");
    }

    return "true";
}

/**
 * Get an array with data of a specific user.
 *
 * @param string $username The username.
 *
 * @return array|null An array with all the rows and fields of the username or null if not exists.
 */
function GetData($username)
{
    $username = strtolower($username);
    $user_exist = Exists($username);

    if($user_exist)
    {
        $user_data_path = $user_exist["path"];

        $user_data = \JarisCMS\PHPDB\Parse($user_data_path);

        if($user_data)
        {
            $user_data[0]["password"] = trim($user_data[0]["password"]);
            $user_data[0]["group"] = $user_exist["group"];
            $user_data[0]["picture"] = trim($user_data[0]["picture"]);

            //Call GetData hook before returning the user data
            \JarisCMS\Module\Hook("User", "GetData", $username, $user_data);

            return $user_data[0];
        }
    }
    else
    {
        return null;
    }
}

/**
 * Get an array with data of a specific user by its email.
 * 
 * @param type $email The email of the user.
 * 
 * @return array|bool User data array or false if fail.
 */
function GetDataByEmail($email)
{
    $email = str_replace("'", "''", $email);
    
    if(\JarisCMS\SQLite\DBExists("users"))
    {
        $db = \JarisCMS\SQLite\Open("users");
        $result = \JarisCMS\SQLite\Query("select * from users where email = '$email'", $db);
        $user_data_sqlite = \JarisCMS\SQLite\FetchArray($result);
        \JarisCMS\SQLite\Close($db);
        
        if($user_data_sqlite)
        {
            $user_data = GetData($user_data_sqlite["username"]);
            $user_data["username"] = $user_data_sqlite["username"];
            
            //Call GetDataByEmail hook before returning the user data
            \JarisCMS\Module\Hook("User", "GetDataByEmail", $user_data_sqlite["username"], $user_data);
            
            return $user_data;
        }
    }
    
    return false;
}

/**
 * Gets the path of the user picture.
 *
 * @param string $username The user we are getting the picture from.
 *
 * @return string|bool The path to the picture or false if not found.
 */
function GetAvatarPath($username)
{
    $username = strtolower($username);
    if($user_info = Exists($username))
    {
        $user_data = GetData($username);

        if($user_data && strlen($user_data["picture"]) > 0)
        {
            $user_picture = $user_info["path"];
            $user_picture = str_replace("data.php", "", $user_picture);
            $user_picture .= $user_data["picture"];

            return $user_picture;
        }
        else
        {
            return false;
        }
    }

    return false;
}

/**
 * Function to retrieve a user uploaded profile picture or
 * generic one in case none available.
 * 
 * @param string $username The login name of the user.
 * 
 * @return string Path of the user picture file.
 */ 
function GetAvatarURL($username)
{
    $username = strtolower($username);
    if($user_info = Exists($username))
    {
        $user_data = GetData($username);

        if($user_data && strlen($user_data["picture"]) > 0)
        {
            $user_picture = \JarisCMS\URI\PrintURL("image/user/$username");

            return $user_picture;
        }
        else
        {
            switch($user_data["gender"])
            {
                case "m":
                    return \JarisCMS\URI\PrintURL("styles/images/male.png");
                case "f":
                    return \JarisCMS\URI\PrintURL("styles/images/female.png");
                default:
                    return \JarisCMS\URI\PrintURL("styles/images/male.png");
                    
            }
        }
    }

    return false;
}

/**
 * Checks if a user already exists.
 * 
 * @param string $username The username to check for existence.
 *
 * @return array Array in the format array(path, group) if exist false if not.
 */
function Exists($username)
{
    $username = strtolower($username);
    $dir_handle = opendir(\JarisCMS\Setting\GetDataDirectory() . "users");
    
    if(!is_bool($dir_handle))
    {
        while(($group_directory = readdir($dir_handle)) !== false)
        {
            //just check directories inside
            if(strcmp($group_directory, ".") != 0 && strcmp($group_directory, "..") != 0)
            {
                $user_data_path = GeneratePath($username, $group_directory);
    
                if(file_exists($user_data_path))
                {
                    return array("path"=>$user_data_path, "group"=>$group_directory);
                }
            }
        }
     }

    return false;
}

/**
 * Add a username and its email to the users sqlite database.
 *
 * @param string $username The username used to log in on the system.
 * @param array $data All the user data to extract only the email.
 * 
 * @todo Move to Jaris\SQLite\AddUser?
 */
function AddToDB($username, $data)
{
    $username = strtolower($username);
    if(!\JarisCMS\SQLite\DBExists("users"))
    {
        $db = \JarisCMS\SQLite\Open("users");
        \JarisCMS\SQLite\Query("create table users (username text, email text, register_date text, user_group text, picture text, ip_address text, gender text, birth_date text, status text)", $db);
        
        \JarisCMS\SQLite\Query("create index users_index on users (username desc, email desc, register_date desc, user_group asc, gender desc, birth_date desc, status desc)", $db);
        
        \JarisCMS\SQLite\Close($db);
    }
    
    $db = \JarisCMS\SQLite\Open("users");
    $data["username"] = $username;
    \JarisCMS\SQLite\EscapeArray($data);
    \JarisCMS\SQLite\Query("insert into users (username, email, register_date, user_group, picture, ip_address, gender, birth_date, status) 
    values ('{$data['username']}','{$data['email']}','{$data['register_date']}','{$data['group']}','{$data['picture']}','{$data['ip_address']}','{$data['gender']}','{$data['birth_date']}','{$data['status']}')", $db);
    
    \JarisCMS\SQLite\Close($db);
}

/**
 * Edit an existing user email on the sqlite users database, used when updating user data.
 *
 * @param string $username The username used to log in. 
 * @param array $data All the data of the username to extract email.
 * 
 * @todo Move to Jaris\SQLite\EditUser?
 */
function EditDB($username, $data)
{
    $username = strtolower($username);
    if(\JarisCMS\SQLite\DBExists("users"))
    {
        $db = \JarisCMS\SQLite\Open("users");
        
        \JarisCMS\SQLite\EscapeArray($data);
        
        \JarisCMS\SQLite\Query("update users set 
        email = '{$data['email']}',
        user_group = '{$data['group']}',
        picture = '{$data['picture']}',
        ip_address = '{$data['ip_address']}',
        gender = '{$data['gender']}',
        birth_date = '{$data['birth_date']}',
        status = '{$data['status']}'
        where username = '$username'", $db);
        
        \JarisCMS\SQLite\Close($db);
    }
}

/**
 * To retrieve a list of users from sqlite database to generate users list page
 *
 * @param integer $page the current page count of users list the admin is viewing.
 * @param integer $limit The amount of users per page to display.
 * 
 * @return array Each username not longer than $limit
 * 
 * @todo Move to Jaris\SQLite\GetUserList?
 */
function GetListFromDB($page=0, $limit=30)
{
    $db = null;
    $page *=  $limit;
    $users = array();
        
    if(\JarisCMS\SQLite\DBExists("users"))
    {
        $db = \JarisCMS\SQLite\Open("users");
        $result = \JarisCMS\SQLite\Query("select username from users order by username asc limit $page, $limit", $db);
    }
    else
    {
        return $users;
    }
    
    $fields = array();
    if($fields = \JarisCMS\SQLite\FetchArray($result))
    {
        $users[] = $fields["username"];
        
        while($fields = \JarisCMS\SQLite\FetchArray($result))
        {
            $users[] = $fields["username"];
        }
        
        \JarisCMS\SQLite\Close($db);
        return $users;
    }
    else
    {
        \JarisCMS\SQLite\Close($db);
        return $users;
    }
}

/**
 * Removes a username from the users sqlite database.
 *
 * @param $username The username to delete.
 * 
 * @todo Move to Jaris\SQLite\RemoveUser?
 */
function RemoveUserFromDB($username)
{
    $username = strtolower($username);
    if(\JarisCMS\SQLite\DBExists("users"))
    {
        $db = \JarisCMS\SQLite\Open("users");
        \JarisCMS\SQLite\Query("delete from users where username = '$username'", $db);
        
        \JarisCMS\SQLite\Close($db);
    }
}

/**
 * Gets an array with the status messages and its id as stored on users database.
 * This function is useful when generating select elements on forms.
 * A user status can be Pending Approval, Active, Blocked.
 * 
 * @return array
 */
function GetListStatus()
{
    $status = array();
    
    $status[t("Active")] = "1";
    $status[t("Pending Approval")] = "0";
    $status[t("Blocked")] = "2";
    
    return $status;
}

/**
 * Resets the password of a user giving its username to search in.
 *
 * @param string $username The username of the user to resets its password.
 *
 * @return string "true" string on success or error message.
 */
function ResetPasswordByName($username)
{   
    $username = strtolower($username);
    $password = GeneratePassword();
    $user_data = GetData($username);
    $user_data["password"] = crypt($password);
    
    $message = Edit($username, $user_data["group"], $user_data);
    
    if($message == "true")
    {
        SendNewPasswordByEmail($username, $user_data, $password);
    }
    
    return $message;
}

/**
 * Resets the password of a user giving its email to search in.
 *
 * @param $email The email of the user to resets its password.
 *
 * @return string "true" string on success or error message.
 */
function ResetPasswordByEmail($email)
{
    $email = str_replace("'", "''", $email);
    
    if(\JarisCMS\SQLite\DBExists("users"))
    {
        $db = \JarisCMS\SQLite\Open("users");
        $result = \JarisCMS\SQLite\Query("select username from users where email = '$email'", $db);
        $data = \JarisCMS\SQLite\FetchArray($result);
        
        \JarisCMS\SQLite\Close($db);
        
        if(isset($data["username"]) && $data["username"] != "")
        {
            $password = GeneratePassword();
            $username = $data["username"];
            $user_data = GetData($username);
            $user_data["password"] = crypt($password);
    
            $message = Edit($username, $user_data["group"], $user_data);
            
            if($message == "true")
            {
                SendNewPasswordByEmail($username, $user_data, $password);
            }
            
            return $message;
        }
        else
        {
            return \JarisCMS\System\GetErrorMessage("user_not_exist");
        }
    }
    else
    {
        return \JarisCMS\System\GetErrorMessage("user_not_exist");
    }
}

/**
 * Generates a random password that can be used to reset originals user password.
 *
 * @return string A random password.
 */
function GeneratePassword()
{
    $password = str_replace(array("\$", ".", "/"), "",crypt(uniqid(rand(),1)));

    if(strlen($password) > 10)
    {
        $password = substr($password, 0, 10);
    }
    
    return $password;
}

/**
 * Sends user an email ntofication when he or she resets a password.
 *
 * @param string $username The current name used to log mailed also to the user.
 * @param array $user_data All the user data including its full name, email, etc.
 * @param string $password The new password wich will the user be able to log in again.
 * 
 * @return bool True on succes or false on fail.
 */
function SendNewPasswordByEmail($username, $user_data, $password)
{
    $username = strtolower($username);
    $to[$user_data["name"]] = $user_data["email"];
    $subject = t("Your password has been reset.");
    
    $url = \JarisCMS\URI\PrintURL("admin/user");
    
    $message = t("Hi") . " " . $user_data["name"] . "<br /><br />";
    $message .= t("Your current username is:") . " <b>" . $username . "</b><br />";
    $message .= t("The new password for your account is:") . " <b>" . $password . "</b><br />";
    $message .= t("Is recommended that you log in and change the password as soon as possible.") . "<br />";
    $message .= t("To log in access the following url:") . " <a href=\"$url\">" . $url . "</a>";
    
    return \JarisCMS\Email\Send($to, $subject, $message);
}

/**
 * Function that prints the content of admin/user and calls a hook for modules to be
 * able to modify user page content.
 */
function PrintPage()
{
    global $base_url;
    
    $tabs[t("Edit My Account")] = array("uri"=>"admin/users/edit", "arguments"=>array("username"=>\JarisCMS\Security\GetCurrentUser()));
    
    if(\JarisCMS\Setting\Get("user_profiles", "main"))
    {
        $tabs[t("View My Profile")] = array("uri"=>"user/" . \JarisCMS\Security\GetCurrentUser());
    }
    
    if(\JarisCMS\Group\GetPermission("add_content", \JarisCMS\Security\GetCurrentUserGroup()))
    {
        $tabs[t("My Content")] = array("uri"=>"admin/user/content");
    }
    
    $content = "";

    if(\JarisCMS\Security\IsAdminLogged())
    {
        $tabs[t("Control Center")] = array("uri"=>"admin/start");
        
        $content = "
        ". t("Welcome Administrator!") . "
        <br /><br />
        " . t("Now that you are logged in you can start modifying the website as you need.");
    }
    else
    {
        $content = "
        " . t("Welcome") . " " . \JarisCMS\Security\GetCurrentUser() . "!" . "
        <br /><br />
        " . t("Now that you are logged in you can enjoy the privileges of registered users on") . " " . str_replace("http://", "", $base_url) . ".";
    }
    
    //Call print user page hooks so modules can modify user page content
    \JarisCMS\Module\Hook("User", "PrintPage", $content, $tabs);
    
    foreach($tabs as $title=>$data)
    {
        if(!isset($data["arguments"]))
        {
            \JarisCMS\System\AddTab($title, $data["uri"], null, $data["row"]?$data["row"]:0);
        }
        else
        {
            \JarisCMS\System\AddTab($title, $data["uri"], $data["arguments"], $data["row"]?$data["row"]:0);
        }
    }
    
    \JarisCMS\System\AddTab(t("Logout"), "admin/logout");
    
    print $content;
}

/**
 * Used on initialization of index when an uri scheme like
 * user/username was used, to set needed arguments and target
 * page to display a user profile.
 * 
 * @param string $page 
 */
function ShowProfile(&$page)
{
    $sections = explode("/", $page);
    $username = $sections[1];
    
    $_REQUEST["username"] = $username;
    
    $page = "user";
    
    //Call show user profile intialization hook
    \JarisCMS\Module\Hook("User", "ShowProfile", $page, $username);
}

/**
 * Generates the data path for a username.
 *
 * @param string $username The username to translate to a valid user data path.
 * @param string $group The group user belongs to.
 * 
 * @return string Path to user data file.
 */
function GeneratePath($username, $group)
{
    $username = strtolower($username);
    
    //We use the generate page path function and substitue some values
    $user_data_path = \JarisCMS\Page\GeneratePath($username) . "/data.php";

    //substitute the data page path with the data users path
    $user_data_path = str_replace(\JarisCMS\Setting\GetDataDirectory() . "pages/singles", \JarisCMS\Setting\GetDataDirectory() . "users/$group", $user_data_path);

    return $user_data_path;
}

?>