<?php /* Template Name: Re-Wholesellers */
get_header(); 

	//Include XML Class from Old Monrovia site
	//include('includes/xml.php');
	//Include Utility Functions from Old Monrovia site
	//include('includes/utility_functions.php');
	
	//Get zip code value, if set
	if(isset($_GET['zipcode']) && $_GET['zipcode'] != ''){
		
	//The Zip code parameter
	$query_zip = $_GET['zipcode'];
	
	// Get XML Data from azlink
	$xml = get_url('http://azlink.monrovia.com/tpg.php?zip='.$query_zip.'&list=rewholesalers&range=75',20);
	$xml_doc = XML_unserialize($xml);
	$rewholesalers = $xml_doc['response']['results']['result'];
	// IF ONLY ONE RESULT, WE NEED TO MAKE SURE IT'S IN ARRAY FORM, WHICH XML_unserialize BREAKS
	if(is_array($rewholesalers)&&count($rewholesalers)&&!is_array($rewholesalers[0])){
		$rewholesalers = array($rewholesalers);
	}
	$cold_zone_description = $xml_doc['response']['general']['cold_zone_description'];
	}
?>

    <div class="content_wrapper clear">
		<section role="main" class="main">
        <?php the_breadcrumb($post->ID); //Print the Breadcrumb ?>
        	<?php if(!isset($query_zip)){ ?>
					<h1><?php the_title(); ?></h1>
                    <?php the_content(); ?>
            <?php } else { ?>
               		<h1>Re-Wholesellers near <?php echo $query_zip ?></h1>
                    <?php the_content(); ?>
               		<?php if($cold_zone_description != ''){ echo "USDA Cold Zone: ".$cold_zone_description; } ?>
            <?php } ?> 
                <form action="?" method="get">
					<input name="zipcode" type="text" value="<?php if(isset($query_zip)){ echo $query_zip; } else{ echo "enter your zip code";} ?>" onfocus="if(this.value=='enter your zip code') this.value=''" onblur="if(!this.value) this.value='enter your zip code';" />
                    <button role="button" type="submit" class="search-submit">Go</button>
				</form>
            <?php if(isset($rewholesalers)){ ?>
            <?php if(is_array($rewholesalers)&&count($rewholesalers)){
				echo "<div>";
            	foreach($rewholesalers as &$rewholesaler){ ?>
            		<div class="rewholeseller">
						<?php if(isset($rewholesaler['url'])&&$rewholesaler['url']!=''){ ?>
								<a href="<?php echo $rewholesaler['url']?>" target="_blank" title="<?php echo $rewholesaler['name']?>"><?php echo $rewholesaler['name']?></a>
								<?php }else{ ?><strong><?php echo $rewholesaler['name']?></strong><? } ?><br />
								<?php echo $rewholesaler['address'].", ".$rewholesaler['city'].", ".$rewholesaler['state']." ".$rewholesaler['zip']?> (<a href="<?php echo $rewholesaler['map_url']?>" class="map_link underline" target="_blank">view map</a>) <b>&middot;</b> <?php echo $rewholesaler['phone'];?>
					</div><!-- end rewholeseller -->
            
			<?php  } //end foreach loop
				echo "</div>";
			} else { ?>
            	<h3>No re-wholesalers found.</h3>
			<?php } ?>
            <?php } ?>
		</section>
        <?php get_sidebar('right'); ?>
	</div><!-- end content_wrapper -->
    
<?php get_footer(); ?>