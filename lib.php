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
 * @package     local_sharedresources
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright   (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 * Provides libraries for resource generic access.
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
        $supports = array(
            'pro' => array(
                'repo' => array('remote'),
                'import' => array('mass'),
                'admin' => array('pro'),
                'emulate' => 'community',
            ),
            'community' => array(
            ),
        );
        $prefer = array();
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
 * a call back function for autoloading classes when unserializing the widgets
 *
 */
function resources_load_searchwidgets($classname) {
    global $CFG;

    $classname = str_replace('local_sharedresources\\search\\', '', $classname);

    if (file_exists($CFG->dirroot."/local/sharedresources/searchwidgets/{$classname}.class.php")) {
        include_once($CFG->dirroot."/local/sharedresources/searchwidgets/{$classname}.class.php");
    }
}

// Prepare autoloader of missing search widgets.
ini_set('unserialize_callback_func', 'resources_load_searchwidgets');

if (!defined('RPC_SUCCESS')) {
    define('RPC_TEST', 100);
    define('RPC_SUCCESS', 200);
    define('RPC_FAILURE', 500);
    define('RPC_FAILURE_USER', 501);
    define('RPC_FAILURE_CONFIG', 502);
    define('RPC_FAILURE_DATA', 503);
    define('RPC_FAILURE_CAPABILITY', 510);
}

function cmp($a, $b) {
    $a = preg_replace('@^(a|an|the) @', '', $a);
    $b = preg_replace('@^(a|an|the) @', '', $b);
    return strcasecmp($a, $b);
}

/**
 * get a stub of local resources
 */
function sharedresources_get_local_resources($repo, &$fullresults, $metadatafilters = '', &$offset = 0, $page = 20) {
    global $DB;

    $config = get_config('sharedresource');
    $systemcontext = context_system::instance();

    $plugins = sharedresource_get_plugins();
    $plugin = $plugins[$config->schema];

    // Check if we have some filters.
    $sqlclauses = array();
    $hasfilter = false;
    $tabresources = array(); // Array with keys = id of a resource and value = number of criteria matched in research.

    $mtdfiltersarr = (array)$metadatafilters;

    foreach ($mtdfiltersarr as $filterkey => $filtervalue) {
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
    $clauses = array();
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
    $fullresults['order'] = array();
    if ($offset >= $fullresults['maxobjects']) {
        // Security when changing filter configuration.
        $offset = 0;
    }
    $fullresults['entries'] = $DB->get_records_sql($sql, array(), $offset, $page);

    if (!empty($fullresults['entries'])) {
        foreach ($fullresults['entries'] as $id => $r) {

            $entryclass = \mod_sharedresource\entry_factory::get_entry_class();
            $rentry = new $entryclass($r);

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

            $select = array('entryid' => $id, 'namespace' => $config->schema);
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
function sharedresources_get_remote_repo_resources($repo, &$fullresults, $metadatafilters = '', $offset = 0, $page = 20) {
    global $CFG, $USER, $DB;

    if ($repo == 'local') {
        print_error('errorrepoprogramming');
    }

    $remotehost = $DB->get_record('mnet_host', array('id' => $repo));

    // Get the originating (ID provider) host info.
    if (!$remotepeer = new mnet_peer()) {
        print_error('errormnetpeer', 'local_sharedresources');
    }
    $remotepeer->set_wwwroot($remotehost->wwwroot);

    // Set up the RPC request.
    $mnetrequest = new mnet_xmlrpc_client();
    $mnetrequest->set_method('mod/sharedresource/rpclib.php/sharedresource_rpc_get_list');

    // Set remoteuser and remoteuserhost parameters.
    if (!empty($USER->username)) {
        $mnetrequest->add_param($USER->username, 'string');
        $remoteuserhost = $DB->get_record('mnet_host', array('id' => $USER->mnethostid));
        $mnetrequest->add_param($remoteuserhost->wwwroot, 'string');
    } else {
        $mnetrequest->add_param('anonymous', 'string');
        $mnetrequest->add_param($CFG->wwwroot, 'string');
    }
    $mnetrequest->add_param($CFG->wwwroot, 'string'); // Calling host.

    // Set filters and offset ad page parameters.
    $mnetrequest->add_param((array)$metadatafilters, 'struct');
    $mnetrequest->add_param($offset, 'int');
    $mnetrequest->add_param($page, 'int');

    // Do RPC call and store response.
    if ($mnetrequest->send($remotepeer) === true) {
        $res = json_decode($mnetrequest->response);
        if ($res->status == RPC_SUCCESS) {
            $fullresults = (array)$res->resources;
        } else {
            print_error($res->error);
        }
    } else {
        $fullresults['entries'] = array();
        $fullresults['maxobjects'] = 0;
        foreach ($mnetrequest->error as $errormessage) {
            list($code, $message) = array_map('trim', explode(':', $errormessage, 2));
            $message .= "ERROR $code:<br/>$errormessage<br/>";
        }
        print_error("RPC mod/sharedresource/get_list:<br/>$message");
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

    $consumers = $DB->get_records_sql($sql, array($hostroot));

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
        $uses = $DB->count_records('sharedresource', array('identifier' => $entry->identifier));
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

                $remoteuserhost = $DB->get_record('mnet_host', array('id' => $user->mnethostid));
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

    $remotehost = $DB->get_record('mnet_host', array('id' => $repo));

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
        $remoteuserhost = $DB->get_record('mnet_host', array('id', $USER->mnethostid));
        $mnetrequest->add_param($remoteuserhost->wwwroot);
    } else {
        $mnetrequest->add_param('anonymous');
        $mnetrequest->add_param($CFG->wwwroot);
    }

    // Set $category and $offset ad $page parameters.
    $mnetrequest->add_param($resourceentry, 'struct');

    $metadata = $DB->get_records('sharedresource_metadata', array('entryid' => $resourceentry->id));

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
            print_error('rpcsharedresourcesubmiterror', '');
        }
    } else {
        foreach ($mnetrequest->error as $errormessage) {
            list($code, $message) = array_map('trim', explode(':', $errormessage, 2));
            $message .= "ERROR $code:<br/>$errormessage<br/>";
        }
        print_error('rpcsharedresourceerror', 'local_sharedresources', $message);
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
        print_error('errormnetpeer', 'local_sharedresources');
    }

    if (!$remotehost = $DB->get_record('mnet_host', array('id' => $repo))) {
        if (debugging()) {
            print_error("No such host $repo in the neighborghood");
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
        $userremoteuserhost = $DB->get_record('mnet_host', array('id' => $USER->mnethostid));
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
            print_error($res->error);
        }
    } else {
        $widgets = array();
    }

    return $widgets;
}

/**
 * Get search clauses from session and udate from incomming changes
 * @param arrayref &$visiblewidgets an array of widgets to check.
 * @param arrayref &$searchfields an array of input search fields  for widget filters.
 */
function sharedresources_process_search_widgets(&$visiblewidgets, &$searchfields) {

    $result = false;
    $config = get_config('sharedresource');

    if (!empty($_GET) && !empty($config->activewidgets)) {
        foreach ($visiblewidgets as $key => $widget) {
            $result = $result or $widget->catch_value($searchfields);
        }
    }
    return $result;
}

function sharedresources_get_string($identifier, $subplugin, $a = '', $lang = '') {
    global $CFG;

    static $string = array();

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
            $string = array();
        }
        $plugstring[$plug] = $string;
    }

    if (array_key_exists($identifier, $plugstring[$plug])) {
        $result = $plugstring[$plug][$identifier];
        if ($a !== null) {
            if (is_object($a) or is_array($a)) {
                $a = (array)$a;
                $search = array();
                $replace = array();
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

    return $DB->get_records_sql($sql, array($entry->identifier));
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
 * @TODO : turn implementation to more portable IN() statement
 */
function sharedresource_get_top_keywords($courseid) {
    global $DB, $CFG;

    $config = get_config('sharedresource');

    if (empty($config->schema)) {
        print_error('nometadataplugin', 'sharedresource');
    }

    $mtdclass = '\\mod_sharedresource\\plugin_'.$config->schema;
    require_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$config->schema.'/plugin.class.php');
    $mtdstandard = new $mtdclass();
    $kwelement = $mtdstandard->getKeywordElement();

    if (!$kwelement) {
        // Some metadata standard have no keywords (DC).
        return '';
    }

    $contexts[] = 1;

    // Get all categories on the way to root.
    if ($courseid > SITEID) {
        $catid = $DB->get_field('course', 'category', array('id' => $courseid));
        $cat = $DB->get_record('course_categories', array('id' => $catid));
        $catcontext = context_coursecat::instance($cat->id);
        $contexts[] = $catcontext->id;
        while ($cat->parent) {
            $cat = $DB->get_record('course_categories', array('id' => $cat->parent));
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

    $topkws = $DB->get_records_sql($sql, array());

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
        $path = utf8_decode($upath);
        $importpath = utf8_decode($data->importpath);
    } else {
        $path = $upath;
        $importpath = $data->importpath;
    }

    if (file_exists($path.'/metadata.csv')) {
        $metadata = file($path.'/metadata.csv');
        mtrace("Found metadata file in $upath");
        $options = array('encoding' => $data->encoding);
        sharedresources_parse_metadata($metadata, $metadatadefines, $upath, $options);
    }

    // Process an optional alias file for taxonomy tokens.
    $aliasescache = array();
    if (file_exists($importpath.'/taxonomy_aliases.txt')) {
        $aliases = file($importpath.'/taxonomy_aliases.txt');
        foreach ($aliases as $aliasline) {
            // Taxonomy aliases should share the same encoding than the metadata.csv.
            if ($data->encoding != 'UTF-8') {
                $aliasline = utf8_encode($aliasline);
            }
            list($from, $to) = explode('=', chop($aliasline));
            $aliasescache[rtrim($from)] = ltrim($to);
        }
    }

    // Apply overriding aliases to taxonomy.
    if (!function_exists('alias_taxon_tokens')) {
        function alias_taxon_tokens(&$item, $unused, $aliases) {
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
            $uentry = utf8_encode($entry);
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

    $authorized = array('file', 'category', 'section', 'visible', 'title',
                        'shortname', 'description', 'keywords', 'language',
                        'authors', 'contributors', 'documenttype', 'documentnature',
                        'pedagogictype', 'difficulty', 'guidance');

    $hl = array_shift($metadata);
    while ($hl && preg_match('/^(\s|\/\/|#|$)/', $hl)) {
        $hl = array_shift($metadata);
    }

    if ($options['encoding'] != 'UTF-8') {
        $hl = utf8_encode($hl);
    }

    $header = explode(';', chop($hl));
    $linesize = count($header);

    if ($header[0] != 'file') {
        echo "First field name must be file. This metadata file is malformed. Skipping all metadata.";
        return;
    }

    $unauthorized = array();
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
        $mtd = array();
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
 * @param arrayref &$metadatadefines
 */
function sharedresources_aggregate($importlist, &$metadatadefines) {
    $aggregatedlist = array();

    foreach ($importlist as $entry) {
        if (array_key_exists($entry, $metadatadefines)) {
            $descriptor = $metadatadefines[$entry];
            $descriptor['fullpath'] = $entry;
        } else {
            $descriptor = array();
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

    $courses = $DB->get_records_menu('course', array('category' => $context->instanceid), 'id,shortname');
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

