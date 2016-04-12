<?php

    require "../../config.php";
    require_once('../lib.php');
    require_once($CFG->dirroot.'/mnet/xmlrpc/client.php');
    include_once $CFG->libdir.'/pear/HTML/AJAX/JSON.php';
    include_once 'relocate_testform.php';

    $context = context_system::instance();
    require_capability('moodle/site:doanything', $context);

    /**
    * Purpose is to test the resource provider relocation service
    * addressed remotely to an consumer platform
    *
    */

    $form = new Relocate_Test_Form();
    
    if ($data = $form->get_data()) {
        
        $consumer = $DB->get_record('mnet_host', array('id'=> $data->consumer));
        
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


?>