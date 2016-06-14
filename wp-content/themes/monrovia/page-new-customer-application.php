<?php
/**
	Name: Catalog Request
	
	@author Brett Exnowski
	@date 1/28/2015
**/
get_header(); 

	ini_set('display_errors','on');
	error_reporting(E_ALL);

//global $wpdb;

/** If there is POST Data, then execute the code! **/
if(isset($_POST['email']) && $_POST['email'] != ''){
	
/** Grab the POST Data **/
$table_name = 'customer_application';
//Default date
$date = date('M j, Y');
/* Success Message */
$msg = "Your application has been submitted!";

/* Get POST Data */
if(isset($_POST['type']))$type = $_POST['type'];
if(isset($_POST['resale'])){$resale = $_POST['resale'];} else { $resale = "null";}
if(isset($_POST['nursery']))$license = $nursery = $_POST['nursery'];
if(isset($_POST['contractor']))$contractor = $_POST['contractor'];
if(isset($_POST['phone']))$phone = $_POST['phone'];
if(isset($_POST['email']))$email = $_POST['email'];
if(isset($_POST['fname']))$fname = $_POST['fname'];
if(isset($_POST['cname']))$cname = $_POST['cname'];
if(isset($_POST['tname']))$bname = $_POST['tname'];
if(isset($_POST['city']))$city = $_POST['city'];
if(isset($_POST['state']))$state = $_POST['state'];
if(isset($_POST['zip']))$zip = $_POST['zip'];
if(isset($_POST['address']))$address = $_POST['address'];
if(isset($_POST['fax']))$fax = $_POST['fax'];
if(isset($_POST['website']))$website = $_POST['website'];

/* If not a Nusery or Wholeseller, then use contractor license */
if(!isset($_POST['nursery']))$license = $contractor;

/** Insert them into the DM **/
$wpdb->insert( 
	$table_name, 
	array( 
		'Type' => $type,
		'ResaleNumber' => $resale, 
		'BusinessLicense' => $license,  
		'LegalName' => $fname, 
		'Email' => $email,
		'TradeName' => $bname,
		'Address' => $address,
		'City' => $city, 
		'State' => $state, 
		'ZipCode' => $zip,
		'ContactName' => $cname, 
		'Phone' => $phone,
		'FAX' => $fax, 
		'Website' => $website, 
	) 
);

/** Generate Auto Emails **/
	$toEmail = get_post_meta( $post->ID, 'form_submission_email', true ); 
	/** To Admin **/
	$emailHeaders = "Reply-To: noreply@monrovia.com\r\n";
	$emailHeaders .= "Return-Path: noreply@monrovia.com\r\n";
	$emailHeaders .= "From: Monrovia <noreply@monrovia.com>\r\n";
	$emailHeaders .= 'Signed-by: monrovia.org\r\n"';
	$emailHeaders .= 'MIME-Version: 1.0' . "\r\n";
	$emailHeaders .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	//$toEmail = 'bexnowski@primitivespark.com';
	//$toEmail = "catalog@monrovia.com";
	$content = "Someone has submitted a New Customer Application<br /><br />";
	$content .= "Type: ".$type."<br />";
	if($type == 'garden-center'){
		$content .= "Resale #: ".$resale."<br />";
		$content .= "Nursery Business License #: ".$license."<br />";
	} else if($type == 'wholeseller'){
		$content .= "Nursery Business License #: ".$license."<br />";
	} else {
		$content .= "Resale #: ".$resale."<br />";
		$content .= "Contractors License #: ".$license."<br />";
	}
	$content .= "Customer Legal Name: ".$fname."<br />";
	$content .= "Trade Name: ".$bname."<br />";
	$content .= "Address: ".$address."<br />";
	$content .= "City: ".$city."<br />";
	$content .= "State: ".$state."<br />";
	$content .= "Zip Code: ".$zip."<br />";
	$content .= "Phone Number: ".$phone."<br />";
	if($fax){
		$content .= "FAX: ".$fax."<br />";
	}
	$content .= "Email: ".$email."<br />";
	$content .= "Website: ".$website."<br />";
	$content .= "<br />";

	mail( $toEmail, 'New Customer Application Submission', $content, $emailHeaders );
	
	/** To User **/
	$emailHeaders = "Reply-To: noreply@monrovia.com\r\n";
	$emailHeaders .= "Return-Path: noreply@monrovia.com\r\n";
	$emailHeaders .= "From: Monrovia <noreply@monrovia.com>\r\n";
	$emailHeaders .= 'Signed-by: monrovia.org\r\n"';
	$emailHeaders .= 'MIME-Version: 1.0' . "\r\n";
	$emailHeaders .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";	
	//$email = 'bexnowski@primitivespark.com';
	//$email = "info@museschool.org";
	$content = "Dear ".$fname.",<br /><br />";
	$content .= "";
	$content .= "<br /><br />";
	$content .="Best,<br />John Doe<br />Director of Catalogs";
	
	//mail( $email, "You're registered for a MUSE High School Open House", $content, $emailHeaders );
}

