<?
///////////////////////////

class office_location {
	function office_location($id,$name,$thumbnail,$override_contact_html,$address,$city,$state,$zip,$fax,$email){
		$this->id = $id;
		$this->name = $name;
		$this->thumbnail = $thumbnail;
		$this->override_contact_html = $override_contact_html;
		$this->address = $address;
		$this->city = $city;
		$this->state = $state;
		$this->zip = $zip;
		$this->fax = $fax;
		$this->email = $email;
	}
	function output_contact_info(){
		if($this->override_contact_html==''){
		?>
		<br />
		<b>Monrovia</b>
		<br />
		<?=$this->address?><br />
		<?=$this->city?>, <?=$this->state?> <?=$this->zip?><br />
		Fax <?=$this->fax?><br />
		E-mail: <span class="email_link"><?=str_replace('@','(#)',$this->email)?></span>
		<?
		}else{
			echo($this->override_contact_html);
		}
	}
}

///////////////////////////
?>