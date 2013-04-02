<?php

/**
* the master shared resources administration view entry point.
* the administration view let you browse through resources with a
* capacity to validate, delete, suspend and reindex resources on the
* local repository.
*
*
*/

    require "../../config.php";
    
    $context = get_context_instance(CONTEXT_SYSTEM);
    require_capability('mod/sharedresource:adminrepository', $context);
    
    $navigation = build_navigation('');
    
    print_header($repositorystr, $repositorystr, '', $navigation);
    
    
    if ($providers = sharedrepository_get_providers()){
        
    $provider = optional_param('provider', 'all', PARAM_ALPHA);

    sharedrepository_print_tabs($provider);    

    sharedrepository_print_browser($provider, 'admin');
    
    print_footer();
?>