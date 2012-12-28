<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 * 
 *@file Jaris CMS module uninstall file
 *
 *Stores the uninstall script for blog module.
 */

namespace JarisCMS\Module\Blog;

function Uninstall()
{
	//Remove related blog block
	JarisCMS\Block\DeleteByField("block_name", "blog_user_archive");
    
    //Remove recent user post block
	JarisCMS\Block\DeleteByField("block_name", "blog_recent_user_posts");
    
    //Remove recent blog block
	JarisCMS\Block\DeleteByField("block_name", "blog_new_blogs");
    
    //Remove most viewed blog block
	JarisCMS\Block\DeleteByField("block_name", "blog_most_viewed_blogs");
    
    //Remove navigate by categories block
	JarisCMS\Block\DeleteByField("block_name", "blog_categories_blogs");
}

?>
