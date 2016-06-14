<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* Option name and values must be in lower case and should not 
 * contain white spaces. 
 * Values must be strings. Do not use true/false etc as the value instead 
 * use yes/no etc.
 * 
 *
 */

class RM_Options
{
    private $default;
    private $options_name_and_methods;
    private $prefix;
    
    public function __construct()    
    {
        $this->default = array();
        $this->prefix = 'rm_option_';
        
        //Initialize default values.
        $this->default['currency'] = 'USD';
        $this->default['currency_symbol'] = '$'; 
        $this->default['payment_gateway'] = 'paypal';
        $this->default['currency_symbol_position'] = 'before';      
        $this->default['enable_captcha'] = 'no';
        $this->default['sub_limit_antispam'] = 10;
        //$this->default['captcha_language'] = 'en';
        //$this->default['enable_captcha_under_login'] = 'no';
        //$this->default['captcha_req_method'] = 'curlpost';
        $this->default['auto_generated_password'] = 'no';
        $this->default['user_auto_approval'] = 'yes';
        $this->default['admin_notification'] = 'no';
        $this->default['display_progress_bar'] = 'no';
        $this->default['admin_email'] = get_option('admin_email');
        $this->default['user_notification_for_notes'] = 'yes';
        $this->default['user_ip'] = 'yes';

        //SMTP stuff
        $this->default['enable_smtp'] = 'no';
        $this->default['smtp_encryption_type'] = 'enc_none';

//Possible theme options: 1.matchmytheme 2.classic 3.blue
        $this->default['theme'] = 'matchmytheme';
        $this->default['form_layout'] = 'label_left';
        $this->default['enable_social'] = 'no';
        $this->default['enable_facebook'] = 'no';
        $this->default['enable_twitter'] = 'no';
        $this->default['enable_mailchimp'] = 'no';
        $this->default['send_password'] = 'yes';
        $this->default['allowed_file_types'] = 'jpg|jpeg|png|gif|doc|pdf|docx|txt';
        $this->default['allow_multiple_file_uploads'] = 'no';
        
        $this->default['senders_display_name'] = get_bloginfo( 'name', 'display' );
        $this->default['senders_email'] = get_option('admin_email');

        
        //Initialize options' names and sanitizers if any.
        $this->options_name_and_methods = array(
            'currency' => 'sanitize_currency',
            'currency_symbol' => null,
            'payment_gateway' => null,
            'paypal_email' => 'sanitize_email',
            'paypal_test_mode' => 'sanitize_checkbox',
            'display_progress_bar' => 'sanitize_checkbox',
            'paypal_page_style' => null,
            'currency_symbol_position' => 'sanitize_currency_pos',            
            'enable_captcha' => 'sanitize_checkbox',
            'sub_limit_antispam' => 'sanitize_submission_limit_antispam',
           // 'captcha_language' => 'sanitize_language',
            //'enable_captcha_under_login' => 'sanitize_checkbox',
            'public_key' => null,
            'private_key' => null,
            //'captcha_req_method' => 'sanitize_captcha_req_method',
            'auto_generated_password' => 'sanitize_checkbox',
            'user_auto_approval' => 'sanitize_checkbox',
            'admin_email' => 'sanitize_email_list',
            'admin_notification' => 'sanitize_checkbox',
            'senders_display_name' => 'sanitize_senders_display_name',
            'senders_email' => 'sanitize_email',
            'user_notification_for_notes' => 'sanitize_checkbox',
            'user_ip' => 'sanitize_checkbox',
            'enable_smtp' => 'sanitize_checkbox',
            'smtp_host' => null,
            'smtp_encryption_type' => 'sanitize_smtp_enctype',
            'smtp_port' => null,
            'smtp_auth' => 'sanitize_checkbox',
            'smtp_user_name' => null,
            'smtp_password' => 'sanitize_password',
            'theme' => null,
            'form_layout' => null,
            'enable_social' => 'sanitize_checkbox',
            'facebook_app_id' => null,
            'facebook_app_secret' => null,
            'enable_facebook' => 'sanitize_checkbox',
            'enable_twitter' => 'sanitize_checkbox',
            'consumer_key' => null,
            'consumer_secret' => null,
            'enable_mailchimp' => 'sanitize_checkbox',
            'mailchimp_key' => null,
            'send_password' => 'sanitize_checkbox',
            'allowed_file_types' => 'sanitize_allowed_file_types',
            'allow_multiple_file_uploads' => 'sanitize_checkbox',
            'default_registration_url' => null,
            'post_submission_redirection_url' => null);
    }
        
