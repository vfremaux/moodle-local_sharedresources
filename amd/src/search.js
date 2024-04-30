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
define(['jquery', 'core/log', 'core/str'], function ($, log, str) {

    var sharedresourcessearch = {

        strs: [],

        init: function() {

            var stringdefs = [
                {key: 'moreoptions', component: 'local_sharedresources'}, // 0
                {key: 'lessoptions', component: 'local_sharedresources'}, // 1
            ];

            str.get_strings(stringdefs).done(function(s) {
                sharedresourcessearch.strs = s;
            });

            $('.selectmultiple-selectall').bind('click', this.selectall);
            $('.selectmultiple-unselectall').bind('click', this.unselectall);
            $('#sharedresources-search-reset-btn').bind('click', this.hardreset);
            $('.shr-search-toggle-more').bind('click', this.togglemoreoptions);

            log.debug('AMD sharedresource search form initialized');
        },

        toggle: function() {

            var that = $(this);

            var key = '#search-widget-' + that.attr('id');
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
            $('.selectmultiple-' + id).attr('checked', null);
        },

        hardreset: function () {

            var that = $(this);

            that.closest('form').find(':input').not(':button, :submit, :reset, :hidden, :checkbox, :radio').val('');
            that.closest('form').find(':checkbox, :radio').prop('checked', false);
            // Add query harderest signal to cleat the multiselect lists.
            that.closest('form').find('[name="hardreset"]').attr('value', 1);
            that.closest('form').submit();
        },

        togglemoreoptions: function() {
            var that = $(this);
            var id = that.attr('id').replace('shr-search-toggle-more-', '');
            $('#selectall-morevalues-' + id).toggleClass('hidden');
            if ($('#selectall-morevalues-' + id).hasClass('hidden')) {
                that.addClass('more');
                that.removeClass('less');
                $('#shr-search-toggle-more-' + id + ' span').html(sharedresourcessearch.strs[0]);
            } else {
                that.removeClass('more');
                that.addClass('less');
                $('#shr-search-toggle-more-' + id + ' span').html(sharedresourcessearch.strs[1]);
            }
        }
    };

    return sharedresourcessearch;
});