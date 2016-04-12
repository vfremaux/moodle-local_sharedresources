<?php
/*
* +----------------------------------------------------------------------+
* | PHP Version 4                                                        |
* +----------------------------------------------------------------------+
* | Copyright (c) 2002-2005 Heinrich Stamerjohanns                       |
* |                                                                      |
* | oaidp-config.php -- Configuration of the OAI Data Provider           |
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
// $Id: oaidp-config.php,v 1.2 2012-01-05 20:57:59 vf Exp $
//

/* 
 * This is the configuration file for the PHP OAI Data-Provider.
 * Please read through the WHOLE file, there are several things, that 
 * need to be adjusted:

 - where to find the PEAR classes (look for PEAR SETUP)
 - parameters for your database connection (look for DATABASE SETUP)
 - the name of the table where you store your data
 - the encoding your data is stored (all below DATABASE SETUP)
*/

// To install, test and debug use this    
// If set to TRUE, will die and display query and database error message
// as soon as there is a problem. Do not set this to TRUE on a production site,
// since it will show error messages to everybody.
// If set FALSE, will create XML-output, no matter what happens.
$SHOW_QUERY_ERROR = FALSE;

// The content-type the WWW-server delivers back. For debug-puposes, "text/plain" 
// is easier to view. On a production site you should use "text/xml".
$CONTENT_TYPE = 'Content-Type: text/plain';

// If everything is running ok, you should use this
// $SHOW_QUERY_ERROR = FALSE;
//$CONTENT_TYPE = 'Content-Type: text/xml';

// do not change
$MY_URI = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'];

// MUST (only one)
$repositoryName       = $SITE->fullname;
$baseURL              = $CFG->wwwroot.'/local/sharedresources/oai/oai2.php';

// do not change
$protocolVersion      = '2.0';

// How your repository handles deletions
// no:             The repository does not maintain status about deletions.
//                It MUST NOT reveal a deleted status.
// persistent:    The repository persistently keeps track about deletions 
//                with no time limit. It MUST consistently reveal the status
//                of a deleted record over time.
// transient:   The repository does not guarantee that a list of deletions is 
//                maintained. It MAY reveal a deleted status for records.
// 
// If your database keeps track of deleted records change accordingly.
// Currently if $record['deleted'] is set to 'true', $status_deleted is set.
// Some lines in listidentifiers.php, listrecords.php, getrecords.php  
// must be changed to fit the condition for your database.
$deletedRecord        = 'no'; 

// MAY (only one)
//granularity is days
//$granularity          = 'YYYY-MM-DD';
// granularity is seconds
$granularity          = 'YYYY-MM-DDThh:mm:ssZ';

// MUST (only one)
// the earliest datestamp in your repository,
// please adjust
$earliestDatestamp    = '2008-01-01';

// this is appended if your granularity is seconds.
// do not change
if ($granularity == 'YYYY-MM-DDThh:mm:ssZ') {
    $earliestDatestamp .= 'T00:00:00.00Z';
}

// MUST (multiple)
$adminEmail            = $CFG->supportemail; 

// MAY (multiple) 
// Comment out, if you do not want to use it.
// Currently only gzip is supported (you need output buffering turned on, 
// and php compiled with libgz). 
// The client MUST send "Accept-Encoding: gzip" to actually receive 
// compressed output.
$compression        = array('gzip');

// MUST (only one)
// should not be changed
$delimiter            = ':';

// MUST (only one)
// You may choose any name, but for repositories to comply with the oai 
// format for unique identifiers for items records. 
// see: http://www.openarchives.org/OAI/2.0/guidelines-oai-identifier.htm
// Basically use domainname-word.domainname
// please adjust
preg_match('/http:\/\/[^.]+([^\:]+)/', $CFG->wwwroot, $matches);
$domain = $matches[1];

$repositoryIdentifier = 'ressources'.$domain; 

// description is defined in identify.php 
$show_identifier = true;

// You may include details about your community and friends (other
// data-providers).
// Please check identify.php for other possible containers 
// in the Identify response

// maximum mumber of the records to deliver
// (verb is ListRecords)
// If there are more records to deliver
// a ResumptionToken will be generated.
$MAXRECORDS = 50;

// maximum mumber of identifiers to deliver
// (verb is ListIdentifiers)
// If there are more identifiers to deliver
// a ResumptionToken will be generated.
$MAXIDS = 200;

// After 24 hours resumptionTokens become invalid.
$tokenValid = 24*3600;
$expirationdatetime = gmstrftime('%Y-%m-%dT%TZ', time()+$tokenValid); 

// define all supported sets in your repository
$SETS = array (
                array('setSpec'=>'sharedresources', 
                      'setName'=>'Shared Resources', 
                      'setDescription'=>'Resources from Shared Resource plug-in'),
                /* array('setSpec'=>'globalsearch', 
                      'setName'=>'Global Search Index', 
                      'setDescription'=>'Resources indexed by global search') */
            );

$default_set = 'sharedresources';

// define all supported metadata formats

//
// myhandler is the name of the file that handles the request for the 
// specific metadata format.
// [record_prefix] describes an optional prefix for the metadata
// [record_namespace] describe the namespace for this prefix

