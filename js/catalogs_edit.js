var search_by_item_number = false;
var plant_list_scroller;
var ajax_request_id = '';
var last_cover_thumbnail_preview_url;

var monrovia_locations = {
	'azusa':'Azusa',
	'cairo':'Cairo',
	'dayton':'Dayton',
	'granby':'Granby',
	'visalia':'Visalia & Venice Hills'
};

Event.observe(window,'load',function(){
	// IF iOS, INIT SCROLLER
	if((/iPhone|iPod|iPad/i).test(navigator.userAgent)) add_touch_scroll('plant_list_scroller');

	// INIT SEARCH FUNCTIONALITY
	if($('form_plant_search')){
		$('form_plant_search').observe('submit',function(evt){
			evt.preventDefault();

			window.scrollTo(0,0);

			// INVOKE SEARCH
			$$('.module_multiselect').each(function(multiselect){
				var field_id = multiselect.id.replace('multiselect_','');
				var input = get_field(field_id);
				var field_value = get_multiselect_module_values(multiselect.id);
				if(input){
					input.value = field_value;
				}else if(field_value){
					input = new Element('input');
					input.type = 'hidden';
					input.name = field_id;
					input.value = field_value;
					$('form_plant_search').appendChild(input);
				}
			});
			
			// PLANT SELECT
			var field_id = 'is_plant_select';
			var input = get_field(field_id);
			var field_value = $('chk_is_plant_select').checked?'1':'0';
			if(input){
				if(field_value=='0'){
					input.remove();
				}else{
					input.value = field_value;
				}
			}else if(field_value){
				input = new Element('input');
				input.type = 'hidden';
				input.name = field_id;
				input.value = field_value;
				$('form_plant_search').appendChild(input);
			}

			get_field('start_page').value = '1';
			perform_search($('form_plant_search').serialize());
		});

		$('form_plant_search_item_number').observe('submit',function(evt){
			evt.preventDefault();
			// INVOKE SEARCH
			perform_search($('form_plant_search_item_number').serialize());
		});
	}
	
	// COVER EDITOR
	if($('ctlTabsEditCovers')){
		var mapped_fields = $$('.field_groups [mapped_field]');
		mapped_fields.each(function(mapped_field){
			mapped_field.observe('focus',function(){
				var mapped_field_name = mapped_field.getAttribute('mapped_field');
				cover_show_outline(mapped_field_name);			
			});
		});
		populate_cover_editor();
	}
	
	window.setTimeout(function(){
		reset_form();
		catalog.refresh();
	},1);
});

function cover_show_outline(mapped_field_name){
	var outlines = $$('#ctlTabsEditCovers .outline')
	outlines.each(function(outline){
		outline.style.display = 'none';
	});
	outlines = $$('#ctlTabsEditCovers .outline[mapped_field="'+mapped_field_name+'"]');
	outlines.each(function(outline){
		outline.style.display = 'block';
	});
}

function select_all(){
	var trs = $$('#results .result:not(.selected):not(.added)');
	trs.each(function(tr){
		tr.addClassName('selected');
	});
}

function unselect_all(){
	var trs = $$('#results .result.selected');
	trs.each(function(tr){
		tr.removeClassName('selected');
	});
}

