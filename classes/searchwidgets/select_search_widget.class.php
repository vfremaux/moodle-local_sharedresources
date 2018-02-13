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
 * @version 0.0.1
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
class select_widget extends search_widget {

    /**
     * Fonction used to display the widget. The parameter $display determines if plugins are displayed on a row or on a column
     */
    public function print_search_widget($layout, $value = 0) {
        global $CFG, $OUTPUT;

        $template = new StdClass;

        $lowername = strtolower($this->label);
        $template->widgetname = get_string(str_replace(' ', '', $lowername), 'sharedmetadata_'.$this->schema);

        include_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$this->schema.'/plugin.class.php');
        $mtdclass = '\\mod_sharedresource\\plugin_'.$this->schema;
        $mtdstandard = new $mtdclass();
        $template->widgetname = $widgetname;
        $template->label = $this->label;
        $template->selectsearchhelpicon = $OUTPUT->help_icon('selectsearch', 'sharedresource', false);
        $options = '';
        foreach ($mtdstandard->METADATATREE[$this->id]['values'] as $optvalue) {
            $selected = ($value == $optvalue) ? 'selected="selected"' : '' ;
            $options .= "<option value=\"$optvalue\" $selected >".get_string(clean_string_key($optvalue), 'sharedmetadata_'.$this->schema).'</option>';
        }
        $template->options = $options;

        return $OUTPUT->render_from_template('local_sharedresources/search_select', $template);
    }

    // Catchs a value in session from CGI input.
    public function catch_value(&$searchfields) {
        global $SESSION;

        if (!isset($SESSION->searchbag)) {
            $SESSION->searchbag = new StdClass();
        }

        $paramkey = str_replace(' ', '_', $this->label);
        $searchfields[$this->id] = @$SESSION->searchbag->$paramkey;
        if (isset($_GET[$paramkey]) && $_GET[$paramkey] != 'defaultvalue') {
            $searchfields[$this->id] = $_GET[$paramkey];
            $SESSION->searchbag->$paramkey = $_GET[$paramkey];
        }
    }
}
