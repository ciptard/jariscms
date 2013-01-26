<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Visits module functions
 */

namespace JarisCMS\Module\Visits;

use JarisCMS\URI;
use JarisCMS\SQLite;
use JarisCMS\Module;
use JarisCMS\System;
use JarisCMS\Module\IPInfo;

function Count()
{
    $time = time();
    $date = date("j", $time);
    $month = date("n", $time);
    $year = date("Y", $time);
    
    //Visitor details
    $ip = $_SERVER["REMOTE_ADDR"];
    $referer = $_SERVER["HTTP_REFERER"];
    $browser = $_SERVER["HTTP_USER_AGENT"];
    
    $db = SQLite\Open(GetDBName($year));
    
    SQLite\Turbo($db);
    
    $select = "select visitor_ip from visitors where visitor_ip='$ip' and date='$date' and month='$month' and year='$year'";
    
    $result = SQLite\Query($select, $db);
    
    //if visitor is found just update the end_time
    if($data = SQLite\FetchArray($result))
    {
        $update = "update visitors set
        end_time = '$time' 
        where visitor_ip='$ip' and date='$date' and month='$month' and year='$year'
        ";
        
        SQLite\Query($update, $db);
    }
    
    //If visitor is not found count it
    else
    {
        $insert = "insert into visitors (visitor_ip, start_time, end_time, date, month, year) 
        values('$ip', '$time', '$time', '$date', '$month', '$year')";
        
        SQLite\Query($insert, $db);
        
        if($referer != "")
        {
            $data = parse_url($referer);
            $data["host"] = str_replace("'", "''", $data["host"]);
            $data["query"] = str_replace("'", "''", $data["query"]);
            $referer = str_replace("'", "''", $referer);
            
            $insert = "insert into referrals (visitor_ip, http_referer, host, query, timestamp, date, month, year) 
            values('$ip', '$referer', '{$data['host']}', '{$data['query']}', '$time', '$date', '$month', '$year')";
            
            SQLite\Query($insert, $db); 
        }
        
        if($browser != "")
        {
            $browser = str_replace("'", "''", $browser);
            $browser_code = System\GetUserBrowser();
            
            $insert = "insert into browsers (visitor_ip, http_user_agent, browser_code, timestamp, date, month, year) 
            values('$ip', '$browser', '$browser_code', '$time', '$date', '$month', '$year')";
            
            SQLite\Query($insert, $db); 
        }
        
        if($location = IPInfo\GetByCountry($ip))
        {
            $insert = "insert into countries (visitor_ip, country_code, country_name, timestamp, date, month, year) 
            values('$ip', '{$location['country_code']}', '{$location['country_name']}', '$time', '$date', '$month', '$year')";
            
            SQLite\Query($insert, $db); 
        }
    }
    
    SQLite\Close($db);
}

function SaveViewedPage()
{
    $uri = str_replace("'", "''", URI\Get());
    
    $time = time();
    $date = date("j", $time);
    $month = date("n", $time);
    $year = date("Y", $time);
    
    //Visitor details
    $ip = $_SERVER["REMOTE_ADDR"];
    
    $db = SQLite\Open(GetDBName($year));
    
    SQLite\Turbo($db);
    
    $insert .= "insert into viewed_pages (visitor_ip, uri, timestamp, date, month, year) 
    values('$ip', '$uri', '$time', '$date', '$month', '$year');"; 
          
    SQLite\Query($insert, $db);
    
    
    //Count page on most viewed table
    $select = "select visits from most_viewed_pages where uri='$uri' and date='$date' and month='$month' and year='$year'";
    
    $result = SQLite\Query($select, $db);
    
    if($data = SQLite\FetchArray($result))
    {
        $update = "update most_viewed_pages set
        visits = visits + 1
        where uri='$uri' and date='$date' and month='$month' and year='$year'
        ";
        
        SQLite\Query($update, $db);
    }
    else
    {
        $insert = "insert into most_viewed_pages (uri, visits, date, month, year) 
        values('$uri', 1, '$date', '$month', '$year')";
        
        SQLite\Query($insert, $db);
    }
    
     SQLite\Close($db);
}

