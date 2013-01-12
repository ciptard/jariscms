<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 * 
 *@file Jaris CMS module install file
 *
 *Stores the installation script for visits module.
 */

namespace JarisCMS\Module\Rating;

function Install()
{
    //Create ratings data base
    if(!\JarisCMS\SQLite\DBExists("ratings"))
    {
        $db = \JarisCMS\SQLite\Open("ratings");
        
        \JarisCMS\SQLite\Query("create table ratings (id integer primary key, content_timestamp text, last_rate_timestamp text, day integer, month integer, year integer, uri text, type text, points integer, rates_count integer)", $db);
        
        \JarisCMS\SQLite\Query("create index ratings_index on ratings (content_timestamp desc, last_rate_timestamp desc, day desc, month desc, year desc, uri desc, type desc, points desc, rates_count desc)", $db);
        
        \JarisCMS\SQLite\Close($db);
    }
}

?>