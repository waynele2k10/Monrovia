<?php
	require_once('class_record.php');

	class editable_module extends record {
		// THIS IS SIMPLY A LIST OF ALL COLUMNS FROM editable_modules TABLE, EXCEPT FOR id
		var $table_fields = 'name,last_modified,locked_by,content,content_search';

		function editable_module($record_id = ''){
			$this->table_name = 'editable_modules';
			if($record_id!='') $this->load($record_id);
		}

		function load($record_id){
			parent::load($record_id);

/*
			$this->info['content'] = str_replace('{{AMP}}','&',$this->info['content']);
			$this->info['content'] = str_replace('{{PERCENT}}','%',$this->info['content']);
			$this->info['content'] = str_replace('{{HASH}}','#',$this->info['content']);
			$this->info['content'] = str_replace('{{QUESTION}}','?',$this->info['content']);

			$this->info['content_search'] = str_replace('{{AMP}}','&',$this->info['content_search']);
			$this->info['content_search'] = str_replace('{{PERCENT}}','%',$this->info['content_search']);
			$this->info['content_search'] = str_replace('{{HASH}}','#',$this->info['content_search']);
			$this->info['content_search'] = str_replace('{{QUESTION}}','?',$this->info['content_search']);
*/
			//$this->info['content'] = htmlspecialchars_decode($this->info['content']);
		}
		function render(){
			if($GLOBALS['render_editable']){
				$content = html_sanitize($this->info['content']);
				echo "<div class=\"editable_module\" module_id=\"".$this->info['id']."\" module_name=\"".$this->info['name']."\" _module_locked_by=\"".$this->info['locked_by']."\"><div><input type=\"button\" value=\"Edit\" class=\"btn_edit\" /><input type=\"button\" value=\"Publish Changes Now\" class=\"btn_publish\" /><span class=\"last_modified print_hide\">Last modified on ".$this->info['last_modified']."</span></div><div class=\"module_content\" html_raw=\"$content\">".$this->info['content']."</div></div>";
			}else{
				echo $this->info['content'];
			}
			$GLOBALS['page_module_ids'][] = $this->info['id'];
		}
	}
	function insert_editable_module($name){
		$cache = new cache(strtolower($name));
		if(!$cache->exists){
			$cache->start();

			////////////
			// SQL INJECTION-SAFE
			//if(is_suspicious(ids_sanitize($name))) return;

			// $name CAN ALSO BE AN INTEGER ID
			$fields = 'id,content';

			if($GLOBALS['render_editable']) $fields .= ',id,name,locked_by,last_modified';
			if(is_numeric($name)){
				$result = sql_query("SELECT $fields FROM editable_modules WHERE id='".sql_sanitize($name)."' LIMIT 1");
			}else{
				$result = sql_query("SELECT $fields FROM editable_modules WHERE name='".sql_sanitize($name)."' LIMIT 1");
			}

			$content = @mysql_result($result,0,"content");
			$id = @mysql_result($result,0,"id");
			if($GLOBALS['render_editable']){
				$name = @mysql_result($result,0,"name");
				$locked_by = @mysql_result($result,0,"locked_by");
				$last_modified = @mysql_result($result,0,"last_modified");
			}

			// POPULATE editable_module OBJECT, THEN RENDERING
			$module = new editable_module();
			if(isset($content)) $module->info['content'] = $content;
			if(isset($id)) $module->info['id'] = $id;
			if(isset($name)) $module->info['name'] = $name;
			if(isset($locked_by)) $module->info['locked_by'] = $locked_by;
			if(isset($last_modified)) $module->info['last_modified'] = $last_modified;
			$module->render();
			//////////
			?>
				<!-- Cached <?=date('Y-m-d H:i:s');?> -->
			<?
			if($id!=''){
				if($GLOBALS['render_editable']){
					// IF IN EDIT MODE, DON'T SAVE
					$cache->stop();
				}else{
					$cache->complete();
				}
			}

		}else{
			echo $cache->buffer;
		}
	}
?>