$METADATAFORMATS = array (
                        'oai_dc' => array('metadataPrefix'=>'oai_dc', 
                                          'schema' => 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd',
                                          'metadataNamespace' => 'http://www.openarchives.org/OAI/2.0/oai_dc/',
                                          'myhandler' => 'record_dc.php',
                                          'record_prefix' => 'dc',
                                          'record_namespace' => 'http://purl.org/dc/elements/1.1/' ),
                                          /*
                        'moodlecore' => array('metadataPrefix'=>'moodlecore', 
                                          'schema' => 'http://www.moodle.org/OAI/2.0/oai_moodle.xsd',
                                          'metadataNamespace' => 'http://www.moodle.org/OAI/2.0/oai_dc/',
                                          'myhandler' => 'record_moodle.php',
                                          'record_prefix' => 'moodle',
                                          'record_namespace' => 'http://moodle.org/moodle/elements/1.9/' ),
                        */
                        'oai_lom' => array('metadataPrefix'=>'oai_lom',
                                        'schema' => 'http://ltsc.ieee.org/xsd/LOM',
                                        'metadataNamespace' => 'http://ltsc.ieee.org/xsd/LOM',
                                        'myhandler' => 'record_lom.php',
                                        'record_prefix' => 'lom',
                                        'defaultnamespace' => false,
                                        'record_namespace' => 'http://ltsc.ieee.org/xsd/LOM'),
                        
                        'oai_lomfr' => array('metadataPrefix' => 'oai_lomfr',
                                        'schema' => 'http://www.lom-fr.fr/xsd/LOMFR',
                                        'metadataNamespace' => 'http://www.lom-fr.fr/xsd/LOMFR',
                                        'myhandler' => 'record_lomfr.php',
                                        'record_prefix' => 'lomfr',
                                        'defaultnamespace' => 'http://ltsc.ieee.org/xsd/LOM',
                                        'record_namespace' => 'http://www.lom-fr.fr/xsd/LOMFR'),

                        'oai_scolomfr' => array('metadataPrefix' => 'oai_scolomfr',
                                        'schema' => 'http://www.lom-fr.fr/xsd/SCOLOMFR',
                                        'metadataNamespace' => 'http://www.lom-fr.fr/xsd/SCOLOMFR',
                                        'myhandler' => 'record_scolomfr.php',
                                        'record_prefix' => 'scolomfr',
                                        'defaultnamespace' => 'http://www.lom-fr.fr/xsd/LOMFR',
                                        'record_namespace' => 'http://www.lom-fr.fr/xsd/SCOLOMFR'),

                        'oai_suplomfr' => array('metadataPrefix' => 'oai_suplomfr',
                                        'schema' => 'http://www.lom-fr.fr/xsd/SUPLOMFR',
                                        'metadataNamespace' => 'http://www.lom-fr.fr/xsd/SUPLOMFR',
                                        'myhandler' => 'record_suplomfr.php',
                                        'record_prefix' => 'suplomfr',
                                        'defaultnamespace' => 'http://www.lom-fr.fr/xsd/LOMFR',
                                        'record_namespace' => 'http://www.lom-fr.fr/xsd/SUPLOMFR'),
            
                        'oai_lre' => array('metadataPrefix'=>'oai_lre',
                                        'schema' => 'http://fire.eun.org/lode/imslorsltitm_v1p0.xsd',
                                        'metadataNamespace' => 'http://www.imsglobal.org/xsd/imslorsltitm_v1p0',
                                        'myhandler' => 'record_lre.php',
                                        'record_prefix' => 'lre',
                                        'defaultnamespace' => false,
                                        'record_namespace' => 'http://www.w3.org/2001/XMLSchema-instancehttp://www.w3.org/2001/XMLSchema-instance')

                    );

// the charset you store your metadata in your database
// currently only utf-8 and iso8859-1 are supported
$charset = "utf-8";

// if entities such as < > ' " in your metadata has already been escaped 
// then set this to true (e.g. you store < as &lt; in your DB)
$xmlescaped = false;

// If you want to expand the internal identifier in some way
// use this (but not for OAI stuff, see next line)
$idPrefix = '';

// this is your external (OAI) identifier for the item
// this will be expanded to
// oai:$repositoryIdentifier:$idPrefix$SQL['identifier']
// should not be changed
$oaiprefix = "oai".$delimiter.$repositoryIdentifier.$delimiter.$idPrefix; 

// adjust anIdentifier with sample contents an identifier
$sampleIdentifier     = $oaiprefix.'anIdentifier';

// set the catalog name
$catalogName = @$CFG->sharedresource_catalog_name;

// set the resource access URL scheme
$resourceBaseURL = $CFG->wwwroot.'/mod/sharedresource/view.php?identifier=';

// typical Pairformance implementation scheme. Resource are accessible through a dedicated 
// fake subdomain
/*
preg_match('/^[^\.]+(.*)/', $CFG->wwwroot);
$domain = $matches[1];
$resourceBaseURL = 'http://ressources'.$domain.'/resources/view.php?id=';
*/

// There is no need to change anything below.

// Current Date
$datetime = gmstrftime('%Y-%m-%dT%T');
$responseDate = $datetime.'Z';

// do not change
$XMLHEADER = 
'<?xml version="1.0" encoding="UTF-8"?>
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/
         http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">'."\n";

$xmlheader = $XMLHEADER . 
              ' <responseDate>'.$responseDate."</responseDate>\n";

// the xml schema namespace, do not change this
$XMLSCHEMA = 'http://www.w3.org/2001/XMLSchema-instance';

?>