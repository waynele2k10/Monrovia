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
class RM_Services
{

    private $model;
    public $mailchimpService;

    public function __construct($model = null)
    {
        $this->model = $model;

        if ($this->get_setting('enable_mailchimp') == 'yes')
            $this->mailchimpService = new RM_MailChimp_Service();
    }

    public function add()
    {
        return $this->model->insert_into_db();
    }

    public function add_user_form()
    {

        $form_id = $this->model->insert_into_db();

        $this->add_default_form_fields($form_id, false);
    }

    public function update($form_id)
    {
        return $this->model->update_into_db();
    }

    public function get_all($model_identifier = null, $offset = 0, $limit = 15, $column = '*', $sort_by = '', $descending = false)
    {

        if (!$model_identifier)
            $model_identifier = $this->model->get_identifier();

        $results = RM_DBManager::get_all($model_identifier, $offset, $limit, $column, $sort_by, $descending);

        return $results;
    }

    public function count($model_identifier, $args)
    {
        return RM_DBManager::count($model_identifier, $args);
    }

    public function get_user($user_id, $field_name)
    {
        $user = new RM_User($user_id);
        return $user->get($field_name);
    }

    public function get($model_identifier, $where, $data_specifier, $result_type = 'results', $offset = 0, $limit = 15, $column = '*', $sort_by = null, $descending = false)
    {
        return RM_DBManager::get($model_identifier, $where, $data_specifier, $result_type, $offset, $limit, $column, $sort_by, $descending);
    }

    public function duplicate($unique_ids, $model_identifier = null)
    {
        if (!$model_identifier)
        {
            $model_identifier = $this->model->get_identifier();
        }

        $ids = array();

        $model_name = RM_Utilities::get_class_name_for($model_identifier);

        $model = new $model_name;
        if (is_array($unique_ids))
        {
            foreach ($unique_ids as $unique_id)
            {
                $model->load_from_db($unique_id, false);
                $ids[$unique_id] = $model->insert_into_db();
            }
        } elseif ((int) $unique_ids)
        {
            $model->load_from_db($unique_ids, false);
            $ids[$unique_ids] = $model->insert_into_db();
        } else
            return false;

        return $ids;
    }

    public function remove($unique_ids, $model_identifier = null, $where = null)
    {
        if (!$model_identifier)
        {
            $model_identifier = $this->model->get_identifier();
        }

        if (is_array($unique_ids))
        {
            foreach ($unique_ids as $unique_id)
            {
                RM_DBManager::remove_row($model_identifier, $unique_id, $where);
            }
        } elseif ((int) $unique_ids)
        {
            RM_DBManager::remove_row($model_identifier, $unique_ids, $where);
        } else
            return false;
    }

    public function remove_submissions($unique_ids, $where = null)
    {
        $model_identifier = 'SUBMISSIONS';

        if (is_array($unique_ids))
        {
            foreach ($unique_ids as $unique_id)
            {
                RM_DBManager::remove_row($model_identifier, $unique_id, $where);
                RM_DBManager::delete_rows('SUBMISSION_FIELDS', array('submission_id' => $unique_id), array('%d'));
            }
        } elseif ((int) $unique_ids)
        {
            RM_DBManager::remove_row($model_identifier, $unique_ids, $where);
            RM_DBManager::delete_rows('SUBMISSION_FIELDS', array('submission_id' => $unique_id), array('%d'));
        } else
            return false;
    }

    public function save_options($options)
    {
        return $this->model->set_values($options);
    }

    public function get_mailchimp_list()
    {
        $result = $this->mailchimpService->get_list();
        $lists = array(null => RM_UI_Strings::get('OPTION_SELECT_LIST'));
        if (!empty($result))
        {
            foreach ($result['lists'] as $key => $list)
            {
                $lists[$list['id']] = $list['name'];
            }
        }

        return $lists;
    }

    public function get_all_form_fields($form_id)
    {
        if ((int) $form_id)
            return RM_DBManager::get_fields_by_form_id($form_id);
        else
            return false;
    }

    public function add_default_form_fields($form_id, $is_reg_form)
    {

        $this->create_default_email_field($form_id);
    }

    public function create_default_password_field($form_id)
    {

        $field = new RM_Fields;
        $field->set(array('form_id' => $form_id,
            'field_type' => 'Password',
            'field_label' => 'Password',
            'field_placeholder' => 'Password',
            'field_css_class' => 'rm_form_default_fields',
            'field_is_required' => 1,
            'field_show_on_user_page' => 0,
            'field_is_read_only' => 0,
            'field_max_length' => 50,
            'is_field_primary' => 1));

        return $field->insert_into_db();
    }

