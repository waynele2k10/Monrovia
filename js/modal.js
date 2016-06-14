var modal_data = {busy:true,blur_elements:[]}; // tmr, active_id, args, return_value, active, dark_screen_opacity, close_on_outside_click, onclosed_handler, onopened_handler, busy
function modal_show(args){
	if(modal_data.tmr||modal_data.busy) return;
	
	if(args.modal_id==modal_data.active_id) return;
	
	var is_a_modal_already_showing = !!modal_data.active_id;
	
	if(is_a_modal_already_showing) modal_hide(null,{ 'reason':'modal_show' });
	
	modal_data.args = args;
	modal_data.active = true;
	modal_data.effect_speed = (modal_data.effect_speed||10);
	modal_data.dark_screen_opacity = (modal_data.dark_screen_opacity||30);
	modal_data.return_value = '';
	
	var modal = $(args.modal_id);	
	if(modal){
		modal_data.active_id = modal.id;
		if(!is_a_modal_already_showing) set_opacity('modal_dark_screen',(args.effect=='fade')?0:modal_data.dark_screen_opacity);
		modal.style.display = 'block';

		if(!is_a_modal_already_showing) $('modal_container').setStyle({'display':'block','visibility':'hidden'});
		modal_reposition();
		if(!is_a_modal_already_showing) $('modal_container').style.visibility = 'visible';
		if(!is_a_modal_already_showing&&args.effect=='fade'){
			modal_data.opacity = 0;
			modal_data.tmr = window.setInterval(modal_fade,1);
		}
		if(!args.effect&&modal_data.onopened_handler){
			modal_data.onopened_handler(modal_data.active_id);
			toggle_background_blur(true);
		}
	}
}

function modal_hide(return_value,args){
	if(modal_data.tmr) return;
	modal_data.return_value = return_value;
	modal_data.active = false;
	if($(modal_data.active_id)){
		var modal = $(modal_data.active_id);
		modal.style.display = 'none';
		if(modal_data.args.effect=='fade'){
			modal_data.tmr = window.setInterval(modal_fade,1);
		}
		if(!(args&&args.reason=='modal_show')) $('modal_container').style.display = 'none';
		if(!modal_data.args.effect&&modal_data.onclosed_handler){
			modal_data.onclosed_handler(modal_data.active_id,modal_data.return_value);
			toggle_background_blur();
		}
		if(!modal_data.args.effect) modal_data.active_id = null;
	}
}

function modal_fade(){
	if(modal_data.active){
		modal_data.opacity += modal_data.effect_speed;
		modal_data.opacity = Math.min(modal_data.opacity,modal_data.dark_screen_opacity);
		if(modal_data.opacity==modal_data.dark_screen_opacity){
			window.clearInterval(modal_data.tmr);
			modal_data.tmr = 0;

			if(modal_data.onopened_handler){
				modal_data.onopened_handler(modal_data.active_id);
				toggle_background_blur(true);
			}

			modal_reposition.defer();
		}
	}else{
		modal_data.opacity -= modal_data.effect_speed;
		modal_data.opacity = Math.max(modal_data.opacity,0);
		if(modal_data.opacity==0){
			window.clearInterval(modal_data.tmr);
			modal_data.tmr = 0;
			modal_data.busy = false;
			$('modal_container').style.display = 'none';
			if(modal_data.onclosed_handler) modal_data.onclosed_handler(modal_data.active_id,modal_data.return_value);
			toggle_background_blur();
			modal_data.active_id = null;
		}
	}
	set_opacity('modal_dark_screen',modal_data.opacity);
	if(modal_data.opacity==modal_data.dark_screen_opacity){
		set_opacity(modal_data.active_id,100);
	}else{
		set_opacity(modal_data.active_id,modal_data.opacity);
	}
}