function display_results(results){

	// OUTPUT PAGINATION
	if(results.pagination_html){
		$$('#results_column .paging').each(function(elt){
			elt.update(results.pagination_html);
		});
	}

	if(results.results.length){
		for(var i=0;i<results.results.length;i++){
			var result = results.results[i];
			
			var tr = $$('#sample_result tr')[0].cloneNode(true);
			
			tr.setAttribute('plant_id',window.parseFloat(result.info['id']));

			var lnk_details = tr.down('a.lnk_details');
			if(result.info['is_active']=='1'){
				lnk_details.setAttribute('href',result.info['details_url']);
				lnk_details.setAttribute('title','View plant details');
			}else{
				lnk_details.style.display = 'none';
				tr.addClassName('inactive');
			}
			tr.down('.thumbnail img').style.backgroundImage = 'url(http://monrovia.wpengine.com/wp-content/uploads/plants/primary.php?id='+result.info['id']+'&version=dt)';
			tr.down('.common_name').update(result.info['common_name']+'&nbsp;');
			tr.down('.botanical_name').update(result.info['botanical_name']+'&nbsp;');
			tr.down('.item_number').update('#'+result.info['item_number']+'&nbsp;');
			tr.down('.types').update(result.info['types_friendly']+'&nbsp;');
			tr.down('.sun_exposures').update(result.info['sun_exposures_friendly']+'&nbsp;');
			tr.down('.cold_zones').update(result.info['cold_zones_friendly']+'&nbsp;');
			tr.down('.collection_name').update(result.info['collection_name']+'&nbsp;');

			tr.plant = result;
			

			$$('#results_table tbody')[0].appendChild(tr);

			tr.down('.thumbnail').observe('click',function(){
				var tr = this.up('tr');
				if(!tr.hasClassName('added')) tr.toggleClassName('selected');
			});
			
			if(catalog.plant_exists(result)){
				tr.addClassName('added');
			}
		};

		$('results_table').style.visibility = 'visible';
		toggle_action_group(true);

		var pagination_items = $$('#results_column .paging a');
		pagination_items.each(function(item){
			item.observe('click',function(evt){
				evt.preventDefault();
				get_field('start_page').value = item.getAttribute('page_num');
				
				if(item.up('#bottom_pagination')) window.scrollTo(0,0);
				
				window.setTimeout(function(){
					perform_search($('form_plant_search').serialize());				
				},1);
			});
		});

	}else{
		$('results_msg').style.display = 'block';
		$('results_msg').update('No plants were found that match your criteria.');
	}

	$('results_column').style.backgroundImage = 'none';
}

function toggle_action_group(show){
	$$('.action_group').each(function(group){
		group.style.visibility = show?'visible':'hidden';
	})
}

function perform_search(parameters){
	$('results_table').style.visibility = 'hidden';
	$('results_column').style.backgroundImage = 'url(/wp-content/uploads/loading.gif)';
	$$('#results_column .paging').each(function(elt){
		elt.update();
	});
	toggle_action_group(false);
	$('results').update();
	$('results_msg').style.display = 'none';
	var this_ajax_request_id = ajax_request_id = Math.random();
	new Ajax.Request('/pdfcatalogs/search.php',{
	  method:'get',parameters:parameters,
	  onComplete:function(transport){
		if(this_ajax_request_id!=ajax_request_id){
			return;
		}else{
			ajax_request_id = null;
		}
		display_results(transport.responseText.evalJSON());
	  }
	});
}

function reset_form(){
	if($('form_plant_search')){
		$('form_plant_search').reset();
		$$('#form_plant_search .module_multiselect').each(function(module){
			module.reset();
		});
		var pagination_items = $$('#results_column .paging');
		pagination_items.each(function(item){
			item.update();
		});
		
		$('results_table').style.visibility = 'hidden';
		toggle_action_group(false);
		$('results').update();
		$('results_msg').style.display = 'block';
		$('results_msg').update('Search for plants to add to your catalog by using<br />one of the search options on the left.');
	}
}

function add_selected_plants(){
	var trs = $$('#results tr.result.selected');
	
	if(!trs.length) return;
	
	var int_added = 0;
	
	trs.each(function(tr,index){	
		if(catalog.plants.length+1>catalog.limit){
			var plants_not_added = trs.length - int_added;
			var msg = 'You have reached the '+catalog.limit+'-plant limit for custom catalogs.<br />';
			if(plants_not_added==1){
				msg += 'One plant you selected was not added.';
			}else{
				msg += plants_not_added + ' of ' + trs.length + ' of the plants you selected were not added.';
			}
			
			show_unobtrusive_message(msg,10000);
			
			catalog.refresh();
			return;
		}else{
			if(!catalog.plant_exists(tr.plant)){
				catalog.plants.push(tr.plant);
				int_added++;
			}
			tr.removeClassName('selected');
			tr.addClassName('added');			
		}
	});
	if(int_added==trs.length){
		var msg = '';
		if(int_added==1){
			msg = 'The plant you selected was added to your catalog.';
		}else{
			msg = 'The ' + int_added + ' plants you selected were added to your catalog.';
		}
		//$('modal_error_message').update(msg);
		//modal_show({'modal_id':'modal_error','effect':'fade'});
		
		show_unobtrusive_message(msg,5000);
	}
	
	catalog.refresh();
	
	// SCROLL TO BOTTOM
	if(int_added==trs.length) $('plant_list_scroller').scrollTop = $('plant_list').clientHeight;
}

