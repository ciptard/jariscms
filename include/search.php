<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Functions to search the content.
 * 
 * @todo Check ReindexCallback at line 267
 */

namespace JarisCMS\Search;

/**
 * Search for pages that match specific keywords.
 *
 * @param string $keywords The words to search for.
 * @param array $field_values An array of specific field with exact values in the format
 *                     fields["name"] = "value"
 * @param array $categories an array of categories to match the page.
 */
function Content($keywords, $field_values = null, $categories=array(), $page=1, $amount=10)
{
	// To protect agains sql injections be sure $page is a int
	if(!is_numeric($page))
	{
		$page = 1;		
	}
	else 
	{
		$page=intval($page);
	}
	
	if(!is_numeric($amount))
	{
		$amount = 10;		
	}
	else 
	{
		$amount=intval($amount);
	}
	
	ResetResults();
	
	if($keywords)
	{
		AddKeywords($keywords);
	}
	
    AddFields($field_values);
    AddCategories($categories);

	//We try to use sqlite database for better speed if possible
	if(\JarisCMS\SQLite\DBExists("search_engine"))
	{
		SQLiteDB($page, $amount);
	}
	
	//Else we search the entire pages directory
	else
	{
    	\JarisCMS\FileSystem\SearchFiles(\JarisCMS\Setting\GetDataDirectory() . "pages", "/.*data\.php/", "CheckPHPDBContent");
	}
}

/**
 * Instead of recursing the whole pages directory we open an sqlite database that stores all pages data
 * 
 * @param integer $page Current displayed page.
 * @param integer $amount The amount of results to display per page.
 */
function SQLiteDB($page=1, $amount=10)
{
	// To protect agains sql injections be sure $page is a int
	if(!is_numeric($page))
	{
		$page = 1;		
	}
	else 
	{
		$page=intval($page);
	}
	
	if(!is_numeric($amount))
	{
		$amount = 10;		
	}
	else 
	{
		$amount=intval($amount);
	}
	
	if(\JarisCMS\SQLite\DBExists("search_engine"))
	{
        $page -= 1;
		$page *= $amount;
        
        $db = \JarisCMS\SQLite\Open("search_engine");
        
        $type = GetContentType();
        
        $group = \JarisCMS\Security\GetCurrentUserGroup();
        
        //Search by keywords and categories
        if(count(GetKeywords()) > 0)
        {
            $keywords = implode(" ", GetKeywords());
            $keywords = str_replace("'", "''", $keywords);
            $categories = serialize(GetCategories());
            $categories = str_replace("'", "''", $categories);
            
			$order_clause = false;
            switch($_REQUEST["order"])
            {
                case "newest":
                    $order_clause = "order by created_date desc"; 
                    break;
                case "oldest":
                    $order_clause = "order by created_date desc"; 
                    break;
                case "title_desc":
                    $order_clause = "order by title desc"; 
                    break;
                case "title_asc":
                    $order_clause = "order by title asc"; 
                    break;
                default:
                    $order_clause = false;
                    break;
            }
            
            $select = "select 
            leftsearch(title, '$keywords') as title_relevancy, leftsearch(content, '$keywords') as content_relevancy,
            normalsearch(description, '$keywords') as description_normal, normalsearch(keywords, '$keywords') as keywords_normal,
            hascategories(categories, '$categories') as has_category,
            haspermission(groups, '$group') as has_permissions,
            uri from uris where
            ((title_relevancy > 0 or content_relevancy > 0 or
            description_normal > 0 or keywords_normal > 0) and has_category > 0 and has_permissions > 0) $type
            order by title_relevancy desc, content_relevancy desc,
            description_normal desc, keywords_normal desc limit $page, $amount";
            
            //Force ordering by user choice instead or relevancy
        	if($order_clause != false)
            {
				$select = "select 
				leftsearch(title, '$keywords') as title_relevancy, leftsearch(content, '$keywords') as content_relevancy,
				normalsearch(description, '$keywords') as description_normal, normalsearch(keywords, '$keywords') as keywords_normal,
				hascategories(categories, '$categories') as has_category,
				haspermission(groups, '$group') as has_permissions,
				uri from uris where
				((title_relevancy > 0 or content_relevancy > 0 or
				description_normal > 0 or keywords_normal > 0) and has_category > 0 and has_permissions > 0) $type
				$order_clause limit $page, $amount";
			}
            
            $result = \JarisCMS\SQLite\Query($select, $db);
            
            while($data = \JarisCMS\SQLite\FetchArray($result))
            {
                AddResult($data["uri"]);
            }
        }
        
        //Search by categories only
        else if(count(GetCategories()) > 0)
        {
            $categories = serialize(GetCategories());
            $categories = str_replace("'", "''", $categories);
            
            $order_clause = "";
            switch($_REQUEST["order"])
            {
                case "newest":
                    $order_clause = "order by created_date desc"; 
                    break;
                case "oldest":
                    $order_clause = "order by created_date desc"; 
                    break;
                case "title_desc":
                    $order_clause = "order by title desc"; 
                    break;
                default:
                    $order_clause = "order by title asc";
                    break;
            }
            
            $select = "select 
            hascategories(categories, '$categories') as has_category,
            haspermission(groups, '$group') as has_permissions,
            uri from uris where
            has_category > 0 and has_permissions > 0 $type $order_clause limit $page, $amount";
            
            $result = \JarisCMS\SQLite\Query($select, $db);
            
            while($data = \JarisCMS\SQLite\FetchArray($result))
            {
                AddResult($data["uri"]);
            }
        }
        
        \JarisCMS\SQLite\Close($db);
	}
}

