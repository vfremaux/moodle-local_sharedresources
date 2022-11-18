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

/**
 * @package    local_sharedresources
 * @category   local
 * @author Valery Fremaux <valery.fremaux@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    $key = 'lre_settings';
    $settings->add(new admin_setting_heading($key, sharedresources_get_string('lre_settings', 'sharedresourceprovider_lre'), ''));

    $key = 'sharedresourceprovider_lre/session_service_url';
    $label = get_string('sessionserviceurl', 'sharedresourceprovider_lre');
    $settings->add(new admin_setting_configtext($key, $label, '', ''));

    $key = 'sharedresourceprovider_lre/query_service_url';
    $label = get_string('queryserviceurl', 'sharedresourceprovider_lre');
    $settings->add(new admin_setting_configtext($key, $label, '', ''));
}