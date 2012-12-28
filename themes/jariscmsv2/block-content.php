<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
*/
?>
<div class="content-block content-block-<?php print $id ?>">
	<div class="title">
	<?php 
		if($post)
			print $post_title; 
		else
			print $title;
	?>
	</div>
	
	<div class="content">
		<?php if($image){ ?>
		<div class="block-image-thumbnail">
			<?php print $image ?>
		</div>
		<?php } ?>
	
		<?php print $content ?>
	</div>
	
	<?php if($post){ ?>
	<div class="clear"></div>
	<?php } ?>
	
	<?php if($view_more){ ?>
	<div class="block-view-more">
		<?php print $view_more ?>
	</div>
	<?php } ?>
</div>
