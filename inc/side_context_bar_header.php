<div class="side_context_bar print_hide">
	<div style="height:500px;width:0px;float:left;"></div><div class="padding_div" style="display:inline;float:left;">
<? if($GLOBALS['server_info']['environment']=='prod'){ ?>

	<style>
		.addthis_toolbox a {
			float:left;
			margin-right:4px;
		}
		
		#btn_google_plus_one a img {
			width:16px;
			height:16px;
			background:url(/img/icons_social_16px.png) -19px 0 no-repeat;
		}
	</style>
	
	<div>
		<div style="float:left;">
			<div id="btn_pinterest">
				<a href="https://pinterest.com/pin/create/button/?url=<?php echo get_permalink($post->ID);?>&media=&description=<?php echo urlencode(get_the_title($post->ID))?>" class="pin-it-button" count-layout="none" target="_blank" google_social_tracking="pinterest|page|{currenturl}"><img src="/img/icon_pinterest_16x16.gif" title="Pin It" /></a>
			</div>
			<div id="btn_google_plus_one">
				<a href="https://plus.google.com/share?url=<?php echo get_permalink($post->ID);?>" onclick="javascript:window.open(this.href,'','menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;" google_social_tracking="google|share|{currenturl}" google_social_tracking_skip_navigation="true"><img src="/img/spacer.gif" alt="Share on Google+" /></a>		
			</div>
			<div style="clear:both;"></div>
		</div>
		<div style="float:left;width:110px;">
			<!-- AddThis Button BEGIN -->
				<div class="addthis_toolbox addthis_16x16_style">
					<a class="addthis_button_facebook"></a>
					<a class="addthis_button_twitter"></a>
					<a class="addthis_button_email"></a>
					<a class="addthis_button_favorites"></a>
					<a class="addthis_button_compact"></a>
					<!--<a class="addthis_counter addthis_bubble_style"></a>-->
				</div>
			<!-- AddThis Button END -->
		</div>
		<div style="clear:both;"></div>
	</div>
<? } ?>
<?
	if(contains($_SERVER['PHP_SELF'],'your-wish-list.php')){
	?>
		<a href="?media=print" target="_blank" style="display:block;width:77px;margin:4px 0px;">print this page</a>
	<?
	}else{
	?>
		<a href="javascript:window.print();" style="display:block;width:77px;margin:4px 0px;">print this page</a>
	<?
	}
?>
<div style="height:8px;"></div>