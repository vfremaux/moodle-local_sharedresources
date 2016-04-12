<?php

if ($action == 'forcedelete' || $action == 'delete') {
    $resourceid = required_param('id', PARAM_INT);
    
    $identifier = $DB->get_field('sharedresource_entry', 'identifier', array('id' => $resourceid));
    $DB->delete_records('sharedresource_metadata', array('entry_id' => $resourceid));
    $DB->delete_records('sharedresource_entry', array('id' => $resourceid));
    
    if ($sharedresources = $DB->get_records('sharedresource', array('identifier' => $identifier))) {
    
        $module = $DB->get_record('modules', array('name' => 'sharedresource'));
    
        foreach ($sharedresources as $sharedresource) {
            $DB->delete_records('sharedresource', array('id' => $sharedresource->id));
            $DB->delete_records('course_modules', array('module' => $module->id, 'instance' => $sharedresource->id));
            
        }
    }
}    

