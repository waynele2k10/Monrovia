<?php
/* 

@date 2015-1-23
@author Primitive Spark
@description Used to create Plant Savvy
newsletters while levaraging WordPress 
backend then importing into iContact

*/

// Uncomment these to Turn on Errors for this page
//ini_set('display_errors','on');
//error_reporting(E_ALL);

if (have_posts()):
    while (have_posts()):
        the_post();
        
        // If its a bot, let it scrape the page, otherwise, redirect to the plant savvy landing page
        // with th e newsletter pre-opened in a lightbox.
        
        // Define the URL
        if (isset($_GET['preview'])) {
            $preview = true;
        } else {
            $preview = false;
        }
        if (isset($_GET['load'])) {
            $load    = true;
            $preview = true;
        } else {
            $load = false;
        }
        $url          = $_SERVER['SERVER_NAME'];
        $dateHash     = strtolower(get_the_date('M-Y'));
        $dateOverride = get_field('newsletter_date');
        if (!empty($dateOverride)) {
            $dateOverride = get_field('newsletter_date');
            $dateHash     = strtolower(date('M-Y', strtotime($dateOverride[1])));
        }
        $ext = '/plant-savvy-newsletter/#' . $dateHash;
        if (!preg_match('/FacebookExternalHit|LinkedInBot|googlebot|Facebot|robot|spider|crawler|curl|^$/i', $_SERVER['HTTP_USER_AGENT'])) {
            // Dont redirect if its being loaded via lightbox
            if (!$preview || (!$load && !$preview)) {
                header("Location:http://" . $url . $ext);
            }
        }
        
        
        //Global defaults
        $meta_description = get('introduction_title');
        $twitter_text     = get('introduction_title');
        $page_url         = get_permalink();
        $date             = get_the_date('F Y');
        $URL_date         = get_the_date('M-Y');
        //Date Overrides
        $dateOverride     = get_field('newsletter_date');
        if (!empty($dateOverride)) {
            $dateOverride = get_field('newsletter_date');
            //print_r($dateOverride);
            $date         = date('F Y', strtotime($dateOverride[1]));
            $URL_date     = date('M-Y', strtotime($dateOverride[1]));
        }
        //Is Promo area Vertical or Horizontal
        $orientation       = get('default_orientation');
        $google_parameters = "?utm_source=plantsavvy&utm_medium=email&utm_campaign=" . $URL_date . "-plant-savvy";
        
        //Custom Meta fields
        if (get('meta_information_meta_facebook_description')) {
            $meta_description = get('meta_information_meta_facebook_description');
        }
        if (get('meta_information_twitter_share_cop')) {
            $twitter_text = get('meta_information_twitter_share_copy');
        }
        // Get the Featured Image Attributes
        $imageAttributes = wp_get_attachment(get_post_thumbnail_id($post->ID));
        
        // Get Youtobe Feature
        $isFeatureYoutube   = get('feature_use_youtube');
        $featureYoutubeLink = get('feature_youtube_link');
        //Get the Main and Sidebar groups
        $sidebars           = get_group('sidebar_content');
        $mains              = get_group('main_col_article');
        //Loop through main articles for creating an table of contents first
        foreach ($mains as $main) {
            $titles[] = $main['main_col_article_title'][1];
        }
        
        /** Clean up <p> tags and inline style to <a> tags **/
        
        function addStyle($string)
        {
            $string = str_replace('<a ', '<a style="color:rgb(129, 157, 15);" data-url="true" ', $string); //Add inline style to <a> tags
            $string = str_replace('<p>', '', $string); //Remove <p> tags - Causes problems with Hotmail, etc
            $string = str_replace('</p>', '<br /><br />', $string); // Convert closeing <p> tags to <br>
            
            //Append all in-content links with $google_parameters
            $instances = substr_count($string, 'href='); //Count how many links in a string
            
            $offset = 0;
            for ($i = 0; $i < $instances; $i++) {
                $off    = strpos($string, 'href="', $offset); // Begin href
                $pos    = strpos($string, '"', $off + 6); // End href
                $string = substr_replace($string, $google_parameters, $pos, 0); //Append
                $offset = $pos + 1;
            }
            
            return $string;
        }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
	<title>Plant Savvy | <?php
        echo $date;
