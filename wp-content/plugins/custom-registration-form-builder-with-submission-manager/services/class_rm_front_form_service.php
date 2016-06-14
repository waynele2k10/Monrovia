<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class_rm_services
 *
 * @author CMSHelplive
 */
class RM_Front_Form_Service extends RM_Services
{

    private $user_service;

    public function __construct()
    {
        $this->user_service = new RM_User_Services();
    }

    private function get_user_ip()
    {
        switch (true)
        {
            case (!empty($_SERVER['HTTP_X_REAL_IP'])) : return $_SERVER['HTTP_X_REAL_IP'];
            case (!empty($_SERVER['HTTP_CLIENT_IP'])) : return $_SERVER['HTTP_CLIENT_IP'];
            case (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) : return $_SERVER['HTTP_X_FORWARDED_FOR'];
            case (!empty($_SERVER['REMOTE_ADDR'])) : return $_SERVER['REMOTE_ADDR'];
            default : return null;
        }
    }

    public function create_stat_entry($params)
    {

        $form_id = (int) $params['form_id'];
        $visited_on = time();

        $user_ip = $this->get_user_ip();

        if ($user_ip == null)
            die("Unauthorised request. Access denied.");

        if (isset($_SERVER['HTTP_USER_AGENT']))
            $ua_string = $_SERVER['HTTP_USER_AGENT'];
        else
            $ua_string = "no_user_agent_found";

        require_once plugin_dir_path(plugin_dir_path(__FILE__)) . 'external/Browser/Browser.php';

        $browser = new Browser($ua_string);
        $browser_name = $browser->getBrowser();

        return RM_DBManager::insert_row('STATS', array('form_id' => $form_id, 'user_ip' => $user_ip, 'ua_string' => $ua_string, 'browser_name' => $browser_name, 'visited_on' => $visited_on), array('%d', '%s', '%s', '%s'));
    }

    public function update_stat_entry($stat_id)
    {
        //$submitted_on = new DateTime();
        $submitted_on = time();

        $visited_on = RM_DBManager::get_row('STATS', $stat_id);

        if ($visited_on)
        {
            $diff_in_secs = $submitted_on - $visited_on->visited_on;
            return RM_DBManager::update_row('STATS', $stat_id, array('submitted_on' => $submitted_on, 'time_taken' => $diff_in_secs), array('%s', '%d'));
        } else
            return false;
    }

    //Check if the form is being submitted through browser reload feature.
    public function is_browser_reload_duplication($stat_id)
    {
        //Not browser reload related, but if stat_id is not set then form submission is not valid or
        // it is just form creation, hence prevent submission.
        if ($stat_id === null)
            return true;

        $stat_entry = RM_DBManager::get_row('STATS', $stat_id);

        if ($stat_entry)
        {
            if ($stat_entry->submitted_on == null)
                return false;
            else
                return true;
        }
        return true; //No entry found in db, prevent submission.
    }

    public function is_off_limit_submission($form_id)
    {
        $submission_limit_per_ip_per_form = (int) $this->get_setting('sub_limit_antispam');

        if ($submission_limit_per_ip_per_form == 0)
            return false;

        //Calculate starting and ending timestamp for today.
        $N = time();
        $n = 24 * 60 * 60;
        $t = $N % $n;

        $start_ts = $N - $t;
        $end_ts = $start_ts + $n - 1;

        $ip = $this->get_user_ip();
        $res = RM_DBManager::get_generic('STATS', "COUNT(#UID#) AS `count`", "`form_id` = $form_id AND `user_ip` = '$ip' AND `submitted_on` BETWEEN '$start_ts' AND '$end_ts'");

        if (!$res)
            return false;

        // IMP: Do not use '<='. As it counts already done submissions which excludes current submission.
        // If already done submissios are limit-1 then allow this one. Otherwise there will be one extra submission.
        if ((int) $res[0]->count < $submission_limit_per_ip_per_form)
            return false;
        else
            return true;
    }

