<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the themes info view page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Module Info") ?>
    field;

    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("view_modules"));

            $info = null;

            if(isset($_REQUEST["path"]))
            {
                $info = JarisCMS\Module\GetInfo($_REQUEST["path"]);
            }
            else
            {
                JarisCMS\System\GoToPage("admin/modules");
            }
        ?>

        <div class="module-info">
            <div class="info">
                <div>
                    <span class="label"><?php print t("Name:") ?></span>
                    <?php print t($info["name"]) ?>
                </div>
                <div>
                    <span class="label"><?php print t("Version:") ?></span>
                    <?php print t($info["version"]) ?>
                </div>
                <div>
                    <span class="label"><?php print t("Description:") ?></span>
                    <?php print t($info["description"]) ?>
                </div>
                <div>
                    <span class="label"><?php print t("Author:") ?></span>
                    <?php print t($info["author"]) ?>
                </div>
                <div>
                    <span class="label"><?php print t("Email:") ?></span>
                    <a href="mailto:<?php print $info["email"] ?>"><?php print $info["email"] ?></a>
                </div>
                <div>
                    <span class="label"><?php print t("Website:") ?></span>
                    <a target="_blank" href="<?php print $info["website"] ?>"><?php print t($info["website"]) ?></a>
                </div>
            </div>
        </div>
    field;

    field: is_system
        1
    field;
row;
