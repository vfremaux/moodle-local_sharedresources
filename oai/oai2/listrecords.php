<?php
/*
* +----------------------------------------------------------------------+
* | PHP Version 4                                                        |
* +----------------------------------------------------------------------+
* | Copyright (c) 2002-2005 Heinrich Stamerjohanns                       |
* |                                                                      |
* | listrecords.php -- Utilities for the OAI Data Provider               |
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
* /            stamer@uni-oldenburg.de                                   |
* +----------------------------------------------------------------------+
*/
//
// $Id: listrecords.php,v 1.2 2012-01-05 20:58:04 vf Exp $
//
global $DB;
global $OAI;

if (!isset($OAI)) $OAI = new StdClass;

// parse and check arguments
foreach($args as $key => $val) {

	switch ($key) { 
		case 'from':
			// prevent multiple from
			if (!isset($OAI->from)) {
				$OAI->from = $val;
			} else {
				$errors .= oai_error('badArgument', $key, $val);
			}
			break;

		case 'until':
			// prevent multiple until
			if (!isset($OAI->until)) {
				$OAI->until = $val; 
			} else {
				$errors .= oai_error('badArgument', $key, $val);
			}
			break;

		case 'metadataPrefix':
			if (is_array($METADATAFORMATS[$val])
					&& isset($METADATAFORMATS[$val]['myhandler'])) {
				$OAI->metadataPrefix = $val;
				$inc_record  = $METADATAFORMATS[$val]['myhandler'];
			} else {
				$errors .= oai_error('cannotDisseminateFormat', $key, $val);
			}
			break;

		case 'set':
			if (oai_find_set($val)) {
				$OAI->set = $val;
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
		if (is_file("tokens/re-$resumptionToken")) {
			$fp = fopen("tokens/re-$resumptionToken", 'r');
			$filetext = fgets($fp, 255);
			$textparts = explode('#', $filetext); 
			$deliveredrecords = (int)$textparts[0]; 
			$extquery = $textparts[1];
			$OAI->metadataPrefix = $textparts[2];
			if (is_array($METADATAFORMATS[$OAI->metadataPrefix])
					&& isset($METADATAFORMATS[$OAI->metadataPrefix]['myhandler'])) {
				$inc_record  = $METADATAFORMATS[$OAI->metadataPrefix]['myhandler'];
			} else {
				$errors .= oai_error('cannotDisseminateFormat', $key, $val);
			}
			fclose($fp); 
			//unlink ("tokens/re-$resumptionToken");
		} else { 
			$errors .= oai_error('badResumptionToken', '', $OAI->resumptionToken); 
		}
	}
}
// no, we start a new session
else {
	$deliveredrecords = 0; 
	if (!isset($args['metadataPrefix'])) {
		$errors .= oai_error('missingArgument', 'metadataPrefix');
	}

	$extquery = '';

    // this needs to be made first for having form and untill queries defined
    if (isset($args['set'])) {
        include oai_find_set($args['set']);
    } else {
        $OAI->set = $default_set;
        include oai_find_set($OAI->set);
	}

	if (isset($args['from'])) {
		if (!checkDateFormat($OAI->from)) {
			$errors .= oai_error('badGranularity', 'from', $OAI->from); 
		}
		$extquery .= fromQuery($OAI->from);
	}

	if (isset($args['until'])) {
		if (!checkDateFormat($OAI->until)) {
			$errors .= oai_error('badGranularity', 'until', $OAI->until);
		}
		$extquery .= untilQuery($OAI->until);
	}

}

// Get records and process list
if (empty($errors)) {
	$query = selectallQuery('') . $extquery;
	if (!$res = $DB->get_records_sql($query)){
	    $errors .= oai_error('noRecordsMatch'); 
	}

    if (empty($res)){
        $errors .= oai_error('noRecordsMatch'); 
    }
}

// break and clean up on error
if ($errors != '') {
	oai_exit();
}

$output .= " <ListRecords>\n";
$num_rows = count($res);

// Will we need a ResumptionToken?
if ($num_rows - $deliveredrecords > $MAXRECORDS) {
	$token = get_token(); 
	$fp = fopen ("tokens/re-$token", 'w'); 
	$thendeliveredrecords = (int)$deliveredrecords + $MAXRECORDS;  
	fputs($fp, "$thendeliveredrecords#"); 
	fputs($fp, "$extquery#"); 
	fputs($fp, "{$OAI->metadataPrefix}#"); 
	fclose($fp); 
	$restoken = 
'  <resumptionToken expirationDate="'.$expirationdatetime.'"
     completeListSize="'.$num_rows.'"
     cursor="'.$deliveredrecords.'">'.$token."</resumptionToken>\n"; 
}
// Last delivery, return empty ResumptionToken
elseif (isset($args['resumptionToken'])) {
	$restoken =
'  <resumptionToken completeListSize="'.$num_rows.'"
     cursor="'.$deliveredrecords.'"></resumptionToken>'."\n";
}

$maxrec = min($num_rows - $deliveredrecords, $MAXRECORDS);

// return records
$countrec  = 0;
foreach ($res as $recordobj) {
    
    $record = get_object_vars($recordobj);
    
    $countrec++;
    if ($countrec > $maxrec) break;

	$identifier = $oaiprefix.$record['oaiid'];
	$datestamp = formatDatestamp($record['datestamp']);
	 
	if (!empty($record['deleted']) && ($deletedRecord == 'transient' || $deletedRecord == 'persistent')) {
		$status_deleted = TRUE;
	} else {
		$status_deleted = FALSE;
	}

	$output .= '  <record>'."\n";
	$output .= '   <header>'."\n";
	$output .= xmlformat($identifier, 'identifier', '', 4);
	$output .= xmlformat($datestamp, 'datestamp', '', 4);
	if (!$status_deleted) 
		// use xmlrecord since we use stuff from database
		$output .= xmlrecord($record['set'], 'setSpec', '', 4);

	$output .= '   </header>'."\n"; 

    // return the metadata record itself
	if (!$status_deleted)
		include('oai2/'.$inc_record);

	$output .= '  </record>'."\n";   
}

// ResumptionToken
if (isset($restoken)) {
	$output .= $restoken;
}

// end ListRecords
$output .= 
' </ListRecords>'."\n";
  
?>
