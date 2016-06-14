<?php
set_time_limit(3600);
require( '../wp-load.php' );
include_once( '../wp-config.php');
require_once('../inc/class_plant.php');

//require($_SERVER['DOCUMENT_ROOT'].'/inc/init.php');
global $current_user;
get_currentuserinfo();
//$email = $current_user->user_email;

$_exp_path = "export_auto/";
$_exp_filename = date("Y-m-d--H-i-s", time()) . ".xls";
$_is_cron = isset($_GET['exbycron']) ? $_GET['exbycron'] : "";
$_max_file = 3;
$_email = "wayne.le2k10@gmail.com";
$_export_flag = update_option( 'monrovia_export_flag', '2' );
// ENSURE USER HAS PERMISSIONS
// Only Allow Admins For the Main Sections
if(check_user_role("administrator", $current_user->ID )  || $_is_cron == '1'){ 
	
	function export_plant_csv($email = null){
		try {
			$_exp_path = "export_auto/";
			$_exp_filename = date("Y-m-d--H-i-s", time()) . ".xls";
			$_is_cron = isset($_GET['exbycron']) ? $_GET['exbycron'] : "";
			$_max_file = 3;
			$_email = "wayne.le2k10@gmail.com";
			$_export_flag = update_option( 'monrovia_export_flag', '2' );
			if ($email == null) {
				$email = $_email;
			}
			//echo('<div style="font-family:tahoma;font-size:11pt;">Generating plant database export. An email will be sent to ' . $monrovia_user->info['email_address'] . ' once it\'s ready. To cancel, press Esc and close this tab.');
			//flush();

			$field_ids = explode(',','is_active,is_new,item_number,types_friendly,deciduous_evergreen,collection_name,common_name,botanical_name,trademark_name,synonym,botanical_family,botanical_genus,botanical_species,botanical_subspecies,botanical_cultivar,phonetic_spelling,is_monrovia_exclusive,special_third_party,release_status,patent,patent_act,year_introduced,geographical_origin,buy_now_url,subcategory,description_design,description_benefits,description_lore,description_history,description_companion_plants,description_catalog,primary_attribute,garden_styles_friendly,special_features_friendly,problem_solutions_friendly,landscape_uses_friendly,attributes,foliage_color,foliage_color_under,foliage_color_new,foliage_color_winter,foliage_color_spring,foliage_color_summer,foliage_color_fall,description_foliage,foliage_shape,flower_color,flowering_time,flowering_seasons_friendly,flower_attributes_friendly,description_flower,growth_habits_friendly,growth_rate,fertilizer[\'name\'],spread,height,pruning_time,average_landscape_size,propagation,description_care,growth_habit,water_requirement,water_requirement_details,sun_exposures_friendly,cold_zone_low,cold_zone_high,sunset_zones_friendly');

			$template_rows = <<<HTML
				<tr>
					<td>{is_active}</td>
					<td>{is_new}</td>
					<td>{item_number}</td>
					<td>{types_friendly}</td>
					<td>{deciduous_evergreen}</td>
					<td>{collection_name}</td>
					<td>{common_name}</td>
					<td>{botanical_name}</td>
					<td>{trademark_name}</td>
					<td>{synonym}</td>
					<td>{botanical_family}</td>
					<td>{botanical_genus}</td>
					<td>{botanical_species}</td>
					<td>{botanical_subspecies}</td>
					<td>{botanical_cultivar}</td>
					<td>{phonetic_spelling}</td>
					<td>{is_monrovia_exclusive}</td>
					<td>{special_third_party}</td>
					<td>{release_status}</td>
					<td>{patent}</td>
					<td>{patent_act}</td>
					<td>{year_introduced}</td>
					<td>{geographical_origin}</td>
					<td>{buy_now_url}</td>
					<td>{subcategory}</td>
					<td>{description_design}</td>
					<td>{description_benefits}</td>
					<td>{description_lore}</td>
					<td>{description_history}</td>
					<td>{description_companion_plants}</td>
					<td>{description_catalog}</td>
					<td>{primary_attribute}</td>
					<td>{garden_styles_friendly}</td>
					<td>{special_features_friendly}</td>
					<td>{problem_solutions_friendly}</td>
					<td>{landscape_uses_friendly}</td>
					<td>{attributes}</td>
					<td>{foliage_color}</td>
					<td>{foliage_color_under}</td>
					<td>{foliage_color_new}</td>
					<td>{foliage_color_winter}</td>
					<td>{foliage_color_spring}</td>
					<td>{foliage_color_summer}</td>
					<td>{foliage_color_fall}</td>
					<td>{description_foliage}</td>
					<td>{foliage_shape}</td>
					<td>{flower_color}</td>
					<td>{flowering_time}</td>
					<td>{flowering_seasons_friendly}</td>
					<td>{flower_attributes_friendly}</td>
					<td>{description_flower}</td>
					<td>{growth_habits_friendly}</td>
					<td>{growth_rate}</td>
					<td>{fertilizer['name']}</td>
					<td>{spread}</td>
					<td>{height}</td>
					<td>{pruning_time}</td>
					<td>{average_landscape_size}</td>
					<td>{propagation}</td>
					<td>{description_care}</td>
					<td>{growth_habit}</td>
					<td>{water_requirement}</td>
					<td>{water_requirement_details}</td>
					<td>{sun_exposures_friendly}</td>
					<td>{cold_zone_low}</td>
					<td>{cold_zone_high}</td>
					<td>{sunset_zones_friendly}</td>
				</tr>
HTML;

			$export_id = date('Ymd');
			$file_handler = fopen($_exp_path.$_exp_filename, 'w+');
			if(!$file_handler) die('fail');
			//if(!$file_handler) die('<br /><br /><b>Error: Could not write to export file. Please make sure no one else is currently running an export.</b>');
			fwrite($file_handler,file_get_contents('inc/templates/plant_export.htm'));
			$_sql = "SELECT id FROM plants";
			$result = mysql_query($_sql);
			$num_rows = mysql_num_rows($result);

			for($i=0;$i<$num_rows;$i++){
				//set_time_limit(5); // ALLOW UP TO 5 SECONDS PER PLANT RECORD

				$id = mysql_result($result,$i,"id");
				$record = new plant($id);
				$html = $template_rows;
				foreach($field_ids as $field_id){

					switch($field_id){
						case 'fertilizer[\'name\']':
							$html = str_replace('{'.$field_id.'}',(isset($record->info['fertilizer'])?html_sanitize($record->info['fertilizer']->name):''),$html);
							break;
						case 'is_new':
							$html = str_replace('{'.$field_id.'}',($record->info[$field_id]=='1')?'Yes':'No',$html);
							break;
						case 'is_monrovia_exclusive':
							$html = str_replace('{'.$field_id.'}',($record->info[$field_id]=='1')?'Yes':'No',$html);
							break;
						case 'is_active':
							$html = str_replace('{'.$field_id.'}',($record->info[$field_id]=='1')?'Yes':'No',$html);
							break;
						case 'item_number':
							$html = str_replace('{'.$field_id.'}','#'.$record->info[$field_id],$html);
							break;
						case 'year_introduced':
							$year = $record->info[$field_id];
							if($year!='0000'){
								$html = str_replace('{'.$field_id.'}',$year,$html);
							}else{
								$html = str_replace('{'.$field_id.'}','',$html);
							}
							break;
						default:
							$html = str_replace('{'.$field_id.'}',(isset($record->info[$field_id]))?html_sanitize($record->info[$field_id]):'',$html);
					}
				}
				fwrite($file_handler,$html);
			}

			fwrite($file_handler,'</table></body></html>');
			fclose($file_handler);

			// $url = 'http://'.$_SERVER['HTTP_HOST'].'/monrovia_admin/export_plants.php?export_id='.$export_id;
			
			// $emailHeaders = "Reply-To: noreply@monrovia.com\r\n";
			// $emailHeaders .= "Return-Path: noreply@monrovia.com\r\n";
			// $emailHeaders .= "From: Monrovia <noreply@monrovia.com>\r\n";
			// $emailHeaders .= 'Signed-by: monrovia.com\r\n"';
			// $emailHeaders .= 'MIME-Version: 1.0' . "\r\n";
			// $emailHeaders .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

			// wp_mail( $email, 'Monrovia plant database export','<a href="'.$url.'">'.$url.'</a>', $emailHeaders );
			update_option('monrovia_export_flag', '0');
			echo('success');
		} catch (Exception $e) {
			update_option('monrovia_export_flag', '1');
			error_log($e->getMessage());
		}
	}
	
	/* function download_export($export_id,$part = 0){
		$file_name = 'plant_database_'.$part. '_' . $export_id . '.xls';
		$file_path = 'export/' . $file_name;

		// OUTPUT FORCE-DOWNLOAD HEADER
		header("Pragma:public");
		header("Expires:0");
		header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
		header("Cache-Control:private",false);
		header("Content-Type:application/vnd.ms-excel");
		header("Content-Disposition:attachment;filename=\"".$file_name);
		header("Content-Transfer-Encoding:binary");
		header("Content-Length:".filesize($file_path));
		header('location:'.$file_path);
	} */

	/* $export_id = '';
	if (isset($_POST['part'])) {
		$_part = $_POST['part'];
	} else if (isset($_GET['part'])) {
		$_part = $_GET['part'];
	} else {
		$_part = 0;
	}
	if(isset($_GET['export_id'])) $export_id = $_GET['export_id'];
	if($export_id==''){
		export_plant_csv($email,$_part);
	}else{
		download_export($export_id,$_part);
	} */
	export_plant_csv();
} else {
	header("Location:/");
}
?>