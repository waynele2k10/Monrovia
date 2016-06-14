var current_module_id;		// THE ID OF THE MODULE THE USER IS CURRENTLY EDITING
var modified_module_ids = '';	// A CSV STRING CONTAINING THE IDs OF MODULES THIS USER MODIFIED
var conflict_alert_acknowledged;
var tmr_poll_changes = window.setInterval(poll_changes,15000);
var is_polling = false;

function editor_load(module){
	var module_editor = $('module_editor');
	var module_id = module.getAttribute('module_id');
	module_editor.setAttribute('module_id',module_id);
	if(module.hasClassName('locked')){
		if(!confirm('Warning: This module is currently being edited by another user.\nAre you sure you want to overwrite those changes?')){
			return;
		}else{
			// OVERWRITE CHOSEN
			module.down('.last_modified').update('');
			module_clear_statuses(module);
		}
	}
	set_locked_by(module_id,monrovia_user_data.name);
	var module_content = module.down('.module_content');
	var html = module_content.getAttribute('html_raw'); //module_content.innerHTML;

	FCKeditorAPI.GetInstance('richtext_editor').SetHTML(html);
	editor_toggle(true);
	current_module_id = module_id;
	if((modified_module_ids+',').indexOf(','+module_id+',')==-1) modified_module_ids += ',' + module_id;
	poll_changes();
}

Event.observe(window,'load',function(){
	$$('div.editable_module').each(function(module){
		module.down('input[class=btn_edit]').observe('click',function(evt){evt.preventDefault();editor_load(module);});
		module.down('input[class=btn_publish]').observe('click',function(evt){evt.preventDefault();editor_publish(module);});
	});

});

function editor_toggle(show){
	if(show){
		var module_id = get_current_module_id();
		var content_div = get_module_content(module_id);
		if(!content_div) return;
		$('module_editor').style.top = Math.max((Element.cumulativeOffset(content_div)[1] - 250),150) + 'px';
		$('module_editor').style.left = '25px';  //(Element.cumulativeOffset(content_div)[0] + 8) + 'px';
		$('module_editor').style.display = 'block';
		//$('module_editor').style.display = 'block';
	}else{
		$('module_editor').style.display = 'none';
		$('module_editor').setAttribute('module_id','');
		conflict_alert_acknowledged = false;
	}
}

function get_module(module_id){
	var modules = $$('div.editable_module[module_id='+module_id+']');
	if(modules.length==1) return modules[0];
}

function get_module_content(module_id){
	var modules = $$('div.editable_module[module_id='+module_id+'] > div.module_content');
	if(modules.length>1){
		alert('Warning! This module module is being used more than once on this page and cannot be edited.');
	}else if(modules.length==1){
		return modules[0];
	}
}

function get_current_module_id(){
	return $('module_editor').getAttribute('module_id');
}

function editor_preview(){
	var module_id = get_current_module_id();
	var content_div = get_module_content(module_id);
	if(content_div){
		var new_html = FCKeditorAPI.GetInstance('richtext_editor').GetHTML();
		new_html = replace_special_characters(new_html);

		// ERASE THE SPACES FCKEDITOR ADDS TO EMPTY TD's
		new_html = new_html.gsub('<td class="side_left">&nbsp;</td>','<td class="side_left"></td>');
		new_html = new_html.gsub('<td class="content">&nbsp;</td>','<td class="content"></td>');
		new_html = new_html.gsub('<td class="side_right">&nbsp;</td>','<td class="side_right"></td>');

		// REMOVE THE EMPTY <p> TAG FCKEDITOR ADDS
		var previous_first_element = content_div.childNodes[0];
		var previous_first_element_was_empty_p_tag = (previous_first_element&&previous_first_element.tagName=='P'&&!previous_first_element.attributes.length);
		if(!previous_first_element_was_empty_p_tag&&new_html.substr(0,3)=='<p>'&&new_html.substr(new_html.length-4)=='</p>'){
			new_html = new_html.substr(3);
			new_html = new_html.substr(0,new_html.length-4);
		}

		if(new_html!=content_div.innerHTML){
			// SAVE THE HTML INTO A CUSTOM PROPERTY, AND ALSO SET THE innerHTML
			content_div.setAttribute('html_raw',html_entity_decode(new_html));
			content_div.innerHTML = new_html;
			var module = content_div.up('.editable_module');
			module_clear_statuses(module);
			module.addClassName('modified');
			render_email_links.defer();
		}
	}
}

function module_clear_statuses(module){
	module.removeClassName('modified');
	module.removeClassName('locked');
	module.removeClassName('released');
}

