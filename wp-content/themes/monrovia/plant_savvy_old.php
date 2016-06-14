<?php
 /* 
 	Template Name: Plant Savvy Old
	
	@date 2014-10-8
	@author Primitive Spark
	@description Used to create Plant Savvy
	newsletters while levaraging WordPress 
	backend then importing into iContact

*/
 
 // Uncomment these to Turn on Errors for this page
	//ini_set('display_errors','on');
	//error_reporting(E_ALL);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html style="" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title><?php the_title(); ?></title>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
    <meta name="p:domain_verify" content="a179bcc297a73a146418ce2d8b4c83b2"/>
    <!--general stylesheet -->
    <style type="text/css">
      p { padding: 0; margin: 0; }
      h1, h2, h3, p, li, span { font-family: Verdana, serif; color:#514942;}
      td { vertical-align:top;}
      ul, ol { margin: 0; padding: 0;}    </style>
  </head>
<?php if (have_posts()): while (have_posts()) : the_post(); ?>

<?php
	function addStyle($string){
		$string = str_replace('<a ', '<a style="color:rgb(129, 157, 15);" ', $string); //Add inline style to <a> tags
		$string = str_replace('<p>', '', $string); //Remove <p> tags - Causes problems with Hotmail, etc
		$string = str_replace('</p>', '<br /><br />', $string); // Convert closeing <p> tags to <br>
		return $string;	
	}
	// Get the Featured Image Attributes
	$imageAttributes = wp_get_attachment( get_post_thumbnail_id($post->ID) );
	
	//Get the Main and Sidebar groups
	$sidebars = get_group('sidebar_content');
	$mains = get_group('main_col_article');
	//Loop through main articles for creating an table of contents first
	foreach($mains as $main){
		$titles[] = $main['main_col_article_title'][1];
	}
?>
  <body bgcolor="#f5f6f5" leftmargin="0" marginheight="0" marginwidth="0" style="margin: 0px; background-color: rgb(245, 246, 245);" topmargin="0">
    <p>&nbsp;
      </p>
    <table bgcolor="#f5f6f5" border="0" cellpadding="0" cellspacing="0" width="100%">
      <tbody>
        <tr valign="top">
          <td valign="top">
            <!--container -->
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="655">
              <tbody>
                <tr>
                  <!--content -->
                  <td valign="top">
                    <table align="center" border="0" cellpadding="0" cellspacing="0" height="83" width="600">
                      <tbody>
                        <tr>
                          <td height="20" valign="top">&nbsp;
                            </td>
                        </tr>
                        <tr>
                          <td style="text-align: center;" valign="top">&nbsp;
                            </td>
                        </tr>
                      </tbody>
                    </table>
                    <table align="center" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" style="border:1px solid #999" width="600">
                      <tbody>
                        <tr>
                          <td colspan="2" height="84" valign="top">&nbsp;
                            </td>
                          <td height="84" style="vertical-align: top;" valign="top">
                            <table align="center" border="0" cellpadding="0" cellspacing="0" height="78">
                              <tbody>
                                <tr>
                                  <td height="15" valign="top">&nbsp;
                                    </td>
                                </tr>
                                <tr>
                                  <td valign="top">
                                    <table align="center" border="0" cellpadding="0" cellspacing="0" width="540">
                                      <tbody>
                                        <tr>
                                          <td style="vertical-align: middle; background-color: rgb(168, 202, 76);" valign="center" width="20">&nbsp;
                                            </td>
                                          <td style="vertical-align: middle; background-color: rgb(168, 202, 76);" valign="center" width="157">
                                            <a href="http://monrovia.com"><img alt="Plant Savvy" height="80" src="https://staticapp.icpsc.com/icp/loadimage.php/mogile/589567/b97bb936a0683366ef33260727f41fe2/image/png" style="margin: 20px 0px 10px; border: 0px solid rgb(0, 0, 0); border-image: none; width: 130px; height: 80px; display: inline;" width="130" /></a></td>
                                          <td style="text-align: right; vertical-align: middle; background-color: rgb(168, 202, 76);" valign="center">
                                            <h1 style="margin: 0px; padding: 0px; font-style: italic; font-weight: normal;">
                                              <a href="http://monrovia.com"><font color="#959d8c" face="Georgia"><span style="font-size: 34px;"><img alt="Monrovia.com" height="35" src="https://staticapp.icpsc.com/icp/loadimage.php/mogile/589567/cff679d8c8715827e1d4033e84856d35/image/png" style="margin: 60px 10px 0px 30px; border: 0px solid rgb(149, 157, 140); border-image: none; width: 150px; height: 35px; display: inline;" width="150" /></span></font></a></h1>
                                          </td>
                                        </tr>
                                      </tbody>
                                    </table>
                                  </td>
                                </tr>
                                <tr>
                                  <td height="20" valign="top">&nbsp;
                                    </td>
                                </tr>
                              </tbody>
                            </table>
                          </td>
                          <td colspan="2" height="84" valign="top">&nbsp;
                            </td>
                        </tr>
                        <tr>
                          <td bgcolor="#fff0" style="background-color: rgb(255, 255, 255);" valign="top" width="8">&nbsp;
                            </td>
                          <td valign="top" width="22">&nbsp;
                            </td>
                          <td valign="top" width="540">
                            <table align="center" border="0" cellpadding="0" cellspacing="0" style="padding-top: 14px;" width="540">
                              <tbody>
                                <tr>
                                  <td valign="top" width="140">
                                    <!--sidebar -->
                                    <table align="center" border="0" cellpadding="0" cellspacing="0" width="140">
                                      <tbody>
                                        <tr>
                                          <td style="text-align: right;" valign="top">
                                            <span style="text-align: center; color: rgb(149, 157, 140); text-transform: uppercase; letter-spacing: 2px; font-family: Georgia; font-size: 9px; font-weight: bold;"><?php the_date('F j, Y');?></span></td>
                                        </tr>
                                        <tr>
                                          <td style="text-align: right;" valign="top">&nbsp;
                                            </td>
                                        </tr>
                                        <tr>
                                          <td style="text-align: right;" valign="top">
                                            <span style="color: rgb(122, 111, 102);font-size: 16px;font-family: times new roman, times, serif;font-style: italic;">
                                            Contents
                                            </span>
                                          </td>
                                        </tr>
                                        <tr>
                                          <td valign="top">
                                            <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
                                              <tbody>
                                              <?php foreach($titles as $title){ ?>
                                                <tr>
                                                  <td style="text-align: right;" valign="top" width="127">
                                                    <a href="#<?php echo trim($title);?>" style="text-align: right; color: rgb(129, 157, 15); font-family: Verdana, sans-serif; font-size: 12px; font-style: normal;"><?php echo trim($title);?></a></td>
                                                  <td height="13" style="text-align: right;" valign="top" width="12">
                                                    <img src="https://staticapp.icpsc.com/icp/loadimage.php/mogile/589567/174e9ae18f45a25e1cb6f4689044ce55/image/jpeg" /></td>
                                                </tr>
                                                <?php } ?>
                                              </tbody>
                                            </table>
                                          </td>
                                        </tr>
                                        <!-- side bar start -->
                                        <?php foreach($sidebars as $sidebar){ ?>
                                        <tr>
                                          <td style="text-align: right; padding-top: 14px;" valign="top">
                                           <?php 
											// Set the $a closing tag to nothing
											$b = '';
											if(isset($sidebar['sidebar_content_image'])){ 
												if($sidebar['sidebar_content_image_link'][1]){
													$b = "</a>";
													echo '<a href="'.$sidebar['sidebar_content_image_link'][1].'" target="_blank">';
												} ?> 
												<img src="<?php echo $sidebar['sidebar_content_image'][1]['original'];?>" style="width:auto;" />
										<?php
												echo $b;
											} else {
												echo "&nbsp;"; //Empty space
											}// End image check 
											?>
                                          </td>
                                        </tr>
                                        <tr>
                                          <td style="margin: 0px; padding: 0px; text-align: right; color: rgb(81, 73, 66); line-height: 18px; font-family: Verdana, sans-serif; font-size: 9px;" valign="top">
                                          <?php echo addStyle($sidebar['sidebar_content_content'][1]); ?>
                                           </td>
                                        </tr>
                                        <!-- end side bar -->
                                        <?php } //End for loop ?>
                                        <tr>
                                          <td style="text-align: right; padding-top: 14px;" valign="top">
                                            <span style="color: rgb(122, 111, 102);font-size: 16px;font-family: times new roman, times, serif;font-style: italic;">
                                            	Connect With Us
                                            </span>
                                            </td>
                                        </tr>
                                        <tr>
                                          <td style="text-align: right; padding-top: 14px;" valign="top">
                                            <a href="http://www.facebook.com/pages/Monrovia/102411039815423?v=wall&amp;ref=sgm"><img border="0" src="http://monrovia.com/email_templates/img/fb.png" title="Check us out on Facebook!" /></a> <a href="https://plus.google.com/106439322773521086880/"><img border="0" src="http://monrovia.com/email_templates/img/google.png" title="Find us on Google+!" /></a> <a href="http://pinterest.com/monroviagrowers/"><img border="0" src="http://monrovia.com/email_templates/img/pinterest.png" title="Watch our boards on Pinterest!" /></a> <a href="http://twitter.com/plantsavvy/"><img border="0" src="http://monrovia.com/email_templates/img/twitter.png" title="Follow us on Twitter!" /></a> <a href="http://www.youtube.com/user/MonroviaPlants"><img border="0" src="http://monrovia.com/email_templates/img/yt.png" title="Watch us on YouTube!" /></a></td>
                                        </tr>
                                        <tr>
                                          <td style="text-align: right;" valign="top">&nbsp;
                                            </td>
                                        </tr>
                                      </tbody>
                                    </table>
                                  </td>
                                  <td valign="top">&nbsp;
                                    </td>
                                  <td style="border-left-color: rgb(150, 183, 17); border-left-width: 1px; border-left-style: dotted;" valign="top">&nbsp;
                                    </td>
                                  <td valign="top" width="360">
                                    <!--main-content -->
                                    <table align="center" border="0" cellpadding="0" cellspacing="0" width="360">
                                      <tbody>
                                        <tr>
                                          <td valign="top">
                                            <span style="margin: 0px; padding: 0px; color: rgb(241, 132, 36); font-family: Times; font-size: 24px; font-weight: normal;"">
												<?php echo get('introduction_title'); ?>
                                            </span>
                                            </td>
                                        </tr>
                                        <tr>
                                          <td height="5" style="height: 5px;" valign="top">&nbsp;</td>
                                        </tr>
                                        <tr>
                                          <td  valign="top">
                                            <a href="<?php echo get('featured_image_link');?>" target="_blank">
                                            	<img border="0" src="<?php echo $imageAttributes['src'];?>" alt="<?php echo $imageAttributes['title'];?>" />
                                            </a>
                                            <span style="font-family: verdana, geneva, sans-serif; display: block;color: rgb(81, 73, 66);font-size: 10px;line-height:14px">
                                            	<?php echo $imageAttributes['caption'];?>
                                            </span>
                                          </td>
                                        </tr>
                                        <tr>
                                          <td height="5" style="height: 5px;" valign="top">&nbsp;</td>
                                        </tr>
                                        <tr>
                                          <td valign="top">
                                            <span style="font-family: verdana, geneva, sans-serif; display: block;color: rgb(81, 73, 66);font-size: 11px;line-height:18px;">
                                            	<?php echo addStyle(get_the_content_with_formatting()); ?>
                                            </span>
                                          </td>
                                        </tr>
                                        <tr>
                                          <td height="30" style="height: 30px;" valign="top">
                                            <span style="display: block;">&nbsp;</span> <span><span style="color: rgb(149, 157, 140); text-transform: uppercase; letter-spacing: 2px; font-family: Georgia; font-size: 11px; font-weight: normal;">Healthier &amp; hardier</span></span> <span style="display: block;">&nbsp;</span></td>
                                        </tr>
                                        <?php foreach($mains as $main){ ?>
                                        <!-- main start -->
                                        <tr>
                                          <td valign="top">
                                            <a style="margin: 0px; padding: 0px; color: rgb(241, 132, 36); font-family: Times; font-size: 22px; font-weight: normal;display:block" name="<?php echo $main['main_col_article_title'][1]; ?>"><?php echo $main['main_col_article_title'][1]; ?></a>
                                          </td>
                                        </tr>
                                        <tr>
                                          <td height="12" style="height: 12px;" valign="top">&nbsp;</td>
                                        </tr>
                                        <tr>
                                          <td valign="top">
                                            <span style="color: rgb(81, 73, 66); font-family: verdana, geneva, sans-serif; font-size: 11px; ; line-height: 16px;">
                                            <?php 
												// Set the $a closing tag to nothing
												$a = '';
												if(isset($main['main_col_article_image'])){ 
                                            		if($main['main_col_article_image_link'][1]){
														$a = "</a>";
                                            			echo '<a href="'.$main['main_col_article_image_link'][1].'" target="_blank">';
                                            		} ?> 
                                            <img align="left" alt="" height="135" hspace="10" src="<?php echo $main['main_col_article_image'][1]['original'];?>" style="padding:0; border: 0; border-image: none; float: left;" vspace="10" width="187" />
                                            <?php
												echo $a;
											 	} // End image check 
											?>
                                            <?php echo addStyle($main['main_col_article_content'][1]); // Print out the copy ?>
                                            </span>
                                            </td>
                                        </tr>
                                        <tr>
                                          <td height="12" style="height: 12px;" valign="top">&nbsp;
                                            </td>
                                        </tr>
                                        <!-- main end -->
                                        <?php } //End main for loop ?>
                                      </tbody>
                                    </table>
                                    <!--/main-content -->
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </td>
                          <td valign="top" width="22">&nbsp;
                            </td>
                          <td bgcolor="#fff0" style="background-color: rgb(255, 255, 255);" valign="top" width="8">&nbsp;
                            </td>
                        </tr>
                        <tr>
                          <td colspan="5" valign="top">&nbsp;
                            </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                  <!--/content -->
                </tr>
                <tr>
                  <td height="20" valign="top">&nbsp;
                    </td>
                </tr>
                <tr>
                  <td style="text-align: center;" valign="top">
                    <span style="color: rgb(102, 102, 102);"><span style="font-size: 10px;"><span style="font-family: verdana, geneva, sans-serif;">. Having trouble viewing this email? </span></span></span><span style="color: rgb(102, 102, 102);"><span style="font-size: 10px;"><span style="font-family: verdana, geneva, sans-serif;">View it in your <a href="<?php the_permalink(); ?>">browser</a></span></span></span><span style="color: rgb(102, 102, 102);"><span style="font-size: 10px;"><span style="font-family: verdana, geneva, sans-serif;">.</span></span></span></span></td>
                </tr>
              </tbody>
            </table>
            <br />
            <!--/container -->
          </td>
        </tr>
      </tbody>
    </table>
  </body>
<?php endwhile; endif; ?>
</html>