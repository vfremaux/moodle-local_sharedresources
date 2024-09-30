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
 * Provides libraries for resource generic access.
 *
 * @package     local_sharedresources
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

/*
 * phpcs:disable moodle.Commenting.ValidTags.Invalid
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mnet/xmlrpc/client.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/rpclib.php');
require_once($CFG->dirroot.'/mod/sharedresource/metadatalib.php');

if (local_sharedresources_supports_feature('admin/pro')) {
    // Get additional general functions for "pro" version.
    require_once($CFG->dirroot.'/local/sharedresources/pro/lib.php');
}

/**
 * Implements the generic community/pro packaging switch.
 * Tells wether a feature is supported or not. Gives back the
 * implementation path where to fetch resources.
 * @param string $feature a feature key to be tested.
 */
function local_sharedresources_supports_feature($feature = null, $getsupported = false) {
    global $CFG;
    static $supports;

    if (!during_initial_install()) {
        $config = get_config('local_sharedresources');
    }

    if (!isset($supports)) {
        $supports = [
            'pro' => [
                'repo' => ['remote'],
                'import' => ['mass'],
                'admin' => ['pro'],
                'emulate' => 'community',
            ],
            'community' => [
            ],
        ];
        $prefer = [];
    }

    if ($getsupported) {
        return $supports;
    }

    // Check existance of the 'pro' dir in plugin.
    if (is_dir(__DIR__.'/pro')) {
        if ($feature == 'emulate/community') {
            return 'pro';
        }
        if (empty($config->emulatecommunity)) {
            $versionkey = 'pro';
        } else {
            $versionkey = 'community';
        }
    } else {
        $versionkey = 'community';
    }

    if (empty($feature)) {
        // Just return version.
        return $versionkey;
    }

    list($feat, $subfeat) = explode('/', $feature);

    if (!array_key_exists($feat, $supports[$versionkey])) {
        return false;
    }

    if (!in_array($subfeat, $supports[$versionkey][$feat])) {
        return false;
    }

    // Special condition for pdf dependencies.
    if (($feature == 'format/pdf') && !is_dir($CFG->dirroot.'/local/vflibs')) {
        return false;
    }

    if (array_key_exists($feat, $supports['community'])) {
        if (in_array($subfeat, $supports['community'][$feat])) {
            // If community exists, default path points community code.
            if (isset($prefer[$feat][$subfeat])) {
                // Configuration tells which location to prefer if explicit.
                $versionkey = $prefer[$feat][$subfeat];
            } else {
                $versionkey = 'community';
            }
        }
    }

    return $versionkey;
}

/**
 * Standard callback.
 * @param global_navigation $nav
 */
function local_sharedresources_extend_navigation(global_navigation $nav) {
    global $USER, $COURSE, $PAGE;

    if ($COURSE->id == SITEID) {
        $context = context_system::instance();
    } else {
        $context = context_course::instance($COURSE->id);
    }

    if (has_capability('repository/sharedresources:view', $context)) {
        $label = get_string('library', 'local_sharedresources');
        $target = new moodle_url('/local/sharedresources/index.php');
        $librarynode = $PAGE->navigation->create($label, $target, navigation_node::TYPE_CONTAINER);
        $nav->add_node($librarynode);
    }
}

/**
 * a call back function for autoloading classes when unserializing the widgets
 * @param string $classname
 */
function resources_load_searchwidgets($classname) {
    global $CFG;

    $classname = str_replace('local_sharedresources\\search\\', '', $classname);

    if (file_exists($CFG->dirroot."/local/sharedresources/searchwidgets/{$classname}.class.php")) {
        include_once($CFG->dirroot."/local/sharedresources/searchwidgets/{$classname}.class.php");
    }
}

// Prepare autoloader of missing search widgets.
// ini_set('unserialize_callback_func', 'resources_load_searchwidgets');
spl_autoload_register('resources_load_searchwidgets');

if (!defined('RPC_SUCCESS')) {
    define('RPC_TEST', 100);
    define('RPC_SUCCESS', 200);
    define('RPC_FAILURE', 500);
    define('RPC_FAILURE_USER', 501);
    define('RPC_FAILURE_CONFIG', 502);
    define('RPC_FAILURE_DATA', 503);
    define('RPC_FAILURE_CAPABILITY', 510);
}

/**
 * Array compare helper.
 * @param string $a
 * @param string $b
 */
function cmp($a, $b) {
    $a = preg_replace('@^(a|an|the) @', '', $a);
    $b = preg_replace('@^(a|an|the) @', '', $b);
    return strcasecmp($a, $b);
}

/**
 * get a stub of local resources
 * @param string $repo
 * @param arrayref &$fullresults
 * @param array $searchfields and array of fields to search in keyed by metadata node identifier. Empty values will be ignored.
 * @param int &$offset paged offset. May be changed (reset) while seeking for resources in some cases.
 * @param int $page the paging size.
 */
