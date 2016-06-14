<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of calss_rm_field_controller
 *
 * @author CMSHelplive
 */
class RM_Field_Controller
{

    private $mv_handler;

    function __construct()
    {
        $this->mv_handler = new RM_Model_View_Handler();
    }

    public function add($model, $service, $request, $params)
    {

        if (isset($request->req['rm_form_id']))
            $fields_data = $service->get_all_form_fields($request->req['rm_form_id']);
        else
            die(RM_UI_Strings::get('MSG_NO_FORM_SELECTED'));

        if ($this->mv_handler->validateForm("add-field"))
        {
            $model->set($request->req);
            if (isset($request->req['field_id']))
                $service->update($model, $service, $request, $params);
            else
                $service->add($model, $service, $request, $params);
                RM_Utilities::redirect(admin_url('/admin.php?page=' . $params['xml_loader']->request_tree->success.'&rm_form_id='.$request->req["rm_form_id"]));
            //$this->view->render();
        } else
        {

            // Edit for request
            if (isset($request->req['rm_field_id']))
            {
                $model->load_from_db($request->req['rm_field_id']);
            }
            
            $data = new stdClass;
            $data->model = $model;
            $data->selected_field = isset($request->req['rm_field_type'])?$request->req['rm_field_type']:null;
            $data->form_id = $request->req['rm_form_id'];
            $data->paypal_fields = RM_Utilities::get_paypal_field_types($service);
            $view = $this->mv_handler->setView("field_add");
            $view->render($data);
        }
    }

    public function manage($model, $service, $request, $params)
    {
        if(isset($request->req['rm_action']) && $request->req['rm_action'] === 'delete')
            $this->remove_field($model, $service, $request, $params);

        if (isset($request->req['rm_form_id']))
            $fields_data = $service->get_all_form_fields($request->req['rm_form_id']);
        else
            die(RM_UI_Strings::get('MSG_NO_FORM_SELECTED'));

        foreach($fields_data as $index => $field_data){
            if($field_data->field_type === 'Price'){
                $price_field = $service->get('PAYPAL_FIELDS', array('field_id' => $field_data->field_value), array('%d'), 'row');
                if($price_field && isset($price_field->type))
                if($price_field->type !== 'fixed')
                    unset($fields_data[$index]);
            }
            elseif($field_data->field_type === 'File' || $field_data->field_type === 'Repeatable')
                unset($fields_data[$index]);
        }

        $data = new stdClass;
        $data->fields_data = $fields_data;
        $data->forms = RM_Utilities::get_forms_dropdown($service);
        $data->field_types = RM_Utilities::get_field_types();
        $data->form_id = $request->req['rm_form_id'];
        $view = $this->mv_handler->setView("field_manager");
        $view->render($data);
    }
    
    public function set_order($model, $service, $request, $params){
        $service->set_field_order($request->req['data']);
    }

    private function remove_field($model, $service, $request, $params){
        if (isset($request->req['rm_field_id']))
            $result = $service->remove($request->req['rm_field_id'], null, array('is_field_primary' => 0));
        else
            die(RM_UI_Strings::get('MSG_NO_FIELD_SELECTED'));
    }
    
    public function duplicate($model, $service, $request, $params)
    {
        $selected = isset($request->req['rm_selected'])?$request->req['rm_selected']:null;
        $ids = $service->duplicate($selected);
        $this->manage($model, $service, $request, $params);
    }
    
    public function remove($model,  RM_Services $service, $request, $params)
    {
        $selected = isset($request->req['rm_selected'])?$request->req['rm_selected']:null;
        $service->remove($selected);
        $this->manage($model, $service, $request, $params);
    }

}
