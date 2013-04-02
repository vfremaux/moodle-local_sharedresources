
function search_widget_toggle(id){
	
	key = '#search-widget-'+id;
	if ($(key).css('display') == 'none'){
		$(key).css('display', '');
	} else {
		$(key).css('display', 'none');
	}
}

function search_widget_selectall(id){
	key = '#search-widget-'+id+' input[type=checkbox]';
	$(key).attr('checked', true);
}

function search_widget_unselectall(id){
	key = '#search-widget-'+id+' input[type=checkbox]';
	$(key).attr('checked', false);
}