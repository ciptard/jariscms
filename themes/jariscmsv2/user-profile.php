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
			<div><b><?php print t("Member since:") . "</b> " . date("d/m/Y", $user_data["register_date"]) ?></div> 
			<div><b><?php print t("Gender: ") . "</b> " . $gender ?></div>
			<div><b><?php print t("Birth date:") . "</b> " . $birth_date ?></div>
		</td>
	</tr>
</table>

<h3><?php print t("Latest post") ?></h3>

<?php

	$pages = JarisCMS\SQLite\GetDataList("search_engine", "uris", 0, 10, "where author='$username' order by created_date desc");

	print "<table class=\"navigation-list\">";
	print "<thead>";
	print "<tr>";
	print "<td>" . t("Title") . "</td>";
	print "<td>" . t("Date") . "</td>";

	print "</tr>";
	print "</thead>";

	foreach($pages as $data)
	{
		$page_data = JarisCMS\Page\GetData($data["uri"]);

		print "<tr>";

		print "<td><a href=\"" . JarisCMS\URI\PrintURL($data["uri"]) . "\">" . JarisCMS\System\PHPEval($page_data["title"]) . "</a></td>";

		print "<td>" . date("d/m/Y", $page_data["created_date"]) . "</td>";

		print "</tr>";
	}

	print "</table>";

?>