function sharedresources_get_local_resources($repo, &$fullresults, $searchfields = [], &$offset = 0, $page = 20) {
    global $DB;

    $config = get_config('sharedresource');
    $systemcontext = context_system::instance();

    $plugins = sharedresource_get_plugins();
    $plugin = $plugins[$config->schema];

    // Check if we have some filters.
    $sqlclauses = [];
    $hasfilter = false;
    $tabresources = []; // Array with keys = id of a resource and value = number of criteria matched in research.

    foreach ($searchfields as $filterkey => $filtervalue) {
        if (!empty($filtervalue)) {
            $entrysets = sharedresource_get_by_metadata($filterkey, $plugin->pluginname, 'entries', $filtervalue);
            foreach ($entrysets as $key => $id) {
                if (!array_key_exists($id, $tabresources)) {
                    $tabresources[$id] = 1;
                } else {
                    $tabresources[$id]++;
                }
            }
            $hasfilter = true;
        }
    }

    // Get sharedresources from that preselection.
    $clauses = [];
    if ($hasfilter) {
        $entrylist = implode("','", array_keys($tabresources));
        $clauses[] = " se.id IN('{$entrylist}') ";
    }

    $clauses[] = ($repo != 'all') ? " provider = '$repo' " : '';

    if (!empty($clauses)) {
        $clause = 'WHERE '.implode(' AND ', $clauses);
    }

    $sql = "
        SELECT
            se.*
        FROM
            {sharedresource_entry} se
        $clause
        ORDER BY
           score DESC, title
    ";

    $sqlcount = "
        SELECT
            COUNT(*)
        FROM
            {sharedresource_entry} se
        $clause
    ";

    $fullresults['maxobjects'] = $DB->count_records_sql($sqlcount);
    $fullresults['order'] = [];
    if ($offset >= $fullresults['maxobjects']) {
        // Security when changing filter configuration.
        $offset = 0;
    }
    $fullresults['entries'] = $DB->get_records_sql($sql, [], $offset, $page);

    if (!empty($fullresults['entries'])) {
        foreach ($fullresults['entries'] as $id => $r) {

            $entryclass = \mod_sharedresource\entry_factory::get_entry_class();
            $rentry = new $entryclass($r);

            // Discard resources that have next version (not last version of).
            if ($rentry->get_next() != $rentry->id) {
                // We have a next version, so do not output in results.
                $fullresults['maxobjects']--;
                unset($fullresults['entries'][$id]);
                continue;
            }

            if (sharedresource_supports_feature('entry/accessctl')) {
                if (function_exists('debug_trace')) {
                    debug_trace('local sharedresources: applying access control to result '.$id);
                }
                if (!$rentry->has_access()) {
                    if (!has_capability('repository/sharedresources:manage', $systemcontext)) {
                        unset($fullresults['entries'][$id]);
                        continue;
                    } else {
                        // Mark it as hidden for administrators.
                        $fullresults['entries'][$id]->hidden = true;
                    }
                }
            }

            $select = ['entryid' => $id, 'namespace' => $config->schema];
            if ($metadata = $DB->get_records('sharedresource_metadata', $select, 'element', 'id, element, namespace, value')) {
                $fullresults['entries'][$id]->metadata = $metadata;
            }
        }
    }

    return $fullresults['entries'];
}

/**
 * makes a call to remote resource exposure service
 * for getting a resource list. Multimodal function that will
 * admit per category browsing or linear "per page" browsing.
 *
 * @uses $CFG
 * @param string $repo the repo identifier
 */
function sharedresources_get_remote_repo_resources($repo, &$fullresults, $searchfields = [], $offset = 0, $page = 20) {
    global $CFG, $USER, $DB;

    if ($repo == 'local') {
        throw new moodle_exception(get_string('errorrepoprogramming'));
    }

    $remotehost = $DB->get_record('mnet_host', ['id' => $repo]);

    // Get the originating (ID provider) host info.
    if (!$remotepeer = new mnet_peer()) {
        throw new moodle_exception(get_string('errormnetpeer', 'local_sharedresources'));
    }
    $remotepeer->set_wwwroot($remotehost->wwwroot);

    // Set up the RPC request.
    $mnetrequest = new mnet_xmlrpc_client();
    $mnetrequest->set_method('mod/sharedresource/rpclib.php/sharedresource_rpc_get_list');

    // Set remoteuser and remoteuserhost parameters.
    if (!empty($USER->username)) {
        $mnetrequest->add_param($USER->username, 'string');
        $remoteuserhost = $DB->get_record('mnet_host', ['id' => $USER->mnethostid]);
        $mnetrequest->add_param($remoteuserhost->wwwroot, 'string');
    } else {
        $mnetrequest->add_param('anonymous', 'string');
        $mnetrequest->add_param($CFG->wwwroot, 'string');
    }
    $mnetrequest->add_param($CFG->wwwroot, 'string'); // Calling host.

    // Set filters and offset ad page parameters.
    $mnetrequest->add_param($searchfields, 'struct');
    $mnetrequest->add_param($offset, 'int');
    $mnetrequest->add_param($page, 'int');

    // Do RPC call and store response.
    if ($mnetrequest->send($remotepeer) === true) {
        $res = json_decode($mnetrequest->response);
        if ($res->status == RPC_SUCCESS) {
            $fullresults = (array)$res->resources;
        } else {
            throw new moodle_exception($res->error);
        }
    } else {
        $fullresults['entries'] = [];
        $fullresults['maxobjects'] = 0;
        foreach ($mnetrequest->error as $errormessage) {
            list($code, $message) = array_map('trim', explode(':', $errormessage, 2));
            $message .= "ERROR $code:<br/>$errormessage<br/>";
        }
        throw new moodle_exception("RPC mod/sharedresource/get_list:<br/>$message");
    }
    unset($mnetrequest);

    return @$fullresults['entries'];
}