?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta content="IE=edge, chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="p:domain_verify" content="a179bcc297a73a146418ce2d8b4c83b2"/>
    <meta name="description" content="<?php
        echo $meta_description;
?>" />
    <meta name="keywords" content=""  />
    <meta name="og:type" content="website" />
    <meta name="og:image" content="<?php
        echo $imageAttributes['src'];
?>"/>
    <meta name="og:title" content="Plant Savvy | <?php
        echo $date;
?>" />
    <meta name="og:description" content="<?php
        echo $meta_description;
?>" />
    <meta name="og:url" content="<?php
        echo $page_url;
?>" />

<style type="text/css">
@import url(http://fonts.googleapis.com/css?family=Montserrat:400,700);
*, *:after, *:before {
    box-sizing: border-box;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    -webkit-font-smoothing: antialiased;
    font-smoothing: antialiased;
    text-rendering: optimizeLegibility;
    font-weight: normal;
}

h2 {
    margin: 0px 0px 10px 0px;
}

h3 {
    margin: 0px 0px 10px 0px;
}

a {
    text-decoration: none;
    outline: none;
}

ul {
    margin: 0;
    padding: 0;
    list-style: none;
}

.contentWrap {
    overflow-y: hidden;
}

a, a:link {
    color: #829c23;
}

.tg, .tg-top {
	border-collapse: collapse;
        border-spacing: 0;
        margin: 0px auto;
        border: none;
  	width:100%;
	font-weight: normal;
	text-align: left;
}
.tg-top .tg-yw4.thumbnail {
    text-align: center;
}

.savvy-wrapper {
    max-width: 700px;
    margin: 0 auto;
    width: 100%;
    padding: 0;
    color: #514942;
    font-family: Verdana, Arial, sans-serif;
    font-size: 16px;
    line-height: 22px;
}

.fmobile {
    display: block;
}

.apple_overlay {
    padding-left: 0px;
    padding-right: 0px;
}

.savvy-header,
.savvy-top .title,
.savvy-top .content,
.savvy-top .read-more,
.savvy-main,
.savvy-sub,
.savvy-footer {
    padding-left: 15px;
    padding-right: 15px;
}

    .savvy-header .logo {
        width: initial;
    }

    .savvy-header .title {
        text-align: right;
        font-family: Verdana, Arial, sans-serif;
        color: #7a6f66;
        width: auto;
        max-width: 50%;
        font-size: 16px;
        letter-spacing: 2px;
	float: right;
    }

.savvy-top .thumbnail {
    margin-bottom: 10px;
}

.savvy-top .title h2,
.savvy-main .content h3,
.savvy-sub .content h3 {
    color: #ef8433;
    font-size: 28px;
    text-transform: uppercase;
    line-height: 28px;
    font-family: 'Montserrat', Verdana;
    font-weight: normal;
}

.savvy-main .content h3 {
    font-size: 24px;
    line-height: 24px;
}

.savvy-sub .content h3 {
    font-size: 14px;
    line-height: 14px;
    margin-bottom: 5px;
}

.savvy-sub .content p {
    font-size: 14px;
    line-height: 15px;
    margin: 0;
}

.savvy-top .read-more a {
    text-decoration: none;
    color: #ffffff;
    background: #97b529;
    width: 90%;
    max-width: 100%;
    display: block;
    text-align: center;
    line-height: 50px;
    font-size: 22px;
    margin: 0 auto;
}

.savvy-main .thumbnail a {
    display: block;
    height: 100%;
    text-align: center;
	overflow: hidden;
}

    .savvy-main .thumbnail a img {
        width: 100%;
    }

#overlay .mailchimpVideo {
    display: none;
}

.video-container {
    display: none;
}

#overlay .video-container {
    display: block;
}

.col6 {
    position: relative;
    min-height: 1px;
    float: left;
    margin-left: 0;
    margin-right: 0;
}

.savvy-header {
    padding-bottom: 5px;
    overflow: auto;
    vertical-align: top;
}