function editor_publish(module){
	try {
		var module_id = module.getAttribute('module_id');
		var module_content = module.down('.module_content');

		var html = htmlentities(replace_special_characters(module_content.getAttribute('html_raw')),'ENT_NOQUOTES');
		var txt = htmlentities(replace_special_characters(module_content.textContent||module_content.innerText),'ENT_NOQUOTES');

		//html = html.gsub(String.fromCharCode(169),'&copy;'); // &copy;
		//html = html.gsub(String.fromCharCode(174),'&reg;'); // &reg;
		//html = html.gsub(String.fromCharCode(8482),'&trade;'); // &trade;

		html = html.gsub(String.fromCharCode(8226),'-'); // &bull;
		html = html.gsub('&','{{AMP}}');
		html = html.gsub('%','{{PERCENT}}');
		html = html.gsub('#','{{HASH}}');
		html = html.gsub('\\?','{{QUESTION}}');

		txt = txt.gsub(String.fromCharCode(8226),'-'); // &bull;
		txt = txt.gsub('&','{{AMP}}');
		txt = txt.gsub('%','{{PERCENT}}');
		txt = txt.gsub('#','{{HASH}}');
		txt = txt.gsub('\\?','{{QUESTION}}');

		//txt = txt.gsub(String.fromCharCode(169),''); // &copy;
		//txt = txt.gsub(String.fromCharCode(174),''); // &reg;
		//txt = txt.gsub(String.fromCharCode(8482),''); // &trade;

		new Ajax.Request('/update.php', {
		  method: 'post',
		  parameters:'id='+module_id+'&content=' + html+'&content_search='+txt+'&rnd='+Math.random(),
		  onComplete: function(transport) {
			var response = transport.responseText.strip();
			if(response=='success'){
				alert('Your changes have been published.\n\nPlease refresh this page.');
				module_clear_statuses(get_module(transport.request.parameters.id));
				clear_locks_by_user(module_id);
				poll_changes.defer(1000);
			}else{
				alert('An error occurred and your changes were not published.');
			}
		  }
		});
	}catch(err){
		alert('An error occurred and your changes were not published.');
	}
}

function get_module_ids(){
	var module_ids = '';
	var modules = $$('div.editable_module');
	modules.each(function(module){
		module_ids += ',' + module.getAttribute('module_id');
	});
	return module_ids.substr(1);
}
function poll_changes(){
	if(is_polling) return;
	is_polling = true;

	if((typeof editable_module_count)!='undefined'){
		if(!editable_module_count){
			window.clearInterval(tmr_poll_changes);
			return;
		}
	}
	var module_ids = get_module_ids();
	new Ajax.Request('/query_modules.php', {
	  method: 'post',
	  parameters:'action=get_locked_by&module_ids='+module_ids+'&rnd='+Math.random(),
	  onSuccess: function(transport){
	  	is_polling = false;
		var module_poll = transport.responseText.evalJSON();
		var modules = $$('div.editable_module');
		modules.each(function(module){
			var module_id = module.getAttribute('module_id');
			var locked_by_current = module_poll['module_'+module_id].locked_by;
			var locked_by_previous = (module.getAttribute('module_locked_by')||'');
			module.setAttribute('module_locked_by',locked_by_current);
			// IF locked_by CHANGED...

			if(locked_by_current!=locked_by_previous){

				module_clear_statuses(module);

				if(locked_by_current){

					if(locked_by_current!=monrovia_user_data.name) module.addClassName('locked');

					var last_modified = module.down('.last_modified');
					last_modified.update('Currently locked by '+locked_by_current);

					if(locked_by_current!=monrovia_user_data.name&&locked_by_previous==monrovia_user_data.name&&!conflict_alert_acknowledged){
						// ANOTHER USER CHOSE TO OVERWRITE YOUR CHANGES
						conflict_alert_acknowledged = true;
						alert('Warning! '+locked_by_current+' has begun to edit this same content. Please back up your own changes:\n\n1) Click the "Source" button in this editor.\n2) Copy the HTML in its entirety and paste it into Notepad or another text-only editor.\n3) Consult with '+locked_by_current+' to consolidate your changes.');
					}
				}else{
					// MODULE NOW UNLOCKED
					if(locked_by_previous!=monrovia_user_data.name){
						module.addClassName('released');
					}else{
						locked_by_previous = 'You'
					}
					var last_modified = module.down('.last_modified');
					last_modified.update(locked_by_previous + ' recently modified this module. Please <a href="javascript:window.location.reload();void(0);">refresh</a> this page.');
				}
			}
		});

	  }
	});
}
function clear_locks_by_user(module_ids){
	if(!module_ids) module_ids = modified_module_ids.substr(1);
	if(!module_ids) return;
	new Ajax.Request('/query_modules.php',{
	  method: 'post',
	  parameters:'action=clear_locked_by&user_name='+monrovia_user_data.name+'&module_ids='+module_ids+'&rnd='+Math.random(),
	  onSuccess: function(transport){}
	});
}
function set_locked_by(module_id,locked_by){
	new Ajax.Request('/query_modules.php', {
	  method: 'post',
	  parameters:'action=set_locked_by&module_ids='+module_id+'&locked_by='+locked_by+'&rnd='+Math.random(),
	  onSuccess: function(transport){
	  	//alert(transport.responseText);
	  }
	});
}

function page_onleave(){
	window.clearInterval(tmr_poll_changes);
	clear_locks_by_user();
}

Event.observe(window,'load',poll_changes);
Event.observe(window,'unload',page_onleave);