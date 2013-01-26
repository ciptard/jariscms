<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 * 
 *@file Jaris CMS module functions file
 *
 *@note File that stores all hook functions.
 */

namespace JarisCMS\Module\Visits\Group
{
    function GetPermissions(&$permissions, &$group)
    {
        $visits["view_daily_visits"] = t("View Daily");
        $visits["view_monthly_visits"] = t("View Monthly");
        $visits["view_yearly_visits"] = t("View Yearly");
        $visits["view_visitors_data_visits"] = t("View Visitors Data");

        $permissions[t("Visits")] = $visits;
    }
}

namespace JarisCMS\Module\Visits\System
{
    use JarisCMS\URI;
    use JarisCMS\Group;
    use JarisCMS\Module;
    use JarisCMS\Security;
    use JarisCMS\Module\Visits;
    
    function Initialization()
    {
        if(!Security\IsAdminLogged())
        {
            Visits\Count();
            Visits\SaveViewedPage();
        }
    }
    
    function GenerateAdminPage(&$sections)
    {
        if(Group\GetPermission("view_daily_visits", Security\GetCurrentUserGroup()))
        {
           $content[] = array("title"=>t("Daily"), "url"=>URI\PrintURL(Module\GetPageURI("admin/visits/daily", "visits")), "description"=>t("The running date visit stats."));
        }
        if(Group\GetPermission("view_monthly_visits", Security\GetCurrentUserGroup()))
        {
           $content[] = array("title"=>t("Monthly"), "url"=>URI\PrintURL(Module\GetPageURI("admin/visits/monthly", "visits")), "description"=>t("The whole month visit stats."));
        }
        if(Group\GetPermission("view_yearly_visits", Security\GetCurrentUserGroup()))
        {
            $content[] = array("title"=>t("Yearly"), "url"=>URI\PrintURL(Module\GetPageURI("admin/visits/yearly", "visits")), "description"=>t("The whole year visit stats."));
        }

        if(Group\GetPermission("view_daily_visits", Security\GetCurrentUserGroup()) || Group\GetPermission("view_monthly_visits", Security\GetCurrentUserGroup()) || Group\GetPermission("view_yearly_visits", Security\GetCurrentUserGroup()))
        {
            $new_section[] = array("class"=>"visits", "title"=>t("Visits"), "sub_sections"=>$content);

            $original_sections = $sections;

            $sections = array_merge($new_section, $original_sections);
        }
    }
}
?>