.savvy-sub .row {
    clear: both;
    overflow: hidden;
    margin-bottom: 10px;
}

    .savvy-sub .row li {
        width: 45%;
        float: left;
    }

.savvy-main .thumbnail, .savvy-main .thumbnail a, .savvy-main .thumbnail a div{
    height: 230px;
    min-height: 230px;
}

 	.savvy-sub .row li.odd {
            padding-right: 5px;
        }

        .savvy-sub .row li.even {
            padding-left: 5px;
        }

.savvy-footer .social-link {
	padding: 10px 0;
        border-top: 2px solid #e4e4e4;
        border-bottom: 2px solid #e4e4e4;
}
.mcnImage {
	height: 100%;	
	max-height: 100%;
}
.read-more-table tr > td {
	padding: 0;
}
@media only screen and (min-width: 339px) {
	.savvy-header .title {
	    font-size: 16px;
	    letter-spacing: 2px;
	}    

	.savvy-top {
            padding-bottom: 0px;
            border-bottom: none;
       }

        .savvy-top .read-more a {
            width: 100%;
        }

    .savvy-main .thumbnail {
        width: 100%;
        padding-right: 0;
        margin-bottom: 15px;
    }

    .savvy-sub .thumbnail {
        margin-bottom: 10px;
    }

    .savvy-sub .row li {
        height: auto;
    }

        .savvy-sub .row li .thumbnail {
            width: 100%;
            padding-right: 0px;
        }

        .savvy-sub .row li.odd {
            padding-right: 20px;
        }

        .savvy-sub .row li.even {
            padding-left: 20px;
        }

    .savvy-footer .social-link .label {
        display: none;
    }

    .savvy-footer .social-link .social-list {
        width: 100%;
        text-align: center;
    }

    .savvy-main .thumbnail.video {
        height: auto;
    }
	
}

@media only screen and (min-width: 699px) {
    .savvy-sub .row li .thumbnail {
        width: 50%;
        float: left;
    }

    .savvy-main .thumbnail {
        width: 50%;
        float: left;
        padding-right: 20px;
        height: 230px;
    }

    .savvy-header,
    .savvy-top .title,
    .savvy-top .content,
    .savvy-top .read-more,
    .savvy-main,
    .savvy-sub,
    .savvy-footer {
        padding-left: 0;
        padding-right: 0;
    }

        .savvy-header .logo {
            margin: 0;
        }

        .savvy-header .title {
            font-size: 24px;
            letter-spacing: 3px;
        }



        .savvy-top .read-more a {
            width: 90%;
        }

    .savvy-top {
        padding-bottom: 20px;
        border-bottom: 2px solid #e4e4e4;
    }

    .savvy-main {
        margin-top: 20px;
    }

        .savvy-main .thumbnail {
            width: 50%;
            float: left;
            padding-right: 20px;
            height: 230px;
        }

    .row-main {
        clear: both;
        overflow: hidden;
        margin-bottom: 20px;
    }

    .savvy-sub .row li {
        height: 230px;
    }

        .savvy-sub .row li, .savvy-sub .row li .thumbnail {
            width: 50%;
            float: left;
        }

            .savvy-sub .row li .thumbnail {
                padding-right: 10px;
            }

    .savvy-main .content p {
        margin: 0;
    }

    .savvy-sub .content p {
        font-size: 14px;
        line-height: 15px;
        margin: 0;
    }

    .savvy-sub .row {
        clear: both;
        overflow: hidden;
        margin-bottom: 10px;
    }

        .savvy-sub .row .odd {
            padding-right: 20px;
        }

        .savvy-sub .row .even {
            padding-left: 20px;
        }

    .savvy-footer .social-link {
        padding: 10px 0;
        border-top: 2px solid #e4e4e4;
        border-bottom: 2px solid #e4e4e4;
        clear: both;
        overflow: hidden;
        vertical-align: middle;
        display: table;
    }

        .savvy-footer .social-link .label {
            width: 30%;
            font-size: 28px;
            line-height: 32px;
            color: #7a6f66;
            font-family: 'Montserrat', Verdana;
            text-align: right;
            padding-right: 40px;
            display: table-cell;
            vertical-align: middle;
        }

        .savvy-footer .social-link .social-list {
            width: 70%;
            display: table-cell;
            vertical-align: middle;
            text-align: left;
        }

    .video-container {
        position: relative;
        padding-bottom: 56.25%;
        padding-top: 30px;
        height: 0;
        overflow: hidden;
    }

        .video-container iframe,
        .video-container object,
        .video-container embed {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            margin: 0;
        }

    .fdesktop {
        display: block;
    }

    .fmobile {
        display: none;
        font-size: 12px;
    }

        .fmobile a {
            color: #514942;
            text-decoration: underline;
        }
	.read-more-table  tr > td {
		padding: 0 5%;
	}
}


