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

    return {

        init: function (args) {

            that = this;

            // Resourceitem hover effect.
            $('.resourceitem').hover(
                function() {
                    $(this).css('background-color','#fcfcfc');
                },
                function() {
                    $(this).css('background-color','#f8f8f8');
                }
            );

            $('.sharedresource-toggle-handle').bind('click', this.toggle_info_panel);
            $('.sharedresource-mark-like').on('click', '', args, this.ajax_mark_like);
            $('.sharedresource-actionlink').bind('click', this.integrate);
        },

        ajax_mark_like: function (e) {
            residentifier = $(this).attr('id').replace('sharedresource-', '');
            url = cfg.wwwroot + '/local/sharedresources/ajax/add_liked_mark.php?';
            url += 'resid=' + residentifier + '&repo=' + e.data;

            newlike = $.get(url, '', function(data, textStatus) {
                $('#sharedresource-likes-' + residentifier).html(that.sharedresource_print_stars(data, 15));
            }, 'html');
        },

        sharedresource_print_stars: function (stars, maxstars) {
            str = '';

            for (i = 0; i < maxstars; i++) {
                icon = (i < stars) ? 'star' : 'star_shadow';
                pixicon = cfg.wwwroot + '/local/sharedresources/pix/' + icon + '.png';
                str += '<img src="'+ pixicon + '" />';
            }
            return str;
        },

        toggle_info_panel: function (e) {
            that = $(this);
            imgid = that.find('img').attr('id');
            residentifier = imgid.replace('sharedresource-toggle-', '');

            if ($('#sharedresource-info-' + residentifier).css('display') === 'none') {
                $('#sharedresource-info-' + residentifier).css('display', 'block');
                iconsrc = $('#sharedresource-toggle-' + residentifier).attr('src');
                iconsrc = iconsrc.replace('right', 'top');
                $('#sharedresource-toggle-' + residentifier).attr('src', iconsrc);
            } else {
                $('#sharedresource-info-' + residentifier).css('display', 'none');
                iconsrc = $('#sharedresource-toggle-' + residentifier).attr('src');
                iconsrc = iconsrc.replace('top', 'right');
                $('#sharedresource-toggle-' + residentifier).attr('src', iconsrc);
            }
        },

        integrate: function() {

            var that = $(this);

            var matches = that.attr('id').match(/id-(\w+)-(\d+)/);
            command = matches[1];
            ix = matches[2];
            document.forms['add' + ix].mode.value = command;
            document.forms['add' + ix].submit();
        }

    };
});