<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class_rm_front_controller
 *
 * @author CMSHelplive
 */
class RM_Front_Controller
{

    private $mv_handler;

    public function __construct()
    {
        $this->mv_handler = new RM_Model_View_Handler;
    }

    public function set_otp($model, $service, $request, $params)
    {
        $key = false;
        //var_dump($request->req);
        if (isset($request->req['rm_otp_email']))
            $email = $request->req['rm_otp_email'];

        if (isset($request->req['rm_otp_key']))
            $key = $request->req['rm_otp_key'];

        // Validate request parameters
        if (!isset($request->req['security_key']))
            echo $service->set_otp($email, $key);

        exit;
    }

    public function submissions($model, RM_Front_Service $service, $request, $params)
    {

        $i = 0;
        $user_email = $service->get_user_email();
        //var_dump($user_email);die;
        if (null != $user_email && is_email($user_email))
        {

            if (isset($request->req['submission_id']))
            {
                $submission = new RM_Submissions();
                $submission->load_from_db($request->req['submission_id']);

                if ($submission->get_user_email() == $user_email)
                {
                    $view = $this->mv_handler->setView('front_submission_data', true);

                    $data = new stdClass;

                    $settings = new RM_Options;

                    $data->is_authorized = true;
                    $data->submission = $submission;

                    $data->payment = $service->get('PAYPAL_LOGS', array('submission_id' => $submission->get_submission_id()), array('%d'), 'row', 0, 99999);

                    if ($data->payment != null)
                    {
                        $data->payment->total_amount = $settings->get_formatted_amount($data->payment->total_amount, $data->payment->currency);

                        if ($data->payment->log)
                            $data->payment->log = maybe_unserialize($data->payment->log);
                    }

                    $data->notes = $service->get('NOTES', array('submission_id' => $submission->get_submission_id(), 'status' => 'publish'), array('%d', '%s'), 'results', 0, 99999, '*', null, true);
                    $i = 0;
                    if (is_array($data->notes))
                        foreach ($data->notes as $note)
                        {
                            $data->notes[$i]->author = get_userdata($note->published_by)->display_name;
                            if ($note->last_edited_by)
                                $data->notes[$i++]->editor = get_userdata($note->last_edited_by)->display_name;
                            else
                                $data->notes[$i++]->editor = null;
                        }
                    /*
                     * Check submission type
                     */
                    $form = new RM_Forms();
                    $form->load_from_db($submission->get_form_id());
                    $form_type = $form->get_form_type() == "1" ? "Registration" : "Contact";
                    $data->form_type = $form_type;
                    $data->form_type_status = $form->get_form_type();
                    $data->form_name = $form->get_form_name();
                    $data->form_is_unique_token = $form->get_form_is_unique_token();

                    /*
                     * User details if form is registration type
                     */
                    if ($form->get_form_type() == "1")
                    {
                        $email = $submission->get_user_email();
                        if ($email != "")
                        {
                            $user = get_user_by('email', $email);
                            $data->user = $user;
                        }
                    }
                    return $view->read($data);
                } else
                    $view = $this->mv_handler->setView('not_authorized', true);
                $msg = RM_UI_Strings::get('MSG_INVALID_SUBMISSION_ID_FOR_EMAIL');
                return $view->read($msg);
            } else
            {
                $data = new stdClass;
                $data->is_authorized = true;
                $data->submissions = array();
                $data->form_names = array();
                $data->submission_exists = false;


                //data for user page
                $user = get_user_by('email', $user_email);
                if ($user instanceof WP_User)
                {
                    $data->is_user = true;
                    $data->user = $user;
                    $data->custom_fields = $service->get_custom_fields($user_email);
                } else
                {
                    $data->is_user = false;
                }

                //For pagination of submissions
                $entries_per_page_sub = 20;
                $req_page_sub = (isset($request->req['rm_reqpage_sub']) && $request->req['rm_reqpage_sub'] > 0) ? $request->req['rm_reqpage_sub'] : 1;
                $offset_sub = ($req_page_sub - 1) * $entries_per_page_sub;
                $total_entries_sub = $service->get_submission_count($user_email);

                $submissions = $service->get_submissions_by_email($user_email, $entries_per_page_sub, $offset_sub);
                $submission_ids = array();
                if ($submissions)
                {
                    $data->submission_exists = true;
                    foreach ($submissions as $submission)
                    {
                        $form_name = $service->get('FORMS', array('form_id' => $submission->form_id), array('%d'), 'var', 0, 1, 'form_name');

                        $data->submissions[$i] = new stdClass();
                        $data->submissions[$i]->submission_ids = array();
                        $data->submissions[$i]->submission_id = $submission->submission_id;
                        $submission_ids[$i] = $submission->submission_id;
                        $data->submissions[$i]->submitted_on = $submission->submitted_on;
                        $data->submissions[$i]->form_name = $form_name;
                        $data->form_names[$submission->submission_id] = $form_name;
                        $i++;
                    }

                    $settings = new RM_Options;
                    $data->date_format = get_option('date_format');
                    $data->payments = $service->get_payments_by_submission_id($submission_ids, 999999, 0, null, true);
                    $i = 0;
                    if ($data->payments)
                        foreach ($data->payments as $p)
                        {
                            $data->payments[$i]->total_amount = $settings->get_formatted_amount($data->payments[$i]->total_amount, $data->payments[$i]->currency);
                            $i++;
                        }

                    //For pagination of payments
                    $entries_per_page_pay = 20;
                    $req_page_pay = (isset($request->req['rm_reqpage_pay']) && $request->req['rm_reqpage_pay'] > 0) ? $request->req['rm_reqpage_pay'] : 1;
                    $data->offset_pay = $offset_pay = ($req_page_pay - 1) * $entries_per_page_pay;
                    $total_entries_pay = $i;
                    $data->total_pages_pay = (int) ($total_entries_pay / $entries_per_page_pay) + (($total_entries_pay % $entries_per_page_pay) == 0 ? 0 : 1);
                    $data->curr_page_pay = $req_page_pay;
                    $data->starting_serial_number_pay = $offset_pay + 1;
                    $data->end_offset_this_page = ($data->curr_page_pay < $data->total_pages_pay) ? $data->offset_pay + $entries_per_page_pay : $total_entries_pay;
                    //Pagination Ends payments
                    $data->total_pages_sub = (int) ($total_entries_sub / $entries_per_page_sub) + (($total_entries_sub % $entries_per_page_sub) == 0 ? 0 : 1);
                    $data->curr_page_sub = $req_page_sub;
                    $data->starting_serial_number_sub = $offset_sub + 1;
                    //Pagination Ends submissions

                    $data->active_tab_index = isset($request->req['rm_tab']) ? (int) $request->req['rm_tab'] : 0;

                    $view = $this->mv_handler->setView('front_submissions', true);
                    return $view->read($data);
                } elseif ($data->is_user === true)
                {
                    $data->payments = false;
                    $data->submissions = false;
                    $view = $this->mv_handler->setView('front_submissions', true);
                    return $view->read($data);
                } else
                {
                    $view = $this->mv_handler->setView('not_authorized', true);
                    $msg = RM_UI_Strings::get('MSG_NO_SUBMISSION_FRONT');
                    return $view->read($msg);
                }
            }
        } else
        {

            $view = $this->mv_handler->setView('not_authorized', true);
            $msg = RM_UI_Strings::get('MSG_NOT_AUTHORIZED');
            return $view->read($msg);
        }
    }

    public function log_off($model, RM_Front_Service $service, $request, $params)
    {
        $user_email = $service->get_user_email();

        if (null != $user_email)
        {
            $service->log_front_user_off($user_email);
            RM_Utilities::redirect(get_permalink(get_option('rm_option_front_sub_page_id')));
        }
    }

}
