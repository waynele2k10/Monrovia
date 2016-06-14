<?php
	//require_once('class_plant.php');
	require_once('class_inflection.php');

	class search_plant {
		// TODO: include param to execute specific method for every result record
		function search_plant($query = '',$result_fields = 'MIN',$include_inactive = false){

			$this->results = array();
			$this->criteria = array('query'=>'','query_original'=>'');
			$this->results_per_page = 8;
			$this->max_pagination_links = 12;
			$this->order_by = '';
			$this->results_start_page = 1;
			$this->view_all = '';
			$this->include_images = false;
			$this->pagination_html = '';
			
			// WHEN SEARCHING BY A GENUS AND ORDERING BY RELEVANCY, PREFER PLANTS WITH MATCHING GENUSES. THIS LIST SHOULD BE LOWERCASE, AND BEGIN AND END WITH A COMMA
			$this->genuses = ',abelia,abeliophyllum,abies,abutilon,acacia,acanthus,acer,achillea,acinos,aconitum,acorus,actaea,actinidia,adiantum,aegopodium,aeonium,aesculus,agapanthus,agastache,agave,agonis,ajuga,akebia,albizia,alcea,alchemilla,alocasia,aloe,alopecurus,aloysia,alpinia,alsophila,alstroemeria,alyogyne,alyssum,amelanchier,amsonia,andromeda,androsace,anemone,anigozanthos,antennaria,antigonon,aquilegia,arachniodes,arbutus,arctostaphyllus,arctostaphylos,arctotis,ardisia,arecastrum,arenaria,armeria,aronia,arrhenatherum,artemisia,arum,asarum,asclepias,asparagus,aspidistra,asplenium,aster,astilbe,astrantia,athyrium,aubrieta,aucuba,azalea,azara,baccharis,bambusa,baptisia,bauhinia,beesia,begonia,bellis,berberis,bergenia,berlandiera,beschorneria,betula,bignonia,blechnum,boltonia,borinda,boronia,bougainvillea,bouteloua,brahea,briza,brunfelsia,brunnera,buddleia,buddleja,bulbine,butia,buxus,caesalpinia,calamagrostis,calamintha,calliandra,callicarpa,callirhoe,callistemon,calluna,calocedrus,calycanthus,calylophus,camellia,campanula,campsis,canna,capparis,capsicum,caragana,carex,carissa,carpinus,caryopteris,cassia,ceanothus,cedrus,centaurea,centranthus,cephalotaxus,ceratostigma,cercidiphyllum,cercidium,cercis,cestrum,cestrus,chaenomeles,chamaecyparis,chamaerops,chasmanthium,cheilanthes,chelone,chilopsis,chionanthus,chitalpa,choisya,chondropetalum,chrysactinia,chrysanthemum,cinnamomum,cistus,citrus,clematis,clerodendrum,clethra,clivia,clytostoma,coleonema,colocasia,convallaria,convolvulus,coprosma,cordyline,coreopsis,cornus,corokia,cortaderia,corydalis,corylopsis,corylus,cosmos,cotinus,cotoneaster,crassula,crocosmia,cryptomeria,cuphea,cupressocyparis,cupressus,cycas,cynara,cyperus,cypripedium,cyrtomium,cytisus,dalea,daphne,daphniphyllum,dasylirion,davidia,decumaria,delosperma,delphinium,dennstaedtia,deschampsia,deutzia,dianella,dianthus,diascia,dicentra,dicksonia,dietes,digitalis,diospyros,disporopsis,dispororum,distictis,dodonaea,doronicum,drimys,dryopteris,dudleya,echeveria,echinacea,echinocactus,echinopsis,elaeagnus,elaeocarpus,elymus,embothrium,enkianthus,ensete,epimedium,equisetum,eremophila,erianthus,erica,erigeron,eryngium,erysimum,escallonia,eucalyptus,eugenia,euonymus,eupatorium,euphorbia,euryops,evolvulus,exochorda,fagus,fallugia,fargesia,fatsia,feijoa,festuca,ficus,forsythia,fothergilla,fragaria,frageria,franklinia,fraxinus,fuchsia,gaillardia,galium,gardenia,gaultheria,gaura,gazania,gelsemium,genista,geranium,geum,ginkgo,glechoma,globularia,graptopetalum,grevillea,gunnera,hakonechloa,hamamelis,hebe,hedera,helenium,helianthemum,helichrysum,helictotrichon,heliotropium,helleborus,hemerocallis,heptacodium,herniaria,hesperaloe,heuchera,heucherella,hibiscus,holboellia,hosta,houttuynia,hydrangea,hydrangeaangea,hypericum,iberis,ilex,illicium,indigofera,ipomea,ipomoea,iris,isotoma,itea,jacaranda,jasminum,jatropha,juncus,juniper,juniperus,justicia,kalanchoe,kalimeris,kalmia,kerria,knautia,kniphofia,koelreuteria,kolkwitzia,laburnum,lagerstroemia,lamium,lantana,larix,laurus,lavandula,lavatera,leontopodium,leptinella,leptodermis,leptospermum,leucophyllum,leucothe,leucothoe,lewisia,liatris,ligularia,ligustrum,limonium,lippia,liquidambar,liriodendron,liriope,lithodora,lobelia,lomandra,lonicera,lophomyrtus,loropetalum,lotus,lupinus,lysimachia,macfadyena,magnolia,mahonia,malus,malva,malvaviscus,mandevilla,manfreda,mangave,mascagnia,matteuccia,mazus,melampodium,melianthus,melissa,mentha,mertensia,metapanax,metasequoia,metrosideros,michelia,microbiota,miscanthus,molinia,monarda,muehlenbeckia,muhlenbergia,mukdenia,murraya,musa,musella,myosotis,myrica,myrsine,myrtus,nandina,nassella,nepeta,nephrolepis,nierembergia,nolina,nyssa,ocimum,oenothera,olea,ophiopogon,opuntia,origanum,osmanthus,osmunda,osteospermum,otatea,othonna,oxalis,pachysandra,paeonia,pandorea,panicum,papaver,parrotia,parthenocissus,passiflora,pavonia,pelargonium,pennisetum,penstemon,pentalinon,perovskia,philadelphus,philodendron,phlomis,phlox,phoenix,phormium,photinia,phyllostachys,physocarpus,physostegia,picea,pieris,pin,pinaceae,pinus,pistacia,pittosporum,platycodon,pleioblastus,plumbago,podocarpus,polemonium,polygala,polygonatum,polygonum,polystichum,populus,potentilla,prosopis,prunus,pseudolarix,pseudotsuga,pulmonaria,punica,pyracantha,pyrus,quercus,raphiolepis,ratibida,rhamnus,rhamnusus,rhaphiolepis,rhododendron,rhus,ribes,robinia,rodgersia,rosa,rosmarinus,rubus,rudbeckia,ruellia,rumex,rumohra,sabal,salix,salvia,sambucus,santolina,sarcococca,sasa,sasaella,scabiosa,schefflera,schinus,schizachyrium,schizophragma,scutellaria,sedum,selaginella,sempervivum,senecio,sequoiadendron,setcreasea,sisyrinchium,skimmia,solanum,solidago,sophora,sorbus,sorghastrum,spartium,spiraea,stachys,stachyurus,stephanotis,stewartia,stokesia,strelitzia,stromanth,styrax,symphoricarpos,symphoricarpus,syringa,tabebuia,tabernaemontana,taxodium,taxus,tecoma,tecomaria,ternstroemia,teucrium,thelypteris,thuja,thujopsis,thunbergia,thymus,tiarella,tibouchina,tilia,trachelospermum,trachycarpus,tradescantia,trochodendron,tsuga,tulbaghia,typha,ulmus,vaccinium,verbena,veronica,viburnum,vinca,viola,vitex,vitis,washingtonia,weigela,wisteria,woodwardia,xylosma,yucca,zantedeschia,zauschneria,';

			//$this->criteria['include_inactive'] = $include_inactive;

			$temp = new plant();
			$this->plant_fields = explode(',',$temp->table_fields);

			// IF SEARCH QUERY IS AN INTEGER, ASSUME ITEM NUMBER
			if(is_numeric($query)){
				$this->criteria['item_number'] = $query;
			}else{
				$query = str_replace('+',' ',$query);
				$this->criteria['query'] = trim(parse_alphanumeric(strip_tags(strtolower($query)),'"\'\-\+ '));

				// REMEMBER ORIGINAL, SANITIZED QUERY
				$this->criteria['query_original'] = $this->criteria['query'];
				
				// INITIAL REPLACEMENTS		
				$this->criteria['query'] = str_replace('buddleja','buddleia',$this->criteria['query']);
				
				$this->criteria['query_used'] = to_mysql_boolean_mode($this->criteria['query']);
			}

			// OMIT INACTIVES UNLESS SPECIFIED OTHERWISE
			if(!$include_inactive) $this->criteria['is_active'] = '1';

			// DETERMINE WHICH FIELDS TO RETURN
			if($result_fields=='MIN'){
				$this->result_fields = 'id,item_number,common_name,status_id,is_new,release_status_id';
			}else{
				$this->result_fields = $result_fields;
			}
		}
		function search($user_initiated=true){
		
			if($this->order_by=='') $this->order_by = 'relevancy DESC, relevancy_metaphone DESC, common_name ASC';

			if($user_initiated){
				//echo isset($this->criteria['release_status_id']);
				if(isset($this->criteria['release_status_id'])){
					$release_status_ids = ',' . (is_array($this->criteria['release_status_id'])?implode(',',$this->criteria['release_status_id']):$this->criteria['release_status_id']) . ',';
				
					if(strpos($release_status_ids,',5,')!==false){
						// RESTRICT TO CERTAIN STATUSES
						$this->criteria['release_status_id'] = array();
						$this->criteria['release_status_id'][] = '1'; // A (ACTIVE)
						$this->criteria['release_status_id'][] = '2'; // NA (NEW/ACTIVE)
						$this->criteria['release_status_id'][] = '3'; // NI (NEW/INACTIVE)
						$this->criteria['release_status_id'][] = '4'; // II (INVENTORY/INACTIVE)
						$this->criteria['release_status_id'][] = '6'; // F (FUTURE)				
					}
								
					$this->criteria['is_active'] = '1';
				}
			}

			// FIGURE OUT HOW MANY PLANT RECORDS THERE ARE
			//$results = mysql_query('SELECT COUNT(*) AS total_records FROM plants');
			//$_SESSION['total_plants'] = intval(@mysql_result($results,0,'total_records'));

			$this->criteria['query'] = str_replace('+',' ',$this->criteria['query']);
			$this->criteria['query'] = trim(parse_alphanumeric(strip_tags(strtolower($this->criteria['query'])),'"\'\-\+ '));
			if(is_suspicious(ids_sanitize($this->criteria['query']))){
				header('location:/');
			}
			
			$exact_match_mode = contains($this->criteria['query'],'"');

			$this->criteria['query_used'] = to_mysql_boolean_mode($this->criteria['query']);

			if($this->results_per_page=='') $this->results_per_page = 0;
			if($this->results_start_page==''||$this->results_start_page==0||$this->view_all==1) $this->results_start_page = 1;
			if($this->view_all==1) $this->results_per_page = $GLOBALS['view_all_max'];

			// COLLAPSE LITERAL FIELD CRITERIA
			for($i=0;$i<count($this->plant_fields);$i++){
				$field_name = $this->plant_fields[$i];
				if(isset($this->criteria[$field_name])&&(!strpos($field_name,'_id')&&is_array($this->criteria[$field_name])&&$field_name!='item_number')){
					//if($field_name=='collection_name'){var_dump($this->criteria[$field_name]);exit;}
					$this->criteria[$field_name] = $this->criteria[$field_name][0];
				}
			}

			$result_fields = ($this->result_fields=='ALL')?'id':$this->result_fields;
			$result_fields = 'plants.' . str_replace(',',',plants.',$result_fields); // . ',plant_availability.itemNo';
			//If Using PLant availibilty Sort, add Plant Availibility Columns
			if(strpos($this->order_by,'quantity ') === 0){
				$result_fields .= ',shop_plant_availibility.quantity,shop_plant_availibility.item_number';
			}
			$query_count = 'SELECT DISTINCT plants.id FROM plants ';
			$query_ids = 'SELECT DISTINCT 1 AS relevancy, 1 AS relevancy_metaphone, plants.id, plants.common_name FROM plants ';

			$query = 'SELECT DISTINCT 1 AS relevancy, 1 AS relevancy_metaphone, ' . $result_fields . ' FROM plants ';
			$query_main = '';
			$additional_criteria = '';

			if(isset($this->criteria['item_number'])&&$this->criteria['item_number']!=''){
				// SEARCH BY ITEM NUMBER
				if(is_array($this->criteria['item_number'])){
					$csv = str_replace(',,',',',implode(',',$this->criteria['item_number']));
					$query_main .= ' AND plants.item_number IN('.$csv.')';
				}else{
					$query_main .= ' AND plants.item_number=' . $this->criteria['item_number'];
				}

				// TAKE is_active AND release_status_id INTO CONSIDERATION
				if(isset($this->criteria['is_active'])&&$this->criteria['is_active']=='1') $query_main .= ' AND plants.is_active=1 AND plants.release_status_id IN (1,2,3,4,6)';
			}else if(isset($this->criteria['id'])&&$this->criteria['id']!=''){
				// SEARCH BY ID
				if(is_array($this->criteria['id'])){
					$csv = str_replace(',,',',',implode(',',$this->criteria['id']));
					$query_main .= ' AND plants.id IN('.$csv.')';
				}else{
					$query_main .= ' AND plants.id=' . $this->criteria['id'];
				}

				// TAKE is_active AND release_status_id INTO CONSIDERATION
				if(isset($this->criteria['is_active'])&&$this->criteria['is_active']=='1') $query_main .= ' AND plants.is_active=1 AND plants.release_status_id IN (1,2,3,4,6)';
			}else{
				// SEARCH BY TEXT

				// IF COMMON NAME BEGINS WITH...
				if(isset($this->criteria['common_name'])&&$this->criteria['common_name']!=''){
					if(strlen($this->criteria['common_name'])==1){
						$query_main .= " AND plants.common_name LIKE '".sql_sanitize($this->criteria['common_name'])."%'";
					}else{
						$query_main .= " AND plants.common_name LIKE '%".sql_sanitize($this->criteria['common_name'])."%'";
					}
					$this->criteria['common_name'] = '';
					$this->criteria['query'] = '';
					$this->criteria['query_used'] = '';
				}

				// IF BOTANICAL NAME BEGINS WITH...
				if(isset($this->criteria['botanical_name'])&&$this->criteria['botanical_name']!=''){
					if(strlen($this->criteria['botanical_name'])==1){
						$query_main .= " AND plants.botanical_name LIKE '".sql_sanitize($this->criteria['botanical_name'])."%'";
					}else{
						$query_main .= " AND plants.botanical_name LIKE '%".sql_sanitize($this->criteria['botanical_name'])."%'";
					}
					$this->criteria['botanical_name'] = '';
					$this->criteria['query'] = '';
					$this->criteria['query_used'] = '';

				}
				// IF TRADEMARK NAME BEGINS WITH...
				if(isset($this->criteria['trademark_name'])&&$this->criteria['trademark_name']!=''&&$this->criteria['trademark_name']!='NOT NULL'){
					$query_main .= " AND plants.trademark_name LIKE '%".sql_sanitize($this->criteria['trademark_name'])."%'";
					$this->criteria['trademark_name'] = '';
					$this->criteria['query'] = '';
					$this->criteria['query_used'] = '';
				}

				// REGULAR QUERIES
				if(isset($this->criteria['query_used'])&&$this->criteria['query_used']!=''){
					$query_metaphone = '';//to_metaphone_string($this->criteria['query']);

					/* ************************ */
					// SPECIAL PROVISIONS
						if($this->criteria['query_used']==to_mysql_boolean_mode('fig')||$this->criteria['query_used']==to_mysql_boolean_mode('figs')||$this->criteria['query_used']=='fig'){
							$query_metaphone = 'FIG';
						}
					/* ************************ */
					$logic = '';
					
					$logic .= '(MATCH(common_name,botanical_name,synonym,trademark_name,php_metaphone,keywords) AGAINST (\''.$this->criteria['query_used'].'\' IN BOOLEAN MODE) ';
					
					// IF ACTUAL SEARCH QUERY PROVIDED (I.E., NOT A common_name OR botanical_name SEARCH), GIVE MORE PROMINENCE TO MATCHING PLANTS
					if($this->criteria['query']!='') $logic .= ' + ((common_name LIKE \'%'. sql_sanitize($this->criteria['query']) . '%\')*2) + ((botanical_name LIKE \'%'. sql_sanitize($this->criteria['query']) . '%\')*2)';
					
					$logic .= ') ';
					
					$query_main .= ' AND ('.$logic;
					if(strlen($query_metaphone)>2&&!$exact_match_mode) $query_main .= ' OR plants.php_metaphone LIKE \'%'.sql_sanitize($query_metaphone).'%\'';
					$query_main .= ')';
					
					$query = str_replace('SELECT DISTINCT 1 ','SELECT DISTINCT '.$logic,$query);
					$query = str_replace('SELECT DISTINCT 1 ','SELECT DISTINCT '.$logic,$query);
					
					$query = str_replace('1 AS relevancy_metaphone','(plants.php_metaphone LIKE \'%'.sql_sanitize($query_metaphone).'%\') AS relevancy_metaphone',$query);
					
				}
				
				// ADD ADDITIONAL CRITERIA
				for($i=0;$i<count($this->plant_fields);$i++){
					$criterion_name = $this->plant_fields[$i];
					if(!contains($criterion_name,'cold_zone_')&&$criterion_name!='growth_habit'){
						if(isset($this->criteria[$criterion_name])){
							if(!is_array($this->criteria[$criterion_name])){
								$criterion_values = array();
								$criterion_values[] = $this->criteria[$criterion_name];
							}else{
								$criterion_values = $this->criteria[$criterion_name];
							}
							$criterion_value_list = '';
							for($n=0;$n<count($criterion_values);$n++){
								$criterion_value = $criterion_values[$n];
								if($criterion_value!=''){
									$criterion_value_list .= ",'$criterion_value'";
								}
							}
							if($criterion_value_list!=''){
								if(contains($criterion_value_list,'\'NOT NULL\'')){
									$additional_criteria .= " AND $criterion_name <> '' ";
								}else{
									$additional_criteria .= " AND $criterion_name IN (" . substr($criterion_value_list,1) . ") ";
								}
							}
						}
					}
				}
				$additional_criteria = str_replace("='NOT NULL'"," <> ''",$additional_criteria);

				// SQL INJECTION-SAFE
				$scan_criteria = $this->criteria;
				$scan_criteria['query'] = '';
				$scan_criteria['query_used'] = '';

				// COLD ZONES
				if(isset($this->criteria['cold_zone_low'])&&$this->criteria['cold_zone_low']!=''){
					if($this->criteria['cold_zone_high']=='') $this->criteria['cold_zone_high'] = $this->criteria['cold_zone_low'];

					$c_low = $this->criteria['cold_zone_low'];
					$c_high = $this->criteria['cold_zone_high'];
					$p_low = 'plants.cold_zone_low';
					$p_high = 'plants.cold_zone_high';

					$query_cold_zone = " (($p_low BETWEEN $c_low AND $c_high) OR ($p_high BETWEEN $c_low AND $c_high))";
					$query_cold_zone_union = " (($c_low BETWEEN $p_low AND $p_high) OR ($c_high BETWEEN $p_low AND $p_high))";

				}

				// ONE-TO-MANY CRITERIA

					$one_to_many_join = '';
					$one_to_many_where = '';

					// TYPES
						$temp = $this->add_one_to_many_sql('type');
						if(isset($temp[0])) $one_to_many_join .= $temp[0]; if(isset($temp[1])) $one_to_many_where .= $temp[1];

					// SUNSET ZONES
					if(isset($this->criteria['sunset_zones'])&&count($this->criteria['sunset_zones'])>0){
						$csv = str_replace(',,',',',implode(',',$this->criteria['sunset_zones']));
						$one_to_many_join .= 'INNER JOIN plant_sunset_zones on plants.id=plant_sunset_zones.plant_id ';
						$one_to_many_where .= 'AND plant_sunset_zones.sunset_zone IN ('.$csv.') ';
					}

					// FLOWERING SEASONS
						$temp = $this->add_one_to_many_sql('flowering_season');
						if(isset($temp[0])) $one_to_many_join .= $temp[0]; if(isset($temp[1])) $one_to_many_where .= $temp[1];

					// FLOWER ATTRIBUTES
						$temp = $this->add_one_to_many_sql('flower_attribute');
						if(isset($temp[0])) $one_to_many_join .= $temp[0]; if(isset($temp[1])) $one_to_many_where .= $temp[1];

					// GARDEN STYLE
						$temp = $this->add_one_to_many_sql('garden_style');
						if(isset($temp[0])) $one_to_many_join .= $temp[0]; if(isset($temp[1])) $one_to_many_where .= $temp[1];

					// LANDSCAPE USE
						$temp = $this->add_one_to_many_sql('landscape_use');
						if(isset($temp[0])) $one_to_many_join .= $temp[0]; if(isset($temp[1])) $one_to_many_where .= $temp[1];

					// PROBLEM/SOLUTION
						$temp = $this->add_one_to_many_sql('problem_solution');
						if(isset($temp[0])) $one_to_many_join .= $temp[0]; if(isset($temp[1])) $one_to_many_where .= $temp[1];

					// SUN EXPOSURE
						$temp = $this->add_one_to_many_sql('sun_exposure');
						if(isset($temp[0])) $one_to_many_join .= $temp[0]; if(isset($temp[1])) $one_to_many_where .= $temp[1];

					// SPECIAL FEATURE
						$temp = $this->add_one_to_many_sql('special_feature');
						if(isset($temp[0])) $one_to_many_join .= $temp[0]; if(isset($temp[1])) $one_to_many_where .= $temp[1];

					// GROWTH HABIT
						$temp = $this->add_one_to_many_sql('growth_habit');
						if(isset($temp[0])) $one_to_many_join .= $temp[0]; if(isset($temp[1])) $one_to_many_where .= $temp[1];
						
					// COLLECTION NAMES
					//var_dump($this->criteria);exit;
						if(isset($this->criteria['collection_names'])&&count($this->criteria['collection_names'])>0){
						
							$csv = '';
							for($i=0;$i<count($this->criteria['collection_names']);$i++){
								$csv .= ",'" . sql_sanitize($this->criteria['collection_names'][$i]) . "'";
							}
							if($csv!=''){
								$csv = substr($csv,1);
								$one_to_many_where .= "AND collection_name IN ($csv)";
							}
						}

					$query_main .= ' ' . $one_to_many_where;
					//$query = 'SELECT ' . $result_fields . ' FROM plants ' . $one_to_many_join . $query;
					$query .= $one_to_many_join;
			}
			$query_main = substr($query_main,4);
			//die($query_main);

			$query_main = (isset($query_main)?$query_main:'');
			$additional_criteria = (isset($additional_criteria)?$additional_criteria:'');
			$query_cold_zone = (isset($query_cold_zone)?$query_cold_zone:'');
			$one_to_many_join = (isset($one_to_many_join)?$one_to_many_join:'');
			
			if(strpos($this->order_by,'quantity ') === 0){
				$query .= 'LEFT JOIN shop_plant_availibility ON plants.item_number = shop_plant_availibility.item_number ';
				$query_count .= 'LEFT JOIN shop_plant_availibility ON plants.item_number = shop_plant_availibility.item_number ';
				$query_ids .= 'LEFT JOIN shop_plant_availibility ON plants.item_number = shop_plant_availibility.item_number ';
			}
			if($query_main . $additional_criteria . $query_cold_zone!=''){
				if($query_main=='') $additional_criteria = substr($additional_criteria,4);
				$query .= 'WHERE ' . $query_main . $additional_criteria;
				$query_count.= $one_to_many_join . ' WHERE '.$query_main . $additional_criteria;
				$query_ids .= $one_to_many_join . ' WHERE ' . $query_main . $additional_criteria;
			}

			// COLD ZONES
			if($query_cold_zone!=''){
				$joiner = (($query_main . $additional_criteria!='')?' AND ':' ');
				$query .= $joiner . $query_cold_zone . ' UNION ' . $query . $joiner . $query_cold_zone_union;
				$query_count .= $joiner . $query_cold_zone . ' UNION ' . $query_count . $joiner . $query_cold_zone_union;
				$query_ids .= $joiner . $query_cold_zone . ' UNION ' . $query_ids . $joiner . $query_cold_zone_union;
			}
			
			if(strpos($this->order_by,'quantity ') === 0){
				$query .= " AND shop_plant_availibility.quantity > 0 GROUP BY shop_plant_availibility.item_number ";
				$query_count .= " AND shop_plant_availibility.quantity > 0 GROUP BY shop_plant_availibility.item_number ";
				$query_ids .= " AND shop_plant_availibility.quantity > 0 GROUP BY shop_plant_availibility.item_number ";
			}

			$genus_preference = '';

			// IF ORDERING BY RELEVANCE, INSTITUTE GENUS PREFERENCE RULES
			
			if($this->order_by==''||strpos($this->order_by,'relevancy ')===0){

				// GENUS PREFERENCES: SEARCH BY BOTANICAL NAME
				$inflection = new Inflect();
				$query_singularized = ' ' . $inflection->singularize($this->criteria['query']) . ' ';
				if(contains($query_singularized,' rose ')) $genus_preference .= ',' . 'rosa';
				if($query_singularized==' citrus ') $genus_preference .= ',' . 'citrus';
				if(contains($query_singularized,' lemon ')||contains($query_singularized,' lime ')||contains($query_singularized,' orange ')||contains($query_singularized,' grapefruit ')||contains($query_singularized,' kumquat ')||contains($query_singularized,' tangelo ')||contains($query_singularized,' tangerine ')) $genus_preference .= ',' . 'citrus';
				if(contains($query_singularized,' lavender ')) $genus_preference .= ',' . 'lavandula';
				if(contains($query_singularized,' lilac ')) $genus_preference .= ',' . 'syringa,ceanothus,vitex';
				if(contains($query_singularized,' banana ')) $genus_preference .= ',' . 'musa,ensete,musella';
				if(contains($query_singularized,' blueberry ')) $genus_preference .= ',' . 'vaccinium';
				if(contains($query_singularized,' cherry ')) $genus_preference .= ',' . 'prunus,eugenia';
				if(contains($query_singularized,' maple ')) $genus_preference .= ',' . 'acer,abutilon';
				if(contains($query_singularized,' cranberry ')) $genus_preference .= ',' . 'viburnum';
				if(contains($query_singularized,' grape ')) $genus_preference .= ',' . 'vitis,mahonia';
				if(contains($query_singularized,' raspberry ')||contains($query_singularized,' rasberry ')) $genus_preference .= ',' . 'rubus';
				if(contains($query_singularized,' strawberry ')) $genus_preference .= ',' . 'fragaria,arbutus';
				if(contains($query_singularized,' fuchsia ')) $genus_preference .= ',' . 'fuchsia';
				if(contains($query_singularized,' fig ')) $genus_preference .= ',' . 'ficus';
				if(contains($query_singularized,' gardenia ')) $genus_preference .= ',' . 'gardenia';

				// GENUS PREFERENCES: SEARCH BY GENUS
				if($genus_preference==''){
					if(strpos(','.$this->genuses,','.strtolower($this->criteria['query']).',')!==false) $genus_preference = $this->criteria['query'];
				}

				// CLEAN UP
				$genus_preference = str_replace(' ','',$genus_preference);
				$genus_preference = str_replace(',','|',trim(trim($genus_preference),','));

				if(isset($genus_preference)&&$genus_preference!=''){
					$genus_preference = 'botanical_name REGEXP "^('.$genus_preference.')" DESC,';
				}
			}

			$query .= ' ORDER BY '.$genus_preference.$this->order_by.' ';
			$query_ids .= ' ORDER BY '.$genus_preference.$this->order_by.' ';// ASC LIMIT '.$_SESSION['total_plants'];
			//$query_count .= '';

			// CLEAN UP SPACES
			$query = trim(str_replace('  ',' ',$query));
			$query_count = trim(str_replace('  ',' ',$query_count));
			$query_ids = trim(str_replace('  ',' ',$query_ids));
            
            // GET TOTAL RECORDS
			 $results_count = mysql_query($query_count);
			#B$results_count = $wpdb->get_results($query_count);
			 $this->results_total = mysql_num_rows($results_count);
			#B$this->results_total = $wpdb->num_rows;

			// CALCULATE PAGINATION INFO
			if($this->results_per_page==0) $this->results_per_page = $this->results_total;
			$this->results_pages = ceil($this->results_total/$this->results_per_page);

			$record_offset = ($this->results_start_page-1) * $this->results_per_page;

			$query .= ' LIMIT ' . $record_offset . ',' . $this->results_per_page;
			//mail('brettex@hotmail.com', 'Monrovia Search', $query);
			
			 $query_result = mysql_query($query);
			#B$query_result = $wpdb->get_results($query);
			//$num_rows = $this->results_total;
			$num_rows='';
			if($query_result){
				 $num_rows = mysql_num_rows($query_result);
				#B$num_rows = $wpdb->num_rows; 
			}

			if($num_rows==''){
				//$queue = array();
			}else{
				for($i=0;$i<$num_rows;$i++){
					$result = '';
					 $id = mysql_result($query_result,$i,"id");
					#B$id = $query_result[$i]->id;
					// GET ROW
					if($this->result_fields=='ALL'){
						$result = new plant($id);
					}else{
						$result_fields = explode(',',$this->result_fields);
						$result = new plant();
						$result->info['id'] = $id;
						foreach($result_fields as &$result_field){
							$result->info[$result_field] = mysql_result($query_result,$i,$result_field);
							#B $result->info[$result_field] = $query_result[$i]->$result_field;
						}
					}
					if($this->result_fields=='ALL'||$this->include_images){
						$result->get_images();
					}else{
						$result->get_primary_image();
					}
					//$result->info['is_available'] = (mysql_result($query_result,$i,'itemNo')!='');
					$result->info['is_available'] = "1";
					$result->populate_dumb_values();
					$this->results[] = $result;
				}
			}
			if($user_initiated){
				// RESTORE ORIGINAL, SANITIZED QUERY
				$this->criteria['query'] = $this->criteria['query_original'];

				// RETAIN SEARCH CRITERIA
				setcookie('search_criteria',to_json($this->criteria),0,'/');
			}

			// SET PAGINATION HTML
			$this->pagination_html = $this->generate_pagination_html();

		}
		function generate_pagination_html(){
			if($this->view_all||$this->results_total<=$this->results_per_page) return '';
			
			$max_pagination_links = $this->max_pagination_links; // MAXIMUM NUMBER OF PAGINATION LINKS TO SHOW
			$last_page = $this->results_pages;
			$leftmost_page = max($this->results_start_page-floor($max_pagination_links/2),1);
			$rightmost_page = min($leftmost_page+$max_pagination_links-1,$last_page);
			$leftmost_page = max(1,$rightmost_page-$max_pagination_links+1);
			$params = $this->get_url_params();
			$pagination_html = array();
			
			$pagination_html[1] = '';


			$pagination_html[1] .= '<a href="?start_page=1&'.$params. '"'. (($this->results_start_page==1)?' class="selected"':'').' page_num="1">1</a>';
			$num_pagination_links = 1;

			for($i=$leftmost_page;$i<$rightmost_page+1;$i++){
				if($i!=1){
					$pagination_html[$i] = '<a href="?start_page='.$i.'&'.$params.'"' . (($this->results_start_page==$i)?' class="selected"':''). ' page_num="'.$i.'">'.$i.'</a>';
					$num_pagination_links++;
				}
			}

			$pagination_html[$last_page] = '<a href="?start_page='.$last_page.'&'.$params.'"'. (($this->results_start_page==$last_page)?' class="selected"':'') .' page_num="'.$last_page.'">'.$last_page.'</a>';
			$num_pagination_links++;

			// TRIM OFF EXTRA PAGINATION LINKS
			for($i=1;$i<2&&$num_pagination_links>$max_pagination_links;$i++){
				if($leftmost_page>1){
					$pagination_html[$leftmost_page] = '<span>&middot;&middot;&middot;</span>&nbsp;';
					$num_pagination_links--;
				}
				if($rightmost_page<$last_page){
					$pagination_html[$rightmost_page] = '<span>&middot;&middot;&middot;</span>&nbsp;';
					$num_pagination_links--;
				}
			};
			if($last_page>1){
				$view_all_html = ($this->results_total<=200)?'<a href="?view_all=1&'.$params.'" style="background-color:transparent!important" class="lnk_view_all">view all</a>':'';
				$pagination_html = implode('',$pagination_html) . $view_all_html;
			}else{
				$pagination_html = '';
			}
			return $pagination_html;
		}

		function add_one_to_many_sql($list_name){
			$ret = array();
			if(isset($this->criteria[$list_name.'s'])&&count($this->criteria[$list_name.'s'])>0){
				$ret[] = 'INNER JOIN plant_'.$list_name.'_plants ON plants.id=plant_'.$list_name.'_plants.plant_id ';
				$ret[] = 'AND plant_'.$list_name.'_plants.'.$list_name.'_id IN ('.implode(',',$this->criteria[$list_name.'s']).') ';
			}
			return $ret;
		}
		function output_results_cms(){
			if(count($this->results)==0){
				echo('Your search yielded no results.');
			}else{
				for($i=0;$i<count($this->results);$i++){
					?>
						<a href="#">
							<table class="companion_plants_search_result" onclick="add_companion_plant({id:'<?php echo $this->results[$i]->info['id']?>',name:'<?php echo js_sanitize($this->results[$i]->info['common_name'])?>',url:'plant_edit.php?id=<?php echo $this->results[$i]->info['id']?>',item_number:'<?php echo $this->results[$i]->info['item_number']?>',image_url:'<?php echo js_sanitize($this->results[$i]->info['image_primary']->info['path_detail_thumbnail'])?>'});">
								<tr>
									<td width="1"><img src="<?php echo $this->results[$i]->info['image_primary']->info['path_detail_thumbnail']?>" /></td><td><?php echo html_sanitize($this->results[$i]->info['common_name'])?></td><td width="1">#<?php echo $this->results[$i]->info['item_number']?></td>
								</tr>
							</table>
						</a>
					<?php
				}
			}
		}
		function output_results_plant_search($version='default'){

			if($version=='mobile'){
				// MOBILE VERSION
				if(count($this->results)==0){
					echo('<div style="text-align:center;">Your search yielded no results.</div>');
				}else{
					for($i=0;$i<count($this->results);$i++){
						$image_path = get_template_directory_uri()."/img/no-image.gif'";
						if(isset($this->results[$i]->info['image_primary'])) $image_path = $this->results[$i]->info['image_primary']->info['path_detail_thumbnail'];
						?>
							<div class="item leaf shadow green_tip">
								<table>
									<tr>
										<td class="icon">
											<img src="<?php echo $image_path?>" alt="<?php echo html_sanitize($this->results[$i]->info['common_name'])?>" class="search_result_plant_image" />
										</td>
										<td>
											<div class="copy font_size_normal">
												<span class="title"><?php echo html_sanitize($this->results[$i]->info['common_name'])?></span>
												<br />
												<em><?php echo html_sanitize($this->results[$i]->info['botanical_name'])?></em>
												<br />
												#<?php echo $this->results[$i]->info['item_number']?>
											</div>
											<a href="<?php echo $this->results[$i]->info['details_url_mobile']?>" class="overlay"></a>
										</td>
									</tr>
								</table>
							</div>
						<?php
					}
				}
			}else{
				// DESKTOP VERSION
				if(count($this->results)==0){
					echo('<h2 style="margin-top:5px;">Sorry, your search yielded no results.</h2>');
				}else{
					echo "<div class='row clear'>";
					for($i=0;$i<count($this->results);$i++){
						$forsale = isForSale($this->results[$i]->info['item_number']);
						if($forsale == false){
							$in_wish_list = (getUserWishlist($this->results[$i]->info['id'])!='');
						}
						$title_text = ($in_wish_list)?'It\'s a Favorite!':'';
						$image_path = '';
						if(isset($this->results[$i]->info['image_primary'])){
					    	$image_path = $this->results[$i]->info['image_primary']->info['path_search_result'];
							$image_path = str_replace('details_thumbnails', 'search_results', $image_path);
						}
						if($image_path=='') $image_path = get_template_directory_uri()."/img/no-image.png";

						?>
                        <?php if('spokesperson' == get_post_type()){
							if($i == 8){
							// Insert Newsletter Markup
							include_once('signup.php');
								
							}
							
						}?>
							<div class="list-plant left">
								
									<?php
									// SHOW "NEW" FLAG IF MARKED AS "NEW PLANT" OR RELEASE STATUS IS "NA"
									/*if((isset($this->results[$i]->info['is_new'])&&$this->results[$i]->info['is_new']=='1')||(isset($this->results[$i]->info['release_status_id'])&&$this->results[$i]->info['release_status_id']=='2')||(isset($this->results[$i]->info['release_status_id'])&&$this->results[$i]->info['release_status_id']=='3')){*/ ?>
									<div class="image-wrap">
                                    <div class='ajax-loader'></div>
									<?php if(isset($this->results[$i]->info['is_new'])&&$this->results[$i]->info['is_new']=='1'){ ?>
											<div class="flag-new">New Plant</div>
									<?php } ?>
										<a href="<?php echo $this->results[$i]->info['details_url']?>">
											<img src="<?php echo $image_path?>" alt="<?php echo html_sanitize($this->results[$i]->info['common_name'])?>" class="search_result_plant_image" />
                                        </a>
                                        <?php // IF its for sale
										if($forsale){ ?>
											<a href="<?php echo $forsale; ?>" title="Buy Now" target='_blank' class='for-sale clear'><i class="fa fa-shopping-cart"></i><span>Buy Now</span></a>
										<?php } else { //Show favorite actions ?>
                                        <a class="favorite-icon <?php echo ($in_wish_list)?'added':''?>" title="<?php echo $title_text?>" data-pid="<?php echo $this->results[$i]->info['id']?>" data-item="<?php echo $this->results[$i]->info['item_number']?>" data-user="<?php echo(is_user_logged_in())?'true':'false'; ?>" data-action="<?php echo ($in_wish_list)?'remove':'add'?>"></a>
                                        <span class="favorite-text"><?php echo ($in_wish_list)?'Remove from Favorites':'Add to Favorites'?></span>
                                        <?php } // End else ?>
                                    </div><!-- image wrap -->
									<a href="<?php echo $this->results[$i]->info['details_url']?>"><?php echo html_sanitize($this->results[$i]->info['common_name'])?></a>
										<br />
										<span><?php echo html_sanitize($this->results[$i]->info['botanical_name'])?><span><br />
										<span>Item #<?php echo $this->results[$i]->info['item_number']?></span>
										<?php if(isset($this->results[$i]->info['primary_attribute'])&&$this->results[$i]->info['primary_attribute']!=''){ ?>
											<br /><?php echo html_sanitize($this->results[$i]->info['primary_attribute'])?>
										<?php } ?>
										<?php if($version=='patented_plants'){ ?>
											<br /><span class="label_patent">&nbsp;Plant Patent No. <?php echo $this->results[$i]->info['patent']?>&nbsp;</span>
										<?php } ?>
										<?php if($version=='trademarked_plants'){ ?>
											<br />Plant Patent No. <?php echo $this->results[$i]->info['patent']?>
										<?php } ?>
							</div><!-- end list plant -->
                          <?php if((($i+1) % 4 == 0) && ($i+1) != count(($this->results))) echo "</div><div class='row clear'>"; ?> 
                          <?php if(($i+1) == count($this->results)) echo "</div>"; ?>
						<?php
					}
				}
			}
		}
		function get_url_params($omit_sortby = false,$omit_startpage = true,$omit_viewall = true){
			$params = explode('&',$_SERVER['QUERY_STRING']);
			$ret = '';
			foreach($params as $param){
			    if(contains($param,'query=')) $param = str_replace(urlencode('"'), '', $param);
				if($omit_sortby&&contains($param,'sort_by=')) $param = '';
				if($omit_startpage&&contains($param,'start_page=')) $param = '';
				if($omit_viewall&&contains($param,'view_all=')) $param = '';
				if(left($param,2)=='y='||left($param,2)=='x=') $param = '';
				if(!contains($param,'zip=')) $ret .= '&' . $param;
			}
			if($ret!='') $ret = substr($ret,1);
			return trim($ret,'&');
		}
	}
?>