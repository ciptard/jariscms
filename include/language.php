<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file All the functions related to the translation of strings
 */

namespace //Keep t() and dt() as global functions
{
    
/**
 * Uses the $language variable to search for a language file on the language
 * directory to translate a short string.
 *
 * @param string $textToTranslate Text that is going to be translated.
 * @param string $po_file Optional parameter to indicate specific po file to use
 *        relative to current language. Example: install.po
 *
 * @return string Translation if availbale or original.
 */
function t($textToTranslate, $po_file=null)
{
    global $language;

    //To reduce the parsing of the file and just parse once.
    static $lang;
    
    $textToTranslate = trim($textToTranslate);

    $translation = $textToTranslate;
    
    if(!$lang)
    {
        $files = array(); 
        
        //Add main website translations
        //In case that a module has a system translation we execute this later to keep original ones
        if($po_file == null)
        {
            $files[] = \JarisCMS\Setting\GetDataDirectory() . "language/" . $language . "/" . "strings.po";
        }
        else
        {
            $files[] = \JarisCMS\Setting\GetDataDirectory() . "language/" . $language . "/" . $po_file;
        }
        
        //Add activated modules translations if available
        foreach(\JarisCMS\Module\GetInstalledNames() as $module_dir)
        {
            $module_translation = "";
            
            if($po_file == null)
            {
                $module_translation = "modules/$module_dir/language/$language/" . "strings.po";
            }
            else
            {
                $module_translation = "modules/$module_dir/language/$language/" . $po_file;
            }
            
            if(is_file($module_translation))
                $files[] = $module_translation;
        }
        
        $lang = \JarisCMS\Language\GenerateCache($language, $files);
    }
    
    if($textToTranslate != "")
    {
        $available_translation = $lang[$textToTranslate];
        
        if($available_translation != "")
        {
            $translation = $available_translation;
        }
    }


    return $translation;
}

/**
 * Checks the existance of a data file translated to the language matching the
 * $language variable if available.
 *
 * @param string $data_file Original data file.
 * @param string $language_code To use optional language on path conversion instead of
 *        the global $language variable.
 * @param bool $force Force the function to always return the data path for a language
 *        even if doesnt exist.
 *
 * @return string Data file that contains the translation or original data file if
 *         translation not available.
 */
function dt($data_file, $language_code = null, $force = false)
{
    global $language;

    $new_data_file = "";

    if($language_code)
    {
        $new_data_file = \JarisCMS\Setting\GetDataDirectory() . "language/" . $language_code . "/" .  str_replace(\JarisCMS\Setting\GetDataDirectory() . "", "", $data_file);
    }
    else
    {
        $new_data_file = \JarisCMS\Setting\GetDataDirectory() . "language/" . $language . "/" .  str_replace(\JarisCMS\Setting\GetDataDirectory() . "", "", $data_file);
    }

    if(file_exists($new_data_file) || $force)
    {
        return $new_data_file;
    }
    else
    {
        return $data_file;
    }
}

}

