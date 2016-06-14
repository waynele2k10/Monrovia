monrovia.sections.qa = {};

/* BEGIN GENERAL STUFF */

monrovia.sections.qa.set_subscribe_status = function(args,callback){
	if(!monrovia.runtime_data.qa_action_pending){
		monrovia.runtime_data.qa_action_pending = true;
		new Ajax.Request('/'+monrovia.config.qa_root+'/action.php',{
			method: 'post', parameters:'action=set_subscribe_status&item_id='+args.item_id+'&item_type='+args.item_type+'&value='+(args.value?'true':'false'),
			onComplete:function(){
				monrovia.runtime_data.qa_action_pending = false;
				if(typeof callback!='undefined') callback();
			}
		});
	}
}

monrovia.sections.qa.set_scrapbook_status = function(args,callback){
	if(!monrovia.runtime_data.qa_action_pending){
		monrovia.runtime_data.qa_action_pending = true;
		new Ajax.Request('/'+monrovia.config.qa_root+'/action.php',{
			method: 'post', parameters:'action=set_scrapbook_status&item_id='+args.item_id+'&item_type='+args.item_type+'&value='+(args.value?'true':'false'),
			onComplete:function(){
				monrovia.runtime_data.qa_action_pending = false;
				if(typeof callback!='undefined') callback();
			}
		});
	}
}

monrovia.sections.qa.set_vote = function(args,callback){
	if(!monrovia.runtime_data.qa_action_pending){
		monrovia.runtime_data.qa_action_pending = true;
		new Ajax.Request('/'+monrovia.config.qa_root+'/action.php',{
			method: 'post', parameters:'action=set_vote&item_id='+args.item_id+'&item_type='+args.item_type+'&direction='+args.direction,
			onComplete:function(response){
				monrovia.runtime_data.qa_action_pending = false;
				if(typeof callback!='undefined') callback(response);
			}
		});
	}
}

monrovia.sections.qa.flag_item = function(args,callback){
	if(!monrovia.runtime_data.qa_action_pending){
		monrovia.runtime_data.qa_action_pending = true;
		new Ajax.Request('/'+monrovia.config.qa_root+'/action.php',{
			method: 'post', parameters:'action=set_flagged_status&item_id='+args.item_id+'&item_type='+args.item_type+'&reason_id=0',
			onComplete:function(){
				monrovia.runtime_data.qa_action_pending = false;
				if(typeof callback!='undefined') callback();
			}
		});
	}
}

monrovia.sections.qa.get_category_info = function(category_id,callback){
	if(!monrovia.runtime_data.qa_action_pending){
		monrovia.runtime_data.qa_action_pending = true;
		new Ajax.Request('/'+monrovia.config.qa_root+'/get-subcategories.php',{
			method: 'get', parameters:'category_id='+category_id,
			onComplete:function(transport){
				monrovia.runtime_data.qa_action_pending = false;
				if(typeof callback!='undefined') callback(transport.responseText);
			}
		});
	}
}

monrovia.sections.qa.get_subscribers = function(args,callback){
	if(!monrovia.runtime_data.qa_action_pending){
		monrovia.runtime_data.qa_action_pending = true;
		
		var params = 'id=' + args.id + '&start_index=' + args.start_index;
		if(args.prefer_user_id) params += '&prefer_user_id=' + args.prefer_user_id;
		
		new Ajax.Request('/'+monrovia.config.qa_root+'/get-subscribers.php',{
			method: 'get', parameters:params,
			onComplete:function(response){
				monrovia.runtime_data.qa_action_pending = false;
				if(typeof callback!='undefined') callback(response);
			}
		});
	}
}

monrovia.sections.qa.activate_links = function(){
	var elts = $$('.answer_content,.question_content');
	elts.each(function(elt){
		elt.update(activate_links(elt.innerHTML,true));
	});
}

/* END GENERAL STUFF */

/* BEGIN CATEGORY DROPDOWNS */

function load_category_dropdowns(category_id){
	if(monrovia.runtime_data.category_id==category_id) return;
	monrovia.runtime_data.category_id = category_id;

	monrovia.sections.qa.get_category_info(category_id,function(ret){
		try {
			ret = ret.evalJSON();
			$('category_dropdowns').update();

			var breadcrumb = [];

			$('effective_category_id').value = '';

			ret.each(function(level){
				var selected_item = render_dropdown(level);
				if(selected_item){
					breadcrumb.push(selected_item['name']);
					$('effective_category_id').value = selected_item['id'];
				}
			});

			if(breadcrumb.length){
				if($('lbl_category_breadcrumb')){
					// PUBLIC-FACING "ASK A QUESTION" PAGE
					$('lbl_category_breadcrumb').update('You have indicated that your question belongs under ' + breadcrumb.join(' &raquo; ') + '.');
				}else{
					// BACKEND
					$('lbl_category_breadcrumb_backend').update(breadcrumb.join(' &raquo; '));
				}
				
			}else{
				if($('lbl_category_breadcrumb')){
					// PUBLIC-FACING "ASK A QUESTION" PAGE
					$('lbl_category_breadcrumb').update('You have not indicated which category your question belongs under.');				
				}else{
					// BACKEND
					$('lbl_category_breadcrumb_backend').update('');
				}
			}

			init_dropdowns();
		}catch(err){};
	});
}
function render_dropdown(categories){
	var ret = null;
	var select = document.createElement('select');
	var option = document.createElement('option');
	option.setAttribute('value','');
	option.innerHTML = '(Select one)';
	select.appendChild(option);

	categories.each(function(category){
		var option = document.createElement('option');
		option.setAttribute('value',category['id']);
		if(category['is_selected']){
			option.setAttribute('selected','selected');
			ret = category;
		}
		option.innerHTML = category['name'];
		select.appendChild(option);
	});
	$('category_dropdowns').appendChild(select);
	return ret;
}

function init_dropdowns(){
	var dropdowns = $$('#category_dropdowns select');
	dropdowns.each(function(dropdown){
		dropdown.option_changed = function(){
			load_category_dropdowns(dropdown.value||get_effective_category_id());
		}
		dropdown.observe('change',dropdown.option_changed);
	});
}

function get_effective_category_id(){
	var dropdowns = $$('#category_dropdowns select');
	var category_id = 0;

	for(var i=0;i<dropdowns.length;i++){
		if(dropdowns[i].value){
			category_id = dropdowns[i].value;
		}else{
			return category_id;
		}

	}
	return category_id;
}

/* END CATEGORY DROPDOWNS */

Event.observe(window,'load',function(){
	monrovia.sections.qa.activate_links();
});