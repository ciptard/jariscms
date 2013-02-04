<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
*/
?>
<div class="block animated-block block-<?php print $id ?>">
    <?php if($title){ ?><div class="title"><?php print $title ?></div><?php } ?>
    
    <div class="content">
       <?php
            print $content;
            print JarisCMS\Module\AnimatedBlocks\PrintBlock($id, $position);
       ?>
    </div>
</div>
