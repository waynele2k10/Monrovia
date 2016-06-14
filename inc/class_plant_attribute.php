<?
require_once('class_plant.php');
//$temp = new plant(97);
//$temp->update_keywords();exit;
class plant_attribute_list {
	function plant_attribute_list($attribute_name){
		$this->name = $attribute_name;
		$this->column_names = get_columns_names('list_'.$attribute_name);
		$this->column_info = get_table_column_info('list_'.$attribute_name);
		$this->supports_historical = in_array('is_historical',$this->column_names);
		$this->supports_synonyms = in_array('synonyms',$this->column_names);
		$this->is_ordinal = in_array('ordinal',$this->column_names);
		$this->friendly_name = ucwords(str_replace('_',' ',$this->name));
	}
}

class plant_attribute_list_item extends record {
	// THIS IS SIMPLY A LIST OF ALL COLUMNS FROM ATTRIBUTE LIST TABLE, EXCEPT FOR id
	var $table_fields = 'name,is_historical,synonyms,ordinal';

	function plant_attribute_list_item($attribute_name,$record_id = ''){
		$this->table_name = 'list_' . $attribute_name;
		$this->attribute_name = $attribute_name;
		if($record_id!='') $this->load($record_id);

		// REASSIGN TABLE FIELDS
		$temp = new plant_attribute_list($attribute_name);
		$this->table_fields = '';
		//Added temp array.  For some reason it was dublicating all the names of the columns
		foreach($temp->column_names as $column_name){
			if($column_name!=''&&$column_name!='id'&&!in_array($column_name, $temp_array)) $this->table_fields .= ',' . $column_name;
			$temp_array[] = $column_name;
		}
		$this->table_fields = substr($this->table_fields,1);
	}

	function generate_bulk_action_sql($new_attribute_id){
		// GATHER IDs, REASSIGN
		$temp = new plant();
		if(contains(','.$temp->table_fields.',',','.$this->attribute_name.'_id,')){
			$cmd_query = 'SELECT id FROM plants WHERE  ' . $this->attribute_name . '_id=' . $this->info['id'];
			$cmd_reassign = 'UPDATE plants SET ' . $this->attribute_name . '_id=' . $new_attribute_id . ' WHERE ' . $this->attribute_name . '_id=' . $this->info['id'];
			$cmd_delete = 'UPDATE plants SET ' . $this->attribute_name . '_id="" WHERE ' . $this->attribute_name . '_id=' . $this->info['id'];
		}else{
			$cmd_query = 'SELECT DISTINCT plant_id AS id FROM plant_'.$this->attribute_name.'_plants WHERE ' . $this->attribute_name . '_id=' . $this->info['id'];
			$cmd_reassign = 'UPDATE plant_'.$this->attribute_name.'_plants SET '.$this->attribute_name.'_id=' . $new_attribute_id . ' WHERE ' . $this->attribute_name . '_id=' . $this->info['id'];
			$cmd_delete = 'DELETE FROM plant_'.$this->attribute_name.'_plants WHERE ' . $this->attribute_name . '_id=' . $this->info['id'] . ' AND ' . $this->attribute_name . '_id=' . $this->info['id'];

		}
		return array('query'=>$cmd_query,'reassign'=>$cmd_reassign,'delete'=>$cmd_delete);
	}

	function get_bulk_action_plant_ids($sql){
		$result = mysql_query($sql);
		$plant_ids = array();
		$num_rows = mysql_num_rows($result);
		for($i=0;$i<$num_rows;$i++){
			$plant_ids[] = mysql_result($result,$i,'id');
		}
		return $plant_ids;
	}

	function reassign($new_attribute_id){
		// GATHER IDs
		$sql = $this->generate_bulk_action_sql($new_attribute_id);
		$plant_ids = $this->get_bulk_action_plant_ids($sql['query']);

		// REASSIGN
		mysql_query($sql['reassign']);
		$ret = mysql_affected_rows();

		// UPDATE KEYWORDS
		update_plant_keywords($plant_ids);

		return $ret;
	}

	function delete(){
		// GATHER IDs
		$sql = $this->generate_bulk_action_sql('');
		$plant_ids = $this->get_bulk_action_plant_ids($sql['query']);

		// DELETE
		mysql_query($sql['delete']);
		$ret = mysql_affected_rows();

		// UPDATE KEYWORDS
		update_plant_keywords($plant_ids);

		parent::delete();

		return $ret;
	}

	function save($update_keywords = false){
		$ret = parent::save();
		if($update_keywords){
			// GATHER IDs
			$sql = $this->generate_bulk_action_sql('');
			$plant_ids = $this->get_bulk_action_plant_ids($sql['query']);

			// UPDATE KEYWORDS
			update_plant_keywords($plant_ids);
		}
		return $ret;
	}

	function get_usage(){
		// RETURNS THE NUMBER OF PLANTS USING THIS ATTRIBUTE

		// DETERMINE IF THIS IS A ONE-TO-ONE OR A ONE-TO-MANY ATTRIBUTE
		$temp = new plant();
		if(contains(','.$temp->table_fields.',',','.$this->attribute_name.'_id,')){
			$sql = 'SELECT COUNT(*) AS usages FROM plants WHERE '.$this->attribute_name.'_id=' . $this->info['id'];
		}else{
			$sql = 'SELECT COUNT(*) AS usages FROM plant_' . $this->attribute_name . '_plants WHERE ' . $this->attribute_name . '_id=' . $this->info['id'];
		}

		$result = mysql_query($sql);
		if($result){
			return intval(mysql_result($result,0,"usages"));
		} else{
			return 0;
		}
	}
}

function get_unordered_list_names(){
	$table_names = get_list_table_names();
	$ret = array();
	foreach($table_names as $table_name){
		$list = new plant_attribute_list(substr($table_name,5));
		if(!$list->is_ordinal) $ret[] = $list;
	}
	return $ret;
}

?>