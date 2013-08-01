<?php

// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
* External Web Service for Shared Resources
*
* @package local-sharedresources
* @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot. "/local/sharedresources/lib.php");

class local_sharedresources_external extends external_api {


    public static function search_parameters() {
        $context = get_context_instance(CONTEXT_SYSTEM);

        $visiblewidgets = array();
        resources_setup_widgets($visiblewidgets, $context);
        $searchfields = array();
        foreach ($visiblewidgets as $w) {
            $searchfields[$w->label] = new external_value(PARAM_TEXT, $w->label, VALUE_DEFAULT, '');
            $searchfields[$w->label.'_option'] = new external_value(PARAM_TEXT, $w->label, VALUE_DEFAULT, 'includes');
        }
        return new external_function_parameters(array('searchfields' => new external_single_structure($searchfields)));
    }

    public static function search($searchfields) {
        $context = get_context_instance(CONTEXT_SYSTEM);

        $params = self::validate_parameters(self::search_parameters(), array('searchfields'=>$searchfields));

        if (!has_capability('mod/sharedresource:browsecatalog', $context)) {
            throw new moodle_exception('cannotbrowsecatalog');
        }

        $visiblewidgets = array();
        resources_setup_widgets($visiblewidgets, $context);
        $searchfields = array();
        if (resources_process_search_widgets($visiblewidgets, $searchfields)){
            // if something has changed in filtering conditions, we might not have same resultset. Keep offset to 0.
            $offset = 0;
        }

        $fullresults = array();

        $metadatafilters = array();
        if (!empty($searchfields)){
            foreach($searchfields as $element => $search){
                if (!empty($search)){
                    $metadatafilters[$element] = $search;
                }
            }
        }
        $result = array();
        if ($resources = get_local_resources('local', $fullresults, $metadatafilters)) {
            foreach ($resources as $r) {
                $metadata = array();
                foreach ($visiblewidgets as $w) {
                    $m = strtolower($w->label);
                    $metadata[$w->label] = $r->{$m};
                }
                $result[] = $metadata;
            }
        }
        return $result;
    }

    public static function search_returns() {
        $context = get_context_instance(CONTEXT_SYSTEM);

        $visiblewidgets = array();
        resources_setup_widgets($visiblewidgets, $context);
        $searchfields = array();
        foreach ($visiblewidgets as $w) {
            $searchfields[$w->label] = new external_value(PARAM_CLEANHTML, $w->label);
        }

        return new external_multiple_structure(new external_single_structure($searchfields));
    }
}