catalog.add_plant_listing = function(plant){
	var li = $$('#sample_plant_listing li')[0].cloneNode(true);
	$('plant_list').appendChild(li);
	li.down('.common_name').update(plant.info['common_name']);
	li.down('.botanical_name').update(plant.info['botanical_name']);
	li.down('.item_number').update('#'+plant.info['item_number']);
	if(plant.info['collection_name']) li.down('.collection_name').update(plant.info['collection_name'] + ' Collection');
	li.setAttribute('plant_id',plant.info['id']);
}

catalog.plant_exists = function(plant){
	for(var i=0;i<catalog.plants.length;i++){
		if(catalog.plants[i].info['id']==plant.info['id']) return true;
	}
	return false;
}

catalog.refresh = function(){
	$('plant_list').update('<li class="reordering_above"></li>');
	for(var i=0;i<catalog.plants.length;i++){
		catalog.add_plant_listing(catalog.plants[i]);
	}
	catalog.restore_selections();
	catalog.update_count_indicator();
	var plant_scroller_items = $$('#plant_list li');
	plant_scroller_items.each(function(item){
		item.observe('click',function(){
			this.toggleClassName('selected');
			catalog.plants_selected_ids = [];
			var lis_selected = $$('#plant_list li.selected');
			lis_selected.each(function(li){
				catalog.plants_selected_ids.push(li.getAttribute('plant_id'));
			});
			catalog.update_count_indicator();
		});
	});
	
	if(!catalog.plants.length){
		$('plant_list').update('<li>There are currently no plants in your catalog.</li>');
	}else{
		// MAKE ITEMS UNSELECTABLE
		jQuery('#plant_list li').attr('unselectable', 'on').css('user-select', 'none').on('selectstart', false);
	}
	
}

catalog.get_plant_index = function(plant_id){
	for(var i=0;i<catalog.plants.length;i++){
		if(catalog.plants[i].info.id==plant_id) return i;
	}
	return -1;
}

catalog.get_plant = function(plant_id){
	for(var i=0;i<catalog.plants.length;i++){
		if(catalog.plants[i].info.id==plant_id) return catalog.plants[i];
	}
	return false;
}

