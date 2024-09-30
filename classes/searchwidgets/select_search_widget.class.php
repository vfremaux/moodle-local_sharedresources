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
 * Widget for single search criteria.
 *
 * @package local_sharedresources
 * @subpackage search
 * @author  Valery Fremaux
 * @copyright  Valery Fremaux (activeprolearn.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
namespace local_sharedresources\search;

use StdClass;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/sharedresource/metadatalib.php');
require_once($CFG->dirroot.'/local/sharedresources/classes/search_widget.class.php');

/**
 * search_widget defines a widget element for the search engine of metadata.
 */
class select_widget extends search_widget {

    /**
     * Constructor
     * @param int $id
     * @param string $label
     */
    public function __construct($id, $label) {
        parent::__construct($id, $label, 'select');
    }

    /**
     * Fonction used to display the widget.
     * @param string $layout
     * @param mixed $value
     */
    public function print_search_widget($layout, $value = 0) {
        global $CFG, $OUTPUT;

        $template = new StdClass;

        $lowername = strtolower($this->label);
        $template->widgetname = get_string(str_replace(' ', '', $lowername), 'sharedmetadata_'.$this->schema);

        include_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$this->schema.'/plugin.class.php');
        $mtdclass = '\\mod_sharedresource\\plugin_'.$this->schema;
        $mtdstandard = new $mtdclass();
        $template->label = $this->label;
        $template->selectsearchhelpicon = $OUTPUT->help_icon('selectsearch', 'sharedresource', false);
        $options = '';
        foreach ($mtdstandard->metadatatree[$this->id]['values'] as $optvalue) {
            $selected = ($value == $optvalue) ? 'selected="selected"' : '';
            $str = get_string(clean_string_key(strtolower($optvalue)), 'sharedmetadata_'.$this->schema);
            $options .= "<option value=\"$optvalue\" $selected >".$str.'</option>';
        }
        $template->options = $options;

        return $OUTPUT->render_from_template('local_sharedresources/search_select', $template);
    }

    /**
     * Catchs a value in session from CGI input.
     * @param array $searchfields
     */
    public function catch_value(& $searchfields) {
        global $SESSION;

        if (!isset($SESSION->searchbag)) {
            $SESSION->searchbag = new StdClass();
        }

        $paramkey = str_replace(' ', '_', $this->label);
        $searchfields[$this->id] = @$SESSION->searchbag->$paramkey;
        if (isset($_GET[$paramkey]) && $_GET[$paramkey] != 'defaultvalue') {
            $paramval = clean_param($_GET[$paramkey], PARAM_TEXT);
            $searchfields[$this->id] = $paramval;
            $SESSION->searchbag->$paramkey = $paramval;
        }
    }
}
