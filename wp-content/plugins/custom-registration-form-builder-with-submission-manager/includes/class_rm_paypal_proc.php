<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


global $wpdb;
$textdomain = 'custom-registration-form-builder-with-submission-manager';
$crf_paypal_log = $wpdb->prefix . "crf_paypal_log";
$crf_fields = $wpdb->prefix . "crf_fields";
$crf_purchases = $wpdb->prefix . "crf_purchases";
$path = plugin_dir_url(__FILE__);
$send_box = get_site_option('crf_test_mode', 'no');
$crf_paypal_email = get_site_option('crf_paypal_email');
$crf_currency = get_site_option('crf_currency');
$crf_paypal_page_style = get_site_option('crf_paypal_page_style');
$p = new crf_paypal_class(); // paypal class
$p->admin_mail = $form_fields->crf_get_admin_email(); // set notification email
$action = $_REQUEST["action"];

if ($send_box == 'yes')
    $p->toggle_sandbox(true);
else
    $p->toggle_sandbox(false);
//var_dump($_POST);die;


switch ($action)
{
    case "process": // case process insert the form data in DB and process to the paypal
        
        /******************************************
         * Leave it to our old superhero the DB-Man.
        $form_fields->crf_insert_submission($entry_id, $content['id'], 'payment_status', 'pending');
        $form_fields->crf_insert_submission($entry_id, $content['id'], 'invoice', $_POST["invoice"]);
        $pricing_field = $form_fields->crf_get_pricing_field($content['id']);
        *****************************************/
        
        
        if (!empty($pricing_field))
        {
            foreach ($pricing_field as $field)
            {
                $product[] = $form_fields->crf_get_pricing_field_Submission_value($entry_id, $field);
            }
        }
        if (!empty($product))
        {
            $itemnumber = 1;
            foreach ($product as $item)
            {
                //print_r($item);
                foreach ($item as $key => $amout)
                {
                    $p->add_field('item_name_' . $itemnumber, $key);
                    $p->add_field('amount_' . $itemnumber, $amout);
                    $itemnumber++;
                }
            }
        }
        $this_script = get_permalink();
        $sign = strpos($this_script, '?') ? '&' : '?';
        $p->add_field('business', $crf_paypal_email); // Call the facilitator eaccount
        $p->add_field('cmd', $_POST["cmd"]); // cmd should be _cart for cart checkout
        $p->add_field('upload', '1');
        $p->add_field('return', $this_script . $sign . 'action=success&id=' . $entry_id); // return URL after the transaction got over
        $p->add_field('cancel_return', $this_script . $sign . 'action=cancel'); // cancel URL if the trasaction was cancelled during half of the transaction
        $p->add_field('notify_url', $this_script . $sign . 'action=ipn'); // Notify URL which received IPN (Instant Payment Notification)
        $p->add_field('currency_code', $crf_currency);
        $p->add_field('invoice', $_POST["invoice"]);
        $p->add_field('custom', $entry_id);
        $p->add_field('page_style', $crf_paypal_page_style);
        $p->submit_paypal_post(); // POST it to paypal
        //$p->dump_fields(); die;
        break;
        
        
    case "success": // success case to show the user payment got success
        echo '<div id="crf-form">';
        echo "<div class='info-text'>Payment Transaction Done Successfully</br>";
        //echo '<P>Use this below URL in paypal sandbox IPN Handler URL to complete the transaction</p>';
        //echo '<p>'.get_page_link().'?action=ipn</p>';
        echo '</div></div>';
        //print_r($_POST);
        /* Show Success Message start */
        $form_fields->crf_get_success_message($content['id']);
        $form_fields->crf_get_sumission_token_number($content['id'], $_REQUEST['id']);
        //echo $_REQUEST['id'];die;
        /* Show Success Message end */
        /* Get redirection start */
        $redirect_option = $form_fields->crf_get_form_option_value('redirect_option', $content['id']);
        $url = $form_fields->crf_get_redirect_url($content['id'], $redirect_option);
        if ($redirect_option != 'none')
        {
            header('refresh: 5; url=' . $url);
        }
        break;
        
        
    case "cancel": // case cancel to show user the transaction was cancelled
        echo '<div id="crf-form">';
        echo "<div class='info-text'>Transaction Cancelled</br>";
        echo '</div></div>';
        break;
    
    
    case "ipn": // IPN case to receive payment information. this case will not displayed in browser. This is server to server communication. PayPal will send the transactions each and every details to this case in secured POST menthod by server to server. 
        //echo '<pre>';
        //print_r($_REQUEST);
        //echo '</pre>';
        $trasaction_id = $_POST["txn_id"];
        $payment_status = strtolower($_POST["payment_status"]);
        $invoice = $_POST["invoice"];
        $entry_id = $_POST["custom"];
        $log_array = maybe_serialize($_POST);
        $log_query = "SELECT count(*) FROM $crf_paypal_log WHERE `txn_id` = '" . $trasaction_id . "'";
        $log_check = $wpdb->get_var($log_query);
        if ($log_check <= 0)
        {
            $qry = "INSERT INTO $crf_paypal_log (`txn_id`, `log`, `posted_date`) VALUES ('" . $trasaction_id . "', '" . $log_array . "', NOW())";
            $wpdb->query($qry);
        } else
        {
            $qry = "UPDATE $crf_paypal_log  SET `log` = '" . $log_array . "' WHERE `txn_id` = '" . $trasaction_id . "'";
            $wpdb->query($qry);
        } // Save and update the logs array
        $paypal_log_id = $wpdb->get_var("SELECT id FROM $crf_paypal_log WHERE `txn_id` = '" . $trasaction_id . "'");
        if ($p->validate_ipn())
        { // validate the IPN, do the others stuffs here as per your app logic
            $form_fields->crf_insert_submission($entry_id, $content['id'], 'trasaction_id', $trasaction_id);
            $form_fields->crf_insert_submission($entry_id, $content['id'], 'paypal_log_id', $paypal_log_id);
            $form_fields->crf_update_submission($entry_id, 'payment_status', $payment_status);
            $form_fields->crf_update_submission($entry_id, 'invoice', $invoice);
            $subject = 'Instant Payment Notification - Recieved Payment';
            //$p->send_report($subject); // Send the notification about the transaction
            if ($form_type == 'reg_form' && $payment_status == 'completed')
            {
                $userid = $form_fields->crf_create_new_user($entry_id);
                if (!is_wp_error($userid))
                {
                    $form_fields->crf_insert_user_meta($entry_id, $userid);
                    $form_fields->crf_create_user_notification($entry_id);
                }
            }
        } 
        else
        {
            $subject = 'Instant Payment Notification - Payment Fail';
            //$p->send_report($subject); // failed notification
        }
        break;
}
?>
