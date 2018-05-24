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
 * Defines form to add a new project
 *
 * @package    local_sharedresources
 * @category   local
 * @subpackage plugins
 * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/local/sharedresources/plugins/lre/sqilib.php');

class Remote_Search_Form extends moodleform {

    public function __construct($action) {
        parent::__construct($action);
    }

    public function definition() {
        global $CFG;

        // Setting variables.
        $mform =& $this->_form;
        
        $langroot = $CFG->dirroot.'/resources/plugins/lre/lang/';

        // Adding fieldset.

        $mform->addElement('hidden', 'incoming', 1);

        $ageoptions = SQIGetAgeRangeOptions();
        $languageoptions = SQIGetLoLanguages();
        $lrtoptions = SQIGetLearningResourceTypeOptions();

        $mform->addElement('text', 'query', get_string('query', 'lre', '', $langroot));
        $mform->setType('query', PARAM_RAW);

        $mform->addElement('select', 'minAge', get_string('minage', 'lre', '', $langroot), $ageoptions);
        $mform->setType('minAge', PARAM_INT);
        $mform->addElement('select', 'maxAge', get_string('maxage', 'lre', '', $langroot), $ageoptions);
        $mform->setType('maxAge', PARAM_INT);

        $mform->addElement('select', 'loLanguage', get_string('lolanguage', 'lre', '', $langroot), $languageoptions);
        $mform->setType('loLanguage', PARAM_TEXT);
        $mform->addElement('select', 'mtdLanguage', get_string('mtdlanguage', 'lre', '', $langroot), $languageoptions);
        $mform->setType('mtdLanguage', PARAM_TEXT);

        $mform->addElement('select', 'lrt', get_string('lrt', 'lre', '', $langroot), $lrtoptions);
        $mform->setType('lrt', PARAM_TEXT);

        $this->add_action_buttons(false, get_string('search'));
    }

    /**
     * validates inputs
     */
    public function validation($data, $files = array()) {
        $errors = parent::validation($data, $files);

        return $errors;
    }
}
