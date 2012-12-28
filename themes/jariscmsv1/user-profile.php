<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
*/
?>

<table class="user-profile">
	<tr>
	<?php if($user_data["picture"]){ ?>
		<td class="picture">
			<img src="<?php print JarisCMS\URI\PrintURL("image/user/$username") ?>" />
		</td>
	<?php } else { ?>
		<td class="picture">
			<?php if($user_data["gender"] == "m"){ ?>
			<img src="<?php print JarisCMS\URI\PrintURL("styles/images/male.png") ?>" />
			<?php } else{ ?>
			<img src="<?php print JarisCMS\URI\PrintURL("styles/images/female.png") ?>" />
			<?php } ?>
		</td>
	<?php } ?>
		
	<?php if($personal_text){ ?>
		<td class="personal-text">
			<?php print $personal_text ?>
		</td>
	<?php } ?>
	
		<td class="details">
			<div><b><?php print t("Member since:") . "</b> " . $register_date ?></div> 
			<div><b><?php print t("Gender:") . "</b> " . $gender ?></div>
			<div><b><?php print t("Birth date:") . "</b> " . $birth_date ?></div>
		</td>
	</tr>
</table>

<?php print $latest_post ?>