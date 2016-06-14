<?php
	require_once('class_record.php');
    require_once('class_monrovia_event_image.php');
	class monrovia_event extends record {
		// THIS IS SIMPLY A LIST OF ALL COLUMNS FROM monrovia_events TABLE, EXCEPT FOR id
		var $table_fields = 'is_active,title,has_thumbnail,timezone,short_description,details,venue_name,venue_address,venue_city,venue_state,venue_zip,venue_address_notes,website_url,phone,additional_info,speaker_names';

		function monrovia_event($record_id = ''){
			$this->table_name = 'monrovia_events';
			if($record_id!='') $this->load($record_id);

			$this->info['thumbnail_path'] = '/img/event_default_thumbnail.gif';

			if (isset($this->info['has_thumbnail'])&&$this->info['has_thumbnail'] == '1'){
				$this->info['thumbnail_path'] = '/img/events/' . $this->info['id'] . '/thumbnail.jpg';
				$this->info['server_thumbnail_path'] = $GLOBALS['server_info']['physical_root'] . 'img/events/'.$this->info['id'].'/thumbnail.jpg';
			}
		}

        function load_associated_data(){
			$this->get_event_dates();
			$this->get_event_images();
			$this->output_event_dates_js();
			//$this->image_sets = array();
        }

        function output_event_dates_js(){
            if(isset($this->info['id'])){
				$result = sql_query("SELECT * FROM monrovia_event_dates WHERE monrovia_event_id='".$this->info['id']."' ORDER BY date,start_time ASC");
				$num_rows = intval(@mysql_numrows($result));
				$dates = null;
				for($i=0;$i<$num_rows;$i++){
					$dates[$i] = mysql_fetch_object($result);
					$dates[$i]->not_formatted_date = $dates[$i]->date;
					$dates[$i]->date = date('F j, Y', strtotime($dates[$i]->date));
					$dates[$i]->start_time = date('h:i A', strtotime($dates[$i]->start_time));
					$dates[$i]->end_time = date('h:i A', strtotime($dates[$i]->end_time));
					$dates[$i]->unix_epoch = date('U', mktime(date('G',strtotime($dates[$i]->start_time)), date('i', strtotime($dates[$i]->start_time)),0,date('n', strtotime($dates[$i]->date)), date('j', strtotime($dates[$i]->date)), date('Y', strtotime($dates[$i]->date))));
				//$this->output_event_dates_js = substr($ret,1);
					$this->output_event_dates_js =json_encode($dates);
				}
            }
        }

    	function is_publicly_visible(){
    		// RECORD MUST NOT BE A COMPLETELY NEW (UNSAVED) RECORD
    		if(!isset($this->info['id'])||$this->info['id']==''||intval($this->info['id'])==0) return false;

    		// RECORD MUST BE ACTIVE
    		if(!isset($this->info['is_active'])||$this->info['is_active']!='1') return false;

    		// LAST DATE MUST BE NO OLDER THAN 6 MONTHS
    		if(!isset($this->info['event_dates'])||count($this->info['event_dates'])==0) $this->get_event_dates();
    		$date_keys = array_keys($this->info['event_dates']);
    		$last_date = strtotime($date_keys[count($date_keys)-1]);
    		$six_months_ago = strtotime('-7 month',time());
			return $last_date >= $six_months_ago;
    	}

		function get_event_dates(){
			$this->info['event_dates'] = array();
			$this->info['event_dates_friendly'] = array();

			$result = sql_query('SELECT * FROM monrovia_event_dates WHERE monrovia_event_id = "'.$this->info['id'].'" ORDER BY date ASC, start_time ASC');
			if (@mysql_numrows($result)){
				 while ($event = mysql_fetch_object($result)) {
					//populate times
					if (!isset($this->info['event_dates'][$event->date])){
						$this->info['event_dates'][$event->date] = array();
						$this->info['event_dates'][$event->date]['times'] = array();
					}

					$this->info['event_dates'][$event->date]['times'][] = array($event->start_time,$event->end_time);
					$this->info['event_dates'][$event->date]['timezone'] = $this->info['timezone'];
					$this->info['event_dates'][$event->date]['times_friendly'] = $this->generate_friendly_times($this->info['event_dates'][$event->date]['times']).' '.$this->info['timezone'];
				}
			}
			$this->generate_friendly_dates();
		}

		function generate_friendly_dates(){
			// STEP 1: CREATE GROUPS ARRAY
			$event_date_groups = array();
			$last_date = null;
			$last_date_times_friendly = null;

			// STEP 2: ITERATE EVENT DATES...
			foreach($this->info['event_dates'] as $event_date=>$time_info){
				// STEP 3: IF INFO IS THE SAME FOR CONSECUTIVE DATES, ADD TO CURRENT GROUP
				// IF THIS DATE IS NOT THE SAME AS THE LAST DATE OR THE DAY AFTER THE LAST DATE, OR IF THE TIMES DON'T MATCH, START A NEW GROUP
				$time_mismatch = $last_date_times_friendly!=$time_info['times_friendly'];
				$date_mismatch = $last_date!=$event_date&&strtotime($last_date)!=strtotime('-1 day',strtotime($event_date));
				if($date_mismatch||$time_mismatch){
					if(isset($event_date_group)) $event_date_groups[] = $event_date_group;
					$event_date_group = array();
				}
				$last_date_times_friendly = $time_info['times_friendly'];
				$last_date = $event_date;
				$event_date_group[$event_date] = $time_info;
			}
			if(isset($event_date_group)&&count($event_date_group)) $event_date_groups[] = $event_date_group;

			// STEP 4: ITERATE GROUPS
			$this->info['event_dates_friendly'] = array();
			for($i=0;$i<count($event_date_groups);$i++){

				$event_dates = array_keys($event_date_groups[$i]);

				// STEP 5: CREATE FRIENDLY TIMES
				if(count($event_date_groups[$i])==1){
					$this->info['event_dates_friendly'][] = array(
						'dates'=>date('l, F j, Y',strtotime($event_dates[0])),
						'times'=>$event_date_groups[$i][$event_dates[0]]['times'],
						'timezone'=>$event_date_groups[$i][$event_dates[0]]['timezone'],
						'times_friendly'=>$event_date_groups[$i][$event_dates[0]]['times_friendly']
					);
				}else{
					$separator = (count($event_date_groups[$i])==2)?' & ':' - ';

					$this->info['event_dates_friendly'][] = array(
						'dates'=>date('l, F j, Y',strtotime($event_dates[0])) . $separator . date('l, F j, Y',strtotime($event_dates[count($event_dates)-1])),
						'times'=>$event_date_groups[$i][$event_dates[0]]['times'],
						'timezone'=>$event_date_groups[$i][$event_dates[0]]['timezone'],
						'times_friendly'=>$event_date_groups[$i][$event_dates[0]]['times_friendly']
					);
				}
			}
		}

		function generate_friendly_times($times){
			// LOOP THROUGH EACH time AND SET $this->times_friendly TO SOMETHING LIKE "10:30 AM - 2:30 PM PST"
			$count = 0;
			$times_friendly = null;
			foreach ($times as $time){
				foreach ($time as $t){
					if ($count%2) {
						$times_friendly .= ' - ';
					} else {
						if ($count) {
							$times_friendly .= ', ';
						}
					}
					$times_friendly .= date("g:i A", strtotime($t));
					$count++;
				}
			}

			return $times_friendly;
		}

		function get_event_images(){
			$this->info['event_images'] = array();
			$result = sql_query('SELECT * FROM monrovia_event_images WHERE monrovia_event_id="'.$this->info['id'].'" AND is_active="1" ORDER BY ordinal ASC');
			if (@mysql_numrows($result)){
				while ($image = mysql_fetch_object($result)) {
					 $new_image = new monrovia_event_image();
					 $new_image-> event_image_set($image->id);
					 $this->info['event_images'][] = $new_image;
				}
			}
			// THIS IS PROBABLY REDUNDANT
			if ($this->info['has_thumbnail'] === '1'){
				$this->info['thumbnail_path'] = '/img/events/' . $this->info['id'] . '/thumbnail.jpg';
				$this->info['server_thumbnail_path'] = $GLOBALS['server_info']['physical_root'] . 'img/events/'.$this->info['id'].'/thumbnail.jpg';
			}

		}

        function output_thumbnail_html(){
             if ($this->info['has_thumbnail'] == 1) {?>
                    <div class="half_left" style="border-right:1px solid #ddd;margin-right:12px;">
                    	<div>
							<img id="event_thumbnail" src="<?echo $this->info['thumbnail_path']?>?<? echo  rand();?>" />
							<button onclick="delete_thumbnail();return false;" style="margin-top:20px;float:left;">Delete thumbnail</button>
							<div style="clear:both;"></div>
						</div>
                    </div>
                    <div style="clear:both;"></div>
                <? }
        }

        function delete_thumbnail(){
            unlink($this->info['server_thumbnail_path']);
            sql_query("UPDATE monrovia_events SET has_thumbnail='0' WHERE id = ".$this->info['id']);

        }

        function output_cms_image_segments_html(){
            $this->get_all_event_images();?>
            <? if(!count($this->info['event_images'])){
                ?>
                    <div class="image_segment">
                        <div style="padding:80px 0px 80px 4px;text-align:center;font-size:12pt;font-weight:bold;">No images have been added.</div>
                    </div>
                <?
            }else{
                foreach ($this->info['event_images'] as $event_image){ ?>
                            <div class="image_segment <?= $event_image->info["is_active"] == '0'? 'inactive':''?>">
                                <img src="<?echo $event_image->info["path_thumbnail"]?>" />
                                <div style="float:left;width:416px;">
                                    <div style="background-color:#ddd;font-size:11px;padding:5px;">
                                        <div  style="float:left;">
                                            <a href="javascript:edit_event_image(<?=$event_image->info['id']?>,<?= $event_image->info["is_active"]?>,'<?=js_sanitize($event_image->info["title"])?>');void(0);">[edit]</a>
                                            <a href="javascript:edit_event_image_delete(<?=$event_image->info['id']?>);void(0);">[delete]</a>
                                        </div>
                                        <? if(count($this->info['event_images'])>1){ ?>
											<div style="float:right">
												<a href="javascript:move_event_image('up',<?=$event_image->info['id']?>,<?=$event_image->info['ordinal']?>);void(0);">[move up]</a>
												<a href="javascript:move_event_image('down',<?=$event_image->info['id']?>,<?=$event_image->info['ordinal']?>);void(0);">[move down]</a>
											</div>
										<? } ?>
                                        <div>&nbsp;</div>
                                    </div>
                                    <?= $event_image->info["is_active"] == '0'? '<p><strong>Inactive</strong></p>':''?>
                                    <p style="padding-top:<?= $event_image->info["is_active"] == '0'? '0':'15'?>px;"><?= $event_image->info["title"]?></p>
                                    <p><a class="lightview" href="#" onclick="lightview_show_image('<?= $event_image->info["path_full"]?>','<?=js_sanitize($event_image->info["title"])?>');">view enlarged size</a></p>
                                </div>
                                <div style="clear:both;"></div>
                            </div>
                  <?}
            }
        }

        function get_all_event_images($include_inactive = true){
            $this->info['event_images'] = array();
            $result = sql_query('SELECT * FROM monrovia_event_images WHERE monrovia_event_id="'.$this->info['id'].'" ORDER BY ordinal ASC');
            if (@mysql_numrows($result)){
                while ($image = mysql_fetch_object($result)) {
                     $new_image = new monrovia_event_image();
                     $new_image-> event_image_set($image->id);
                     $this->info['event_images'][] = $new_image;
                }
            }

            if ($this->info['has_thumbnail'] === '1'){
                $this->info['thumbnail_path'] = '/img/events/' . $this->info['id'] . '/thumbnail.jpg';
            }
        }


        function delete(){
            // DELETE IMAGES AND ENCLOSING FOLDER
            try {
                $base_path = $GLOBALS['server_info']['physical_root'] . 'img/events/' .$this->info['id'];

                // DELETE FOLDER
                if(is_dir($base_path)){
                    if($directory_handle = opendir($base_path)){
                        while(($file_name = readdir($directory_handle))!==false){
                            if($file_name!=''&&$file_name!='.'&&$file_name!='..') {
                                 recursive_delete("$base_path/$file_name");
                             }
                        }
                        closedir($directory_handle);
                    }
                    @rmdir($base_path);
                }

            }catch(Exception $err){
                //echo('error: delete');
                //return false;
            }

            // DELETE ASSOCIATED DATA
            $associated_tables = array('monrovia_event_dates','monrovia_event_images');

            for($i=0;$i<count($associated_tables);$i++){
                sql_query('DELETE FROM '.$associated_tables[$i].' WHERE monrovia_event_id=' . $this->info['id']);
            }

            // DELETE EVENT ITSELF
            parent::delete();
        }

		function clear_cache(){
			try {
				require_once('class_cache.php');

				// CLEAR CACHE FILE, IF EXISTS
				$cache_name = 'home :: calendar :: event ' . $this->info['id'];
				$cache = new cache($cache_name);
				if($cache->exists) $cache->remove();

				return true;
			}catch(Exception $err){
				return false;
			}
		}

        function save(){

        	// SANITIZE
			$this->info['title'] = strip_tags($this->info['title']);
			$this->info['short_description'] = strip_tags($this->info['short_description']);
			$this->info['details'] = wysiwyg_strip_tags($this->info['details']);
 			$this->info['venue_name'] = strip_tags($this->info['venue_name']);
 			$this->info['venue_address'] = strip_tags($this->info['venue_address']);
 			$this->info['venue_city'] = strip_tags($this->info['venue_city']);
 			$this->info['venue_address_notes'] = strip_tags($this->info['venue_address_notes']);
 			$this->info['additional_info'] = strip_tags($this->info['additional_info']);
 			$this->info['speaker_names'] = trim(strip_tags($this->info['speaker_names']),':');

            $success = parent::save();

            if(is_numeric($this->info['id'])){
                try {
                    // UPDATE EVENT DATES
                    sql_query("DELETE FROM monrovia_event_dates WHERE monrovia_event_id = ".$this->info['id']);
                    for($i=0;$i<count($this->info['event_dates']);$i++){

                        $db_date = date('Y-m-d',$this->info['event_dates'][$i]->unix_epoch);
                        $db_start_time = date('H:i:00',strtotime($this->info['event_dates'][$i]->start_time));
                        $db_end_time = date('H:i:00',strtotime($this->info['event_dates'][$i]->end_time));

                        sql_query("INSERT INTO monrovia_event_dates (monrovia_event_id,date,start_time,end_time) VALUES (".$this->info['id'].",'".$db_date."','".$db_start_time."','". $db_end_time. "')");

                    }

                    //$this->clear_cache();

                }catch(Exception $err){
                    $success = false;
                }

                $this->clear_cache();

            }else{
                $success = false;
            }
            return $success;
        }
	}
?>