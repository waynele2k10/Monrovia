<?php
	require_once('class_sql.php');
	class record {
		var $table_name;
		var $info;

		function load($record_id){
			// SQL INJECTION-SAFE
			if(is_numeric($record_id)){
				$result =mysql_query("SELECT * FROM $this->table_name WHERE id=$record_id");
				if(mysql_num_rows($result)==1){
					$row = mysql_fetch_assoc($result);
					$col_names = array_keys($row);
					for($i=0;$i<count($col_names);$i++){
						$this->info[$col_names[$i]] = unescape_special_characters(trim($row[$col_names[$i]]));
					}
				}else{
					$this->info['id'] = '';
				}
			}
		}

		function save($params = null){
		
			if(is_null($params)) $params = array();
			
			// OPTIONAL ARRAY CONTAINING FIELD NAMES TO ALLOW LINE BREAKS FOR
			if(!isset($params['allow_line_breaks'])) $params['allow_line_breaks'] = array();
		
			$col_names = explode(',',$this->table_fields);
			$values = '';
			for($i=0;$i<count($col_names);$i++){
				if(isset($this->info['id'])&&$this->info['id']!=''){
					// EXISTING RECORD
					$col_name = $col_names[$i];
					
					$allow_line_breaks = in_array($col_name,$params['allow_line_breaks']);
					
					$values .= ",$col_name='".(isset($this->info[$col_names[$i]])?sql_sanitize($this->info[$col_names[$i]],!$allow_line_breaks):'')."'";
				}else{
					// NEW RECORD
					
					$allow_line_breaks = in_array($col_names[$i],$params['allow_line_breaks']);
					$values .= ',\'' . sql_sanitize(isset($this->info[$col_names[$i]])?$this->info[$col_names[$i]]:'',!$allow_line_breaks) . '\'';
				}
			}
			$values = substr($values,1);
			//if(!is_suspicious(array($this->table_name,$this->table_fields))){
				if(isset($this->info['id'])&&$this->info['id']!=''){
					if(is_numeric($this->info['id'])){
						$ret =mysql_query("UPDATE $this->table_name SET $values WHERE id=".$this->info['id']);
					}
				}else{
					$ret =mysql_query("INSERT INTO $this->table_name ($this->table_fields) VALUES($values)");
					$this->info['id'] = mysql_insert_id();
				}
			//}
			return $ret;
		}

		function delete(){
			if(is_numeric($this->info['id'])){
				return mysql_query("DELETE FROM $this->table_name WHERE id=".$this->info['id']);
			}
		}
	}
?>