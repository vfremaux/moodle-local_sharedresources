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
 * Version details.
 *
 * @package     local_sharedresources
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
defined('MOODLE_INTERNAL') || die();

$plugin->version  = 2018011800;   // The (date) version of this plugin.
$plugin->requires = 2019112200;   // Requires this Moodle version.
$plugin->component = 'local_sharedresources';
$plugin->release = '3.8.0 (Build 2018011800)';
$plugin->maturity = MATURITY_RC;
$plugin->dependencies = array('local_staticguitexts' => 2013121900);

// Non moodle attributes.
$plugin->codeincrement = '3.8.0003';
$plugin->privacy = 'dualrelease';