namespace JarisCMS\Language
{

/**
 * Generates a cache file for a list of po files.
 * 
 * @param string $language The language of the file.
 * @param array $files List of po files to generate the cache.
 * @return array All string translations
 */
function GenerateCache($language, $files)
{
    $cache_file = \JarisCMS\Setting\GetDataDirectory(). "language_cache/$language";
    
    $lang = array();
    
    //Create cache file
    if(!file_exists($cache_file))
    {
        if(file_exists(\JarisCMS\Setting\GetDataDirectory() . "language/" . $language))
        {
            foreach($files as $file)
            {
                //Store the file path on the cache file to know later which files are cached
                $lang[$file] = md5_file($file);

                //Store translation strings
                $lang += ParsePO($file);
            }

            file_put_contents($cache_file, serialize($lang));
        }
    }
    
    //Use existing cache file and update if neccesary
    else
    {
        $lang = unserialize(file_get_contents($cache_file));
        
        //Check for not cached files
        $po_file_not_cached = false;
        
        foreach($files as $file)
        {
            if(!isset($lang[$file]) || $lang[$file] != md5_file($file))
            {
                $po_file_not_cached = true;
                $lang[$file] = md5_file($file);
                $lang = array_merge($lang, ParsePO($file));
            }
        }
        
        //Save new translation files to cache
        if($po_file_not_cached)
        {    
            file_put_contents($cache_file, serialize($lang));
            
            //In case a system translation is being overwritten by a module one
            //we re-overwrite it again with the system translations
            $system_translations = ParsePO(\JarisCMS\Setting\GetDataDirectory() . "language/" . $language . "/" . "strings.po");
            $lang = array_merge($lang, $system_translations);
            
            file_put_contents($cache_file, serialize($lang));
        }
    }
    
    return $lang;
}

/**
 * Retreive the available languages on the system.
 *
 * @return array All the available languages in the following format.
 * $language["code"] = "name", for example: $language["en"] = "English"
 */
function GetAll()
{
    $languages = array();

    $lang_dir = opendir(\JarisCMS\Setting\GetDataDirectory() . "language");
    
    if(!is_bool($lang_dir))
    {
        while(($file = readdir($lang_dir)) !== false)
        {
            $directory_array[] = $file;
        }
    }

    $found_language = false;
    if(is_array($directory_array))
    {
        foreach ($directory_array as $directory)
        {
            $current_directory = \JarisCMS\Setting\GetDataDirectory() . "language/" . $directory;
    
            if(is_dir($current_directory) && $directory != "template")
            {
                if(file_exists($current_directory . "/info.php"))
                {
                    include($current_directory . "/info.php");
                    $languages[$language["code"]] = $language["name"];
                    $found_language = true;
                }
            }
        }
    }

    //Always add english since it's the core language
    $languages["en"] = "English";


    return $languages;
}

/**
 * Retreive the language information on an array
 *
 * @return array|bool Languages info in the following format:
 * array("code"=>val, "name"=>val, "translator"=>val, "translator_email"=>val, "contributors"=>val)
 * if language couldn't be found returns false.
 */
function GetInfo($language_code)
{
    $lang_dir = \JarisCMS\Setting\GetDataDirectory() . "language/$language_code";
    
    if(is_dir($lang_dir))
    {
        if(file_exists($lang_dir . "/info.php"))
        {
            $language = array();
            
            include($lang_dir . "/info.php");
            
            return $language;
        }
    }
    
    return false;
}

/**
 * Gets the human readable name of a language code.
 *
 * @param string $language_code the machine code of the language.
 *
 * @return string The human readable name of language code.
 */
function GetName($language_code)
{
    $languages = GetAll();

    return $languages[$language_code];
}

/**
 * Checks the $_REQUEST["language"] and stores it's value
 * on $_SESSION["language"].
 *
 * @return string The language code to use on the $_SESSION if available or the default one.
 */
function GetCurrent()
{
    global $language;

    if(isset($_REQUEST["language"]))
    {
        $_SESSION["language"] = $_REQUEST["language"];
        return $_REQUEST["language"];
    }
    else if(isset($_SESSION["language"]))
    {
        return $_SESSION["language"];
    }
    else
    {
        if($language == "autodetect")
        {
            AutoDetect();
        }
        
        return $language;
    }
}

/**
 * Checks if a given language is available on the system.
 * 
 * @param string $code The language code to check if exists on the system.
 */
function Exists($code)
{
    if(file_exists(\JarisCMS\Setting\GetDataDirectory() . "language/$code"))
    {
        return true;
    }
    
    return false;
}

/**
 * Checks the user browser language and sets the global language variable to it if possible.
 */
function AutoDetect()
{
    global $language;
    
    if(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]))
    {
        $user_languages = explode(",", str_replace(" ", "", strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"])));
        
        foreach($user_languages as $user_language)
        {
            $language_code_array = explode(";", $user_language);
            
            $language_code = explode("-", $language_code_array[0]);
            
            if(count($language_code) > 1)
            {
                $glue = implode("-", $language_code);
                if(Exists($glue) || $glue == "en")
                {
                    $language = $glue;
                    return;
                }
                elseif(Exists($language_code[0]) || $language_code[0] == "en")
                {
                    $language = $language_code[0];
                    return;
                }
            }
            elseif(Exists($language_code[0]) || $language_code[0] == "en")
            {
                $language = $language_code[0];
                return;
            }
        }
    }
    
    //Default language in case no match found
    $language = "en";
}

/**
 * Retreive all existing languages.
 * 
 * @return array List of languages suitable to generate a select list on a form.
 */
function GetCodes()
{
    $codes["Afrikaans"] = "af";
    $codes["Albanian"] = "sq";
    $codes["Arabic (Algeria)"] = "ar-dz";
    $codes["Arabic (Bahrain)"] = "ar-bh";
    $codes["Arabic (Egypt)"] = "ar-eg";
    $codes["Arabic (Iraq)"] = "ar-iq";
    $codes["Arabic (Jordan)"] = "ar-jo";
    $codes["Arabic (Kuwait)"] = "ar-kw";
    $codes["Arabic (Lebanon)"] = "ar-lb";
    $codes["Arabic (libya)"] = "ar-ly";
    $codes["Arabic (Morocco)"] = "ar-ma";
    $codes["Arabic (Oman)"] = "ar-om";
    $codes["Arabic (Qatar)"] = "ar-qa";
    $codes["Arabic (Saudi Arabia)"] = "ar-sa";
    $codes["Arabic (Syria)"] = "ar-sy";
    $codes["Arabic (Tunisia)"] = "ar-tn";
    $codes["Arabic (U.A.E.)"] = "ar-ae";
    $codes["Arabic (Yemen)"] = "ar-ye";
    $codes["Arabic"] = "ar";
    $codes["Armenian"] = "hy";
    $codes["Assamese"] = "as";
    $codes["Azeri"] = "az";
    $codes["Basque"] = "eu";
    $codes["Belarusian"] = "be";
    $codes["Bengali"] = "bn";
    $codes["Bulgarian"] = "bg";
    $codes["Catalan"] = "ca";
    $codes["Chinese (China)"] = "zh-cn";
    $codes["Chinese (Hong Kong SAR)"] = "zh-hk";
    $codes["Chinese (Macau SAR)"] = "zh-mo";
    $codes["Chinese (Singapore)"] = "zh-sg";
    $codes["Chinese (Taiwan)"] = "zh-tw";
    $codes["Chinese"] = "zh";
    $codes["Croatian"] = "hr";
    $codes["Czech"] = "cs";
    $codes["Danish"] = "da";
    $codes["Divehi"] = "div";
    $codes["Dutch (Belgium)"] = "nl-be";
    $codes["Dutch (Netherlands)"] = "nl";
    $codes["English (Australia)"] = "en-au";
    $codes["English (Belize)"] = "en-bz";
    $codes["English (Canada)"] = "en-ca";
    $codes["English (Ireland)"] = "en-ie";
    $codes["English (Jamaica)"] = "en-jm";
    $codes["English (New Zealand)"] = "en-nz";
    $codes["English (Philippines)"] = "en-ph";
    $codes["English (South Africa)"] = "en-za";
    $codes["English (Trinidad)"] = "en-tt";
    $codes["English (United Kingdom)"] = "en-gb";
    $codes["English (United States)"] = "en-us";
    $codes["English (Zimbabwe)"] = "en-zw";
    $codes["English"] = "en";
    $codes["English (United States)"] = "us";
    $codes["Estonian"] = "et";
    $codes["Faeroese"] = "fo";
    $codes["Farsi"] = "fa";
    $codes["Finnish"] = "fi";
    $codes["French (Belgium)"] = "fr-be";
    $codes["French (Canada)"] = "fr-ca";
    $codes["French (Luxembourg)"] = "fr-lu";
    $codes["French (Monaco)"] = "fr-mc";
    $codes["French (Switzerland)"] = "fr-ch";
    $codes["French (France)"] = "fr";
    $codes["FYRO Macedonian"] = "mk";
    $codes["Gaelic"] = "gd";
    $codes["Georgian"] = "ka";
    $codes["German (Austria)"] = "de-at";
    $codes["German (Liechtenstein)"] = "de-li";
    $codes["German (Luxembourg)"] = "de-lu";
    $codes["German (Switzerland)"] = "de-ch";
    $codes["German (Germany)"] = "de";
    $codes["Greek"] = "el";
    $codes["Gujarati"] = "gu";
    $codes["Hebrew"] = "he";
    $codes["Hindi"] = "hi";
    $codes["Hungarian"] = "hu";
    $codes["Icelandic"] = "is";
    $codes["Indonesian"] = "id";
    $codes["Italian (Switzerland)"] = "it-ch";
    $codes["Italian (Italy)"] = "it";
    $codes["Japanese"] = "ja";
    $codes["Kannada"] = "kn";
    $codes["Kazakh"] = "kk";
    $codes["Konkani"] = "kok";
    $codes["Korean"] = "ko";
    $codes["Kyrgyz"] = "kz";
    $codes["Latvian"] = "lv";
    $codes["Lithuanian"] = "lt";
    $codes["Malay"] = "ms";
    $codes["Malayalam"] = "ml";
    $codes["Maltese"] = "mt";
    $codes["Marathi"] = "mr";
    $codes["Mongolian (Cyrillic)"] = "mn";
    $codes["Nepali (India)"] = "ne";
    $codes["Norwegian (Bokmal)"] = "nb-no";
    $codes["Norwegian (Nynorsk)"] = "nn-no";
    $codes["Norwegian (Bokmal)"] = "no";
    $codes["Oriya"] = "or";
    $codes["Polish"] = "pl";
    $codes["Portuguese (Brazil)"] = "pt-br";
    $codes["Portuguese (Portugal)"] = "pt";
    $codes["Punjabi"] = "pa";
    $codes["Rhaeto-Romanic"] = "rm";
    $codes["Romanian (Moldova)"] = "ro-md";
    $codes["Romanian"] = "ro";
    $codes["Russian (Moldova)"] = "ru-md";
    $codes["Russian"] = "ru";
    $codes["Sanskrit"] = "sa";
    $codes["Serbian"] = "sr";
    $codes["Slovak"] = "sk";
    $codes["Slovenian"] = "ls";
    $codes["Sorbian"] = "sb";
    $codes["Spanish (Argentina)"] = "es-ar";
    $codes["Spanish (Bolivia)"] = "es-bo";
    $codes["Spanish (Chile)"] = "es-cl";
    $codes["Spanish (Colombia)"] = "es-co";
    $codes["Spanish (Costa Rica)"] = "es-cr";
    $codes["Spanish (Dominican Republic)"] = "es-do";
    $codes["Spanish (Ecuador)"] = "es-ec";
    $codes["Spanish (El Salvador)"] = "es-sv";
    $codes["Spanish (Guatemala)"] = "es-gt";
    $codes["Spanish (Honduras)"] = "es-hn";
    $codes["Spanish (Mexico)"] = "es-mx";
    $codes["Spanish (Nicaragua)"] = "es-ni";
    $codes["Spanish (Panama)"] = "es-pa";
    $codes["Spanish (Paraguay)"] = "es-py";
    $codes["Spanish (Peru)"] = "es-pe";
    $codes["Spanish (Puerto Rico)"] = "es-pr";
    $codes["Spanish (United States)"] = "es-us";
    $codes["Spanish (Uruguay)"] = "es-uy";
    $codes["Spanish (Venezuela)"] = "es-ve";
    $codes["Spanish (Traditional Sort)"] = "es";
    $codes["Sutu"] = "sx";
    $codes["Swahili"] = "sw";
    $codes["Swedish (Finland)"] = "sv-fi";
    $codes["Swedish"] = "sv";
    $codes["Syriac"] = "syr";
    $codes["Tamil"] = "ta";
    $codes["Tatar"] = "tt";
    $codes["Telugu"] = "te";
    $codes["Thai"] = "th";
    $codes["Tsonga"] = "ts";
    $codes["Tswana"] = "tn";
    $codes["Turkish"] = "tr";
    $codes["Ukrainian"] = "uk";
    $codes["Urdu"] = "ur";
    $codes["Uzbek"] = "uz";
    $codes["Vietnamese"] = "vi";
    $codes["Xhosa"] = "xh";
    $codes["Yiddish"] = "yi";
    $codes["Zulu"] = "zu";

    return $codes;
}

/**
 * Generates an html form that enables to change the language.
 *
 * @return string The html form code.
 */
function GenerateForm()
{
    global $clean_urls, $page, $base_url, $language;
    $form = "<div class=\"language-form\">
    <form action=\"\" action=\"post\">";

    //Adds the current page as a parameter in case clean urls is disabled
    //to avoid the loss of the current page.
    if(!$clean_urls)
    {
        $form .= "<input type=\"hidden\" name=\"p\" value=\"$page\" />";
    }

    $form .= "<select name=\"language\">";

    if($languages = GetAll())
    {
        foreach ($languages as $code => $name)
        {
            if($language == $code)
            {
                $form .= "<option selected=\"selected\" value=\"$code\">$name</option>";
            }
            else
            {
                $form .= "<option value=\"$code\">$name</option>";
            }
        }
    }

    $form .= "</select>
            <input type=\"submit\" value=\"" . t("Submit") . "\" />
        </form></div>";

    return $form;
}

/**
 * Adds a new language to the system.
 *
 * @param string $language_code The code of the language, example: es
 * @param string $name Readable name of language, example: Español
 *
 * @return bool True if language created succesfully or false if not.
 */
function Add($language_code, $name, $translator, $translator_email, $contributors)
{
    $language_path = \JarisCMS\Setting\GetDataDirectory() . "language/" . $language_code;

    if(file_exists($language_path))
    {
        \JarisCMS\System\AddMessage(t("The language already exist."), "error");

        return false;
    }
    else
    {
        \JarisCMS\FileSystem\MakeDir($language_path, 0755, true);
        copy(\JarisCMS\Setting\GetDataDirectory() . "language/template/strings.pot", $language_path . "/strings.po");

        $language_info = $language_path . "/info.php";

        $content = "<?php\n";
        $content .= "\$language[\"code\"] = \"$language_code\";\n";
        $content .= "\$language[\"name\"] = \"$name\";\n";
        $content .= "\$language[\"translator\"] = \"".addcslashes($translator, '"')."\";\n";
        $content .= "\$language[\"translator_email\"] = \"".addcslashes($translator_email, '"')."\";\n";
        $content .= "\$language[\"contributors\"] = \"".addcslashes($contributors, '"')."\";\n";
        $content .= "?>";

        if(!file_put_contents($language_info, $content))
        {
            \JarisCMS\System\AddMessage(t("Language could not be added, check your write permissions on the <b>language</b> directory."), "error");

            return false;
        }
    }

    return true;
}

/**
 * Edit an existing language on the system.
 *
 * @param string $language_code The code of the language to edit.
 * @param string $translator The main translator of the language
 * @param string $translator_email The email of main translator
 * @param string $contributors List of contributors seperated by new lines
 * 
 * @return bool True if language was modified or false if not.
 */
function Edit($language_code, $translator, $translator_email, $contributors)
{
    $language_path = \JarisCMS\Setting\GetDataDirectory() . "language/" . $language_code;

    if(!file_exists($language_path))
    {
        return false;
    }
    else
    {
        $language_info = $language_path . "/info.php";

        $content = "<?php\n";
        $content .= "\$language[\"code\"] = \"$language_code\";\n";
        $content .= "\$language[\"name\"] = \"".GetName($language_code)."\";\n";
        $content .= "\$language[\"translator\"] = \"".addcslashes($translator, '"')."\";\n";
        $content .= "\$language[\"translator_email\"] = \"".addcslashes($translator_email, '"')."\";\n";
        $content .= "\$language[\"contributors\"] = \"".addcslashes($contributors, '"')."\";\n";
        $content .= "?>";

        if(!file_put_contents($language_info, $content))
        {
            return false;
        }
    }

    return true;
}

/**
 * Gets the amount of system strings translated, using the file
 * language/strings.po as reference for calculations.
 *
 * @param string $language_code The code of the language to check for translations.
 *
 * @return array In the format array("total_strings", "translated_strings", "percent")
 */
function GetTranslatedStats($language_code)
{
    //Check the template file for amount of strings
    $file = \JarisCMS\Setting\GetDataDirectory() . "language/template/strings.pot";
    $lang = ParsePO($file);

    $total_strings = count($lang);

    unset($lang);

    $file = \JarisCMS\Setting\GetDataDirectory() . "language/" . $language_code . "/strings.po";
    $lang = ParsePO($file);

    $translated_strings = 0;
    foreach($lang as $original=>$fields)
    {
        if(trim($fields) != "" || $original == "")
        {
            $translated_strings++;
        }
    }

    unset($lang);

    $percent = 0;
    if($translated_strings > 0)
    {
        $percent = round(($translated_strings / $total_strings) * 100, 2);
    }

    return array("total_strings"=>$total_strings,
    "translated_strings"=>$translated_strings, "percent"=>$percent);
}

/**
 * Retrieve all strings available for translation on a specific language.
 *
 * @param string $language_code The code of the language to retreive the strings.
 *
 * @return array In the format array[] = ("original"=>"text", "translation"=>"text")
 */
function GetStrings($language_code)
{
    $file = \JarisCMS\Setting\GetDataDirectory() . "language/" . $language_code . "/strings.po";

    $lang = ParsePO($file);

    $strings = array();
    foreach($lang as $index=>$string)
    {
        //Skip empty messages
        if(trim($index) != "")
            $strings[] = array("original"=>$index, "translation"=>$string);
    }

    unset($lang);

    return $strings;
}

/**
 * Adds or edits a current string on the language strings.po file.
 *
 * @param string $language_code The language code to add or edit string.
 * @param string $original_text The original english text of the string.
 * @param string $translation The translation of the english text.
 *
 * @return bool True if changes applied successfully of false if not.
 */
function AddString($language_code, $original_text, $translation)
{
    $file = \JarisCMS\Setting\GetDataDirectory() . "language/" . $language_code . "/strings.po";

    $lang = ParsePO($file);

    $lang[$original_text] = $translation;

    if(WritePO($lang, $file))
    {
        unset($lang);
        return true;
    }
    else
    {
        unset($lang);
        return false;
    }
}

/**
 * Remove a current string on the language strings.po file.
 *
 * @param string $language_code The language code to remove the string.
 * @param string $original_text The original english text of the string.
 *
 * @return bool True if changes applied successfully of false if not.
 */
function DeleteString($language_code, $original_text)
{
    $file = \JarisCMS\Setting\GetDataDirectory() . "language/" . $language_code . "/strings.po";

    $lang = ParsePO($file);

    unset($lang[$original_text]);

    if(WritePO($lang, $file))
    {
        unset($lang);
        return true;
    }
    else
    {
        unset($lang);
        return false;
    }
}

/**
 * Parses a .pot file generated by gettext tools.
 *
 * @param string $file The path of the file to translate.
 *
 * @return array In the format array["original text"] = "translation"
 */
function ParsePO($file)
{
    if(!file_exists($file))
    {
        return false;
    }

    $file_rows = file($file);

    $original_string = "";
    $translations = array();

    $found_original = false;

    foreach($file_rows as $row)
    {
        if(!$found_original)
        {
            if(substr(trim($row),0,6) == "msgid ")
            {
                $found_original = true;
                $string = str_replace("msgid ", "", trim($row));

                $pattern = "/(\")(.*)(\")/";
                $replace = "\$2";
                $string = preg_replace($pattern, $replace, $string);
                $string = str_replace(array("\\t", "\\n", "\\r", "\\0", "\\v", "\\f", "\\\\", "\\\""), array("\t", "\n", "\r", "\0", "\v", "\f", "\\", "\""), $string);

                $original_string = $string;
            }
        }
        else
        {
            if(substr(trim($row),0,7) == "msgstr ")
            {
                $found_original = false;
                $string = str_replace("msgstr ", "", trim($row));

                $pattern = "/(\")(.*)(\")/";
                $replace = "\$2";
                $string = preg_replace($pattern, $replace, $string);
                $string = str_replace(array("\\t", "\\n", "\\r", "\\0", "\\v", "\\f", "\\\\", "\\\""), array("\t", "\n", "\r", "\0", "\v", "\f", "\\", "\""), $string);

                $translations[$original_string] = $string;
            }
        }
    }

    unset($file_rows);

    return $translations;
}

/**
 * Parses a .pot file generated by gettext tools with extra information included.
 */
function ParsePOWithHeaders($file)
{
    //TODO: Implement this function
}

/**
 * Writes a simple .pot file for the use of jariscms.
 *
 * @param array $strings_array In the format array["original string"] = "translation"
 * @param string $file the path of the file to output.
 *
 * @return bool true on success false on fail.
 */
function WritePO($strings_array, $file)
{
    $content = "";

    foreach($strings_array as $original=>$translation)
    {
        $original = addcslashes($original, "\n\t\r\0\"\v\f\\");
        $content .= "msgid \"$original\"\n";

        $translation = addcslashes($translation, "\n\t\r\0\"\v\f\\");
        $content .= "msgstr \"$translation\"\n\n";
    }

    if(!file_put_contents($file, $content))
    {
        unset($content);
        return false;
    }

    unset($content);
    return true;
}

}
?>