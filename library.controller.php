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
 * General controller of library.
 *
 * @package     local_sharedresources
 * @author Valery Fremaux <valery@gmail.com>
 * @copyright Valery Fremaux (activeprolearn.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 *
 * @todo : turn to controller class.
 */
defined('MOODLE_INTERNAL') || die();

if ($action == 'forcedelete' || $action == 'delete') {

    $resourceid = required_param('id', PARAM_INT);

    $identifier = $DB->get_field('sharedresource_entry', 'identifier', ['id' => $resourceid]);
    $DB->delete_records('sharedresource_metadata', ['entryid' => $resourceid]);
    $DB->delete_records('sharedresource_entry', ['id' => $resourceid]);

    if ($sharedresources = $DB->get_records('sharedresource', ['identifier' => $identifier])) {

        foreach ($sharedresources as $sharedresource) {
            if ($cm = get_coursemodule_from_instance('sharedresource', $sharedresource->id)) {
                course_delete_module($cm->id);
            }
        }
    }
}
