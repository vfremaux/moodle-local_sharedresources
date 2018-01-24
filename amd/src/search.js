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
 *
 * @module     local_sharedresource/library
 * @package    local
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// jshint unused: true, undef:true
define(['jquery', 'core/log'], function ($, log) {

    var sharedresourcessearch = {

        init: function() {
            $('.selectmultiple-selectall').bind('click', this.selectall);
            $('.selectmultiple-unselectall').bind('click', this.unselectall);

            log.debug('AMD sharedresource search form initialized');
        },

        toggle: function() {

            that = $(this);

            key = '#search-widget-' + that.attr('id');
            if ($(key).css('display') == 'none') {
                $(key).css('display', '');
            } else {
                $(key).css('display', 'none');
            }
        },

        /**
         * Select all options in a single multiselect widget.
         */
        selectall: function() {

            var that = $(this);

            var id = that.attr('id').replace('selectall-', '');
            $('.selectmultiple-' + id).attr('checked', true);
        },

        /**
         * Unselect all options in a single multiselect widget.
         */
        unselectall: function() {

            var that = $(this);

            var id = that.attr('id').replace('unselectall-', '');
            $('.selectmultiple-' + id).attr('checked', true);
        },
    };

    return sharedresourcessearch;
});