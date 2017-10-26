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
 * forms for converting resources to sharedresources
 *
 * @package    local_sharedresources
 * @category   local
 * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */
defined('MOODLE_INTERNAL') || die();

require $CFG->libdir.'/formslib.php';

class sharedresource_massimport_form extends moodleform {

    function __construct($courses) {
        parent::moodleform();
    }

    function definition() {
        $mform = & $this->_form;

        $mform->addElement('hidden', 'courseid', $this->_customdata['course']);
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('header', 'importvolumehdr', get_string('importvolume', 'local_sharedresources'));

        $mform->addElement('text', 'importpath', get_string('importpath', 'local_sharedresources'), array('size' => 80));
        $mform->setType('importpath', PARAM_TEXT);
        $mform->addHelpButton('importpath', 'importpath', 'local_sharedresources');

        $label = get_string('exclusionpattern', 'local_sharedresources');
        $mform->addElement('text', 'importexclusionpattern', $label, array('size' => 50));
        $mform->setType('importexclusionpattern', PARAM_TEXT);
        $mform->addHelpButton('importexclusionpattern', 'exclusionpattern', 'local_sharedresources');

        $mform->addElement('checkbox', 'deducetaxonomyfrompath', get_string('deducetaxonomyfrompath', 'local_sharedresources'));
        $mform->setType('deducetaxonomyfrompath', PARAM_BOOL);
        $mform->addHelpButton('deducetaxonomyfrompath', 'deducetaxonomyfrompath', 'local_sharedresources');

        /*
         * sharing context :
         * users can share a sharedresource at public system context level, or share privately to a specific course category
         * (and subcatgories)
         */
        $contextopts[1] = get_string('systemcontext', 'sharedresource');
        sharedresource_add_accessible_contexts($contextopts);
        $mform->addElement('select', 'context', get_string('sharingcontext', 'sharedresource'), $contextopts);
        $mform->setType('context', PARAM_INT);
        $mform->addHelpButton('context', 'sharingcontext', 'sharedresource');

        $mform->addElement('header', 'resetvolumehdr', get_string('resetvolume', 'local_sharedresources'));

        $mform->addElement('checkbox', 'resetvolume', get_string('doresetvolume', 'local_sharedresources'));
        $mform->setType('resetvolume', PARAM_BOOL);
        $mform->addHelpButton('resetvolume', 'resetvolume', 'local_sharedresources');

        // Adding submit and reset button.
        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'go_submit', get_string('submit'));
        $buttonarray[] = &$mform->createElement('cancel', 'go_cancel', get_string('cancel'));

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);

        $mform->closeHeaderBefore('buttonar');
    }
}
