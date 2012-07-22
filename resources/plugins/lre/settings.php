<?php  //$Id: settings.php,v 1.1.1.1 2009-03-14 21:03:07 cvsprf Exp $

$temp = new admin_settingpage('lre', get_string('lresettings', 'lre', '', $CFG->dirroot.'/resources/plugins/lre/lang/'));

if ($ADMIN->fulltree) {
    $temp->add(new admin_setting_configtext('lre_session_service_url', get_string('sessionserviceurl', 'lre', '', $CFG->dirroot.'/resources/plugins/lre/lang/'),
                       '', @$CFG->lre_session_service_url));
    
    $temp->add(new admin_setting_configtext('lre_query_service_url', get_string('queryserviceurl', 'lre', '', $CFG->dirroot.'/resources/plugins/lre/lang/'),
                       '', @$CFG->lre_session_service_url));
}
$ADMIN->add('resources', $temp);

?>
