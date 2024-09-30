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
 * View a resource
 *
 * @package     local_sharedresources
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

/*
 * This file allows acceding to resources in a platform instance independant way.
 * The user will only need to present a local id (internal resource id) or remote id
 *
 * The resource access layer is for use with mod/sharedresource resource plugin.
 */
require('../../config.php');

if (!file_exists($CFG->dirroot.'/mod/sharedresource/lib.php')) {
    throw new coding_exception('Shared resource plugin is not installed.');
}

require_once($CFG->dirroot.'/local/sharedresources/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->libdir.'/filelib.php');

$config = get_config('local_sharedresources');

// For use when library is declared as a private catalog, to authentify valid consumers.
// When getting resource list from us, the consumers have received a long or persistant key from us.
$token = optional_param('token', '', PARAM_TEXT);

$isloggedin = false;
if (!empty($config->privatecatalog)) {

    $tokenchecked = false;
    if (!empty($token)) {
        include_once($CFG->dirroot.'/auth/ticket/lib.php');
        if ($ticket = ticket_decode($token, 'internal')) {
            if (!$tokenchecked = ticket_accept($ticket)) {
                throw new moodle_exception(get_string('errorinvalidticket', 'local_sharedresources'));
            }
        }
    }

    if (!$tokenchecked) {
        require_login();
        $isloggedin = true;
    }
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
    throw new moodle_exception(get_string('errorinvalidresourceid', 'local_sharedresources'));
}

if (!$resource = $DB->get_record('sharedresource_entry', [$idfield => $idvalue])) {
    throw new moodle_exception(get_string('errorinvalidresource', 'local_sharedresources'));
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
    $context = $DB->get_record('context', ['id' => $resource->context]);
    if (!$isloggedin) {
        require_login();
    }
    // Do we have the view capability in this context or some upper ?
    if (!sharedresources_has_capability_in_upper_contexts('repository/sharedresources:view', $context, true, true)) {
        send_file_not_found();
    }
}

// TODO : implement logging.

if (empty($resource->file) && !empty($resource->url)) {
    if ($resource->url == $FULLME) {
        throw new moodle_exception('Resource seems be a looping url on the library');
    }
    redirect($resource->url);
} else {
    // First form.
    $fs = get_file_storage();
    $storedfile = $fs->get_file_by_id($resource->file);
    if (!$storedfile) {
        send_file_not_found();
    }
    send_stored_file($storedfile, 60 * 60, 0, $forcedownload);
}
