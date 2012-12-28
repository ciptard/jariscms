<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the ie update script.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		IE update script
	field;

	field: content
        //<script>
        $(document).ready(function(){
           
           if($.browser.msie)
    		{	
				if(parseInt($.browser.version) <= 7)
				{
					$("body").prepend($(
                        '<div id="ie-update-popup">' +
                            '<div class="close"><a title="<?php print t("close") ?>" href="#">X</a></div>' +
                            '<div style="clear: both"></div>' +
            				'<center>' +
            				'<h3 class="message"><?php print t("Your current browser is not supported. Please upgrade to one of the following:") ?></h3>' +
            				'<a href="http://microsoft.com/windows/internet-explorer/" target="_blank"><img style="border: 0" alt="Internet Explorer 8+" src="<?php print JarisCMS\URI\PrintURL("modules/ieupdate/images/internet8.png") ?>" /></a>' +
            				'<a href="http://www.mozilla.com/" target="_blank"><img style="border: 0" alt="Mozilla Firefox" src="<?php print JarisCMS\URI\PrintURL("modules/ieupdate/images/mozilla.png") ?>" /></a>' +
            				'<a href="http://www.google.com/chrome" target="_blank"><img style="border: 0" alt="Google Chrome" src="<?php print JarisCMS\URI\PrintURL("modules/ieupdate/images/chrome.png") ?>" /></a>' +
            				'<a href="http://www.opera.com/browser/" target="_blank"><img style="border: 0" alt="Opera Browser" src="<?php print JarisCMS\URI\PrintURL("modules/ieupdate/images/opera-browser.png") ?>" /></a>' +
            				'<a href="http://www.apple.com/safari/" target="_blank"><img style="border: 0" alt="Safari Browsser" src="<?php print JarisCMS\URI\PrintURL("modules/ieupdate/images/safari-browser.png") ?>" /></a>' +
            				'</center>' +
                        '</div>'
                    ).hide().fadeIn());
                    
                    $("#ie-update-popup .close a").click(function(){
                        $("#ie-update-popup").fadeOut();
                    }); 
                    
                    $("#ie-update-popup").css("left", ($(window).width() / 2) - ($("#ie-update-popup").width() / 2) + "px");
                    
                    $("#ie-update-popup").css("top", ($(window).height() / 2) - ($("#ie-update-popup").height() / 2) + "px");
                }
            }           
        });
        //</script>
	field;

	field: is_system
		1
	field;
row;
