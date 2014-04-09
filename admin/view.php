<?php

/**
* the master shared resources administration view entry point.
* the administration view let you browse through resources with a
* capacity to validate, delete, suspend and reindex resources on the
* local repository.
*
*
*/

    require "../../../config.php";
    
    $context = get_context_instance(CONTEXT_SYSTEM);
    // require_capability('repository/sharedresources:manage', $context);
    
    $PAGE->set_pagelayout('standard');
    $PAGE->set_context($context);
    $PAGE->set_title(get_string('adminrepository', 'local_sharedresources'));
    $PAGE->set_heading($SITE->fullname); 
    $PAGE->navbar->add(get_string('adminrepository', 'local_sharedresources'),'view.php','misc');

    $PAGE->set_focuscontrol('');
    $PAGE->set_cacheable(false);
    $PAGE->set_button('');
    $PAGE->set_headingmenu('');

    $url = new moodle_url('/local/sharedresources/search.php');
    $PAGE->set_url($url);
    print($OUTPUT->header()); 
    

    
    if ($providers = sharedrepository_get_providers()){
        
    $provider = optional_param('provider', 'all', PARAM_ALPHA);

    sharedrepository_print_tabs($provider);    

    sharedrepository_print_browser($provider, 'admin');
    }
    
    print($OUTPUT->footer());
?>