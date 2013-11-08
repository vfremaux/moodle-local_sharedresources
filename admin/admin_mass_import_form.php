<?php  // $Id: admin_convert_form.php,v 1.1 2013-02-13 21:56:39 wa Exp $

/**
* forms for converting resources to sharedresources
*
* @package    mod-sharedresource
* @category   mod
* @author     Valery Fremaux <valery.fremaux@club-internet.fr>
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
* @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
*/

/**
* Includes and requires
*/
require $CFG->libdir.'/formslib.php';


class sharedresource_massimport_form extends moodleform{

    function __construct($courses){
        parent::moodleform();
    }
 
    function definition(){
        $mform = & $this->_form;

		$mform->addElement('hidden', 'courseid', $this->_customdata['course']);
		$mform->setType('courseid', PARAM_INT);

		$mform->addElement('text', 'importpath', get_string('importpath', 'local_sharedresources'), array('size' => 80));
		$mform->setType('importpath', PARAM_TEXT);

		$mform->addElement('text', 'importexclusionpattern', get_string('exclusionpattern', 'local_sharedresources'), array('size' => 50));
		$mform->setType('importexclusionpattern', PARAM_TEXT);

		$mform->addElement('checkbox', 'deducetaxonomyfrompath', get_string('deducetaxonomyfrompath', 'local_sharedresources'));
		$mform->setType('deducetaxonomyfrompath', PARAM_BOOL);

		// sharing context : 
		// users can share a sharedresource at public system context level, or share privately to a specific course category
		// (and subcatgories)
		$contextopts[1] = get_string('systemcontext', 'sharedresource');
		sharedresource_add_accessible_contexts($contextopts);
		$mform->addElement('select', 'context', get_string('sharingcontext', 'sharedresource'), $contextopts);
		$mform->setType('context', PARAM_INT);
        $mform->addHelpButton('context', 'sharingcontext', 'sharedresource');

		$mform->addElement('header', 'resetvolumehdr', get_string('resetvolume', 'local_sharedresources'));

		$mform->addElement('checkbox', 'resetvolume', get_string('doresetvolume', 'local_sharedresources'));
		$mform->setType('resetvolume', PARAM_BOOL);
		$mform->addHelpButton('resetvolume', 'resetvolume', 'local_sharedresources');


		// Adding submit and reset button
        $buttonarray = array();
    	$buttonarray[] = &$mform->createElement('submit', 'go_submit', get_string('submit'));
    	$buttonarray[] = &$mform->createElement('cancel', 'go_cancel', get_string('cancel'));
        
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);

		$mform->closeHeaderBefore('buttonar');
    }
}
