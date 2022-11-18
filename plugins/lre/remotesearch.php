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
 * @author Valery Fremaux <valery.fremaux@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 * Provides libraries for resource generic access.
 */
defined('MOODLE_INTERNAL') || die();

/*
 * Implements an SQI querier
 */

require_once($CFG->dirroot.'/local/sharedresources/plugin/lre/extlib/sqilib.php');
require_once($CFG->dirroot.'/local/sharedresources/plugin/lre/form_remote_search.class.php');

echo $OUTPUT->heading(get_string('lresearch', 'local_sharedresources'));
echo $OUTPUT->box_start(true, 'emptyleftspace');

$params = array('id' => $courseid, 'repo' => $repo);
$searchform = new Remote_Search_Form(new moodle_url('/local/sharedresources/results.php', $params));

echo '<table width="95%" style="position:relative;left:-60px">';
echo '<tr>';
echo '<td width="120">'.$OUTPUT->pix_icon('lre', '', 'sharedresourceprovider_lre').'</td>';
echo '<td width=\"70%\">';
$searchform->display();
echo '</td></tr></table>';
echo $OUTPUT->box_end('emptyleftspace');
