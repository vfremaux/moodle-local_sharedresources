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
 * @package    local_sharedresources
 * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 */
namespace local_sharedresources\browser;

defined('MOODLE_INTERNAL') or die();

class navigation {

    protected $config;

    protected $plugin;

    function __construct() {
        $this->config = get_config('sharedresource');

        $this->plugin = sharedresource_get_plugin($this->config->schema);

    }

    function get_category_filters() {
        return array();
    }

    function get_filter_modalities($filter) {
        return array();
    }

    /**
     * Get the available taxonomies.
     * Taxonomies are dynamically detected from distinct instances of the 
     *
     */
    function get_taxonomies() {
        
    }

    /**
     * get a category given the local category id in the taxonomy
     *
     */
    function get_category($catid) {
        global $DB;

        return $DB->get_record('sharedresource_taxonomy', array('id' => $catid));
    }

    /**
     * Counts the total number of entries recusrsively in the subtree.
     * @param int $catid
     */
    function count_entries_rec($catid) {
        global $DB;

        $config = get_config('local_sharedresources');
        $shrconfig = get_config('sharedresource');

        $plugins = sharedresource_get_plugins();
        $plugin = $plugins[$shrconfig->schema];
        $element = $plugin->getTaxonomyValueElement();

        $count = $DB->count_records('sharedresource_metadata', array('element' => $element->node.':0_0_0_0', 'namespace' => $element->source));

        $children = $this->get_children($catid);
        if ($children) {
            foreach ($children as $ch) {
                $count += $this->count_entries_rec($ch);
            }
        }
    }

    /**
     * Get children of a category
     */
    function get_children(&$categoryorid) {
        global $DB;

        $config = get_config('sharedresource');

        if (is_object($categoryorid)) {
            $catid = $category->id;
        } else {
            $catid = $categoryorid;
        }

        $children = $DB->get_records('sharedresource_taxonomy', array('parent' => $catid), 'sortorder');
        return $children;
    }
}