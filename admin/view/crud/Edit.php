<?php
/**
 * Admin edition view, i.e. view for editing an entry of an entity.
 *
 * @category   MT
 * @package    admin
 * @subpackage view
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
	public function __construct($model) {
		parent::__construct($model);
		parent::setTitle($this->model->getName().' '.MT_Functions::addButton( '?page=mt-'.$this->model->name().'&type=edit'));
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
			$this->_delete($_GET['action']);
		}
	}
	
	private function _delete($action) {
		if($action === 'delete') {
			MT_Functions::button( '?page=mt-'.$this->model->name().'&type=edit&id='.$this->model->getId().'&action=deleteY', 'Ja, die Datei soll wirklich gelöscht werden!', 'deleteButton' );
		} else if($action === 'deleteY') {
			if ($this->model->deleteOne()) {
				MT_Functions::box( 'delete' );
			} else {
				MT_Functions::box( 'notDelete' );
			}	
		} else {
			MT_Functions::button( '?page=mt-'.$this->model->name().'&type=edit&id='.$this->model->getId().'&action=delete', 'Löschen', 'deleteButton' );
		}
	}
	
	protected function _outputTableHead() {
		echo '<tr><th>Feld</th><th>Wert</th></tr>';
	}

	protected function _outputTableNavBottom() {
		if ($this->model->hasId() || $this->model->name() == 'news' || $this->model->name() == 'photographer' || $this->model->name() == 'photo' || $this->model->name() == 'category' || $this->model->name() == 'subcategory' ) {
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