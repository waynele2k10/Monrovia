function slimTab_hide(tabContainer){
	// GET ALL TABS WITHIN CONTAINER
	var slimTabs = Element.getElementsBySelector(tabContainer,'.slimTab');
	slimTabs.each(
		function(item){
			// SET AS INACTIVE
			Element.removeClassName(item,'selected');
		}
	);
	// GET ALL BLURBS WITHIN CONTAINER
	var tabBlurbs = Element.getElementsBySelector(tabContainer,'.slimTabBlurb');
	tabBlurbs.each(
		function(item){
			// HIDE
			Element.removeClassName(item,'sel');
		}
	);
}

function slimTab_clicked(tab){
	// GET TAB NUMBER
	var tabNum = tab.getAttribute('tab');
	if(tabNum){
		// GETS THE CONTAINING DIV
		var tabContainer = Element.up(tab,'div.slimTabContainer');

		// GET CORRESPONDING TAB BLURB (WOW...CSS SELECTORS RULE)
		var tabBlurb = Element.down(tabContainer,'div.slimTabBlurb[tab="'+ tabNum +'"]');

		// HIDE ALL BLURBS
		slimTab_hide(tabContainer);

		// SHOW ACTIVE BLURB
		Element.addClassName(tabBlurb,'sel');

		// SET TAB TO ACTIVE
		Element.addClassName(tab,'selected');

		focus_first_field(tabBlurb);
	}
}

function slimTab_init(){
	// GET ALL SLIM TAB TABS
	var slimTabContainers = $$('div.slimTabContainer div.slimTab');
	slimTabContainers.each(
		function(item){
			// SET ONCLICK EVENT FOR TAB
			Event.observe(item,'click',function(){slimTab_clicked(this)},false);
		}
	);
}
function slimTab_get_current(containerId){
	return $$('div.slimTabContainer#' + containerId + ' div.slimTab.selected')[0].getAttribute('tab');
}

function slimTab_showTab(containerId,tab){
	// THIS METHOD SHOWS A SPECIFIC TAB IN A SPECIFIC TAB CONTAINER
    try{
    	var tabs;
	if(typeof(tab)=="number"){
	    tabs = $$('div.slimTabContainer#' + containerId + ' div.slimTab[tab="' + tab + '"]');
	    slimTab_clicked(tabs[0]);
	}else{
	    tabs = $$('div.slimTabContainer#' + containerId + ' div.slimTab');
	    tabs.each(
	        function(item){
	            if(item.innerHTML==tab){
	               	slimTab_clicked(item);
	            }
	        }
	    );
	}
    }catch(err){}
}

function focus_first_field(tabBlurb){
	var first_field = tabBlurb.down('input,select,textarea');
	if(first_field) first_field.focus();
}

Event.observe(window,'load',slimTab_init,false);

///

// KEYBOARD SHORTCUTS
Event.observe(document,'keyup',function(objEvent){
	objEvent = (objEvent||window.event);
	if(altPressed(objEvent)&&shiftPressed(objEvent)){
		slimTab_showTab('ctlTabs',window.parseFloat(String.fromCharCode(objEvent.keyCode)));
	}
	$$('.slimTab').each(function(tab){
		if(tab.style.backgroundColor!='#ffffff') tab.style.backgroundColor = '#fff';
	});
});
Event.observe(document,'keydown',function(objEvent){
	objEvent = (objEvent||window.event);
	if(altPressed(objEvent)&&shiftPressed(objEvent)){
		$$('.slimTab').each(function(tab){
			tab.style.backgroundColor = '#ffc';
		});
	}
});

function altPressed(objEvent){
	return (objEvent.altKey||(objEvent.modifiers%2));
}
function ctrlPressed(objEvent){
	return (objEvent.ctrlKey||objEvent.modifiers==2||objEvent.modifiers==3||objEvent.modifiers>5);
}
function shiftPressed(objEvent){
	return (objEvent.shiftKey||objEvent.modifiers>3);
}
function cancel_enter_key(evt){
	return evt.keyCode!=13;
}