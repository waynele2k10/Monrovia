function accordion_segment_toggle(segment){
	segment.toggleClassName('expanded');
}

Event.observe(window,'load',function(){
	// ACCORDION SEGMENTS
	$$('.accordion_segment .title').each(function(title){
		var segment = title.up('.accordion_segment');
		title.observe('click',function(){accordion_segment_toggle(segment);});
	});
	// PAGE NAV ITEMS
	$$('.page_nav .nav_item').each(function(nav_item){
		var tab_id = nav_item.getAttribute('tab_id');
		nav_item.observe('click',function(){
			page_nav_items_clear();
			this.addClassName('selected');

			var page_nav_tab_header = $('page_nav_tab_header_'+tab_id);
			if(page_nav_tab_header) page_nav_tab_header.style.display = 'block';

			page_nav_show_segments(tab_id);

		});
	});
	// RE-SELECT SELECTED ITEM
	var nav_item_selected = $$('.nav_item.selected')[0];
	if(nav_item_selected){
		var tab_id = nav_item_selected.getAttribute('tab_id');
		page_nav_show_segments(tab_id);
		var page_nav_tab_header = $('page_nav_tab_header_'+nav_item_selected.getAttribute('tab_id'));
		if(page_nav_tab_header) page_nav_tab_header.style.display = 'block';
	}
});

function page_nav_show_segments(tab_id){
	var cssSelector = (location_id)?'.location_'+location_id:'';
	$$('.accordion_segment'+cssSelector).each(function(segment){
		segment.style.display = 'block';
	});
}

function page_nav_items_clear(){
	$$('.page_nav .nav_item').each(function(nav_item){
		nav_item.removeClassName('selected');
	});
	$$('.location_header, .accordion_segment').each(function(elt){
		elt.style.display = 'none';

		// IF ACCORDION SEGMENT, RESET TO COLLAPSED
		if(elt.hasClassName('accordion_segment')) elt.removeClassName('expanded');
	});
}