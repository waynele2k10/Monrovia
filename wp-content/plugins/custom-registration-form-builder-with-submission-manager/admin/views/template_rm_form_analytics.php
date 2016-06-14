
<?php
 //echo "<pre>",var_dump($data),"</pre>";

?>
    
    <!--------WP Menu Bar
    
    <div class="wpadminbar">Hi</div>
   
    <div class="adminmenublock">
    test</div>-->
    
    <div class="rmagic">
        
<!-----Operationsbar Starts-->
    
    <div class="operationsbar">
        <div class="rmtitle"><?php echo RM_UI_Strings::get('TITLE_FORM_STAT_PAGE'); ?></div>
        <div class="icons">
        <a href="<?php echo get_admin_url()."admin.php?page=rm_options_manage";?>"><img src="<?php echo plugin_dir_url(dirname(dirname(__FILE__))) . "images/global-settings.png"; ?>">
        </a></div>
        <div class="nav">
        <ul>
            <li onclick="jQuery.rm_do_action_with_alert( '<?php echo RM_UI_Strings::get('ALERT_STAT_RESET'); ?>','rm_form_analytic_dd', 'rm_analytics_reset')"><a href="javascript:void(0)"><?php echo RM_UI_Strings::get('LABEL_RESET_STATS');?></a></li>
            <li class="rm-form-toggle"><?php
                    if (count($data->forms) !== 0)
                    {
                        echo RM_UI_Strings::get('LABEL_FILTER_BY');
                        ?> 
                        <form action="" id="rm_form_analytic_dd" method="post">
                            <input type="hidden" name='rm_slug' value='' id='rm_slug_input_field'>
                            <select id="rm_form_dropdown" name="rm_form_id" onchange="rm_load_page(this, 'analytics_show_form')">
                                <?php
                                foreach ($data->forms as $form_id => $form)
                                    if ($data->current_form_id == $form_id)
                                        echo "<option value=$form_id selected>$form</option>";
                                    else
                                        echo "<option value=$form_id>$form</option>";
                                ?>
                            </select>
                        </form>
                        <?php
                    }
                    ?>
            </li>
            </ul>
        </div>
        
        </div>
<!--------Operationsbar Ends-->

<!--------Filters

<div class="rmfilters">
<ul>
<li>Filters </li>
<li><a href="#" class="filteron">Time &#x2715;</a></li>
<li><a href="#">Submissions &#x25BF;</a></li>
<li><a href="#">Search &#x25BF;</a></li>
<li class="sort"><a href="#">By Name &#x25BF;</a></li>
<li class="sort">Sort </li>
</ul>
</div> -->
        
<!-------Contentarea Starts-->
        
<div class="rmagic-analytics">

    <div class="rm-analytics-table-wrapper">

<?php
    if(count($data->forms) == 0):
?>
    <table class="rm-form-analytics">
    <th>
    
        <div class="rmnotice" style="min-height: 45px;"><?php echo RM_UI_Strings::get('MSG_NO_FORMS_FUNNY'); ?></div>
    
      </th>
<?php
        return;
        endif;
?>

<?php
    if(!$data->stat_data):
?>
    <table class="rm-form-analytics">
    <th>
    
        <div class="rmnotice" style="min-height: 45px;"><?php echo RM_UI_Strings::get('ERROR_STAT_INSUFF_DATA'); ?></div>
    
      </th>
<?php
        return;
        endif;
?>
    
    
    <table class="rm-form-analytics">
    <th>#</th>
    <th><?php echo RM_UI_Strings::get('LABEL_IP'); ?></th>
    <th><?php echo RM_UI_Strings::get('LABEL_SUBMISSION_STATE'); ?></th>
    <th><?php echo RM_UI_Strings::get('LABEL_VISITED_ON'); ?></th>
    <th><?php echo RM_UI_Strings::get('LABEL_SUBMITTED_ON'); ?></th>
    <th><?php echo RM_UI_Strings::get('LABEL_TIME_TAKEN'); ?></th>

<?php
		$i = $data->starting_serial_number;
		foreach($data->stat_data as $stat)
		{
			$visited_on = RM_Utilities::convert_to_mysql_timestamp($stat->visited_on);
           $visited_on = RM_Utilities::localize_time($visited_on, 'd M Y, h:ia');
			if($stat->submitted_on)
				$submitted_on = RM_Utilities::convert_to_mysql_timestamp($stat->submitted_on);
			else
				$submitted_on = null;
?>
    		<tr>
            <td><?php echo $i; ?></td>
    		<td><a href='https://geoiptool.com/?ip=<?php echo $stat->user_ip; ?>'><?php echo $stat->user_ip; ?></a></td>
    	 	<td>&nbsp;
<?php           if($stat->submitted_on)
                    echo "<img class='rmsubmitted_icon' src='".
                         plugin_dir_url(dirname(dirname(__FILE__))) . "images/right.png'>";
?>
            </td>
    	 	<td><?php echo $visited_on; ?></td>
            <td><?php echo $submitted_on; ?></td>
            <td><?php echo $stat->time_taken; if($stat->time_taken) echo "s"; ?></td>
            </tr>
<?php                 
			$i++;
		}

        
