<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
*/
?>
<html>

<head>
<title><?php print $title; ?></title>
<?php print $header_info ?>
<?php print $meta ?>
<!--[if IE]>
<link rel="stylesheet" type="text/css" href="<?php print $theme_path; ?>/ie.css" />
<![endif]-->
<?php print $styles ?>
<?php print $scripts ?>
</head>

<body>

<center>
<div style="width: 1014px; background-color: #ffffff;">

<!--Header-->
<div class="top-menu">
    <?php print $secondary_links ?>
</div>

<table id="header">
    <tr>
        <td class="logo"></td>
        <td class="imagen"></td>
    </tr>
</table>

<div id="top-bar" style="text-align: left;">
    <?php print $primary_links ?>
</div>

<table id="content">
    <tr>
        <?php if($left){ ?>
        <td class="left">
            <?php echo $left; ?>
        </td>
        <?php }?>

        <td class="center">
            <h1><?php print $content_title; ?></h1>
            
            <?php if($breadcrumb){?>
            <div id="breadcrumb"><?php print $breadcrumb; ?></div>
            <?php } ?>

            <?php if($messages){?>
            <div id="messages"><?php print $messages; ?></div>
            <?php } ?>

            <?php if($tabs){?>
            <div id="tabs-menu"><?php print $tabs; ?></div>
            <?php } ?>

            <?php print $content; ?>
        </td>

        <?php if($right){ ?>
        <td class="right">
            <?php echo $right; ?>
        </td>
        <?php }?>
    </tr>
</table>

<?php if($footer){ ?>
<table id="footer">
    <tr>
    <?php echo $footer; ?>
    </tr>
</table>
<?php } ?>

<div id="footer_message">
    <?php echo $footer_message; ?>
</div>

</div>
</center>

</body>

</html>
