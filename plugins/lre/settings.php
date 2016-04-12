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

defined('MOODLE_INTERNAL') || die();

/**
 * @package    local_sharedresources
 * @category   local
 * @author Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */

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