catalog.save = function(){
	var plant_ids = '';
	for(var i=0;i<catalog.plants.length;i++){
		plant_ids += ',' + catalog.plants[i].info['id'];
	}
	if(plant_ids) plant_ids = plant_ids.substr(1);

	var catalog_name = ($('catalog_name').value+'').strip();
	get_field('save_catalog_name').value = catalog_name;
	get_field('save_catalog_plant_ids').value = plant_ids;
	
	// CATALOG COVER FIELDS
	if($('ctlTabsEditCovers')){
		get_field('save_catalog_title').value = get_field('cover[title]').value;
		get_field('save_catalog_customer').value = get_field('cover[customer]').value;
		get_field('save_catalog_customer_contact').value = get_field('cover[customer_contact]').value;
		get_field('save_catalog_sales_rep_name').value = get_field('cover[sales_rep_name]').value;
		get_field('save_catalog_additional_info_1').value = get_field('cover[additional_info_1]').value;
		get_field('save_catalog_additional_info_2').value = get_field('cover[additional_info_2]').value;
		get_field('save_catalog_additional_info_3').value = get_field('cover[additional_info_3]').value;

		get_field('save_catalog_plant_1_image_set_id').value = get_field('cover[plant_1_image_set_id]').value;
		get_field('save_catalog_plant_2_image_set_id').value = get_field('cover[plant_2_image_set_id]').value;
		get_field('save_catalog_plant_3_image_set_id').value = get_field('cover[plant_3_image_set_id]').value;
	}
	
	if(!catalog_name){
		$('modal_error_message').update('Please provide a name for your catalog.');
		modal_show({'modal_id':'modal_error','effect':'fade'});
	}else{
		new Ajax.Request('/pdfcatalogs/check_name.php', {
		  method: 'post', parameters:'name='+catalog_name+'&exclude_id='+window.details.queryString()['id'],
		  onComplete:function(transport){
		  	if(transport.responseText=='valid'){
				$('catalog_name').removeClassName('error');
				show_unobtrusive_message('<img src="/wp-content/uploads/icon_loading_yellow.gif" style="margin-top:3px;margin-bottom:-3px;" /> Saving catalog; please wait...',60000);
				$('form_save').submit();
		  	}else{
				$('modal_error_message').update('You already created a catalog called "'+catalog_name+'." Please choose a unique name.');
				modal_show({'modal_id':'modal_error','effect':'fade'});
				$('catalog_name').addClassName('error');
		  	}
		  }
		});
	}
}

catalog.restore_selections = function(){
	var plant_list = $('plant_list');
	for(var i=0;i<catalog.plants_selected_ids.length;i++){
		var li = plant_list.down('li[plant_id="'+catalog.plants_selected_ids[i]+'"]');
		if(li) li.addClassName('selected');
	}
}

catalog.remove_selected = function(){
	if(!catalog.plants_selected_ids.length) return;
	
	var selector = '';
	
	for(var i=0;i<catalog.plants_selected_ids.length;i++){
		for(var x=catalog.plants.length-1;x>-1;x--){
			if(catalog.plants[x].info['id']==catalog.plants_selected_ids[i]){
				catalog.plants.splice(x,1);
				selector += ',.result.added[plant_id="'+catalog.plants_selected_ids[i]+'"]';
			}
		}
	}
	
	if(catalog.plants_selected_ids.length==1){
		show_unobtrusive_message('The plant you selected was removed from your catalog.',3000);
	}else{
		show_unobtrusive_message('The '+catalog.plants_selected_ids.length+' plants you selected were removed from your catalog.',3000);
	}
	
	if(selector){
		var elts = $$(selector.substr(1));
		elts.each(function(elt){
			elt.removeClassName('added');
		});
	}
	
	catalog.plants_selected_ids = [];
	catalog.refresh();
}

catalog.unselect_all = function(){
	catalog.plants_selected_ids = [];
	var lis_selected = $$('#plant_list li.selected');
	lis_selected.each(function(li){
		li.removeClassName('selected');
	});
	catalog.update_count_indicator();
}

catalog.select_all = function(){
	var lis_unselected = $$('#plant_list li:not(.selected):not(.reordering_above)');
	lis_unselected.each(function(li){
		li.addClassName('selected');
		catalog.plants_selected_ids.push(li.getAttribute('plant_id'));
	});
	catalog.update_count_indicator();
}

catalog.update_count_indicator = function(){
	if(!catalog.plants.length){
		$('msg_count_indicator').update();
	}else{
		$('msg_count_indicator').update(' (' + catalog.plants_selected_ids.length + ' selected, ' + catalog.plants.length + ' total)');
	}
}

function show_unobtrusive_message(msg,duration){
	if(!duration) duration = 5000;
	var unobtrusive_message = $('unobtrusive_message');
	unobtrusive_message.update(msg);
	
	window.clearTimeout(window.tmr_unobtrusive_message);
	if(window.effect_unobtrusive_message) window.effect_unobtrusive_message.cancel();

	reposition_unobtrusive_message();

	window.effect_unobtrusive_message = new Effect.Appear(unobtrusive_message,{ 'duration':.5 });
	window.tmr_unobtrusive_message = window.setTimeout(function(){
		window.tmr_unobtrusive_message = null;
		window.effect_unobtrusive_message = new Effect.Fade(unobtrusive_message,{ 'duration':.5 });
	},duration);
}