    public function save_form($request, $params, $register_user = true, $redirect_as_well = true)
    {
        if (isset($params['is_payment_form']) && $params['is_payment_done'])
        {
            //user already activated in 'ipn' case, do nothing.
            //register user if it was a 'zero_amount case'.
            if (isset($params['za_user_id']) && $params['za_user_id'])
            {
                $gopt = new RM_Options;
                $this->user_service->activate_user_by_id($params['za_user_id']);
            }
            $this->after_submission_proc($params);
            return;
        }

        $user_error = false;

        $form_fields = parent::get_all_form_fields($params['form']->form_id);

        $valid_field_ids = $profile_field_id = array();

        $form_type = $params['form']->form_type;

        $is_auto_generate = parent::get_setting('auto_generated_password');

        $email = null;

        $profile = array();

        $reg_data = new stdClass;

        $reg_data->submission_id = null;
        $reg_data->user_id = null;

        foreach ($form_fields as $form_field)
        {
            $valid_field_ids[] = $form_field->field_id;

            if ($form_field->field_type === 'Email' && $form_field->is_field_primary == 1)
                $profile_field_id['email'] = $form_field->field_id;

            if ($form_type == 1)
            {
                if ($form_field->field_type === 'Textbox' && $form_field->is_field_primary == 1)
                    $profile_field_id['username'] = $form_field->field_id;

                if ($form_field->field_type === 'Password' && $form_field->is_field_primary == 1)
                    $profile_field_id['password'] = $form_field->field_id;
            }
        }

        $submissions_data = array();
        $attachment = new RM_Attachment_Service();
        $att_ids = $attachment->attach();
        //var_dump($att_ids);
        foreach ($request->req as $key => $value)
        {
            $key_parts = explode('_', $key);
            $count_parts = count($key_parts);
            if (($count_parts === 2 || $count_parts === 3) && in_array($key_parts[1], $valid_field_ids, true))
            {
                $field = new RM_Fields;
                $field->load_from_db($key_parts[1]);
                if ($field->field_type === $key_parts[0])
                {
                    if ($field->field_type === "Password")
                    {
                        if ($is_auto_generate === 'yes')
                            $value = wp_generate_password(8, false);
                        //$value = md5($value);
                    }

                    elseif ($field->field_type === "Fname" || $field->field_type === "Lname" || $field->field_type === "BInfo")
                    {
                        $profile[$field->field_type] = $value;
                    } else if ($field->field_type === "File")
                    {
                        $value = null;
                        foreach ($att_ids as $field_name => $att_id)
                        {
                            $value = array();

                            if ($field_name == $key)
                            {
                                $value['rm_field_type'] = 'File';

                                if (is_array($att_id))
                                    foreach ($att_id as $abc)
                                        $value[] = $abc;
                                else
                                    $value[] = $att_id;
                            }
                        }
                    }

                    if ($field->field_type === "Price")
                    {
                        $paypal_field = new RM_PayPal_Fields();

                        $prices = array();
                        $item_names = array();

                        //foreach ($payment_fields as $pf_name => $pf_value)
                        {
                            $paypal_field->load_from_db((int) $key_parts[2]);

                            switch ($paypal_field->get_type())
                            {
                                case "fixed":
                                case "userdef":
                                    $submission_field_rows[] = array('field_id' => $key_parts[1], 'value' => $value, 'form_id' => $params['form']->form_id);
                                    $submissions_data[$field->field_id] = new stdClass();
                                    $submissions_data[$field->field_id]->label = $field->field_label;
                                    $submissions_data[$field->field_id]->value = $value;
                                    break;

                                case "multisel":
                                    $tmp_v = maybe_unserialize($paypal_field->get_option_price());
                                    $tmp_l = maybe_unserialize($paypal_field->get_option_label());
                                    $gopt = new RM_Options;
                                    $val_arr = array();

                                    if ($value)
                                    {
                                        foreach ($value as $pf_single_val)
                                        {
                                            $index = (int) substr($pf_single_val, 1);
                                            if (!isset($tmp_v[$index]))
                                                continue;
                                            $prices[] = $tmp_v[$index];
                                            $item_names[] = $tmp_l[$index];
                                            $val_arr[] = $tmp_l[$index] . " (" . $gopt->get_formatted_amount($tmp_v[$index]) . ")";
                                        }
                                        $value = $val_arr;
                                    }


                                    $submission_field_rows[] = array('field_id' => $key_parts[1], 'value' => $value, 'form_id' => $params['form']->form_id);
                                    $submissions_data[$field->field_id] = new stdClass();
                                    $submissions_data[$field->field_id]->label = $field->field_label;
                                    $submissions_data[$field->field_id]->value = $value;
                                    break;

                                case "dropdown":
                                    $tmp_v = maybe_unserialize($paypal_field->get_option_price());
                                    $tmp_l = maybe_unserialize($paypal_field->get_option_label());
                                    $gopt = new RM_Options;

                                    if ($value)
                                    {
                                        $index = (int) substr($value, 1);
                                        if (!isset($tmp_v[$index]))
                                            break;
                                        $prices[] = $tmp_v[$index];
                                        $item_names[] = $tmp_l[$index];

                                        $value = $tmp_l[$index] . " (" . $gopt->get_formatted_amount($tmp_v[$index]) . ")";
                                    }

                                    $submission_field_rows[] = array('field_id' => $key_parts[1], 'value' => $value, 'form_id' => $params['form']->form_id);
                                    $submissions_data[$field->field_id] = new stdClass();
                                    $submissions_data[$field->field_id]->label = $field->field_label;
                                    $submissions_data[$field->field_id]->value = $value;
                                    break;
                            }
                        }
                    }

                    else if ($field->get_field_type() !== 'HTMLH' && $field->get_field_type() !== 'HTMLP')
                    {
                        $submission_field_rows[] = array('field_id' => $key_parts[1], 'value' => $value, 'form_id' => $params['form']->form_id);
                        $submissions_data[$field->field_id] = new stdClass();
                        $submissions_data[$field->field_id]->label = $field->field_label;
                        $submissions_data[$field->field_id]->value = $value;
                    }

                    foreach ($profile_field_id as $key => $id)
                    {
                        if ($key_parts[1] === $id)
                        {
                            if ($key == 'email')
                                $email = $value;
                        }
                    }
                }
            }
        }



        /*
         * Register the user if form is registration type (FormType value is 1)
         */
        if ($form_type == 1 && !is_user_logged_in())
        {
            if (isset($params['is_payment_form']))
            {
                if ($params['is_payment_done'])
                {
                    //user already activated in 'ipn' case, do nothing.
                } else
                {
                    //create user but keep deactivated
                    $user_id = $this->register_user($request, $params['form'], $is_auto_generate, false);
                    $this->update_user_profile($user_id, $profile);

                    $reg_data->user_id = $user_id;
                }
            } else
            {
                $user_id = $this->register_user($request, $params['form'], $is_auto_generate);
                $this->update_user_profile($user_id, $profile);
                $reg_data->user_id = $user_id;
            }
        } else
        {

            $this->update_user_profile($email, $profile, true);
        }


        //if ($form_type == 1)

        /*
         * Check if any attachment was with submission only if there is no form error
         */


        $submission_row = array('form_id' => $params['form']->form_id, 'data' => $submissions_data, 'user_email' => $email);

        $submissions = new RM_Submissions;
        $submissions->set($submission_row);
        $submission_id = $submissions->insert_into_db();
        if ($submission_field_rows)
        {
            foreach ($submission_field_rows as $submission_field_row)
            {
                $submission_field_row['submission_id'] = $submission_id;
                $submission_field = new RM_Submission_Fields;
                $submission_field->set($submission_field_row);

                //If submission is already in the table update it. (for PayPal cases.)
                if ($submission_field->insert_into_db() === false)
                    $submission_field->update_into_db();

                unset($submission_field);
            }
        }

        /*
         * Send email notification to admin and other receivers
         */
        $submissions->load_from_db($submission_id);
        $email = $this->prepare_email('to_admin', $submissions, $params['form']);
        RM_Utilities::send_mail($email);
        /*
         * If auto reply option enabled
         */
        if ($params['form']->get_form_should_send_email() == "1")
        {
            $email = $this->prepare_email('to_registrar', $submissions, $params['form'], $request);
            RM_Utilities::send_mail($email);
        }


        /*
         * Redirecting user as per form configuration after submission
         */
        if ($redirect_as_well)
            $this->after_submission_proc($params);
        /* echo $params['form']->form_options->form_success_message!=""?$params['form']->form_options->form_success_message:$params['form']->form_name." Submitted ";


          if(isset($params['form']->form_redirect) && $params['form']->form_redirect!="none" && $params['form']->form_redirect!=""){
          if($params['form']->form_redirect=="page"){
          RM_Utilities::redirect(null,true,$params['form']->get_form_redirect_to_page(),true);
          }else{
          RM_Utilities::redirect($params['form']->get_form_redirect_to_url(),false,0,true);
          }
          }
         */

        //if(isset($request->req['stat_id']))

        $reg_data->submission_id = $submission_id;

        return $reg_data;
    }

