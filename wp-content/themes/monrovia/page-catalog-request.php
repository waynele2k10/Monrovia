<?php
/**
	Name: Catalog Request
	
	@author Brett Exnowski
	@date 1/28/2015
**/

//global $wpdb;
get_header(); 

/** If there is POST Data, then execute the code! **/
if(isset($_POST['email']) && $_POST['email'] != ''){
	
/** Grab the POST Data **/
$table_name = 'catalog_requests';
//Default date
$date = date('M j, Y');
/* Success Message */
$msg = "Your Catalog Request has been submitted!";

/* Get POST Data */
if(isset($_POST['phone']))$phone = $_POST['phone'];
if(isset($_POST['email']))$email = $_POST['email'];
if(isset($_POST['fname']))$fname = $_POST['fname'];
if(isset($_POST['bname']))$bname = $_POST['bname'];
if(isset($_POST['city']))$city = $_POST['city'];
if(isset($_POST['state']))$state = $_POST['state'];
if(isset($_POST['zip']))$zip = $_POST['zip'];
if(isset($_POST['address']))$address = $_POST['address'];
if(isset($_POST['fax']))$fax = $_POST['fax'];
if(isset($_POST['rep']))$rep = $_POST['rep'];
if(isset($_POST['customer-number']))$customerNumber = $_POST['customer-number'];
if(isset($_POST['ref']))$ref = $_POST['ref'];

/** Insert them into the DM **/
$wpdb->insert( 
	$table_name, 
	array( 
		'Name' => $fname, 
		'Email' => $email,
		'BusinessName' => $bname,
		'Address' => $address,
		'City' => $city, 
		'State' => $state, 
		'ZipCode' => $zip, 
		'Phone' => $phone,
		'FAX' => $fax, 
		'SalesRep' => $rep, 
		'CustomerNumber' => $customerNumber, 
		'Referal' => $ref,
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
	$content = "Someone has submitted a request for a Catalog<br /><br />";
	$content .= "Name: ".$fname."<br />";
	$content .= "Email: ".$email."<br />";
	$content .= "Business Name: ".$bname."<br />";
	$content .= "Business Address: ".$address."<br />";
	$content .= "City: ".$city."<br />";
	$content .= "State: ".$state."<br />";
	$content .= "Zip Code: ".$zip."<br />";
	$content .= "Business Phone Number: ".$phone."<br />";
	if($fax){
		$content .= "FAX: ".$fax."<br />";
	}
	
	$content .= "Sales Rep: ".$rep."<br />";
	$content .= "Customer Number: ".$customerNumber."<br />";
	$content .= "How did you hear about us: ".$ref."<br />";
	$content .= "<br />";

	mail( $toEmail, 'Catalog Request', $content, $emailHeaders );
	
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
			<!-- /article -->
            <?php if(isset($msg)){ ?>
            <p class="global-message message" style="display:block;"><?php echo $msg; ?></p>
            <?php } else { ?>
            <p class="global-message message"></p>
            <?php } ?> 
             <form action="" method="POST" enctype="application/x-www-form-urlencoded" id="catalog" name="catalog" class="clear basic" onSubmit="return checkForm(jQuery('#catalog'));">
                <div class="form-item">	
                	<label for="fname"><span class="req">*</span>Your Name</label>
                    <input type="text" name="fname" id="fname" data-req='true' class="checkKeypress checkBlur"/>
                </div><!-- end form-item -->
                <div class="form-item">	
                	<label for="email"><span class="req">*</span>Your Email</label>
                    <input type="text" name="email" id="email" data-req='true' class="checkKeypress checkBlur"/>
                </div><!-- end form-item -->
                <div class="form-item">	
                	<label for="bname"><span class="req">*</span>Business Name</label>
                    <input type="text" name="bname" id="bname" data-req='true' class="checkKeypress checkBlur"/>
                </div><!-- end form-item -->
                <div class="form-item">	
                	<label for="address"><span class="req">*</span>Business Address</label>
                    <input type="text" name="address" id="address" data-req='true' class="checkKeypress checkBlur"/>
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
                    <input type="text" name="zip" id="zip" max-length="10" data-req='true' class="checkKeypress checkBlur"/>
                </div><!-- end form-item -->
                <div class="form-item number">	
                	<label for="phone"><span class="req">*</span>Business Phone</label>
                    <input type="text" name="phone" id="phone" data-req='true' class="checkKeypress checkBlur"/>
                </div><!-- end form-item -->
                <div class="form-item number">	
                	<label for="fax">Fax Number</label>
                    <input type="text" name="fax" id="fax" />
                </div><!-- end form-item -->
                <h2>How did you hear about Monrovia?</h2>
                <p>This information is optional, but may help us serve you faster.</p>
                <div class="form-item">	
                	<label for="rep">Name of a sales rep you talked to</label>
                    <input type="text" name="rep" id="rep" />
                </div><!-- end form-item -->
                <div class="form-item">	
                	<label for="customer-number">Your customer number</label>
                    <input type="text" name="customer-number" id="customer-number" />
                </div><!-- end form-item -->
                <div class="form-item">	
                	<label for="ref">How did you hear about us?</label>
                    <div class="select-wrap">
                        <select name="ref" id="ref">
                            <option value="" selected="selected">Select one...</option>
                            <option value="Customer">I'm already a customer</option>
                            <option value="SalesRep">A sales rep talked to me</option>
                            <option value="Friend">Heard from a friend</option>
                            <option value="Specified">Specified by a customer or designer</option>
                            <option value="Magazine">Saw Monrovia in a magazine</option>
                            <option value="Trade Show">Saw Monrovia at a trade show</option>
                            <option value="Retailer">Saw Monrovia at a retailer</option>
                        </select>
                    </div>
                </div><!-- end form-item -->
                <p class="required"><span class="req">*</span>Required Fields</p>
                <div class="form-item">	
                	<input type="submit" class="button" name="submit" value="Submit" />
                </div><!-- end form-item -->
            </form>
			<?php endwhile; endif; ?>
		</section>
		<?php get_sidebar('right'); ?>
	</div><!-- end content_wrapper -->
    
<?php get_footer(); ?>