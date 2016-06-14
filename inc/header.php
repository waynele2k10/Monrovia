<?php
	// if(substr_count($_SERVER['HTTP_ACCEPT_ENCODING'],'gzip')) ob_start('ob_gzhandler');else ob_start(); //UNCOMMENT THIS
	ob_start();
	require_once('init.php');

	// LOG IN OR LOAD USER DATA

	if(isset($_POST['login'])){
		$logging_in = true;
		$user_name = $_POST['login']['user_name'];
		$user_password = $_POST['login']['user_password'];
		$remember_me = isset($_POST['login']['remember_me'])?$_POST['login']['remember_me']:false;

		$monrovia_user = new monrovia_user();
		$monrovia_user->log_in($user_name,$user_password,$remember_me);
	}else{
		if(!isset($monrovia_user)||$monrovia_user=='') $monrovia_user = new monrovia_user((isset($_SESSION['monrovia_user_id'])?$_SESSION['monrovia_user_id']:''));
	}

	$page_module_ids = array();
	$render_editable = strpos($monrovia_user->info['permissions'],',html,')!==false;
	$user_name = '';
	if(isset($monrovia_user->info['user_name'])) $user_name = $monrovia_user->info['user_name'];

	if(function_exists('before_output')) before_output();

	header("Content-type: text/html; charset=windows-1252");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title><? if(isset($GLOBALS['page_title'])) echo($GLOBALS['page_title']); ?></title>
	<meta http-equiv="Content-type" value="text/html; charset=windows-1252" />
	<meta name="title" content="<? if(isset($GLOBALS['page_title'])) echo(html_sanitize($GLOBALS['page_title'])); ?>" />
	<? 
		if(isset($page_meta_description)) $page_meta_description = trim(trim($page_meta_description),",");
		if(isset($page_meta_keywords)) $page_meta_keywords = trim(trim($page_meta_keywords),",");
	?>
	<? if(isset($page_meta_description)&&$page_meta_description!=''){ ?><meta name="description" content="<?=html_sanitize($page_meta_description)?>" /><? } ?>
	<? if(isset($page_meta_keywords)&&$page_meta_keywords!=''){ ?><meta name="keywords" content="<?=html_sanitize($page_meta_keywords)?>" /><? } ?>
	<? if(!isset($page_meta_facebook_thumbnail)||$page_meta_facebook_thumbnail=='') $page_meta_facebook_thumbnail = 'http://www.monrovia.com/img/facebook_thumb_pots.jpg'; ?>
	
	<link rel="image_src" href="<?=html_sanitize($page_meta_facebook_thumbnail)?>" />
	<meta property="og:image" content="<?=html_sanitize($page_meta_facebook_thumbnail)?>" />
	
	<script>
		var persist = '';
		var monrovia_user_data = {
			'is_logged_in':<?=($monrovia_user->is_logged_in()?'true':'false')?><? if(isset($render_editable)&&$render_editable){ ?>,'name':'<?=$monrovia_user->info['user_name']?>'<? } ?>,'email_address':'<?=$monrovia_user->info['email_address']?>'
		}
	</script>

<!--
	<script src="/inc/packer.php?path=/js/prototype.js,/js/prototype_extensions.js,/js/general.js,/swf/swfobject.js,/js/media.js,/js/modal_min.js" type="text/javascript"></script>
