<?php
/**
 * Klasse um Zend_Form Elemente zu erstellen
 *
 * @category   	MT
 * @package    	Admin
 * @subpackage	Form
 */
class MT_Admin_Form_Element_Select {

	private $_name;
	private $_array;
	private $_selected;
	private $_size;

	/**
	 * Construct MT_Admin_InputField object
	 *
	 * @param  string $value Type (e.g.: input)
	 * @return void
	 */
	public function __construct( $name, $array, $selected = '1', $size = '1' )
	{
		$this->_name = $name;
		$this->_array = $array;
		$this->_selected = $selected;
		$this->_size = $size;
	}
	
	public function __toString()
	{
		$string = '<select name="' . $this->_name . '" size="' . $this->_size . '">
';
		while( $value = current( $this->_array ) ) {
			$string .= '<option value="' . key( $this->_array ) . '" ' . selected( $this->_selected, key( $this->_array ) ) . '>' . $value . '</option>
';
			next( $this->_array );
		}
		$string .= '</select>
';
		return $string;
	}
}
?>