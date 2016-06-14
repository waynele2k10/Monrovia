<?php
class Element_Checkbox extends OptionElement {
	protected $_attributes = array("type" => "checkbox");
	protected $inline;

	public function render() { 
		if(isset($this->_attributes["value"])) {
			if(!is_array($this->_attributes["value"]))
				$this->_attributes["value"] = array($this->_attributes["value"]);
		}
		else
			$this->_attributes["value"] = array();

		if(substr($this->_attributes["name"], -2) != "[]")
			$this->_attributes["name"] .= "[]";

		$labelClass = 'rmradio';//'rm'. $this->_attributes["type"];
		if(!empty($this->inline))
			$labelClass .= " inline";

		$count = 0;
                echo '<ul class="' .$labelClass. '">';
		foreach($this->options as $value => $text) {
			$value = $this->getOptionValue($value);
                        
                        echo '<li> <input id="', $this->_attributes["id"], '-', $count, '"', $this->getAttributes(array("id", "value", "checked", "required")), ' value="', $this->filter($value), '"';
			//echo '<label class="', $labelClass, '"> <input id="', $this->_attributes["id"], '-', $count, '"', $this->getAttributes(array("id", "value", "checked", "required")), ' value="', $this->filter($value), '"';
			if(in_array($value, $this->_attributes["value"]))
				echo ' checked="checked"';
			//echo '/> ', $text, ' </label> ';
			echo '/> ', $text, ' </li> ';
			++$count;
		}
                if(isset($this->_attributes["rm_is_other_option"]) && $this->_attributes["rm_is_other_option"] == 1){                        
                   echo '<li onclick="rm_append_other_option(this)"><input type="checkbox">Other</li><li id="rm_appendable_textbox" style="display:none"><input type="text" id="rm_textbox_other_option" name="'.$this->_attributes["name"].'" disabled></li>';
                }
                    echo '</ul>';
	}
}
