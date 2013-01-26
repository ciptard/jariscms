<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Jaris CMS module install file
 *
 *Stores the installation script for jaris realty module.
 */

namespace JarisCMS\Module\ContactForms;

function Install()
{
    //To facilitate translation
    $text = t("Contact Form");
    $text = t("To create a contact form page.");
    $text = t("Message");

    //Create new contact form type
    $new_type["name"] = "Contact Form";
    $new_type["description"] = "To create a contact form page.";

    \JarisCMS\Type\Add("contact-form", $new_type);
}

?>
