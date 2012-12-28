<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 * 
 */
?>
<html>

<head>
<title><?php print $title; ?></title>
<?php print $header_info ?>
<?php print $meta ?>
<?php print $styles ?>
<?php print $scripts ?>
</head>

<body>

<table id="content">
	<tr>
		<td class="center">
			<h1><?php print $content_title; ?></h1>

			<?php if($messages){?>
			<div id="messages"><?php print $messages; ?></div>
			<?php } ?>

			<?php print $content; ?>
		</td>
	</tr>
</table>

</body>


</html>
