<?php
	//require_once('utility_functions.php');

	$sql_cache = array();
	$sql_queries = array();

	function sql_connect(){
		@mysql_connect($GLOBALS['server_info']['db']['host'], $GLOBALS['db_user'], $GLOBALS['db_pass']) or die ('<h3>Unable to connect to database.</h3>');
		@mysql_select_db($GLOBALS['server_info']['db']['name']) or die ('<h3>Unable to connect to database.</h3>');
	}
	function sql_disconnect(){
		@mysql_close();
	}
	function get_list_item_value($table_name,$id,$col_name = 'name'){
		if($id==0||$id=='') return;
                $ret = "";
		if(!is_suspicious(array($table_name,$id,$col_name))){
			$query = "SELECT $col_name FROM $table_name WHERE id=$id";
			if(isset($GLOBALS['sql_cache'][$query])) return $GLOBALS['sql_cache'][$query];
                        $mysql_query = mysql_query($query);
                        if ($mysql_query && mysql_num_rows($mysql_query)) {
                            $ret = mysql_result($mysql_query,0,$col_name);
                        }
			$GLOBALS['sql_cache'][$query] = $ret;
			//echo("SELECT $col_name FROM $table_name WHERE id=$id");
		}
		return $ret;
	}
	function get_list_item_id($table_name,$value,$col_name = 'name'){
		$value = sql_sanitize($value);
		if(!is_suspicious(array($table_name,$value,$col_name))){
			$query = "SELECT id FROM $table_name WHERE $col_name='$value'";
			if(isset($GLOBALS['sql_cache'][$query])) return $GLOBALS['sql_cache'][$query];
			$ret = mysql_result(mysql_query($query),0,'id');
			$GLOBALS['sql_cache'][$query] = $ret;
			//echo("SELECT id FROM $table_name WHERE $col_name='$value'");
		}
		return $ret;
	}
	/*function sql_sanitize($value, $remove_line_breaks=true){
		//$value = iconv('UTF-8','windows-1256',$value);
		//$value = htmlspecialchars($value,ENT_QUOTES,'UTF-8');

		$value = replace_smart_characters($value);

		$value = str_replace_unicode(153,"{{#153}}", $value);
		$value = str_replace_unicode(174,"{{#174}}", $value);
		$value = str_replace_unicode(169,"{{#169}}", $value);
		$value = str_replace_unicode(176,"{{#176}}", $value);
		$value = str_replace_unicode(188,"{{#188}}", $value);
		$value = str_replace_unicode(189,"{{#189}}", $value);
		$value = str_replace_unicode(190,"{{#190}}", $value);
		$value = str_replace_unicode(187,"{{#187}}", $value);
		$value = str_replace_unicode(171,"{{#171}}", $value);
		$value = str_replace_unicode(215,"{{#215}}", $value);
		$value = str_replace_unicode(232,"{{#232}}", $value);
		$value = str_replace_unicode(233,"{{#233}}", $value);

		$value = str_replace_unicode(215,"x", $value);
		$value = str_replace_unicode(96,"'", $value);
		$value = str_replace_unicode(180,"'", $value);

		if ( $remove_line_breaks )
		{
			$value = str_replace_unicode(10, " ", $value);
			$value = str_replace_unicode(13, " ", $value);
		}
		$value = str_replace("{{AMP}}", "&", $value);
		$value = str_replace("{{PERCENT}}", "%", $value);
		$value = str_replace("{{HASH}}", "#", $value);
		$value = str_replace("{{QUESTION}}", "?", $value);

		$value = str_replace('\t', ' ', $value);

		$value = str_replace("'", "''", $value); // THIS REPLACES "\\" WITH "\" (IN THE CASE OF CACHING BREADCRUMBS)
		//$value = addslashes($value); // THIS ADDS SLASHES TO DB
	
		if ( $remove_line_breaks )
			$value = trim(preg_replace('/\s\s+/',' ',$value));
		
		return $value;
	} */

	// ### slightly different for XML, which doesn't support some HTML entities. ###
	function special_entities_xml($value){

		// TRADE
		$value = str_replace_unicode(8482,"&#8482;",$value);
		$value = str_replace_unicode(153,"&#153;",$value);

		// COPY
		$value = str_replace_unicode(169,"&#169;",$value);

		// REG
		$value = str_replace_unicode(174,"&#174;",$value);

		// HTML TAGS
		//$value = str_replace('<',"&lt;",$value);
		//$value = str_replace('>',"&gt;",$value);

		// OTHER SYMBOLS

		$value = str_replace_unicode(189,"&#189;",$value); // 1/2
		$value = str_replace_unicode(189,"&#189;",$value); // 1/2
		$value = str_replace_unicode(188,"&#188;",$value); // 1/4
		$value = str_replace_unicode(188,"&#188;",$value); // 1/4
		$value = str_replace_unicode(190,"&#190;",$value); // 3/4
		return $value;
	}

	function xml_sanitize($value,$escape_html_tags=true){
		$value = replace_smart_characters($value);
		$value = unescape_special_characters($value);
		$value = special_entities_xml($value);
		if($escape_html_tags){
			$value = str_replace('<','&lt;',$value);
			$value = str_replace('>','&gt;',$value);
			$value = str_replace('"','&quot;',$value);
			$value = str_replace("\'",'&apos;',$value);
			$value = str_replace("& ",'&amp; ',$value);

			// ALLOW <em> TAGS
			$value = str_ireplace('&lt;em&gt;','<em>',$value);
			$value = str_ireplace('&lt;/em&gt;','</em>',$value);

		}
		return $value;
	}

	function get_table_data($table_name){
		if(!is_suspicious($table_name)){
			$sql = "SELECT * FROM $table_name";

			if($table_name=='list_height'||$table_name=='list_sun_exposure'||$table_name=='list_spread') $sql .= ' ORDER BY ordinal';

			$results = mysql_query($sql);
			$ret = array();
			while ($row = mysql_fetch_array($results, MYSQL_ASSOC)) {
				$ret[] = $row;
			}
		}
		return $ret;
	}

	function get_unique_plant_values($field_id,$query = '',$max_results =''){
		if(!is_suspicious($field_id,$query,$max_results)){
			if($query!=''){
				$where = "$field_id LIKE '$query%'";
			}else{
				$where = "$field_id IS NOT NULL AND $field_id <> ''";
			}
			$sql = "SELECT DISTINCT $field_id AS value FROM plants WHERE ".$where." ORDER BY $field_id ASC";
			if($max_results!='') $sql .= ' LIMIT '.$max_results;
			$results = mysql_query($sql);
			$ret = array();
			if(is_resource($results)){
				while ($row = mysql_fetch_array($results, MYSQL_ASSOC)) {
					$ret[] = $row;
				}
			}
		}
		return $ret;
	}

	function output_select_options($table_name){
		$list_records = get_table_data($table_name);

		for($i=0;$i<count($list_records);$i++){
			echo("<option value=\"".$list_records[$i]['id']."\" attribute_name=\"".html_sanitize($list_records[$i]['name'])."\" attribute_is_historical=\"".(isset($list_records[$i]['is_historical'])?$list_records[$i]['is_historical']:'')."\">".html_sanitize($list_records[$i]['name'])."</option>");

		}
	}
	function generate_attribute_id_csv($array_attributes){
		$ids = '';
		for($i=0;$i<count($array_attributes);$i++){
			if(isset($array_attributes[$i])) $ids .= ','.$array_attributes[$i]->id;
		}
		if($ids!='') $ids = substr($ids,1);
		return $ids;
	}
	function generate_attribute_name_csv($array_attributes){
		$names = '';
		for($i=0;$i<count($array_attributes);$i++){
			$names .= ', '.$array_attributes[$i]->name;
		}
		if($names!='') $names = substr($names,2);
		return $names;
	}
	function multiselect_to_attributes($field_data){
		$ret = array();
		for($i=0;$i<count($field_data);$i++){
			if(isset($field_data[$i])) $ret[] = new plant_attribute($field_data[$i],null,null,null);
		}
		return $ret;
	}
	function id_csv_to_attributes($ids){
		$array_ids = explode(',',$ids);
		$ret = array();
		for($i=0;$i<count($array_ids);$i++){
			if($array_ids[$i]!='') $ret[] = new plant_attribute($array_ids[$i],null,null,null);
		}
		return $ret;
	}

	function tinyint_to_string_boolean($value){
		return ($value=='1')?'true':'false';
	}
	function checkbox_to_tinyint($value){
		return ($value=='on')?'1':'0';
	}

	function parse_words($value){
		return preg_replace("/[^a-zA-Z ]+/","",$value);
	}
	function to_metaphone_string($value){
		$value = strtolower(parse_words($value));
		$value = str_replace(' x ',' ',$value);
		$value = str_replace(' hybrid ',' ',$value);
		$value = str_replace(' var ',' ',$value);

		$words = explode(' ',$value);

		$ret = '';
		for($i=0;$i<count($words);$i++){
			$word = trim(strtoupper($words[$i]));
			$part = metaphone($word);

			// IF METAPHONE TOO SHORT, USE ACTUAL WORD
			if(strlen($part)>3&&strlen($word)>3){
				$ret .= ' ' . $part;
			}else{
				$ret .= ' ' . trim(strtoupper($words[$i]));
			}
		}
		return trim($ret);

	//	return metaphone($value);
	}
	function sql_query($query){
		$GLOBALS['sql_queries'][] = $query;
		return mysql_query($query);
	}
	function current_mysql_date(){
		return date('Y-m-d H:i:s');
	}

