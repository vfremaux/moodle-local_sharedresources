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

function ajax_mark_liked(wwwroot, repo, residentifier){
	newlike = $.get(wwwroot+'/local/sharedresources/ajax/add_liked_mark.php?resid='+residentifier+'&repo='+repo, '', function(data, textStatus){
		$('#sharedresource-liked-'+residentifier).html(sharedresource_print_stars(data, 15, wwwroot));
	});
}

function sharedresource_print_stars(stars, maxstars, wwwroot){
	str = '';
	
	for(i = 0 ; i < maxstars ; i++){
		icon = (i < stars) ? 'star' : 'star_shadow';
		str += '<img src="'+wwwroot+'/local/sharedresources/pix/'+icon+'.png" />';
	}
	return str;
}