    public function create_default_username_field($form_id)
    {

        $field = new RM_Fields;
        $field->set(array('form_id' => $form_id,
            'field_type' => 'Textbox',
            'field_label' => 'User Name',
            'field_placeholder' => 'User Name',
            'field_css_class' => 'rm_form_default_fields',
            'field_is_required' => 1,
            'field_show_on_user_page' => 1,
            'field_is_read_only' => 0,
            'field_max_length' => 70,
            'is_field_primary' => 1));

        return $field->insert_into_db();
    }

    public function create_default_email_field($form_id)
    {

        $field = new RM_Fields;
        $field->set(array('form_id' => $form_id,
            'field_type' => 'Email',
            'field_label' => 'Email',
            'field_placeholder' => 'Email',
            'field_css_class' => 'rm_form_default_fields',
            'field_is_required' => 1,
            'field_show_on_user_page' => 0,
            'field_is_read_only' => 0,
            'is_field_primary' => 1));

        return $field->insert_into_db();
    }

    public function remove_form_fields($form_id)
    {
        if (is_array($form_id))
            foreach ($form_id as $formId)
                RM_DBManager::delete_form_fields($formId);
        elseif ((int) $form_id)
            RM_DBManager::delete_form_fields($formId);
        else
            throw new InvalidArgumentException("Invalid Form ID '$form_id'.");
    }

    public function get_setting($name)
    {
        $global_settings = new RM_Options;
        $result = $global_settings->get_value_of($name);
        return $result;
    }

    public function is_form_expired($form)
    {

        if (!$form->form_should_auto_expire)
        {
            return false;
        }

        $form_id = $form->form_id;
        $criterian = $form->form_options->form_expired_by;

        if ($criterian == "date")
        {
            if (RM_DBManager::is_expired_by_date($form_id))
                return true;
        }elseif ($criterian == "submissions")
        {
            if (RM_DBManager::is_expired_by_submissions($form_id, $form->form_options->form_submissions_limit))
                return true;
        }elseif ($criterian == "both")
        {
            return (RM_DBManager::is_expired_by_date($form_id) || RM_DBManager::is_expired_by_submissions($form_id, $form->form_options->form_submissions_limit));
        }
    }

    public function get_form_expiry_stats($form)
    {
        //die('called');
        $form_id = $form->get_form_id();
        $form_options = $form->get_form_options();
        $criterian = $form_options->form_expired_by;


        $remaining = (object) array('state' => 'perpetual',
                    'criteria' => 'both',
                    'remaining_days' => 'undefined',
                    'remaining_subs' => 'undefined',
                    'sub_limit' => 0);

        if (!$form->form_should_auto_expire || $this->get_setting('display_progress_bar') !== 'yes')
        {
            return $remaining;
        }

        if ($criterian == "date")
        {
            if (RM_DBManager::is_expired_by_date($form_id, $remaining->remaining_days))
            {
                $remaining->state = 'expired';
                $remaining->criteria = 'date';
            } else
            {
                $remaining->state = 'not_expired';
                $remaining->criteria = 'date';
            }
            return $remaining;
        } elseif ($criterian == "submissions")
        {
            if (RM_DBManager::is_expired_by_submissions($form_id, $form_options->form_submissions_limit, $remaining->remaining_subs))
            {
                $remaining->state = 'expired';
                $remaining->criteria = 'subs';
            } else
            {
                $remaining->state = 'not_expired';
                $remaining->criteria = 'subs';
                $remaining->sub_limit = $form_options->form_submissions_limit;
            }
            return $remaining;
        } elseif ($criterian == "both")
        {
            if (RM_DBManager::is_expired_by_date($form_id, $remaining->remaining_days) || RM_DBManager::is_expired_by_submissions($form_id, $form_options->form_submissions_limit, $remaining->remaining_subs))
            {
                $remaining->state = 'expired';
                $remaining->criteria = 'both';
            } else
            {
                $remaining->state = 'not_expired';
                $remaining->criteria = 'both';
                $remaining->sub_limit = $form_options->form_submissions_limit;
            }
            return $remaining;
        }
    }

    public function delete_rows($model_identifier, $where, $where_format = null)
    {
        return RM_DBManager::delete_rows($model_identifier, $where, $where_format);
    }

    public function duplicate_form_fields($form_id, $ids)
    {
        if (is_array($form_id))
            foreach ($form_id as $formId)
            {
                $fields = RM_DBManager::get_fields_by_form_id($formId);
                foreach ($fields as $field)
                    $this->duplicate_field($field->field_id, $ids[$formId]);
            } elseif ((int) $form_id)
        {
            $fields = RM_DBManager::get_fields_by_form_id($form_id);
            foreach ($fields as $field)
                $this->duplicate_field($field->field_id, $ids[$form_id]);
        } else
            throw new InvalidArgumentException("Invalid Form ID '$form_id'.");
    }

