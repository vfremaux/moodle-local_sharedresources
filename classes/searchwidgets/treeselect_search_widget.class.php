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
 *
 */
namespace local_sharedresources\search;

use \StdClass;
use \html_writer;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/sharedresource/metadatalib.php');
require_once($CFG->dirroot.'/local/sharedresources/classes/search_widget.class.php');
require_once($CFG->dirroot.'/mod/sharedresource/classificationlib.php');

/**
 * search_widget defines a widget element for the search engine of metadata.
 */
class treeselect_widget extends search_widget {

    public function __construct($id, $label) {
        parent::__construct($id, $label, 'treeselect');
    }

    /**
     * Fonction used to display the widget. The parameter $display determines if plugins are displayed on a row or on a column
     */
    public function print_search_widget($layout, $value = 0) {
        global $OUTPUT, $CFG, $SESSION;

        $template = new StdClass;
        $config = get_config('sharedresource');

        $lowername = strtolower($this->label);
        $template->widgetname = get_string(str_replace(' ', '', $lowername), 'sharedmetadata_'.$config->schema);

        include_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$config->schema.'/plugin.class.php');

        $template->helpicon = $OUTPUT->help_icon('classificationsearch', 'sharedresource', false);
        $template->taxonpathstr = get_string('taxonpath', 'sharedmetadata_'.$config->schema);

        // $jshandler = 'javascript:classif(this.options[selectedIndex].value,1,\'\',this.options[selectedIndex].value,this.options[this.selectedIndex].value);';

        $nochoicestr = get_string('none', 'sharedresource');
        $classificationoptions[] = array('' => array('' => $nochoicestr));
        $classificationoptions = array_merge($classificationoptions, metadata_get_classification_options());
        $paramkey = str_replace(' ', '_', $this->label);
        $preselect = @$SESSION->searchbag->$paramkey;

        $attrs = array('class' => 'widget-treeselect-select');
        $template->select = html_writer::select($classificationoptions, $paramkey, $preselect, null, $attrs);
        $template->label = $paramkey;
        $subskey = $paramkey.'_subs';
        $template->subschecked = (@$SESSION->searchbag->$subskey) ? 'checked="checked"' : '';
        $template->searchinsubsstr = get_string('searchinsubs', 'sharedresource');

        return $OUTPUT->render_from_template('local_sharedresources/search_treeselect', $template);
    }

    // Catchs a value in session from CGI input.
    public function catch_value(&$searchfields) {
        global $SESSION;

        if (!isset($SESSION->searchbag)) {
            $SESSION->searchbag = new StdClass();
        }

        $paramkey = str_replace(' ', '_', $this->label);

        $subskey = $paramkey.'_subs';
        $withsubs = optional_param($subskey, false, PARAM_BOOL);
        $input = optional_param($paramkey, '', PARAM_TEXT);

        $searchinput = $input;

        if ($withsubs) {
            // We add the "subs:" operator.
            $searchinput = 'subs:'.$input;
        }
        if ($input != '') {
            $searchfields[$this->id] = $searchinput;
            $SESSION->searchbag->$paramkey = $input;
        } else {
            unset($SESSION->searchbag->$paramkey);
        }
    }
}
