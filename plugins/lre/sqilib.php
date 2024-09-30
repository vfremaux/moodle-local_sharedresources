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
 * @package     local_sharedresources
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright   (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 * wraps the SQI soap calls for activating SQI Target
 */
defined('MOODLE_INTERNAL') || die();

class SQI {

    /**
     * Opens an SQI session and fills an SQI descriptor.
     */
    public static function init() {
        global $CFG, $SESSION;

        ini_set('soap.wsdl_cache_enabled', 0); // While debug.

        $wsdl = $CFG->dirroot.'/resources/plugins/lre/wsdl/sqiSessionManagement.wsdl';

        $soapoptions = array(
            'uri'      => "urn:www.cenorm.be/isss/ltws/wsdl/SQIv1p0",
            'style'    => SOAP_RPC,
            'use'      => SOAP_ENCODED
        );

        if (!empty($CFG->proxyhost)) {
             $soapoptions['proxy_host']     = $CFG->proxyhost;
             $soapoptions['proxy_port']     = 0 + $CFG->proxyport;
             $soapoptions['proxy_login']    = $CFG->proxyuser;
             $soapoptions['proxy_password'] = $CFG->proxypassword;
        }

        $sessionsoapclient = new SoapClient($wsdl, $soapoptions);

        $sessionIDObject = $sessionsoapclient->createAnonymousSession();
        $SESSION->SQI = new StdClass;
        $SESSION->SQI->resultSetSize = 8;
        $SESSION->SQI->sessionID = $sessionIDObject->createAnonymousSessionReturn;
        if (!empty($SESSION->SQI->sessionID)) {

            $wsdl = $CFG->dirroot.'/resources/plugins/lre/wsdl/sqiTarget.wsdl';

            $soapoptions = array(
                'location' => "http://lrecoretest.eun.org:6080/LRE-SQI/services/TargetServiceBinding?wsdl",
                'uri'      => "urn:www.cenorm.be/isss/ltws/wsdl/SQIv1p0",
                'style'    => SOAP_RPC,
                'use'      => SOAP_ENCODED,
                'encoding'      => 'UTF-8'
            );

            if (!empty($CFG->proxyhost)) {
                 $soapoptions['proxy_host']     = $CFG->proxyhost;
                 $soapoptions['proxy_port']     = 0 + $CFG->proxyport;
                 $soapoptions['proxy_login']    = $CFG->proxyuser;
                 $soapoptions['proxy_password'] = $CFG->proxypassword;
            }

            $SESSION->SQI->client = new SoapClient(NULL, $soapoptions);

            $p_sessionID     = new SoapParam($SESSION->SQI->sessionID, 'targetSessionID');
            $p_queryLanguage = new SoapParam('lre', 'queryLanguageID');
            $SESSION->SQI->client->setQueryLanguage($p_sessionID, $p_queryLanguage); // The formal language of the query.

            $p_resultSize = new SoapParam($SESSION->SQI->resultSetSize, 'resultsSetSize');
            $SESSION->SQI->client->setResultsSetSize($p_sessionID, $p_resultSize);

            if (!empty($SESSION->SQI->client->__SOAP_Fault)) {
                print_object($SESSION->SQI->client);
            }
        } else {
            unset($SESSION->SQI);
            error("Something is wrong in SQI setup");
        }
    }

    /**
    * terminates an open SQI session
    */
    public static function end() {
        global $CFG, $SESSION;

        if (!empty($SESSION->SQI->sessionID)) {
            $wsdl = $CFG->dirroot.'/resources/plugins/lre/wsdl/sqiSessionManagement.wsdl';

            $soapoptions = array(
                'uri'      => "urn:www.cenorm.be/isss/ltws/wsdl/SQIv1p0",
                'style'    => SOAP_RPC,
                'use'      => SOAP_ENCODED
            );

            if (!empty($CFG->proxyhost)) {
                 $soapoptions['proxy_host']     = $CFG->proxyhost;
                 $soapoptions['proxy_port']     = 0 + $CFG->proxyport;
                 $soapoptions['proxy_login']    = $CFG->proxyuser;
                 $soapoptions['proxy_password'] = $CFG->proxypassword;
            }

            $sessionsoapclient = new SoapClient($wsdl, $soapoptions);

            $p_sessionID = new SoapParam($SESSION->SQI->sessionID, 'sessionID');
            unset($SESSION->SQI);
        }
    }

    /**
     *
     */
    public static function query($query, $offset = 1) {
        global $CFG, $SESSION;

        if (is_null($SESSION->SQI)) {
            error("SQI Not yet initialized. Run SQIInit first");
        }

        $SESSION->SQI->query = $query;

        $p_sessionID = new SoapParam($SESSION->SQI->sessionID, 'targetSessionID');
        $p_query     = new SoapParam($query, 'queryStatementqueryStatement');
        $p_offset    = new SoapParam($offset, 'startResult');
        $response = $SESSION->SQI->client->synchronousQuery($p_sessionID, $p_query, $p_offset);
        return $response;
    }

    /**
    *
    */
    public static function results_count() {
        global $CFG, $SESSION;

        if (is_null($SESSION->SQI)) {
            error("SQI Not yet initialized. Run SQIInit first");
        }

        $sessionID = (string)$SESSION->SQI->sessionID;
        $query = (string)$SESSION->SQI->query;

        $p_sessionID = new SoapParam($sessionID, 'targetSessionID');
        $p_query = new SoapParam($query, 'queryStatement');
        $response = $SESSION->SQI->client->getTotalResultsCount($p_sessionID, $p_query);

        $maxrecords = $response;

        return $maxrecords;
    }

    /**
     *
     */
    public static function get_max_page() {
        global $CFG, $SESSION;

        if (is_null($SESSION->SQI)) {
            error("SQI Not yet initialized. Run SQIInit first");
        }

        $sessionID = (string)$SESSION->SQI->sessionID;
        $query = (string)$SESSION->SQI->query;

        $p_sessionID = new SoapParam($sessionID, 'targetSessionID');
        $p_query = new SoapParam($query, 'queryStatement');
        $response = $SESSION->SQI->client->getTotalResultsCount($p_sessionID, $p_query);

        $maxrecords = $response;

        return (int)ceil($maxrecords / $SESSION->SQI->resultSetSize);
    }

    /**
     *
     */
    public static function get_page($page) {
        global $CFG, $SESSION;

        if (is_null($SESSION->SQI)) {
            error("SQI Not yet initialized. Run SQIInit first");
        }

        if (empty($SESSION->SQI->query)) {
            error("SQI Query not yet initialized. Run SQIQuery first");
        }

        $offset = 1 + ($page * $SESSION->SQI->resultSetSize);

        $p_sessionID = new SoapParam($SESSION->SQI->sessionID, 'targetSessionID');
        $p_query     = new SoapParam($SESSION->SQI->query, 'queryStatement');
        $p_offset    = new SoapParam($offset, 'startResult');
        $response = $SESSION->SQI->client->synchronousQuery($p_sessionID, $p_query, $p_offset);
        return $response;
    }

    /**
     *
     */
    public static function get_age_range_options() {

        $age = array('0', '3', '5', '8', '10', '14', '16', '18');

        $agerangeoptions = array_combine($age, $age);
        $agerangeoptions['0'] = '-';
        return $agerangeoptions;
    }

    /**
     *
     */
    public static function get_lo_languages() {
        global $CFG;

        $langkeys = array('de', 'en', 'hy', 'bg', 'ca', 'hr', 'da', 'es', 'et', 'fi', 'fr', 'el', 'hu', 'he', 'ga', 'is',
                          'it', 'lv', 'lt', 'mt', 'no', 'nl', 'pl', 'pt', 'ro', 'ru', 'sk', 'sl', 'sv', 'cs', 'uk');
        $options[''] = get_string('choose');
        foreach ($langkeys as $akey) {
            $options[$akey] = get_string($akey, 'sharedresourceprovider_lre');
        }
        return $options;
    }

    /**
    * 
    */
    public static function get_learning_resource_type_options() {
        global $CFG;

        $lrtkeys = array('application',
                    'assessment',
                    'broadcast',
                    'case study',
                    'course',
                    'demonstration',
                    'drill and practice',
                    'educational game',
                    'enquiry-oriented activity',
                    'experiment',
                    'exploration',
                    'glossary',
                    'guide',
                    'audio',
                    'data',
                    'image',
                    'model',
                    'text',
                    'video',
                    'lesson plan',
                    'open activity',
                    'presentation',
                    'project',
                    'reference',
                    'role play',
                    'simulation',
                    'tool',
                    'weblog',
                    'web page',
                    'wiki',
                    'other web resource',
                    'other');

        $options[0] = get_string('choose');
        foreach ($lrtkeys as $akey) {
            $options[$akey] = get_string($akey, 'sharedresourceprovider_lre');
        }
        return $options;
    }
}