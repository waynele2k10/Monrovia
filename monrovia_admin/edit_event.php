<? require_once($_SERVER['DOCUMENT_ROOT'].'/monrovia_admin/inc/header.php'); ?>
<? $monrovia_user->permission_requirement('cmgt'); ?>
<? $monrovia_user->permission_requirement('caln'); ?>
<? require_once($_SERVER['DOCUMENT_ROOT'].'/inc/class_monrovia_event.php'); ?>
<?
		$event = '';
        if (isset($_POST['action']) && $_POST['action']=='save') require('inc/event_save.php');

		if(isset($_GET['msg'])&&$_GET['msg']=='saved') output_page_notice('Your changes have been saved.');

		if($event!=''){
            // RETAIN SAVED EVENT
            $event_id = $event->info['id'];
        }else{
            $event_id = 1;
            if(isset($_GET['id'])) $event_id = $_GET['id'];
            $event = new monrovia_event($event_id);
            if((!isset($event->info['id'])||$event->info['id']=='')&&$_GET['id']!='new'){
                die('Event not found.');
            }else{
                if(isset($_GET['action'])&&$_GET['action']=='delete'){
                    $event->delete();
                    die('Event has been deleted successfully.<br /><br /><a href="./events.php">&laquo; Back to Calendar</a>');
                }
            }
        }
    
    	// REDIRECT TO NEW EVENT CREATED
        if($_GET['id']==''&&$event->info['id']!=''){
            header('location:edit_event.php?id='.$event->info['id'].'&msg=saved');
            exit;
        }
        
        if($_GET['id']=='new'){
	       	// NEW EVENT; SET SOME DEFAULT VALUES
			//$event->info['is_active'] = '1';
        }else{
        	// GET ASSOCIATED DATA (IMAGES, DATES/TIMES)
        	$event->load_associated_data();
        }
        
	?>
    <form id="form_event" action="?id=<?=(isset($event->info['id'])?$event->info['id']:'')?>" onsubmit="return page_validate();" onkeypress="return check_to_cancel_enter_key(window.event||event);" method="post">
	
	<script type="text/javascript" src="/fckeditor/fckeditor.js"></script>
	<style>
		#table_events {
			margin-top:8px;
			width:982px;
		}
		#table_events td {
			font-size:9pt;
		}
		#table_events, #event_dates {
			border-collapse:collapse;
		}
		#table_events td, #event_dates td {
			padding:4px 8px 4px 8px;
		}
		#event_editor {
			width:800px;
			border:2px outset #eee;
			background-color:#eee;
			padding:2px;
			display:none;
		}
		#event_editor_title_bar {
			font-weight:bold;
			font-size:9pt;
			margin-bottom:4px;
			background-color:#cfcfcf;
			padding:2px;
		}
		#page_content {
			position:absolute;
		}
		.field_label {
			float:right;
			whitespace:no-wrap;
		}
		#img_preview {
			max-width:200px;
			max-height:200px;
			#width:200px;
			position:absolute;
			display:none;
			margin-left:16px;
			border:2px outset #ccc;
		}
		.link_remove {
            text-decoration:underline;
            color:#00f;
            cursor:pointer;
        }
        .row_even td{
            background-color: #EEEEEE;
        }
        .half_left {
            width:250px;
            padding-right:20px;
            float:left;
            font-size: 9pt;
        }
        .half_left img {
            padding: 10px 30px 10px 0;
            float:left;
        }
        #image_segments {
            width:500px;
            float:left;
            margin-right:40px;
            font-size:12px;
        }
        /*
        #image_segments .field_group {
        	background-color:#fff;
        }
        */
        .image_segment {
            padding: 10px;
            border:1px solid #ddd;
            margin-bottom:20px;
            width:500px;
        }
        .image_segment  img {
            padding-right:10px;
            float:left;
        }
        .inactive {
            opacity: 0.5;
            #filter:alpha(opacity=50);
            #zoom:1;
        }
        #iframe_upload_thumbnail {
        	height:75px;
        }
        #event_thumbnail {
        	padding:0px 10px 0px 0px;
        }
	</style>


	<h2><?=(($_GET['id']=='new')?'Add':'Edit')?> Event</h2>

	<div style="margin-top:4px;">
        <input type="submit" value="Save Changes" />
        <input type="button" value="Cancel" onclick="window.location='./events.php';" />
        <? if($event_id!='new'){ ?>
            <input type="button" value="Delete Event" onclick="modal_show({'modal_id':'modal_delete_event'});" style="margin-left:600px;color:#a00;" />
        <? } ?>
    </div>
	<div class="slimTabContainer" id="ctlTabs">
        <div>
            <div class="slimTab spacer" style="width:4px">&nbsp;</div>
            <div class="slimTab selected" tab="1" title="Alt + Shift + 1">1. Basic Info</div>
            <div class="slimTab" tab="2" title="Alt + Shift + 2">2. Venue</div>
            <div class="slimTab" tab="3" title="Alt + Shift + 3">3. Dates and Times</div>
            <div class="slimTab" tab="4" title="Alt + Shift + 4">4. Images</div>
            
            <div class="slimTab spacer" style="width:32px;">&nbsp;</div>
        </div>
        
        <div class="slimTabBlurb sel" tab="1">
            <input type="checkbox" name="event[is_active]" <?=(isset($event->info['is_active'])&&$event->info['is_active']=='1')?'checked':''?> class="checkbox" id="event_is_active" /><label for="event_is_active" style="font-size: 9pt;vertical-align: middle;">Active</label>
    		<div class="field_group">
    		    <table width="100%">
        		    <tr>
                        <td class="field_label">Title: <span class="small">(Required)</span></td>
                         <td><input name="event[title]" class="text_field" value="<?=(isset($event->info['title'])?html_sanitize($event->info['title']):'')?>" maxlength="255" /></td>
                     </tr>
                     <tr>
                        <td class="field_label">Short Description:</td>
                         <td>
                         	<input name="event[short_description]" class="text_field" value="<?=(isset($event->info['short_description'])?html_sanitize($event->info['short_description']):'')?>" maxlength="255" />
                         	<div class="small">(The short description is the description of the event that appears on the <a href="/event-calendar/list.php" target="_blank">event listings page</a>.)</div>
                         </td>
                     </tr>
                     <tr>
                        <td class="field_label">Details:</td>
                         <td>
                             <script>
                                var oFCKeditor = new FCKeditor('event[details]');
                                oFCKeditor.BasePath = "/fckeditor/";
                                oFCKeditor.Config["CustomConfigurationsPath"] = "/fckeditor/custom_minimal.js";
                                oFCKeditor.Height = '250';
                                oFCKeditor.Value = "<?=(isset($event->info['details'])?str_replace('"','\\"',$event->info['details']):'')?>";
                                oFCKeditor.Create();
                                //FCKeditorAPI.GetInstance('richtext_editor').GetHTML();
                            </script>
                        </td>
                     </tr>
                     <tr>
                        <td class="field_label">Phone:</td>
                         <td><input name="event[phone]" class="text_field" value="<?=(isset($event->info['phone'])?html_sanitize($event->info['phone']):'')?>" maxlength="20" /></td>
                     </tr>
                 </table>
    		</div>
    		<div class="field_group">
                <table width="100%">
                     <tr>
                        <td class="field_label">Speakers: <br /><span class="small">(one per line)</span></td>
                        <td><textarea class="text_field full_width" name="event[speaker_names]" class="text_field" maxlength="255"><?=(isset($event->info['speaker_names'])?str_replace(':',PHP_EOL,$event->info['speaker_names']):'')?></textarea></td>
                     </tr>
                    <tr>
                        <td class="field_label">Website URL:</td>
                         <td><input name="event[website_url]" class="text_field" value="<?=(isset($event->info['website_url'])?html_sanitize($event->info['website_url']):'')?>" maxlength="255" /></td>
                     </tr>
                     <tr>
                        <td class="field_label">Additional Info:</td>
                         <td>
                         	<input name="event[additional_info]" class="text_field" value="<?=(isset($event->info['additional_info'])?html_sanitize($event->info['additional_info']):'')?>" maxlength="255" />
                         	<div class="small">(Example: "Please bring a photo ID")</div>
                         </td>
                     </tr>
                 </table>
            </div>
    	</div>
    	
    	<? include_once($_SERVER['DOCUMENT_ROOT'].'/inc/state_field_options.php'); ?>
    	<div class="slimTabBlurb" tab="2">
            <div class="field_group">
                <table width="100%">
                    <tr>
                        <td class="field_label">Name:</td>
                         <td colspan="3"><input name="event[venue_name]" class="text_field" value="<?=(isset($event->info['venue_name'])?html_sanitize($event->info['venue_name']):'')?>" maxlength="40" /></td>
                     </tr>
                     <tr>
                        <td class="field_label">Address:</td>
                         <td colspan="3"><input name="event[venue_address]" class="text_field" value="<?=(isset($event->info['venue_address'])?html_sanitize($event->info['venue_address']):'')?>" maxlength="255" /></td>
                     </tr>
                     <tr>
                        <td class="field_label">City:</td>
                         <td colspan="3"><input name="event[venue_city]" class="text_field" value="<?=(isset($event->info['venue_city'])?html_sanitize($event->info['venue_city']):'')?>" maxlength="40" /></td>
                     </tr>
                     <tr>
                        <td class="field_label">State:</td>
                         <td>
                             <select name="event[venue_state]">
                                 <option value=""></option>
                                 <?=output_state_select_options(isset($event->info['venue_state'])?$event->info['venue_state']:'');?>
                             </select>
                             
                         </td>
                         <td class="field_label">Zip:</td>
                         <td><input name="event[venue_zip]" value="<?=(isset($event->info['venue_zip'])?html_sanitize($event->info['venue_zip']):'')?>" maxlength="7" size="7" /></td>
                     </tr>
                     <tr>
                        <td class="field_label">Notes:</td>
                         <td colspan="3"><input name="event[venue_address_notes]" class="text_field" value="<?=(isset($event->info['venue_address_notes'])?html_sanitize($event->info['venue_address_notes']):'')?>" maxlength="40" style="width:350px;" />
                         <div class="small">(Example: "In the gray building")</div>
                         </td>
                     </tr>
                 </table>
            </div>            
        </div>
        
        <div class="slimTabBlurb" tab="3">
            <p style="font-size: 9pt;">Time Zone: &nbsp;&nbsp;
                <select name="event[timezone]">
                    <option value="PST" <?=(isset($event->info['timezone']) && $event->info['timezone'] ==='PST'?'selected':'')?>>PST</option>
                    <option value="EST" <?=(isset($event->info['timezone']) && $event->info['timezone'] ==='EST'?'selected':'')?>>EST</option>
                    <option value="MST" <?=(isset($event->info['timezone']) && $event->info['timezone'] ==='MST'?'selected':'')?>>MST</option>
                    <option value="CST" <?=(isset($event->info['timezone']) && $event->info['timezone'] ==='CST'?'selected':'')?>>CST</option>
                </select>
            </p>
            
            
            <!-- GROUP -->
            <div class="field_group" style="width:512px;">
                    <div class="dates_container">
                        <h3 style="margin:0px 0px 0px 6px;">Add Date and Times</h3>
                        <div id="add_date_form">
                        <table>
                            <tr>
                                <td class="field_label">Date:</td>
                                <td><input id="add_new_date_day" class="text_field" value="" maxlength="10" placeholder="yyyy-mm-dd" /></td>
                            </tr>
                            <tr>
                                <td class="field_label">Start Time:</td>
                                <td>
                                    <input id="add_new_date_start_hour" value="" maxlength="2" size="2" placeholder="hh" /> :
                                    <input id="add_new_date_start_minute" value="" maxlength="2" size="2" placeholder="mm" />
                                    <select id="add_new_date_start_am_pm">
                                        <option value="">--</option>
                                        <option value="AM">AM</option>
                                        <option value="PM">PM</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="field_label">End Time:</td>
                                <td>
                                    <input id="add_new_date_end_hour" value="" maxlength="2" size="2" placeholder="hh" /> :
                                    <input id="add_new_date_end_minute" value="" maxlength="2" size="2" placeholder="mm" />
                                    <select id="add_new_date_end_am_pm">
                                        <option value="">--</option>
                                        <option value="AM">AM</option>
                                        <option value="PM">PM</option>
                                    </select>
                                    
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td><input type="button" value="Add" onclick="add_event_date(this);" /></td>
                                
                            </tr>
                        </table>
                        </div>
                    </div>
            </div>
            <!-- /GROUP -->  
            
            <div class="field_group" style="width:512px;">
            <table id="event_dates" class="data_table" width="100%">
               <thead>
                   <tr style="background-color:#666;color:#fff;">
                       <td>Date</td>
                       <td>Start Time</td>
                       <td>End Time</td>
                       <td width="1">Remove</td>
                   </tr>
               </thead>
               <tbody>
                   
               </tbody>
               <script>
                   var current_event_image_id;
                   var event_id = '<?=(isset($event->info['id'])?$event->info['id']:'')?>';
                   var event_dates = <?echo(isset($event->output_event_dates_js)?$event->output_event_dates_js:'[]')?>;
                   
                   function td(innerHTML) {
                        var ret = document.createElement('td');
                        ret.innerHTML = (innerHTML||'');
                        return ret;
                    }
                   function refresh_table_data(){
                       var parent_element = $$('#event_dates tbody')[0];
                       parent_element.update('');
                       var ids = '';
                       for (var i=0;i<event_dates.length;i++){
                           var tr = new Element('tr');
                           if(i%2) tr.addClassName('row_even');
                           tr.appendChild(new td(event_dates[i].not_formatted_date));
                           tr.appendChild(new td(event_dates[i].start_time));
                           tr.appendChild(new td(event_dates[i].end_time));
                           var td_col_remove = new td('<span onclick="remove_event_date(this);" class="link_remove">Remove</span>');
                           tr.appendChild(td_col_remove);
                           parent_element.appendChild(tr);
                       }
                   }
                   
                   function remove_event_date(element){
                        var container = element.up('tr');
                        var date_to_remove = [];
                        var values = container.children;
                        for (var i=0;i<3;i++){
                            date_to_remove.push(values[i].innerHTML.stripTags());
                        }
                        
                        for (var i=event_dates.length;i--;){
                            if (event_dates[i].not_formatted_date == date_to_remove[0] && event_dates[i].start_time == date_to_remove[1] && event_dates[i].end_time == date_to_remove[2]) {
                                event_dates.splice(i,1);
                            }
                        }
                        
                        refresh_table_data('event_dates');
                    }
                    
                    Date.createTimestamp = function(string) { 
                       if(typeof string === 'string') {
                          var t = string.split(/[- :]/);
                            return new Date(t[0], t[1] - 1, t[2], t[3] || 0, t[4] || 0, t[5] || 0);          
                       }
                       return null;   
                    }

            
                    function add_event_date(element){
						var date_entered = $('add_new_date_day').value;

						// VALIDATE DATE ITSELF
						if(!is_valid_mysql_date(date_entered)){
							alert('Please enter a valid date in yyyy-mm-dd format.');
							return false;
						}

						var date_times = {
							'date':date_entered,
							'times':{
								'start':{
									'meridiem':$('add_new_date_start_am_pm').value,
									'h_12':window.parseFloat($('add_new_date_start_hour').value),
									'm':window.parseFloat($('add_new_date_start_minute').value)||0
								},
								'end':{
									'meridiem':$('add_new_date_end_am_pm').value,
									'h_12':window.parseFloat($('add_new_date_end_hour').value),
									'm':window.parseFloat($('add_new_date_end_minute').value)||0
								}
							}
						}
						
						// VALIDATE HOURS AND MINUTES AND MAKE SURE MERIDIEM IS SPECIFIED
						if(date_times.times.start.h_12<1||date_times.times.start.h_12>12||date_times.times.start.m<0||date_times.times.start.m>59||date_times.times.end.h_12<1||date_times.times.end.h_12>12||date_times.times.end.m<0||date_times.times.end.m>59||!date_times.times.start.meridiem||!date_times.times.end.meridiem){
							alert('Please enter a valid start and end time.');
							return false;
						}

						// CONVERT TO 24-HOUR SYSTEM
						date_times.times.start.h_24 = date_times.times.start.h_12;
						date_times.times.end.h_24 = date_times.times.end.h_12;
						if(date_times.times.start.meridiem=='AM'&&date_times.times.start.h_12==12) date_times.times.start.h_24 = 0;
						if(date_times.times.end.meridiem=='AM'&&date_times.times.end.h_12==12) date_times.times.end.h_24 = 0;
						if(date_times.times.start.meridiem=='PM'&&date_times.times.start.h_12!=12) date_times.times.start.h_24 += 12;
						if(date_times.times.end.meridiem=='PM'&&date_times.times.end.h_12!=12) date_times.times.end.h_24 += 12;

						// VALIDATE
						var date_segments = date_entered.split('-');
						date_times.times.start.date = new Date(date_segments[0],date_segments[1]-1,date_segments[2],date_times.times.start.h_24,date_times.times.start.m,0,0);
						date_times.times.end.date = new Date(date_segments[0],date_segments[1]-1,date_segments[2],date_times.times.end.h_24,date_times.times.end.m,0,0);

						if(isNaN(date_times.times.start.date)||isNaN(date_times.times.end.date)){
							alert('Please enter a valid start and end time.');
							return false;
						}
						if(date_times.times.start.h_24==date_times.times.end.h_24&&date_times.times.start.m==date_times.times.end.m){
							alert('The start and end times may not be the same.');
							return false;
						}

						// NORMALIZE
						date_times.date = date_times.times.start.date.getFullYear() + '-' + padDigits(date_times.times.start.date.getMonth()+1,2)+'-'+padDigits(date_times.times.start.date.getDate(),2);

						// SWAP START/END TIMES IF OUT OF ORDER
						if(date_times.times.start.date>date_times.times.end.date){
							var start_time = {
								'meridiem':date_times.times.end.meridiem,
								'h_12':date_times.times.end.h_12,
								'h_24':date_times.times.end.h_24,
								'm':date_times.times.end.m
							};
							var end_time = {
								'meridiem':date_times.times.start.meridiem,
								'h_12':date_times.times.start.h_12,
								'h_24':date_times.times.end.h_24,
								'm':date_times.times.start.m
							};

							date_times.times.start = start_time;
							date_times.times.end = end_time;

							date_times.times.start.date = new Date(date_segments[0],date_segments[1]-1,date_segments[2],date_times.times.start.h_24,date_times.times.start.m,0,0);
							date_times.times.end.date = new Date(date_segments[0],date_segments[1]-1,date_segments[2],date_times.times.end.h_24,date_times.times.end.m,0,0);
						}

						var event_date_time = {
							'not_formatted_date':date_times.date,
							'start_time':padDigits(date_times.times.start.h_12,2) + ':' + padDigits(date_times.times.start.m,2) + ' ' + date_times.times.start.meridiem,
							'end_time':padDigits(date_times.times.end.h_12,2) + ':' + padDigits(date_times.times.end.m,2) + ' ' + date_times.times.end.meridiem,
							'unix_epoch':date_times.times.start.date/1000
						}

                        if(!event_date_present(event_date_time)){
                			// ADD EVENT TIMES
							event_dates.push(event_date_time);
							//sort array by unix epoch time
							event_dates.sort(function(a,b) {return (a.unix_epoch > b.unix_epoch) ? 1 : ((b.unix_epoch > a.unix_epoch) ? -1 : 0);} );
							refresh_table_data($('event_dates'));
                         }
                         clear_fields();
                    }
                    
                    function validate_am_pm(time,rel,hour){
                       if (!time) {
                           //alert('Please choose AM or PM for the '+rel+' time'); 
                           return false;
                        }
                       
                        if (time =='PM') {
                            if (hour != 12){
                                hour+= 12;
                            } else {
                                hour = 12;
                            }
                        } else {
                            if (hour == 12){
                                hour = 0;
                            }
                        } 
                        return time;
                    }
                    
                    function validate_time(num,time,rel){
                        num = parseFloat(num);
                        if(isNaN(num) || num!=Math.ceil(num) || num!=Math.floor(num) || num<0 ) return false;
                        if (time == 'hour'){
                            if (num>12) return false;
                        } else {
                            if (num>59) {
                                return false;
                            } else if (num<10){
                                num = '0'+ num;
                            }
                        }
                        return num;
                    }
                    
                    function event_date_present(event_date_time){
                        for(var i=event_dates.length;i--;){
                            if(event_dates[i].not_formatted_date==event_date_time.not_formatted_date && event_dates[i].start_time == event_date_time.start_time && event_dates[i].end_time == event_date_time.end_time) {
                                return true;
                            } 
                        }
                    }
                    
                    function clear_fields(){
                        $('add_new_date_day').value = '';
                        $('add_new_date_start_hour').value = '';
                        $('add_new_date_start_minute').value = '';
                        $('add_new_date_end_hour').value = '';
                        $('add_new_date_end_minute').value = '';
					    get_field('add_new_date_start_am_pm').value = '';
					    get_field('add_new_date_end_am_pm').value = '';
                    }
                   
                   Event.observe(window,'load',function(){
                        refresh_table_data('event_dates');
                        
                        get_field('event[phone]').observe('blur',function(){
                        	var val = parse_numeric_characters(this.value);
                        	if(val.length>=10){
                        		this.value = '(' + val.substr(0,3) + ') ' + val.substr(3,3) + '-' + val.substr(6,4);
                        		if(val.length>10) this.value += ' x' + val.substr(10);
                        	}
                        });
                        
                    });
                    
                    function refresh_image_sets(){
                        new Ajax.Request('query_event_images.php', {
                          method: 'post',
                          parameters:'id=<?=(isset($event->info['id'])?$event->info['id']:'')?>',
                          onSuccess:function(transport){
                              $('image_segments').update(transport.responseText);
                          }
                        });
                    }
                    
                    function move_event_image(direction,image_id,ordinal){
                        new Ajax.Request('query_event_images.php', {
                          method: 'post',
                          parameters:'id=<?=(isset($event->info['id'])?$event->info['id']:'')?>&direction='+direction+'&image_id='+image_id+'&ordinal='+ordinal,
                          onSuccess:function(transport){
                              $('image_segments').update(transport.responseText);
                          }
                        });
                    }
                    
                    function upload_complete(){
                        if(!$('iframe_upload_image')) return;
                        var iframe_upload = $('iframe_upload_image').contentWindow;
                        var iframe_result = Element.down(iframe_upload.document.body,'iframe').contentWindow;
                        if(iframe_result.action=='upload'){
                            var success = (iframe_result.result=='1');
                            if(success){
                                Element.down(iframe_upload.document.body,'form').reset();
                                refresh_image_sets();
                            }else{
                                alert('An error occurred and your file was not uploaded.');
                            }
                            Element.down(iframe_upload.document.body,'div[id=upload_form_container]').style.visibility = 'visible';
                            iframe_result.location.href = 'about:blank';
                        }
                    }
                    
                    function thumbnail_upload_complete(){
                        if(!$('iframe_upload_thumbnail')) return;
                        var iframe_upload = $('iframe_upload_thumbnail').contentWindow;
                        var iframe_result = Element.down(iframe_upload.document.body,'iframe').contentWindow;
                        if(iframe_result.action=='upload'){
                            var success = (iframe_result.result=='1');
                            if(success){
                                Element.down(iframe_upload.document.body,'form').reset();
                                refresh_thumbnail();
                            }else{
                                alert('An error occurred and your file was not uploaded.');
                            }
                            Element.down(iframe_upload.document.body,'div[id=upload_form_container]').style.visibility = 'visible';
                            iframe_result.location.href = 'about:blank';
                        }
                    }
                    
                    function refresh_thumbnail(){
                          
                        new Ajax.Request('query_event_thumbnail.php', {
                          method: 'post',
                          parameters:'id=<?=(isset($event->info['id'])?$event->info['id']:'')?>',
                          onSuccess:function(transport){
                              $('event_thumbnail_container').update(transport.responseText);
                          }
                        });                   
                    }
                    
                    function delete_thumbnail(){
                        if(!confirm('Warning: Deleting a thumbnail is irreversible.\n\nDelete the thumbnail?')) return;
            
                        new Ajax.Request('delete_event_thumbnail.php', {
                          method: 'post',
                          parameters:'id='+<?=(isset($event->info['id'])?$event->info['id']:'0')?>+'&action=delete',
                          onSuccess:function(transport){
                              refresh_thumbnail();
                          }
                        });                       
                    }
                    
                    function edit_event_image_delete(id){
                        if(!confirm('Warning: Deleting an image is irreversible.\n\nDelete this image?')) return;
            
                        new Ajax.Request('update_event_image.php', {
                          method: 'post',
                          parameters:'id='+id+'&action=delete',
                          onSuccess:function(transport){
                              refresh_image_sets();
                          }
                        });
                    }
                     
                    function edit_event_image(id,is_active,title){
                        current_event_image_id = id;
                        $('edit_event_title').value = title;
                        if (is_active){
                            $('edit_event_is_active').checked = true;
                        } else {
                            $('edit_event_is_active').checked = false;
                        }
                        modal_show({'modal_id':'modal_edit_event','effect':'fade'});
                    } 
                    
                    function edit_event_image_update(){
                        var title = $('edit_event_title').value;
                        var is_active = ($('edit_event_is_active').checked)?'1':'0';
                        var id = current_event_image_id;
                        
            
                        if(!title){ alert('Please specify a title for this image.'); return false; }
                                    
                        modal_hide();
                        new Ajax.Request('update_event_image.php', {
                          method: 'post',
                          parameters:'id='+id+'&title='+title+'&is_active='+is_active,
                          onSuccess:refresh_image_sets
                        });
                    }
                    
                    function delete_event(){
                        window.location.href = '?id='+event_id+'&action=delete';
                    }
                                                                     
               </script>
            </table>
            </div>
            
        </div>
        
        <div class="slimTabBlurb" tab="4">
            <? if(!isset($event->info['id'])||$event->info['id']==''){ ?>
                <h4>You will be able to add images after saving.</h4>
            <? }else{ ?>
                <h5>Note: Image updates, additions, and deletions are published immediately.</h5>
                <div>
                	<h3 style="margin-bottom:8px;">Thumbnail</h3>
                	<div class="small" style="padding-bottom:2em;">(The thumbnail appears only on the <a href="/event-calendar/list.php" target="_blank">event listings page</a>.)</div>
					<div id="event_thumbnail_container" style="float:left;">
						<? $event->output_thumbnail_html();?>
					</div>
					<div class="half_left">
						<iframe src="upload_event_thumbnail.php?event_id=<?=(isset($event->info['id'])?$event->info['id']:'')?>" id="iframe_upload_thumbnail" frameborder="0"></iframe>
					</div>
					<div style="clear:both;"></div>
				</div>
                <div style="padding-top:16px;">
		            <h3 style="margin-bottom:8px;">Poster Images</h3>
					<div id="image_segments">
						<? $event->output_cms_image_segments_html();?>
					</div>
					<div class="module_right" style="width:255px;height:175px;float:left;border:1px solid #ddd;">
						<div style="padding:4px 8px;height:100%;width:100%;">
							<iframe src="upload_event_image.php?event_id=<?=(isset($event->info['id'])?$event->info['id']:'')?>" id="iframe_upload_image" frameborder="0" height="200" width="240"></iframe>
						</div>
					</div>
					<div style="clear:both;"></div>
				</div>
            <? } ?>
        </div>
        
        <input type="hidden" name="event[event_dates]" value='' />
        <input type="hidden" name="id" value="<?=(isset($event->info['id'])?$event->info['id']:'')?>" />
        <input type="hidden" name="action" value="save" />
    </div>
    
    </form>
    <?
    function output_modals(){
    ?>
        <div class="modal_dialog" id="modal_edit_event">
            <table class="modal_dialog_backing">
                <tr>
                    <td class="corner corner_topleft"></td><td class="corner corner_top"><td class="corner corner_topright"></td>
                </tr>
                <tr>
                    <td class="corner corner_left"></td>
                    <td class="corner corner_middle" style="width:210px;">
                        <h5 style="margin:0px 0px 8px 0px;">Edit Image</h5>
                        <div class="field_label" style="float:none;text-align:left;">Title:</div>
                        <input class="text_field" id="edit_event_title" maxlength="40" />
                        <div style="margin-top:6px;">
                            <div class="checkbox_group">
                                <div style="float:left;"><input type="checkbox" id="edit_event_is_active" /><label for="edit_event_is_active" class="field_label">Active</label></div>
                                
                            </div>
                            
                            <div style="display:block;height:25px;"></div>
                            <input type="button" value="OK" onclick="edit_event_image_update();" />
                            <input type="button" value="Cancel" onclick="modal_hide();" />
                        </div>
                    </td>
                    <td class="corner corner_right"></td>
                </tr>
                <tr>
                    <td class="corner corner_bottomleft"></td><td class="corner corner_bottom"><td class="corner corner_bottomright"><td>
                </tr>
            </table>
        </div>
        
        <div class="modal_dialog" id="modal_delete_event">
            <table class="modal_dialog_backing">
                <tr>
                    <td class="corner corner_topleft"></td><td class="corner corner_top"><td class="corner corner_topright"></td>
                </tr>
                <tr>
                    <td class="corner corner_left"></td>
                    <td class="corner corner_middle" style="width:210px;">
                        <h5 style="margin:0px 0px 8px 0px;">Delete Event</h5>
                        <div style="font-size:9pt;width:500px;">Are you sure you want to permanently delete this event and all related information from the database?<br /><br />You may alternatively deactivate this event, which will prevent it from appearing on the website.<br /><br /><b>Click OK to proceed and permanently delete this event.</b></div>
                        <div style="margin-top:6px;">
                            <div style="display:block;height:8px;"></div>
                            <input type="button" value="Cancel" onclick="modal_hide();" style="float:right;" />
                            <input type="button" value="OK" onclick="delete_event();" style="color:#900;" />
                            <div style="clear:both;"></div>
                        </div>
                    </td>
                    <td class="corner corner_right"></td>
                </tr>
                <tr>
                    <td class="corner corner_bottomleft"></td><td class="corner corner_bottom"><td class="corner corner_bottomright"><td>
                </tr>
            </table>
        </div>
    <?  } ?>
    
	<script>
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
        function check_to_cancel_enter_key(evt){
            var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
            if(evt.keyCode == 13 && node.nodeName != "TEXTAREA"){
                return false;
            } 
        }
        
        function page_validate(){
            get_field('event[is_active]').value = ($('event_is_active').checked)?'1':'0';
            //get_dates
            get_field('event[event_dates]').value = JSON.stringify(event_dates);
            get_field('event[speaker_names]').value = (get_field('event[speaker_names]').value).replace(/\n/g, ':');          
            
			if(!(get_field('event[title]').value)){
				slimTab_showTab('ctlTabs',1);
				alert('Please provide an title for this event.');
				return false;
			}
			
			var website_url = get_field('event[website_url]').value;
			if(website_url){
				var expr = /http:\/\/[A-Za-z0-9\.-]{3,}\.[A-Za-z]{3}/;
				if(!expr.match(website_url)){
					slimTab_showTab('ctlTabs',1);
					alert('Please provide a valid website URL beginning with "http://".');
					return false;
				}
			}
			
			if(get_field('event[event_dates]').value=='[]'){
				slimTab_showTab('ctlTabs',3);
				alert('Please provide at least one date for this event.');
				return false;
			}
            
        }
	</script>
<? include('inc/footer.php'); ?>