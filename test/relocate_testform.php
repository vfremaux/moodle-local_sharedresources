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

require_once($CFG->libdir.'/formslib.php');

class Relocate_Test_Form extends moodleform{

    public function definition() {
        global $CFG;

        // Setting variables
        $mform =& $this->_form;

        // Adding title and description
        echo "Testing resource remote relocation to newrepo and some newurl";

        if ($providers = sharedresources_get_providers()) {
            foreach ($providers as $provider) {
                echo "$provider->id => $provider->name<br/>";
            }
        } else {
            echo "<p>No providers</p>";
        }

        if ($consumers = sharedresources_get_consumers()) {
            foreach ($consumers as $consumer) {
                $consumeropts[$consumer->id] = $consumer->name;
            }
            $mform->addElement('select', 'consumer', 'consumers', $consumeropts);
        } else {
            echo "<p>No consumers</p>";
        }

        $mform->addElement('text', 'identifier', 'Identifier');
        $mform->addElement('text', 'targetrepo', 'Target repo name');

        // Adding submit and reset button.
        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'go_confirm', get_string('confirm'));
        $buttonarray[] = &$mform->createElement('cancel', 'go_cancel', get_string('cancel'));

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }
}