    public function after_submission_proc($params)
    {
        echo $params['form']->form_options->form_success_message != "" ? $params['form']->form_options->form_success_message : $params['form']->form_name . " Submitted ";


        if (isset($params['form']->form_redirect) && $params['form']->form_redirect != "none" && $params['form']->form_redirect != "")
        {
            echo "<br>", RM_UI_Strings::get("MSG_REDIRECTING_TO") . "<br>";
            //echo "<br>", var_dump(),die;
            if ($params['form']->form_redirect == "page")
            {
                $page_id = $params['form']->get_form_redirect_to_page();
                $page_title = get_post($page_id)->post_title? : '#' . $page_id . ' (No title)';
                echo $page_title;
                RM_Utilities::redirect(null, true, $page_id, true);
            } else
            {
                $url = $params['form']->get_form_redirect_to_url();
                RM_Utilities::redirect($url, false, 0, true);
            }
        }
    }

    public function send_user($email, $username, $password, $content)
    {
        $send_details = parent::get_setting('send_password');

        //echo $content;
        if ($send_details == "yes")
        {
            RM_Utilities::send_email($email, $content);
        }
    }

    public function register_user($request, $form, $is_auto_generate, $is_paid = true)
    {
        $gopt = new RM_Options();
        $username = $request->req['username'];

        if ($is_auto_generate != "yes")
            $password = $request->req['password'];
        else
            $password = wp_generate_password(8, false, false);

        $primary_emails = $this->get_primary_email_fields($form->form_id);

        $request_keys = array_keys($request->req);
        $emails = array_intersect($request_keys, $primary_emails);

        foreach ($emails as $email)
        {
            $email_field_name = $email;
            break;
        }

        $email = $request->req[$email_field_name];

        $user_id = wp_create_user($username, $password, $email);

        if (is_wp_error($user_id))
        {
            foreach ($user_id as $err)
            {
                foreach ($err as $error)
                {
                    echo $error[0];
                    die;
                }
            }
        } else
        {
            /*
             * User created. Check if details are to send via an email
             */
            $required_params = new stdClass();
            $required_params->email = $email;
            $required_params->username = $username;
            $required_params->password = $password;

            if ($this->get_setting('send_password') === 'yes' || $this->get_setting('auto_generated_password') === 'yes')
            {
                $email_obj = $this->prepare_email('new_user', null, $form, $required_params);
                RM_Utilities::send_mail($email_obj);
            }

            /*
             * Deactivate the user in case auto approval is off
             */


            if (!$is_paid || $gopt->get_value_of('user_auto_approval') != "yes")
            {
                $this->user_service->deactivate_user_by_id($user_id);
                $link = $this->user_service->create_user_activation_link($user_id);
                $required_params->link = $link;
                $email_obj = $this->prepare_email('user_activation', null, $required_params);
                RM_Utilities::send_mail($email_obj);
            }

            /*
             * If role is chosen by registrar
             */
            if (isset($request->req['role_as']) && !empty($request->req['role_as']))
            {
                $this->user_service->set_user_role($user_id, $request->req['role_as']);
            } else
            {
                $tmp = $form->get_default_form_user_role();
                if (!empty($tmp))
                {
                    /*
                     * Assign user role if configured by default
                     */
                    $this->user_service->set_user_role($user_id, $form->get_default_form_user_role());
                }
            }
        }

        return $user_id;
    }

