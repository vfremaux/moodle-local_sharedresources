$(document).ready(function(){

    //resourceitem hover effect
    $('.resourceitem').hover(
    function(){
        
        $(this).css('background-color','#fcfcfc');
    },
    function(){
        
        $(this).css('background-color','#f8f8f8');
    });
    
});

function ajax_mark_liked(resid, wwwroot){
	newlike = $.get(wwwroot+'/local/sharedresource/ajax/add_liked_mark.php?resid='+resid, '', function(data, textStatus){
		$('#sharedresource-liked-'+resid).html(sharedresource_print_stars(data, 15, wwwroot));
	});
}

function sharedresource_print_stars(stars, maxstars, wwwroot){
	str = '';
	
	for(i = 0 ; i < maxstars ; i++){
		icon = (i < stars) ? 'star' : 'star_shadow';
		str += '<img src="'+wwwroot+'/local/sharedresource/pix/'+icon+'.png" />';
	}
	return str;
}