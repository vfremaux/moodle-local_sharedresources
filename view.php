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
 * @category   local
 * @author Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 * This file allows acceding to resources in a platform instance independant way.
 * The user will only need to present a local id (internal resource id) or remote id
 *
 * The resource access layer is for use with mod/taoresource resource plugin.
 */
require('../../config.php');

if (!file_exists($CFG->dirroot.'/mod/sharedresource/lib.php')) {
    throw new coding_exception('Shared resource plugin is not installed.');
}

require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->libdir.'/filelib.php');

$config = get_config('local_sharedresources');

$isloggedin = false;
if (!empty($config->privatecatalog)) {
    require_login();
    $isloggedin = true;
}

$resourceid = optional_param('id', '', PARAM_ALPHANUM);
$identifier = optional_param('identifier', '', PARAM_ALPHANUM);
$remote = optional_param('remote', 0, PARAM_ALPHANUM);
$forcedownload = optional_param('forcedownload', 0, PARAM_BOOL);

if (!empty($resourceid)) {
    if ($remote) {
        $idfield = 'remoteid';
    } else {
        $idfield = 'id';
    }
    $idvalue = $resourceid;
} else if (!empty($identifier)) {
    if ($remote) {
        $idfield = 'remoteid';
    } else {
        $idfield = 'identifier';
    }
    $idvalue = $identifier;
} else {
    print_error('errorinvalidresourceid', 'local_sharedresource');
    exit;
}

if (!$resource = $DB->get_record('sharedresource_entry', array($idfield => $idvalue))) {
    print_error('errorinvalidresource', 'local_sharedresource');
}

// Is resource valid for public delivery ?
if (!$resource->isvalid) {
    if (!$isloggedin) {
        require_login();
        $isloggedin = true;
    }
}

// Is resource shared in lower context ?
if ($resource->context > 1) {
    $context = $DB->get_record('context', array('id' => $resource->context));
    if (!$isloggedin) {
        require_login();
    }
    if (!sharedresource_has_capability_somewhere('repository/sharedresources:use', true, true, $context, false)) {
        send_file_not_found();
    }
}

// TODO : implement logging.

if (empty($resource->file) && !empty($resource->url)) {
    redirect($resource->url);
} else {
    // First form.
    $fs = get_file_storage();
    $stored_file = $fs->get_file_by_id($resource->file);
    send_stored_file($stored_file, 60*60, 0, $forcedownload);
}
