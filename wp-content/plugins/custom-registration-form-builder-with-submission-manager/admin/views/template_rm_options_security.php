<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$lang_arr = array(
    "ar" => "Arabic ",
    "af" => "Afrikaans ",
    "am" => "Amharic ",
    "hy" => "Armenian ",
    "az" => "Azerbaijani ",
    "eu" => "Basque ",
    "bn" => "Bengali ",
    "bg" => "Bulgarian ",
    "ca" => "Catalan ",
    "zh-CN" => "Chinese (China) ",
    "zh-HK" => "Chinese (Hong Kong) ",
    "zh-TW" => "Chinese (Taiwan) ",
    "hr" => "Croatian ",
    "cs" => "Czech ",
    "da" => "Danish ",
    "nl" => "Dutch ",
    "en" => "English (US) ",
    "en-GB" => "English (UK) ",
    "et" => "Estonian ",
    "fil" => "Filipino ",
    "fi" => "Finnish ",
    "fr-CA" => "French (Canadian) ",
    "fr" => "French (France) ",
    "gl" => "Galician ",
    "ka" => "Georgian ",
    "de" => "German ",
    "de-AT" => "German (Austria) ",
    "de-CH" => "German (Switzerland) ",
    "el" => "Greek ",
    "gu" => "Gujarati ",
    "iw" => "Hebrew ",
    "hi" => "Hindi ",
    "hu" => "Hungarian ",
    "is" => "Icelandic ",
    "id" => "Indonesian ",
    "it" => "Italian ",
    "ja" => "Japanese ",
    "kn" => "Kannada ",
    "ko" => "Korean ",
    "lo" => "Laothian ",
    "lv" => "Latvian ",
    "lt" => "Lithuanian ",
    "ms" => "Malay ",
    "ml" => "Malayalam ",
    "mr" => "Marathi ",
    "mn" => "Mongolian ",
    "no" => "Norwegian ",
    "ps" => "Pashto ",
    "fa" => "Persian ",
    "pl" => "Polish ",
    "pt" => "Portuguese ",
    "pt-BR" => "Portuguese (Brazil) ",
    "pt-PT" => "Portuguese (Portugal) ",
    "ro" => "Romanian ",
    "ru" => "Russian ",
    "sr" => "Serbian ",
    "si" => "Sinhalese ",
    "sk" => "Slovak ",
    "sl" => "Slovenian ",
    "es-419" => "Spanish (Latin America)",
    "es" => "Spanish (Spain) ",
    "sw" => "Swahili ",
    "sv" => "Swedish ",
    "ta" => "Tamil ",
    "te" => "Telugu ",
    "th" => "Thai ",
    "tr" => "Turkish ",
    "uk" => "Ukrainian ",
    "ur" => "Urdu ",
    "vi" => "Vietnamese ",
    "zu" => "Zulu ",
);
?>

<div class="rmagic">

    <!--Dialogue Box Starts-->
    <div class="rmcontent">


        <?php
        $form = new Form("options_security");
        $form->configure(array(
            "prevent" => array("bootstrap", "jQuery"),
            "action" => ""
        ));

        $options_pb_key = array("id" => "rm_captcha_public_key", "value" => $data['public_key'], "longDesc" => RM_UI_Strings::get('HELP_OPTIONS_ASPM_SITE_KEY'));
        $options_pr_key = array("id" => "rm_captcha_private_key", "value" => $data['private_key'], "longDesc" => RM_UI_Strings::get('HELP_OPTIONS_ASPM_SECRET_KEY'));

        if ($data['enable_captcha'] !== 'yes')
        {
            $options_pb_key['disabled'] = true;
            $options_pr_key['disabled'] = true;
        } 

        $form->addElement(new Element_HTML('<div class="rmheader">' . RM_UI_Strings::get('LABEL_ANTI_SPAM') . '</div>'));
        $form->addElement(new Element_Checkbox(RM_UI_Strings::get('LABEL_ENABLE_CAPTCHA'), "enable_captcha", array("yes" => ''), $data['enable_captcha'] == 'yes' ? array("id" => "id_rm_enable_captcha_cb", "value" => "yes", "onchange" => "checkbox_disable_elements(this,'rm_captcha_public_key,rm_captcha_private_key', 0)", "longDesc" => RM_UI_Strings::get('HELP_OPTIONS_ASPM_ENABLE_CAPTCHA')) : array("id" => "id_rm_enable_captcha_cb", "onchange" => "checkbox_disable_elements(this,'rm_captcha_public_key,rm_captcha_private_key', 0)", "longDesc" => RM_UI_Strings::get('HELP_OPTIONS_ASPM_ENABLE_CAPTCHA'))));

        $form->addElement(new Element_HTML("<div class='childfieldsrow'>"));

        //$form->addElement(new Element_Select(RM_UI_Strings::get('LABEL_CAPTCHA_LANG'), "captcha_language", $lang_arr, array("value" => $data['captcha_language'])));
        //$form->addElement(new Element_Checkbox(RM_UI_Strings::get('LABEL_CAPTCHA_AT_LOGIN'), "enable_captcha_under_login", array("yes" => ''), $data['enable_captcha_under_login'] == 'yes' ? array("value" => "yes") : array()));
        $form->addElement(new Element_Textbox(RM_UI_Strings::get('LABEL_SITE_KEY'), "public_key", $options_pb_key));
        $form->addElement(new Element_Textbox(RM_UI_Strings::get('LABEL_CAPTCHA_KEY'), "private_key", $options_pr_key));
        //$form->addElement(new Element_Select(RM_UI_Strings::get('LABEL_CAPTCHA_METHOD'), "captcha_req_method", array("curlpost" => "CurlPost", "socketpost" =>"SocketPost"), array("value" => $data['captcha_req_method'], "longDesc"=>RM_UI_Strings::get('LABEL_CAPTCHA_METHOD_HELP'))));

        $form->addElement(new Element_HTML("</div>"));

        $form->addElement(new Element_Number(RM_UI_Strings::get('LABEL_SUB_LIMIT_ANTISPAM'), "sub_limit_antispam", array("value" => $data['sub_limit_antispam'], "step" => 1, "min" => 0, "longDesc" => RM_UI_Strings::get('LABEL_SUB_LIMIT_ANTISPAM_HELP'))));

        $form->addElement(new Element_HTMLL('&#8592; &nbsp; Cancel', '?page=rm_options_manage', array('class' => 'cancel')));
        $form->addElement(new Element_Button(RM_UI_Strings::get('LABEL_SAVE')));

        $form->render();
        ?>
    </div>
</div>

<?php   