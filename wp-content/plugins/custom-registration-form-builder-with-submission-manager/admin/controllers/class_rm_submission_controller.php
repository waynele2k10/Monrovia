<?php

/**
 * Class for submissions controller
 * 
 * Manages the submissions related operations in the backend.
 *
 * @author CMSHelplive
 */
class RM_Submission_Controller
{

    private $mv_handler;

    public function __construct()
    {
        $this->mv_handler = new RM_Model_View_Handler();
    }

    public function manage($model, $service, $request, $params)
    {
        $data = new stdClass();

        if (isset($request->req['rm_field_to_search']) && isset($request->req['rm_value_to_serach']) && (int) $request->req['rm_field_to_search'])
        {
            $field_id = $request->req['rm_field_to_search'];
            $field_value = $request->req['rm_value_to_serach'];
        } else
        {
            $field_id = null;
            $field_value = null;
        }

        if (isset($request->req['rm_form_id']))
            $form_id = $request->req['rm_form_id'];
        else
            $form_id = $service->get('FORMS', 1, array('%d'), 'var', 0, 15, $column = 'form_id', null, true);

        if ((int) $field_id)
        {
            $data->searched = true;            
        } else
            $data->searched = false;
        
        $data->to_search = new stdClass();
        $data->to_search->id = $field_id;
        $data->to_search->value = $field_value;
        
        $data->form_id = $form_id;
        $data->forms = RM_Utilities::get_forms_dropdown($service);
        $data->fields = $service->get_all_form_fields($form_id);
        //$data->submissions = $service->get('SUBMISSIONS', array('form_id' => $form_id), array('%d'), 'results', 0, 100, '*', $sort_by = null, $descending = true);

        $data->interval = isset($request->req['rm_interval']) ? $request->req['rm_interval'] : 'all';

        $data->total_entries = count(RM_DBManager::get_results_for_last($data->interval, $form_id, $field_id, $field_value));

        //echo "<pre>",var_dump($data->x), die;
        $data->rm_slug = $request->req['page'];

        //Pagination
        $entries_per_page = 10;
        $req_page = (isset($request->req['rm_reqpage']) && $request->req['rm_reqpage'] > 0) ? $request->req['rm_reqpage'] : 1;
        $offset = ($req_page - 1) * $entries_per_page;
        $total_entries = $data->total_entries;
        $data->total_pages = (int) ($total_entries / $entries_per_page) + (($total_entries % $entries_per_page) == 0 ? 0 : 1);
        $data->curr_page = $req_page;

        $data->submissions = RM_DBManager::get_results_for_last($data->interval, $form_id, $field_id, $field_value, $offset, $entries_per_page, 'submission_id', true);

        $view = $this->mv_handler->setView('submissions_manager');
        $view->render($data);
    }

    public function view($model, RM_Services $service, $request, $params)
    {
        if (isset($request->req['rm_submission_id']))
        {

            if (!$model->load_from_db($request->req['rm_submission_id']))
            {

                $view = $this->mv_handler->setView('show_notice');
                $data = RM_UI_Strings::get('MSG_DO_NOT_HAVE_ACCESS');
                $view->render($data);
            } else
            {

                if (isset($request->req['rm_action']) && $request->req['rm_action'] == 'delete')
                {
                    $request->req['rm_form_id'] = $model->get_form_id();
                    $service->remove($request->req['rm_submission_id']);
                    $this->manage($model, $service, $request, $params);
                } else
                {
                    $settings = new RM_Options;

                    $data = new stdClass();

                    $data->submission = $model;

                    $data->payment = $service->get('PAYPAL_LOGS', array('submission_id' => $model->get_submission_id()), array('%d'), 'row', 0, 99999);

                    if ($data->payment != null)
                    {
                        $data->payment->total_amount = $settings->get_formatted_amount($data->payment->total_amount, $data->payment->currency);

                        if ($data->payment->log)
                            $data->payment->log = maybe_unserialize($data->payment->log);
                    }

                    $data->notes = $service->get('NOTES', array('submission_id' => $model->get_submission_id()), array('%d'), 'results', 0, 99999, '*', null, true);
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
                    $form->load_from_db($model->get_form_id());
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
                        $email = $model->get_user_email();
                        if ($email != "")
                        {
                            $user = get_user_by('email', $email);
                            $data->user = $user;
                        }
                    }
                    $view = $this->mv_handler->setView('view_submission');

                    $view->render($data);
                }
            }
        } else
            throw new InvalidArgumentException(RM_UI_Strings::get('MSG_INVALID_SUBMISSION_ID'));
    }

    public function print_pdf($model, $service, $request, $params)
    {
        $this->manage($model, $service, $request, $params);
    }

    public function remove($model, RM_Services $service, $request, $params)
    {
        $selected = isset($request->req['rm_selected']) ? $request->req['rm_selected'] : null;
        $service->remove_submissions($selected);
        $service->remove_submission_notes($selected);
        $service->remove_submission_payment_logs($selected);
        $this->manage($model, $service, $request, $params);
    }

    public function export($model, RM_Services $service, $request, $params)
    {
        $this->manage($model, $service, $request, $params);
    }

    public function search($model, RM_Services $service, $request, $params)
    {
        
    }

}
