<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Class file to inject sqlite text search functions
 */

namespace JarisCMS\SQLite;

 /**
  * Class to inject text search functions to sqlite as some other misc.
  */
 class Search
 {
    //A cache of the words to search
    private $keywords;
    private $keywords_count;
    private $keywords_string;
    
    //Cache for categories
    private $categories_to_check;
    private $categories_to_check_set;
    private $categories_empty;
    
    /**
     * Initialize the class for use on a opened database.
     * 
     * @param resource Handle to opened database object.
     */
    public function __construct(&$db)
    {
        $this->keywords = array();
        $this->keywords_count = array();
        
        $this->categories_to_check = array();
        $this->categories_to_check_set = false;
        $this->categories_empty = false;
    
        
        if(gettype($db) == "object" && class_exists("SQLite3"))
        {
            $db->createFunction("normalsearch", array (&$this, 'normal_text_search'), 2); 	
    		$db->createFunction("leftsearch", array (&$this, 'left_text_search'), 2);
            $db->createFunction("dateformat", array (&$this, 'date_format'), 2);
            $db->createFunction("hascategories", array (&$this, 'has_categories'), 2);
            $db->createFunction("haspermission", array (&$this, 'has_permission'), 2);
        }
            
        elseif(gettype($db) == "object")
        {
            $db->sqliteCreateFunction("normalsearch", array (&$this, 'normal_text_search'), 2); 	
    		$db->sqliteCreateFunction("leftsearch", array (&$this, 'left_text_search'), 2);
            $db->sqliteCreateFunction("dateformat", array (&$this, 'date_format'), 2);
            $db->sqliteCreateFunction("hascategories", array (&$this, 'has_categories'), 2);
            $db->sqliteCreateFunction("haspermission", array (&$this, 'has_permission'), 2);
        }
            
        else
        {
            sqlite_create_function($db, "normalsearch", array (&$this, 'normal_text_search'), 2); 	
    		sqlite_create_function($db, "leftsearch", array (&$this, 'left_text_search'), 2);
            sqlite_create_function($db, "dateformat", array (&$this, 'date_format'), 2);
            sqlite_create_function($db, "hascategories", array (&$this, 'has_categories'), 2);
            sqlite_create_function($db, "haspermission", array (&$this, 'has_permission'), 2);
        }
    }
    
    /**
     * Converts keywords or input text to search to array of words as count it and saves keywords to cache.
     * 
     * @param string $text The text to convert.
     * @param string $keywords True to indicate if input text is user keywords or false otherwise.
     * 
     * @return array Array in the format array("words"=>array(), "count"=>number)
     */
    private function text_to_array($text, $keywords=false)
    {
        $original_text = $text;
        
        if($keywords)
        {
            if(isset($this->keywords[$text]))
            {
                return array("words"=>$this->keywords[$text], "count"=>$this->keywords_count[$text], "text"=>$keywords_string[$text]);
            }
        }
        
        /*Since this slower the search we already do it when storing the page on database
        $text = \JarisCMS\Search\StripHTMLTags($text);
        
        //Substitute some characters with spaces to improve search quality
        $text = str_replace(array(".", ",", "'", "\"", "(", ")"), " ", $text);
        
        //Remove repeated whitespaces
        $text = preg_replace('/\s\s+/', ' ', $text);
        
        $text = strtolower($text);
        */
        
        $words =  explode(" ", $text);
        $count = count($words);
        
        if($keywords)
        {
            if(!$this->keywords[$original_text])
            {
                $text = strtolower($text);
                $this->keywords_string[$original_text] = $text;
                $this->keywords[$original_text] = $words;
                $this->keywords_count[$original_text] = $count;
            }
        }
        
        return array("words"=>$words, "count"=>$count, "text"=>$text);
    }
    
    /**
     * Gets a number representing the percent of the keywords on the text to search.
     * 
     * @param array $text An array of the words to search returned by text_to_array.
     * @param array $text An array of the keywords returned by text_to_array.
     * 
     * @return float The keywords density on the text.
     */
    private function get_matching_percent($text, $keywords)
    {		
		$matching_sum = 0;
		foreach($keywords["words"] as $keyword)
        {
			foreach($text["words"] as $position=>$text_word)
            {
				$position += 1;
				if($text_word == $keyword)
                {
					$matching_sum += ($position * $position) / $text["count"];
				}
			}
		}
		
		$divisor = ($text["count"] + 1) * ($text["count"] / 2);
		
		return $matching_sum / $divisor;				
	}
    
    /**
     * To perform a normal text search.
     * 
     * @param string $text The haystack.
     * @param array $keywords The needle.
     * @param string $input_format The text format.
     * 
     * @return float The matching percent of needles in the haystack.
     */
    public function normal_text_search($text, $keywords, $input_format="php_code")
    {   
        /*Slowers down search
        $text = \JarisCMS\InputFormat\FilterData($text, $input_format);
        */
        
        if(strlen($text) <= 0)
        {
            return 0;
        }
                        
		$matching_percent = 0;
	
        $keywords = $this->text_to_array($keywords, true);
        $keywords_count = $keywords["count"];
        $keywords = $keywords["words"];
				
		$keywords_string = "";
        
		for ($i=0; $i < $keywords_count; $i++)
        {
			$keywords_string .= $keywords[$i] . " ";
            
            $substring_count = substr_count($text_string, $keywords_string);
            
            if($substring_count > 0)
            {
                $matching_percent += ($substring_count * ($i + 1));
            } 
		}	
	
		return $matching_percent;
	}

    /**
     * Perform a text search with priority to the starting words.
     * 
     * @param string $text The haystack.
     * @param array $keywords The needle.
     * @param string $input_format The text format.
     * 
     * @return float Matching percent.
     */
	public function left_text_search($text, $keywords, $input_format="php_code")
    {
        /*Slowers down search
        $text = \JarisCMS\InputFormat\FilterData($text, $input_format);
        */
        
        if(strlen($text) <= 0)
        {
            return 0;
        }
        
        $keywords = $this->text_to_array($keywords, true);
		
		$keyword_count = $keywords["count"];
        $keywords = $keywords["words"];
        
        $long_word = 0;
		foreach($keywords as $word)
		{
			$len = strlen($word);
			if($len > $long_word)
			{
				$long_word = $len;
			}
		}
        
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
				if("" . stripos($text, $keywords_string) . "" != "")
				{
					return $i+$keyword_count;
				}
			}
		}
        
        return 0;
	}
    
    /**
     * To format a given timestamp.
     * 
     * @param timestamp $timestamp A given time stamp to format.
     * @param string $format The format options wanted as output for the given time stamp.
     * 
     * @return string A formatted time stamp.
     */
    public function date_format($timestamp, $format)
    {
        return date($format, $timestamp);
    }
    
    /**
     * Used for the search_engine database to check if a given content has a given categories.
     * 
     * @param string $caregories The serialized categories stored on search_engine database.
     * @param string $categories_to_check_input A serialized categories array to compare against the stored categories.
     * 
     * @return integer 1 on true 0 on false.
     */
    public function has_categories($categories, $categories_to_check_input)
    {
        if(!$this->categories_to_check_set)
        {
            $this->categories_to_check = unserialize($categories_to_check_input);
            $this->categories_to_check_set = true;
        }
        
        if(!$this->categories_empty)
        {   
            //Look if a category was selected
            $category_selected = false;
            foreach($this->categories_to_check as $machine_name=>$sub_categories)
            {
                if(count($sub_categories) > 1)
                {
                    foreach($sub_categories as $subcategory_id)
                    {
                        if($subcategory_id != "-1")
                        {
                            $category_selected = true;
                            break 2;
                        }
                    }
                }
                else
                {
                    if($sub_categories[0] != "-1")
                    {
                        $category_selected = true;
                        break;
                    }
                }
            }
            
            //If no category selected return 1 and dont check anymore for categories just return 1
            if(!$category_selected)
            {
                $this->categories_empty = true;
                return 1;
            }
            
            $categories = unserialize($categories);
            
            foreach($this->categories_to_check as $machine_name=>$sub_categories)
    		{
    			if(count($sub_categories) > 1)
                {
                    if(isset($categories[$machine_name]))
                    {
                        foreach($sub_categories as $subcategory_id)
                        {
                            foreach($categories[$machine_name] as $content_subcategory_id)
                            {
                                if($subcategory_id == $content_subcategory_id)
                                {
                                    return 1;
                                }
                            }
                        }
                    }
                }
                else
                {
                    if($sub_categories[0] != "-1")
                    {
                        if(isset($categories[$machine_name]))
                        {
                            foreach($categories[$machine_name] as $subcategory_id)
                            {
                                if($subcategory_id == $sub_categories[0])
                                {
                                    return 1;
                                }
                            }
                        }
                    }
                }   
    		}
            
            return 0;
        }
        
        return 1;
    }
    
    /**
     * Used on the search_engine database to check if a current group has permission to view a content.
     * @param string $groups The column of groups stored and serialized on the search engine database.
     * @param string $current_group The current group to check if has permissions.
     */
    public function has_permission($groups, $current_group)
    {
    	//Groups is null or groups array is empty
    	if("" . strpos($groups, "N;") . "" != "" || "" . strpos($groups, "a:0") . "" != "")
    	{
    		return 1;
    	}
    	
    	if("" . strpos($groups, '"'. $current_group. '"') . "" != "")
    	{
    		return 1;
    	}
    	
    	return 0;
    }
 }
 
 ?>