/**
 * Resources providers are mnet_hosts for which we have a subscription to its provider
 * service
 */
function sharedresources_get_providers() {
    global $CFG, $DB;

    $sql = "
        SELECT
            mh.*
        FROM
            {$CFG->prefix}mnet_host mh,
            {$CFG->prefix}mnet_host2service h2s,
            {$CFG->prefix}mnet_service ms
        WHERE
            mh.id = h2s.hostid AND
            h2s.serviceid = ms.id AND
            ms.name = 'sharedresourceservice' AND
            h2s.subscribe = 1 AND
            mh.deleted = 0
    ";

    $providers = $DB->get_records_sql($sql);

    return $providers;
}

/**
 * Resources consumers are mnet_hosts for which we have a subscription to its consumer service API
 * service
 */
function sharedresources_get_consumers() {
    global $CFG, $DB;

    $sql = "
        SELECT
            mh.*
        FROM
            {$CFG->prefix}mnet_host mh,
            {$CFG->prefix}mnet_host2service h2s,
            {$CFG->prefix}mnet_service ms
        WHERE
            mh.id = h2s.hostid AND
            h2s.serviceid = ms.id AND
            ms.name = 'sharedresourceservice' AND
            h2s.subscribe = 1 AND
            mh.deleted = 0
    ";

    $consumers = $DB->get_records_sql($sql);

    return $consumers;
}

/**
 * Resources consumers are mnet_hosts for which we have a subscription to its consumer service API
 * service
 */
function sharedresources_is_consumer($hostroot) {
    global $DB;

    $sql = "
        SELECT
            mh.*
        FROM
            {mnet_host} mh,
            {mnet_host2service} h2s,
            {mnet_service} ms
        WHERE
            mh.id = h2s.hostid AND
            h2s.serviceid = ms.id AND
            ms.name = 'sharedresourceservice' AND
            h2s.publish = 1 AND
            mh.deleted = 0 AND
            mh.wwwroot = ?
    ";

    $consumers = $DB->get_records_sql($sql, [$hostroot]);

    return $consumers;
}

/**
 * fetch remotely or locally amount of usages about a resource.
 * @uses $USER
 * @param object $entry an sharedresource entry
 * @param object $response an array for aggregating error messages
 * @param array $consumers an array of available resource consumers. If not provided, will check localy.
 * @param object $user an eventual user on behalf to whom asking for usage check.
 * @return a count for how many times the resource was used
 */
function sharedresource_get_usages($entry, &$response, $consumers = null, $user = null) {
    global $USER, $DB;

    if (is_null($user)) {
        $user = $USER;
    }

    if (is_null($consumers)) {
        $uses = $DB->count_records('sharedresource', ['identifier' => $entry->identifier]);
    } else {
        $uses = 0;
        if ($consumers) {
            foreach ($consumers as $consumer) {

                // Get the originating (ID provider) host info.
                if (!$remotepeer = new mnet_peer()) {
                    $response['error'][] = "MNET client initialisation error";
                }
                $remotepeer->set_wwwroot($consumer->wwwroot);

                // Set up the RPC request.
                $mnetrequest = new mnet_xmlrpc_client();
                $mnetrequest->set_method('mod/sharedresource/rpclib.php/sharedresource_rpc_check');

                // Set remoteuser and remoteuserhost parameters.
                $mnetrequest->add_param($user->username);

                $remoteuserhost = $DB->get_record('mnet_host', ['id' => $user->mnethostid]);
                $mnetrequest->add_param($remoteuserhost->wwwroot);

                // Set category and resourceID parameter.
                $mnetrequest->add_param($entry->identifier);

                // Do RPC call and store response.
                if ($mnetrequest->send($remotepeer) === true) {
                    $uses += (int) json_decode($mnetrequest->response);
                } else {
                    foreach ($mnetrequest->error as $errormessage) {
                        list($code, $message) = array_map('trim', explode(':', $errormessage, 2));
                        $message .= " Callback ERROR $code:<br/>$errormessage<br/>";
                    }
                    $response['error'][] = "RPC mod/sharedresource/check:<br/>$message";
                }
                unset($mnetrequest);
            }
        }
    }
    return $uses;
}

/**
 * submits a resource to a remote provider
 * @param string $repo
 * àparam objectref &$resourceentry
 */
