<?php
function output_side_context_bar($subsection=''){ ?>
			<style>
			.side_context_bar { min-height:inherit; }
			/* HIDE GLOBAL SEARCH ON RETAILER SECTION */
				#global_search {
					display:none;
				}
				#masthead_image {
					background-image:url(/img/masthead_no_search.gif);
				}
				.side_context_bar h3 {
					font-size:16px;
					color:#575430;
					margin:8px 0 5px 0;
				}
			</style>

			<? include($_SERVER['DOCUMENT_ROOT'].'/inc/side_context_bar_header.php'); ?>

			<h3>elsewhere in retailers &amp; professionals</h3>
			<ul>
				<li id="lnk_sidebar_retail_gardengateway"><a href="https://mnc400.monrovia.com/gg/prod/Logon.php" target="_blank">garden gateway</a></li>
				<li id="lnk_sidebar_retail_application"><a href="https://azdomino.monrovia.com/Register.nsf/ProspectInfo!OpenForm" target="_blank">new retailer application</a></li>
				<li id="lnk_sidebar_retail_resources"><a href="/retail/online-resources/">online resources</a></li>
				<li id="lnk_sidebar_retail_social_media_tips"><a href="/retail/social-media-tips.php">social media tips</a></a>
				<li id="lnk_sidebar_retail_finddesigner"><a href="/landscape-architects/join" target="_blank">find a design professional</a></li>
				<li id="lnk_sidebar_retail_request"><a href="https://azdomino.monrovia.com/Feedback.nsf/CatalogRequest?OpenForm" target="_blank">request a catalog</a></li>
				<li id="lnk_sidebar_retail_faqs"><a href="/retail/faqs-retail.php">retailers FAQs</a></li>
				<li id="lnk_sidebar_retail_rewholesalers"><a href="/retail/find-a-rewholesaler.php">find a re-wholesaler</a></li>
			</ul>

			<?php switch($subsection){
				 case 'online_resources': ?>
					<h3>elsewhere in online resources</h3>
					<ul>
						<li id="lnk_sidebar_retail_brandedbanners"><a href="/retail/online-resources/banners-monrovia-branded.php">Monrovia branded banners</a></li>
						<li id="lnk_sidebar_retail_plantspecific"><a href="/retail/online-resources/banners-plant-specific.php">plant-specific banners</a></li>
						<li id="lnk_sidebar_retail_featuredcollections"><a href="/retail/online-resources/banners-featured-plant-collections.php">featured collections banners</a></li>
						<li id="lnk_sidebar_retail_copyblurbs"><a href="/retail/online-resources/more-content-copy-blurbs.php">copy blurbs</a></li>
						<li id="lnk_sidebar_retail_whymonrovia"><a href="/retail/online-resources/more-content-why-monrovia.php">why Monrovia?</a></li>
					</ul>
				<? break; ?>
			<? } ?>
			<div>
				MONROVIA STAFF USE ONLY<br />
				<ul>
					<li id="lnk_sidebar_retail_craftsmen"><a href="https://azdomino.monrovia.com/login" target="_blank">craftsmen site</a></li>
				</ul>
			</div>
		</div>
	</div>
<? } ?>