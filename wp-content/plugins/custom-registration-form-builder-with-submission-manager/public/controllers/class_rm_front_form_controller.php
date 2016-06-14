<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class_rm_front_form_controller
 *
 * @author CMSHelplive
 */
class RM_Front_Form_Controller
{

    private $mv_handler;

    public function __construct()
    {
        $this->mv_handler = new RM_Model_View_Handler();
    }

    public function process($model, $service, $request, $params)
    {
        $data = new stdClass();
        
        if (isset($params['form_id']) && $params['form_id'])
        {
            $form = new RM_Forms;
            $form->load_from_db($params['form_id']);
            //echo "<pre>",var_dump($form),die();
        }
        else
            return;


        //Called from PayPal, straight to processing.
        if (isset($request->req['rm_pproc']))
        {
            if ($request->req['rm_pproc'] == 'success' ||
                    $request->req['rm_pproc'] == 'cancel' ||
                    $request->req['rm_pproc'] == 'ipn')
            {
                //error_log('rm_pproc = '.$request->req['rm_pproc']);
                $paystate = $service->process_payment(null, null, $service, $request);
                
                //if ($paystate == 'success')
                {
                    $params['form'] = $form;
                    $params['is_payment_form'] = true;
                    $params['is_payment_done'] = true;
                }
                /*else //Pending or cancelation cases
                {
                    $params['form'] = $form;
                    $params['is_payment_form'] = true;
                    $params['is_payment_done'] = false;                    
                }*/
                
                $service->save_form($request, $params);
                return;
            }
        }


        /*
         * If register form type then check if user exists
         */
        $user_exists = false;
        if ($form->form_type == 1 && !is_user_logged_in())
        {
            if ($service->user_exists($form, $request))
            {
                $user_exists = true;
            } else
            {
                Form::clearErrors('form_' . $form->form_id);
            }
        }

        if (isset($request->req['stat_id']))
            $stat_id = $request->req['stat_id'];
        else
            $stat_id = null;
        /*
         * Validates the form in case form is not expired.
         */
        if ($this->mv_handler->validateForm('form_' . $form->form_id) && !$service->is_form_expired($form) && !$user_exists && !$service->is_browser_reload_duplication($stat_id))
        {
            $params['form'] = $form;
            // echo "<pre>",var_dump($form),die();
            if ($service->is_off_limit_submission($form->form_id))
                die(RM_UI_Strings::get("ALERT_SUBMISSIOM_LIMIT"));

            $service->update_stat_entry($stat_id);

            if ($service->get_setting('enable_mailchimp') == 'yes')
            {
                $form_options_mc = $form->get_form_options(); //die;

                if ($form_options_mc->form_is_opt_in_checkbox == 1)
                    $should_subscribe = isset($request->req['rm_subscribe_mc']) && $request->req['rm_subscribe_mc'][0] == 1 ? 'yes' : 'no';
                else
                    $should_subscribe = 'yes';

                if ($should_subscribe == 'yes'):
                    $mailchimp = new RM_MailChimp_Service;
                    $mc_member = new stdClass;

                    if (isset($request->req[$form_options_mc->mailchimp_mapped_email]))
                    {
                        $mc_member->email = $request->req[$form_options_mc->mailchimp_mapped_email];

                        if (isset($request->req[$form_options_mc->mailchimp_mapped_first_name]))
                            $mc_member->first_name = $request->req[$form_options_mc->mailchimp_mapped_first_name];
                        else
                            $mc_member->first_name = NULL;

                        if (isset($request->req[$form_options_mc->mailchimp_mapped_last_name]))
                            $mc_member->last_name = $request->req[$form_options_mc->mailchimp_mapped_last_name];
                        else
                            $mc_member->last_name = NULL;

                        $mailchimp->subscribe($mc_member, $form_options_mc->mailchimp_list);
                    }
                endif;
            }


            if (isset($request->req['rm_payment_form']))
            {
                //Do not register user if the payment+registration type form, wait for payment confirmation.
                $params['is_payment_form'] = true;
                $params['is_payment_done'] = false;

                $rd = $service->save_form($request, $params, true, false);
                //die("xx");
                $res = $service->process_payment($form->get_form_id(), $rd, $service, $request);

                if ($res == 'zero_amount')
                {
                    $params['is_payment_form'] = true;
                    $params['is_payment_done'] = true;
                    $params['za_user_id'] = $rd->user_id;
                    $rd = $service->save_form($request, $params);
                    //$submission_id = $rd->submission_id;
                }
            } else
            {
                $rd = $service->save_form($request, $params);
                $submission_id = $rd->submission_id;
            }
        } else
        {

            if (isset($request->req['rm_submission_id']))
            {
                $submissions = new RM_Submissions;
                $submissions->load_from_db($request->req['rm_submission_id']);
            }



            /*
             * Get all the fields associated with the Form
             */

            $data->fields_data = $service->get_all_form_fields($params['form_id']);
            $i = 0;
            if (is_array($data->fields_data) || is_object($data->fields_data))
                foreach ($data->fields_data as $field_data)
                    $data->fields_data[$i++]->properties = $service->set_properties(maybe_unserialize($field_data->field_options));
            $data->form = $form;
            $data->is_auto_generate = false;

            /*
             * Checking if password generation is configured
             */
            if ($form->get_form_type() == 1)
            {
                $auto_generate = $service->get_setting('auto_generated_password');
                if ($auto_generate == "yes")
                    $data->is_auto_generate = true;
            }


            /*
             * Check if mailchimp is enabled in options
             */
            if ($service->get_setting('enable_mailchimp') == 'yes')
                $data->is_mailchimp_enabled = true;
            else
                $data->is_mailchimp_enabled = false;

            /*
             * If user role are to be choosen by registrar
             */
            /*           echo '<pre>';
              print_r($form); */

            if (!empty($form->form_options->form_should_user_pick) || !(isset($form->form_user_role) && !empty($form->form_user_role)))
            {
                $role_pick = $form->form_options->form_should_user_pick;

                if ($role_pick)
                {
                    global $wp_roles;
                    $allowed_roles = array();
                    $default_wp_roles = $wp_roles->get_names();
                    $form_roles = $form->get_form_user_role();
                    if (is_array($form_roles) && count($form_roles) > 0)
                    {
                        foreach ($form_roles as $val)
                        {
                            if (array_key_exists($val, $default_wp_roles))
                                $allowed_roles[$val] = $default_wp_roles[$val];
                        }
                    }
                    $data->allowed_roles = $allowed_roles;
                    $data->role_as = empty($form->form_options->form_user_field_label) ? RM_UI_Strings::get('LABEL_ROLE_AS') : $form->form_options->form_user_field_label;
                }
            }


            /*
             * Checking if form is expired
             */

            $data->expired = $service->is_form_expired($form);
            $data->user_exists = $user_exists;

            if (isset($submissions))
                $data->submissions = $submissions;
            $data->curr_symbol = $service->get_setting('currency_symbol');
            $data->currency_pos = $service->get_setting('currency_symbol_position');

            $data->stat_id = $service->create_stat_entry($params);

            if ($service->get_setting('theme') == 'matchmytheme')
            {
                $data->submit_btn_fgcolor = $form->form_options->form_submit_btn_color;
                $data->submit_btn_bgcolor = $form->form_options->form_submit_btn_bck_color;
            } else
            {
                $data->submit_btn_fgcolor = null;
                $data->submit_btn_bgcolor = null;
            }

            $data->expiry_details = $service->get_form_expiry_stats($form);
            
            $view = $this->mv_handler->setView("user_form", true);
            return $view->read($data);
        }
    }

}
