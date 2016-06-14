// ADDTHIS CONFIG
var addthis_pub="tpgmonrovia";
var submitted_form;
var preloader = [];

var monrovia = {
	'runtime_data':{},
	'config':{
		'google_analytics':{
			'delay':500
		}
	},
	'sections':{
	}
}

/* COOKIES */

function CookieHandler() {

	this.setCookie = function (name, value, seconds) {

		if (typeof(seconds) != 'undefined') {
			var date = new Date();
			date.setTime(date.getTime() + (seconds*1000));
			var expires = "; expires=" + date.toGMTString();
		}
		else {
			var expires = "";
		}

		document.cookie = name+"="+value+expires+"; path=/";
	}

	this.getCookie = function (name) {

		name = name + "=";
		var carray = document.cookie.split(';');

		for(var i=0;i < carray.length;i++) {
			var c = carray[i];
			while (c.charAt(0)==' ') c = c.substring(1,c.length);
			if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
		}

		return null;
	}

	this.deleteCookie = function (name) {
		this.setCookie(name, "", -1);
	}

}

var cookie_handler = new CookieHandler();
var search_criteria = (window.unescape(cookie_handler.getCookie('search_criteria')).evalJSON()||{});

/* END COOKIES */

function replace_special_characters(content){
	content += '';
	content = content.gsub('&ldquo;','"');
	content = content.gsub('&rdquo;','"');
	content = content.gsub('&lsquo;','\'');
	content = content.gsub('&rsquo;','\'');
	content = content.gsub('&mdash;','--');
	content = content.gsub('&hellip;','...');

	content = content.gsub(String.fromCharCode(8216),"'");
	content = content.gsub(String.fromCharCode(8217),"'");
	content = content.gsub(String.fromCharCode(8220),'"');
	content = content.gsub(String.fromCharCode(8221),'"');
	content = content.gsub(String.fromCharCode(8211),"-");
	content = content.gsub(String.fromCharCode(8212),"--");
	content = content.gsub(String.fromCharCode(8230),"...");
	//content = content.gsub(String.fromCharCode(8482),String.fromCharCode(153));

	// APOS
	content = content.gsub(String.fromCharCode(96), "'");
	content = content.gsub(String.fromCharCode(145), "'");
	content = content.gsub(String.fromCharCode(146), "'");
	content = content.gsub(String.fromCharCode(180), "'");
	content = content.gsub(String.fromCharCode(8216), "'");
	content = content.gsub(String.fromCharCode(8217), "'");

	// QUOT
	content = content.gsub(String.fromCharCode(147), '"');
	content = content.gsub(String.fromCharCode(148), '"');
	content = content.gsub(String.fromCharCode(8220), '"');
	content = content.gsub(String.fromCharCode(8221), '"');

	// DASH
	content = content.gsub(String.fromCharCode(149),"-");
	content = content.gsub(String.fromCharCode(150),"-");
	content = content.gsub(String.fromCharCode(151),"--");

	// OTHER
	content = content.gsub(String.fromCharCode(160),' ');
	content = content.gsub(String.fromCharCode(133),'...');
	content = content.gsub(String.fromCharCode(130),'\'');
	content = content.gsub(String.fromCharCode(132),'"');
	content = content.gsub(String.fromCharCode(147),'"');
	content = content.gsub(String.fromCharCode(148),'"');
	content = content.gsub(String.fromCharCode(145),'\'');
	content = content.gsub(String.fromCharCode(146),'\'');

	return content;
}
function return_false(){
	return false;
}

function padDigits(num,total_length){ 
	if(num.toString) num = num.toString(); 
	var prefix = ''; 
	if(total_length>num.length){ 
		for(i=0;i<total_length-num.length;i++){ 
			prefix += '0';
		} 
	} 
	return prefix + num.toString();
} 

function get_field(name){
	return $$('input[name=\''+name+'\'],select[name=\''+name+'\'],textarea[name=\''+name+'\'],input[id=\''+name+'\'],select[id=\''+name+'\'],textarea[id=\''+name+'\']')[0];
}
function get_select_friendly_value(name){
	var select = $$('select[name=\''+name+'\'],select[id=\''+name+'\']')[0];
	return select.down('option[value='+select.value+']').innerHTML;
}
function random_integer(length){
	var ret = '';
	for(var i=0;i<length;i++){
		ret += Math.floor(Math.random() * 10);
	}
	return ret;
}
function parse_alphabetic_characters(txt){
	txt = (txt + '').toLowerCase().gsub(' ','_');
	var ret = '';

	for(var i=0;i<txt.length;i++){
		var char = txt.substr(i,1);
		if(char.match(/[a-z]/)) ret += char;
	}
	return ret;
}

function parse_numeric_characters(txt){
	txt = (txt + '');
	var ret = '';

	for(var i=0;i<txt.length;i++){
		var char = txt.substr(i,1);
		if(char.match(/[\d]/)) ret += char;
	}
	return ret;
}

function is_alphanumeric(txt){
	var regex=/^[0-9A-Za-z]+$/;
	return (regex.test(txt));
}

function is_alphabetic(txt,allow){
	var regex = new RegExp('^[A-Za-z'+allow+']+$');
	return (regex.test(txt));
}

function is_valid_us_canadian_zip(txt){
	var regex = new RegExp('^[A-Za-z][0-9][A-Za-z][ ]?[\-]?[0-9][A-Za-z][0-9]$');
	var is_valid_canadian = regex.test(txt);
	var is_valid_american = (txt.length==5&&!isNaN(txt-1));
	return (is_valid_canadian||is_valid_american);
}

function swf_wish_list_add_item(item_number){
	if(monrovia_user_data.is_logged_in){
		new Ajax.Request('/community/wish-list-update.php', {
		  method: 'post', parameters:'plant_item_number='+item_number+'&action=item_upsert&rnd='+Math.random(),
		  onComplete:function(transport){
		  	if(transport.responseText=='fail/full'){
		  		//modal_show({'modal_id':'modal_wish_list_full','effect':'fade'});
		  		window.location.href = '/community/your-wish-list.php?notice=wish_list_full';
		  	}
		  }
		});
	}else{
		window.location.href = '/community/login.php?notice=wish_list_login';
	}
}