function GetVisitorData($ip, $date, $month, $year)
{
    $visitor_data = array();
    
    $db = SQLite\Open(GetDBName($year));
    
    //First fetch visitor data
    $select = "select * from visitors where visitor_ip='$ip' and date='$date' and month='$month' and year='$year'";
    
    $result = SQLite\Query($select, $db);
    
    $data = SQLite\FetchArray($result);
    
    $visitor_data["start_time"] = $data["start_time"];
    $visitor_data["end_time"] = $data["end_time"];
    
    //Fetch referral of visitor data
    $select = "select * from referrals where visitor_ip='$ip' and date='$date' and month='$month' and year='$year'";
    
    $result = SQLite\Query($select, $db);
    
    $data = SQLite\FetchArray($result);
    
    $visitor_data["http_referer"] = $data["http_referer"];
    $visitor_data["host"] = $data["host"];
    $visitor_data["query"] = $data["query"];
    
    //Fetch browser of visitor data
    $select = "select * from browsers where visitor_ip='$ip' and date='$date' and month='$month' and year='$year'";
    
    $result = SQLite\Query($select, $db);
    
    $data = SQLite\FetchArray($result);
    
    $visitor_data["http_user_agent"] = $data["http_user_agent"];
    $visitor_data["browser_code"] = $data["browser_code"];
    
    //Fetch country of visitor data
    $select = "select * from countries where visitor_ip='$ip' and date='$date' and month='$month' and year='$year'";
    
    $result = SQLite\Query($select, $db);
    
    $data = SQLite\FetchArray($result);
    
    if($data)
    {
        $visitor_data["country_code"] = $data["country_code"];
        $visitor_data["country_name"] = $data["country_name"];
    }
    
    return $visitor_data;
}

function CountPageViewsByVisitor($ip, $date, $month, $year)
{
    $db = SQLite\Open(GetDBName($year));
    
    $select = "select count(uri) as pages from viewed_pages where visitor_ip='$ip' and date='$date' and month='$month' and year='$year'";
    
    $result = SQLite\Query($select, $db);
    
    $data = SQLite\FetchArray($result);
    
    SQLite\Close($db);
    
    return $data["pages"];
}

function CountPageViewsByDate($date, $month, $year)
{
    $db = SQLite\Open(GetDBName($year));
    
    $select = "select count(uri) as pages from viewed_pages where date='$date' and month='$month' and year='$year'";
    
    $result = SQLite\Query($select, $db);
    
    $data = SQLite\FetchArray($result);
    
    SQLite\Close($db);
    
    return $data["pages"];
}

function CountPageViewsByMonth($month, $year)
{
    $db = SQLite\Open(GetDBName($year));
    
    $select = "select count(uri) as pages from viewed_pages where month='$month' and year='$year'";
    
    $result = SQLite\Query($select, $db);
    
    $data = SQLite\FetchArray($result);
    
    SQLite\Close($db);
    
    return $data["pages"];
}

function CountPageViewsByYear($year)
{
    $db = SQLite\Open(GetDBName($year));
    
    $select = "select count(uri) as pages from viewed_pages where year='$year'";
    
    $result = SQLite\Query($select, $db);
    
    $data = SQLite\FetchArray($result);
    
    SQLite\Close($db);
    
    return $data["pages"];
}

function CountVisitors($date=null, $month=null, $year=null)
{
    $db = SQLite\Open(GetDBName($year));
    
    $select = "";
    if(isset($date, $month, $year))
    {
        $select = "select count(visitor_ip) as visitors_count from visitors where date='$date' and month='$month' and year='$year'";
    }
    elseif(isset($month, $year))
    {
        $select = "select count(visitor_ip) as visitors_count from visitors where month='$month' and year='$year'";
    }
    elseif(isset($year))
    {
        $select = "select count(visitor_ip) as visitors_count from visitors where year='$year'";
    }
    
    $result = SQLite\Query($select, $db);
    
    $data = SQLite\FetchArray($result);
    
    SQLite\Close($db);
    
    return $data["visitors_count"];
}

