<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 * 
 *@file Jaris CMS module install file
 *
 *Stores the installation script for ecommerce module.
 */

namespace JarisCMS\Module\GoogleTranslate;

function Install()
{	
	//Create block for shopping cart
	$translate_block["description"] = "google translate";
	$translate_block["title"] = "Translate";
	
	$translate_block_content = "
	<div id=\"google_translate_element\"></div><script>
	function googleTranslateElementInit() {
	  new google.translate.TranslateElement({
		pageLanguage: '<?php if(\$language = get_setting(\"input_language\", \"google_translate\")){print \$language;}else{print \"en\";} ?>'
	  }, 'google_translate_element');
	}
	</script><script src=\"http://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit\"></script>
	";
	
	$translate_block["content"] = $translate_block_content;
	$translate_block["order"] = "0";
	$translate_block["display_rule"] = "all_except_listed";
	$translate_block["pages"] = "";
	$translate_block["return"] = '';
	$translate_block["is_system"] = "1";
	$translate_block["block_name"] = "google_translate_block";
					
	JarisCMS\Block\Add($translate_block, "none");
    
    //Strings to enable translations programs to scan them
	$strings[] = t("Translate");
	
	//User notification
	JarisCMS\System\AddMessage(t("Remember to set the google translate configurations to work properly.") . " <a href=\"" . JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/settings/google-translate", "google_translate")) . "\">" . t("Configure Now") . "</a>");
}

?>