function wish_list_update_item(wish_list_id,plant_id,action,notes,icon){
	if(monrovia_user_data.is_logged_in){
		if(!notes) notes = '';
		notes = notes.stripTags();
		notes = notes.gsub('&','{{AMP}}');
		notes = notes.gsub('%','{{PERCENT}}');
		notes = notes.gsub('#','{{HASH}}');
		notes = notes.gsub('\\?','{{QUESTION}}');
		new Ajax.Request('/community/wish-list-update.php', {
		  method: 'post', parameters:'wish_list_id='+wish_list_id+'&plant_id='+plant_id+'&action='+action+'&notes='+notes+'&rnd='+Math.random(),
		  onComplete:function(transport){
		  	if(transport.responseText=='fail/full'){
		  		if(parent.modal_show) parent.modal_show({'modal_id':'modal_wish_list_full','effect':'fade'});
		  	}else if(transport.responseText.indexOf('success')>-1){
		  		var icon = $$('img.icon_wish_list[plant_id="'+plant_id+'"]');
		  		if(icon.length){
					icon[0].removeClassName('plus');
					icon[0].addClassName('star');
					icon[0].title = 'on your wish list';
				}

				// PLANT DETAIL PAGE
				if($('notification_added')) $('notification_added').appear();
		  	}
		  }
		});
	}else{
		window.location.href = '/community/login.php?notice=wish_list_login';
	}
}
function wish_list_remove_item(wish_list_id,plant_id,callback_function){
	var options = {
	  method: 'post', parameters:'wish_list_id='+wish_list_id+'&plant_id='+plant_id+'&action=item_remove&rnd='+Math.random()
	}
	if(callback_function) options['onSuccess'] = callback_function;
	new Ajax.Request('/community/wish-list-update.php',options);
}

function is_valid_mysql_date(value){
	value = value.replaceAll('-','/');
	var date_obj = new Date(value);
	var date_array = value.split('/');
	return (date_obj.getDate()==window.parseFloat(date_array[2])&&date_obj.getMonth()==window.parseFloat(date_array[1])-1&&date_obj.getFullYear()==window.parseFloat(date_array[0]));
}
// FORM VALIDATION
// SYNTAX: (FORM).validate() - RETURNS BOOLEAN
Element.addMethods('form',{
  validate:function(form){
	var ret = true;
	var field_error_message = $('field_error_message');
	if(field_error_message) field_error_message.style.display = 'none';
	form.select('input,select,textarea').each(function(field){
		//alert(field.name + ',' + field.validation_exclude);
		
		if((typeof field.validate)=='function'&&(field.hasAttribute('validation_type')||field.hasAttribute('validation_required'))&&(field.validation_exclude||field.getAttribute('validation_exclude'))!=='true'){
			var field_valid = field.validate();
			if(field_error_message.style.display=='none'){
				if(!field_valid&&ret&&field.error_message){
					field_error_message.down('#message').update(field.error_message);
					field_error_message.style.top = field.cumulativeOffset()[1] + 'px';
					field_error_message.style.left = (field.cumulativeOffset()[0] + field.clientWidth) + 'px';
					field_error_message.style.display = 'block';
					try {
						// field.focus();
					}catch(err){

					}
				}
			}
			ret = field_valid&&ret;
		}else{
			//if(field.validation_exclude) alert(field.validation_exclude);
		}
	});
	if(typeof form.custom_validation=='function'){
		ret = form.custom_validation()&&ret;
	}
	return ret;
	},
  perform_submit:function(form){
	  clear_validation_errors();
	  if(!form.is_submitted&&form.validate()){
		  submitted_form = form;
		  submit_form_defer.defer();
		  //form.submit();
		  //form.disable();
		  form.is_submitted = true;
	  }
	  return void(0);
	}
});

function submit_form_defer(){
	if(submitted_form) submitted_form.submit();
}

function clear_validation_errors(){
	var elts = $$('.error_validation');
	 elts.each(function(field){
		  field.removeClassName('error_validation');
		  field.error_message = '';
	  });
	if($('field_error_message')) $('field_error_message').style.display = 'none';
}

function get_multiselect_values(multiselect){
	// INSTRINSIC HTML MULTIPLE SELECTS
	var ret = '';
	multiselect.select('option').each(function(option){
	    if(option.selected) ret += ',' + option.value;
	});
	if(ret) ret = ret.substr(1);
	return ret;
}

