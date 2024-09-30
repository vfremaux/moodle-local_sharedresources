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
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
defined('MOODLE_INTERNAL') || die();

<<<<<<< HEAD
$plugin->version  = 2024052400;   // The (date) version of this plugin.
=======
$plugin->version  = 2021102100;   // The (date) version of this plugin.
<<<<<<< HEAD
$plugin->requires = 2022041900;   // Requires this Moodle version.
$plugin->component = 'local_sharedresources';
$plugin->release = '4.0.0 (Build 2021102100)';
$plugin->maturity = MATURITY_RC;
$plugin->supported = [40, 40];
$plugin->dependencies = array('local_staticguitexts' => 2013121900);

// Non moodle attributes.
$plugin->codeincrement = '4.0.0004';
=======
>>>>>>> 62dff05934a1d3aeb8a752ff12b704e516c40e87
$plugin->requires = 2022112801;   // Requires this Moodle version.
$plugin->component = 'local_sharedresources';
$plugin->release = '4.1.0 (Build 2024052400)';
$plugin->maturity = MATURITY_STABLE;
$plugin->supported = [401, 402];
/*
 * Do not depend harldy on this component. Sharedresource use it
 * but can ignore it if missing.
 * $plugin->dependencies = array('local_staticguitexts' => 2013121900);
 */

// Non moodle attributes.
<<<<<<< HEAD
$plugin->codeincrement = '4.1.0006';
$plugin->privacy = 'dualrelease';
=======
$plugin->codeincrement = '4.1.0005';
>>>>>>> MOODLE_401_STABLE
$plugin->privacy = 'dualrelease';
>>>>>>> 62dff05934a1d3aeb8a752ff12b704e516c40e87
