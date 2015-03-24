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
	private $cache;
	
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
		$arrayElement = 'data['.$elementNumber.']['.$this->name.']';

		if($this->required) {
			$attribute = 'required';
		}
		
		switch ($this->type) {
			case 'string':
				return '<input type="text" name="'.$arrayElement.'" size="50" maxlength="'.$this->maxLength.'" value="'.$value.'" '.$attribute .'>';
			case 'hidde':
				return '<input type="hidden" name="'.$arrayElement.'" value="'.$value.'">';
			case 'bool':
				return '<input type="checkbox" name="'.$arrayElement.'" value="checked" '.($value ? 'checked' : '').'>';
			case 'text':
				return '<textarea name="'.$arrayElement.'" cols="38" rows="4" '.$attribute.'>'.$value.'</textarea>';
			case 'reference':
				if($this->reference === 'gallery') {
					return '<select name="'. $arrayElement.'" size="1" '.$attribute .'>
					<option value="0"></option>'
				. $this->outputAllGalleries($value) .'
				</select>';
				}
				else if($this->reference === 'photographer') {
					return '<select name="'.$arrayElement.'" size="1" '.$attribute .'>'
				. $this->outputAllPhotographers($value) .'
				</select>';
				}
				else {
					return $value;
				}		
		}
	}
	
	/**
	 * Output all photographers (Form: <option>)
	 *
	 * @param	string	$selectedPhotographer	Selected photographer
	 * @return	void
	 */
	private function outputAllPhotographers($selectedPhotographer = 1) {
		$resultString = '';
		if (empty($this->cache)) {
			$this->cache = MT_Photographer::getAll(array('id', 'name'), 'name');
		}
		foreach ($this->cache as $item) {
			$resultString .= '<option value="'.$item->id.'" '.MT_Functions::selected($selectedPhotographer, $item->id).'>'.$item->name.'</option>';
		}
		return $resultString;
	}

	/**
	 * Outputs all galleries (Form: <optgroup>, <option>)
	 *
	 * @param	string|null		$selectedGallery	Selected gallery
	 * @return	void
	 */
	private function outputAllGalleries($selectedGallery = NULL) {	
		$resultString = '';
		$tempOptgroup = NULL;
		
		if (empty($this->cache)) {
			$query = (new MT_QueryBuilder())
				->from('gallery', array( 'id' ))
				->select('wp_mt_category.name as categoryName')
				->select('wp_mt_subcategory.name as subcategoryName')
				->select('wp_mt_gallery.name as galleryName')
				->join('category', TRUE)
				->joinLeft('subcategory', TRUE)
				->orderBy(array('wp_mt_category.name', 'wp_mt_subcategory.name', 'wp_mt_gallery.name'));
			//TODO IS CALLED EACH TIME
			//echo $query;
			$this->cache = $query->getResult('ARRAY_A');
		}
		foreach ($this->cache as $row) {
			$optgroup = $row['categoryName'] . MT_Functions::getIfNotEmpty( $row['subcategoryName'], ' > ' . $row['subcategoryName'] );
			if( $tempOptgroup != $optgroup ) {
				$tempOptgroup = $optgroup;
				// Nicht beim ersten Mal beenden
				if( isset( $tempOptgroup ) ) {
					$resultString .= '</optgroup>';
				}
				$resultString .= '
				<optgroup label="' . $optgroup .'">';
			}
			$resultString .= '
					<option value="'.$row['id'].'"'.($row['id'] == $selectedGallery ? ' selected' : '').'>'.$row['galleryName'].'</option>
			';
		}
		$resultString .= '</optgroup>';
		return $resultString;
	}
	
}