<?php
<<<<<<< HEAD

/**
* get the query parts, recompose the complete LRE SQI query and shoots a SQI SOAP call.
*/   

    require_once 'sqilib.php'; 
    require_once 'locallib.php'; 
    require_once 'form_remote_search.class.php'; 

    $stringlocationurl = $CFG->dirroot.'/resources/plugins/lre/lang/';
    
    $searchform = new Remote_Search_Form('');

    $courseid = optional_param('id', null, PARAM_INT);
    $page = optional_param('p', 0, PARAM_INT);
    
    $data = $searchform->get_data();
    if (!$page){
        if (empty($data)) 
            redirect($CFG->wwwroot."/resources/search.php?id={$courseid}&amp;repo=lre");
            $query = clean_param($data->query, PARAM_TEXT);
            if (!empty($query)) $queryparts[] = $query;
            if (!empty($data->minAge)) $queryparts[] = 'lre.minAge='.clean_param($data->minAge, PARAM_INT);
            if (!empty($data->maxAge)) $queryparts[] = 'lre.maxAge='.clean_param($data->maxAge, PARAM_INT);
            if (!empty($data->loLanguage)) $queryparts[] = 'lre.loLanguage='.clean_param($data->loLanguage, PARAM_TEXT);
            if (!empty($data->lrt)) $queryparts[] = 'lre.lrt='.clean_param($data->lrt, PARAM_TEXT);
            if (!empty($data->mtdLanguage)) $queryparts[] = ' lre.mtdLanguage='.clean_param($data->mtdLanguage, PARAM_TEXT);
            $fullquery = implode(' and ', $queryparts);
    } else {
            $fullquery = required_param('query', PARAM_RAW);
    }
    
    print_box_start();
    
    // we have search data. Need assemble
    
    $displayquery = '<b>'.get_string('yousearched', 'lre', '', $stringlocationurl).':</b> '. $fullquery;

    if (empty($page)){
        SQIEnd();    
        SQIInit();
        $page = 1;
    }
    
    if ($page == 1){
        $xmlresults = SQIQuery($fullquery);
    } else {
        $xmlresults = SQIGetPage($page);
    }

    $maxpage = SQIGetMaxPage();
    $counter->page = $page;
    $counter->results = SQIResultsCount();
    $resultcount = get_string('resultcount', 'lre', $counter, $stringlocationurl);
    
    if ($maxpage >= 2){
        lre_print_paging($page, $maxpage, $courseid, $fullquery);
    }

    echo "<p><table width=\"100%\"><tr><td><span class=\"searched\">$displayquery</span></td><td align=\"right\">$resultcount</td></tr></table></p>";
    
    if (!empty($xmlresults)){
      // if (true){
      // $xmlresults = implode('', file($CFG->dirroot.'/x_tmp/lre_results.xml'));
        
        $hits = lre_parse_xml_results($xmlresults);
        
        if (!empty($hits)){

            foreach($hits as $hit){
                lre_print_search_result($hit, $courseid, $page);
            }
            
            if ($maxpage >= 2){
                lre_print_paging($page, $maxpage, $courseid, $fullquery);
            }
            
        } else {
            print_box(get_string('noresults', 'lre', '', $stringlocationurl));
        }    
    } else {
        // may be an error
        echo get_string('badquery', 'lre', '', $stringlocationurl);
    }

    add_to_log(0, 'lre', 'results', $fullquery, 0);
    
    print_box_end();

?>
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
 * @package    local_sharedresources
 * @category   local
 * @author Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 * Provides libraries for resource generic access.
 */
defined('MOODLE_INTERNAL') || die();

/**
 * get the query parts, recompose the complete LRE SQI query and shoots a SQI SOAP call.
 */

require_once($CFG->dirroot.'/local/sharedresources/plugin/lre/sqilib.php');
require_once($CFG->dirroot.'/local/sharedresources/plugin/lre/locallib.php');
require_once($CFG->dirroot.'/local/sharedresources/plugin/lre/form_remote_search.class.php');

$searchform = new Remote_Search_Form('');

$courseid = optional_param('id', null, PARAM_INT);
$page = optional_param('p', 0, PARAM_INT);

$data = $searchform->get_data();
if (!$page) {

    if (empty($data)) {
        redirect(new moodle_url('/local/sharedresources/search.php', array('id' => $courseid, 'repo' => 'lre')));
    }

    $query = clean_param($data->query, PARAM_TEXT);
    if (!empty($query)) {
        $queryparts[] = $query;
    }
    if (!empty($data->minAge)) {
        $queryparts[] = 'lre.minAge='.clean_param($data->minAge, PARAM_INT);
    }
    if (!empty($data->maxAge)) {
        $queryparts[] = 'lre.maxAge='.clean_param($data->maxAge, PARAM_INT);
    }
    if (!empty($data->loLanguage)) {
        $queryparts[] = 'lre.loLanguage='.clean_param($data->loLanguage, PARAM_TEXT);
    }
    if (!empty($data->lrt)) {
        $queryparts[] = 'lre.lrt='.clean_param($data->lrt, PARAM_TEXT);
    }
    if (!empty($data->mtdLanguage)) {
        $queryparts[] = ' lre.mtdLanguage='.clean_param($data->mtdLanguage, PARAM_TEXT);
    }
    $fullquery = implode(' and ', $queryparts);
} else {
    $fullquery = required_param('query', PARAM_RAW);
}

echo $OUTPUT->box_start();

// We have search data. Need assemble.

$displayquery = '<b>'.get_string('yousearched', 'local_sharedresources').':</b> '. $fullquery;

if (empty($page)) {
    SQI::end();
    SQI::init();
    $page = 1;
}

if ($page == 1) {
    $xmlresults = SQI::query($fullquery);
} else {
    $xmlresults = SQI::get_page($page);
}

$maxpage = SQI::get_max_page();
$counter->page = $page;
$counter->results = SQI::results_count();
$resultcount = get_string('resultcount', 'lre', $counter, $stringlocationurl);

if ($maxpage >= 2) {
    lre_print_paging($page, $maxpage, $courseid, $fullquery);
}

echo '<p><table width="100%">';
echo '<tr>';
echo '<td><span class="searched">'.$displayquery.'</span></td>';
echo '<td align="right">'.$resultcount.'</td>';
echo '</tr>';
echo '</table>';
echo '</p>';

if (!empty($xmlresults)) {

    $hits = lre_parse_xml_results($xmlresults);

    if (!empty($hits)) {

        foreach ($hits as $hit) {
            lre_print_search_result($hit, $courseid, $page);
        }

        if ($maxpage >= 2) {
            lre_print_paging($page, $maxpage, $courseid, $fullquery);
        }

    } else {
        echo $OUTPUT->box(get_string('noresults', 'local_sharedresources'));
    }
} else {
    // May be an error.
    echo get_string('badquery', 'local_sharedresources');
}

echo $OUTPUT->box_end();
>>>>>>> MOODLE_33_STABLE