function GetBrowserStatsByMonth($month, $year)
{
    $browser_stats = array();
    $db = SQLite\Open(GetDBName($year));
    
    //Count IE
    $select = "select count(browser_code) as browser_count from browsers where browser_code='ie' and month='$month' and year='$year'";
    
    $result = SQLite\Query($select, $db);
    
    $data = SQLite\FetchArray($result);
    
    if($data)
    {
        $browser_stats["Internet Explorer"] = $data["browser_count"];
    }
    else
    {
        $browser_stats["Internet Explorer"] = 0;
    }
    
    //Count FF
    $select = "select count(browser_code) as browser_count from browsers where browser_code='firefox' and month='$month' and year='$year'";
    
    $result = SQLite\Query($select, $db);
    
    $data = SQLite\FetchArray($result);
    
    if($data)
    {
        $browser_stats["FireFox"] = $data["browser_count"];
    }
    else
    {
        $browser_stats["FireFox"] = 0;
    }
    
    //Count Chrome
    $select = "select count(browser_code) as browser_count from browsers where browser_code='chrome' and month='$month' and year='$year'";
    
    $result = SQLite\Query($select, $db);
    
    $data = SQLite\FetchArray($result);
    
    if($data)
    {
        $browser_stats["Chrome"] = $data["browser_count"];
    }
    else
    {
        $browser_stats["Chrome"] = 0;
    }
    
    //Count Safari
    $select = "select count(browser_code) as browser_count from browsers where browser_code='safari' and month='$month' and year='$year'";
    
    $result = SQLite\Query($select, $db);
    
    $data = SQLite\FetchArray($result);
    
    if($data)
    {
        $browser_stats["Safari"] = $data["browser_count"];
    }
    else
    {
        $browser_stats["Safari"] = 0;
    }
    
    //Count Opera
    $select = "select count(browser_code) as browser_count from browsers where browser_code='opera' and month='$month' and year='$year'";
    
    $result = SQLite\Query($select, $db);
    
    $data = SQLite\FetchArray($result);
    
    if($data)
    {
        $browser_stats["Opera"] = $data["browser_count"];
    }
    else
    {
        $browser_stats["Opera"] = 0;
    }
    
    //Count Other
    $select = "select count(browser_code) as browser_count from browsers where browser_code='other' and month='$month' and year='$year'";
    
    $result = SQLite\Query($select, $db);
    
    $data = SQLite\FetchArray($result);
    
    if($data)
    {
        $browser_stats["Others"] = $data["browser_count"];
    }
    else
    {
        $browser_stats["Others"] = 0;
    }
    
    SQLite\Close($db);
    
    arsort($browser_stats);
    
    return $browser_stats;
}

function GetBrowserStatsByYear($year)
{
    $browser_stats = array();
    $db = SQLite\Open(GetDBName($year));
    
    //Count IE
    $select = "select count(browser_code) as browser_count from browsers where browser_code='ie' and year='$year'";
    
    $result = SQLite\Query($select, $db);
    
    $data = SQLite\FetchArray($result);
    
    if($data)
    {
        $browser_stats["Internet Explorer"] = $data["browser_count"];
    }
    else
    {
        $browser_stats["Internet Explorer"] = 0;
    }
    
    //Count FF
    $select = "select count(browser_code) as browser_count from browsers where browser_code='firefox' and year='$year'";
    
    $result = SQLite\Query($select, $db);
    
    $data = SQLite\FetchArray($result);
    
    if($data)
    {
        $browser_stats["FireFox"] = $data["browser_count"];
    }
    else
    {
        $browser_stats["FireFox"] = 0;
    }
    
    //Count Chrome
    $select = "select count(browser_code) as browser_count from browsers where browser_code='chrome' and year='$year'";
    
    $result = SQLite\Query($select, $db);
    
    $data = SQLite\FetchArray($result);
    
    if($data)
    {
        $browser_stats["Chrome"] = $data["browser_count"];
    }
    else
    {
        $browser_stats["Chrome"] = 0;
    }
    
    //Count Safari
    $select = "select count(browser_code) as browser_count from browsers where browser_code='safari' and year='$year'";
    
    $result = SQLite\Query($select, $db);
    
    $data = SQLite\FetchArray($result);
    
    if($data)
    {
        $browser_stats["Safari"] = $data["browser_count"];
    }
    else
    {
        $browser_stats["Safari"] = 0;
    }
    
    //Count Opera
    $select = "select count(browser_code) as browser_count from browsers where browser_code='opera' and year='$year'";
    
    $result = SQLite\Query($select, $db);
    
    $data = SQLite\FetchArray($result);
    
    if($data)
    {
        $browser_stats["Opera"] = $data["browser_count"];
    }
    else
    {
        $browser_stats["Opera"] = 0;
    }
    
    //Count Other
    $select = "select count(browser_code) as browser_count from browsers where browser_code='other' and year='$year'";
    
    $result = SQLite\Query($select, $db);
    
    $data = SQLite\FetchArray($result);
    
    if($data)
    {
        $browser_stats["Others"] = $data["browser_count"];
    }
    else
    {
        $browser_stats["Others"] = 0;
    }
    
    SQLite\Close($db);
    
    arsort($browser_stats);
    
    return $browser_stats;
}

