<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the administration page for whizzywig.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("File browser") ?>
	field;

	field: content
		<?php 
            $groups = get_setting("groups", "whizzywig")?unserialize(get_setting("groups", "whizzywig")):false;	

        	//Check if current user is on one of the groups that can use the editor
        	if($groups)
        	{
        		$user_is_in_group = false;
        		foreach($groups as $machine_name=>$value)
        		{
        			if(current_user_group() == $machine_name && $value)
        			{
        				$user_is_in_group = true;
        				break;
        			}
        		}
        		
        		if(!is_admin_logged() && !$user_is_in_group)
        		{
        			exit;
        		}
        	}
            
            $rtnfield = "lf_url";
            
            if($_REQUEST['element_id'])
            {
            	$rtnfield = "lf_url" . $_REQUEST['element_id']; 
            }
            
            $module_url = print_url("modules/whizzywig/whizzywig");
            
            $uri = $_REQUEST["uri"];
        ?>
        
        <script type="text/javascript">
            function WantThis(url) 
            {
                window.opener.document.getElementById('<?php echo $rtnfield; ?>').value = url;
                window.close();
            }
        </script>
       
        <div id="files" >
        
        <?php print t("Click a name below to select.") ?><br>
        
        <?php
            $files = get_file_list($uri);
            
            if($files || !uri)
            {
            	foreach ($files as $file) 
            	{
            		$url = print_url("file/$uri/{$file['name']}");
            		$flist .= "<div style='float:left;width:20em'><a href='#' onclick='WantThis(\"$url\")'>{$file['name']}</a></div>";
            	}
            	echo $flist;
            }
            else
            {
            	print "<h2>" . t("No files available.") . "</h2>";
            }
        ?>
        </div>
	field;

	field: is_system
		1
	field;
row;
