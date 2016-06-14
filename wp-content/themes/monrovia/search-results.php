<?php 
/* 
	Template Name: Search Results
	Display the Search results using GET Variables
	Re-using code from OLD Monrovia.com
*/

get_header(); 

// Uncomment these to Turn on Errors for this page
	//ini_set('display_errors','on');
	//error_reporting(E_ALL);

	require_once('includes/classes/class_search_plant.php');

	$query_zip = '';
	if(isset($_GET['zip'])) $query_zip = $_GET['zip'];
	if(($query_zip==''||!is_numeric($query_zip))&&isset($_COOKIE['zip'])) $query_zip = $_COOKIE['zip'];

	if(isset($_GET['zip'])&&$_GET['zip']!=''){
		$response = get_url('http://azlink.monrovia.com/tpg_cold_zone.php?zip='.$query_zip);
		$parts = explode('|',$response);
		$cold_zone = $parts[0];
		$cold_zone_description = $parts[1];

	}else{
		$cold_zone_description = '';
	}
	
	// SQL INJECTION-SAFE
	$query = '';
	if(isset($_GET['query'])) $query = $_GET['query'];
	if(is_suspicious(ids_sanitize($query))){
		@header('location:/plant-catalog/');
		exit;
	}

	$query = trim(parse_alphanumeric(strtolower(strip_tags(stripslashes($query))),'\'\-\+ '));
	$query = str_replace('+',' ',$query);
	if(is_suspicious(ids_sanitize($query))){
		@header('location:/plant-catalog/');
		exit;
	}
	
	// PLANTS
	if($query=='plant select'){
		$search = new search_plant('','id,item_number,common_name,botanical_name,is_new,primary_attribute,release_status_id',false);	
		$search->criteria['is_plant_select'] = '1';
	}else if(strpos($query,'brazelberr')!==false){
		$search = new search_plant('','id,item_number,common_name,botanical_name,is_new,primary_attribute,release_status_id',false);	
		$search->criteria['item_number'] = array('8170','8171','7938');
	}else{
		$search = new search_plant($query,'id,item_number,common_name,botanical_name,is_new,primary_attribute,release_status_id',false);
	}
	
	$search->results_per_page = 12;

	for($i=0;$i<count($search->plant_fields);$i++){
		if(isset($_GET[$search->plant_fields[$i]])){
			$field_name = $search->plant_fields[$i];
			$field_values_list = $_GET[$search->plant_fields[$i]];
			if($field_values_list!=''){
				$field_values = explode(',',$field_values_list);
				$search->criteria[$field_name] = $field_values;
			}
		}
	}

	// COLD ZONES
	$cold_zones_list = '';
	if(isset($_GET['cold_zone'])) $cold_zones_list = $_GET['cold_zone'];
	if($cold_zones_list!=''){
		$cold_zones = explode(',',$cold_zones_list);
		$cold_zone_low = 11;
		$cold_zone_high = 0;
		for($i=0;$i<count($cold_zones);$i++){
			$cold_zone_low = min($cold_zone_low,intval($cold_zones[$i]));
			$cold_zone_high = max($cold_zone_high,intval($cold_zones[$i]));
		}
		$search->criteria['cold_zone_low'] = $cold_zone_low;
		$search->criteria['cold_zone_high'] = $cold_zone_high;
	}

	// ONE-TO-MANY CRITERIA
		add_one_to_many_criterion('type');
		add_one_to_many_criterion('sunset_zone');
		add_one_to_many_criterion('flowering_season');
		add_one_to_many_criterion('flower_attribute');
		add_one_to_many_criterion('garden_style');
		add_one_to_many_criterion('landscape_use');
		add_one_to_many_criterion('problem_solution');
		add_one_to_many_criterion('sun_exposure');
		add_one_to_many_criterion('special_feature');
		add_one_to_many_criterion('growth_habit');
		add_one_to_many_criterion('water_requirement_id');  //Wasn' in Orginal code, but seemed to be missing anyhow

	if(isset($_GET['view_all'])&&$_GET['view_all']=='1') $search->view_all = 1;

	$search->results_start_page = isset($_GET['start_page'])?intval($_GET['start_page']):0;

	// TODO
	if($search->results_start_page==0) $search->results_start_page = 1;

	$_SESSION['search'] = $search;

	if(isset($_GET['sort_by'])){
		switch($_GET['sort_by']){
			/*case 'is_available':
				$order_by = 'relevancy DESC,relevancy_metaphone DESC,common_name';
			break; */
			case 'is_available':
				$order_by = 'quantity DESC,common_name,relevancy DESC,relevancy_metaphone DESC';
			break;
			case 'is_new':
				$order_by = 'is_new DESC,relevancy DESC,relevancy_metaphone DESC,common_name';
			break;
			case 'common_name':
				$order_by = 'common_name ASC,relevancy DESC,relevancy_metaphone DESC';
			break;
			case 'botanical_name':
				$order_by = 'botanical_name ASC,relevancy DESC,relevancy_metaphone DESC';
			break;
			case 'has_photo':
				$order_by = '((SELECT COUNT(*) FROM plant_image_sets WHERE plant_image_sets.plant_id=plants.id AND plant_image_sets.is_active=1 AND (expiration_date>NOW() OR expiration_date="0000-00-00"))>0) DESC,relevancy DESC,relevancy_metaphone DESC,common_name';
			break;
			case 'cold_zone':
				$search->result_fields .= ',cold_zone_low';
				$order_by = 'cold_zone_low ASC,relevancy DESC,relevancy_metaphone DESC,common_name ASC';
			break;
		}
	}
	if(isset($order_by)) $search->order_by = $order_by;

	//'relevancy DESC, common_name ASC'
	$search->search(true);

	function add_one_to_many_criterion($list_name){
		global $search;
		$raw_list = '';
		if(isset($_GET[$list_name])) $raw_list = $_GET[$list_name];
		$search->criteria[$list_name.'s'] = array();
		if($raw_list!=''){
			$list = explode(',',$raw_list);
			for($i=0;$i<count($list);$i++){
				if($list[$i]!='') $search->criteria[$list_name.'s'][] = $list[$i];
			}
		}
	}

	function append_to_head(){
		?>
		<?php
	}

	function output_search_multiselect($table_name,$field_id,$table_title,$css_class = 'five_col'){

		// CUSTOM
		switch($field_id){
			case 'cold_zone':
				$list_records = array();
				for($i=1;$i<=11;$i++){
					$list_records[] = array('id'=>$i,'name'=>'Zone '.$i);
				}
			break;
			default:
				$list_records = get_table_data($table_name);
		}

		?>
			<div class="module_multiselect <?php echo $css_class?>" id="multiselect_<?php echo $field_id?>">
				<div class="list_content">
						<?php
							for($i=0;$i<count($list_records);$i++){
								if(!isset($list_records[$i]['is_historical'])||$list_records[$i]['is_historical']!='1'){
									echo("<span value=\"".$list_records[$i]['id']."\">".html_sanitize($list_records[$i]['name'])."<i class='fa fa-times-circle'></i></span><div></div>");
								}
							}
						?>
				</div>
			</div>
		<?php
	}
	
	// Showing Results Text
	function getResultsText($search){
	$record_first = (($search->results_start_page-1)*$search->results_per_page)+1;
	$record_last = min($record_first + $search->results_per_page - 1,$search->results_total);
	if($search->results_total>0){
		if($record_first==$record_last){
			$text = (" $record_first of ".$search->results_total);
		}else if($record_first==1&&$record_last==$search->results_total){
			$text = ("Showing all ".$search->results_total. " results");
		}else{
			$text =(" $record_first - $record_last of ".$search->results_total);
		}
			return $text;
	}
	}