function set_multiselect_values(multiselect,values){
	// INSTRINSIC HTML MULTIPLE SELECTS
	// values HAS TO BE A SIMPLE COMMA-DELIMITED STRING
	multiselect.select('option').each(function(option){option.selected=false;});
	values.split(',').each(function(value){
		if(value){
			var option = multiselect.down('option[value='+value+']');
			if(option){
				option.selected = true;
			}
		}
	});
}
Event.observe(window,'load',function(){
	//if(typeof console=='undefined'&&typeof window.loadFirebugConsole!='undefined') window.console = window.loadFirebugConsole();

	// INIT TOGGLE WIDGETS
	var elts = $$('.toggle_widget .header_collapsed,.toggle_widget .header_expanded');
	elts.each(function(header){
		header.observe('click',function(){
			this.up('.toggle_widget').toggleClassName('expanded');
		})
	});

	// SET VALUES FOR INTRINSIC SELECT DROPDOWNS/MULTISELECTS
	var elts = $$('select[_value]');
	elts.each(function(select){
		var value = select.getAttribute('_value');
		if(select.hasAttribute('multiple')){
			set_multiselect_values(select,value);
		}else{
			select.value = value;
		}
	});
	var elts = $$('.btn_green');
	elts.each(function(button){
		var form = button.up('form');
		// IF FORM DOESN'T CONTAIN A SUBMIT BUTTON (I.E., DOESN'T SUPPORT ENTER KEY-SUBMISSION, ADD ONE
		if(form){
			if(!form.down('input[type=submit],input[type=image]')){
				var btn_submit = new Element('input');
				btn_submit.type = 'image';
				btn_submit.src = '/img/spacer.gif';
				form.appendChild(btn_submit);
				btn_submit.style.position = 'absolute';
				btn_submit.style.width = btn_submit.style.height = '0px';
			}
		}
	});
	// MAKE MODULE TABLES CLICKABLE IN IE
	if(window.details.ieVersion()>-1){
		var tables = $$('.module_wrapper.promotional,.module_wrapper_leaf4 a table');
		tables.each(function(table){
			table.observe('click',function(){
				if(table.up('a')) window.location.href = table.up('a').getAttribute('href');
			})
		});
	}
	// FORM VALIDATION
	var elts = $$('form[validation_enabled=\'true\']');
	elts.each(function(form){
		form.select('*[validation_type],*[validation_required]').each(function(field){
			field.validate = function(is_revalidate){
				var ret = true;
				this.error_message = '';
				var value = (this.type=='checkbox')?this.checked:this.value;

				if(!value){
					// NO VALUE SPECIFIED AND FIELD IS REQUIRED
					ret = (this.getAttribute('validation_required')!='true');
					if(!ret) this.error_message = 'This field is required.';
				}else{
					switch(this.getAttribute('validation_type')){
						case 'mysql_date':
							var expr = /^(\d){4}\-(\d){2}\-(\d){2}/;
							ret = expr.match(value);
							if(!ret){
								this.error_message = 'Date must be in a yyyy-mm-dd format.';
							}else{
								ret = is_valid_mysql_date(value);
								if(!ret) this.error_message = 'Invalid date.';
							}
							break;
						case 'phone':
							var expr = /^(\d){3}\-(\d){3}\-(\d){4}$/;
							ret = expr.match(value);
							if(!ret){
								this.error_message = 'Number must be in xxx-xxx-xxxx format.';
							}
							break;
						case 'email':
							var expr = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-Z0-9]{2,4}$/;
							ret = expr.match(value);
							if(!ret){
								this.error_message = 'Please enter a valid email address.';
							}else{
								if(value.toLowerCase().endsWith('.con')){
									this.error_message = 'The email address you specified ends<br />in ".con." Did you mean ".com"?';
									ret = false;
								}
							}
							break;
						case 'url':
							var expr = /http:\/\/[A-Za-z0-9\.-]{3,}\.[A-Za-z]{3}/;
							ret = expr.match(value);
							if(!ret){
								this.error_message = 'Please enter a valid web address<br />beginning with "http://".';
							}else{
								if(value.toLowerCase().endsWith('.con')){
									this.error_message = 'The web address you specified ends<br />in ".con." Did you mean ".com"?';
									ret = false;
								}
							}
							break;
						case 'zip':
							if(!is_valid_us_canadian_zip(value)){
								this.error_message = 'Please enter a valid 5-digit zip code<br />or a 6-character Canadian postal code.';
								ret = false;
							}
							break;
					}
				}
				if(ret&&(typeof this.custom_validation)=='function') ret = this.custom_validation(is_revalidate)&&ret;
				if(ret){
					this.removeClassName('error_validation');
				}else{
					if(!is_revalidate) this.addClassName('error_validation');
					if(is_revalidate) this.error_message = '';
				}
				return ret;
			}
			// is_revalidate WILL CLEAR ERROR STATE, BUT WON'T ADD ERROR STATE
			field.observe('blur',function(){
				if(field.error_message){
					// IF FIELD INPUT PREVIOUSLY INVALID, REVALIDATE FORM ON BLUR
					field.up('form').validate();
				}else{
					field.validate(true);
				}
			});
		});
		form.observe('submit',function(evt){evt.stop();form.perform_submit(true)});
	});

	// CUSTOM MULTISELECT MODULES
	var elts = $$('.module_multiselect');
	elts.each(function(multiselect){
		var field_id = multiselect.id.replace('multiselect_','');
		var items = multiselect.select('.list_content span');
		// ADD EVENT HANDLER TO KEEP HIDDEN FIELDS UP-TO-DATE
		multiselect.on_change = function(){
			var input = get_field(field_id);
			var field_value = get_multiselect_module_values(multiselect.id);
			if(input) input.value = field_value;
		}
		multiselect.reset = function(){
			items.each(function(item){
				item.removeClassName('selected');
			});
			multiselect.on_change();
		}
		// RESTORE MULTISELECT VALUES
		var field_value = (search_criteria[field_id+'s']||search_criteria[field_id]||'');
		if(field_value){
			var field_value_csv = ((typeof field_value)=='string')?field_value:field_value.join(',');
			set_multiselect_module_values(multiselect.id,field_value_csv);
		}

		// INIT MULTISELECT OPTIONS
		items.each(function(span){
			span.observe('click',function(){
				span.toggleClassName('selected');
				multiselect.on_change();
			});
		});
	});

	// COLD ZONES
	var cold_zone_low = search_criteria['cold_zone_low'];
	var cold_zone_high = search_criteria['cold_zone_high'];
	var cold_zones = '';
	if(cold_zone_low&&cold_zone_high){
		for(var i=0;i<=(cold_zone_high-cold_zone_low);i++){
			cold_zones += ',' + (i+cold_zone_low);
		}
		var multiselect_cold_zone = $('multiselect_cold_zone');
		if(multiselect_cold_zone&&cold_zones) set_multiselect_module_values('multiselect_cold_zone',cold_zones.substr(1));
	}
	// CUSTOM SINGLESELECT MODULES
	var elts = $$('.module_singleselect');
	elts.each(function(singleselect){
		// ADD EVENT HANDLER TO KEEP HIDDEN FIELDS UP-TO-DATE
		singleselect.on_change = function(){
			var field_id = singleselect.id.replace('singleselect_','');
			var input = get_field(field_id);
			var field_value = get_multiselect_module_values(singleselect.id);
			if(input) input.value = field_value;
		}
		singleselect.clear = function(){
			singleselect.select('.list_content span').each(function(span){
				span.removeClassName('selected');
			});
			singleselect.on_change();
		}
		// RESTORE SINGLESELECT VALUES
		var input = get_field(singleselect.id.replace('singleselect_',''));
		if(input&&input.value) set_multiselect_module_values(singleselect.id,input.value);

		// INITIALIZE SINGLESELECT OPTIONS
		singleselect.select('.list_content span').each(function(span){
			span.observe('click',function(){
				//var module = span.up('.module_singleselect');
				var value = !span.hasClassName('selected');
				if(value) singleselect.clear();
				span.toggleClassName('selected');
				singleselect.on_change();
			});
		});

	});

	// ADD TO WISH LIST ICONS
	render_add_to_wish_list_icons.defer();

	// GLOBAL SEARCH
	var global_search_query = $('global_search_query');
	if(global_search_query){
		global_search_query.observe('focus',function(){this.value='';});
		global_search_query.observe('blur',function(){
			if(!this.value) this.value='search site';
			window.setTimeout(function(){
				$('global_search').removeClassName('autocomplete');
			},1000);
		});
		/*
		global_search_query.observe('keyup',function(){
			if(this.value.length>2){
				window.clearTimeout(tmr_autocomplete);
				tmr_autocomplete = window.setTimeout(global_autocomplete,500);
			}else{
				$('global_search').removeClassName('autocomplete');
			}
		});
		*/
	}

	// RENDER EMAIL LINKS
	render_email_links();

	// IE6 BACKGROUND FLICKER FIX
	if(window.details.ieVersion()==6){
		try {
			document.execCommand("BackgroundImageCache", false, true);
		}catch(err){}
	}

	// GOOGLE ANALYTICS EVENT TRACKING
	var elts = $$('a[google_event_tag]');
	elts.each(function(anchor){
		anchor.observe('click',function(evt){

			// FOR target=_blank ANCHORS, LET GOOGLE ANALYTICS TRACK NORMALLY. FOR ALL OTHER ANCHORS, TRACK, DEFER, THEN PROGRAMMATICALLY CLICK
			if(anchor.clicked||typeof pageTracker=='undefined') return;
			anchor.clicked = true;
			var google_event_tags = anchor.getAttribute('google_event_tag').split('|');
			
			// IF FOURTH PARAM EXISTS, CAST IT TO A NUMBER
			if(google_event_tags.length>3&&parse_numeric_characters(google_event_tags[3])==google_event_tags[3]) google_event_tags[3] = window.parseFloat(google_event_tags[3]);

			// IF FIFTH PARAM EXISTS, CAST IT TO A NUMBER
			if(google_event_tags.length>4) google_event_tags[4] = google_event_tags[4] == 'true';
			
			if(google_event_tags.length==3){
				pageTracker._trackEvent(google_event_tags[0],google_event_tags[1],google_event_tags[2]);			
			}else if(google_event_tags.length==4){
				pageTracker._trackEvent(google_event_tags[0],google_event_tags[1],google_event_tags[2],google_event_tags[3]);			
			}else if(google_event_tags.length==5){
				pageTracker._trackEvent(google_event_tags[0],google_event_tags[1],google_event_tags[2],google_event_tags[3],google_event_tags[4]);			
			}
			
			if(anchor.getAttribute('target')!='_blank'){
				Event.stop(evt);
				window.setTimeout(function(){
					window.location.href = anchor.getAttribute('href');
				},250);
			}
		});
	 });
	 
	// GOOGLE ANALYTICS SOCIAL MEDIA TRACKING
	var elts = $$('a[google_social_tracking]');
	elts.each(function(anchor){
		anchor.observe('click',function(evt){

			// FOR target=_blank ANCHORS, LET GOOGLE ANALYTICS TRACK NORMALLY. FOR ALL OTHER ANCHORS, TRACK, DEFER, THEN PROGRAMMATICALLY CLICK
			if(anchor.clicked||typeof pageTracker=='undefined') return;
			anchor.clicked = true;
			var components = anchor.getAttribute('google_social_tracking').split('|');
			
			if(components[2]=='{currenturl}') components[2] = window.location.href;
			
			pageTracker._trackSocial(components[0],components[1],components[2]);
			if(anchor.getAttribute('google_social_tracking_skip_navigation')!='true'&&anchor.getAttribute('target')!='_blank'){
				Event.stop(evt);
				window.setTimeout(function(){
					window.location.href = anchor.getAttribute('href');
				},250);
			}
		});
	 });
});

