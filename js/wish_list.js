var listing_delete;
function toggle_please_wait_message(show){
	if(show){
		$('remove_ui').style.display = 'none';
		$('msg_please_wait').style.display = 'block';
	}else{
		$('msg_please_wait').style.display = 'none';
		$('remove_ui').style.display = 'block';
	}
}
function confirm_remove(){
	toggle_please_wait_message(true);
	listing_delete.fade({duration:.5});
	window.setTimeout(function(){remove_item(listing_delete);},1000);
	var container = $('wish_list_items');
	if(container){
		if(container.select('.wish_list_plant_listing[plant_id]').length==1){
			// HIDE ADDITIONAL ELEMENTS
			$$('.paging,#wish_list_buttons').each(function(item){
				item.style.display = 'none';
			});
			$('notification').style.display = 'block';
			$('notification').update('Please wait...');
			$('notification').highlight();
		}
	}
}

Event.observe(window,'load',function(){
	if(window.details.queryString()['notice']=='wish_list_full'){
		$('notification').style.display = 'block';
		$('notification').update('Sorry, but your wish list has reached the maximum limit allowed. Please choose a plant to delete before adding a new one.');
		$('notification').highlight();
	}
	$$('.notes_view').each(function(notes_view){
		notes_view.observe('click',function(){
			var listing = this.up('.wish_list_plant_listing');
			var textarea = listing.down('textarea');
			textarea.value = listing.getAttribute('notes');
			listing.addClassName('edit_mode');
			try {
				textarea.focus();
			}catch(err){}
		});
	});
	$$('.btn_cancel_small,.btn_save_small').each(function(btn){
		btn.observe('click',function(){
			var listing = this.up('.wish_list_plant_listing');
			listing.removeClassName('edit_mode');

			var notes = '';

			// SET IF "save" CLICKED
			if(btn.hasClassName('btn_save_small')){
				listing.setAttribute('notes',(listing.down('textarea').value+'').stripTags());
				// UPDATE
				notes = listing.getAttribute('notes');
				wish_list_update_item(listing.getAttribute('wish_list_id'),listing.getAttribute('plant_id'),'item_upsert',notes);
			}
			// SYNCHRONIZE
			notes = listing.getAttribute('notes');
			listing.down('.notes_view').textContent = listing.down('.notes_view').innerText = ((notes)?notes:'Click me to add notes');
			listing.down('textarea').value = notes;
		});
	});
	$$('.icon_wish_list_remove').each(function(btn){
		btn.observe('click',function(){
			listing_delete = this.up('.wish_list_plant_listing');
			toggle_please_wait_message(false);
			modal_data.close_on_outside_click = false;
			modal_show({'modal_id':'modal_remove_confirm','effect':'fade'});
		});
	});
});

function remove_item(listing){
	var container = listing.up('.plant_listings_four_col');
	if(container){
		if(container.select('.wish_list_plant_listing[plant_id]').length==1){
			$('wish_list_buttons').hide();
			$('lnk_wishlist_email').hide();
			$('lnk_wishlist_download').hide();
		}

		wish_list_remove_item(listing.getAttribute('wish_list_id'),listing.getAttribute('plant_id'),function(response){
			modal_hide();
			toggle_please_wait_message(false);
			var container = $('wish_list_items');
			if(container){
				if(!container.select('.wish_list_plant_listing[plant_id]').length){
					// HIDE ADDITIONAL ELEMENTS
					$$('.paging,#wish_list_buttons').each(function(item){
						item.style.display = 'none';
					});
					reload_page();
				}
			}

		});
		// IF THERE ARE OTHER PLANTS ON THE PAGE, VISUALLY REMOVE PLANT
		container.removeChild(listing);
	}
}


function wish_list_email(wish_list_id){
	new Ajax.Request('/community/wish-list-email.php', {
	  method: 'post',
	  onSuccess:function(transport){
		    var success = transport.responseText.contains('success');
			var message = (success)?'We\'re sending your wish list to <b>'+monrovia_user_data.email_address+'</b>. Please check your email in a few minutes.<br />Note: Please add <b>website@monrovia.com</b> to your "safe" list to be sure it doesn\'t end up in your junk mail folder.':'An error occurred and we were unable to send you your wish list. Please try again later.<br />Note: You can also download your wish list in the form of an <a href="wish-list-export.php" target="_blank">Excel spreadsheet</a>.';
			$('notification').style.display = 'block';
			$('notification').update(message);
			$('notification').highlight();
	  }
	});
}

function reload_page(){
	// WE WANT TO OMIT THE "notice" URL PARAM, SO WE'RE NOT USING window.location.reload.
	var url = '/community/your-wish-list.php';
	if(window.details.queryString()['page']) url += '?page='+window.details.queryString()['page'];
	window.location.href = url;
}