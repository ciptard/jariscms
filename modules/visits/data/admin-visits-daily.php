<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the administration page for referrals on memberpay.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Daily Visitors") ?>
    field;

    field: content
        <style>
            .visitors td
            {
                width: auto;
                padding: 5px;
                border-bottom: solid 1px #000;
            }
            
            .visitors thead td
            {
                width: auto;
                font-weight:  bold;
                border-bottom: 0;
            }
        </style>
        
        <?php
            JarisCMS\Security\ProtectPage(array("view_daily_visits"));
            
            if(JarisCMS\Group\GetPermission("view_monthly_visits", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("View Monthly Stats"), JarisCMS\Module\GetPageURI("admin/visits/monthly", "visits"));
            }
            
            if(JarisCMS\Group\GetPermission("view_yearly_visits", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("View Yearly Stats"), JarisCMS\Module\GetPageURI("admin/visits/yearly", "visits"));
            }
           
            $page = 1;
            
            if(isset($_REQUEST["page"]))
            {
                $page = $_REQUEST["page"];
            }
            
            
            $time = time();
            $date = $_REQUEST["date"]?$_REQUEST["date"]:date("j", $time);
            $month = $_REQUEST["month"]?$_REQUEST["month"]:date("n", $time);
            $year = $_REQUEST["year"]?$_REQUEST["year"]:date("Y", $time);
            
            
            $parameters["name"] = "visits-daily-stats";
            $parameters["class"] = "visits-daily-stats";
            $parameters["action"] = JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/visits/daily", "visits"));
            $parameters["method"] = "get";
            
            $dates = array();
            for($i=1; $i<=31; $i++)
            {
                $dates[$i] = $i;
            }
            
            $fields[] = array("type"=>"select", "name"=>"date", "value"=>$dates, "selected"=>$date, "label"=>t("Date:"), "id"=>"date", "description"=>t("Select the date you want to see the stats."));

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
            
            
            $visitors_count = JarisCMS\Module\Visits\CountVisitors($date, $month, $year);
            
            print "<h2>" . t("Total visitors:") . " " . $visitors_count . "</h2>";
            
            print "<hr /><br />";
            
            $visitors = JarisCMS\Module\Visits\GetVisitors($page - 1, 100, $date, $month, $year);
            
            JarisCMS\System\PrintGenericNavigation($visitors_count, $page, "admin/visits/daily", "visits", 100, array("date"=>$date, "month"=>$month, "year"=>$year));
            
            print "<table class=\"visitors\">";
            print "<thead>";
            print "<tr>";
            print "<td>" . t("IP") . "</td>";
            print "<td>" . t("Country") . "</td>";
            print "<td>" . t("Start time") . "</td>";
            print "<td>" . t("End time") . "</td>";
            print "<td>" . t("Browser") . "</td>";
            print "<td>" . t("Referral") . "</td>";
            print "<td>" . t("Search terms") . "</td>";
            print "<td>" . t("Page Views") . "</td>";
            print "</tr>";
            print "</thead>";
            
            foreach($visitors as $ip)
            {
                $visitors_data = JarisCMS\Module\Visits\GetVisitorData($ip, $date, $month, $year);
                
                print "<tr>";
                
                print "<td><a href=\"" . JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/visits/visitor", "visits"), array("ip"=>$ip, "time"=>$visitors_data["start_time"])) . "\">" . $ip . "</a></td>";
                
                $country_name = $visitors_data["country_name"]?$visitors_data["country_name"]:t("N/A");
                
                print "<td>" . $country_name . "</td>";
                
                print "<td>" . date("g:i:s a", $visitors_data["start_time"]) . "</td>";
                
                print "<td>" . date("g:i:s a", $visitors_data["end_time"]) . "</td>";
                
                print "<td>" . str_replace(array("ie", "firefox", "chrome", "safari", "opera", "other"), array("Internet Explorer", "FireFox", "Chrome", "Safari", "Opera", "Other"), $visitors_data["browser_code"]) . "</td>";
                  
                
                print "<td>" . $visitors_data["host"] . "</td>";
                
                print "<td>" . JarisCMS\Module\Visits\GetKeywordsFromQuery($visitors_data["query"]) . "</td>";
                
                print "<td>" . JarisCMS\Module\Visits\CountPageViewsByVisitor($ip, $date, $month, $year) . "</td>";
                
                print "</tr>";
            }
            
            print "</table>";
            
            JarisCMS\System\PrintGenericNavigation($visitors_count, $page, "admin/visits/daily", "visits", 100, array("date"=>$date, "month"=>$month, "year"=>$year));
            

        ?>
    field;

    field: is_system
        1
    field;
row;