</style>
</head>
<body onload="setHeightLoad()">
<!--[if mso]><table width="840"><tr><td width="840"><![endif]-->
	<div class="savvy-wrapper">
		<div class="savvy-header">
<style type="text/css">

                        .tg td, .tg th  {
                            font-size: 24px;
	                    letter-spacing: 3px;	
			    font-weight: normal;
                            padding: 0;
                            border: none;
                            overflow: hidden;
                            word-break: normal;
                        }

                        .tg .tg-yw4l {
                            vertical-align: top;
			    text-align:right;
			    width: 50%;
			    white-space: nowrap;
                        }

                        .tg .tg-yw4l:first-child {
			    text-align:left;
                        }
			.tg .tg-yw4l.thtitle {
			    font-size: 16px;
			    letter-spacing: 2px;
			}
                    @media screen and (min-width: 767px) {
                        .tg-wrap {
                            overflow-x: auto;
                            -webkit-overflow-scrolling: touch;
                            margin: auto 0px;
                        }
			.tg .tg-yw4l.thtitle {
			    font-size: 24px;
			    letter-spacing: 3px;
			}
                    }
                </style>
		<div class="tg-wrap">
			<table class="tg" style="max-width:840px">
			  <tr>
			    <th class="tg-yw4l" width="210">
				<a href="http://www.monrovia.com/<?php
        echo $google_parameters;
?>" target="_blank">
					<img src="http://www.monrovia.com/wp-content/themes/monrovia/img/logo.png" style="max-width:210px;" alt="Monrovia"/>
				</a>
			    </th>
			    <th class="tg-yw4l thtitle " style="width:50%">
				<span style="color:#89a134;">PLANT</span> SAVVY<br /><span style="font-size:14px;letter-spacing:1px;"><?php
        echo $date;
?></span></th><th></th>
			  </tr>
			</table>
 		 </div>
		</div>
		<div class="savvy-top">
		   <div class="tg-wrap">
			<table class="tg-top" style="max-width:840px">
			  <tr>
			    <th class="tg-yw4 thumbnail">
				<div class="thumbnail <?php
        echo (trim($featureYoutubeLink) != "") ? 'video' : '';
?>">
				<?php
        if (trim($featureYoutubeLink) != "") {
            echo '<div class="video-container"><iframe src="//' . $featureYoutubeLink . '" width="560" height="315"></iframe></div>';
        } else {
?>
					<a href="<?php
            echo get('featured_image_link') . $google_parameters;
?>" target="_blank">
						<img border="0" src="<?php
            echo $imageAttributes['src'];
?>" alt="<?php
            echo $imageAttributes['title'];
?>" width="100%" style="max-width:700px;"/>
					</a>
				<?php
        }
?>
				
			</div>
			</th>
			  </tr>
			</table>
 		 </div>
			<div class="title">
				<a href="<?php
        echo get('featured_image_link') . $google_parameters;
?>" target="_blank">
					<h2><?php
        echo get('introduction_title');
?></h2>
				</a>
			</div>
			<div class="content" ><?php
        echo addStyle(get_the_content_with_formatting());
?></div>
			<div class="read-more">
<table class="read-more-table" cellpadding="0" cellmargin="0" border="0" height="50" style="border-collapse: collapse; width:100%;">
  <tr>