var tmr_autocomplete;
function global_autocomplete(){
	var query = $('global_search_query').value;
	new Ajax.Request('/plant-autocomplete.php', {
		method: 'post', parameters:'query='+query,
		onComplete:function(transport){
			var json = transport.responseText.strip();
			if(json){
				var listings = transport.responseText.evalJSON();
				if(listings.length){
					var global_search_dropdown = $('global_search_dropdown');
					global_search_dropdown.update();

					listings.each(function(listing){
						var div = new Element('div');
						var anchor = new Element('a');
						anchor.setAttribute('href',listing.url);
						anchor.onclick = function(){
							$('global_search').removeClassName('autocomplete');
							$('global_search_query').value = (anchor.textContent||anchor.innerText);
						}
						anchor.innerHTML = listing.name;
						div.appendChild(anchor);
						global_search_dropdown.appendChild(div);
					});
					$('global_search').addClassName('autocomplete');
				}else{
					$('global_search').removeClassName('autocomplete');
				}
			}else{
				$('global_search').removeClassName('autocomplete');
			}
		}
	});
}

function icon_wish_list_plus_doadd(icon){
	wish_list_update_item('1',icon.getAttribute('plant_id'),'item_upsert');
}

function render_add_to_wish_list_icons(){
	var search_result_plants = $$('.search_result_plant');
	search_result_plants.each(function(search_result_plant){
		// PLUS SIGN ICON
		var icon = search_result_plant.down('img.icon_wish_list.plus');
		var flag_add = search_result_plant.down('.flag_add');
		var flag_view = search_result_plant.down('.flag_view');
		var thumbnail = search_result_plant.down('.search_result_plant_image');

		if(icon){
			icon.setAttribute('inited','true');
			icon.observe('click',function(){
				if(!monrovia_user_data.is_logged_in){
					// NEED TO USE PARENT FOR plant-catalog/collections/tabs/
					parent.window.location.href = '/community/login.php?notice=wish_list_login';
				}else{
					// RUN ONCE
					if(icon.hasClassName('plus')){
						search_result_plant.hide_flag_add();
						icon_wish_list_plus_doadd(icon);
					}
				}
			});

			// FLAGS
			search_result_plant.show_flag_add = function(){
				if(icon.hasClassName('plus')){
					search_result_plant.hide_flag_view();
					flag_add.appear({duration:.15,from:0,to:.9});
				}
			}
			search_result_plant.hide_flag_add = function(){
				if(icon.hasClassName('plus')){
					search_result_plant.show_flag_view();
					flag_add.appear({duration:.3,from:.9,to:0});
				}
			}
			icon.observe('mouseover',search_result_plant.show_flag_add);
			icon.observe('mouseout',search_result_plant.hide_flag_add);
		}

		if(flag_view&&thumbnail){
			search_result_plant.show_flag_view = function(){
				flag_view.appear({duration:.15,from:0,to:.9});
			}
			search_result_plant.hide_flag_view = function(){
				flag_view.appear({duration:.3,from:.9,to:0});
			}
			search_result_plant.check_hover = function(){
				var hover = (flag_view.getAttribute('hover')=='true'||thumbnail.getAttribute('hover')=='true'||search_result_plant.getAttribute('hover')=='true');
				if(search_result_plant.getAttribute('last_hover_state')==hover.toString()) return;
				search_result_plant.setAttribute('last_hover_state',hover.toString());
				if(hover){
					search_result_plant.show_flag_view();
				}else{
					search_result_plant.hide_flag_view();
				}
			}
			search_result_plant.onmouseover = flag_view.onmouseover = thumbnail.onmouseover = function(){
				this.setAttribute('hover','true');
				search_result_plant.check_hover.defer();
			}
			search_result_plant.onmouseout = flag_view.onmouseout = thumbnail.onmouseout = function(){
				this.setAttribute('hover','false');
				search_result_plant.check_hover.defer();
			}

		}
	});

	// INIT STANDALONE PLUS SIGNS NOT PREVIOUSLY INITED
	var elts = $$('img.icon_wish_list.plus:not([inited="true"])');
	elts.each(function(icon){
		icon.setAttribute('inited','true');
		icon.observe('click',function(){
			if(icon.hasClassName('plus')){
				icon_wish_list_plus_doadd(icon);
			}
		});
	});
}

