<?php
/*
* +----------------------------------------------------------------------+
* | PHP Version 4                                                        |
* +----------------------------------------------------------------------+
* | Copyright (c) 2002-2005 Heinrich Stamerjohanns                       |
* |                                                                      |
* | listidentifiers.php -- Utilities for the OAI Data Provider           |
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
// $Id: listidentifiers.php,v 1.2 2012-01-05 20:58:03 vf Exp $
//

global $OAI;
global $DB;

if (!isset($OAI)) $OAI = new StdClass;

// parse and check arguments
foreach($args as $key => $val) {

    // TODO remove those unusefull checks and 
    // perform real parameter value format check

	switch ($key) { 
		case 'from':
			$OAI->from = $val;
			break;

		case 'until':
			$OAI->until = $val; 
			break;

		case 'set':
			if (oai_find_set($val)) {
				$OAI->set = $val;
			} else {
				$errors .= oai_error('badArgument', $key, $val);
			}
			break;      

		case 'metadataPrefix':
			if (!isset($OAI->metadataPrefix)) {
				if (array_key_exists($val, $METADATAFORMATS) && is_array($METADATAFORMATS[$val]) 
					&& isset($METADATAFORMATS[$val]['myhandler'])) {
					$OAI->metadataPrefix = $val;
					$inc_record  = $METADATAFORMATS[$val]['myhandler'];
				} else {
					$errors .= oai_error('cannotDisseminateFormat', $key, $val);
				}
			} else {
				$errors .= oai_error('badArgument', $key, $val);
			}
			break;

		case 'resumptionToken':
			if (!isset($OAI->resumptionToken)) {
				$OAI->resumptionToken = $val;
			} else {
				$errors .= oai_error('badArgument', $key, $val);
			}
			break;

		default:
			$errors .= oai_error('badArgument', $key, $val);
	}
}

// Resume previous session?
if (isset($args['resumptionToken'])) {            
	if (count($args) > 1) {
		// overwrite all other errors
		$errors = oai_error('exclusiveArgument');
	} else {
		if (is_file("tokens/id-$resumptionToken")) {
			$fp = fopen("tokens/id-$resumptionToken", 'r');
			$filetext = fgets($fp, 255);
			$textparts = explode('#', $filetext);
			$deliveredrecords = (int)$textparts[0];
			$extquery = $textparts[1];
			$metadataPrefix = $textparts[2];
			fclose($fp); 
			unlink ("tokens/id-$resumptionToken");
		} else {
			$errors .= oai_error('badResumptionToken', '', $resumptionToken);
		}
	}
} else {
    // no, new session
	$deliveredrecords = 0;
	$extquery = '';

	if (!isset($args['metadataPrefix'])) {
		$errors .= oai_error('missingArgument', 'metadataPrefix');
	}

	if (isset($args['from'])) {
		if (!checkDateFormat($from)) {
			$errors .= oai_error('badGranularity', 'from', $from);
		}
		$extquery .= fromQuery($from);     
	}

    if (isset($args['until'])) {
	    if (!checkDateFormat($until)) {
		    $errors .= oai_error('badGranularity', 'until', $until); 
	    }
	    $extquery .= untilQuery($until);
    }

    if (isset($args['set'])) {
        include oai_find_set($args['set']);
    } else {
        $OAI->set = $default_set;
        include oai_find_set($OAI->set);
	}
}

if (empty($errors)) {
	$query = idQuery();
	if (!$res = $DB->get_records_sql($query)){
    	oai_exit();
	}
} else {
	oai_exit();
}

$num_rows = count($res);

/// Generate OAI output

$output .= " <ListIdentifiers>\n";

// Will we need a ResumptionToken?
if ($num_rows - $deliveredrecords > $MAXIDS) {
	$token = get_token(); 
	$fp = fopen ("tokens/id-$token", 'w');
	$thendeliveredrecords = (int)$deliveredrecords + $MAXIDS;
	fputs($fp, "$thendeliveredrecords#"); 
	fputs($fp, "$extquery#"); 
	fclose($fp); 
	$restoken = 
'  <resumptionToken expirationDate="'.$expirationdatetime.'"
     completeListSize="'.$num_rows.'"
     cursor="'.$deliveredrecords.'">'.$token."</resumptionToken>\n";
}
// Last delivery, return empty ResumptionToken
elseif (isset($set_resumptionToken)) {
	$restoken = 
'  <resumptionToken completeListSize="'.$num_rows.'"
     cursor="'.$deliveredrecords.'"></resumptionToken>'."\n";
}

$maxrec = min($num_rows - $deliveredrecords, $MAXIDS);
$countrec = 0;

foreach ($res as $record) {
    $countrec++;

	$identifier = $oaiprefix.$record->oaiid; 
	$datestamp = formatDatestamp($record->datestamp); 

	if (isDeleted($record->oaiid) && ($deletedRecord == 'transient' || $deletedRecord == 'persistent')) {
		$status_deleted = TRUE;
	} else {
		$status_deleted = FALSE;
	}


	$output .= 
'  <header';
	if ($status_deleted) {
		$output .= ' status="deleted"';
	}  
	$output .='>'."\n";

	// use xmlrecord since we use stuff from database
	$output .= xmlrecord($identifier, 'identifier', '', 3);
	$output .= xmlformat($datestamp, 'datestamp', '', 3);
	if (!$status_deleted) 
		$output .= xmlrecord($record->set, 'setSpec', '', 3);
	$output .= '  </header>'."\n"; 
}

// ResumptionToken
if (isset($restoken)) {
	$output .= $restoken;
}

$output .= " </ListIdentifiers>\n"; 
