/*
 *
 */
// jshint undef:false, unused:false
$(document).ready(function() {

    // Resourceitem hover effect.
    $('.resourceitem').hover(
    function() {
        $(this).css('background-color','#fcfcfc');
    },
    function() {
        $(this).css('background-color','#f8f8f8');
    });
});

function ajax_mark_liked(repo, residentifier) {
    url = M.cfg.wwwroot + '/local/sharedresources/ajax/add_liked_mark.php?';
    url += 'resid=' + residentifier + '&repo=' + repo;

    newlike = $.get(url, '', function(data, textStatus) {
        $('#sharedresource-liked-'+residentifier).html(sharedresource_print_stars(data, 15));
    });
}

function sharedresource_print_stars(stars, maxstars) {
    str = '';

    for (i = 0; i < maxstars; i++) {
        icon = (i < stars) ? 'star' : 'star_shadow';
        pixicon = M.cfg.wwwroot + '/local/sharedresources/pix/' + icon + '.png';
        str += '<img src="'+ pixicon + '" />';
    }
    return str;
}

function toggle_info_panel(panelid) {
    if ($('#resource-info-' + panelid).css('display') === 'none') {
        $('#resource-info-' + panelid).css('display', 'block');
        iconsrc = $('#resource-toggle-' + panelid).attr('src');
        iconsrc = iconsrc.replace('right', 'top');
        $('#resource-toggle-' + panelid).attr('src', iconsrc);
    } else {
        $('#resource-info-' + panelid).css('display', 'none');
        iconsrc = $('#resource-toggle-' + panelid).attr('src');
        iconsrc = iconsrc.replace('top', 'right');
        $('#resource-toggle-' + panelid).attr('src', iconsrc);
    }
}