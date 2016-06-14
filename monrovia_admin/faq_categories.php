<? include('inc/header.php'); ?>
<? $monrovia_user->permission_requirement('cmgt'); ?>
<? $monrovia_user->permission_requirement('html'); ?>
<? require_once($_SERVER['DOCUMENT_ROOT'].'/inc/class_faqs.php'); ?>
<?
	if(isset($_POST['action'])&&$_POST['action']=='save'){
		if(save_category_data(json_parse($_POST['data']['consumer']))&&save_category_data(json_parse($_POST['data']['retailer']))){
			output_page_notice('Your changes have been saved.');
		}else{
			output_page_notice('ERROR: An error occurred and your changes may not have been saved.');
		}
	}
	function save_category_data($data){
		$num_errors = 0;
		for($i=0;$i<count($data);$i++){
			$category = new faq_category($data[$i]->id);
			if($data[$i]->id!=$category->info['id']){
				$num_errors++; // THIS MAY OCCUR WHEN RECORD DOESN'T EXIST
			}else{
				if($data[$i]->is_delete=='1'){
					$num_errors += ($category->delete())?0:1;
				}else{
					$category->info['is_active'] = $data[$i]->is_active;
					$category->info['name'] = $data[$i]->name;
					$category->info['ordinal'] = $i;
					$category->info['audience'] = $data[$i]->audience;
					$num_errors += ($category->save())?0:1;
				}
			}
		}

		// CLEAR CACHE FILES (IF ONES EXIST)
		$cache = new cache('home :: about us :: faqs');
		if($cache->exists) $cache->remove();
		$cache = new cache('home :: retail :: faqs');
		if($cache->exists) $cache->remove();

		return ($num_errors==0);
	}
	function json_parse($raw){
		$ret = array();
		$raw = str_replace('[{','',$raw);
		$raw = str_replace('}]','',$raw);
		$raw_json = explode('},{',$raw);
		for($i=0;$i<count($raw_json);$i++){
			$ret[] = from_json('{'.stripslashes($raw_json[$i]).'}');
		}
		return $ret;
	}
	function append_to_head(){
		?>
		<link href="/inc/packer.php?path=/monrovia_admin/css/sortable_list.css" rel="stylesheet" type="text/css" />
		<style>
			.add_new {
				border:1px solid #ccc;
				padding:8px;
				width:220px;
				margin-bottom:6px;
			}
			.add_new .field_label {
				text-align:left;
				display:inline;
			}
			.add_new .title {
				font-size:9pt;
				font-weight:bold;
				padding-bottom:6px;
			}
		</style>
		<script type="text/javascript" src="/inc/packer.php?path=/monrovia_admin/js/sortable_list.js"></script>
		<script type="text/javascript">
			var num_new_items = 0;
			function page_validate(){
				$$('.sortable_list').each(function(list){
					var field = new Element('input');
					field.type = 'hidden';
					field.name = 'data['+list.getAttribute('audience')+']';
					field.value = category_serialize(list.id);
					$('form_faq_category').appendChild(field);
				});
				// SET "SAVE" FLAG ONLY IF THIS GETS EXECUTED, WHICH SHOULDN'T HAPPEN IF A JS ERROR OCCURS
				get_field('action').value = 'save';
				return true;
			}
			function page_cancel(){
				window.location = 'index.php';
			}
			function add_category(list,elt){
				var input = elt.previous('input');
				num_new_items++;
				if(sortable_list_valid_name_inline($(list),input.value)&&input.value.length<=40){
					sortable_list_add_item_inline(list,elt,num_new_items);
					$('item_new_'+num_new_items).down('span.total_items').update('0 items');
				}else{
					input.value = '';
				}
			}
			function category_serialize(id){
				var ret = [];
				var audience = $(id).getAttribute('audience');
				$(id).select('li').each(function(li){
					var name = li.down('input').value;
					var id = (li.id.indexOf('_new_')==-1)?li.id.replace('item_',''):'';
					var is_active = (li.hasClassName('inactive'))?'0':'1';
					var is_delete = (li.hasClassName('deleted'))?'1':'0';
					var item = new category_item(id,name,audience,is_active,is_delete);
					ret.push(item);
				});
				return Object.toJSON(ret);
			}
			function category_item(id,name,audience,is_active,is_delete){
				this.id = id;
				this.name = name;
				this.audience = audience;
				this.is_active = is_active;
				this.is_delete = is_delete;
			}
		</script>

		<?
	}
?>
	<form id="form_faq_category" action="" onsubmit="return page_validate();" onkeypress="return cancel_enter_key(window.event||event);" method="post">
		<h2 id="page_subheader"><?=(isset($record->info['common_name'])?$record->info['common_name']:'')?></h2>
		<div style="margin-top:4px;">
			<input type="submit" value="Save Changes" />
			<input type="button" value="Cancel" onclick="page_cancel();" />
		</div>
		<div class="slimTabContainer" id="ctlTabs">
			<div>
				<div class="slimTab spacer" style="width:4px">&nbsp;</div>
				<div class="slimTab selected" tab="1" title="Alt + Shift + 1">1. Consumer</div>
				<div class="slimTab" tab="2" title="Alt + Shift + 2">2. Retailer</div>
				<div class="slimTab spacer" style="width:32px;">&nbsp;</div>
			</div>
			<div class="slimTabBlurb sel" tab="1">
				<div class="add_new">
					<div class="title">Add A New Category</div>
					<div class="field_label">Name: </div>
					<input type="text_field" maxlength="40" />
					<input type="button" value="Add" onclick="add_category('lst_categories_consumer',this);" />
				</div>
				<ul id="lst_categories_consumer" class="sortable_list inline_edit" audience="consumer">
					<? cms_output_categories('consumer'); ?>
				</ul>
			</div>
			<div class="slimTabBlurb" tab="2">
				<div class="add_new">
					<div class="title">Add A New Category</div>
					<div class="field_label">Name: </div>
					<input type="text_field" maxlength="40" />
					<input type="button" value="Add" onclick="add_category('lst_categories_retailer',this);" />
				</div>
				<ul id="lst_categories_retailer" class="sortable_list inline_edit" audience="retailer">
					<? cms_output_categories('retailer'); ?>
				</ul>
			</div>
		</div>
		<input type="hidden" name="action" value="" />
	</form>
<? include('inc/footer.php'); ?>