function GetCountryStatsByMonth($month, $year)
{
    $countries = array();
    
    $db = SQLite\Open(GetDBName($year));
    
    $select = "select country_name from countries where month='$month' and year='$year'";
    
    $result = SQLite\Query($select, $db);
    
    while($data = SQLite\FetchArray($result))
    {
        if(isset($countries[$data["country_name"]]))
        {
            $countries[$data["country_name"]]++;
        }
        else
        {
            $countries[$data["country_name"]] = 1;
        }
    }
    
    arsort($countries);
    
    return $countries;
}

function GetCountryStatsByYear($year)
{
    $countries = array();
    
    $db = SQLite\Open(GetDBName($year));
    
    $select = "select country_name from countries where year='$year'";
    
    $result = SQLite\Query($select, $db);
    
    while($data = SQLite\FetchArray($result))
    {
        if(isset($countries[$data["country_name"]]))
        {
            $countries[$data["country_name"]]++;
        }
        else
        {
            $countries[$data["country_name"]] = 1;
        }
    }
    
    arsort($countries);
    
    return $countries;
}

function GetMostViewedStatsByMonth($month, $year)
{
    $most_viewed = array();
    
    $db = SQLite\Open(GetDBName($year));
    
    $select = "select uri, visits from most_viewed_pages where month='$month' and year='$year' order by visits desc";
    
    $result = SQLite\Query($select, $db);
    
    $total_uris = 0;
    
    while($data = SQLite\FetchArray($result))
    {
        if(!isset($most_viewed[$data["uri"]]))
        {
            //if new uri count it
            $total_uris++;
            
            if($total_uris == 31)
            {
                //if we have 30 different uris and the last one was fully counted end the loop
                break;
            }
            
            $most_viewed[$data["uri"]] = 0;
        }
    }
    
    foreach($most_viewed as $uri=>$visits)
    {
        $select = "select visits from most_viewed_pages where month='$month' and year='$year' and uri='$uri'";
        
        $result = SQLite\Query($select, $db);
    
        while($data = SQLite\FetchArray($result))
        {
            $most_viewed[$uri] += $data["visits"];
        }
    }
    
    SQLite\Close($db);
    
    arsort($most_viewed);
    
    return $most_viewed;
}

function GetMostViewedStatsByYear($year)
{
    $most_viewed = array();
    
    $db = SQLite\Open(GetDBName($year));
    
    $select = "select uri, visits from most_viewed_pages where year='$year' order by visits desc";
    
    $result = SQLite\Query($select, $db);
    
    $total_uris = 0;
    
    while($data = SQLite\FetchArray($result))
    {
        if(!isset($most_viewed[$data["uri"]]))
        {
            //if new uri count it
            $total_uris++;
            
            if($total_uris == 31)
            {
                //if we have 30 different uris and the last one was fully counted end the loop
                break;
            }
            
            $most_viewed[$data["uri"]] = 0;
        }
    }
    
    foreach($most_viewed as $uri=>$visits)
    {
        $select = "select visits from most_viewed_pages where year='$year' and uri='$uri'";
        
        $result = SQLite\Query($select, $db);
    
        while($data = SQLite\FetchArray($result))
        {
            $most_viewed[$uri] += $data["visits"];
        }
    }
    
    SQLite\Close($db);
    
    arsort($most_viewed);
    
    return $most_viewed;
}

function GetVisitors($page=0, $limit=30, $date, $month, $year)
{
    $db = null;
    $page *=  $limit;
    $visitors = array();
        
    $db = SQLite\Open(GetDBName($year));
    $result = SQLite\Query("select visitor_ip from visitors where date='$date' and month='$month' and year='$year' order by start_time desc limit $page, $limit", $db);

    $fields = array();
    if($fields = SQLite\FetchArray($result))
    {
        $visitors[] = $fields["visitor_ip"];
        
        while($fields = SQLite\FetchArray($result))
        {
            $visitors[] = $fields["visitor_ip"];
        }
        
        SQLite\Close($db);
        return $visitors;
    }
    else
    {
        SQLite\Close($db);
        return $visitors;
    }
}

