<?php

class MT_Admin_Field {
	
	public $name;
	public $label;
	private $type;
	private $required = false;
	public $disabled = false;
	private $maxLength;
	private $reference;
	public $referencedField;
	
	public function __construct($name, $label, $type = 'string') {
		$this->name = $name;
		$this->label = $label;
		$this->type = $type;
		return $this;
	}
	
	public function setReference($reference, $referencedField = 'id') {
		$this->reference = $reference;
		$this->referencedField = $referencedField;
		return $this;
	}
	
	public function getReference() {
		if(!empty($this->reference)) {
			return $this->reference;
		} else {
			return false;
		}
	}
	
	/**
	 * Set the field as required.
	 * 
	 * @param   boolean $value  True, if element is required
	 */
	public function setRequired() {
		$this->required = true;
		return $this;
	}
	
	/**
	 * Set the field as disabled.
	 * 
	 * @param   boolean $value True, if the field should be disabled
	 */	
	public function setDisabled() {
		$this->disabled = true;
		return $this;
	}
	
	public function setMaxLength($maxLength) {
		$this->maxLength = $maxLength;
		return $this;
	}
	
	public function getString($value) {
		if ($this->type === 'date') {
			return gmdate("m.d.Y, H:i", $value);
		}
		else {
			return ''.$value;
		}
	}
	
	public function getElement($value, $elementNumber = NULL) {
		if ($this->disabled) {
			return $this->getString($value);
		}
		$arrayElement = 'data['.$elementNumber.']';

		if( $this->type === 'string' ) {

			if( $this->required ) {
				$attribute = 'required';
			}

			return '<input type="text" name="' . $arrayElement . '[' . $this->name . ']" size="50" maxlength="' . $this->maxLength . '" value="' . $value . '" ' . $attribute . '>';
		}
		else if( $this->type === 'text' ) {
                   			
			if( $this->required ) {
				$attribute = 'required';
			}
			return '<textarea name="' . $arrayElement . '[' . $this->name . ']" cols="38" rows="4" ' . $attribute . '>' . $value . '</textarea>';
		}
		else if($this->type === 'reference' && $this->reference === 'gallery') {
			return '<select name="'. $arrayElement . '[' . $this->name . ']" size="1">
					<option value="0"></option>'
				. MT_Functions::outputAllGalleries($value) .'
				</select>';
		}
		else if($this->type === 'reference') {
			return $value;
		}
//		else {
//			return $this->getString($value);
//		}
	}
}