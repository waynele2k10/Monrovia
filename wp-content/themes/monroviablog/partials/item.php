<li class="item">
	<a class="thumbnail-box" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" <?php if (has_post_thumbnail()) { 
	echo 'style="background-position: 50% center;
	background-size: cover; 
	background-image: url(' . wp_get_attachment_image_src(get_post_thumbnail_id( get_the_ID() ), 'large' )[0] . ');"'; 
}else{
	echo 'style="background-position: 50% center;
	background-size: cover; 
	background-image: url(' . get_bloginfo('stylesheet_directory') . '/img/thumbnail-default.jpg);"'; 
	}?>>
		<?php if (has_post_thumbnail()) : ?>
			<?php echo get_the_post_thumbnail(get_the_ID(), 'monroviablog-list', array()); ?>
		<?php else: ?>
			<?php echo '<img width="490" height="275" class="attachment-monroviablog-list size-monroviablog-list wp-post-image" src="' . get_bloginfo( 'stylesheet_directory' ) . '/img/thumbnail-default.jpg" />'; ?>
		<?php endif; ?>
		<?php $yellow_value = get_post_meta(get_the_ID(), "yellow_label", true); ?>
		<?php if (! empty( $yellow_value )) : ?>
		<div class="yellow-label"><span><?php echo $yellow_value ?></span></div>
		<?php endif; ?>
	</a>
	<div class="content-box" style="background: #f9f8f3;">
		<?php
		$icon_name = get_post_meta(get_the_ID(), "icon_name", true);
                
		$icon_value = get_post_meta(get_the_ID(), "icon_value", true);
		if (! empty( $icon_value )) :
		?>
		<span class="icon-label"><?php echo get_icon_label($icon_value,$icon_name);?></span>
		<?php endif; ?>
		<h2 class="title"><a href="<?php the_permalink(); ?>" class="title-body-text" title="outdoor living"><?php the_title(); ?></a></h2>
		<p class="content"><?php echo get_the_excerpt(); ?></p>
		<p class="more-info"><a href="<?php echo get_author_posts_url(get_the_author_meta('ID'), get_the_author_meta('user_nicename')); ?>"><?php the_author(); ?></a> | <a href="<?php echo get_day_link(get_the_time('Y'), get_the_time('m'), get_the_time('d')); ?>" title="<?php the_time('F j, Y') ?>"><?php the_time('F j, Y') ?></a></p>
		<a href="<?php the_permalink(); ?>" class="read-more">Read More</a>
	</div>
</li>