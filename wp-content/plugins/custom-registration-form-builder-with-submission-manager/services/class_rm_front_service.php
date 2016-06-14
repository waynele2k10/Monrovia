<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class_rm_front_service
 *
 * @author CMSHelplive
 */
class RM_Front_Service extends RM_Services
{

    public function set_otp($email,$key=null)
    {
        $response = new stdClass();
        $response->error = false;
        $response->show = "#rm_otp_kcontact";
        $response->hide = "#rm_otp_kcontact";
        $response->reload = false;

            // Validate key
            if ($key)
            {
                $rm_user = $this->get('FRONT_USERS', array('otp_code' => $key), array('%s'), 'row');
                
                if (!$rm_user)
                {
                    $response->error = true;
                    $response->msg = RM_UI_Strings::get('MSG_INVALID_OTP');
                } else
                {
                    $this->set_auth_params($key, $rm_user->email);
                    $response->error = false;
                    $response->msg = RM_UI_Strings::get('MSG_AFTER_OTP_LOGIN');
                    $response->reload = true;

                }
            } else
            {
                // Validate email
                if (is_email($email))
                {
                    if ($this->is_user($email))
                    {   
                        $otp_code= $this->generate_otp($email);
                        $response->msg = RM_UI_Strings::get('MSG_OTP_SUCCESS');
                        $subject= RM_UI_Strings::get('LABEL_OTP');
                        $message= RM_UI_Strings::get('OTP_MAIL').$otp_code;
                        wp_mail($email,$subject,$message);
                    } else
                    {
                        $response->error = true;
                        $response->msg = RM_UI_Strings::get('MSG_EMAIL_NOT_EXIST');
                    }
                } else
                {
                    $response->error = true;
                    $response->msg = RM_UI_Strings::get('INVALID_EMAIL');
                }
            }

        return json_encode($response);
    }

    public function is_user($email)
    {
        $submissions = $this->get_submissions_by_email($email);
        //var_dump($submissions);die;
        if ($submissions)
            return true;
        else
            return false;
    }

    public function is_authorized()
    {
        if (!is_user_logged_in() && isset($_COOKIE['rm_secure_otp']))
        {
            $this->delete_front_user('10','m',true);
            
            $rm_user = $this->get('FRONT_USERS', array('otp_code' => $_COOKIE['rm_secure_otp']), array('%s'), 'row');

            if (empty($rm_user)){
                $this->unset_auth_params();
                return false;
            }
            else
            {
                $this->update_last_activity();
                return true;
            }
        }
        return false;
    }

    public function generate_otp($email)
    {
        $otp_code = wp_generate_password(15, false);
        
        // Delete previous OTP//$wpdb->delete($wpdb->prefix . 'rm_front_users', array('email' => $email));
        $this->delete_rows('FRONT_USERS', array('email' => $email), '%s');
        
        $front_user = new RM_Front_Users;
        
        $front_user->set(array(
            'email' => $email,
            'otp_code' => $otp_code
                ));
        
        $front_user->insert_into_db();
        return $otp_code;
    }

    public function set_auth_params($key, $email)
    {
        setcookie("rm_secure_otp", $key, time() + (3600), "/");
        setcookie("rm_autorized_otp", "true", time() + (3600), "/");
        setcookie("rm_autorized_email", $email, time() + (3600), "/");
    }
    
    public function delete_front_user($interval, $time_format, $by_last_activity = false){
        
        return RM_DBManager::delete_front_user($interval, $time_format, $by_last_activity);
    }
    
    public function update_last_activity(){
        return RM_DBManager::update_last_activity();
    }
    
    public function get_user_email(){
        
        $user_email = null;
        
        if(is_user_logged_in()){
            $user = wp_get_current_user();
            $user_email = isset($user->user_email)?$user->user_email:null;
        }
        elseif(isset($_COOKIE['rm_autorized_email'])){
            $user_email = $_COOKIE['rm_autorized_email'];
        }
        
        
        return $user_email;
    }
    
    public function log_front_user_off($user_email){
        $this->unset_auth_params();
        return RM_DBManager::delete_rows('FRONT_USERS',array('email' => $user_email));
    }
    
    private function unset_auth_params()
    {
        setcookie("rm_secure_otp", '', time() - (3600), "/");
        setcookie("rm_autorized_otp", "true", time() - (3600), "/");
        setcookie("rm_autorized_email", '', time() - (3600), "/");
    }

    public function get_submission_count($user_email)
    {
        return RM_DBManager::count('SUBMISSIONS', array('user_email' => $user_email));
    }

}
