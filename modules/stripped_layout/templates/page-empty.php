<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 * 
 */
?>
<!DOCTYPE html>
<html lang="<?php print JarisCMS\Language\GetCurrent() ?>">

<head>
<title><?php print $title; ?></title>
<?php print $header_info ?>
<?php print $meta ?>
<?php print $styles ?>
<?php print $scripts ?>
</head>


<body>
<?php print $content; ?>
</body>

</html>
