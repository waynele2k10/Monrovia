<?php

/**
 * Model class for submissions
 * 
 * @author cmshelplive
 */
class RM_Submissions extends RM_Base_Model
{

    private $submission_id;
    private $form_id;
    private $data;
    private $submitted_on;
    private $user_email;
    //private $initialized;
    //errors submission validation
    private $errors;
   

    public function __construct()
    {
        $this->initialized = false;
        $this->submission_id = NULL;
    }
    
     /*     * *Getters** */
    
    public static function get_identifier()
    {
        return 'SUBMISSIONS';
    }
    
    public function get_submission_id()
    {
        return $this->submission_id;
    }

    public function get_form_id()
    {
        return $this->form_id;
    }

    public function get_data()
    {
        return RM_Utilities::strip_slash_array(maybe_unserialize($this->data));
    }

    public function get_submitted_on()
    {
        return $this->submitted_on;
    }

    public function get_user_email()
    {
        return trim($this->user_email);
    }
    
    public function get_unique_token()
    {
        return null;
    }
    

    /*     * *Setters** */

    public function set_submission_id($submission_id)
    {
        $this->submission_id = $submission_id;
    }

    public function set_unique_token($unique_token)
    {
        $this->unique_token = $unique_token;
    }
    
    public function set_form_id($form_id)
    {
        $this->form_id = $form_id;
    }

    public function set_data($data)
    {
        $this->data = maybe_serialize($data);
    }

    public function set_submitted_on($submitted_on)
    {
        $this->submitted_on = $submitted_on;
    }

    public function set_user_email($user_email)
    {
        $this->user_email = $user_email;
    }
    
//    public function set($request)
//    {
//
//        foreach ($request as $property => $value)
//        {
//            if (property_exists ($this ,$property ))
//            {
//                $this->$property = $value;
//            }
//        }
//    }

    /*     * *validations** */

    private function validate_form_id()
    {
        if (empty($this->form_id))
        {
            $this->errors['FORM_ID'] = 'Form id can not be empty';
        }
    }

    private function validate_data()
    {
        if (empty($this->data))
        {
            $this->errors['DATA'] = 'No data submitted';
        }
        if (!is_array($this->data))
        {
            $this->errors['DATA'] = 'Invalid data format';
        } $this->errors['DATA'] = 'Invalid data format';
    }

    private function validate_user_email()
    {
        if (empty($this->user_email))
        {
            $this->errors['USER_EMAIL'] = 'User email must not be empty.';
        }
        if (!is_email($this->user_email))
        {
            $this->errors['USER_EMAIL'] = 'Invalid Email format.';
        }
    }
    
     public function is_valid()
    {
        $this->validate_form_id();
        $this->validate_data();
        $this->validate_user_email();
        
        return count($this->errors) === 0;
    }
    
    public function errors(){
        return $this->errors;
    }
    
     /*     * **Database Operations*** */

    public function insert_into_db()
    {

        if (!$this->initialized)   
        {
            return false;
        }

        if ($this->submission_id)
        {
            return false;
        }

        $data = array(            
                    'form_id' => $this->form_id,
                    'data' => $this->data,
                    'user_email' => $this->user_email,
                    'submitted_on' => date('Y-m-d H:i:s'),
                    'unique_token'=> null
                    );

        $data_specifiers = array(
            '%d',
            '%s',
            '%s',
            '%s',
            '%s'
        );

        $result = RM_DBManager::insert_row('SUBMISSIONS', $data, $data_specifiers);

        if (!$result)
        {
            return false;
        }

        $this->submission_id = $result;

        return $result;
    }

    public function update_into_db()
    {
        if (!$this->initialized)
        {
            return false;
        }
        if (!$this->submission_id)
        {
            return false;
        }

        $data = array(            
                    'form_id' => $this->form_id,
                    'data' => $this->data,
                    'user_email' => $this->user_email
                    );

        $data_specifiers = array(
            '%d',
            '%s',
            '%s'
        );

        $result = RM_DBManager::update_row('SUBMISSIONS', $this->submission_id, $data, $data_specifiers);

        if (!$result)
        {
            return false;
        }

        return true;
    }

    public function load_from_db($submission_id,$should_set_id=true)
    {

        $result = RM_DBManager::get_row('SUBMISSIONS', $submission_id);

        if (null !== $result)
        {       
                if($should_set_id)
                    $this->submission_id = $submission_id;
                $this->form_id = $result->form_id;
                $this->data = $result->data;
                $this->user_email = $result->user_email;
                $this->submitted_on = $result->submitted_on;
                $this->unique_token = $result->unique_token;
        } else
        {
            return false;
        }

        return true;
    }

    public function remove_from_db()
    {
        return RM_DBManager::remove_row('SUBMISSIONS', $this->submission_id);
    }


}
