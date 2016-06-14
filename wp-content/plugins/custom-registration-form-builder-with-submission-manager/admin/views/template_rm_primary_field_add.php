<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$form = new Form("add-field");

$form->configure(array(
    "prevent" => array("bootstrap", "jQuery"),
    "action" => ""
));

$form->addElement(new Element_HTML('<div class="rmheader">' . RM_UI_Strings::get("TITLE_EDIT_FIELD_PAGE") . '</div>'));

$form->addElement(new Element_Hidden("field_id", $data->model->field_id));

$form->addElement(new Element_Hidden("form_id", $data->form_id));
$form->addElement(new Element_Hidden("field_is_required", 1));

$form->addElement(new Element_Textbox("<b>" . RM_UI_Strings::get('LABEL_SELECT_TYPE') . ":</b>", "primary_field_type", array("id" => "rm_field_type_select_primary", "disabled" => "1" , "value" => 'Email', "class" => "rm_static_field rm_required", /*"required" => "1",*/ "longDesc"=>RM_UI_Strings::get('HELP_ADD_PRIMARY_FIELD_EMAIL'))));
$form->addElement(new Element_Hidden('field_type','Email'));
$form->addElement(new Element_Textbox("<b>" . RM_UI_Strings::get('LABEL_LABEL') . ":</b>", "field_label", array("class" => "rm_static_field rm_required", "required" => "1", "value" => $data->model->field_label, "longDesc"=>RM_UI_Strings::get('HELP_ADD_FIELD_LABEL'))));
$form->addElement(new Element_Textbox("<b>" . RM_UI_Strings::get('LABEL_PLACEHOLDER_TEXT') . ":</b>", "field_placeholder", array("id" => "rm_field_placeholder", "class" => "rm_static_field rm_text_type_field rm_input_type", "value" => $data->model->field_options->field_placeholder, "longDesc"=>RM_UI_Strings::get('HELP_ADD_FIELD_PLACEHOLDER'))));
$form->addElement(new Element_Textbox("<b>" . RM_UI_Strings::get('LABEL_CSS_CLASS') . ":</b>", "field_css_class", array("id" => "rm_field_class", "class" => "rm_static_field rm_required", "value" => $data->model->field_options->field_css_class, "longDesc"=>RM_UI_Strings::get('HELP_ADD_FIELD_CSS_CLASS'))));

//Button Area
$form->addElement(new Element_HTMLL('&#8592; &nbsp; Cancel', '?page=rm_field_manage&rm_form_id='.$data->form_id, array('class' => 'cancel')));
$form->addElement(new Element_Button(RM_UI_Strings::get('LABEL_SAVE'), "submit", array("id" => "rm_submit_btn", "class" => "rm_btn", "name" => "submit")));


$form->render();