function reposition_unobtrusive_message(){
	if((/iPhone|iPod|iPad/i).test(navigator.userAgent)){
		var unobtrusive_message = $('unobtrusive_message');
		unobtrusive_message.setStyle({
			'position':'absolute',
			'left':(window.pageXOffset + 10) + 'px',
			'top':(window.pageYOffset + 10) + 'px'
		});
	}
}

function td(className,innerHTML) {
	var ret = document.createElement('td');
	ret.className = className;
	ret.innerHTML = (innerHTML||'');
	return ret;
}

function refresh_locations_table(){
	var locations = ($('save_monrovia_locations').value||'').split(',');
	$$('#table_monrovia_locations tbody')[0].update('');
	for(var i=0;i<locations.length;i++){
		if(locations[i] && locations[i] != 'lagrange'){
			var tr = document.createElement('tr');
			tr.setAttribute('location_id',locations[i]);
			tr.appendChild(new td('location_name',monrovia_locations[locations[i]]));
			
			// AZUSA CANNOT BE DELETED
			if(locations[i]=='azusa'){
				tr.appendChild(new td('action',''));
			}else{
				tr.appendChild(new td('action remove','<img src="/wp-content/uploads/icon_remove.gif" title="Remove" onclick="remove_location(\''+locations[i]+'\');" />'));
			}
			
			tr.appendChild(new td('action up','<img src="/wp-content/uploads/icon_up.gif" title="Move up" onclick="move_location(\''+locations[i]+'\',\'up\');" />'));
			tr.appendChild(new td('action down','<img src="/wp-content/uploads/icon_down.gif" title="Move down" onclick="move_location(\''+locations[i]+'\',\'down\');" />'));
			$$('#table_monrovia_locations tbody')[0].appendChild(tr);

			if(tr.down){
				var remove_button = tr.down('.action.remove');
				if(remove_button) remove_button.observe('click',function(){
					remove_location(tr.getAttribute('location_id'));
				});
			}
		}
	}
	$('select_locations').value = '';
}

function add_monrovia_location(){
	var locations = ($('save_monrovia_locations').value||'').split(',');
	var location_id = $('select_locations').value;
	if(location_id&&array_get_index(locations,location_id)==-1){
		locations.push(location_id);
		$('save_monrovia_locations').value = locations.join(',');
		refresh_locations_table();
		populate_locations_dropdown();
	}
}

function populate_locations_dropdown(){
	var locations = ($('save_monrovia_locations').value||'').split(',');
	$('select_locations').update('<option value="">(Select a location)</option>');
	for(location_id in monrovia_locations){
		if(monrovia_locations.hasOwnProperty(location_id)&&array_get_index(locations,location_id)==-1){
			var option = document.createElement('option');
			option.setAttribute('value',location_id);
			option.innerHTML = monrovia_locations[location_id];
			if(location_id != 'lagrange'){ // Added to prevent lagrange from creating an empty space
				$('select_locations').appendChild(option);
			}
		}
	}
	if($$('#select_locations option').length>1){
		$('add_location').removeClassName('disabled');
		$('select_locations').removeAttribute('disabled');
	}else{
		$('add_location').addClassName('disabled');
		$('select_locations').setAttribute('disabled','disabled');
	}
}

function remove_location(location_id){
	$$('#table_monrovia_locations tr[location_id="'+location_id+'"]')[0].setAttribute('location_id','');
	commit_location_changes();
	populate_locations_dropdown();
}

function move_location(location_id,direction){
	var locations = ($('save_monrovia_locations').value||'').split(',');
	
	var index = window.parseFloat(array_get_index(locations,location_id));
	if(index!=-1){
		if(direction=='up'){
			if(index){
				var a = locations[index-1];
				locations[index] = a;
				locations[index-1] = location_id;
			}
		}else{
			if(index<locations.length-1){
				var a = locations[index+1];
				locations[index] = a;
				locations[index+1] = location_id;
			}
		}
		var location_ids = locations.join(',');
		$('save_monrovia_locations').value = location_ids;
		refresh_locations_table();
	}
}

