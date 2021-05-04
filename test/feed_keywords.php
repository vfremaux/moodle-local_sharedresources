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
 * This scripts converts all existing keywords in sharedresource_entries and feeds the metadata
 * records with suitable keyword records for known metadata schemes.
 */
require_once("../../config.php");
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');

// Protect this script from non-admins.
require_login();
require_capability('moodle/site:config', context_system::instance());

echo "<pre>";
mtrace("Setting keywords for all sharedresources...");
if ($resources = $DB->get_records('sharedresource_entry', array('' => ''))) {
    foreach ($resources as $entry) {
        if (!empty($entry->keywords)) {
            $plugins = sharedresource_get_plugins($entry->id); // Hidden plugins are already discarded here.
            foreach ($plugins as $plugin) {
                $plugin->setEntry($entry->id);
                if (method_exists($plugin, 'setKeywords')) {
                    mtrace("\tSetting keywords for entry {$entry->identifier} with {$entry->keywords}");
                    $plugin->setKeywords($entry->keywords);
                }
            }
        }
    }
}
mtrace("Done.");
echo "</pre>";
