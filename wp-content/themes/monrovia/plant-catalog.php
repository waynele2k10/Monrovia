<?php /* Template Name: Plant Catalog */

	function output_search_multiselect($table_name,$field_id,$table_title,$css_class = 'four_col'){

		// CUSTOM
		switch($field_id){
			case 'cold_zone':
				$list_records = array();
				for($i=1;$i<=11;$i++){
					$list_records[] = array('id'=>$i,'name'=>'Zone '.$i);
				}
			break;
			case 'sunset_zone':
				$list_records = array();
				for($i=1;$i<=45;$i++){
					$list_records[] = array('id'=>$i,'name'=>'Zone '.$i);
				}
			break;
			default:
				$list_records = get_table_data($table_name);
		}

		?>
			<div class="module_multiselect <?php echo $css_class?>" id="multiselect_<?php echo $field_id?>">
            	<h4><?php echo $table_title;?></h4>
				<div class="list_content flexcroll">
						<?php
							for($i=0;$i<count($list_records);$i++){
								if(!isset($list_records[$i]['is_historical'])||$list_records[$i]['is_historical']!='1'){
									echo("<span value=\"".$list_records[$i]['id']."\">".html_sanitize($list_records[$i]['name'])."<i class='fa fa-times-circle'></i></span><div></div>");
								}
							}
						?>
				</div>
				<input name="<?php echo $field_id?>" type="hidden" />
			</div>
		<?php
	}

	function generate_abc_html(){
		for($i=65;$i<91;$i++){
			?>
				<span value="<?php echo chr($i)?>"><?php echo chr($i)?></span><?php if($i != 90) { echo  "<span class='dot'>&#8226;</span>"; } ?>
			<?php
		}
	}

 get_header(); ?>

