<?php

/**
* @package local-sharedresources
*
* Marks locally or remotely the like index
*/

require('../../../config.php');

// No special security here...

$resid = required_param('resid', PARAM_TEXT);
$repo = optional_param('repo', $CFG->mnet_localhost_id, PARAM_INT); // Repo is given as mnethostid.

$repohostroot = $DB->get_field('mnet_host', 'wwwroot', array('id' => $repo));

if ($repohostroot == $CFG->wwwroot) {
    // do this locally
    $oldvalue = $DB->get_field('sharedresource_entry', 'scorelike', array('identifier' => $resid));
    $value = $oldvalue + 1;
    $DB->set_field('sharedresource_entry', 'scorelike', $value, array('identifier' => $resid));
} else {
    // fire remote ajax_liked_mark thru Curl direct shoot (No need MNET here)
    
    $url = $repohostroot.'/local/sharedresources/ajax/add_liked_mark.php?resid='.$resid;
    
    $curl = curl_init($url);

    curl_setopt($curl, CURLOPT_TIMEOUT, 60);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, false);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Moodle');
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: text/xml charset=UTF-8"));
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

    if (!empty($CFG->proxyhost) and !@$proxybypass) {
        // SOCKS supported in PHP5 only.
        if (!empty($CFG->proxytype) and ($CFG->proxytype == 'SOCKS5')) {
            if (defined('CURLPROXY_SOCKS5')) {
                curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
            }
        }

        curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, false);

        if (empty($CFG->proxyport)) {
            curl_setopt($curl, CURLOPT_PROXY, $CFG->proxyhost);
        } else {
            curl_setopt($curl, CURLOPT_PROXY, $CFG->proxyhost.':'.$CFG->proxyport);
        }

        if (!empty($CFG->proxyuser) and !empty($CFG->proxypassword)) {
            curl_setopt($curl, CURLOPT_PROXYUSERPWD, $CFG->proxyuser.':'.$CFG->proxypassword);
            if (defined('CURLOPT_PROXYAUTH')) {
                // Any proxy authentication if PHP 5.1.
                curl_setopt($curl, CURLOPT_PROXYAUTH, CURLAUTH_BASIC | CURLAUTH_NTLM);
            }
        }
    }

    $value = curl_exec($curl);

    // Prepared for debugging.
    $result = new StdClass;
    $result->info  = curl_getinfo($curl);
    $result->error = curl_error($curl);
    $result->errno = curl_errno($curl);

    curl_close($curl);
}

echo $value;