/**
 * Regenerates the sqlite uri database from all the existent content on the system
 * by recursive searching the file system for content pages.
 */
function ReindexSQLite()
{
	if(\JarisCMS\SQLite\DBExists("search_engine"))
	{
		unlink(\JarisCMS\Setting\GetDataDirectory() . "sqlite/search_engine");
	}
	
	//Recreate database and table
	$db = \JarisCMS\SQLite\Open("search_engine");
	
	if(!$db)
	{
		return false;
	}
	
	\JarisCMS\SQLite\Query("create table uris (
        title text, 
        content text, 
        description text, 
        keywords text, 
        groups text, 
        categories text,
        input_format text,
        created_date text,
        last_edit_date text,
        last_edit_by text,
        author text,
        type text,
        views integer,
		views_day integer,
		views_day_count integer,
		views_week integer,
		views_week_count integer,
		views_month integer,
		views_month_count integer,
        uri text, 
        data text)", $db);
        
    \JarisCMS\SQLite\Query("create index uris_index on uris (
        title desc, 
        content desc, 
        description desc, 
        keywords desc, 
        categories desc,
        created_date desc,
        last_edit_date desc,
        author desc,
        type desc,
        views desc,
		views_day_count desc,
		views_week_count desc,
		views_month_count desc,
        uri desc)", $db);
	
	\JarisCMS\SQLite\Close($db);
	
	\JarisCMS\FileSystem\SearchFiles(\JarisCMS\Setting\GetDataDirectory() . "pages", "/.*data\.php/", "ReindexCallback");
	
	return true;
}

