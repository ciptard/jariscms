<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the yearly visits stats.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Yearly Visits") ?>
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
        
            JarisCMS\Security\ProtectPage(array("view_yearly_visits"));
            
            if(JarisCMS\Group\GetPermission("view_daily_visits", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("View Daily Stats"), JarisCMS\Module\GetPageURI("admin/visits/daily", "visits"));
            }
            
            if(JarisCMS\Group\GetPermission("view_monthly_visits", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("View Monthly Stats"), JarisCMS\Module\GetPageURI("admin/visits/monthly", "visits"));
            }
            
            $time = time();
            $year = $_REQUEST["year"]?$_REQUEST["year"]:date("Y", $time);
            
            
            $parameters["name"] = "visits-yearly-stats";
            $parameters["class"] = "visits-yearly-stats";
            $parameters["action"] = JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/visits/yearly", "visits"));
            $parameters["method"] = "get";
            
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
            
            print "<div><strong>" . t("Visits:") . "</strong> " . JarisCMS\Module\Visits\CountVisitors(null, null, $year) . "</div>";
            print "<div><strong>" . t("Page Views:") . "</strong> " . JarisCMS\Module\Visits\CountPageViewsByYear($year) . "</div>";
            
            print "<hr />";
            
            print "<h2>" . t("Monthly stats") . "</h2>";
            
            print "<table class=\"stats\">";
            print "<thead>";
            print "<tr>";
            print "<td>" . t("Month") . "</td>";
            print "<td>" . t("Visits") . "</td>";
            print "<td>" . t("Page Views") . "</td>";
            print "</tr>";
            print "</thead>";
            
            $months_text[1] = t("January");
            $months_text[2] = t("February");
            $months_text[3] = t("March");
            $months_text[4] = t("April");
            $months_text[5] = t("May");
            $months_text[6] = t("June");
            $months_text[7] = t("July");
            $months_text[8] = t("August");
            $months_text[9] = t("September");
            $months_text[10] = t("October");
            $months_text[11] = t("November");
            $months_text[12] = t("December");
            
            for($month=1; $month<=12; $month++)
            {
                $db = JarisCMS\SQLite\Open(JarisCMS\Module\Visits\GetDBName($year));
                
                $select  = "select * from visitors where month='$month' and year='$year' limit 0, 1";
                
                $result = JarisCMS\SQLite\Query($select, $db);
                
                $data = JarisCMS\SQLite\FetchArray($result);
                
                JarisCMS\SQLite\Close($db);
                
                if($data)
                {
                    print "<tr>";
                    
                    print "<td><a href=\"" . JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/visits/monthly", "visits"), array("month"=>$month, "year"=>$year)) . "\">" . $months_text[$month] . "</a></td>";
                    
                    print "<td>" . JarisCMS\Module\Visits\CountVisitors(null, $month, $year) . "</td>";
                    
                    print "<td>" . JarisCMS\Module\Visits\CountPageViewsByMonth($month, $year) . "</td>";
                    
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
            
            $browsers = JarisCMS\Module\Visits\GetBrowserStatsByYear($year);
            
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
            
            $countries = JarisCMS\Module\Visits\GetCountryStatsByYear($year);
            
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
            
            $pages = JarisCMS\Module\Visits\GetMostViewedStatsByYear($year);
            
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