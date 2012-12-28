<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the site settings management page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("Control Center") ?>
	field;

	field: content
		<?php
			//Stop unauthorized access
			if(!JarisCMS\Security\IsUserLogged())
			{
				JarisCMS\Security\ProtectPage();
			}
		?>
		
		<script type="text/javascript" src="<?php print JarisCMS\URI\PrintURL("scripts/optional/chili-1.7.pack.js") ?>"></script>
		<script type="text/javascript" src="<?php print JarisCMS\URI\PrintURL("scripts/optional/jquery.easing.js") ?>"></script>
		<script type="text/javascript" src="<?php print JarisCMS\URI\PrintURL("scripts/optional/jquery.dimensions.js") ?>"></script>
		<script type="text/javascript" src="<?php print JarisCMS\URI\PrintURL("scripts/optional/jquery.accordion.js") ?>"></script>
		
		<script type="text/javascript">
			jQuery().ready(function(){
				jQuery('div.administration-list').accordion({ 
				    header: 'h2',
				    autoheight: false,
				    active: false,
				    alwaysOpen: false
				});
			});
		</script>
	
		<?php
			$sections = JarisCMS\System\GenerateAdminPageSection();
            
            JarisCMS\System\GenerateAdminPage($sections);
		?>
	field;

	field: is_system
		1
	field;
row;
