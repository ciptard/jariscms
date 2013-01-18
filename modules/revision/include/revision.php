<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 */

namespace JarisCMS\Module\Revision;

/**
 * Generates html that shows differences in two files.
 * @param string $older Path to older file.
 * @param string $newer Path to newer file.
 * @return string
 */
function DiffFiles($older, $newer)
{
    require_once("modules/revision/phpdiff/class.Diff.php");
    
    $diff = \Diff::compareFiles($older, $newer);

    // initialise the HTML
    $html = '<div class="code">';

    $html .= "<table>";
    
    $line_number = 1;
    
    // loop over the lines in the diff
    foreach ($diff as $line)
    {
        $html .= "<tr>";
        
        $html .= "<td class=\"linenum\">$line_number</td>";
        
        $html .= "<td>";
        
        // extend the HTML with the line
        switch ($line[1]){
          case \Diff::UNMODIFIED : $element = 'span'; break;
          case \Diff::DELETED    : $element = 'del';  break;
          case \Diff::INSERTED   : $element = 'ins';  break;
        }
        $html .=
            '<' . $element . '>'
            . str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", htmlspecialchars($line[0]))
            . '</' . $element . '>';
        
        $html .= "</td>";
        
        $html .= "</tr>";
        
        $line_number++;
    }
    
    $html .= "</table>";
    
    $html .= "</div>";

    // return the HTML
    return $html;
}

/**
 * Generates html that shows differences in html strings.
 * @param string $older Older html string.
 * @param string $newer Newer html string.
 * @return string
 */
function DiffHTML($older, $newer)
{
    require_once("modules/revision/htmldiff/html_diff.php");
    
    return html_diff($older, $newer);
}
?>
