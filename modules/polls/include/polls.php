<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Functions related to the management of polls
 *
 */

namespace JarisCMS\Module\Polls\Core\Recent
{
    use JarisCMS\Block;

    function Add($poll_page, $poll_title)
    {
        if(trim($poll_page) == "")
        {
            return false;
        }

        $block = Block\GetDataByField("poll_block", "1");

        $block["content"] = '
        <?php
            $page_data = JarisCMS\Page\GetData("' . $poll_page . '");
            $page_data["option_name"] = unserialize($page_data["option_name"]);
            $page_data["option_value"] = unserialize($page_data["option_value"]);

            $poll_data = array();
            foreach($page_data["option_name"] as $id=>$name)
            {
                $poll_data[t($name)] = $id;
            }

            $parameters["class"] = "block-poll";
            $parameters["action"] = JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/polls/vote", "polls"));
            $parameters["method"] = "get";

            $fields[] = array("type"=>"hidden", "name"=>"uri", "id"=>"uri", "value"=>"' . $poll_page . '");
            $fields[] = array("type"=>"hidden", "name"=>"actual_uri", "id"=>"actual_uri", "value"=>JarisCMS\URI\Get());
            $fields[] = array("type"=>"radio", "name"=>"id", "id"=>"id", "value"=>$poll_data, "horizontal_list"=>true);
            $fields[] = array("type"=>"submit", "value"=>t("Vote"));

            $fieldset[] = array("fields"=>$fields);

            print t("' . $poll_title . '") . "<br />";

            if(!isset($_COOKIE["poll"]["' . $poll_page . '"]) && !JarisCMS\Module\Polls\Core\Expired("' . $poll_page . '"))
            {
                print JarisCMS\Form\Generate($parameters, $fieldset);
            }
            else
            {
                $total_votes = 0;
                foreach($page_data["option_value"] as $value)
                {
                    $total_votes += $value;
                }

                $option_percent = array();
                foreach($page_data["option_value"] as $value)
                {
                    if($value <= 0)
                    {
                        $option_percent[] = 0;
                    }
                    else
                    {
                        $option_percent[] = floor(($value / $total_votes) * 100);
                    }
                }

                print "<div style=\"padding: 4px\">";
                for($i=0; $i<count($page_data["option_name"]); $i++)
                {
                    print "<br />";
                    print "<b>" . t($page_data["option_name"][$i]) . ":</b>";
                    print "<div style=\"text-align: center; background-color: #d3d3d3; width: {$option_percent[$i]}%\">{$option_percent[$i]}%</div>\n";
                }

                print "<br /><a href=\"" . JarisCMS\URI\PrintURL("' . $poll_page . '") . "\">" . t("More Details") . "</a>";
                print "</div>";
            }
        ?>
        ';
        $block["is_system"] = true;
        $block["poll_block"] = "1";
        $block["poll_page"] = $poll_page;

        Block\EditByField("poll_block", "1", $block);
    }

    function Edit($poll_page, $poll_title, $current_page)
    {
        if(trim($poll_page) == "")
        {
            return false;
        }

        $block = Block\GetDataByField("poll_block", "1");

        $block["content"] = '
        <?php
            $page_data = JarisCMS\Page\GetData("' . $poll_page . '");
            $page_data["option_name"] = unserialize($page_data["option_name"]);
            $page_data["option_value"] = unserialize($page_data["option_value"]);

            $poll_data = array();
            foreach($page_data["option_name"] as $id=>$name)
            {
                $poll_data[t($name)] = $id;
            }

            $parameters["class"] = "block-poll";
            $parameters["action"] = JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/polls/vote", "polls"));
            $parameters["method"] = "get";

            $fields[] = array("type"=>"hidden", "name"=>"uri", "id"=>"uri", "value"=>"' . $poll_page . '");
            $fields[] = array("type"=>"hidden", "name"=>"actual_uri", "id"=>"actual_uri", "value"=>JarisCMS\URI\Get());
            $fields[] = array("type"=>"radio", "name"=>"id", "id"=>"id", "value"=>$poll_data, "horizontal_list"=>true);
            $fields[] = array("type"=>"submit", "value"=>t("Vote"));

            $fieldset[] = array("fields"=>$fields);

            print t("' . $poll_title . '") . "<br />";

            if(!isset($_COOKIE["poll"]["' . $poll_page . '"]) && !JarisCMS\Module\Polls\Core\Expired("' . $poll_page . '"))
            {
                print JarisCMS\Form\Generate($parameters, $fieldset);
            }
            else
            {
                $total_votes = 0;
                foreach($page_data["option_value"] as $value)
                {
                    $total_votes += $value;
                }

                $option_percent = array();
                foreach($page_data["option_value"] as $value)
                {
                    if($value <= 0)
                    {
                        $option_percent[] = 0;
                    }
                    else
                    {
                        $option_percent[] = floor(($value / $total_votes) * 100);
                    }
                }

                print "<div style=\"padding: 4px\">";
                for($i=0; $i<count($page_data["option_name"]); $i++)
                {
                    print "<br />";
                    print "<b>" . t($page_data["option_name"][$i]) . ":</b>";
                    print "<div style=\"text-align: center; background-color: #d3d3d3; width: {$option_percent[$i]}%\">{$option_percent[$i]}%</div>\n";
                }

                print "<br /><a href=\"" . JarisCMS\URI\PrintURL("' . $poll_page . '") . "\">" . t("More Details") . "</a>";
                print "</div>";
            }
        ?>
        ';
        $block["is_system"] = true;
        $block["poll_block"] = "1";
        $block["poll_page"] = $poll_page;

        Block\EditByField("poll_page", $current_page, $block);
    }