<td>
<table  cellpadding="0" cellmargin="0" border="0" height="50" width="100%" style="border-collapse: collapse; width:100%;">
        <tr>
          <td bgcolor="#97b529" valign="middle" align="center">
      <div style="font-size: 18px; color: #ffffff; line-height: 1; margin: 0; padding: 0; mso-table-lspace:0; mso-table-rspace:0;">
	<a href="<?php echo get('featured_image_link') . $google_parameters;?>" style="text-decoration: none; color: #ffffff; border: 0; mso-table-lspace:0; mso-table-rspace:0;">READ MORE »</a>
      </div>
    </td>
        </tr>
      </table>
    </td>
  </tr>
</table>		
				
				<br>
			</div>
	    
			
		</div>
		<div class="savvy-main">
			<?php
        foreach ($mains as $main) {
?>
				<div class="row-main">
					<div class="thumbnail <?php
            echo (trim($main['main_col_article_youtube_link'][1]) != "") ? 'video' : '';
?>">
						<?php
            
            if (trim($main['main_col_article_youtube_link'][1]) != "") {
                
                echo '<div class="video-container"><iframe src="http://' . $main['main_col_article_youtube_link'][1] . '" width="560" height="315"></iframe></div>';
                $videoID = end(explode('/', $main['main_col_article_youtube_link'][1]));
                if (trim($videoID) != "") {
                    echo '<span class="mailchimpVideo">*|YOUTUBE:[$vid=' . $videoID . ',$title=N, $border=N, $trim_border=N, $views=N, $ratings=N]|*</span>';
                }
            } else {
?>
							<?php
                // Set the $a closing tag to nothing
                $a = '';
                if (isset($main['main_col_article_image'])) {
                    if ($main['main_col_article_image_link'][1]) {
                        $a = "</a>";
                        echo '<a href="' . $main['main_col_article_image_link'][1] . $google_parameters . '" target="_blank">';
                    }
?> 
									<img src="<?php
                    echo $main['main_col_article_image'][1]['original'];
?>" />
							<?php
                    echo $a;
                } // End image check 
?>
						<?php
            }
?>
					</div>
					<div class="content">
						<h3><?php
            echo $main['main_col_article_title'][1];
?></h3>
						<p><?php
            echo addStyle($main['main_col_article_content'][1]);
?></p>
					</div>
				</div>
			<?php
        }
?>
		</div>
		<div class="savvy-sub">
			<?php
        $index = 0;
?>
			<?php
        foreach ($sidebars as $sidebar) {
?>
				<?php
            $index++;
            if (($index % 2) == 1) {
                echo '<ul class="row">';
            }
?>
				<li class="<?php
            echo (($index % 2) == 1) ? 'odd' : 'even';
?>">
					<div class="thumbnail <?php
            echo (trim($sidebar['sidebar_content_youtube_link'][1]) != "") ? 'video' : '';
?>">
						<?php
            if (trim($sidebar['sidebar_content_youtube_link'][1]) != "") {
                echo '<div class="video-container"><iframe src="//' . $sidebar['sidebar_content_youtube_link'][1] . '" width="560" height="315"></iframe></div>';
                $videoID = end(explode('/', $sidebar['sidebar_content_youtube_link'][1]));
                if (trim($videoID) != "") {
                    echo '<span class="mailchimpVideo">*|YOUTUBE:[$vid=' . $videoID . ',$title=N, $border=N, $trim_border=N, $views=N, $ratings=N]|*</span>';
                }
            } else {
?>
						<?php
                // Set the $a closing tag to nothing
                $b = '';
                if (isset($sidebar['sidebar_content_image'])) {
                    if ($sidebar['sidebar_content_image_link'][1]) {
                        $b = "</a>";
                        echo '<a href="' . $sidebar['sidebar_content_image_link'][1] . $google_parameters . '" target="_blank">';
                    }
?> 
								<img src="<?php
                    echo $sidebar['sidebar_content_image'][1]['original'];
?>" width="100%" style="max-width:100%;" alt=""/>
						<?php
                    echo $b;
                } else {
                    echo "&nbsp;"; //Empty space
                } // End image check 
?>
						<?php
            }
?>
					</div>
					<div class="content">
						<h3><?php
            echo $sidebar['sidebar_content_title'][1];
?></h3>
						<p><?php
            echo addStyle($sidebar['sidebar_content_content'][1]);
?></p>
					</div>
				</li>
				<?php
            if (($index % 2) == 0) {
                echo '</ul>';
            }
?>
			<?php
        }
