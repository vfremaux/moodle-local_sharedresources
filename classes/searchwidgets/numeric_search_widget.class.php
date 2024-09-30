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
 * Search widget for numeric range
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
class numeric_widget extends search_widget {

    /**
     * constructor
     * @param int $id
     * @param string $label
     */
    public function __construct($id, $label) {
        parent::__construct($id, $label, 'numeric');
    }

    /**
     * Fonction used to display the widget.
     * @param string $layout
     * @param mixed $value
     */
    public function print_search_widget($layout, $value = 0) {
        echo $OUTPUT;

        $template = new StdClass;

        $lowername = strtolower($this->label);
        $template->widgetname = get_string(str_replace(' ', '', $lowername), 'sharedmetadata_'.$this->schema);

        $template->numericsearchhelpicon = $OUTPUT->help_icon('numericsearch', 'sharedresource', false);
        $template->label = $this->label;

        return $OUTPUT->render_from_template('local_sharedresources/search_numeric', $template);
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
        if (!empty($_GET[$paramkey])) {
            $paramval = clean_param($_GET[$paramkey], PARAM_NUMBER);
            $paramsymbolval = clean_param($_GET[$paramkey.'_symbol'], PARAM_TEXT);
            $searchstring = $paramsymbolval.':'.$paramval;
            $searchfields[$this->id] = $searchstring;
            $SESSION->searchbag->$paramkey = $searchstring;
        }
    }
}
