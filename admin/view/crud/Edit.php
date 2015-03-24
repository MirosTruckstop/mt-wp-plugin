<?php
/**
 * Service class for categories and subcategories
 *
 * @category   MT
 * @package    Admin
 */
class MT_View_Edit extends MT_Admin_View_Common {
	
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
		parent::setTitle($this->model->getName());
		parent::setPerPage(1);
	}
	
	public function setData($data) {
		parent::setPerPage(count($data));
		$this->data = $data;
	}
	
	protected function outputHeadMessages() {
		$data = stripslashes_deep($_POST['data']);
		if(!empty($data)) {
			if (parent::updateOrInsertAll($data)) {
				MT_Functions::box( 'save' );
			} else {
				MT_Functions::box( 'exception', 'TODO: Fehler beim Einfügen');					
			}
		}
		if($this->model->isDeletable()) {
			$this->_delete();
		}
	}
	
	private function _delete() {
		$action = $_GET['action'];
		
		if( $action === 'delete' ) {
			MT_Functions::button( '?page=mt-'.$this->model->name().'&id='.$this->model->getId().'&action=delete', 'Ja, die Datei soll wirklich gelöscht werden!', 'deleteButton' );
		} else {
			MT_Functions::button( '?page=mt-add&model='.$this->model->name().'&id='.$this->model->getId().'&action=delete', 'Löschen', 'deleteButton' );
		}
	}
	
	protected function _outputTableHead() {
		echo '<tr><th>Feld</th><th>Wert</th></tr>';
	}

	protected function _outputTableNavBottom() {
		if ($this->model->hasId() || $this->model->name() == 'news' || $this->model->name() == 'photographer' || $this->model->name() == 'photo' ) {
			echo MT_Functions::submitButton();
		}
		echo '&#160;'.MT_Functions::cancelButton( '?page=mt-'.$this->model->name());
	}


	/**
	 * Output table body
	 *
	 * @return void
	 */
	protected function _outputTableBody() {
		if (isset($this->data)) {
			foreach ($this->data as $i => $item) {
				$this->_outputItem($item, $i, TRUE);
			}
		} else if ($this->model->hasId()) {
			foreach ($this->getResult() as $i => $item) {
				$this->_outputItem($item, $i, TRUE);
			}
		} else {
			$this->_outputItem(NULL, 0, FALSE);
		}
	}
	
	private function _outputItem($item, $i, $showContent) {
		foreach ($this->fields as $index => $field) {
			if ($field->name === 'id') {
				echo $field->getElement($item[$field->name], $i);
				continue;
			}
			else if (!($this->model->hasId()) && $field->disabled) {
				continue;
			}
			echo '		<tr ' . ($index % 2 == 0? 'class="alternate"' : '') . '>
							<td>' . $field->label . '</td>
							<td>' . $field->getElement( ($showContent ? $item[$field->name] : ''), $i) . '</td>
						</tr>';
		}
	}
}
?>