?>


<style>
<?php
	if($search->results_pages<2){
		echo('.paging{display:none;}');
	}
	if($search->results_total==0){
		echo('.sorter{display:none;}');
	}
?>
</style>

<script>
// Function to Retrieve a Cookie
function getCookie(c_name)
{
var i,x,y,ARRcookies=document.cookie.split(";");
for (i=0;i<ARRcookies.length;i++)
{
  x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
  y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
  x=x.replace(/^\s+|\s+$/g,"");
  if (x==c_name)
    {
    return unescape(y);
    }
  }
} 
	function accordion_segment_toggle(segment){
		//segment.toggleClassName('expanded');
	}
	function get_field(name){return jQuery('input[name=\''+name+'\'],select[name=\''+name+'\'],textarea[name=\''+name+'\'],input[id=\''+name+'\'],select[id=\''+name+'\'],textarea[id=\''+name+'\']')[0];}
	
	function get_multiselect_module_values(id){
		var value='';
		jQuery(id).find('.list_content span.selected').each(function(){
			value+=','+jQuery(this).attr('value');
		});
		return value.substr(1);
	}
	function set_multiselect_module_values(id,values){
		var values_array=values.split(',');

		for(var i=0;i<values_array.length;i++){
			var temp = values_array[i];
			jQuery('#multiselect_cold_zone').find("span[value='"+temp+"']").addClass('selected');
		}
	}		
	
	var search_criteria = '';
	var search_criteria = jQuery.parseJSON(window.unescape(getCookie('search_criteria')));
	

	function change_zip(){
		var zip = jQuery('change_zip_zip').value;
		window.location = jQuery('change_zip_zip').up('form').action += '&zip=' + zip;
		return false;
	}
	

	jQuery(document).ready( function($){
		window.current_sorting_method = '';
		/*
		// ACCORDION SEGMENTS
		$$('.accordion_segment .title').each(function(title){
			var segment = title.up('.accordion_segment');
			title.observe('click',function(){accordion_segment_toggle(segment);});
			accordion_segment_toggle(segment); // BEGIN WITH EVERYTHING EXPANDED, PER ANDREA
		});
		*/
		
		// Add selected class to Criteria that was selected on 
		// previous search submission
		for(criterion_name in search_criteria){
			var skip = ['is_active','release_status_id','query_used'];
			if(skip.indexOf(criterion_name) == -1){
				var last_letter = criterion_name.substr(criterion_name.length-1);
				var field_name = (last_letter=='s')?criterion_name.substr(0,criterion_name.length-1):criterion_name;
				
				//console.log(criterion_name);
				if(search_criteria[criterion_name].length > 0){
					for(var i=0;i<search_criteria[criterion_name].length;i++){
						$('#multiselect_'+field_name+' span[value="'+search_criteria[criterion_name][i]+'"]').addClass('selected');
					}
				}
			}
		}
		
		// Special Case for Cold Zone values
		var cold_zone_low=search_criteria['cold_zone_low'];
		var cold_zone_high=search_criteria['cold_zone_high'];
		var cold_zones='';
		if(cold_zone_low&&cold_zone_high){
			for(var i=0;i<=(cold_zone_high-cold_zone_low);i++){
				cold_zones+=','+(i+cold_zone_low);
				}
			var multiselect_cold_zone=jQuery('#multiselect_cold_zone');
			if(multiselect_cold_zone&&cold_zones)set_multiselect_module_values('multiselect_cold_zone',cold_zones.substr(1));
		}
		
		
		// Get the current Selected value
		current_sorting_method = $('#sort_by').attr('_value');
		
		// INITIALIZE SORTING DROPDOWN
		$('#sort_by').on('change',sorting_method_changed);
		
		//Add selected state of Drop Down
		$('#sort_by option[value='+current_sorting_method+']').prop('selected', 'selected');
		
		//Toggle class of selected on Filterable items
		$('.module_multiselect span').click( function(){
				$(this).toggleClass('selected');
		});

	});

	function sorting_method_changed(){
		if(this.value!=current_sorting_method || current_sorting_method == ''){
			var url = '?<?php echo js_sanitize($search->get_url_params(true,false,false));?>';
			url += (url=='?'?'':'&') + 'sort_by='+this.value;
			window.location = url;
		}
	}
	
	function submit_form(explicit_submit){
	jQuery('.module_multiselect').each(function(multiselect){
			var field_id = jQuery(this).attr('id').replace('multiselect_','');
			var input = get_field(field_id);
			var field_value = get_multiselect_module_values(jQuery(this));
			if(input){
				input.value = field_value;
			}else if(field_value){
				/*input = new Element('input');
				input.type = 'hidden';
				input.name = field_id;
				input.value = field_value; */
				jQuery('#form_plant_search').append('<input type="hidden" value="'+field_value+'" name="'+field_id+'" />');
			}
		}); 

		// RETAIN PREVIOUS CRITERIA
		for(criterion_name in search_criteria){
			var skip = ['is_active','release_status_id','query_used'];
			if(skip.indexOf(criterion_name) == -1){
				var last_letter = criterion_name.substr(criterion_name.length-1);
				var field_name = (last_letter=='s')?criterion_name.substr(0,criterion_name.length-1):criterion_name;
				if(search_criteria[criterion_name]&&!get_field(field_name)){
					
					var test = '';
					var temp = '';
					
					// Get all the Items that are still selected
					jQuery('#multiselect_'+field_name+' .selected').each( function(index,el){
						test[index] = jQuery(this).attr('value');
					});
						var b = 0;
						//Strip out values that are no longer selected
						for(var i=0;i<search_criteria[criterion_name].length;i++){
							if(test.indexOf(search_criteria[criterion_name][i]) != -1){
								// Remove item
								if(!jQuery.isArray(search_criteria[criterion_name])){
									console.log('Not array'+search_criteria[criterion_name]);
								} else{
									console.log('Array'+search_criteria[criterion_name]);
									temp[b] = search_criteria[criterion_name][i];
									b++;
								}
							}
						}
						search_criteria[criterion_name] = temp;
					// Get the field value
					field_value = (typeof search_criteria[criterion_name])=='string'?search_criteria[criterion_name]:search_criteria[criterion_name].join(',');
					if(field_value) field_value = field_value.replace('+',' ');
					
					if(field_value) jQuery('#form_plant_search').append('<input type="hidden" value="'+field_value+'" name="'+field_name+'" />');
				}
			}
		} 
		//if(explicit_submit) jQuery('#form_plant_search').submit();
		return true;
	}
