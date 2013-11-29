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
require_once $CFG->dirroot.'/mod/sharedresource/lib.php';
require_once $CFG->dirroot.'/mod/sharedresource/rpclib.php';
require_once($CFG->dirroot.'/mod/sharedresource/metadatalib.php');

if (!defined('RPC_SUCCESS')) {
    define('RPC_TEST', 100);
    define('RPC_SUCCESS', 200);
    define('RPC_FAILURE', 500);
    define('RPC_FAILURE_USER', 501);
    define('RPC_FAILURE_CONFIG', 502);
    define('RPC_FAILURE_DATA', 503);
    define('RPC_FAILURE_CAPABILITY', 510);
}

function cmp($a, $b){
    $a = preg_replace('@^(a|an|the) @', '', $a);
    $b = preg_replace('@^(a|an|the) @', '', $b);
    return strcasecmp($a, $b);
}

/**
* get a stub of local resources
*/
function get_local_resources($repo, &$fullresults, $metadatafilters = '', &$offset = 0, $page = 20){
    global $CFG, $USER,$DB;

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
            {sharedresource_entry} se
        $clause
        ORDER BY
           title
    ";
    $sqlcount = "
        SELECT
            COUNT(*)
        FROM
            {sharedresource_entry} se
        $clause
    ";
    
    // debug_trace('postsearch: '.$sql);
    $fullresults['maxobjects'] = $DB->count_records_sql($sqlcount);
    $fullresults['order'] = array();
    if ($offset >= $fullresults['maxobjects']) $offset = 0; // security when changing filter configuration
    $fullresults['entries'] = $DB->get_records_sql($sql, array(), $offset, $page);

	if (!empty($fullresults['entries'])){
		foreach($fullresults['entries'] as $id => $r){
		    if ($metadata = $DB->get_records('sharedresource_metadata', array('entry_id' => $id), 'element', 'element, namespace, value')){
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
    global $CFG, $USER, $DB;
    
    if ($repo == 'local') print_error('errorrepoprogramming');    
    
    $remote_host = $DB->get_record('mnet_host', array('id' => $repo));
    
    // get the originating (ID provider) host info
    if (!$remotepeer = new mnet_peer()){
        print_error('errormnetpeer', 'local_sharedresources');
    }
    $remotepeer->set_wwwroot($remote_host->wwwroot);

    // set up the RPC request
    $mnetrequest = new mnet_xmlrpc_client();
    $mnetrequest->set_method('mod/sharedresource/rpclib.php/sharedresource_rpc_get_list');

    // set $remoteuser and $remoteuserhost parameters
    if (!empty($USER->username)){
        $mnetrequest->add_param($USER->username, 'string');
        $remoteuserhost = $DB->get_record('mnet_host', array('id'=> $USER->mnethostid));
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
        print_error("RPC mod/sharedresource/get_list:<br/>$message");
    }
    unset($mnetrequest);
    
    return $fullresults['entries'];
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
    global $CFG,$DB;

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
function get_consumers(){
    global $CFG,$DB;
    
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
* fetch remotely or locally amount of usages about a resource.
* @uses $USER
* @param object $entry an sharedresource entry
* @param object $response an array for aggregating error messages
* @param array $consumers an array of available resource consumers. If not provided, will check localy.
* @param object $user an eventual user on behalf to whom asking for usage check.
* @return a count for how many times the resource was used
*/
function sharedresource_get_usages($entry, &$response, $consumers = null, $user = null){
    global $USER,$DB;
    
    if (is_null($user)) {
        $user = $USER;
    }

    if (is_null($consumers)){
        $uses = $DB->count_records('sharedresource', array('identifier'=> $entry->identifier));
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
            
                $remoteuserhost = $DB->get_record('mnet_host', array('id'=> $user->mnethostid));
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
*
*/
function sharedresource_submit($repo, $resourceentry){
    global $CFG,$DB;
    
    $remote_host = $DB->get_record('mnet_host', array('id'=> $repo));
    
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
        $remoteuserhost = $DB->get_record('mnet_host',array('id', $USER->mnethostid));
        $mnetrequest->add_param($remoteuserhost->wwwroot);
    } else {
        $mnetrequest->add_param('anonymous');
        $mnetrequest->add_param($CFG->wwwroot);
    }

    // set $category and $offset ad $page parameters
    $mnetrequest->add_param($resourceentry, 'struct');
    
    $metadata = $DB->get_records('sharedresource_metadata', array('entry_id' => $resourceentry->id));

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
                $DB->update_record('sharedresource', $resourceentry);
    
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
	global $CFG,$DB;
	
    // setup the catalog view separating providers with tabs
	$plugins = sharedresource_get_plugins();
	$pluginname = $plugins[$CFG->pluginchoice]->pluginname;
	if(has_capability('repository/sharedresources:systemmetadata', $context)){
		$capability = 'system';
	}
	elseif(has_capability('repository/sharedresources:indexermetadata', $context)){
		$capability = 'indexer';
	}
	elseif(has_capability('repository/sharedresources:authormetadata', $context)){
		$capability = 'author';
	} else {
		error(get_string('noaccessform', 'sharedresource'));
	}
	
    if ($activewidgets = unserialize(@$CFG->activewidgets)){
		$count = 0;
		foreach($activewidgets as $key => $widget){
			if($DB->record_exists_select('config_plugins', "name LIKE 'config_{$pluginname}_{$capability}_{$widget->id}'")){
				$count++;
				array_push($visiblewidgets, $widget);
			}
		}
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

function resources_get_string($identifier, $subplugin, $a = '', $lang = ''){
	global $CFG;
	
	static $string = array();
	
	if (empty($lang)) $lang = current_language();

	list($type, $plug) = explode('_', $subplugin);
	
	include $CFG->dirroot.'/local/sharedresources/db/subplugins.php';
	
	if (!isset($plugstring[$plug])){
		if (file_exists($CFG->dirroot.'/'.$subplugins[$type].'/'.$plug.'/lang/en/'.$subplugin.'.php')){
			include $CFG->dirroot.'/'.$subplugins[$type].'/'.$plug.'/lang/en/'.$subplugin.'.php';
		} else {
            debugging("English lang file must exist", DEBUG_DEVELOPER);
		}

		// override with lang file if exists
		if (file_exists($CFG->dirroot.'/'.$subplugins[$type].'/'.$plug.'/lang/'.$lang.'/'.$subplugin.'.php')){
			include $CFG->dirroot.'/'.$subplugins[$type].'/'.$plug.'/lang/'.$lang.'/'.$subplugin.'.php';
		} else {
			$string = array();
		}
		$plugstring[$plug] = $string;
	}

	if (array_key_exists($identifier, $plugstring[$plug])){
		$result = $plugstring[$plug][$identifier];
        if ($a !== NULL) {
            if (is_object($a) or is_array($a)) {
                $a = (array)$a;
                $search = array();
                $replace = array();
                foreach ($a as $key=>$value) {
                    if (is_int($key)) {
                        // we do not support numeric keys - sorry!
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
	    // Debugging feature lets you display string identifier and component
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
*
*/
function sharedresource_is_lti($resource){
	global $CFG;
	
	if (preg_match('/LTI/', $resource->keywords)) return true;
	
	return false;
}

/**
* get top ranking keywords from metadata
* @TODO : turn implementation to more portable IN() statement
*/
function sharedresource_get_top_keywords($courseid){
	global $DB, $CFG;
	
	$contexts[] = 1;
	
	// get all categories on the way to root 
	if ($courseid > SITEID){
		$catid = $DB->get_field('course', 'category', array('id' => $courseid));
		$cat = $DB->get_record('course_categories', array('id' => $catid));
		$catcontext = context_coursecat::instance($cat->id);
		$contexts[] = $catcontext->id;
		while($cat->parent){
			$cat = $DB->get_record('course_categories', array('id' => $cat->parent));
			$catcontext = context_coursecat::instance($cat->id);
			$contexts[] = $catcontext->id;
		}
	}
	
	$contextlist = implode(',', $contexts);
		
	$object = 'sharedresource_plugin_'.$CFG->pluginchoice;
	$mtdstandard = new $object;
	
	$kwelement = $mtdstandard->getKeywordElement();
	
	$topranksize = 20;
	
	$sql = "
		SELECT
			value,
			COUNT(DISTINCT entry_id) as rank
		FROM
			{sharedresource_metadata} shm,
			{sharedresource_entry} sh
		WHERE
			shm.entry_id = sh.id AND
			sh.context IN ('{$contextlist}') AND
			element LIKE '{$kwelement->name}:%' AND
			namespace = '{$CFG->pluginchoice}' AND
			value IS NOT NULL AND
			value != ''
		GROUP BY 
			value
		ORDER BY
			rank DESC			
		LIMIT 
			0, $topranksize
	";
	
	$topkws = $DB->get_records_sql($sql, array());
	
	return $topkws;
	
}

/**
* A recursive path explorator for building import information from physical directory
* @param $path the local path for each iteration
* @param $importlines the aray of descriptors being built by the recursion
* @param $data the initial recursion start information non mutable
*/
function sharedresources_scan_importpath($path, &$importlines, &$METADATA, &$data){
	global $CFG;
	
	if(is_dir($path)){
		
		if (file_exists($path.'/metadata.csv')){
			$metadata = file($path.'/metadata.csv');
			sharedresources_parse_metadata($metadata, $METADATA, $path);
		}

		// process an optional alias file for taxonomy tokens
		$ALIASES = array();
		if (file_exists($data->importpath.'/taxonomy_aliases.txt')){
			$aliases = file($data->importpath.'/taxonomy_aliases.txt');
			foreach($aliases as $aliasline){
				list($from, $to) = explode('=', chop($aliasline));
				$ALIASES[rtrim($from)] = ltrim($to);
			}
		}
		
		// apply overriding aliases to taxonomy
		if (!function_exists('alias_taxon_tokens')){
			function alias_taxon_tokens(&$item, $k, $aliases){
				if (array_key_exists($item, $aliases)){
					$item = $aliases[$item];
				}
			}
		}

		$taxonparts = null;
		if (!empty($data->deducetaxonomyfrompath)){
			// get relative path
			$cleanedpath = str_replace($data->importpath, '', $path);
			if (!empty($cleanedpath)){
				$cleanedpath = preg_replace('/^\//', '', $cleanedpath);
				// split into parts
				$taxonparts = explode('/', $cleanedpath);
				array_walk($taxonparts, 'alias_taxon_tokens', $ALIASES);
			}
		}
		
		$DIR = opendir($path);
		
		while($entry = readdir($DIR)){
			if (preg_match('/^\\./', $entry)) continue;
			if (preg_match('/(CVS|SVN)/', $entry)) continue;
			// if (!is_readable($path.'/'.$entry)) continue;
			$entry = iconv("ISO-8859-1", "UTF-8//TRANSLIT", $entry);
			if (is_dir($path.'/'.$entry)){
				sharedresources_scan_importpath($path.'/'.$entry, $importlines, $METADATA, $data);
			} else {
				if (preg_match('/^__/', $entry)) continue; // skip any already processed file
				if ($entry == "metadata.csv") continue; // skip any metadata add on
				if ($entry == "taxonomy_aliases.txt") continue; // skip any taxonomy translator add on
				if ($entry == "moodle_sharedlibrary_import.log") continue;
				if (!empty($excludepattern)){
					if (!preg_match('/'.$data->importexclusionpattern.'/', $entry)) $importlines[] = $path.'/'.$entry;
				} else {
					$importlines[] = $path.'/'.$entry;
				}
				
				// add taxonomy to metadata
				if (!empty($taxonparts)){
					$METADATA[$path.'/'.$entry]['taxonomy'] = implode(', ', $taxonparts);
				}
			}
		}
		
		closedir($DIR);
	}	
}

/**
* parses some metadata in the metadata import file
*
*
*/
function sharedresources_parse_metadata(&$metadata, &$METADATA, $path){
	
	$AUTHORIZED = array('file', 'title', 'description', 'keywords', 'language', 'authors', 'contributors', 'documenttype', 'documentnature', 'pedagogictype', 'difficulty');

	$hl = array_shift($metadata);
	while($hl && preg_match('/^(\s|\/\/|#|$)/', $hl)){
		$hl = array_shift($metadata);
	}

	$header = explode(';', chop($hl));
	$linesize = count($header);
	
	if ($header[0] != 'file'){
		echo "First field name must be file. This metadata file is malformed. Skipping all metadata.";
		return;
	}


	$unauthorized = array();
	foreach($header as $column){
		if (!in_array($column, $AUTHORIZED)){
			$unauthorized[] = $column;
		}

	}

	if ($unauthorized){
		echo "Unauthorized columns in file header: ".implode(', ', $unauthorized);
		return;
	}

	$i = 1;
	foreach($metadata as $l){
		if (preg_match('/^(\s|\/\/|#|$)/', $hl)) continue; // skip comments, empty lines
		$l = chop($l);
		
		$line = explode(';', $l);
		if (count($line) != $linesize){
			echo "Bad count in $path at line $i: ignoring...<br/>\n";
			continue;
		}
		
		$j = 0;
		$mtd = array();
		foreach($line as $field){
			if (!$j){ // first field is filename
				$filename = $field;
			}
			
			$mtd[$header[$j]] = $field;
			
			$j++;
		}
		$METADATA[$path.'/'.$filename] = $mtd;

		$i++;		
	}	
}

function sharedresources_reset_volume($data){
	global $CFG;
	
	$path = $data->importpath;
	
	if (file_exists($path.'/moodle_sharedlibrary_import.log')){
		unlink ($path.'/moodle_sharedlibrary_import.log');
	}
	$r = 0;
	sharedresources_reset_volume_rec($path, $r);
	
	return get_string('reinitialized', 'local_sharedresources', $r);
}

function sharedresources_reset_volume_rec($path, &$r){

	if (!is_dir($path)){
		return;
	}

	$DIR = opendir($path);	
	while($entry = readdir($DIR)){
		if (preg_match('/^\\./', $entry)) continue;
		if (preg_match('/(CVS|SVN)/', $entry)) continue;
		// if (!is_readable($path.'/'.$entry)) continue;
		$entry = iconv("ISO-8859-1", "UTF-8//TRANSLIT", $entry);
		if (is_dir($path.'/'.$entry)){
			sharedresources_reset_volume_rec($path.'/'.$entry, $r);
		} else {
			if (preg_match('/^__(.*)/', $entry, $matches)){
				$unmarked = $matches[1];
				rename($path.'/'.$entry, $path.'/'.$unmarked);
				$r++;
			}
		}	
	}
	closedir($DIR);	
}

/**
* Renames an imported file so it would not be imported twice when
* replaying an import.
*/
function sharedresources_mark_file_imported($path){
	
	$parts = pathinfo($path);	
	$newname = $parts['dirname'].'/__'.$parts['basename'];
	rename($path, $newname);
}


/**
* this method combines the file list an metadata to build adequate descriptors
* for the import processor.
*
*/
function sharedresources_aggregate($importlist, &$METADATA){
	
	$aggregatedlist = array();
	
	foreach($importlist as $entry){
		if (array_key_exists($entry, $METADATA)){
			$descriptor = $METADATA[$entry];
			$descriptor['fullpath'] = $entry;
		} else {
			$descriptor = array();
			$descriptor['fullpath'] = $entry;
			$descriptor['file'] = pathinfo($entry, PATHINFO_FILENAME);
			$descriptor['title'] = basename($entry);
		}
		$aggregatedlist[] = $descriptor;
	}
	
	return $aggregatedlist;
}

/**
* checks if a user has a some named capability effective somewhere in a course.
*/
function sharedresource_has_capability_somewhere($capability, $excludesystem = true, $excludesite = true, $fromcategorycontext = null, $doanything = false){
	global $USER;

	if (!$fromcategorycontext){
		// this will not be very efficient
		$hassome = get_user_capability_course($capability, $USER->id, false); 
		if ($excludesite && !empty($hassome) && array_key_exists(SITEID, $hassome)){
			unset($hassome[SITEID]);
		}
		if (!empty($hassome)){
			return true;
		}
		
		$systemcontext = context_system::instance();
		if (!$excludesystem && has_capability($capability, $systemcontext, $USER->id, $doanything)){
			return true;
		}
	} else {
		// return as soon as we can
		if (has_capability($capability, $fromcategorycontext)) return true;
		if ($allsubcontexts = $DB->get_records_select('context', " path LIKE '{$fromcategorycontext->path}/%' ")){
			foreach($allsubcontexts as $sc){
				$c = context::create_instance_from_record($sc);
				if (has_capability($capability, $c)) return true;
			}
		}
	}
	
	return false;
}
