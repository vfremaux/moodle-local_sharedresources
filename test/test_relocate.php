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

require('../../config.php');
require_once($CFG->dirroot.'/local/sharedresources/lib.php');
require_once($CFG->dirroot.'/mnet/xmlrpc/client.php');
require_once($CFG->dirroot.'/local/sharedresources/relocate_testform.php');

$context = context_system::instance();
require_login();
require_capability('moodle/site:doanything', $context);

/*
 * Purpose is to test the resource provider relocation service
 * addressed remotely to an consumer platform
 *
 */

$form = new Relocate_Test_Form();

if ($data = $form->get_data()) {

    $consumer = $DB->get_record('mnet_host', array('id' => $data->consumer));

    $client = new mnet_xmlrpc_client();
    $client->set_method('mod/sharedresource/rpclib.php/sharedresource_rpc_move');
    $client->add_param($USER->username);
    $client->add_param($CFG->wwwroot);
    $client->add_param($data->identifier);
    $client->add_param($data->targetrepo, 'string');
    $client->add_param($CFG->wwwroot.'/changed_url', 'string');

    if (!$remotepeer = new mnet_peer()) {
        error ("MNET client initialisation error");
    }
    $remotepeer->set_wwwroot($consumer->wwwroot);

    if ($client->send($remotepeer) === true) {
        $results = json_decode($client->response);
        print_object($results);
    } else {
        foreach ($client->error as $errormessage) {
            echo "$errormessage<br />";
        }
    }
} else {
    $form->display();
}