</script>
    <div class="content_wrapper clear">
		<section role="main" class="main">
        	<div class="left">
        		<?php the_breadcrumb($post->ID); //Print the Breadcrumb ?>
                <?php 
				// For Mobile, if you arrive here from Main Nav
				// It should say all plants
				$title = 'Plant Catalog'; //Default Text
				if(isset($_GET['first'])){
					$title = 'All Plants';
				} ?>
            	<h1><?php echo $title; ?></h1>
            </div><!-- end left -->
            <div class="hideMobile">
            <?php include('includes/zone-box.php'); ?>
            </div>
            <div class="search-wrap clearfix">
    			<div id="sidebar-left" class="search-refine left sidebar" >
    				<h3>Refine Your Search</h3>
                    <div class="list-content hideMobile">
                    	<a href="<?php echo get_permalink(34); ?>">Start a new search</a>, or refine your search below.
                    </div><!-- end list content --> 
                    <a href="javascript:void(0);" id="clearAll" onclick="jQuery('#form_plant_search .selected').removeClass('selected')">Clear All Filters</a>
                    <div class="refine-list">
                        <form action="" method="get" onsubmit="return submit_form(this);" id="form_plant_search">
                        	<input type="submit" class="green-btn" title="search" value="Search" /><br />
                        	<?php include('includes/refine-search.php'); ?>
							<input type="submit" class="green-btn" title="search" value="Search" />
							<input type="hidden" name="query" value="<?php echo $query?>" />
						</form>
                	</div><!-- end refine list -->
				</div><!-- end search-refine left -->
                
    			<div class="search-results left">
                <?php if($search->results_total>0) { ?>
                	<div class="search-meta clear">
                    	<div class="showing left">
                			<?php echo getResultsText($search); ?>
        				</div><!-- end showing -->
                		<div class="sorter left">
                        	<a href="javascript:void(0);" class="showMobile green-btn left" onclick="jQuery('body').toggleClass('open-filter');">Filter</a>
                			<label for="sort_by" class="hideMobile">Sort by:</label>
							<div class="select-wrap">
                    			<select id="sort_by" name="sort_by" class="field_text" _value="<?php echo (isset($_GET['sort_by'])&&$_GET['sort_by']!='')?$_GET['sort_by']:'relevancy'?>">
                                <option value="common_name">Common Name</option>
								<option value="relevancy">Relevancy</option>
								<option value="is_available">Online Availability</option>
								<option value="is_new">New Introduction</option>
								<option value="botanical_name">Botanical Name</option>
								<option value="has_photo">With Photo</option>
								<option value="cold_zone">USDA Hardiness Zones</option>
							</select>
                    	</div><!-- end select wrap -->
                        <label for="sort_by" class="showMobile">Sort by:</label>
                	</div><!-- end sorter -->
                    <div class="paging right">
						<?php echo $search->pagination_html;?>
	  				</div><!-- end paging -->
                </div><!-- end search meta --> <?php } // End if ?>
      			<?php if($query != '' && $search->results_total>0){ ?>
      				<h2>Search results for <?php echo $query; ?></h2>
      			<?php } ?>
      			<!-- Display Search Results -->
      			<div class="plants-grid search-results clear">
					<?php $search->output_results_plant_search(); ?>
      			</div><!-- end plants grid -->
          		
                <?php if($search->results_total>0){ ?>
	  			<div class="search-meta bottom clear">
                    <div class="showing left">
                		<?php echo getResultsText($search); ?>
        			</div><!-- end showing -->
                    <div class="paging right">
						<?php echo $search->pagination_html;?>
	  				</div><!-- end paging -->
               </div><!-- end search meta -->
               <?php } //End If ?>
    		</div><!-- end right column -->
        </div><!-- end search wrap -->
		</section>
	</div><!-- end content_wrapper -->    
<?php get_footer(); ?>