function render_email_links(){
	var email_links = $$('span.email_link');
	email_links.each(function(email_link){
		// TAG SHOULD FOLLOW THIS FORMAT:
		// <span class="email_link">hello(#)monrovia.com</span> OR <span class="email_link" address="hello(#)monrovia.com">hello</span>
		var mail_to = (email_link.getAttribute('address')||(email_link.innerText||email_link.textContent)).replace('(#)','@');

		if(mail_to){
			var anchor_html = '';
			anchor_html = '<a href="mailto:'+mail_to+'"';
			var google_event_tag = email_link.getAttribute('google_event_tag');
			if(google_event_tag) anchor_html += ' google_event_tag="' + google_event_tag + '"';

			anchor_html +='>'+email_link.innerHTML.replace('(#)','@')+'</a>';
			email_link.innerHTML = anchor_html;
		}

		email_link.style.visibility = 'visible';
	});
}

function global_search_validate(){
	return ($('global_search_query').value!=''&&$('global_search_query').value!='search site');
}

function get_multiselect_module_values(id){
	var value = '';
	$(id).select('.list_content span.selected').each(function(item){
		value += ',' + item.getAttribute('value');
	});
	return value.substr(1);
}

function set_multiselect_module_values(id,values){
	// values HAS TO BE A CSV STRING
	var values_array = values.split(',');
	var selector = '';
	for(var i=0;i<values_array.length;i++){
		selector += ',span[value=\''+values_array[i]+'\']';
	}
	var elts = $$('#'+id+' .list_content')[0].select(selector.substr(1));
	elts.each(function(item){
		item.addClassName('selected');
	});
}

