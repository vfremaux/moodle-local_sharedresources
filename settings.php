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
 * @author Valery Fremaux <valery@valeisti.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/sharedresources/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/metadatalib.php');

// Settings default init.
if (is_dir($CFG->dirroot.'/local/adminsettings')) {
    // Integration driven code.
    require_once($CFG->dirroot.'/local/adminsettings/lib.php');
    list($hasconfig, $hassiteconfig, $capability) = local_adminsettings_access();
} else {
    // Standard Moodle code.
    $capability = 'moodle/site:config';
    $hasconfig = $hassiteconfig = has_capability($capability, context_system::instance());
}

if ($hasconfig) {
    // Needs this condition or there is error on login page.

    $ADMIN->add('root', new admin_category('resources', get_string('resources', 'local_sharedresources')));

    $label = get_string('pluginname', 'local_sharedresources');
    $pageurl = new moodle_url('/local/sharedresources/index.php');
    $settingspage = new admin_externalpage('resourcelibrary', $label, $pageurl, 'repository/sharedresources:view');
    $ADMIN->add('resources', $settingspage);
}
if ($hassiteconfig) {
    $settings = new admin_settingpage('local_sharedresources', get_string('pluginname', 'sharedresource'));

    $config = get_config('sharedresource');

    if (!empty($config->schema)) {
        include_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$config->schema.'/plugin.class.php');
        $mtdclass = '\\mod_sharedresource\\plugin_'.$config->schema;
        $mtdstandard = new $mtdclass();

        $purposes = array();
        $purposefield = $mtdstandard->getTaxonomyPurposeElement();
        foreach ($purposefield->values as $purpose) {
            $purposes[$purpose] = get_string(clean_string_key($purpose), 'sharedmetadata_'.$config->schema);
        }

        $key = 'local_sharedresources/defaulttaxonomypurposeonimport';
        $label = get_string('defaulttaxonomypurposeonimport', 'local_sharedresources');
        $desc = get_string('configdefaulttaxonomypurposeonimport', 'local_sharedresources');
        $settings->add(new admin_setting_configselect($key, $label, $desc, 0, $purposes));
    }

    $key = 'local_sharedresources/privatecatalog';
    $label = get_string('private_catalog', 'local_sharedresources');
    $desc = get_string('config_private_catalog', 'local_sharedresources');
    $settings->add(new admin_setting_configcheckbox($key, $label, $desc, 1));

    $plugins =  core_component::get_plugin_list('local/sharedresources/plugins');
    foreach ($plugins as $plugin) {
        if (file_exists($CFG->dirroot.'/local/sharedresources/plugins/'.$plugin.'/settings.php')) {
            // Each plugin shoud add its proper page.
            include($CFG->dirroot.'/local/sharedresources/plugins/'.$plugin.'/settings.php');
        }
    }

    $ADMIN->add('localplugins', $settings);
}
