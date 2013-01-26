<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the monthly visits stats.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Monthly Visits") ?>
    field;

    field: content
        <style>
            .stats td
            {
                width: auto;
                padding: 15px;
                border-bottom: solid 1px #000;
            }
            
            .stats thead td
            {
                width: auto;
                font-weight:  bold;
                border-bottom: 0;
            }
        </style>
        
        <?php
        
            JarisCMS\Security\ProtectPage(array("view_monthly_visits"));
            
            if(JarisCMS\Group\GetPermission("view_daily_visits", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("View Daily Stats"), JarisCMS\Module\GetPageURI("admin/visits/daily", "visits"));
            }
            
            if(JarisCMS\Group\GetPermission("view_yearly_visits", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("View Yearly Stats"), JarisCMS\Module\GetPageURI("admin/visits/yearly", "visits"));
            }
            
            $time = time();
            $month = $_REQUEST["month"]?$_REQUEST["month"]:date("n", $time);
            $year = $_REQUEST["year"]?$_REQUEST["year"]:date("Y", $time);
            
            
            $parameters["name"] = "visits-monthly-stats";
            $parameters["class"] = "visits-monthly-stats";
            $parameters["action"] = JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/visits/monthly", "visits"));
            $parameters["method"] = "get";

            $months[t("January")] = 1;
            $months[t("February")] = 2;
            $months[t("March")] = 3;
            $months[t("April")] = 4;
            $months[t("May")] = 5;
            $months[t("June")] = 6;
            $months[t("July")] = 7;
            $months[t("August")] = 8;
            $months[t("September")] = 9;
            $months[t("October")] = 10;
            $months[t("November")] = 11;
            $months[t("December")] = 12;

            $fields[] = array("type"=>"select", "name"=>"month", "value"=>$months, "selected"=>$month, "label"=>t("Month:"), "id"=>"month", "description"=>t("Select the month you want to see the stats."));
            
            $years = array();
            for($i=2010; $i<date("Y", time())+1; $i++)
            {
                $years[$i] = $i;
            }
            
            $fields[] = array("type"=>"select", "name"=>"year", "value"=>$years, "selected"=>$year, "label"=>t("Year:"), "id"=>"year", "description"=>t("Select the year you want to see the stats."));
            
            
            $fields[] = array("type"=>"submit", "name"=>"btnView", "value"=>t("View"));


            $fieldset[] = array("fields"=>$fields);

            print JarisCMS\Form\Generate($parameters, $fieldset);
            
            print "<hr />";
            
            print "<div><strong>" . t("Visits:") . "</strong> " . JarisCMS\Module\Visits\CountVisitors(null, $month, $year) . "</div>";
            print "<div><strong>" . t("Page Views:") . "</strong> " . JarisCMS\Module\Visits\CountPageViewsByMonth($month, $year) . "</div>";
            
            print "<hr />";
            
            print "<h2>" . t("Daily stats") . "</h2>";
            
            print "<table class=\"stats\">";
            print "<thead>";
            print "<tr>";
            print "<td>" . t("Date") . "</td>";
            print "<td>" . t("Visits") . "</td>";
            print "<td>" . t("Page Views") . "</td>";
            print "</tr>";
            print "</thead>";
            
            for($date=1; $date<=31; $date++)
            {
                $db = JarisCMS\SQLite\Open(JarisCMS\Module\Visits\GetDBName($year));
                
                $select  = "select * from visitors where date='$date' and month='$month' and year='$year' limit 0, 1";
                
                $result = JarisCMS\SQLite\Query($select, $db);
                
                $data = JarisCMS\SQLite\FetchArray($result);
                
                JarisCMS\SQLite\Close($db);
                
                if($data)
                {
                    print "<tr>";
                    
                    print "<td><a href=\"" . JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/visits/daily", "visits"), array("date"=>$date, "month"=>$month, "year"=>$year)) . "\">$date</a></td>";
                    
                    print "<td>" . JarisCMS\Module\Visits\CountVisitors($date, $month, $year) . "</td>";
                    
                    print "<td>" . JarisCMS\Module\Visits\CountPageViewsByDate($date, $month, $year) . "</td>";
                    
                    print "</tr>";
                }
            }
            
            print "</table>";
            
            
            
            print "<h2>" . t("Browser stats") . "</h2>";
            
            print "<table class=\"stats\">";
            print "<thead>";
            print "<tr>";
            print "<td>" . t("Browser") . "</td>";
            print "<td>" . t("Visits") . "</td>";
            print "</tr>";
            print "</thead>";
            
            $browsers = JarisCMS\Module\Visits\GetBrowserStatsByMonth($month, $year);
            
            foreach($browsers as $name=>$visits)
            {
                print "<tr>";
                
                print "<td>$name</td>";
                
                print "<td>$visits</td>";
                
                print "</tr>";
            }
            
            print "</table>";
            
            
            
            print "<h2>" . t("Country stats") . "</h2>";
            
            print "<table class=\"stats\">";
            print "<thead>";
            print "<tr>";
            print "<td>" . t("Country") . "</td>";
            print "<td>" . t("Visits") . "</td>";
            print "</tr>";
            print "</thead>";
            
            $countries = JarisCMS\Module\Visits\GetCountryStatsByMonth($month, $year);
            
            foreach($countries as $name=>$visits)
            {
                print "<tr>";
                
                print "<td>$name</td>";
                
                print "<td>$visits</td>";
                
                print "</tr>";
            }
            
            print "</table>";
            
            
            
            print "<h2>" . t("Most viewed pages stats") . "</h2>";
            
            print "<table class=\"stats\">";
            print "<thead>";
            print "<tr>";
            print "<td>" . t("Page") . "</td>";
            print "<td>" . t("Visits") . "</td>";
            print "</tr>";
            print "</thead>";
            
            $pages = JarisCMS\Module\Visits\GetMostViewedStatsByMonth($month, $year);
            
            foreach($pages as $page_uri=>$visits)
            {
                print "<tr>";
                
                print "<td><a href=\"" . JarisCMS\URI\PrintURL($page_uri) . "\">$page_uri</a></td>";
                
                print "<td>$visits</td>";
                
                print "</tr>";
            }
            
            print "</table>";
            
        ?>
    field;
    
    field: is_system
        1
    field;
row;
