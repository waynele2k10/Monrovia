<?php

/**
 * Form field model
 * 
 * This class represents the model for a form's fields and has the properties 
 * of a field and also have the DB operations for the model 
 *
 * @author cmshelplive
 */
class RM_Fields extends RM_Base_Model
{

    public $field_id;
    public $form_id;
    public $field_label;
    public $field_type;
    public $field_value;
    public $field_order;
    public $field_options;
    public $is_field_primary;
    public $field_show_on_user_page;
    //private $initialized;
    //errors of field data validation
    private $errors;

    public function __construct()
    {
        $this->initialized = false;
        $this->field_id = NULL;
        $this->field_order = 99999999;
        $this->valid_options = array('field_placeholder', 'field_default_value', 'field_css_class', 'field_max_length', 'field_textarea_columns', 'field_textarea_rows', 'field_is_required', 'field_is_read_only', 'field_is_other_option');
        $this->field_options = new stdClass;
        foreach ($this->valid_options as $valid_option)
            $this->field_options->$valid_option = null;
    }

    /*     * *getters** */

    public static function get_identifier()
    {
        return 'FIELDS';
    }

    public function get_is_field_primary()
    {
        return $this->is_field_primary;
    }

    public function get_field_id()
    {
        return $this->field_id;
    }

    public function get_form_id()
    {
        return $this->form_id;
    }

    public function get_field_label()
    {
        return $this->field_label;
    }

    public function get_field_type()
    {
        return $this->field_type;
    }

    public function get_field_value()
    {
        return maybe_unserialize($this->field_value);
    }

    public function get_field_order()
    {
        return $this->field_order;
    }

    public function get_field_options()
    {
        $options_serialized = maybe_serialize($this->field_options);
        return $options_serialized;
    }

    public function get_field_default_value()
    {
        return maybe_unserialize($this->field_options->field_default_value);
    }
    
    public function get_field_show_on_user_page(){
        return $this->field_show_on_user_page();
    }

    /*     * *setters** */

    public function set_field_default_value($field_default_value)
    {
        $this->field_options->field_default_value = maybe_serialize($field_default_value);
    }

    public function set_field_show_on_user_page($field_show_on_user_page)
    {
        if(is_array($field_show_on_user_page))
            $this->field_show_on_user_page = count($field_show_on_user_page);
        else
            $this->field_show_on_user_page = $field_show_on_user_page;
            
    }
    
    public function set_is_field_primary($is_field_primary)
    {
        $this->is_field_primary = $is_field_primary;
    }

    public function set_field_id($field_id)
    {
        $this->field_id = $field_id;
    }

    public function set_form_id($form_id)
    {
        $this->form_id = $form_id;
    }

    public function set_field_label($label)
    {
        $this->field_label = $label;
    }

    public function set_field_type($type)
    {
        $this->field_type = $type;
    }

    public function set_field_value($value)
    {
        $this->field_value = maybe_serialize($value);
    }

    public function set_field_order($order)
    {
        $this->field_order = $order;
    }

    public function set_field_options($options)
    {
        $field_options = maybe_unserialize($options);
        $this->field_options = RM_Utilities::merge_object($field_options, $this->field_options);
    }

    public function set(array $request)
    {
        foreach ($request as $property => $value)
        {
            $set_property_method = 'set_' . $property;

            if (method_exists($this, $set_property_method))
            {
                $this->$set_property_method($value);
            } elseif (in_array($property, $this->valid_options, true))
            {
                if (is_array($value))
                    $value = count($value);
                $this->field_options->$property = $value;
            }
        }

       if($this->field_type == 'File')
                ;//return initiate_file_types($service, $controller);
       else
           return $this->initialized = true;
       
    }

    /*     * **Validations*** */

    private function validate_form_id()
    {
        if (empty($this->form_id))
        {
            $this->errors['FORM_ID'] = 'No Form ID defined.';
        }
        if (!is_int($this->form_id))
        {
            $this->errors['FORM_ID'] = 'Not a valid form id';
        }
    }

