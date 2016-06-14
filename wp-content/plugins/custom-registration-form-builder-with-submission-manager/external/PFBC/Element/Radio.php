<?php
class Element_Radio extends OptionElement {
	protected $_attributes = array("type" => "radio");
	protected $inline;

	public function render() { 
		$labelClass = 'rmradio';//$this->_attributes["type"];
		if(!empty($this->inline))
			$labelClass .= " inline";

		$count = 0;
                
            echo '<ul class="' .$labelClass. '">';
		foreach($this->options as $value => $text) {
			$value = $this->getOptionValue($value);

			//echo '<label class="', $labelClass . '"> <input id="', $this->_attributes["id"], '-', $count, '"', $this->getAttributes(array("id", "value", "checked")), ' value="', $this->filter($value), '"';
			echo '<li> <input id="', $this->_attributes["id"], '-', $count, '"', $this->getAttributes(array("id", "value", "checked")), ' value="', $this->filter($value), '"';
			if(isset($this->_attributes["value"]) && $this->_attributes["value"] == $value)
				echo ' checked="checked"';
			//echo '/> ', $text, ' </label> ';
			echo '/> ', $text, ' </li> ';
			++$count;
		}
            echo '</ul>';
	}
}
