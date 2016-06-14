<?php

/**
 * @internal Plugin Template File [Add Text Type Field]
 * 
 * This view generates the form for adding text type field to the form
 */

$price_field_type = array("fixed" => "Fixed");

$fixed_class = "class = 'rm_hidden_element'";
$dd_class = "class = 'rm_hidden_element'";
        
if($data->selected_field == 'fixed'){
        $fixed_class = "";
        $dd_class = "class = 'rm_hidden_element'";
    }

?>

<div class="rmagic">

    <!--Dialogue Box Starts-->
    <div class="rmcontent">
        
        <?php
        
$form = new Form("add-paypal-field");

$form->configure(array(
    "prevent" => array("bootstrap", "jQuery"),
    "action" => ""
));

if (isset($data->model->field_id))
{
    $form->addElement(new Element_HTML('<div class="rmheader">' . RM_UI_Strings::get("TITLE_EDIT_PAYPAL_FIELD_PAGE") . '</div>'));
    $form->addElement(new Element_Hidden("field_id", $data->model->field_id));
}
else
    $form->addElement(new Element_HTML('<div class="rmheader">' . RM_UI_Strings::get("TITLE_NEW_PAYPAL_FIELD_PAGE") . '</div>'));

$form->addElement(new Element_Select("<b>" . RM_UI_Strings::get('LABEL_SELECT_TYPE') . ":</b>", "type", $price_field_type, array("value" => $data->selected_field, "id"=>"id_paypal_field_type_dd",  "class" => "rm_static_field", "required" => "1", "onchange" => "rm_toggle_visiblity_pricing_fields(this)", "longDesc"=>RM_UI_Strings::get('HELP_ADD_PRICE_FIELD_SELECT_TYPE'))));
$form->addElement(new Element_Textbox("<b>" . RM_UI_Strings::get('LABEL_LABEL') . ":</b>", "name", array("id"=>"id_paypal_field_name_tb", /*"required" => "0",*/ "class" => "rm_static_field", "required" => "1", "value" => $data->model->name, "longDesc"=>RM_UI_Strings::get('HELP_ADD_PRICE_FIELD_LABEL'))));

$form->addElement(new Element_HTML("<div id='id_block_fields_for_fixed' $fixed_class>"));
$form->addElement(new Element_Number("<b>" . RM_UI_Strings::get('LABEL_PRICE') . ":</b>", "value", array("id"=>"id_paypal_field_value_no", /*"required" => "0",*/ "class" => "rm_static_field", "step"=>"0.01", "min"=>"0.01", "value" => $data->model->value, "longDesc" => RM_UI_Strings::get('HELP_PRICE_FIELD'))));
$form->addElement(new Element_Checkbox("<b>" . RM_UI_Strings::get('LABEL_SHOW_ON_FORM') . ":</b>", "show_on_form", array(1 => ""), array("id"=>"id_paypal_field_visible_cb", "class" => "rm_static_field", "value" => $data->show_on_form)));
$form->addElement(new Element_HTML("</div>"));

$form->addElement(new Element_HTML("<div id='id_block_fields_for_dd_multisel' $dd_class>"));

$multiple_prices = maybe_unserialize($data->model->get_option_price());
$multiple_labels = maybe_unserialize($data->model->get_option_label());

$form->addElement(new Element_HTML('<div class="rm_ul_sortable_container">'));
$form->addElement(new Element_Textboxsortable("<b>" . RM_UI_Strings::get('LABEL_PRICE') . ":</b>", "multisel_name_value[]", array("id" => "id_placeholder_tb", "placeholder" => RM_UI_Strings::get('LABEL_LABEL'), "class" => "rm_static_field", "required" => "1", "value" => $multiple_labels, "longDesc" => RM_UI_Strings::get('HELP_PRICE_FIELD')), array("type" => "number", "placeholder" => RM_UI_Strings::get('LABEL_PRICE'), "name" => "multisel_price_value[]", "id" => "id_placeholder_no", "class" => "rm_static_field", "required" => "1", "step" => "0.01", "min" => "0.01", "value" => $multiple_prices)));
$form->addElement(new Element_HTML("</div></div>"));
$form->addElement(new Element_HTMLL('&#8592; &nbsp; Cancel', '?page=rm_paypal_field_manage', array('class' => 'cancel')));
$form->addElement(new Element_Button(RM_UI_Strings::get('LABEL_SAVE'), "submit", array("id" => "rm_submit_btn", "onClick" => "jQuery.prevent_field_add(event, '".RM_UI_Strings::get('MSG_REQUIRED_FIELD') ."')", "class" => "rm_btn", "name" => "submit")));
 
$form->render();

?>
         
    </div>
</div>

<?php   