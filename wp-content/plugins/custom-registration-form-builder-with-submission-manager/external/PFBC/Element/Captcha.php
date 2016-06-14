<?php
class Element_Captcha extends Element {
	protected $privateKey = "";
	protected $publicKey = "";

	public function __construct($label = "", array $properties = null) {
		$this->publicKey= get_option('rm_option_public_key');
		$this->privateKey= get_option('rm_option_private_key');
		parent::__construct($label, "recaptcha_response_field", $properties);
	}	

	public function render() {
		$this->validation[] = new Validation_Captcha($this->privateKey);
		require_once(dirname(__FILE__) . "/../Resources/recaptchalib.php");
		echo recaptcha_get_html($this->publicKey);
	}
}
