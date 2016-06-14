<?php require( dirname(__FILE__) . '/wp-load.php' ); ?>
<?php include_once( dirname(__FILE__) . '/wp-config.php'); ?>
<?php


function xls_export_string($data){

			set_time_limit(60*3);	// ALLOW UP TO THREE MINUTES FOR EXPORT

			require_once('inc/class_plant.php');

			$ret = '<html><table border="1"><tr><td colspan="2" bgcolor="#ffff00"><center><b>MONROVIA</b></center></td><td colspan="2" bgcolor="#ffcc99"><center><b>PLANT NAME</b></center></td><td colspan="2" bgcolor="#ccffff"><center><b>HARDINESS ZONE</b></center></td><td bgcolor="#cc99ff"><center><b>MATURE PLANT</b></center></td><td colspan="3" bgcolor="#ff9900"><center><b>FOLIAGE</b></center></td><td colspan="3" bgcolor="#3366ff"><center><b>FLOWER</b></center></td><td colspan="3" bgcolor="#c0c0c0"><center><b>CULTURAL</b></center></td><td bgcolor="#00aa00"><center><b>OTHER</b></center></td></tr><tr><td><center><b>Monrovia Item #</b></center></td><td><center><b>Introduction Year</b></center></td><td><center><b>Botanical Name</b></center></td><td><center><b>Common Name</b></center></td><td><center><b>Cold Hardiness Zone</b></center></td><td><center><b>Sunset Hardiness Zone</b></center></td><td><center><b>Growth Habit, Mature Landscape Height and Width</b></center></td><td><center><b>Spring Foliage Color</b></center></td><td><center><b>Fall Foliage Color</b></center></td><td><center><b>Foliage Shape</b></center></td><td><center><b>Flower Color</b></center></td><td><center><b>Bloom Time</b></center></td><td><center><b>Ornamental or Edible Fruit</b></center></td><td><center><b>Origin</b></center></td><td><center><b>Light Exposure</b></center></td><td><center><b>Water Needs</b></center></td><td><center><b>Your Notes</b></center></td></tr>';

			foreach($data as $key => $item){

				$plant = new plant($item['pid']);

				$year_introduced = ($plant->info['year_introduced']!='0')?$plant->info['year_introduced']:'';
				$sunset_zones = ($plant->info['sunset_zones_friendly']!='')?'Zones '.$plant->info['sunset_zones_friendly']:'';
				$cold_zones = ($plant->info['cold_zones_friendly']!='')?'Zones '.$plant->info['cold_zones_friendly']:'';

				$ret .= '<tr><td>'.$plant->info['item_number'].'</td><td>'.$year_introduced.'</td><td>'.$plant->info['botanical_name'].'</td><td>'.$plant->info['common_name'].'</td><td>'.$cold_zones.'</td><td>'.$sunset_zones.'</td><td>'.$plant->info['average_landscape_size'].'</td><td>'.$plant->info['foliage_color_spring'].'</td><td>'.$plant->info['foliage_color_fall'].'</td><td>'.$plant->info['foliage_shape'].'</td><td>'.$plant->info['flower_color'].'</td><td>'.$plant->info['flowering_time'].'</td><td>'.$plant->info['Ornamental or Edible Fruit'].'</td><td>'.$plant->info['geographical_origin'].'</td><td>'.$plant->info['sun_exposures_friendly'].'</td><td>'.$plant->info['water_requirement_details'].'</td><td>'.$item['notes'].'</td></tr>';
			}
			$ret .= '</table></html>';
			return $ret;
		}
        ?>
<?php
// Uncomment these to Turn on Errors for this page
	//ini_set('display_errors','on');
	//error_reporting(E_ALL);
//get_header(); 

	ini_set('zlib.output_compression','Off');

	$file_name = "monrovia_favorite_list.xls";
	
	$plants = getUserWishlist();
	foreach($plants as $plant){ 
		$data[] = $plant;
		$notes[] = $plant['notes'];
	}

	//print_r($data);
	if(count($data)>0){

		$file_path = $_SERVER['DOCUMENT_ROOT'].'/temp.xls';
		file_put_contents($file_path,xls_export_string($data));

		// OUTPUT FORCE-DOWNLOAD HEADER
		header("Pragma:public");
		header("Expires:0");
		header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
		header("Cache-Control:private",false);
		header("Content-Type:application/vnd.ms-excel");
		header("Content-Disposition:attachment;filename=".$file_name);
		header("Content-Transfer-Encoding:binary");
		header("Content-Length:".filesize($file_path));

		// OUTPUT FILE CONTENTS
		$file = @fopen($file_path,'rb');
		if($file){
			fpassthru($file); 
		} 
	}else{
		echo('An error occurred. Please make sure you are logged in.');
	}
?>