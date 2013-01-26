<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the api page for get ip info with city.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        IP information with city
    field;

    field: content
        <?php 
            if(!JarisCMS\Setting\Get("enable_api", "ipinfo"))
            {
                JarisCMS\System\GoToPage("");
            }

            $ip_data = JarisCMS\Module\IPInfo\GetByCityInternal($_REQUEST["ip"]);
            
            header("Content-type: text/xml", true);
        ?>
<?php print "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" ?>
<Response>
    <Ip><?php print $_REQUEST["ip"] ?></Ip>
    <Status>OK</Status>
    <CountryCode><?php print $ip_data["country_code"] ?></CountryCode>
    <CountryName><?php print $ip_data["country_name"] ?></CountryName>
    <RegionCode><?php print $ip_data["region_code"] ?></RegionCode>

    <RegionName><?php print $ip_data["region_name"] ?></RegionName>
    <City><?php print $ip_data["city"] ?></City>
    <ZipPostalCode><?php print $ip_data["zipcode"] ?></ZipPostalCode>
    <Latitude><?php print $ip_data["latitude"] ?></Latitude>
    <Longitude><?php print $ip_data["longitude"] ?></Longitude>
    <Timezone><?php print $ip_data["timezone"] ?></Timezone>

    <Gmtoffset><?php print $ip_data["gmtOffset"] ?></Gmtoffset>
    <Dstoffset><?php print $ip_data["dstOffset"] ?></Dstoffset>
</Response>
    field;

    field: is_system
        1
    field;
row;