function sharedresource_submit($repo, &$resourceentry) {
    global $CFG, $DB;

    $remotehost = $DB->get_record('mnet_host', ['id' => $repo]);

    // Get the originating (ID provider) host info.
    if (!$remotepeer = new mnet_peer()) {
        error ("MNET client initialisation error");
    }
    $remotepeer->set_wwwroot($remotehost->wwwroot);

    // Set up the RPC request.
    $mnetrequest = new mnet_xmlrpc_client();
    $mnetrequest->set_method('mod/sharedresource/rpclib.php/sharedresource_rpc_submit');

    // Set $remoteuser and $remoteuserhost parameters.
    if (!empty($USER->username)) {
        $mnetrequest->add_param($USER->username);
        $remoteuserhost = $DB->get_record('mnet_host', ['id', $USER->mnethostid]);
        $mnetrequest->add_param($remoteuserhost->wwwroot);
    } else {
        $mnetrequest->add_param('anonymous');
        $mnetrequest->add_param($CFG->wwwroot);
    }

    // Set $category and $offset ad $page parameters.
    $mnetrequest->add_param($resourceentry, 'struct');

    $metadata = $DB->get_records('sharedresource_metadata', ['entryid' => $resourceentry->id]);

    $mnetrequest->add_param($metadata, 'array');

    $result = false;

    // Do RPC call and store response.
    if ($mnetrequest->send($remotepeer) === true) {
        $result = json_decode($mnetrequest->response);

        if (!$result) {
            return false;
        }

        if ($result->status == RPC_SUCCESS) {

            // We need converting our local instance as a proxy.
            if (!empty($resourceentry->file)) {

                $file = $resourceentry->file;

                // Convert local.
                $resourceentry->url = $remotehost->wwwroot.'/resources/view.php?id='.$resourceentry->identifier;
                $resourceentry->file = '';
                $resourceentry->provider = sharedresources_repo($remotehost->wwwroot);
                $DB->update_record('sharedresource', $resourceentry);

                // Destroy local file.
                $filename = $CFG->dataroot.SHAREDRESOURCE_RESOURCEPATH.$resourceentry->file;
                unlink($filename);
            }
        } else {
            throw new moodle_exception(get_string('rpcsharedresourcesubmiterror'));
        }
    } else {
        foreach ($mnetrequest->error as $errormessage) {
            list($code, $message) = array_map('trim', explode(':', $errormessage, 2));
            $message .= "ERROR $code:<br/>$errormessage<br/>";
        }
        throw new moodle_excpetion(get_string('rpcsharedresourceerror', 'local_sharedresources', $message));
    }
    unset($mnetrequest);

    return $result;
}

/**
 * Temporarily (untill better choice) unbinds repo naming
 * from hostnames
 * // TODO : evaluate better strategies
 */
function sharedresources_repo($wwwroot) {
    global $CFG;

    if (preg_match("/https?:\\/\\/([^.]+)/", $wwwroot, $matches)) {
        return $matches[1];
    }

    return str_replace('http://', '', $wwwroot);
}

/**
 * setup visible search widgets depending on metadata plugin and
 * user quality
 * @param array ref $visiblewidgets an array to be filled by the function with objets reprensenting visible widgets
 * @param object $context course or site context
 * @return void fills the $visiblewidgets
 */
function sharedresources_setup_widgets(&$visiblewidgets, $context) {
    global $CFG;

    // Load all widget classes.
    $widgetclasses = glob($CFG->dirroot.'/local/sharedresources/classes/searchwidgets/*');
    foreach ($widgetclasses as $classfile) {
        include_once($classfile);
    }

    $config = get_config('sharedresource');

    if ($activewidgets = unserialize(@$config->activewidgets)) {
        $count = 0;
        foreach ($activewidgets as $key => $widget) {

            $count++;
            $visiblewidgets[$key] = $widget;
        }
    } else {
        if (function_exists('debug_trace')) {
            debug_trace('Failed deserializing');
        }
    }
}

/**
 * Get local widgets from a remote resource producer.
 * @param int $repo the repo id (mnet_host id)
 * @param string $context role context for sharedresources
 */
function sharedresources_remote_widgets($repo, $context) {
    global $USER, $DB, $CFG;

    // Load all widget classes.
    $widgetclasses = glob($CFG->dirroot.'/local/sharedresources/classes/searchwidgets/*');

    foreach ($widgetclasses as $classfile) {
        include_once($classfile);
    }

    // Get the originating (ID provider) host info.
    if (!$remotepeer = new mnet_peer()) {
        throw new moodle_exception(get_string('errormnetpeer', 'local_sharedresources'));
    }

    if (!$remotehost = $DB->get_record('mnet_host', ['id' => $repo])) {
        if (debugging()) {
            throw new moodle_exception("No such host $repo in the neighborghood");
        }
        return;
    }
    $remotepeer->set_wwwroot($remotehost->wwwroot);

    // Set up the RPC request.
    $mnetrequest = new mnet_xmlrpc_client();
    $mnetrequest->set_method('mod/sharedresource/rpclib.php/sharedresource_rpc_get_widgets');

    // Set remoteuser and remoteuserhost parameters.
    if (!empty($USER->username)) {
        $mnetrequest->add_param($USER->username, 'string');
        $userremoteuserhost = $DB->get_record('mnet_host', ['id' => $USER->mnethostid]);
        $mnetrequest->add_param($userremoteuserhost->wwwroot, 'string');
    } else {
        $mnetrequest->add_param('anonymous', 'string');
        $mnetrequest->add_param($CFG->wwwroot, 'string');
    }

    $mnetrequest->add_param($CFG->wwwroot, 'string'); // Calling host.

    // Set filters and offset ad page parameters.
    $mnetrequest->add_param('', 'string'); // Context. Not yet in use.

    // Do RPC call and store response.
    if ($mnetrequest->send($remotepeer) === true) {
        $res = json_decode($mnetrequest->response);
        if ($res->status == RPC_SUCCESS) {
            $widgets = (array)$res->widgets;
            foreach ($widgets as $ix => $wdg) {
                // We need reclass.
                $classname = "\\local_sharedresources\\search\\".$wdg->type.'_widget';
                $reclassed = new $classname($wdg->id, $wdg->label, $wdg->type);
                $widgets[$ix] = $reclassed;
            }
        } else {
            throw new moodle_exception($res->error);
        }
    } else {
        $widgets = [];
    }

    return $widgets;
}

