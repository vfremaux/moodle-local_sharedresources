<?php

    require_once($CFG->libdir.'/formslib.php');

    class Relocate_Test_Form extends moodleform{
        
        function definition() {
            global $CFG;
            
            // Setting variables
            $mform =& $this->_form;
            
            // Adding title and description
            echo "Testing resource remote relocation to newrepo and some newurl";

            if ($providers = get_providers()) {
                foreach ($providers as $provider) {
                    echo "$provider->id => $provider->name<br/>";
                }
            } else {
                echo "<p>No providers</p>";
            }

            if ($consumers = get_consumers()) {            
                foreach ($consumers as $consumer) {
                    $consumeropts[$consumer->id] = $consumer->name;
                }
                $mform->addElement('select', 'consumer', 'consumers', $consumeropts);
            } else {
                echo "<p>No consumers</p>";
            }

            $mform->addElement('text', 'identifier', 'Identifier');
            $mform->addElement('text', 'targetrepo', 'Target repo name');
            
            // Adding submit and reset button
            $buttonarray = array();
            $buttonarray[] = &$mform->createElement('submit', 'go_confirm', get_string('confirm'));
            $buttonarray[] = &$mform->createElement('cancel', 'go_cancel', get_string('cancel'));

            $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);            
        }
    }

?>