?>
    </table>

<?php /************ Pagination Logic ***************/
    $max_pages_without_abb = 10;
    $max_visible_pages_near_current_page = 3; //This many pages will be shown on both sides of current page number.

     if ($data->total_pages > 1): ?>
            <ul class="rmpagination">
                <?php
                if($data->curr_page > 1):
                ?>
                    <li><a href="?page=<?php echo $data->rm_slug ?>&rm_form_id=<?php echo $data->current_form_id;?>&rm_reqpage=1"><?php echo RM_UI_Strings::get('LABEL_FIRST'); ?></a></li>
                    <li><a href="?page=<?php echo $data->rm_slug ?>&rm_form_id=<?php echo $data->current_form_id;?>&rm_reqpage=<?php echo $data->curr_page - 1; ?>"><?php echo RM_UI_Strings::get('LABEL_PREVIOUS'); ?></a></li>
                <?php
                endif;
                 if($data->total_pages > $max_pages_without_abb):
                    if($data->curr_page > $max_visible_pages_near_current_page+1):
                ?>
                        <li><a> ... </a></li>
                <?php
                        $first_visible_page = $data->curr_page - $max_visible_pages_near_current_page;
                    else:
                        $first_visible_page = 1;
                    endif;

                    if($data->curr_page < $data->total_pages - $max_visible_pages_near_current_page):    
                        $last_visible_page = $data->curr_page + $max_visible_pages_near_current_page;
                    else:
                        $last_visible_page = $data->total_pages;
                    endif;
                else:
                    $first_visible_page = 1;
                    $last_visible_page = $data->total_pages;
                endif;
                for ($i = $first_visible_page; $i <= $last_visible_page; $i++):
                    if ($i != $data->curr_page):
                ?>
                        <li><a href="?page=<?php echo $data->rm_slug ?>&rm_form_id=<?php echo $data->current_form_id;?>&rm_reqpage=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                <?php else:
                ?>
                        <li><a class="active" href="?page=<?php echo $data->rm_slug ?>&rm_form_id=<?php echo $data->current_form_id;?>&rm_reqpage=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                <?php endif;
                endfor;
                if($data->total_pages > $max_pages_without_abb):
                    if($data->curr_page < $data->total_pages - $max_visible_pages_near_current_page):
                ?>
                    <li><a> ... </a></li>
                <?php
                    endif;
                endif;
                ?>
                <?php
                if($data->curr_page < $data->total_pages):
                ?>
                    <li><a href="?page=<?php echo $data->rm_slug ?>&rm_form_id=<?php echo $data->current_form_id;?>&rm_reqpage=<?php echo $data->curr_page + 1; ?>"><?php echo RM_UI_Strings::get('LABEL_NEXT'); ?></a></li>
                    <li><a href="?page=<?php echo $data->rm_slug ?>&rm_form_id=<?php echo $data->current_form_id;?>&rm_reqpage=<?php echo $data->total_pages; ?>"><?php echo RM_UI_Strings::get('LABEL_LAST'); ?></a></li>
                <?php
                endif;
                ?>
            </ul>
<?php endif; ?>

        
         </div>
<div class="rm-left-stats-box">
        <div class="rm-box-title"><?php echo RM_UI_Strings::get('LABEL_CONVERSION')." % (".RM_UI_Strings::get('LABEL_SUBMISSIONS')."/". RM_UI_Strings::get('LABEL_TOTAL_VISITS').")"; ?></div>
        <div class="rm-box-graph" id="rm_conversion_chart_div">
            </div>
        </div>
    
    <div class="rm-right-stats-box">
        <div class="rm-box-title"><?php echo RM_UI_Strings::get('LABEL_BROWSERS_USED'); ?></div>
        <div class="rm-box-graph" id="rm_browser_usage_chart_div">
            </div>
        </div>
    
    <div class="rm-left-stats-box">
        <div class="rm-analytics-stat-counter">
        <div class="rm-analytics-stat-counter-value"><?php echo $data->analysis->failure_rate;?><span class="rm-counter-value-dark">%</span></div>
        <div class="rm-analytics-stat-counter-text"><?php echo RM_UI_Strings::get('LABEL_FAILURE_RATE'); ?></div>
        </div>
        </div>
    
    <div class="rm-right-stats-box">
        <div class="rm-analytics-stat-counter">
        <div class="rm-analytics-stat-counter-value"><?php echo $data->analysis->avg_filling_time;?><span class="rm-counter-value-dark">s</span></div>
       <div class="rm-analytics-stat-counter-text"><?php echo RM_UI_Strings::get('LABEL_TIME_TAKEN_AVG'); ?></div>
        </div>
        </div>
    
    <div class="rm-center-stats-box">
        <div class="rm-box-title"><?php echo RM_UI_Strings::get('LABEL_CONV_BY_BROWSER'); ?></div>
        <div class="rm-box-graph" id="rm_conversion_by_browser_chart_div">
            </div>
        </div>

    
