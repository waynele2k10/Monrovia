Event.observe(window,'load',function(){
	// PAGE NAV ITEMS
	$$('.page_nav .nav_item').each(function(nav_item,nav_index){
		var tab_id = nav_item.getAttribute('tab_id');
		var page_nav = nav_item.up('table.page_nav');
		nav_item.observe('click',function(){
			page_nav_item_select(page_nav,tab_id);
		});
		nav_item.observe('mouseenter',function(){
			page_nav_item_hover(nav_item,nav_index);
		});
		nav_item.observe('mouseleave',function(){
			page_nav_item_clear(nav_item,nav_index);
		});
	});
	
	// RE-SELECT SELECTED ITEMS
	$$('table.page_nav').each(function(page_nav){
		var nav_item_selected = page_nav.down('.nav_item.selected');
		if(nav_item_selected) page_nav_item_select(page_nav,nav_item_selected.getAttribute('tab_id'));
	});
});

function page_nav_item_hover(item,index){
	item.addClassName('selected');
	if(index==0){
		// SET BACKGROUND OF LEFT SIDE THING
		$('left_corner').style.backgroundImage = 'url(/img/page_nav_left_selected.gif)';
		
	} else {
		var dividers = item.previousSiblings('.nav_divider');
		if(dividers) dividers[0].addClassName('selected');
	}				
}

function page_nav_item_clear(item,index){
	if (!item.childElements()[1].hasClassName('selected')) {
		if(index!=0){
			// DESELECT PRECEDING DIVIDER
			var dividers = item.previousSiblings('.nav_divider');
			if(dividers) dividers[0].removeClassName('selected');								
		}
		else {
			// SET BACKGROUND OF LEFT SIDE THING
			$('left_corner').style.backgroundImage = 'url(/img/page_nav_left.gif)';
		}
		
		item.removeClassName('selected');
		$('left_corner').removeClassName('selected');
	}
}

function page_nav_item_select(page_nav,tab_id){
	
	var page_nav_items = page_nav.getElementsBySelector('.nav_item');
	
	page_nav_items_clear(page_nav);
	var page_nav_item = page_nav.down('.nav_item[tab_id=\''+tab_id+'\']');
	
	var item_is_first = (page_nav_item===page_nav_items[0]);
	
	// SET BACKGROUND OF LEFT SIDE THING
	$('left_corner').style.backgroundImage = (item_is_first)?'url(/img/page_nav_left_selected.gif)':'url(/img/page_nav_left.gif)';
	
	// DESELECT SELECTED DIVIDERS AND TRIANGLES
	var selected_elts = page_nav.getElementsBySelector('.nav_divider.selected,.triangle.selected');
	if(selected_elts){
		selected_elts.each(function(elt){
			elt.removeClassName('selected');
		});	
	}
	
	// TOGGLE PRECEDING DIVIDER
	if(!item_is_first){
			var dividers = page_nav_item.previousSiblings('.nav_divider');
			if(dividers) dividers[0].addClassName('selected');
	}
	
	// SHOW TRIANGLE
	page_nav_item.down('.triangle').addClassName('selected');
	
	page_nav_item.addClassName('selected');
	var nav_item_content = page_nav.down('div.nav_item_content[tab_id=\''+tab_id+'\']');
	if(nav_item_content) nav_item_content.addClassName('selected');

	Event.fire(page_nav,'widget:nav_item_selected',tab_id);
	
	
}
function page_nav_items_clear(page_nav){
	page_nav.select('.nav_item,.nav_item_content').each(function(div){
		div.removeClassName('selected');
	});
}