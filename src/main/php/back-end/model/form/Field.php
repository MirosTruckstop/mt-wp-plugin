<?php
namespace MT\WP\Plugin\Backend\Model\Form;

use MT\WP\Plugin\Api\MT_Photographer;
use MT\WP\Plugin\Common\MT_QueryBuilder;
use MT\WP\Plugin\Common\Util\MT_Util_Common;
use MT\WP\Plugin\Common\Util\MT_Util_Html;

/**
 * (Input) fields used for forms.
 */
class MT_Admin_Field
{
	
	/**
	 * Field type reference
	 */
	const TYPE_REFERENCE = 'reference';
	/**
	 * Field type data
	 */
	const TYPE_DATE = 'date';
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
	 *
	 * @var string
	 */
	private $type;
	/**
	 * True, if field is required.
	 *
	 * @var bolean
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
	 * CSS class of the input field
	 *
	 * @var string
	 */
	private $cssClass;
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
	
	/**
	 * Can be used to store query results.
	 *
	 * @var object
	 */
	private $cache;
	
	/**
	 * Create a (input) field.
	 *
	 * @param string      $name     Name of the field
	 * @param string      $label    Label of the field
	 * @param string      $type     Type of the field: string|date|hidden|bool|text|reference
	 * @param null|string $cssClass Additional CSS classes
	 *
	 * @return MT_Admin_Field
	 */
	public function __construct($name, $label, $type = 'string', $cssClass = null)
	{
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
	public function getType()
	{
		return $this->type;
	}
	
	/**
	 * Sets the referance to another database table. Overrides the field type to
	 * "reference".
	 *
	 * @param string      $reference       Reference, i.e. name of a database table without praefix
	 * @param null|string $referencedField Referenced field in this table
	 *
	 * @return MT_Admin_Field
	 */
	public function setReference($reference, $referencedField = 'id')
	{
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
	public function getReference()
	{
		if (!empty($this->reference)) {
			return $this->reference;
		} else {
			return false;
		}
	}
	
	/**
	 * Set the field as required.
	 *
	 * @return MT_Admin_Field
	 */
	public function setRequired()
	{
		$this->required = true;
		return $this;
	}
	
	/**
	 * Set the field as disabled.
	 *
	 * @return MT_Admin_Field
	 */
	public function setDisabled()
	{
		$this->disabled = true;
		return $this;
	}
	
	/**
	 * Set the maximum length of the field.
	 *
	 * @param integer $maxLength Maximum input length
	 *
	 * @return MT_Admin_Field
	 */
	public function setMaxLength($maxLength)
	{
		$this->maxLength = $maxLength;
		return $this;
	}
	
	/**
	 * Get the content of the field as string.
	 *
	 * @param string $value Value of the field
	 *
	 * @return string Content of the field
	 */
	public function getString($value)
	{
		if ($this->type === self::TYPE_DATE) {
			return date('m.d.Y, H:i', $value);
		} else {
			return ''.$value;
		}
	}
	
	/**
	 * Get the element/field.
	 *
	 * @param string       $value         Value of the field
	 * @param null|integer $elementNumber Number of elements
	 *
	 * @return string Element as string
	 */
	public function getElement($value, $elementNumber = null)
	{
		if ($this->disabled) {
			return $this->getString($value);
		}
		$arrayElement = 'data['.$elementNumber.']['.$this->name.']';

		$attribute = '';
		if ($this->required) {
			$attribute .= 'required';
		}
		if (!empty($this->cssClass)) {
			$attribute .= ' class="'.$this->cssClass.'"';
		}
		
		// phpcs:disable PEAR.WhiteSpace.ScopeIndent.IncorrectExact
		switch ($this->type) {
			case 'string':
				return $this->getInputField('text', $arrayElement, $value, 50);
			case self::TYPE_DATE:
				return $this->getInputField('text', $arrayElement, date('d.m.Y H:i:s', $value));
			case 'hidden':
				return $this->getInputField('hidden', $arrayElement, $value);
			case 'bool':
				return '<input type="checkbox" name="'.$arrayElement.'" value="checked" '.($value ? 'checked' : '').'>';
			case 'text':
				return '<textarea name="'.$arrayElement.'" cols="38" rows="4" '.$attribute.'>'.$value.'</textarea>';
			case self::TYPE_REFERENCE:
				if ($this->reference === 'category') {
					return '<select name="'. $arrayElement.'" size="1" '.$attribute .'>
							<option value=""></option>'
					. $this->outputAllCategories($value) .'
				</select>';
				} elseif ($this->reference === 'subcategory') {
					return '<select name="'. $arrayElement.'" size="1" '.$attribute .'>
						<option value=""></option>'
					. $this->outputAllSubcategories($value) .'
				</select>';
				} elseif ($this->reference === 'gallery') {
					return '<select name="'. $arrayElement.'" size="1" '.$attribute .'>
					<option value=""></option>'
					. $this->outputAllGalleries($value) .'
				</select>';
				} elseif ($this->reference === 'photographer') {
					return '<select name="'.$arrayElement.'" size="1" '.$attribute .'>
					<option value="0"></option>'
					. $this->outputAllPhotographers($value) .'
				</select>';
				} else {
					return $value;
				}
		}
		// phpcs:enable
	}
	
	/**
	 * Returns an input field as string.
	 *
	 * @param string       $type  Input type, e.g. 'text'
	 * @param string       $name  Name of the input field
	 * @param string       $value Value of the input field
	 * @param integer|null $size  Size of the input field
	 *
	 * @return string Input field as string
	 */
	private function getInputField($type, $name, $value, $size = null)
	{
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
		return '<input type="'.$type.'" name="'.$name.'" value="'.$value.'" '.$attribute.'>';
	}
	
	/**
	 * Returns an <option> tag
	 *
	 * @param string $value  Value of the option
	 * @param string $name   Name of the option
	 * @param string $select Empty or 'selected' string
	 *
	 * @return string <option> tag
	 */
	private function getSelectOption($value, $name, $select = '')
	{
		return '<option value="'.$value.'" '.$select.'>'.$name.'</option>';
	}
	
	/**
	 * Output all photographers (Form: <option>)
	 *
	 * @param string $selectedPhotographer Selected photographer
	 *
	 * @return void
	 */
	private function outputAllPhotographers($selectedPhotographer = 1)
	{
		$resultString = '';
		if (empty($this->cache)) {
			$this->cache = MT_Photographer::getAll(array('id', 'name'), 'name');
		}
		foreach ($this->cache as $item) {
			$resultString .= $this->getSelectOption($item->id, $item->name, MT_Util_Html::selected($selectedPhotographer, $item->id));
		}
		return $resultString;
	}
	
	private function outputAllCategories($selectedCategory)
	{
		$resultString = '';
		$query = MT_Category::getAll(array('id', 'name'));
		foreach ($query as $item) {
			$resultString .= $this->getSelectOption($item->id, $item->name, MT_Util_Html::selected($selectedCategory, $item->id));
		}
		return $resultString;
	}
	
	private function outputAllSubcategories($selectedSubcategory)
	{
		$resultString = '';
		$query = MT_Subcategory::getAll(array('id', 'name'));
		foreach ($query as $item) {
			$resultString .= $this->getSelectOption($item->id, $item->name, MT_Util_Html::selected($selectedSubcategory, $item->id));
		}
		return $resultString;
	}

	/**
	 * Outputs all galleries (Form: <optgroup>, <option>)
	 *
	 * @param string|null $selectedGallery Selected gallery
	 *
	 * @return void
	 */
	public function outputAllGalleries($selectedGallery = null)
	{
		$resultString = '';
		$tempOptgroup = null;
		
		if (empty($this->cache)) {
			$query = (new MT_QueryBuilder())
				->from('gallery', array( 'id' ))
				->select('wp_mt_category.name as categoryName')
				->select('wp_mt_subcategory.name as subcategoryName')
				->select('wp_mt_gallery.name as galleryName')
				->join('category', true)
				->joinLeft('subcategory', true)
				->orderBy(array('wp_mt_category.name', 'wp_mt_subcategory.name', 'wp_mt_gallery.name'));
			$this->cache = $query->getResult('ARRAY_A');
		}
		foreach ($this->cache as $row) {
			$optgroup = $row['categoryName'] . MT_Util_Common::getIfNotEmpty($row['subcategoryName'], ' > ' . $row['subcategoryName']);
			if ($tempOptgroup != $optgroup) {
				$tempOptgroup = $optgroup;
				// Nicht beim ersten Mal beenden
				if (isset($tempOptgroup)) {
					$resultString .= '</optgroup>';
				}
				$resultString .= '<optgroup label="'.$optgroup.'">';
			}
			$resultString .= $this->getSelectOption($row['id'], $row['galleryName'], ($row['id'] == $selectedGallery ? ' selected' : ''));
		}
		$resultString .= '</optgroup>';
		return $resultString;
	}
}
