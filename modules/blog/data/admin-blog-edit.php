<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the video settings management page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("Blog Settings") ?>
	field;

	field: content
		<?php

            if(!JarisCMS\Group\GetTypePermission("blog", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\Security\ProtectPage();
            }

			//Get exsiting settings or defualt ones if main settings table doesn't exist
			$blog_settings = JarisCMS\Module\Blog\GetSettings();

			if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("blog-edit"))
			{
                $fields["title"] = substr(JarisCMS\Search\StripHTMLTags($_REQUEST["title"]), 0, 80);
                $fields["description"] = substr(JarisCMS\Search\StripHTMLTags($_REQUEST["description"]), 0, 500);
                $fields["tags"] = substr(JarisCMS\Search\StripHTMLTags($_REQUEST["tags"]), 0, 300);
                $fields["category"] = $_REQUEST[$blog_settings["main_category"]][0];
                
                JarisCMS\Module\Blog\Create(JarisCMS\Security\GetCurrentUser());
                
                JarisCMS\Module\Blog\EditFromDB(JarisCMS\Security\GetCurrentUser(), $fields);
                
                JarisCMS\System\AddMessage(t("Blog settings successfully updated."));
                
				JarisCMS\System\GoToPage(JarisCMS\Module\GetPageURI("user/blog", "blog"));
			}
			elseif(isset($_REQUEST["btnCancel"]))
			{
				JarisCMS\System\GoToPage(JarisCMS\Module\GetPageURI("user/blog", "blog"));
			}
            
            $blog_data = JarisCMS\Module\Blog\GetFromDB(JarisCMS\Security\GetCurrentUser());

			$parameters["name"] = "blog-edit";
			$parameters["class"] = "blog-edit";
			$parameters["action"] = JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/blog/edit", "blog"));
			$parameters["method"] = "post";
            
            if($blog_settings["main_category"] != "")
            {
                $fields = JarisCMS\Category\GenerateFieldList(array("{$blog_settings['main_category']}"=>array($blog_data["category"])), $blog_settings["main_category"]);  
                $fields[0]["label"] = t("Category:"); 
            }
            
            $fields[] = array("type"=>"text", "limit"=>80, "name"=>"title", "label"=>t("Title:"), "id"=>"title", "value"=>$blog_data["title"], "description"=>t("The title or name of the blog."));
            $fields[] = array("type"=>"textarea", "name"=>"description", "limit"=>500, "label"=>t("Description:"), "id"=>"description", "value"=>$blog_data["description"], "description"=>t("A brief description of the blog."));
            $fields[] = array("type"=>"textarea", "name"=>"tags", "limit"=>300, "label"=>t("Tags:"), "id"=>"tags", "value"=>$blog_data["tags"], "description"=>t("A list of words seperated by space that describe the blog."));

			$fields[] = array("type"=>"submit", "name"=>"btnSave", "value"=>t("Save"));
			$fields[] = array("type"=>"submit", "name"=>"btnCancel", "value"=>t("Cancel"));

			$fieldset[] = array("fields"=>$fields);

			print JarisCMS\Form\Generate($parameters, $fieldset);

		?>
	field;

	field: is_system
		1
	field;
row;
