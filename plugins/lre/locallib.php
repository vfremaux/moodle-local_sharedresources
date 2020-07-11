<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package     local_sharedresources
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL
 *
 * Provides libraries for resource generic access.
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/sharedresources/plugins/lre/extlib/simpleXmlDeserializer.php');

/**
 * given an XML result bulk, get the interesting data in and
 * makes a hit array. Implenting a Saxe parser
 * @param string $results
 * @return array
 */
function lre_parse_xml_results($results) {
    global $CFG;

    $hits = array();
    $i = 0;

    $parser = new SimpleXmlDeserializer();
    $tree = $parser->parse($results);

    if (isset($tree['STRICTLRERESULTS'][0]['LOM'])) {
        foreach ($tree['STRICTLRERESULTS'][0]['LOM'] as $lom) {
            $hit = new StdClass;
            $identifiers = $lom['GENERAL'][0]['IDENTIFIER'];
            if (count($identifiers) == 1) {
                $hit->remoteid = $identifiers[0]['ENTRY'][0]['value'];
            } else {
                foreach ($identifiers as $identifierarr) {
                    if ($identifierarr['CATALOG'][0]['value'] == 'oai') {
                        $hit->remoteid = $identifierarr['ENTRY'][0]['value'];
                    }
                }
            }
            $hit->language = $lom['GENERAL'][0]['LANGUAGE'][0]['value'];
            $titles = $lom['GENERAL'][0]['TITLE'];
            foreach ($titles as $title) {
                // Scan available strings and choose our language than resource own language than defaults to last known.
                $hit->title = $title['STRING'][0]['value'];
                if ($title['STRING'][0]['attributes']['LANGUAGE']['value'] == substr(current_language(), 0, 2)) {
                    break;
                }
                if ($title['STRING'][0]['attributes']['LANGUAGE']['value'] == $hit->language) {
                    break;
                }
            }
            $hit->description = '';
            $descriptions = @$lom['GENERAL'][0]['DESCRIPTION'];
            if (!empty($descriptions)) {
                foreach ($descriptions as $description) {
                    // Scan available strings and choose our language than resource own language than defaults to last known.
                    $hit->description = $description['STRING'][0]['value'];
                    if ($description['STRING'][0]['attributes']['LANGUAGE']['value'] == substr(current_language(), 0, 2)) {
                        break;
                    }
                    if ($description['STRING'][0]['attributes']['LANGUAGE']['value'] == $hit->language) {
                        break;
                    }
                }
            }
            $hit->url = $lom['TECHNICAL'][0]['LOCATION'][0]['value'];
            $keywordset = array();
            $keywordstrings = @$lom['GENERAL'][0]['KEYWORD'][0]['STRING'];
            if (!empty($keywordstrings)) {
                foreach ($keywordstrings as $keywordstring) {
                    $keywordlanguage = $keywordstring['attributes']['LANGUAGE'];
                    if ($keywordlanguage == substr(current_language(), 0, 2) || $keywordlanguage == $hit->language) {
                        $keywordset[] = $keywordstring['value'];
                    }
                }
            }
            $hit->keywords = implode(', ', $keywordset);
            $hit->i = $i;
            $hits[] = $hit;
            $i++;
        }

        return $hits;
    } else {
        return array();
    }
}

/**
 * prints a single result slot.
 * @param object $hit
 * @param int $courseid
 * @param int $page the pagecount of this page of results
 */
function lre_print_search_result($hit, $courseid, $page) {
    global $CFG, $SESSION, $DB;

    $stringlocationurl = $CFG->dirroot.'/resources/plugins/lre/lang/';

    $seedetailstr = get_string('viewthenotice', 'lre', '', $stringlocationurl);
    $keywordsstr = get_string('keywords', 'lre', '', $stringlocationurl);
    $addtocourse = get_string('addtocourse', 'lre', '', $stringlocationurl);
    $num = $hit->i + (($page - 1) * $SESSION->SQI->resultSetSize) + 1;

    echo '<li class="searchresult">';
    echo "$num. <a href=\"{$hit->url}\" target=\"_blank\">{$hit->title}</a> ";
    echo "<img src=\"{$CFG->wwwroot}/resources/plugins/lre/pix/lremark.png\" /><br/>";
    echo "<a class=\"indexing_hiturl\" href=\"{$hit->url}\" target=\"_blank\">{$hit->url}</a><br/>";
    echo "<span class=\"desc\">".mb_convert_encoding($hit->description, 'utf-8', 'auto').'</span><br/>';
    echo "<div style=\"font-size:0.8em;margin-top:3px\" class=\"keywords\"><b>$keywordsstr</b>: {$hit->keywords}</div><br/>";
    if ($courseid > SITEID && $CFG->taomode != 'main') {
        $context = context_course::instance($courseid);
        if (has_capability('moodle/course:manageactivities', $context)) {
            $formurl = new moodle_url('/mod/sharedresource/addremotetocourse.php');
            echo '<form name="add'.$hit->i.'" action="'.$formurl.'" style="display:inline">';
            $entry = $DB->get_record('sharedresource_entry', 'remoteid', $hit->remoteid, 'provider', 'lre');
            if (empty($entry)) {
                echo '<input type="hidden" name="id" value="'.$courseid.'" />';
                echo "<input type=\"hidden\" name=\"title\" value=\"".htmlentities($hit->title, ENT_QUOTES, 'UTF-8')."\" />";
                echo "<input type=\"hidden\" name=\"description\" value=\"".htmlentities($hit->description, ENT_QUOTES, 'UTF-8')."\" />";
                echo "<input type=\"hidden\" name=\"url\" value=\"".htmlentities($hit->url, ENT_QUOTES, 'UTF-8')."\" />";
                echo "<input type=\"hidden\" name=\"keywords\" value=\"".htmlentities($hit->keywords, ENT_QUOTES, 'UTF-8')."\" />";
                echo "<input type=\"hidden\" name=\"provider\" value=\"lre\" />";
                echo "<input type=\"hidden\" name=\"repo\" value=\"lre\" />";
                echo "<input type=\"hidden\" name=\"remoteid\" value=\"".htmlentities($hit->remoteid, ENT_QUOTES, 'UTF-8')."\" />";
            } else {
                echo "<input type=\"hidden\" name=\"id\" value=\"{$courseid}\" />";
                echo "<input type=\"hidden\" name=\"repo\" value=\"lre\" />";
                echo "<input type=\"hidden\" name=\"identifier\" value=\"{$entry->identifier}\" />";
            }
            echo "</form>";
            echo "<div style=\"text-align:right\" class=\"commands\">";
            echo "<a href=\"javascript:document.forms['add{$hit->i}'].submit();\">{$addtocourse}</a></div>";
        }
    }
    echo "</li>";
}

function lre_print_paging($page, $maxpage, $courseid, $fullquery) {

    $links = array();

    $maxpageceil = min($maxpage, 20);
    for ($i = 1; $i <= $maxpageceil; $i++) {
        if ($page != $i) {
            $params = array('repo' => 'lre', 'id' => $courseid, 'p' => $i, 'query' => urlencode($fullquery));
            $qurl = new moodle_url('/resources/results.php', $params);
            $links[] = '<a href="'.$qurl.'">'.$i.'</a>';
        } else {
            $links[] = '<b><u>'.$i.'</u></b>';
        }
    }
    if ($maxpage > $maxpageceil) {
        $links[] = '...';
    }

    echo '<center>';
    echo implode(' ', $links);
    echo '</center>';
}
