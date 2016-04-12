<?php
/*
* +----------------------------------------------------------------------+
* | PHP Version 4                                                        |
* +----------------------------------------------------------------------+
* | Copyright (c) 2002-2005 Heinrich Stamerjohanns                       |
* |                                                                      |
* | getrecord.php -- Utilities for the OAI Data Provider                 |
* |                                                                      |
* | This is free software; you can redistribute it and/or modify it under|
* | the terms of the GNU General Public License as published by the      |
* | Free Software Foundation; either version 2 of the License, or (at    |
* | your option) any later version.                                      |
* | This software is distributed in the hope that it will be useful, but |
* | WITHOUT  ANY WARRANTY; without even the implied warranty of          |
* | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the         |
* | GNU General Public License for more details.                         |
* | You should have received a copy of the GNU General Public License    |
* | along with  software; if not, write to the Free Software Foundation, |
* | Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA         |
* |                                                                      |
* +----------------------------------------------------------------------+
* | Derived from work by U. Müller, HUB Berlin, 2002                     |
* |                                                                      |
* | Written by Heinrich Stamerjohanns, May 2002                          |
* |            stamer@uni-oldenburg.de                                   |
* +----------------------------------------------------------------------+
*/
//
// $Id: getrecord.php,v 1.2 2012-01-05 20:58:02 vf Exp $
//
global $DB;

// parse and check arguments
foreach ($args as $key => $val) {

    switch ($key) { 
        case 'identifier':
            $identifier = $val; 
            if (!is_valid_uri($identifier)) {
                $errors .= oai_error('badArgument', $key, $val);
            }
            break;

        case 'metadataPrefix':
            if (is_array($METADATAFORMATS[$val])
                    && isset($METADATAFORMATS[$val]['myhandler'])) {
                $metadataPrefix = $val;
                $inc_record  = $METADATAFORMATS[$val]['myhandler'];
            } else {
                $errors .= oai_error('cannotDisseminateFormat', $key, $val);
            }
            break;
            
        case 'set':
            break;

        default:
            $errors .= oai_error('badArgument', $key, $val);
    }
}

if (!isset($args['identifier'])) {
    $errors .= oai_error('missingArgument', 'identifier');
}
if (!isset($args['metadataPrefix'])) {
    $errors .= oai_error('missingArgument', 'metadataPrefix');
} 

// choose a set or the default set
if (isset($args['set'])) {
    include oai_find_set($args['set']);
} else {
    $OAI->set = $default_set;
    include oai_find_set($OAI->set);
}

// remove the OAI part to get the identifier
if (empty($errors)) {
    $id = str_replace($oaiprefix, '', $identifier); 
    if ($id == '') {
        $errors .= oai_error('idDoesNotExist', '', $identifier);
    }

    $query = selectAllQuery($id); 
    if (!$res = $DB->get_record_sql($query)) {
            $errors .= oai_error('idDoesNotExist', '', $identifier); 
    }

    if (empty($res)) {
        $errors .= oai_error('idDoesNotExist', '', $identifier); 
    }
}

// break and clean up on error
if ($errors != '') {
    oai_exit();
}

$output .= "  <GetRecord>\n";

$num_rows = count($res);
if ($num_rows) {
    $record = (array)$res;
    
    $identifier = $oaiprefix.$record['oaiid'];

    $datestamp = formatDatestamp($record['datestamp']); 

    if (isset($record['deleted']) && ($record['deleted'] == 'true') && 
        ($deletedRecord == 'transient' || $deletedRecord == 'persistent')) {
        $status_deleted = TRUE;
    } else {
        $status_deleted = FALSE;
    }

// print Header
    $output .= 
'  <record>'."\n";
    $output .= 
'  <header';
    if ($status_deleted) {
        $output .= ' status="deleted"';
    }  
    $output .='>'."\n";

    // use xmlrecord since we include stuff from database;
    $output .= xmlrecord($identifier, 'identifier', '', 3);
    $output .= xmlformat($datestamp, 'datestamp', '', 3);
    if (!$status_deleted) 
        $output .= xmlrecord($record['set'], 'setSpec', '', 3);
    $output .= 
'   </header>'."\n"; 

// return the metadata record itself
    if (!$status_deleted) 
        include('oai2/'.$inc_record); 

    $output .= 
'  </record>'."\n"; 
} 
else {
    // we should never get here
    oai_error('idDoesNotExist');
}

// End GetRecord
$output .= 
' </GetRecord>'."\n"; 
?>
