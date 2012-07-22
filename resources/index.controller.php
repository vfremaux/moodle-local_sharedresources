<?php

if ($action == 'forcedelete' || $action == 'delete'){
    $resourceid = required_param('id', PARAM_INT);
    
    $identifier = get_field('sharedresource_entry', 'identifier', 'id', $resourceid);
    delete_records('sharedresource_metadata', 'entry_id', $resourceid);
    delete_records('sharedresource_entry', 'id', $resourceid);
    
    if($sharedresources = get_records('sharedresource', 'identifier', $identifier)){
    
        $module = get_record('modules', 'name', 'sharedresource');
    
        foreach($sharedresources as $sharedresource){
            delete_records('sharedresource', 'id', $sharedresource->id);
            delete_records('course_modules', 'module', $module->id, 'instance', $sharedresource->id);
        }
    }
}    

?>