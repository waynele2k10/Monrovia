monrovia.runtime_data.tmr_subscriber_tooltip = null;
monrovia.runtime_data.subscriber_tooltip_subscriber_user_name = null;

function init_subscriber_tooltips(){
	var tooltip = $('subscriber_tooltip');
	var subscribers = $$('#subscribers .subscriber:not([data-inited="true"])');

	subscribers.each(function(subscriber){
		subscriber.setAttribute('data-inited','true');
		subscriber.observe('mouseenter',function(evt){
			subscriber.setAttribute('data-hover','true');
			if(monrovia.runtime_data.subscriber_tooltip_subscriber_user_name!=subscriber.getAttribute('data-user-name')){
				tooltip.hide();
				monrovia.runtime_data.subscriber_tooltip_subscriber_user_name = subscriber.getAttribute('data-user-name');
				window.clearTimeout(monrovia.runtime_data.tmr_subscriber_tooltip);

				monrovia.runtime_data.tmr_subscriber_tooltip = window.setTimeout(function(){
					// POPULATE AND DISPLAY TOOLTIP
					tooltip.style.left = (subscriber.getAttribute('data-cursor-x')||Event.pointerX(evt)) + 'px';
					tooltip.style.top = (subscriber.getAttribute('data-cursor-y')||Event.pointerY(evt)) + 'px';

					$('lnk_user_name').update(subscriber.getAttribute('data-user-name'));

					if(subscriber.getAttribute('data-website-name')&&subscriber.getAttribute('data-website-name')){
						$('lnk_user_website').update(subscriber.getAttribute('data-website-name'));
						$('lnk_user_website').setAttribute('href',subscriber.getAttribute('data-website-url'));
						$('user_website').show();
					}else{
						$('user_website').hide();
					}

					$('lbl_total_questions').update(subscriber.getAttribute('data-total-questions'));
					$('lbl_total_answers').update(subscriber.getAttribute('data-total-answers'));
					$('lbl_member_since').update(subscriber.getAttribute('data-member-since'));

					new Effect.Appear(tooltip,{ 'duration':.25 });
				},750);
			}
		});

		subscriber.observe('mousemove',function(evt){
			subscriber.setAttribute('data-cursor-x',Event.pointerX(evt));
			subscriber.setAttribute('data-cursor-y',Event.pointerY(evt));
		});

		subscriber.observe('mouseleave',function(){
			subscriber.removeAttribute('data-hover');
			window.setTimeout(function(){
				if(monrovia.runtime_data.subscriber_tooltip_subscriber_user_name!=subscriber.getAttribute('data-user-name')||tooltip.getAttribute('data-hover')!='true'){
					tooltip.hide();
					monrovia.runtime_data.subscriber_tooltip_subscriber_user_name = '';
				}
			},100);
		});
	});
}

Event.observe(window,'load',function(){
	var tooltip = $('subscriber_tooltip');
	tooltip.observe('mouseenter',function(){
		tooltip.setAttribute('data-hover','true');
	});
	tooltip.observe('mouseleave',function(){
		tooltip.removeAttribute('data-hover');
		window.setTimeout(check_tooltip_status,100);
	});
	init_subscriber_tooltips();
});

function check_tooltip_status(){
	var tooltip = $('subscriber_tooltip');
	var hovered_subscriber = $$('#subscribers .subscriber[data-hover="true"]');
	if(hovered_subscriber.length){
		if(monrovia.runtime_data.subscriber_tooltip_subscriber_user_name!=hovered_subscriber[0].getAttribute('data-user-name')){
			monrovia.runtime_data.subscriber_tooltip_subscriber_user_name = hovered_subscriber[0].getAttribute('data-user-name')
			window.clearTimeout(monrovia.runtime_data.tmr_subscriber_tooltip);
			tooltip.hide();
		}
	}else{
		if(tooltip.getAttribute('data-hover')!='true'){
			tooltip.hide();
			monrovia.runtime_data.subscriber_tooltip_subscriber_user_name = '';
		}
	}
}

function load_subscribers(){
	monrovia.sections.qa.get_subscribers({
		'id':monrovia.runtime_data.user_id,
		'start_index':$$('#subscribers .subscriber').length,
		'prefer_user_name':monrovia_user_data.name||''
	},function(response){
		response = response.responseText.evalJSON();
		$('subscribers').update($('subscribers').innerHTML + response.html);
		if(response.subscribers_remaining==0){
			$('lnk_view_more').remove();
		}else{
			init_subscriber_tooltips();
		}
	});
}