function modal_reposition(){
	var modal_active = $(modal_data.active_id);
	var modal_dark_screen = $('modal_dark_screen');
	if(modal_active&&modal_data.active&&!modal_data.busy){
	
		var modal_dimensions = [modal_active.clientWidth,modal_active.clientHeight];
		
		// OPERA, FF, SAFARI			
		var modal_dark_screen = $('modal_dark_screen');
		var height = window.pageYOffset||0;
		height += Math.max((window.innerHeight||document.documentElement.clientHeight),document.body.clientHeight);
		modal_dark_screen.style.height = height + 'px';

		// ACCOMMODATE FOR PAGE SCROLL TOP
		if((/iPhone|iPod/i).test(navigator.userAgent)){
			// ZOOMING DOES NOT AFFECT AND THEREFORE BREAKS iPAD's height=100%
			modal_active.setStyle({
				'left':window.pageXOffset + ((window.innerWidth - modal_dimensions[0])/2)+'px',
				'top':window.pageYOffset + ((window.innerHeight - modal_dimensions[1])/2)+'px',
				'marginLeft':'0px',
				'marginTop':'0px'
			});
		}else{
			var margins = [];
			margins[0] = -modal_dimensions[0]/2;
			margins[1] = -modal_dimensions[1]/2;
			modal_active.setStyle({
				'marginLeft':margins[0]+'px',
				'marginTop':margins[1]+'px'
			});
		}

		// FIXES RENDERING ISSUE WITH OPERA 9
		$('modal_container').style.display = 'block';
	}
}

function set_opacity(id,opacity){
	if(!$(id)) return;

	if(window.details.ieVersion()>-1&&window.details.ieVersion()<9){
		$(id).style.filter = 'alpha(opacity='+opacity+')';
		if(opacity==100){
			$(id).style.filter = '';
		}
	}else{
		$(id).style.opacity = opacity / 100;
	}
}

function modal_dark_screen_onclick(){
	if(modal_data.close_on_outside_click) modal_hide();
}

function modal_init(){
	if(!$('modal_container')) return;
	if(!$('modal_container').empty()){
		$('modal_container').style.display = 'block';
		// CREATE DARK SCREEN ELEMENT
		var dark_screen = document.createElement('div');
		dark_screen.id = 'modal_dark_screen';
		dark_screen.onclick = modal_dark_screen_onclick;
		$('modal_container').insertBefore(dark_screen,$('modal_container').childNodes[0]);

		// ADJUST FOR SCROLLING AND RESIZING
		Event.observe(window,'resize',modal_reposition);
		Event.observe(window,'scroll',modal_reposition);
	}
	// HIDE MODAL CONTAINER
	set_opacity('modal_container',100);
	$('modal_container').style.display = 'none';

	// MODAL TRIGGERS
	var lightview_anchors = $$('a.lightview');
	if(lightview_anchors.length){
		lightview_anchors.each(function(anchor){
			// PRELOAD
			//var img = new Image();
			//img.setAttribute('src',image_path);
			//$('image_container').appendChild(img);
			anchor.observe('click',function(evt){
				var image_path = anchor.getAttribute('href');
				evt.preventDefault();

				var title = anchor.getAttribute('title')||'';
				var parts = title.split('::');
				lightview_show_image(image_path,parts[0],parts[1]);
			});
		});
	}
	if(window.details.ieVersion()==6) $('modal_container').style.position = 'absolute';
}

function lightview_show_image(path,title,description){
	var img = new Image();

	if(img.observe){
		img.observe('load',function(){
			lightview_show_image2(img,title,description);
		});
	}else{
		img.onload = function(){
			lightview_show_image2(img,title,description);
		}
	}
	img.setAttribute('src',path);
}

function lightview_show_image2(img,title,description){
	$('image_container').update();
	$('image_container').appendChild(img);

	if(title){
		$('lightview_title').update(title);
		if(description){
			$('lightview_description').update(description);
			$('lightview_description').show();
		}else{
			$('lightview_description').hide();
		}
		$('lightview_info').style.display = 'block';
	}else{
		$('lightview_info').style.display = 'none';
	}
	modal_show({'modal_id':'modal_lightview','effect':'fade'});
}

function toggle_background_blur(enable){
	if(enable){
		modal_data.blur_elements = $$('body > :not(script,.print_only,.preload,#modal_container)');
		modal_data.blur_elements.each(function(elt){
			if(elt.addClassName) elt.addClassName('blur');
		});
	}else{
		modal_data.blur_elements.each(function(elt){
			if(elt.removeClassName) elt.removeClassName('blur');
		});
	}
	
}