?>
		</div>
		<div class="savvy-footer">
			<div class="social-link">
				<span class="label">SHARE:</span>
				<div class="social-list">
					<a href="mailto:Enter Email(s) Here?subject=Monrovia - Plant Savvy&body=Dear Friend, %0A%0AI think you would enjoy this Monrovia Newsletter below %0A%0A<?php
        echo $page_url;
?>" target="_blank" style="text-decoration:none;">
						<img src="<?php
        echo get_template_directory_uri();
?>/img/email/email.jpg" width="15%" style="max-width:100px" border="0" alt="email"/>
					</a>
					<a href="http://www.facebook.com/sharer/sharer.php?u=<?php
        echo $page_url;
?>" target="_blank" style="text-decoration:none;">
						<img src="<?php
        echo get_template_directory_uri();
?>/img/email/fb.jpg" width="15%" style="max-width:100px" border="0" alt="Facebook" />
					</a>
					<a href="https://twitter.com/share?url=<?php
        echo $page_url;
?>&text=<?php
        echo $twitter_text;
?>" target="_blank" style="text-decoration:none;">
						<img src="<?php
        echo get_template_directory_uri();
?>/img/email/twitter.jpg" width="15%" style="max-width:100px" border="0" alt="Twitter" />
					</a>
					<a href="https://plus.google.com/share?url=<?php
        echo $page_url;
?>" target="_blank" style="text-decoration:none;">
						<img src="<?php
        echo get_template_directory_uri();
?>/img/email/google.jpg" width="15%" style="max-width:100px" border="0" alt="Google" />
					</a>
					<a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php
        echo $page_url;
?>&title=Plant Savvy <?php
        echo $date;
?>&summary=<?php
        echo get('introduction_title');
?>" target="_blank" style="text-decoration:none;">
						<img src="<?php
        echo get_template_directory_uri();
?>/img/email/linkedin.jpg" width="15%" style="max-width:100px" border="0" alt="LinkedIn" />
					</a>
				</div>
			</div>
			<div class="fdesktop">
				<table width="100%" cellpadding="0" cellspacing="0">
					<tr><td style="line-height:10px;">&nbsp;</td></tr>
					<tr>
						<td align="center">Change your subscription settings <a href="[manage_your_subscription_url]" style="color:#829c23;">here</a>.</td>
					</tr>
					<tr><td style="line-height:10px;">&nbsp;</td></tr>
				 </table>
			 </div>
			 <!--div class="fmobile">
				 <table width="100%" cellpadding="0" cellspacing="0">
					<tr><td colspan="2"  style="line-height:10px;">&nbsp;</td></tr>
					<tr>
						<td colspan="2" align="center">This email was sent to <a href="#">klichthart@monrovia.com</a></td>
					</tr>
					<tr>
						<td align="center"><a href="#">why did I get this?</a></td>
						<td align="center"><a href="#">unsubscribe from this list</a></td>
					</tr>
					<tr>
						<td colspan="2" align="center"><a href="#">update subscription preferences</a></td>
					</tr>
					<tr>
						<td colspan="2" align="center">Monrovia &#8226; 817 E. Monrovia Place &#8226; Azusa, Ca 91702 &#8226; USA</td>
					</tr>
					<tr>
						<td colspan="2" align="center">Copyright &#169; 2016 Monrovia. All rights reserved.</td>
					</tr>
				 </table>
			</div-->
		</div>
	</div>
<!--[if mso]></td></tr></table><![endif]-->
	<script>
	jQuery(document).ready( function(){
		setHeightLoad();
		jQuery('img').load(function () {
			setHeightLoad();
		});
		jQuery('.video-container iframe').load(function () {
			setHeightLoad();
		});
		
	});
	function setHeightLoad() {
		var overlay_h = jQuery('#overlay').outerHeight(true) + 60;
		var body_h = jQuery(document).outerHeight(true);
		console.log(overlay_h + ' : ' + body_h);
		document.getElementsByTagName('body')[0].style.height = overlay_h+"px";
	}
	
	</script>
</body>
</html>
<?php
    endwhile;
endif;
?>