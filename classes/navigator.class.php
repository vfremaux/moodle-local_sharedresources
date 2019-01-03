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
 * A navigator provides data exploration services for a browser. An instance represents
 * one available taxonomy tree.
 * 
 * @package    local_sharedresources
 * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */
namespace local_sharedresources\browser;

require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
if (mod_sharedresource_supports_feature('taxonomy/accessctl')) {
    include_once($CFG->dirroot.'/mod/sharedresource/pro/classes/sharedresource_access_control.class.php');
}

use \StdClass;
use \coding_exception;

defined('MOODLE_INTERNAL') or die();

class navigation {

    // Sharedresouce configuration.
    protected $config;

    // The metadata active plugin.
    protected $plugin;

    /**
     * The active taxonomy (object). Contains all references to the underlying classification storage model.
     */
    protected $taxonomy;

    public function __construct($taxonomyorid) {
        global $DB;

        if (is_object($taxonomyorid)) {
            $taxonomy = $taxonomyorid;
        } else {
            $taxonomy = $DB->get_record('sharedresource_classif', array('id' => $taxonomyorid));
        }

        $this->taxonomy = $taxonomy;
        $this->config = get_config('sharedresource');
        $this->plugin = sharedresource_get_plugin($this->config->schema);
    }

    public function __get($field) {
        if (!isset($this->taxonomy->$field)) {
            throw new \coding_exception("Bad taxonomy attribute $field");
        }
        return $this->taxonomy->$field;
    }

    public static function instance_by_id($id) {
        global $DB;

        if (!is_numeric($id)) {
            // Some old recorded records may have table name as source id.
            $taxonomyrec = $DB->get_record('sharedresource_classif', array('shortname' => $id));
        } else {
            $taxonomyrec = $DB->get_record('sharedresource_classif', array('id' => $id));
        }

        if (empty($taxonomyrec)) {
            throw new coding_exception('Invalid taxonomy id or name given : '.$id);
        }

        return new navigation($taxonomyrec);
    }

    public function get_category_filters() {
        return array();
    }

    public function get_filter_modalities($filter) {
        return array();
    }

    /**
     * Get the available taxonomies.
     * Taxonomies are dynamically detected from distinct instances of the sharedresource_classif descriptor.
     * @param boolean $enabled
     * @return array of classif records.
     */
    static function get_taxonomies($enabled = true) {
        global $DB;

        return $DB->get_records('sharedresource_classif', array('enabled' => $enabled));
    }

    /**
     * Get the available taxonomies.
     * Taxonomies are dynamically detected from distinct instances of the sharedresource_classif descriptor.
     * @param boolean $enabled
     * @return array of classif records.
     */
    public static function get_taxonomies_menu($enabled = true, $internal = false) {
        global $DB;

        $params = array();
        if ($enabled) {
            $params = array('enabled' => $enabled);
        }

        if ($internal) {
            $params['tablename'] = 'sharedresource_taxonomy';
        }

        return $DB->get_records_menu('sharedresource_classif', $params, 'name', 'id,name');
    }

    /**
     * get a category given the local category id in the taxonomy
     * @param $catid numeric id of the category
     * @param $catpath slash separated (and terminated) id list of the cat path from root.
     * @param $filters (for future use)
     */
    public function get_category($catid, $catpath = null, $filters = array()) {
        global $DB;

        if (empty($this->taxonomy)) {
            return null;
        }

        $fields = "{$this->taxonomy->sqlid} as id, {$this->taxonomy->sqllabel} as name, {$this->taxonomy->sqlparent} as parent ";
        $category = $DB->get_record($this->taxonomy->tablename, array($this->taxonomy->sqlid => $catid), $fields);

        $category->hassubs = $DB->count_records($this->taxonomy->tablename, array($this->taxonomy->sqlparent => $category->id));

        if (!is_null($catpath)) {
            $category->entries = $this->get_entries($catpath);
        }

        return $category;
    }

    /**
     *
     */
    public function count_taxons() {
        global $DB;

        $whereclauses = array();

        if (!empty($this->taxonomy->sqlrestriction)) {
            $whereclauses[] = $this->taxonomy->sqlrestriction;
        }

        $params = array();
        if (!empty($this->taxonomy->taxonselection)) {
            list($insql, $inparams) = $DB->get_in_or_equal(explode(',', $this->taxonomy->taxonselection));
            $whereclauses[] = " id $insql";
            $params = $inparams;
        }

        if ($this->taxonomy->tablename == "sharedresource_taxonomy") {
            $whereclauses[] = ' classificationid = ? ';
            $params[] = $this->taxonomy->id;
        }

        $select = implode(' AND ', $whereclauses);

        return $DB->count_records_select($this->taxonomy->tablename, $select, $params);
     }

