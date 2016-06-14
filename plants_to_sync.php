<?php set_time_limit(0);
include_once( 'wp-config.php');
$_mage = $_GET['mage'];
if ($_mage == 'monrovia19') {
	$_sql = "SELECT 
plants.id AS 'id',
plants.common_name AS 'name',
plants.botanical_name AS 'botanical_name',
plants.phonetic_spelling AS 'botanical_pronunciation',
plants.average_landscape_size AS 'average_size',
plants.flowering_time AS 'bloom_time',
plants.patent_act AS 'patent_act',
list_fertilizer.name AS 'soil_needs',
plants.water_requirement_details AS 'water_needs',
plants.item_number AS 'item_number',
list_deciduous_evergreen.name AS 'deciduous_evergreen',
list_flower_color.name AS 'flower_color',
list_foliage_color.name AS 'foliage_color',
list_growth_rate.name AS 'growth_rate',
list_water_requirement.name AS 'water_icons',
plants.primary_attribute AS 'key_feature',
plants.description_catalog AS 'description',
plants.keywords AS 'meta_keyword',
plants.description_care AS 'care_instructions',
plants.description_lore AS 'lore',
plants.description_design AS 'design_ideas',
plants.cold_zone_low AS 'cold_zone_low',
plants.cold_zone_high AS 'cold_zone_high',
GROUP_CONCAT(DISTINCT list_sun_exposure.name SEPARATOR ';') AS 'light_needs',
GROUP_CONCAT(DISTINCT list_flowering_season.name SEPARATOR ';') AS 'flowering_season',
GROUP_CONCAT(DISTINCT list_flower_attribute.name SEPARATOR ';') AS 'flower_attribute',
GROUP_CONCAT(DISTINCT list_garden_style.name SEPARATOR ';') AS 'garden_style',
GROUP_CONCAT(DISTINCT list_landscape_use.name SEPARATOR ';') AS 'landscape_use',
GROUP_CONCAT(DISTINCT list_special_feature.name SEPARATOR ';') AS 'special_feature',
GROUP_CONCAT(DISTINCT list_growth_habit.name SEPARATOR ';') AS 'growth_habit'
FROM plants
LEFT JOIN list_fertilizer
ON plants.fertilizer_id = list_fertilizer.id
LEFT JOIN list_deciduous_evergreen
ON plants.deciduous_evergreen_id = list_deciduous_evergreen.id
LEFT JOIN list_flower_color
ON plants.flower_color_id = list_flower_color.id
LEFT JOIN list_foliage_color
ON plants.foliage_color_id = list_foliage_color.id
LEFT JOIN list_growth_rate
ON plants.growth_rate_id = list_growth_rate.id
LEFT JOIN list_water_requirement
ON plants.water_requirement_id = list_water_requirement.id
LEFT JOIN plant_sun_exposure_plants
	JOIN list_sun_exposure
	ON list_sun_exposure.id = plant_sun_exposure_plants.sun_exposure_id
ON plant_sun_exposure_plants.plant_id = plants.id
LEFT JOIN plant_flowering_season_plants
	JOIN list_flowering_season
	ON list_flowering_season.id = plant_flowering_season_plants.flowering_season_id
ON plant_flowering_season_plants.plant_id = plants.id
LEFT JOIN plant_flower_attribute_plants
	JOIN list_flower_attribute
	ON list_flower_attribute.id = plant_flower_attribute_plants.flower_attribute_id
ON plant_flower_attribute_plants.plant_id = plants.id
LEFT JOIN plant_garden_style_plants
	JOIN list_garden_style
	ON list_garden_style.id = plant_garden_style_plants.garden_style_id
ON plant_garden_style_plants.plant_id = plants.id
LEFT JOIN plant_landscape_use_plants
	JOIN list_landscape_use
	ON list_landscape_use.id = plant_landscape_use_plants.landscape_use_id
ON plant_landscape_use_plants.plant_id = plants.id
LEFT JOIN plant_special_feature_plants
	JOIN list_special_feature
	ON list_special_feature.id = plant_special_feature_plants.special_feature_id
ON plant_special_feature_plants.plant_id = plants.id
LEFT JOIN plant_growth_habit_plants
	JOIN list_growth_habit
	ON list_growth_habit.id = plant_growth_habit_plants.growth_habit_id
ON plant_growth_habit_plants.plant_id = plants.id
WHERE plants.synch_with_mage=1 
AND plants.item_number <> ''
GROUP BY plants.id
ORDER BY last_modified DESC LIMIT 40";
	$result = mysql_query($_sql);
	$num_rows = mysql_num_rows($result);
	$_plants = array();
	if ($num_rows > 0) {
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$_plants[] = $row;
			mysql_query("UPDATE plants SET synch_with_mage=0 WHERE id=" . $row['id']);
		}
		echo json_encode($_plants);
	}
}
