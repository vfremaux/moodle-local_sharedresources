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
 * Post-install code for the sharedresource local plugin.
 *
 * @package     local_sharedresource
 * @category    local
 * @copyright   2013 Valery Fremaux
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * on the install we still need to build and add the librarian role.
 */
function xmldb_local_sharedresources_install() {
    global $DB;

    $result = true;

    // Create the teacherowner role if absent.
    if (!$oldrole = $DB->get_record('role', ['shortname' => 'librarian'])) {
        $rolestr = get_string('librarian', 'local_sharedresources');
        $roledesc = get_string('librarian_desc', 'local_sharedresources');
        $librarianid = create_role($rolestr, 'librarian', str_replace("'", "\\'", $roledesc), null);
        set_role_contextlevels($librarianid, [CONTEXT_SYSTEM, CONTEXT_COURSECAT]);

        // We cannot setup permissions from the access.php files for custom roles.
        $context = context_system::instance();
        role_change_permission($librarianid, $context, 'repository/sharedresources:manage', CAP_ALLOW);
        role_change_permission($librarianid, $context, 'repository/sharedresources:use', CAP_ALLOW);
        role_change_permission($librarianid, $context, 'repository/sharedresources:view', CAP_ALLOW);
        role_change_permission($librarianid, $context, 'repository/sharedresources:accessall', CAP_ALLOW);
        role_change_permission($librarianid, $context, 'repository/sharedresources:create', CAP_ALLOW);
        role_change_permission($librarianid, $context, 'repository/sharedresources:indexermetadata', CAP_ALLOW);
    } else {
        $context = context_system::instance();
        role_change_permission($oldrole->id, $context, 'repository/sharedresources:manage', CAP_ALLOW);
        role_change_permission($oldrole->id, $context, 'repository/sharedresources:use', CAP_ALLOW);
        role_change_permission($oldrole->id, $context, 'repository/sharedresources:view', CAP_ALLOW);
        role_change_permission($oldrole->id, $context, 'repository/sharedresources:accessall', CAP_ALLOW);
        role_change_permission($oldrole->id, $context, 'repository/sharedresources:create', CAP_ALLOW);
        role_change_permission($oldrole->id, $context, 'repository/sharedresources:indexermetadata', CAP_ALLOW);
    }

    return $result;
}