function ReindexCallback($content_path)
{
	//Obviate system pages from indexation using black list for performance improvement
	if(\JarisCMS\System\MakePagesBlacklist($content_path))
	{
		return;
	}
	
	$uri = \JarisCMS\FileSystem\GetURIFromPath(str_replace("/data.php", "", str_replace(\JarisCMS\Setting\GetDataDirectory() . "pages/", "", $content_path)));
	
	$page_data = \JarisCMS\Page\GetData($uri);
    
    $data = $page_data;
    $data["groups"] = serialize($data["groups"]);
    $data["categories"] = serialize($data["categories"]);
    
    $data["content"] = StripHTMLTags($data["content"]); 
    //Substitute some characters with spaces to improve search quality
    $data["content"] = str_replace(array(".", ",", "'", "\"", "(", ")"), " ", $data["content"]);
    //Remove repeated whitespaces
    $data["content"] = preg_replace('/\s\s+/', ' ', $data["content"]);
    $data["content"] = strtolower($data["content"]);
    
    //Get views
    $views_file = \JarisCMS\Page\GeneratePath($uri) . "/views.php";
    $views = \JarisCMS\PHPDB\GetData(0, $views_file);
    
    if(!$views["views"])
    {
        $data["views"] = "0";
    }
    else
    {
        $data["views"] = $views["views"];
    }
    
    \JarisCMS\SQLite\EscapeArray($data);
	
	if($page_data["is_system"])
	{
		return;
	}
    
    $page_data = str_replace("'", "''", serialize($page_data));
    $uri = str_replace("'", "''", $uri);
	
	$db = \JarisCMS\SQLite\Open("search_engine");
	\JarisCMS\SQLite\Turbo($db);
	
	\JarisCMS\SQLite\Query("insert into uris 
    (title, content, description, keywords, groups, categories, input_format, 
     created_date, last_edit_date, last_edit_by, author, type, views, uri, data) 
     
    values ('{$data['title']}', '{$data['content']}', '{$data['description']}', '{$data['keywords']}',
    '{$data['groups']}', '{$data['categories']}','{$data['input_format']}','{$data['created_date']}', 
    '{$data['last_edit_date']}', '{$data['last_edit_by']}', '{$data['author']}', '{$data['type']}', 
    {$data['views']}, '$uri', '$page_data')", $db);
	
	\JarisCMS\SQLite\Close($db);
}

function GetContentType()
{
    if(trim($_REQUEST["type"]) != "")
    {
        $type = str_replace("'", "''", $_REQUEST["type"]);
        return "and type='$type'";
    }
    
    return "";
}

/**
 *Checks a list of keywords and fields to match the content of a page database file.
 *
 *@param $content_path The path of the database file to check.
 *@param $content_data optional parameter to alarady pass page data to no open each file every time.
 */
function CheckPHPDBContent($content_path, $content_data=null)
{
	//Obviate system pages from search using black list for performance improvement
	if(\JarisCMS\System\MakePagesBlacklist($content_path))
	{
		return;
	}
	
    $uri = \JarisCMS\FileSystem\GetURIFromPath(str_replace("/data.php", "", str_replace(\JarisCMS\Setting\GetDataDirectory() . "pages/", "", $content_path)));

    if($content_data == null)
    {
        $content_data = \JarisCMS\Page\GetData($uri, \JarisCMS\Language\GetCurrent());
    }
    
    //Skip pages marked as system in case is not specified on the blacklist
    if($content_data["is_system"])
    {
    	return;
    }
    
    if(!\JarisCMS\Page\UserAccess($content_data))
	{
		return;
	}

    $fields_to_search = GetFields();

    $all_fields_same = true;
    foreach($fields_to_search as $key=>$fields)
    {
    	if($fields["code"])
    	{
    		eval("?>" . $fields["code"]);
    	}
        elseif (trim($content_data[$fields["name"]]) != $fields["value"])
        {
            $all_fields_same = false;
            break;
        }
    }

    if($all_fields_same)
    {	
		//Check if the user does not selected a category
		$categories = GetCategories();
		$all_none_selected = true;
		foreach($categories as $machine_name=>$values)
		{
			foreach($categories[$machine_name] as $id=>$subcategories)
			{
				if("" . $categories[$machine_name][$id] . "" != "-1")
				{
					$all_none_selected = false;
					break 2;
				}
			}
		}
		
		//Check categories that match on content if user selected a category
		$found_category_match = false;
		if(!$all_none_selected)
		{
			foreach($categories as $machine_name=>$sub_categories)
			{
				if(count($sub_categories) > 1)
                {
                    if(isset($content_data["categories"][$machine_name]))
                    {
                        foreach($sub_categories as $subcategory_id)
                        {
                            foreach($content_data["categories"][$machine_name] as $content_subcategory_id)
                            {
                                if($subcategory_id == $content_subcategory_id)
                                {
                                    $found_category_match = true;
                                    break 3;
                                }
                            }
                        }
                    }
                }
                else
                {
                    if($sub_categories[0] != "-1")
                    {
                        if(isset($content_data["categories"][$machine_name]))
                        {
                            foreach($content_data["categories"][$machine_name] as $subcategory_id)
                            {
                                if($subcategory_id == $sub_categories[0])
                                {
                                    $found_category_match = true;
                                    break 2;
                                }
                            }
                        }
                    }
                }   
			}
		}
		
		$keywords = GetKeywords();
		
        if(count($keywords) <= 0 && count(GetFields()) >= 1)
		{
			AddResult($uri);
		}
		else if(count($keywords) > 0)
		{		
			if($all_none_selected || $found_category_match)
			{
				$title = strtolower($content_data["title"]);
				$content = strtolower(StripHTMLTags(\JarisCMS\InputFormat\FilterData($content_data["content"], $content_data["input_format"])));
				$word = strtolower($word);
				$keywords_string = implode(" ", $keywords);
				
				$long_word = 0;
				foreach($keywords as $word)
				{
					$len = strlen($word);
					if($len > $long_word)
					{
						$long_word = $len;
					}
				}
				
				$found = false;
				$keyword_count = count($keywords);
				for($i=$keyword_count-1; $i>=0; $i--)
				{
					$keywords_array = array();
					for($y=0; $y<=$i; $y++)
					{
						$keywords_array[] = $keywords[$y];
					}
					
					$keywords_string = implode(" ", $keywords_array);
					
					$len = strlen($keywords_string);
					if($len > 1 && $len >= $long_word)
					{
						//First search for exact matches on title
						if("" . stripos($title, $keywords_string) . "" != "")
						{
							AddResult($uri, "title", $i+$keyword_count);
							$found = true;
							break;
						}
					}
				}
				
				if(!$found)
				{
					for($i=$keyword_count-1; $i>=0; $i--)
					{
						$keywords_array = array();
						for($y=0; $y<=$i; $y++)
						{
							$keywords_array[] = $keywords[$y];
						}
						
						$keywords_string = implode(" ", $keywords_array);
						
						$len = strlen($keywords_string);
						if($len > 1 && $len >= $long_word)
						{
							//Second search for exact matches on content
							if("" . stripos($content, $keywords_string) . "" != "")
							{
								AddResult($uri, "content", $i+$keyword_count);
								$found = true;
								break;
							}
						}
					}
				}
				
				if(!$found)
				{
					sort($keywords);
				
					for($i=$keyword_count-1; $i>=0; $i--)
					{
						$keywords_string = $keywords[$i];
						
						if(strlen($keywords_string) >= $long_word)
						{
							if("" . stripos($title, $keywords_string) . "" != "")
							{
								AddResult($uri, "title", $i);
								break;
							}
							else if("" . stripos($content, $keywords_string) . "" != "")
							{
								AddResult($uri, "content", $i);
								break;
							}
						}
					}
				}
					
			}
		}
		else if($found_category_match)
		{
			AddResult($uri);
		}
    }
    
    unset($content_data);
}

function GetResults($page, $amount)
{
	// To protect against sql injections be sure $page is a int
	if(!is_numeric($page))
	{
		$page = 1;		
	}
	else 
	{
		$page=intval($page);
	}
	
	if(!is_numeric($amount))
	{
		$amount = 10;		
	}
	else 
	{
		$amount=intval($amount);
	}
	
	unset($_SESSION["search"]["results"]);
	
	//First we sort title results and content results by relevancy
	$title_results = \JarisCMS\PHPDB\Sort($_SESSION["search"]["results_title"], "relevancy", SORT_DESC);
	$content_results = \JarisCMS\PHPDB\Sort($_SESSION["search"]["results_content"], "relevancy", SORT_DESC);
	
	if(is_array($title_results))
	{
		//Add title results to search results session
		foreach($title_results as $values)
		{
			$_SESSION["search"]["results"][] = $values["uri"];
		}
	}
	
	if(is_array($content_results))
	{
		//Add content results to search results session
		foreach($content_results as $values)
		{
			$_SESSION["search"]["results"][] = $values["uri"];
		}
	}
	
	if(is_array($_SESSION["search"]["results_normal"]))
	{
		//Add normal results to search results session
		foreach($_SESSION["search"]["results_normal"] as $value)
		{
			$_SESSION["search"]["results"][] = $value;
		}
	}

	unset($_SESSION["search"]["results_title"]);
	unset($_SESSION["search"]["results_content"]);
	unset($_SESSION["search"]["results_normal"]);
	
	$page_count = 0;
	$remainder_pages = 0;

	if(GetResultCount() <= $amount)
	{
		$page_count = 1;
	}
	else
	{
		$page_count = floor(GetResultCount() / $amount);
		$remainder_pages = GetResultCount() % $amount;

		if($remainder_pages > 0)
		{
			$page_count++;
		}
	}

	//In case someone is trying a page out of range
	if($page > $page_count || $page < 1)
	{
		return false;
	}

	if(\JarisCMS\SQLite\DBExists("search_engine"))
	{
		$start_result = 0;
		$end_result = $amount - 1;
	}
	else
	{
		$start_result = ($page * $amount) - $amount;
		$end_result = ($page * $amount) - 1;
	}
	
	$results_data = array();
	for($start_result; isset($_SESSION["search"]["results"][$start_result]) && $start_result <= $end_result; $start_result++)
	{
		$page_data = \JarisCMS\Page\GetData($_SESSION["search"]["results"][$start_result], \JarisCMS\Language\GetCurrent());
		$page_data["uri"] = $_SESSION["search"]["results"][$start_result];

		$results_data[] = $page_data;
		
		unset($page_data);
	}

	return $results_data;
}

function PrintNavigation($page, $amount=10, $search_uri="search")
{
	// To protect agains sql injections be sure $page is a int
	if(!is_numeric($page))
	{
		$page = 1;		
	}
	else 
	{
		$page=intval($page);
	}
	
	if(!is_numeric($amount))
	{
		$amount = 10;		
	}
	else 
	{
		$amount=intval($amount);
	}
	
    //In case person is searching with category aliases set search uri to it
    if(\JarisCMS\URI\GetType(\JarisCMS\URI\Get()) == "category" && $search_uri == "search")
    {
        $search_uri = \JarisCMS\URI\Get();
    }
    
	$page_count = 0;
	$remainder_pages = 0;

	if(GetResultCount() <= $amount)
	{
		$page_count = 1;
	}
	else
	{
		$page_count = floor(GetResultCount() / $amount);
		$remainder_pages = GetResultCount() % $amount;

		if($remainder_pages > 0)
		{
			$page_count++;
		}
	}

	//In case someone is trying a page out of range or not print if only one page
	if($page > $page_count || $page < 0 || $page_count == 1)
	{
		return false;
	}
	
	//Generate list of selected categories to pass
	$categories = \JarisCMS\Category\GetList();
	$categories_string = "";
	
    //If category uri alias dont generate category arguments list
    if(\JarisCMS\URI\GetType(\JarisCMS\URI\Get()) != "category")
    {
        if(is_array($categories))
        {
        	foreach($categories as $category_name=>$values)
        	{
        		if(isset($_REQUEST[$category_name]))
        		{
        			foreach($_REQUEST[$category_name] as $selected)
        			{
        				$categories_string .= $category_name . "[]=" . $selected . "&";
        			}
        		}
        	}
         }
    }
	
	if($categories_string)
	{
		$categories_string = "&" . rtrim($categories_string, "&");
	}

	print "<div class=\"navigation\">\n";
	if($page != 1)
	{
        if(isset($_REQUEST["keywords"]))
        {
		  $previous_page = \JarisCMS\URI\PrintURL($search_uri, array("page"=>$page - 1, "keywords"=>$_REQUEST["keywords"], "type"=>$_REQUEST["type"], "order"=>$_REQUEST["order"], "results_count"=>$_REQUEST["results_count"])) . $categories_string;
        }
        else
        {
            $previous_page = \JarisCMS\URI\PrintURL($search_uri, array("page"=>$page - 1, "type"=>$_REQUEST["type"], "order"=>$_REQUEST["order"], "results_count"=>$_REQUEST["results_count"])) . $categories_string;
        }
		$previous_text = t("Previous");
		print "<a class=\"previous\" href=\"$previous_page\">$previous_text</a>";
	}

	$start_page = $page;
	$end_page = $page + $amount;

	for($start_page; $start_page < $end_page && $start_page <= $page_count; $start_page++)
	{
		$text = t($start_page);

		if($start_page > $page || $start_page < $page)
		{
            if(isset($_REQUEST["keywords"]))
            {
                $url = \JarisCMS\URI\PrintURL($search_uri, array("page"=>$start_page, "keywords"=>$_REQUEST["keywords"], "type"=>$_REQUEST["type"], "order"=>$_REQUEST["order"], "results_count"=>$_REQUEST["results_count"])) . $categories_string;
            }
            else
            {
                $url = \JarisCMS\URI\PrintURL($search_uri, array("page"=>$start_page, "type"=>$_REQUEST["type"], "order"=>$_REQUEST["order"], "results_count"=>$_REQUEST["results_count"])) . $categories_string;
            }
			print "<a class=\"page\" href=\"$url\">$text</a>";
		}
		else
		{
			print "<a class=\"current-page page\">$text</a>";
		}
	}

	if($page < $page_count)
	{
        if(isset($_REQUEST["keywords"]))
        {
            $next_page = \JarisCMS\URI\PrintURL($search_uri, array("page"=>$page + 1, "keywords"=>$_REQUEST["keywords"], "type"=>$_REQUEST["type"], "order"=>$_REQUEST["order"], "results_count"=>$_REQUEST["results_count"])) . $categories_string;
        }
        else
        {
            $next_page = \JarisCMS\URI\PrintURL($search_uri, array("page"=>$page + 1, "type"=>$_REQUEST["type"], "order"=>$_REQUEST["order"], "results_count"=>$_REQUEST["results_count"])) . $categories_string;
        }
		$next_text = t("Next");
		print "<a class=\"next\" href=\"$next_page\">$next_text</a>";
	}
	print "</div>\n";
}

function AddResult($result, $position="append", $relevancy=null)
{
	switch($position)
	{
		case "title":
			$_SESSION["search"]["results_title"][] = array("uri"=>$result, "relevancy"=>$relevancy);
			break;
			
		case "content":
			$_SESSION["search"]["results_content"][] = array("uri"=>$result, "relevancy"=>$relevancy);
			break;
			
		case "append":
			$_SESSION["search"]["results_normal"][] = $result;
			break;
	}
	
    $_SESSION["search"]["count"]++;
}

function ReturnResults()
{
    return $_SESSION["search"]["results"];
}

function GetResultCount()
{
    static $count;
    
    if(\JarisCMS\SQLite\DBExists("search_engine"))
    {
        if($count <= 0)
        {
            $db = \JarisCMS\SQLite\Open("search_engine");
            $count = 0;
            
            $type = GetContentType();
            
            $group = \JarisCMS\Security\GetCurrentUserGroup();
            
            //Search by keywords and categories
            if(count(GetKeywords()) > 0)
            {
                $keywords = implode(" ", GetKeywords());
                $keywords = str_replace("'", "''", $keywords);
                $categories = serialize(GetCategories());
                $categories = str_replace("'", "''", $categories);
                
                $select = "select 
                leftsearch(title, '$keywords') as title_relevancy, leftsearch(content, '$keywords') as content_relevancy,
                normalsearch(description, '$keywords') as description_normal, normalsearch(keywords, '$keywords') as keywords_normal,
                hascategories(categories, '$categories') as has_category,
                haspermission(groups, '$group') as has_permissions,
                count(uri) as uri_count from uris where
                ((title_relevancy > 0 or content_relevancy > 0 or
                description_normal > 0 or keywords_normal > 0) and has_category > 0 and has_permissions > 0) $type";
                
                $result = \JarisCMS\SQLite\Query($select, $db);
                
                if($data = \JarisCMS\SQLite\FetchArray($result))
                {
                    $count = $data["uri_count"];
                }
            }
            
            //Search by categories only
            else if(count(GetCategories()) > 0)
            {
                $categories = serialize(GetCategories());
                $categories = str_replace("'", "''", $categories);
                
                $select = "select
                hascategories(categories, '$categories') as has_category,
                haspermission(groups, '$group') as has_permissions,
                count(uri) as uri_count from uris where
                has_category > 0 and has_permissions > 0 $type";
                
                $result = \JarisCMS\SQLite\Query($select, $db);
                
                if($data = \JarisCMS\SQLite\FetchArray($result))
                {
                    $count = $data["uri_count"];
                }
            }
            
            \JarisCMS\SQLite\Close($db);
        }
        
        return $count;
    }
    
    return $_SESSION["search"]["count"];
}

function ResetResults()
{
    unset($_SESSION["search"]);
}

function AddKeywords($keywords)
{
    $keywords = trim($keywords);
    $keywords = preg_replace("/ +/", " ", $keywords);
    $words = explode(" ", $keywords);
    $_SESSION["search"]["keywords"] = $words;
}

function GetKeywords()
{
    return $_SESSION["search"]["keywords"];
}

function AddFields($field_values)
{
    $_SESSION["search"]["field_values"] = $field_values;
}

function AddCategories($categories)
{
	$_SESSION["search"]["categories"] = $categories;
}

function  GetCategories()
{
	return $_SESSION["search"]["categories"];
}

function GetFields()
{
    if (!$_SESSION["search"]["field_values"])
    {
        return array();
    }

    return $_SESSION["search"]["field_values"];
}

function HighlightResults($result, $input_format="full_html", $type="title")
{
	if($input_format == "php_code")
	{
		$result = \JarisCMS\System\PHPEval($result);
	}
	
	$result = StripHTMLTags($result);
	
	$keywords = GetKeywords()?GetKeywords():array();
	$keywords_string = implode(" ", $keywords);

	$result = preg_replace("/ +/", " ", $result);
	
	if("" . stripos($result, $keywords_string) . "" != "")
	{
		$result = str_ireplace($keywords_string, "<span class=\"search-highlight\">" . $keywords_string . "</span>", $result);
	}	
	else
	{		
		$result_words = explode(" ", $result);
		
		$long_word = 0;
		foreach($keywords as $word)
		{
			$len = strlen($word);
			if($len > $long_word)
			{
				$long_word = $len;
			}
		}
		
		foreach($result_words as $index=>$result_word)
		{
			foreach($keywords as $word)
			{
				if(("" . stripos($result_word, $word) . "" == "0") && (strlen($word) >= $long_word || strlen($word) >= 3))
				{
					$result_words[$index] = str_ireplace($word, "<span class=\"search-highlight\">" . $word . "</span>", $result_words[$index]);
					break;
				}
			}
		}
		
		$result = implode(" ", $result_words);
	}

	$sentences = explode("</span>", $result);
	$sentences_count = count($sentences);
	
	//If no word was hightlited
	if($sentences_count-1 <= 0)
	{
		if($type != "title")
		{
			return \JarisCMS\System\PrintContentPreview($result, 35, true);
		}
		else 
		{
			return \JarisCMS\System\PrintContentPreview($result, 35, false);
		}
	}

	$final_result = "";
	for($i=0; $i<$sentences_count; $i++)
	{
		$len = strlen($sentences[$i]);

		if($len > 80 && $type!="title")
		{
			$new_sentence = " ... ";
			$new_sentence .= substr($sentences[$i], $len - 80, 80);
			$new_sentence .= "</span>";
		}
		else
		{
			$new_sentence = $sentences[$i];

			if($i != $sentences_count-1)
			{
				$new_sentence .= "</span>";
			}
		}

		if(strlen($final_result) <= 500)
		{
			$final_result .=  $new_sentence;
		}
		else
		{
			break;
		}
	}

	return $final_result;

}

function StripHTMLTags($text, $allowed_tags="")
{
    //Allow object and embed
    if("" . stripos($allowed_tags, "object") . "" != "" ||
       "" . stripos($allowed_tags, "embed") . "" != ""
      )
    {
        $text = preg_replace(
    		array(
    			// Remove invisible content
    			'@<head[^>]*?>.*?</head>@siu',
    			'@<style[^>]*?>.*?</style>@siu',
    			'@<script[^>]*?.*?</script>@siu',
    			'@<applet[^>]*?.*?</applet>@siu',
    			'@<noframes[^>]*?.*?</noframes>@siu',
    			'@<noscript[^>]*?.*?</noscript>@siu',
    
    			// Add line breaks before & after blocks
    			'@<((br)|(hr))@iu',
    			'@</?((address)|(blockquote)|(center)|(del))@iu',
    			'@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
    			'@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
    			'@</?((table)|(th)|(td)|(caption))@iu',
    			'@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
    			'@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
    			'@</?((frameset)|(frame)|(iframe))@iu',
    		),
    		array(
    			' ', ' ', ' ', ' ', ' ', ' ',
    			"\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
    			"\n\$0", "\n\$0",
    		),
    		$text );
    }
	// PHP's strip_tags() function will remove tags, but it
	// doesn't remove scripts, styles, and other unwanted
	// invisible text between tags.  Also, as a prelude to
	// tokenizing the text, we need to insure that when
	// block-level tags (such as <p> or <div>) are removed,
	// neighboring words aren't joined.
    else
    {
    	$text = preg_replace(
    		array(
    			// Remove invisible content
    			'@<head[^>]*?>.*?</head>@siu',
    			'@<style[^>]*?>.*?</style>@siu',
    			'@<script[^>]*?.*?</script>@siu',
    			'@<object[^>]*?.*?</object>@siu',
    			'@<embed[^>]*?.*?</embed>@siu',
    			'@<applet[^>]*?.*?</applet>@siu',
    			'@<noframes[^>]*?.*?</noframes>@siu',
    			'@<noscript[^>]*?.*?</noscript>@siu',
    			'@<noembed[^>]*?.*?</noembed>@siu',
    
    			// Add line breaks before & after blocks
    			'@<((br)|(hr))@iu',
    			'@</?((address)|(blockquote)|(center)|(del))@iu',
    			'@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
    			'@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
    			'@</?((table)|(th)|(td)|(caption))@iu',
    			'@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
    			'@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
    			'@</?((frameset)|(frame)|(iframe))@iu',
    		),
    		array(
    			' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
    			"\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
    			"\n\$0", "\n\$0",
    		),
    		$text );
    }

	// Remove all remaining tags and comments and return.
	return strip_tags($text, $allowed_tags);
}

function GetTypeFields($type)
{
    static $types_array;
    
    if(!$types_array[$type])
    {
        $fields_string = \JarisCMS\Setting\Get("{$type}_fields", "main");
        
        if(trim($fields_string) != "")
        {
            $fields = explode(",", $fields_string);
            $fields_array = array();
            foreach($fields as $name)
            {
                $fields_data = explode(":", $name);
                
                if(count($fields_data) > 1)
                {
                    $fields_array[t(trim($fields_data[0])) . ":"] = trim($fields_data[1]);
                }
                else
                {
                    $fields_array[] = trim($fields_data[0]);
                }
            }
            
            $types_array[$type] = $fields_array;
        }
        else
        {
            $types_array[$type][] = "content";
        }
    }
    
    return $types_array[$type];
}
?>