<!--  Conditional Container Tag: Monrovia (7439) | Plant Catalog Page (56251) | 2016 Grow Beautifully  (5972) | Expected URL: http://www.monrovia.com/plant-catalog/ --> <script type="text/javascript"> 
var ftRandom = Math.random()*1000000; document.write('<iframe style="position:absolute; visibility:hidden; width:1px; height:1px;" src="http://servedby.flashtalking.com/container/7439;56251;5972;iframe/?spotName=Plant_Catalog_Page&cachebuster='+ftRandom+'"></iframe>'); 
</script>

    <div class="content_wrapper clear">
		<section role="main" class="main">
        <?php the_breadcrumb($post->ID); //Print the Breadcrumb ?>
			<h1><?php the_title(); ?></h1>
            <?php if(isset($_GET['msg']) && $_GET['msg'] != ''){ //Message to display if redirect here from an inactive plant?>
               <p class="message">Were sorry, but the plant you are looking for is currently not available on our site. Please search for another one.</p>
            <?php } ?>
			<?php if (have_posts()): while (have_posts()) : the_post(); ?>
			<!-- article -->
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php the_content(); ?>
			</article>
			<!-- /article -->
			<?php endwhile; endif; ?>
            <div class="plant-search-top clear">
            	<div class="search-item clear">
					<h3 class="left">Searching for something in particular?</h3>
					<form action="<?php echo get_permalink(1191); ?>" method="get" id="form_query" onsubmit="return validate_query();" class="right">
                        <div class="form-item">
							<input id="plant_name_query" name="query"  value="search plants" onfocus="if(this.value=='search plants') this.value=''" onblur="if(!this.value) this.value='search plants'" type="text" />
							<input onclick="ga('send', 'event', 'Plant Catalog Search', 'Text search performed');" type="submit" title="search" value="Go" />
                        </div><!-- end form item -->
                     </form>
                </div><!-- end search item -->
                <div class="search-item clear">
					<h3>Searching by name?</h3>
					<form action="<?php echo get_permalink(1191); ?>" method="get" id="form_initials" class="clear">
						<div class="form-item clear">
                        	<h6 class="left">Common name starts with:</h6>
							<div class="module_singleselect right" id="singleselect_common_name">
								<div class="list_content letters">
									<?php generate_abc_html(); ?>
								</div>
							<input name="common_name" type="hidden" />
							</div>
                        </div><!-- end form item -->
						<div class="form-item clear">
                        	<h6 class="left">Botanical name starts with:</h6>
							<div class="module_singleselect right" id="singleselect_botanical_name">
								<div class="list_content letters">
									<?php generate_abc_html(); ?>
								</div>
							<input name="botanical_name" type="hidden" />
							</div>
                        </div><!-- end form item -->
                        <div class="center">
							<a href="javascript:void(0);" onclick="form_initials_submit();"  _onclick="$('form_initials').submit();" class="green-btn clear" id="by-name">Search Plants by Name</a>
                        </div><!-- end center -->
					<input type="hidden" name="sort_by" value="" />
				</form>
        	</div><!-- end search item -->
		</div><!-- plant search top -->
        <div class="plant-search-bottom">
			<form action="<?php echo get_permalink(1191); ?>" id="form_plant_search" _onsubmit="return submit_form();">
				<h3>Looking for recommendations?</h3>
				<p>Let us help you get started finding the perfect plants from our database of thousands by selecting from the criteria below.  Choose as many as you like. Not all fields are required.
                <div class="center">
                	<p><a href="javascript:void(0);" onclick="submit_form(true);" class="green-btn" id="criteria-top">Search with these Criteria</a></p>
                </div><!-- end center -->
				<div id="criteria_modules" class="clear">
					<?php
						$transient_name = 'plant_catalog_modules';
						$modules_output = monrovia_get_cache( $transient_name );
						if ( false === $modules_output ) :
							ob_start();		
					
							output_search_multiselect('list_type','type','plant types','three_col'); // ONE-TO-MANY
							output_search_multiselect('','cold_zone','USDA cold hardiness zone','three_col');
							output_search_multiselect('list_sun_exposure','sun_exposure','light needs','three_col'); // ONE-TO-MANY
							output_search_multiselect('list_deciduous_evergreen','deciduous_evergreen_id','deciduous/evergreen','three_col');
							output_search_multiselect('','sunset_zone','sunset zones','three_col'); // ONE-TO-MANY
							output_search_multiselect('list_water_requirement','water_requirement_id','water needs','three_col');
							output_search_multiselect('list_height','height_id','height','three_col');
							output_search_multiselect('list_spread','spread_id','spread','three_col');
							output_search_multiselect('list_growth_rate','growth_rate_id','growth rate','three_col');
							output_search_multiselect('list_growth_habit','growth_habit','growth habit','three_col');
							output_search_multiselect('list_foliage_color','foliage_color_id','foliage color','three_col');
							output_search_multiselect('list_flower_color','flower_color_id','flower color','three_col');
							output_search_multiselect('list_flowering_season','flowering_season','flowering season','three_col');
							output_search_multiselect('list_flower_attribute','flower_attribute','flower attribute','three_col');
							output_search_multiselect('list_special_feature','special_feature','special feature','three_col');
							output_search_multiselect('list_garden_style','garden_style','garden style','three_col');
							output_search_multiselect('list_landscape_use','landscape_use','landscape use','three_col');
							output_search_multiselect('list_problem_solution','problem_solution','problem/solution','three_col');
							$modules_output = ob_get_clean();
							monrovia_set_cache( $transient_name, $modules_output, MONROVIA_PLANT_DATA_TRANSIENT_EXPIRE );
						endif;
						echo $modules_output;
						?>
				</div><!-- end criteria_modules -->
				<div class="center">
                	<a href="javascript:void(0);" onclick="submit_form(true);" class="green-btn" id="criteria-btm">Search with these Criteria</a>
                </div><!-- end center -->
			</form>
    	</div><!-- plant-search-bottom -->
	</section>
    <?php get_sidebar('right'); ?>
	</div><!-- end content_wrapper -->
    
    <script>
	jQuery(document).ready( function($){
		
		//Add Select states to module_singleselect
		$('.module_singleselect span').on('click', function(){
			//Remove select state from other letter
			if($(this).hasClass('selected')){
					$(this).removeClass('selected');
			} else {
				$(this).parents('.module_singleselect').find('.selected').removeClass('selected');
				$(this).addClass('selected');
			}
			
			//Update the hidden input
			$(this).parents('.module_singleselect').find('input').val($(this).attr('value'));
		});
		
		// Toggle the select states and update the hidden input field
		$('.module_multiselect span').on('click', function(){

				$(this).toggleClass('selected');
				var inputVal ='';
				var values = $(this).parents('.module_multiselect').find('.selected');
				for(var i=0;i<values.length;i++){
					inputVal = inputVal+$(values[i]).attr('value');
					//If not last item, add a comma
					if((i+1) != values.length){
						inputVal = inputVal+',';
					}
				}
				//Set the input value
				$(this).parents('.module_multiselect').find('input').val(inputVal);

		});
	});
	
	function submit_form(explicit_submit){
		// BUILD URL
		var url = '';
		jQuery('.module_multiselect').each(function(multiselect){
			var field_id = jQuery(this).attr('id').replace('multiselect_','');
			var input = get_field(field_id);
			if(input.value) url += '&' + field_id + '=' + input.value;
		});
		url = '<?php echo get_permalink(1191);?>?' + url.substr(1);
		if(explicit_submit) navigate_to(url);
		//return true;
	}

	function form_initials_submit(){
		// IF ONLY BOTANICAL NAME INITIAL CHOSEN, SORT BY THAT
		var sort_by = get_field('sort_by');
		if(get_field('botanical_name').value&&!get_field('common_name').value){
			sort_by.value = 'botanical_name';
		}else{
			sort_by.value = 'common_name';
		}
		jQuery('#form_initials').submit();
	}
	
	function validate_query(){
		var query = jQuery('plant_name_query').value;
		return query&&query!='search plants';
	}
	
	function get_field(name){return jQuery('input[name=\''+name+'\'],select[name=\''+name+'\'],textarea[name=\''+name+'\'],input[id=\''+name+'\'],select[id=\''+name+'\'],textarea[id=\''+name+'\']')[0];}
	
	function navigate_to(url){window.setTimeout(function(){window.location=url;},0);}
</script>
    
<?php get_footer(); ?>