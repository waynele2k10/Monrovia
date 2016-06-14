<? include('inc/header.php'); ?>
<? require_once($_SERVER['DOCUMENT_ROOT'].'/inc/class_plant_attribute.php'); ?>
<?
	$lists = get_unordered_list_names();

	$list_name = '';
	if(isset($_GET['list_name'])) $list_name = $_GET['list_name'];
	if(!in_array('list_'.$list_name,get_list_table_names())) $list_name = 'deciduous_evergreen';
	$list = new plant_attribute_list($list_name);

	function append_to_head(){
	?>
		<style>
			#table_attributes {
				margin-top:8px;
				width:982px;
			}
			#table_attributes td {
				font-size:9pt;
			}
			#table_attributes {
				border-collapse:collapse;
			}
			#table_attributes td {
				padding:4px 8px 4px 8px;
			}
		</style>
		<script>
			function set_list(){
				window.location = '?list_name=' + get_field('list_name').value;
			}
		</script>
	<?
	}

?>
	<h2 id="page_subheader">Plant Attributes</h2>
	<div id="page_content">
		<div style="margin-top:4px;">
			<input type="button" value="Cancel" onclick="page_cancel();" />
		</div>
		<hr />
		<div style="margin-top:8px;">
			<label class="field_label">Attribute List: </label>
			<select id="list_name" class="text_field" onchange="set_list();" _value="<?=$list_name?>">
			<?
				foreach($lists as $_list){
					if($_list->name!='release_status'){
					?>
						<option value="<?=$_list->name?>"><?=$_list->friendly_name?></option>
					<?
					}
				}
			?>
			</select>
		</div>
		<hr />
		<input type="button" value="Add A New Attribute" onclick="window.location = 'plant_attribute.php?list_name=<?=$list_name?>&attribute_id=new&action=edit';" />
		<table id="table_attributes">
			<thead>

				<tr style="background-color:#666;color:#fff;">
					<td width="65">Action</td>
					<td>Name</td>
					<? if($list->supports_synonyms){ ?><td>Synonyms</td><? } ?>
					<? if($list->supports_historical){ ?><td>Historical?</td><? } ?>
					<td width="1">Usage</td>
				</tr>
			</thead>
			<tbody id="table_attributes_contents">
			<? $attributes = get_table_data('list_'.$list_name);

				$i = 0;
				foreach($attributes as $attribute){
					$attribute = new plant_attribute_list_item($list_name,$attribute['id']);
					$num_plants = $attribute->get_usage();
				?>
				<tr<?=((!($i++%2))?'':' class="row_even"')?>>
					<td>
						<a href="plant_attribute.php?list_name=<?=$list_name?>&attribute_id=<?=$attribute->info['id']?>&action=edit"><img src="./img/icon_pencil.png" title="Edit" /></a>
						<? if($num_plants>0){ ?><a href="plant_attribute.php?list_name=<?=$list_name?>&attribute_id=<?=$attribute->info['id']?>&action=reassign"><img src="./img/icon_reassign.png" title="Reassign" /></a><? }else{ ?><img src="./img/icon_reassign.png" title="Reassign" style="opacity:.25;#filter:alpha(opacity=25);#zoom:1;" /><? } ?>
						<a href="plant_attribute.php?list_name=<?=$list_name?>&attribute_id=<?=$attribute->info['id']?>&action=delete" title="Delete"><img src="./img/icon_cross.png" /></a>
					</td>
					<td><nobr><?=$attribute->info['name']?></nobr></td>
					<? if($list->supports_synonyms){ ?><td><?=truncate($attribute->info['synonyms'],75)?></td><? } ?>
					<? if($list->supports_historical){ ?><td><?=(($attribute->info['is_historical']=='1')?'Yes':'No')?></td><? } ?>
					<td><nobr><?=$num_plants?> plant(s)</nobr></td>
				</tr>
			<? } ?>
			</tbody>
		</table>
	</div>
<? include('inc/footer.php'); ?>