function commit_location_changes(){
	var trs = $$('#table_monrovia_locations tr');
	var location_ids = '';
	trs.each(function(tr){
		if(tr.getAttribute('location_id')) location_ids += ',' + tr.getAttribute('location_id');
	});
	if(location_ids) location_ids = location_ids.substr(1);
	$('save_monrovia_locations').value = location_ids;
	refresh_locations_table();
}

function plant_dropdown_changed(num,is_manual){
	var dropdown_plant = get_field('cover[plant_'+num+'_id]');
	var dropdown_image = get_field('cover[plant_'+num+'_image_set_id]');
	if(is_manual){
		dropdown_plant.setAttribute('_value',dropdown_plant.value);
		dropdown_image.setAttribute('_value','');
		populate_plant_image_dropdown(num);
	}
}

function plant_image_dropdown_changed(num,is_manual){
	var dropdown_image = get_field('cover[plant_'+num+'_image_set_id]');
	if(is_manual){
		dropdown_image.setAttribute('_value',dropdown_image.value);
		display_plant_image(num);
	}
}

function populate_plant_dropdown(num){
	var plants = [];
	for(var i=0;i<catalog.plants.length;i++){
		plants.push({
			'info':{
				'common_name':catalog.plants[i].info.common_name,
				'botanical_name':catalog.plants[i].info.botanical_name,
				'id':catalog.plants[i].info.id
			}
		});
	}
	var dropdown_plant = get_field('cover[plant_'+num+'_id]');
	var dropdown_image = get_field('cover[plant_'+num+'_image_set_id]');
	dropdown_plant.update('<option value="">(Random plant)</option>');

	if(plants.length){
		for(var x=0;x<plants.length;x++){
			var caption = plants[x].info['common_name'] + ' (' + plants[x].info['botanical_name'];
			dropdown_plant.appendChild(new option(plants[x].info['id'],caption));
		}

		var _value = dropdown_plant.getAttribute('_value');

		if(_value){
			if(dropdown_plant.down('option[value="'+_value+'"]')){
				dropdown_plant.value = _value;
			}else{
				dropdown_plant.value = '';
				dropdown_plant.setAttribute('_value','');
				dropdown_image.value = '';
				dropdown_image.setAttribute('_value','');
			}
		}	
	}else{
		dropdown_plant.value = '';
		dropdown_plant.setAttribute('_value','');
	}
	populate_plant_image_dropdown(num);
}

function populate_plant_dropdowns(){
	for(var i=1;i<=3;i++){
		populate_plant_dropdown(i);
	}
}

function populate_plant_image_dropdown(num){
	var dropdown_plant = get_field('cover[plant_'+num+'_id]');
	var dropdown_image = get_field('cover[plant_'+num+'_image_set_id]');
	dropdown_image.update('');
	if(dropdown_plant.value){
		new Ajax.Request('/ext/get_plant_image_sets.php?id='+dropdown_plant.value,{
		  method:'get',
		  onComplete:function(transport){
			var image_sets = transport.responseText.evalJSON();
			if(image_sets.length){
				var first_id = '';
				for(var i=0;i<image_sets.length;i++){
					if(!first_id) first_id = image_sets[i].id;
					dropdown_image.appendChild(new option(image_sets[i].id,image_sets[i].title));
				}
				
				var _value = dropdown_image.getAttribute('_value');
				if(_value){
					if(dropdown_image.down('option[value="'+_value+'"]')){
						dropdown_image.value = _value;
						display_plant_image(num);
					}else{
						dropdown_image.value = '';
						dropdown_image.setAttribute('_value','');
						dropdown_plant.value = '';
						dropdown_plant.setAttribute('_value','');
						populate_plant_image_dropdown(num);
					}
				}else{
					// DEFAULT TO FIRST IMAGE
					if(first_id){
						dropdown_image.value = first_id;
						dropdown_image.setAttribute('_value',first_id);
						display_plant_image(num);
					}
				}
			}else{
				dropdown_image.value = '';
				dropdown_image.setAttribute('_value','');
				display_plant_image(num);
			}
		  }
		});		
	}else{
		dropdown_image.value = '';
		dropdown_image.setAttribute('_value','');
		display_plant_image(num);
	}
}

