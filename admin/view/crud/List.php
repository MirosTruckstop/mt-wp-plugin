<?php
/**
 * Klasse um Uebersichtstabellen zu erstellen
 *
 * @category   MT
 * @package    Admin
 */
class MT_View_List extends MT_Admin_View_Common {
	
	/**
	 * Construct MT_Admin_Table_Manage object
	 *
	 * @param	string	$typ   	Typ (e.g.: photograper)
	 * @param	string	$class	CSS table class
	 * @return	void
	 */
	public function __construct($model) {
		parent::__construct($model);
		parent::setTitle($this->model->getName().' ' . MT_Functions::addButton( '?page=mt-'.$this->model->name().'&type=edit'));
	}
	
	protected function outputHeadMessages() {
        if ( $_GET['action'] === 'delete' ) {
			$this->_delete( $_GET['id'] );
        }		
	}
	
	protected function _outputTableNavBottom() {
	}

	/**
	 * Output table body (Form: tr, td)
	 *
	 * @return void
	 */
	protected function _outputTableBody() {
		$j = 0;
		foreach ($this->getResult() as $item) {
			$j++;
			echo '<tr ' . ($j % 2 == 1? 'class="alternate"' : '') . '>
					<td><input type="checkbox" name="checked" value="' . $item['id'] .'"></td>';
			for ($i = 1; $i < count($this->fields); $i++) {
				$field = $this->fields[$i];
				if($i == 1) {
					echo '<td><a href="?page=mt-'.$this->model->name().'&type=edit&id=' . $item['id'] .'">' . $item[$field->name] .'</a></td>';					
				} else {
					echo '<td>'.$field->getString($item[$field->name]).'</td>';
				}
			}
			echo '</tr>';
		}
	}
	
	/**
	 * Delete database entry.
	 * 
	 * @param int   $id Data id
	 */
	private function _delete( $id ) {
            
            $check = FALSE;
            
            /**
             * TODO: Workaround
             */
			switch ($this->model->name()) {
				case 'photo':
					MT_Admin_Photo::__deletePhoto($id);
					$check = TRUE;
					break;
				case 'photographer':
					if ( !MT_Photographer::__hasPhotos($id) ) {
						$check = TRUE;
					}
					break;
			}
            
            if ($check) {
                $this->_dbTable->delete( $this->_typ . '_id = ?', $id);
                MT_Functions::box( 'delete' );
            } else {
                MT_Functions::box( 'notDelete' );
            }
	}	
}
?>