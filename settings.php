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
require_once($CFG->dirroot.'/local/sharedresources/classes/navigator.class.php');

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

$shcaps = array(
    'repository/sharedresouces:use',
    'repository/sharedresouces:view',
    'repository/sharedresouces:manage',
);

$usecap = sharedresources_has_capability_somewhere('repository/sharedresources:use', false, false, false, CONTEXT_COURSECAT.','.CONTEXT_COURSE);
$viewcap = sharedresources_has_capability_somewhere('repository/sharedresources:view', false, false, false, CONTEXT_COURSECAT.','.CONTEXT_COURSE);
$managecap = sharedresources_has_capability_somewhere('repository/sharedresources:manage', false, false, false, CONTEXT_COURSECAT.','.CONTEXT_COURSE);

if ($hasconfig || $usecap || $viewcap || $managecap) {
    // Needs this condition or there is error on login page.

    if ($DB->get_field('modules', 'visible', array('name' => 'sharedresource'))) {

        $ADMIN->add('root', new admin_category('resources', get_string('resources', 'local_sharedresources')));

        $label = get_string('pluginname', 'local_sharedresources');
        $pageurl = new moodle_url('/local/sharedresources/index.php');
        $settingspage = new admin_externalpage('resourcelibrary', $label, $pageurl, 'repository/sharedresources:view');
        $ADMIN->add('resources', $settingspage);
    }
}
if ($hassiteconfig) {
    $settings = new admin_settingpage('local_sharedresources', get_string('pluginname', 'sharedresource'));

    $config = get_config('sharedresource');

    if (!empty($config->schema)) {
        if (@$debugwhitepage) {
            echo "\t\tLoading active schema: $config->schema\n";
        }
        include_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$config->schema.'/plugin.class.php');
        $mtdclass = '\\mod_sharedresource\\plugin_'.$config->schema;
        $mtdstandard = new $mtdclass();

        $taxonomies = \local_sharedresources\browser\navigation::get_taxonomies_menu(true);

        if (!empty($taxonomies)) {
            $key = 'local_sharedresources/defaulttaxonomyonimport';
            $label = get_string('configdefaulttaxonomyonimport', 'local_sharedresources');
            $desc = get_string('configdefaulttaxonomyonimport_desc', 'local_sharedresources');
            $settings->add(new admin_setting_configselect($key, $label, $desc, 0, $taxonomies));
        }

        $purposes = array();
        $purposefield = $mtdstandard->getTaxonomyPurposeElement();
        if ($purposefield) {
            foreach ($purposefield->values as $purpose) {
                $purposes[$purpose] = get_string(clean_string_key($purpose), 'sharedmetadata_'.$config->schema);
            }

            $key = 'local_sharedresources/defaulttaxonomypurposeonedit';
            $label = get_string('configdefaulttaxonomypurposeonedit', 'local_sharedresources');
            $desc = get_string('configdefaulttaxonomypurposeonedit_desc', 'local_sharedresources');
            $settings->add(new admin_setting_configselect($key, $label, $desc, 0, $purposes));
        }

        $defaultpages = array(
            'explore' => get_string('searchengine', 'local_sharedresources')
        );

        $plugin = sharedresource_get_plugin($config->schema);
        $taxonelement = $plugin->getTaxonomyValueElement();
        if (!empty($taxonelement)) {
            $defaultpages['browse'] = get_string('browser', 'local_sharedresources');
        }

        $key = 'local_sharedresources/defaultlibraryindexpage';
        $label = get_string('configdefaultlibraryindexpage', 'local_sharedresources');
        $desc = get_string('configdefaultlibraryindexpage_desc', 'local_sharedresources');
        $settings->add(new admin_setting_configselect($key, $label, $desc, 'explore', $defaultpages));
    }

    $key = 'local_sharedresources/privatecatalog';
    $label = get_string('configprivatecatalog', 'local_sharedresources');
    $desc = get_string('configprivatecatalog_desc', 'local_sharedresources');
    $default = true;
    $settings->add(new admin_setting_configcheckbox($key, $label, $desc, $default));

    $plugins =  core_component::get_plugin_list('local/sharedresources/plugins');
    foreach ($plugins as $plugin) {
        if (@$debugwhitepage) {
            echo "Loading subsettings for plugin: $plugin\n";
        }
        if (file_exists($CFG->dirroot.'/local/sharedresources/plugins/'.$plugin.'/settings.php')) {
            // Each plugin shoud add its proper page.
            include($CFG->dirroot.'/local/sharedresources/plugins/'.$plugin.'/settings.php');
        }
    }

    if (local_sharedresources_supports_feature('emulate/community')) {
        // This will accept any.
        $settings->add(new admin_setting_heading('plugindisthdr', get_string('plugindist', 'local_sharedresource'), ''));

        $key = 'local_sharedresources/emulatecommunity';
        $label = get_string('emulatecommunity', 'local_sharedresources');
        $desc = get_string('emulatecommunity_desc', 'local_sharedresources');
        $settings->add(new admin_setting_configcheckbox($key, $label, $desc, 0));
    } else {
        $label = get_string('plugindist', 'local_sharedresources');
        $desc = get_string('plugindist_desc', 'local_sharedresources');
        $settings->add(new admin_setting_heading('plugindisthdr', $label, $desc));
    }

    $ADMIN->add('localplugins', $settings);
}
