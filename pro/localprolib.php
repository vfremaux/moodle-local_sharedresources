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
 * @package     local_sharedresources
 * @categroy    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_sharedresources;

defined('MOODLE_INTERNAL') || die();

/**
 * A class that specifies component local features, in addition to the generic prolib.
 */
final class local_pro_manager {

    /**
     * this adds additional settings to the component settings (generic part of the prolib system).
     * @param objectref &$admin
     * @param objectref &$settings
     */
    public static function add_settings(&$admin, &$settings) {

        $key = 'local_sharedresources_pro_hdr';
        $label = get_string('configprohdr', 'local_sharedresources');
        $desc = '';
        $settings->add(new \admin_setting_heading($key, $label, $desc));

        $key = 'local_sharedresources/hidesocial';
        $label = get_string('confighidesocial', 'local_sharedresources');
        $desc = '';
        $settings->add(new \admin_setting_configcheckbox($key, $label, $desc, 1));

        $key = 'local_sharedresources/hidenotice';
        $label = get_string('confighidenotice', 'local_sharedresources');
        $desc = '';
        $settings->add(new \admin_setting_configcheckbox($key, $label, $desc, 0));
    }
}