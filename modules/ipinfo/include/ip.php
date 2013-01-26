<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 * 
 */

namespace JarisCMS\Module\IPInfo;

use JarisCMS\SQLite;
use JarisCMS\Setting;
use \SimpleXMLElement;

/**
 * Gets information of ip by city from external server or local database
 * depending on current configuration.
 * @param string $ip
 * @return boolean|array Array if info found or false if not.
 */
function GetByCity($ip)
{
    if(Setting\Get("use_external_api", "ipinfo"))
    {
        $d = file_get_contents(Setting\Get("city_group_url", "ipinfo") . "?ip=$ip");
     
        //return false if cannot connect
        if (!$d){
            return false;
        }else{
            $answer = new SimpleXMLElement($d);
        }
        
        $answer = get_object_vars($answer);
     
        $country_code = $answer["CountryCode"];
        $country_name = $answer["CountryName"];
        $region_code = $answer["RegionCode"];
        $region_name = $answer["RegionName"];
        $city = $answer["City"];
        $zippostalcode = $answer["ZipPostalCode"];
        $latitude = $answer["Latitude"];
        $longitude = $answer["Longitude"];
        $timezone = $answer["Timezone"];
        $gmtoffset = $answer["Gmtoffset"];
        $dstoffset = $answer["Dstoffset"];
     
        //Return the data as an array
        return array(
            'ip' => $ip, 
            'country_code' => $country_code, 
            'country_name' => $country_name, 
            'region_code' => $region_code, 
            'region_name' => $region_name, 
            'city' => $city, 
            'zipcode' => $zippostalcode, 
            'latitude' => $latitude, 
            'longitude' => $longitude, 
            'timezone' => $timezone, 
            'gmtOffset' => $gmtoffset, 
            'dstOffset' => $dstoffset
        );
    }
    else
    {
        return GetByCityInternal($ip);
    }

}

/**
 * Gets information of ip by country from external server or local database
 * depending on current configuration.
 * @param string $ip
 * @return boolean|array Array if info found or false if not.
 */
function GetByCountry($ip)
{
    if(Setting\Get("use_external_api", "ipinfo"))
    {
        $d = file_get_contents(Setting\Get("country_group_url", "ipinfo") . "?ip=$ip");
     
        //return false if cannot connect
        if (!$d){
            return false;
        }else{
            $answer = new SimpleXMLElement($d);
        }
        
        $answer = get_object_vars($answer);
     
        $country_code = $answer["CountryCode"];
        $country_name = $answer["CountryName"];
     
        //Return the data as an array
        return array(
            'ip' => $ip, 
            'country_code' => $country_code, 
            'country_name' => $country_name
        );
    }
    else
    {
        return GetByCountryInternal($ip);
    }
}

/**
 * Gets information of ip by city from local database.
 * @param string $ip
 * @return boolean|array Array if info found or false if not.
 */
function GetByCityInternal($ip)
{
    if(SQLite\DBExists("ip_group_city", "modules/ipinfo/db/"))
    {
        $ip = ConvertIP($ip);
        
        $db = SQLite\Open("ip_group_city", "modules/ipinfo/db/");
        
        $query = "SELECT 
            country_code, 
            country_name, 
            region_code, 
            region_name, 
            city, 
            zipcode, 
            latitude, 
            longitude, 
            timezone, 
            gmtOffset, 
            dstOffset FROM ip_group_city 
            
            where ip_start <= '$ip' order by ip_start desc limit 1";
        
        $result = SQLite\Query($query, $db);
        
        $data = SQLite\FetchArray($result);
        
        SQLite\Close($db);
        
        return $data;
    }
    else
    {
        return false;
    }
}

/**
 * Gets information of ip by country from local database.
 * @param string $ip
 * @return boolean|array Array if info found or false if not.
 */
function GetByCountryInternal($ip)
{
    if(SQLite\DBExists("ip_group_city", "modules/ipinfo/db/"))
    {
        $ip = ConvertIP($ip);
        
        $db = SQLite\Open("ip_group_country", "modules/ipinfo/db/");
        
        $query = "SELECT 
            ip_cidr, 
            country_code, 
            country_name FROM ip_group_country 
            
            where ip_start <= '$ip' order by ip_start desc limit 1";
        
        $result = SQLite\Query($query, $db);
        
        $data = SQLite\FetchArray($result);
        
        SQLite\Close($db);
        
        return $data;
    }
    else
    {
        return false;
    }
}

/**
 * Converts an ip to a whole integer used in the database for faster searches.
 * @param string $ip
 * @return int
 */
function ConvertIP($ip)
{
    $ip_segments = explode(".", $ip);
    
    $result = (($ip_segments[0]*256+$ip_segments[1])*256+$ip_segments[2])*256 + $ip_segments[3];
    
    return $result;
}
?>