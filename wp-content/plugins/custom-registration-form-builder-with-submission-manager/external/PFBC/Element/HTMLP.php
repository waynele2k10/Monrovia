<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HTMLP
 *
 * @author CMSHelplive
 */
class Element_HTMLP extends Element
{

    public function __construct($value)
    {
        $properties = array("value" => $value);
        parent::__construct("", "", $properties);
    }

    public function render()
    {
        $this->renderTag("prepend");
        echo $this->_attributes["value"];
        $this->renderTag("append");
    }
    
    public function renderTag($type = "prepend"){
        if($type === "prepend")
            echo '<p class="rm_form_field_type_paragraph">';
        if($type === "append")
            echo '</p>';
    }

}