/* MINI SLIDESHOWS */

	// NOTE: CURRENTLY WILL NOT WORK CORRECTLY IF THERE ARE MORE THAN ONE ON A PAGE

	Event.observe(window,'load',function(){
		var elts = $$('.mini_slideshow');
		elts.each(function(mini_slideshow){
			// CONFIG
			mini_slideshow.active = (mini_slideshow.getAttribute('active')!='false');
			mini_slideshow.duration = (mini_slideshow.getAttribute('duration')||8000);
			// END CONFIG

			mini_slideshow.current_slide_num = 0;
			if(mini_slideshow.select('.mini_slideshow_button').length<2){
				mini_slideshow.down('.mini_slideshow_buttons').style.display = 'none';
			}
			mini_slideshow.select('.mini_slideshow_button').each(function(button,index){
				button.slide_num = index;
				button.observe('click',function(){mini_slideshow.show_slide(button);});
				preload_image(button.getAttribute('path_thumbnail'));
			});
			mini_slideshow.down('.mini_slideshow_display').observe('click',function(){
				var mini_slideshow = this.up('.mini_slideshow');
				var path_full = mini_slideshow.path_full;
				var path_link = mini_slideshow.path_link;
				var caption = mini_slideshow.caption;

				if(path_full){
					// DISPLAY IMAGE OR LINK TO FILE
					if(path_full.toLowerCase().contains('.gif')||path_full.toLowerCase().contains('.jpg')){
						lightview_show_image(path_full,caption);
					}else{
						// PDFs, ETC.
						window.open(path_full);
					}
				}else if(path_link){
					window.location = path_link;
				}
			});
			mini_slideshow.show_slide = function(button){
				if(!isNaN(button)) button = mini_slideshow.select('.mini_slideshow_button')[button];
				mini_slideshow.current_slide_num = button.slide_num;

				mini_slideshow.select('.on').each(function(button_on){button_on.removeClassName('on');});
				button.addClassName('on');

				var display = mini_slideshow.down('.mini_slideshow_display');
				display.style.backgroundImage = 'url('+button.getAttribute('path_thumbnail')+')';
				mini_slideshow.path_full = button.getAttribute('path_full');
				mini_slideshow.path_link = button.getAttribute('path_link');
				mini_slideshow.caption = button.getAttribute('caption');

				var message_view_full = ((button.getAttribute('path_full')+'').toLowerCase().contains('.pdf'))?'view larger (pdf)':'view larger';
				if(button.getAttribute('path_link')) message_view_full = '';

				display.style.cursor = (mini_slideshow.path_full||mini_slideshow.path_link)?'pointer':'default';
				display.title = (mini_slideshow.path_full)?message_view_full:'';

				var caption = button.getAttribute('caption');
				mini_slideshow.down('.mini_slideshow_caption').update(caption||'&nbsp;');
			}
			mini_slideshow.next_slide = function(){
				if(mini_slideshow.active){
					mini_slideshow.current_slide_num++;
					if(mini_slideshow.current_slide_num>=mini_slideshow.select('.mini_slideshow_button').length) mini_slideshow.current_slide_num = 0;
					mini_slideshow.show_slide(mini_slideshow.current_slide_num);
				}
			}
			mini_slideshow.mouse_over = function(){
				mini_slideshow.active = false;
			}
			mini_slideshow.mouse_out = function(){
				mini_slideshow.active = true;
				mini_slideshow.start();
			}
			mini_slideshow.start = function(){
				window.clearInterval(mini_slideshow.tmr);
				mini_slideshow.tmr = window.setInterval(function(){(mini_slideshow_tmr).call(mini_slideshow)},mini_slideshow.duration);
				return false;
			}

			mini_slideshow.observe('mouseover',mini_slideshow.mouse_over,false);
			mini_slideshow.observe('mouseout',mini_slideshow.mouse_out,false);

			// DEFAULT TO FIRST SLIDE
			mini_slideshow.show_slide(0);
			if(mini_slideshow.active) mini_slideshow.start();
		});	
	});

	function mini_slideshow_tmr(){
		this.next_slide();
	}

/* END MINI SLIDESHOWS */

Event.observe(window,'load',function(){
		// RETAIN CUSTOM MULTISELECT VALUES
		var elts = $$('.module_multiselect');
		elts.each(function(multiselect){
			var field_id = multiselect.id.replace('multiselect_','');
			var input = get_field(field_id);
			var field_value = get_multiselect_module_values(multiselect.id);
			if(input){
				// HIDDEN FIELD ALREADY EXISTS
				if(field_value){
					// REUSE FIELD
					input.value = field_value;
				}else{
					// NO VALUE; DELETE FIELD
					//$('form_plant_search').removeChild(input);
				}
			}else{
				// CREATE NEW HIDDEN FIELD
				if(field_value){
					input = new Element('input');
					input.type = 'hidden';
					input.name = field_id;
					input.value = field_value;
					$('form_plant_search').appendChild(input);
				}
			}
		});

		// EXPANDABLE COPY
		var expandable_copy_containers = $$('.expandable_content_container');
		expandable_copy_containers.each(function(expandable_copy_container){
			var full_height = expandable_copy_container.down('.expandable_content').getHeight();
			expandable_copy_container.next('.expandable_content_read_more').down('a').observe('click',function(evt){
				evt.preventDefault();
				this.parentNode.style.display = 'none';
				expandable_copy_container.style.height = full_height + 'px';
			});
		});
		
		init_tease_widgets();

});

function init_tease_widgets(){
	// TEASE WIDGET
	var elts = $$('.tease_widget');
	elts.each(function(elt){
		var content = elt.down('.content');
		if(content&&(content.scrollHeight>content.clientHeight)){
			elt.addClassName('expandable');
			var lnk_expand = elt.down('.lnk_expand');
			if(lnk_expand){
				lnk_expand.stopObserving('click');
				lnk_expand.observe('click',function(evt){
					evt.preventDefault();
					elt.addClassName('expanded manually_expanded');
					elt.removeClassName('expandable');
				});
			}
			
			content.observe('mouseenter',function(){
				content.setAttribute('hover','true');
				window.setTimeout(function(){
					if(content.getAttribute('hover')=='true'){
						elt.addClassName('expanded');
						elt.removeClassName('expandable');
					}
				},500);
			});
			
			content.observe('mouseleave',function(){
				content.removeAttribute('hover');
				if(!elt.hasClassName('manually_expanded')){
					elt.removeClassName('expanded');
					elt.addClassName('expandable');
				}

			});
		}
	});
}

function preload_image(path){
	var img = new Element('img');
	img.src = path;
	preloader.push(img);
}

///////////////////////////////////////////

// PHP.JS

function htmlentities (string, quote_style) {
    // Convert all applicable characters to HTML entities
    //
    // version: 909.322
    // discuss at: http://phpjs.org/functions/htmlentities
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: nobbler
    // +    tweaked by: Jack
    // +   bugfixed by: Onno Marsman
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +    bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Ratheous
    // -    depends on: get_html_translation_table
    // *     example 1: htmlentities('Kevin & van Zonneveld');
    // *     returns 1: 'Kevin &amp; van Zonneveld'
    // *     example 2: htmlentities("foo'bar","ENT_QUOTES");
    // *     returns 2: 'foo&#039;bar'
    var hash_map = {}, symbol = '', tmp_str = '', entity = '';
    tmp_str = string.toString();

    if (false === (hash_map = this.get_html_translation_table('HTML_ENTITIES', quote_style))) {
        return false;
    }
    hash_map["'"] = '&#039;';
    hash_map[String.fromCharCode(8482)] = '&#153;';
    
    for (symbol in hash_map) {
        entity = hash_map[symbol];
        tmp_str = tmp_str.split(symbol).join(entity);
    }

    return tmp_str;
}

