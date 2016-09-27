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
 * @author Valery Fremaux <valery@valeisti.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require_once($CFG->dirroot.'/local/sharedresources/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/metadatalib.php');

// settings default init
if (is_dir($CFG->dirroot.'/local/adminsettings')) {
    // Integration driven code 
    require_once($CFG->dirroot.'/local/adminsettings/lib.php');
    list($hasconfig, $hassiteconfig, $capability) = local_adminsettings_access();
} else {
    // Standard Moodle code
    $capability = 'moodle/site:config';
    $hasconfig = $hassiteconfig = has_capability($capability, context_system::instance());
}

if ($hasconfig) { // Needs this condition or there is error on login page.

    $ADMIN->add('root', new admin_category('resources', get_string('resources', 'local_sharedresources')));

    $ADMIN->add('resources', new admin_externalpage('resourcelibrary', get_string('pluginname', 'local_sharedresources'), new moodle_url('/local/sharedresources/index.php'), 'repository/sharedresources:view'));
}
if ($hassiteconfig) {
    $settings = new admin_settingpage('local_sharedresources', get_string('pluginname', 'sharedresource'));

    if (!empty($CFG->pluginchoice)) {
        require_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$CFG->pluginchoice.'/plugin.class.php');
        $object = 'sharedresource_plugin_'.$CFG->pluginchoice;
        $mtdstandard = new $object;

        $purposes = array();
        $purposefield = $mtdstandard->getTaxonomyPurposeElement();
        foreach ($purposefield->values as $purpose) {
            $purposes[$purpose] = get_string(clean_string_key($purpose), 'sharedmetadata_'.$CFG->pluginchoice);
        }

        $settingstr = get_string('defaulttaxonomypurposeonimport', 'local_sharedresources');
        $settingdesc = get_string('configdefaulttaxonomypurposeonimport', 'local_sharedresources');
        $settings->add(new admin_setting_configselect('defaulttaxonomypurposeonimport', $settingstr, $settingdesc, 0, $purposes));
    }

    $settings->add(new admin_setting_configcheckbox('local_sharedresources/privatecatalog', get_string('private_catalog', 'local_sharedresources'),
                       get_string('config_private_catalog', 'local_sharedresources'), 1));


    $plugins =  core_component::get_plugin_list('local/sharedresources/plugins');
    foreach ($plugins as $plugin) {
        if (file_exists($CFG->dirroot.'/local/sharedresources/plugins/'.$plugin.'/settings.php')) {
            // each plugin shoud add its proper page
            include($CFG->dirroot.'/local/sharedresources/plugins/'.$plugin.'/settings.php');
        }
    }

    $ADMIN->add('localplugins', $settings);
}