/**
 * Get search clauses from session and udate from incomming changes.
 * Additionally react to an eventual "simplesearch" query
 * @param object $mtdplugin the active metadata plugin
 * @param array $visiblewidgets an array of widgets to check, built from confguration.
 * @param arrayref &$searchfields an array of input search fields  for widget filters. This array is given to catch_value()
 * of each widget for adding search query inputs.
 */
function sharedresources_process_search_widgets($mtdplugin, $visiblewidgets, &$searchfields, $mode) {
    global $SESSION;

    if ($mode == 'simple') {
        // Erase all searches.
        unset($SESSION->searchbag);

        $fieldsforsingle = $mtdplugin->get_simple_search_elements();
        $searchvalue = optional_param('simplesearch', '', PARAM_TEXT);
        $searchoption = optional_param('simplesearch_option', '', PARAM_TEXT);
        // Fakes a full search, based on what the metadata plugin tells as relevant fields to search text in.
        if (!empty($searchvalue)) {
            foreach ($fieldsforsingle as $f) {
                $searchfields[$f] = "$searchoption:$searchvalue";
            }
        }

        return;
    }

    $result = false;
    $config = get_config('sharedresource');

    if (!empty($_GET) && !empty($config->activewidgets)) {
        foreach ($visiblewidgets as $key => $widget) {
            // The widget catches the value in $_GET, cleans it and self registers in $searchfields.
            $result = $result || $widget->catch_value($searchfields);
        }
    }
    return $result;
}

/**
 * Get a string in a subplugin
 * @param string $identifier
 * @param string $subplugin
 * @param string $a replacement object
 * @param string $lang
 */
function sharedresources_get_string($identifier, $subplugin, $a = '', $lang = '') {
    global $CFG;

    static $string = [];

    if (empty($lang)) {
        $lang = current_language();
    }

    list($type, $plug) = explode('_', $subplugin);

    include($CFG->dirroot.'/local/sharedresources/db/subplugins.php');

    if (!isset($plugstring[$plug])) {
        if (file_exists($CFG->dirroot.'/'.$subplugins[$type].'/'.$plug.'/lang/en/'.$subplugin.'.php')) {
            include($CFG->dirroot.'/'.$subplugins[$type].'/'.$plug.'/lang/en/'.$subplugin.'.php');
        } else {
            debugging("English lang file must exist", DEBUG_DEVELOPER);
        }

        // Override with lang file if exists.
        if (file_exists($CFG->dirroot.'/'.$subplugins[$type].'/'.$plug.'/lang/'.$lang.'/'.$subplugin.'.php')) {
            include($CFG->dirroot.'/'.$subplugins[$type].'/'.$plug.'/lang/'.$lang.'/'.$subplugin.'.php');
        } else {
            $string = [];
        }
        $plugstring[$plug] = $string;
    }

    if (array_key_exists($identifier, $plugstring[$plug])) {
        $result = $plugstring[$plug][$identifier];
        if ($a !== null) {
            if (is_object($a) || is_array($a)) {
                $a = (array)$a;
                $search = [];
                $replace = [];
                foreach ($a as $key => $value) {
                    if (is_int($key)) {
                        // We do not support numeric keys - sorry!
                        continue;
                    }
                    $search[]  = '{$a->'.$key.'}';
                    $replace[] = (string)$value;
                }
                if ($search) {
                    $result = str_replace($search, $replace, $result);
                }
            } else {
                $result = str_replace('{$a}', (string)$a, $result);
            }
        }
        // Debugging feature lets you display string identifier and component.
        if (!empty($CFG->debugstringids) && optional_param('strings', 0, PARAM_INT)) {
            $result .= ' {' . $identifier . '/' . $subplugin . '}';
        }
        return $result;
    }

    if (!empty($CFG->debugstringids) && optional_param('strings', 0, PARAM_INT)) {
        return "[[$identifier/$subplugin]]";
    } else {
        return "[[$identifier]]";
    }
}

/**
 * provides a mean to recognize sharedresource hides an LTI Tool definition
 * TODO : find other ways to guess it.
 * Admits the lti typology has been resolved remotely. In this case, the resource has a islti marker.
 * @param object $resource a sharedresource descriptor
 */
function sharedresource_is_lti($resource) {
    return(preg_match('/LTI/', $resource->keywords) || @$resource->islti);
}

/**
 * provides a mean to recognize sharedresource hides an media or a media
 * proxy that can be played in a mplayer
 * TODO : refine filtering of mime types that are acceptable
 * Admits the media typology has been resolved remotely. In this case, the resource has a ismedia marker.
 * @param object $resource a sharedresource descriptor
 */
function sharedresource_is_media($resource) {

    if (!empty($resource->ismedia)) {
        // Resolved remotely.
        return true;
    }

    if ($resource->file) {
        $fs = get_file_storage();
        if ($resourcefile = $fs->get_file_by_id($resource->file)) {
            if (preg_match('#^video/#', $resourcefile->get_mimetype())) {
                return true;
            }
        }
    }

    if (preg_match('/youtube\.com|youtu\.be/', $resource->url)) {
        return true;
    }

    return false;
}

/**
 * Based on the structure of the zip file.
 * @param StdClass $resource
 */
