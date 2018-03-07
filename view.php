<?php
<<<<<<< HEAD
/**
 * Moodle - Modular Object-Oriented Dynamic Learning Environment
 *          http://moodle.org
 * Copyright (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mod-sharedresource
 * @subpackage resources
=======
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
>>>>>>> MOODLE_33_STABLE
 * @author Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 * This file allows acceding to resources in a platform instance independant way.
 * The user will only need to present a local id (internal resource id) or remote id
<<<<<<< HEAD
<<<<<<< HEAD
 * 
=======
 *
>>>>>>> MOODLE_34_STABLE
 * The resource access layer is for use with mod/taoresource resource plugin.
 */
    
    include_once dirname(dirname(dirname(__FILE__))).'/config.php';
    
    if (!file_exists($CFG->dirroot.'/mod/sharedresource/lib.php')){
        error('Shared resource plugin is not installed.');
        exit;
    }
    
    include_once $CFG->dirroot.'/mod/sharedresource/lib.php';
    include_once $CFG->libdir.'/filelib.php';
    
    // require_login();
    // do we need to be authentified to access resource ?
    
    $resourceid = optional_param('id', '', PARAM_ALPHANUM);
    $identifier = optional_param('identifier', '', PARAM_ALPHANUM);
    $remote = optional_param('remote', 0, PARAM_ALPHANUM);
    $forcedownload = optional_param('forcedownload', 0, PARAM_BOOL);

    if (!empty($resourceid)){
    	if ($remote){
	    	$idfield = 'remoteid';
	    } else {
	    	$idfield = 'id';
	    }
    	$idvalue = $resourceid;
    } elseif (!empty($identifier)) {
    	if ($remote){
	    	$idfield = 'remoteid';
	    } else {
	    	$idfield = 'identifier';
	    }
    	$idvalue = $identifier;
    } else {
        print_error('errorinvalidresourceid', 'local_sharedresource');
        exit;
    }
    
    if (!$resource = $DB->get_record('sharedresource_entry', array($idfield => $idvalue))){
        print_error('errorinvalidresource', 'local_sharedresource');
    }

    // is resource valid for public delivery ?
    if (!$resource->isvalid){
        require_login();
    }

    // is resource shared in lower context ?
    if ($resource->context > 1){
    	$context = $DB->get_record('context', array('id' => $resource->context));
        require_login();
    	if (!sharedresource_has_capability_somewhere('repository/sharedresources:use', true, true, $context, false)){
    		send_file_not_found();
    	}
    }        
    
    if ($remote){
        add_to_log (SITEID, 'sharedresource', 'view', $CFG->wwwroot.'/local/sharedresources/view.php?id='.$resource->id, 'localid' , 0, 0);
    } else {
        add_to_log (SITEID, 'sharedresource', 'view', $CFG->wwwroot.'/local/sharedresources/view.php?id='.$resource->id.'&amp;remote=1', 'remoteid' , 0, 0);
    }

    if (empty($resource->file) && !empty($resource->url)){
        redirect($resource->url);
    } else {
        // first form
        $fs = get_file_storage();
        $stored_file = $fs->get_file_by_id($resource->file);
        send_stored_file($stored_file, 60*60, 0, $forcedownload);
    }
=======
 *
 * The resource access layer is for use with mod/taoresource resource plugin.
 */
require('../../config.php');

if (!file_exists($CFG->dirroot.'/mod/sharedresource/lib.php')) {
    throw new coding_exception('Shared resource plugin is not installed.');
}

require_once($CFG->dirroot.'/local/sharedresources/lib.php');
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
    // Do we have the use capability in this context or some upper ?
    if (!sharedresources_has_capability_in_upper_contexts('repository/sharedresources:use', $context, true)) {
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
>>>>>>> MOODLE_33_STABLE
