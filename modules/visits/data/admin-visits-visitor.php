<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the referrals view page of memberpay.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Visitor Data") ?>
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
            JarisCMS\Security\ProtectPage(array("view_visitors_data_visits"));
  
            if(JarisCMS\Group\GetPermission("view_daily_visits", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("View Daily Stats"), JarisCMS\Module\GetPageURI("admin/visits/daily", "visits"));
            }
            
            if(JarisCMS\Group\GetPermission("view_monthly_visits", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("View Monthly Stats"), JarisCMS\Module\GetPageURI("admin/visits/monthly", "visits"));
            }
            
            if(JarisCMS\Group\GetPermission("view_yearly_visits", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("View Yearly Stats"), JarisCMS\Module\GetPageURI("admin/visits/yearly", "visits"));
            }
            
            $ip = $_REQUEST["ip"];
            $time = $_REQUEST["time"];
            $date = date("j", $time);
            $month = date("n", $time);
            $year = date("Y", $time);

            print "<h2>" . t("Stats for ip:") . " " . $ip . "</h2>";
            print "<strong>" . t("Date:") . " " . date("j/n/Y", $time) . "</strong>";
            
            print "<hr />";
            
            $location = JarisCMS\Module\IPInfo\GetByCity($ip);
            
            if($location)
            {
                print "<h2>" . t("Location") . "</h2>";
                
                print "<div><strong>" . t("Country code:") . "</strong> " . $location["country_code"] . "</div>";
                print "<div><strong>" . t("Country name:") . "</strong> " . $location["country_name"] . "</div>";
                print "<div><strong>" . t("Region code:") . "</strong> " . $location["region_code"] . "</div>";
                print "<div><strong>" . t("Region name:") . "</strong> " . $location["region_name"] . "</div>";
                print "<div><strong>" . t("City:") . "</strong> " . $location["city"] . "</div>";
                print "<div><strong>" . t("Zipcode:") . "</strong> " . $location["zipcode"] . "</div>";
                print "<div><strong>" . t("Latitude:") . "</strong> " . $location["latitude"] . "</div>";
                print "<div><strong>" . t("Longitude:") . "</strong> " . $location["longitude"] . "</div>";
                print "<div><strong>" . t("Timezone:") . "</strong> " . $location["timezone"] . "</div>";
                print "<div><strong>" . t("GMT Offset:") . "</strong> " . $location["gmtOffset"] . "</div>";
                print "<div><strong>" . t("DST Offset:") . "</strong> " . $location["dstOffset"] . "</div>";
            }
            
            $visitors_data = JarisCMS\Module\Visits\GetVisitorData($ip, $date, $month, $year);
            
            print "<h2>" . t("Visitor Data") . "</h2>";
            
            print "<div><strong>" . t("Start time:") . "</strong> " .  date("j/n/Y g:i:s a", $visitors_data["start_time"]) . "</div>";
            print "<div><strong>" . t("End time:") . "</strong> " .  date("j/n/Y g:i:s a", $visitors_data["end_time"]) . "</div>";
            print "<div><strong>" . t("Http referer:") . "</strong> " . $visitors_data["http_referer"] . "</div>";
            print "<div><strong>" . t("Http user agent:") . "</strong> " . $visitors_data["http_user_agent"] . "</div>";

            print "<h2>" . t("Viewed Pages") . "</h2>";
            
            print "<table class=\"stats\">";
            print "<thead>";
            print "<tr>";
            print "<td>" . t("Page") . "</td>";
            print "<td>" . t("View time") . "</td>";
            print "</tr>";
            print "</thead>";
            
            
            $select  = "select * from viewed_pages where visitor_ip='$ip' and date='$date' and month='$month' and year='$year' order by timestamp asc";
            
            $db = JarisCMS\SQLite\Open(JarisCMS\Module\Visits\GetDBName($year));
            $results = JarisCMS\SQLite\Query($select, $db);
            
            while($data = JarisCMS\SQLite\FetchArray($results))
            {
                print "<tr>";
                
                print "<td><a href=\"" . JarisCMS\URI\PrintURL($data["uri"]) . "\">" . $data["uri"] . "</a></td>";
                
                print "<td>" . date("j/n/Y g:i:s a", $data["timestamp"]) . "</td>";
                
                print "</tr>";
            }
            
            print "</table>";
            
            JarisCMS\SQLite\Close($db);
        ?>
    field;

    field: is_system
        1
    field;
row;