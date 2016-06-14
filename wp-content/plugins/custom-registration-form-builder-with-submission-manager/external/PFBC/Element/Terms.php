<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Terms
 *
 * @author CMSHelplive
 */
class Element_Terms extends Element
{

    private $terms;
    protected $_attributes = array("rows" => "5");

    public function __construct($label, $name, $options, array $properties = null)
    {
        $configuration = array(
            "label" => $label,
            "name" => $name
        );

        /* Merge any properties provided with an associative array containing the label
          and name properties. */
        if (is_array($properties))
            $configuration = array_merge($configuration, $properties);

        $this->configure($configuration);
        $this->_attributes["default_value"] = $options;
    }

    public function render()
    {
        //var_dump($this->_attributes['name']);die;
        echo "<div class='rm_terms_checkbox'><input type='checkbox'", $this->getAttributes(array("default_value", "value")), "></div>";
        echo "<div class='rm_terms_textarea'><textarea disabled id='rm_terms_area_", $this->_attributes['name'], "' class='rm_terms_area'>";
        if (!empty($this->_attributes["default_value"]))
            echo $this->filter($this->_attributes["default_value"]);
        echo "</textarea></div>";
    }

    public function getAttributes($ignore = "")
    {

        $str = "";
        if (!empty($this->_attributes))
        {
            if (!is_array($ignore))
                $ignore = array($ignore);
            $attributes = array_diff(array_keys($this->_attributes), $ignore);
            foreach ($attributes as $attribute)
            {
                $str .= ' ' . $attribute;
                if ($this->_attributes[$attribute] !== "" && !is_array($this->_attributes[$attribute] && $attribute === "default_value"))
                    $str .= '="' . $this->filter($this->_attributes[$attribute]) . '"';
            }
        }
        return $str;
    }

}