    public function duplicate_field($field_id, $form_id)
    {
        $model = new RM_Fields;

        $model->load_from_db($field_id, false);
        $model->set_form_id($form_id);
        $model->insert_into_db();
    }

    public function set_field_order($list)
    {
        RM_DBManager::set_field_order($list);
    }

    public function get_submissions_by_email($user_email, $limit = 9999999, $offset = 0, $sort_by = '', $descending = true)
    {
        return RM_DBManager::get_submissions_for_user($user_email, $limit, $offset, $sort_by, $descending);
    }

    public function get_payments_by_email($user_email, $limit = 9999999, $offset = 0, $sort_by = '', $descending = true)
    {


        $submission_ids = $this->get_submissions_by_email($user_email, $limit, $offset, $sort_by, $descending);

        return get_payments_by_submission_id($submission_ids, $limit, $offset, $sort_by, $descending);
    }

    public function get_payments_by_submission_id($submission_ids, $limit = 9999999, $offset = 0, $sort_by = '', $descending = false)
    {

        if (is_array($submission_ids))
            $fields = RM_DBManager::get_results_for_array('PAYPAL_LOGS', 'submission_id', $submission_ids);
        elseif ((int) $submission_ids)
            $fields = $this->get('PAYPAL_LOGS', array('submission_id' => $submission_ids), array('%d'), 'row', $offset, $limit, '*', $sort_by, $descending);

        if (!$fields)
            return false;

        return $fields;
    }

    public function remove_form_submissions($form_id)
    {
        if (is_array($form_id))
            foreach ($form_id as $formId)
                RM_DBManager::delete_form_submissions($formId);
        elseif ((int) $form_id)
            RM_DBManager::delete_form_submissions($form_id);
        else
            throw new InvalidArgumentException("Invalid Form ID '$form_id'.");
    }

    public function remove_form_stats($form_id)
    {
        if (is_array($form_id))
            foreach ($form_id as $formId)
                RM_DBManager::delete_form_stats($formId);
        elseif ((int) $form_id)
            RM_DBManager::delete_form_stats($form_id);
        else
            throw new InvalidArgumentException("Invalid Form ID '$form_id'.");
    }

    public function remove_form_notes($form_id)
    {
        if (is_array($form_id))
            foreach ($form_id as $formId)
                RM_DBManager::delete_form_notes($formId);
        elseif ((int) $form_id)
            RM_DBManager::delete_form_notes($form_id);
        else
            throw new InvalidArgumentException("Invalid Form ID '$form_id'.");
    }

    public function remove_form_payment_logs($form_id)
    {
        if (is_array($form_id))
            foreach ($form_id as $formId)
                RM_DBManager::delete_form_payment_logs($formId);
        elseif ((int) $form_id)
            RM_DBManager::delete_form_payment_logs($form_id);
        else
            throw new InvalidArgumentException("Invalid Form ID '$form_id'.");
    }

    public function remove_submission_payment_logs($sub_id)
    {
        if (is_array($sub_id))
            foreach ($sub_id as $sub_id_)
                RM_DBManager::delete_rows('PAYPAL_LOGS', array('submission_id' => $sub_id_));
        elseif ((int) $sub_id)
            RM_DBManager::delete_rows('PAYPAL_LOGS', array('submission_id' => $sub_id));
        else
            throw new InvalidArgumentException("Invalid Submission ID '$sub_id'.");
    }

    public function remove_submission_notes($sub_id)
    {
        if (is_array($sub_id))
            foreach ($sub_id as $sub_id_)
                RM_DBManager::delete_rows('NOTES', array('submission_id' => $sub_id_));
        elseif ((int) $sub_id)
            RM_DBManager::delete_rows('NOTES', array('submission_id' => $sub_id));
        else
            throw new InvalidArgumentException("Invalid Submission ID '$sub_id'.");
    }

