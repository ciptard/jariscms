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

namespace JarisCMS\Module\Comments;

function Install()
{
    //Create comments data base
    if(!JarisCMS\SQLite\DBExists("comments"))
    {
        $db = JarisCMS\SQLite\Open("comments");
		
        JarisCMS\SQLite\Query("create table comments (id integer, created_timestamp text, uri text, type text, flags integer)", $db);
        
        JarisCMS\SQLite\Query("create index comments_index on comments (created_timestamp desc, uri desc, type desc, flags desc)", $db);
        
        JarisCMS\SQLite\Close($db);
    }
}

?>