function html_entity_decode (string, quote_style) {
    // Convert all HTML entities to their applicable characters
    //
    // version: 909.322
    // discuss at: http://phpjs.org/functions/html_entity_decode
    // +   original by: john (http://www.jd-tech.net)
    // +      input by: ger
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Onno Marsman
    // +   improved by: marc andreu
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +    bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Ratheous
    // -    depends on: get_html_translation_table
    // *     example 1: html_entity_decode('Kevin &amp; van Zonneveld');
    // *     returns 1: 'Kevin & van Zonneveld'
    // *     example 2: html_entity_decode('&amp;lt;');
    // *     returns 2: '&lt;'
    var hash_map = {}, symbol = '', tmp_str = '', entity = '';
    tmp_str = string.toString();

    if (false === (hash_map = this.get_html_translation_table('HTML_ENTITIES', quote_style))) {
        return false;
    }

    for (symbol in hash_map) {
        entity = hash_map[symbol];
        tmp_str = tmp_str.split(entity).join(symbol);
    }
    tmp_str = tmp_str.split('&#039;').join("'");

    return tmp_str;
}

function get_html_translation_table (table, quote_style) {
    // Returns the internal translation table used by htmlspecialchars and htmlentities
    //
    // version: 909.322
    // discuss at: http://phpjs.org/functions/get_html_translation_table
    // +   original by: Philip Peterson
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: noname
    // +   bugfixed by: Alex
    // +   bugfixed by: Marco
    // +   bugfixed by: madipta
    // +   improved by: KELAN
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Frank Forte
    // +   bugfixed by: T.Wild
    // +      input by: Ratheous
    // %          note: It has been decided that we're not going to add global
    // %          note: dependencies to php.js, meaning the constants are not
    // %          note: real constants, but strings instead. Integers are also supported if someone
    // %          note: chooses to create the constants themselves.
    // *     example 1: get_html_translation_table('HTML_SPECIALCHARS');
    // *     returns 1: {'"': '&quot;', '&': '&amp;', '<': '&lt;', '>': '&gt;'}

    var entities = {}, hash_map = {}, decimal = 0, symbol = '';
    var constMappingTable = {}, constMappingQuoteStyle = {};
    var useTable = {}, useQuoteStyle = {};

    // Translate arguments
    constMappingTable[0]      = 'HTML_SPECIALCHARS';
    constMappingTable[1]      = 'HTML_ENTITIES';
    constMappingQuoteStyle[0] = 'ENT_NOQUOTES';
    constMappingQuoteStyle[2] = 'ENT_COMPAT';
    constMappingQuoteStyle[3] = 'ENT_QUOTES';

    useTable       = !isNaN(table) ? constMappingTable[table] : table ? table.toUpperCase() : 'HTML_SPECIALCHARS';
    useQuoteStyle = !isNaN(quote_style) ? constMappingQuoteStyle[quote_style] : quote_style ? quote_style.toUpperCase() : 'ENT_COMPAT';

    if (useTable !== 'HTML_SPECIALCHARS' && useTable !== 'HTML_ENTITIES') {
        throw new Error("Table: "+useTable+' not supported');
        // return false;
    }
    //entities['38'] = '&amp;';
    if (useTable === 'HTML_ENTITIES') {
//        entities['160'] = '&nbsp;';
        entities['161'] = '&iexcl;';
        entities['162'] = '&cent;';
        entities['163'] = '&pound;';
        entities['164'] = '&curren;';
        entities['165'] = '&yen;';
        entities['166'] = '&brvbar;';
        entities['167'] = '&sect;';
        entities['168'] = '&uml;';
        entities['169'] = '&copy;';
        entities['170'] = '&ordf;';
        entities['171'] = '&laquo;';
        entities['172'] = '&not;';
        entities['173'] = '&shy;';
        entities['174'] = '&reg;';
        entities['175'] = '&macr;';
        entities['176'] = '&deg;';
        entities['177'] = '&plusmn;';
        entities['178'] = '&sup2;';
        entities['179'] = '&sup3;';
        entities['180'] = '&acute;';
        entities['181'] = '&micro;';
        entities['182'] = '&para;';
        entities['183'] = '&middot;';
        entities['184'] = '&cedil;';
        entities['185'] = '&sup1;';
        entities['186'] = '&ordm;';
        entities['187'] = '&raquo;';
        entities['188'] = '&frac14;';
        entities['189'] = '&frac12;';
        entities['190'] = '&frac34;';
        entities['191'] = '&iquest;';
        entities['192'] = '&Agrave;';
        entities['193'] = '&Aacute;';
        entities['194'] = '&Acirc;';
        entities['195'] = '&Atilde;';
        entities['196'] = '&Auml;';
        entities['197'] = '&Aring;';
        entities['198'] = '&AElig;';
        entities['199'] = '&Ccedil;';
        entities['200'] = '&Egrave;';
        entities['201'] = '&Eacute;';
        entities['202'] = '&Ecirc;';
        entities['203'] = '&Euml;';
        entities['204'] = '&Igrave;';
        entities['205'] = '&Iacute;';
        entities['206'] = '&Icirc;';
        entities['207'] = '&Iuml;';
        entities['208'] = '&ETH;';
        entities['209'] = '&Ntilde;';
        entities['210'] = '&Ograve;';
        entities['211'] = '&Oacute;';
        entities['212'] = '&Ocirc;';
        entities['213'] = '&Otilde;';
        entities['214'] = '&Ouml;';
        entities['215'] = '&times;';
        entities['216'] = '&Oslash;';
        entities['217'] = '&Ugrave;';
        entities['218'] = '&Uacute;';
        entities['219'] = '&Ucirc;';
        entities['220'] = '&Uuml;';
        entities['221'] = '&Yacute;';
        entities['222'] = '&THORN;';
        entities['223'] = '&szlig;';
        entities['224'] = '&agrave;';
        entities['225'] = '&aacute;';
        entities['226'] = '&acirc;';
        entities['227'] = '&atilde;';
        entities['228'] = '&auml;';
        entities['229'] = '&aring;';
        entities['230'] = '&aelig;';
        entities['231'] = '&ccedil;';
        entities['232'] = '&egrave;';
        entities['233'] = '&eacute;';
        entities['234'] = '&ecirc;';
        entities['235'] = '&euml;';
        entities['236'] = '&igrave;';
        entities['237'] = '&iacute;';
        entities['238'] = '&icirc;';
        entities['239'] = '&iuml;';
        entities['240'] = '&eth;';
        entities['241'] = '&ntilde;';
        entities['242'] = '&ograve;';
        entities['243'] = '&oacute;';
        entities['244'] = '&ocirc;';
        entities['245'] = '&otilde;';
        entities['246'] = '&ouml;';
        entities['247'] = '&divide;';
        entities['248'] = '&oslash;';
        entities['249'] = '&ugrave;';
        entities['250'] = '&uacute;';
        entities['251'] = '&ucirc;';
        entities['252'] = '&uuml;';
        entities['253'] = '&yacute;';
        entities['254'] = '&thorn;';
        entities['255'] = '&yuml;';


    }

    if (useQuoteStyle !== 'ENT_NOQUOTES') {
        entities['34'] = '&quot;';
    }
    if (useQuoteStyle === 'ENT_QUOTES') {
        entities['39'] = '&#39;';
    }
//    entities['60'] = '&lt;';
//    entities['62'] = '&gt;';


    // ascii decimals to real symbols
    for (decimal in entities) {
        symbol = String.fromCharCode(decimal);
        hash_map[symbol] = entities[decimal];
    }

    return hash_map;
}
//htmlentities('»')


