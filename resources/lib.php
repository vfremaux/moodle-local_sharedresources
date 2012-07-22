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
 * @package    mod-taoresource
 * @subpackage resources
 * @author Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 * Provides libraries for resource generic access.
 */
 
require_once $CFG->dirroot.'/mnet/xmlrpc/client.php';
include_once $CFG->libdir.'/pear/HTML/AJAX/JSON.php';
require_once $CFG->dirroot.'/mod/sharedresource/lib.php';
require_once $CFG->dirroot.'/mod/sharedresource/rpclib.php';

if (!defined('RPC_SUCCESS')) {
    define('RPC_TEST', 100);
    define('RPC_SUCCESS', 200);
    define('RPC_FAILURE', 500);
    define('RPC_FAILURE_USER', 501);
    define('RPC_FAILURE_CONFIG', 502);
    define('RPC_FAILURE_DATA', 503);
    define('RPC_FAILURE_CAPABILITY', 510);
}

function resources_search_print_tabs($repo, $course){
    global $CFG;
    
    $repos = get_list_of_plugins('resources/plugins');
    
    if (!in_array($repo, $repos)) $repo = $repos[0];
    
    foreach($repos as $arepo){
        $rows[0][] = new tabobject($arepo, $CFG->wwwroot."/resources/search.php?id={$course->id}&amp;repo=$arepo", get_string('reponame', $arepo, '', $CFG->dirroot."/resources/plugins/{$arepo}/lang/"));
    }
    
    print_tabs($rows, $repo);

}

/**
* print tabs allowing selection of the current repository provider
* note that provider is necessarily a mnet host identity.
*/
function resources_browse_print_tabs($repo, $course){
    global $CFG;
    
    $repos['local'] = get_string('local', 'sharedresource');
    
    if ($providers = get_providers()){

        foreach($providers as $provider){
            $repos["$provider->id"] = $provider->name;
        }
    }

    $repoids = array_keys($repos);
    if (!in_array($repo, $repoids)) $repo = $repoids[0];

    foreach($repoids as $repoid){
        if ($course){
            $rows[0][] = new tabobject($repoid, $CFG->wwwroot."/resources/index.php?course={$course->id}&amp;repo=$repoid", $repos[$repoid]);
        } else {
            $rows[0][] = new tabobject($repoid, $CFG->wwwroot."/resources/index.php?repo=$repoid", $repos[$repoid]);
        }
    }
    
    print_tabs($rows, $repo);
}