-->

	<script src="/js/prototype.js" type="text/javascript"></script>
	<script src="/js/prototype_extensions.js" type="text/javascript"></script>
	<script src="/js/general.js?r=2" type="text/javascript"></script>
	<script src="/swf/swfobject.js" type="text/javascript"></script>
	<script src="/js/modal_min.js" type="text/javascript"></script>

	<script>
		monrovia.config.qa_root = '<?=$GLOBALS['server_info']['qa_root']?>';
	</script>

	<style>
		<?
			// @font-face STUFF
			$extension = '';
			$format = '';
			if($GLOBALS['browser_info']['name']=='opera'||$GLOBALS['browser_info']['name']=='firefox'||$GLOBALS['browser_info']['medium']=='mobile'||$GLOBALS['browser_info']['medium']=='tablet'){
				$extension = 'otf';
				$format = 'opentype';
			}
			if($GLOBALS['browser_info']['family']=='webkit'){
				$extension = 'ttf';
				$format = 'truetype';
			}
			
			if($GLOBALS['browser_info']['name']=='ie'){
				$extension = 'eot';
			}
			
			/*
				Opera: otf
				FF: otf
				Tablets, mobile: otf
				IE: ttf
				Chrome: ttf
				Safari: ttf
				Everything else: otf, woff, ttf			
			*/
			
			if($extension!=''){
			?>
				@font-face {
					font-family: apex-medium;
					src: url('/fonts/apex-sans-medium.<?=$extension?>') <? if($format!=''){ ?>format('<?=$format?>')<? } ?>;
				}

				@font-face {
					font-family: apex-medium-italic;
					src: url('/fonts/apex-sans-medium-italic.<?=$extension?>') <? if($format!=''){ ?>format('<?=$format?>')<? } ?>;
				}

				@font-face {
					font-family: apex-book;
					src: url('/fonts/apex-sans-book.<?=$extension?>') <? if($format!=''){ ?>format('<?=$format?>')<? } ?>;
				}

				@font-face {
					font-family: apex-book-italic;
					src: url('/fonts/apex-sans-book-italic.<?=$extension?>') <? if($format!=''){ ?>format('<?=$format?>')<? } ?>;
				}
			<?
			}else{
			?>
				@font-face {
					font-family: apex-medium;
					src: url('/fonts/apex-sans-medium.otf') format('opentype'),url('/fonts/apex-sans-medium.woff') format('woff'),url('/fonts/apex-sans-medium.ttf') format('truetype');
				}

				@font-face {
					font-family: apex-medium-italic;
					src: url('/fonts/apex-sans-medium-italic.otf') format('opentype'),url('/fonts/apex-sans-medium-italic.woff') format('woff'),url('/fonts/apex-sans-medium-italic.ttf') format('truetype');
				}

				@font-face {
					font-family: apex-book;
					src: url('/fonts/apex-sans-book.otf') format('opentype'),url('/fonts/apex-sans-book.woff') format('woff'),url('/fonts/apex-sans-book.ttf') format('truetype');
				}

				@font-face {
					font-family: apex-book-italic;
					src: url('/fonts/apex-sans-book-italic.otf') format('opentype'),url('/fonts/apex-sans-book-italic.woff') format('woff'),url('/fonts/apex-sans-book-italic.ttf') format('truetype');
				}
			<?
			}
		?>
	</style>

	<link rel="stylesheet" type="text/css" href="/inc/packer.php?path=/css/reset.css%2C/css/general_min.css%2C/css/modal.css&r=8" />

	<script src="/js/scriptaculous/scriptaculous.js?load=effects" type="text/javascript"></script>
	<!--[if lte IE 6]><link rel="stylesheet" type="text/css" href="/css/ie6.css" /><![endif]-->
	<link rel="stylesheet" type="text/css" href="/css/print.css" media="print" />
	<?
		if(isset($render_editable)&&$render_editable){
	?>
		<link rel="stylesheet" type="text/css" href="/css/editable_modules.css" />
		<script src="/inc/packer.php?path=/fckeditor/fckeditor.js,/js/editable_modules.js" type="text/javascript"></script>
	<? }
		if(function_exists('append_to_head')) append_to_head();
		//if(function_exists('append_to_head_blog')) append_to_head_blog();
	?>