///////////////////////////////////////////

function replace_all(s_txt,s_find,s_replace){
	// THIS FUNCTION IS NEEDED BECAUSE String.gsub DOESN'T WORK CORRECTLY IN SOME CASES
	while(s_txt.indexOf(s_find)>-1){
		s_txt = s_txt.replace(s_find,s_replace);
	}
	return s_txt;
}

function remove_newlines(txt){
	txt = replace_all(txt.gsub('[\n\r\t]',' '),'  ',' ');
	txt = txt.replace(/\u000d\u000a/g,' ');
	txt = txt.replace(/\u000a/g,' ');
	txt = txt.replace(/\u000d/g,' ');
	txt = txt.replace(/  /g,' ');
	return txt;
}

function navigate_to(url){
	// SETTING window.location DOESN'T ALWAYS WORK IN IE6
	window.setTimeout(function(){
	    window.location = url;
	},0);
}

function add_touch_scroll(id) {
	var el = document.getElementById(id);
	var scrollStartPosY = 0;
	var scrollStartPosX = 0;

	document.getElementById(id).addEventListener("touchstart", function (event) {
		scrollStartPosY = this.scrollTop + event.touches[0].pageY;
		scrollStartPosX = this.scrollLeft + event.touches[0].pageX;
	}, false);

	document.getElementById(id).addEventListener("touchmove", function (event) {
		if ((this.scrollTop < this.scrollHeight - this.offsetHeight && this.scrollTop + event.touches[0].pageY < scrollStartPosY - 5) || (this.scrollTop != 0 && this.scrollTop + event.touches[0].pageY > scrollStartPosY + 5)) event.preventDefault();
		if ((this.scrollLeft < this.scrollWidth - this.offsetWidth && this.scrollLeft + event.touches[0].pageX < scrollStartPosX - 5) || (this.scrollLeft != 0 && this.scrollLeft + event.touches[0].pageX > scrollStartPosX + 5)) event.preventDefault();
		this.scrollTop = scrollStartPosY - event.touches[0].pageY;
		this.scrollLeft = scrollStartPosX - event.touches[0].pageX;
	}, false);
}

Event.observe(window, 'load', function () {
	var slideshows = $$('.mini_slideshow_video');
	slideshows.each(function (mini_slideshow_video) {
		mini_slideshow_video.select('.mini_slideshow_controls span[video]').each(function (span) {
			span.observe('click', function () {
				var mini_slideshow_controls = span.up('.mini_slideshow_controls');
				mini_slideshow_controls.select('span[video]').each(function (elt) {
					elt.removeClassName('selected');
				});
				span.addClassName('selected');
				var slideshow_viewport = mini_slideshow_controls.previous('.slideshow_viewport');
				if (slideshow_viewport) {
					slideshow_viewport.select('.slideshow_slide').each(function (slide) {
						slide.style.display = 'none';
					});
					var slide = slideshow_viewport.down('.slideshow_slide#' + span.getAttribute('video'));
					if (slide) slide.style.display = 'block';
				}
			});
		});
		var first_video = mini_slideshow_video.down('.slideshow_viewport .slideshow_slide');
		if (first_video) first_video.style.display = 'block';
	});
	
	// POP OPEN EXTERNAL LINKS IN A NEW WINDOW
	var anchors = $$('#page_content_padding a');
	if(anchors){
		anchors.each(function(anchor){
			if(anchor.getAttribute('href')&&anchor.getAttribute('href').indexOf('http:')>-1&&anchor.getAttribute('href').indexOf(window.location.host)==-1&&!anchor.getAttribute('target')) anchor.setAttribute('target','_blank');
		});
	}
	
}, false);

function array_get_index(_array,value){
	for(i in _array){
		if(_array.hasOwnProperty(i)&&_array[i]==value) return i
	}
	return -1;
}

function activate_links(html,monrovia_links_only){
	var regex_string = 'http://';
	if(monrovia_links_only) regex_string += '([a-zA-Z])*(.){0,1}monrovia.com';
	regex_string += '([a-zA-Z0-9/.\\-_%#?=])*';
	return html.replace(new RegExp(regex_string,'gi'),function(match){
		return '<a href="' + match + '">' + match + '</a>';
	});
}

/*
function activate_email_links(html){
	// ACTIVATES monrovia.com EMAIL ADDRESSES ONLY
	var regex_string = '([a-zA-Z0-9._-])+@monrovia.com';
	return html.replace(new RegExp(regex_string,'gi'),function(match){
		return '<a href="mailto:' + match + '">' + match + '</a>';
	});
}
*/