    public function get_submissions_to_export($form_id)
    {

        $export_data = array();
        $is_payment = false;
        $option = new RM_Options;

        if (!(int) $form_id)
            return false;

        $fields = $this->get_all_form_fields($form_id);

        if (!$fields)
            return false;

        $field_ids = array();

        foreach ($fields as $field)
        {
            if ($field->field_type != 'Price' && $field->field_type != 'HTMLH' && $field->field_type != 'HTMLP')
            {
                $field_ids[] = $field->field_id;
                $export_data[0][$field->field_id] = $field->field_label;
            }
            $i = 0;
            if ($field->field_type == 'price' && $i == 0)
            {
                $is_payment = true;
                $export_data[0]['invoice'] = 'Payment Invoice';
                $export_data[0]['txn_id'] = 'Payment TXN Id';
                $export_data[0]['status'] = 'Payment Status';
                $export_data[0]['total_amount'] = 'Paid Amount';
                $export_data[0]['date'] = 'Date of Payment';
                $i++;
            }
        }

        $submissions = RM_DBManager::get_results_for_array('SUBMISSION_FIELDS', 'field_id', $field_ids);

        $submission_ids = $this->get('SUBMISSIONS', array('form_id' => $form_id), array('%d'), 'col', 0, 999999, 'submission_id', null, true);

        if (!$submission_ids)
            return false;

        foreach ($submission_ids as $s_id)
        {
            $export_data[$s_id] = array();

            $payment = $this->get('PAYPAL_LOGS', array('submission_id' => $s_id), array('%d'), 'row', 0, 10, '*', null, true);

            foreach ($field_ids as $f_id)
            {
                $export_data[$s_id][$f_id] = null;
            }

            if ($is_payment)
            {
                $export_data[$s_id]['invoice'] = isset($payment->invoice) ? : null;
                $export_data[$s_id]['txn_id'] = isset($payment->txn_id) ? : null;
                $export_data[$s_id]['status'] = isset($payment->status) ? : null;
                $export_data[$s_id]['total_amount'] = isset($payment->total_amount) ? $option->get_formatted_amount($payment->total_amount, $payment->currency) : null;
                $export_data[$s_id]['date'] = isset($payment->posted_date) ? RM_Utilities::localize_time($payment->posted_date, get_option('date_format')) : null;
            }
        }

        foreach ($submissions as $submission)
        {
            $value = maybe_unserialize($submission->value);
            if (is_array($value))
            {
                if (isset($value['rm_field_type']) && $value['rm_field_type'] == 'File')
                {
                    unset($value['rm_field_type']);
                    if (count($value) == 0)
                        $value = null;
                    else
                    {
                        $file = array();
                        foreach ($value as $a)
                            $file[] = wp_get_attachment_url($a);

                        $value = implode(',', $file);
                    }
                } else
                    $value = implode(',', $value);
            }
            $export_data[$submission->submission_id][$submission->field_id] = stripslashes($value);
        }

        return $export_data;
    }

    public function create_csv($data)
    {

        $csv_name = 'rm_submissions' . time() . mt_rand(10, 1000000);
        $csv_path = get_temp_dir() . $csv_name . '.csv';
        $csv = fopen($csv_path, "w");

        if (!$csv)
        {
            return false;
        }

        foreach ($data as $a)
        {
            if (!fputcsv($csv, $a))
                return false;
        }

        fclose($csv);

        return $csv_path;
    }

    public function download_file($file, $unlink = true)
    {
        if (ob_get_contents())
        {
            ob_end_clean();
        }
        if (file_exists($file))
        {
            $mime_type = RM_Utilities::mime_content_type($file);
            header('Content-Description: File Transfer');
            header('Content-Type: ' . $mime_type);
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            header('Expires: 0');
            readfile($file);
            if ($unlink)
                unlink($file);
            exit;
        } else
            return false;

        return true;
    }

    public function get_custom_fields($user_email)
    {

        $field_ids = array();
        $forms = array();
        $custom_fields = array();

        $submissions = $this->get('SUBMISSIONS', array('user_email' => $user_email), array('%s'), 'results', 0, 999999, '*', null, false);

        if (!$submissions)
            return false;

        if (is_array($submissions) || is_object($submissions))
            foreach ($submissions as $submission)
            {
                if (!in_array($submission, $forms))
                {
                    $forms[] = $submission->form_id;
                    $result = $this->get('FIELDS', array('form_id' => $submission->form_id, 'field_show_on_user_page' => 1), array('%s', '%d'), 'results', 0, 999999, '*', null, false);
                    if ($result)
                        $field_ids[$submission->submission_id] = $result;
                }
            }

        foreach ($field_ids as $submission_id => $field)
        {
            foreach ($field as $f_row)
            {

                $result = $this->get('SUBMISSION_FIELDS', array('submission_id' => $submission_id, 'field_id' => $f_row->field_id), array('%d', '%d'), 'var', 0, 999999, 'value', null, false);

                if ($result)
                {
                    $custom_fields[$f_row->field_id] = new stdClass();
                    $custom_fields[$f_row->field_id]->label = $f_row->field_label;
                    $custom_fields[$f_row->field_id]->value = $result;
                }
            }
        }

        if (count($custom_fields) == 0)
            return null;

        return $custom_fields;
    }

}
