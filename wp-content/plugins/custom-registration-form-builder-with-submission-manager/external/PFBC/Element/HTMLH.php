<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HTMLH
 *
 * @author CMSHelplive
 */
class Element_HTMLH extends Element
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
            echo '<h1 class="rm_form_field_type_heading">';
        if($type === "append")
            echo '</h1>';
    }

}
