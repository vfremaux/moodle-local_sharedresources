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
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright   (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 * the master shared resources administration view entry point.
 * the administration view let you browse through resources with a
 * capacity to validate, delete, suspend and reindex resources on the
 * local repository.
 */
require('../../../../config.php');

require_once($CFG->dirroot.'/local/sharedresources/lib.php');

$context = context_system::instance();

require_login();
require_capability('repository/sharedresources:manage', $context);

$PAGE->set_pagelayout('standard');
$PAGE->set_context($context);
$PAGE->set_title(get_string('adminrepository', 'local_sharedresources'));
$PAGE->set_heading($SITE->fullname); 
$PAGE->navbar->add(get_string('adminrepository', 'local_sharedresources'), 'view.php', 'misc');

$PAGE->set_focuscontrol('');
$PAGE->set_cacheable(false);
$PAGE->set_button('');
$PAGE->set_headingmenu('');

$url = new moodle_url('/local/sharedresources/pro/admin/view.php');
$PAGE->set_url($url);

$libadmin = get_library_admin();

echo $OUTPUT->header();

// Provide infor about all bound repos (remote)
echo $OUTPUT->heading(get_string('repos', 'local_sharedresources'));

// Provide infor about all local resource
echo $OUTPUT->heading(get_string('local', 'local_sharedresources'));

echo $OUTPUT->heading(get_string('localstats', 'local_sharedresources'), 3);

echo $OUTPUT->heading(get_string('localstatus', 'local_sharedresources'), 3);

echo $OUTPUT->footer();