    public function get_primary_email_fields($form_id)
    {
        $primary_fields = RM_DBManager::get_primary_fields_by_type($form_id, 'Email');
        // print_r($primary_fields); die;
        if (is_array($primary_fields['emails']))
            $email_fields = $primary_fields['emails'];
        else
            $email_fields = array();

        return $email_fields;
    }

    public function process_payment($form_id, $reg_data, $service, $request)
    {

        //echo "<pre>"; var_dump($_GET); die;

        $payment_fields = array();

        foreach ($request->req as $field_name => $field_value)
            if (substr($field_name, 0, 5) === 'Price')
                $payment_fields[$field_name] = $field_value;
        //echo "<br>id= ".explode("_", $field_name)[2];
        //var_dump($payment_fields);
        //die;

        $sandbox = parent::get_setting('paypal_test_mode');
        $paypal_email = parent::get_setting('paypal_email');
        $currency = parent::get_setting('currency');
        $paypal_page_style = parent::get_setting('paypal_page_style');

        require_once plugin_dir_path(plugin_dir_path(__FILE__)) . 'external/PayPal/paypal.php';

        $p = new paypal_class(); // paypal class

        if ($sandbox == 'yes')
            $p->toggle_sandbox(true);
        else
            $p->toggle_sandbox(false);

        $p->admin_mail = get_option('admin_email'); // set notification email

        if (isset($request->req['rm_pproc']))
        {
            switch ($request->req['rm_pproc'])
            {
                case 'success':
                    if (isset($request->req['rm_pproc_id']))
                    {
                        $log_id = $request->req['rm_pproc_id'];
                        $log = RM_DBManager::get_row('PAYPAL_LOGS', $log_id);
                        if ($log)
                        {
                            if ($log->log)
                            {
                                $paypal_log = maybe_unserialize($log->log);
                                $payment_status = $paypal_log['payment_status'];

                                if ($payment_status == 'Completed')
                                {
                                    echo '<div id="rmform">';
                                    echo "<div class='rminfotextfront'>" . RM_UI_Strings::get("MSG_PAYMENT_SUCCESS") . "</br>";
                                    echo '</div></div>';
                                    return 'success';
                                } else if ($payment_status == 'Denied' || $payment_status == 'Failed' || $payment_status == 'Refunded' || $payment_status == 'Reversed' || $payment_status == 'Voided')
                                {
                                    echo '<div id="rmform">';
                                    echo "<div class='rminfotextfront'>" . RM_UI_Strings::get("MSG_PAYMENT_FAILED") . "</br>";
                                    echo '</div></div>';
                                    return 'failed';
                                } else if ($payment_status == 'In-Progress' || $payment_status == 'Pending' || $payment_status == 'Processed')
                                {
                                    echo '<div id="rmform">';
                                    echo "<div class='rminfotextfront'>" . RM_UI_Strings::get("MSG_PAYMENT_PENDING") . "</br>";
                                    echo '</div></div>';
                                    return 'pending';
                                } else if ($payment_status == 'Canceled_Reversal')
                                {
                                    return 'canceled_reversal';
                                }
                            }
                        }
                    }
                    return false;

                case 'cancel':
                    echo '<div id="rmform">';
                    echo "<div class='rminfotextfront'>" . RM_UI_Strings::get("MSG_PAYMENT_CANCEL") . "</br>";
                    echo '</div></div>';
                    return;

                case 'ipn':
                    $trasaction_id = $_POST["txn_id"];
                    $payment_status = $_POST["payment_status"];
                    $cstm = $_POST["custom"];
                    $abcd = explode("|", $cstm);
                    $user_id = (int) ($abcd[1]);
                    $acbd = explode("|", $cstm);
                    $log_entry_id = (int) ($acbd[0]); //$_POST["custom"];
                    $log_array = maybe_serialize($_POST);

                    $curr_date = RM_Utilities::get_current_time(); // date_i18n(get_option('date_format'));

                    RM_DBManager::update_row('PAYPAL_LOGS', $log_entry_id, array(
                        'status' => $payment_status,
                        'txn_id' => $trasaction_id,
                        'posted_date' => $curr_date,
                        'log' => $log_array), array('%s', '%s', '%s', '%s'));

                    if ($p->validate_ipn())
                    {
                        //IPN is valid, check payment status and process logic
                        if ($payment_status == 'Completed')
                        {
                            if ($user_id)
                            {
                                $gopt = new RM_Options;
                                $this->user_service->activate_user_by_id($user_id);
                            }
                            return 'success';
                        } else if ($payment_status == 'Denied' || $payment_status == 'Failed' || $payment_status == 'Refunded' || $payment_status == 'Reversed' || $payment_status == 'Voided')
                        {
                            return 'failed';
                        } else if ($payment_status == 'In-Progress' || $payment_status == 'Pending' || $payment_status == 'Processed')
                        {
                            return 'pending';
                        } else if ($payment_status == 'Canceled_Reversal')
                        {
                            return 'canceled_reversal';
                        }

                        //Send mail notifications about payment success.
                        /* $recipients = parent::get_setting('admin_email');

                          if ($recipients)
                          {
                          $recipients = explode(',', $recipients);

                          foreach ($recipients as $recipient)
                          {
                          $p->send_report($recipient);
                          }
                          } */

                        return 'unknown';
                    }

                    return 'invalid_ipn';
            }
            //return;
        }

        $paypal_field = new RM_PayPal_Fields();

        $prices = array();
        $item_names = array();

        foreach ($payment_fields as $pf_name => $pf_value)
        {
            $abe = explode("_", $pf_name);
            $paypal_field->load_from_db((int) $abe[2]);

            switch ($paypal_field->get_type())
            {
                case "fixed":
                    $prices[] = $paypal_field->get_value();
                    $item_names[] = $paypal_field->get_name();
                    break;

                case "userdef":
                    if ($pf_value == "")
                        break;
                    $prices[] = $pf_value;
                    $item_names[] = $paypal_field->get_name();
                    break;

                case "multisel":
                    $tmp_v = maybe_unserialize($paypal_field->get_option_price());
                    $tmp_l = maybe_unserialize($paypal_field->get_option_label());

                    foreach ($pf_value as $pf_single_val)
                    {
                        $index = (int) substr($pf_single_val, 1);
                        if (!isset($tmp_v[$index]))
                            continue;
                        $prices[] = $tmp_v[$index];
                        $item_names[] = $tmp_l[$index];
                    }
                    break;

                case "dropdown":
                    $tmp_v = maybe_unserialize($paypal_field->get_option_price());
                    $tmp_l = maybe_unserialize($paypal_field->get_option_label());

                    //Check whether dropdown was not submitted
                    if (!$pf_value)
                        break;

                    $index = (int) substr($pf_value, 1);
                    if (!isset($tmp_v[$index]))
                        break;
                    $prices[] = $tmp_v[$index];
                    $item_names[] = $tmp_l[$index];


                    break;
            }
        }
        /*
          echo "<br><br>========  names =============<br><br>";
          var_dump($item_names);
          echo "<br><br>========  prices =============<br><br>";
          var_dump($prices);
          die;
         */

        $this_script = get_permalink();
        $sign = strpos($this_script, '?') ? '&' : '?';

        $i = 1;
        foreach ($item_names as $item_name)
        {
            $p->add_field('item_name_' . $i, $item_name);
            $i++;
        }

        $i = 1;
        $total_amount = 0.0;
        foreach ($prices as $price)
        {
            $p->add_field('amount_' . $i, $price);
            $total_amount += floatval($price);
            $i++;
        }

        $invoice = (string) date("His") . rand(1234, 9632);

        $p->add_field('business', $paypal_email); // Call the facilitator eaccount
        $p->add_field('cmd', '_cart'); // cmd should be _cart for cart checkout
        $p->add_field('upload', '1');
        $p->add_field('return', $this_script . $sign . 'rm_pproc=success&rm_pproc_id='); // return URL after the transaction got over
        $p->add_field('cancel_return', $this_script . $sign . 'rm_pproc=cancel'); // cancel URL if the trasaction was cancelled during half of the transaction
        $p->add_field('notify_url', $this_script . $sign . 'rm_pproc=ipn'); // Notify URL which received IPN (Instant Payment Notification)
        $p->add_field('currency_code', $currency);
        $p->add_field('invoice', $invoice);

        $p->add_field('page_style', $paypal_page_style);

        //Insert into PayPal log table

        $curr_date = RM_Utilities::get_current_time(); //date_i18n(get_option('date_format'));

        if ($total_amount <= 0.0)
        {
            $log_entry_id = RM_DBManager::insert_row('PAYPAL_LOGS', array('submission_id' => $reg_data->submission_id,
                        'form_id' => $form_id,
                        'invoice' => $invoice,
                        'status' => 'Completed',
                        'total_amount' => $total_amount,
                        'currency' => $currency,
                        'posted_date' => $curr_date), array('%d', '%d', '%s', '%s', '%f', '%s', '%s'));

            return 'zero_amount';
        } else
        {
            $log_entry_id = RM_DBManager::insert_row('PAYPAL_LOGS', array('submission_id' => $reg_data->submission_id,
                        'form_id' => $form_id,
                        'invoice' => $invoice,
                        'status' => 'Pending',
                        'total_amount' => $total_amount,
                        'currency' => $currency,
                        'posted_date' => $curr_date), array('%d', '%d', '%s', '%s', '%f', '%s', '%s'));
        }

        $p->add_field('custom', $log_entry_id . "|" . $reg_data->user_id);
        $p->add_field('return', $this_script . $sign . 'rm_pproc=success&rm_pproc_id=' . $log_entry_id); // return URL after the transaction got over
        $p->add_field('cancel_return', $this_script . $sign . 'rm_pproc=cancel&rm_pproc_id=' . $log_entry_id); // cancel URL if the trasaction was cancelled during half of the transaction
        $p->add_field('notify_url', $this_script . $sign . 'rm_pproc=ipn'); // Notify URL which received IPN (Instant Payment Notification)
        $p->submit_paypal_post(); // POST it to paypal
        //$p->dump_fields();
    }