function PrintVisitorsNavigation($visitors_count, $page, $amount=30)
{
    $page_count = 0;
    $remainder_pages = 0;

    if($visitors_count <= $amount)
    {
        $page_count = 1;
    }
    else
    {
        $page_count = floor($visitors_count / $amount);
        $remainder_pages = $visitors_count % $amount;

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

    print "<div class=\"navigation\">\n";
    if($page != 1)
    {
        $previous_page = URI\PrintURL(Module\GetPageURI("admin/visits/today", "visits"), array("page"=>$page - 1));
        $previous_text = t("Previous");
        print "<a class=\"previous\" href=\"$previous_page\">$previous_text</a>";
    }

    $start_page = $page;
    $end_page = $page + 10;

    for($start_page; $start_page < $end_page && $start_page <= $page_count; $start_page++)
    {
        $text = t($start_page);

        if($start_page > $page || $start_page < $page)
        {
            $url = URI\PrintURL(Module\GetPageURI("admin/visits/today", "visits"), array("page"=>$start_page));
            print "<a class=\"page\" href=\"$url\">$text</a>";
        }
        else
        {
            print "<a class=\"current-page page\">$text</a>";
        }
    }

    if($page < $page_count)
    {
        $next_page = URI\PrintURL(Module\GetPageURI("admin/visits/today", "visits"), array("page"=>$page + 1));
        $next_text = t("Next");
        print "<a class=\"next\" href=\"$next_page\">$next_text</a>";
    }
    print "</div>\n";
}

function GetKeywordsFromQuery($query)
{
    $variables[] = "q";
    $variables[] = "qt";
    $variables[] = "p";
    $variables[] = "searchfor";
    $variables[] = "query";
    
    $elements = explode("&", $query);
    
    foreach($variables as $variable)
    {
        foreach($elements as $element)
        {
            if("" . strpos($element, "$variable=") . "" != "")
            {
                return rawurldecode(str_replace(array("$variable=", "+"), array("", " "), $element));
            }
        }
    }
    
}

function GetDBName($year)
{
    static $db_name = array();
    
    $year = intval($year);
    $current_year = date("Y", time());
    
    //Cache of db names to prevent slow jaris_sqlite_db_exists checks
    if(isset($db_name[$year]))
    {
        return $db_name[$year];
    }
    
    //Create Visits data base by year
    if((!SQLite\DBExists("visits_$year") && $year == $current_year))
    {
        $db = SQLite\Open("visits_$year");
        SQLite\Query("create table visitors (visitor_ip text, start_time text, end_time text, date text, month text, year text)", $db);
        SQLite\Query("create index visitors_index on visitors (visitor_ip desc, date desc, month desc, year desc)", $db);
        
        SQLite\Query("create table referrals (visitor_ip text, http_referer text, host text, query text, timestamp text, date text, month text, year text)", $db);
        SQLite\Query("create index referrals_index on referrals (visitor_ip desc, timestamp desc, date desc, month desc, year desc)", $db);
        
        SQLite\Query("create table viewed_pages (visitor_ip text, uri text, timestamp text, date text, month text, year text)", $db);
        SQLite\Query("create index viewed_pages_index on viewed_pages (visitor_ip desc, uri desc, timestamp desc, date desc, month desc, year desc)", $db);
        
        SQLite\Query("create table most_viewed_pages (uri text, visits integer, date text, month text, year text)", $db);
        SQLite\Query("create index most_viewed_pages_index on most_viewed_pages (uri desc, visits desc, date desc, month desc, year desc)", $db);
        
        SQLite\Query("create table browsers (visitor_ip text, http_user_agent text, browser_code text, timestamp text, date text, month text, year text)", $db);
        SQLite\Query("create index browsers_index on browsers (visitor_ip desc, browser_code desc, timestamp desc, date desc, month desc, year desc)", $db);
        
        SQLite\Query("create table countries (visitor_ip text, country_code text, country_name text, timestamp text, date text, month text, year text)", $db);
        SQLite\Query("create index countries_index on countries (visitor_ip desc, country_code desc, country_name desc, timestamp desc, date desc, month desc, year desc)", $db);
        
        SQLite\Close($db);
        
        $db_name[$year] = "visits_$year";
    }
    
    //If year is not current year but db was created previously for passed year
    elseif(SQLite\DBExists("visits_$year"))
    {
        $db_name[$year] = "visits_$year";
    }
    //Use older database that stored data for all years on older moduler version
    else
    {
        $db_name[$year] = "visits";
    }
    
    return $db_name[$year];
}
?>