    public function get_value_of($option)
    {
        if ($option === 'currency_symbol')
            return $this->get_currency_symbol();

        if ($option === 'senders_email_formatted')
        {
            $disp_name = $this->get_value_of('senders_display_name');
            $sender_mail = $this->get_value_of('senders_email');
            
            if($disp_name && $sender_mail)
                $str = $disp_name." <".$sender_mail.">";
            elseif($disp_name && !$sender_mail)
                $str = $disp_name;
            elseif(!$disp_name && $sender_mail)
                $str = $sender_mail;
            elseif(!$disp_name && !$sender_mail)
                $str = $this->default['senders_display_name']." <".$this->default['senders_email'].">";

            return $str;
         }
        
        //To be on safe side, also prepend prefix before using the option name.
        $prefixed_option = strtolower($this->prefix.$option);

        $value = get_option($prefixed_option, null);

        //If option is not in database try to load default value.
        if (null === $value)
        {
           return isset($this->default[$option]) ? $this->default[$option] : null;
        }
        else
        {
            if ($option === 'smtp_password' && $value)
                $value = RM_Utilities::dec_str($value);
            elseif($option === 'admin_email' && trim($value) === '')
                $value = $this->default[$option];
            elseif ($option === 'allowed_file_types' && trim($value) === '')
                $value = $this->default[$option];
            elseif ($option === 'payment_gateway')
                $value = $this->default['payment_gateway'];
            
            return $value;
        }
    }
    
     public function get_currency_symbol($curr = null)
    {
       $currency = $curr?:$this->get_value_of('currency');
       $curr_arr = $this->currency_array();
       return isset($curr_arr[$currency])?$curr_arr[$currency]:$currency;
    }
    
    public function get_formatted_amount($amount, $curr = null, $use_symbol=true)
    {
        $position = $this->get_value_of('currency_symbol_position');

        $currency = $curr? : $this->get_value_of('currency');

        $symbol = $use_symbol ? $this->get_currency_symbol($currency) : (($position === 'before') ? $currency . " " : " " . $currency);

        if ($position === 'before')
            return $symbol . $amount;

        return $amount . $symbol;
    }
    

    //Resets option to its default value
    public function reset($option)
    {
        //To be on safe side, also prepend prefix before using the option name.
        $prefixed_option = strtolower($this->prefix.$option);

        $value = isset($this->default[$option]) ? $this->default[$option] : null;
        $this->set_value_of($option, $value);
    }

    public function set_value_of($option, $value)
    {
        $option = strtolower($option);

        //Update only if it is a valid option
        if(array_key_exists($option, $this->options_name_and_methods))
        {
            //Call sanitizer if exists
            $sanitizer_method = $this->options_name_and_methods[$option];
            if($sanitizer_method != null)
                $value = $this->$sanitizer_method($value);
            
            $option = $this->prefix.$option;

            if(null === $value)
                $value = '';
            update_option($option, $value, false);
        }
            
        else
            return false;
    }
    
    public function set_values($asso_array_of_options)
    {
        if(is_array($asso_array_of_options))
        {
            foreach($asso_array_of_options as $option => $value)
            {
                $this->set_value_of($option, $value);
            }
        }
    }
    
    public function get_all_options()
    {
        $options_arr = array();
        
        foreach($this->options_name_and_methods as $option=>$method)
        {
           $options_arr[$option] = $this->get_value_of($option);
        }
        return $options_arr;
    }
    

