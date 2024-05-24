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
class freetext_widget extends search_widget {

    public function __construct($id, $label) {
        parent::__construct($id, $label, 'freetext');
    }

    /**
     * Fonction used to display the widget. The parameter $display determines if plugins are displayed on a row or on a column
     */
    public function print_search_widget($layout, $value = '') {
        global $OUTPUT;

        $template = new StdClass;

        $lowername = strtolower($this->label);
        $template->widgetname = get_string(clean_string_key($lowername), 'sharedmetadata_'.$this->schema);

        if (!empty($value) && preg_match('/^([^:]+):(.*)/', $value, $matches)) {
            $operator = $matches[1];
            $template->value = $matches[2];
        } else {
            $operator = '';
            $template->value = '';
        }

        $template->includesselected = ($operator == 'includes') ? 'selected="selected"' : '';
        $template->equalsselected = ($operator == 'equals') ? 'selected="selected"' : '';
        $template->beginswithselected = ($operator == 'beginswith') ? 'selected="selected"' : '';
        $template->endswithselected = ($operator == 'endswith') ? 'selected="selected"' : '';

        $template->textsearchhelpicon = $OUTPUT->help_icon('textsearch', 'sharedresource', false);
        $template->label = $this->label;
        $template->containsstr = get_string('contains', 'local_sharedresources');
        $template->equaltostr = get_string('equalto', 'local_sharedresources');
        $template->startswithstr = get_string('startswith', 'local_sharedresources');
        $template->endswithstr = get_string('endswith', 'local_sharedresources');

        return $OUTPUT->render_from_template('local_sharedresources/search_freetext', $template);
    }

    /**
     * catchs a value in session from CGI input
     * @param arrayref &$searchfields
     * @return true if filter configuration has changed
     */
    public function catch_value(&$searchfields) {
        global $SESSION;

        if (!isset($SESSION->searchbag)) {
            $SESSION->searchbag = new StdClass();
        }

        $paramkey = str_replace(' ', '_', $this->label);
        $searchfields[$this->id] = @$SESSION->searchbag->$paramkey;
        if (isset($_GET[$paramkey])) {
            $paramval = clean_param($_GET[$paramkey], PARAM_TEXT);
            $paramoptionval = clean_param($_GET[$paramkey.'_option'] ?? '', PARAM_TEXT);
            $searchstring = $paramoptionval.':'.$paramval;
            if ($_GET[$paramkey] != '') {
                $searchfields[$this->id] = $searchstring;
                $SESSION->searchbag->$paramkey = $searchstring;
            } else {
                $searchfields[$this->id] = '';
                $SESSION->searchbag->$paramkey = '';
            }
            return true;
        }

        return false;
    }
}