function update_cover_thumbnails(){
	if(ajax_request_id) return;
	var url = '/pdfcatalogs/generate_preview_thumbnail.php?template_id='+catalog.template_id+'&title='+window.escape(get_field('cover[title]').value)+'&customer='+window.escape(get_field('cover[customer]').value)+'&customer_contact='+window.escape(get_field('cover[customer_contact]').value)+'&plant_1_image_set_id='+window.escape(get_field('cover[plant_1_image_set_id]').value)+'&plant_2_image_set_id='+window.escape(get_field('cover[plant_2_image_set_id]').value)+'&plant_3_image_set_id='+window.escape(get_field('cover[plant_3_image_set_id]').value)+'&sales_rep_name='+window.escape(get_field('cover[sales_rep_name]').value)+'&additional_info_1='+window.escape(get_field('cover[additional_info_1]').value)+'&additional_info_2='+window.escape(get_field('cover[additional_info_2]').value)+'&additional_info_3='+window.escape(get_field('cover[additional_info_3]').value)+'&monrovia_locations='+window.escape(get_field('catalog[monrovia_locations]').value);
	
	if(url==last_cover_thumbnail_preview_url) return;
	last_cover_thumbnail_preview_url = url;
	
	var front_covers = $$('.cover_thumbnail_front');
	var back_covers = $$('.cover_thumbnail_back');
	front_covers.each(function(front_cover){
		front_cover.setAttribute('src','/wp-content/uploads/spacer.gif');
	});
	back_covers.each(function(back_cover){
		back_cover.setAttribute('src','/wp-content/uploads/spacer.gif');
	});

	var this_ajax_request_id = ajax_request_id = Math.random();
	new Ajax.Request(url, {
	  method: 'get',
	  onComplete:function(transport){
		var src_front = '/downloads/pdf/custom_catalogs/temp/thumbnail_front.jpg?rnd='+Math.random();
		var src_back = '/downloads/pdf/custom_catalogs/temp/thumbnail_back.jpg?rnd='+Math.random();
		console.log(url);
		console.log(transport.responseText);

		if(transport.responseText=='fail') src_front = src_back = '/wp-content/uploads/catalog_cover_error.gif';
		front_covers.each(function(front_cover){
			front_cover.setAttribute('src',src_front);
		});
		back_covers.each(function(back_cover){
			back_cover.setAttribute('src',src_back);
		});
		if(ajax_request_id==this_ajax_request_id) ajax_request_id = null;
	  }
	});
}

function display_plant_image(num){
	var dropdown_plant = get_field('cover[plant_'+num+'_id]');
	var dropdown_image = get_field('cover[plant_'+num+'_image_set_id]');
	var thumbnail = dropdown_plant.up('.plant_image_field').down('.image_thumbnail');
	
	if(dropdown_plant.value&&dropdown_image.value){
		thumbnail.setAttribute('src','/wp-content/uploads/plants/image_set_thumbail.php?id='+dropdown_image.value+'&width=90&height=90');	
	}else{
		thumbnail.setAttribute('src','/wp-content/uploads/catalog_cover_plant_random.gif');
	}
}

function option(value,caption){
	var option = document.createElement('option');
	option.setAttribute('value',value);
	option.innerHTML = caption;
	return option;
}

function populate_cover_editor(){
	refresh_locations_table();
	populate_locations_dropdown();
	populate_plant_dropdowns();
	
}

function launch_cover_editor(){
	populate_cover_editor();
	modal_show({'modal_id':'modal_edit_cover','effect':'fade'});
}


Event.observe(window,'resize',reposition_unobtrusive_message);
Event.observe(window,'scroll',reposition_unobtrusive_message);