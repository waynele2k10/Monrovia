function sortable_list_add_item_inline(list,elt,new_index){
	var li = new Element('li');
	li.id = 'item_new_'+new_index;
	var input = elt.previous('input');
	li.innerHTML = '<div class="control"><img src="/img/spacer.gif" title="Make Inactive" class="make_active_inactive" /><img src="img/icon_pencil.png" title="Rename" class="rename" /><img src="img/icon_cross.png" title="Delete" class="delete" /></div><input maxlength="40" value="'+input.value+'" /><span class="title">'+input.value+'</span><span class="total_items"></span>';
	$(list).appendChild(li);
	li.highlight();
	sortable_list_init_row_inline(li);
	Sortable.destroy($(list).id);
	Sortable.create($(list).id);
	input.value = '';
}
function sortable_list_item_begin_rename_inline(elt){
	sortable_list_clear_edit_mode();
	var li = elt.up('li');
	li.addClassName('edit_mode');
	li.down('input').focus();
}
function sortable_list_item_toggle_active(img){
	sortable_list_clear_edit_mode();
	var li = img.up('li');
	li.toggleClassName('inactive');
	img.title = (li.hasClassName('inactive'))?'Make Active':'Make Inactive';
}
function sortable_list_clear_edit_mode(){
	$$('.sortable_list').each(function(list){
		list.blur();
	});
	$$('.sortable_list li').each(function(li){
		li.removeClassName('edit_mode');
	});
}
function sortable_list_item_rename(input){
	sortable_list_clear_edit_mode();
	var li = input.up('li');
	var new_name = li.down('input').value;
	li.down('span').innerHTML = new_name;
}
function sortable_list_item_begin_delete(elt,item_name){
	sortable_list_clear_edit_mode();
	var li = elt.up('li');
	if(!item_name) item_name = li.down('input').value;
	if(li.id.indexOf('_new_')>-1){
		// NEW CATEGORY, NO NEED FOR A CONFIRMATION
		li.up('.sortable_list').removeChild(li);
	}else{
		// EXISTING CATEGORY; CONFIRM
		if(confirm('Are you sure you want to delete "'+item_name+'"?\n\nThis action cannot be reversed.')){
			li.addClassName('deleted');
		}
	}
}
function sortable_list_init_row_inline(li){
	li.down('div.control img.rename').observe('click',function(){
		sortable_list_item_begin_rename_inline(this);
	},true);
	li.down('div.control img.delete').observe('click',function(){
		sortable_list_item_begin_delete(this);
	},true);
	li.down('div.control img.make_active_inactive').observe('click',function(){
		sortable_list_item_toggle_active(this);
	},true);
	li.down('input').observe('blur',function(){
		sortable_list_item_rename(this);
	},true);
	li.down('input').observe('focus',function(){
		sortable_list_item_begin_rename_inline(this);
	},true);
	li.down('span').observe('mousedown',function(){
		sortable_list_clear_edit_mode();
		return false;
	},true);
}
function sortable_list_valid_name_inline(list,name){
	var ret = (name!='');
	list.select('li input').each(function(input){
		if(input.value==name) ret = false;
	});
	return ret;
}
Event.observe(window,'load',function(){
	$$('.sortable_list').each(function(list){
		Sortable.create(list.id);
	});
	// INIT ROW CONTROLS
	$$('.sortable_list.inline_edit li').each(function(li){
		var list = li.up('.sortable_list');
		if(list.hasClassName('inline_edit')){
			sortable_list_init_row_inline(li);
		}else{
			//sortable_list_init_row(li);
		}
	});
});