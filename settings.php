<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file
 * Main web site configurations
 *
 *@note In case data/settings/main.php doesnt exist this configurations are used
 *      so you can have the flexibility of controlling main configuration from
 *      a file.
 */

//Web site title
$title = 'Jaris CMS';

//Main url of the web site without trailing slash
$base_url = 'http://localhost';

//Optional slogan for placement anywhere on page template
$slogan = 'Where performance matters!';

//Wether to enable user profiles
$user_profiles = false;

//Optional footer message for the page
$footer_message = 'Powered by <img title="File Based CMS" src="$base_url/themes/jariscmsv1/images/logo_icon_ie.png" />';

//Default theme
$theme = 'jariscmsv1';
$theme_path = $base_url . '/themes/' . $theme;

//Enables clean url system. from http://yourpage.com/?p=url to http://yourpage.com/url (only works on apache with url_mod_rewrite enabled)
$clean_urls = true;

//Default language for the web site.
$language = 'en';
?>
