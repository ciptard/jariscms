<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the language info view page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Language Info") ?>
    field;

    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("view_languages"));

            $info = null;

            if(isset($_REQUEST["code"]))
            {
                $info = JarisCMS\Language\GetInfo($_REQUEST["code"]);
            }
            else
            {
                JarisCMS\System\GoToPage("admin/languages");
            }
            
            if(JarisCMS\Group\GetPermission("edit_languages", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("Edit Info"), "admin/languages/edit-info", array("code"=>$_REQUEST["code"]));
            }
        ?>

        <div class="language-info">
            <div class="info">
                <div>
                    <span class="label"><?php print t("Name:") ?></span>
                    <?php print t($info["name"]) ?>
                </div>
                
                <div>
                    <span class="label"><?php print t("Code:") ?></span>
                    <?php print t($info["code"]) ?>
                </div>
                
                <div>
                    <span class="label"><?php print t("Translator:") ?></span>
                    <?php print t($info["translator"]) ?>
                </div>
                
                <div>
                    <span class="label"><?php print t("E-mail:") ?></span>
                    <a href="mailto:<?php print $info["translator_email"] ?>"><?php print $info["translator_email"] ?></a>
                </div>
                
                <?php if(trim($info["contributors"]) != ""){ ?>
                <hr />
                <div>
                    <span class="label"><?php print t("Contributors:") ?></span>
                    <br />
                    <?php print str_replace("\n", "<br />\n", $info["contributors"]) ?>
                </div>
                <?php } ?>
            </div>
        </div>
    field;

    field: is_system
        1
    field;
row;
