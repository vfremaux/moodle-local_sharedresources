<?php
<<<<<<< HEAD

/**
*
*/

require $CFG->dirroot.'/local/lib/simpleXmlDeserializer.php';

/**
* given an XML result bulk, get the interesting data in and 
* makes a hit array. Implenting a Saxe parser
* @param string $results
* @return array
*/
function lre_parse_xml_results($results){
=======
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
>>>>>>> MOODLE_33_STABLE
    global $CFG;

    $hits = array();
    $i = 0;

    $parser = new SimpleXmlDeserializer();
    $tree = $parser->parse($results);
<<<<<<< HEAD
<<<<<<< HEAD
    
    // print_object($tree);
    if (isset($tree['STRICTLRERESULTS'][0]['LOM'])){
        foreach($tree['STRICTLRERESULTS'][0]['LOM'] as $LOM){
            $hit = new StdClass;
            $IDENTIFIERS = $LOM['GENERAL'][0]['IDENTIFIER'];
            if (count($IDENTIFIERS) == 1){
                $hit->remoteid = $IDENTIFIERS[0]['ENTRY'][0]['value'];
            } else {
                foreach($IDENTIFIERS as $IDENTIFIER){
                    if ($IDENTIFIER['CATALOG'][0]['value'] == 'oai'){
=======

    if (isset($tree['STRICTLRERESULTS'][0]['LOM'])) {
        foreach ($tree['STRICTLRERESULTS'][0]['LOM'] as $lom) {
            $hit = new StdClass;
            $identifiers = $lom['GENERAL'][0]['IDENTIFIER'];
            if (count($identifiers) == 1) {
                $hit->remoteid = $identifiers[0]['ENTRY'][0]['value'];
            } else {
=======

    if (isset($tree['STRICTLRERESULTS'][0]['LOM'])) {
        foreach ($tree['STRICTLRERESULTS'][0]['LOM'] as $lom) {
            $hit = new StdClass;
            $identifiers = $lom['GENERAL'][0]['IDENTIFIER'];
            if (count($identifiers) == 1) {
                $hit->remoteid = $identifiers[0]['ENTRY'][0]['value'];
            } else {
>>>>>>> MOODLE_34_STABLE
                foreach ($identifiers as $IDENTIFIER) {
                    if ($IDENTIFIER['CATALOG'][0]['value'] == 'oai') {
>>>>>>> MOODLE_33_STABLE
                        $hit->remoteid = $IDENTIFIER['ENTRY'][0]['value'];
                    }
                }
            }
<<<<<<< HEAD
<<<<<<< HEAD
            $hit->language = $LOM['GENERAL'][0]['LANGUAGE'][0]['value'];
            $TITLES = $LOM['GENERAL'][0]['TITLE'];
            foreach($TITLES as $TITLE){
                // scan available strings and choose our language than resource own language than defaults to last known
                $hit->title = $TITLE['STRING'][0]['value'];
                if ($TITLE['STRING'][0]['attributes']['LANGUAGE']['value'] == substr(current_language(), 0, 2)){
                    break;
                }
                if ($TITLE['STRING'][0]['attributes']['LANGUAGE']['value'] == $hit->language){
=======
=======
>>>>>>> MOODLE_34_STABLE
            $hit->language = $lom['GENERAL'][0]['LANGUAGE'][0]['value'];
            $titles = $lom['GENERAL'][0]['TITLE'];
            foreach ($titles as $title) {
                // Scan available strings and choose our language than resource own language than defaults to last known.
                $hit->title = $title['STRING'][0]['value'];
                if ($title['STRING'][0]['attributes']['LANGUAGE']['value'] == substr(current_language(), 0, 2)) {
                    break;
                }
                if ($title['STRING'][0]['attributes']['LANGUAGE']['value'] == $hit->language) {
<<<<<<< HEAD
>>>>>>> MOODLE_33_STABLE
=======
>>>>>>> MOODLE_34_STABLE
                    break;
                }
            }
            $hit->description = '';
<<<<<<< HEAD
<<<<<<< HEAD
            $DESCRIPTIONS = @$LOM['GENERAL'][0]['DESCRIPTION'];
            if (!empty($DESCRIPTIONS)){
                foreach($DESCRIPTIONS as $DESCRIPTION){
                    // scan available strings and choose our language than resource own language than defaults to last known
                    $hit->description = $DESCRIPTION['STRING'][0]['value'];
                    if ($DESCRIPTION['STRING'][0]['attributes']['LANGUAGE']['value'] == substr(current_language(), 0, 2)){
                        break;
                    }
                    if ($DESCRIPTION['STRING'][0]['attributes']['LANGUAGE']['value'] == $hit->language){
=======
            $descriptions = @$lom['GENERAL'][0]['DESCRIPTION'];
            if (!empty($descriptions)) {
                foreach ($descriptions as $description) {
                    // Scan available strings and choose our language than resource own language than defaults to last known.
                    $hit->description = $description['STRING'][0]['value'];
                    if ($description['STRING'][0]['attributes']['LANGUAGE']['value'] == substr(current_language(), 0, 2)) {
                        break;
                    }
                    if ($description['STRING'][0]['attributes']['LANGUAGE']['value'] == $hit->language) {
>>>>>>> MOODLE_33_STABLE
=======
            $descriptions = @$lom['GENERAL'][0]['DESCRIPTION'];
            if (!empty($descriptions)) {
                foreach ($descriptions as $description) {
                    // Scan available strings and choose our language than resource own language than defaults to last known.
                    $hit->description = $description['STRING'][0]['value'];
                    if ($description['STRING'][0]['attributes']['LANGUAGE']['value'] == substr(current_language(), 0, 2)) {
                        break;
                    }
                    if ($description['STRING'][0]['attributes']['LANGUAGE']['value'] == $hit->language) {
>>>>>>> MOODLE_34_STABLE
                        break;
                    }
                }
            }
<<<<<<< HEAD
<<<<<<< HEAD
            $hit->url = $LOM['TECHNICAL'][0]['LOCATION'][0]['value'];
            $keywordset = array();
            $KEYWORDSTRINGS = @$LOM['GENERAL'][0]['KEYWORD'][0]['STRING'];
            if (!empty($KEYWORDSTRINGS)){
                foreach($KEYWORDSTRINGS as $KEYWORDSTRING){
                    $keywordLanguage = $KEYWORDSTRING['attributes']['LANGUAGE'];
                    if ($keywordLanguage == substr(current_language(), 0, 2) || $keywordLanguage == $hit->language){
                        $keywordset[] = $KEYWORDSTRING['value'];
=======
            $hit->url = $lom['TECHNICAL'][0]['LOCATION'][0]['value'];
            $keywordset = array();
=======
            $hit->url = $lom['TECHNICAL'][0]['LOCATION'][0]['value'];
            $keywordset = array();
>>>>>>> MOODLE_34_STABLE
            $keywordstrings = @$lom['GENERAL'][0]['KEYWORD'][0]['STRING'];
            if (!empty($keywordstrings)) {
                foreach ($keywordstrings as $keywordstring) {
                    $keywordLanguage = $keywordstring['attributes']['LANGUAGE'];
                    if ($keywordLanguage == substr(current_language(), 0, 2) || $keywordLanguage == $hit->language) {
                        $keywordset[] = $keywordstring['value'];
<<<<<<< HEAD
>>>>>>> MOODLE_33_STABLE
=======
>>>>>>> MOODLE_34_STABLE
                    }
                }
            }
            $hit->keywords = implode(', ', $keywordset);
            $hit->i = $i;
            $hits[] = $hit;
            $i++;
        }
<<<<<<< HEAD
<<<<<<< HEAD
        
=======

>>>>>>> MOODLE_33_STABLE
=======

>>>>>>> MOODLE_34_STABLE
        return $hits;
    } else {
        return array();
    }
}

/**
<<<<<<< HEAD
<<<<<<< HEAD
* prints a single result slot.
* @param object $hit
* @param int $courseid
* @param int $page the pagecount of this page of results
*/
function lre_print_search_result($hit, $courseid, $page){
    global $CFG, $SESSION;
    
    $stringlocationurl = $CFG->dirroot.'/resources/plugins/lre/lang/';
    
=======
 * prints a single result slot.
 * @param object $hit
 * @param int $courseid
 * @param int $page the pagecount of this page of results
 */
function lre_print_search_result($hit, $courseid, $page) {
    global $CFG, $SESSION, $DB;

    $stringlocationurl = $CFG->dirroot.'/resources/plugins/lre/lang/';

>>>>>>> MOODLE_33_STABLE
=======
 * prints a single result slot.
 * @param object $hit
 * @param int $courseid
 * @param int $page the pagecount of this page of results
 */
function lre_print_search_result($hit, $courseid, $page) {
    global $CFG, $SESSION, $DB;

    $stringlocationurl = $CFG->dirroot.'/resources/plugins/lre/lang/';

>>>>>>> MOODLE_34_STABLE
    $seedetailstr = get_string('viewthenotice', 'lre', '', $stringlocationurl);
    $keywordsstr = get_string('keywords', 'lre', '', $stringlocationurl);
    $addtocourse = get_string('addtocourse', 'lre', '', $stringlocationurl);
    $num = $hit->i + (($page - 1) * $SESSION->SQI->resultSetSize) + 1;

    echo '<li class="searchresult">';
    echo "$num. <a href=\"{$hit->url}\" target=\"_blank\">{$hit->title}</a> ";
    echo "<img src=\"{$CFG->wwwroot}/resources/plugins/lre/pix/lremark.png\" /><br/>";
    echo "<a class=\"indexing_hiturl\" href=\"{$hit->url}\" target=\"_blank\">{$hit->url}</a><br/>";
<<<<<<< HEAD
<<<<<<< HEAD
    // echo "<a style=\"font-size:0.8em\" href=\"{$hit->urldesc}\" target=\"_blank\">{$seedetailstr}</a><br/>";
=======
>>>>>>> MOODLE_34_STABLE
    echo "<span class=\"desc\">".mb_convert_encoding($hit->description, 'utf-8', 'auto').'</span><br/>';
    echo "<div style=\"font-size:0.8em;margin-top:3px\" class=\"keywords\"><b>$keywordsstr</b>: {$hit->keywords}</div><br/>";
    if ($courseid > SITEID && $CFG->taomode != 'main'){
        $context = get_context_instance(CONTEXT_COURSE, $courseid);
        if (has_capability('moodle/course:manageactivities', $context)){
            echo "<form name=\"add{$hit->i}\" action=\"{$CFG->wwwroot}/mod/taoresource/addremotetocourse.php\" style=\"display:inline\">";
            $entry = get_record('taoresource_entry', 'remoteid', $hit->remoteid, 'provider', 'lre');
            if (empty($entry)){
                echo "<input type=\"hidden\" name=\"id\" value=\"{$courseid}\" />";
=======
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
<<<<<<< HEAD
>>>>>>> MOODLE_33_STABLE
=======
>>>>>>> MOODLE_34_STABLE
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
<<<<<<< HEAD
<<<<<<< HEAD
            echo "<div style=\"text-align:right\" class=\"commands\"><a href=\"javascript:document.forms['add{$hit->i}'].submit();\">{$addtocourse}</a></div>";
=======
            echo "<div style=\"text-align:right\" class=\"commands\">";
            echo "<a href=\"javascript:document.forms['add{$hit->i}'].submit();\">{$addtocourse}</a></div>";
>>>>>>> MOODLE_33_STABLE
=======
            echo "<div style=\"text-align:right\" class=\"commands\">";
            echo "<a href=\"javascript:document.forms['add{$hit->i}'].submit();\">{$addtocourse}</a></div>";
>>>>>>> MOODLE_34_STABLE
        }
    }
    echo "</li>";
}

<<<<<<< HEAD
function lre_print_paging($page, $maxpage, $courseid, $fullquery){
    global $CFG;

    $links = array();

    $maxpageceil = min($maxpage, 20);
<<<<<<< HEAD
    for($i = 1 ; $i <= $maxpageceil ; $i++){
        if ($page != $i){
            $links[] = "<a href=\"{$CFG->wwwroot}/resources/results.php?repo=lre&amp;id=$courseid&amp;p={$i}&query=".urlencode($fullquery)."\">{$i}</a>";
=======
    for ($i = 1; $i <= $maxpageceil; $i++) {
        if ($page != $i) {
            $qurl = new moodle_url('/resources/results.php', array('repo' => 'lre', 'id' => $courseid, 'p' => $i, 'query' => urlencode($fullquery)));
            $links[] = '<a href="'.$qurl.'">'.$i.'</a>';
>>>>>>> MOODLE_34_STABLE
        } else {
            $links[] = '<b><u>'.$i.'</u></b>';
        }
    }
<<<<<<< HEAD
    if ($maxpage > $maxpageceil) $links[] = '...';
    
=======
function lre_print_paging($page, $maxpage, $courseid, $fullquery) {
    global $CFG;

    $links = array();

    $maxpageceil = min($maxpage, 20);
    for ($i = 1; $i <= $maxpageceil; $i++) {
        if ($page != $i) {
            $qurl = new moodle_url('/resources/results.php', array('repo' => 'lre', 'id' => $courseid, 'p' => $i, 'query' => urlencode($fullquery)));
            $links[] = '<a href="'.$qurl.'">'.$i.'</a>';
        } else {
            $links[] = '<b><u>'.$i.'</u></b>';
        }
    }
=======
>>>>>>> MOODLE_34_STABLE
    if ($maxpage > $maxpageceil) {
        $links[] = '...';
    }

<<<<<<< HEAD
>>>>>>> MOODLE_33_STABLE
=======
>>>>>>> MOODLE_34_STABLE
    echo '<center>';
    echo implode(' ', $links);
    echo '</center>';
}
<<<<<<< HEAD

?>
=======
>>>>>>> MOODLE_33_STABLE
