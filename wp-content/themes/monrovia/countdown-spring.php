<?php
/**
  Template Name: Countdown to Spring
 */
get_header();
?>
<!--<script type='text/javascript' src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/js/grayscale.js" type="text/javascript"></script>
<script src="<?php echo get_template_directory_uri(); ?>/js/functions.js" type="text/javascript"></script>-->
<script src="https://code.jquery.com/jquery-2.2.1.min.js" type="text/javascript"></script>
<script src="<?php echo get_template_directory_uri(); ?>/js/jquery.gray.js" type="text/javascript"></script>
<div class="content_wrapper clear">
    <section role="main" class="main">
        <?php the_breadcrumb($post->ID); //Print the Breadcrumb  ?>
        <section class="spring-intro">
            <div class="content">
                <h1><?php the_title(); ?></h1>
                <?php if (have_posts()): while (have_posts()) : the_post(); ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                            <?php the_content(); ?>
                        </article>
                        <?php
                        // Set the Variables
                        $id = $wp_query->post->ID;
                        $url = get_permalink($id);
                        ?> 
                        <?php
                        $pin_url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
                        if (isset($wp_query->query_vars['pid'])) {
                            $plant_id = $wp_query->query_vars['pid'];
                            $record = monrovia_get_plant_record($plant_id);
                            $record->get_images(true);
                            $image_set = $record->info['image_primary'];
                        }


                        if (isset($record->info['image_primary']) && $image_set != null) {
                            $pin_media = $image_set->info["path_detail"];
                        } else {
                            $pin_media = 'http://' . $_SERVER['SERVER_NAME'] . '/wp-content/themes/monrovia/img/FB_image.jpg';
                        }
                        ?>
                        <div class="social">
                            <div class="social-text">Share:</div>
                            <a class="addthis_button_email">
                                <img src="<?php echo get_template_directory_uri(); ?>/img/icons/icon-e.png"/></a>
                            <a class="addthis_button_facebook">
                                <img src="<?php echo get_template_directory_uri(); ?>/img/icons/icon-f.png"/></a>
                            <a class="addthis_button_twitter" data-text="custom share text">
                                <img src="<?php echo get_template_directory_uri(); ?>/img/icons/icon-t.png"/></a>
                            <a href="https://plus.google.com/share?url=<?php echo $url ?>"  onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');
                                    return false;">
                                <img src="<?php echo get_template_directory_uri(); ?>/img/icons/icon-g.png"/></a>
                            <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo $url; ?>&title=12%20days%20of%20springtime&summary=&source="  target="linkedwin" onclick="window.open(this.href, 'linkedwin',
                                            'left=20,top=20,width=500,height=500,toolbar=1,resizable=0'); return false;">
                                <img src="<?php echo get_template_directory_uri(); ?>/img/icons/icon-in.png"/></a>
                        </div>
                        <?php
                    endwhile;
                endif;
                ?>
            </div>
        </section>
        <section class="spring-wrapper">
            <?php
            preg_match('/MSIE (.*?);/', $_SERVER['HTTP_USER_AGENT'], $matches);
            if (count($matches) < 2) {
                preg_match('/Trident\/\d{1,2}.\d{1,2}; rv:([0-9]*)/', $_SERVER['HTTP_USER_AGENT'], $matches);
            }

            $isIE = false;
            if (count($matches) > 1) {
                $isIE = true;
            }

            $query = array('post_type' => 'countdown_to_spring');
            $custom_query = new WP_Query($query);
            if ($custom_query->have_posts()):
                ?>	

                <?php while ($custom_query->have_posts()): $custom_query->the_post(); ?>
                    <?php
                    $items = get_group('countdown_block');
                    $timezone_format = _x('m/d/Y', 'timezone date format');
                    $currentDate = new DateTime(date_i18n($timezone_format));
                    foreach ($items as $item):
                        $posx = intval($item['countdown_block_posx'][1]);
                        $posy = intval($item['countdown_block_posy'][1]);
                        $imgUrl = $item['countdown_block_image'][1]['original'];
                        $imgMobileUrl = $item['countdown_block_image_mobile'][1]['original'];
                        $hasButton = $item['countdown_block_display_sweepstakes_links'][1];
                        $date = new DateTime($item['countdown_block_date'][1]);
                        $m = intval($date->format('m'));
                        $d = $date->format('d');
                        $interval = date_diff($currentDate, $date)->format("%R%a");
                        $imgClass = "";
                        $dateClass = "";
                        if ($interval > 0) {
                            $imgClass = " grayscale";
                            $dateClass = " date-inactive";
                        }

                        $title = $item['countdown_block_title'][1];
                        $link = "javascript:;";
                        if ($item['countdown_block_link'][1] != "#" && $interval <= 0) {
                            $link = $item['countdown_block_link'][1];
                        }
                        ?>
                        <div class="brick" style="top:<?php echo $posy; ?>px;left:<?php echo $posx; ?>px">
                            <div class="brick-wrapper" onclick="goToPage('<?php echo $link; ?>')">
                                <span class="imgHolder">
                                    <?php if ($hasButton == 1 && $interval <= 0) : ?>
                                        <a onclick="event.cancelBubble = true;" target="_blank" href="<?php echo $item['countdown_block_sweepstakes_links'][1]; ?>"><div class="brick-button">Sweepstakes</div></a>
                                    <?php endif; ?>
                                    <img src="<?php echo $imgUrl; ?>" class="img-pc item-fade<?php echo $imgClass; ?>"/>
                                    <?php if (!$isIE) : ?>
                                        <img src="<?php echo $imgMobileUrl; ?>" class="img-mb item-fade<?php echo $imgClass; ?>"/>
            <?php endif; ?>
                                    <div class="date<?php echo $dateClass ?>"><?php echo $m . "/" . $d; ?></div>
                                    <div class="brick-title" ><h3><?php echo $title; ?></h3></div>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endwhile; ?>
<?php endif; ?>
        </section>
    </section>
</div><!-- end content_wrapper -->
<script type="text/javascript">
    function goToPage(link) {
        if (link == "javascript:;")
            return;

        window.open(link, '_blank');
    }
</script>
<?php get_footer(); ?>