    private function validate_label()
    {
        if (empty($this->field_label))
        {
            $this->errors['LABEL'] = 'Label can not be empty.';
        }
        if (!is_string($this->field_label))
        {
            $this->errors['LABEL'] = 'Label must be a string.';
        }
        if (preg_match('/[^a-zA-Z0-9_\-\.]/', $this->field_label))
        {
            $this->errors['LABEL'] = 'Label can not contain special characters.';
        }
    }

    private function validate_type()
    {

        if (empty($this->field_type))
        {
            $this->errors['TYPE'] = 'field type can not be empty.';
        }

        //validation of field_type data
    }

    private function validate_value()
    {
        //validations for value of field; 
    }

    private function validate_order()
    {
        if (empty($this->field_order))
        {
            $this->errors['ORDER'] = 'field order can not be empty.';
        }
        if (is_int($this->field_order))
        {
            $this->errors['ORDER'] = 'Invalid order.';
        }
    }

    public function is_valid()
    {
        $this->validate_form_id();
        $this->validate_label();
        $this->validate_type();

        return count($this->errors) === 0;
    }

    public function errors()
    {
        return $this->errors;
    }

    /*     * **Database Operations*** */

    public function insert_into_db()
    {
        if (!$this->initialized)
        {
            return false;
        }

        if ($this->field_id)
        {
            return false;
        }

        if($this->field_type == 'File' || $this->field_type == 'Repeatable')
        {
            return true;
        }

        $data = array(
            'form_id' => $this->form_id,
            'field_label' => $this->field_label,
            'field_type' => $this->field_type,
            'field_value' => $this->field_value,
            'field_order' => $this->field_order,
            'field_show_on_user_page' => $this->field_show_on_user_page,
            'is_field_primary' => $this->is_field_primary?$this->is_field_primary:0,
            'field_options' => $this->get_field_options(),
        );

        $data_specifiers = array(
            '%d',
            '%s',
            '%s',
            '%s',
            '%d',
            '%d',
            '%d',
            '%s'
        );

        $result = RM_DBManager::insert_row('FIELDS', $data, $data_specifiers);

        if (!$result)
        {
            return false;
        }

        $this->field_id = $result;

        return $result;
    }

    public function update_into_db()
    {
        if (!$this->initialized)
        {
            return false;
        }
        if (!$this->field_id)
        {
            return false;
        }

        $data = array(
            'form_id' => $this->form_id,
            'field_label' => $this->field_label,
            'field_type' => $this->field_type,
            'field_value' => $this->field_value,
            'field_show_on_user_page' => $this->field_show_on_user_page,
            'field_options' => $this->get_field_options()
        );

        $data_specifiers = array(
            '%d',
            '%s',
            '%s',
            '%s',
            '%d',
            '%s'
        );

        $result = RM_DBManager::update_row('FIELDS', $this->field_id, $data, $data_specifiers);

        if (!$result)
        {
            return false;
        }

        return true;
    }

    public function load_from_db($field_id, $should_set_id = true)
    {

        $result = RM_DBManager::get_row('FIELDS', $field_id);

        if (null !== $result)
        {
            if ($should_set_id)
                $this->field_id = $field_id;
            else
                $this->field_id = null;
            $this->form_id = $result->form_id;
            $this->field_label = $result->field_label;
            $this->field_type = $result->field_type;
            $this->field_value = $result->field_value;
            $this->field_order = $result->field_order;
            $this->is_field_primary = $result->is_field_primary;
            $this->field_show_on_user_page = $result->field_show_on_user_page;
            $this->set_field_options($result->field_options);
        } else
        {
            return false;
        }
        $this->initialized = true;
        return true;
    }

    public function remove_from_db()
    {
        return RM_DBManager::remove_row('FIELDS', $this->field_id);
    }

}
