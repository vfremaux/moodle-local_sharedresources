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

define('LOCAL_SHAREDRESOURCES_COMPONENT_PROVIDER_ROUTER_URL', 'http://www.mylearningfactory.com/providers/router.php');

final class pro_manager {

    private static $component = 'local_sharedresources';
    private static $componentpath = 'local/sharedresources';

    /**
     * this adds additional settings to the component settings (generic part of the prolib system).
     * @param objectref &$admin
     * @param objectref &$settings
     */
    public static function add_settings(&$admin, &$settings) {
        global $CFG, $PAGE;

        $PAGE->requires->js_call_amd(self::$component.'/pro', 'init');

        $settings->add(new \admin_setting_heading('plugindisthdr', get_string('plugindist', self::$component), ''));

        $key = self::$component.'/emulatecommunity';
        $label = get_string('emulatecommunity', self::$component);
        $desc = get_string('emulatecommunity_desc', self::$component);
        $settings->add(new \admin_setting_configcheckbox($key, $label, $desc, 0));

        $key = self::$component.'/licenseprovider';
        $label = get_string('licenseprovider', self::$component);
        $desc = get_string('licenseprovider_desc', self::$component);
        $settings->add(new \admin_setting_configtext($key, $label, $desc, ''));

        $key = self::$component.'/licensekey';
        $label = get_string('licensekey', self::$component);
        $desc = get_string('licensekey_desc', self::$component);
        $settings->add(new \admin_setting_configtext($key, $label, $desc, ''));

        if (file_exists($CFG->dirroot.'/'.self::$componentpath.'/pro/localprolib.php')) {
            include_once($CFG->dirroot.'/'.self::$componentpath.'/pro/localprolib.php');
            local_pro_manager::add_settings($admin, $settings);
        }
    }

    /**
     * Sends an empty license using advice to registered provider.
     */
    public static function send_empty_license_signal() {
        $config = get_config(self::$component);

        if (local_sharedresources_supports_feature() && empty($config->licensekey)) {
            if ($config->licensekeycheckdate < time() - 30 * DAYSECS) {

                $url = LOCAL_SHAREDRESOURCES_COMPONENT_PROVIDER_ROUTER_URL;
                $url .= '?provider='.$config->licenseprovider.'&service=tell&component='.self::$component;
                $url .= '&host='.urlencode($CFG->wwwroot);

                $res = curl_init($url);

                curl_exec($res);
                set_config('licensekeycheckdate', time(), self::$component);
            }
        }
    }

    public static function print_empty_license_message() {
        return '<div class="licensing">-- Pro licensed version --<br/>This plugin is being used in proversion without license key for demonstration.</div>';
    }

    public static function set_and_check_license_key($customerkey, $provider = null, $interactive = false) {
        global $CFG, $DB;

        if (empty($provider)) {
            $config = get_config(self::$component);
            $provider = $config->licenseprovider;
        }

        $regusers = $DB->count_records('user', array('deleted' => 0));
        $courses = $DB->count_records('course');
        $coursecats = $DB->count_records('course_categories');

        $url = LOCAL_SHAREDRESOURCES_COMPONENT_PROVIDER_ROUTER_URL;
        $url .= '?provider='.$provider.'&service=set&customerkey='.$customerkey.'&component='.self::$component;
        $url .= '&host='.urlencode($CFG->wwwroot).'&users='.$regusers.'&courses='.$courses.'&coursecats='.$coursecats;

        if (function_exists('debug_trace')) {
            debug_trace($url);
        }

        $res = curl_init($url);
        $result = curl_exec($res);

        // Get result content.
        if (!preg_match('/SET OK/', $result)) {
            // Invalidate key.
            if (!$interactive) {
                set_config('licensekey', $result, self::$component);
                die();
            }
        }

        // Give exact service result without change.
        return $result;
    }
}