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
 * Form to push a resource to an outdoors repository.
 *
 * @package     local_sharedresources
 * @author Valery Fremaux <valery@gmail.com>
 * @copyright Valery Fremaux (activeprolearn.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * Form definition.
 */
class PushOut_Form extends moodleform {

    /** @var the resource identifier */
    protected $resourceid;

    /**
     * Constructor
     * @param int $resourceid the resource identifier
     */
    public function __construct($resourceid) {
        $this->resourceID = $resourceid;
        parent::__construct();
    }

    /**
     * Standard definition.
     */
    public function definition() {
        global $CFG, $OUTPUT;

        // Setting variables.
        $mform =& $this->_form;

        // Adding title and description.
        $mform->addElement('html', $OUTPUT->heading(get_string('export', 'sharedresource')));

        $buttonarray = [];

        $providers = sharedresources_get_providers();

        if (count($providers) > 1) {
            foreach ($providers as $provider) {
                $provideropts[$provider->id] = $provider->name;
            }
            $mform->addElement('html', get_string('chooseprovidertopushto', 'sharedresource', $providers[0]->name));
            $mform->addElement('select', 'provider', get_string('providers', 'sharedresource'), 0, $provideropts);
            $mform->setType('provider', PARAM_TEXT);

            $mform->addElement('hidden', 'resourceid', $this->resourceID);
            $mform->setType('resourceid', PARAM_INT);
            $buttonarray[] = &$mform->createElement('submit', 'go_confirm', get_string('confirm'));
        } else if (count($providers) == 1) {
            $providers = array_values($providers);
            $mform->addElement('html', get_string('pushtosingleprovider', 'sharedresource', $providers[0]->name));

            $mform->addElement('hidden', 'provider', $providers[0]->id);
            $mform->setType('provider', PARAM_TEXT);

            $mform->addElement('hidden', 'resourceid', $this->resourceID);
            $mform->setType('resourceid', PARAM_INT);
            $buttonarray[] = &$mform->createElement('submit', 'go_confirm', get_string('confirm'));
        } else {
            $mform->addElement('hidden', 'resourceid', $this->resourceID);
            $mform->setType('resourceid', PARAM_INT);
            $mform->addElement('html', get_string('noprovidertopushto', 'sharedresource'));
        }

        // Adding submit and reset button.
        $buttonarray[] = &$mform->createElement('cancel', 'go_cancel', get_string('cancel'));

        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
    }

    /**
     * validates the form and incomming data.
     * @param object $data
     * @param array $files
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (empty($data['provider'])) {
            $errors['provider'] = get_string('emptyprovidererror', 'sharedresource');
        }

        return $errors;
    }
}
