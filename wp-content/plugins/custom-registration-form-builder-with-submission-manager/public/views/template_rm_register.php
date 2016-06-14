<?php
$current_user = wp_get_current_user();
$form->addElement(new Element_Textbox("<b>" . RM_UI_Strings::get('LABEL_USERNAME'). "</b>:", "username", array("value" => $current_user->user_login, "required" => "1","placeholder"=>RM_UI_Strings::get('LABEL_USERNAME'))));

/*
 * Skip password field if auto generation is on
 */
if(!$data->is_auto_generate){
    $form->addElement(new Element_Password("<b>" . RM_UI_Strings::get('LABEL_PASSWORD') . "</b>:", "password",array("required"=>1, "longDesc"=>RM_UI_Strings::get('HELP_PASSWORD_MIN_LENGTH'),"minLength", "validation" => new Validation_RegExp("/.{7,}/", "Error: The %element% must be atleast 7 characters long."))));

}

?>