function sharedresource_is_scorm($resource) {
    global $CFG;

    if (!empty($resource->isscorm)) {
        // Resolved remotely.
        return true;
    }

    $fs = get_file_storage();

    if ($resource->file) {
        if ($resourcefile = $fs->get_file_by_id($resource->file)) {
            $filename = $resourcefile->get_filename();
            if (preg_match('/\.zip$/', $filename)) {

                $zip = new ZipArchive;

                $contenthash = $resourcefile->get_contenthash();
                $l1 = $contenthash[0] . $contenthash[1];
                $l2 = $contenthash[2] . $contenthash[3];
                $filepath = $CFG->dataroot."/filedir/$l1/$l2/{$contenthash}";

                if ($zip->open($filepath) !== true) {
                    return false;
                }

                if ($zip->locateName('imsmanifest.xml', ZipArchive::FL_NOCASE | ZIPARCHIVE::FL_NODIR)) {
                    return true;
                }
            }
        }
    }

    return false;
}

/**
 * Get courses where a resource is published.
 * @param object $entry
 * @return array of course records
 * @todo restrict number of fields in the result for optimization
 */
function sharedresources_get_courses($entry) {
    global $DB;

    $sql = "
        SELECT DISTINCT
            c.*
        FROM
            {course} c,
            {course_modules} cm,
            {modules} m,
            {sharedresource} sh
        WHERE
            c.id = cm.course AND
            cm.instance = sh.id AND
            m.id = cm.module AND
            m.name = 'sharedresource' AND
            sh.identifier = ?
    ";

    return $DB->get_records_sql($sql, [$entry->identifier]);
}

/**
 * provides a mean to recognize sharedresource hides a deployable moodle backup.
 * @param sharedresource $resource
 */
function sharedresource_is_moodle_activity($resource) {

    $fs = get_file_storage();

    if ($storedfile = $fs->get_file_by_id($resource->file)) {
        $archivename = $storedfile->get_filename();
        if ('application/vnd.moodle.backup' == $storedfile->get_mimetype()) {
            return true;
        }
    }

    return false;
}

/**
 * get top ranking keywords from metadata
 * @param int $courseid
 * @todo turn implementation to more portable IN() statement
 */
function sharedresource_get_top_keywords($courseid) {
    global $DB, $CFG;

    $config = get_config('sharedresource');

    if (empty($config->schema)) {
        throw new moodle_exception(get_string('nometadataplugin', 'sharedresource'));
    }

    $mtdclass = '\\mod_sharedresource\\plugin_'.$config->schema;
    require_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$config->schema.'/plugin.class.php');
    $mtdstandard = new $mtdclass();
    $kwelement = $mtdstandard->get_keyword_element();

    if (!$kwelement) {
        // Some metadata standard have no keywords (DC).
        return '';
    }

    $contexts[] = 1;

    // Get all categories on the way to root.
    if ($courseid > SITEID) {
        $catid = $DB->get_field('course', 'category', ['id' => $courseid]);
        $cat = $DB->get_record('course_categories', ['id' => $catid]);
        $catcontext = context_coursecat::instance($cat->id);
        $contexts[] = $catcontext->id;
        while ($cat->parent) {
            $cat = $DB->get_record('course_categories', ['id' => $cat->parent]);
            $catcontext = context_coursecat::instance($cat->id);
            $contexts[] = $catcontext->id;
        }
    }

    $contextlist = implode(',', $contexts);

    $topranksize = 20;

    $sql = "
        SELECT
            value,
            COUNT(DISTINCT entryid) as ranking
        FROM
            {sharedresource_metadata} shm,
            {sharedresource_entry} sh
        WHERE
            shm.entryid = sh.id AND
            sh.context IN ('{$contextlist}') AND
            element LIKE '{$kwelement->name}:%' AND
            namespace = '{$config->schema}' AND
            value IS NOT NULL AND
            value != ''
        GROUP BY
            value
        ORDER BY
            ranking DESC
        LIMIT
            0, $topranksize
    ";

    $topkws = $DB->get_records_sql($sql, []);

    return $topkws;
}

/**
 * A recursive path explorator for building import information from physical directory
 * @param string $upath the local path for each iteration
 * @param arrayref &$importlines the aray of descriptors being built by the recursion
 * @param arrayref &$metadatadefines an output array for parsed metadata
 * @param objectref &$data the initial recursion start information non mutable
 *
 * In all the code, $_ variable contain filesystem compatible encodings, other
 * are all UTF8 variable
 */
