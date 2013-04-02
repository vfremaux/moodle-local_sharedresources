<?php
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
 * @author Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 * This file allows acceding to resources in a platform instance independant way.
 * The user will only need to present a local id (internal resource id) or remote id
 * 
 * The resource access layer is for use with mod/taoresource resource plugin.
 */
    
    include_once dirname(dirname(__FILE__)).'/config.php';
    
    if (!file_exists($CFG->dirroot.'/mod/sharedresource/lib.php')){
        error('Shared resource plugin is not installed.');
        exit;
    }
    
    include_once $CFG->dirroot.'/mod/sharedresource/lib.php';
    include_once $CFG->libdir.'/filelib.php';
    
    // require_login();
    // do we need to be authentified to access resource ?
    
    $resourceid = optional_param('id', '', PARAM_ALPHANUM);
    $remote = optional_param('remote', 0, PARAM_ALPHANUM);
    
    if (!empty($resourceid)){
        if ($remote){
            $resource = get_record('sharedresource_entry', 'remoteid', $resourceid);
        } else {
            $resource = get_record('sharedresource_entry', 'identifier', $resourceid);
        }

        // is resource valid for public delivery ?
        if (!$resource->isvalid){
            require_login();
        }
        
    } else {
        error("Invalid resource Id");
        exit;
    }
    
    if (!$resource){
        error("Unkown resource");
        exit;
    }

    if ($remote){
        add_to_log (SITEID, 'sharedresource', 'view', $CFG->wwwroot.'/resource/view.php?id='.$resourceid, 'localid' , 0, 0);
    } else {
        add_to_log (SITEID, 'sharedresource', 'view', $CFG->wwwroot.'/resource/view.php?id='.$resourceid.'&amp;remote=1', 'remoteid' , 0, 0);
    }

    if (empty($resource->file) && !empty($resource->url)){
        redirect($resource->url);
    } else {
        // first form
        $path = $CFG->dataroot . SHAREDRESOURCE_RESOURCEPATH;
        $filename = $resource->file;
        send_file($path.'/'.$filename, $filename, 'default', 0, false, false, '');
    }

?>