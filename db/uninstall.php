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
 * Post-uninstall code for the sharedresource local plugin.
 *
 * @package     local_sharedresources
 * @author      2013 Valery Fremaux
 * @copyright   2013 Valery Fremaux
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * on the install we still need to build and add the librarian role.
 */
function xmldb_local_sharedresources_uninstall() {
    global $DB;

    $result = true;

    // Remove the teacherowner role if absent.
    if ($oldrole = $DB->get_record('role', ['shortname' => 'librarian'])) {
        delete_role($oldrole->id);
    }

    return $result;
}