?>
	
    <div class="content_wrapper clear">
		<section role="main" class="main">
        <?php the_breadcrumb($post->ID); //Print the Breadcrumb ?>
			<h1><?php the_title(); ?></h1>
			<?php if (have_posts()): while (have_posts()) : the_post(); ?>
			<!-- article -->
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php the_content(); ?>
			</article>
            <?php if(isset($msg)){ ?>
            <p class="global-message message" style="display:block;"><?php echo $msg; ?></p>
            <?php } else { ?>
            <p class="global-message message"></p>
            <?php } ?> 
            <form action="" method="POST" enctype="application/x-www-form-urlencoded" id="application" name="application" class="clear basic" onSubmit="return checkForm(jQuery('#application'));">
                <div class="form-item">
                    Please Choose one of the following:
                    <ul class="form-options clear">
                        <li>
                            <input type="radio" name="type" value="garden-center" id="garden-center" checked="checked"/>
                            <label for="garden-center">Garder Center</label>
                        </li>
                        <li>
                            <input type="radio" name="type" value="wholeseller" id="wholeseller" />
                            <label for="wholeseller">Rewholeseller</label>
                        </li>
                        <li>
                            <input type="radio" name="type" value="landscaper" id="landscaper" />
                            <label for="landscaper">Landscaper</label>
                        </li>
                    </ul>
                </div><!-- end form-item -->
                <div class="form-item">
                    <div class="clear">
                        <div class="form-item sub-option garden-center landscaper" data-type="resale" style="display:block">
                            <label for="resale"><span class="req">*</span>Resale #</label>
                            <input type="text" name="resale" id="resale" data-req='multi' class="checkKeypress checkBlur"/>
                        </div><!-- end form item -->
                        <div class="form-item sub-option garden-center wholeseller" data-type="nursery" style="display:block">
                            <label for="nursery"><span class="req">*</span>Nursery Business License #</label>
                            <input type="text" name="nursery" id="nursery" data-req='multi' class="checkKeypress checkBlur"/>
                        </div><!-- end form item -->
                        <div class="form-item sub-option landscaper" data-type="contractor">
                            <label for="contractor"><span class="req">*</span>Contractor License #</label>
                            <input type="text" name="contractor" id="contractor" data-req='multi' class="checkKeypress checkBlur"/>
                        </div><!-- end form item -->
                    </div>
                </div><!-- end form-item -->
                <div class="form-item">	
                	<label for="fname"><span class="req">*</span>Customer Legal Name</label>
                    <input type="text" name="fname" id="fname" data-req='true' class="checkKeypress checkBlur"/>
                </div><!-- end form-item -->
                <div class="form-item">	
                	<label for="tname"><span class="req">*</span>Trade Name</label>
                    <input type="text" name="tname" id="tname" data-req='true' class="checkKeypress checkBlur"/>
                </div><!-- end form-item -->
                <div class="form-item">	
                	<label for="address"><span class="req">*</span>Billing Address</label>
                    <input type="text" name="address" id="address"data-req='true' class="checkKeypress checkBlur" />
                </div><!-- end form-item -->
                <div class="form-item">	
                	<label for="city"><span class="req">*</span>City</label>
                    <input type="text" name="city" id="city" data-req='true' class="checkKeypress checkBlur"/>
                </div><!-- end form-item -->
                <div class="form-item state">	
                	<label for="state"><span class="req">*</span>State</label>
                    <input type="text" name="state" id="state" maxlength="2" data-req='true' class="checkKeypress checkBlur"/>
                </div><!-- end form-item -->
                <div class="form-item zip">	
                	<label for="zip"><span class="req">*</span>Zip Code</label>
                    <input type="text" name="zip" id="zip" data-req='true' class="checkKeypress checkBlur"/>
                </div><!-- end form-item -->
                <div class="form-item">	
                	<label for="cname"><span class="req">*</span>Contact Name</label>
                    <input type="text" name="cname" id="cname" data-req='true' class="checkKeypress checkBlur"/>
                </div><!-- end form-item -->
                <div class="form-item number">	
                	<label for="phone"><span class="req">*</span>Phone Number</label>
                    <input type="text" name="phone" id="phone" data-req='true' class="checkKeypress checkBlur"/>
                </div><!-- end form-item -->
                <div class="form-item number">	
                	<label for="fax">Fax Number</label>
                    <input type="text" name="fax" id="fax" />
                </div><!-- end form-item -->
                <div class="form-item">	
                	<label for="email"><span class="req">*</span>Email Address</label>
                    <input type="text" name="email" id="email" data-req='true' class="checkKeypress checkBlur"/>
                </div><!-- end form-item -->
                <div class="form-item">	
                	<label for="website"><span class="req">*</span>Website</label>
                    <input type="text" name="website" id="website" data-req='true' class="checkKeypress checkBlur"/>
                </div><!-- end form-item -->
                <p class="required"><span class="req">*</span>Required Fields</p>
                <div class="form-item">	
                	<input type="submit" class="submit-button" name="submit" value="Submit" />
                </div><!-- end form-item -->
            </form>
			<!-- /article -->
			<?php endwhile; endif; ?>
		</section>
		<?php get_sidebar('right'); ?>
	</div><!-- end content_wrapper -->
    <script>
		jQuery(document).ready( function(){
			// Show/Hide options based on which radio option is selected.
			jQuery('.form-options input').on('click', function(){
				var type = jQuery(this).val();
				jQuery('.sub-option').each( function(){
					if(jQuery(this).hasClass(type)){
						jQuery(this).slideDown();
					} else {
						jQuery(this).slideUp();	
					}
				});
			});
		});
		
	</script>
    
<?php get_footer(); ?>