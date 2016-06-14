<?php
	$plant_list = array();

	$plant_list[] = array('common_name'=>'Texas Purple Japanese Wisteria','botanical_name'=>'Wisteria floribunda \'Texas Purple\'','item_number'=>'7677','id'=>'2238','image_path'=>'/img/plants/4307/sr/7677-texas-purple-japanese-wisteria-full-shot.jpg','is_new'=>false);

	$plant_list[] = array('common_name'=>'Black Beauty Coral Bells','botanical_name'=>'Heuchera \'Black Beauty\' P.P.# 13288','item_number'=>'3961','id'=>'1365','image_path'=>'/img/plants/1465/sr/3961-black-beauty-coral-bells-medium-shot.jpg','is_new'=>false);


	$temp_plant = new plant();

	function output_plant_this_now_srp(){
		global $plant_list, $temp_plant;
		foreach($plant_list as &$plant){
			$in_wish_list = ($GLOBALS['monrovia_user']->get_wish_list_item_by_plant_id($plant['id'])!='');
			$title_text = ($in_wish_list)?'on your wish list':'';

			// GENERATE SEO-FRIENDLY NAME
			$temp_plant->info['common_name'] = $plant['common_name'];
			$temp_plant->info['id'] = $plant['id'];
			$temp_plant->populate_dumb_values();
			$plant['details_url'] = $temp_plant->info['details_url'];
		?>
			<div class="search_result_plant">
				<div class="inner">
					<a href="<?=$plant['details_url']?>">
						<? if($plant['is_new']){ ?><div class="flag_new">NEW PLANT</div><? } ?>
						<img src="<?=$plant['image_path']?>" title="<?=$plant['common_name']?>" class="search_result_plant_image" />
						<span class="uppercase"><?=$plant['common_name']?></span><br />
						<?=$plant['botanical_name']?><br />
						Item #<?=$plant['item_number']?>
						<img src="/img/icon_srp_view.gif" class="flag_view" style="display:none;" />
						<img src="/img/icon_wish_list_plus_flag.gif" class="flag_add" style="display:none;" />
					</a>
					<img src="/img/spacer.gif" class="icon_wish_list <?=($in_wish_list)?'star':'plus'?>" title="<?=$title_text?>" plant_id="<?=$plant['id']?>" />
				</div>
			</div>

		<? }
		}

	function output_plant_this_now_sidebar(){
		global $plant_list, $temp_plant;
		for($i=0;$i<min(count($plant_list),2);$i++){

			// TEMPORARY LOGIC--SHOW FIRST AND SECOND PLANTS
			$plant = $plant_list[$i];

			// GENERATE SEO-FRIENDLY NAME
			$temp_plant->info['common_name'] = $plant['common_name'];
			$temp_plant->info['id'] = $plant['id'];
			$temp_plant->populate_dumb_values();
			$plant['details_url'] = $temp_plant->info['details_url'];

			$html = addslashes('<div class="plant_this_now_result"><a href="'.$plant['details_url'].'" title="'.html_sanitize($plant['common_name']).'" class="gray"><img src="'.$plant['image_path'].'" class="thumbnail" /></a><a href="'.$plant['details_url'].'" title="'.html_sanitize($plant['common_name']).'" class="gray"><span class="uppercase">'.html_sanitize($plant['common_name']).'</span><br />'.html_sanitize($plant['botanical_name']).'<br />Item #'.$plant['item_number'].'</a></div>');

			echo('<script>document.write(\''.$html.'\');</script>');
		}
	}

	function output_plant_this_now_home(){
		global $plant_list, $temp_plant;
		for($i=0;$i<min(count($plant_list),2);$i++){

		// TEMPORARY LOGIC--SHOW FIRST AND SECOND PLANTS
//		for($i=1;$i<3;$i++){
			$plant = $plant_list[$i];

			// GENERATE SEO-FRIENDLY NAME
			$temp_plant->info['common_name'] = $plant['common_name'];
			$temp_plant->info['id'] = $plant['id'];
			$temp_plant->populate_dumb_values();
			$plant['details_url'] = $temp_plant->info['details_url'];
		?>
			<div class="generic_two_col" style="width:135px;margin:0px 5px;">
				<a href="<?=$plant['details_url']?>" style="color:#3e3b12;text-align:center;display:block;" title="<?=html_sanitize($plant['common_name'])?>" google_event_tag="Home Page - Right Module 5|Click|Plants in the Spotlight">
					<!--<img src="/img/spacer.gif" class="alignLeft" alt="" style="margin-bottom:6px;background:url(<?=str_replace('/sr/','/dt/',$plant['image_path'])?>) center no-repeat;width:64px;height:64px;" />-->
					<img src="/img/spacer.gif" alt="" style="background:url(<?=$plant['image_path']?>) center no-repeat;width:128px;height:154px;display:block;margin:auto;" />
					<span style="margin:auto;display:block;padding-top:2px;"><?=html_sanitize($plant['common_name'])?></span>
				</a>
				<!--
				<div style="float:left;display:inline;width:75px;">
					<a href="<?=$plant['details_url']?>" style="color:#3e3b12;" title="<?=html_sanitize($plant['common_name'])?>" google_event_tag="Home Page - Right Module 5|Click|Plant This Now">
						<span class="uppercase"><?=html_sanitize($plant['common_name'])?></span><br/><?=html_sanitize($plant['botanical_name'])?>
					</a>
				</div>
				-->
			</div>

		<? }
		}

	?>