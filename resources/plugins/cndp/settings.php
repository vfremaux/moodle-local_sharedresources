<?php  //$Id: settings.php,v 1.2 2010/12/17 22:38:24 vf Exp $

$temp = new admin_settingpage('cndp', get_string('cndpsettings', 'cndp', '', $CFG->dirroot.'/resources/plugins/cndp/lang/'));

if ($ADMIN->fulltree) {
    $temp->add(new admin_setting_configtext('cndpindexing_username', get_string('cndpindexing_username', 'cndp', '', $CFG->dirroot.'/resources/plugins/cndp/lang/'),
                       '', @$CFG->cndpindexing_username));

    $temp->add(new admin_setting_configtext('cndpindexing_password', get_string('cndpindexing_password', 'cndp', '', $CFG->dirroot.'/resources/plugins/cndp/lang/'),
                       '', @$CFG->cndpindexing_password));

    $temp->add(new admin_setting_configtext('cndpindexing_timeout', get_string('cndpindexing_timeout', 'cndp', '', $CFG->dirroot.'/resources/plugins/cndp/lang/'),
                       '', @$CFG->cndpindexing_timeout));

    $temp->add(new admin_setting_configtext('cndpindexing_searchurl', get_string('cndpindexing_searchurl', 'cndp', '', $CFG->dirroot.'/resources/plugins/cndp/lang/'),
                       '', @$CFG->cndpindexing_searchurl));

    $temp->add(new admin_setting_configtext('cndpindexing_noticedetailurl', get_string('cndpindexing_noticedetailurl', 'cndp', '', $CFG->dirroot.'/resources/plugins/cndp/lang/'),
                       '', @$CFG->cndpindexing_noticedetailurl));

    $temp->add(new admin_setting_configtext('cndpindexing_ticketkey', get_string('cndpindexing_ticketkey', 'cndp', '', $CFG->dirroot.'/resources/plugins/cndp/lang/'),
                       '', @$CFG->cndpindexing_ticketkey));

    $temp->add(new admin_setting_configtext('cndpindexing_ticketvector', get_string('cndpindexing_ticketvector', 'cndp', '', $CFG->dirroot.'/resources/plugins/cndp/lang/'),
                       '', @$CFG->cndpindexing_ticketvector));

    $temp->add(new admin_setting_configtext('cndpindexing_usessl', get_string('cndpindexing_usessl', 'cndp', '', $CFG->dirroot.'/resources/plugins/cndp/lang/'),
                       '', @$CFG->cndpindexing_usessl));

    $temp->add(new admin_setting_configtext('cndpindexing_sslcert', get_string('cndpindexing_sslcert', 'cndp', '', $CFG->dirroot.'/resources/plugins/cndp/lang/'),
                       '', @$CFG->cndpindexing_sslcert));

    $temp->add(new admin_setting_configtext('cndpindexing_sslcert_password', get_string('cndpindexing_sslcert_password', 'cndp', '', $CFG->dirroot.'/resources/plugins/cndp/lang/'),
                       '', @$CFG->cndpindexing_sslcert_password));

    $temp->add(new admin_setting_configtext('cndpindexing_sslkey', get_string('cndpindexing_sslkey', 'cndp', '', $CFG->dirroot.'/resources/plugins/cndp/lang/'),
                       '', @$CFG->cndpindexing_sslkey));

    $temp->add(new admin_setting_configtext('cndpindexing_sslkey_password', get_string('cndpindexing_sslkey_password', 'cndp', '', $CFG->dirroot.'/resources/plugins/cndp/lang/'),
                       '', @$CFG->cndpindexing_sslkey_password));
    
}
$ADMIN->add('resources', $temp);

?>