    public function user_exists($form, $request)
    {
        $valid = false;
        $primary_emails = $this->get_primary_email_fields($form->form_id);


        $form_type = $form->form_type;
        if ($form_type == 1 && isset($request->req['username']))
        {
            $username = $request->req['username'];
            $email_field_name = '';

            $user = get_user_by('login', $username);
            if (!empty($user))
            {
                //Form::setError('form_' . $form->form_id,RM_UI_Strings::get("USERNAME_EXISTS"));
                $valid = true;
            }

            $request_keys = array_keys($request->req);
            $emails = array_intersect($request_keys, $primary_emails);

            foreach ($emails as $e)
            {
                $email_field_name = $e;
            }

            if (isset($request->req[$email_field_name]))
            {
                $email = $request->req[$email_field_name];
                $user = get_user_by('email', $email);
                if (!empty($user))
                {
                    //  Form::setError('form_' . $form->form_id,RM_UI_Strings::get("USEREMAIL_EXISTS"));
                    $valid = true;
                }
            }
        }

        return $valid;
    }

    public function update_user_profile($user_id_or_email, array $profile, $is_email = false)
    {

        $return = true;

        if ((int) $user_id_or_email)
        {
            $user_id = $user_id_or_email;
        } elseif (is_email($user_id_or_email))
        {
            if ($is_email)
            {
                $user = get_user_by('email', $user_id_or_email);
                if (!isset($user->ID))
                    return false;
                if ((int) $user->ID)
                    $user_id = $user->ID;
                else
                    return false;
            } else
                return false;
        } else
            return false;

        foreach ($profile as $type => $pr)
        {
            switch ($type)
            {
                case 'Fname' :
                    $return = update_user_meta($user_id, 'first_name', $pr);
                    break;
                case 'Lname' :
                    $return = update_user_meta($user_id, 'last_name', $pr);
                    break;
                case 'BInfo' :
                    $return = update_user_meta($user_id, 'description', $pr);
                    break;
            }
        }

        return $return;
    }

