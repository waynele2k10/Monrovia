<?php
$orig_post = $post;
global $post, $wpdb;
$categories = get_the_category($post->ID);
$_plant_site_url = "//www.monrovia.com/";
// $_related_plant_str = get_post_custom_values('related-plants'); // custom field
$_related_plant_str = get('related_plants',1,1); // magic field
$_related_plant_title_str = get('related_plants_title',1,1); // magic field
$_related_plant_ids = explode(',', $_related_plant_id);
//$_plant_sql = "SELECT * FROM plants WHERE item_number <> '' AND release_status_id IN (1,2,3,4,6) AND id IN ($_related_plant_str)";
$_plant_sql = "SELECT * FROM plants WHERE release_status_id IN (1,2,3,4,6) AND item_number IN (".trim($_related_plant_str,",").")";
$_plants = $wpdb->get_results($_plant_sql);
if(empty($_related_plant_title_str)){
	$title = "Related Plants";
}else{
	$title = $_related_plant_title_str;
}
if (count($_plants) > 0) {
    echo '<div id="related_plants" class="clearfix"><h3>'.$title.'</h3><ul class="clearfix">';
}
$_index = 0;
foreach ($_plants as $_plant) {
    $data = getPlantData('', $_plant->item_number);
    $forsale = isForSale($data['item']);
    if ($_index == 5) {
        echo '</ul><ul class="clearfix">';
    }
    ?>
    <li class="col10-md-2">
        <div class="relatedthumb">
            <a href="<?php echo $_plant_site_url . '/plant-catalog/plants/' . $data['pid'] . '/' . $data['seo']; ?>"
               rel="bookmark" title="<?php echo $data['botanical']; ?>">
                <div class='plant-related-thumb' 
                     style="background: url('<?php echo site_url() . '/wp-content/uploads/plants/search_results/' . $data['image-id'] . '.jpg' ?>') no-repeat top center;background-size: 100%;">
                </div>
            </a>
            <?php if($forsale){ ?>
                <a href="<?php echo $forsale; ?>" title="Buy Now" target='_blank' class='for-sale clear'><i class="fa fa-shopping-cart"></i><span>Buy Now</span></a>
            <?php } ?>
        </div>
        <div class="relatedcontent">
            <div class="title">
                <a href="<?php echo $_plant_site_url . '/plant-catalog/plants/' . $data['pid'] . '/' . $data['seo']; ?>" 
                   title="<?php echo $data['title']; ?>">
                       <?php echo $data['title']; ?>
                </a>
            </div>
            <?php // client request plant image and plant name title with link only. ?>
            <p class="content-post">
                <span class="title"><?php echo html_sanitize($data['botanical']) ?></span>
                <br />
                Item #<?php echo $data['item'] ?>
                <?php if(isset($data['attribute']) && $data['attribute']!=''){ ?>
                    <br /><?php echo html_sanitize($data['attribute'])?>
                <?php } ?>
            </p>
        </div>
    </li>
    <?php
    $_index++;
}
if (count($_plants) > 0) {
    echo '</ul></div>';
}



