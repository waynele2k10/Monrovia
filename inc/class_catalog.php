<?php
	ini_set('memory_limit','512M');

	
	require_once($_SERVER['DOCUMENT_ROOT'].'/wp-content/themes/monrovia/includes/classes/class_record.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fpdf/fpdf.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/inc/fpdf/fpdi.php');
	require_once('class_xml.php');
	?>
    <?php

	define('PAGE_WIDTH',215); // UNITS ARE IN mm
	define('PAGE_HEIGHT',277); // UNITS ARE IN mm

	define('TEMP_THUMBNAIL_WIDTH',465); // UNITS ARE IN px
	define('TEMP_THUMBNAIL_HEIGHT',599); // UNITS ARE IN px
	define('THUMBNAIL_WIDTH',154); // UNITS ARE IN px
	define('THUMBNAIL_HEIGHT',197); // UNITS ARE IN px
	
	class catalog extends record {
		// THIS IS SIMPLY A LIST OF ALL COLUMNS FROM press_releases TABLE, EXCEPT FOR id
		var $table_fields = 'user_id,name,title,is_official_catalog,original_catalog_id,download_path,date_created,date_last_modified,template_id,plant_1_image_set_id,plant_2_image_set_id,plant_3_image_set_id,customer,customer_contact,sales_rep_name,additional_info_1,additional_info_2,additional_info_3,monrovia_locations,plant_count,plant_ids';

		function catalog($record_id = ''){
			$this->table_name = 'catalogs';
			$this->plants = array();
			$this->sorting_method = 'custom';                                 
			
			if($record_id!='') $this->load($record_id);                        
            
			if(isset($this->info['plant_ids'])) $this->info['plant_ids'] = trim($this->info['plant_ids'],',');
			if(isset($this->info['monrovia_locations'])) $this->info['monrovia_locations'] = trim($this->info['monrovia_locations'],',');
			//$this->get_plants();
			
			$this->info['cover_image_paths'] = array(
				'front'	=> '',
				'back'	=> ''
			);
            
                        
			if((!isset($this->info['id'])||$this->info['id']=='')||(isset($this->info['template_id'])&&intval($this->info['template_id']>0))){
				// NEW CATALOG, OR CATALOG IS OFFICIAL CATALOG, OR CATALOG WAS CREATED FROM SCRATCH, USING A TEMPLATE XML FILE				
				if(isset($this->info['is_official_catalog'])&&$this->info['is_official_catalog']=='1'){
						$this->info['cover_image_paths'] = array(
							'front'	=> '/pdfcatalogs/pdfs/'.$this->info['id'].'/thumbnail.gif',
							'back'	=> ''
						);
				}else{
					if(!isset($this->info['id'])||$this->info['id']==''){
						// NEW CATALOG
						$this->info['template_id'] = 1;
						$this->info['monrovia_locations'] = 'azusa,visalia,dayton,cairo,granby';
						$this->info['cover_image_paths'] = array(
							'front'	=> '/pdfcatalogs/templates/'.$this->info['template_id'].'_thumbnail_front.gif',
							'back'	=> '/pdfcatalogs/templates/'.$this->info['template_id'].'_thumbnail_back.gif'
						);
					}else{
						$base_path = $_SERVER['DOCUMENT_ROOT'].'/downloads/pdf/custom_catalogs/'.$this->info['id'];
						if(!is_dir($base_path)) mkdir($base_path);

						$this->cover_paths = array(
							'front'=>$base_path.'/front.pdf',
							'back'=>$base_path.'/back.pdf'
						);
						$this->info['cover_image_paths'] = array(
							'front'	=> '/downloads/pdf/custom_catalogs/'.$this->info['id'].'/thumbnail_front.jpg',
							'back'	=> '/downloads/pdf/custom_catalogs/'.$this->info['id'].'/thumbnail_back.jpg'
						);
					}				
				}
			}else{
				// DETERMINE COVER THUMBNAIL PATHS
				if(isset($this->info['original_catalog_id'])&&$this->info['original_catalog_id']!=''&&$this->info['original_catalog_id']!='0'&&$this->info['original_catalog_id']!=$this->info['id']){
					// CATALOG STARTED AS A COPY OF ANOTHER
					$created_from_catalog = new catalog($this->info['original_catalog_id']);
					if($created_from_catalog->info['id']!=''){
						$this->info['cover_image_paths'] = array(
							'front'	=> '/pdfcatalogs/pdfs/'.$this->info['original_catalog_id'].'/thumbnail.gif',
							'back'	=> ''
						);
						$this->info['created_from_name'] = $created_from_catalog->info['name'];
					}
					$thumbnail_catalog_id = $this->info['original_catalog_id'];
				}else{
					// CATALOG DID NOT START AS A COPY OF ANOTHER
					if(isset($this->info['is_official_catalog'])&&$this->info['is_official_catalog']=='1'){
						$this->info['cover_image_paths'] = array(
							'front'	=> '/pdfcatalogs/pdfs/'.$this->info['id'].'/thumbnail.gif',
							'back'	=> ''
						);
					}
					
					if(isset($this->info['id'])) $thumbnail_catalog_id = $this->info['id'];
				}
				
				if($this->info['cover_image_paths']['front']==''){
					// CATALOG WAS CREATED FROM SCRATCH, WITHOUT USING A TEMPLATE XML FILE
					$this->info['cover_image_paths']['front'] = '/pdfcatalogs/pdfs/generic/thumbnail.gif';
					$thumbnail_catalog_id = 'generic';
				}

				$this->cover_paths = array(
					'front'=>$_SERVER['DOCUMENT_ROOT'].'/pdfcatalogs/pdfs/'.$thumbnail_catalog_id.'/beginning.pdf',
					'back'=>$_SERVER['DOCUMENT_ROOT'].'/pdfcatalogs/pdfs/'.$thumbnail_catalog_id.'/end.pdf'
				);			
			}
			
			// GENERATE THUMBNAILS IF A TEMPLATED CATALOG AND THUMBNAILS DON'T EXIST
			if(isset($this->info['id'])&&$this->info['id']!=''&&isset($this->info['template_id'])&&intval($this->info['template_id'])>0&&$this->info['is_official_catalog']!='1'){
				$path_front = $_SERVER['DOCUMENT_ROOT'].'/downloads/pdf/custom_catalogs/'.$this->info['id'].'/thumbnail_front.jpg';
				$path_back = $_SERVER['DOCUMENT_ROOT'].'/downloads/pdf/custom_catalogs/'.$this->info['id'].'/thumbnail_back.jpg';
				if(!file_exists($path_front)||!file_exists($path_back)){
					$this->generate_covers(true);
					$this->delete_cover_pdfs();
				}
			}
			
		}

		function get_plant_count(){
			if(isset($this->info['plant_ids'])){
				$this->info['plant_ids'] = trim($this->info['plant_ids'],',');
			}else{
				$this->info['plant_ids'] = '';
			}
			if($this->info['plant_ids']!=''){
				$this->info['plant_count'] = count(explode(',',$this->info['plant_ids']));
			}else{
				$this->info['plant_count'] = 0;			
			}
		}

		function save(){
			//$cover_image_path = $this->info['cover_image_path_front'];
			
			// DON'T SAVE COVER IMAGE IF CUSTOM CATALOG
			//if($this->info['is_official_catalog']!='1') $this->info['cover_image_path'] = '';
			
			$this->get_plant_count();
			
			if(!isset($this->info['id'])||$this->info['id']=='') $this->info['date_created'] = date('Y-m-d H:i:s');
			$this->info['date_last_modified'] = date('Y-m-d H:i:s');
			$ret = parent::save();
			
			//$this->info['cover_image_path'] = $cover_image_path;
			
			// IF TEMPLATED CATALOG, REGENERATE THUMBNAILS
			if(isset($this->info['template_id'])&&intval($this->info['template_id'])>0) $this->generate_covers(true);
			
			return $ret;
		}
		
		function delete(){		
			try {
				$directory = $_SERVER['DOCUMENT_ROOT'].'/downloads/pdf/custom_catalogs';
				$base_path = $directory . '/'.$this->info['id'];
				
				// DELETE FOLDER (CATALOGS WITH TEMPLATE XML FILES ONLY)
				if(is_dir($base_path)){
					if($directory_handle = opendir($base_path)){
						while(($file_name = readdir($directory_handle))!==false){
							if($file_name!=''&&$file_name!='.'&&$file_name!='..') unlink("$base_path/$file_name");
						}
						closedir($directory_handle);
					}
					@rmdir($base_path);
				}
				
				if(is_dir($directory)){
					if($directory_handle = opendir($directory)){
						while(($file_name = readdir($directory_handle))!==false){
							if(strpos($file_name,'.pdf')!==false){
								$segments = (explode('_',$file_name));
								if($segments[0]==$this->info['id']) unlink("$directory/$file_name");
							}
						}
						closedir($directory_handle);
					}
				}
				//return true;
			}catch(Exception $err){
				//echo('error: delete');
				//return false;
			}
			
			return parent::delete();			
		}
						
		function get_plants(){
			$this->plants = array();
			if($this->info['plant_ids']!=''){

				switch($this->sorting_method){
					case 'botanicalname':
						$sorting_method = 'botanical_name';
						break;
					case 'commonname':
						$sorting_method = 'common_name';
						break;
					case 'collectionname':
						$sorting_method = 'collection_name="Dan Hinkley" DESC,collection_name="Itoh Peonies" DESC,collection_name="Edibles" DESC,collection_name="Succulents" DESC,collection_name="Proven Winners" DESC,collection_name IN("Distinctively Better","",NULL) DESC,collection_name="Distinctively Better Perennials" DESC,botanical_name';
						break;
					case 'itemnumber':
						$sorting_method = 'item_number';
						break;
					case 'custom':
					default:
						$sorting_method = 'FIELD(plants.id,'.trim($this->info['plant_ids'],',').')';
				}
				
				// THESE RELEASE STATUSES ARE ALLOWED IN CUSTOM CATALOGS: A (Active), NA (New/Active), NI (New/Inactive), F (Future)
				$result = mysql_query('SELECT id FROM plants WHERE id IN ('.$this->info['plant_ids'].') AND is_active=\'1\' AND release_status_id IN (\'1\',\'2\',\'3\',\'6\') ORDER BY '.$sorting_method);
				$num_rows = intval(mysql_num_rows($result));

				if($num_rows>0){
					for($i=0;$i<$num_rows;$i++){
						$temp = new plant(mysql_result($result,$i,'id'));
						$temp->get_primary_image();
						
						// CATEGORIZE PLANTS NOT BELONGING TO A CATEGORY AS "DISTINCTIVELY BETTER"
						if($temp->info['collection_name']=='') $temp->info['collection_name'] = 'Distinctively Better';
						
						$this->plants[] = $temp;
					}
				}
			}
		}
		
		function generate_pdf($sorting_method,$include_collection=true){
		
			try{
				if(isset($sorting_method)&&$sorting_method!='') $this->sorting_method = $sorting_method;
                $this->include_collection = $include_collection;

				$file_path = '/downloads/pdf/custom_catalogs/'.$this->info['id'].'_'.time().'_'.$this->sorting_method.'.pdf';

				// CLEAN UP			
				delete_old_pdfs();

				if(isset($this->info['template_id'])&&intval($this->info['template_id'])>0) $this->generate_covers(false);

				$this->pdf = new PDF();

				$server_root_path = $_SERVER['DOCUMENT_ROOT'];
				$server_file_path = $server_root_path.$file_path;

				if(file_exists($server_file_path)){
					// PDF ALREADY EXISTS; USE EXISTING
					return array('success'=>true,'path'=>'http://'.$_SERVER['HTTP_HOST']."/".$file_path);
				}else{
					$this->get_plants();
					$result = $this->generate_middle_pdfs();
					if($result===true){
						// PDFs GENERATED SUCCESSFULLY; NOW WE CONCATENATE
						set_time_limit(60 * 50); // ALLOW UP TO 5 MINUTES
						$pdf = new concat_pdf();

						$pdf->setFiles($this->middle_pdfs);
						$pdf->concat();

						/*
						// SET META DATA
						$this->pdf->SetAuthor('Monrovia');
						$this->pdf->SetCreator('Monrovia');
						$this->pdf->SetSubject('Plant Catalog');

						if(isset($this->info['title'])&&$this->info['title']!=''){
							$this->pdf->SetSubject($this->info['title']);
						}else{
							$this->pdf->SetSubject('Plant Catalog');
						}
						*/
						$pdf->Output($server_file_path,'F');

						$this->delete_middle_pdfs();
						$this->delete_cover_pdfs();

						return array('success'=>true,'path'=>'http://'.$_SERVER['HTTP_HOST'].$file_path);

					}else{
						$this->delete_middle_pdfs();
						$this->delete_cover_pdfs();
						return array('success'=>false,'error'=>$result);
					}
				}
				$this->delete_middle_pdfs();
				$this->delete_cover_pdfs();
				return array('success'=>false,'error'=>'');
			}catch(Exception $err){
				return array('success'=>false,'error'=>serialize($err));
			}
		}
		
		function delete_middle_pdfs(){
			for($i=0;$i<count($this->middle_pdfs);$i++){
				if(strpos($this->middle_pdfs[$i],$this->info['id'].'_'.strtotime($this->info['date_last_modified']).'_'.$this->sorting_method.'_part')!==false) @unlink($this->middle_pdfs[$i]);
			}
		}
		
		function delete_cover_pdfs(){
			if(isset($this->info['template_id'])&&intval($this->info['template_id'])>0){
				if(file_exists($this->cover_paths['front'])) unlink($this->cover_paths['front']);
				if(file_exists($this->cover_paths['back'])) unlink($this->cover_paths['back']);
			}
		}
		
		function generate_covers($thumbnails_only){
			$success = true;
			$front_cover = new custom_catalog_cover($this,'front');
			$success = $success&&$front_cover->generate($thumbnails_only);
			$back_cover = new custom_catalog_cover($this,'back');
			$success = $success&&$back_cover->generate($thumbnails_only);
			return $success;
		}
		
		function generate_middle_pdfs(){
			// RETURNS true ON SUCCESS; SERIALIZED Exception OBJ ON FAILURE
				
			set_time_limit(60*5); // ALLOW UP TO 5 MINUTES
				
			$original_catalog_id = intval($this->info['original_catalog_id']);
			if($original_catalog_id==0) $original_catalog_id = 'generic';

			$server_root_path = $_SERVER['DOCUMENT_ROOT']."/";
			
			$this->middle_pdfs = array($this->cover_paths['front']);
	
			try{
				if($this->sorting_method=='collectionname'){

					// ASSUMES PLANTS ALREADY SORTED BY COLLECTION

					if(count($this->plants)>0){
						$collection_name = $this->plants[0]->info['collection_name'];
						$pdf = new PDF();
						$collection_num = 1;
						$file_path = 'downloads/pdf/custom_catalogs/'.$this->info['id'].'_'.strtotime($this->info['date_last_modified']).'_'.$this->sorting_method.'_part'.($collection_num).'.pdf';
						
						// ADD COLLECTION INTRO PAGE
						//check if we need collection intro pages
						if($this->include_collection){
                            $collection_abbreviation = get_collection_abbreviation($collection_name);
                            if($collection_abbreviation!='') $this->middle_pdfs[] = $server_root_path.'pdfcatalogs/pdfs/'.$original_catalog_id.'/collection_'.$collection_abbreviation.'.pdf';
                        }
						for($i=0;$i<count($this->plants);$i++){
						    // WHEN NEW COLLECTION ENCOUNTERED (ONLY FIRES FOR SECOND AND SUBSEQUENT COLLECTIONS)
							if($collection_name!=$this->plants[$i]->info['collection_name']){
    							// PRODUCE PDF, ADD TO LIST
    							$pdf->Output($server_root_path.$file_path,'F');
    							if($pdf->error_msg==''){
    								// PDF CREATED SUCCESSFULLY; ADD PATH TO ARRAY
    								$this->middle_pdfs[] = $server_root_path.$file_path;
    							}else{
    								throw new Exception('Section: collection-plantiteration; Message: ' . $pdf->error_msg);
    							}
    							$pdf = '';
    							// IF NOT LAST ITERATION; START NEW PDF
    							if($i<count($this->plants)){
    								$collection_name = $this->plants[$i]->info['collection_name'];
    								$pdf = new PDF();
    								$collection_num++;
    								$file_path = 'downloads/pdf/custom_catalogs/'.$this->info['id'].'_'.strtotime($this->info['date_last_modified']).'_'.$this->sorting_method.'_part'.($collection_num).'.pdf';
    									
    								//check if we need collection intro pages
                                    if($this->include_collection){
    									$collection_abbreviation = get_collection_abbreviation($collection_name);
    									if($collection_abbreviation!='') $this->middle_pdfs[] = $server_root_path . 'pdfcatalogs/pdfs/'.$original_catalog_id.'/collection_'.$collection_abbreviation.'.pdf';
    								}
    							 }
							 }

							$pdf->output_plant($this->plants[$i]);
						}
						
						// PRODUCE FINAL PDF, ADD TO LIST
						if($pdf!=''){
							$pdf->Output($server_root_path.$file_path,'F');
							if($pdf->error_msg==''){
								// PDF CREATED SUCCESSFULLY; ADD PATH TO ARRAY
								$this->middle_pdfs[] = $server_root_path . $file_path;
							}else{
								throw new Exception('Section: collection-plantfinish; Message: ' . $pdf->error_msg);
							}
						}
					}else{
						// NO PLANTS; THROW EXCEPTION
						throw new Exception('Section: collection-noplants');
					}

				}else{
					// OTHER SORTING
					$pdf = new PDF();
					$file_path = 'downloads/pdf/custom_catalogs/'.$this->info['id'].'_'.strtotime($this->info['date_last_modified']).'_'.$this->sorting_method.'_part1.pdf';
					
					$collection_names = array(
						'Dan Hinkley'=>0,
						'Itoh Peonies'=>0,
						'Edibles'=>0,
						'Succulents'=>0,
						'Proven Winners'=>0,
						'Distinctively Better'=>0,
						'Distinctively Better Perennials'=>0
					);
					
					for($i=0;$i<count($this->plants);$i++){
						$pdf->output_plant($this->plants[$i]);
						$collection_name = $this->plants[$i]->info['collection_name'];
						
						// DEFAULT TO DISTINCTIVELY BETTER
						if($collection_name=='') $collection_name = 'Distinctively Better';
						
						$collection_names[$collection_name]++;
						
						//if($collection_name!=''&&!array_key_exists($collection_name,$collection_names)) $collection_names[] = $collection_name;
					}
					// PRODUCE PDF, ADD TO LIST
					$pdf->Output($server_root_path.$file_path,'F');
					if($pdf->error_msg==''){
						// PDF CREATED SUCCESSFULLY; ADD PATH TO ARRAY
						$this->middle_pdfs[] = $server_root_path . $file_path;
					}else{
						throw new Exception('Section: noncollection-plantfinish; Message: ' . $pdf->error_msg);
					}
					
					// COLLECTION INTRO PAGES
					//only if we need them
                    if($this->include_collection){ 
    					$collection_name_keys = array_keys($collection_names);
    					foreach($collection_name_keys as $collection_name){
    						if($collection_names[$collection_name]>0){
    							$collection_abbreviation = get_collection_abbreviation($collection_name);
								//Temporarily Omit Cover pages for Distinctively Better and Perennials @3/17/2015
    							if($collection_abbreviation!='' && $collection_abbreviation!='distinctivelybetterperennials' && $collection_abbreviation!='distinctivelybetter'){
    								$this->middle_pdfs[] = $server_root_path . 'pdfcatalogs/pdfs/'.$original_catalog_id.'/collection_'.$collection_abbreviation.'.pdf';
    							}
    						}
    					}
                    }
				}
				$this->middle_pdfs[] = $this->cover_paths['back'];
				
				// CHANGE PATHS TO REAL PATHS
				$middle_pdfs = array();
				
				for($i=0;$i<count($this->middle_pdfs);$i++){
					$server_file_path = realpath($this->middle_pdfs[$i]);
					if($server_file_path===false){
						throw new Exception('Section: realpaths');
					}else{
						$middle_pdfs[] = $server_file_path;
					}
				}
				
				$this->middle_pdfs = $middle_pdfs;				
			}catch(Exception $err){
				return serialize($err);
			}
			return true;
		}
		
	}
	
	class concat_pdf extends FPDI {

		var $files = array();

		function setFiles($files) {
			$this->files = $files;
		}

		function concat() {
			foreach($this->files AS $file) {
				$pagecount = $this->setSourceFile($file);
				for ($i = 1; $i <= $pagecount; $i++) {
					 $tplidx = $this->ImportPage($i);
					 $s = $this->getTemplatesize($tplidx);
					 $this->AddPage('P', array($s['w'], $s['h']));
					 $this->useTemplate($tplidx);
				}
			}
		}

	}

	class PDF extends FPDF {
		function Header(){}
		function Footer(){}
		function Error($msg){ $this->error_msg = $msg; }
		function clear_error(){ $this->error_msg = ''; }
		function __construct(){
			parent::__construct('P','mm',array(PAGE_WIDTH,PAGE_HEIGHT)); // A4, Letter
			$this->clear_error();
			$this->plants_total = 0;
			
			$this->pdf_config = array(
				'margin-top'				=>15,
				'margin-left'				=>10,
				'margin-right'				=>10,
				'plant-listings-per-page'	=>4,
				'plant-listing-height'		=>62,
				'x_plant_info'				=>63.5,
				'base_font_size'			=>11,
				'icon-size'					=>10,
				'icon-margin'				=>1
			);

			$this->AliasNbPages();
			
			$this->fontpath = $_SERVER['DOCUMENT_ROOT'].'/inc/fpdf/fonts/';

			$this->font_file_names = array(
				'Garamond'				=>'garamond',
				'GaramondBookItalic'	=> 'garamond_book_italic',
				'GillSansMT'			=> 'gill_sans_mt',
				'GillSansMTBold'		=> 'gill_sans_mt_bold',
				'GillSansLight'			=> 'gill_sans_light'
			);
			
			$font_names = array_keys($this->font_file_names);
			for($i=0;$i<count($font_names);$i++){
				$this->AddFont($font_names[$i],'',$this->font_file_names[$font_names[$i]].'.php');
			}
		}
		function output_plant(&$plant){
			try {
				$server_root_path = $_SERVER['DOCUMENT_ROOT']."/";

				if(($this->plants_total%$this->pdf_config['plant-listings-per-page'])==0){
					$this->AddPage();
					$y = $this->pdf_config['margin-top'];
				}else{
					$plants_on_page = $this->plants_total%$this->pdf_config['plant-listings-per-page'];
					$y = ($plants_on_page * $this->pdf_config['plant-listing-height']) + $this->pdf_config['margin-top'];
				}

				$x_image = $this->pdf_config['margin-left'] + 10;
				$y_image = $y;
				$y_botanical_name = $y+2;
				$y_item_number = $y_botanical_name;
				$y_cold_zones = $y_item_number + round($this->pdf_config['base_font_size']/2.22);
				$y_common_name = $y_botanical_name + round($this->pdf_config['base_font_size']/2.2);
                $y_description = $y_common_name + round($this->pdf_config['base_font_size']/3.5);

				// PLANT IMAGE
				if(isset($plant->info['image_primary'])&&file_exists($plant->info['image_primary']->info['server_path_search_result'])){
					$this->Image($plant->info['image_primary']->info['server_path_search_result'],$x_image,$y_image,0,0,'JPEG',$plant->info['details_url']);	
				}else{
					$this->Image($server_root_path . 'wp-content/uploads/404_sr.gif',$x_image,$y_image,0,0,'GIF',$plant->info['details_url']);
				}

				// NEW ICON
				if($plant->info['is_new']=='1'){
					$this->Image($server_root_path . 'wp-content/uploads/catalog/icons/new.gif',$this->pdf_config['margin-left'],$y_image,0,0,'GIF');
				}

				// MONROVIA EXCLUSIVE ICON
				if($plant->info['is_monrovia_exclusive']=='1'){
					$this->Image($server_root_path . 'wp-content/uploads/catalog/icons/monrovia_exclusive.gif',$this->pdf_config['margin-left'],$y_image,0,0,'GIF');
				}

				$this->SetRightMargin(0);

				// ITEM NUMBER AND COLD ZONE
				$line_1 = 'Item #' . $plant->info['item_number'];
				$line_2 = 'Zone: ' . $plant->info['cold_zones_friendly'];

				$txt = $line_1;
				if($plant->info['cold_zones_friendly']!='') $txt .= "\n$line_2";

				$this->SetFont('GillSansMT','',round($this->pdf_config['base_font_size']/1.1,1));
				$top_right_info_width = ceil(max($this->GetStringWidth($line_1),$this->GetStringWidth($line_2)) * 1.1); // FOR SOME REASON, GetStringWidth NOT RETURNING EXPECTED RESULTS, SO WE'LL MULTIPLY IT BY AN ARBITRARY NUMBER

				$line_height = round($this->pdf_config['base_font_size']/2.4);
				$this->SetXY(PAGE_WIDTH - $top_right_info_width - $this->pdf_config['margin-right'],$y_item_number-($line_height/2.5));	
				$this->MultiCell($top_right_info_width,$line_height,$txt);

				$this->SetLeftMargin($this->pdf_config['x_plant_info']);
				$plant_name_max_width = PAGE_WIDTH-$top_right_info_width-$this->pdf_config['x_plant_info']-$this->pdf_config['margin-right']-5;

				// BOTANICAL NAME
				$line_height = round($this->pdf_config['base_font_size']/3);
				$this->SetXY($this->pdf_config['x_plant_info'],$y_botanical_name - ($line_height / 2.5));
				$this->SetFont('GaramondBookItalic','',$this->pdf_config['base_font_size']);
				$this->MultiCell($plant_name_max_width,$line_height,$this->decode_entities($plant->info['botanical_name']));

				// COMMON NAME
				$this->SetFont('GillSansMTBold','',$this->pdf_config['base_font_size']);
				$line_height = round($this->pdf_config['base_font_size']/2.2);
				$this->SetXY($this->pdf_config['x_plant_info'],$this->GetY());
				$this->MultiCell($plant_name_max_width,$line_height,$this->decode_entities($plant->info['common_name']));
                
                // DESCRIPTION
				$this->SetXY($this->pdf_config['x_plant_info'],$this->GetY()+($this->pdf_config['base_font_size']/10));
				$this->SetFont('Garamond','',round($this->pdf_config['base_font_size']/1.2,1));
				$this->MultiCell($plant_name_max_width+$top_right_info_width,round($this->pdf_config['base_font_size']/3),$this->decode_entities(truncate($plant->info['description_benefits'],520,false,false,true).' '.$plant->info['average_landscape_size']));

				$icon_groups = array();
				// SUN EXPOSURE ICONS
				if(isset($plant->info['sun_exposures'])){
					$sun_exposures = '';
					for($i=0;$i<count($plant->info['sun_exposures']);$i++){
						$sun_exposures .= ',' . $plant->info['sun_exposures'][$i]->name;
					}
					$sun_exposures = strtolower($sun_exposures . ',');

					$icons_sun_exposure = array();
					if(strpos($sun_exposures,',full sun,')!==false) $icons_sun_exposure[] = $server_root_path . 'wp-content/uploads/catalog/icons/full_sun.gif';
					if(strpos($sun_exposures,',partial shade,')!==false||strpos($sun_exposures,',filtered sun,')!==false||strpos($sun_exposures,',partial sun,')!==false) $icons_sun_exposure[] = $server_root_path . 'wp-content/uploads/catalog/icons/partial_sun.gif';
					if(strpos($sun_exposures,',full shade,')!==false) $icons_sun_exposure[] = $server_root_path . 'wp-content/uploads/catalog/icons/full_shade.gif';

					$icon_groups[] = $icons_sun_exposure;
				}

				// WATER REQUIREMENT ICONS
				if(isset($plant->info['water_requirement'])){
					if($plant->info['water_requirement']=='High') $icon_groups[] = array($server_root_path . 'wp-content/uploads/catalog/icons/water_high.gif');
					if($plant->info['water_requirement']=='Moderate') $icon_groups[] = array($server_root_path . 'wp-content/uploads/catalog/icons/water_moderate.gif');
					if($plant->info['water_requirement']=='Low') $icon_groups[] = array($server_root_path . 'wp-content/uploads/catalog/icons/water_low.gif');
				}

				// DEER RESISTANT ICON
				if(strpos($plant->info['special_features_friendly'],'Deer Resistant')!==false) $icon_groups[] = array($server_root_path . 'wp-content/uploads/catalog/icons/deer_resistant.gif');

				// COLLECTIONS ICONS
				// Only Show below Collections/ Per Destini Request
				$allowArray = array('Edibles','Succulents');
				$collection_abbreviation = get_collection_abbreviation($plant->info['collection_name']);
				if($collection_abbreviation!='' && in_array($plant->info['collection_name'],$allowArray)){
					$icon_groups[] = array($server_root_path . 'wp-content/uploads/catalog/icons/collection_'.$collection_abbreviation.'.gif');
				}

				// RENDER ICONS	
				$y = $this->GetY() + ($this->pdf_config['icon-margin'] * 2);
				$x = $this->pdf_config['x_plant_info'] + $this->pdf_config['icon-margin'];
				for($i_icon_groups=0;$i_icon_groups<count($icon_groups);$i_icon_groups++){
					if(count($icon_groups[$i_icon_groups])>0){
						for($i_icons=0;$i_icons<count($icon_groups[$i_icon_groups]);$i_icons++){
							$this->Image($icon_groups[$i_icon_groups][$i_icons],$x,$y,$this->pdf_config['icon-size'],0,'GIF');
							$x += ($this->pdf_config['icon-size']+$this->pdf_config['icon-margin']);
						}
						if($i_icon_groups<count($icon_groups)-1){
							$x += ($this->pdf_config['icon-margin']/2);
							$this->Line($x,$y,$x,$y + $this->pdf_config['icon-size']);
							$x += ($this->pdf_config['icon-margin'] * 1.5);
						}
					}
				}
                
                // LINK
                $y_link = $y + $this->pdf_config['icon-size'] + round($this->pdf_config['base_font_size']/3);
                $line_height = $this->pdf_config['base_font_size']/3; 
                $this->SetXY($this->pdf_config['x_plant_info'],$y_link - ($line_height / 3));
                $this->SetFont('GaramondBookItalic','',$this->pdf_config['base_font_size']/1.2,1);
                $this->MultiCell($plant_name_max_width+$top_right_info_width,$line_height,$this->decode_entities($_SERVER['HTTP_HOST'].'plant-catalog/details.php?item_number='.$plant->info['item_number']));                

				$this->plants_total++;
			}catch(Exception $err){
				throw new Exception('Section: output_plant(); error: ' . serialize($err));
			}
		}

		function decode_entities($txt){
			$txt = html_entity_decode($txt);
			$txt = str_replace('&#153;',chr(153),$txt);
			$txt = str_replace('&trade;',chr(153),$txt);
			$txt = str_replace('&#174;',chr(174),$txt);
			$txt = str_replace('&reg;',chr(174),$txt);
			$txt = str_replace('&amp;',chr(38),$txt);
			$txt = str_replace("\'", "'", $txt);
			return $this->sanitize($txt);
		}
		
		function sanitize($txt){
			$txt =  str_replace(chr(9),' ',$txt);
			$txt =  str_replace('Â','',$txt);
			$txt =  str_replace(chr(194),'',$txt);
			return $txt;
		}
		
	}
	
	function compare_item_number($a,$b){
		return strnatcmp($a->info['item_number'],$b->info['item_number']);
	}

	function compare_botanical_name($a,$b){
		return strnatcmp($a->info['botanical_name'],$b->info['botanical_name']);
	}

	function compare_common_name($a,$b){
		return strnatcmp($a->info['common_name'],$b->info['common_name']);
	}

	function compare_collection_name($a,$b){
		return strnatcmp($a->info['collection_name'],$b->info['collection_name']);
	}
	
	function delete_old_pdfs(){
		// DELETE PDFs OVER A MONTH OLD
		$directory = $_SERVER['DOCUMENT_ROOT'] . '/downloads/pdf/custom_catalogs';
		try {
			if(is_dir($directory)){
				if($directory_handle = opendir($directory)){
					while(($file_name = readdir($directory_handle))!==false){
						if(strpos($file_name,'.pdf')!==false){
							$segments = (explode('_',$file_name));
							$timestamp = intval($segments[1]);
							// 1 MONTH ~= 2629743 SECONDS
							if(time()-$timestamp>2629743) unlink("$directory/$file_name");
						}
					}
					closedir($directory_handle);
				}
			}
			return true;
		}catch(Exception $err){
			//echo('error: delete_old_pdfs');
			return false;
		}
	}
	
	/*function get_collection_abbreviation($full_name){
		$mappings = array(
			'Dan Hinkley'=>'danhinkley',
			'Distinctively Better'=>'distinctivelybetter',
			'Distinctively Better Perennials'=>'distinctivelybetterperennials',
			'Edibles'=>'edibles',
			'Itoh Peonies'=>'itohpeonies',
			'Proven Winners'=>'provenwinners',
			'Succulents'=>'succulents'
		);
		return $mappings[$full_name];
	} */
	
	class custom_catalog_cover {
		function custom_catalog_cover($catalog,$version){
			try {
				$this->pdf = new PDF();
				$this->pdf_config = $this->pdf->pdf_config;
				$this->catalog = $catalog;
				$this->version = $version;
			}catch(Exception $err){
				//echo('error: custom_catalog_cover');
				throw new Exception('Section: custom_catalog_cover :: constructor; error: ' . serialize($err));
			//	return false;
			}
		}
		function generate($thumbnail_only){
			if(!isset($this->catalog->info['template_id'])||intval($this->catalog->info['template_id'])==0) return false;
			try {
				$this->thumbnail_only = $thumbnail_only;
				// INITIALIZE THUMBNAIL TWICE THE INTENDED SIZE; WE'LL SHRINK IT DOWN LATER
				$this->thumbail_path = $_SERVER['DOCUMENT_ROOT'] . '/downloads/pdf/custom_catalogs/'.$this->catalog->info['id'].'/thumbnail_'.$this->version.'.jpg';
				if(file_exists($this->thumbail_path)) unlink($this->thumbail_path);
				$this->thumbnail_temp = imagecreatetruecolor(TEMP_THUMBNAIL_WIDTH,TEMP_THUMBNAIL_HEIGHT);
				$color = imagecolorallocate($this->thumbnail_temp,255,255,255);
				imagefilledrectangle($this->thumbnail_temp,0,0,TEMP_THUMBNAIL_WIDTH-1,TEMP_THUMBNAIL_HEIGHT-1,$color);

				$this->pdf->AddPage();
				$this->pdf->SetAutoPageBreak(false);

				include_once($_SERVER['DOCUMENT_ROOT'].'/pdfcatalogs/templates/'.$this->catalog->info['template_id'].'.php');

				// LOAD TEMPLATE XML, NORMALIZE MAIN NODE
				$xml_doc = XML_unserialize(file_get_contents('http://'.$_SERVER['HTTP_HOST'].'/pdfcatalogs/templates/'.$this->catalog->info['template_id'].'.xml'));
				$template = $xml_doc['covers'][$this->version.'_cover']['elements'];
				if(count($template)>1) $template = array('element'=>$template);
				$template = $template['element'];

				$tag_names = array_keys($template);

				for($i=0;$i<count($tag_names);$i+=2){
					$attributes = $template[$tag_names[$i]];
					$contents = $template[$tag_names[$i+1]];

					switch($attributes['type']){
						case 'rect':
							$this->add_rect($attributes);
						break;
						case 'img':
							$this->add_image($attributes);
						break;
						case 'p':
							$this->add_p($attributes,$contents);
						break;
					}
				}

				if(isset($this->thumbail_path)){
					// RESIZE THUMBNAIL TO ORIGINALLY INTENDED SIZE
					$this->thumbnail = imagecreatetruecolor(THUMBNAIL_WIDTH,THUMBNAIL_HEIGHT);
					$color = imagecolorallocate($this->thumbnail,255,255,255);
					imagefilledrectangle($this->thumbnail,0,0,THUMBNAIL_WIDTH-1,THUMBNAIL_HEIGHT-1,$color);
					imagecopyresampled($this->thumbnail,$this->thumbnail_temp,0,0,0,0,THUMBNAIL_WIDTH,THUMBNAIL_HEIGHT,TEMP_THUMBNAIL_WIDTH,TEMP_THUMBNAIL_HEIGHT);

					// OUTPUT THUMBNAIL				
					imagejpeg($this->thumbnail,$this->thumbail_path,90);
					imagedestroy($this->thumbnail_temp);
					imagedestroy($this->thumbnail);
				}

				// OUTPUT PDF
				if(!$thumbnail_only){
					$dest_path = $this->catalog->cover_paths[$this->version];
					if(file_exists($dest_path)){
						if(unlink($dest_path)===false) throw new Exception('custom_catalog_cover: unlink');
					}
					$this->pdf->Output($dest_path,'F');
				}
				
				return true;

			}catch(Exception $err){
				//echo('error: custom_catalog_cover->generate');
				//return false;
				throw new Exception('Section: custom_catalog_cover :: generate(); error: ' . serialize($err));
			}
		}
		function add_image($attributes){
			try {
				if(count($attributes)>0){
					$array_keys = array_keys($attributes);
					if($array_keys[0]=='0 attr'){
						// IF THERE ARE MULTIPLE IMG TAGS...
						for($i=0;$i<count($array_keys);$i++){
							if(strpos($array_keys[$i],' attr')!==false) $this->add_image($attributes[$array_keys[$i]]);
						}
					}else{

						// ALLOW SPECIAL PROVISIONS
						$field_id = null; if(isset($attributes['id'])) $field_id = $attributes['id'];
						if(function_exists('before_output_img')) $attributes = before_output_img($this->catalog->info,$field_id,$attributes);

						$src = $attributes['src'];
						if(strpos($src,'http:')===false) $src = $_SERVER['DOCUMENT_ROOT'] . $src;

						$format = $this->infer_image_format($src);
						if(isset($attributes['format'])) $format = $attributes['format'];

						$this->pdf->Image($src,$this->x_pct_to_units(floatval($attributes['x_pct'])),$this->y_pct_to_units(floatval($attributes['y_pct'])),$this->x_pct_to_units(floatval($attributes['width_pct'])),$this->y_pct_to_units(floatval($attributes['height_pct'])),$format);

						// THUMBNAIL
						if(isset($this->thumbail_path)){
							$pointA = array($this->x_pct_to_px_temp_thumbnail(floatval($attributes['x_pct'])),$this->y_pct_to_px_temp_thumbnail(floatval($attributes['y_pct'])));
							$pointB = array($this->x_pct_to_px_temp_thumbnail(floatval($attributes['width_pct'])),$this->y_pct_to_px_temp_thumbnail(floatval($attributes['height_pct'])));	

							if(isset($attributes['thumbnail_src'])&&$attributes['thumbnail_src']!=''){
								$src = $attributes['thumbnail_src'];
								if(strpos($src,'http:')===false) $src = $_SERVER['DOCUMENT_ROOT'] . $src;
							}

							if($format=='PNG'){
								$img = imagecreatefrompng($src);
								$src_image = imagecreatefrompng($src);
							}else if($format=='JPG'){
								$img = imagecreatefromjpeg($src);
								$src_image = imagecreatefromjpeg($src);
							}else if($format=='GIF'){
								$img = imagecreatefromgif($src);
								$src_image = imagecreatefromgif($src);
							}
							if($src_image){
								$src_width = imagesx($src_image);
								$src_height = imagesy($src_image);
								imagedestroy($src_image);
								imagecopyresampled($this->thumbnail_temp,$img,$pointA[0],$pointA[1],0,0,$pointB[0],$pointB[1],$src_width,$src_height);			
							}
						}
					}
				}
			}catch(Exception $err){
				throw new Exception('Section: add_image; error: ' . serialize($err));
			}
		}
		function add_rect($attributes){
			try {
				if(count($attributes)>0){
					$array_keys = array_keys($attributes);
					if($array_keys[0]=='0 attr'){
						// IF THERE ARE MULTIPLE RECT TAGS...
						for($i=0;$i<count($array_keys);$i++){
							if(strpos($array_keys[$i],' attr')!==false) $this->add_rect($attributes[$array_keys[$i]]);
						}
					}else{
						$this->pdf->SetLineWidth(0);
						$rgb = $this->hex_to_rgb($attributes['fill_color']);
						$this->pdf->SetDrawColor($rgb[0],$rgb[1],$rgb[2]);
						$this->pdf->SetFillColor($rgb[0],$rgb[1],$rgb[2]);
						$this->pdf->Rect($this->x_pct_to_units(floatval($attributes['x_pct'])),$this->y_pct_to_units(floatval($attributes['y_pct'])),$this->x_pct_to_units(floatval($attributes['width_pct'])),$this->y_pct_to_units(floatval($attributes['height_pct'])),'DF');

						// THUMBNAIL					
						if(isset($this->thumbail_path)){
							$pointA = array($this->x_pct_to_px_temp_thumbnail(floatval($attributes['x_pct'])),$this->y_pct_to_px_temp_thumbnail(floatval($attributes['y_pct'])));
							$pointB = array($pointA[0] + $this->x_pct_to_px_temp_thumbnail(floatval($attributes['width_pct'])),$pointA[1] + $this->y_pct_to_px_temp_thumbnail(floatval($attributes['height_pct'])));

							if($pointB[0]==$pointA[0]) $pointB[0] += 1;
							if($pointB[1]==$pointA[1]) $pointB[1] += 1;

							$color = imagecolorallocate($this->thumbnail_temp,$rgb[0],$rgb[1],$rgb[2]);
							imagefilledrectangle($this->thumbnail_temp,$pointA[0],$pointA[1],$pointB[0],$pointB[1],$color);
						}
					}
				}
			}catch(Exception $err){
				throw new Exception('Section: add_rect(); error: ' . serialize($err));
			}
		}
		function add_p($tag_attributes,$tag_contents){
			try {
				$coords = array($this->x_pct_to_units(floatval($tag_attributes['x_pct'])),$this->y_pct_to_units(floatval($tag_attributes['y_pct'])));
				$dimensions = array($this->x_pct_to_units(floatval($tag_attributes['width_pct'])),$this->y_pct_to_units(floatval($tag_attributes['height_pct'])));
				$this->pdf->SetXY($coords[0],$coords[1]);

				// NORMALIZE
				if(count($tag_contents)>1) $tag_contents = array('element'=>$tag_contents);
				$tag_contents = $tag_contents['element'];

				$array_keys = array_keys($tag_contents);

				for($i=0;$i<count($tag_contents);$i+=2){
					$attributes = $tag_contents[$array_keys[$i]];
					$contents = iconv('UTF-8','windows-1252',$tag_contents[$array_keys[$i+1]]);

					// PLACEHOLDER SUBSTITUTION			
					$components = preg_split('/({{([^\{\}]+)}})/i',$contents,-1,PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
					$contents = '';

					for($x=0;$x<count($components);$x++){
						$variable_id = '';
						if(preg_match('/({{([^\{\}]+)}})/',$components[$x])===1){
							// SUCCEEDING ARRAY ITEM MUST BE VARIABLE ID, ISOLATED FROM "{{" AND "}}"
							if(isset($components[$x+1])&&$components[$x]=='{{'.$components[$x+1].'}}'){
								$variable_id = $components[++$x];
								if(isset($this->catalog->info[$variable_id])) $contents .= $this->catalog->info[$variable_id];
							}
						}else{
							$contents .= $components[$x];
						}	
					}

					// ALLOW SPECIAL PROVISIONS
					$contents = $this->pdf->decode_entities($contents);
					$field_id = null; if(isset($attributes['id'])) $field_id = $attributes['id'];
					if(function_exists('before_output_field')) $contents = before_output_field($this->catalog->info,$field_id,$contents);

					$font_size = round($this->pdf_config['base_font_size']*floatval($attributes['font_size']),1);
					$this->pdf->SetFont($attributes['font_face'],'',$font_size);
					$rgb = $this->hex_to_rgb($attributes['font_color']);
					$this->pdf->SetTextColor($rgb[0],$rgb[1],$rgb[2]);

					$line_height = $font_size/2.4;

					// THUMBNAIL
					if(isset($this->thumbail_path)){

						// SHRINK FONT SIZE TO SCALE
						$font_size *= (THUMBNAIL_WIDTH/PAGE_WIDTH);

						// REDUCE IT EVEN MORE FOR GD
						$font_size /= 2;

						$coords_thumbnail = array($this->x_pct_to_px_temp_thumbnail(floatval($tag_attributes['x_pct'])),$this->y_pct_to_px_temp_thumbnail(floatval($tag_attributes['y_pct'])));

						$thumbnail_x = $this->x_units_to_px_temp_thumbnail($this->pdf->GetX());
						$thumbnail_y = $this->y_units_to_px_temp_thumbnail($this->pdf->GetY()) + ($font_size*2);

						$color = imagecolorallocate($this->thumbnail_temp,$rgb[0],$rgb[1],$rgb[2]);					
						imagettftext($this->thumbnail_temp,$font_size,0,$thumbnail_x,$thumbnail_y,$color,$this->get_font_file_name($attributes['font_face']),$contents);
					}

					$this->pdf->MultiCell($dimensions[0],$dimensions[1],$contents);
					if(isset($attributes['line_height'])) $line_height *= floatval($attributes['line_height']);

					// LINE BREAK
					$coords[1] += $line_height;
					$this->pdf->SetXY($coords[0],$coords[1]);
				}
			}catch(Exception $err){
				throw new Exception('Section: add_p(); error: ' . serialize($err));
			}
		}
		function x_pct_to_units($pct){
			return PAGE_WIDTH * $pct;
		}
		function y_pct_to_units($pct){
			return PAGE_HEIGHT * $pct;
		}
		function x_pct_to_px_temp_thumbnail($pct){
			return round(TEMP_THUMBNAIL_WIDTH * $pct);
		}
		function y_pct_to_px_temp_thumbnail($pct){
			return round(TEMP_THUMBNAIL_HEIGHT * $pct);
		}
		function x_units_to_px_temp_thumbnail($x){
			return round(TEMP_THUMBNAIL_WIDTH * ($x/PAGE_WIDTH));
		}
		function y_units_to_px_temp_thumbnail($y){
			return round(TEMP_THUMBNAIL_HEIGHT * ($y/PAGE_HEIGHT));
		}
		function get_font_file_name($font_name){
			return $_SERVER['DOCUMENT_ROOT'].'/inc/fpdf/fonts/'.$this->pdf->font_file_names[$font_name] . '.ttf';
		}
		function infer_image_format($path){
			if(strpos(strtolower($path),'.gif')!==false) return 'GIF';
			if(strpos(strtolower($path),'.jpg')!==false) return 'JPG';
			if(strpos(strtolower($path),'.jpeg')!==false) return 'JPG';
			if(strpos(strtolower($path),'.png')!==false) return 'PNG';
			return false;
		}
		function hex_to_rgb($hex) {
		   $hex = str_replace("#", "", $hex);

		   if(strlen($hex) == 3) {
			  $r = hexdec(substr($hex,0,1).substr($hex,0,1));
			  $g = hexdec(substr($hex,1,1).substr($hex,1,1));
			  $b = hexdec(substr($hex,2,1).substr($hex,2,1));
		   } else {
			  $r = hexdec(substr($hex,0,2));
			  $g = hexdec(substr($hex,2,2));
			  $b = hexdec(substr($hex,4,2));
		   }
		   $rgb = array($r, $g, $b);
		   return $rgb; // returns an array with the rgb values
		}
	}
	
?>