    /**
     * Get a hierarchical object with all categories and subcategories (taxon tree) depending on 
     * restriction rules.
     * @param string $outputlayout if flat, returns a flat associative array
     * @param bool $short if short, names of parents are shortened to a single '-';
     */
    public function get_full_tree($outputlayout = 'flat', $short = true) {
        global $DB;

        $whereclauses = array("{$this->taxonomy->sqlparent} = 0");

        if (!empty($this->taxonomy->sqlrestriction)) {
            $whereclauses[] = $this->taxonomy->sqlrestriction;
        }

        $params = array();
        if (!empty($this->taxonomy->taxonselection)) {
            list($insql, $inparams) = $DB->get_in_or_equal(explode(',', $this->taxonomy->taxonselection));
            $whereclauses[] = " id $insql";
            $params = $inparams;
        }

        if ($this->taxonomy->tablename == 'sharedresource_taxonomy') {
            $whereclauses[] = ' classificationid = ? ';
            $params[] = $this->taxonomy->id;
        }

        $select = implode(' AND ', $whereclauses);

        $roots = $DB->get_records_select($this->taxonomy->tablename, $select, $params);

        $flatoptions = array();

        if (!empty($roots)) {
            foreach ($roots as $r) {

                $r->idpath = $r->id.'/';
                if ($short) {
                    $r->namepath = '';
                } else {
                    $r->namepath = $r->value;
                }
                if ($outputlayout == 'flat') {
                    $childs = $this->get_full_tree_rec($r->id, $r->idpath, $r->namepath, 'flat');
                    $flatoptions[$r->id.'/'] = $r->value;
                    if (!empty($childs)) {
                        foreach ($childs as $cid => $cvalue) {
                            $flatoptions[$cid] = $cvalue;
                        }
                    }
                } else {
                    $r->childs = $this->get_full_tree_rec($r->id, $r->idpath, $r->namepath, 'tree', $short);
                }
            }
        }

        if ($outputlayout == 'flat') {
            return $flatoptions;
        }
        return $roots;
    }

    /**
     * Get a hierarchical object with all categories and subcategories depending on 
     * restriction rules.
     * @param int parentid
     * @param string $idpath the slashed full path of taxon ids with all parents
     * @param string $namepath namepath will get the full label slashed path construct, or a shortened indentation indication for selects
     * @param string $outputlayout if flat, will return a flattened array of all taxonomy tokens.
     * @param boolean $short if true, the namepath and the taxon label
     */
    public function get_full_tree_rec($parentid, $idpath, $namepath, $outputlayout, $short = true) {
        global $DB;

        $whereclauses = array(" {$this->taxonomy->sqlparent} = ? ");

        $params = array($parentid);

        if (!empty($this->taxonomy->taxonselection)) {
            list($insql, $inparams) = $DB->get_in_or_equal(explode(',', $this->taxonomy->taxonselection));
            $whereclauses[] = " id $insql";
            if (!empty($inparams)) {
                foreach ($inparams as $pid => $pvalue) {
                    $params[] = $pvalue;
                }
            }
        }

        $select = implode(' AND ', $whereclauses);

        $flatoptions = array();

        $childs = $DB->get_records_select($this->taxonomy->tablename, $select, $params);
        if (!empty($childs)) {
            foreach ($childs as $c) {
                $c->idpath = $idpath.$c->id.'/';
                if ($short) {
                    $c->namepath = $namepath . ' - ';
                } else {
                    $c->namepath = $namepath.' / '.$c->value;
                }
                if ($outputlayout == 'flat') {
                    $subchilds = $this->get_full_tree_rec($c->id, $c->idpath, $c->namepath, 'flat', $short);
                    $flatoptions[$c->idpath] = $c->namepath . $c->value;

                    if (!empty($subchilds)) {
                        foreach ($subchilds as $subid => $sub) {
                            $flatoptions[$subid] = $sub;
                        }
                    }
                } else {
                    $c->childs = $this->get_full_tree_rec($c->id, $c->idpath, $namepath.' / '.$c->value);
                }
            }
        }

        if ($outputlayout == 'flat') {
            return $flatoptions;
        }
        return $childs;
    }

    /**
     * Counts the total number of entries recursively in the subtree.
     * @param int $catid
     * // TODO : add mutiple taxonomy source arity. At the moment just handling first instance.
     */
    public function count_entries_rec($catpath) {
        global $DB;

        $catids = explode('/', $catpath);
        $fooid = array_pop($catids); // First is an end of path empty string.
        $catid = array_pop($catids);

        $config = get_config('local_sharedresources');
        $shrconfig = get_config('sharedresource');

        $plugins = \sharedresource_get_plugins();
        $plugin = $plugins[$shrconfig->schema];
        $elementnode = $plugin->getTaxumpath()['id'];

        $params = array('element' => $elementnode.':%', 'namespace' => $shrconfig->schema, 'value' => $catpath);
        $select = ' element LIKE ? AND namespace = ? AND value = ? AND entryid != 0 ';
        $count = $DB->count_records_select('sharedresource_metadata', $select, $params);

        $children = $this->get_children($catid);

        if ($children) {
            foreach ($children as $ch) {
                $chpath = $catpath.$ch->id.'/';
                $count += $this->count_entries_rec($chpath);
            }
        }

        return 0 + $count;
    }

