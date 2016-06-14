<?php require( dirname(__FILE__) . '/wp-load.php' ); ?>
<?php include_once( dirname(__FILE__) . '/wp-config.php'); ?>
<?php


function email_export_string($wish_list_title,$plants,$version = 'email'){

			set_time_limit(60*3);	// ALLOW UP TO THREE MINUTES FOR EXPORT

			include_once($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/monrovia/includes/classes/class_plant.php');

			if($version=='print'){
				$template_main = file_get_contents('email_templates/wish_list_print.htm');
				$template_items = file_get_contents('email_templates/wish_list_items_print.htm');
			}else{
				$template_main = file_get_contents('email_templates/wish_list.htm');
				$template_items = file_get_contents('email_templates/wish_list_items.htm');
			}

			$html_items = '';

			foreach($plants as $item){
				$plant = new plant($item['pid']);
				//$plant->get_images();
				$plant->get_primary_image();

				$temp = $template_items;
				$common_name = strtoupper($plant->info['common_name']);
				$common_name = str_replace('&TRADE;','&trade;',$common_name);
				$common_name = str_replace('&REG;','&reg;',$common_name);
				$common_name = str_replace('&#174;','&reg;',$common_name);
				$common_name = str_replace('&#153;','&trade;',$common_name);

				$temp = str_replace('{common_name}','<b>'.$common_name.'</b><br />',$temp);
				if(isset($plant->info['botanical_name'])&&$plant->info['botanical_name']!='') $temp = str_replace('{botanical_name}','<i>'.$plant->info['botanical_name'].'</i>',$temp);

				$temp = email_export_fieldset($temp,'Monrovia Item #: ','item_number',isset($plant->info['item_number'])?$plant->info['item_number']:'',$version);
				$temp = email_export_fieldset($temp,'Cold Zones: ','cold_zones',isset($plant->info['cold_zones_friendly'])?$plant->info['cold_zones_friendly']:'',$version);
				$temp = email_export_fieldset($temp,'Light Exposure: ','sun_exposures',isset($plant->info['sun_exposures_friendly'])?$plant->info['sun_exposures_friendly']:'',$version);
				$temp = email_export_fieldset($temp,'Flower Color: ','flower_color',isset($plant->info['flower_color'])?$plant->info['flower_color']:'',$version);
				$temp = email_export_fieldset($temp,'Bloom Time: ','flowering_time',isset($plant->info['flowering_time'])?$plant->info['flowering_time']:'',$version);
				$temp = email_export_fieldset($temp,'Water Needs: ','water_requirements',isset($plant->info['water_requirements'])?$plant->info['water_requirements']:'',$version);
				$temp = email_export_fieldset($temp,'Your Notes: ','notes',isset($item['notes'])?$item['notes']:'',$version);

				$temp = str_replace('{plant_details_url}',$plant->info['details_url'],$temp);

				$image_path = '';
				if(isset($plant->info['image_primary'])) $image_path = $plant->info['image_primary']->info['path_search_result'];
				if($image_path=='') $image_path = 'http://www.monrovia.com/wp-content/uploads/404_sr.gif';

				$temp = str_replace('{plant_image_url}',$image_path,$temp);

				$html_items .= $temp;
			}

			$ret = $template_main;
			$ret = str_replace('{wish_list_items}',$html_items,$ret);
			$ret = str_replace('{wish_list_title}',$wish_list_title,$ret);

			return trim($ret);
		}
		function email_export_fieldset($html,$field_friendly_name,$field_name,$field_value,$version = 'email'){
			$s_replace = '';
			if($field_value!=''){
				if($version=='print'){
					$s_replace = '<div class="field_label">'.$field_friendly_name.'</div><div class="field_value">'.html_sanitize($field_value).'</div><div style="clear:both;"></div>';
				}else{
					$s_replace = '<tr><td width="100" valign="top"><font size="2" face="arial" color="#666666"><nobr>'.$field_friendly_name.'</nobr></font></td><td width="600"><font size="2" face="arial">'.html_sanitize($field_value).'</font></td></tr>';
				}
			}
			return str_replace('{'.$field_name.'}',$s_replace,$html);
		}

	//require($_SERVER['DOCUMENT_ROOT'].'/inc/init.php');
	//$monrovia_user = new monrovia_user($_SESSION['monrovia_user_id']);
	// Set up User Data
	global $current_user;
	get_currentuserinfo();
	
	//Define Title
	$wish_list_title = (($current_user->user_firstname!='')?$current_user->user_firstname.'\'s':'My').' Monrovia Wish List';
	
	// Get user's Wishlist
	$plants = getUserWishlist();
	
	// Email Headers
	$emailHeaders = "Reply-To: noreply@monrovia.com\r\n";
	$emailHeaders .= "Return-Path: noreply@monrovia.com\r\n";
	$emailHeaders .= "From: Monrovia <noreply@monrovia.com>\r\n";
	$emailHeaders .= 'Signed-by: monrovia.com\r\n"';
	$emailHeaders .= 'MIME-Version: 1.0' . "\r\n";
	$emailHeaders .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

	if(count($plants)>0){
		$html = email_export_string($wish_list_title, $plants, '');
	if(wp_mail( $current_user->user_email, $wish_list_title, $html, $emailHeaders )){ echo 'success';
	} else { echo 'fail'; } 
	}else{
		echo'fail';
	}
?>