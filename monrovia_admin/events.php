<? require_once($_SERVER['DOCUMENT_ROOT'].'/monrovia_admin/inc/header.php'); ?>
<? $monrovia_user->permission_requirement('cmgt'); ?>
<? $monrovia_user->permission_requirement('caln'); ?>
<? require_once($_SERVER['DOCUMENT_ROOT'].'/inc/class_monrovia_event.php'); ?>
	<style>
		#table_events {
			margin-top:8px;
			width:982px;
		}
		#table_events td {
			font-size:9pt;
		}
		#table_events {
			border-collapse:collapse;
		}
		#table_events td {
			padding:4px 8px 4px 8px;
		}
		
		#page_content {
			position:absolute;
		}
		
		#table_events_contents tr td {
		    display:none;
		}
		
		#table_events_contents tr.visible td {
            display:table-cell;
        }
		
	</style>
    <? 
    
    $events = array();
    /*
    if (isset($_GET['year']) && $_GET['year']!='' && isset($_GET['month']) && $_GET['month']!=''){
        //show the events for that month/year 
        $current_month = $_GET['month']; 
        $event_year= $_GET['year'];
        $event_month = date("m", strtotime($current_month));
        
    } else {
        //show the events for this month
        $event_month = date('m');
        $event_year = date('Y');
    }
    $query ="SELECT DISTINCT monrovia_event_id FROM monrovia_event_dates WHERE YEAR(date) ='".$event_year."' AND MONTH(date) ='".$event_month."' ORDER BY date, start_time ASC";
    */
    
  /*  select monrovia_events.id FROM monrovia_events LEFT JOIN monrovia_event_dates ON monrovia_event_dates.monrovia_event_id = monrovia_events.id ORDER BY date, start_time ASC 
  
  hello
  */
    
    $query ="SELECT DISTINCT monrovia_event_id FROM monrovia_event_dates ORDER BY date, start_time ASC";
    if ($result = sql_query($query)){
        while ($event_id = mysql_fetch_object($result)) {
            
            //populate event objects with ids
            $temp = new monrovia_event($event_id->monrovia_event_id);
            $temp->load_associated_data();
            
            //loop through events dates (in case it spans for more than one month)
            foreach($temp->info["event_dates"] as $key => $value){
               $k = explode('-',$key); 
               $month = $k[1];
               $year = $k[0];
                    
               if(!isset( $events[$year][$month])) {
                   $events[$year][$month] = array();
               }
                    
               if (!in_array($temp, $events[$year][$month])){
                   $events[$year][$month][] = $temp;
               }
           }
        }
    }
    ?>
	

	<h2>Calendar</h2>

	<div id="page_content">
		<div style="margin-top:4px;">
		
		<? if(count($events)==0){ ?>
			There are no events to show.
			<br /><br />
			<input type="button" value="Add A New Event" onClick="window.location.href = 'edit_event.php?id=new';" />
			
		<? }else{ ?>

			<select id="event_month_selector" style="width:200px;">
				<option value="all">All Events</option>
				<? foreach ($events as $event_year => $event_month){
					 foreach ($event_month as $key => $value) { //loop through each month?>
					  <option value="<?=$event_year?>_<?=$key?>" <?=$event_year == date('Y') && $key == date('m') ? 'selected':''?>><?= date('F Y', mktime(0, 0, 0, $key, 10, $event_year)) ?></option> 
				<?   }
				   } 
				?>
			</select>
			<hr />

		
			<input type="button" value="Add A New Event" onClick="window.location.href = 'edit_event.php?id=new';" />
		
		      <table id="table_events">
        			<thead>
        				<tr style="background-color:#666;color:#fff;">
        					<td width="50">Status</td>
        				    <td width="75">Start&nbsp;Date</td>
        				    <td width="75">End&nbsp;Date</td>
        					<td>Title</td>
        					<td width="250">City</td>
        					<td width="30">State</td>
        					<td width="30">View</td>
        				</tr>
        			</thead>
        			<tbody id="table_events_contents">
        			   <?
        			     foreach ($events as $event_year => $event_month){
                            foreach ($event_month as $key => $value) {
                              foreach ($value as $event){ //loop through individual month events
                                 $event_date_keys = array_keys($event->info["event_dates"]);?>
                                 <tr class="<?=$event_year?>_<?=$key?>">
                                    <td><?=$event->info["is_active"] == '1'?'Active':'Inactive'?></td>
                                    <td><?=$event_date_keys[0] ?></td>
                                    <td><?=$event_date_keys[count($event_date_keys)-1]?></td>
                                    <td><a href="edit_event.php?id=<?=$event->info["id"]?>"><?=$event->info["title"]?></a></td>
                                    <td><?=$event->info["venue_city"]?></td>
                                    <td><?=$event->info["venue_state"]?></td>
                                    <td><a href="/event-calendar/detail.php?event=<?=$event->info["id"]?>" target="_blank">view</a></td>
                                </tr>
                             <? }
                            }
                         }
        			   ?> 
        			    
        			</tbody>
        	</table>
        	
        <? } ?>
        </div>
	</div>
	<script>
	window.onload = function(){
/*
	    var selected_month = get_field('event_month_selector').value;
	    var options = $$('#event_month_selector option');
	    if (selected_month=='Select a month'){
	        options[options.length-1].selected = true;
	    }
	    
        */
        
        displayEvents();
        $('event_month_selector').observe('change', this.displayEvents.bind(this)); 
        
    }
    function displayEvents(){
        var display_date = $('event_month_selector').value;
        if(display_date=='all'){
			$$('#table_events_contents tr').each(function(el){
				el.addClassName('visible');
			});        
        }else{
			$$('#table_events_contents .visible').each(function(el){
				el.removeClassName('visible');
			});

			$$('#table_events_contents .'+display_date).each(function(el){
				el.addClassName('visible');
			});
        
        }
    }
	</script>
<? include('inc/footer.php'); ?>