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
 * @package     local_sharedresource
 * @category    local
 * @author      Valery Fremaux (valery.fremaux@gmail.com)
 */
defined('MOODLE_INTERNAL') || die();

if ($action == 'forcedelete' || $action == 'delete') {
    $resourceid = required_param('id', PARAM_INT);

    $identifier = $DB->get_field('sharedresource_entry', 'identifier', array('id' => $resourceid));
    $DB->delete_records('sharedresource_metadata', array('entryid' => $resourceid));
    $DB->delete_records('sharedresource_entry', array('id' => $resourceid));

    if ($sharedresources = $DB->get_records('sharedresource', array('identifier' => $identifier))) {

        $module = $DB->get_record('modules', array('name' => 'sharedresource'));

        foreach ($sharedresources as $sharedresource) {
            if ($cm = get_coursemodule_from_instance('sharedresource', $sharedresource->id)) {
                course_delete_module($cm->id);
            }
        }
    }
}
