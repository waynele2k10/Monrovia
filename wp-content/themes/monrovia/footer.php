			</div><!-- /wrapper -->
            <div id="exposeMask"></div>
			<!-- footer -->
            <!-- Cold Zone Box FOR MOBILE -->
            <div class="showMobile">
    			<?php include('includes/mobile-zone-box.php'); ?>
     		</div>
            <!-- end Cold Zone Box -->
			<footer class="footer" role="contentinfo">
            	<div class="footer-menu">
					<?php wp_nav_menu(array( 'menu' => 'footer-menu') ); ?>
                </div><!-- end footer menu -->
				<!-- copyright -->
				<p class="copyright">
					 Copyright &copy; <?php echo date("Y"); ?> Monrovia. All rights reserved.
				</p>
				<!-- /copyright -->
			</footer>
            <a href="javascript:void(0);" id="toTop" class="showMobile" onclick='jQuery("html, body").animate({ scrollTop: 0 }, 600);'>Back to Top <i class="fa fa-arrow-circle-up"></i></a>
			<!-- /footer -->
		


		<?php wp_footer(); ?>
         <?php if(strpos($_SERVER['REQUEST_URI'],'/event-calendar/') === false && strpos($_SERVER['REQUEST_URI'],'/catalogs/') === false) :  ?>
        <script>
			jQuery(document).ready(function($){
    			jQuery('select').customSelect();
			});
		</script>
        <?php endif; ?>
        
        <script>	
		// Delete Zone Cookie if Profile was updated 
		// Tried to hook into WP profile update function
		// But wasnt able to print out js in the php hook
		<?php if(isset($_GET['updated']) && $_GET['updated'] == 'true') {?>
				eraseCookie('zip_code');
				eraseCookie('cold_zone');
				// Call getZone to set the Cookie values
				getZone();
		<?php } ?>
		</script>
        		
        <!-- font awesome css -->
        <link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet" >
        
        <?php if(strpos($_SERVER['REQUEST_URI'],'/event-calendar/') === false) :  ?>
        <!-- Google Maps -->
        <!-- TODO: Attach only on specific pages -->
        <script src="https://maps.googleapis.com/maps/api/js?sensor=false" type="text/javascript"></script>
        <?php endif; ?>
		
		<!-- Google Analytics -->
<script type="text/javascript">
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
  
  ga('create', 'UA-3929008-1', 'auto');
  ga('send', 'pageview');
</script>
        
        <!-- FACEBOOK Tracking -->
       <?php if(strpos($_SERVER['REQUEST_URI'],'/plant-catalog/plants/') === false && strpos($_SERVER['REQUEST_URI'],'/find-a-garden-center/') === false ) :  ?>

		<script type="text/javascript">
		var fb_param = {};
		fb_param.pixel_id = '6007795409400';
		fb_param.value = '0.00';
		fb_param.currency = 'USD';
		(function(){
  			var fpw = document.createElement('script');
  			fpw.async = true;
  			fpw.src = '//connect.facebook.net/en_US/fp.js';
  			var ref = document.getElementsByTagName('script')[0];
  			ref.parentNode.insertBefore(fpw, ref);
		})();
		</script>
		<noscript><img height="1" width="1" alt="" style="display:none" src="https://www.facebook.com/offsite_event.php?id=6007795409400&amp;value=0&amp;currency=USD" /></noscript>
		<script type="text/javascript">
		var fb_param = {};
		fb_param.pixel_id = '6007795396000';
		fb_param.value = '0.00';
		fb_param.currency = 'USD';
		(function(){
  			var fpw = document.createElement('script');
  			fpw.async = true;
  			fpw.src = '//connect.facebook.net/en_US/fp.js';
  			var ref = document.getElementsByTagName('script')[0];
  			ref.parentNode.insertBefore(fpw, ref);
		})();
		</script>
		<noscript><img height="1" width="1" alt="" style="display:none" src="https://www.facebook.com/offsite_event.php?id=6007795396000&amp;value=0&amp;currency=USD" /></noscript>

	<?php endif; ?>
	<!-- /FACEBOOK Tracking -->
        
        <!-- ADDTHIS -->
		<script type="text/javascript">
			var addthis_config = {
				//'data_track_addressbar':true,
				'services_expanded':'delicious,digg,live',
				'data_ga_property':'UA-3929008-1',
				'data_ga_social':true
			};
			
			var current_path = '<?php echo $_SERVER['REQUEST_URI']; ?>';
                        
            if(current_path.indexOf("12-days-of-springtime") != -1 ) {
                var addthis_share = {
                        url: document.URL,
                        passthrough : {
                            twitter: {
                                text: "Loving @MonroviaPlants #12daysSpring campaign. Cool stuff every day till March 20"
                            }
                        }
                };
                addthis_config.ui_email_note = 'Hey! Check out 12 Days of Springtime from Monrovia. What a fun campaign. Gardening tips, secrets, and videos every day till March 20!';
            } else {
                var addthis_share = {
                        url: document.URL
                };
            }
		</script>
		<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=tpgmonrovia"></script>
	<!-- /ADDTHIS -->
    <!-- One Tag Conditional Container: Monrovia (5920) | Monrovia OneTag (4345) -->
    
    <script type="text/javascript">
    var ft_onetag_4345 = {
        ft_vars:{
            "ftXRef":"",
            "ftXValue":"",
            "ftXType":"",
            "ftXName":"",
            "ftXNumItems":"",
            "ftXCurrency":"",
            "U1":"",
            "U2":"",
            "U3":"",
            "U4":"",
            "U5":"",
            "U6":"",
            "U7":"",
            "U8":"",
            "U9":"",
            "U10":"",
            "U11":"",
            "U12":"",
            "U13":"",
            "U14":"",
            "U15":"",
            "U16":"",
            "U17":"",
            "U18":"",
            "U19":"",
            "U20":""
            },
        ot_dom:document.location.protocol+'//servedby.flashtalking.com',
        ot_path:'/container/5920;37112;4345;iframe/?',
        ot_href:'ft_referrer='+escape(document.location.href),
        ot_rand:Math.random()*1000000,
        ot_ref:document.referrer,
        ot_init:function(){
            var o=this,qs='',count=0,ns='';
            for(var key in o.ft_vars){
                qs+=(o.ft_vars[key]==''?'':key+'='+o.ft_vars[key]+'&');
            }
            count=o.ot_path.length+qs.length+o.ot_href+escape(o.ot_ref).length;
            ns=o.ot_ns(count-2000);
            document.write('<iframe style="position:absolute; visibility:hidden; width:1px; height:1px;" src="'+o.ot_dom+o.ot_path+qs+o.ot_href+'&ns='+ns+'&cb='+o.ot_rand+'"></iframe>');
        },
        ot_ns:function(diff){
            if(diff>0){
                var o=this,qo={},
                    sp=/(?:^|&)([^&=]*)=?([^&]*)/g,
                    fp=/^(http[s]?):\/\/?([^:\/\s]+)\/([\w\.]+[^#?\s]+)(.*)?/.exec(o.ot_ref),
                    ro={h:fp[2],p:fp[3],qs:fp[4].replace(sp,function(p1,p2,p3){if(p2)qo[p2]=[p3]})};
                return escape(ro.h+ro.p.substring(0,10)+(qo.q?'?q='+unescape(qo.q):'?p='+unescape(qo.p)));
            }else{
                var o=this;
                return escape(unescape(o.ot_ref));
            }
                }
        }
    ft_onetag_4345.ot_init();
    </script>
    
    <?php
		//Fire the CRON
	 	wp_cron();
     ?>
	
	</body>
</html>