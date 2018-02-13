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
define(['jquery', 'core/config', 'core/log'], function ($, cfg, log) {

    var libraryboxview = {

        init: function (args) {

            that = this;

            // Resourceitem hover effect.
            $('.box-resource-images').hover(this.opendesc, this.closedesc);
            $('.box-resource-titles').hover(this.opendesc, this.closedesc);

            log.debug('ADM Shared resource Library JS initialized');
        },

        opendesc: function (e) {
            var that = $(this);

            // Remove either prefixs.
            var identifier = that.attr('id').replace('sharedresource-image-', '');
            identifier = identifier.replace('sharedresource-title-', '');
            $('.sharedresource-info-box').css('display', 'none'); // Ensure all is closed.
            $('#sharedresource-info-' + identifier).css({'top':e.pageY,'left':e.pageX}).fadeIn('fast');
            $('#sharedresource-info-' + identifier).css('display', 'inline-block');
        },

        closedesc: function (e) {
            var that = $(this);

            // Remove either prefixs.
            var identifier = that.attr('id').replace('sharedresource-image-', '');
            identifier = identifier.replace('sharedresource-title-', '');

            $('#sharedresource-info-' + identifier).css('display', 'none');
        },

    };

    return libraryboxview;
});