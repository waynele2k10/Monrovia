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

class Element_HTMLL extends Element
{

    public function __construct($value,$href,$properties = null)
    {
        $properties['value'] = $value;
        $properties['href'] = $href;
        parent::__construct("", "", $properties);
    }

    public function render()
    {
        $this->renderTag("prepend");
        echo $this->_attributes["value"];
        $this->renderTag("append");
    }
    
    public function renderTag($type = "prepend"){
        $class = isset($this->_attributes['class'])?$this->_attributes['class']:null;
        $href = isset($this->_attributes['href'])?$this->_attributes['href']:null;
        
        if($type === "prepend")
            echo '<div class="' .$class. '"><a href="' .$href. '">';
        if($type === "append")
            echo '</a></div>';
    }

}