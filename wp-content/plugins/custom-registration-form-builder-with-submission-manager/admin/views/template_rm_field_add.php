<?php
//echo'<pre>';var_dump($data->model);die;
/**
 * @internal Plugin Template File [Add Text Type Field]
 * 
 * This view generates the form for adding text type field to the form
 */
?>
<div class="rmagic">

    <!--Dialogue Box Starts-->
    <div class="rmcontent">

        <?php
        if (isset($data->model->is_field_primary) && $data->model->is_field_primary == 1)
        {
            include_once plugin_dir_path(__FILE__) . 'template_rm_primary_field_add.php';
        } else
        {

            $form = new Form("add-field");

            $form->configure(array(
                "prevent" => array("bootstrap", "jQuery"),
                "action" => ""
            ));

            if (isset($data->model->field_id))
            {
                $form->addElement(new Element_HTML('<div class="rmheader">' . RM_UI_Strings::get("TITLE_EDIT_FIELD_PAGE") . '</div>'));
                $form->addElement(new Element_Hidden("field_id", $data->model->field_id));
            } else
                $form->addElement(new Element_HTML('<div class="rmheader">' . RM_UI_Strings::get("TITLE_NEW_FIELD_PAGE") . '</div>'));

            $form->addElement(new Element_Hidden("form_id", $data->form_id));

            $form->addElement(new Element_Select("<b>" . RM_UI_Strings::get('LABEL_SELECT_TYPE') . ":</b>", "field_type", RM_Utilities::get_field_types(), array("id" => "rm_field_type_select_dropdown", "value" => $data->selected_field, "class" => "rm_static_field rm_required", "required" => "1", "onchange" => "rm_toggle_field_add_form_fields(this)", "longDesc" => RM_UI_Strings::get('HELP_ADD_FIELD_SELECT_TYPE'))));


            $form->addElement(new Element_Textbox("<b>" . RM_UI_Strings::get('LABEL_LABEL') . ":</b>", "field_label", array("class" => "rm_static_field rm_required", "required" => "1", "value" => $data->model->field_label, "longDesc" => RM_UI_Strings::get('HELP_ADD_FIELD_LABEL'))));

//Field_value fields only one can be used at a time
            $form->addElement(new Element_Textarea("<b>" . RM_UI_Strings::get('LABEL_T_AND_C') . ":</b>", "field_value", array("id" => "rm_field_value_terms", "class" => "rm_static_field rm_field_value", "value" => is_array($data->model->get_field_value()) ? null : $data->model->get_field_value(), "longDesc" => RM_UI_Strings::get('HELP_ADD_FIELD_TnC_VAL'))));

            $form->addElement(new Element_Textarea("<b>" . RM_UI_Strings::get('LABEL_FILE_TYPES') . ":</b>(" . RM_UI_Strings::get('LABEL_FILE_TYPES_DSC') . ")", "field_value", array("id" => "rm_field_value_file_types", "class" => "rm_static_field rm_field_value", "value" => is_array($data->model->get_field_value()) ? null : $data->model->get_field_value(), "longDesc" => RM_UI_Strings::get('HELP_ADD_FIELD_FILETYPE'))));


            $form->addElement(new Element_Select("<b>" . RM_UI_Strings::get('LABEL_PRICING_FIELD') . ":</b>", "field_value", $data->paypal_fields, array("id" => "rm_field_value_pricing", "value" => is_array($data->model->get_field_value()) ? null : $data->model->get_field_value(), "class" => "rm_field_value rm_static_field", "longDesc" => RM_UI_Strings::get('HELP_ADD_FIELD_PRICE_FIELD'))));
            $form->addElement(new Element_Textarea("<b>" . RM_UI_Strings::get('LABEL_PARAGRAPF_TEXT') . ":</b>", "field_value", array("id" => "rm_field_value_paragraph", "class" => "rm_static_field rm_field_value", "value" => is_array($data->model->get_field_value()) ? null : $data->model->get_field_value(), "longDesc" => RM_UI_Strings::get('HELP_ADD_FIELD_PARA_TEXT'))));
            $form->addElement(new Element_Textarea("<b>" . RM_UI_Strings::get('LABEL_HEADING_TEXT') . ":</b>", "field_value", array("id" => "rm_field_value_heading", "class" => "rm_static_field rm_field_value", "value" => is_array($data->model->get_field_value()) ? null : $data->model->get_field_value(), "longDesc" => RM_UI_Strings::get('HELP_ADD_FIELD_HEADING_TEXT'))));
            $form->addElement(new Element_Textarea("<b>" . RM_UI_Strings::get('LABEL_OPTIONS') . ":</b>(" . RM_UI_Strings::get('LABEL_DROPDOWN_OPTIONS_DSC') . ")", "field_value", array("id" => "rm_field_value_options_textarea", "class" => "rm_static_field rm_field_value", "value" => is_array($data->model->get_field_value()) ? null : $data->model->get_field_value(), "longDesc" => RM_UI_Strings::get('HELP_ADD_FIELD_OPTIONS_COMMASEP'))));
            $form->addElement(new Element_Textboxsortable("<b>" . RM_UI_Strings::get('LABEL_OPTIONS') . ":</b>", "field_value[]", array("id" => "rm_field_value_options_sortable", "class" => "rm_static_field rm_field_value", "value" => is_array($data->model->get_field_value()) ? $data->model->get_field_value() : explode(',', $data->model->get_field_value()), "longDesc" => RM_UI_Strings::get('HELP_ADD_FIELD_OPTIONS_SORTABLE'))));

//$form->addElement(new Element_HTML(""));
$form->addElement(new Element_HTML('<div id="rmaddotheroptiontextboxdiv" style="display:none">'));
$form->addElement(new Element_HTML('<div class="rmrow"><div class="rmfield" for="rm_other_option_text"><label>  </label></div><div class="rminput"><input type="text" name="rm_textbox" id="rm_other_option_text" class="rm_static_field" readonly="disabled" value="Their Answer"><div id="rmaddotheroptiontextdiv2"><div onclick="jQuery.rm_delete_textbox_other(this)">'.RM_UI_Strings::get('LABEL_DELETE').'</div></div></div></div>'));
$form->addElement(new Element_HTML('</div>'));
//$form->addElement(new Element_HTML("<div onclick=''>".RM_UI_Strings::get('LABEL_DELETE')."</div></div>"));
            $form->addElement(new Element_Hidden("field_is_other_option", "", array("id" => "rm_field_is_other_option")));
            $form->addElement(new Element_HTML('<div class="rmrow" id="rm_jqnotice_row"><div class="rmfield" for="rm_field_value_options_textarea"><label></label></div><div class="rminput" id="rm_jqnotice_text"></div></div>'));

            $form->addElement(new Element_Textbox("<b>" . RM_UI_Strings::get('LABEL_PLACEHOLDER_TEXT') . ":</b>", "field_placeholder", array("id" => "rm_field_placeholder", "class" => "rm_static_field rm_text_type_field rm_input_type", "value" => $data->model->field_options->field_placeholder, "longDesc" => RM_UI_Strings::get('HELP_ADD_FIELD_PLACEHOLDER'))));
            $form->addElement(new Element_Textbox("<b>" . RM_UI_Strings::get('LABEL_CSS_CLASS') . ":</b>", "field_css_class", array("id" => "rm_field_class", "class" => "rm_static_field rm_required", "value" => $data->model->field_options->field_css_class, "longDesc" => RM_UI_Strings::get('HELP_ADD_FIELD_CSS_CLASS'))));
            $form->addElement(new Element_Number("<b>" . RM_UI_Strings::get('LABEL_MAX_LENGTH') . ":</b>", "field_max_length", array("id" => "rm_field_max_length", "class" => "rm_static_field rm_text_type_field rm_input_type", "value" => $data->model->field_options->field_max_length, "longDesc" => RM_UI_Strings::get('HELP_ADD_FIELD_MAX_LEN'))));
            $form->addElement(new Element_Textbox("<b>" . RM_UI_Strings::get('LABEL_DEFAULT_VALUE') . ":</b>", "field_default_value", array("id" => "rm_field_default_value", "class" => "rm_static_field rm_options_type_fields rm_input_type", "value" => is_array(maybe_unserialize($data->model->field_options->field_default_value)) ? null : $data->model->field_options->field_default_value, "longDesc" => RM_UI_Strings::get('HELP_ADD_FIELD_DEF_VALUE'))));
            $form->addElement(new Element_Textboxsortable("<b>" . RM_UI_Strings::get('LABEL_DEFAULT_VALUE') . ":</b>", "field_default_value[]", array("id" => "rm_field_default_value_sortable", "class" => "rm_static_field rm_options_type_fields rm_input_type", "value" => $data->model->get_field_default_value())));
            $form->addElement(new Element_Number("<b>" . RM_UI_Strings::get('LABEL_COLUMNS') . ":</b>", "field_textarea_columns", array("id" => "rm_field_columns", "class" => "rm_static_field rm_textarea_type rm_input_type", "value" => $data->model->field_options->field_textarea_columns, "longDesc" => RM_UI_Strings::get('HELP_ADD_FIELD_COLS'))));
            $form->addElement(new Element_Number("<b>" . RM_UI_Strings::get('LABEL_ROWS') . ":</b>", "field_textarea_rows", array("id" => "rm_field_rows", "class" => "rm_static_field rm_textarea_type rm_input_type", "value" => $data->model->field_options->field_textarea_rows, "longDesc" => RM_UI_Strings::get('HELP_ADD_FIELD_ROWS'))));
            $form->addElement(new Element_HTML('<div class="rmrow rm_sub_heading">' . RM_UI_Strings::get('TEXT_RULES') . '</div>'));
            $form->addElement(new Element_Checkbox("<b>" . RM_UI_Strings::get('LABEL_IS_REQUIRED') . ":</b>", "field_is_required", array(1 => ""), array("id" => "rm_field_is_required", "class" => "rm_static_field rm_input_type", "value" => $data->model->field_options->field_is_required, "longDesc" => RM_UI_Strings::get('HELP_ADD_FIELD_IS_REQUIRED'))));

            $form->addElement(new Element_Checkbox("<b>" . RM_UI_Strings::get('LABEL_SHOW_ON_USER_PAGE') . ":</b>", "field_show_on_user_page", array(1 => ""), array("id" => "rm_field_show_on", "class" => "rm_static_field rm_required", "value" => $data->model->field_show_on_user_page, "longDesc" => RM_UI_Strings::get('HELP_ADD_FIELD_SHOW_ON_USERPAGE'))));

//Button Area
            $form->addElement(new Element_HTMLL('&#8592; &nbsp; Cancel', '?page=rm_field_manage&rm_form_id=' . $data->form_id, array('class' => 'cancel')));
            $form->addElement(new Element_Button(RM_UI_Strings::get('LABEL_SAVE'), "submit", array("id" => "rm_submit_btn", "onClick" => "jQuery.prevent_field_add(event, '".RM_UI_Strings::get('MSG_REQUIRED_FIELD') ."')", "class" => "rm_btn", "name" => "submit")));



            $form->render();
//array('field_type','field_label','field_value','field_default_value','field_order','field_options');
        }
        ?>  
    </div>
</div>