// ADD SHORTHAND FUNCTION IF INEXISTENT
if(typeof $!='function') eval('function $(id){	return document.getElementById(id); }');

Event.observe(window,'load',function(){
	modal_init();

	// CUSTOM SETTINGS
	modal_data.close_on_outside_click = true;	// DEFAULTS TO false
	modal_data.effect_speed = 10;			// DEFAULTS TO 10
	modal_data.dark_screen_opacity = 60;		// DEFAULTS TO 50
	modal_data.busy = false;
	modal_data.onclosed_handler = function(modal_id,return_value){
		switch(modal_id){
			case 'modal_lightview':

			break;
			case 'modal_edit_plant':
				$('edit_plant_title').value = '';
				$('edit_plant_credit').value = '';
				$('edit_plant_source').value = '';
				$('edit_plant_expiration_date').value = '';
				$('edit_plant_is_primary').checked = '';
				$('edit_plant_is_active').checked = '';
				$('edit_plant_is_distributable').checked = '';
			break;
			case 'modal_edit_cover':
				update_cover_thumbnails();
			break;
			case 'modal_qa_flag':
				if(return_value&&modal_data&&modal_data.args&&modal_data.args.data){					
					var item_type = modal_data.args.data.item_type;
					var item_id = modal_data.args.data.item_id;
					monrovia.sections.qa.flag_item({
						'item_type':item_type,
						'item_id':item_id
					},function(){
						var actions_bar = $$('.actions_bar[data-item-type="'+item_type+'"][data-item-id="'+item_id+'"]');
						if(actions_bar.length) actions_bar[0].addClassName('flagged');
					});					
				}
			break;
			
			case 'modal_qa_yes_no':
				if(return_value){
					if(modal_data&&modal_data.args&&modal_data.args.data){
						switch(modal_data.args.data.action){
							case 'unsubscribe':
								var item_type = modal_data.args.data.item_type;
								var item_id = modal_data.args.data.item_id;
								monrovia.sections.qa.set_subscribe_status({
									'item_type':item_type,
									'item_id':item_id,
									'value':false
								},function(){
									var parent_id = item_type=='question'?'questions_subscribed_to':'users_subscribed_to';
									var elt = $$('#' + parent_id + ' .question_item[data-item-type="'+item_type+'"][data-item-id="'+item_id+'"]');
									if(elt.length){
										elt[0].remove();
										// ENSURE FIRST ITEM BELONGING TO PARENT IS MARKED AS "first"
										var first_item = $(parent_id).down('[data-item-type][data-item-id]');
										if(first_item){
											first_item.addClassName('first');
										}else{
											$(parent_id).addClassName('empty');
										}
									}
								});
							break;
							case 'remove_from_scrapbook':
								var item_type = modal_data.args.data.item_type;
								var item_id = modal_data.args.data.item_id;
								monrovia.sections.qa.set_scrapbook_status({
									'item_type':item_type,
									'item_id':item_id,
									'value':false
								},function(){
									var selector = '';
									if(item_type=='answer'){
										selector = '.answer[data-item-type="answer"][data-item-id="'+item_id+'"]';
										var elt = $$(selector)[0];										
										elt.remove();
									}else if(item_type='question'){
										selector = '.question_item[data-item-type="question"][data-item-id="'+item_id+'"]';
										var elt = $$(selector)[0];
										elt.removeClassName('scrapbooked');
									}
									clean_up_questions();
								});
							break;
						}

					}
				}
			break;
		}
	}
	modal_data.onopened_handler = function(modal_id){
		switch(modal_id){
			case 'modal_lightview':

			break;
			case 'modal_edit_plant':
				var data = get_image_segment_data(current_plant_image_id);
				$('edit_plant_title').value = data['title'];
				$('edit_plant_credit').value = data['credit'];
				$('edit_plant_source').value = data['source'];
				$('edit_plant_expiration_date').value = data['expiration_date'];
				$('edit_plant_is_primary').checked = data['is_primary'];
				$('edit_plant_is_active').checked = data['is_active'];
				$('edit_plant_is_distributable').checked = data['is_distributable'];
			break;
		}
	}
	
});