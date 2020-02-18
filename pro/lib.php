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
 * a pro complement to the general lib of local_sharedresources
 *
 * @package     local_sharedresources
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright   (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */

require_once($CFG->dirroot.'/local/sharedresources/pro/classes/library_admin.class.php');

/** 
 * singleton class accessor.
 */
function get_library_admin() {
    static $libraryadmin;

    if (is_null($libraryadmin)) {
        $libraryadmin = new \local_sharedresources\library_admin();
    }

    return $libraryadmin;
}

function sharedresources_get_provider_tabs($repo, $course) {

    $repos['local'] = get_string('local', 'sharedresource');

    if ($providers = sharedresources_get_providers()) {

        foreach ($providers as $provider) {
            $repos["$provider->id"] = $provider->name;
        }
    }

    $repoids = array_keys($repos);
    if (!in_array($repo, $repoids)) $repo = $repoids[0];

    foreach ($repoids as $repoid) {
        if ($course) {
            $repourl = new moodle_url('/local/sharedresources/explore.php', array('course' => $course->id, 'repo' => $repoid));
            $rows[0][] = new tabobject($repoid, $repourl, $repos[$repoid]);
        } else {
            $repourl = new moodle_url('/local/sharedresources/explore.php', array('repo' => $repoid));
            $rows[0][] = new tabobject($repoid, $repourl, $repos[$repoid]);
        }
    }

    return print_tabs($rows, $repo, $repos[$repo], null, true);
}