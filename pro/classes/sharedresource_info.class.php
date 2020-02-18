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
 * sharedresources.
 *
 * @package     local_sharedresources
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright   (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */

namespace local_sharedresources;

require_once($CFG->dirroot.'/mod/sharedresources\classes\sharedresource_entry.class.php');

class sharedresource_info {

    public $title;

    public $repo;

    public $hasacl;

    public $taxonomies;

    public $remote;

    public $enabled;

    public $haslocalfile;

    public function __construct($shrentry) {
        
    }

    public static function instance_by_identifier($identifier) {
        global $DB;

        $shrentry = \mod_sharedresource\entry::read($identifier);
        if (!$shrentry) {x
            throw new \moodle_exception('sharedresource entry not found');
        }
        return new sharedresource_info($shrentry);
    }
}