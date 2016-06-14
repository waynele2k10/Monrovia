<?
	$plant_savvy_interests = array();
	$plant_savvy_interests[] = array('abbreviation'=>'interestBirds','friendly_name'=>'Attracting birds to my garden');
	$plant_savvy_interests[] = array('abbreviation'=>'interestContainer','friendly_name'=>'Container gardening outdoors');
	$plant_savvy_interests[] = array('abbreviation'=>'interestDrought','friendly_name'=>'Drought-resistant plants');
	$plant_savvy_interests[] = array('abbreviation'=>'interestLandscaping','friendly_name'=>'Landscaping');
	$plant_savvy_interests[] = array('abbreviation'=>'interestRegionIssues','friendly_name'=>'Issues that affect my region');
	$plant_savvy_interests[] = array('abbreviation'=>'interestHowTo','friendly_name'=>'How-to demonstrations');
	$plant_savvy_interests[] = array('abbreviation'=>'interestIndoorGardening','friendly_name'=>'Indoor gardening');
	$plant_savvy_interests[] = array('abbreviation'=>'interestShadeGardening','friendly_name'=>'Shade gardening');
	$plant_savvy_interests[] = array('abbreviation'=>'interestFullSunGardening','friendly_name'=>'Full-sun gardening');
	$plant_savvy_interests[] = array('abbreviation'=>'interestInspiration','friendly_name'=>'Inspiration and ideas for my garden');
	$plant_savvy_interests[] = array('abbreviation'=>'interestNewPlants','friendly_name'=>'New plants');
	$plant_savvy_interests[] = array('abbreviation'=>'interestYearRoundGardening','friendly_name'=>'Year-round gardening');
	$plant_savvy_interests[] = array('abbreviation'=>'interestPlantNutrition','friendly_name'=>'Plant nutrition');
	$plant_savvy_interests[] = array('abbreviation'=>'interestPruning','friendly_name'=>'Pruning');
	$plant_savvy_interests[] = array('abbreviation'=>'interestEcoFriendly','friendly_name'=>'Eco-friendly gardening');
	$plant_savvy_interests[] = array('abbreviation'=>'interestCottageGarden','friendly_name'=>'Cottage garden');
	$plant_savvy_interests[] = array('abbreviation'=>'interestContemporary','friendly_name'=>'Contemporary garden');
	$plant_savvy_interests[] = array('abbreviation'=>'interestTropical','friendly_name'=>'Tropical garden');
	$plant_savvy_interests[] = array('abbreviation'=>'interestZenGarden','friendly_name'=>'Zen garden');
	$plant_savvy_interests[] = array('abbreviation'=>'interestClassicGarden','friendly_name'=>'Classic garden');
	$plant_savvy_interests[] = array('abbreviation'=>'interestLatestTrends','friendly_name'=>'Latest trends');
?>
<h3>i'm interested in:</h3>
<form action="" id="form_interests" method="POST">
	<div>
		<?
			foreach($plant_savvy_interests as &$interest){
				?>
					<div class="interestItem"><input type="checkbox" name="interestItems[]" value="<?=$interest['abbreviation']?>" id="<?=$interest['abbreviation']?>" <? if(strpos('|'.$monrovia_user->info['newsletter_interests'].'|','|'.str_replace('interest','',$interest['abbreviation']).'|')!==false) echo('checked') ?> /><label for="<?=$interest['abbreviation']?>"><?=$interest['friendly_name']?></label></div>
				<?
			}
		?>
	</div>
	<div class="clear"></div>
	<p>
		<div style="margin-bottom:8px;">I would like to receive these versions of the Plant Savvy monthly newsletter:</div>
		<div class="interestItem"><input type="checkbox" name="newsletterVersions[]" value="versionWarmClimate" id="versionWarmClimate" <? if(strpos('|'.$monrovia_user->info['newsletter_versions'].'|','|versionWarmClimate|')!==false) echo('checked') ?> /><label for="versionWarmClimate" style="font-size:9pt;">Warm-climate version</label></div>
		<div class="interestItem"><input type="checkbox" name="newsletterVersions[]" value="versionColdClimate" id="versionColdClimate" <? if(strpos('|'.$monrovia_user->info['newsletter_versions'].'|','|versionColdClimate|')!==false) echo('checked') ?> /><label for="versionColdClimate" style="font-size:9pt;">Cold-climate version</label></div>
	</p>
	<div class="clear"></div>

	<?	// USER IS LOGGED ON...
		if($monrovia_user->info['id']!=''){ ?>
		<div style="width:120px;margin-top:6px;float:right;" class="btn_green">
			<img class="side_left" src="/img/spacer.gif"/><a href="#" onclick="$('form_interests').submit();"><?=(($monrovia_user->info['newsletter']=='1')?'save changes':'subscribe')?></a><img src="/img/spacer.gif"/>
		</div>
		<div class="clear"></div>
		<div class="btn_green" style="width:90px;float:right;margin-top:8px;">
			<img src="/img/spacer.gif" class="side_left" /><a href="./">cancel</a><img src="/img/spacer.gif" />
		</div>
	<? }else{ ?>
		<div style="width:120px;margin-top:6px;float:right;" class="btn_green">
			<img class="side_left" src="/img/spacer.gif"/><a href="#" onclick="$('form_interests').submit();">continue</a><img src="/img/spacer.gif"/>
		</div>
	<? } ?>

	<div class="clear"></div>
	<a href="/img/privacy_statement.gif" target="_blank" class="lightview">read our privacy statement</a>
	<input name="action" value="set_interests" type="hidden" />
</form>