<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Holds the current version of Jaris CMS as a changelog for reference
 */

define("JARIS_CMS_VERSION", "4.9.8 MS");

/**

=Change Log=

Version 4.9.8 - Date 05/02/2013
 
	* New option to force logins with ssl if supported.

Version 4.9.7 - Date 27/01/2013
 
	* Now themes gotta be enabled.
    * Blocks can be positioned per theme.

Version 4.9.6 - Date 02/01/2013
 
	* Added new feature to enable selection of account type at registration.
    * Added validation field to registration form.

Version 4.9.5 - Date 17/11/2012
 
    * Added custom format support for date fields.
    * Updated jdpicker to work with newer jquery library.

Version 4.9.4 - Date 17/11/2012
 
    * Added JarisCMS\Sqlite\Turbo to JarisCMS\Page\CountView increasing performance in about 85%
    * Added JarisCMS\Sqlite\Turbo to JarisCMS\Search\ReindexCallback
    * Added JarisCMS\Sqlite\Turbo to users re-index page

Version 4.9.3 - Date 14/10/2012
    
    * Fixes to check boxes on form generation functions.
    * Added progress bar and concurrent support to file uploads.
    * Small fix to print file functions.

Version 4.9.2 - Date 10/10/2012
    
    * Added uri autocompletion
    * Added visual sorting of blocks, menus, categories, types, etc...
    * Updated Email\Send function to use attacments array index as file name if not integer.

Version 4.9.1 - Date 26/09/2012

    * Itialian translation (thanks to Andrea Zanellato <zanellato.andrea@gmail.com>)
    * Improvements on language structure by Andrea Zanellato.
    * Adjusments to language.php as needed for new structure.
    * Removed empty string listed on language strings edition.
    * Added language details capability
    * Added counting of views by current day, week and month
    * Improved custom file upload field functionality
    * Added language po files caching for faster retrieval.
    * Added optional data caching for better performance on embedded devices like the sheevaplug.
    * Added image upload for as a content type custom filed.
    * Fixes to content types file uploads custom field
    * Fixes to cronjob to be able to run them in cli mode

Version 4.9.0 - Date 23/09/2012

    * User can login with email also instead of username
    * Fixed blocks lists management page appeareance
    * Fixed menu lists management page appeareance
    * Added maximum amount of posts per content type and users group

Version 4.8.22 - Date 23/06/2012

    * Added ability for user profiles

Version 4.8.21 - Date 19/05/2012

    * Added re-indexetion of users database

Version 4.8.20 - Date 29/03/2012

    * Added support to add file upload fields to content types
    * Added meta titles to creation of pages

Version 4.8.19 - Date 24/01/2012

    * New permission: Can edit all users content
    * New function to generated Control Center: generate_admin_page_sections();

Version 4.8.18 - Date 18/12/2011

    * Removed use of deprecated pass by reference on module hook_module() function
    * Improved admin/pages/list

Version 4.8.17 - Date 29/11/2011

    * Added template support to search results
    * Added new function jaris_sqlite_close_result($result); to not 
      forget that in some cases results need to be unset to unlock database.
    * Added numeration to menus generated on theme_links ex: l1, l2, l3
    * Other things I forgot.

Version 4.8.16 - Date 15/8/2011
    
    * Improved aspect ratio calculation for scaled images
    * Added content type option for content block templates

Version 4.8.15 - Date 27/6/2011
    
    * When adding or editing menu made the url optional and automatically generated if left blank
    * Fixed multiple add_messages on form validation.
    * Modified send_email function to permit a $from variable
    * Protected access to images and files when the page user access options are set to certain groups.
    * Added image quality option into settings page for jpeg processing

Version 4.8.14 - Date 16/5/2011
    
    * Added email checking on registration and users add page to see if it is already in use
    * Disabled breadcrumbs since a bug needs to be fixed

Version 4.8.13 - Date 20/3/2011
    
    * Added position select box on content blocks edit page
    * Added simple by following a uri paths breadcrumb support
    * Also added hidden_parameters functionality for the breadcrumbs

Version 4.8.12 - Date 10/3/2011

    * Added has_permission method on jaris_sqlite_search class to use on search_engine database.
    * Fixed security issue of search page displaying results of content where user has no permissions
    * by using the new has_permission function on the search_content functions.

Version 4.8.11 - Date 20/2/2011

    * Adjusted send_mail function to use utf-8
    * Fixed a security issue on the jaris_sqlite_get_data_list that had possibility of sql injections
    * Also fixed security issues on search functions where the $_REQUEST[page] parameter could be used for sql injections.

Version 4.8.10 - Date 18/1/2011

    * Modified get_years_array to sort years in reverse order
    * Fixed user_login function to lowercase username
    * Other minor changes

Version 4.8.9 - Date 19/12/2010

    * Improved post blocks options.
    * Improved print_content_preview function.

Version 4.8.8 - Date 29/11/2010

    * Fixed search results that were cutting words, when searching by category.
    * Added dusplay suspensive points to print_content_preview function.
    * Fixed highlight_search_results and added 'type' argument to know if displaying suspensive points is needed.
    * Modified search page.

Version 4.8.7 - Date 4/11/2010

    * Added register link on login page.
    * slogan now supports php.
    * Updated installer script.

Version 4.8.6 - Date 27/09/2010

    * Changed admin/user title to My Account and admin/user/edit title to My Account Details
    * Modified cron.php to check if cron is already running and skip execution.
    * Fixed username from case sensitive to case insesitive to prevent multiple registrations of same username with different cases
    * Fixed bug on categories menu not sorting
    * Fixed terrible bug that created categories folder with strange permissions because of passing permissions as string "0755" instead of 0755
    * Security issues addressed on edit user page
    * Added SQLite3 database connector since pdo seems to lock databases in some systems on multiple access like ubuntu
    * New convert special characters of uploaded filenames to friendly uris (to stop problem of different file name encodings when moving from linux to windows or viceversa)
    * If page doesn't exist instead of redirect the create page to admin/pages/add redirect to admin/pages/types
    * Added edit button to content blocks
    * Added sqlite function to attach databases to already open ones
    * Added option to export users database to csv
    * Fixed minor bug on admin/user/content (type not passed to navigation links)
    * Other minor improvements

Version 4.8.5 - Date 07/09/2010

    * Fixed small bug on caching functions trying to cache visual uris

Version 4.8.4 - Date 25/08/2010

    * Added function hooks on theme_styles and theme_scripts
    * Imrpoved page caching functionality
    * Fixed user edit bug logging out user if not changed password
    * Fixed bug on add page block not enforcing description
    * Added translations importer

Version 4.8.3 - Date 15/08/2010

    * Added function on forms.php to check if a string is a valid float or integer number.
    * Added theme_content function hook to be able to modify content output
    * Added results title h2 on search page
    * Fixed JarisCMS\Search\StripHTMLTags function to optionally allow object and embed tags
    * Improved category get_subcategory_list functions performance by caching data
    * Added main category sortings option
    * Added optional permission to enable or disable user from etering meta tags
    * Improved custom type fields options
    * Display images by name and not by id on most parts
    * Added color and date picker support to custom type fields
    * Added caching capability to jaris cms

Version 4.8.2 - Date 11/08/2010

    * Removed author column from my content page.
    * Moved logout tab to the end on My account page.
    * Added option to add terms and conditions to registrations
    * Added return argument to register and login page so after a successful register or login action user is redirected back to where it was
    * Added permission option to enable disable user from editing the width of uploaded images at compression box
    * Added gender and birth date at registration
    * Added get array functions for dates, months and years
    * Added index to users database for incremented query speed
    * Added uri field on the search database index
    * Added print content preview function
    * Fixed image htmlhex_to_rgb function returning bad values
    * Added ability to change title and content, label and descriptions to custom types when adding or editing content.

Version 4.8.1 - Date 03/08/2010

    * Made the validation of user ip on session validation optional at settings page
    * Added pdo_sqlite on php.ini to enable sqlite3 support on windows and linux

Version 4.8.0 - Date 10/07/2010

    * Fixed function JarisCMS\Security\IsUserLogged check on user logged site returning false when logged from non www and clicking on www links

Version 4.7.9 - Date 28/06/2010

    * Added new option to content types to restrict amount of files and images uploaded per post.

Version 4.7.8 - Date 12/06/2010

    * Fixed a bug on data_writer function where it doesnt unlocks a file if it cant write to it.
    * Fixed bug on user_has_permissions not getting permissions of specified user
    * Fixed bug on upgrade module function not replacing _ by -
    * Added ob_clean() to JarisCMS\System\GoToPage to clean any previous output for added protection
    * Removed unnecessary statement from get_data_path
    * The page views count is done in separate file to protect page data file from possible corruption
    * Added validation field to check if human is filling a form.

Version 4.7.7 - Date 12/06/2010

    * Now sqlite functions try to use sqlite3 when possible instead of sqlite2
    * New sqlite turbo mode function
    
Version 4.7.6 - Date 11/06/2010

    * Improved search performance

Version 4.7.5 - Date 09/06/2010

    * Added slogan support

Version 4.7.4 - Date 05/06/2010

    * Improved search system to mainly use sqlite
    * Added the ability to show different fields for different content types on search results
    * Translated new strings
    * Many more things

Version 4.7.3 - Date 23/05/2010

    * Improved permissions system
    * Many fixes and changes

Version 4.7.2 - Date 17/05/2010

    * Improved system functions
    * Added UDF to sqlite to search text

Version 4.7.1 - Date 10/05/2010

    * New feature cron jobs
    * Fixed JarisCMS\URI\PrintURL function tu support arguments on real exisiting paths

Version 4.7.0 - Date 07/05/2010

    * New feature pages list view
    * Fixed not add page to search_engine database when is system

Version 4.6.2 - Date 24/04/2010

    * Bug on hook_set_group_permission passing incorrect variable name
    * Fixed Control Center Page to allow access to registered users
    * Improved generate_admin_page function to support Control Center fix
    * Added new JarisCMS\SQLite\EscapeArray function to escape single quotes


Version 4.6.1 - Date 14/04/2010
    
    * Bug on translate content not serializing groups access array

**/

?>
