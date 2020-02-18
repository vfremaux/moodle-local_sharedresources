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
 * this is an utility class for administrators that get computed statistics and information about 
 * sharedresources. Lib admin will help caching some data for performance.
 *
 * @package     local_sharedresources
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright   (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */

namespace local_sharedresources;

class library_admin {

    public function get_resources_infos() {
        global $DB;

        $stats = new StdClass;

        $sql = "
            SELECT
                COUNT(*) as resnum,
                SUM(CASE WHEN sh.id IS NULL THAN 1 ELSE 0 END) as unused,
                scoreview,
                scorelike
            FROM
                {sharedresource_entry} she
            LEFT JOIN
                {sharedresource} sh
            ON
                sh.identifier = she.identifier
        ";

        $stats->resnum = $DB->get_record_sql($sql);
    }

    public function get_repo_infos() {
        
    }

    /**
     * Get all the resource access information from the resource
     * record itself or the associated taxonomies bindings by metadada.
     * The resulting info should allow an administrator to have a global understanding
     * of access restrictions that affect a resource for a particular user.
     *
     * this first implementation is NOT intended to be efficiant and should be optimized
     * later, using more caching results and less DB calls.
     */
    public function get_access_info() {
        global $DB;

        $config = get_config('sharedresource');
        $plugin = sharedresource_get_plugin($config->schema);
        $taxumarray = $plugin->getTaxumpath();

        $resources = $DB->get_records('sharedresource_entry', $params, 'title', 'id,identifier,accessctl');
        if ($resources) {
            foreach ($resources as $r) {
                if (!empty($r->accessctl)) {
                    $accessctl = \mod_sharedresource\access_ctl::instance($r->accessctl);
                    $r->accessrulesstr = $accessctl->to_string();
                }

                // Examine taxonomies
                if (!empty($taxumarray)) {
                    
                }
            }
        }
    }
}