function cmp($a, $b)
{
    $a = preg_replace('@^(a|an|the) @', '', $a);
    $b = preg_replace('@^(a|an|the) @', '', $b);
    return strcasecmp($a, $b);
}
/**
* get a stub of local resources
*/
function get_local_resources($repo, &$fullresults, $metadatafilters = '', &$offset = 0, $page = 20){
    global $CFG, $USER;

	$plugins = sharedresource_get_plugins();
	$plugin = $plugins[$CFG->{'pluginchoice'}];
    // check if we have some filters 
    $mtdfiltersarr = (array)$metadatafilters;
    $sqlclauses = array();
    $hasfilter = false;
	$tabresources = array(); //array with keys = id of a resource and value = number of criteria matched in research
    foreach($mtdfiltersarr as $filterkey => $filtervalue){
    	if (!empty($filtervalue)){
	    	$entrysets = sharedresource_get_by_metadata($filterkey, $namespace = $plugin->pluginname, $what = 'entries', $filtervalue);
			foreach($entrysets as $key => $id){
				if(!array_key_exists($id, $tabresources)){
					$tabresources[$id] = 1;
				} else {
					$tabresources[$id]++;
				}
			}
    		$hasfilter = true;
	    }
    }

	// get sharedresources from that preselection	
    $clauses = array();
    if ($hasfilter){
    	$entrylist = implode("','", array_keys($tabresources));
    	$clauses[] = " se.id IN('{$entrylist}') ";
    }

    $clauses[] = ($repo != 'all') ? " provider = '$repo' " : '' ;
    
    if (!empty($clauses)){
    	$clause = 'WHERE '.implode(' AND ', $clauses);
    }
    
    $sql = "
        SELECT
            se.*
        FROM
            {$CFG->prefix}sharedresource_entry se
        $clause
        ORDER BY
           title
    ";
    $sqlcount = "
        SELECT
            COUNT(*)
        FROM
            {$CFG->prefix}sharedresource_entry se
        $clause
    ";
    
    debug_trace('postsearch: '.$sql);
    $fullresults['maxobjects'] = count_records_sql($sqlcount);
    $fullresults['order'] = array();
    if ($offset >= $fullresults['maxobjects']) $offset = 0; // security when changing filter configuration
    $fullresults['entries'] = get_records_sql($sql, $offset, $page);

	if (!empty($fullresults['entries'])){
		foreach($fullresults['entries'] as $id => $r){
		    if ($metadata = get_records('sharedresource_metadata', 'entry_id', $id, 'element', 'element,namespace,value')){
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
* @uses $CFG
* @param string $repo the repo identifier
* 
*/
function get_remote_repo_resources($repo, &$fullresults, $metadatafilters = '', $offset = 0, $page = 20){
    global $CFG, $USER;

    if ($repo == 'local') error("Odd situation : trying to get remote list of local repo");    
    
    $remote_host = get_record('mnet_host', 'id', $repo);
    
    // get the originating (ID provider) host info
    if (!$remotepeer = new mnet_peer()){
        error ("MNET client initialisation error");
    }
    $remotepeer->set_wwwroot($remote_host->wwwroot);

    // set up the RPC request
    $mnetrequest = new mnet_xmlrpc_client();
    $mnetrequest->set_method('mod/sharedresource/rpclib.php/sharedresource_rpc_get_list');

    // set $remoteuser and $remoteuserhost parameters
    if (!empty($USER->username)){
        $mnetrequest->add_param($USER->username, 'string');
        $remoteuserhost = get_record('mnet_host', 'id', $USER->mnethostid);
        $mnetrequest->add_param($remoteuserhost->wwwroot, 'string');
    } else {
        $mnetrequest->add_param('anonymous', 'string');
        $mnetrequest->add_param($CFG->wwwroot, 'string');
    }

    // set $filters and $offset ad $page parameters
    $mnetrequest->add_param((array)$metadatafilters, 'struct');
    $mnetrequest->add_param($offset, 'int');
    $mnetrequest->add_param($page, 'int');
    
    // Do RPC call and store response
    if ($mnetrequest->send($remotepeer) === true) {
        $res = json_decode($mnetrequest->response);
        if ($res->status == RPC_SUCCESS){
            $fullresults = (array)$res->resources;
        }
    } else {
        $fullresults['entries'] = array();
        $fullresults['maxobjects'] = 0;
        foreach ($mnetrequest->error as $errormessage) {
            list($code, $message) = array_map('trim',explode(':', $errormessage, 2));
            $message .= "ERROR $code:<br/>$errormessage<br/>";
        }
        error("RPC mod/sharedresource/get_list:<br/>$message");
    }
    unset($mnetrequest);
    
    return $fullresults['entries'];
}

/**
*
*/
function resources_print_tools($course){
    global $CFG;
    
    if ($course){
        echo '<center>';
        $convertstr = get_string('resourceconversion', 'sharedresource');
        echo "<a href=\"{$CFG->wwwroot}/mod/sharedresource/admin_convertall.php?course={$course->id}\">$convertstr</a>";
        echo '</center>';
    }
}


/**
* print list of the selected resources
*/
function resources_browse_print_list(&$resources, &$course, $isediting = false, $repo = 'local'){
    global $CFG, $USER;

    $isremote = ($repo != 'local');
    $consumers = get_consumers();
    
    $courseid = (empty($course->id)) ? '' : $course->id;
    
    if ($resources){
        $i = 0;
        foreach($resources as $resource){
            
            if (!$isremote){
                // get local once
                $resource->uses = sharedresource_get_usages($resource, $response, null);
                if (!empty($consumers)){
                    // $resource->uses += sharedresource_get_usages($resource, $response, $consumers);
                }
                $reswwwroot = $CFG->wwwroot;
            } else {
    			$resource_host = get_record('mnet_host', 'id', $repo);
                $reswwwroot = $resource_host->wwwroot;
            }
            
            $commands = '';
            if ($isediting){
                $editstr = get_string('update');
                $deletestr = get_string('delete');
                $exportstr = get_string('export', 'sharedresource');
                $forcedeletestr = get_string('forcedelete');
                $commands = "<a href=\"{$CFG->wwwroot}/mod/sharedresource/edit.php?course=1&type=file&add=sharedresource&return=0&mode=update&entry_id={$resource->id}&insertinpage=0\" title=\"$editstr\"><img src=\"{$CFG->pixpath}/t/edit.gif\" /></a>";
                if ($resource->uses == 0){
                    $commands .= " <a href=\"index.php?what=delete&amp;course=$courseid&amp;id={$resource->id}\" title=\"$deletestr\"><img src=\"{$CFG->pixpath}/t/delete.gif\" /></a>";
                } else {
                    $commands .= " <a href=\"index.php?what=forcedelete&amp;course=$courseid&amp;id={$resource->id}\" title=\"$forcedeletestr\"><img src=\"{$CFG->pixpath}/t/delete.gif\" /></a>";
                }
                $commands .= " <a href=\"pushout.php?course={$courseid}&amp;resourceid={$resource->id}\" title=\"$exportstr\"><img src=\"{$CFG->wwwroot}/mod/sharedresource/pix/export.gif\" /></a>";
            }
            
            $icon = ($isremote) ? 'pix/remoteicon.gif' : 'icon.gif' ;
            $readnotice = get_string('readnotice', 'sharedresource');
            echo "<h3><img src=\"{$CFG->wwwroot}/mod/sharedresource/$icon\"/> <span class=\"title\">{$resource->title}</span> $commands</h3>";
            print_box_start('generalbox');
            echo "<a class=\"smalllink\" href=\"{$resource->url}\" target=\"_blank\">{$resource->url}</a><br/>";
            echo "<a class=\"smalllink\" href=\"{$reswwwroot}/mod/sharedresource/metadatanotice.php?identifier={$resource->identifier}&course={$courseid}\">{$readnotice}</a><br/>";
            echo '<span class="smalltext">'.get_string('keywords', 'sharedresource'). ": $resource->keywords</span><br/>";
            if (!empty($resource->description)){
                echo "<span class=\"description\">$resource->description</span><br/>";
            }

            echo get_string('used', 'sharedresource', $resource->uses).'</p>';
            if (!empty($course)){
                $addtocourse = get_string('addtocourse', 'sharedresource');
                $localizetocourse = get_string('localizetocourse', 'sharedresource');
                $addfiletocourse = get_string('addfiletocourse', 'sharedresource');
                if (!$isremote){
                    // if is local or already proxied
                    echo "<form name=\"add{$i}\" action=\"{$CFG->wwwroot}/mod/sharedresource/addlocaltocourse.php\" style=\"display:inline\">";
                } else {
                    // if is a true remote
                    echo "<form name=\"add{$i}\" action=\"{$CFG->wwwroot}/mod/sharedresource/addremotetocourse.php\" style=\"display:inline\" method=\"POST\" >";
                }
                echo "<input type=\"hidden\" name=\"id\" value=\"{$course->id}\" />";
                echo "<input type=\"hidden\" name=\"mode\" value=\"shared\" />";
                echo "<input type=\"hidden\" name=\"identifier\" value=\"{$resource->identifier}\" />";
                $desc = htmlentities($resource->description);
                echo "<input type=\"hidden\" name=\"description\" value=\"$desc\" />";
                $title = $resource->title;
                echo "<input type=\"hidden\" name=\"title\" value=\"$title\" />";
                echo "<input type=\"hidden\" name=\"provider\" value=\"$repo\" />";
                echo "<input type=\"hidden\" name=\"file\" value=\"{$resource->file}\" />";
                echo "<input type=\"hidden\" name=\"url\" value=\"{$resource->url}\" />";
                echo "</form>";
                echo "<div style=\"text-align:right\" class=\"commands\">";
                echo "<a href=\"javascript:document.forms['add{$i}'].submit();\">{$addtocourse}</a>";
                if (!empty($resource->file) || ($isremote && empty($resource->isurlproxy))){
                    echo " - <a href=\"javascript:document.forms['add{$i}'].mode.value = 'local';document.forms['add{$i}'].submit();\">{$localizetocourse}</a>";
                    echo " - <a href=\"javascript:document.forms['add{$i}'].mode.value = 'file';document.forms['add{$i}'].submit();\">{$addfiletocourse}</a>";
                }
			    if (file_exists($CFG->dirroot.'/blocks/activity_publisher/lib/activity_publisher.class.php')){
			    	include_once($CFG->dirroot.'/blocks/activity_publisher/lib/activity_publisher.class.php');
                	if (activity_publisher::is_activity_backup($resource->file)){
	                	$deployincourse = get_string('deployincourse', 'block_activity_publisher');
		                echo " - <a href=\"javascript:document.forms['add{$i}'].mode.value = 'deploy';document.forms['add{$i}'].submit();\">{$deployincourse}</a>";
		            }
	            }
                echo "</div>";
            }
            print_box_end();
            $i++;
        }
    } else {
        echo get_string('noresources', 'resources', '', $CFG->dirroot.'/resources/lang/');
    }
}

/**
* prints a pager for resource pages
* @param int $courseid the course context id. I null the library is browsed from non course area
* @param int $repo the repository ID
* @param int $nbrpages
*/
function resources_print_pager($courseid, $repo, $nbrpages, $page, $offset = 0, $isediting = false){
	echo '<center><b>';
	if($courseid){
		for($i = 1 ; $i <= $nbrpages ; $i++){
			$pageoffset = ($i - 1)*$page;
			$pagestyle = ($pageoffset == $offset) ? 'color:black;font-size:14pt' : 'color:grey;font-size:12pt' ;
			echo "<a style=\"{$pagestyle}\" name=\"page{$i}\" href=\"index.php?course={$courseid}&amp;repo={$repo}&amp;offset={$pageoffset}&amp;isediting={$isediting}\">$i</a>";
		}
	} else {
		for($i = 1 ; $i <= $nbrpages ; $i++){
			$pageoffset = ($i - 1)*$page;
			$pagestyle = ($pageoffset == $offset) ? 'color:black;font-size:14pt' : 'color:grey;font-size:12pt' ;
			echo "<a style=\"{$pagestyle}\" name=\"page{$i}\" href=\"index.php?repo={$repo}&amp;offset={$pageoffset}&amp;isediting={$isediting}\">$i</a>";
		}
	}
	echo '</center>';
}

/**
*
*/
function update_resourcepage_icon() {
    global $CFG, $USER;
    
    if (!isloggedin()) return '';

    if (!empty($USER->editing)) {
        $string = get_string('updateresourcepageoff', 'sharedresource');
        $edit = '0';
    } else {
        $string = get_string('updateresourcepageon', 'sharedresource');
        $edit = '1';
    }
    
    $return = "<form {$CFG->frametarget} method=\"get\" action=\"$CFG->wwwroot/resources/index.php\">";
    $return .= "<div>";
    $return .= "<input type=\"hidden\" name=\"edit\" value=\"$edit\" />";
    $return .= "<input type=\"submit\" value=\"$string\" />";
    $return .= "</div></form>";

    return $return;
}

/**
* Resources providers are mnet_hosts for which we have a subscription to its provider
* service
*/
function get_providers(){
    global $CFG;

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
            ms.name = 'resource_provider' AND
            h2s.subscribe = 1 AND
            mh.deleted = 0
    ";

    $providers = get_records_sql($sql);
    
    return $providers;
}

/**
* Resources consumers are mnet_hosts for which we have a subscription to its consumer service API
* service
*/
function get_consumers(){
    global $CFG;
    
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
            ms.name = 'resource_consumer' AND
            h2s.subscribe = 1 AND
            mh.deleted = 0           
    ";
    
    $consumers = get_records_sql($sql);

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
function sharedresource_get_usages($entry, &$response, $consumers = null, $user = null){
    global $USER;
    
    if (is_null($user)) {
        $user = $USER;
    }

    if (is_null($consumers)){
        $uses = count_records('sharedresource', 'identifier', $entry->identifier);
    } else {
        $uses = 0;
        if ($consumers){
            foreach($consumers as $consumer){

                // get the originating (ID provider) host info
                if (!$remotepeer = new mnet_peer()){
                    $response['error'][] = "MNET client initialisation error";
                }
                $remotepeer->set_wwwroot($consumer->wwwroot);
            
                // set up the RPC request
                $mnetrequest = new mnet_xmlrpc_client();
                $mnetrequest->set_method('mod/sharedresource/rpclib.php/sharedresource_rpc_check');
            
                // set $remoteuser and $remoteuserhost parameters
                $mnetrequest->add_param($user->username);
            
                $remoteuserhost = get_record('mnet_host', 'id', $user->mnethostid);
                $mnetrequest->add_param($remoteuserhost->wwwroot);
            
                // set $category and $resourceID parameter
                $mnetrequest->add_param($entry->identifier);
            
                // Do RPC call and store response
                if ($mnetrequest->send($remotepeer) === true) {
                    $uses += (int) json_decode($mnetrequest->response);
                } else {
                    foreach ($mnetrequest->error as $errormessage) {
                        list($code, $message) = array_map('trim',explode(':', $errormessage, 2));
                        $message .= " Callback ERROR $code:<br/>$errormessage<br/>";
                    }
                    $response['error'][] = "RPC mod/sharedresource/get_list:<br/>$message";
                }
                unset($mnetrequest);
            }
        }
    }
    return $uses;
}

/**
* submits a resource to a remote provider
*
*/
function sharedresource_submit($repo, $resourceentry){
    global $CFG;
    
    $remote_host = get_record('mnet_host', 'id', $repo);
    
    // get the originating (ID provider) host info
    if (!$remotepeer = new mnet_peer()){
        error ("MNET client initialisation error");
    }
    $remotepeer->set_wwwroot($remote_host->wwwroot);

    // set up the RPC request
    $mnetrequest = new mnet_xmlrpc_client();
    $mnetrequest->set_method('mod/sharedresource/rpclib.php/sharedresource_rpc_submit');

    // set $remoteuser and $remoteuserhost parameters
    if (!empty($USER->username)){
        $mnetrequest->add_param($USER->username);
        $remoteuserhost = get_record('mnet_host', 'id', $USER->mnethostid);
        $mnetrequest->add_param($remoteuserhost->wwwroot);
    } else {
        $mnetrequest->add_param('anonymous');
        $mnetrequest->add_param($CFG->wwwroot);
    }

    // set $category and $offset ad $page parameters
    $mnetrequest->add_param($resourceentry, 'struct');
    
    $metadata = get_records('sharedresource_metadata', 'entry_id', $resourceentry->id);

    $mnetrequest->add_param($metadata, 'array');

    // Do RPC call and store response
    if ($mnetrequest->send($remotepeer) === true) {
        $results = json_decode($mnetrequest->response);
        
        // print_object($results);

        if ($result->status == RPC_SUCCESS){

            // we need converting our local instance as a proxy
            if (!empty($resourceentry->file)){
                
                $file = $resourceentry->file;
                
                // convert local
                $resourceentry->url = $remote_host->wwwroot.'/resources/view.php?id='.$resourceentry->identifier;
                $resourceentry->file = '';
                $resourceentry->provider = resources_repo($remote_host->wwwroot);
                update_record('sharedresource', $resourceentry);
    
                // destroy local file
                $filename = $CFG->dataroot.SHAREDRESOURCE_RESOURCEPATH.$resourceentry->file;
                unlink($filename);
            }
        } else {
            error("RPC remote error in submit:<br/>{$status->error}");
        }
    } else {
        foreach ($mnetrequest->error as $errormessage) {
            list($code, $message) = array_map('trim',explode(':', $errormessage, 2));
            $message .= "ERROR $code:<br/>$errormessage<br/>";
        }
        error("RPC mod/sharedresource/get_list:<br/>$message");
    }
    unset($mnetrequest);
    
    return $results;
}

/**
* Temporarily (untill better choice) unbinds repo naming
* from hostnames
* // TODO : evaluate better strategies
*/
function resources_repo($wwwroot){
    global $CFG;
    
    if (preg_match("/https?:\\/\\/([^.]+)/", $wwwroot, $matches)){
        return $matches[1];
    }    

    return str_replace('http://', '', $wwwroot);    
}

/**
* setup visible search widgets depenging on metadata plugin and 
* user quality
* @param array ref $visiblewidgets an array to be filled by the function with objets reprensenting visible widgets
* @param object $context course or site context
*/
function resources_setup_widgets(&$visiblewidgets, $context){
	global $CFG;
	
    // setup the catalog view separating providers with tabs
	$plugins = sharedresource_get_plugins();
	$pluginname = $plugins[$CFG->pluginchoice]->pluginname;
	if(has_capability('mod/sharedresource:systemmetadata', $context)){
		$capability = 'system';
	}
	elseif(has_capability('mod/sharedresource:indexermetadata', $context)){
		$capability = 'indexer';
	}
	elseif(has_capability('mod/sharedresource:authormetadata', $context)){
		$capability = 'author';
	} else {
		error(get_string('noaccessform', 'sharedresource'));
	}
	
    if ($activewidgets = unserialize(@$CFG->activewidgets)){
		$count = 0;
		foreach($activewidgets as $key => $widget){
			if(record_exists_select('config_plugins', "name LIKE 'config_{$pluginname}_{$capability}_{$widget->id}'")){
				$count++;
				array_push($visiblewidgets, $widget);
			}
		}
    }
}

/**
* print widgets calling the adequate widget class instance
* @param int $courseid
* @param int $repo
* @param int $offset the record count offset of the current page
* @param object $context the current course or site context
* @param array ref $visiblewidgets an array of widgets to print
*/
function resources_print_search_widgets($courseid, $repo, $offset, $context, &$visiblewidgets, &$searchvalues){
	global $CFG;


	if(empty($visiblewidgets)){
		echo '<br/><center>'.get_string('nowidget', 'sharedresource').'</center><br/>';
	} else {
		echo "<form name=\"cat\" action=\"{$CFG->wwwroot}/resources/index.php\"style=\"display:inline\">";
		if ($courseid){
			echo "<input type=\"hidden\" name=\"course\" value=\"{$courseid}\">";
		}
		echo "<input type=\"hidden\" name=\"repo\" value=\"{$repo}\">";
		echo "<input type=\"hidden\" name=\"offset\" value=\"{$offset}\">";
		echo "<fieldset>";
		$searchstr = get_string('searchinlibrary', 'sharedresource');
		echo "<legend>$searchstr</legend>";
		echo '<table>';
		echo '<tr>';
		$n = 0;
		foreach($visiblewidgets as $key => $widget){
			echo '<td>';
			echo $widget->print_search_widget('column', @$searchvalues[$widget->id]);
			echo '</td>';
			$n++;
		}
		echo "</tr><tr><td colspan=\"{$n}\" align=\"center\">";
		$search = get_string('search');
		echo "<input type=\"submit\" name=\"go\" value=\"$search\" />";
		echo "</td></tr>";
		echo "</table>";
		echo "</fieldset>";
		echo "</form>";
	}
}

/**
* get search clauses from session and udate from incomming changes
*
*/
function resources_process_search_widgets(&$visiblewidgets, &$searchfields){
	global $CFG;

	$result = false;

	if(!empty($_GET) && !empty($CFG->activewidgets)){
		foreach($visiblewidgets as $key => $widget){
			$result = $result or $widget->catch_value($searchfields);
		}
	}	

	return $result;
}
?>