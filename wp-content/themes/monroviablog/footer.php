<?php // Exit if accessed directly
if (!defined('ABSPATH')) {echo '<h1>Forbidden</h1>'; exit();} ?>

        </div><!-- .container (initiated at header -->

        <footer>
            <div class="col-sm-6 footer-left">
                 <div class="footer-wrapper">
                     <div>
                        <h4>Sign up for our newsletter</h4>
                        <!--p>Sign up for our newsletter</p-->
                     </div>
                     <div class="newletter">
                        <input type="text" class="form-control">
                        <button class="btn">GO</button>
                        <div style="clear: both;"></div>
                     </div>
                 </div>   
            </div>
            <div class="col-sm-6 footer-right">
                <div class="footer-wrapper">
                    <div>
                        <h4 style="display:inline-block;">Connect with us</h4>
                        <!--p>Connect with us</p-->
                        <a style="display:inline-block;padding-left: 5px;" href="http://growbeautifully.monrovia.com/feed/">
                            <img style="width: 16px;padding-bottom: 6px;" src="<?php echo get_template_directory_uri(); ?>/assets/imgs/icon/rss.png">
                        </a>
                    </div>
                    <?php 
                        $url = 'http://' . $_SERVER['SERVER_NAME'];
                        $pin_url='http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
                    ?>
                    <div class="social">
                        <a target="_blank" href="<?php echo get_social_facebook_html () ?>" title="Facebook">
                            <!--<img src="<?php echo get_template_directory_uri(); ?>/assets/imgs/icon/icon_f.png">-->
                            <i class="fa fa-facebook"></i>
                        </a>
                        <a target="_blank" href="<?php echo get_social_twitter_html () ?>" title="Twitter">
                            <!--<img src="<?php echo get_template_directory_uri(); ?>/assets/imgs/icon/icon_t.png">-->
                            <i class="fa fa-twitter"></i>
                        </a>
                        <a target="_blank" href="<?php echo get_social_pinterest_html () ?>" title="Pinterest">
                            <!--<img src="<?php echo get_template_directory_uri(); ?>/assets/imgs/icon/icon_p.png">-->
                            <i class="fa fa-pinterest"></i>
                        </a>
                        <a target="_blank" href="<?php echo get_social_googleplus_html () ?>" title="Google+">
                            <!--<img src="<?php echo get_template_directory_uri(); ?>/assets/imgs/icon/icon_g.png">-->
                            <i class="fa fa-google-plus"></i>
                        </a>
                    </div>
                </div>
            </div>
        </footer>

        <?php wp_footer(); ?>

        <!--[if lt IE 9]>
	<script src="<?php echo get_template_directory_uri(); ?>/assets/libs/respond.min.js"></script>
	<![endif]-->

<!-- Google Analytics -->
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-3929008-1', 'auto');
  ga('send', 'pageview');

</script>


        <!-- ADDTHIS -->
        <script type="text/javascript">
            var addthis_config = {
                //'data_track_addressbar':true,
                'services_expanded':'delicious,digg,live',
                'data_ga_property':'UA-3929008-1',
                'data_ga_social':true
            };
                        
            var addthis_share = {
                url: document.URL
            };
            
        </script>
        <script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=tpgmonrovia"></script>
    <!-- /ADDTHIS -->
    <script type="text/javascript">
        jQuery(window).load(function(){         
            jQuery(".addthis_button_pinterest_share").removeAttr("target");
        });
    </script>    
    </body>
</html>