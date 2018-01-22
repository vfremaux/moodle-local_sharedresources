<?php
/*
* +----------------------------------------------------------------------+
* | PHP Version 4                                                        |
* +----------------------------------------------------------------------+
* | Copyright (c) 2002 Heinrich Stamerjohanns                            |
* |                                                                      |
* | oai2.php -- An OAI Data Provider for version OAI v2.0                |
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
* | Derived from work by U. Müller, HUB Berlin                           |
* |                                                                      |
* | Written by Heinrich Stamerjohanns, May 2002                          |
* |            stamer@uni-oldenburg.de                                   |
* +----------------------------------------------------------------------+
*/
//
// $Id: oai2.php,v 1.2 2012-01-05 20:57:58 vf Exp $
//

ob_start();

$output = '';
$errors = '';

// call Moodle config
require_once('../../../config.php');

require_once('oai2/oaidp-util.php');

// register_globals does not need to be set
if (!php_is_at_least('4.1.0')) {
<<<<<<< HEAD
	$_SERVER = $HTTP_SERVER_VARS;
	$_SERVER['REQUEST_METHOD'] = $REQUEST_METHOD;
	$_GET = $HTTP_GET_VARS;
	$_POST = $HTTP_POST_VARS;
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	$args = $_GET;
	$getarr = explode('&', $_SERVER['QUERY_STRING']);
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$args = $_POST;
} else {
	$errors .= oai_error('badRequestMethod', $_SERVER['REQUEST_METHOD']);
=======
    $_SERVER = $HTTP_SERVER_VARS;
    $_SERVER['REQUEST_METHOD'] = $REQUEST_METHOD;
    $_GET = $HTTP_GET_VARS;
    $_POST = $HTTP_POST_VARS;
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $args = $_GET;
    $getarr = explode('&', $_SERVER['QUERY_STRING']);
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $args = $_POST;
} else {
    $errors .= oai_error('badRequestMethod', $_SERVER['REQUEST_METHOD']);
>>>>>>> MOODLE_33_STABLE
}

require_once('oaidp-config.php');

// and now we make the OAI Repository Explorer really happy
// I have not found any way to check this for POST requests.
if (isset($getarr)) {
<<<<<<< HEAD
	if (count($getarr) != count($args)) {
		$errors .= oai_error('sameArgument');
	}
=======
    if (count($getarr) != count($args)) {
        $errors .= oai_error('sameArgument');
    }
>>>>>>> MOODLE_33_STABLE
}

$reqattr = '';
if (is_array($args)) {
<<<<<<< HEAD
	foreach ($args as $key => $val) {
		$reqattr .= ' '.$key.'="'.htmlspecialchars(stripslashes($val)).'"';
	}
=======
    foreach ($args as $key => $val) {
        $reqattr .= ' '.$key.'="'.htmlspecialchars(stripslashes($val)).'"';
    }
>>>>>>> MOODLE_33_STABLE
}

// in case register_globals is on, clean up polluted global scope
$verbs = array ('from', 'identifier', 'metadataPrefix', 'set', 'resumptionToken', 'until');
<<<<<<< HEAD
foreach($verbs as $val) {
	unset($$val);
=======
foreach ($verbs as $val) {
    unset($$val);
>>>>>>> MOODLE_33_STABLE
}

$request = ' <request'.$reqattr.'>'.$MY_URI."</request>\n";
$request_err = ' <request>'.$MY_URI."</request>\n";

if (is_array($compression)) {
<<<<<<< HEAD
	if (in_array('gzip', $compression)
		&& ini_get('output_buffering')) {
		$compress = TRUE;
	} else {
		$compress = FALSE;
	}
}

if (isset($args['verb'])) {
	switch ($args['verb']) {

		case 'GetRecord':
			unset($args['verb']);
			include 'oai2/getrecord.php';
			break;

		case 'Identify':
			unset($args['verb']);
			// we never use compression in Identify
			$compress = FALSE;
			include 'oai2/identify.php';
			break;

		case 'ListIdentifiers':
			unset($args['verb']);
			include 'oai2/listidentifiers.php';
			break;

		case 'ListMetadataFormats':
			unset($args['verb']);
			include 'oai2/listmetadataformats.php';
			break;

		case 'ListRecords':
			unset($args['verb']);
			include 'oai2/listrecords.php';
			break;

		case 'ListSets':
			unset($args['verb']);
			include 'oai2/listsets.php';
			break;

		default:
			// we never use compression with errors
			$compress = FALSE;
			$errors .= oai_error('badVerb', $args['verb']);
	} /*switch */

} else {
	$errors .= oai_error('noVerb');
}

if ($errors != '') {
	oai_exit();
}

if ($compress) {
	// ob_start('ob_gzhandler');
=======
    if (in_array('gzip', $compression)
        && ini_get('output_buffering')) {
        $compress = TRUE;
    } else {
        $compress = FALSE;
    }
}

if (isset($args['verb'])) {
    switch ($args['verb']) {

        case 'GetRecord':
            unset($args['verb']);
            include 'oai2/getrecord.php';
            break;

        case 'Identify':
            unset($args['verb']);
            // we never use compression in Identify
            $compress = FALSE;
            include 'oai2/identify.php';
            break;

        case 'ListIdentifiers':
            unset($args['verb']);
            include 'oai2/listidentifiers.php';
            break;

        case 'ListMetadataFormats':
            unset($args['verb']);
            include 'oai2/listmetadataformats.php';
            break;

        case 'ListRecords':
            unset($args['verb']);
            include 'oai2/listrecords.php';
            break;

        case 'ListSets':
            unset($args['verb']);
            include 'oai2/listsets.php';
            break;

        default:
            // we never use compression with errors
            $compress = FALSE;
            $errors .= oai_error('badVerb', $args['verb']);
    } /*switch */

} else {
    $errors .= oai_error('noVerb');
}

if ($errors != '') {
    oai_exit();
}

if ($compress) {
    // ob_start('ob_gzhandler');
>>>>>>> MOODLE_33_STABLE
}

header($CONTENT_TYPE);
echo $xmlheader;
echo $request;
echo $output;
<<<<<<< HEAD
oai_close(); 

?>
=======
oai_close();
>>>>>>> MOODLE_33_STABLE
