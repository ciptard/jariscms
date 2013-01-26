<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 * 
 *@file Jaris CMS module install file
 *
 *Stores the installation script for module.
 */

namespace JarisCMS\Module\Visits;

use JarisCMS\SQLite;

function Install()
{
    //Create Visits data base
    if(!SQLite\DBExists("visits"))
    {
        $db = SQLite\Open("visits");
        
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
    }
}

?>