    function Delete($poll_page)
    {
        if(trim($poll_page) == "")
        {
            return false;
        }

        $block = Block\GetDataByField("poll_block", "1");

        $block["content"] = '';
        $block["is_system"] = true;
        $block["poll_block"] = "1";
        $block["poll_page"] = "";

        Block\EditByField("poll_page", $poll_page, $block);
    }
}

namespace JarisCMS\Module\Polls\Core\SQLite
{
    use JarisCMS\SQLite;

    function Add($uri, $date)
    {
        if(!SQLite\DBExists("polls"))
        {
            $db = SQLite\Open("polls");
            SQLite\Query("create table polls (uri text, date text)", $db);
            SQLite\Close($db);
        }

        $db = SQLite\Open("polls");
        SQLite\Query("insert into polls (uri, date) values ('$uri', '$date')", $db);
        SQLite\Close($db);
    }

    function Delete($uri)
    {
        if(SQLite\DBExists("polls"))
        {
            $db = SQLite\Open("polls");
            SQLite\Query("delete from polls where uri = '$uri'", $db);
            SQLite\Close($db);
        }
    }

    function Get($page=0, $limit=30)
    {
        $db = null;
        $page *=  $limit;
        $polls = array();

        if(SQLite\DBExists("polls"))
        {
            $db = SQLite\Open("polls");
            $result = SQLite\Query("select uri from polls order by date desc limit $page, $limit", $db);
        }
        else
        {
            return $polls;
        }

        $fields = array();
        if($fields = SQLite\FetchArray($result))
        {
            $polls[] = $fields["uri"];

            while($fields = SQLite\FetchArray($result))
            {
                $polls[] = $fields["uri"];
            }

            SQLite\Close($db);
            return $polls;
        }
        else
        {
            SQLite\Close($db);
            return $polls;
        }
    }

    function Count()
    {
        if(SQLite\DBExists("polls"))
        {
            $db = SQLite\Open("polls");
            $result = SQLite\Query("select count(uri) as 'polls_count' from polls", $db);

            $count = SQLite\FetchArray($result);

            SQLite\Close($db);

            return $count["polls_count"];
        }
        else
        {
            return 0;
        }
    }
}

namespace JarisCMS\Module\Polls\Core
{
    use JarisCMS\URI;
    use JarisCMS\Page;
    use JarisCMS\Form;
    use JarisCMS\Module;

    function Expired($poll_uri)
    {
        $poll_data = Page\GetData($poll_uri);

        $time_diffrence = time() - $poll_data["created_date"];


        $days = floor($time_diffrence / 60 / 60 / 24 );

        if($poll_data["duration"] <= 0)
        {
            return false;
        }
        else
        {
            return $days >= $poll_data["duration"];
        }
    }

    function PrintPollsContent($uri, $content_data)
    {
        $content_data["option_name"] = unserialize($content_data["option_name"]);
        $content_data["option_value"] = unserialize($content_data["option_value"]);

        if(!isset($_COOKIE["poll"][$uri]) && !Expired($uri))
        {
            $poll_data = array();
            foreach($content_data["option_name"] as $id=>$name)
            {
                $poll_data[t($name)] = $id;
            }

            $parameters["class"] = "poll-vote";
            $parameters["action"] = URI\PrintURL(Module\GetPageURI("admin/polls/vote", "polls"));
            $parameters["method"] = "get";

            $fields[] = array("type"=>"hidden", "name"=>"uri", "id"=>"uri", "value"=>$uri);
            $fields[] = array("type"=>"hidden", "name"=>"actual_uri", "id"=>"actual_uri", "value"=>$uri);
            $fields[] = array("type"=>"radio", "name"=>"id", "id"=>"id", "value"=>$poll_data, "horizontal_list"=>true);
            $fields[] = array("type"=>"submit", "value"=>t("Vote"));

            $fieldset[] = array("fields"=>$fields);

            print Form\Generate($parameters, $fieldset);

            print "<hr />";
        }

        print "<h2>" . t("Results:") . "</h2>";

        $total_votes = 0;
        foreach($content_data["option_value"] as $value)
        {
            $total_votes += $value;
        }

        $option_percent = array();
        foreach($content_data["option_value"] as $value)
        {
            if($value <= 0)
            {
                $option_percent[] = 0;
            }
            else
            {
                $option_percent[] = floor(($value / $total_votes) * 100);
            }
        }

        for($i=0; $i<count($content_data["option_name"]); $i++)
        {
            print "<h4>" . t($content_data['option_name'][$i]) . "</h4>\n";

            if($content_data['option_value'][$i] != 0)
            {
                print "<div style=\"text-align: center; background-color: #d3d3d3; width: {$option_percent[$i]}%\">{$option_percent[$i]}% " . $content_data['option_value'][$i] . " " . t("of") . " " . $total_votes . " " . t("votes") . "</div>\n";
            }
            else
            {
                print "<div style=\"text-align: center; background-color: #d3d3d3; width: {$option_percent[$i]}%\">{$option_percent[$i]}%</div>\n";
            }

        }

        print "<br /><br />";

        print "<b>" . t("Total votes:") . "</b>" . $total_votes;
    }
}
?>