    public function prepare_email($type, $submissions, $form, $request = '')
    {
        $email = new stdClass();
        $email_content = '<div class="mail-wrapper">';
        if ($submissions != null)
            $data = $submissions->get_data();
        $gopt = new RM_Options();
        $values = '';

        if ($type == "to_admin")
        {
            /*
             * Loop through serialized data for submission
             */
            foreach ($data as $val)
            {
                $email_content .= '<div class="row"> <span class="key">' . $val->label . ':</span>';

                if (is_array($val->value))
                {
                    $values = '';
                    // Check attachment type field
                    if (isset($val->value['rm_field_type']) && $val->value['rm_field_type'] == 'File')
                    {
                        unset($val->value['rm_field_type']);

                        /*
                         * Grab all the attachments as links
                         */
                        foreach ($val->value as $attachment_id)
                        {
                            $values .= wp_get_attachment_link($attachment_id) . '    ';
                        }

                        $email_content .= '<span class="key-val">' . $values . '</span><br/>';
                    } else
                    {
                        $email_content .= '<span class="key-val">' . implode(', ', $val->value) . '</span><br/>';
                    }
                } else
                {
                    $email_content .= '<span class="key-val">' . $val->value . '</span><br/>';
                }
            }


            $email->message = $email_content . "</div>";
            // Prepare recipients

            $to = array();
            $header = '';
            if ($gopt->get_value_of('admin_notification') == "yes")
            {
                $to = explode(',', $gopt->get_value_of('admin_email'));
            } else
                $to = null;

            $subject = $form->form_name . " " . RM_UI_Strings::get('LABEL_NEWFORM_NOTIFICATION') . " ";
            $from_email = $gopt->get_value_of('senders_email_formatted');
            $header = "From: $from_email\r\n";

            $header.= "Content-Type: text/html; charset=ISO-8859-1\r\n";


            $email->to = $to;
            $email->header = $header;
            $email->subject = $subject;
            $email->attachments = array();
        }

        if ($type == "to_registrar")
        {
            /* Preparing content for front end notification */
            $email_content .= $form->form_options->form_email_content . '<br><br>';


            foreach ($request->req as $key => $val)
            {
                //echo "<pre", var_dump($request->req),die;
                if (!is_array($val))
                    $email_content = str_replace('{{' . $key . '}}', $val, $email_content);
                else
                {
                    $email_content = str_replace('{{' . $key . '}}', implode(',', $val), $email_content);
                }
            }

            $out = array();
            $preg_result = preg_match_all('/{{(.*?)}}/', $email_content, $out);

            if ($preg_result)
            {
                $id_vals = array();

                foreach ($request->req as $key => $val)
                {
                    //$val would be like '{field_type}_{field_id}'

                    $key_parts = explode('_', $key);
                    $k_c = count($key_parts);
                    if ($k_c >= 2 && is_numeric($key_parts[$k_c - 1]))
                    {
                        if (is_array($val))
                            $val = implode(",", $val);

                        if ($key_parts[0] === 'Fname' || $key_parts[0] === 'Lname' || $key_parts[0] === 'BInfo')
                        {
                            $id_vals[$key_parts[0]] = $val;
                        } else
                            $id_vals[$key_parts[1]] = $val;
                    }
                }

                foreach ($out[1] as $caught)
                {
                    //echo "<br>".$caught;
                    $x = explode("_", $caught);
                    $id = $x[count($x) - 1];
                    if (is_numeric($id))
                    {
                        if (isset($id_vals[(int) $id]))
                            $email_content = str_replace('{{' . $caught . '}}', $id_vals[(int) $id], $email_content);
                    }
                    else
                    {
                        switch ($caught)
                        {
                            case 'first_name':
                                if (isset($id_vals['Fname']))
                                    $email_content = str_replace('{{' . $caught . '}}', $id_vals['Fname'], $email_content);
                                break;

                            case 'last_name':
                                if (isset($id_vals['Lname']))
                                    $email_content = str_replace('{{' . $caught . '}}', $id_vals['Lname'], $email_content);
                                break;

                            case 'description':
                                if (isset($id_vals['BInfo']))
                                    $email_content = str_replace('{{' . $caught . '}}', $id_vals['BInfo'], $email_content);
                                break;
                        }
                    }

                    //Blank the placeholder if still any remaining.
                    $email_content = str_replace('{{' . $caught . '}}', '', $email_content);
                }
            }

            $email->message = $email_content . "</div>";
            $email->subject = $form->form_options->form_email_subject? : RM_UI_Strings::get('MAIL_REGISTRAR_DEF_SUB');
            $email->to = $submissions->get_user_email();
            $email->attachments = array();
            $from_email = $gopt->get_value_of('senders_email_formatted');
            $header = "From: $from_email\r\n";

            $header.= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            $email->header = $header;
        }


        if ($type == "new_user")
        {

            //$email->message = "Your account has been successfully created on ".get_bloginfo( 'name', 'display' ).". You can now login using following credentials:<br>Username : $request->username<br>Password : $request->password";
            $msg = RM_UI_Strings::get('MAIL_BODY_NEW_USER_NOTIF');
            $msg = str_replace('%SITE_NAME%', get_bloginfo('name', 'display'), $msg);
            $msg = str_replace('%USER_NAME%', $request->username, $msg);
            $msg = str_replace('%USER_PASS%', $request->password, $msg);

            $email->message = $email_content . $msg . "</div>";
            $email->subject = RM_UI_Strings::get('MAIL_NEW_USER_DEF_SUB');
            $email->to = $request->email;
            $email->attachments = array();
            $from_email = $gopt->get_value_of('senders_email_formatted');
            $header = "From: $from_email\r\n";

            $header.= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            $email->header = $header;
        } elseif ($type === 'user_activation')
        {

            $user_email = $params->email;

            /* $boundary = uniqid('rm');

              $header_html = "Content-type: text/html;charset=utf-8\r\n\r\n";
              $header_text = "Content-type: text/plain;charset=utf-8\r\n\r\n"; */

            /* $msg_text = 'A new user has been regitered on %SITE_NAME%. \r\n User Name : %USER_NAME% \r\n User Email : %USER_EMAIL% \r\n\r\n Please click on the link below to activate the user.';

              $msg_text = str_replace('%SITE_NAME%', get_bloginfo('name', 'display'), $msg_text);
              $msg_text = str_replace('%USER_NAME%', $params->username, $msg_text);
              $msg_text = str_replace('%USER_EMAIL%', $user_email, $msg_text); */
            //$msg_css = '<style type=text/css> .mail-wrapper{ border: 1px solid black; padding: 20px; background-color: #fdfdfd; box-shadow: .1px .1px 8px .1px grey; font-size: 14px; font-family: monospace; } a.rm_btn{ border: 1px solid; padding: 4px; background-color: powderblue; box-shadow: 1px 1px 3px .1px; } a.rm_btn:hover{ box-shadow: 1px 1px 3px .1px inset; } a.rm-link{ color: blue; font-size: 11px; } div.rm-btn-link{ width: 100%; text-align: center; margin-top: 10px; margin-bottom: 15px; } div.link-div{ border: 1px dotted; padding: 13px; background-color: ivory; margin-top: 4px; width: 100%; } div.mail_body{ background-color: floralwhite; padding: 20px; } </style>';

            $html_pre = '<!DOCTYPE html>
                                <html>
                                <head>
                                  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                                  <meta http-equiv="Content-Style-Type" content="text/css">
                                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                                  <title></title>
                                  <meta name="Generator" content="Cocoa HTML Writer">
                                  <meta name="CocoaVersion" content="1404.34">
                                    <link rel="stylesheet" type="text/css" href="matchmytheme.css">
                                </head>
                                <body style="font-size:14px">';
            $html_post = '</body></html>';
            $msg_html = '<div class="mail-wrapper" style="border: 1px solid black; padding: 20px; box-shadow: .1px .1px 8px .1px grey; font-size: 14px; font-family: monospace;"> <div class="mail_body" style="padding: 20px;">' . RM_UI_Strings::get('MAIL_NEW_USER1') . '.<br/> ' . RM_UI_Strings::get('LABEL_USER_NAME') . ' : %USER_NAME% <br/> ' . RM_UI_Strings::get('LABEL_USEREMAIL') . ' : %USER_EMAIL% <br/> <br/>' . RM_UI_Strings::get('MAIL_NEW_USER2') . '<br/> <div class="rm-btn-link" style="width: 100%; text-align: center; margin-top: 10px; margin-bottom: 15px;"><a class="rm_btn" href="%ACTIVATION_LINk%" style="border: 1px solid; padding: 4px; background-color: powderblue; box-shadow: 1px 1px 3px .1px;">Activate</a></div> <div class="link-div" style="border: 1px dotted; padding: 13px; background-color: white; margin-top: 4px; width: 100%;"> ' . RM_UI_Strings::get('MAIL_NEW_USER3') . '.<br/> <a class="rm-link" href="%ACTIVATION_LINk%" style="color: blue; font-size: 11px;">%ACTIVATION_LINk%</a> </div> </div> </div>';


            $msg_html = str_replace('%SITE_NAME%', get_bloginfo('name', 'display'), $msg_html);
            $msg_html = str_replace('%USER_NAME%', $params->username, $msg_html);
            $msg_html = str_replace('%USER_EMAIL%', $user_email, $msg_html);
            $msg_html = str_replace('%ACTIVATION_LINk%', $params->link, $msg_html);

            //$email->message = "msg \r\n\r\n--" . $boundary . "\r\n" . $header_text . $msg_text . "\r\n\r\n--" . $boundary . "\r\n" . $header_html . $html_pre .$msg_css . $msg_html . $html_post . "\r\n\r\n--" . $boundary . "--\r\n";
            $email->message = $html_pre . $msg_html . $html_post;

            $email->subject = RM_UI_Strings::get('MAIL_ACTIVATE_USER_DEF_SUB');
            $email->to = get_option('admin_email');
            $email->attachments = array();
            $from_email = $gopt->get_value_of('senders_email_formatted');

            $header = "From: $from_email\r\n";
            $header.= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            $email->header = $header;
        }


        return $email;
    }

    public function set_properties(stdClass $options)
    {
        $properties = array();

        if (null != $options->field_placeholder)
            $properties['placeholder'] = $options->field_placeholder;
        if (null != $options->field_css_class)
            $properties['class'] = $options->field_css_class;
        if (null != $options->field_max_length)
            $properties['maxlength'] = $options->field_max_length;
        if (null != $options->field_textarea_columns)
            $properties['cols'] = $options->field_textarea_columns;
        if (null != $options->field_textarea_rows)
            $properties['rows'] = $options->field_textarea_rows;
        if (null != $options->field_is_required)
            $properties['required'] = $options->field_is_required;
        if (null != $options->field_default_value)
            $properties['value'] = maybe_unserialize($options->field_default_value);
        if (isset($options->field_is_other_option) && null != $options->field_is_other_option)
            $properties['rm_is_other_option'] = $options->field_is_other_option;

        return $properties;
    }

}
