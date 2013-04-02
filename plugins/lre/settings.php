<?php  //$Id: settings.php,v 1.1.1.1 2011-06-20 18:57:32 vf Exp $

/*$temp = new admin_settingpage('lre', get_string('lresettings', 'lre', '', $CFG->dirroot.'/local/sharedresources/plugins/lre/lang/'));
*/
if ($ADMIN->fulltree) {
    
    $settings->add(new admin_setting_heading('lre_settings', resources_get_string('lre_settings', 'sharedresourceprovider_lre'),''),'');
 
    
    $settings->add(new admin_setting_configtext('lre_session_service_url', resources_get_string('sessionserviceurl', 'sharedresourceprovider_lre'),
                       '', @$CFG->lre_session_service_url));
    
    $settings->add(new admin_setting_configtext('lre_query_service_url', resources_get_string('queryserviceurl', 'sharedresourceprovider_lre'),
                       '', @$CFG->lre_session_service_url));
}


?>
