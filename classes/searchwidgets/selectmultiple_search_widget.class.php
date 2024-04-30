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
 *
 */
namespace local_sharedresources\search;

use \Stdclass;

defined('MOODLE_INTERNAL') || die();
define('MOREOPTIONS_THRESHOLD', 8);

require_once($CFG->dirroot.'/mod/sharedresource/metadatalib.php');
require_once($CFG->dirroot.'/local/sharedresources/classes/search_widget.class.php');

/**
 * search_widget defines a widget element for the search engine of metadata.
 */
class selectmultiple_widget extends search_widget {

    public function __construct($id, $label) {
        parent::__construct($id, $label, 'selectmultiple');
    }

    /**
     * Fonction used to display the widget. The parameter $display determines if plugins are displayed on a row or on a column
     * @param string $layout
     * @param mixed $value
     */
    public function print_search_widget($layout, $value = 0) {
        global $CFG, $OUTPUT;

        $str = '';

        $lowername = strtolower($this->label);
        $widgetname = get_string(clean_string_key($lowername), 'sharedmetadata_'.$this->schema);

        include_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$this->schema.'/plugin.class.php');
        $mtdclass = '\\mod_sharedresource\\plugin_'.$this->schema;
        $mtdstandard = new $mtdclass();

        $template = new Stdclass;
        $template->widgetname = $widgetname;

        $template->selectsearchstr = $OUTPUT->help_icon('selectsearch', 'sharedresource', false);
        $template->selectallstr = get_string('selectall', 'sharedresource');
        $template->unselectallstr = get_string('unselectall', 'sharedresource');
        $template->id = $this->id;
        $template->values = [];
        $template->morevalues = [];

        $i = 0;

        $template->hasmoreoptions = false;
        foreach ($mtdstandard->METADATATREE[$this->id]['values'] as $optvalue) {
            $valuetpl = new StdClass;
            $valuetpl->checked = ($this->checkvalue($optvalue, $value)) ? ' checked ' : '';
            $valuetpl->optvalue = $optvalue;
            $valuetpl->label = $this->label;
            if (is_numeric($optvalue)) {
                $valuetpl->optlabel = $optvalue;
            } else {
                $valuetpl->optlabel = get_string(clean_string_key(strtolower($optvalue)), 'sharedmetadata_'.$this->schema);
            }
            if ($i < MOREOPTIONS_THRESHOLD) {
                $template->values[] = $valuetpl;
            } else {
                $template->hasmoreoptions = true;
                $template->morevalues[] = $valuetpl;
            }
            $i++;
        }

        return $OUTPUT->render_from_template('local_sharedresources/search_selectmultiple', $template);
    }

    // Catchs a value in session from CGI input.
    public function catch_value(&$searchfields) {
        global $SESSION;

        if (!isset($SESSION->searchbag)) {
            $SESSION->searchbag = new StdClass();
        }

        if (optional_param('go', false, PARAM_TEXT) || optional_param('hardreset', false, PARAM_BOOL)) {
            // Real search query, or form hard reset, so delete session recording of choices. they will be rebuilt from query.
            if (isset($SESSION->searchbag->$paramkey)) {
                unset($SESSION->searchbag->$paramkey);
            }
        }

        $paramkey = str_replace(' ', '_', $this->label);
        $searchfields[$this->id] = @$SESSION->searchbag->$paramkey;

        if (isset($_GET[$paramkey])) {
            $valueset = array();
            if (is_array($_GET[$paramkey])) {
                $paramarrayval = clean_param_array($_GET[$paramkey], PARAM_TEXT);
                $selectvalue = implode(',', array_values($paramarrayval));
            } else {
                $paramval = clean_param($_GET[$paramkey], PARAM_TEXT);
                $selectvalue = $paramval;
            }
            if ($selectvalue != '') {
                $searchfields[$this->id] = $selectvalue;
                @$SESSION->searchbag->$paramkey = $selectvalue;
            }
        }
    }

    /**
     * checks an options against a selection given as scalar or array valueset.
     * @param mixed $opt the option to check in selection
     * @param mixed $value the selection as an array or a textual list
     */
    public function checkvalue($opt, $value) {
        if (is_array($value)) {
            return in_array($opt, $value);
        }
        if (is_string($value)) {
            $opt = str_replace('/', '\\/', $opt);
            return preg_match("/\\b{$opt}\\b/", $value);
        }
        return false;
    }
}
