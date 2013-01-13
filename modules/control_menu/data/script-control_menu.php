<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		Control menu script
	field;
	
	field: content
		<?php
            if(!JarisCMS\Security\IsUserLogged())
                return;
            
			$sections = JarisCMS\System\GenerateAdminPageSection();
			
			//Also get control center sections of modules
			JarisCMS\Module\Hook("System", "GenerateAdminPage", $sections);
			
			function control_menu_generate_menu_code($sections)
			{
				$html = "<div id=\"control-menu\">";
				$html .= "<a class=\"user\" href=\"".JarisCMS\URI\PrintURL("admin/user")."\">".JarisCMS\Security\GetCurrentUser()."</a>";
				$html .= "<ul>";
				
				foreach($sections as $section_details)
				{
					$html .= "<li>";

					if(count($section_details["sub_sections"]) > 0)
					{
						$html .= "<ul>";
						
						foreach($section_details["sub_sections"] as $fields)
						{
							$html .= "<li>";
							$html .= "<a href=\"{$fields['url']}\">{$fields['title']}</a>";
							$html .= "</li>";
						}
						
						$html .= "</ul>";
					}
					
					$html .= "<a>{$section_details['title']}</a></li>";
				}
				
				$html .= "</ul>";
				
				$html .= "<div class=\"right\">";
				
				if(JarisCMS\Security\IsAdminLogged())
				{
					$html .= "<a class=\"about\" title=\"".t("about jariscms")."\" href=\"".JarisCMS\URI\PrintURL("admin/settings/about")."\"></a>";
					
                    if($help_link = JarisCMS\Setting\Get("help_link", "control_menu"))
                    {
                        $html .= "<a class=\"help\" target=\"_blank\" title=\"".t("help")."\" href=\"".JarisCMS\URI\PrintURL($help_link)."\"></a>";
                    }
				}
				
				$html .= "<a class=\"logout\" title=\"".t("logout")."\" href=\"".JarisCMS\URI\PrintURL("admin/logout")."\"></a>";
				$html .= "</div>";
				
				$html .= "<div style=\"clear: both\"></div>";
				$html .= "</div>";
				
				return $html;
			}
		?>
		
		$(document).ready(function(){
			control_menu_html = $('<?php print control_menu_generate_menu_code($sections) ?>');
			control_menu_html.appendTo("body");
			$("body").css("padding-bottom", control_menu_html.height()+"px");
			control_menu_html.css("z-index", "100000");
			CalcControlMenuPosition(control_menu_html);
			
			$(window).resize(function(){
				CalcControlMenuPosition(control_menu_html);
			});
			
			if("ontouchstart" in document.documentElement){
				$(window).bind("touchstart", function(){
					CalcControlMenuPosition(control_menu_html);
				});
				
				$(window).scroll(function(){
					CalcControlMenuPosition(control_menu_html);
				});
			}
			
			$("#control-menu ul li a").click(function(){
				$(this).prev().slideToggle(100);
			});
		});
		
		function CalcControlMenuPosition(menu)
		{
			if("ontouchstart" in document.documentElement){
				window_width = window.innerWidth;
				window_height = window.innerHeight;
			}
			else{
				window_height = $(window).height();
				window_width = $(window).width();
			}
			
			menu_width = menu.innerWidth();
			menu_height = menu.innerHeight();
			
			menu.css("left", "0px");
			menu.css("top", (window_height - menu_height) + "px");
			menu.css("width", "100%");
		}
	field;

	field: is_system
		1
	field;
row;
