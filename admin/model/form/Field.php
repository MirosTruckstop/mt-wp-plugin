<?php
/**
 * (Input) fields used for forms.
 * 
 * @package admin
 * @subpackage model
 */
class MT_Admin_Field {
	
	/**
	 * Field type reference
	 */
	const TYPE_REFERENCE = 'reference';
	/**
	 * Name of the field (mostly database table column)
	 * 
	 * @var string
	 */
	public $name;
	/**
	 * Label of the field
	 * 
	 * @var string 
	 */
	public $label;
	/**
	 * Type of the field: string|date|hidden|bool|text|reference
	 * @var string 
	 */
	private $type;
	/**
	 * True, if field is required.
	 * 
	 * @var bollean 
	 */
	private $required = false;
	/**
	 * True, if field is disabled.
	 * 
	 * @var bolean
	 */
	public $disabled = false;
	/**
	 * Maximum input length of the field.
	 * 
	 * @var integer 
	 */
	private $maxLength;

	/**
	 * Reference, i.e. name of a database table without praefix
	 * 
	 * @var string
	 */
	private $reference;
	/**
	 * Referenced field, i.e. a column in @$reference@
	 * 
	 * @var string 
	 */
	public $referencedField;
	
	private $staticReference;
	private $cache;
	private $cssClass;
	
	/**
	 * Create a (input) field.
	 * 
	 * @param string $name Name of the field
	 * @param string $label Label of the field
	 * @param string $type Type of the field: string|date|hidden|bool|text|reference
	 * @param null|string $cssClass
	 * @return MT_Admin_Field
	 */
	public function __construct($name, $label, $type = 'string', $cssClass = NULL) {
		$this->name = $name;
		$this->label = $label;
		$this->type = $type;
		$this->cssClass = $cssClass;
		return $this;
	}
	
	/**
	 * Returns the type of the field.
	 * 
	 * @return string Field type
	 */
	public function getType() {
		return $this->type;
	}
	
	/**
	 * Sets the referance to another database table. Overrides the field type to
	 * "reference".
	 * 
	 * @param string $reference Reference, i.e. name of a database table without praefix
	 * @param null|string $referencedField Referenced field in this table
	 * @return MT_Admin_Field
	 */
	public function setReference($reference, $referencedField = 'id') {
		$this->type = self::TYPE_REFERENCE;
		$this->reference = $reference;
		$this->referencedField = $referencedField;
		return $this;
	}
	
	/**
	 * Returns the reference (name of a database table) if it exists, otherwise
	 * false.
	 * 
	 * @return boolean|string
	 */
	public function getReference() {
		if(!empty($this->reference)) {
			return $this->reference;
		} else {
			return false;
		}
	}
	