</div>
        
    
    </div>
    


<?php

    /**************************************************************
    **************     Chart drawing - Conversion    **************
    **************************************************************/

    $dataset = array(RM_UI_Strings::get('LABEL_FAILED_SUBMISSIONS') => $data->analysis->failed_submission,
                     RM_UI_Strings::get('LABEL_SUBMISSIONS') => $data->analysis->total_entries - $data->analysis->failed_submission);

    $json_table = RM_Utilities::create_json_for_chart(RM_UI_Strings::get('LABEL_SUBMISSIONS'), RM_UI_Strings::get('LABEL_FAILED_SUBMISSIONS'), $dataset);
?>
<script>
    function drawConversionChart()
    { 
        var data = new google.visualization.DataTable('<?php echo $json_table;?>');

        // Set chart options
        var options = { /*is3D : true,*/
                       title:'<?php echo strtoupper(RM_UI_Strings::get('LABEL_TOTAL_VISITS')." ".$data->analysis->total_entries); ?>',
                       /*width:400,*/
                       height:300,
                       fontName: 'Titillium Web',
                       pieSliceTextStyle: {fontSize: 12},
                       titleTextStyle: {fontSize: 18, color: '#87c2db', bold: false},
                       legend: { position: 'bottom', maxLines: 1, textStyle: {fontSize: 12} },
                       /*chartArea: {left:20,top:0,width:'50%',height:'75%'},*/
                       colors: ['#e69f9f', '#87c2db']};

        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.PieChart(document.getElementById('rm_conversion_chart_div'));
        chart.draw(data, options);
    }
</script>

<?php
    /******************************************************************
    **************     Chart drawing - Browser Usage     **************
    ******************************************************************/
    $dataset = array();

    foreach($data->analysis->browsers as $name=>$usage)
    {
        $formatted_name = RM_UI_Strings::get('LABEL_BROWSER_'.strtoupper($name));
        $dataset[$formatted_name] = $usage->visits;
    }

    $json_table = RM_Utilities::create_json_for_chart(RM_UI_Strings::get('LABEL_BROWSER'),
        RM_UI_Strings::get('LABEL_HITS'), $dataset);
   
?>
<script>
    function drawBrowserUsageChart()
    { 
        var data = new google.visualization.DataTable('<?php echo $json_table;?>');

        // Set chart options
        var options = { /*is3D : true,*/
                        /* width:400,*/
                       height:300,
                       fontName: 'Titillium Web',
                       pieSliceTextStyle: {fontSize: 12},
                       colors: ['#87c2db','#ebb293','#93bc94','#e69f9f','#cecece','#f0e4a5','#d6c4df','#e2a1c4','#8eb2cc']};

        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.PieChart(document.getElementById('rm_browser_usage_chart_div'));
        chart.draw(data, options);
    }
</script>

<?php
    /******************************************************************
    **************     Chart drawing - C/B Bar Chart     **************
    ******************************************************************/
    $data_string = '';
    foreach($data->analysis->browsers as $name=>$usage)
    {
        if($usage->visits != 0)
        {
            $formatted_name = RM_UI_Strings::get('LABEL_BROWSER_'.strtoupper($name));
            $data_string .= ", ['$formatted_name', ".$usage->visits.", $usage->submissions]";
        }
    }
    $data_string = substr($data_string, 2);

   
?>
<script>
    function drawConversionByBrowserChart()
    { 
        var data = google.visualization.arrayToDataTable([
        ['<?php echo RM_UI_Strings::get('LABEL_BROWSER'); ?>', 
         '<?php echo RM_UI_Strings::get('LABEL_TOTAL_VISITS'); ?>',
         '<?php echo RM_UI_Strings::get('LABEL_SUBMISSIONS'); ?>'],
        <?php echo $data_string; ?>
      ]);

      var options = {
        chartArea: {width: '50%'},
        height: 500,
        fontName: 'Titillium Web',
        pieSliceTextStyle: {fontSize: 12},
        hAxis: {
          title: '<?php echo RM_UI_Strings::get('LABEL_HITS'); ?>',
          minValue: 0
        },
        vAxis: {
          title: '<?php echo RM_UI_Strings::get('LABEL_BROWSER'); ?>'
        },
        legend: { position: 'top', maxLines: 3 },
        colors: ['#8eb2cc', '#e2a1c4'],
        bar: {
            groupWidth: 20
        }
      };

      var chart = new google.visualization.BarChart(document.getElementById('rm_conversion_by_browser_chart_div'));
      chart.draw(data, options);
    }
</script>


<!--  '#87c2db','#ebb293','#93bc94','#e69f9f','#cecece','#f0e4a5','#d6c4df','#e2a1c4','#8eb2cc','#b8d5e9'  -->
