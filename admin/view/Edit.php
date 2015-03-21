<?php
/**
 * Service class for categories and subcategories
 *
 * @category   MT
 * @package    Admin
 */
class MT_View_Edit extends MT_Admin_Table_Common {
	
	private $data;

	/**
	 * Construct MT_Admin_Table_Add object
	 *
	 * @param	string	$typ	Typ (e.g.: photograper)
	 * @param	string	$id		Id of content to edit
	 * @param	string	$class	CSS table class
	 * @return	void
	 */
	public function __construct( $model, $cssClass = 'widefat') {
		parent::__construct($model, $cssClass);
		parent::setPerPage(1);
		parent::setFields(array(
			new MT_Admin_Field(NULL, 'Feld'),
			new MT_Admin_Field(NULL, 'Wert')			
		));
	}
	
	public function setData($data) {
		parent::setPerPage(count($data));
		$this->data = $data;
	}

	public function outputContent() {
		echo '<div class="wrap">';
		echo '<h2>' . $this->model->getName() . '</h2>';
		$data = $_POST['data'];
		if(!empty($data)) {
			if ($this->model->hasId()) {
				if( $this->model->update($data[0]) ) {
					MT_Functions::box( 'save' );
				} else {
					MT_Functions::box( 'exception', 'TODO: Fehler beim Aktu');
				}
			} else {
				if( $this->model->insertAll($data) ) {
					MT_Functions::box( 'save' );
				} else {
					MT_Functions::box( 'exception', 'TODO: Fehler beim Einfügen');
				}	
			}
		}
		echo '<div class="tablenav top">';
		// TODO: bisher nur für News, Photo und Photographer verfügbar
		if($this->model->isDeletable()) {
			$this->_delete();
		}
		echo '</div>';
		parent::_outputForm();
		echo '</div>';
	}
	
	private function _delete() {
		$action = $_GET['action'];
		
		if( $action === 'delete' ) {
			MT_Functions::button( '?page=mt-' . $this->model . '&id=' . $this->model->getId() . '&action=delete', 'Ja, die Datei soll wirklich gelöscht werden!', 'deleteButton' );
		} else {
			MT_Functions::button( '?page=mt-add&model=' . $this->model . '&id=' . $this->model->getId() . '&action=delete', 'Löschen', 'deleteButton' );
		}
	}
	
	protected function _outputTableNavBottom() {
		if ($this->model->hasId() || $this->model == 'news' || $this->model == 'photographer' || $this->model == 'photo' ) {
			echo MT_Functions::submitButton();
		}
		echo '&#160;'.MT_Functions::cancelButton( '?page=mt-' . $this->model );
	}


	/**
	 * Output table body
	 *
	 * @return void
	 */
	protected function _outputTableBody() {
		if (!isset($this->data)) {
			$this->data = $this->getResult();			
		}
		for($i = 0; $i < count($this->data); $i++) {
			$item = $this->data[$i];
			for( $j = 0; $j < count( $this->fields ); $j++) {
				$field = $this->fields[$j];
				if (!($this->model->hasId()) && $field->disabled) {
					continue;
				}
				echo '
							<tr ' . ($j % 2 == 0? 'class="alternate"' : '') . '>
								<td>' . $field->label . '</td>
								<td>' . $field->getElement( ($this->model->hasId() ? $item[$j] : ''), $i) . '</td>
							</tr>
';
			}
		}
	}
}
?>