</head>
<body>
	<!-- Google Tag Manager -->
	<noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-S59B"
	height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','GTM-S59B');</script>
	<!-- End Google Tag Manager -->
	<div id="page_header" class="print_hide">
			<div id="masthead">
				<img src="/img/spacer.gif" usemap="#map_masthead" id="masthead_image" />
				<ul id="top_nav_links">
					<li><a href="/about-us/">about us</a></li>
					<li><a href="/plant-catalog/">plant catalog</a></li>
					<li><a href="/design-inspiration/">inspiration</a></li>
					<li><a href="/how-to-garden/">how-to</a></li>
					<li><a href="/plant-savvy-newsletter/">plant savvy&reg;</a></li>
					<li><a href="/gardening-videos/">videos</a></li>
					<li><a href="/<?=$GLOBALS['server_info']['qa_root']?>/">Q&amp;A</a></li>
					<li><a href="/find-a-garden-center/">garden centers</a></li>
					<li><a href="/landscape-architects/">find a design professional</a></li>
					<li><a href="/event-calendar/">calendar</a></li>
				</ul>
				<map name="map_masthead">
					<area coords="816,0,959,21" href="/retail/" />
					<area coords="121,24,369,77" href="/" />
				</map>
				<div id="user_bar">
					<div class="side_left"></div>
					<div class="content">
						<? if($monrovia_user->info['id']!=''){ ?>
							Welcome, <?=($monrovia_user->info['first_name']!='')?$monrovia_user->info['first_name']:$monrovia_user->info['user_name']?> (<a href="/community/login.php?log_out=1" style="text-decoration:underline;" title="Sign on as another user">not you?</a>)
							<?
								// IF USER HAS BACKEND ACCESS AND HAS A SPECIFIC BACKEND PRIVILEGE, SHOW BACKEND LINK

								// THESE ARE PERMISSIONS THAT MERIT BACKEND ACCESS
								$backend_permissions = array('hres','pldb','html','caln','user','prof','qamd');

								$has_backend_access = false;

								for($i=0;$i<count($backend_permissions);$i++){
									if(contains($monrovia_user->info['permissions'],','.$backend_permissions[$i].',')){
										$has_backend_access = true;
										break;
									}
								}

								if($has_backend_access&&!contains($monrovia_user->info['permissions'],',cmgt,')) $has_backend_access = false;

								if($has_backend_access){
									?>
										<img src="/img/user_bar_leaf_left.gif" align="absmiddle" class="leaf_divider" />
										<a href="/monrovia_admin/">Back-end System</a>
									<?
								}
							?>
							<?
								// CATALOG
								if(contains($monrovia_user->info['permissions'],',pdfs,')){
									?>
										<img src="/img/user_bar_leaf_right.gif" align="absmiddle" class="leaf_divider" />
										<a href="/catalogs/">Catalogs</a>
									<?
								}
							?>
							<img src="/img/user_bar_leaf_right.gif" align="absmiddle" class="leaf_divider" />
							<a href="/community/update-profile.php">Your profile</a>
							<img src="/img/user_bar_leaf_left.gif" align="absmiddle" class="leaf_divider" />
							<a href="/community/your-wish-list.php">Your wish list</a>
							<img src="/img/user_bar_leaf_right.gif" align="absmiddle" class="leaf_divider" />
							<a href="/?log_out=1">Sign out</a>
						<? }else{ ?>
							<a href="/community/login.php">Sign in/Sign up</a>&nbsp;
						<? } ?>
					</div>
				</div>
				<div id="global_search">
					<form action="/search.php" method="get" onsubmit="return global_search_validate();">
						<input id="global_search_query" value="search site" name="query" maxlength="40" autocomplete="off" />
						<input type="image" src="/img/spacer.gif" id="global_search_submit" title="Search" />
						<div style="clear:both;"></div>
					</form>
					<div style="position:relative;width:194px;">
						<div id="global_search_dropdown_container">
							<div id="global_search_dropdown"></div>
						</div>
					</div>
					<div style="text-align:right;padding:4px 8px 0px 0px;">
						<a href="/plant-catalog/" style="font-size:7pt;color:#fff;font-weight:bold;">advanced plant search</a>
					</div>
				</div>
			</div>
			<?
				if(isset($render_editable)&&$render_editable){
					?>
					<div style="border:2px solid #000;background-color:#fff;color:#000;font-weight:bold;text-align:center;position:absolute;top:0px;left:0px;padding:8px;opacity:.85;filter:alpha(opacity=85);#zoom:1;margin:8px;cursor:default;">
						You are in edit mode as a website content editor.
					</div>
					<?
				}
			?>
		</div>

		<div id="page_content">
			<img src="/img/print_logo.gif" class="print_only" id="print_logo" alt="Monrovia Logo" />
			<div class="padding" id="page_content_padding">

			<!-- FACEBOOK CONTEST BANNER -->
				<? if(isset($_SERVER['PHP_SELF'])&&$_SERVER['PHP_SELF']!='/index.php'){ ?>
					<style>
						#facebook_contest_banner {
							background:#e4de9e url(/img/announcement_banner_graphic.jpg) right top no-repeat;
							position:relative;
							width:920px;
							margin-bottom:12px;
							border-radius:10px 0px 10px 0px;
							-webkit-border-radius:10px 0px 10px 0px;
							-moz-border-radius:10px 0px 10px 0px;
						}
						#facebook_contest_banner_title {
							color:#77b500;
							font-family:apex-medium,arial;
							font-size:19px;
							padding-bottom:.5em;
							padding-top:4px;
							text-transform:lowercase;
						}
					</style>
					<div id="facebook_contest_banner" class="print_hide" style="height: 90px;">
						<div style="padding:10px 0px 4px 20px;">
							<div id="facebook_contest_banner_title" style="text-transform:none;">Coming Soon! Shop Monrovia</div>
							<p style="margin-right:16px;">
								<? insert_editable_module('Global :: Announcement Banner'); ?>
							</p>
							<a  href="http://www.monrovia.com/shop-monrovia/" style="border-bottom: none;position: absolute;right: 0;width: 150px;height: 90px;top: 0;"></a>
						</div>
					</div>
				<? } ?>
			<!-- /FACEBOOK CONTEST BANNER -->