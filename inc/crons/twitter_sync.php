<?php
	@include_once($_SERVER['DOCUMENT_ROOT'].'/inc/init.php');				// DEV
	@include_once('/var/www/monrovia.com/root/inc/init.php');				// LIVE
	@include_once('/var/www/vhosts/tpgphpdev1.net/httpdocs/inc/init.php');	// STAGING
	require_once("/var/www/monrovia.com/root/inc/twitteroauth/twitteroauth/twitteroauth.php"); //twitter API

	sql_disconnect();
	sql_set_user('med');
	sql_connect();

	function charset2_decode_utf_8 ($string) {
	      /* Only do the slow convert if there are 8-bit characters */
	    /* avoid using 0xA0 (\240) in ereg ranges. RH73 does not like that */
	    if (! preg_match("[\200-\237]", $string) and ! preg_match("[\241-\377]", $string))
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

	function get_new_tweets(){
		try {

			//session_start();
			$twitteruser = "PlantSavvy";
			$notweets = 30;
			$consumerkey = "oZGCGgMJknrQI54eExApdA";
			$consumersecret = "JWuNw5k1Ci3GnbvbTxrPp71kEE9wSAQmFl1OL4hQUyA";
			$accesstoken = "95955505-tnOJm0DvHqPeW4S1DDnEi3mwTkKWSvCNvXSWHi2DQ";
			$accesstokensecret = "72yG0eADaMwl7Ro3sBlBrZgs2nrtq6DKUEx0VXSzrIs";

			function getConnectionWithAccessToken($cons_key, $cons_secret, $oauth_token, $oauth_token_secret) {
			  $connection = new TwitterOAuth($cons_key, $cons_secret, $oauth_token, $oauth_token_secret);
			  return $connection;
			}

			  $connection = getConnectionWithAccessToken($consumerkey, $consumersecret, $accesstoken, $accesstokensecret);
			  $tweets = $connection->get("https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=".$twitteruser."&count=".$notweets);

			foreach($tweets as $tweet){
				if ($tweet->user->screen_name == "PlantSavvy"){

	  				$date_created = date('Y-m-d H:i:s',strtotime($tweet->created_at));
	  				$content = sql_sanitize(charset2_decode_utf_8($tweet->text));
	  				$id = $tweet->id_str;	  				

	  				if(!already_retrieved($id)&&$content!=''){
	    				sql_query("INSERT INTO twitter(id,date_created,content) VALUES('$id','$date_created','$content')");
	    			}
	    		}
	  		}

	  		//OLD TWITTER < oAuth1.1

			// $feed = object_to_array(simplexml_load_string(file_get_contents('http://api.twitter.com/1/statuses/user_timeline.rss?screen_name=plantsavvy')));
			// $statuses = $feed['channel']['item'];
			// for($i=0;$i<count($statuses);$i++){
			// 	$id = substr($statuses[$i]['guid'],39);
			// 	$date_created = date('Y-m-d H:i:s',strtotime($statuses[$i]['pubDate'])); // TRIM OFF "http://twitter.com/PlantSavvy/statuses/"
			// 	if(substr($statuses[$i]['title'],0,12)=='PlantSavvy: '){
			// 		$content = sql_sanitize(substr(charset_decode_utf_8($statuses[$i]['title']),12)); // TRIM OFF "PlantSavvy: "
			// 		if(!already_retrieved($id)&&$content!=''){
			// 			sql_query("INSERT INTO twitter(id,date_created,content) VALUES('$id','$date_created','$content')");
			// 		}
			// 	}
			// }


		}catch(Exception $err){
			echo(date('Y-m-d h:i:s') . "\tFailed\n");
		}
	}
	function already_retrieved($tweet_id){
		$result = sql_query("SELECT COUNT(*) AS tweets FROM twitter WHERE id='$tweet_id'");	
		return (intval(mysql_result($result,0,'tweets'))>0);
	}
	get_new_tweets();

	

?>