///////////////////////////////

	function get_columns_names($table_name) {
		$result = mysql_query("SELECT GROUP_CONCAT(COLUMN_NAME) AS column_names FROM information_schema.columns WHERE TABLE_NAME='$table_name'");
		return explode(',',mysql_result($result,0,'column_names'));
    }

	function get_table_column_info($table_name){
		$ret = array();
		$result = mysql_query("SELECT COLUMN_NAME AS col_name, DATA_TYPE AS col_type, CHARACTER_MAXIMUM_LENGTH AS col_maxlength FROM information_schema.columns WHERE TABLE_NAME='$table_name'");
		$num_rows = mysql_num_rows($result);
		for($i=0;$i<$num_rows;$i++){
			$ret[mysql_result($result,$i,'col_name')] = new column_info(mysql_result($result,$i,'col_name'),mysql_result($result,$i,'col_type'),mysql_result($result,$i,'col_maxlength'));
		}
		return $ret;
	}

	class column_info {
		function column_info($name,$type,$max_length){
			$this->name = $name;
			$this->type = $type;
			$this->max_length = $max_length;
		}
	}

	function get_list_table_names() {
		$result = mysql_query("SELECT GROUP_CONCAT(DISTINCT TABLE_NAME) AS table_names FROM information_schema.columns WHERE TABLE_NAME like 'list_%'");
		return explode(',',mysql_result($result,0,'table_names'));
    }

///////////////////////////////////

	// ESTABLISH CONNECTION FOR PAGE
	//sql_connect();

?>