function sharedresources_scan_importpath($upath, &$importlines, &$metadatadefines, &$data) {
    global $CFG;

    if ($CFG->ostype == 'WINDOWS' && !$data->nativeutf8) {
        $path = mb_convert_encoding($upath, 'Windows-1252', 'UTF-8');
        $importpath = mb_convert_encoding($data->importpath, 'Windows-1252', 'UTF-8');
    } else {
        $path = $upath;
        $importpath = $data->importpath;
    }

    if (file_exists($path.'/metadata.csv')) {
        $metadata = file($path.'/metadata.csv');
        mtrace("Found metadata file in $upath");
        $options = ['encoding' => $data->encoding];
        sharedresources_parse_metadata($metadata, $metadatadefines, $upath, $options);
    }

    // Process an optional alias file for taxonomy tokens.
    $aliasescache = [];
    if (file_exists($importpath.'/taxonomy_aliases.txt')) {
        $aliases = file($importpath.'/taxonomy_aliases.txt');
        foreach ($aliases as $aliasline) {
            // Taxonomy aliases should share the same encoding than the metadata.csv.
            if ($data->encoding != 'UTF-8') {
                $aliasline = mb_convert_encoding($aliasline, 'UTF-8', 'auto');
            }
            list($from, $to) = explode('=', chop($aliasline));
            $aliasescache[rtrim($from)] = ltrim($to);
        }
    }

    // Apply overriding aliases to taxonomy.
    if (!function_exists('alias_taxon_tokens')) {
        /**
         * Internal helper for array_walk.
         * @param object $item
         * @param object $unused
         * @param object $aliases
         */
        function alias_taxon_tokens(& $item, $unused, $aliases) {
            if (array_key_exists($item, $aliases)) {
                $item = $aliases[$item];
            }
        }
    }

    // Utf8 processing here for taxon path.

    $taxonparts = null;

    if (!empty($data->deducetaxonomyfrompath)) {
        // Get relative path.
        $cleanedpath = str_replace($data->importpath, '', $upath);
        if (!empty($cleanedpath)) {
            // We remove an eventual first slash.
            $cleanedpath = preg_replace('/^\//', '', $cleanedpath);

            // Split into parts.
            $taxonparts = explode('/', $cleanedpath);

            // Eventually translate using an aliasing table.
            array_walk($taxonparts, 'alias_taxon_tokens', $aliasescache);
        }
    }

    $dir = opendir($path);

    if (!$dir) {
        mtrace("Failed opening $upath");
        return;
    }

    if (defined('CLI_SCRIPT')) {
        mtrace("Processing entries from $upath");
    }

    while ($entry = readdir($dir)) {

        if ($CFG->ostype == 'WINDOWS' && !$data->nativeutf8) {
            /*
             * $entry is read as ASCII from Windows file system. We need it so for accessing
             * Windows filesystem but in UTF8 for all other purposes.
             */
            $uentry = mb_convert_encoding($entry, 'UTF-8', 'auto');
        } else {
            $uentry = $entry;
        }

        if (preg_match('/^\\./', $uentry)) {
            continue;
        }
        if (preg_match('/(CVS|SVN)/', $uentry)) {
            continue;
        }
        if (is_dir($path.'/'.$entry)) {
            mtrace("Processing dir $upath/$uentry ");
            sharedresources_scan_importpath($upath.'/'.$uentry, $importlines, $metadatadefines, $data);
        } else {
            if (preg_match('/^__/', $uentry)) {
                continue; // Skip any already processed file.
            }
            if ($uentry == "metadata.csv") {
                continue; // Skip any metadata add on.
            }
            if ($uentry == "taxonomy_aliases.txt") {
                continue; // Skip any taxonomy translator add on.
            }
            if ($uentry == "moodle_sharedlibrary_import.log") {
                continue;
            }

            // If we have no metadata at all for this entry, we cannot process it.
            if (empty($metadatadefines) || !array_key_exists($upath.'/'.$uentry, $metadatadefines)) {
                continue;
            }

            if (!empty($excludepattern)) {
                if (!preg_match('/'.$data->importexclusionpattern.'/', $uentry)) {
                    $importlines[$metadatadefines[$upath.'/'.$uentry]['sortorder']] = $upath.'/'.$uentry;
                    mtrace("Prepare import ".$upath.'/'.$uentry);
                }
            } else {
                $importlines[$metadatadefines[$upath.'/'.$uentry]['sortorder']] = $upath.'/'.$uentry;
                mtrace("Prepare import ".$upath.'/'.$uentry);
            }

            /*
             * Add taxonomy attribute to metadata from file path, or from a 'category' field in metadata.
             * file path directory names might have been aliased.
             */
            if (!empty($taxonparts)) {
                $metadatadefines[$upath.'/'.$uentry]['taxonomy'] = implode('/', $taxonparts);
            } else if (!empty($metadatadefines[$upath.'/'.$uentry]['category'])) {
                $metadatadefines[$upath.'/'.$uentry]['taxonomy'] = $metadatadefines[$upath.'/'.$uentry]['category'];
            }
        }
    }
    closedir($dir);
}

/**
 * parses some metadata in the metadata import file
 * @param array &$metadata a metadata.csv file content as an array of strings
 * @param array &$metadatadefines an array of parsed metadata to be integrated
 * @param string $uppath the physical path where the metadatafile.csv was found.
 * @param array $options some operation options comming from from context such as encoding.
 */
function sharedresources_parse_metadata(&$metadata, &$metadatadefines, $upath, $options) {

    static $sortorder = 0; // An absolute counter for ordering file in inputlist, based on metadata analysis.

    $authorized = ['file', 'category', 'section', 'visible', 'title',
                        'shortname', 'description', 'keywords', 'language',
                        'authors', 'contributors', 'documenttype', 'documentnature',
                        'pedagogictype', 'difficulty', 'guidance'];

    $hl = array_shift($metadata);
    while ($hl && preg_match('/^(\s|\/\/|#|$)/', $hl)) {
        $hl = array_shift($metadata);
    }

    if ($options['encoding'] != 'UTF-8') {
        $hl = mb_convert_encoding($hl, 'UTF-8', 'auto');
    }

    $header = explode(';', chop($hl));
    $linesize = count($header);

    if ($header[0] != 'file') {
        echo "First field name must be file. This metadata file is malformed. Skipping all metadata.";
        return;
    }

    $unauthorized = [];
    foreach ($header as $column) {
        if (!in_array($column, $authorized)) {
            $unauthorized[] = $column;
        }
    }

    if ($unauthorized) {
        echo "Unauthorized columns in file header: ".implode(', ', $unauthorized);
        return;
    }

    $i = 1;
    foreach ($metadata as $l) {
        if (preg_match('/^(\s|\/\/|#|$)/', $l)) {
            continue; // Skip comments, empty lines.
        }
        $l = chop($l);

        $line = explode(';', $l);
        $linecount = count($line);
        if ($linecount != $linesize) {
            $state = ($linecount < $linesize) ? -1 : 1;
            echo "Bad count in $path at line ".($i + 1)." ($state): ignoring...<br/>\n$l\n";
            $i++;
            continue;
        }

        $j = 0;
        $mtd = [];
        $mtd['sortorder'] = $sortorder++;
        foreach ($line as $field) {
            if (!$j) {
                // First field is filename.
                $filename = $field;
                $urealpath = $upath.'/'.$filename;
                $urealpath = str_replace('\\', '/', $urealpath);
                if (!file_exists($urealpath)) {
                    $message = "File $urealpath not in archive. Be carefull file names need NOT HAVE extended chars.";
                    $message .= " This is NOT reductible by php programming.";
                    mtrace($message);
                }
            }

            $mtd[$header[$j]] = $field;
            $j++;
        }

        $metadatadefines[$upath.'/'.$filename] = $mtd;
        $i++;
    }
}

