<?php


  	$php_ids_init = '';

	function var_dump_exit($var){
		var_dump($var);
		exit;
	}

	function is_suspicious($data_check){
		/*if(is_cron()) return false;

		if($GLOBALS['php_ids_init']==''){
			require_once($GLOBALS['server_info']['physical_root'].'inc/php_ids/Init.php');
			$GLOBALS['php_ids_init'] = IDS_Init::init($GLOBALS['server_info']['physical_root'].'inc/php_ids/Config/Config.ini.php');
		}
		if(!is_array($data_check)) $data_check = array($data_check);

		if(isset($data_check['newsletter_versions'])) $data_check['newsletter_versions'] = ids_sanitize($data_check['newsletter_versions']);
		if(isset($data_check['permissions'])) $data_check['permissions'] = ids_sanitize(str_replace(',','',$data_check['permissions']));

		$ids = new IDS_Monitor($data_check, $GLOBALS['php_ids_init']);
		$result = $ids->run();
		//if(!$result->isEmpty()) die($result);
		//return $result;
		return (!$result->isEmpty());
		*/
	} 

	function ids_sanitize($txt){
		// REMOVES CHARACTERS THAT PRODUCE FALSE POSITIVES WITH SQL INJECTION SNIFFER
		$txt = html_entity_decode($txt);
		$txt = str_replace('-','',$txt);
		$txt = str_replace('\'','',$txt);
		$txt = str_replace('|','',$txt);
		$txt = str_replace(':','',$txt);
		$txt = str_replace(',','',$txt);
		return $txt;
	}

	function contains($haystack,$needle){
		return (strpos($haystack,$needle)!==false);
	}
	function within($num,$range_low,$range_high){
		return ($num>=$range_low&&$num<=$range_high);
	}
	function valid_email($value){
		return (preg_match('/^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-Z0-9]{2,4}$/',$value)==1);
	}

	function valid_url($value){
		return (preg_match('/http:\/\/[A-Za-z0-9\.-]{3,}\.[A-Za-z]{3}/',$value)==1);
	}

	function is_valid_us_canadian_zip($txt){
		return (is_valid_us_zip($txt)||is_valid_canadian_zip($txt));
	}

	function is_valid_us_zip($txt){
		return (strlen($txt)==5&&is_numeric($txt));
	}

	function is_valid_canadian_zip($txt){
		return preg_match('/^[A-Za-z][0-9][A-Za-z][ ]?[\-]?[0-9][A-Za-z][0-9]$/',$txt);
	}

	function current_url() {
		$pageURL = 'http';
		if (isset($_SERVER["HTTPS"])&&$_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}

	function get_current_season(){
		$ret = array();
		$season_ids = array('Spring'=>'1','Summer'=>'2','Fall'=>'3','Winter'=>'4');
		$SeasonDates = array('/12/21'=>'Winter','/09/21'=>'Fall','/06/21'=>'Summer','/03/21'=>'Spring','/12/31'=>'Winter');
		foreach ($SeasonDates AS $key => $value){
			$SeasonDate = date("Y").$key;
			if (strtotime("now") > strtotime($SeasonDate)){
				$ret['id'] = $season_ids[$value];
				$ret['name'] = $value;
				return $ret;
			}
		}
	}

	function parse_alpha($txt,$allow = ''){
		return preg_replace('/[^a-zA-Z'.$allow.']/', '', $txt);
	}
	function parse_numeric($txt,$allow = ''){
		return preg_replace('/[^0-9'.$allow.']/', '', $txt);
	}
	function parse_alphanumeric($txt,$allow = ''){
		return preg_replace('/[^a-zA-Z0-9'.$allow.']/', '', $txt);
	}

	function unicode_chr($code){
		return html_entity_decode('&#'.$code.';',ENT_NOQUOTES,'windows-1252');
	}

	function unicode_code_to_hex($code){
		$ret = '';
		$char = mb_convert_encoding(unicode_chr($code),'windows-1252');
		for($i=0;$i<strlen($char);$i++){
			$ret .= '\\x' . dechex(ord(substr($char,$i)));
		}

		return $ret;
	}

	function escape_entities($txt){
		if(strpos($txt,'&')===false){
			return $txt;
		}else{
			$matches = preg_split("/([&][^;&]+[;&])/",$txt,null,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
			$ret = '';
			for($i=0;$i<count($matches);$i++){
				if(is_valid_xml_entity($matches[$i])){
					// VALID ENTITY; TAKE AS-IS
					$ret .= $matches[$i];
				}else{
					// INVALID ENTITY; REPLACE AMPS
					$ret .= str_replace('&','&amp;',$matches[$i]);
				}
			}
		}
		return $ret;
	}

	function is_valid_xml_entity($txt){
		return (@simplexml_load_string("<?xml version=\"1.0\"?><a>$txt</a>")!==false);
	}

	function str_replace_unicode($code,$s_replace,$s_text){
		if($code<256){
			return str_replace(chr($code),$s_replace,$s_text);
		}else{
			return mb_eregi_replace(unicode_code_to_hex($code),$s_replace,$s_text);
		}
	}

	function get_page_path(){
		$path = $_SERVER['PHP_SELF'];
		$url_redirect = '';
		if(isset($_SERVER['REDIRECT_URL'])) $url_redirect = $_SERVER['REDIRECT_URL'];
		if($url_redirect!='') $path = $url_redirect;

		//if(contains($_SERVER['REQUEST_URI'],'press_release.php?id=')) $path .= '?id=' . $_GET['id'];

		if(contains($_SERVER['REQUEST_URI'],'/event-calendar/detail.php?event=')) $path .= '?event=' . $_GET['event'];

		$path = str_replace('//','/',$path);
		$path = str_replace(' ','',$path); // NULLIFIES SQL INJECTIONS

		$path = rtrim($path,'/');

		return strtolower($path);
	}
	function get_page_id(){
		// SQL INJECTION-SAFE
		$page_url = get_page_path();
		if(contains($page_url,'/press-releases/')){
			$page_base_url = substr($page_url,0,strrpos($page_url,'/')+1);
			$sql = "SELECT id FROM pages WHERE path LIKE '$page_base_url%' LIMIT 1";
		}else{
			$sql = "SELECT id FROM pages WHERE path='$page_url' LIMIT 1";
		}
		$result = sql_query($sql);
		return @mysql_result($result,0,"id");
	}

	function send_email($to,$subject,$html,$text,$bcc_admin){

		// CAN ACCEPT MULTIPLE "TO" ADDRESSES VIA ARRAY

		if(!is_array($to)) $to = array($to);

		if($GLOBALS['server_info']['environment']=='dev') return true;

		@include_once($GLOBALS['server_info']['physical_root'].'inc/htmlMimeMail5/htmlMimeMail5.php');
		@include_once('/var/www/monrovia.com/root/inc/htmlMimeMail5/htmlMimeMail5.php');				// LIVE
		@include_once('/var/www/vhosts/tpgphpdev1.net/httpdocs/inc/htmlMimeMail5/htmlMimeMail5.php');	// STAGING

		$mail = new htmlMimeMail5();
		$mail->setHTMLCharset('UTF-8');
		$mail->setHeadCharset('UTF-8');
		$mail->setTextCharset('UTF-8');
		//if($_SERVER["HTTP_HOST"]!='') $mail->setFrom('Monrovia Website <website@monrovia.com>');
		if($GLOBALS['server_info']['environment']=='prod') $mail->setFrom('Monrovia Website <website@monrovia.com>');
		$mail->setSubject($subject);

		if($text=='') $text = $html;

		$mail->setText($text);
		$mail->setHTML($html);
		if($GLOBALS['admin_email']!=''&&$bcc_admin&&$GLOBALS['admin_email']!=$to[0]) $mail->setBcc($GLOBALS['admin_email']);
		$result = @$mail->send($to);
		return ($result===true);
	}

	function right($value, $count){
	    return substr($value, ($count*-1));
	}

	function left($string, $count){
	    return substr($string, 0, $count);
	}

	function to_mysql_boolean_mode($txt,$strict = false){
		require_once('classes/class_inflection.php');

		$ret_original = '';
		$ret_singular = '';
		if(contains($txt,'"')){
			// IF USER SEARCHES WITH QUOTES, LOOK FOR EXACT MATCHES
			$ret = $txt;
		}else{
			$txt_original = $txt;
			$txt = mysql_replace_stopwords($txt);
			$inflection = new Inflect();
			$words = explode(' ',$txt);
			$ret = '';
			foreach($words as $word){
				if($strict){
					$ret_original .= '+' . $word .  ' ';
				}else{
					if(right($word,2)!='us'){
						$word_singularized = $inflection->singularize($word);
						if($word_singularized!=$word){
							$ret_singular .= $word_singularized . ' ';
						}else{
							$ret_original .= '+' . $word .  ' ';
						}
					}else{
						$ret_original .= '+' . $word . ' ';
					}
				}
			}
			$ret = trim($ret_original) . ' ' . trim($ret_singular);
			if(trim($txt_original)!='') $ret = '"' . trim($txt_original) . '" ' . $ret;
		}
		$ret = trim($ret);
		if($ret=='+') $ret = '';

		$ret = sql_sanitize($ret);

		return $ret;
	}

	//die(to_mysql_boolean_mode('red flowers'));

	function title_case($string) {
	   $temp = preg_split('/(\W)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE );
	   foreach ($temp as $key=>$word) {
		   $temp[$key] = ucfirst(strtolower($word));
	   }
	   $temp = join('', $temp);
	   $temp = str_replace('\'S ','\'s ',$temp);
	   return $temp;
	}


	function generate_url_friendly_string($txt,$length = null){
		if(is_null($length)) $length = 255;
		
		$txt = strtolower($txt);
		$txt = str_replace('&#153;','',$txt);
		$txt = str_replace('&trade;','',$txt);
		$txt = str_replace('&reg;','',$txt);
		$txt = str_replace('&#174;','',$txt);
		$txt = str_replace('&#153;','',$txt);
		$txt = mysql_replace_stopwords($txt);
		$txt = truncate($txt,$length,false,false);
		$txt = trim(str_replace(' ','-',parse_alphanumeric($txt,'\\- ')),'-');
		$txt = str_replace('--','-',$txt);
		$txt = str_replace(' ','-',$txt);
		$txt = trim(trim($txt,'-'));
		
		return $txt;
	}


	function wysiwyg_strip_tags($html){
		$html = str_replace('<div','<p',$html);
		$html = str_replace('</div','</p',$html);
		$html = str_replace('font-size:',':',$html);
		$html = str_replace('margin:',':',$html);
		$html = str_replace('text-indent:',':',$html);
		return strip_tags($html,'<a><b><i><u><strong><ul><li><ol><span><p><br><img><table><thead><tbody><tr><td>');
	}

	function truncate($string, $length, $stopanywhere=false, $add_ellipsis = true, $stoponsentence=false) {
		//truncates a string to a certain char length, stopping on a word if not specified otherwise.
		if (strlen($string) > $length) {
			//limit hit!
			$string = substr($string,0,($length -3));
			if ($stopanywhere) {
				//stop anywhere
				if($add_ellipsis) $string .= '...';
			} else{
			    if (!$stoponsentence){
    				//stop on a word.
    				$string = substr($string,0,strrpos($string,' '));
    				$last_char = substr($string,strlen($string)-1);
    				if($last_char==',') $string = substr($string,0,strlen($string)-1);
                } else {
                    //stop on the last sentence within the specified length
                    $string = substr($string,0,strrpos($string,'.'));
                    $string .= '.';
                }
				if($add_ellipsis) $string .= '...';
			}
		}
		return $string;
	}

	function mysql_replace_stopwords($txt){
		$stop_words = array('a','able','about','above','according','accordingly','across','actually','after','afterwards','again','against','ain\'t','all','allow','allows','almost','alone','along','already','also','although','always','am','among','amongst','an','and','another','any','anybody','anyhow','anyone','anything','anyway','anyways','anywhere','apart','appear','appreciate','appropriate','are','aren\'t','around','as','aside','ask','asking','associated','at','available','away','awfully','be','became','because','become','becomes','becoming','been','before','beforehand','behind','being','believe','below','beside','besides','best','better','between','beyond','both','brief','but','by','c\'mon','c\'s','came','can','can\'t','cannot','cant','cause','causes','certain','certainly','changes','clearly','co','com','come','comes','concerning','consequently','consider','considering','contain','containing','contains','corresponding','could','couldn\'t','course','currently','definitely','described','despite','did','didn\'t','different','do','does','doesn\'t','doing','don\'t','done','down','downwards','during','each','edu','eg','eight','either','else','elsewhere','enough','entirely','especially','et','etc','even','ever','every','everybody','everyone','everything','everywhere','ex','exactly','example','except','far','few','fifth','first','five','followed','following','follows','for','former','formerly','forth','four','from','further','furthermore','get','gets','getting','given','gives','go','goes','going','gone','got','gotten','greetings','had','hadn\'t','happens','hardly','has','hasn\'t','have','haven\'t','having','he','he\'s','hello','help','hence','her','here','here\'s','hereafter','hereby','herein','hereupon','hers','herself','hi','him','himself','his','hither','hopefully','how','howbeit','however','i','i\'d','i\'ll','i\'m','i\'ve','ie','if','ignored','immediate','in','inasmuch','inc','indeed','indicate','indicated','indicates','inner','insofar','instead','into','inward','is','isn\'t','it','it\'d','it\'ll','it\'s','its','itself','just','keep','keeps','kept','know','knows','known','last','lately','later','latter','latterly','least','less','lest','let','let\'s','like','liked','likely','little','look','looking','looks','ltd','mainly','many','may','maybe','me','mean','meanwhile','merely','might','more','moreover','most','mostly','much','must','my','myself','name','namely','nd','near','nearly','necessary','need','needs','neither','never','nevertheless','new','next','nine','no','nobody','non','none','noone','nor','normally','not','nothing','novel','now','nowhere','obviously','of','off','often','oh','ok','okay','old','on','once','one','ones','only','onto','or','other','others','otherwise','ought','our','ours','ourselves','out','outside','over','overall','own','particular','particularly','per','perhaps','placed','please','plus','possible','presumably','probably','provides','que','quite','qv','rather','rd','re','really','reasonably','regarding','regardless','regards','relatively','respectively','right','said','same','saw','say','saying','says','second','secondly','see','seeing','seem','seemed','seeming','seems','seen','self','selves','sensible','sent','serious','seriously','seven','several','shall','she','should','shouldn\'t','since','six','so','some','somebody','somehow','someone','something','sometime','sometimes','somewhat','somewhere','soon','sorry','specified','specify','specifying','still','sub','such','sup','sure','t\'s','take','taken','tell','tends','th','than','thank','thanks','thanx','that','that\'s','thats','the','their','theirs','them','themselves','then','thence','there','there\'s','thereafter','thereby','therefore','therein','theres','thereupon','these','they','they\'d','they\'ll','they\'re','they\'ve','think','third','this','thorough','thoroughly','those','though','three','through','throughout','thru','thus','to','together','too','took','toward','towards','tried','tries','truly','try','trying','twice','two','un','under','unfortunately','unless','unlikely','until','unto','up','upon','us','use','used','useful','uses','using','usually','value','various','very','via','viz','vs','want','wants','was','wasn\'t','way','we','we\'d','we\'ll','we\'re','we\'ve','welcome','well','went','were','weren\'t','what','what\'s','whatever','when','whence','whenever','where','where\'s','whereafter','whereas','whereby','wherein','whereupon','wherever','whether','which','while','whither','who','who\'s','whoever','whole','whom','whose','why','will','willing','wish','with','within','without','won\'t','wonder','would','would','wouldn\'t','yes','yet','you','you\'d','you\'ll','you\'re','you\'ve','your','yours','yourself','yourselves','zero');

		foreach($stop_words as $stop_word){
			$txt = str_replace(' ' . $stop_word . ' ',' ',$txt);
		}
		return $txt;

	}

	function get_url($url,$timeout = 10){
		$context = stream_context_create(array(
			'http' => array(
				'timeout' => $timeout
				)
			)
		);
		return @file_get_contents(str_replace(' ','%20',$url),0,$context);
	}

	function get_url_async($data, $options = array()) {

	  // array of curl handles
	  $curly = array();
	  // data to be returned
	  $result = array();

	  // multi handle
	  $mh = curl_multi_init();

	  // loop through $data and create curl handles
	  // then add them to the multi-handle
	  foreach ($data as $id => $d) {

		$curly[$id] = curl_init();

		$url = (is_array($d) && !empty($d['url'])) ? $d['url'] : $d;
		curl_setopt($curly[$id], CURLOPT_URL,            $url);
		curl_setopt($curly[$id], CURLOPT_HEADER,         0);
		curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER, 1);

		// post?
		if (is_array($d)) {
		  if (!empty($d['post'])) {
			curl_setopt($curly[$id], CURLOPT_POST,       1);
			curl_setopt($curly[$id], CURLOPT_POSTFIELDS, $d['post']);
		  }
		}

		// extra options?
		if (!empty($options)) {
		  curl_setopt_array($curly[$id], $options);
		}

		curl_multi_add_handle($mh, $curly[$id]);
	  }

	  // execute the handles
	  $running = null;
	  do {
		curl_multi_exec($mh, $running);
	  } while($running > 0);

	  // get content and remove handles
	  foreach($curly as $id => $c) {
		$result[$id] = curl_multi_getcontent($c);
		curl_multi_remove_handle($mh, $c);
	  }

	  // all done
	  curl_multi_close($mh);

	  return $result;
	}


	function page_log($msg){
		$GLOBALS['page_log'][] = $msg;
	}

	function json_init(){
		if(!isset($GLOBALS['json'])||$GLOBALS['json']==''){
			require_once('classes/class_json.php');
			$GLOBALS['json'] = new Services_JSON();
		}
	}

	function to_json($value){
		json_init();
		return $GLOBALS['json']->encode($value);
	}

	function from_json($value){
		$value = trim($value);
		if($value=='') return;
		json_init();
		return $GLOBALS['json']->decode($value);
	}

	function get_browser_info(){
		$GLOBALS['browser_info'] = array();

		$GLOBALS['browser_info']['medium'] = 'desktop';

		if(isset($_SERVER ['HTTP_USER_AGENT'])){

			$user_agent = strtolower($_SERVER ['HTTP_USER_AGENT']);

			$GLOBALS['browser_info']['description'] = $user_agent;

			// DESKTOP BROWSERS
			if(contains($user_agent,"opera")){
				$GLOBALS['browser_info']['family'] = 'opera';
				$GLOBALS['browser_info']['name'] = 'opera';
			}
			if(contains($user_agent,"firefox")){
				$GLOBALS['browser_info']['family'] = 'mozilla';
				$GLOBALS['browser_info']['name'] = 'firefox';
			}
			if(contains($user_agent,"msie")){
				$GLOBALS['browser_info']['family'] = 'ie'; // SHOULD TECHNICALLY BE "trident" or "lynx"
				$GLOBALS['browser_info']['name'] = 'ie';
			}
			if(contains($user_agent,"safari")){
				$GLOBALS['browser_info']['family'] = 'webkit';
				$GLOBALS['browser_info']['name'] = 'safari';
			}
			if(contains($user_agent,"chrome")){
				$GLOBALS['browser_info']['family'] = 'webkit';
				$GLOBALS['browser_info']['name'] = 'chrome';
			}

			//if(isset($GLOBALS['browser_info']['name'])) $GLOBALS['browser_info']['family'] = $GLOBALS['browser_info']['name'];

			// MOBILE BROWSERS

			$mobile_signatures = explode(',','up.browser,up.link,mmp,symbian,smartphone,midp,wap,phone,windows ce,pda,mobile,mini,palm,android,blackberry');

			for($i=0;$i<count($mobile_signatures);$i++){
				if(contains($user_agent,$mobile_signatures[$i])){
					$GLOBALS['browser_info']['name'] = 'unknown';
					$GLOBALS['browser_info']['family'] = 'unknown';
					$GLOBALS['browser_info']['medium'] = 'mobile';
				}
			}

			if(contains($user_agent,"ipad")){
				$GLOBALS['browser_info']['name'] = 'ipad';
				$GLOBALS['browser_info']['family'] = 'ios';
				$GLOBALS['browser_info']['medium'] = 'tablet';
			}
			if(contains($user_agent,"iphone")){
				$GLOBALS['browser_info']['name'] = 'iphone';
				$GLOBALS['browser_info']['family'] = 'ios';
				$GLOBALS['browser_info']['medium'] = 'mobile';
			}
			if(contains($user_agent,"ipod")){
				$GLOBALS['browser_info']['name'] = 'ipod';
				$GLOBALS['browser_info']['family'] = 'ios';
				$GLOBALS['browser_info']['medium'] = 'mobile';
			}
			if(contains($user_agent,"android")){
				$GLOBALS['browser_info']['name'] = 'android';
				$GLOBALS['browser_info']['family'] = 'android';
				$GLOBALS['browser_info']['medium'] = 'mobile';
			}
			if(contains($user_agent,"blackberry")){
				$GLOBALS['browser_info']['name'] = 'blackberry';
				$GLOBALS['browser_info']['family'] = 'blackberry';
				$GLOBALS['browser_info']['medium'] = 'mobile';
			}
		}
	}

	function get_friendly_sincestamp($timestamp){
		$minutes_since_tweet = (time()-$timestamp)/60;
		$hours_since_tweet = $minutes_since_tweet / 60;
		$days_since_tweet = $hours_since_tweet / 24;
		$weeks_since_tweet = $days_since_tweet / 7;

		if($weeks_since_tweet>=1){
			if($weeks_since_tweet>=2){
				$weeks_since_tweet = round($weeks_since_tweet);
				$ret = $weeks_since_tweet . ' weeks ago';
			}else{
				$ret = 'last week';
			}
		}else if($days_since_tweet>=2){
			$days_since_tweet = round($days_since_tweet);
			$ret = $days_since_tweet . ' days ago';
		}else if($hours_since_tweet>=1){
			$hours_since_tweet = round($hours_since_tweet);
			$ret = 'about ' . (($hours_since_tweet>1)?$hours_since_tweet . ' hours':'an hour') . ' ago';
		}else{
			$minutes_since_tweet = max(round($minutes_since_tweet),1);
			$ret = $minutes_since_tweet . (($minutes_since_tweet>1)?' minutes':' minute') . ' ago';
		}
		return $ret;
	}

	function charset_decode_utf_8 ($string) {
	      /* Only do the slow convert if there are 8-bit characters */
	    /* avoid using 0xA0 (\240) in ereg ranges. RH73 does not like that */
	    if (! ereg("[\200-\237]", $string) and ! ereg("[\241-\377]", $string))
	        return $string;

	    // decode three byte unicode characters
	    $string = preg_replace("/([\340-\357])([\200-\277])([\200-\277])/e",
	    "'&#'.((ord('\\1')-224)*4096 + (ord('\\2')-128)*64 + (ord('\\3')-128)).';'",
	    $string);

	    // decode two byte unicode characters
	    $string = preg_replace("/([\300-\337])([\200-\277])/e",
	    "'&#'.((ord('\\1')-192)*64+(ord('\\2')-128)).';'",
	    $string);

	    return $string;
	}

	function activate_links($txt){
		// ADDS ANCHOR TAGS TO STATIC TEXT
		$txt = str_replace('  ',' ',$txt);
		$words = explode(' ',$txt);
		$ret = '';
		foreach($words as $word){
			if(strpos($word,'http://')===0) $word = '<a href="'.$word.'" target="_blank" google_event_tag="Home Page - Right Module 4|Click|Twitter Link">'.$word.'</a>';
			$ret .= ' ' . $word;
		}
		return trim($ret);
	}

	function replace_smart_characters($value){
		// CONVERT TO UNICODE

		// PRE-CONVERSION REPLACEMENTS
		$value = mb_eregi_replace('\xe2\x80\x9a','\'',$value);

		$value = iconv('windows-1252','UTF-8',$value);

		$value = str_replace_unicode(8216,"'", $value);
		$value = str_replace_unicode(8217,"'", $value);

		$value = str_replace_unicode(8218,"'", $value);

		$value = str_replace_unicode(8219,"'", $value);
		$value = str_replace_unicode(8220,'"', $value);
		$value = str_replace_unicode(8221,'"', $value);
		$value = str_replace_unicode(8222,'"', $value);
		$value = str_replace_unicode(8223,'"', $value);
		$value = str_replace_unicode(8211,"-", $value);
		$value = str_replace_unicode(8212,"--", $value);
		$value = str_replace_unicode(8230,"...", $value);
//		$value = str_replace_unicode(8482,chr(153), $value); // CAUSES STRINGS TO CUT OFF AT TMs

		// APOS
		$value = str_replace_unicode(8216, chr(39), $value);
		$value = str_replace_unicode(8217, chr(39), $value);

		// QUOT
		$value = str_replace_unicode(8220, chr(34), $value);
		$value = str_replace_unicode(8221, chr(34), $value);

		// CONVERT BACK TO ANSI
		$value = iconv('UTF-8','windows-1252',$value);

		// DASH
		$value = str_replace_unicode(149,"-",$value);
		$value = str_replace_unicode(150,"-",$value);
		$value = str_replace_unicode(151,"--",$value);

		$value = str_replace_unicode(96, chr(39), $value);
		$value = str_replace_unicode(145, chr(39), $value);
		$value = str_replace_unicode(146, chr(39), $value);
		$value = str_replace_unicode(180, chr(39), $value);

		// OTHER
		$value = str_replace_unicode(160,' ', $value);
		$value = str_replace_unicode(133,'...', $value);
		$value = str_replace_unicode(130,'\'', $value);
		//$value = str_replace(chr(132),'"', $value); // BREAKS (TM)s OR QUOTES--ONE OF THOSE?
		$value = str_replace_unicode(147,'"', $value);
		$value = str_replace_unicode(148,'"', $value);
		$value = str_replace_unicode(145,'\'', $value);
		$value = str_replace_unicode(146,'\'', $value);

		return $value;
	}
	function unescape_special_characters($value){
		$value = str_replace('{{#153}}','&#153;', $value);
		$value = str_replace('{{#174}}','&#174;', $value);
		$value = str_replace('{{#169}}','&#169;', $value);
		$value = str_replace('{{#176}}','&#176;', $value);
		$value = str_replace('{{#188}}','&#188;', $value);
		$value = str_replace('{{#189}}','&#189;', $value);
		$value = str_replace('{{#190}}','&#190;', $value);
		$value = str_replace('{{#187}}','&#187;', $value);
		$value = str_replace('{{#171}}','&#171;', $value);
		$value = str_replace('{{#215}}','&#215;', $value);
		$value = str_replace('{{#232}}','&#232;', $value);
		$value = str_replace('{{#233}}','&#233;', $value);

		$value = str_replace('{{#96}}','\'', $value);
		$value = str_replace('{{#180}}','\'', $value);
		$value = str_replace('{{#8216}}','\'', $value);
		$value = str_replace('{{#8217}}','\'', $value);
		$value = str_replace('{{#8218}}','\'', $value);
		$value = str_replace('{{#8219}}','\'', $value);
		$value = str_replace('{{#8220}}','"', $value);
		$value = str_replace('{{#8221}}','"', $value);
		$value = str_replace('{{#8222}}','"', $value);
		$value = str_replace('{{#8223}}','"', $value);
		$value = str_replace('{{#8230}}','...', $value);


		$value = str_replace('{{AMP}}','&',$value);
		$value = str_replace('{{PERCENT}}','%',$value);
		$value = str_replace('{{HASH}}','#',$value);
		$value = str_replace('{{QUESTION}}','?',$value);
		return $value;
	}
	function special_entities($value){
		// TRADE
		$value = str_replace_unicode(8482,"&trade;",$value);
		$value = str_replace_unicode(153,"&trade;",$value);

		// COPY
		$value = str_replace_unicode(169,"&copy;",$value);

		// REG
		$value = str_replace_unicode(174,"&reg;",$value);

		// OTHER SYMBOLS
		$value = str_replace_unicode(189,"&#189;",$value); // 1/2
		$value = str_replace_unicode(188,"&#188;",$value); // 1/4
		$value = str_replace_unicode(190,"&#190;",$value); // 3/4
		return $value;
	}
	function html_sanitize($value,$escape_html_tags=true){
		$value = replace_smart_characters($value);
		$value = unescape_special_characters($value);
		$value = special_entities($value);
		if($escape_html_tags){
			$value = str_replace('<','&lt;',$value);
			$value = str_replace('>','&gt;',$value);
			$value = str_replace('"','&quot;',$value);
			$value = str_replace("\'",'&apos;',$value);
			$value = str_replace("& ",'&amp; ',$value);

			// ALLOW <em> TAGS
			$value = str_ireplace('&lt;em&gt;','<em>',$value);
			$value = str_ireplace('&lt;/em&gt;','</em>',$value);

		}
		return $value;
	}

	function js_sanitize($value,$escape_html_tags=true){
		$value = html_sanitize($value,$escape_html_tags);

		$value = str_replace('\'','\\\'',$value);
		$value = str_replace('""','"',$value); // ?

		//$value = html_entity_decode($value);
		return $value;
	}

	function clear_cache(){
		/*$directory = $GLOBALS['server_info']['physical_root'] . 'cache';
		try {
			if(is_dir($directory)){
				if($directory_handle = opendir($directory)){
					while(($file_name = readdir($directory_handle))!==false){
						if(substr($file_name,0,6)=='cache_') unlink("$directory/$file_name");
					}
					closedir($directory_handle);
				}
			}
			return true;
		}catch(Exception $err){
			return false;
		} */
	}

	function object_to_array($arrObjData, $arrSkipIndices = array())
	{
		$arrData = array();

		// if input is object, convert into array
		if (is_object($arrObjData)) {
			$arrObjData = get_object_vars($arrObjData);
		}

		if (is_array($arrObjData)) {
			foreach ($arrObjData as $index => $value) {
				if (is_object($value) || is_array($value)) {
					$value = object_to_array($value, $arrSkipIndices); // recursive call
				}
				if (in_array($index, $arrSkipIndices)) {
					continue;
				}
				$arrData[$index] = $value;
			}
		}
		return $arrData;
	}

	function generate_email_html($message){
		return str_replace('{message}',$message,file_get_contents('/nas/wp/www/cluster-1975/monrovia/email_templates/generic.htm'));
	}

	function is_cron(){
		return !isset($_SERVER['HTTP_HOST'])&&isset($_SERVER['argv']);
	}

	function mark_sidebar_current($link_id){
	?>
		<style>#<?php echo $link_id?> a { color:#a1cc50!important; cursor:default; }</style>
	<?php
	}

    function generate_custom_thumbnail($width,$height,$server_path_original,$server_path_detail,$output_dest,$horizontal_alignment = null,$vertical_alignment = null){
        if(is_null($horizontal_alignment)) $horizontal_alignment = 'center';
        if(is_null($vertical_alignment)) $vertical_alignment = 'center';

            // THIS IS USED BY THE CATALOG CREATOR. IT STRETCHES THE ORIGINAL IMAGE TO FIT WITHIN THE BOUNDS PROVIDED, THEN CROPPING.
            // RETURN VALUE: RAW JPEG DATA
            try {
                //$dest_filename = $GLOBALS['server_info']['physical_root'].'temp/plant_image_set_' . $this->info['id'] . '_' . time() . '_' . rand() . '.jpg';

                if(file_exists($server_path_original)){
                    $src = imagecreatefromjpeg($server_path_original);
                }else{
                    if(!file_exists($server_path_detail)) throw new Exception('Section: generate_custom_thumbnail; error: file not found');
                    $src = imagecreatefromjpeg($server_path_detail);
                }

                $src_width = imagesx($src);
                $src_height = imagesy($src);

                $aspect_ratio_src = $src_width / $src_height;
                $aspect_ratio_dest = $width / $height;

                // CALCULATE DIFFERENCES IN DIMENSIONS OF SOURCE AND DESTINATION IMAGES TO DETERMINE WHICH DIMENSION TO USE AS BASE
                $width_diff = $src_width - $width;
                $height_diff = $src_height - $height;

                if($width_diff>$height_diff){
                    // IMAGES ARE CLOSER IN HEIGHT THAN IN WIDTH; USE HEIGHT
                    $dest_width = $width * $aspect_ratio_src;
                    $dest_height = $height;
                }else if($width_diff<$height_diff){
                    // IMAGES ARE CLOSER IN WIDTH THAN IN WIDTH; USE WIDTH
                    $dest_width = $width;
                    $dest_height = $height / $aspect_ratio_src;
                }else{
                    $dest_width = $width;
                    $dest_height = $height;
                }
                // AT THIS POINT, DESTINATION DIMENSIONS ARE ALWAYS EQUAL TO OR GREATER THAN REQUESTED DIMENSIONS ($width, $height)

                // ALIGNMENT CALCULATIONS
                $dest_x = 0; $dest_y = 0;
                if($horizontal_alignment=='right'){
                    $dest_x = $width - $dest_width;
                }else if($horizontal_alignment=='center'){
                    $dest_x = -(($dest_width-$width)/2);
                }
                if($vertical_alignment=='bottom'){
                    $dest_y = $height - $dest_height;
                }else if($vertical_alignment=='center'){
                    $dest_y = -(($dest_height-$height)/2);
                }

                $dest = imagecreatetruecolor($width, $height);

                imagecopyresampled($dest,$src,$dest_x,$dest_y,0,0,$dest_width,$dest_height,$src_width,$src_height);

                imagejpeg($dest,$output_dest,100);
                imagedestroy($dest);
                return true;
                //return $dest_filename;
            }catch(Exception $err){
                return false;
            }
    }

    function recursive_delete($str){
            if(is_file($str)){
                return @unlink($str);
            }
            elseif(is_dir($str)){
                $scan = glob(rtrim($str,'/').'/*');
                foreach($scan as $index=>$path){
                    recursive_delete($path);
                }
                return @rmdir($str);
            }
        }

	function conditional_device_redirect($medium,$url){

		// DISABLE MOBILE BY ADDING "mobile=off" TO URL
		// DISABLE MOBILE AND DESKTOP REDIRECTS BY ADDING "redirects=off" TO URL

		if(isset($_SERVER["HTTP_HOST"])){

			$mobile_version_url = str_replace('mobile=off','mobile=on',$url);
			if(strpos($mobile_version_url,'mobile=on')===false) {
				if(strpos($mobile_version_url,'?')===false){
					$mobile_version_url .= '?mobile=on';
				}else{
					$mobile_version_url .= '&mobile=on';
				}
			}
			$GLOBALS['mobile_version_url'] = $mobile_version_url;

			$redirects_flag = '';
			$mobile_flag = '';

			// DISABLE MOBILE REDIRECTS
			if(isset($_GET['mobile'])&&$_GET['mobile']=='off'){
				setcookie('mobile','off',0,'/','.'.$_SERVER['SERVER_NAME']);
				$mobile_flag = 'off';
			}

			// ENABLE MOBILE REDIRECTS
			if(isset($_GET['mobile'])&&$_GET['mobile']=='on'){
				setcookie('mobile','',0,'/','.'.$_SERVER['SERVER_NAME']);
			}

			// DISABLE MOBILE AND DESKTOP REDIRECTS
			if(isset($_GET['redirects'])&&$_GET['redirects']=='off'){
				setcookie('redirects','off',0,'/','.'.$_SERVER['SERVER_NAME']);
				$redirects_flag = 'off';
			}

			// ENABLE MOBILE AND DESKTOP REDIRECTS
			if(isset($_GET['redirects'])&&$_GET['redirects']=='on'){
				setcookie('redirects','',0,'/','.'.$_SERVER['SERVER_NAME']);
			}

			if(isset($_COOKIE['mobile'])&&$_COOKIE['mobile']=='off') $mobile_flag = 'off';
			if(isset($_COOKIE['redirects'])&&$_COOKIE['redirects']=='off') $redirects_flag = 'off';

			if($redirects_flag!='off'){
				if($medium!='mobile'||$mobile_flag!='off'){
					if($medium==$GLOBALS['browser_info']['medium']){
						header('location:' . $url);
						exit;
					}
				}
			}
		}
	}

	function do_geocode($query){
		$result = array('success'=>false);

		try {
			$google_maps_api_url = 'http://maps.google.com/maps/api/geocode/json?sensor=false&address=' . urlencode($query);
			$google_maps_result = json_decode(file_get_contents($google_maps_api_url));
			$result['original_response'] = $google_maps_result;
			//$google_maps_result->status = 'OVER_QUERY_LIMIT';
			if ($google_maps_result->status != 'OK' ){
				if($google_maps_result->status=='OVER_QUERY_LIMIT'){
					// OVER LIMIT; FALL BACK TO MAPQUEST
					$api_result = json_decode(file_get_contents('http://www.mapquestapi.com/geocoding/v1/address?key=Fmjtd%7Cluua2hutnd%2C2n%3Do5-96ax9r&location='.urlencode($query)));
					$result['original_response'] = $api_result;
					$result['success'] = true;
					$result['source'] = 'mq';
					$result['lat_long'] = array($api_result->results[0]->locations[0]->latLng->lat,$api_result->results[0]->locations[0]->latLng->lng);
				}
			} else {
				$result['success'] = true;
				$result['source'] = 'gm';
				$result['lat_long'] = array($google_maps_result->results[0]->geometry->location->lat,$google_maps_result->results[0]->geometry->location->lng);
			}
		}catch(Exception $err){}
		return $result;
	}

	function replace_monrovia_email_links($html){
		return preg_replace('/([a-zA-Z0-9._-]+)@monrovia.com/','<span class="email_link">$1(#)monrovia.com</span>',$html);	
	}
	
	function sql_sanitize($value, $remove_line_breaks=true){
		//$value = iconv('UTF-8','windows-1256',$value);
		//$value = htmlspecialchars($value,ENT_QUOTES,'UTF-8');

		$value = replace_smart_characters($value);

		$value = str_replace_unicode(153,"{{#153}}", $value);
		$value = str_replace_unicode(174,"{{#174}}", $value);
		$value = str_replace_unicode(169,"{{#169}}", $value);
		$value = str_replace_unicode(176,"{{#176}}", $value);
		$value = str_replace_unicode(188,"{{#188}}", $value);
		$value = str_replace_unicode(189,"{{#189}}", $value);
		$value = str_replace_unicode(190,"{{#190}}", $value);
		$value = str_replace_unicode(187,"{{#187}}", $value);
		$value = str_replace_unicode(171,"{{#171}}", $value);
		$value = str_replace_unicode(233,"{{#233}}", $value);
		$value = str_replace_unicode(232,"{{#232}}", $value);

		$value = str_replace_unicode(215,"x", $value);
		$value = str_replace_unicode(96,"'", $value);
		$value = str_replace_unicode(180,"'", $value);

		if ( $remove_line_breaks )
		{
			$value = str_replace(chr(10), " ", $value);
			$value = str_replace(chr(13), " ", $value);
		}
		$value = str_replace("{{AMP}}", "&", $value);
		$value = str_replace("{{PERCENT}}", "%", $value);
		$value = str_replace("{{HASH}}", "#", $value);
		$value = str_replace("{{QUESTION}}", "?", $value);

		$value = str_replace('\t', ' ', $value);

		$value = str_replace("'", "''", $value); // THIS REPLACES "\\" WITH "\" (IN THE CASE OF CACHING BREADCRUMBS)
		//$value = addslashes($value); // THIS ADDS SLASHES TO DB
	
		if ( $remove_line_breaks )
			$value = trim(preg_replace('/\s\s+/',' ',$value));
		
		return $value;
	}
	

	//get_browser_info();
	//get_current_season();
?>