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
 *
 * @author  Valery Fremaux
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resource
 * @package local_sharedresources
 * @subpackage search
 * @category local
 */
namespace local_sharedresources\search;

use \StdClass;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/sharedresource/metadatalib.php');
require_once($CFG->dirroot.'/local/sharedresources/classes/search_widget.class.php');

/**
 * search_widget defines a widget element for the search engine of metadata.
 */
class duration_widget extends search_widget {

    /**
     * Fonction used to display the widget. The parameter $display determines if plugins are displayed on a row or on a column
     */
    public function print_search_widget($layout, $value = 0) {
        global $OUTPUT, $CFG;

        $template = new StdClass;

        $lowername = strtolower($this->label);
        $template->widgetname = get_string(str_replace(' ', '', $lowername), 'sharedmetadata_'.$this->schema);

        if (!empty($value)) {
            preg_match('/^([^:]+):(.*)/', $value, $matches);
            $operator = $matches[1];
            $value = $this->durationsplit($matches[2]);
            $template->days = $value->days;
            $template->hours = $value->hours;
            $template->mins = $value->mins;
            $template->secs = $value->secs;
        } else {
            $operator = '';
            $template->days = '';
            $template->hours = '';
            $template->mins = '';
            $template->secs = '';
        }

        $template->equalselected = ($operator == '=') ? 'selected="selected"' : '';
        $template->nonequalselected = ($operator == '!=') ? 'selected="selected"' : '';
        $template->lessselected = ($operator == '<') ? 'selected="selected"' : '';
        $template->moreselected = ($operator == '>') ? 'selected="selected"' : '';
        $template->lessequalselected = ($operator == '<=') ? 'selected="selected"' : '';
        $template->moreequalselected = ($operator == '>=') ? 'selected="selected"' : '';

        $template->durationsearchhelpicon = $OUTPUT->help_icon('durationsearch', 'sharedresource', false);
        $template->label = $this->label;
        $template->dstr = get_string('d', 'sharedresource');
        $template->hstr = get_string('h', 'sharedresource');
        $template->mstr = get_string('m', 'sharedresource');
        $template->sstr = get_string('s', 'sharedresource');

        return $OUTPUT->render_from_template('local_sharedresources/search_duration', $template);
    }

    // Catchs a value in session from CGI input.
    public function catch_value(&$searchfields) {
        global $SESSION;

        if (!isset($SESSION->searchbag)) {
            $SESSION->searchbag = new StdClass();
        }

        $paramkey = str_replace(' ', '_', $this->label);
        $searchfields[$this->id] = @$SESSION->searchbag->$paramkey;

        // check we have operator and at least one field is fed
        if ((isset($_GET[$paramkey.'_day']) ||
                isset($_GET[$paramkey.'_hour']) ||
                        isset($_GET[$paramkey.'_min']) ||
                                isset($_GET[$paramkey.'_sec'])) &&
                                        isset($_GET[$paramkey.'_symbol']) &&
                                                $_GET[$paramkey.'_symbol'] != 'defaultvalue') {
            // Check of numeric values.
            if (($_GET[$paramkey.'_day'] == '' ||
                    is_numeric($_GET[$paramkey.'_day'])) &&
                            ($_GET[$paramkey.'_hour'] == '' ||
                                    is_numeric($_GET[$paramkey.'_hour'])) &&
                                            ($_GET[$paramkey.'_min'] == '' ||
                                                    is_numeric($_GET[$paramkey.'_min'])) &&
                                                            ($_GET[$paramkey.'_sec'] == '' ||
                                                                    is_numeric($_GET[$paramkey.'_sec']))) {
                $searchduration = 0;

                // Find number of seconds of the duration.
                if (isset($_GET[$paramkey.'_day'])) {
                    $searchduration += $_GET[$paramkey.'_day'] * DAYSECS;
                }
                if (isset($_GET[$paramkey.'_hour'])) {
                    $searchduration += $_GET[$paramkey.'_hour'] * HOURSECS;
                }
                if (isset($_GET[$paramkey.'_min'])) {
                    $searchduration += $_GET[$paramkey.'_min'] * 60;
                }
                if (isset($_GET[$paramkey.'_sec'])) {
                    $searchduration += $_GET[$paramkey.'_sec'];
                }

                $searchfields[$this->id] = $_GET[$paramkey.'_symbol'].':'.$searchduration;
                $SESSION->searchbag->$paramkey = $_GET[$paramkey.'_symbol'].':'.$searchduration;
            }
        }
        if (isset($_GET[$paramkey.'_symbol']) &&
                $_GET[$paramkey.'_symbol'] == 'defaultvalue') {
            $searchfields[$this->id] = '';
            $SESSION->searchbag->$paramkey = '';
        }
    }

    public function durationsplit($duration) {
        $return->days = floor($duration / DAYSECS);
        $duration -= $return->days * DAYSECS;
        $return->hours = floor($duration / HOURSECS);
        $duration -= $return->hours * HOURSECS;
        $return->mins = floor($duration / 60);
        $duration -= $return->mins * 60;
        $return->secs = $duration;

        return $return;
    }
}