/**
 * This method combines the file list and metadata to build adequate fd file descriptors
 * for the import processor.
 *
 * @param array $importlist The list of file physical paths to import
 * @param array $metadatadefines
 */
function sharedresources_aggregate($importlist, $metadatadefines) {
    $aggregatedlist = [];

    foreach ($importlist as $entry) {
        if (array_key_exists($entry, $metadatadefines)) {
            $descriptor = $metadatadefines[$entry];
            $descriptor['fullpath'] = $entry;
        } else {
            $descriptor = [];
            $descriptor['fullpath'] = $entry;
            $descriptor['file'] = pathinfo($entry, PATHINFO_BASENAME);
            $descriptor['title'] = basename($entry);
        }
        $aggregatedlist[] = $descriptor;
    }

    return $aggregatedlist;
}

/**
 * This is a relocalized function in order to get local_my more compact.
 * checks if a user has a some named capability effective somewhere in a course.
 * @param string $capability;
 * @param bool $excludesystem
 * @param bool $excludesite
 * @param bool $doanything
 * @param string $contextlevels restrict to some contextlevel may speedup the query.
 */
function sharedresources_has_capability_somewhere($capability, $excludesystem = true, $excludesite = true,
                                           $doanything = false, $contextlevels = '') {
    global $USER, $DB;

    // Faster check.
    $systemcontext = context_system::instance();
    if (!$excludesystem && has_capability($capability, $systemcontext, $USER->id, $doanything)) {
        return true;
    }

    $contextclause = '';

    if ($contextlevels) {
        list($sql, $params) = $DB->get_in_or_equal(explode(',', $contextlevels), SQL_PARAMS_NAMED);
        $contextclause = "
           AND ctx.contextlevel $sql
        ";
    }
    $params['capability'] = $capability;
    $params['userid'] = $USER->id;

    $sitecoursecontext = context_course::instance(SITEID);

    $sitecontextexclclause = '';
    if ($excludesite) {
        $sitecontextexclclause = " ctx.id != {$sitecoursecontext->id}  AND ";
    }

    // This is a a quick rough query that may not handle all role override possibility.

    $sql = "
        SELECT
            COUNT(DISTINCT ra.id)
        FROM
            {role_capabilities} rc,
            {role_assignments} ra,
            {context} ctx
        WHERE
            rc.roleid = ra.roleid AND
            ra.contextid = ctx.id AND
            $sitecontextexclclause
            rc.capability = :capability
            $contextclause
            AND ra.userid = :userid AND
            rc.permission = 1
    ";
    $hassome = $DB->count_records_sql($sql, $params);

    if (!empty($hassome)) {
        return true;
    }

    return false;
}

/**
 * This is a relocalized function in order to get local_my more compact.
 * checks if a user has a some named capability effective somewhere in a course.
 * @param string $capability;
 * @param bool $excludesystem
 * @param bool $excludesite
 * @param bool $doanything
 * @param string $contextlevels restrict to some contextlevel may speedup the query.
 */
function sharedresources_has_capability_in_upper_contexts($capability, $context, $checkcourses = true, $doanything = false) {
    global $USER, $DB;

    $systemcontext = context_system::instance();
    if ($doanything && has_capability('moodle/site:config', $systemcontext)) {
        // Administrators can always see.
        return true;
    }

    if (has_capability('repository/sharedresources:manage', $systemcontext)) {
        // Librarians can always see.
        return true;
    }

    if (is_numeric($context)) {
        $context = context::instance_by_id($context);
    }

    $contextstocheck = explode('/', $context->path);
    $contextstocheck = array_reverse($contextstocheck);
    array_pop($contextstocheck);
    if (!empty($contextstocheck)) {
        foreach ($contextstocheck as $ctxid) {
            $ctx = context::instance_by_id($ctxid);
            if (has_capability($capability, $ctx, $USER)) {
                return true;
            }
        }
    }

    $courses = $DB->get_records_menu('course', ['category' => $context->instanceid], 'id,shortname');
    if (!empty($courses)) {
        foreach (array_keys($courses) as $cid) {
            $ctx = context_course::instance($cid);
            if (has_capability($capability, $ctx)) {
                return true;
            }
        }
    }

    return false;
}