    /**
     * Get entries that match this taxonomy level. Any taxonomy entry may match.
     * @param int $catid
     * @return An array of resource records.
     */
    public function get_entries($catid) {
        global $DB;

        $config = get_config('local_sharedresources');
        $shrconfig = get_config('sharedresource');

        $plugins = sharedresource_get_plugins();
        $plugin = $plugins[$shrconfig->schema];
        $elementnode = $plugin->getTaxumpath()['id'];

        $sql = "
            SELECT
                shr.*
            FROM
                {sharedresource_entry} shr,
                {sharedresource_metadata} shm
            WHERE
                shm.element LIKE ? AND
                shm.namespace = ? AND
                shr.id = shm.entryid AND
                shm.value = ?
        ";

        $resources = $DB->get_records_sql($sql, array($elementnode.':%', $shrconfig->schema, $catid));

        return $resources;
    }

    /**
     * Get children of a category
     * @param mixed $categoryorid the parent category or category id.
     * @return array of categories.
     */
    public function get_children(&$categoryorid) {
        global $DB;
        static $childrensets = array();

        $config = get_config('sharedresource');

        if (is_object($categoryorid)) {
            $catid = $categoryorid->id;
        } else {
            $catid = $categoryorid;
        }

        if (array_key_exists($catid, $childrensets)) {
            return $childrensets[$catid];
        }

        // Lazy loading;

        $fields = "{$this->taxonomy->sqlid} as id, ";
        $fields .= "{$this->taxonomy->sqllabel} as name, ";
        $fields .= "{$this->taxonomy->sqlparent} as parent, ";
        $fields .= "{$this->taxonomy->sqlsortorder} as sortorder";

        $params = array($this->taxonomy->sqlparent => $catid);

        if ($this->taxonomy->tablename == 'sharedresource_taxonomy') {
            $params['classificationid'] = $this->taxonomy->id;
        }

        if ($children = $DB->get_records($this->taxonomy->tablename, $params, $this->taxonomy->sqlsortorder, $fields)) {
            foreach ($children as &$child) {
                $params = array($this->taxonomy->sqlparent => $child->id);
                $child->hassubs = $DB->count_records($this->taxonomy->tablename, $params);
                $child->value = $child->name; // For caller compatibiity.
            }
        }

        $childrensets[$catid] = $children;

        return $childrensets[$catid];
    }

    /**
     * Prints a displayable taxonomy path.
     *
     */
    public function get_printable_taxon_path($taxonvalue) {
        global $DB;

        if ($taxon = $DB->get_record($this->taxonomy->tablename, array('id' => $taxonvalue))) {
            $labelfield = $this->taxonomy->sqllabel;
            $parentfield = $this->taxonomy->sqlparent;
            $taxonpathelms[] = $taxon->$labelfield;
            while ($taxon->$parentfield) {
                $taxon = $DB->get_record($this->taxonomy->tablename, array('id' => $taxon->$parentfield));
                $taxonpathelms[] = $taxon->$labelfield;
            }

            $taxonpathelms = array_reverse($taxonpathelms);
            return implode(' / ', $taxonpathelms);
        } else {
            throw new coding_exception('Taxon not found for value '.$taxonvalue);
        }
    }

    /**
     * Checks if this taxonomy is useable by the current user. this based on taxonomy acl definitions.
     * Only Pro versions.
     *
     */
    public function can_use() {

        if (!mod_sharedresource_supports_feature('taxonomy/accessctl')) {
            // Always access granted when not supporting the feature.
            return true;
        }

        if (empty($this->taxonomy->accessctl)) {
            // User can use if this taxonomy has no access control defined.
            return true;
        }

        $accessctl = \mod_sharedresource\access_ctl::instance($this->taxonomy->accessctl);
        return $accessctl->can_use();
    }

    /**
     * Get the token info from a token id
     * return Stdclass with id, name, taxonomyid, parent id
     */
    public function get_token_info($tokenid) {
        global $DB;

        $table = $this->taxonomy->tablename;

        $tokenrecord = $DB->get_record($table, array('id' => $tokenid));
        if ($tokenrecord) {
            $tokenrecord->taxonomyid = $this->taxonomy->id;
        }

        return $tokenrecord;
    }

    /**
     * Recursively deletes token in the inderlying taxonomy, and unbinds existing metadata
     * from the deleted tokens. Only works in sharedresource_taxonomy local table.
     * @param int $tokenid the token id
     */
    public function delete_token($tokenid) {
        global $DB;

        $token = $DB->get_record('sharedresource_taxonomy', array('id' => $tokenid));
        if (!$token) {
            return;
        }

        $children = $DB->get_records($this->taxonomy->tablename, array($this->taxonomy->sqlparent => $tokenid));
        if (!empty($children)) {
            foreach ($children as $ch) {
                $this->delete_token($ch->id);
            }
        }

        // Delete all resource metadata binding related to this token id.
        $this->plugin->unbind_taxon($token->classificationid, $token->id);

        // Finally delete the token.
        $DB->delete_records($this->taxonomy->tablename, array('id' => $tokenid));
    }
}