    private function currency_array()
    {
      return array(
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'AUD' => '$',
            'BRL' => 'R$',
            'CAD' => '$',
            'CZK' => 'Kč',
            'DKK' => 'kr',
            'HKD' => '$',
            'HUF' => 'Ft',
            'ILS' => '₪',
            'JPY' => '¥',
            'MYR' => 'RM',
            'MXN' => '$',
            'NZD' => '$',
            'NOK' => 'kr',
            'PHP' => '₱',
            'PLN' => 'zł',
            'SGD' => '$',
            'SEK' => 'kr',
            'CHF' => 'CHF',
            'TWD' => 'NT$',
            'THB' => '฿',
            'INR' => '₹',
            'TRY' => 'TRY',
            'RIAL' => 'RIAL',
            'RUB' => 'руб');
    }

    //Sanitizer methods for specific options.
    //If any supplied value is invalid it will revert to default or null value.
    private function sanitize_currency($curr)
    {
        $valid_currencies =  $this->currency_array();

        if(!array_key_exists($curr, $valid_currencies))
                return $this->default['currency'];
        else return $curr;
    }
    
    private function sanitize_checkbox($val)
    {
        if ($val === 'yes')
            return 'yes';
        else
            return null;
    }
    
    private function sanitize_currency_pos($val)
    {
        if ($val === 'after')
            return 'after';
        else
            return 'before';
    }
    
    private function sanitize_allowed_file_types($val)
    {
        //strip out all the whitespaces
        $val = preg_replace('/\s+/', '', $val);
        
        //check the validity. Allowed chars: a-z,A-Z,0-9 and '|'.
        $tmp = preg_replace('/[a-zA-Z\|0-9]*/', '', $val);
        //it $tmp is empty then it means the string matched completely.
        
        if ($tmp === '')
            return trim(strtolower($val),'|');
        else
            return $this->default['allowed_file_types'];
    }
    
    private function sanitize_email($val)
    {
        if (!filter_var($val, FILTER_VALIDATE_EMAIL)) 
            return null;
        else 
            return $val;

    }
    
    private function sanitize_language($val)
    {
        $valid_languages = array(
            "ar",
            "af",
            "am",
            "hy",
            "az",
            "eu",
            "bn",
            "bg",
            "ca",
            "zh-CN",
            "zh-HK",
            "zh-TW",
            "hr",
            "cs",
            "da",
            "nl",
            "en",
            "en-GB",
            "et",
            "fil",
            "fi",
            "fr-CA",
            "fr",
            "gl",
            "ka",
            "de",
            "de-AT",
            "de-CH",
            "el",
            "gu",
            "iw",
            "hi",
            "hu",
            "is",
            "id",
            "it",
            "ja",
            "kn",
            "ko",
            "lo",
            "lv",
            "lt",
            "ms",
            "ml",
            "mr",
            "mn",
            "no",
            "ps",
            "fa",
            "pl",
            "pt",
            "pt-BR",
            "pt-PT",
            "ro",
            "ru",
            "sr",
            "si",
            "sk",
            "sl",
            "es-419",
            "es",
            "sw",
            "sv",
            "ta",
            "te",
            "th",
            "tr",
            "uk",
            "ur",
            "vi",
            "zu"
        );

        if(!in_array($val, $valid_languages))
                return $this->default['captcha_language'];
        else return $val;

    }

    private function sanitize_submission_limit_antispam($val)
    {
        $val = (int)$val;
        if($val >= 0)
            return $val;
        else
            return $this->default['sub_limit_antispam'];
    }
    
    private function sanitize_captcha_req_method($val)
    {
        if ($val === 'socketpost')
            return 'socketpost';
        else
            return 'curlpost';
    }
    
    //removes any invalid email from a string of comma separated email addresses.
    private function sanitize_email_list($val)
    {
        $emails = explode(',', $val);
        $processed_emails = array();
        
        foreach($emails as $email)
        {
            if($this->sanitize_email($email)!= null)
                $processed_emails[] = $email;
        }
        
        return implode(",", $processed_emails);
    }

    private function sanitize_smtp_enctype($val)
    {
        if ($val === 'enc_tls' || $val === 'enc_ssl')
            return $val;        
        else
            return 'enc_none';
    
    }

    private function sanitize_password($val)
    {
        return RM_Utilities::enc_str($val);
    }

    private function sanitize_senders_display_name($val)
    {
        return trim($val);
    }
}
