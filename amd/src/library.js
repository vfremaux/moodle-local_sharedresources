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
define(['jquery', 'core/config', 'core/log', 'core/str'], function ($, cfg, log, str) {

    var sharedresourceslibrary = {

        strs: [],

        init: function (args) {

            var stringdefs = [
                {key: 'confirmresourceforcedeletion', component: 'local_sharedresources'}, // 0
                {key: 'confirmresourcedeletion', component: 'local_sharedresources'}, // 1
            ];

            str.get_strings(stringdefs).done(function(s) {
                sharedresourceslibrary.strs = s;
            });

            var that = $(this);

            // Resourceitem hover effect.
            $('.resourceitem').hover(
                function() {
                    that.css('background-color','#fcfcfc');
                },
                function() {
                    that.css('background-color','#f8f8f8');
                }
            );

            $('.sharedresource-toggle-handle').bind('click', this.toggle_info_panel);
            $('.sharedresource-mark-like').on('click', '', args, this.ajax_mark_like);
            $('.sharedresource-actionlink').bind('click', this.integrate);
            $('.sharedresource.delete').bind('click', this.confirm);
            $('.sharedresource.force-delete').bind('click', this.confirm);

            log.debug('ADM Shared resource Library JS initialized');
        },

        ajax_mark_like: function () {

            var identifier = $(this).attr('id').replace('sharedresource-', '');
            var arr = identifier.split('-'); // Has repo-resid form.
            var repoid = arr[0];
            var residentifier = arr[1];

            var url = cfg.wwwroot + '/local/sharedresources/ajax/add_liked_mark.php?';
            url += 'resid=' + residentifier + '&repo=' + repoid;

            $.get(url, '', function(data) {
                $('#sharedresource-likes-' + residentifier).html(sharedresourceslibrary.sharedresource_print_stars(data, 15));
            }, 'html');
        },

        sharedresource_print_stars: function (stars, maxstars) {

            var str = '';

            for (var i = 0; i < maxstars; i++) {
                var icon = (i < stars) ? 'star' : 'star_shadow';
                var pixicon = cfg.wwwroot + '/local/sharedresources/pix/' + icon + '.png';
                str += '<img src="'+ pixicon + '" />';
            }
            return str;
        },

        toggle_info_panel: function () {

            var that = $(this);

            var imgid = that.attr('id');
            var iconsrc;
            var residentifier = imgid.replace('sharedresource-toggle-', '');

            if ($('#sharedresource-info-' + residentifier).css('display') === 'none') {
                $('#sharedresource-info-' + residentifier).css('display', 'block');
                $('#sharedresource-social-' + residentifier).css('display', 'block');
                iconsrc = $('#sharedresource-toggle-' + residentifier).attr('src');
                iconsrc = iconsrc.replace('right', 'top');
                $('#sharedresource-toggle-' + residentifier).attr('src', iconsrc);
            } else {
                $('#sharedresource-info-' + residentifier).css('display', 'none');
                $('#sharedresource-social-' + residentifier).css('display', 'none');
                iconsrc = $('#sharedresource-toggle-' + residentifier).attr('src');
                iconsrc = iconsrc.replace('top', 'right');
                $('#sharedresource-toggle-' + residentifier).attr('src', iconsrc);
            }
        },

        integrate: function() {

            var that = $(this);

            var matches = that.attr('id').match(/id-(\w+)-(\d+)/);
            var command = matches[1];
            var ix = matches[2];
            document.forms['add' + ix].mode.value = command;
            document.forms['add' + ix].submit();
        },

        confirm: function(e) {

            var that = $(this);

            var strindex = 0;
            if (that.hasClass('delete')) {
                strindex = 1;
            }

            if (!confirm(sharedresourceslibrary.strs[strindex])) {
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            }
        }

    };

    return sharedresourceslibrary;
});