	/**
	 * Sets a static reference. Overrides the field type to "staticReference".
	 * 
	 * @param type $staticReference
	 * @return \MT_Admin_Field
	 */
	public function setStaticReference($staticReference) {
		$this->type = self::TYPE_STATIC_REFERENCE;
		$this->staticReference = $staticReference;
		return $this;
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
			return date('m.d.Y, H:i', $value);
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
				return $this->getInputField('text', $arrayElement, $value, 50);
			case 'date':
				return $this->getInputField('text', $arrayElement, date('d.m.Y H:i:s', $value));
			case 'hidden':
				return $this->getInputField('hidden', $arrayElement, $value);
			case 'bool':
				return '<input type="checkbox" name="'.$arrayElement.'" value="checked" '.($value ? 'checked' : '').'>';
			case 'text':
				return '<textarea name="'.$arrayElement.'" class="'.$this->cssClass.'" cols="38" rows="4" '.$attribute.'>'.$value.'</textarea>';
			case self::TYPE_REFERENCE:
				if($this->reference === 'category') {
					return '<select name="'. $arrayElement.'" size="1" '.$attribute .'>'
				. $this->outputAllCategories($value) .'
				</select>';
				}
				else if($this->reference === 'subcategory') {
					return '<select name="'. $arrayElement.'" size="1" '.$attribute .'>
						<option value=""></option>'
				. $this->outputAllSubcategories($value) .'
				</select>';
				}
				else if($this->reference === 'gallery') {
					return '<select name="'. $arrayElement.'" size="1" '.$attribute .'>
					<option value=""></option>'
				. $this->outputAllGalleries($value) .'
				</select>';
				}
				else if($this->reference === 'photographer') {
					return '<select name="'.$arrayElement.'" size="1" '.$attribute .'>
					<option value="0"></option>'						
				. $this->outputAllPhotographers($value) .'
				</select>';
				} else {
					return $value;					
				}
/*			case self::TYPE_STATIC_REFERENCE:
				if($this->staticReference === 'categorySubcategory') {
					return '<select name="'. $arrayElement.'" size="1" '.$attribute .'>'
						. $this->outputAllCategoriesSubcategories($value) .'
						</select>';
				}*/
		}
	}
	
	private function getInputField($type, $name, $value, $size = NULL) {
		$attribute = '';
		if (!empty($size)) {
			$attribute .= ' size="'.$size.'"';
		}
		if (!empty($this->required)) {
			$attribute .= ' required';
		}
		if (!empty($this->maxLength)) {
			$attribute .= ' maxlength="'.$this->maxLength.'"';
		}
		if (!empty($this->cssClass)) {
			$attribute .= ' class="'.$this->cssClass.'"';
		}
		return '<input type="'.$type.'" name="'.$name.'" value="'.$value.'"'.$attribute.'>';
	}
	
	/**
	 * Returns an <option> tag
	 * 
	 * @param string $value Value of the option
	 * @param string $name Name of the option
	 * @param string $select Empty or 'selected' string
	 * @return string <option> tag
	 */
	private function getSelectOption($value, $name, $select = '') {
		return '<option value="'.$value.'" '.$select.'>'.$name.'</option>';
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
			$resultString .= $this->getSelectOption($item->id, $item->name, MT_Functions::selected($selectedPhotographer, $item->id));
		}
		return $resultString;
	}
	
	private function outputAllCategories($selectedCategory) {
		$resultString = '';
		$query = MT_Category::getAll(array('id', 'name'));
		foreach ($query as $item) {
			$resultString .= $this->getSelectOption($item->id, $item->name, MT_Functions::selected($selectedCategory, $item->id));
		}
		return $resultString;		
	}
	
	private function outputAllSubcategories($selectedSubcategory) {
		$resultString = '';
		$query = MT_Subcategory::getAll(array('id', 'name'));
		foreach ($query as $item) {
			$resultString .= $this->getSelectOption($item->id, $item->name, MT_Functions::selected($selectedSubcategory, $item->id));
		}
		return $resultString;		
	}

	/**
	 * Outputs all galleries (Form: <optgroup>, <option>)
	 *
	 * @param	string|null		$selectedGallery	Selected gallery
	 * @return	void
	 */
	public function outputAllGalleries($selectedGallery = NULL) {	
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
				$resultString .= '<optgroup label="'.$optgroup.'">';
			}
			$resultString .= $this->getSelectOption($row['id'], $row['galleryName'], ($row['id'] == $selectedGallery ? ' selected' : ''));
		}
		$resultString .= '</optgroup>';
		return $resultString;
	}
	
	/**
	 * Output all categories and subcategories (Form: <option>)
	 *
	 * @return	void
	 */
/*	public function outputAllCategoriesSubcategories($selectedCategorySubcategory) {
		$resultString = '';
		$query = (new MT_QueryBuilder())
				->from('category', array('id', 'name'))
				->orderBy(array('wp_mt_category.name', 'wp_mt_subcategory.name'))
				->joinLeftOuter('subcategory', 'wp_mt_subcategory.category = wp_mt_category.id', array('id AS subcategoryId', 'name AS subcategoryName'));
		$result = $query->getResult();
		
		foreach ($result as $item) {
			$value = $item->id.'_'.$item->sub;
			$resultString .= $this->getSelectOption($value, $item->name.MT_Functions::getIfNotEmpty($item->subcategoryName, ' > '.$item->subcategoryName), MT_Functions::selected($selectedCategorySubcategory, $value));
		}
		return $resultString;
	}*/
}